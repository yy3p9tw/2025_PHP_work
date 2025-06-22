<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯分類</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
    .form-container {
        max-width: 350px;
        margin: 40px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px #ffb34733;
        padding: 2em 1.5em 1.5em 1.5em;
        display: flex;
        flex-direction: column;
        gap: 1.2em;
    }
    .form-container label {
        font-weight: bold;
        color: #b97a56;
        margin-bottom: 0.3em;
    }
    .form-container input[type="text"] {
        padding: 0.6em 1em;
        border: 1px solid #ffb347;
        border-radius: 6px;
        font-size: 1em;
    }
    .form-container button, .form-container .btn-back {
        padding: 0.5em 1.2em;
        border-radius: 6px;
        border: 1px solid #ffb347;
        background: #ffb347;
        color: #fff;
        font-size: 1em;
        margin-top: 0.5em;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: background 0.2s;
    }
    .form-container button:hover, .form-container .btn-back:hover {
        background: #ffa500;
    }
    @media (max-width: 600px) {
        .form-container {
            max-width: 98vw;
            padding: 1.2em 0.5em 1em 0.5em;
        }
        .main-title { font-size: 1.1em; }
    }
    </style>
</head>