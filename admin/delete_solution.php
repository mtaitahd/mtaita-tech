<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Solution.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $solution = new Solution();
    $solution->delete($id);
}

header('Location: solutions.php?msg=' . urlencode('Solution deleted.'));
exit;
