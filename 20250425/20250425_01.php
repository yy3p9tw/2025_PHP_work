<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上課作業  作品集</title>
    <style>
        /* 設置全局樣式 */
        body {
            /* 設置背景色為淺灰色，提升視覺層次 */
            background-color: #f4f7fa;
            /* 使用現代化字體，備用字體為系統預設 */
            font-family: 'Segoe UI', Arial, sans-serif;
            /* 讓內容垂直水平居中 */
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        /* 標題樣式 */
        h1 {
            /* 設置標題顏色和大小 */
            color: #333;
            font-size: 2.5em;
            margin: 20px 0;
            text-align: center;
        }

        ul {
            /* 移除無序清單的預設項目符號 */
            list-style-type: none;
            /* 使用 flexbox 佈局 */
            display: flex;
            /* 子元素之間均勻分佈 */
            justify-content: space-between;
            /* 允許換行 */
            flex-wrap: wrap;
            /* 設置寬度為父容器的 80%，更靈活 */
            width: 80%;
            /* 設置內邊距 */
            padding: 20px;
            /* 添加背景色和圓角 */
            background-color: #ffffff;
            border-radius: 10px;
            /* 添加陰影，提升層次感 */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        li {
            /* 設置內邊距 */
            padding: 12px 24px;
            /* 設置邊框 */
            border: 1px solid #007bff;
            /* 設置圓角 */
            border-radius: 12px;
            /* 添加陰影 */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            /* 設置最小寬度 */
            min-width: 120px;
            /* 設置背景色 */
            background-color: #e7f1ff;
            /* 添加過渡效果，使變化平滑 */
            transition: transform 0.2s, background-color 0.2s;
            /* 設置外邊距，增加間距 */
            margin: 10px;
            /* 設置文字居中 */
            text-align: center;
        }

        /* 超連結樣式 */
        li a {
            /* 移除預設下劃線 */
            text-decoration: none;
            /* 設置文字顏色 */
            color: #007bff;
            /* 設置字體大小 */
            font-size: 1.1em;
            /* 設置字體粗細 */
            font-weight: 500;
        }

        /* 懸停效果 */
        li:hover {
            /* 放大效果 */
            transform: translateY(-3px);
            /* 改變背景色 */
            background-color: #d0e4ff;
            /* 改變邊框顏色 */
            border-color: #0056b3;
        }

        li a:hover {
            /* 懸停時文字顏色變深 */
            color: #0056b3;
        }

        /* 響應式設計 */
        @media (max-width: 768px) {
            ul {
                /* 小螢幕時寬度調整為 90% */
                width: 90%;
                /* 減少內邊距 */
                padding: 10px;
            }

            li {
                /* 減少最小寬度 */
                min-width: 100px;
                /* 減少外邊距 */
                margin: 8px;
            }
        }
    </style>
</head>

<body>
    <!-- 添加標題 -->
    <h1>我的上課作業</h1>
    <ul>
        <li><a href="../20250418/20250418_03.html" target="_blank">P5音樂</a></li>
        <li><a href="../20250417/20250417_04.html" target="_blank">課表圖</a></li>
        <li><a href="../20250423/20250423_05.html" target="_blank">表單訓練</a></li>
        <li><a href="../20250424/20250424_05/20250424_06.html" target="_blank">超連結練習</a></li>
        <li><a href="./20250425_02.php" target="_blank">變數</a></li>
        <li><a href="../20250428/20250428_01.php" target="_blank">選擇結構</a></li>
        <li><a href="../20250428/20250428_03.php" target="_blank">for 迴圈</a></li>
        <li><a href="../20250428/20250428_04.php" target="_blank">九九乘法表</a></li>
        <li><a href="../20250430/20250430_02.html" target="_blank">SWOT 表格</a></li>
        <li><a href="../20250502/20250502_02.php" target="_blank">迴圈圖形</a></li>
        <li><a href="../20250505/20250505_06.html" target="_blank">于崇銘 - 履歷</a></li>
    </ul>
</body>

</html>