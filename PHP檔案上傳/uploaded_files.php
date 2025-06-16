<?php 
// 除錯用：顯示 POST 與 FILES 內容，方便檢查表單資料與檔案資訊
// print_r($_POST) 會輸出表單欄位資料，print_r($_FILES) 會輸出上傳檔案資訊
// 上線時可註解掉這段
echo "<pre>";
print_r($_POST);
print_r($_FILES);
echo "</pre>";

// 取得上傳檔案的原始檔名
$name = $_FILES['name']['name'];
// 取得表單送出的檔案類型（分類）
$type = $_POST['type'];
// 取得表單送出的檔案描述
$description = $_POST['description'];

// 將上傳的暫存檔案移動到指定目錄（./files/）下，檔名為原始檔名
move_uploaded_file($_FILES['name']['tmp_name'], './files/'.$name);

// 連接 MySQL 資料庫，設定資料來源名稱、帳號、密碼
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, 'root', '');

// 組成 SQL 語法，將檔案資訊寫入 uploads 資料表
$sql = "insert into uploads(`name`,`type`,`description`) values ('$name','$type','$description')";
// 執行 SQL 寫入
$pdo->exec($sql); 

// 上傳成功後導回管理頁，並帶上成功訊息與檔名
header("location:manage.php?msg=檔案上傳成功，檔名為：".$name);
?>

