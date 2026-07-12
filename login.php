<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/google_oauth_config.php';
require_once __DIR__ . '/lib/OTP.php';
$page_title = 'Login — Mtaita Tech';
$page_desc = 'Login to your Mtaita Tech account to access your courses.';
$page_keywords = 'login, sign in, Mtaita Tech account, courses access';

if (!empty($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = urldecode($_GET['redirect']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header('Location: login.php?error=' . urlencode('Invalid security token. Please try again.'));
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $otp_method = $_POST['otp_method'] ?? 'email';

    if (empty($email) || empty($password)) {
        header('Location: login.php?error=' . urlencode('Email and password are required.'));
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, email, password, phone, otp_preference FROM public_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['password'] !== null && password_verify($password, $user['password'])) {
        if ($otp_method === 'sms' && empty($user['phone'])) {
            header('Location: login.php?error=' . urlencode('No phone number on file. Please use email OTP or update your profile.'));
            exit;
        }

        $otp = new OTP();
        if ($otp->sendUserOTP($user['id'], 'login', $otp_method)) {
            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['pending_email'] = $user['email'];
            $_SESSION['pending_type'] = 'login';
            $_SESSION['pending_otp_method'] = $otp_method;
            header('Location: otp-verify?type=login');
            exit;
        } else {
            header('Location: login.php?error=' . urlencode('Could not send verification code. Please try again.'));
            exit;
        }
    } else {
        header('Location: login.php?error=' . urlencode('Invalid email or password.'));
        exit;
    }
}

$hide_navbar = true;
$body_class = 'auth-page-bg';
require_once 'header.php';
$get_msg = $_GET['msg'] ?? '';
$get_error = $_GET['error'] ?? '';
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

            <h1>Welcome Back</h1>
            <p class="auth-sub">Sign in to your account to continue</p>

            <form method="POST" action="login">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="auth-field">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="auth-field">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>

                <div class="auth-forgot">
                    <a href="forgot-password">Forgot Password?</a>
                </div>

                <div class="auth-field">
                    <label>Receive verification via</label>
                    <div class="auth-otp-inline">
                        <span id="loginOtpMethodLabel">Email</span>
                        <input type="hidden" name="otp_method" id="loginOtpPreferenceInput" value="email">
                        <button type="button" class="auth-otp-toggle-link" id="loginOtpToggleBtn">Change area to receive OTP</button>
                    </div>
                    <div class="auth-otp-options" id="loginOtpOptions" style="display:none;">
                        <label class="auth-otp-radio">
                            <input type="radio" name="otp_method_radio" value="email" checked>
                            <span><i class="fas fa-envelope"></i> Email</span>
                        </label>
                        <label class="auth-otp-radio">
                            <input type="radio" name="otp_method_radio" value="sms">
                            <span><i class="fas fa-mobile-alt"></i> SMS</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="auth-btn">Login</button>

                <div class="auth-divider"><span>or</span></div>

                <a href="<?= getGoogleAuthUrl() ?>" class="auth-btn-google">
                    <svg width="20" height="20" viewBox="0 0 48 48" style="margin-right:10px">
                        <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3c-2.5 7.2-9.2 12-17.3 12-10 0-18-8-18-18s8-18 18-18c4.6 0 8.7 1.7 11.9 4.5l5.7-5.7C32.5 2.5 28.5 1 24 1 11.3 1 1 11.3 1 24s10.3 23 23 23c11.4 0 21-8.3 21-23 0-1.6-.2-3.1-.4-3.9z"/>
                        <path fill="#FF3D00" d="M3.6 14.2l6.6 4.8C12.4 14 17.8 10 24 10c5.2 0 9.9 1.9 13.5 5.1L43 9.5C38.5 4.9 31.8 2 24 2 15.1 2 7.2 6.8 3.6 14.2z"/>
                        <path fill="#4CAF50" d="M24 46c7.5 0 14.4-2.8 19.6-7.3l-6.7-5.7c-3 2.3-6.8 3.6-12.9 3.6-7.4 0-13.6-4.4-16.3-10.8l-6.5 5.1C5.5 39.5 13.9 46 24 46z"/>
                        <path fill="#1976D2" d="M46.5 24c0-1.6-.4-3.2-.9-4.9H24v9.8h12.8c-1.3 3.9-4.3 6.6-8.8 8.3l6.7 5.7c4.9-4.1 9.8-10.1 9.8-18.9z"/>
                    </svg>
                    Continue with Google
                </a>

                <p class="auth-link">Don't have an account? <a href="register">Register here</a></p>
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

    var toggleBtn = document.getElementById('loginOtpToggleBtn');
    var otpOptions = document.getElementById('loginOtpOptions');
    var otpLabel = document.getElementById('loginOtpMethodLabel');
    var otpInput = document.getElementById('loginOtpPreferenceInput');
    var radios = document.querySelectorAll('input[name="otp_method_radio"]');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (otpOptions.style.display === 'none') {
                otpOptions.style.display = 'block';
                toggleBtn.textContent = 'Hide options';
            } else {
                otpOptions.style.display = 'none';
                toggleBtn.textContent = 'Change area to receive OTP';
            }
        });

        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    otpInput.value = this.value;
                    otpLabel.textContent = this.value === 'email' ? 'Email' : 'SMS';
                    otpOptions.style.display = 'none';
                    toggleBtn.textContent = 'Change area to receive OTP';
                }
            });
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>
