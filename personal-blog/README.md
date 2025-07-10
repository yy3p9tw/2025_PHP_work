# 個人部落格系統 專案規格書

## 一、專案簡介
本系統為現代化個人部落格，具備前台文章瀏覽、分頁、分類、標籤、搜尋，以及完整的管理端（文章、分類、標籤、帳號、日誌、備份還原等），強調美觀、響應式、資安與可維護性。

## 二、系統架構
- 前端：HTML + Bootstrap 5 + JS（RWD、modal、互動元件）
- 後端：PHP（PDO，安全、可維護）
- 資料庫：MySQL
- 目錄結構：
  - `/` 前台首頁（index.php）、單篇文章（post.php）
  - `/admin/` 管理端（dashboard、login、post_create、post_edit、user_manage、activity_log...）
  - `/includes/` 共用 PHP 函式（db.php）
  - `/assets/` 圖片、插畫、CSS、JS
  - `/uploads/` 上傳圖片

## 三、功能說明
### 1. 前台功能
- 文章列表、分頁、分類篩選、標籤雲、標籤篩選、標題搜尋
- 單篇文章頁（顯示分類、標籤、瀏覽次數、可點擊標籤）
- 響應式設計，現代美觀

### 2. 管理端功能
- 登入/登出、帳號管理、密碼變更
- 文章新增、編輯、刪除（支援分類、標籤多選/新增、圖片上傳、摘要、精選）
- 分類管理、標籤管理、標籤合併
- 操作日誌（所有重要行為自動記錄，支援 modal 詳細檢視）
- 資料庫一鍵備份下載、還原（僅 admin 可用）
- 重要操作皆自動寫入 activity_log
- 支援 Bootstrap modal 彈窗互動
- 可擴充留言、API、權限分級等進階功能

## 四、資料表設計
- posts（id, title, content, category_id, cover_img, user_id, view_count, is_featured, summary, created_at, updated_at）
- categories（id, name）
- tags（id, name）
- posts_tags（post_id, tag_id）
- users（id, username, password）
- activity_log（id, user_id, action, detail, created_at）

## 五、UI/UX 特色
- Bootstrap 5 樣式，支援深色模式、分頁、卡片 hover 動畫
- 管理端多處採用 modal 彈窗（如日誌詳細內容）
- 表單、按鈕、提示皆現代化
- 響應式設計，手機/桌機皆美觀

## 六、資安與維護性
- 所有 SQL 皆用 PDO 預處理，防 SQL injection
- 密碼雜湊（password_hash）
- 管理端操作皆有 session 驗證
- 重要操作自動寫入日誌，方便追蹤
- 結構清晰，易於擴充

## 七、安裝與使用
1. 匯入 `db.sql` 建立資料表
2. 設定 `/includes/db.php` 連線資訊
3. 預設管理者帳號：admin / 密碼：123（請登入後盡快修改）
4. 前台首頁：`index.php`，管理端入口：`/admin/login.php`

## 八、後續擴充建議
- 新增留言功能、API、權限分級、SEO 優化、定時自動備份等
- 進階操作日誌查詢、批次管理、前台互動功能

## 九、建表語法
見 `db.sql`

## 十、測試方式
- 功能測試：依據功能說明逐項操作（文章 CRUD、分類/標籤管理、帳號管理、日誌、備份還原等）
- 權限測試：未登入、一般管理者、admin 權限分級測試
- 響應式測試：桌機、平板、手機瀏覽效果
- 資安測試：SQL injection、XSS、session 控管
- 資料備份/還原測試：備份下載、SQL 匯入、還原功能

## 十一、維護建議
- 定期備份資料庫與上傳檔案
- 管理者密碼請定期更換，勿使用弱密碼
- 若有新功能需求，建議先設計資料表再擴充程式
- 建議升級 PHP/MySQL 至最新版，保持資安
- 可依需求擴充留言、API、權限分級、SEO、前台互動等
