<?php
/**
 * AJAX endpoint — check payment/enrollment status by payment reference
 *
 * GET /check-payment-status.php?ref=PAY-XXXXX
 * Returns JSON with payment status from source-of-truth.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

$reference = trim($_GET['ref'] ?? '');
if (empty($reference)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing reference.']);
    exit;
}

// Check payments table first (source of truth)
$stmt = $pdo->prepare("SELECT status, payment_reference FROM payments WHERE payment_reference = ? LIMIT 1");
$stmt->execute([$reference]);
$payment = $stmt->fetch();

// Fall back to enrollments table
$stmt = $pdo->prepare("SELECT status FROM enrollments WHERE payment_reference = ? LIMIT 1");
$stmt->execute([$reference]);
$enrollment = $stmt->fetch();

header('Content-Type: application/json');

if (!$payment && !$enrollment) {
    echo json_encode(['status' => 'not_found']);
    exit;
}

// Determine status: payment status takes precedence
$status = 'pending';
if ($payment) {
    $status = $payment['status'];
    // Map completed to active for frontend consistency
    if ($status === 'completed') {
        $status = 'active';
    }
} elseif ($enrollment) {
    $status = $enrollment['status'];
}

echo json_encode(['status' => $status, 'reference' => $reference]);
