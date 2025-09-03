<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// CORS: Allow only specific origins
$allowedOrigins = [
    'http://127.0.0.1:5500',
    'https://ram-cpu-coder.github.io/Static_PurePetNutrition'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
    }
    exit(0);
}

try {
    // Path to SSL certificate
    $certPath = __DIR__ . '/certs/BaltimoreCyberTrustRoot.crt.pem';

    if (!file_exists($certPath)) {
        throw new Exception("SSL certificate not found at: " . $certPath);
    }

    // Initialize mysqli with SSL
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, $certPath, NULL, NULL);

    mysqli_real_connect(
        $conn,
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        3306,
        NULL,
        MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
    );

    $conn->set_charset('utf8mb4');

    // Apply CORS headers for actual requests
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
    }

    // Optional dev confirmation
    if (APP_ENV === 'local') {
        echo "Connected securely to Azure MySQL!";
    }


} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo "Database connection failed: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
    exit;
}