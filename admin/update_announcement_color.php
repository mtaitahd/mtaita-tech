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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $color = trim($_POST['text_color'] ?? '#FFFFFF');

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE announcements SET text_color = ? WHERE id = ?");
        $stmt->execute([$color, $id]);
    }
}

admin_redirect('announcements.php?msg=' . urlencode('Announcement color updated!'));
