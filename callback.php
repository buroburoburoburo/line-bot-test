<?php
$channelAccessToken = "nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=";
$channelSecret      = "7d476031f0cd9aa123139ab88c3a0d13";
$calorieMamaApiKey  = "4207c53db948f046dfb21bfb45ccfc8f";

// DB 接続
$pdo = new PDO(
    "mysql:host=localhost;dbname=health;charset=utf8",
    "root",
    "AdminDef"
);

// 画像保存フォルダ
$imageDir = __DIR__ . "/images";
if (!file_exists($imageDir)) mkdir($imageDir, 0777, true);

// LINE署名チェック
$signature = $_SERVER["HTTP_X_LINE_SIGNATURE"] ?? '';
$body = file_get_contents("php://input");

if (!hash_equals(base64_encode(hash_hmac('sha256', $body, $channelSecret, true)), $signature)) {
    http_response_code(403);
    exit("Invalid signature");
}

$events = json_decode($body, true)["events"] ?? [];

// メイン処理
foreach ($events as $event) {

    if ($event["type"] === "message" && $event["message"]["type"] === "image") {

        $replyToken = $event["replyToken"];
        $messageId  = $event["message"]["id"];
        $userId     = $event["source"]["userId"];
    
        // ① LINEから画像取得
        $url = "https://api-data.line.me/v2/bot/message/$messageId/content";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$channelAccessToken}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $imgData = curl_exec($ch);
        curl_close($ch);

        if (!$imgData) {
            replyMessage($replyToken, "画像を取得できませんでした。");
            exit;
        }

        // ② ローカルに保存（ファイル名は日時）
        $fileName = date("Ymd_His") . "_" . $messageId . ".jpg";
        $savePath = "$imageDir/$fileName";
        file_put_contents($savePath, $imgData);

        // ★★ ③ DBへ画像パスを保存 ★★
        $sql = "INSERT INTO food_images (userId, image_path, created_at)
                VALUES (:userId, :image_path, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':userId'     => $userId,
            ':image_path' => "images/$fileName"
        ]); 


        // ④ CalorieMama に送信してカロリー解析
        $apiUrl = "https://api.caloriemama.ai/v1/foodrecognition";
        $postFields = [
            "file" => new CURLFile($savePath)
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-api-key: {$calorieMamaApiKey}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        // ⑤ LINEへ返信
        if (!empty($result["results"])) {
            $text = "解析しました！\n";
            foreach ($result["results"] as $item) {
                $text .= "{$item['name']}：約 {$item['calories']} kcal\n";
            }
            $text .= "\n画像パスを保存しました：\nimages/$fileName";
        } else {
            $text = "食品が検出できませんでした。\n画像パス：images/$fileName";
        }

        replyMessage($replyToken, $text);
    }
}

// -------------------------------------------------
// 返信関数
// -------------------------------------------------
function replyMessage($replyToken, $message)
{
    global $channelAccessToken;

    $url = "https://api.line.me/v2/bot/message/reply";
    $postData = [
        "replyToken" => $replyToken,
        "messages" => [
            ["type" => "text", "text" => $message]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$channelAccessToken}"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>
