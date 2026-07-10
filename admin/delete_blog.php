<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    header('Location: blog');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT feature_image FROM blogs WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row && $row['feature_image']) {
        $file = __DIR__ . '/../' . $row['feature_image'];
        if (file_exists($file)) @unlink($file);
    }

    $pdo->prepare("DELETE FROM blogs WHERE id = ?")->execute([$id]);
} catch (Exception $e) {
    error_log('delete_blog error: ' . $e->getMessage());
}

header('Location: blog');
exit;
