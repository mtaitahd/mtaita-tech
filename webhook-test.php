<?php
/**
 * Webhook Test — Simulate a payment completion for testing
 *
 * POST /webhook-test.php
 *   ref=PAY-XXXXX  (our internal payment reference)
 *
 * Creates a fake Snippe webhook event and runs it through
 * the same PaymentService::handleWebhook() + verifyPayment()
 * pipeline that a real webhook would use.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/services/PaymentService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST required.']);
    exit;
}

if (!isset($_SESSION['admin_logged_in']) && ($app_env ?? 'production') !== 'development') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Admin or dev mode only.']);
    exit;
}

$ref = trim($_POST['ref'] ?? '');
if (empty($ref)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing ref parameter.']);
    exit;
}

// Find payment by our internal reference
$stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_reference = ? LIMIT 1");
$stmt->execute([$ref]);
$payment = $stmt->fetch();

if (!$payment) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Payment not found: ' . $ref]);
    exit;
}

if ($payment['status'] === 'completed') {
    echo json_encode(['status' => 'success', 'message' => 'Payment already completed.', 'reference' => $ref]);
    exit;
}

// Build a fake webhook event matching Snippe's format
$fakeEvent = [
    'id'   => 'evt_test_' . bin2hex(random_bytes(8)),
    'type' => 'payment.completed',
    'data' => [
        'reference' => $payment['snippe_reference'],
        'status'    => 'completed',
        'amount'    => (int)$payment['amount'],
        'currency'  => $payment['currency'],
        'metadata'  => json_decode($payment['metadata'] ?? '{}', true),
    ],
];

try {
    $paymentService = new PaymentService($pdo);

    // Step 1: Process webhook
    $handled = $paymentService->handleWebhook($fakeEvent);

    if (!$handled) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => $paymentService->getLastError() ?? 'Webhook processing failed',
        ]);
        exit;
    }

    // Step 2: Verify the payment was actually updated (read from DB, don't call API)
    $verified = $paymentService->getPaymentByReference($ref);

    if ($verified && $verified['status'] === 'completed') {
        echo json_encode([
            'status'    => 'success',
            'message'   => 'Payment simulated successfully.',
            'reference' => $ref,
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Verification failed. Status: ' . ($verified['status'] ?? 'unknown'),
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}
