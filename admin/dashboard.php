<?php
require_once 'guard.php';
require_once 'layout.php';
require_once '../config/db.php';

admin_header('Admin Dashboard');

// Fetch counts only for existing tables
$counts = [
    'Blogs' => $conn->query("SELECT COUNT(*) AS total FROM blogs")->fetch_assoc()['total'],
    'Products' => $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'],
    'Testimonials' => $conn->query("SELECT COUNT(*) AS total FROM testimonials")->fetch_assoc()['total']
];
?>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f6fa;
        margin: 0;
        padding: 0;
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

    /* Dashboard Styling */
    .container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 0 15px;
    }
    h1 {
        margin-bottom: 10px;
        color: #333;
    }
    p {
        color: #666;
    }
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.2s ease;
    }
    .card:hover {
        transform: translateY(-3px);
    }
    .card h2 {
        margin: 0;
        font-size: 1.2rem;
        color: #444;
    }
    .card p {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
        color: #0077cc;
    }
    .card a {
        display: inline-block;
        padding: 6px 12px;
        background: #0077cc;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .card a:hover {
        background: #005fa3;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
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
</style>


<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Quick overview of products, blogs and testimonials.</p>

    <div class="cards">
        <?php foreach ($counts as $label => $total): ?>
            <div class="card">
                <h2><?= $label ?></h2>
                <p><?= $total ?></p>
                <a href="/admin/<?= strtolower($label) ?>.php">Manage</a>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 style="margin-top:40px;">Recent Blogs</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Date Created</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = $conn->query("SELECT title, created_at FROM blogs ORDER BY created_at DESC LIMIT 5");
            while ($row = $res->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php admin_footer(); ?>