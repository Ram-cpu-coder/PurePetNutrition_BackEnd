<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    // connection confirmation
    // if (APP_ENV === 'local') {
    //     echo "Database connected successfully!!!";
    // }
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo "Database connection failed: " . $e->getMessage();
    exit;
}