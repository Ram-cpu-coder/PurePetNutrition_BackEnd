<?php
require_once 'guard.php';
require_once '../config/db.php';
require_once 'layout.php';
require_once 'csrf.php';

$msg = '';
$err = '';

function h($s){return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');}

// Standard POST (non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['ajax'])) {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $messageTxt = trim($_POST['message'] ?? '');
        $rating = $_POST['rating'] !== '' ? (int)$_POST['rating'] : null;
        $approved = isset($_POST['approved']) ? 1 : 0;

        if ($id && $name && $messageTxt) {
            $stmt = $conn->prepare("UPDATE testimonials SET name=?, message=?, rating=?, approved=? WHERE id=?");
            $stmt->bind_param("ssiii", $name, $messageTxt, $rating, $approved, $id);
            $stmt->execute();
            $msg = '✅ Testimonial updated';
        } else {
            $err = '⚠ Name and message are required';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM testimonials WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $msg = '🗑 Testimonial deleted';
        }
    }
}

// AJAX: toggle visibility
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'toggle_visibility') {
    header('Content-Type: application/json');
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $approved = (int)($_POST['approved'] ?? 0);
    if ($id) {
        $stmt = $conn->prepare("UPDATE testimonials SET approved=? WHERE id=?");
        $stmt->bind_param("ii", $approved, $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
    exit;
}

$res = $conn->query("SELECT id, name, message, rating, approved, created_at FROM testimonials ORDER BY id DESC");
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

admin_header('Manage Testimonials');
?>

<style>
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
h2 { margin-top: 20px; }
.msg { padding: 10px 15px; border-radius: 6px; margin: 15px 0; font-weight: bold; }
.ok { background: #e6ffed; border: 1px solid #b6f0c0; color: #256029; }
.err { background: #ffecec; border: 1px solid #ffb3b3; color: #8a1f11; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
th, td { padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top; }
th { background: #0077cc; color: #fff; }
tr:hover td { background: #f0f8ff; }
tr.viewable { cursor: pointer; }
details summary { cursor: pointer; color: #0077cc; font-weight: bold; }
form { margin: 0; display: inline-block; }
input, textarea, select, button {
    padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem;
}
textarea { min-height: 60px; resize: vertical; width: 100%; }
button { background: #0077cc; color: #fff; border: none; cursor: pointer; padding: 6px 12px; }
button:hover { background: #005fa3; }
button.delete-btn { background: #cc0000; }
button.delete-btn:hover { background: #a30000; }
.toggle-visibility { cursor: pointer; font-size: 1.2rem; user-select: none; }
.view-btn { background: #555; margin-top: 5px; }
.view-btn:hover { background: #333; }

/* Modal */
#testimonial-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; padding:40px 16px; }
#testimonial-modal .modal-content { background:#fff; max-width:700px; margin:auto; padding:20px; border-radius:8px; position:relative; }
#modal-close { position:absolute; top:10px; right:10px; background:none; border:none; font-size:1.2rem; cursor:pointer; }
#modal-message { white-space: pre-wrap; }
.modal-meta { color:#555; font-size: 0.9rem; }
.modal-actions { margin-top: 12px; display:flex; gap:8px; }
</style>

<div class="container">
    <h2>Testimonials</h2>
    <?php if($msg):?><div class="msg ok"><?=h($msg)?></div><?php endif;?>
    <?php if($err):?><div class="msg err"><?=h($err)?></div><?php endif;?>

    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Message</th><th>Rating</th><th>Visible</th><th>Created</th><th>Actions</th>
        </tr>
        <?php foreach($rows as $r): ?>
        <tr class="viewable"
            data-viewable="1"
            data-id="<?= (int)$r['id'] ?>"
            data-name="<?= h($r['name']) ?>"
            data-message="<?= h($r['message']) ?>"
            data-rating="<?= h($r['rating']) ?>"
            data-approved="<?= (int)$r['approved'] ?>"
            data-created="<?= h($r['created_at']) ?>">
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['name']) ?></td>
            <td><?= nl2br(h($r['message'])) ?></td>
            <td><?= h($r['rating']) ?></td>
            <td class="visibility-cell" data-id="<?= (int)$r['id'] ?>">
                <span class="toggle-visibility" data-approved="<?= (int)$r['approved'] ?>">
                    <?= $r['approved'] ? '👁' : '🙈' ?>
                </span>
            </td>
            <td><?= h($r['created_at']) ?></td>
            <td>
                <button type="button" class="view-btn"
                    data-name="<?= h($r['name']) ?>"
                    data-message="<?= h($r['message']) ?>"
                    data-rating="<?= h($r['rating']) ?>"
                    data-approved="<?= (int)$r['approved'] ?>"
                    data-created="<?= h($r['created_at']) ?>">
                    View
                </button>
                <form method="post" class="inline" onsubmit="return confirm('Delete this testimonial?')">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
                <details>
                    <summary>Edit</summary>
                    <form method="post" class="edit-form">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <label>Name</label><input name="name" value="<?= h($r['name']) ?>" required>
                        <label>Message</label><textarea name="message" required><?= h($r['message']) ?></textarea>
                        <label>Rating</label>
                        <select name="rating">
                            <option value="">-- None --</option>
                            <?php for($i=1;$i<=5;$i++): ?>
                                <option value="<?=$i?>" <?= ($r['rating']==$i?'selected':'') ?>><?=$i?></option>
                            <?php endfor; ?>
                        </select>
                        <label><input type="checkbox" name="approved" value="1" <?= $r['approved'] ? 'checked' : '' ?>> Visible</label>
                        <div style="margin-top:8px;"><button type="submit">Save</button></div>
                    </form>
                </details>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Modal -->
<div id="testimonial-modal">
    <div class="modal-content">
        <button id="modal-close">✖</button>
        <h3 id="modal-name"></h3>
        <p id="modal-message"></p>
        <p class="modal-meta"><strong>Rating:</strong> <span id="modal-rating"></span></p>
        <p class="modal-meta"><strong>Status:</strong> <span id="modal-approved"></span></p>
        <p class="modal-meta"><strong>Created:</strong> <span id="modal-created"></span></p>
        <div class="modal-actions">
            <button type="button" id="modal-toggle-visibility">Toggle visibility</button>
            <button type="button" id="modal-close-2">Close</button>
        </div>
    </div>
</div>

<script>
// Helpers
function openModal(data) {
    document.getElementById('modal-name').textContent = data.name || '';
    document.getElementById('modal-message').textContent = data.message || '';
    document.getElementById('modal-rating').textContent = data.rating || '—';
    document.getElementById('modal-approved').textContent = data.approved === '1' ? 'Visible' : 'Hidden';
    document.getElementById('modal-created').textContent = data.created || '';
    document.getElementById('testimonial-modal').dataset.id = data.id || '';
    document.getElementById('testimonial-modal').dataset.approved = data.approved || '0';
    document.getElementById('testimonial-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('testimonial-modal').style.display = 'none';
}

document.addEventListener('click', function(e) {
    const target = e.target;

    // Avoid row-click when interacting with controls
    const isInteractive = target.closest('button, .toggle-visibility, form, details, select, input, textarea, a');

    // Row click to view
    if (!isInteractive) {
        const row = target.closest('tr[data-viewable="1"]');
        if (row) {
            openModal({
                id: row.dataset.id,
                name: row.dataset.name,
                message: row.dataset.message,
                rating: row.dataset.rating || '—',
                approved: row.dataset.approved,
                created: row.dataset.created
            });
            return;
        }
    }

    // View button (explicit)
    if (target.classList.contains('view-btn')) {
        openModal({
            id: target.closest('tr').dataset.id,
            name: target.dataset.name,
            message: target.dataset.message,
            rating: target.dataset.rating || '—',
            approved: target.dataset.approved,
            created: target.dataset.created
        });
        return;
    }

// Eye toggle
const eye = target.closest('.toggle-visibility');
if (eye) {
    const cell = eye.closest('.visibility-cell');
    const row = eye.closest('tr');
    const id = cell.dataset.id;
    const nextApproved = eye.dataset.approved === '1' ? 0 : 1;

    // Call the updated API endpoint directly
    fetch(`/api/testimonials.php?id=${encodeURIComponent(id)}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ approved: nextApproved })
    })
    .then(r => r.json())
    .then(json => {
        if (json && json.message) {
            // Update icon state
            eye.dataset.approved = String(nextApproved);
            eye.textContent = nextApproved ? '👁' : '🙈';

            // Sync row dataset and any edit checkbox
            row.dataset.approved = String(nextApproved);
            const editCheckbox = row.querySelector('input[type="checkbox"][name="approved"]');
            if (editCheckbox) editCheckbox.checked = !!nextApproved;

            // Sync modal if it's open for this row
            const modal = document.getElementById('testimonial-modal');
            if (modal.style.display === 'block' && modal.dataset.id === String(id)) {
                modal.dataset.approved = String(nextApproved);
                document.getElementById('modal-approved').textContent = nextApproved ? 'Visible' : 'Hidden';
            }
        } else {
            console.error('Toggle failed', json);
        }
    })
    .catch(err => console.error('AJAX error', err));

    return;
}

    // Modal close
    if (target.id === 'modal-close' || target.id === 'modal-close-2' || target.id === 'testimonial-modal') {
        if (target.id === 'testimonial-modal' && !target.classList.contains('modal-overlay')) {
            // clicking the dark overlay (outside content)
            if (!e.target.closest('.modal-content')) closeModal();
        } else {
            closeModal();
        }
        return;
    }
// Modal toggle visibility button
if (target.id === 'modal-toggle-visibility') {
    const modal = document.getElementById('testimonial-modal');
    const id = modal.dataset.id;
    const current = modal.dataset.approved === '1' ? 1 : 0;
    const nextApproved = current ? 0 : 1;

    fetch(`/api/testimonials.php?id=${encodeURIComponent(id)}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ approved: nextApproved })
    })
    .then(r => r.json())
    .then(json => {
        if (json && json.message) {
            // Update modal state
            modal.dataset.approved = String(nextApproved);
            document.getElementById('modal-approved').textContent = nextApproved ? 'Visible' : 'Hidden';

            // Update table eye/icon and row dataset
            const eyeSpan = document.querySelector(`.visibility-cell[data-id="${CSS.escape(id)}"] .toggle-visibility`);
            if (eyeSpan) {
                eyeSpan.dataset.approved = String(nextApproved);
                eyeSpan.textContent = nextApproved ? '👁' : '🙈';
                const row = eyeSpan.closest('tr');
                if (row) {
                    row.dataset.approved = String(nextApproved);
                    const editCheckbox = row.querySelector('input[type="checkbox"][name="approved"]');
                    if (editCheckbox) editCheckbox.checked = !!nextApproved;
                }
            }
        } else {
            console.error('Toggle failed', json);
        }
    })
    .catch(err => console.error('AJAX error', err));

    return;
}
});

// Close modal on ESC
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeModal();
});
</script>

<?php admin_footer(); ?>
