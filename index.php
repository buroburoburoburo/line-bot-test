<?php
// -------------------------------------------------
// DB 接続
// -------------------------------------------------
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=health;charset=utf8",
        "root",
        "AdminDef",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    exit("DB Error: " . $e->getMessage());
}

// -------------------------------------------------
// 画像一覧を取得（最新順）
// -------------------------------------------------
$sql = "SELECT * FROM food_images ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>送信した画像一覧</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f3f3f3;
        padding: 20px;
    }
    h1 {
        margin-bottom: 20px;
    }
    .card {
        background: white;
        width: 320px;
        margin: 15px;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        display: inline-block;
        vertical-align: top;
    }
    .card img {
        width: 100%;
        border-radius: 10px;
        cursor: pointer;
    }
    .info {
        margin-top: 10px;
        font-size: 14px;
        color: #444;
    }
</style>
</head>
<body>

<h1>送信した画像一覧</h1>

<?php if (empty($rows)): ?>
    <p>まだ画像がありません。</p>
<?php endif; ?>

<?php foreach ($rows as $row): ?>
    <div class="card">
        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="food">

        <div class="info">
            <strong>DAY:</strong> <?= htmlspecialchars($row['day']) ?><br>
            <strong>UserID:</strong> <?= htmlspecialchars($row['userId']) ?><br>
            <strong>日時:</strong> <?= htmlspecialchars($row['created_at']) ?>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
