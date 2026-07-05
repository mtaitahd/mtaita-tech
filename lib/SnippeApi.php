<?php
/**
 * SnippeApi — HTTP client for the Snippe Payments API
 *
 * API Reference: https://docs.snippe.sh/docs/2026-01-25
 * Authentication: Bearer token via Authorization header
 * Idempotency: Idempotency-Key header (max 30 chars, valid 24h)
 * Base URL: https://api.snippe.sh
 */
class SnippeApi
{
    private string $apiKey;
    private string $baseUrl;
    private string $apiVersion;
    private int $timeout;
    private ?string $lastError;
    private ?int $lastHttpCode;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?string $apiVersion = null)
    {
        $this->apiKey = $apiKey ?? SNIPPE_API_KEY;
        $this->baseUrl = rtrim($baseUrl ?? SNIPPE_API_URL, '/');
        $this->apiVersion = $apiVersion ?? SNIPPE_API_VERSION;
        $this->timeout = 30;
        $this->lastError = null;
        $this->lastHttpCode = null;
    }

    /**
     * Create a payment intent
     * POST /v1/payments
     *
     * @param array $params Payment parameters per API docs
     * @param string $idempotencyKey Unique key (max 30 chars) to prevent duplicates
     * @return array|null Decoded response or null on failure
     */
    public function createPayment(array $params, string $idempotencyKey): ?array
    {
        $response = $this->request('POST', '/v1/payments', $params, [
            'Idempotency-Key' => $idempotencyKey,
        ]);

        if ($response === null) {
            return null;
        }

        // Expect 201 Created on success
        if ($this->lastHttpCode !== 201) {
            $this->lastError = $response['message'] ?? 'Unexpected response code: ' . $this->lastHttpCode;
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * Get payment status by Snippe reference
     * GET /v1/payments/{reference}
     */
    public function getPaymentStatus(string $reference): ?array
    {
        $response = $this->request('GET', "/v1/payments/{$reference}");

        if ($response === null) {
            return null;
        }

        if ($this->lastHttpCode !== 200) {
            $this->lastError = $response['message'] ?? 'Payment not found';
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * Trigger USSD push for a mobile money payment
     * POST /v1/payments/{reference}/push
     */
    public function triggerUssdPush(string $reference): ?array
    {
        $response = $this->request('POST', "/v1/payments/{$reference}/push");

        if ($response === null) {
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * Get account balance
     * GET /v1/payments/balance
     */
    public function getBalance(): ?array
    {
        $response = $this->request('GET', '/v1/payments/balance');

        if ($response === null || $this->lastHttpCode !== 200) {
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * Search payments by reference
     * GET /v1/payments/search?reference={ref}
     */
    public function searchPayment(string $reference): ?array
    {
        $response = $this->request('GET', '/v1/payments/search?reference=' . urlencode($reference));

        if ($response === null || $this->lastHttpCode !== 200) {
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * List payments with pagination
     * GET /v1/payments?limit=N&offset=N
     */
    public function listPayments(int $limit = 20, int $offset = 0): ?array
    {
        $response = $this->request('GET', "/v1/payments?limit={$limit}&offset={$offset}");

        if ($response === null || $this->lastHttpCode !== 200) {
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * Format phone number to Snippe format (255XXXXXXXXX)
     * Strips + and leading 0, prepends 255
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        } elseif (str_starts_with($phone, '255') && strlen($phone) === 12) {
            // Already correct
        } else {
            $phone = '255' . $phone;
        }
        return $phone;
    }

    /**
     * Validate TZS amount (minimum 500)
     */
    public static function validateAmount(int $amount): ?string
    {
        if ($amount < CURRENCY_MIN_AMOUNT) {
            return 'Amount ' . $amount . ' is below minimum of ' . CURRENCY_MIN_AMOUNT . ' TZS';
        }
        return null;
    }

    /**
     * Generate a unique idempotency key (max 30 chars)
     * Format: ORD-{orderId}-{attempt}
     */
    public static function generateIdempotencyKey(string $orderId, int $attempt = 1): string
    {
        $key = 'ORD-' . $orderId . '-a' . $attempt;
        // Truncate to 30 chars max per Snippe docs
        if (strlen($key) > 30) {
            $key = substr($key, 0, 30);
        }
        return $key;
    }

    /**
     * Build customer array for API request
     */
    public static function buildCustomer(array $user): array
    {
        $nameParts = explode(' ', $user['name'] ?? 'Customer', 2);
        return [
            'firstname' => $nameParts[0] ?: 'Customer',
            'lastname'  => $nameParts[1] ?? 'Name',
            'email'     => $user['email'] ?? '',
        ];
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get last HTTP status code
     */
    public function getLastHttpCode(): ?int
    {
        return $this->lastHttpCode;
    }

    /**
     * Execute an HTTP request against the Snippe API
     */
    private function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): ?array
    {
        $url = $this->baseUrl . $path;
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        foreach ($extraHeaders as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $raw = curl_exec($ch);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            $this->lastError = $curlError ?: 'Empty response from Snippe API';
            $this->logError($this->lastError, ['url' => $url, 'method' => $method]);
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastError = 'Invalid JSON response: ' . json_last_error_msg();
            $this->logError($this->lastError, ['url' => $url, 'raw' => substr($raw, 0, 500)]);
            return null;
        }

        // Log successful API call metadata
        $this->logApiCall($method, $url, $this->lastHttpCode);

        return $decoded;
    }

    /**
     * Log API errors for debugging
     */
    private function logError(string $message, array $context = []): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        file_put_contents(
            $logDir . '/snippe_api.log',
            date('Y-m-d H:i:s') . ' | ERROR | ' . $message . $contextStr . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Log API call for audit
     */
    private function logApiCall(string $method, string $url, int $httpCode): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents(
            $logDir . '/snippe_api.log',
            date('Y-m-d H:i:s') . " | {$method} {$url} | HTTP {$httpCode}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
