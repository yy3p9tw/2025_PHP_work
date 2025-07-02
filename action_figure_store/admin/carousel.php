<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin(); // 只有管理員才能管理輪播

$db = new Database();
$conn = $db->getConnection();

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// 處理輪播新增、編輯、刪除
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_slide'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $slide_order = $_POST['slide_order'] ?? 0;
        $image_url = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image_url = uploadImage($_FILES['image']);
            if (!$image_url) {
                header('Location: carousel.php');
                exit();
            }
        } else {
            $_SESSION['flash_message'] = '請上傳圖片！';
            $_SESSION['flash_type'] = 'danger';
            header('Location: carousel.php');
            exit();
        }

        $stmt = $conn->prepare('INSERT INTO carousel_slides (title, description, image_url, slide_order) VALUES (:title, :description, :image_url, :slide_order)');
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':slide_order', $slide_order);
        $stmt->execute();

        $_SESSION['flash_message'] = '輪播項目已新增！';
        $_SESSION['flash_type'] = 'success';
        header('Location: carousel.php');
        exit();
    }

    if (isset($_POST['edit_slide'])) {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $slide_order = $_POST['slide_order'] ?? 0;
        $current_image_url = $_POST['current_image_url'] ?? null;
        $image_url = $current_image_url;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $new_image_url = uploadImage($_FILES['image']);
            if ($new_image_url) {
                $image_url = $new_image_url;
                // 如果有舊圖片且不是預設圖片，可以考慮刪除舊圖片
                // if ($current_image_url && file_exists(ROOT_PATH . '/uploads/' . $current_image_url) && $current_image_url != 'default.jpg') {
                //     unlink(ROOT_PATH . '/uploads/' . $current_image_url);
                // }
            } else {
                header('Location: carousel.php');
                exit();
            }
        }

        $stmt = $conn->prepare('UPDATE carousel_slides SET title = :title, description = :description, image_url = :image_url, slide_order = :slide_order WHERE id = :id');
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':slide_order', $slide_order);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $_SESSION['flash_message'] = '輪播項目已更新！';
        $_SESSION['flash_type'] = 'success';
        header('Location: carousel.php');
        exit();
    }

    if (isset($_POST['delete_slide'])) {
        $id = $_POST['id'] ?? 0;

        // 獲取圖片路徑以便刪除檔案
        $stmt_img = $conn->prepare('SELECT image_url FROM carousel_slides WHERE id = :id');
        $stmt_img->bindParam(':id', $id);
        $stmt_img->execute();
        $slide_image = $stmt_img->fetchColumn();

        $stmt = $conn->prepare('DELETE FROM carousel_slides WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // 刪除圖片檔案 (如果存在且不是預設圖片)
        if ($slide_image && file_exists(ROOT_PATH . '/uploads/' . $slide_image) && $slide_image != 'default.jpg') {
            unlink(ROOT_PATH . '/uploads/' . $slide_image);
        }

        $_SESSION['flash_message'] = '輪播項目已刪除！';
        $_SESSION['flash_type'] = 'success';
        header('Location: carousel.php');
        exit();
    }
}

