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
    $color = trim($_POST['text_color'] ?? '#FFFFFF');

    if ($text !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO announcements (announcement_text, text_color, is_active) VALUES (?, ?, 1)");
            $stmt->execute([$text, $color]);
            admin_redirect('announcements.php?msg=' . urlencode('Announcement added!'));
        } catch (Exception $e) {
            error_log('add_announcement DB error: ' . $e->getMessage());
            admin_redirect('dashboard.php?error=' . urlencode('Database error. Please try again.'));
        }
    }
}

admin_redirect('announcements.php?error=' . urlencode('Please enter announcement text.'));
