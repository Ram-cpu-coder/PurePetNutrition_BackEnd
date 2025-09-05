


<?php
require_once _DIR_ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Hardcode CORS for local testing
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $certPath = _DIR_ . '/certs/BaltimoreCyberTrustRoot.crt.pem';
    if (!file_exists($certPath)) {
        throw new Exception("SSL certificate not found at: " . $certPath);
    }

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
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
    exit;
}