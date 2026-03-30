<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/UrlService.php';

$response = [
    'success' => false,
    'data' => null,
    'error' => null
];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['error'] = 'Method not allowed';
    http_response_code(405);
    echo json_encode($response);
    exit;
}

try {
    $urlService = new UrlService();
    $urls = $urlService->getAllUrls();
    
    $response['success'] = true;
    $response['data'] = $urls;
    $response['total'] = count($urls);
} catch (Exception $e) {
    $response['error'] = 'Internal server error';
    http_response_code(500);
    error_log('API Error: ' . $e->getMessage());
}

echo json_encode($response);
