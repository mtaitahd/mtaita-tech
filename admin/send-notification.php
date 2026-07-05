<?php
$page_title = 'Send Notification';
$active_page = 'notifications';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/SMS.php';

$stats = [];
try {
    $sms = new SMS();
    $stats = $sms->getAccountStats();
} catch (Exception $e) {}

// Handle template CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_template'])) {
        $name = trim($_POST['tpl_name'] ?? '');
        $category = trim($_POST['tpl_category'] ?? 'general');
        $subject = trim($_POST['tpl_subject'] ?? '');
        $message = trim($_POST['tpl_message'] ?? '');
        $placeholders = trim($_POST['tpl_placeholders'] ?? '');
        $tplId = (int)($_POST['tpl_id'] ?? 0);

        if (empty($name) || empty($message)) {
            $error_msg = 'Template name and message are required.';
        } else {
            try {
                if ($tplId > 0) {
                    $stmt = $pdo->prepare("UPDATE notification_templates SET name=?, category=?, subject=?, message=?, placeholders=? WHERE id=?");
                    $stmt->execute([$name, $category, $subject, $message, $placeholders, $tplId]);
                    $success_msg = 'Template updated.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO notification_templates (name, category, subject, message, placeholders) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $category, $subject, $message, $placeholders]);
                    $success_msg = 'Template created.';
                }
            } catch (Exception $e) {
                $error_msg = 'Database error.';
            }
        }
    }

    if (isset($_POST['delete_template'])) {
        $tplId = (int)($_POST['tpl_id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM notification_templates WHERE id = ?")->execute([$tplId]);
            $success_msg = 'Template deleted.';
        } catch (Exception $e) {
            $error_msg = 'Database error.';
        }
    }

    if (isset($_POST['delete_log'])) {
        $logId = (int)($_POST['log_id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM sms_log WHERE id = ?")->execute([$logId]);
            $success_msg = 'Log entry deleted.';
        } catch (Exception $e) {
            $error_msg = 'Database error.';
        }
    }

    if (isset($_POST['send_sms'])) {
        $message = trim($_POST['message'] ?? '');
        $recipient_type = $_POST['recipient_type'] ?? 'all';
        $test_phone = trim($_POST['test_phone'] ?? '');

        if (empty($message)) {
            $error_msg = 'Message is required.';
        } elseif ($recipient_type === 'test' && empty($test_phone)) {
            $error_msg = 'Phone number is required for test send.';
        } else {
            $sms = new SMS();
            $recipients = [];
            $sentCount = 0;
            $allPhones = [];

            if ($recipient_type === 'test') {
                $recipients[] = ['phone' => $test_phone, 'name' => 'Test'];
            } else {
                $userQuery = "SELECT phone, name FROM public_users WHERE phone IS NOT NULL AND phone != ''";
                $stmt = $pdo->query($userQuery);
                $recipients = $stmt->fetchAll();
            }

            if (empty($recipients)) {
                $error_msg = 'No recipients found.';
            } else {
                foreach ($recipients as $r) {
                    $personalized = $message;
                    $personalized = str_replace('{name}', $r['name'] ?? '', $personalized);
                    $personalized = str_replace('{phone}', $r['phone'] ?? '', $personalized);

                    if ($sms->send($r['phone'], $personalized)) {
                        $sentCount++;
                    }
                    $allPhones[] = $r['phone'];
                }

                if ($sentCount > 0) {
                    $success_msg = 'Notification sent to ' . $sentCount . ' of ' . count($recipients) . ' recipient(s).';
                    try {
                        $insertStmt = $pdo->prepare("INSERT INTO sms_log (type, recipient, message, status) VALUES ('manual', ?, ?, 'sent')");
                        $insertStmt->execute([implode(', ', $allPhones), $message]);
                    } catch (Exception $e) {}
                } else {
                    $error_msg = 'Failed to send notification. Check your SMS balance and API key.';
                }
            }
        }
    }
}

// Fetch templates
$templates = $pdo->query("SELECT * FROM notification_templates WHERE is_active = 1 ORDER BY category, name")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM notification_templates WHERE is_active = 1 ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);

