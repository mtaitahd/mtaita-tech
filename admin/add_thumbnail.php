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
    $title = trim($_POST['thumbnail_title'] ?? '');
    $category = trim($_POST['video_category'] ?? '');

    if ($title === '') $errors[] = 'Thumbnail title is required.';

    if (empty($errors) && isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['thumbnail_image'];

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $mime = $file['type'];
        }

        if (!in_array($mime, $allowed)) {
            $errors[] = 'Invalid file type. Only PNG, JPG, and WEBP are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'File is too large. Maximum size is 5MB.';
        } else {
            $upload_dir = __DIR__ . '/../assets/img/uploads/thumbnails/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('thumb_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO thumbnails (thumbnail_title, thumbnail_image_path, video_category) VALUES (?, ?, ?)");
                    $stmt->execute([$title, 'assets/img/uploads/thumbnails/' . $filename, $category ?: null]);
                    admin_redirect('thumbnails.php?msg=' . urlencode('Thumbnail uploaded successfully.'));
                } catch (Exception $e) {
                    error_log('add_thumbnail DB error: ' . $e->getMessage());
                    $errors[] = 'Database error. Please try again.';
                }
            } else {
                $errors[] = 'Failed to move uploaded file. Please check directory permissions.';
            }
        }
    } elseif (empty($errors)) {
        $errors[] = 'Please select a thumbnail image.';
    }
}

$error_string = implode(' ', $errors);
admin_redirect('thumbnails.php?error=' . urlencode($error_string));
