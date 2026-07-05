<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/lib/OTP.php';

$page_title = 'Verify OTP — Mtaita Tech';
$page_desc = 'Enter the verification code sent to your email.';
$page_keywords = 'verify, OTP, verification code, Mtaita Tech';
$hide_navbar = true;
$body_class = 'auth-page-bg';

$type = $_GET['type'] ?? ($_SESSION['pending_type'] ?? '');
$userId = $_SESSION['pending_user_id'] ?? 0;
$email = $_SESSION['pending_email'] ?? '';
$otp_method = $_SESSION['pending_otp_method'] ?? 'email';
$error_msg = '';
$success_msg = '';

if (empty($type) || !in_array($type, ['verify', 'login', 'reset']) || empty($userId)) {
    header('Location: login');
    exit;
}

$_SESSION['pending_type'] = $type;

// Fetch OTP expiry for countdown
$expiresIn = 0;
$stmt = $pdo->prepare("SELECT expires_at FROM user_otps WHERE user_id = ? AND type = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$userId, $type]);
$otpRow = $stmt->fetch();
if ($otpRow) {
    $expiresAt = strtotime($otpRow['expires_at']);
    $expiresIn = max(0, $expiresAt - time());
}

// Show success toast only on first arrival, not on refresh
$successFlag = 'otp_success_shown_' . $type;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_SESSION[$successFlag]) && empty($error_msg)) {
    $_SESSION[$successFlag] = true;
    $success_msg = $otp_method === 'sms'
        ? 'Verification code sent via SMS to your phone.'
        : 'Verification code sent to your email. Check Spam if not in inbox.';
}

$verified = false;
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otpCode = trim($_POST['otp'] ?? '');
    $otpCode = preg_replace('/[^0-9]/', '', $otpCode);

    if (strlen($otpCode) !== 6) {
        $error_msg = 'Please enter the complete 6-digit code.';
    } else {
        $otp = new OTP();
        if ($otp->verify($userId, $otpCode, $type)) {
            if ($type === 'reset') {
                $_SESSION['reset_verified'] = $email;
                unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['pending_type']);
                $verified = true;
                $redirectUrl = 'reset-password';
            } else {
                $stmt = $pdo->prepare("SELECT id, name, email FROM public_users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['public_user_id'] = $user['id'];
                    unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['pending_type']);

                    if ($type === 'verify') {
                        $redirectUrl = 'dashboard.php?msg=' . urlencode('Welcome! Your email has been verified.');
                    } else {
                        $redirectUrl = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                        unset($_SESSION['redirect_after_login']);
                    }
                    $verified = true;
                }
            }
        } else {
            $error_msg = 'Invalid or expired code. Please try again.';
        }
    }
}

if ($verified): ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="robots" content="noindex,nofollow"><title>Verified!</title>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background:#0b0c1a;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;">
<script>
Swal.fire({ icon:'success', title:'Verified!', text:'Redirecting...', timer:1500, showConfirmButton:false });
setTimeout(function(){ window.location.href='<?= $redirectUrl ?>'; }, 1500);
</script>
</body>
</html>
<?php exit; endif;

$typeLabels = ['verify' => 'email verification', 'login' => 'login', 'reset' => 'password reset'];
$label = $typeLabels[$type] ?? 'verification';
$page_title = 'Verify OTP — Mtaita Tech';

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
            <?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
            <?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

            <h1 style="font-size:1.5rem;"><?= $otp_method === 'sms' ? 'Check Your Phone' : 'Check Your Email' ?></h1>
            <p class="auth-sub">We sent a 6-digit code <?= $otp_method === 'sms' ? 'via SMS to your registered phone number' : 'to <strong>' . htmlspecialchars($email) . '</strong>' ?></p>
            <?php if ($otp_method !== 'sms'): ?>
            <p class="auth-sub" style="margin-top:-14px;font-size:0.78rem;color:#e67e22;"><i class="fas fa-exclamation-triangle me-1"></i>If not in inbox, check your <strong>Spam</strong> folder.</p>
            <?php endif; ?>
            <p class="auth-sub" style="margin-top:-14px;font-size:0.78rem;">Enter the code to complete <?= htmlspecialchars($label) ?>.</p>

            <form method="POST" action="otp-verify" id="otpForm">
                <div class="auth-field">
                    <label>Verification Code</label>
                    <div class="auth-otp-inputs" id="otpInputs">
                        <input type="text" name="otp_digit_1" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" required>
                        <input type="text" name="otp_digit_2" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" name="otp_digit_3" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" name="otp_digit_4" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" name="otp_digit_5" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" name="otp_digit_6" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                    </div>
                    <input type="hidden" name="otp" id="otpHidden">
                </div>

                <div class="auth-expiry" id="otpExpiry">
                    <i class="fas fa-clock"></i> Code expires in <span id="otpCountdown"></span>
                </div>

                <button type="submit" class="auth-btn" id="verifyBtn">Verify Code</button>

                <div class="auth-resend">
                    <p class="mb-0">Didn't receive the code?</p>
                    <a href="javascript:void(0)" id="resendOtp">Resend code</a>
                    <span class="resend-timer" id="resendTimer" style="display:none;"></span>
                </div>

                <p class="auth-link"><a href="login"><i class="fas fa-arrow-left me-1"></i> Back to Login</a></p>
            </form>
        </div>
    </div>
