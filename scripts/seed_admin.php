<?php
require_once __DIR__ . '/../config/db.php';

$username = 'admin';
$password = 'admin123'; 
$role = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

// Ensure users table exists
$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hash, $role);
$stmt->execute();

echo "Admin seeded. Username: $username\n";