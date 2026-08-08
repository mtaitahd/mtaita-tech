<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($type === 'modules' && $id > 0) {
    $stmt = $pdo->prepare("SELECT id, title FROM modules WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($type === 'lessons' && $id > 0) {
    $stmt = $pdo->prepare("
        SELECT l.id, l.title, l.module_id, m.title AS module_title
        FROM lessons l LEFT JOIN modules m ON l.module_id = m.id
        WHERE l.module_id = ?
        ORDER BY l.sort_order ASC, l.id ASC
    ");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($type === 'courses') {
    $stmt = $pdo->query("SELECT id, title, type, status FROM courses ORDER BY title ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

echo json_encode([]);
