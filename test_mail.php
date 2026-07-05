<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

echo "SMTP_USER: " . SMTP_USER . "\n";
echo "SMTP_PASS: " . SMTP_PASS . "\n";
echo "FROM_EMAIL: " . FROM_EMAIL . "\n";
echo "FROM_NAME: " . FROM_NAME . "\n\n";

$mailer = new Mailer();
$sent = $mailer->send(
    'mtaitahd@gmail.com',
    'Test from Mtaita Tech',
    "This is a test message sent at " . date('Y-m-d H:i:s')
);

echo "Result: " . ($sent ? "SUCCESS" : "FAILED") . "\n";
