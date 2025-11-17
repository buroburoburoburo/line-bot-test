<?php
// -------------------------------------------------
// 設定
// -------------------------------------------------
$channelAccessToken = "nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=";
$channelSecret      = "7d476031f0cd9aa123139ab88c3a0d13";
$calorieMamaApiKey  = "4207c53db948f046dfb21bfb45ccfc8f";

$logDir = __DIR__ . "/log";
if (!file_exists($logDir)) mkdir($logDir, 0777, true);

// -------------------------------------------------
// LINE署名チェック
// -------------------------------------------------
$signature = $_SERVER["HTTP_X_LINE_SIGNATURE"] ?? '';
$body = file_get_contents("php://input");
file_put_contents($logDir . "/latest_webhook.json", $body);

// 署名チェック
if (!hash_equals(base64_encode(hash_hmac('sha256', $body, $channelSecret, true)), $signature)) {
    http_response_code(403);
    exit("Invalid signature");
}

$events = json_decode($body, true)["events"] ?? [];

foreach ($events as $event) {

    // -------------------------------------------------
    // 画像メッセージのみ処理
    // -------------------------------------------------
    if ($event["type"] === "message" && $event["message"]["type"] === "image") {

        $replyToken = $event["replyToken"];
        $messageId = $event["message"]["id"];

        // ① LINE から画像を取得
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

        // ② 一時保存
        $tmpPath = __DIR__ . "/tmp_image.jpg";
        file_put_contents($tmpPath, $imgData);

        // ③ Calorie Mama API へ送る
        $apiUrl = "https://api.caloriemama.ai/v1/foodrecognition";
        $postFields = [
            "file" => new CURLFile($tmpPath)
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

        // ④ JSON保存（index.php 用）
        $resultDir = __DIR__ . "/results";
        if (!file_exists($resultDir)) mkdir($resultDir, 0777, true);

        $timestamp = date("Ymd_His");
        $resultPath = "{$resultDir}/result_{$timestamp}.json";
        file_put_contents($resultPath, $response);

        // 最新リンク
        @unlink("{$resultDir}/result_latest.json");
        symlink($resultPath, "{$resultDir}/result_latest.json");

        // ⑤ LINEへ返信
        if (!empty($result["results"])) {
            $text = "解析しました！\n";
            foreach ($result["results"] as $item) {
                $text .= "{$item['name']}：約 {$item['calories']} kcal\n";
            }
        } else {
            $text = "食品が検出できませんでした。";
        }

        replyMessage($replyToken, $text);
    }
}

// -------------------------------------------------
// 返信用関数
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
