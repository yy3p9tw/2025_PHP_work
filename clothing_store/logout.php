<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/csrf_functions.php';

session_unset();
session_destroy();
header('Location: index.php');
exit;
?>