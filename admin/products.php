<?php
require_once 'guard.php';
require_once '../config/db.php';
require_once 'layout.php';
require_once 'csrf.php';

function h($s){return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');}
$msg=''; 
$err='';

if ($_SERVER['REQUEST_METHOD']==='POST'){ 
    csrf_verify();
    $action=$_POST['action']??'';
    
    if($action==='create' || $action==='update'){
        $name=trim($_POST['name']??'');
        $category=trim($_POST['category']??'');
        $description=trim($_POST['description']??'');
        $image=trim($_POST['image']??'');
        $price=trim($_POST['price']??'');

        if($name && in_array($category,['food','supplements'],true) && $description && $image && is_numeric($price)){
            if($action==='create'){
                $stmt=$conn->prepare("INSERT INTO products (name,category,description,image,price) VALUES (?,?,?,?,?)");
                $stmt->bind_param("ssssd",$name,$category,$description,$image,$price);
                $stmt->execute(); 
                $msg='✅ Product created';
            } else {
                $id=(int)($_POST['id']??0);
                if($id){
                    $stmt=$conn->prepare("UPDATE products SET name=?,category=?,description=?,image=?,price=? WHERE id=?");
                    $stmt->bind_param("ssssdi",$name,$category,$description,$image,$price,$id);
                    $stmt->execute(); 
                    $msg='✅ Product updated';
                } else { 
                    $err='❌ Invalid ID'; 
                }
            }
        } else { 
            $err='⚠ All fields required; category must be food/supplements; price numeric'; 
        }
    } elseif($action==='delete'){
        $id=(int)($_POST['id']??0);
        if($id){ 
            $stmt=$conn->prepare("DELETE FROM products WHERE id=?"); 
            $stmt->bind_param("i",$id); 
            $stmt->execute(); 
            $msg='🗑 Product deleted'; 
        }
    }
}

$res=$conn->query("SELECT id,name,category,description,image,price FROM products ORDER BY id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];

admin_header('Manage Products');
?>

<style>
h2 {
    margin-top: 20px;
    color: #333;
}
h3 {
    margin-top: 30px;
    color: #0077cc;
}
.msg {
    padding: 10px 15px;
    border-radius: 6px;
    margin: 15px 0;
    font-weight: bold;
}
.ok { background: #e6ffed; border: 1px solid #b6f0c0; color: #256029; }
.err { background: #ffecec; border: 1px solid #ffb3b3; color: #8a1f11; }

form {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}
label {
    display: block;
    margin: 8px 0 4px;
    font-weight: bold;
}
input, select, textarea, button {
    padding: 8px;
    width: 100%;
    max-width: 500px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 0.95rem;
}
textarea { min-height: 80px; resize: vertical; }
button {
    background: #0077cc;
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background 0.2s ease;
}
button:hover { background: #005fa3; }
button.delete-btn {
    background: #cc0000;
}
button.delete-btn:hover {
    background: #a30000;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    text-align: left;
}
th {
    background: #0077cc;
    color: #fff;
}
tr:hover td {
    background: #f0f8ff;
}
details {
    margin-top: 5px;
}
details summary {
    cursor: pointer;
    color: #0077cc;
    font-weight: bold;
}
details form {
    margin-top: 10px;
    background: #f9f9f9;
}
a.view-link {
    color: #0077cc;
    text-decoration: none;
}
a.view-link:hover {
    text-decoration: underline;
}
</style>

<div class="container">
    <h2>Products</h2>
    <?php if($msg):?><div class="msg ok"><?=h($msg)?></div><?php endif; ?>
    <?php if($err):?><div class="msg err"><?=h($err)?></div><?php endif; ?>

    <h3>Create</h3>
    <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <label>Name</label><input name="name" required>
        <label>Category</label>
        <select name="category" required>
            <option value="food">food</option>
            <option value="supplements">supplements</option>
        </select>
        <label>Description</label><textarea name="description" required></textarea>
        <label>Image URL</label><input name="image" required>
        <label>Price</label><input type="number" step="0.01" name="price" required>
        <div class="row"><button type="submit">Create</button></div>
    </form>

    <h3>Existing</h3>
    <table>
        <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Image</th><th>Actions</th></tr>
        <?php foreach($rows as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['name']) ?></td>
            <td><?= h($r['category']) ?></td>
            <td>$<?= number_format((float)$r['price'],2) ?></td>
            <td><a class="view-link" href="<?= h($r['image']) ?>" target="_blank" rel="noopener">view</a></td>
            <td>
                <form method="post" class="inline" onsubmit="return confirm('Delete product?')">
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
                        <label>Name</label><input name="name" value="<?= h($r['name']) ?>" required>
                        <label>Category</label>
                        <select name="category" required>
                            <option value="food" <?= $r['category']==='food'?'selected':'' ?>>food</option>
                            <option value="supplements" <?= $r['category']==='supplements'?'selected':'' ?>>supplements</option>
                        </select>
                        <label>Description</label><textarea name="description" required><?= h($r['description']) ?></textarea>
                        <label>Image URL</label><input name="image" value="<?= h($r['image']) ?>" required>
                        <label>Price</label><input type="number" step="0.01" name="price" value="<?= h($r['price']) ?>" required>
                        <div class="row"><button type="submit">Save</button></div>
                    </form>
                </details>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php admin_footer(); ?>