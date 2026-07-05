<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/OTP.php';

$errors = [];
$success_msg = '';
$error_msg = '';
$old = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $otp_preference = 'email';

    if (empty($old['name'])) $errors[] = 'Name is required.';
    if (empty($old['email'])) $errors[] = 'Email is required.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (empty($old['phone'])) $errors[] = 'Phone number is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    elseif (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM public_users WHERE email = ?");
        $stmt->execute([$old['email']]);
        if ($stmt->rowCount() > 0) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO public_users (name, email, phone, otp_preference, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$old['name'], $old['email'], $old['phone'], $otp_preference, $hashed_password])) {
            $user_id = $pdo->lastInsertId();
            $_SESSION['pending_otp_method'] = $otp_preference;
            $otp = new OTP();
            if ($otp->sendUserOTP($user_id, 'verify', $otp_preference)) {
                $_SESSION['pending_user_id'] = $user_id;
                $_SESSION['pending_email'] = $old['email'];
                $_SESSION['pending_type'] = 'verify';
                header('Location: otp-verify?type=verify');
                exit;
            } else {
                $errors[] = 'Account created but could not send verification email. Please contact support.';
            }
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }

    if (!empty($errors)) {
        $error_msg = implode(', ', $errors);
    }
}

$page_title = 'Register — Mtaita Tech';
$page_desc = 'Create your account on Mtaita Tech to access courses.';
$page_keywords = 'register, create account, Mtaita Tech, courses';
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
            <?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
            <?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

            <h1 style="font-size:1.5rem;">Create Account</h1>
            <p class="auth-sub">Join Mtaita Tech to access courses</p>

            <form method="POST" action="register">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="auth-field">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($old['name']) ?>" required placeholder="Enter your full name">
                </div>

                <div class="auth-field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required placeholder="Enter your email">
                </div>

                <div class="auth-field">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone']) ?>" required placeholder="+255 XXX XXX XXX">
                </div>

                <div class="auth-field">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Create a password (min 6 characters)">
                </div>

                <div class="auth-field">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm your password">
                </div>

                <button type="submit" class="auth-btn">Create Account</button>

                <p class="auth-link">Already have an account? <a href="login">Login here</a></p>
            </form>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
