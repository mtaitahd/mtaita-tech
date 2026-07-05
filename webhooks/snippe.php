<?php
/**
 * Snippe Webhook Endpoint
 *
 * Receives real-time payment event notifications:
 *   - payment.completed
 *   - payment.failed
 *   - payment.voided
 *   - payment.expired
 *
 * Security:
 *   - Verifies HMAC-SHA256 signature
 *   - Validates timestamp freshness (max 5 min old)
 *   - Uses constant-time comparison (hash_equals)
 *   - Deduplicates via event ID
 *   - Responds 2xx immediately, processes async
 *
 * API Reference: https://docs.snippe.sh/docs/2026-01-25/webhooks
 */

// Bootstrap: minimal load — fast 2xx response is priority
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';

// Read raw request body BEFORE any processing
$rawBody = file_get_contents('php://input');

// Headers
$headers = getallheaders();
$headers = array_change_key_case($headers, CASE_LOWER);

$webhookEvent   = $headers['x-webhook-event'] ?? '';
$webhookTimestamp = $headers['x-webhook-timestamp'] ?? '';
$webhookSignature = $headers['x-webhook-signature'] ?? '';
$userAgent      = $headers['user-agent'] ?? '';

// Log raw webhook for audit
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents(
    $logDir . '/webhooks_snippe.log',
    date('Y-m-d H:i:s') . " | EVENT: {$webhookEvent} | UA: {$userAgent} | BODY: " . $rawBody . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

// ================================================================
// VERIFY SIGNATURE
// ================================================================
$secret = SNIPPE_WEBHOOK_SECRET;
$signatureValid = false;

if (!empty($secret) && !empty($webhookSignature)) {
    try {
        $signatureValid = verifySnippeSignature($rawBody, $webhookTimestamp, $webhookSignature, $secret);
    } catch (Exception $e) {
        file_put_contents(
            $logDir . '/webhooks_snippe.log',
            date('Y-m-d H:i:s') . ' | Signature verification error: ' . $e->getMessage() . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
} else {
    // No secret configured — skip verification (dev mode)
    $signatureValid = true;
}

if (!$signatureValid) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Invalid signature';
    exit;
}

// ================================================================
// PARSE EVENT
// ================================================================
$event = json_decode($rawBody, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Invalid JSON';
    exit;
}

$eventId   = $event['id'] ?? '';
$eventType = $event['type'] ?? '';
$eventData = $event['data'] ?? [];

// ================================================================
// DEDUPLICATE — Respond 2xx if already processed
// ================================================================
if (!empty($eventId)) {
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE JSON_EXTRACT(webhook_payload, '$.id') = ? LIMIT 1");
    $stmt->execute([$eventId]);
    if ($stmt->fetch()) {
        // Already processed — acknowledge silently
        http_response_code(200);
        header('Content-Type: text/plain');
        echo 'OK';
        exit;
    }
}

// ================================================================
// RESPOND 2xx IMMEDIATELY — process asynchronously
// ================================================================
http_response_code(200);
header('Content-Type: text/plain');
echo 'OK';

// Flush output to client before processing
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} elseif (function_exists('ob_flush')) {
    ob_flush();
    flush();
}

// ================================================================
// PROCESS EVENT
// ================================================================
$handled = false;

try {
    $paymentService = new PaymentService($pdo);
    $handled = $paymentService->handleWebhook($event);
} catch (Exception $e) {
    file_put_contents(
        $logDir . '/webhooks_snippe.log',
        date('Y-m-d H:i:s') . ' | Process error: ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

// ================================================================
// VERIFY PAYMENT AFTER WEBHOOK
// ================================================================
// Per Snippe best practices: always verify after webhook
if ($handled && in_array($eventType, ['payment.completed', 'payment.failed'])) {
    $snippeRef = $eventData['reference'] ?? '';
    if (!empty($snippeRef)) {
        try {
            // Find our payment reference by snippe reference
            $stmt = $pdo->prepare("SELECT payment_reference FROM payments WHERE snippe_reference = ? LIMIT 1");
            $stmt->execute([$snippeRef]);
            $payment = $stmt->fetch();
            if ($payment) {
                $paymentService->verifyPayment($payment['payment_reference']);
            }
        } catch (Exception $e) {
            file_put_contents(
                $logDir . '/webhooks_snippe.log',
                date('Y-m-d H:i:s') . ' | Verification error: ' . $e->getMessage() . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }
    }
}

exit;

// ================================================================
// SIGNATURE VERIFICATION FUNCTION
// ================================================================

/**
 * Verify Snippe webhook HMAC-SHA256 signature
 *
 * Per Snippe docs:
 *   X-Webhook-Signature = hex(HMAC-SHA256(signing_key, "{timestamp}.{json_body}"))
 *
 * @param string $rawBody    Raw request body exactly as received
 * @param string $timestamp  X-Webhook-Timestamp header
 * @param string $signature  X-Webhook-Signature header
 * @param string $secret     Webhook signing key
 * @return bool
 */
function verifySnippeSignature(string $rawBody, string $timestamp, string $signature, string $secret): bool
{
    // Reject timestamps older than 5 minutes (replay attack prevention)
    $eventTime = (int)$timestamp;
    $currentTime = time();
    if ($currentTime - $eventTime > 300) {
        throw new Exception('Webhook timestamp too old (replay attack)');
    }

    // Also reject timestamps in the future (clock drift tolerance: 2 min)
    if ($eventTime > $currentTime + 120) {
        throw new Exception('Webhook timestamp is in the future');
    }

    // Construct message per Snippe spec: "{timestamp}.{raw_body}"
    $message = $timestamp . '.' . $rawBody;

    // Compute expected signature
    $expectedSignature = hash_hmac('sha256', $message, $secret);

    // Constant-time comparison using hash_equals (prevents timing attacks)
    return hash_equals($expectedSignature, $signature);
}
