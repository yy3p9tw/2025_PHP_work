<?php
/**
 * 1.建立表單
 * 2.建立處理檔案程式
 * 3.搬移檔案
 * 4.顯示檔案列表
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>檔案上傳</title>
    <style>
        body {
            font-family: 'Noto Sans TC', Arial, sans-serif;
            background: #f7f8fa;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            font-size: 2em;
            color: #3b82f6;
            margin: 32px 0 24px 0;
            letter-spacing: 2px;
        }
        .upload-form {
            background: #fff;
            max-width: 420px;
            margin: 40px auto;
            padding: 32px 28px 24px 28px;
            border-radius: 16px;
            box-shadow: 0 4px 24px #bfa04633;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .upload-form label {
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 6px;
        }
        .upload-form input[type="file"],
        .upload-form select,
        .upload-form textarea {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #b6c7e6;
            font-size: 1em;
            font-family: inherit;
            margin-bottom: 8px;
        }
        .upload-form textarea {
            min-height: 60px;
            resize: vertical;
        }
        .upload-form button {
            background: linear-gradient(90deg, #3b82f6 60%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 12px 0;
            font-weight: bold;
            font-size: 1.08em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .upload-form button:hover {
            background: linear-gradient(90deg, #60a5fa 60%, #3b82f6 100%);
        }
        .upload-form .desc-label {
            margin-top: 8px;
        }
        .manage-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #3b82f6;
            text-decoration: underline;
            font-weight: bold;
            font-size: 1.1em;
        }
        .manage-link:hover {
            color: #1565c0;
        }
    </style>
</head>
<body>
 <h1 class="header">檔案上傳練習</h1>
 <form class="upload-form" action="uploaded_files.php" method="post" enctype="multipart/form-data">
    <label for="name">選擇檔案上傳：</label>
    <input type="file" name="name" id="name" required>

    <label for="type">檔案類型：</label>
    <select name="type" id="type">
        <option value="image">影像</option>
        <option value="document">文件</option>
        <option value="video">影片</option>
        <option value="music">音樂</option>
    </select>

    <label for="description" class="desc-label">檔案描述：</label>
    <textarea name="description" id="description" placeholder="請輸入檔案描述..."></textarea>

    <button type="submit">上傳檔案</button>
</form>
<a href="manage.php" class="manage-link">查看所有上傳檔案</a>


</body>
</html>