
<?php
// Environment
define('APP_ENV', getenv('APP_ENV') ?: 'local');

// Database credentials
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));

// Security
define('ALLOWED_ORIGIN', getenv('ALLOWED_ORIGIN') ?: '*');
define('CSRF_ENABLED', true);
