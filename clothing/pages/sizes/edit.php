<?php
require_once '../../includes/db.php';
$Size = new DB('sizes');

$id = $_GET['id'] ?? null;
$size = null;

if ($id) {
    $size = $Size->all("id = $id")[0] ?? null;
    if (!$size) {
        header('Location: list.php');
        exit();
    }
} else {
    header('Location: list.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $Size->update($id, ['name' => $name]);
        header('Location: list.php');
        exit();
    } else {
        $error = "尺寸名稱不能為空。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯尺寸</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title mb-4">編輯尺寸</h1>
        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">尺寸名稱:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($size['name']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">儲存</button>
                <a href="list.php" class="btn btn-outline-secondary">取消</a>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>