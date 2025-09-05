

<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['uid'])) {
    header("Location: /admin/dashboard.php");
    exit;
}
require_once '../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($pass, $row['password_hash'])) {
                $_SESSION['uid'] = (int)$row['id'];
                header("Location: /admin/dashboard.php");
                exit;
            }
        }
    }
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<style>
body{font-family:Arial;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
form{border:1px solid #ddd;border-radius:8px;padding:24px;min-width:320px}
.row{margin:10px 0} input{width:100%;padding:10px}
.err{color:#b00020;margin-top:8px}
button{padding:10px 14px;width:100%}
</style></head><body>
<form method="post">
    <h2>Admin Login</h2>
    <div class="row"><input type="email" name="email" placeholder="Email" required></div>
    <div class="row"><input type="password" name="password" placeholder="Password" required></div>
    <button type="submit">Login</button>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
</form>
</body></html>