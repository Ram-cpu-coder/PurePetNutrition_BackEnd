<?php
require_once 'guard.php';
require_once '../config/db.php';
require_once 'layout.php';
function h($s){return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');}
$res = $conn->query("SELECT id, name, email, message, consent, submitted_at FROM contacts ORDER BY submitted_at DESC");
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
admin_header('View Contacts');
?>
<h2>Contacts</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Consent</th><th>Submitted</th></tr>
    <?php foreach($rows as $r): ?>
    <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= h($r['name']) ?></td>
        <td><a href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a></td>
        <td><?= nl2br(h($r['message'])) ?></td>
        <td><?= $r['consent'] ? 'Yes' : 'No' ?></td>
        <td><?= h($r['submitted_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php admin_footer(); ?>