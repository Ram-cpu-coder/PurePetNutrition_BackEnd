<?php
require_once 'guard.php';
require_once 'layout.php';
admin_header('Admin Dashboard');
?>
<h1>Dashboard</h1>
<p>Manage site content: blogs, products, testimonials, and review contact submissions.</p>
<ul>
    <li><a href="/backend/admin/blogs.php">Manage Blogs</a></li>
    <li><a href="/backend/admin/products.php">Manage Products</a></li>
    <li><a href="/backend/admin/testimonials.php">Manage Testimonials</a></li>
    <li><a href="/backend/admin/contacts.php">View Contacts</a></li>
</ul>
<?php admin_footer(); ?>