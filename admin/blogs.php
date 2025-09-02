<?php
require_once 'guard.php';
require_once '../config/db.php';
require_once 'layout.php';
require_once 'csrf.php';

$msg = ''; 
$err = '';

function h($s){return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($title && $category && $description) {
            $stmt = $conn->prepare("INSERT INTO blogs (title, category, description) VALUES (?,?,?)");
            $stmt->bind_param("sss", $title, $category, $description);
            $stmt->execute();
            $msg = 'Blog created';
        } else { $err = 'All fields required'; }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($id && $title && $category && $description) {
            $stmt = $conn->prepare("UPDATE blogs SET title=?, category=?, description=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $category, $description, $id);
            $stmt->execute();
            $msg = 'Blog updated';
        } else { $err = 'All fields required'; }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM blogs WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $msg = 'Blog deleted';
        }
    }
}

$res = $conn->query("SELECT id, title, category, description FROM blogs ORDER BY id DESC");
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

admin_header('Manage Blogs');
?>
<h2>Blogs</h2>
<?php if($msg):?><div class="msg ok"><?=h($msg)?></div><?php endif;?>
<?php if($err):?><div class="msg err"><?=h($err)?></div><?php endif;?>

<h3>Create</h3>
<form method="post">
    <?php csrf_field(); ?>
    <input type="hidden" name="action" value="create">
    <label>Title</label><input name="title" required>
    <label>Category</label><input name="category" required>
    <label>Description</label><textarea name="description" required></textarea>
    <div class="row"><button type="submit">Create</button></div>
</form>

<h3>Existing</h3>
<table>
    <tr><th>ID</th><th>Title</th><th>Category</th><th>Description</th><th>Actions</th></tr>
    <?php foreach($rows as $r): ?>
    <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= h($r['title']) ?></td>
        <td><?= h($r['category']) ?></td>
        <td><?= h($r['description']) ?></td>
        <td>
            <form method="post" class="inline" onsubmit="return confirm('Delete this blog?')">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit">Delete</button>
            </form>
            <details>
                <summary>Edit</summary>
                <form method="post">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <label>Title</label><input name="title" value="<?= h($r['title']) ?>" required>
                    <label>Category</label><input name="category" value="<?= h($r['category']) ?>" required>
                    <label>Description</label><textarea name="description" required><?= h($r['description']) ?></textarea>
                    <div class="row"><button type="submit">Save</button></div>
                </form>
            </details>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php admin_footer(); ?>