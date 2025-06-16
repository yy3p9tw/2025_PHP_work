<?php
// 多選下載
if (isset($_POST['download']) && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    $dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
    $pdo = new PDO($dsn, "root", "");
    $in = str_repeat('?,', count($ids)-1) . '?';
    $stmt = $pdo->prepare("SELECT name FROM uploads WHERE id IN ($in)");
    $stmt->execute($ids);
    $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $zip = new ZipArchive();
    $zipname = 'download_'.date('YmdHis').'.zip';
    $zip->open($zipname, ZipArchive::CREATE);
    foreach ($files as $file) {
        $path = __DIR__ . '/files/' . $file;
        if (file_exists($path)) {
            $zip->addFile($path, $file);
        }
    }
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$zipname.'"');
    readfile($zipname);
    unlink($zipname);
    exit;
}
// 多選刪除
if (isset($_POST['delete']) && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    $dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
    $pdo = new PDO($dsn, "root", "");
    $in = str_repeat('?,', count($ids)-1) . '?';
    $stmt = $pdo->prepare("SELECT name FROM uploads WHERE id IN ($in)");
    $stmt->execute($ids);
    $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($files as $file) {
        $path = __DIR__ . '/files/' . $file;
        if (file_exists($path)) {
            unlink($path);
        }
    }
    $pdo->prepare("DELETE FROM uploads WHERE id IN ($in)")->execute($ids);
    header('Location: manage.php?msg=批次刪除完成');
    exit;
}
?>
