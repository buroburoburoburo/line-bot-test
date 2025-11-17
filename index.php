<?php
$result_file = __DIR__ . "/results/result_latest.json";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>カロリー解析結果</title>
</head>
<body>
<h1>カロリー解析 結果</h1>

<?php
if (file_exists($result_file)) {
    $json = json_decode(file_get_contents($result_file), true);

    if (!empty($json["results"])) {
        echo "<h2>解析結果</h2>";
        foreach ($json["results"] as $item) {
            echo "<p>{$item['name']}： 約 {$item['calories']} kcal</p>";
        }
    } else {
        echo "<p>食品が検出されませんでした。</p>";
    }
} else {
    echo "<p>まだ解析結果がありません。</p>";
}
?>

<p><hr></p>
<p>このページは <b>callback.php</b> で画像解析された最新結果を表示します。</p>

</body>
</html>
