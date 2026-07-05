<?php
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'test';
$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once __DIR__ . '/admin/admin_header.php';
?><h4 class="mb-4">Dashboard Test</h4>
<p class="text-muted">If you see this styled, CSS & Bootstrap are working.</p>
<?php require_once __DIR__ . '/admin/admin_footer.php';