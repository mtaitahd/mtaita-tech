<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'message' => 'Not authorized.']);
    exit;
}

$adminName = $_SESSION['admin_username'] ?? ($_SESSION['admin_email'] ?? 'admin');
$maxSize = 100 * 1024 * 1024;
$uploadDir = __DIR__ . '/../assets/uploads/products/';

function respond($ok, $message)
{
    header('Content-Type: application/json');
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'POST required.');
}

if (!isset($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'No ZIP file received.');
}

$file = $_FILES['zip_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Upload error code: ' . $file['error']);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'zip') {
    respond(false, 'Only .zip files are allowed.');
}

if ($file['size'] <= 0 || $file['size'] > $maxSize) {
    respond(false, 'File must be smaller than 100MB.');
}

$mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : $file['type'];
$allowedMimes = ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'];
if (!in_array($mime, $allowedMimes, true)) {
    respond(false, 'Invalid file type detected (' . $mime . ').');
}

$handle = fopen($file['tmp_name'], 'rb');
$header = $handle ? fread($handle, 4) : '';
if ($handle) fclose($handle);
if (!in_array($header, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true)) {
    respond(false, 'File is not a valid ZIP archive.');
}

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$htaccess = $uploadDir . '.htaccess';
if (!file_exists($htaccess)) {
    @file_put_contents($htaccess, "# Disable PHP execution in this folder\nphp_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php6 .php7 .phps .phar\nRemoveType .php .phtml .php3 .php4 .php5 .php6 .php7 .phps .phar\n<FilesMatch \"\\.(php|phtml|phar|php[0-9])$\">\n  Require all denied\n</FilesMatch>\n");
}

$newName = 'product_' . bin2hex(random_bytes(16)) . '.zip';
$dest = $uploadDir . $newName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    respond(false, 'Failed to save the file.');
}
@chmod($dest, 0644);

$logFile = __DIR__ . '/../assets/uploads/zip-upload-log.csv';
$line = date('Y-m-d H:i:s') . ',' . $adminName . ',' . $newName . PHP_EOL;
@file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

respond(true, 'Uploaded ' . $newName);
