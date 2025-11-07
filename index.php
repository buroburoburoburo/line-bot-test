<?php
$result_file = __DIR__ . "/result_latest.json";

if (file_exists($result_file)) {
    $data = json_decode(file_get_contents($result_file), true);
    if (!empty($data['results'])) {
        echo "<h2>カロリー解析結果</h2>";
        foreach ($data['results'] as $food) {
            echo "<p>🍽️ {$food['name']} - 約 {$food['calories']} kcal</p>";
        }
    } else {
        echo "<p>食品が検出されませんでした。</p>";
    }
} else {
    echo "<p>まだ解析結果がありません。</p>";
}