require_once 'admin_header.php';
?>
<ul class="nav nav-tabs mb-4" id="notifTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="send-tab" data-bs-toggle="tab" data-bs-target="#send" type="button" role="tab">
            <i class="fas fa-paper-plane me-1"></i> Send Notification
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" type="button" role="tab">
            <i class="fas fa-template me-1"></i> Message Templates
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Send Tab -->
    <div class="tab-pane fade show active" id="send" role="tabpanel">
        <?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
        <?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

        <div class="admin-card">
            <div class="card-body">
                <form method="POST" action="send-notification">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Load from template</label>
                        <select class="form-select" id="templateSelect" onchange="loadTemplate(this.value)">
                            <option value="">-- Select a template --</option>
                            <?php foreach ($categories as $cat): ?>
                            <optgroup label="<?= htmlspecialchars(ucfirst($cat)) ?>">
                                <?php foreach ($templates as $t): ?>
                                <?php if ($t['category'] === $cat): ?>
                                <option value="<?= $t['id'] ?>" data-subject="<?= htmlspecialchars($t['subject'] ?? '') ?>" data-message="<?= htmlspecialchars($t['message']) ?>" data-placeholders="<?= htmlspecialchars($t['placeholders'] ?? '') ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select a template to pre-fill the message. Placeholders like <code>{name}</code> will be replaced automatically.</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Recipients</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" value="all" id="rAll" checked>
                                <label class="form-check-label" for="rAll">All users with phone numbers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" value="test" id="rTest">
                                <label class="form-check-label" for="rTest">Send test to single number</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="testPhoneGroup" style="display:none;">
                        <label class="form-label">Test Phone Number</label>
                        <input type="tel" name="test_phone" class="form-control" placeholder="255744963858">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject <small class="text-muted">(optional)</small></label>
                        <input type="text" name="subject" id="msgSubject" class="form-control" placeholder="Notification subject" style="border:1px solid #d1d3e2;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message</label>
                        <textarea name="message" id="msgBody" class="form-control" rows="6" required placeholder="Type your message here..." style="border:1px solid #d1d3e2;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <span><span id="charCount">0</span> characters</span>
                            <span class="text-muted" id="placeholderHelp" style="display:none;">
                                Available: <code id="availablePlaceholders"></code>
                            </span>
                        </div>
                    </div>

                    <button type="submit" name="send_sms" class="btn btn-cyan">
                        <i class="fas fa-paper-plane me-1"></i> Send Notification
                    </button>
                </form>

                <?php if ($stats && isset($stats['balance'])): ?>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Balance (TZS)</div>
                            <div class="fw-bold fs-5"><?= number_format($stats['balance'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Total Sent</div>
                            <div class="fw-bold fs-5"><?= number_format($stats['total_messages_sent'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Successful</div>
                            <div class="fw-bold fs-5"><?= number_format($stats['successful_deliveries'] ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Success Rate</div>
                            <div class="fw-bold fs-5"><?= ($stats['success_rate'] ?? 0) ?>%</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Recent Notifications</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Recipients</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $logs = $pdo->query("SELECT * FROM sms_log WHERE type = 'manual' ORDER BY created_at DESC LIMIT 20")->fetchAll();
                            if ($logs): foreach ($logs as $log):
                                $phones = array_map('trim', explode(',', $log['recipient'] ?? ''));
                                $recipientInfo = [];
                                foreach ($phones as $ph) {
                                    if (empty($ph)) continue;
                                    $uStmt = $pdo->prepare("SELECT name FROM public_users WHERE phone = ? LIMIT 1");
                                    $uStmt->execute([$ph]);
                                    $uName = $uStmt->fetchColumn();
                                    $recipientInfo[] = $uName ? htmlspecialchars($uName) . ' <small class="text-muted">(' . htmlspecialchars($ph) . ')</small>' : htmlspecialchars($ph);
                                }
                            ?>
                            <tr>
                                <td class="text-nowrap small"><?= htmlspecialchars($log['created_at']) ?></td>
                                <td class="small"><?= implode('<br>', $recipientInfo) ?></td>
                                <td class="small"><?= htmlspecialchars(mb_substr($log['message'], 0, 60)) ?><?= mb_strlen($log['message']) > 60 ? '...' : '' ?></td>
                                <td><span class="badge bg-<?= $log['status'] === 'sent' ? 'success' : ($log['status'] === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst($log['status']) ?></span></td>
                                <td class="text-nowrap">
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this log entry?')">
                                        <input type="hidden" name="log_id" value="<?= $log['id'] ?>">
                                        <button type="submit" name="delete_log" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No notifications sent yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Tab -->
    <div class="tab-pane fade" id="templates" role="tabpanel">
        <div class="admin-card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-template me-2"></i>Manage Templates</h6>
                <button class="btn btn-cyan btn-sm" onclick="openTemplateForm()"><i class="fas fa-plus me-1"></i> New Template</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Subject</th>
                                <th>Message preview</th>
                                <th>Placeholders</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $allTpls = $pdo->query("SELECT * FROM notification_templates ORDER BY category, name")->fetchAll(); ?>
                            <?php if ($allTpls): foreach ($allTpls as $t): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($t['name']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($t['category']) ?></span></td>
                                <td class="small text-muted"><?= htmlspecialchars($t['subject'] ?? '—') ?></td>
                                <td class="small"><?= htmlspecialchars(mb_substr($t['message'], 0, 50)) ?>...</td>
                                <td><code><?= htmlspecialchars($t['placeholders'] ?? '') ?></code></td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" onclick='openTemplateForm(<?= json_encode($t) ?>)'><i class="bi bi-pencil"></i></button>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this template?')">
                                        <input type="hidden" name="tpl_id" value="<?= $t['id'] ?>">
                                        <button type="submit" name="delete_template" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No templates yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Template form (hidden, shown via JS) -->
        <div class="admin-card" id="templateFormCard" style="display:none;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold" id="templateFormTitle"><i class="fas fa-plus me-2"></i>New Template</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="send-notification">
                    <input type="hidden" name="tpl_id" id="tplId" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Template Name</label>
                            <input type="text" name="tpl_name" id="tplName" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="tpl_category" id="tplCategory" class="form-select">
                                <option value="general">General</option>
                                <option value="onboarding">Onboarding</option>
                                <option value="enrollment">Enrollment</option>
                                <option value="payment">Payment</option>
                                <option value="completion">Completion</option>
                                <option value="promotional">Promotional</option>
                                <option value="account">Account</option>
                                <option value="reminder">Reminder</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="tpl_active" id="tplActive" value="1" checked>
                                <label class="form-check-label" for="tplActive">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject <small class="text-muted">(optional, supports placeholders)</small></label>
                            <input type="text" name="tpl_subject" id="tplSubject" class="form-control" placeholder="e.g. Welcome to {course_title}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message <small class="text-muted">(use <code>{name}</code>, <code>{course_title}</code>, etc.)</small></label>
                            <textarea name="tpl_message" id="tplMessage" class="form-control" rows="6" required style="border:1px solid #d1d3e2;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Placeholders <small class="text-muted">(comma-separated, e.g. name, course_title, amount)</small></label>
                            <input type="text" name="tpl_placeholders" id="tplPlaceholders" class="form-control" placeholder="name, course_title, amount">
                            <div class="form-text">These will be shown as hints when the template is selected.</div>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="save_template" class="btn btn-cyan"><i class="fas fa-save me-1"></i> Save Template</button>
                            <button type="button" class="btn btn-outline-secondary ms-2" onclick="closeTemplateForm()">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Template loader (send tab)
function loadTemplate(id) {
    if (!id) return;
    var opt = document.querySelector('#templateSelect option[value="' + id + '"]');
    if (!opt) return;
    document.getElementById('msgSubject').value = opt.dataset.subject || '';
    document.getElementById('msgBody').value = opt.dataset.message || '';
    document.getElementById('charCount').textContent = opt.dataset.message.length || 0;

    var ph = opt.dataset.placeholders || '';
    if (ph) {
        document.getElementById('placeholderHelp').style.display = 'inline';
        document.getElementById('availablePlaceholders').textContent = '{' + ph.replace(/,/g, ', ') + '}';
    } else {
        document.getElementById('placeholderHelp').style.display = 'none';
    }
}

// Template form (templates tab)
function openTemplateForm(data) {
    var card = document.getElementById('templateFormCard');
    card.style.display = 'block';
    if (data) {
        document.getElementById('templateFormTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Template';
        document.getElementById('tplId').value = data.id;
        document.getElementById('tplName').value = data.name;
        document.getElementById('tplCategory').value = data.category;
        document.getElementById('tplSubject').value = data.subject || '';
        document.getElementById('tplMessage').value = data.message;
        document.getElementById('tplPlaceholders').value = data.placeholders || '';
        document.getElementById('tplActive').checked = data.is_active == 1;
    } else {
        document.getElementById('templateFormTitle').innerHTML = '<i class="fas fa-plus me-2"></i>New Template';
        document.getElementById('tplId').value = 0;
        document.getElementById('tplName').value = '';
        document.getElementById('tplCategory').value = 'general';
        document.getElementById('tplSubject').value = '';
        document.getElementById('tplMessage').value = '';
        document.getElementById('tplPlaceholders').value = '';
        document.getElementById('tplActive').checked = true;
    }
    card.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closeTemplateForm() {
    document.getElementById('templateFormCard').style.display = 'none';
}

document.querySelectorAll('input[name="recipient_type"]').forEach(function(el) {
    el.addEventListener('change', function() {
        document.getElementById('testPhoneGroup').style.display = this.value === 'test' ? 'block' : 'none';
    });
});
document.querySelector('textarea[name="message"]').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});
</script>

<?php require_once 'admin_footer.php'; ?>
