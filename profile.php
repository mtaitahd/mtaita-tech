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
                    $user = getPublicUser();
                }
            }
        } catch (Exception $e) {
            $error_msg = 'Database error. Please try again.';
        }
    }
}

$page_title = 'My Profile';
$active_page = 'profile';
require_once 'user_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <?php if ($success_msg): ?><div class="d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
        <?php if ($error_msg): ?><div class="d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

        <div class="admin-card">
            <h5 class="mb-4"><i class="bi bi-person text-cyan me-2"></i>My Profile</h5>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+255...">
                    </div>
                    <div class="col-12"><hr></div>
                    <div class="col-12">
                        <h6 class="fw-bold">Change Password <small class="text-muted fw-normal">(leave blank to keep current)</small></h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="6">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-cyan"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'user_footer.php'; ?>
