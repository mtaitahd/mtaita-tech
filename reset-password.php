<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
$page_title = 'Reset Password — Mtaita Tech';
$page_desc = 'Set a new password for your Mtaita Tech account.';
$page_keywords = 'reset password, new password, Mtaita Tech';

$verified_email = $_SESSION['reset_verified'] ?? '';

if (empty($verified_email)) {
    header('Location: forgot-password');
    exit;
}

$get_msg = $_GET['msg'] ?? '';
$get_error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $get_error = 'Invalid security token. Please try again.';
    }

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($verified_email)) {
        $get_error = 'Session expired. Please start the password reset process again.';
    } elseif (strlen($password) < 6) {
        $get_error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $get_error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE public_users SET password = ? WHERE email = ?");
            $stmt->execute([$hash, $verified_email]);

            $pdo->commit();

            unset($_SESSION['reset_verified']);

            header('Location: login.php?msg=' . urlencode('Password reset successful. Please login.'));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $get_error = 'An error occurred. Please try again.';
        }
    }
}

function maskEmail(string $email): string {
    $parts = explode('@', $email);
    $name = $parts[0];
    $domain = $parts[1] ?? '';
    $maskedName = substr($name, 0, 1) . str_repeat('*', max(0, strlen($name) - 1));
    return $maskedName . '@' . $domain;
}

$hide_navbar = true;
$body_class = 'auth-page-bg';
require_once 'header.php';
?>
<section class="auth-page">
    <div class="auth-wrap">
        <div class="auth-left">
            <img src="/assets/img/jj.png" alt="Mtaita Tech">
            <h3>MTAITA TECH</h3>
            <p>IT &amp; Graphic Design Agency</p>
            <div class="auth-social">
                <a href="https://web.facebook.com/profile.php?id=61583334572270" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/johnsonpaul1269/" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/@mtaitatech" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
            <div class="auth-location"><i class="fas fa-map-marker-alt"></i> Arusha, Tanzania</div>
            <a href="/" class="auth-home-link"><i class="fas fa-home"></i> Back to Home</a>
        </div>
        <div class="auth-right">
            <?php if ($get_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($get_msg) ?></div><?php endif; ?>
            <?php if ($get_error): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($get_error) ?></div><?php endif; ?>

            <h1 style="font-size:1.5rem;">Set New Password</h1>
            <p class="auth-sub">For <strong><?= htmlspecialchars(maskEmail($verified_email)) ?></strong></p>

            <form method="POST" action="reset-password">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="auth-field">
                    <label>New Password</label>
                    <input type="password" name="password" required placeholder="Enter new password" minlength="6">
                </div>

                <div class="auth-field">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm new password" minlength="6">
                </div>

                <button type="submit" class="auth-btn">Reset Password</button>

                <p class="auth-link"><a href="login"><i class="fas fa-arrow-left me-1"></i> Back to Login</a></p>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var msg = document.querySelector('.swal-msg');
    if (msg) {
        var type = msg.dataset.type;
        Swal.fire({ icon: type, title: msg.textContent.trim(), timer: 3000, showConfirmButton: false });
    }
});
</script>

<?php require_once 'footer.php'; ?>
