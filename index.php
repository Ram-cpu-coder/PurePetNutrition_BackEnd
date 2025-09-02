<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'Backend API is running',
    'version' => '1.0',
    'endpoints' => [
        'login' => '/backend/api/auth/login.php',
        'logout' => '/backend/api/auth/logout.php',
        'csrf_token' => '/backend/api/auth/csrf_token.php',
        'products' => '/backend/api/products.php',
        'blogs' => '/backend/api/blogs.php',
        'testimonials' => '/backend/api/testimonials.php'
    ]
]);