<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>儀表板
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'products') ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box-seam me-2"></i>產品管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'categories') ? 'active' : ''; ?>" href="categories.php">
                    <i class="bi bi-tags me-2"></i>分類管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'carousel') ? 'active' : ''; ?>" href="carousel.php">
                    <i class="bi bi-images me-2"></i>輪播管理
                </a>
            </li>
            <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'users') ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people me-2"></i>使用者管理
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <hr>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="../index.html" target="_blank">
                    回到前台
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    登出
                </a>
            </li>
        </ul>
    </div>
</nav>
