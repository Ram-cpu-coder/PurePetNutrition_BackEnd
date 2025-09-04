<?php

require_once "../../config/cors.php";
require_once __DIR__ . '/../_bootstrap.php';

// POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$data = get_json_body();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!v_required($username) || !v_required($password)) {
    json_error('Username and password are required', 400);
}

$stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_error('Invalid credentials', 401);
}

// Set session
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
// CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

json_ok([
    'message' => 'Login successful',
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ],
    'csrf_token' => $_SESSION['csrf_token']
]);