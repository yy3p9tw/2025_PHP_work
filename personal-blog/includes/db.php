<?php
// 資料庫連線與常用函式
function db() {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=localhost;dbname=blog;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}
function get_all_posts() {
    $sql = "SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC";
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function get_all_categories() {
    return db()->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}

// 取得單一分類
function get_category($id) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 新增分類
function create_category($name) {
    $stmt = db()->prepare("INSERT INTO categories (name) VALUES (?)");
    return $stmt->execute([$name]);
}

// 更新分類
function update_category($id, $name) {
    $stmt = db()->prepare("UPDATE categories SET name=? WHERE id=?");
    return $stmt->execute([$name, $id]);
}

// 刪除分類
function delete_category($id) {
    $stmt = db()->prepare("DELETE FROM categories WHERE id=?");
    return $stmt->execute([$id]);
}

// 取得所有標籤
function get_all_tags() {
    return db()->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
}

// 取得單一標籤
function get_tag($id) {
    $stmt = db()->prepare("SELECT * FROM tags WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 依名稱取得標籤
function get_tag_by_name($name) {
    $stmt = db()->prepare("SELECT * FROM tags WHERE name=?");
    $stmt->execute([$name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 新增標籤
function create_tag($name) {
    $stmt = db()->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
    $stmt->execute([$name]);
    return db()->lastInsertId();
}

// 設定文章標籤（先刪除再新增）
function set_post_tags($post_id, $tag_names) {
    // $tag_names: array of tag name (string)
    db()->prepare("DELETE FROM posts_tags WHERE post_id=?")->execute([$post_id]);
    foreach ($tag_names as $name) {
        $name = trim($name);
        if ($name === '') continue;
        $tag = get_tag_by_name($name);
        if (!$tag) {
            create_tag($name);
            $tag = get_tag_by_name($name);
        }
        db()->prepare("INSERT IGNORE INTO posts_tags (post_id, tag_id) VALUES (?, ?)")->execute([$post_id, $tag['id']]);
    }
}

// 取得文章所有標籤
function get_post_tags($post_id) {
    $sql = "SELECT t.* FROM tags t JOIN posts_tags pt ON t.id=pt.tag_id WHERE pt.post_id=? ORDER BY t.name ASC";
    $stmt = db()->prepare($sql);
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function login($user, $pw) {
    $stmt = db()->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row && password_verify($pw, $row['password'])) return $row;
    return false;
}
// 新增文章
function create_post($title, $content, $category_id, $cover_img, $user_id, $summary = '', $is_featured = 0) {
    $sql = "INSERT INTO posts (title, content, category_id, cover_img, user_id, summary, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = db()->prepare($sql);
    return $stmt->execute([$title, $content, $category_id, $cover_img, $user_id, $summary, $is_featured]);
}

// 取得單篇文章
function get_post($id) {
    $sql = "SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id=c.id WHERE p.id=?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 更新文章
function update_post($id, $title, $content, $category_id, $cover_img = null, $summary = '', $is_featured = 0) {
    if ($cover_img) {
        $sql = "UPDATE posts SET title=?, content=?, category_id=?, cover_img=?, summary=?, is_featured=? WHERE id=?";
        $params = [$title, $content, $category_id, $cover_img, $summary, $is_featured, $id];
    } else {
        $sql = "UPDATE posts SET title=?, content=?, category_id=?, summary=?, is_featured=? WHERE id=?";
        $params = [$title, $content, $category_id, $summary, $is_featured, $id];
    }
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

// 刪除文章
function delete_post($id) {
    $sql = "DELETE FROM posts WHERE id=?";
    $stmt = db()->prepare($sql);
    return $stmt->execute([$id]);
}

// 操作日誌
function log_action($user_id, $action, $detail = null) {
    $stmt = db()->prepare("INSERT INTO activity_log (user_id, action, detail) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $detail]);
}

function get_user_by_id($id) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// 預留：取得留言數
function get_comment_count($post_id) {
    // return (int)db()->query("SELECT COUNT(*) FROM comments WHERE post_id=".intval($post_id))->fetchColumn();
    return 0;
}
