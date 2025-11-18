<?php
$dsn = "mysql:host=localhost;dbname=health;charset=utf8";
$user = "root";
$pass = "AdminDef";

try {
    $pdo = new PDO($dsn, $user, $pass);

    $stmt = $pdo->query("SELECT * FROM foodlog ORDER BY id DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>解析一覧</title>
<style>
table { width: 100%; border-collapse: collapse; }
td, th { padding: 10px; border: 1px solid #ccc; }
img { width: 150px; border-radius: 8px; }
</style>
</head>
<body>

<h2>LINE から送った画像一覧</h2>

<table>
<tr>
    <th>画像</th>
    <th>結果</th>
    <th>合計 kcal</th>
    <th>日時</th>
</tr>

<?php foreach ($rows as $r): ?>
<tr>
    <td><img src="<?= $r['imagePath'] ?>"></td>
    <td>
        <?php
            $items = json_decode($r["calories"], true);
            foreach ($items as $i) {
                echo "{$i['name']}：{$i['calories']} kcal<br>";
            }
        ?>
    </td>
    <td><?= $r["total"] ?> kcal</td>
    <td><?= $r["created"] ?></td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
