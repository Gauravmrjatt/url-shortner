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

$shortCode = $_GET['code'] ?? '';

if (empty($shortCode)) {
    $response['error'] = 'Short code is required';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

try {
    $urlService = new UrlService();
    $result = $urlService->getStats($shortCode);
    
    if ($result['success']) {
        $response['success'] = true;
        $response['data'] = $result['data'];
    } else {
        $response['error'] = $result['error'];
        http_response_code(404);
    }
} catch (Exception $e) {
    $response['error'] = 'Internal server error';
    http_response_code(500);
    error_log('API Error: ' . $e->getMessage());
}

echo json_encode($response);