</section>

<style>
.auth-otp-inputs .otp-input {
    width: 42px;
    height: 48px;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 700;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    background: #F8FAFC;
    color: #333;
    outline: none;
    padding: 0;
    transition: border-color 0.2s;
}
.auth-otp-inputs .otp-input:focus {
    border-color: rgb(105, 105, 141);
    background: #fff;
}
.auth-otp-inputs .otp-input.filled {
    border-color: #10B981;
    background: #F0FDF4;
}
.auth-expiry {
    text-align: center;
    font-size: 0.82rem;
    color: #94A3B8;
    margin-bottom: 16px;
}
.auth-expiry i {
    margin-right: 4px;
}
.auth-expiry.expired {
    color: #DC2626;
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('.otp-input');
    var hidden = document.getElementById('otpHidden');

    inputs.forEach(function(input, index) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 1) {
                this.classList.add('filled');
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                if (index === inputs.length - 1) {
                    updateHidden();
                    return;
                }
            } else {
                this.classList.remove('filled');
            }
            updateHidden();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                inputs[index - 1].classList.remove('filled');
            }
            if (e.key === 'ArrowLeft' && index > 0) {
                inputs[index - 1].focus();
            }
            if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var paste = (e.clipboardData || window.clipboardData).getData('text');
            var digits = paste.replace(/[^0-9]/g, '').split('');
            digits.forEach(function(digit, i) {
                if (i < inputs.length) {
                    inputs[i].value = digit;
                    inputs[i].classList.add('filled');
                }
            });
            var nextIndex = Math.min(digits.length, inputs.length - 1);
            if (digits.length > 0) {
                inputs[nextIndex].focus();
            }
            updateHidden();
        });
    });

    function updateHidden() {
        var code = '';
        inputs.forEach(function(inp) { code += inp.value; });
        hidden.value = code;
    }

    document.getElementById('otpForm').addEventListener('submit', function(e) {
        updateHidden();
        if (hidden.value.length !== 6) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Please enter the full 6-digit code.' });
        } else {
            document.getElementById('verifyBtn').disabled = true;
            document.getElementById('verifyBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';
        }
    });

    var resendBtn = document.getElementById('resendOtp');
    var timerEl = document.getElementById('resendTimer');
    var cooldown = 30;
    var expiryEl = document.getElementById('otpCountdown');
    var expiryWrap = document.getElementById('otpExpiry');
    var otpExpiresIn = <?= $expiresIn ?>;
    var expiryInterval;

    function formatTime(seconds) {
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function updateExpiry() {
        if (otpExpiresIn <= 0) {
            expiryEl.textContent = 'expired';
            expiryWrap.classList.add('expired');
            clearInterval(expiryInterval);
            return;
        }
        expiryEl.textContent = formatTime(otpExpiresIn);
    }

    function startExpiryCountdown() {
        if (expiryInterval) clearInterval(expiryInterval);
        updateExpiry();
        expiryInterval = setInterval(function() {
            otpExpiresIn--;
            updateExpiry();
        }, 1000);
    }

    resendBtn.addEventListener('click', function() {
        resendBtn.style.display = 'none';
        timerEl.style.display = 'inline';
        timerEl.textContent = 'Resend in ' + cooldown + 's';

        fetch('resend-otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: '<?= $type ?>', otp_method: '<?= $otp_method ?>' })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var toastOpts = { icon: 'success', title: 'Code resent!', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 };<?php if ($otp_method !== 'sms'): ?> toastOpts.text = 'Check Spam folder if not in inbox.';<?php endif; ?> Swal.fire(toastOpts);
                otpExpiresIn = <?= ($type === 'reset' ? 900 : 600) ?>;
                expiryWrap.classList.remove('expired');
                startExpiryCountdown();
            } else {
                Swal.fire({ icon: 'error', title: data.message || 'Failed to resend code.' });
            }
        });

        var countdown = cooldown;
        var interval = setInterval(function() {
            countdown--;
            timerEl.textContent = 'Resend in ' + countdown + 's';
            if (countdown <= 0) {
                clearInterval(interval);
                timerEl.style.display = 'none';
                resendBtn.style.display = 'inline';
            }
        }, 1000);
    });

    startExpiryCountdown();
    inputs[0].focus();
});
</script>

<?php require_once 'footer.php'; ?>
