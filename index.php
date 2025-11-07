<?php
$result_path = __DIR__ . '/result.json';
if (!file_exists($result_path)) {
    echo "<h2>まだ解析結果がありません。</h2>";
    exit;
}

$json = json_decode(file_get_contents($result_path), true);
$foods = $json['results'][0]['items'] ?? [];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>カロリー解析結果</title>
</head>
<body>
<h1>カロリー解析結果</h1>
<?php if ($foods): ?>
<ul>
<?php foreach ($foods as $food): ?>
    <li>
        <b><?= htmlspecialchars($food['name_jp'] ?? $food['name_en']) ?></b>：
        <?= htmlspecialchars(round($food['calories'], 1)) ?> kcal
    </li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p>食品が検出されませんでした。</p>
<?php endif; ?>
</body>
</html>
