<?php
$page_title = 'Settings';
$active_page = 'settings';
require_once 'admin_header.php';
require_once __DIR__ . '/../lib/Settings.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $fields = [
        'site_name', 'site_tagline',
        'admin_email', 'admin_phone',
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass',
        'from_email', 'from_name',
        'meseji_api_key', 'meseji_sender_id'
    ];
    foreach ($fields as $key) {
        $val = trim($_POST[$key] ?? '');
        Settings::set($key, $val);
    }
    $success_msg = 'Settings saved successfully.';
}
?>
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
            <!-- General -->
            <div class="tab-pane fade show active" id="general">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(Settings::get('site_name', 'Mtaita Tech')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Tagline</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars(Settings::get('site_tagline', 'IT & Graphic Design Agency')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars(Settings::get('admin_email', 'mtaitajohnson7@gmail.com')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin Phone Number</label>
                        <input type="text" name="admin_phone" class="form-control" value="<?= htmlspecialchars(Settings::get('admin_phone', '+255 616 591 639')) ?>">
                    </div>
                </div>
            </div>

            <!-- SMTP -->
            <div class="tab-pane fade" id="smtp">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars(Settings::get('smtp_host', 'smtp.gmail.com')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" value="<?= htmlspecialchars(Settings::get('smtp_port', '465')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars(Settings::get('smtp_user', '')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars(Settings::get('smtp_pass', '')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Email</label>
                        <input type="email" name="from_email" class="form-control" value="<?= htmlspecialchars(Settings::get('from_email', '')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars(Settings::get('from_name', 'Mtaita Tech')) ?>">
                    </div>
                </div>
            </div>

            <!-- SMS -->
            <div class="tab-pane fade" id="sms">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meseji API Key</label>
                        <input type="text" name="meseji_api_key" class="form-control" value="<?= htmlspecialchars(Settings::get('meseji_api_key', '')) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sender ID</label>
                        <input type="text" name="meseji_sender_id" class="form-control" value="<?= htmlspecialchars(Settings::get('meseji_sender_id', 'MTAITATEC')) ?>">
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
