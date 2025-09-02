<?php
function admin_header($title = 'Admin') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        .container {
        max-width: 1200px;   
        margin: 0 auto;  
        padding: 0 20px; 
        box-sizing: border-box;
}
        /* Navbar Styling */
        .admin-navbar {
            background: #0077cc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            height: 60px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .admin-navbar .nav-brand {
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-decoration: none;
            color:#fff;
        }
        .admin-navbar nav {
            display: flex;
            gap: 15px;
        }
        .admin-navbar nav a {
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s ease;
        }
        .admin-navbar nav a:hover {
            background: rgba(255,255,255,0.15);
        }
        .admin-navbar nav a.logout {
            background: #cc0000;
        }
        .admin-navbar nav a.logout:hover {
            background: #a30000;
        }
        @media (max-width: 600px) {
            .admin-navbar {
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                padding: 10px;
            }
            .admin-navbar nav {
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 10px;
            }
        }
        /* Existing form/table styles */
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        form.inline { display: inline; }
        .row { margin: 10px 0; }
        .msg { padding: 8px; border-radius: 6px; margin: 10px 0; }
        .ok { background: #e6ffed; border: 1px solid #b6f0c0; }
        .err { background: #ffecec; border: 1px solid #ffb3b3; }
        input, select, textarea { padding: 8px; width: 100%; max-width: 560px; }
        label { display: block; margin: 8px 0 4px; }
        button { padding: 8px 14px; cursor: pointer; }
    </style></head><body>';

    echo '<header class="admin-navbar">
        <div><a href="/admin/dashboard.php" class="nav-brand">Admin Panel</a></div>
        <nav>
            <a href="/admin/dashboard.php">Dashboard</a>
            <a href="/admin/blogs.php">Blogs</a>
            <a href="/admin/products.php">Products</a>
            <a href="/admin/testimonials.php">Testimonials</a>
            <a href="/admin/logout.php" class="logout">Logout</a>
        </nav>
    </header>';
}

function admin_footer() {
    echo '</body></html>';
}