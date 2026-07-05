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

    public function sendEmail($email, $otp, $type) {
        $subject = 'Your Mtaita Tech Verification Code';
        $typeLabels = ['verify' => 'email verification', 'login' => 'login', 'reset' => 'password reset'];
        $label = $typeLabels[$type] ?? 'verification';

        require_once __DIR__ . '/../email_template.php';

        $bodyHtml = '
            <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.6;">Use this code to complete your <strong>' . $label . '</strong>.</p>
            <div style="background:#F1F5F9;border-radius:10px;padding:20px;text-align:center;font-size:36px;font-weight:700;letter-spacing:10px;color:#DC2626;font-family:monospace;">' . $otp . '</div>
            <p style="margin:20px 0 0;font-size:13px;color:#94a3b8;">This code expires in ' . ($type === 'reset' ? '15' : '10') . ' minutes. Do not share this code with anyone.</p>';

        $message = buildEmailHtml('Verification Code', $bodyHtml);

        require_once __DIR__ . '/../mailer.php';
        $mailer = new Mailer();
        return $mailer->send($email, $subject, $message, true);
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

        return $this->sendEmail($user['email'], $otp, $type);
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
