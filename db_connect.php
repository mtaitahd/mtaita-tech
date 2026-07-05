<?php
require_once __DIR__ . '/config.php';

$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function getActiveAnnouncement($pdo) {
    $stmt = $pdo->query("SELECT announcement_text, text_color FROM announcements WHERE is_active = 1 LIMIT 1");
    return $stmt->fetch();
}

function getActiveAnnouncements($pdo) {
    $stmt = $pdo->query("SELECT id, announcement_text, text_color, badge, badge_bg FROM announcements WHERE is_active = 1 ORDER BY id ASC");
    return $stmt->fetchAll();
}
