<?php
$page_title = 'Manage Users';
$active_page = 'users';
require_once 'admin_header.php';

$success_msg = '';
$error_msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $otp_preference = $_POST['otp_preference'] ?? 'email';

        if (!$name || !$email) {
            $error_msg = 'Name and email are required.';
        } else {
            try {
                if ($id > 0) {
                    if ($password) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE public_users SET name = ?, email = ?, phone = ?, password = ?, otp_preference = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $phone, $hash, $otp_preference, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE public_users SET name = ?, email = ?, phone = ?, otp_preference = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $phone, $otp_preference, $id]);
                    }
                    $success_msg = 'User updated successfully.';
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM public_users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error_msg = 'A user with this email already exists.';
                    } else {
                        $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
                        $stmt = $pdo->prepare("INSERT INTO public_users (name, email, phone, password, otp_preference) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $email, $phone, $hash, $otp_preference]);
                        $success_msg = 'User added successfully.';
                    }
                }
            } catch (Exception $e) {
                $error_msg = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM public_users WHERE id = ?");
                $stmt->execute([$id]);
                $success_msg = 'User deleted.';
            } catch (Exception $e) {
                $error_msg = 'Delete failed: ' . $e->getMessage();
            }
        }
    }
}

$users = $pdo->query("SELECT id, name, email, phone, otp_preference, created_at, updated_at FROM public_users ORDER BY created_at DESC")->fetchAll();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 text-gray-800">Manage Users</h4>
    <button class="btn btn-cyan btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAdd()"><i class="bi bi-plus-lg me-1"></i> Add User</button>
</div>

<?php if ($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>OTP</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                    <td><span class="badge bg-<?= $u['otp_preference'] === 'sms' ? 'info' : 'secondary' ?>"><?= $u['otp_preference'] ?></span></td>
                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-cyan me-1" onclick="openEdit(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES) ?>', '<?= $u['otp_preference'] ?>')"><i class="bi bi-pencil"></i></button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this user?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No registered users yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="userId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="userName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="userEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="userPhone" class="form-control" placeholder="+255...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(leave blank if editing)</small></label>
                        <input type="password" name="password" class="form-control" id="userPassword">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OTP Preference</label>
                        <select name="otp_preference" id="userOtpPref" class="form-select">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdd() {
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('formAction').value = 'add';
    document.getElementById('userId').value = '0';
    document.getElementById('userName').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPhone').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('userOtpPref').value = 'email';
}

function openEdit(id, name, email, phone, otpPref) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = id;
    document.getElementById('userName').value = name;
    document.getElementById('userEmail').value = email;
    document.getElementById('userPhone').value = phone;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('userOtpPref').value = otpPref;
}
</script>

<?php require_once 'admin_footer.php'; ?>
