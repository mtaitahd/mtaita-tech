<?php
header('X-Debug-Path: ' . __FILE__);
$page_title = 'Settings';
$active_page = 'settings';
require_once 'admin_header.php';
require_once __DIR__ . '/../lib/Settings.php';

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
    foreach ($fields as $key) {
        $val = trim($_POST[$key] ?? '');
        Settings::set($key, $val);
    }

    // About image upload
    if (!empty($_FILES['about_image']['name'])) {
        $file = $_FILES['about_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            $error_msg = 'Invalid image format. Allowed: jpg, jpeg, png, webp, gif.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error_msg = 'Upload failed.';
        } else {
            $upload_dir = __DIR__ . '/../assets/img/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $filename = 'about_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                Settings::set('about_image', 'assets/img/uploads/' . $filename);
            } else {
                $error_msg = 'Failed to save uploaded file.';
            }
        }
    }

    if (!$error_msg) $success_msg = 'Settings saved successfully.';
}
?>
<div class="admin-card">
    <form method="POST" action="" enctype="multipart/form-data">
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin Location</label>
                        <input type="text" name="admin_location" class="form-control" value="<?= htmlspecialchars(Settings::get('admin_location', 'Moshi, Kilimanjaro')) ?>">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">About Page Image</label>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:120px;height:80px;border-radius:8px;overflow:hidden;background:#1e293b;flex-shrink:0;">
                                <?php $about_img = Settings::get('about_image', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80'); ?>
                                <img src="<?= htmlspecialchars($about_img) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <div>
                                <input type="file" name="about_image" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended: 600x400px. Upload will replace the current image.</small>
                            </div>
                        </div>
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
                        <label class="form-label">SMTP Encryption</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="ssl" <?= Settings::get('smtp_encryption', 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
                            <option value="tls" <?= Settings::get('smtp_encryption', 'ssl') === 'tls' ? 'selected' : '' ?>>TLS (port 587)</option>
                            <option value="none" <?= Settings::get('smtp_encryption', 'ssl') === 'none' ? 'selected' : '' ?>>None (port 25)</option>
                        </select>
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
