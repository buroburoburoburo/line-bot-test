<?php
// 最新の解析結果ファイル
$result_file = __DIR__ . "/result_latest.json";

// HTMLヘッダー
echo '<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>カロリー解析結果</title>
<style>
body {
    font-family: "Meiryo", sans-serif;
    margin: 20px;
    background: #f5f5f5;
}
.container {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    max-width: 700px;
    margin: auto;
    box-shadow: 0px 0px 10px #ccc;
}
h2 {
    color: #444;
}
.food-box {
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}
.food-box:last-child {
    border-bottom: none;
}
.name {
    font-size: 20px;
    font-weight: bold;
}
.cal {
    color: #c44;
}
.conf {
    color: #555;
}
</style>
</head>
<body>
<div class="container">
<h2>最新のカロリー解析結果</h2>
';

// ファイル存在チェック
if (!file_exists($result_file)) {
    echo "<p>まだ解析された画像がありません。</p></div></body></html>";
    exit;
}

// JSONの読み込み
$json = file_get_contents($result_file);
$data = json_decode($json, true);

// 結果がない場合
if (empty($data["results"])) {
    echo "<p>食品が検出されませんでした。</p></div></body></html>";
    exit;
}

// 結果表示
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

    // 信頼度(%)
    $conf = isset($food["confidence"])
        ? round($food["confidence"] * 100, 1) . "%"
        : "不明";

    echo '<div class="food-box">';
    echo "<div class='name'>{$name}</div>";
    echo "<div class='cal'>推定カロリー：{$cal} kcal</div>";
    echo "<div class='conf'>信頼度：{$conf}</div>";
    echo '</div>';
}

echo "</div></body></html>";
?>
