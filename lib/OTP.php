<?php
class OTP {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function generate($length = 6) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    public function create($userId, $type) {
        $this->pdo->prepare("UPDATE user_otps SET used = 1 WHERE user_id = ? AND type = ? AND used = 0")->execute([$userId, $type]);

        $otp = $this->generate();
        $expiry = $type === 'reset' ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $this->pdo->prepare("INSERT INTO user_otps (user_id, type, otp, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $type, $otp, $expiry]);
        return $otp;
    }

    public function sendEmail($email, $name, $otp, $type) {
        $subject = 'Your Mtaita Tech Verification Code';
        $typeLabels = ['verify' => 'email verification', 'login' => 'login', 'reset' => 'password reset'];
        $label = $typeLabels[$type] ?? 'verification';
        $expires = $type === 'reset' ? '15' : '10';
        $safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $body = '<div style="font-family:Arial,sans-serif;color:#333;max-width:500px;">';
        $body .= '<p style="margin:0 0 4px;">Dear <strong>' . $safe . '</strong>,</p>';
        $body .= '<p style="margin:0 0 16px;">Use the code below to complete your ' . htmlspecialchars($label) . '.</p>';
        $body .= '<div style="background:#f4f4f5;border-radius:8px;padding:16px;text-align:center;font-size:32px;font-weight:700;letter-spacing:8px;color:#dc2626;font-family:monospace;margin:0 0 16px;">' . htmlspecialchars($otp) . '</div>';
        $body .= '<p style="margin:0 0 4px;font-size:13px;color:#666;">This code expires in <strong>' . $expires . ' minutes</strong>. Do NOT share this code with anyone.</p>';
        $body .= '<p style="margin:0 0 16px;font-size:13px;color:#666;">If you did not request this code, please ignore this message.</p>';
        $body .= '<hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0;">';
        $body .= '<p style="margin:0;font-size:12px;color:#94a3b8;">Best regards,<br><strong>Mtaita Tech</strong><br><a href="https://mtaitatech.online" style="color:#dc2626;text-decoration:none;">https://mtaitatech.online</a></p>';
        $body .= '</div>';

        require_once __DIR__ . '/../mailer.php';
        $mailer = new Mailer();
        return $mailer->send($email, $subject, $body, true);
    }

    public function sendSms($phone, $otp, $type) {
        require_once __DIR__ . '/SMS.php';
        $sms = new SMS();
        return $sms->sendOTP($phone, $otp, $type);
    }

    public function sendUserOTP($userId, $type, $method = 'email') {
        $stmt = $this->pdo->prepare("SELECT id, name, email, phone, otp_preference FROM public_users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) return false;

        if ($method === 'auto') {
            $method = $user['otp_preference'] ?? 'email';
        }

        $otp = $this->create($userId, $type);

        if ($method === 'sms') {
            if (!empty($user['phone'])) {
                return $this->sendSms($user['phone'], $otp, $type);
            }
            return false;
        }

        return $this->sendEmail($user['email'], $user['name'], $otp, $type);
    }

    public function verify($userId, $inputOtp, $type) {
        $stmt = $this->pdo->prepare("
            SELECT id, otp, expires_at FROM user_otps
            WHERE user_id = ? AND type = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId, $type]);
        $row = $stmt->fetch();
        if (!$row) return false;

        if (hash_equals($row['otp'], $inputOtp)) {
            $this->pdo->prepare("UPDATE user_otps SET used = 1 WHERE id = ?")->execute([$row['id']]);
            $this->cleanup();
            return true;
        }
        return false;
    }

    private function cleanup() {
        $this->pdo->exec("DELETE FROM user_otps WHERE expires_at < NOW() OR used = 1");
    }
}
