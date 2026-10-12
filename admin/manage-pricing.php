<?php
$page_title = 'Pricing';
$active_page = 'pricing';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Pricing.php';
require_once __DIR__ . '/../lib/Settings.php';

if (empty($_SESSION['pricing_csrf'])) {
    $_SESSION['pricing_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['pricing_csrf'];

$services = Pricing::services();

function pricing_csrf_ok(): bool
{
    return isset($_POST['csrf']) && hash_equals($_SESSION['pricing_csrf'] ?? '', (string)$_POST['csrf']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !pricing_csrf_ok()) {
    $error_msg = 'Your session expired. Please reload the page and try again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'save_pricing_settings') {
        $rate = trim($_POST['usd_rate'] ?? '');
        $year = trim($_POST['pricing_valid_year'] ?? '');
        if ($rate !== '' && (!is_numeric($rate) || (float)$rate < 0)) {
            $error_msg = 'The USD rate must be a positive number.';
        } elseif ($year !== '' && !preg_match('/^\d{4}$/', $year)) {
            $error_msg = 'The validity year must be a 4-digit year (for example 2026).';
        } else {
            Settings::set('usd_rate', $rate);
            Settings::set('pricing_valid_year', $year);
            $success_msg = 'Pricing settings saved successfully.';
        }
    }

    if ($action === 'add_package' || $action === 'edit_package') {
        $id = (int)($_POST['id'] ?? 0);
        $slug = trim($_POST['service_slug'] ?? '');
        $name = trim($_POST['package_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $tzs  = trim($_POST['price_tzs'] ?? '');
        $usd  = trim($_POST['price_usd'] ?? '');
        $billing = ($_POST['billing'] ?? 'one-off') === 'annual' ? 'annual' : 'one-off';
        $order = (int)($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if (!Pricing::isValidService($slug)) $errors[] = 'Please choose a valid service.';
        if ($name === '') $errors[] = 'The package name is required.';
        if ($tzs === '' || !is_numeric($tzs) || (float)$tzs < 0) $errors[] = 'The TZS price is required and must be a positive number.';
        if ($usd !== '' && (!is_numeric($usd) || (float)$usd < 0)) $errors[] = 'The USD price must be a positive number or left empty.';
        if ($action === 'edit_package' && $id < 1) $errors[] = 'Invalid package.';

        if (!empty($errors)) {
            $error_msg = implode(' ', $errors);
        } else {
            $tzsVal = (int)round((float)$tzs);
            $usdVal = $usd === '' ? null : round((float)$usd, 2);
            try {
                if ($action === 'add_package') {
                    $stmt = $pdo->prepare("INSERT INTO service_pricing (service_slug, package_name, description, price_tzs, price_usd, billing, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$slug, $name, $desc, $tzsVal, $usdVal, $billing, $order, $active]);
                    $success_msg = 'Package added successfully.';
                } else {
                    $stmt = $pdo->prepare("UPDATE service_pricing SET service_slug = ?, package_name = ?, description = ?, price_tzs = ?, price_usd = ?, billing = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$slug, $name, $desc, $tzsVal, $usdVal, $billing, $order, $active, $id]);
                    $success_msg = 'Package updated successfully.';
                }
            } catch (Exception $e) {
                error_log('pricing save error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        }
    }

    if ($action === 'delete_package') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("DELETE FROM service_pricing WHERE id = ?")->execute([$id]);
                $success_msg = 'Package deleted.';
            } catch (Exception $e) {
                error_log('pricing delete error: ' . $e->getMessage());
                $error_msg = 'Could not delete the package.';
            }
        }
    }

    if ($action === 'toggle_package') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("UPDATE service_pricing SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
                $success_msg = 'Package visibility updated.';
            } catch (Exception $e) {
                error_log('pricing toggle error: ' . $e->getMessage());
                $error_msg = 'Could not update the package.';
            }
        }
    }
}

$packages = [];
try {
    $packages = $pdo->query("SELECT * FROM service_pricing ORDER BY service_slug ASC, sort_order ASC, id ASC")->fetchAll();
} catch (Exception $e) {
    $error_msg = $error_msg ?: 'The service_pricing table is missing. Please run database/migration_012_service_pricing.sql.';
}

$usd_rate = Settings::get('usd_rate', '');
$valid_year = Settings::get('pricing_valid_year', '');

require_once 'admin_header.php';
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 text-gray-800">Pricing</h4>
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addPackageModal"><i class="bi bi-plus-lg"></i> Add Package</button>
</div>

<?php if ($success_msg): ?>
<div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="admin-card mb-4">
    <h5 class="mb-3"><i class="bi bi-sliders text-cyan me-2"></i>Pricing Settings</h5>
    <form method="POST" class="row g-3 align-items-end">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="action" value="save_pricing_settings">
        <div class="col-md-4">
            <label class="form-label">USD Exchange Rate (1 USD = ? TZS)</label>
            <input type="number" name="usd_rate" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars((string)$usd_rate) ?>" placeholder="e.g. 2600">
            <small class="text-muted">Used to convert TZS prices when no USD price is set. Leave empty to hide the USD switch.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Price Validity Year</label>
            <input type="text" name="pricing_valid_year" class="form-control" maxlength="4" value="<?= htmlspecialchars((string)$valid_year) ?>" placeholder="e.g. 2026">
            <small class="text-muted">Shown as "Valid for 2026" on the public page.</small>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Settings</button>
        </div>
    </form>
</div>

<div class="admin-card">
    <h5 class="mb-3"><i class="bi bi-tags text-cyan me-2"></i>Packages</h5>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th><th>Service</th><th>Package</th><th>TZS</th><th>USD</th><th>Billing</th><th>Order</th><th>Active</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($packages)): ?>
                    <tr><td colspan="9" class="text-muted text-center">No pricing packages yet. Click "Add Package" to create the first one.</td></tr>
                <?php else: ?>
                    <?php foreach ($packages as $p): ?>
                    <tr>
                        <td><?= (int)$p['id'] ?></td>
                        <td><span class="badge bg-dark"><?= htmlspecialchars(Pricing::label($p['service_slug'])) ?></span></td>
                        <td>
                            <div><?= htmlspecialchars($p['package_name']) ?></div>
                            <?php if (!empty($p['description'])): ?>
                                <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($p['description'], 0, 70, '…')) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format((float)$p['price_tzs']) ?></td>
                        <td><?= $p['price_usd'] !== null && $p['price_usd'] !== '' ? number_format((float)$p['price_usd'], 2) : '—' ?></td>
                        <td><?= $p['billing'] === 'annual' ? 'Annual' : 'One-off' ?></td>
                        <td><?= (int)$p['sort_order'] ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="action" value="toggle_package">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn btn-sm <?= !empty($p['is_active']) ? 'btn-outline-cyan' : 'btn-outline-secondary' ?>" title="Toggle visibility">
                                    <i class="bi <?= !empty($p['is_active']) ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#editPackageModal"
                                data-id="<?= (int)$p['id'] ?>"
                                data-slug="<?= htmlspecialchars($p['service_slug'], ENT_QUOTES) ?>"
                                data-name="<?= htmlspecialchars($p['package_name'], ENT_QUOTES) ?>"
                                data-desc="<?= htmlspecialchars($p['description'], ENT_QUOTES) ?>"
                                data-tzs="<?= htmlspecialchars((string)((int)$p['price_tzs']), ENT_QUOTES) ?>"
                                data-usd="<?= htmlspecialchars((string)$p['price_usd'], ENT_QUOTES) ?>"
                                data-billing="<?= htmlspecialchars($p['billing'], ENT_QUOTES) ?>"
                                data-order="<?= (int)$p['sort_order'] ?>"
                                data-active="<?= !empty($p['is_active']) ? 1 : 0 ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this pricing package?');">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="action" value="delete_package">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Package Modal -->
<div class="modal fade" id="addPackageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="add_package">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add Pricing Package</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Service</label>
                            <select name="service_slug" class="form-control" required>
                                <?php foreach ($services as $slug => $meta): ?>
                                    <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($meta['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package Name</label>
                            <input type="text" name="package_name" class="form-control" placeholder="e.g. Starter Website" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control" maxlength="500" placeholder="Short description of what is included."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price (TZS)</label>
                            <input type="number" name="price_tzs" class="form-control" min="0" step="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price (USD) — optional</label>
                            <input type="number" name="price_usd" class="form-control" min="0" step="0.01" placeholder="Auto-converted if empty">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Billing</label>
                            <select name="billing" class="form-control">
                                <option value="one-off">One-off</option>
                                <option value="annual">Annual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" step="1">
                        </div>
                        <div class="col-md-8 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="addIsActive" checked>
                                <label class="form-check-label" for="addIsActive">Visible on the public pricing page</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-plus-lg"></i> Add Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Package Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="edit_package">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-cyan me-2"></i>Edit Pricing Package</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Service</label>
                            <select name="service_slug" id="edit-slug" class="form-control" required>
                                <?php foreach ($services as $slug => $meta): ?>
                                    <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($meta['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package Name</label>
                            <input type="text" name="package_name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit-desc" rows="2" class="form-control" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price (TZS)</label>
                            <input type="number" name="price_tzs" id="edit-tzs" class="form-control" min="0" step="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price (USD) — optional</label>
                            <input type="number" name="price_usd" id="edit-usd" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Billing</label>
                            <select name="billing" id="edit-billing" class="form-control">
                                <option value="one-off">One-off</option>
                                <option value="annual">Annual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="edit-order" class="form-control" value="0" step="1">
                        </div>
                        <div class="col-md-8 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">Visible on the public pricing page</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editPackageModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('edit-id').value = btn.dataset.id;
            document.getElementById('edit-slug').value = btn.dataset.slug;
            document.getElementById('edit-name').value = btn.dataset.name;
            document.getElementById('edit-desc').value = btn.dataset.desc || '';
            document.getElementById('edit-tzs').value = btn.dataset.tzs;
            document.getElementById('edit-usd').value = btn.dataset.usd || '';
            document.getElementById('edit-billing').value = btn.dataset.billing || 'one-off';
            document.getElementById('edit-order').value = btn.dataset.order || 0;
            document.getElementById('editIsActive').checked = btn.dataset.active == '1';
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
