<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/OTP.php';
$page_title = 'Forgot Password — Mtaita Tech';
$page_desc = 'Reset your Mtaita Tech account password.';
$page_keywords = 'forgot password, reset password, Mtaita Tech';

$get_msg = $_GET['msg'] ?? '';
$get_error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header('Location: forgot-password.php?error=' . urlencode('Invalid security token. Please try again.'));
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $otp_method = $_POST['otp_method'] ?? 'email';

    if (empty($email)) {
        header('Location: forgot-password.php?error=' . urlencode('Please enter your email address.'));
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, email, phone, otp_preference FROM public_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: forgot-password.php?error=' . urlencode('No account found with that email address.'));
        exit;
    }

    if ($otp_method === 'sms' && empty($user['phone'])) {
        header('Location: forgot-password.php?error=' . urlencode('No phone number on file. Please use email OTP.'));
        exit;
    }

    $otp = new OTP();
    if ($otp->sendUserOTP($user['id'], 'reset', $otp_method)) {
        $_SESSION['pending_user_id'] = $user['id'];
        $_SESSION['pending_email'] = $user['email'];
        $_SESSION['pending_type'] = 'reset';
        $_SESSION['pending_otp_method'] = $otp_method;
        header('Location: otp-verify?type=reset');
        exit;
    } else {
        header('Location: forgot-password.php?error=' . urlencode('Could not send reset code. Please try again.'));
        exit;
    }
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

            <h1 style="font-size:1.5rem;">Forgot Password</h1>
            <p class="auth-sub">Enter your email to receive a reset code</p>

            <form method="POST" action="forgot-password">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="auth-field">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="auth-field">
                    <label>Receive reset code via</label>
                    <div class="auth-otp-methods">
                        <label class="auth-otp-radio">
                            <input type="radio" name="otp_method" value="email" checked>
                            <span><i class="fas fa-envelope"></i> Email</span>
                        </label>
                        <label class="auth-otp-radio">
                            <input type="radio" name="otp_method" value="sms">
                            <span><i class="fas fa-mobile-alt"></i> SMS</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="auth-btn">Send Reset Code</button>

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