// 獲取所有輪播項目
$slides_stmt = $conn->query("SELECT * FROM carousel_slides ORDER BY slide_order ASC, created_at DESC");
$slides = $slides_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>輪播管理 - 公仔銷售網站後台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 側邊導航欄 -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                儀表板
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                產品管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="carousel.php">
                                輪播管理
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                使用者管理
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                登出
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- 主要內容區域 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">輪播管理</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                            新增輪播項目
                        </button>
                    </div>
                </div>

                <?php if ($flash_message): ?>
                    <div class="alert alert-<?php echo $flash_type; ?>" role="alert">
                        <?php echo $flash_message; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>圖片</th>
                                <th>標題</th>
                                <th>描述</th>
                                <th>排序</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($slides): ?>
                                <?php foreach ($slides as $slide): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($slide['id']); ?></td>
                                        <td>
                                            <?php if ($slide['image_url']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($slide['image_url']); ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>" style="width: 100px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                無圖片
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($slide['title']); ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($slide['description'], 0, 50, 'utf-8')) . (mb_strlen($slide['description'], 'utf-8') > 50 ? '...' : ''); ?></td>
                                        <td><?php echo htmlspecialchars($slide['slide_order']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm edit-slide-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editSlideModal"
                                                data-id="<?php echo $slide['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($slide['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($slide['description']); ?>"
                                                data-image="<?php echo htmlspecialchars($slide['image_url']); ?>"
                                                data-order="<?php echo htmlspecialchars($slide['slide_order']); ?>">
                                                編輯
                                            </button>
                                            <form action="carousel.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此輪播項目嗎？');">
                                                <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
                                                <button type="submit" name="delete_slide" class="btn btn-danger btn-sm">刪除</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">目前沒有任何輪播項目。</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- 新增輪播 Modal -->
    <div class="modal fade" id="addSlideModal" tabindex="-1" aria-labelledby="addSlideModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="carousel.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSlideModalLabel">新增輪播項目</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="slideTitle" class="form-label">標題</label>
                            <input type="text" class="form-control" id="slideTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="slideDescription" class="form-label">描述</label>
                            <textarea class="form-control" id="slideDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="slideImage" class="form-label">圖片</label>
                            <input type="file" class="form-control" id="slideImage" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="slideOrder" class="form-label">排序 (數字越小越靠前)</label>
                            <input type="number" class="form-control" id="slideOrder" name="slide_order" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_slide" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯輪播 Modal -->
    <div class="modal fade" id="editSlideModal" tabindex="-1" aria-labelledby="editSlideModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="carousel.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSlideModalLabel">編輯輪播項目</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editSlideId">
                        <input type="hidden" name="current_image_url" id="editSlideCurrentImageUrl">
                        <div class="mb-3">
                            <label for="editSlideTitle" class="form-label">標題</label>
                            <input type="text" class="form-control" id="editSlideTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSlideDescription" class="form-label">描述</label>
                            <textarea class="form-control" id="editSlideDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editSlideImage" class="form-label">圖片 (留空則不修改)</label>
                            <input type="file" class="form-control" id="editSlideImage" name="image" accept="image/*">
                            <small class="text-muted" id="currentSlideImagePreview"></small>
                        </div>
                        <div class="mb-3">
                            <label for="editSlideOrder" class="form-label">排序 (數字越小越靠前)</label>
                            <input type="number" class="form-control" id="editSlideOrder" name="slide_order" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_slide" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editSlideModal = document.getElementById('editSlideModal');
            if (editSlideModal) {
                editSlideModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget; // Button that triggered the modal
                    var id = button.getAttribute('data-id');
                    var title = button.getAttribute('data-title');
                    var description = button.getAttribute('data-description');
                    var image = button.getAttribute('data-image');
                    var order = button.getAttribute('data-order');

                    var slideIdInput = editSlideModal.querySelector('#editSlideId');
                    var slideTitleInput = editSlideModal.querySelector('#editSlideTitle');
                    var slideDescriptionInput = editSlideModal.querySelector('#editSlideDescription');
                    var currentImageUrlInput = editSlideModal.querySelector('#editSlideCurrentImageUrl');
                    var currentImagePreview = editSlideModal.querySelector('#currentSlideImagePreview');
                    var slideOrderInput = editSlideModal.querySelector('#editSlideOrder');

                    slideIdInput.value = id;
                    slideTitleInput.value = title;
                    slideDescriptionInput.value = description;
                    currentImageUrlInput.value = image;
                    slideOrderInput.value = order;

                    if (image) {
                        currentImagePreview.innerHTML = '當前圖片: <img src="../uploads/' + image + '" style="width: 100px; height: 50px; object-fit: cover;"> (' + image + ')';
                    } else {
                        currentImagePreview.innerHTML = '無圖片';
                    }
                });
            }
        });
    </script>
</body>
</html>