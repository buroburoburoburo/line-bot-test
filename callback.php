<?php
$access_token = 'nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=';

// LINEから受信データを取得
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// イベントタイプ確認
if (!empty($data['events'][0]['message']) && $data['events'][0]['message']['type'] === 'image') {
    $replyToken = $data['events'][0]['replyToken'];
    $messageId = $data['events'][0]['message']['id'];

    // LINEから画像を取得
    $ch = curl_init("https://api-data.line.me/v2/bot/message/{$messageId}/content");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$access_token}"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $img_data = curl_exec($ch);
    curl_close($ch);

    // 画像を一時保存
    $img_path = __DIR__ . "/uploads/{$messageId}.jpg";
    file_put_contents($img_path, $img_data);

    // CalorieMama APIへ送信
    $api_key = 'YOUR_CALORIEMAMA_API_KEY';
    $curl = curl_init('https://api-portal.caloriemama.ai/v1/food_recognitions?locale=ja_JP');
    $cfile = new CURLFile($img_path, 'image/jpeg', basename($img_path));

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer {$api_key}"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => ['photo' => $cfile]
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    // 結果を保存（後でindex.phpで表示する）
    file_put_contents(__DIR__ . "/result.json", $response);

    // ユーザーに返信
    $reply = [
        'replyToken' => $replyToken,
        'messages' => [
            ['type' => 'text', 'text' => '画像を解析中です！数秒後に結果ページを確認してください。']
        ]
    ];
    $ch = curl_init('https://api.line.me/v2/bot/message/reply');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer {$access_token}"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($reply)
    ]);
    curl_exec($ch);
    curl_close($ch);
}
echo "OK";
