<?php
// Helper functions to enforce auth and CSRF when needed

function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        json_error('Unauthorized', 401);
    }
}

function require_csrf_if_enabled() {
    if (!CSRF_ENABLED) return;

    $method = $_SERVER['REQUEST_METHOD'];
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (!$clientToken || !$sessionToken || !hash_equals($sessionToken, $clientToken)) {
            json_error('Forbidden: invalid CSRF token', 403);
        }
    }
}