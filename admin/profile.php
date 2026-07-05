<?php
$page_title = 'Profile';
$active_page = '';
require_once 'admin_header.php';

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$success_msg = '';
$error_msg = '';

$stmt = $pdo->prepare("SELECT id, username, email, avatar, role FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');

        if ($new_username === '') {
            $error_msg = 'Username cannot be empty.';
        } elseif ($new_username !== $user['username']) {
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->execute([$new_username, $admin_id]);
            if ($check->rowCount() > 0) {
                $error_msg = 'Username already taken.';
            }
        }

        if (empty($error_msg)) {
            $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")->execute([$new_username, $new_email, $admin_id]);
            $_SESSION['admin_username'] = $new_username;
            $user['username'] = $new_username;
            $user['email'] = $new_email;
            $success_msg = 'Profile updated successfully.';
        }
    }

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $row = $stmt->fetch();
        if (!password_verify($current, $row['password'])) {
            $error_msg = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 6) {
            $error_msg = 'New password must be at least 6 characters.';
        } elseif ($new_pass !== $confirm) {
            $error_msg = 'Passwords do not match.';
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $admin_id]);
            $success_msg = 'Password changed successfully.';
        }
    }

    if (isset($_POST['upload_avatar'])) {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error_msg = 'Allowed file types: jpg, jpeg, png, gif, webp.';
            } else {
                $dir = __DIR__ . '/../assets/img/uploads/avatars/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'admin_' . $admin_id . '.' . $ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename);

                if ($user['avatar'] && $user['avatar'] !== $filename && file_exists($dir . $user['avatar'])) {
                    @unlink($dir . $user['avatar']);
                }

                $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$filename, $admin_id]);
                $user['avatar'] = $filename;
                $success_msg = 'Profile picture updated.';
            }
        } else {
            $error_msg = 'Please select an image file.';
        }
    }

    // Re-fetch user data
    $stmt = $pdo->prepare("SELECT id, username, email, avatar, role FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $user = $stmt->fetch();
}

$avatar_url = $user['avatar'] ? '/assets/img/uploads/avatars/' . rawurlencode($user['avatar']) : '';
?>
<div class="row">
    <div class="col-lg-4">
        <div class="admin-card text-center">
            <div class="mb-3">
                <?php if ($avatar_url): ?>
                    <img src="<?= $avatar_url ?>" alt="Avatar" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;border:4px solid var(--accent);">
                <?php else: ?>
                    <i class="fas fa-user-circle" style="font-size:6rem;color:var(--accent);"></i>
                <?php endif; ?>
            </div>
            <h5 class="mb-1"><?= htmlspecialchars($user['username']) ?></h5>
            <p class="text-muted small mb-3"><?= htmlspecialchars($user['role'] ?? 'Admin') ?></p>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-2">
                    <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                </div>
                <button type="submit" name="upload_avatar" class="btn btn-cyan btn-sm w-100">Upload Picture</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if ($success_msg): ?>
            <div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <div class="admin-card mb-3">
            <h5><i class="bi bi-pencil-square text-cyan me-2"></i>Edit Profile</h5>
            <form method="POST" class="mt-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn btn-cyan">Save Changes</button>
            </form>
        </div>

        <div class="admin-card">
            <h5><i class="bi bi-shield-lock text-cyan me-2"></i>Change Password</h5>
            <form method="POST" class="mt-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn btn-cyan">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
