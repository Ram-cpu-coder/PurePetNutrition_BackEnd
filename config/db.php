<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Path to SSL certificate
    $certPath = __DIR__ . '/certs/BaltimoreCyberTrustRoot.crt.pem';

    // Check if certificate exists
    if (!file_exists($certPath)) {
        throw new Exception("SSL certificate not found at: " . $certPath);
    }

    // Initialize mysqli
    $conn = mysqli_init();

    // Set SSL options
    mysqli_ssl_set($conn, NULL, NULL, $certPath, NULL, NULL);

    // Connecting with SSL and don't verify server cert (avoids Windows issues)
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

    // Set charset
    $conn->set_charset('utf8mb4');

    //  CORS header
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: " . ALLOWED_ORIGIN);
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
    }

    // connection confirmation for development
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
