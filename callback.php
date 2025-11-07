<?php
$access_token = 'nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=';

$json = file_get_contents('php://input');
$event = json_decode($json, true);

error_log(print_r($event, true));

if (!empty($event['events'])) {
    foreach ($event['events'] as $e) {
        $replyToken = $e['replyToken'] ?? '';
        $messageText = $e['message']['text'] ?? '';

        $replyMessage = [
            'type' => 'text',
            'text' => '受け取ったよ: ' . $messageText
        ];

        $url = 'https://api.line.me/v2/bot/message/reply';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ];
        $body = json_encode([
            'replyToken' => $replyToken,
            'messages' => [$replyMessage]
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_exec($ch);
        curl_close($ch);
    }
}

http_response_code(200);
echo "OK";
?>
