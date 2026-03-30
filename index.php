<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/UrlService.php';

$password = $_GET['password'] ?? '';
$isAuthorized = $password === ADMIN_PASSWORD;

if (!$isAuthorized) {
    header('Location: ' . BASE_URL . '/my-links.php?password=' . urlencode($password));
    exit;
}

$urlService = new UrlService();
$urls = $urlService->getAllUrls();

$totalClicks = 0;
foreach ($urls as $url) {
    $totalClicks += $url['clicks'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener - Welcome</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <style>
        .stats-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 36px;
            font-weight: 700;
        }
        .stat-box .label {
            font-size: 14px;
            opacity: 0.9;
        }
        .urls-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .urls-table th, .urls-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .urls-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .urls-table tr:hover {
            background: #f8f9fa;
        }
        .short-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .short-link:hover {
            text-decoration: underline;
        }
        .clicks-count {
            font-weight: 600;
            color: #667eea;
        }
        .create-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="card">
            <h1>URL Shortener</h1>
            <p class="subtitle">Manage your shortened URLs</p>
            
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="number"><?= count($urls) ?></div>
                    <div class="label">Total URLs</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?= number_format($totalClicks) ?></div>
                    <div class="label">Total Clicks</div>
                </div>
            </div>
            
            <a href="<?= BASE_URL ?>/short.php" class="create-btn">+ Create New Short URL</a>
            
            <?php if (empty($urls)): ?>
                <div class="alert alert-error">No URLs created yet. <a href="<?= BASE_URL ?>/short.php">Create your first URL!</a></div>
            <?php else: ?>
                <table class="urls-table">
                    <thead>
                        <tr>
                            <th>Short URL</th>
                            <th>Original URL</th>
                            <th>Clicks</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urls as $url): ?>
                        <tr>
                            <td>
                                <a href="<?= htmlspecialchars($url['short_url']) ?>" target="_blank" class="short-link">
                                    <?= htmlspecialchars($url['short_code']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($url['original_url']) ?>" target="_blank" style="color: #666; text-decoration: none;">
                                    <?= htmlspecialchars(mb_strimwidth($url['original_url'], 0, 50, '...')) ?>
                                </a>
                            </td>
                            <td class="clicks-count"><?= number_format($url['clicks']) ?></td>
                            <td><?= date('M d, Y', strtotime($url['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
