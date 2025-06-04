<?php
session_start(); // 啟動 session，準備儲存登入狀態

try {
    // 連接資料庫
require_once '../includes/db.php'; // 路徑依實際位置調整
$pdo = getPDO();

    // 取得帳號與密碼
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 查詢帳號
    $stmt = $pdo->prepare("SELECT * FROM members WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 驗證密碼
    if ($user && password_verify($password, $user['password'])) {
        // 登入成功，寫入 session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // 更新最後登入時間
        $pdo->prepare("UPDATE members SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
        // 導向會員中心
        header("Location: ../member/member_center.php");
        exit;
    } else {
        // 登入失敗，回登入頁並顯示錯誤訊息
        header("Location: login.php?err=帳號或密碼錯誤");
        exit;
    }
} catch (Exception $e) {
    // 系統錯誤，回登入頁並顯示錯誤訊息
    header("Location: login.php?err=系統錯誤，請稍後再試");
    exit;
}
?>

<!-- 若有錯誤訊息，顯示於畫面上 -->
<?php if(isset($_GET['err'])): ?>
    <div class="msg"><?=htmlspecialchars($_GET['err'])?></div>
<?php endif; ?>