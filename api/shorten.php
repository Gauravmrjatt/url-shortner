<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = 'Method not allowed';
    http_response_code(405);
    echo json_encode($response);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $url = $input['url'] ?? '';
    $customAlias = $input['custom_alias'] ?? null;
    $expiresAt = $input['expires_at'] ?? null;
} else {
    $url = $_POST['url'] ?? '';
    $customAlias = $_POST['custom_alias'] ?? null;
    $expiresAt = $_POST['expires_at'] ?? null;
}

if (empty($url)) {
    $response['error'] = 'URL is required';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

try {
    $urlService = new UrlService();
    $result = $urlService->createShortUrl($url, $customAlias, null, $expiresAt);
    
    if ($result['success']) {
        $response['success'] = true;
        $response['data'] = $result['data'];
        http_response_code(201);
    } else {
        $response['error'] = $result['error'];
        http_response_code(400);
    }
} catch (Exception $e) {
    $response['error'] = 'Internal server error';
    http_response_code(500);
    error_log('API Error: ' . $e->getMessage());
}

echo json_encode($response);
