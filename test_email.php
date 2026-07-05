<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

$mailer = new Mailer();
$result = $mailer->send('mtaitahd@gmail.com', 'Test from Mtaita Tech', 'This is a test email sent from the server.');

echo $result ? 'Email sent successfully!' : 'Failed to send email. Check error log.';
