<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Not Found — StockSense</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --accent:#D97706; --bg:#FDF8F3; --text:#1C1C1E; --text-2:#78716C; }
        body { margin:0; font-family:'Nunito',sans-serif; background:var(--bg); color:var(--text); display:flex; align-items:center; justify-content:center; min-height:100vh; text-align:center; padding:24px; box-sizing:border-box; }
        .num { font-size:5rem; font-weight:900; color:var(--accent); line-height:1; }
        .msg { font-size:1.1rem; font-weight:700; margin:8px 0 4px; }
        .sub { font-size:.85rem; color:var(--text-2); margin-bottom:28px; }
        .btn { display:inline-block; background:var(--accent); color:#fff; font-family:'Nunito',sans-serif; font-weight:800; font-size:.9rem; padding:13px 28px; border-radius:14px; text-decoration:none; }
    </style>
</head>
<body>
<div>
    <div class="num">404</div>
    <div class="msg">Page not found</div>
    <div class="sub">This page doesn't exist or was moved.</div>
    <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/" class="btn">Go Home</a>
</div>
</body>
</html>
