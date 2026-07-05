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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['project_title'] ?? '');
    $desc = trim($_POST['project_desc'] ?? '');
    $link = trim($_POST['project_link'] ?? '');

    if ($title === '') $errors[] = 'Project title is required.';
    if ($desc === '') $errors[] = 'Project description is required.';
    if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) $errors[] = 'A valid project URL is required.';

    if (empty($errors) && isset($_FILES['project_screenshot']) && $_FILES['project_screenshot']['error'] === UPLOAD_ERR_OK) {
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
            $errors[] = 'Invalid file type. Only PNG, JPG, WEBP, and GIF are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'File is too large. Maximum size is 5MB.';
        } else {
            $upload_dir = __DIR__ . '/../assets/img/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('proj_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO portfolio (project_title, project_desc, project_link, project_screenshot) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $desc, $link, 'assets/img/uploads/' . $filename]);
                    admin_redirect('projects.php?msg=' . urlencode('Project uploaded successfully.'));
                } catch (Exception $e) {
                    error_log('add_project DB error: ' . $e->getMessage());
                    $errors[] = 'Database error. Please try again.';
                }
            } else {
                $errors[] = 'Failed to move uploaded file. Please check directory permissions.';
            }
        }
    } elseif (empty($errors)) {
        $errors[] = 'Please select a screenshot file.';
    }
}

$error_string = implode(' ', $errors);
admin_redirect('projects.php?error=' . urlencode($error_string));
