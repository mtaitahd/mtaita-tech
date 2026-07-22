<?php
/**
 * CLEANUP: Delete diagnostic and fix scripts from server
 * Access: https://mtaitatech.online/cron/cleanup.php?key=mtaita-clean-2026
 * DELETE THIS FILE AFTER USE
 */

$key = $_GET['key'] ?? '';
if ($key !== 'mtaita-clean-2026') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: text/plain');

$files = [
    __DIR__ . '/diag.php',
    __DIR__ . '/fix-webhook.php',
    __DIR__ . '/cleanup.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Deleted: " . basename($file) . "\n";
    } else {
        echo "Not found: " . basename($file) . "\n";
    }
}

echo "\nDone. All diagnostic files removed.\n";
