<?php
// -------------------------------------------------
// 最新の解析結果を表示するページ
// -------------------------------------------------

$resultFile = __DIR__ . "/results/result_latest.json";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カロリー解析結果</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            color: #444;
        }
        .food {
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }
    </style>
</head>
<body>

<h1>カロリー解析結果</h1>

<?php
// -------------------------------------------------
// JSONが存在するか確認
// -------------------------------------------------
if (!file_exists($resultFile)) {
    echo "<p>まだ解析結果がありません。</p>";
    exit;
}

$json = json_decode(file_get_contents($resultFile), true);

if (empty($json["results"])) {
    echo "<p>食品が検出されませんでした。</p>";
    exit;
}

// -------------------------------------------------
// 食品の一覧表示
// -------------------------------------------------
echo "<h2>検出された食品</h2>";

foreach ($json["results"] as $item) {
    $name = htmlspecialchars($item["name"]);
    $cal  = htmlspecialchars($item["calories"]);
    echo "<div class='food'>{$name} — 約 {$cal} kcal</div>";
}
?>

</body>
</html>
