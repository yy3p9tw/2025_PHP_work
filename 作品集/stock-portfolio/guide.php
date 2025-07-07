<?php
$page_title = '系統使用指南';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-book me-2"></i>系統使用指南
            </h1>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                歡迎使用股票投資組合管理系統！本指南將幫助您快速上手。
            </div>
            
            <!-- 快速開始 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>快速開始</h5>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">註冊帳戶</div>
                                點擊「註冊」按鈕建立您的個人帳戶
                            </div>
                            <a href="register.php" class="btn btn-sm btn-primary">前往註冊</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">瀏覽股票</div>
                                查看股票列表，了解市場動態
                            </div>
                            <a href="stocks.php" class="btn btn-sm btn-success">瀏覽股票</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">建立投資組合</div>
                                開始記錄您的投資情況
                            </div>
                            <a href="portfolio.php" class="btn btn-sm btn-info">管理投資組合</a>
                        </li>
                    </ol>
                </div>
            </div>
            
            <!-- 主要功能 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>投資組合管理</h5>
                        </div>
                        <div class="card-body">
                            <h6>功能特色：</h6>
                            <ul>
                                <li>追蹤持股數量與成本</li>
                                <li>即時計算投資損益</li>
                                <li>查看投資組合總覽</li>
                                <li>分析投資績效</li>
                            </ul>
                            <h6>使用方式：</h6>
                            <p>登入後前往「投資組合」頁面，您可以查看所有持股狀況，包括現價、損益、報酬率等資訊。</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>關注清單</h5>
                        </div>
                        <div class="card-body">
                            <h6>功能特色：</h6>
                            <ul>
                                <li>收藏感興趣的股票</li>
                                <li>快速查看關注股票動態</li>
                                <li>一鍵加入或移除關注</li>
                                <li>股價變動通知</li>
                            </ul>
                            <h6>使用方式：</h6>
                            <p>在股票列表或詳情頁面點擊「關注」按鈕，即可將股票加入關注清單。</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>股票資訊</h5>
                        </div>
                        <div class="card-body">
                            <h6>提供資訊：</h6>
                            <ul>
                                <li>即時股價與漲跌</li>
                                <li>成交量資訊</li>
                                <li>產業分類</li>
                                <li>歷史價格走勢</li>
                            </ul>
                            <h6>搜索功能：</h6>
                            <p>使用搜索框輸入股票代碼或名稱，快速找到您要的股票。也可以按產業分類篩選。</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>財經新聞</h5>
                        </div>
                        <div class="card-body">
                            <h6>新聞內容：</h6>
                            <ul>
                                <li>最新市場動態</li>
                                <li>重要公司消息</li>
                                <li>經濟分析報告</li>
                                <li>投資策略建議</li>
                            </ul>
                            <h6>閱讀方式：</h6>
                            <p>點擊新聞標題可查看完整內容，也可以點擊「原文連結」查看新聞來源。</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 測試帳戶 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>測試帳戶</h5>
                </div>
                <div class="card-body">
                    <p>為了方便測試，系統提供以下預設帳戶：</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-danger">管理員帳戶</h6>
                                    <p class="card-text">
                                        <strong>帳號：</strong>admin<br>
                                        <strong>密碼：</strong>admin123
                                    </p>
                                    <p class="small text-muted">擁有完整的系統管理權限</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">一般用戶</h6>
                                    <p class="card-text">
                                        <strong>帳號：</strong>demo<br>
                                        <strong>密碼：</strong>demo123
                                    </p>
                                    <p class="small text-muted">一般使用者權限</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 技術說明 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>技術說明</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>前端技術</h6>
                            <ul>
                                <li>HTML5 / CSS3</li>
                                <li>Bootstrap 5 響應式框架</li>
                                <li>JavaScript / jQuery</li>
                                <li>Font Awesome 圖標</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>後端技術</h6>
                            <ul>
                                <li>PHP 8.0+</li>
                                <li>MySQL 資料庫</li>
                                <li>PDO 資料庫連接</li>
                                <li>Session 狀態管理</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h6 class="mt-3">安全特性</h6>
                    <ul>
                        <li>密碼加密存儲</li>
                        <li>CSRF 攻擊防護</li>
                        <li>SQL 注入防護</li>
                        <li>XSS 跨站腳本防護</li>
                    </ul>
                </div>
            </div>
            
            <!-- 常見問題 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>常見問題</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    如何重設密碼？
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    請聯繫管理員協助重設密碼，或使用測試帳戶登入體驗系統功能。
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    股票價格是即時的嗎？
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    目前系統使用模擬數據，實際部署時可以整合真實的股票 API 服務。
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    如何查看系統狀態？
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    您可以訪問 <a href="test.php">系統測試頁面</a> 來檢查系統各項功能是否正常運作。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 聯絡資訊 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>需要協助？</h5>
                </div>
                <div class="card-body">
                    <p>如果您在使用過程中遇到問題，請嘗試以下方法：</p>
                    <ol>
                        <li>查看 <a href="test.php">系統測試頁面</a> 檢查系統狀態</li>
                        <li>使用測試帳戶重新登入</li>
                        <li>清除瀏覽器快取後重新載入頁面</li>
                        <li>查閱 <a href="README.md" target="_blank">技術文件</a></li>
                    </ol>
                    
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-primary me-2">返回首頁</a>
                        <a href="test.php" class="btn btn-outline-info">系統測試</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
