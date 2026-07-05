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
    $text = trim($_POST['announcement_text'] ?? '');

    if ($text !== '') {
        $stmt = $pdo->query("SELECT id FROM announcements WHERE is_active = 1 LIMIT 1");
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE announcements SET announcement_text = ? WHERE id = ?");
            $stmt->execute([$text, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO announcements (announcement_text, is_active) VALUES (?, 1)");
            $stmt->execute([$text]);
        }
    }
}

admin_redirect('announcements.php?msg=' . urlencode('Announcement updated!'));
