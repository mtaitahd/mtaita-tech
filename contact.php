<?php
$page_title = 'Contact Mtaita Tech | IT Company Kilimanjaro';
$page_desc = 'Contact Mtaita Tech in Kilimanjaro. Get a free quote for website development, mobile apps, POS systems, inventory software or custom solutions.';
$page_keywords = 'contact Mtaita Tech, IT company Moshi, software developer Kilimanjaro, web designer Arusha, business system Tanzania, website quote Tanzania';
require_once 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_contact'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $phone && $service && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, service, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $service, $message]);
            $success = 'Thank you! Your message has been sent. We\'ll get back to you soon.';
        } catch (Exception $e) {
            error_log('contact DB save error: ' . $e->getMessage());
            $error = 'Sorry, something went wrong. Please try again later.';
        }

        if ($success) {
            $details = "Name: $name\nEmail: $email\nPhone: $phone\nService: $service\nMessage: $message";

            require_once __DIR__ . '/lib/Settings.php';
            $admin_email = Settings::get('admin_email', ADMIN_EMAIL);
            $admin_phone = Settings::get('admin_phone', '+255 616 591 639');

            require_once __DIR__ . '/mailer.php';
            require_once __DIR__ . '/email_template.php';
            $mailer = new Mailer();
            $bodyHtml = '
            <table style="width:100%;font-size:14px;color:#475569;line-height:1.6;">
                <tr><td style="padding:4px 0;font-weight:600;color:#1e293b;width:80px;">Name</td><td style="padding:4px 0;">' . htmlspecialchars($name) . '</td></tr>
                <tr><td style="padding:4px 0;font-weight:600;color:#1e293b;">Email</td><td style="padding:4px 0;">' . htmlspecialchars($email) . '</td></tr>
                <tr><td style="padding:4px 0;font-weight:600;color:#1e293b;">Phone</td><td style="padding:4px 0;">' . htmlspecialchars($phone) . '</td></tr>
                <tr><td style="padding:4px 0;font-weight:600;color:#1e293b;">Service</td><td style="padding:4px 0;">' . htmlspecialchars($service) . '</td></tr>
                <tr><td style="padding:4px 0;font-weight:600;color:#1e293b;">Message</td><td style="padding:4px 0;">' . nl2br(htmlspecialchars($message)) . '</td></tr>
            </table>';
            $mailResult = $mailer->send([$admin_email], "New Contact Inquiry from $name", buildEmailHtml('New Contact Inquiry', $bodyHtml), true);
            if (!$mailResult) {
                error_log("Contact form: email notification failed for $name ($email)");
            }

            require_once __DIR__ . '/lib/SMS.php';
            $sms = new SMS();
            $smsMsg = "New inquiry: $name, $email, $phone, $service: $message";
            $smsResult = $sms->send($admin_phone, $smsMsg);
            if (!$smsResult) {
                error_log("Contact form: SMS notification failed for $name ($email) - HTTP " . $sms->getLastHttpCode() . ': ' . $sms->getLastResponse());
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<section class="page-header">
    <div class="container">
        <h1><?= __('contact_title') ?></h1>
        <p><?= __('contact_subtitle') ?></p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <h2><?= __('contact_get_in_touch') ?></h2>
                <p class="text-muted mb-4"><?= __('contact_form_text') ?></p>

                <?php if ($success): ?>
                    <div class="d-none swal-msg" data-type="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="d-none swal-msg" data-type="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label"><?= __('contact_name') ?></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('contact_email') ?></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('contact_phone') ?></label>
                        <input type="tel" name="phone" class="form-control" required placeholder="+255 XXX XXX XXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('contact_service') ?></label>
                        <select name="service" class="form-select" required>
                            <option value=""><?= __('contact_service_placeholder') ?></option>
                            <option value="Web Development"><?= __('contact_service_web') ?></option>
                            <option value="Graphic Design"><?= __('contact_service_graphic') ?></option>
                            <option value="Mobile App Development"><?= __('contact_service_mobile') ?></option>
                            <option value="SEO & Digital Marketing"><?= __('contact_service_seo') ?></option>
                            <option value="IT Consulting"><?= __('contact_service_consulting') ?></option>
                            <option value="Other"><?= __('contact_service_other') ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('contact_message') ?></label>
                        <textarea name="message" rows="5" class="form-control" required></textarea>
                    </div>
                    <button type="submit" name="send_contact" class="btn-red w-100"><?= __('contact_send') ?></button>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="contact-card">
                    <h4><?= __('contact_info') ?></h4>
                    <?php
                    require_once __DIR__ . '/lib/Settings.php';
                    $contact_admin_email = Settings::get('admin_email', ADMIN_EMAIL);
                    $contact_admin_phone = Settings::get('admin_phone', '+255 616 591 639');
                    ?>
                    <ul class="contact-info-list">
                        <li><i class="bi bi-envelope"></i> <?= htmlspecialchars($contact_admin_email) ?></li>
                        <li><i class="bi bi-phone"></i> <?= htmlspecialchars($contact_admin_phone) ?></li>
                        <li><i class="bi bi-geo-alt"></i> Moshi, Kilimanjaro</li>
                        <li><i class="bi bi-clock"></i> Mon - Sat: 8:00 AM - 6:00 PM</li>
                    </ul>
                    <div class="mt-4">
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                            <div id="map" style="width:100%;height:100%;border:0;border-radius:12px;"></div>
                        </div>
                        <p class="text-muted small mt-2"><i class="bi bi-crosshair me-1"></i><span id="mapStatus">Detecting your location...</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([-3.3349, 37.3265], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

document.getElementById('mapStatus').textContent = 'Fetching your location...';

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        map.setView([lat, lng], 15);
        L.marker([lat, lng]).addTo(map)
            .bindPopup('You are here')
            .openPopup();
        document.getElementById('mapStatus').textContent = 'Showing your current location.';
    }, function() {
        // Fallback: show Moshi office
        L.marker([-3.3349, 37.3265]).addTo(map)
            .bindPopup('Mtaita Tech — Moshi, Kilimanjaro')
            .openPopup();
        document.getElementById('mapStatus').textContent = 'Could not detect location — showing our office.';
    });
} else {
    L.marker([-3.3349, 37.3265]).addTo(map)
        .bindPopup('Mtaita Tech — Moshi, Kilimanjaro')
        .openPopup();
    document.getElementById('mapStatus').textContent = 'Geolocation not supported — showing our office.';
}
</script>

<?php
require_once 'footer.php';
?>
