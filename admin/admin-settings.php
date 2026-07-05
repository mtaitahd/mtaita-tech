<?php
$page_title = 'Settings';
$active_page = 'settings';
require_once 'admin_header.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $fields = [
        'site_name', 'site_tagline',
        'admin_email', 'admin_phone', 'admin_location',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_user', 'smtp_pass',
        'from_email', 'from_name',
        'meseji_api_key', 'meseji_sender_id'
    ];
    try {
        foreach ($fields as $key) {
            $val = trim($_POST[$key] ?? '');
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, $val]);
        }
        $success_msg = 'Settings saved successfully.';
    } catch (Exception $e) {
        $error_msg = 'Database error: ' . $e->getMessage();
    }
}

$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
function s($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 text-gray-800">Settings</h4>
</div>

<?php if ($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <form method="POST" action="">
        <ul class="nav nav-tabs nav-cyan mb-4" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="smtp-tab" data-bs-toggle="tab" data-bs-target="#smtp" type="button">SMTP</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms" type="button">SMS</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="general">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(s('site_name', 'Mtaita Tech')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Tagline</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars(s('site_tagline', 'IT & Graphic Design Agency')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars(s('admin_email', 'mtaitajohnson7@gmail.com')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin Phone Number</label>
                        <input type="text" name="admin_phone" class="form-control" value="<?= htmlspecialchars(s('admin_phone', '+255 616 591 639')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin Location</label>
                        <input type="text" name="admin_location" class="form-control" value="<?= htmlspecialchars(s('admin_location', 'Moshi, Kilimanjaro')) ?>">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="smtp">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars(s('smtp_host', 'smtp.gmail.com')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" value="<?= htmlspecialchars(s('smtp_port', '465')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Encryption</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="ssl" <?= s('smtp_encryption', 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
                            <option value="tls" <?= s('smtp_encryption', 'ssl') === 'tls' ? 'selected' : '' ?>>TLS (port 587)</option>
                            <option value="none" <?= s('smtp_encryption', 'ssl') === 'none' ? 'selected' : '' ?>>None (port 25)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars(s('smtp_user')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars(s('smtp_pass')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Email</label>
                        <input type="email" name="from_email" class="form-control" value="<?= htmlspecialchars(s('from_email')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars(s('from_name', 'Mtaita Tech')) ?>">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="sms">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meseji API Key</label>
                        <input type="text" name="meseji_api_key" class="form-control" value="<?= htmlspecialchars(s('meseji_api_key')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sender ID</label>
                        <input type="text" name="meseji_sender_id" class="form-control" value="<?= htmlspecialchars(s('meseji_sender_id', 'MTAITATEC')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <button type="submit" name="save_settings" class="btn btn-cyan"><i class="bi bi-check-lg me-1"></i> Save Settings</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabEl = document.querySelector('#settingsTab a[data-bs-toggle="tab"]');
    if (tabEl) {
        new bootstrap.Tab(tabEl);
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
