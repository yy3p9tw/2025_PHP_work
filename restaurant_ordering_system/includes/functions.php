<?php

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

function getOrderStatusText($status) {
    switch ($status) {
        case 'pending':
            return '待處理';
        case 'preparing':
            return '準備中';
        case 'completed':
            return '已完成';
        case 'cancelled':
            return '已取消';
        default:
            return $status;
    }
}

function getPaymentStatusText($status) {
    switch ($status) {
        case 'unpaid':
            return '未支付';
        case 'paid':
            return '已支付';
        default:
            return $status;
    }
}

function getAvailabilityText($is_available) {
    return $is_available ? '<span class="badge bg-success">上架</span>' : '<span class="badge bg-danger">下架</span>';
}

// 其他通用函數可以在這裡添加

?>