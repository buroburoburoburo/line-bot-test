<?php
// ------------------------------
// 設定
// ------------------------------
$LINE_ACCESS_TOKEN = "【LINEチャネルアクセストークン】";
$CALORIE_API_KEY   = "【CalorieMama APIキー】";

// ------------------------------
// ① LINEから受信データ取得
// ------------------------------
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// 画像以外のメッセージは無視
if (empty($data["events"][0]["message"]) ||
    $data["events"][0]["message"]["type"] !== "image") {
    exit;
}

$replyToken = $data["events"][0]["replyToken"];
$messageId  = $data["events"][0]["message"]["id"];

// ------------------------------
// ② LINE 画像データ取得
// ------------------------------
$ch = curl_init("https://api-data.line.me/v2/bot/message/{$messageId}/content");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$LINE_ACCESS_TOKEN}"]);
$img_bin = curl_exec($ch);
curl_close($ch);

// 取得失敗時
if (!$img_bin) {
    replyText($replyToken, "画像を取得できませんでした。");
    exit;
}

// 画像保存
$upload_dir = __DIR__ . "/uploads";
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

$timestamp = date("Ymd_His");
$img_path = "{$upload_dir}/img_{$timestamp}.jpg";
file_put_contents($img_path, $img_bin);

// ------------------------------
// ③ CalorieMama API へ画像送信
// ------------------------------
$curl = curl_init("https://api-portal.caloriemama.ai/v1/food_recognitions?locale=ja_JP");
$cfile = new CURLFile($img_path, "image/jpeg", "upload.jpg");

curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer {$CALORIE_API_KEY}"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => ["image_file" => $cfile]
]);
$api_response = curl_exec($curl);
curl_close($curl);

if (!$api_response) {
    replyText($replyToken, "カロリー解析に失敗しました。");
    exit;
}

$data = json_decode($api_response, true);

// ------------------------------
// ④ カロリー解析結果を生成
// ------------------------------
if (empty($data["results"])) {
    replyText($replyToken, "食品が検出されませんでした。");
    exit;
}

$reply_text = "解析結果：\n";

foreach ($data["results"] as $food) {

    // 食品名
    $name =
        $food["food_name"] ??
        $food["group"] ??
        ($food["recognition_results"][0]["name"] ?? "不明な食品");

    // カロリー
    $cal =
        $food["calories"] ??
        ($food["nutrition"]["calories"] ?? "不明");

    $reply_text .= "{$name}： 約 {$cal} kcal\n";
}

// ------------------------------
// ⑤ LINE へ返信
// ------------------------------
replyText($replyToken, $reply_text);


// ------------------------------
// 共通：テキスト返信関数
// ------------------------------
function replyText($replyToken, $text) {
    global $LINE_ACCESS_TOKEN;

    $reply = [
        "replyToken" => $replyToken,
        "messages" => [
            ["type" => "text", "text" => $text]
        ]
    ];

    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer {$LINE_ACCESS_TOKEN}"
        ],
        CURLOPT_POSTFIELDS => json_encode($reply),
        CURLOPT_RETURNTRANSFER => true
    ]);
    curl_exec($ch);
    curl_close($ch);
}
?>
