<?php
require_once __DIR__ . '/../_bootstrap.php';

require_auth();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

json_ok(['csrf_token' => $_SESSION['csrf_token']]);