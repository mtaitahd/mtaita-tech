<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

function admin_redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script>window.location.href=' . json_encode($url) . ';</script>';
    }
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT poster_image_path FROM posters WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $poster = $stmt->fetch();

    if ($poster) {
        $file_path = __DIR__ . '/../' . $poster['poster_image_path'];
        if (file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        }

        $stmt = $pdo->prepare("DELETE FROM posters WHERE id = ?");
        $stmt->execute([$id]);
    }
}

admin_redirect('posters.php?msg=' . urlencode('Poster deleted successfully.'));
