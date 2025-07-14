<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>現代化萬年曆 | 作品集</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- 導航列 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-calendar3 me-2"></i>現代化萬年曆
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.html">
                    <i class="bi bi-house-door me-1"></i>回作品集
                </a>
            </div>
        </div>
    </nav>

    <!-- 主要內容 -->
    <main class="main-content">
        <div class="container">
            <!-- 工具列 -->
            <div class="calendar-toolbar">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="calendar-navigation">
                            <button class="btn btn-outline-primary" id="prevMonth">
                                <i class="bi bi-chevron-left"></i> 上一月
                            </button>
                            <button class="btn btn-primary mx-2" id="today">今天</button>
                            <button class="btn btn-outline-primary" id="nextMonth">
                                下一月 <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="calendar-controls">
                            <div class="input-group">
                                <input type="date" class="form-control" id="dateSearch" placeholder="搜尋日期">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 月份年份標題 -->
            <div class="calendar-header text-center mb-4">
                <h1 class="month-year-title" id="monthYearTitle"></h1>
                <p class="text-muted">點擊日期查看詳細資訊</p>
            </div>

            <!-- 萬年曆主體 -->
            <div class="calendar-container">
                <div class="calendar-grid" id="calendarGrid">
                    <!-- 星期標題 -->
                    <div class="weekday-header">日</div>
                    <div class="weekday-header">一</div>
                    <div class="weekday-header">二</div>
                    <div class="weekday-header">三</div>
                    <div class="weekday-header">四</div>
                    <div class="weekday-header">五</div>
                    <div class="weekday-header">六</div>
                    <!-- 日期格子會由 JavaScript 動態生成 -->
                </div>
            </div>

            <!-- 日期詳情模態框 -->
            <div class="modal fade" id="dayDetailModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">日期詳情</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="modalBody">
                            <!-- 動態內容 -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 圖例說明 -->
            <div class="calendar-legend mt-4">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="legend-item">
                            <span class="legend-color today"></span>
                            <span>今天</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="legend-item">
                            <span class="legend-color holiday"></span>
                            <span>節日</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="legend-item">
                            <span class="legend-color event"></span>
                            <span>活動</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="legend-item">
                            <span class="legend-color selected"></span>
                            <span>選中</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="calendar.js"></script>

    <?php
    // 引入 PHP 數據
    include 'calendar-data.php';
    ?>

    <script>
        // 將 PHP 數據傳遞給 JavaScript
        window.calendarData = {
            holidays: <?= json_encode($holidays) ?>,
            events: <?= json_encode($events) ?>,
            currentDate: '<?= date('Y-m-d') ?>',
            currentYear: <?= date('Y') ?>,
            currentMonth: <?= date('m') ?>
        };
    </script>
</body>
</html>
