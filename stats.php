<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/UrlService.php';

$password = $_GET['password'] ?? $_POST['password'] ?? '';
$isAuthorized = $password === ADMIN_PASSWORD;

if (!$isAuthorized) {
    http_response_code(401);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - Osm-it</title>
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'DM Sans', sans-serif; background: #ede8e1; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .card { background: #faf8f5; padding: 40px; border-radius: 16px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
            h1 { font-family: 'DM Serif Display', serif; color: #2a1e10; margin-bottom: 16px; }
            p { color: #9a8470; margin-bottom: 20px; }
            input { padding: 12px 16px; border: 2px solid #dfd0bc; border-radius: 8px; font-size: 14px; width: 200px; }
            button { padding: 12px 24px; background: #7b5c3c; color: #fff; border: none; border-radius: 8px; cursor: pointer; margin-left: 8px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Osm-it</h1>
            <p>Enter password to access</p>
            <form method="GET">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Go</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$code = $_GET['code'] ?? '';
$urlData = null;
$error = '';

if (!empty($code)) {
    try {
        $urlService = new UrlService();
        $result = $urlService->getStats($code);
        
        if ($result['success']) {
            $urlData = $result['data'];
        } else {
            $error = $result['error'];
        }
    } catch (Exception $e) {
        $error = 'Failed to load stats';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Stats - URL Shortener</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>URL Statistics</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($urlData): ?>
                <p class="subtitle"><?= htmlspecialchars($urlData['short_url']) ?></p>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($urlData['clicks']) ?></div>
                        <div class="stat-label">Total Clicks</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= date('M d, Y', strtotime($urlData['created_at'])) ?></div>
                        <div class="stat-label">Created</div>
                    </div>
                    <?php if ($urlData['last_clicked']): ?>
                    <div class="stat-card">
                        <div class="stat-value"><?= date('M d, Y', strtotime($urlData['last_clicked'])) ?></div>
                        <div class="stat-label">Last Clicked</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="result-box">
                    <label>Original URL:</label>
                    <input type="text" value="<?= htmlspecialchars($urlData['original_url']) ?>" readonly>
                </div>
            <?php else: ?>
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="code">Enter short code:</label>
                        <input type="text" name="code" id="code" placeholder="abc123" required>
                    </div>
                    <button type="submit" class="btn-primary">View Stats</button>
                </form>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>/" class="back-link">&larr; Back to Home</a>
        </div>
    </div>
</body>
</html>
