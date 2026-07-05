<?php
$page_title = 'Messages';
$active_page = 'contacts';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $cid = (int)($_POST['id'] ?? 0);
    if ($cid > 0) {
        $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?")->execute([$cid]);
        $success_msg = 'Marked as read.';
    }
}

// Delete single contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_contact'])) {
    $cid = (int)($_POST['id'] ?? 0);
    if ($cid > 0) {
        $pdo->prepare("DELETE FROM contacts WHERE id = ?")->execute([$cid]);
        $success_msg = 'Message deleted.';
    }
}

// Delete all contacts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_contacts'])) {
    $pdo->exec("DELETE FROM contacts");
    $success_msg = 'All messages deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_contact'])) {
    $cid = (int)($_POST['id'] ?? 0);
    $reply_text = trim($_POST['reply_text'] ?? '');

    if ($cid > 0 && $reply_text !== '') {
        $stmt = $pdo->prepare("SELECT name, email FROM contacts WHERE id = ? LIMIT 1");
        $stmt->execute([$cid]);
        $contact = $stmt->fetch();

        if ($contact) {
            require_once __DIR__ . '/../mailer.php';
            require_once __DIR__ . '/../email_template.php';
            $mailer = new Mailer();
            $replyEmail = Settings::get('reply_email', 'info@mtaitatech.online');
            $fromEmail = Settings::get('from_email', '');
            $mailer->setReplyTo($replyEmail);
            $subject = "Re: Your message to Mtaita Tech";
            $bodyHtml = '
            <p style="margin:0 0 16px;font-size:15px;color:#475569;line-height:1.6;">Hi <strong>' . htmlspecialchars($contact['name']) . '</strong>,</p>
            <p style="margin:0 0 16px;font-size:15px;color:#475569;line-height:1.6;">Thank you for reaching out to us.</p>
            <div style="background:#F8FAFC;border-radius:8px;padding:16px;margin:16px 0;border-left:3px solid #DC2626;font-size:14px;color:#334155;line-height:1.6;">' . nl2br(htmlspecialchars($reply_text)) . '</div>
            <p style="margin:16px 0 0;font-size:15px;color:#475569;line-height:1.6;">Best regards,<br><strong>Mtaita Tech Team</strong></p>';
            $body = buildEmailHtml('We received your message', $bodyHtml);
            $sent = $mailer->send($contact['email'], $subject, $body, true);

            if ($sent) {
                $pdo->prepare("UPDATE contacts SET is_read = 1, replied_at = NOW() WHERE id = ?")->execute([$cid]);
                $success_msg = 'Reply sent to ' . htmlspecialchars($contact['email']) . ' (From: ' . htmlspecialchars($fromEmail) . ', Reply-To: ' . htmlspecialchars($replyEmail) . ')';
            } else {
                $error_msg = 'Failed to send reply email. Check SMTP settings. (From: ' . htmlspecialchars($fromEmail) . ')';
            }
        } else {
            $error_msg = 'Contact not found.';
        }
    } else {
        $error_msg = 'Reply text is required.';
    }
}

