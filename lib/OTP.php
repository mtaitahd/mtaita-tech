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

        $body = "Dear $name,\n\n";
        $body .= "Use the code below to complete your $label.\n\n";
        $body .= str_repeat('-', 28) . "\n";
        $body .= "    $otp\n";
        $body .= str_repeat('-', 28) . "\n\n";
        $body .= "This code expires in $expires minutes. Do NOT share this code with anyone.\n\n";
        $body .= "If you did not request this code, please ignore this message.\n\n";
        $body .= "Best regards,\nMtaita Tech\nhttps://mtaitatech.online";

        require_once __DIR__ . '/../mailer.php';
        $mailer = new Mailer();
        return $mailer->send($email, $subject, $body, false);
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
