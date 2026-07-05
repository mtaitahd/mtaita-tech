<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/config.php';

$input = file_get_contents('php://input');
$payload = json_decode($input, true);

$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$log_file = $log_dir . '/webhook.log';
$log_entry = date('Y-m-d H:i:s') . ' | ' . $input . PHP_EOL;
file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

// Test mode: ?test=1 with POST param ref=PAY-XXX
if (isset($_GET['test']) && $_GET['test'] === '1') {
    $test_ref = trim($_POST['ref'] ?? '');

    if (empty($test_ref)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing ref parameter.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'active' WHERE payment_reference = ? AND status = 'pending'");
    $stmt->execute([$test_ref]);

    if ($stmt->rowCount() > 0) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . ' | TEST activated: ' . $test_ref . PHP_EOL, FILE_APPEND | LOCK_EX);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Enrollment activated.', 'reference' => $test_ref]);
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Enrollment not found or already active.', 'reference' => $test_ref]);
    }
    exit;
}

if ($payload === null) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload.']);
    exit;
}

$event = $payload['event'] ?? $payload['status'] ?? '';

if ($event === 'payment.completed' || $event === 'completed' || $event === 'success') {
    $reference = $payload['reference'] ?? $payload['transaction_id'] ?? $payload['order_id'] ?? '';

    if (empty($reference)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing payment reference.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'active' WHERE payment_reference = ? AND status = 'pending'");
    $stmt->execute([$reference]);

    if ($stmt->rowCount() > 0) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . ' | Activated: ' . $reference . PHP_EOL, FILE_APPEND | LOCK_EX);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Enrollment activated.']);
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ignored', 'message' => 'Enrollment not found or already active.']);
    }
} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'received', 'message' => 'Event not processed.']);
}
