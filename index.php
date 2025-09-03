<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'Backend API is running',
    'version' => '1.0',
    'endpoints' => [
        'login' => '/admin/login.php',
        'logout' => '/admin/logout.php',
        'csrf_token' => '/api/auth/csrf_token.php',
        'products' => '/admin/products.php',
        'blogs' => '/admin/blogs.php',
        'testimonials' => '/admin/testimonials.php'
    ]
]);