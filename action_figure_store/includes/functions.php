<?php

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// 處理圖片上傳
function uploadImage($file, $target_dir = ROOT_PATH . '/uploads/') {
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // 檢查是否為真實圖片
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $_SESSION['flash_message'] = "檔案不是圖片。";
        $_SESSION['flash_type'] = "danger";
        $uploadOk = 0;
    }

    // 檢查檔案是否已存在
    if (file_exists($target_file)) {
        $_SESSION['flash_message'] = "抱歉，檔案已存在。";
        $_SESSION['flash_type'] = "danger";
        $uploadOk = 0;
    }

    // 檢查檔案大小 (限制 5MB)
    if ($file["size"] > 5000000) {
        $_SESSION['flash_message'] = "抱歉，您的檔案太大。";
        $_SESSION['flash_type'] = "danger";
        $uploadOk = 0;
    }

    // 允許特定的檔案格式
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $_SESSION['flash_message'] = "抱歉，只允許 JPG, JPEG, PNG & GIF 檔案。";
        $_SESSION['flash_type'] = "danger";
        $uploadOk = 0;
    }

    // 檢查 $uploadOk 是否為 0
    if ($uploadOk == 0) {
        return false;
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return basename($file["name"]); // 返回檔案名稱
        } else {
            $_SESSION['flash_message'] = "抱歉，上傳您的檔案時發生錯誤。";
            $_SESSION['flash_type'] = "danger";
            return false;
        }
    }
}

// 統一 API 回應格式
function jsonResponse($success, $data = null, $error = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error
    ]);
    exit;
}

// 格式化商品圖片URL
function formatProductImage($image_url) {
    if ($image_url) {
        return 'uploads/' . $image_url;
    } else {
        return 'assets/images/no-image.png';
    }
}

// 格式化單一商品資料
function formatProductData($product) {
    if (!$product) {
        return null;
    }

    $product['id'] = (int)$product['id'];
    $product['price'] = (float)$product['price'];
    $product['image_url'] = formatProductImage($product['image_url']);
    $product['created_at'] = date('Y-m-d H:i:s', strtotime($product['created_at']));

    // 處理分類
    $product['categories'] = [];
    if (!empty($product['category_names'])) {
        $names = explode(', ', $product['category_names']);
        $ids = explode(',', $product['category_ids']);
        for ($i = 0; $i < count($names); $i++) {
            if (isset($ids[$i]) && $ids[$i]) {
                $product['categories'][] = [
                    'id' => (int)$ids[$i],
                    'name' => trim($names[$i])
                ];
            }
        }
    }
    unset($product['category_names'], $product['category_ids']);

    return $product;
}
?>