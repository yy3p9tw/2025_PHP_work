<?php

/**
 * 獲取 CSRF Token。如果 Session 中沒有，則生成一個。
 * @return string CSRF Token
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 驗證提交的 CSRF Token。
 * @param string $token 提交的 Token
 * @return bool 驗證結果
 */
function validate_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

?>