<?php
/**
 * URGENT FIX: Update webhook secret on production + manually process pending payment
 * Access: https://mtaitatech.online/cron/fix-webhook.php?key=mtaita-fix-2026
 * DELETE AFTER USE
 */

$key = $_GET['key'] ?? '';
if ($key !== 'mtaita-fix-2026') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: text/plain');

// ================================================================
// STEP 1: Fix the .env file with the real webhook secret
// ================================================================
echo "=== STEP 1: Updating .env webhook secret ===\n";

$envFile = __DIR__ . '/../.env';
$envContent = file_get_contents($envFile);

if ($envContent === false) {
    echo "ERROR: Cannot read .env file\n";
    exit;
}

$oldSecret = 'whsec_your_webhook_secret_here';
$newSecret = 'whsec_5108dec098e7499a2b86712c61327b08bfeaf329d484a9013a772d32dc3ab25b';

if (strpos($envContent, $oldSecret) !== false) {
    $envContent = str_replace(
        "SNIPPE_WEBHOOK_SECRET={$oldSecret}",
        "SNIPPE_WEBHOOK_SECRET={$newSecret}",
        $envContent
    );
    file_put_contents($envFile, $envContent);
    echo "SUCCESS: .env updated with real webhook secret\n";
} elseif (strpos($envContent, $newSecret) !== false) {
    echo "OK: .env already has the correct secret\n";
} else {
    echo "WARNING: .env has a different secret. Not overwriting.\n";
    echo "Current value starts with: " . substr(preg_replace('/.*SNIPPE_WEBHOOK_SECRET=/', '', explode("\n", $envContent)[array_search(true, array_map(fn($l) => str_contains($l, 'SNIPPE_WEBHOOK_SECRET'), explode("\n", $envContent))) ?? 0] ?? ''), 0, 10) . "...\n";
}

// ================================================================
// STEP 2: Manually process the pending payment
// ================================================================
echo "\n=== STEP 2: Processing pending payment SN17847315125119988 ===\n";

require_once $envFile;
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/PaymentService.php';

// Check if the webhook_payload column exists
$stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'webhook_payload'");
$hasColumn = $stmt->fetch();
if (!$hasColumn) {
    echo "Adding missing webhook_payload column...\n";
    $pdo->exec("ALTER TABLE payments ADD COLUMN webhook_payload JSON DEFAULT NULL AFTER transaction_data");
    echo "Column added.\n";
} else {
    echo "webhook_payload column exists.\n";
}

// Find the pending payment
$stmt = $pdo->prepare("SELECT * FROM payments WHERE snippe_reference = ? LIMIT 1");
$stmt->execute(['SN17847315125119988']);
$payment = $stmt->fetch();

if (!$payment) {
    echo "ERROR: Payment not found\n";
    exit;
}

echo "Found payment ID:{$payment['id']} | ref:{$payment['payment_reference']} | status:{$payment['status']}\n";

if ($payment['status'] === 'completed') {
    echo "Already completed! Nothing to do.\n";
    exit;
}

// Verify via Snippe API
$paymentService = new PaymentService($pdo);
$verified = $paymentService->verifyPayment($payment['payment_reference']);

if ($verified) {
    echo "After verifyPayment: status={$verified['status']}\n";
    if ($verified['status'] === 'completed') {
        echo "SUCCESS: Payment is now completed!\n";
    } else {
        echo "Payment status is: {$verified['status']}\n";
        echo "Trying manual activation...\n";
        // Force complete
        $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', completed_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$payment['id']]);
        $paymentService->activateService($payment);
        echo "Manual activation done.\n";
    }
} else {
    echo "verifyPayment returned null: " . ($paymentService->getLastError() ?? 'unknown') . "\n";
    echo "Trying manual activation...\n";
    $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', completed_at = NOW(), updated_at = NOW() WHERE id = ?");
    $stmt->execute([$payment['id']]);
    $paymentService->activateService($payment);
    echo "Manual activation done.\n";
}

// Verify final state
$stmt = $pdo->prepare("SELECT status, webhook_payload FROM payments WHERE id = ?");
$stmt->execute([$payment['id']]);
$final = $stmt->fetch();
echo "\nFinal status: {$final['status']}\n";

echo "\n=== DONE ===\n";
echo "DELETE THIS FILE AFTER USE: cron/fix-webhook.php\n";
