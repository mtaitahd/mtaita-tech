</main>

<?php if (empty($hide_navbar)): ?>
<?php
require_once __DIR__ . '/lib/Settings.php';
$ft_email = Settings::get('admin_email', ADMIN_EMAIL);
$ft_phone = Settings::get('admin_phone', '+255 616 591 639');
$ft_location = Settings::get('admin_location', 'Moshi, Kilimanjaro');
$ft_phone_link = preg_replace('/\s+/', '', $ft_phone);
?>
<footer class="footer">
    <div class="container">
        <!-- Row 1: Contact information -->
        <div class="footer-contact-row">
            <div class="footer-contact-item">
                <span class="footer-contact-icon" aria-hidden="true"><i class="bi bi-telephone-fill"></i></span>
                <span class="footer-contact-text">
                    <span class="footer-contact-label"><?= __('footer_call_us') ?></span>
                    <a href="tel:<?= htmlspecialchars($ft_phone_link) ?>"><?= htmlspecialchars($ft_phone) ?></a>
                </span>
            </div>
            <div class="footer-contact-item">
                <span class="footer-contact-icon" aria-hidden="true"><i class="bi bi-envelope-fill"></i></span>
                <span class="footer-contact-text">
                    <span class="footer-contact-label"><?= __('footer_email_us') ?></span>
                    <a href="mailto:<?= htmlspecialchars($ft_email) ?>"><?= htmlspecialchars($ft_email) ?></a>
                </span>
            </div>
            <div class="footer-contact-item">
                <span class="footer-contact-icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></span>
                <span class="footer-contact-text">
                    <span class="footer-contact-label"><?= __('footer_our_office') ?></span>
                    <span><?= htmlspecialchars($ft_location) ?></span>
                </span>
            </div>
        </div>

        <!-- Row 2: Main footer columns -->
        <div class="row g-4 footer-main">
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-brand"><img src="/assets/img/jj.png" alt="Mtaita Tech" height="30" style="vertical-align:middle;margin-right:8px;"> <?= SITE_NAME ?></h5>
                <p><?= __('footer_desc') ?></p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5><?= __('footer_quick_links') ?></h5>
                <ul class="list-unstyled">
                    <li><a href="/"><?= __('home') ?></a></li>
                    <li><a href="/about"><?= __('about_us') ?></a></li>
                    <li><a href="/services"><?= __('services') ?></a></li>
                    <li><a href="/portfolio"><?= __('portfolio') ?></a></li>
                    <li><a href="/contact"><?= __('contact_us') ?></a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5><?= __('footer_contact_info') ?></h5>
                <ul class="list-unstyled contact-info-list">
                    <li><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ft_location) ?></li>
                    <li><i class="bi bi-envelope"></i> <a href="mailto:<?= htmlspecialchars($ft_email) ?>"><?= htmlspecialchars($ft_email) ?></a></li>
                    <li><i class="bi bi-phone"></i> <a href="tel:<?= htmlspecialchars($ft_phone_link) ?>"><?= htmlspecialchars($ft_phone) ?></a></li>
                    <li><i class="bi bi-clock"></i> <?= __('footer_hours_value') ?></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5><?= __('footer_follow_us') ?></h5>
                <div class="social-links">
                    <a href="https://www.facebook.com/profile.php?id=61591507924322" target="_blank" rel="noopener" aria-label="Mtaita Tech on Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/mtaitatech1/" target="_blank" rel="noopener" aria-label="Mtaita Tech on Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.youtube.com/@mtaitatech" target="_blank" rel="noopener" aria-label="Mtaita Tech on YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>

        <!-- Row 3: Copyright and bottom navigation -->
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. <?= __('footer_rights') ?></p>
            <ul class="footer-bottom-nav">
                <li><a href="/about"><?= __('about_us') ?></a></li>
                <li><a href="/services"><?= __('services') ?></a></li>
                <li><a href="/portfolio"><?= __('portfolio') ?></a></li>
                <li><a href="/contact"><?= __('contact_us') ?></a></li>
            </ul>
        </div>
    </div>

    <?php if ($page === ''): ?>
    <div id="cookieConsent" class="cookie-consent-overlay">
        <div class="cookie-consent-popup">
            <p class="cookie-consent-text"><?= __('cookie_text') ?></p>
            <div class="cookie-consent-actions">
                <button class="cookie-btn cookie-btn-settings"><?= __('cookie_settings') ?></button>
                <button class="cookie-btn cookie-btn-accept"><?= __('cookie_accept') ?></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</footer>

<a href="#" class="back-to-top" id="backToTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></a>
<?php endif; ?>

<?php if ($page === ''): ?>
<a href="https://wa.me/255616591639" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-notif"><?= __('whatsapp_help') ?></span>
</a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/script.js"></script>
<script>
$(function () {
    AOS.init({ duration: 800, once: true });
    $('.swal-msg').each(function () {
        var msg = $(this).text().trim();
        var type = $(this).data('type');
        if (msg) {
            Swal.fire({
                icon: type,
                title: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: type === 'error' ? 5000 : 3000,
                timerProgressBar: true
            });
        }
    });
    $('.whatsapp-float').on('click', function (e) {
        e.preventDefault();
        var notif = $(this).find('.whatsapp-notif');
        if (notif.is(':visible')) {
            notif.fadeOut();
        } else {
            notif.fadeIn().css({ opacity: 1, transform: 'translateY(-50%) translateX(0)' });
        }
        window.open('https://wa.me/255616591639', '_blank');
    });

    // Back to top
    var $backTop = $('#backToTop');
    if ($backTop.length) {
        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 300) {
                $backTop.addClass('is-visible');
            } else {
                $backTop.removeClass('is-visible');
            }
        });
        $backTop.on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 400);
        });
    }

    if (getCookie('cookie_consent') !== 'accepted') {
        $('#cookieConsent').show();
    }

    $('.cookie-btn-accept').on('click', function() {
        setCookie('cookie_consent', 'accepted', 365);
        $('#cookieConsent').fadeOut();
    });

    $('.cookie-btn-settings').on('click', function() {
        setCookie('cookie_consent', 'accepted', 365);
        $('#cookieConsent').fadeOut();
    });

    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/';
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }
});
</script>
<script>
if (document.querySelector('.partners-swiper')) {
    new Swiper('.partners-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: true },
        pagination: { el: '.partners-swiper .swiper-pagination', clickable: true },
        navigation: { nextEl: '.partners-swiper .swiper-button-next', prevEl: '.partners-swiper .swiper-button-prev' },
        breakpoints: {
            576: { slidesPerView: 2, spaceBetween: 20 },
            768: { slidesPerView: 3, spaceBetween: 24 },
            1024: { slidesPerView: 4, spaceBetween: 24 }
        }
    });
}
</script>

</body>
</html>
