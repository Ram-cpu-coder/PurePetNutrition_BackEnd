<?php
function admin_header($title = 'Admin') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body{font-family:Arial, sans-serif;margin:20px}
        nav a{margin-right:12px}
        table{border-collapse:collapse;width:100%}
        th,td{border:1px solid #ddd;padding:8px}
        form.inline{display:inline}
        .row{margin:10px 0}
        .msg{padding:8px;border-radius:6px;margin:10px 0}
        .ok{background:#e6ffed;border:1px solid #b6f0c0}
        .err{background:#ffecec;border:1px solid #ffb3b3}
        input,select,textarea{padding:8px;width:100%;max-width:560px}
        label{display:block;margin:8px 0 4px}
        button{padding:8px 14px;cursor:pointer}
    </style></head><body>';
    echo '<nav>
        <a href="/backend/admin/dashboard.php">Dashboard</a>
        <a href="/backend/admin/blogs.php">Blogs</a>
        <a href="/backend/admin/products.php">Products</a>
        <a href="/backend/admin/testimonials.php">Testimonials</a>
        <a href="/backend/admin/contacts.php">Contacts</a>
        <a href="/backend/admin/logout.php">Logout</a>
    </nav><hr>';
}
function admin_footer() {
    echo '</body></html>';
}