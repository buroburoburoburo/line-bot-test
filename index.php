<?php
// チャネルアクセストークン（Messaging API設定画面で発行）
$access_token = 'nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=';

// LINEからのリクエストを取得
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// メッセージを取り出す
$text = $data['events'][0]['message']['text'] ?? '';
$replyToken = $data['events'][0]['replyToken'] ?? '';

// オウム返し（送られたメッセージをそのまま返す）
if ($text) {
    $response = [
        'replyToken' => $replyToken,
        'messages' => [
            ['type' => 'text', 'text' => "あなたが送ったのは：「{$text}」ですね！"]
        ]
    ];

    $ch = curl_init('https://api.line.me/v2/bot/message/reply');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
    $result = curl_exec($ch);
    curl_close($ch);
}
echo "OK";
