<?php
// DB接続
$pdo = new PDO(
    "mysql:host=host.docker.internal;dbname=health;charset=utf8",
    "root",
    "AdminDef"
);

// データ取得（最新順）
$stmt = $pdo->query("SELECT * FROM food_images ORDER BY created_at DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>送信画像一覧</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f9f9f9; }
        h1 { text-align: center; margin-bottom: 30px; }
        .record { 
            border: 1px solid #ccc; 
            padding: 10px; 
            margin-bottom: 15px; 
            display: flex; 
            gap: 15px; 
            align-items: center; 
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .record img { max-width: 120px; max-height: 120px; object-fit: cover; border: 1px solid #999; border-radius: 4px; }
        .info { display: flex; flex-direction: column; }
        .info div { margin-bottom: 5px; }
        .no-record { text-align: center; color: #555; font-size: 18px; }
    </style>
</head>
<body>
    <h1>送信画像一覧</h1>

    <?php if (empty($records)): ?>
        <p class="no-record">まだ画像は送信されていません。</p>
    <?php else: ?>
        <?php foreach ($records as $rec): ?>
            <div class="record">
                <img src="<?= htmlspecialchars($rec['image_path']) ?>" alt="送信画像">
                <div class="info">
                    <div><strong>ユーザーID:</strong> <?= htmlspecialchars($rec['userId']) ?></div>
                    <div><strong>日付:</strong> <?= htmlspecialchars($rec['day']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
