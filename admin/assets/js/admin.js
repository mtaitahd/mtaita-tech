$(function () {
    // SweetAlert for flash messages
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

    // Delete confirmation
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var type = $(this).data('type') || 'item';

        Swal.fire({
            title: 'Delete ' + type + '?',
            text: 'You won\u2019t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });

    // Auto-prefix URLs
    $('input[type="url"]').on('blur', function () {
        var val = $(this).val();
        if (val && !val.startsWith('http://') && !val.startsWith('https://')) {
            $(this).val('https://' + val);
        }
    });

    // ============================================
    // SIDEBAR TOGGLE — Desktop & Mobile
    // ============================================

    function isMobile() {
        return window.innerWidth <= 768;
    }

    function toggleSidebar() {
        if (isMobile()) {
            // Mobile: slide sidebar in/out with overlay
            $('.sidebar').toggleClass('show');
            $('body').toggleClass('sidebar-open');
            if ($('body').hasClass('sidebar-open')) {
                $('<div class="sidebar-overlay"></div>')
                    .appendTo('body')
                    .fadeIn(200)
                    .on('click', closeSidebar);
            } else {
                $('.sidebar-overlay').fadeOut(200, function () { $(this).remove(); });
            }
        } else {
            // Desktop: toggle icon-mode
            $('.sidebar').toggleClass('toggled');
            $('body').toggleClass('sidebar-toggled');
        }
    }

    function closeSidebar() {
        $('.sidebar').removeClass('show');
        $('body').removeClass('sidebar-open');
        $('.sidebar-overlay').fadeOut(200, function () { $(this).remove(); });
    }

    // Toggle button in topbar (visible on mobile)
    $('#sidebarToggleTop').on('click', function (e) {
        e.preventDefault();
        toggleSidebar();
    });

    // Close sidebar on mobile when clicking a nav link
    $('.sidebar .nav-link, .sidebar .collapse-item').on('click', function () {
        if (isMobile()) {
            closeSidebar();
        }
    });

    // Close sidebar on Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('body').hasClass('sidebar-open')) {
            closeSidebar();
        }
    });

    // Auto-open sidebar accordion for current page
    var currentPath = window.location.pathname.split('/').pop();
    $('.sidebar .collapse').each(function () {
        var hasActive = $(this).find('.collapse-item[href="' + currentPath + '"]').length > 0;
        if (hasActive) {
            $(this).addClass('show');
            var trigger = $('[data-bs-target="#' + $(this).attr('id') + '"]');
            trigger.attr('aria-expanded', 'true').removeClass('collapsed');
        }
    });

    // Smooth scroll for sidebar
    $('.sidebar .nav-link[href^="#"]').on('click', function (e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({ scrollTop: target.offset().top }, 300);
        }
    });
});
