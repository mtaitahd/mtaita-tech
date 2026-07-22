<?php
/**
 * Diagnostic: Check webhook processing status
 * Access: https://mtaitatech.online/cron/diag.php?key=mtaita-check-2026
 * DELETE THIS AFTER USE
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';

$key = $_GET['key'] ?? '';
if ($key !== 'mtaita-check-2026') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: text/plain');

echo "=== DATABASE CHECK ===\n\n";

// 1. Check all payments
echo "ALL PAYMENTS:\n";
$stmt = $pdo->query("SELECT id, payment_reference, snippe_reference, status, amount, created_at, updated_at FROM payments ORDER BY id DESC LIMIT 20");
$payments = $stmt->fetchAll();
if (empty($payments)) {
    echo "  (none found)\n";
} else {
    foreach ($payments as $p) {
        echo "  ID:{$p['id']} | ref:{$p['payment_reference']} | snippe:{$p['snippe_reference']} | status:{$p['status']} | TZS " . number_format($p['amount']) . " | {$p['created_at']}\n";
    }
}

// 2. Check for the specific snippe reference from the failed webhook
echo "\n=== LOOKING FOR SNIPPE REF: SN17847315125119988 ===\n";
$stmt = $pdo->prepare("SELECT * FROM payments WHERE snippe_reference = ?");
$stmt->execute(['SN17847315125119988']);
$found = $stmt->fetch();
if ($found) {
    echo "FOUND! Status: {$found['status']}\n";
    echo "Payment ref: {$found['payment_reference']}\n";
    echo "Webhook payload: " . ($found['webhook_payload'] ?: '(null)') . "\n";
} else {
    echo "NOT FOUND in payments table!\n";
    echo "This is why the webhook had no effect — no payment record matches this Snippe reference.\n";
}

// 3. Check pending payments
echo "\n=== PENDING PAYMENTS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'");
echo "Count: " . $stmt->fetchColumn() . "\n";

// 4. Check completed payments
echo "\n=== COMPLETED PAYMENTS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'completed'");
echo "Count: " . $stmt->fetchColumn() . "\n";

// 5. Check recent enrollments
echo "\n=== RECENT ENROLLMENTS ===\n";
$stmt = $pdo->query("SELECT e.id, e.payment_id, e.status, e.payment_reference, e.created_at FROM enrollments e ORDER BY e.id DESC LIMIT 10");
$enrollments = $stmt->fetchAll();
if (empty($enrollments)) {
    echo "  (none found)\n";
} else {
    foreach ($enrollments as $e) {
        echo "  ID:{$e['id']} | payment_id:{$e['payment_id']} | ref:{$e['payment_reference']} | status:{$e['status']} | {$e['created_at']}\n";
    }
}

// 6. Check if webhook log exists
echo "\n=== WEBHOOK LOG ===\n";
$logFile = __DIR__ . '/../logs/webhooks_snippe.log';
if (file_exists($logFile)) {
    echo "File exists. Last 10 lines:\n";
    $lines = file($logFile);
    $last10 = array_slice($lines, -10);
    foreach ($last10 as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "webhooks_snippe.log does NOT exist — webhooks may never have been received successfully.\n";
}

// 7. Check payments log
echo "\n=== PAYMENTS LOG (last 10) ===\n";
$payLog = __DIR__ . '/../logs/payments.log';
if (file_exists($payLog)) {
    $lines = file($payLog);
    $last10 = array_slice($lines, -10);
    foreach ($last10 as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "payments.log does NOT exist\n";
}

echo "\n=== DONE ===\n";
