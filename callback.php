<?php
$access_token = 'nMdpRsipbRbnb2LhpFzdNjUAv2FRUJPrTDtdYY9UeKTMWaS7vap2M84JsUOvLGxw0ctninsD6sFTUIx8sETnE7K8OgGCObdFIQUPYZWqRejOLd+Fy61qG/Rm988TbtALitMtEJQoXRx4OkPnCk93QAdB04t89/1O/w1cDnyilFU=';
// LINEから受信データを取得
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!empty($data['events'][0]['message']) && $data['events'][0]['message']['type'] === 'image') {
    $replyToken = $data['events'][0]['replyToken'];
    $messageId = $data['events'][0]['message']['id'];

    // uploadsフォルダ作成
    $uploadDir = __DIR__ . "/uploads";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    // LINEから画像を取得
    $ch = curl_init("https://api-data.line.me/v2/bot/message/{$messageId}/content");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$access_token}"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $img_data = curl_exec($ch);
    curl_close($ch);

    // 一時ファイルに保存
    $timestamp = date("Ymd_His");
    $tmp_path = "{$uploadDir}/tmp_{$timestamp}.jpg";
    file_put_contents($tmp_path, $img_data);

    // 画像をリサイズ（最大幅・高さ1024px）
    $src = imagecreatefromstring(file_get_contents($tmp_path));
    $w = imagesx($src);
    $h = imagesy($src);
    $max_size = 1024;

    if ($w > $max_size || $h > $max_size) {
        if ($w > $h) {
            $new_w = $max_size;
            $new_h = intval($h * $max_size / $w);
        } else {
            $new_h = $max_size;
            $new_w = intval($w * $max_size / $h);
        }
        $dst = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        $img_path = "{$uploadDir}/resized_{$timestamp}.jpg";
        imagejpeg($dst, $img_path, 85);
        imagedestroy($dst);
    } else {
        $img_path = $tmp_path;
    }
    imagedestroy($src);

    // CalorieMama API送信
    $api_key = 'YOUR_CALORIEMAMA_API_KEY';
    $curl = curl_init('https://api-portal.caloriemama.ai/v1/food_recognitions?locale=ja_JP');
    $cfile = new CURLFile($img_path, 'image/jpeg', basename($img_path));
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer {$api_key}"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => ['image_file' => $cfile]
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    // 結果を保存
    $resultDir = __DIR__ . "/results";
    if (!file_exists($resultDir)) mkdir($resultDir, 0777, true);
    $result_path = "{$resultDir}/result_{$timestamp}.json";
    file_put_contents($result_path, $response);
    @unlink(__DIR__ . "/result_latest.json"); // 古いリンク削除
    symlink($result_path, __DIR__ . "/result_latest.json");

    // ユーザーに返信
    $reply = [
        'replyToken' => $replyToken,
        'messages' => [
            ['type' => 'text', 'text' => "画像を解析しました！\n結果はこちら:\nhttps://your-domain.com"]
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
?>
