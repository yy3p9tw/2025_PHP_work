<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./include.css">
    <title>學生管理系統-關於</title>
</head>
<body>
   <?php require "header.php"?>
   <?php
    // $page = isset($_GET['page']) ? $_GET['page'] : 'main';
    $page = $_GET['page'] ?? 'main';
    $file=$page.".php";
    if (file_exists($file)) {
    include $file;
} else {
    include "main.php";
}
// switch($page){
//             case 'list':
//                 include "list.php";
//                 break;
//             case 'new':
//                 include "new.php";
//                 break;
//             case 'query':
//                 include "query.php";
//                 break;
//             case 'about':
//                 include "about.php";
//                 break;
//             default:
//                 include "main.php";
//         }

    ?>
<?php include "footer.php"; ?>
</body>
</html>
