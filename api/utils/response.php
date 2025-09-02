<?php
function json_ok($data = [], int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function json_error(string $message, int $code = 400, $details = null) {
    http_response_code($code);
    $payload = ['error' => $message];
    if (!is_null($details)) $payload['details'] = $details;
    echo json_encode($payload);
    exit;
}