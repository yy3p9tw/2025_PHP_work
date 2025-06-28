<?php
// 這個檔案根據傳入的 $current_page 變數來決定哪個導覽項目要被標示為 "selected"
$current_page = $current_page ?? '';
?>
<nav class="admin-navbar">
    <a href="index.php" class="admin-nav-item <?php echo ($current_page === 'items') ? 'selected' : ''; ?>">商品管理</a>
    <a href="manage_categories.php" class="admin-nav-item <?php echo ($current_page === 'categories') ? 'selected' : ''; ?>">分類管理</a>
    <a href="manage_colors.php" class="admin-nav-item <?php echo ($current_page === 'colors') ? 'selected' : ''; ?>">顏色管理</a>
    <a href="orders.php" class="admin-nav-item <?php echo ($current_page === 'orders') ? 'selected' : ''; ?>">訂單管理</a>
    <a href="customers.php" class="admin-nav-item <?php echo ($current_page === 'customers') ? 'selected' : ''; ?>">顧客管理</a>
</nav>
