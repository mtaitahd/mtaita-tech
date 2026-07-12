<?php
require_once __DIR__ . '/Settings.php';

class SMS {
    private $apiKey;
    private $senderId;
    private $baseUrl = 'https://meseji.co.tz/api/v1';

    public function __construct() {
        $this->apiKey = defined('MESEJI_API_KEY') ? MESEJI_API_KEY : '';
        $this->senderId = defined('MESEJI_SENDER_ID') ? MESEJI_SENDER_ID : 'MTAITATECH';

        if (class_exists('Settings')) {
            $this->apiKey = Settings::get('meseji_api_key', $this->apiKey);
            $dbSenderId = Settings::get('meseji_sender_id', '');
            if ($dbSenderId !== '') {
                $this->senderId = $dbSenderId;
            }
        }
    }

    public function send($phone, $message) {
        $phone = $this->formatPhone($phone);
        if (!$phone) return false;

        $url = $this->baseUrl . '/sms/send';
        $data = [
            'sender_id' => $this->senderId,
            'message' => $message,
            'contacts' => $phone
        ];

        $result = $this->httpPost($url, $data);
        return $this->isSuccess($result);
    }

    public function sendBulk($phones, $message) {
        if (empty($phones)) return false;

        $contacts = [];
        foreach ($phones as $phone) {
            $formatted = $this->formatPhone($phone);
            if ($formatted) $contacts[] = $formatted;
        }
        if (empty($contacts)) return false;

        $url = $this->baseUrl . '/sms/send';
        $data = [
            'sender_id' => $this->senderId,
            'message' => $message,
            'contacts' => implode(', ', $contacts)
        ];

        $result = $this->httpPost($url, $data);
        return $this->isSuccess($result);
    }

    public function getAccountStats() {
        $url = $this->baseUrl . '/sms/user-stats';
        return $this->httpGet($url);
    }

    public function getBatchStats($batchId) {
        $url = $this->baseUrl . '/sms/stats/' . $batchId;
        return $this->httpGet($url);
    }

    public function getSenderIds() {
        $url = $this->baseUrl . '/sms/sender-ids';
        return $this->httpGet($url);
    }

    public function getHistory() {
        $url = $this->baseUrl . '/sms/history';
        return $this->httpGet($url);
    }

    public function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            return '255' . substr($phone, 1);
        }
        if (strlen($phone) === 12 && substr($phone, 0, 3) === '255') {
            return $phone;
        }
        if (strlen($phone) === 13 && substr($phone, 0, 4) === '0255') {
            return substr($phone, 1);
        }
        return null;
    }

    private $lastResponse;
    private $lastHttpCode;

    public function getLastResponse() { return $this->lastResponse; }
    public function getLastHttpCode() { return $this->lastHttpCode; }

    private function httpPost($url, $data) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->lastResponse = $response;
        $this->lastHttpCode = $httpCode;

        if ($error) {
            error_log("SMS API HTTP error: $error");
            return false;
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && $decoded) {
            return $decoded;
        }

        error_log("SMS API error (HTTP $httpCode) to $url: $response | payload: " . json_encode($data));
        return false;
    }

    private function httpGet($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("SMS API HTTP error: $error");
            return false;
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && $decoded) {
            return $decoded;
        }

        error_log("SMS API error (HTTP $httpCode): $response");
        return false;
    }

    public function sendOTP($phone, $otp, $type, $name = '') {
        $typeLabels = ['verify' => 'verification', 'login' => 'login', 'reset' => 'password reset'];
        $label = $typeLabels[$type] ?? 'verification';
        $mins = $type === 'reset' ? '15' : '10';
        $greeting = $name ? "Dear $name" : "Dear User";
        $message = "$greeting: Your verification code is $otp. Use this code to complete your $label. Code expires in $mins minutes.\nRegards By MtaitaTech";
        return $this->send($phone, $message);
    }

    private function isSuccess($result) {
        if (!$result) return false;
        if (isset($result['status']) && in_array($result['status'], ['queued', 'sent', 'success', 'completed', 'pending'])) return true;
        if (isset($result['success']) && $result['success']) return true;
        return false;
    }
}
