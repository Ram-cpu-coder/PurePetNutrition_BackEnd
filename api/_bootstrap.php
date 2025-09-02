<?php
// Common bootstrap for all API endpoints
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/utils/response.php';
require_once __DIR__ . '/utils/validate.php';
require_once __DIR__ . '/auth/auth_check.php'; 

// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
$allowedOrigin = ALLOWED_ORIGIN === '*' ? '*' : ALLOWED_ORIGIN;
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// decode JSON body safely
function get_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// get query param
function qparam(string $key, $default = null) {
    return $_GET[$key] ?? $default;
}