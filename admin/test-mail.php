<?php
try {
    session_start();
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: login');
        exit;
    }

    $result = '';
    $error = '';
    $mailer = null;

    try {
        require_once __DIR__ . '/../config.php';
        require_once __DIR__ . '/../mailer.php';
        $mailer = new Mailer();
    } catch (Throwable $e) {
        $error = 'Mailer initialization failed: ' . $e->getMessage();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mailer) {
        $to = trim($_POST['to'] ?? '');
        $subject = trim($_POST['subject'] ?? 'Test from Mtaita Tech');
        $message = trim($_POST['message'] ?? 'This is a test email.');

        if (!$to) {
            $error = 'Email address is required.';
        } else {
            try {
                $sent = $mailer->send($to, $subject, $message);
                if ($sent) {
                    $result = 'Email sent successfully to ' . htmlspecialchars($to);
                } else {
                    $error = 'Failed to send email. Check server error log for details.';
                }
            } catch (Throwable $e) {
                $error = 'Send error: ' . $e->getMessage();
            }
        }
    }
} catch (Throwable $e) {
    $error = 'Fatal error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <title>Test Mail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm" style="max-width:600px;margin:auto;">
            <div class="card-body">
                <h4 class="mb-4">Test Email</h4>
                <?php if ($result): ?><div class="alert alert-success"><?= $result ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-control" value="mtaitahd@gmail.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" value="Test from Mtaita Tech">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4">This is a test email sent from the admin panel.</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Test Email</button>
                    <a href="dashboard" class="btn btn-outline-secondary ms-2">Back</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
