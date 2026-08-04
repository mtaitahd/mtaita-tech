<?php
$page_title = 'Digital Products';
$active_page = 'products';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Product.php';

$productModel = new Product();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_product'])) {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'code';
        $price = (int)($_POST['price'] ?? 0);
        $is_paid = isset($_POST['is_paid']) ? 1 : 0;
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;
        $youtube_url = trim($_POST['youtube_url'] ?? '');

        $thumbnail = $_POST['existing_thumbnail'] ?? '';
        $zipFile = $_POST['existing_zip'] ?? '';

        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $file = $_FILES['thumbnail'];
            $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : $file['type'];
            if (in_array($mime, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                $upload_dir = __DIR__ . '/../assets/img/uploads/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($thumbnail) @unlink(__DIR__ . '/../' . $thumbnail);
                    $thumbnail = 'assets/img/uploads/products/' . $filename;
                }
            }
        }

        if (isset($_FILES['zip_file']) && $_FILES['zip_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_zip = ['application/zip', 'application/x-zip-compressed'];
            $file = $_FILES['zip_file'];
            $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : $file['type'];
            if (in_array($mime, $allowed_zip) || pathinfo($file['name'], PATHINFO_EXTENSION) === 'zip') {
                $upload_dir = __DIR__ . '/../assets/uploads/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $filename = 'product_' . uniqid() . '.zip';
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($zipFile) @unlink(__DIR__ . '/../' . $zipFile);
                    $zipFile = 'assets/uploads/products/' . $filename;
                }
            }
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'price' => $price,
            'is_paid' => $is_paid,
            'is_visible' => $is_visible,
            'youtube_url' => $youtube_url,
            'zip_file' => $zipFile,
            'thumbnail' => $thumbnail,
        ];

        if ($title && $description) {
            if ($id > 0) {
                $productModel->update($id, $data);
                $success_msg = 'Product updated!';
            } else {
                $productModel->create($data);
                $success_msg = 'Product added!';
            }
        } else {
            $error_msg = 'Title and description are required.';
        }
    }

    if (isset($_POST['delete_product'])) {
        $id = (int)($_POST['id'] ?? 0);
        $productModel->delete($id);
        $success_msg = 'Product deleted.';
    }

    if (isset($_POST['toggle_visibility'])) {
        $id = (int)($_POST['id'] ?? 0);
        $productModel->toggleVisibility($id);
        $success_msg = 'Visibility toggled.';
    }
}

$products = $productModel->getAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#productModal"><i class="bi bi-plus-lg"></i> Add Product</button>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thumb</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Paid</th>
                    <th>Visible</th>
                    <th>Downloads</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="9" class="text-muted text-center">No products yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <?php if ($p['thumbnail']): ?>
                                <img src="../<?= htmlspecialchars($p['thumbnail']) ?>" alt="" width="60" height="34" style="object-fit:cover;border-radius:4px;">
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['title']) ?></td>
                        <td><span class="badge bg-info"><?= $productModel->getTypeLabel($p['type']) ?></span></td>
                        <td><?= $p['is_paid'] ? 'TZS ' . number_format($p['price']) : '<span class="text-muted">Free</span>' ?></td>
                        <td><?= $p['is_paid'] ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-success">No</span>' ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="toggle_visibility" value="1">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm <?= $p['is_visible'] ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                                    <i class="bi bi-eye<?= $p['is_visible'] ? '' : '-slash' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td><?= $p['download_count'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#productModal"
                                data-id="<?= $p['id'] ?>"
                                data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                data-type="<?= $p['type'] ?>"
                                data-price="<?= $p['price'] ?>"
                                data-is-paid="<?= $p['is_paid'] ?>"
                                data-is-visible="<?= $p['is_visible'] ?>"
                                data-youtube="<?= htmlspecialchars($p['youtube_url'] ?? '', ENT_QUOTES) ?>"
                                data-thumbnail="<?= htmlspecialchars($p['thumbnail'] ?? '', ENT_QUOTES) ?>"
                                data-zip="<?= htmlspecialchars($p['zip_file'] ?? '', ENT_QUOTES) ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_product" value="1">
                <input type="hidden" name="id" id="product-id" value="0">
                <input type="hidden" name="existing_thumbnail" id="product-existing-thumbnail" value="">
                <input type="hidden" name="existing_zip" id="product-existing-zip" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-box text-cyan me-2"></i><span id="product-modal-title">New Product</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="product-title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="product-desc" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" id="product-type" class="form-select">
                                <option value="code">Source Code</option>
                                <option value="video">Video Tutorial</option>
                                <option value="both">Code + Video</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price (TZS)</label>
                            <input type="number" name="price" id="product-price" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-3 pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_paid" id="product-is-paid" class="form-check-input" value="1">
                                <label class="form-check-label" for="product-is-paid">Paid</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_visible" id="product-is-visible" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="product-is-visible">Visible</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" id="product-youtube" class="form-control" placeholder="https://youtu.be/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ZIP File</label>
                            <input type="file" name="zip_file" class="form-control" accept=".zip">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <div class="col-12" id="product-thumbnail-preview-wrap" style="display:none;">
                            <label class="form-label">Current Thumbnail</label>
                            <div><img id="product-thumbnail-preview" src="" alt="" height="56" style="object-fit:cover;border-radius:4px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('productModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var id = btn.dataset.id || '0';
            document.getElementById('product-id').value = id;
            document.getElementById('product-title').value = btn.dataset.title || '';
            document.getElementById('product-desc').value = btn.dataset.description || '';
            document.getElementById('product-type').value = btn.dataset.type || 'code';
            document.getElementById('product-price').value = btn.dataset.price || '0';
            document.getElementById('product-is-paid').checked = btn.dataset.isPaid === '1';
            document.getElementById('product-is-visible').checked = btn.dataset.isVisible !== '0';
            document.getElementById('product-youtube').value = btn.dataset.youtube || '';
            document.getElementById('product-existing-thumbnail').value = btn.dataset.thumbnail || '';
            document.getElementById('product-existing-zip').value = btn.dataset.zip || '';
            document.getElementById('product-modal-title').textContent = id !== '0' ? 'Edit Product' : 'New Product';

            var thumb = btn.dataset.thumbnail || '';
            var preview = document.getElementById('product-thumbnail-preview');
            var wrap = document.getElementById('product-thumbnail-preview-wrap');
            if (thumb) {
                preview.src = '../' + thumb;
                wrap.style.display = '';
            } else {
                wrap.style.display = 'none';
            }
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
