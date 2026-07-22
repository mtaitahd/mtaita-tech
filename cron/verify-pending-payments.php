<?php
/**
 * Cron Job: Verify Pending Payments
 *
 * Checks all pending payments against the Snippe API and updates their status.
 * Handles cases where webhooks were missed or failed to deliver.
 *
 * Usage (run via cron every 5-10 minutes):
 *   php /path/to/cron/verify-pending-payments.php
 *
 * Or via HTTP (with IP/secret protection):
 *   https://mtaitatech.online/cron/verify-pending-payments.php?secret=YOUR_SECRET
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/PaymentService.php';

// Security: only allow CLI or secret parameter
$isCLI = php_sapi_name() === 'cli';
$secret = $_GET['secret'] ?? '';

if (!$isCLI && $secret !== env('CRON_SECRET', 'mtaita-cron-2026')) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: application/json');

$paymentService = new PaymentService($pdo);
$results = ['verified' => 0, 'activated' => 0, 'failed' => 0, 'errors' => []];

// Find all pending payments (older than 2 minutes to avoid race with webhooks)
$stmt = $pdo->prepare("
    SELECT payment_reference, snippe_reference, created_at
    FROM payments
    WHERE status = 'pending'
    AND created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)
    ORDER BY created_at ASC
    LIMIT 50
");
$stmt->execute();
$pendingPayments = $stmt->fetchAll();

if (empty($pendingPayments)) {
    $results['message'] = 'No pending payments to verify.';
    echo json_encode($results);
    exit;
}

foreach ($pendingPayments as $payment) {
    try {
        $updated = $paymentService->verifyPayment($payment['payment_reference']);
        $results['verified']++;

        if ($updated && $updated['status'] === 'completed') {
            $results['activated']++;
        }
    } catch (Exception $e) {
        $results['failed']++;
        $results['errors'][] = [
            'reference' => $payment['payment_reference'],
            'error'     => $e->getMessage(),
        ];
    }
}

$results['message'] = "Verified {$results['verified']} payments, {$results['activated']} activated, {$results['failed']} failed.";

echo json_encode($results) . PHP_EOL;

if ($isCLI) {
    echo "\n";
}
