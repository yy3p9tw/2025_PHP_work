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
                <a class="nav-link" href="carousel.php">
                    輪播管理
                </a>
            </li>
            <?php if (function_exists('isAdmin') && isAdmin()): ?>
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