$contacts = $pdo->query("SELECT id, name, email, phone, service, message, is_read, replied_at, created_at FROM contacts ORDER BY created_at DESC")->fetchAll();
$unread_count = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
require_once 'admin_header.php';
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 style="margin:0;display:inline;"><i class="bi bi-envelope me-2 text-cyan"></i>Messages</h4>
        <span class="badge bg-cyan ms-2" id="unread-badge" style="display:<?= ($unread_count ?? 0) > 0 ? 'inline' : 'none' ?>"><?= ($unread_count ?? 0) ?> unread</span>
    </div>
    <?php if (!empty($contacts)): ?>
    <form method="POST" action="" onsubmit="return confirm('Delete ALL messages? This cannot be undone.');">
        <button type="submit" name="delete_all_contacts" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i> Delete All</button>
    </form>
    <?php endif; ?>
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
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Service</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Replied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr><td colspan="8" class="text-muted text-center">No messages yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                    <tr class="<?= !$c['is_read'] ? 'fw-bold' : 'text-muted' ?>">
                        <td>
                            <?php if (!$c['is_read']): ?>
                                <span class="badge bg-cyan"><i class="bi bi-envelope-fill"></i></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-envelope-open"></i></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="text-cyan small"><?= htmlspecialchars($c['email']) ?></a></td>
                        <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                        <td><span class="badge bg-cyan text-dark"><?= htmlspecialchars($c['service'] ?? '—') ?></span></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($c['message']) ?>">
                            <?= htmlspecialchars(mb_strimwidth($c['message'], 0, 80, '...')) ?>
                        </td>
                        <td class="small"><?= date('M d, Y H:i', strtotime($c['created_at'])) ?></td>
                        <td class="small"><?= $c['replied_at'] ? date('M d, Y', strtotime($c['replied_at'])) : '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#viewContactModal"
                                data-id="<?= $c['id'] ?>"
                                data-name="<?= htmlspecialchars($c['name']) ?>"
                                data-email="<?= htmlspecialchars($c['email']) ?>"
                                data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>"
                                data-service="<?= htmlspecialchars($c['service'] ?? '') ?>"
                                data-message="<?= htmlspecialchars($c['message']) ?>"
                                data-date="<?= $c['created_at'] ?>"
                                data-replied="<?= $c['replied_at'] ?? '' ?>"
                                <?= $c['is_read'] ? '' : 'data-unread="1"' ?>
                                title="View message"><i class="bi bi-eye"></i></button>
                            <?php if (!$c['replied_at']): ?>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#replyContactModal"
                                data-id="<?= $c['id'] ?>"
                                data-name="<?= htmlspecialchars($c['name']) ?>"
                                data-email="<?= htmlspecialchars($c['email']) ?>"
                                title="Reply"><i class="bi bi-reply"></i></button>
                            <?php endif; ?>
                            <?php if (!$c['is_read']): ?>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" name="mark_read" class="btn btn-sm btn-outline-secondary" title="Mark read"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this message?');">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" name="delete_contact" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope text-cyan me-2"></i>Message</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>From:</strong> <span id="view-name"></span> (<span id="view-email" class="text-cyan"></span>)</p>
                <p><strong>Phone:</strong> <span id="view-phone"></span></p>
                <p><strong>Service:</strong> <span id="view-service" class="badge bg-cyan text-dark"></span></p>
                <p><strong>Date:</strong> <span id="view-date" class="text-muted"></span></p>
                <p><strong>Replied:</strong> <span id="view-replied" class="text-muted"></span></p>
                <hr>
                <div id="view-message" style="white-space:pre-wrap;background:#1E293B;padding:1rem;border-radius:6px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="replyContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="reply_contact" value="1">
                <input type="hidden" name="id" id="reply-id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-reply text-cyan me-2"></i>Reply to <span id="reply-name"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Replying to: <span id="reply-email" class="text-cyan"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Quick Template</label>
                        <select class="form-select" id="reply-template">
                            <option value="">— Select a template —</option>
                            <option value="Thank you for reaching out to Mtaita Tech. We appreciate your message and will get back to you as soon as possible. If you have any urgent concerns, please call us directly.">General Acknowledgment</option>
                            <option value="Thank you for your interest in our services. We have received your request and will prepare a quotation for you. You can expect to hear from us within 24 hours during business days.">Quote Request Response</option>
                            <option value="We have received your support request and it has been forwarded to our technical team. We will follow up with you shortly. For urgent assistance, please call us.">Support Request Response</option>
                            <option value="Thank you for your inquiry. We have noted your details and requirements. Our team will review them and contact you with the relevant information and next steps.">General Inquiry Response</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Reply</label>
                        <textarea name="reply_text" rows="6" class="form-control" id="reply-text" placeholder="Type your reply here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-send"></i> Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewModal = document.getElementById('viewContactModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('view-name').textContent = btn.dataset.name;
            document.getElementById('view-email').textContent = btn.dataset.email;
            document.getElementById('view-phone').textContent = btn.dataset.phone;
            document.getElementById('view-service').textContent = btn.dataset.service;
            document.getElementById('view-date').textContent = btn.dataset.date;
            document.getElementById('view-replied').textContent = btn.dataset.replied || 'Not replied';
            document.getElementById('view-message').textContent = btn.dataset.message;
            if (btn.dataset.unread === '1') {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('mark_read=1&id=' + btn.dataset.id);
                btn.closest('tr').classList.remove('fw-bold');
                btn.closest('tr').classList.add('text-muted');
                delete btn.dataset.unread;
            }
        });
    }

    var replyModal = document.getElementById('replyContactModal');
    if (replyModal) {
        replyModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('reply-id').value = btn.dataset.id;
            document.getElementById('reply-name').textContent = btn.dataset.name;
            document.getElementById('reply-email').textContent = btn.dataset.email;
            document.getElementById('reply-text').value = '';
            document.getElementById('reply-template').value = '';
        });
    }

    var templateSelect = document.getElementById('reply-template');
    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            var textarea = document.getElementById('reply-text');
            if (this.value) {
                textarea.value = this.value;
            }
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
