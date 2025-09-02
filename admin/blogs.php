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
        $image = trim($_POST['image'] ?? '');
        if ($title && in_array($category, ['food','supplements'], true) && $description && $image) {
            $stmt = $conn->prepare("INSERT INTO blogs (title, category, description, image) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $title, $category, $description, $image);
            $stmt->execute();
            $msg = '✅ Blog created';
        } else { 
            $err = '⚠ All fields required; category must be food or supplements'; 
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        if ($id && $title && in_array($category, ['food','supplements'], true) && $description && $image) {
            $stmt = $conn->prepare("UPDATE blogs SET title=?, category=?, description=?, image=? WHERE id=?");
            $stmt->bind_param("ssssi", $title, $category, $description, $image, $id);
            $stmt->execute();
            $msg = '✅ Blog updated';
        } else { 
            $err = '⚠ All fields required; category must be food or supplements'; 
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM blogs WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $msg = '🗑 Blog deleted';
        }
    }
}

$res = $conn->query("SELECT id, title, category, description, image FROM blogs ORDER BY id DESC");
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

admin_header('Manage Blogs');
?>

<style>
/* same styling as before */
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; box-sizing: border-box; }
h2 { margin-top: 20px; color: #333; }
h3 { margin-top: 30px; color: #0077cc; }
.msg { padding: 10px 15px; border-radius: 6px; margin: 15px 0; font-weight: bold; }
.ok { background: #e6ffed; border: 1px solid #b6f0c0; color: #256029; }
.err { background: #ffecec; border: 1px solid #ffb3b3; color: #8a1f11; }
form { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
label { display: block; margin: 8px 0 4px; font-weight: bold; }
input, select, textarea, button { padding: 8px; width: 100%; max-width: 500px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.95rem; }
textarea { min-height: 80px; resize: vertical; }
button { background: #0077cc; color: #fff; border: none; cursor: pointer; transition: background 0.2s ease; }
button:hover { background: #005fa3; }
button.delete-btn { background: #cc0000; }
button.delete-btn:hover { background: #a30000; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
th, td { padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; }
th { background: #0077cc; color: #fff; }
tr:hover td { background: #f0f8ff; }
details { margin-top: 5px; }
details summary { cursor: pointer; color: #0077cc; font-weight: bold; }
details form { margin-top: 10px; background: #f9f9f9; }
</style>

<div class="container">
    <h2>Blogs</h2>
    <?php if($msg):?><div class="msg ok"><?=h($msg)?></div><?php endif;?>
    <?php if($err):?><div class="msg err"><?=h($err)?></div><?php endif;?>

    <h3>Create</h3>
    <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <label>Title</label><input name="title" required>
        <label>Category</label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            <option value="food">Food</option>
            <option value="supplements">Supplements</option>
        </select>
        <label>Description</label><textarea name="description" required></textarea>
        <label>Image URL</label><input name="image" required>
        <div class="row"><button type="submit">Create</button></div>
    </form>

    <h3>Existing</h3>
    <table>
        <tr><th>ID</th><th>Title</th><th>Category</th><th>Description</th><th>Image</th><th>Actions</th></tr>
        <?php foreach($rows as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['title']) ?></td>
            <td><?= h($r['category']) ?></td>
            <td><?= h($r['description']) ?></td>
            <td><a href="<?= h($r['image']) ?>" target="_blank" rel="noopener">view</a></td>
            <td>
                <form method="post" class="inline" onsubmit="return confirm('Delete this blog?')">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
                <details>
                    <summary>Edit</summary>
                    <form method="post">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <label>Title</label><input name="title" value="<?= h($r['title']) ?>" required>
                        <label>Category</label>
                        <select name="category" required>
                            <option value="food" <?= $r['category']==='food'?'selected':'' ?>>Food</option>
                            <option value="supplements" <?= $r['category']==='supplements'?'selected':'' ?>>Supplements</option>
                        </select>
                        <label>Description</label><textarea name="description" required><?= h($r['description']) ?></textarea>
                        <label>Image URL</label><input name="image" value="<?= h($r['image']) ?>" required>
                        <div class="row"><button type="submit">Save</button></div>
                    </form>
                </details>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php admin_footer(); ?>