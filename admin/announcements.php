<?php
$page_title = 'Announcements';
$active_page = 'announcements';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $text = trim($_POST['announcement_text'] ?? '');
    $color = trim($_POST['text_color'] ?? '#FFFFFF');
    $badge = trim($_POST['badge'] ?? '');
    $badge_bg = trim($_POST['badge_bg'] ?? '#DC2626');

    if ($edit_id > 0) {
        if ($text !== '') {
            try {
                $stmt = $pdo->prepare("UPDATE announcements SET announcement_text = ?, text_color = ?, badge = ?, badge_bg = ? WHERE id = ?");
                $stmt->execute([$text, $color, $badge, $badge_bg, $edit_id]);
                $success_msg = 'Announcement updated!';
            } catch (Exception $e) {
                error_log('update_announcement DB error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        } else {
            $error_msg = 'Please enter announcement text.';
        }
    } elseif ($text !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO announcements (announcement_text, text_color, badge, badge_bg, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$text, $color, $badge, $badge_bg]);
            $success_msg = 'Announcement added!';
        } catch (Exception $e) {
            error_log('add_announcement DB error: ' . $e->getMessage());
            $error_msg = 'Database error. Please try again.';
        }
    } else {
        $error_msg = 'Please enter announcement text.';
    }
}

$announcements = $pdo->query("SELECT id, announcement_text, text_color, badge, badge_bg, is_active, updated_at FROM announcements ORDER BY id ASC")->fetchAll();
require_once 'admin_header.php';

// Popular emojis for quick insertion
$popular_emojis = ['🔥', '🆕', '⚡', '🎉', '📢', '⭐', '💡', '🚀', '💎', '✨', '🎯', '💰', '🏆', '💥', '🛒', '🎨'];
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal"><i class="bi bi-plus-lg"></i> Add Announcement</button>
</div>

<?php if ($success_msg): ?>
<div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr><th>ID</th><th>Badge</th><th>Text</th><th>Color</th><th>Status</th><th>Updated</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="7" class="text-muted text-center">No announcements found.</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td>
                            <?php if (!empty($a['badge'])): ?>
                            <span class="badge" style="background:<?= htmlspecialchars($a['badge_bg']) ?>;color:#fff;font-size:0.72rem;padding:3px 8px;"><?= htmlspecialchars($a['badge']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($a['announcement_text']) ?></td>
                        <td>
                            <form method="POST" action="update_announcement_color" class="d-inline">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <input type="color" name="text_color" value="<?= htmlspecialchars($a['text_color']) ?>" class="form-control form-control-color" style="width:36px;height:28px;padding:2px;display:inline-block;vertical-align:middle;cursor:pointer;" onchange="this.form.submit()" title="Click to change color">
                            </form>
                            <code class="text-muted small ms-1"><?= htmlspecialchars($a['text_color']) ?></code>
                        </td>
                        <td><?= $a['is_active'] ? '<span class="text-cyan">Active</span>' : '<span class="text-muted">Inactive</span>' ?></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($a['updated_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit-announcement"
                                data-id="<?= $a['id'] ?>"
                                data-text="<?= htmlspecialchars($a['announcement_text'], ENT_QUOTES) ?>"
                                data-color="<?= htmlspecialchars($a['text_color']) ?>"
                                data-badge="<?= htmlspecialchars($a['badge'] ?? '', ENT_QUOTES) ?>"
                                data-badge-bg="<?= htmlspecialchars($a['badge_bg'] ?? '#DC2626') ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="toggle_announcement?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Toggle active/inactive"><i class="bi bi-arrow-repeat"></i></a>
                            <a href="delete_announcement?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="announcement"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add New Announcement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Announcement Text</label>
                            <input type="text" name="announcement_text" id="add_announcement_text" class="form-control" placeholder="Enter announcement text..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1">Popular Emojis <small class="text-muted">(click to insert)</small></label>
                            <div class="emoji-picker" style="display:flex;flex-wrap:wrap;gap:4px;padding:6px 8px;background:#1e293b;border-radius:6px;border:1px solid #334155;">
                                <?php foreach ($popular_emojis as $emoji): ?>
                                <button type="button" class="btn-emoji-insert" data-target="add_announcement_text" style="background:none;border:none;font-size:1.2rem;cursor:pointer;padding:2px 5px;border-radius:4px;transition:background 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='none'"><?= $emoji ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Badge (optional)</label>
                            <input type="text" name="badge" class="form-control" placeholder="e.g. NEW, 🔥, ⚡" maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="badge_bg" class="form-control form-control-color" value="#DC2626" style="height:38px;padding:4px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Text Color</label>
                            <input type="color" name="text_color" class="form-control form-control-color" value="#FCD34D" style="height:38px;padding:4px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-plus-lg"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Edit Announcement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Announcement Text</label>
                            <input type="text" name="announcement_text" id="edit_announcement_text" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1">Popular Emojis <small class="text-muted">(click to insert)</small></label>
                            <div class="emoji-picker" style="display:flex;flex-wrap:wrap;gap:4px;padding:6px 8px;background:#1e293b;border-radius:6px;border:1px solid #334155;">
                                <?php foreach ($popular_emojis as $emoji): ?>
                                <button type="button" class="btn-emoji-insert" data-target="edit_announcement_text" style="background:none;border:none;font-size:1.2rem;cursor:pointer;padding:2px 5px;border-radius:4px;transition:background 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='none'"><?= $emoji ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Badge (optional)</label>
                            <input type="text" name="badge" id="edit_badge" class="form-control" placeholder="e.g. NEW, 🔥, ⚡" maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="badge_bg" id="edit_badge_bg" class="form-control form-control-color" value="#DC2626" style="height:38px;padding:4px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Text Color</label>
                            <input type="color" name="text_color" id="edit_text_color" class="form-control form-control-color" style="height:38px;padding:4px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once 'admin_footer.php'; ?>
<script>
$(function () {
    $(document).on('click', '.btn-emoji-insert', function () {
        var targetId = $(this).data('target');
        var input = $('#' + targetId);
        var emoji = $(this).text();
        var cursorPos = input[0].selectionStart;
        var val = input.val();
        input.val(val.substring(0, cursorPos) + emoji + val.substring(cursorPos));
        input[0].focus();
        input[0].setSelectionRange(cursorPos + emoji.length, cursorPos + emoji.length);
    });

    $('.btn-edit-announcement').on('click', function () {
        var id = $(this).data('id');
        var text = $(this).data('text');
        var color = $(this).data('color');
        var badge = $(this).data('badge') || '';
        var badgeBg = $(this).data('badge-bg') || '#DC2626';
        $('#edit_id').val(id);
        $('#edit_announcement_text').val(text);
        $('#edit_text_color').val(color);
        $('#edit_badge').val(badge);
        $('#edit_badge_bg').val(badgeBg);
        $('#editAnnouncementModal').modal('show');
    });
});
</script>
