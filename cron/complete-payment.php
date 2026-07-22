<?php
/**
 * URGENT: Manually complete a stuck payment
 * Access: https://mtaitatech.online/cron/complete-payment.php?key=mtaita-fix&ref=PAY-6711AFCF40011AA2
 * DELETE AFTER USE
 */

$key = $_GET['key'] ?? '';
$ref = $_GET['ref'] ?? '';
if ($key !== 'mtaita-fix' || empty($ref)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/PaymentService.php';

header('Content-Type: text/plain');

$stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_reference = ? LIMIT 1");
$stmt->execute([$ref]);
$payment = $stmt->fetch();

if (!$payment) {
    echo "Payment not found: {$ref}\n";
    exit;
}

echo "Payment ID: {$payment['id']}\n";
echo "Status: {$payment['status']}\n";
echo "Amount: TZS " . number_format($payment['amount']) . "\n";
echo "Snippe ref: {$payment['snippe_reference']}\n\n";

if ($payment['status'] === 'completed') {
    echo "Already completed!\n";
    exit;
}

$paymentService = new PaymentService($pdo);

// Try verifyPayment first (calls Snippe API)
echo "Verifying with Snippe API...\n";
$verified = $paymentService->verifyPayment($ref);

if ($verified && $verified['status'] === 'completed') {
    echo "SUCCESS via API! Status: completed\n";
} else {
    echo "API returned: " . ($verified['status'] ?? 'null') . " — forcing completion\n";
    $paymentService->adminMarkCompleted($ref);
    
    // Re-fetch to confirm
    $stmt = $pdo->prepare("SELECT status FROM payments WHERE payment_reference = ?");
    $stmt->execute([$ref]);
    $final = $stmt->fetch();
    echo "Final status: {$final['status']}\n";
}

echo "\nDone. Go back to payment-pending page — it should redirect to success.\n";
echo "DELETE THIS FILE AFTER USE\n";
