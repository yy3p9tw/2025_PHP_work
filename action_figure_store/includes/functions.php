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

?>