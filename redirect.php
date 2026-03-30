<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/UrlService.php';

$shortCode = $_GET['code'] ?? '';

if (empty($shortCode)) {
    http_response_code(404);
    echo "URL not found";
    exit;
}

try {
    $urlService = new UrlService();
    $url = $urlService->getUrlByCode($shortCode);
    
    if (!$url) {
        http_response_code(404);
        echo "URL not found";
        exit;
    }
    
    if (!$url->isActive()) {
        http_response_code(410);
        echo "This URL has been deactivated";
        exit;
    }
    
    if ($url->isExpired()) {
        http_response_code(410);
        echo "This URL has expired";
        exit;
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $urlService->incrementClick($url);
    $urlService->logClick($url->getId(), $referer, $userAgent, $ipAddress);
    
    header('Location: ' . $url->getOriginalUrl(), true, 301);
    exit;
    
} catch (Exception $e) {
    error_log('Redirect Error: ' . $e->getMessage());
    http_response_code(500);
    echo "An error occurred";
    exit;
}
