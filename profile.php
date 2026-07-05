<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

$user = getPublicUser();
$userId = $user['id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !$email) {
        $error_msg = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Invalid email format.';
    } else {
        try {
            // Check email uniqueness (exclude current user)
            $stmt = $pdo->prepare("SELECT id FROM public_users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $error_msg = 'Email already in use by another account.';
            } else {
                $sql = "UPDATE public_users SET name = ?, email = ?, phone = ?";
                $params = [$name, $email, $phone];

                if ($newPass) {
                    if (!$current) {
                        $error_msg = 'Current password is required to set a new password.';
                    } elseif (strlen($newPass) < 6) {
                        $error_msg = 'New password must be at least 6 characters.';
                    } elseif ($newPass !== $confirm) {
                        $error_msg = 'Passwords do not match.';
                    } else {
                        // Verify current password
                        $stmt = $pdo->prepare("SELECT password FROM public_users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $row = $stmt->fetch();
                        if (!$row['password'] || !password_verify($current, $row['password'])) {
                            $error_msg = 'Current password is incorrect.';
                        } else {
                            $sql .= ", password = ?";
                            $params[] = password_hash($newPass, PASSWORD_DEFAULT);
                        }
                    }
                }

                if (!$error_msg) {
                    $sql .= " WHERE id = ?";
                    $params[] = $userId;
                    $pdo->prepare($sql)->execute($params);
                    $success_msg = 'Profile updated successfully.';
                    // Refresh user data
                    $user = getPublicUser();
                }
            }
        } catch (Exception $e) {
            $error_msg = 'Database error. Please try again.';
        }
    }
}

$page_title = 'My Profile — Mtaita Tech';
$page_desc = 'Manage your profile information.';
$page_keywords = 'profile, account settings, Mtaita Tech';
$hide_navbar = true;
require_once 'header.php';
?>
<section class="dashboard-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="mt-glass p-3 rounded-4">
                    <div class="text-center mb-3">
                        <div style="width:60px;height:60px;border-radius:50%;background:#dc2626;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:24px;font-weight:700;color:#fff;"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                        <h6 class="text-white mt-2 mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                        <small class="text-secondary"><?= htmlspecialchars($user['email']) ?></small>
                    </div>
                    <hr class="border-secondary">
                    <nav class="d-flex flex-column gap-1">
                        <a href="dashboard" class="btn btn-outline-primary btn-sm text-start"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                        <a href="my-courses" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-book me-2"></i>My Courses</a>
                        <a href="digital_products" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-box me-2"></i>Digital Products</a>
                        <a href="profile" class="btn btn-primary btn-sm text-start"><i class="fas fa-user me-2"></i>My Profile</a>
                        <a href="courses" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-search me-2"></i>Browse Courses</a>
                        <a href="logout" class="btn btn-outline-danger btn-sm text-start mt-2"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9">
                <div class="mt-dash-hero text-white mb-4" data-aos="fade-up">
                    <h1 class="fw-bold mb-2" style="font-size:1.75rem;">My Profile</h1>
                    <p class="text-secondary mb-0">Manage your account information.</p>
                </div>

                <?php if ($success_msg): ?><div class="d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
                <?php if ($error_msg): ?><div class="d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

                <div class="mt-glass p-4 rounded-4" data-aos="fade-up">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+255...">
                            </div>
                            <div class="col-12"><hr class="border-secondary"></div>
                            <div class="col-12">
                                <h6 class="text-white fw-bold">Change Password <small class="text-secondary fw-normal">(leave blank to keep current)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white">Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="6">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary mt-btn-glow px-4"><i class="fas fa-save me-2"></i>Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
