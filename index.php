<?php
// DB 接続
$pdo = new PDO(
    "mysql:host=localhost;dbname=health;charset=utf8",
    "root",
    "AdminDef"
);

// 今日の日付（例：20251119）
$day = date("Ymd");

// userId を GET で受け取る（LINE ユーザーごとに一覧を出すため）
$userId = $_GET["userId"] ?? "";

// userId が無い場合のエラー
if ($userId === "") {
    echo "userId が指定されていません。<br>";
    exit;
}

// 画像一覧取得
$stmt = $pdo->prepare("
    SELECT * FROM food_images
    WHERE day = :day AND userId = :userId
    ORDER BY created_at ASC
");
$stmt->execute([
    ":day"    => $day,
    ":userId" => $userId
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>送った画像一覧（<?= htmlspecialchars($day) ?>）</title>
    <style>
        body { font-family: Arial; }
        .item { margin-bottom: 25px; border-bottom: 1px solid #ccc; padding-bottom: 20px; }
        img { width: 250px; border: 1px solid #aaa; }
    </style>
</head>
<body>

<h2>📸 <?= htmlspecialchars($day) ?> に送った画像一覧</h2>

<?php if (empty($rows)) : ?>
    <p>本日の画像はまだ送信されていません。</p>
<?php else : ?>

    <?php foreach ($rows as $img) : ?>

        <div class="item">
            <p><strong>送信時刻：</strong> <?= $img["created_at"] ?></p>

            <!-- 画像表示 -->
            <img src="<?= $img["image_path"] ?>" alt="画像">

            <!-- 画像に対応する JSON 結果（あれば） -->
            <?php
            // 画像ファイル名から messageId を取り出して JSON を探す
            $file = basename($img["image_path"]); // 20251118_HHMMSS_xxxxxx.jpg
            $id   = explode("_", $file)[2] ?? ""; // messageId

            $jsonPath = __DIR__ . "/results/result_{$id}.json";
            ?>

            <?php if (file_exists($jsonPath)) : ?>
                <p><strong>カロリー解析結果：</strong></p>
                <ul>
                <?php
                    $data = json_decode(file_get_contents($jsonPath), true);
                    if (!empty($data["results"])) {
                        foreach ($data["results"] as $r) {
                            echo "<li>{$r['name']}： 約 {$r['calories']} kcal</li>";
                        }
                    } else {
                        echo "<li>データなし</li>";
                    }
                ?>
                </ul>
            <?php else : ?>
                <p>解析データなし</p>
            <?php endif; ?>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>
