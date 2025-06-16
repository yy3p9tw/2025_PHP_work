<?php 
// 取得表單送出的檔案類型（分類）
$type = $_POST['type'];
// 取得表單送出的檔案描述
$description = $_POST['description'];
// 判斷有無多檔案上傳
$names = $_FILES['name']['name'];
$tmp_names = $_FILES['name']['tmp_name'];
// 連接 MySQL 資料庫
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, 'root', '');
// 處理多檔案上傳
$success = [];
for ($i = 0; $i < count($names); $i++) {
    if ($names[$i] == '') continue;
    $name = $names[$i];
    $tmp = $tmp_names[$i];
    if (move_uploaded_file($tmp, './files/'.$name)) {
        $sql = "insert into uploads(`name`,`type`,`description`) values ('$name','$type','$description')";
        $pdo->exec($sql);
        $success[] = $name;
    }
}
// 上傳成功後導回 upload.php 並帶上成功檔名與描述
if ($success) {
    header('Location: upload.php?success=' . urlencode(json_encode($success)) . '&desc=' . urlencode($description));
    exit;
}
?>

