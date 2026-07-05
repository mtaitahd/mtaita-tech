<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Settings.php';

class Mailer
{
    private $host;
    private $port;
    private $encryption;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $replyTo;

    public function __construct()
    {
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $this->port = defined('SMTP_PORT') ? SMTP_PORT : 465;
        $this->encryption = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'ssl';
        $this->username = defined('SMTP_USER') ? SMTP_USER : '';
        $this->password = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->fromEmail = defined('FROM_EMAIL') ? FROM_EMAIL : '';
        $this->fromName = defined('FROM_NAME') ? FROM_NAME : 'Mtaita Tech';

        if (class_exists('Settings')) {
            $this->host = Settings::get('smtp_host', $this->host);
            $this->port = (int)Settings::get('smtp_port', $this->port);
            $this->encryption = Settings::get('smtp_encryption', $this->encryption);
            $this->username = Settings::get('smtp_user', $this->username);
            $this->password = Settings::get('smtp_pass', $this->password);
            $this->fromEmail = Settings::get('from_email', $this->fromEmail);
            $this->fromName = Settings::get('from_name', $this->fromName);
        }
    }

    public function setFrom($email, $name = '')
    {
        $this->fromEmail = $email;
        if ($name) $this->fromName = $name;
    }

    public function setReplyTo($email)
    {
        $this->replyTo = $email;
    }

    public function send($to, $subject, $body, $isHtml = false)
    {
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $scheme = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $remote = $scheme . '://' . $this->host . ':' . $this->port;

        $socket = @stream_socket_client($remote, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $ctx);
        if (!$socket) {
            error_log("Mailer: connection failed to $remote ($errno) $errstr");
            return false;
        }

        $log = function ($msg) { error_log("Mailer: $msg"); };

        $r = $this->read($socket);
        if (!$r) { $log("no banner"); fclose($socket); return false; }

        $this->write($socket, "EHLO mtaita-tech");
        $r = $this->read($socket);
        if (!$r) { $log("ehlo failed"); fclose($socket); return false; }

        $this->write($socket, "AUTH LOGIN");
        $r = $this->read($socket);
        if (!$r) { $log("auth login failed"); fclose($socket); return false; }

        $this->write($socket, base64_encode($this->username));
        $r = $this->read($socket);
        if (!$r) { $log("username rejected"); fclose($socket); return false; }

        $this->write($socket, base64_encode($this->password));
        $response = $this->read($socket);
        if (strpos($response, '235') === false && strpos($response, '334') === false) {
            $log("auth failed: $response");
            fclose($socket);
            return false;
        }

        $this->write($socket, "MAIL FROM:<{$this->fromEmail}>");
        $r = $this->read($socket);
        if (!$r) { $log("mail from failed"); fclose($socket); return false; }

        $recipients = is_array($to) ? $to : [$to];
        foreach ($recipients as $rEmail) {
            $this->write($socket, "RCPT TO:<$rEmail>");
            $r = $this->read($socket);
            if (!$r) {
                $log("rcpt to <$rEmail> failed");
                fclose($socket);
                return false;
            }
        }

        $this->write($socket, "DATA");
        $r = $this->read($socket);
        if (!$r) { $log("data command failed"); fclose($socket); return false; }

        $recipientList = implode(', ', $recipients);
        $contentType = $isHtml ? 'text/html' : 'text/plain';
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "To: $recipientList\r\n";
        $headers .= "Reply-To: " . ($this->replyTo ?: $this->fromEmail) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: $contentType; charset=UTF-8\r\n";
        $headers .= "Message-ID: <" . time() . '.' . uniqid() . '@' . $this->host . ">\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "X-Mailer: Mtaita Tech Mailer\r\n";

        $message = "Subject: $subject\r\n$headers\r\n$body\r\n.";

        $this->write($socket, $message);
        $response = $this->read($socket);

        $this->write($socket, "QUIT");
        fclose($socket);

        $code = substr(trim($response), 0, 3);
        $success = $code === '250' || $code === '354';
        if (!$success) {
            $log("SMTP DATA response: $response (code: $code)");
            $log("Send to: " . implode(', ', $recipients) . " from: $this->fromEmail subject: $subject");
        }
        return $success;
    }

    private function write($socket, $data)
    {
        fwrite($socket, $data . "\r\n");
    }

    private function read($socket)
    {
        if (!is_resource($socket)) return false;
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $response;
    }
}
