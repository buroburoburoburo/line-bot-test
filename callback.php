<?php
// -------------------------------------------------
// 設定
// -------------------------------------------------
$channelAccessToken = "nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=";

// CalorieMama API
$apiKey = "4207c53db948f046dfb21bfb45ccfc8f";

// DB
$dbh = new PDO("mysql:host=localhost;dbname=health;charset=utf8","root","AdminDef");

// -------------------------------------------------
// LINE Webhook受信
// -------------------------------------------------
$input = file_get_contents("php://input");
$events = json_decode($input, true);

if (!isset($events["events"])) exit;

foreach ($events["events"] as $event) {

    // 画像メッセージだけ処理
    if ($event["type"] !== "message" || $event["message"]["type"] !== "image") continue;

    $userId = $event["source"]["userId"];
    $messageId = $event["message"]["id"];

    // -------------------------------------------------
    // ① LINE の画像を取得
    // -------------------------------------------------
    $url = "https://api-data.line.me/v2/bot/message/{$messageId}/content";
    $headers = ["Authorization: Bearer $channelAccessToken"];

    $ch = curl_init($url_
    ?>
