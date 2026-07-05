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

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    admin_redirect('projects.php?error=' . urlencode('Project not found.'));
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['project_title'] ?? '');
    $desc = trim($_POST['project_desc'] ?? '');
    $link = trim($_POST['project_link'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if ($desc === '') $errors[] = 'Description is required.';
    if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) $errors[] = 'A valid URL is required.';

    $screenshot_path = $project['project_screenshot'];

    if (isset($_FILES['project_screenshot']) && $_FILES['project_screenshot']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file = $_FILES['project_screenshot'];

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $mime = $file['type'];
        }

        if (!in_array($mime, $allowed)) {
            $errors[] = 'Invalid file type.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'File is too large (max 5MB).';
        } else {
            $old_path = __DIR__ . '/../' . $project['project_screenshot'];
            if (file_exists($old_path) && is_file($old_path)) {
                @unlink($old_path);
            }

            $upload_dir = __DIR__ . '/../assets/img/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('proj_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $screenshot_path = 'assets/img/uploads/' . $filename;
            } else {
                $errors[] = 'Failed to upload new file.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE portfolio SET project_title = ?, project_desc = ?, project_link = ?, project_screenshot = ? WHERE id = ?");
            $stmt->execute([$title, $desc, $link, $screenshot_path, $id]);
            admin_redirect('projects.php?msg=' . urlencode('Project updated successfully.'));
        } catch (Exception $e) {
            error_log('edit_project DB error: ' . $e->getMessage());
            $errors[] = 'Database error. Please try again.';
        }
    }

    $error_string = implode(' ', $errors);
    admin_redirect('projects.php?error=' . urlencode($error_string));
}
