$(function () {
    // Active nav link highlighting
    var path = window.location.pathname.split('/').pop() || 'index.php';
    $('.navbar-nav .nav-link').each(function () {
        if ($(this).attr('href') === path) {
            $(this).addClass('active');
        }
    });

    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function (e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({ scrollTop: target.offset().top - 80 }, 500);
        }
    });

    // Navbar scrolled state
    $(window).on('scroll', function () {
        var navbar = $('.navbar');
        if ($(this).scrollTop() > 50) {
            navbar.addClass('navbar-scrolled');
        } else {
            navbar.removeClass('navbar-scrolled');
        }
    });

    // Services horizontal scroll
    var track = document.getElementById('servicesScrollTrack');
    var leftBtn = document.getElementById('servicesScrollLeft');
    var rightBtn = document.getElementById('servicesScrollRight');
    if (track && leftBtn && rightBtn) {
        var inner = track.querySelector('.services-scroll-inner');
        function updateScrollButtons() {
            var maxScroll = inner.scrollWidth - track.clientWidth;
            leftBtn.disabled = track.scrollLeft <= 1;
            rightBtn.disabled = track.scrollLeft >= maxScroll - 1;
        }
        leftBtn.addEventListener('click', function () {
            track.scrollBy({ left: -280, behavior: 'smooth' });
            setTimeout(updateScrollButtons, 400);
        });
        rightBtn.addEventListener('click', function () {
            track.scrollBy({ left: 280, behavior: 'smooth' });
            setTimeout(updateScrollButtons, 400);
        });
        track.addEventListener('scroll', updateScrollButtons);
        updateScrollButtons();
        window.addEventListener('resize', updateScrollButtons);
    }

    // Close mobile navbar when a nav link is clicked
    var navbarToggler = $('.navbar-toggler');
    var navbarCollapse = $('#mainNavbar');
    navbarCollapse.find('.nav-link').on('click', function () {
        if (navbarToggler.is(':visible') && navbarCollapse.hasClass('show')) {
            navbarToggler.trigger('click');
        }
    });

    // Close mobile navbar on outside click / document tap
    $(document).on('click touchstart', function (e) {
        if (navbarToggler.is(':visible') && navbarCollapse.hasClass('show')) {
            if (!$(e.target).closest('.navbar').length) {
                navbarToggler.trigger('click');
            }
        }
    });

});
