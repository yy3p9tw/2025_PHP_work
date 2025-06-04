<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php?msg=您已成功登出");
exit;
?>