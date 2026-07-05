<?php
/**
 * PaymentService — Payment lifecycle business logic
 *
 * Follows the Snippe payment lifecycle:
 * 1. Create payment intent → save to DB → process based on type
 * 2. Customer completes payment (USSD/redirect/QR)
 * 3. Webhook received → verify → activate service
 *
 * NEVER trust frontend success responses.
 * NEVER grant access before webhook + verification.
 */

require_once __DIR__ . '/../lib/SnippeApi.php';

class PaymentService
{
    private PDO $pdo;
    private SnippeApi $api;
    private ?string $lastError;

    public function __construct(?PDO $pdo = null)
    {
        global $pdo;
        $this->pdo = $pdo ?? $GLOBALS['pdo'];
        $this->api = new SnippeApi();
        $this->lastError = null;
    }

    /**
     * STEP 1 & 2: Create payment intent + save to database
     *
     * @param array $user  Customer data from getPublicUser()
     * @param array $item  Product/course data (id, title, price, type, slug)
     * @param string $paymentType  'mobile'
     * @param string $phone  Customer phone (for mobile money)
     * @param string $itemType  'course' or 'product'
     * @return array|null  Payment record array or null on failure
     */
    public function createPayment(array $user, array $item, string $paymentType, string $phone = '', string $itemType = 'course'): ?array
    {
        $this->lastError = null;

        if ($paymentType !== 'mobile') {
            $this->lastError = 'Only mobile money payments are supported.';
            return null;
        }

        // Validate amount
        $amount = (int)($item['price'] ?? 0);
        $amountError = SnippeApi::validateAmount($amount);
        if ($amountError) {
            $this->lastError = $amountError;
            return null;
        }

        // Validate phone for mobile money
        if ($paymentType === 'mobile') {
            $phone = SnippeApi::formatPhone($phone);
            if (!preg_match('/^255[0-9]{9}$/', $phone)) {
                $this->lastError = 'Invalid phone number. Must be a valid Tanzanian number.';
                return null;
            }
        }

        // Generate order ID and idempotency key
        $orderId = 'ORD-' . strtoupper(bin2hex(random_bytes(6)));
        $idempotencyKey = SnippeApi::generateIdempotencyKey($orderId);
        if (strlen($idempotencyKey) > 30) {
            // Truncate if still too long (per Snippe API rules)
            $idempotencyKey = substr($idempotencyKey, 0, 30);
        }

        // Build API request body per Snippe docs
        $apiParams = [
            'payment_type' => $paymentType,
            'details'      => [
                'amount'   => $amount,
                'currency' => CURRENCY,
            ],
            'customer'     => SnippeApi::buildCustomer($user),
            'metadata'     => [
                'order_id'  => $orderId,
                'item_id'   => (string)$item['id'],
                'item_type' => $itemType,
            ],
        ];

        // Use production webhook URL (Snippe requires HTTPS, rejects localhost)
        $apiParams['webhook_url'] = 'https://mtaitatech.online/webhooks/snippe.php';

        // Add phone for mobile money
        if ($paymentType === 'mobile') {
            $apiParams['phone_number'] = $phone;
        }

        // Generate our internal reference (before URLs so we can include it)
        $paymentReference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));

        // CALL SNIPPE API — POST /v1/payments
        $apiResult = $this->api->createPayment($apiParams, $idempotencyKey);

        if ($apiResult === null) {
            $this->lastError = 'Snippe API error: ' . ($this->api->getLastError() ?? 'Unknown error');
            return null;
        }

        // Extract Snippe reference (UUID)
        $snippeReference = $apiResult['reference'] ?? '';
        if (empty($snippeReference)) {
            $this->lastError = 'Snippe API returned no reference';
            return null;
        }

        // Extract response fields
        $expiresAt   = $apiResult['expires_at'] ?? null;

        // STEP 2: Save payment record in database
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO payments (order_id, payment_reference, snippe_reference, payment_type,
                    amount, currency, customer_name, customer_email, customer_phone,
                    status, transaction_data, idempotency_key, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)
            ");

            $customerName = ($apiParams['customer']['firstname'] ?? '') . ' ' . ($apiParams['customer']['lastname'] ?? '');
            $stmt->execute([
                $orderId,
                $paymentReference,
                $snippeReference,
                $paymentType,
                $amount,
                CURRENCY,
                trim($customerName) ?: null,
                $user['email'] ?? null,
                $phone ?: null,
                json_encode($apiResult),
                $idempotencyKey,
                json_encode($apiParams['metadata']),
            ]);

            $paymentId = $this->pdo->lastInsertId();
        } catch (Exception $e) {
            $this->lastError = 'Database error while saving payment: ' . $e->getMessage();
            $this->logError($this->lastError, $apiParams);
            return null;
        }

        // Return complete payment record
        return $this->getPaymentByReference($paymentReference);
    }

    /**
     * STEP 5: Verify payment status with Snippe API
     * GET /v1/payments/{reference}
     *
     * Uses the Snippe reference (UUID) to check real status.
     * Never trust what the frontend says.
     */
    public function verifyPayment(string $paymentReference): ?array
    {
        $payment = $this->getPaymentByReference($paymentReference);
        if (!$payment) {
            $this->lastError = 'Payment not found: ' . $paymentReference;
            return null;
        }

        $snippeReference = $payment['snippe_reference'];
        if (empty($snippeReference)) {
            $this->lastError = 'No Snippe reference for this payment';
            return null;
        }

        // Call Snippe API to verify status
        $statusData = $this->api->getPaymentStatus($snippeReference);
        if ($statusData === null) {
            $this->lastError = 'Failed to verify payment: ' . ($this->api->getLastError() ?? 'Unknown error');
            return null;
        }

        $snippeStatus = $statusData['status'] ?? 'pending';

        // Map Snippe status to our status
        $newStatus = $this->mapSnippeStatus($snippeStatus);

        // If status changed, update DB
        if ($newStatus !== $payment['status']) {
            $this->updatePaymentStatus($payment['id'], $newStatus, $snippeStatus);

            // STEP 6: If completed, activate service
            if ($newStatus === 'completed') {
                $this->activateService($payment);
            }
        }

        return $this->getPaymentByReference($paymentReference);
    }

    /**
     * STEP 4: Handle webhook event
     *
     * Called from webhooks/snippe.php after signature verification.
     * Returns 2xx immediately, processes asynchronously.
     */
    public function handleWebhook(array $event): bool
    {
        $eventType = $event['type'] ?? '';
        $eventData = $event['data'] ?? [];
        $snippeReference = $eventData['reference'] ?? '';
        $apiStatus = $eventData['status'] ?? '';

        if (empty($snippeReference)) {
            $this->lastError = 'Webhook missing reference';
            return false;
        }

        // Find payment by Snippe reference
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE snippe_reference = ? LIMIT 1");
        $stmt->execute([$snippeReference]);
        $payment = $stmt->fetch();

        if (!$payment) {
            // Unknown reference — log but don't reject (may be for different system)
            $this->logError('Webhook for unknown reference: ' . $snippeReference, $eventData);
            return false;
        }

        // Store webhook payload
        $stmt = $this->pdo->prepare("UPDATE payments SET webhook_payload = ? WHERE id = ?");
        $stmt->execute([json_encode($event), $payment['id']]);

        // Map and update status
        $newStatus = $this->mapSnippeStatus($apiStatus);

        if ($newStatus !== $payment['status']) {
            $this->updatePaymentStatus($payment['id'], $newStatus, $apiStatus);

            // Activate or deactivate service based on status
            if ($newStatus === 'completed') {
                $this->activateService($payment);
            } elseif (in_array($newStatus, ['failed', 'voided', 'expired'])) {
                $this->deactivateService($payment);
            }
        }

        return true;
    }

    /**
     * Check if a payment is ready for service activation
     * Both webhook received AND verification confirm completed
     */
    public function isPaymentCompleted(string $paymentReference): bool
    {
        $payment = $this->getPaymentByReference($paymentReference);
        if (!$payment) {
            return false;
        }

        return $payment['status'] === 'completed';
    }

    /**
     * Get payment record by our internal reference
     */
    public function getPaymentByReference(string $reference): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE payment_reference = ? LIMIT 1");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();
        return $payment ?: null;
    }

    /**
     * Get enrollment for a payment
     */
    public function getEnrollmentForPayment(int $paymentId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT e.* FROM enrollments e WHERE e.payment_id = ? LIMIT 1");
        $stmt->execute([$paymentId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get last error
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Admin: manually mark a payment as completed
     */
    public function adminMarkCompleted(string $paymentReference): bool
    {
        $payment = $this->getPaymentByReference($paymentReference);
        if (!$payment) {
            $this->lastError = 'Payment not found';
            return false;
        }
        if ($payment['status'] === 'completed') {
            $this->lastError = 'Payment already completed';
            return false;
        }
        $this->updatePaymentStatus($payment['id'], 'completed', 'manual');
        $this->activateService($payment);
        return true;
    }

    /**
     * Get SnippeApi instance (for direct access if needed)
     */
    public function getApi(): SnippeApi
    {
        return $this->api;
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    /**
     * Map Snippe API status to our internal status
     */
    private function mapSnippeStatus(string $snippeStatus): string
    {
        $map = [
            'pending'   => 'pending',
            'completed' => 'completed',
            'failed'    => 'failed',
            'voided'    => 'voided',
            'expired'   => 'expired',
        ];
        return $map[$snippeStatus] ?? 'pending';
    }

    /**
     * Update payment status in database with timestamp
     */
    private function updatePaymentStatus(int $paymentId, string $newStatus, string $snippeStatus): void
    {
        $completedAt = $newStatus === 'completed' ? date('Y-m-d H:i:s') : null;

        $stmt = $this->pdo->prepare("
            UPDATE payments
            SET status = ?,
                updated_at = NOW(),
                completed_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $completedAt, $paymentId]);

        $this->logEvent('Payment ' . $newStatus, [
            'payment_id'     => $paymentId,
            'snippe_status'  => $snippeStatus,
            'new_status'     => $newStatus,
        ]);
    }

    /**
     * STEP 6: Activate service — mark enrollment active, grant access
     *
     * Called only after:
     *   - Webhook received AND
     *   - Payment verification completed
     */
    private function activateService(array $payment): void
    {
        $metadata = json_decode($payment['metadata'] ?? '{}', true);
        $itemId   = (int)($metadata['item_id'] ?? 0);
        $itemType = $metadata['item_type'] ?? 'course';

        if ($itemId <= 0) {
            $this->logError('Cannot activate: missing item_id in metadata', $payment);
            return;
        }

        // For products, payment completion is enough (access via ref token)
        if ($itemType === 'product') {
            $this->logEvent('Product payment completed', [
                'payment_id' => $payment['id'],
                'product_id' => $itemId,
            ]);
            return;
        }

        // For courses: find or create enrollment
        $stmt = $this->pdo->prepare("
            SELECT id FROM enrollments
            WHERE payment_id = ? LIMIT 1
        ");
        $stmt->execute([$payment['id']]);
        $enrollment = $stmt->fetch();

        if ($enrollment) {
            // Update existing enrollment to active
            $stmt = $this->pdo->prepare("
                UPDATE enrollments SET status = 'active', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$enrollment['id']]);
        } else {
            // Create enrollment (should not happen normally, but handle gracefully)
            $userStmt = $this->pdo->prepare("
                SELECT id FROM public_users WHERE email = ? LIMIT 1
            ");
            $userStmt->execute([$payment['customer_email']]);
            $user = $userStmt->fetch();

            if ($user) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO enrollments (payment_id, user_id, item_type, item_id, payment_reference, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$payment['id'], $user['id'], $itemType, $itemId, $payment['payment_reference']]);
            }
        }

        $this->logEvent('Service activated', [
            'payment_id' => $payment['id'],
            'item_id'    => $itemId,
            'item_type'  => $itemType,
        ]);
    }

    /**
     * Deactivate service when payment fails/expires
     */
    private function deactivateService(array $payment): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE enrollments e
            JOIN payments p ON e.payment_id = p.id
            SET e.status = 'cancelled', e.updated_at = NOW()
            WHERE p.id = ?
        ");
        $stmt->execute([$payment['id']]);
    }

    /**
     * Log event for audit trail
     */
    private function logEvent(string $message, array $context = []): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        file_put_contents(
            $logDir . '/payments.log',
            date('Y-m-d H:i:s') . ' | ' . $message . $contextStr . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Log error
     */
    private function logError(string $message, array $context = []): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        file_put_contents(
            $logDir . '/payments_error.log',
            date('Y-m-d H:i:s') . ' | ' . $message . $contextStr . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
