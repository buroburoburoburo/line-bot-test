<?php
// 設定
$channelAccessToken = "nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=";
$channelSecret      = "7d476031f0cd9aa123139ab88c3a0d13";
$calorieMamaApiKey  = "4207c53db948f046dfb21bfb45ccfc8f";
// -------------------------------------------------
// DB 接続
// -------------------------------------------------
$pdo = new PDO(
    "mysql:host=host.docker.internal;dbname=health;charset=utf8",
    "root",
    "AdminDef"
);

// -------------------------------------------------
// Webhook受信
// -------------------------------------------------
$input = file_get_contents("php://input");
$events = json_decode($input, true);

// 署名チェック
$signature = $_SERVER["HTTP_X_LINE_SIGNATURE"] ?? "";
if (!hash_equals(
    base64_encode(hash_hmac("sha256", $input, $channelSecret, true)),
    $signature
)) {
    http_response_code(400);
    exit("Invalid signature");
}

// -------------------------------------------------
// メイン処理
// -------------------------------------------------
foreach ($events["events"] as $event) {

    if (
        $event["type"] === "message" &&
        $event["message"]["type"] === "image"
    ) {

        $replyToken = $event["replyToken"];
        $messageId  = $event["message"]["id"];
        $userId     = $event["source"]["userId"];

        // 今日の日付
        $day = date("Ymd");

        // -------------------------------
        // ① LINE から画像をダウンロード
        // -------------------------------
        $url = "https://api-data.line.me/v2/bot/message/{$messageId}/content";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$channelAccessToken}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $imgData = curl_exec($ch);
        curl_close($ch);

        if (!$imgData) {
            replyText($replyToken, "画像を取得できませんでした。");
            exit;
        }

        // -------------------------------
        // ② サーバーに画像を保存
        // -------------------------------
        $imageDir = __DIR__ . "/images";
        if (!file_exists($imageDir)) mkdir($imageDir, 0777, true);

        $fileName = date("Ymd_His") . "_" . $messageId . ".jpg";
        $savePath = "{$imageDir}/{$fileName}";
        file_put_contents($savePath, $imgData);

        // -------------------------------
        // ③ CalorieMama 解析
        // -------------------------------
        $apiUrl = "https://api.caloriemama.ai/v1/foodrecognition";

        $postFields = ["file" => new CURLFile($savePath)];

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

        // -------------------------------
        // ④ DBへ保存
        // -------------------------------
        $stmt = $pdo->prepare("
            INSERT INTO food_images (day, userId, image_path, created_at)
            VALUES (:day, :userId, :image_path, NOW())
        ");

        $stmt->execute([
            ":day"        => $day,
            ":userId"     => $userId,
            ":image_path" => "images/{$fileName}"
        ]);

        // -------------------------------
        // ⑤ LINEへ返信
        // -------------------------------
        if (!empty($result["results"])) {

            $replyText = "解析結果：\n";
            foreach ($result["results"] as $r) {
                $replyText .= "{$r['name']}：約 {$r['calories']} kcal\n";
            }

            replyText($replyToken, $replyText);

        } else {
            replyText($replyToken, "解析できませんでした。\n画像を保存しました。");
        }
    }
}

// -------------------------------------------------
// LINEへ返信関数
// -------------------------------------------------
function replyText($replyToken, $text)
{
    global $channelAccessToken;

    $url = "https://api.line.me/v2/bot/message/reply";
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer {$channelAccessToken}"
    ];

    $postData = [
        "replyToken" => $replyToken,
        "messages" => [
            ["type" => "text", "text" => $text]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>
