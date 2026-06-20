(function($) {
    "use strict";

    // Initialize scroll animation
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 850,
            once: true,
            offset: 80,
            easing: 'ease-out-cubic'
        });
    }

    // Preloader
    $(window).on('load', function() {
        $('#preloader').fadeOut('slow');
    });

    // Sticky Navbar
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#bbhNavbar').addClass('sticky');
            $('#backToTop').addClass('show');
        } else {
            $('#bbhNavbar').removeClass('sticky');
            $('#backToTop').removeClass('show');
        }
    });

    // Back to Top
    $('#backToTop').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 1000, 'easeInOutExpo');
        return false;
    });

    // Counter Up
    if ($.fn.counterUp) {
        $('.stat-number').counterUp({
            delay: 10,
            time: 1800
        });
    }

    // Testimonials Carousel
    if ($.fn.owlCarousel) {
        $('.testimonial-carousel').owlCarousel({
            loop: true,
            margin: 30,
            nav: false,
            dots: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            smartSpeed: 900,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 2
                },
                992: {
                    items: 3
                }
            }
        });
    }

    // Portfolio Isotope
    var $portfolioContainer = $('.portfolio-container');
    if ($portfolioContainer.length) {
        var $portfolioIsotope = $portfolioContainer.isotope({
            itemSelector: '.portfolio-item',
            layoutMode: 'fitRows'
        });

        $('#portfolio-flters li').on('click', function() {
            $('#portfolio-flters li').removeClass('active');
            $(this).addClass('active');

            $portfolioIsotope.isotope({
                filter: $(this).data('filter')
            });
        });
    }

    // Magnific Popup for Portfolio
    if ($.fn.magnificPopup) {
        $('.portfolio-overlay .btn').magnificPopup({
            type: 'image',
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300
            }
        });
    }

    // Smooth Scroll for Anchor Links
    $('a[href*="#"]').on('click', function(e) {
        if ($(this.hash).length && $(this.hash).offset().top) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $(this.hash).offset().top - 100
            }, 1000, 'easeInOutExpo');
        }
    });

    // Navbar Mobile Menu Close on Click
    $('.navbar-nav .nav-link').not('.dropdown-toggle').on('click', function() {
        if ($('.navbar-collapse').hasClass('show')) {
            $('.navbar-toggler').click();
        }
    });

    // Mobile navbar accordion: feature groups stay closed until clicked.
    var $mainNavbar = $('#mainNavbar');

    function isMobileNavbar() {
        return window.innerWidth <= 991;
    }

    function closeMobileFeatureMenus() {
        var $dropdowns = $mainNavbar.find('.dropdown');
        $dropdowns.find('> .dropdown-menu').removeClass('show').removeAttr('style');
        $dropdowns.find('> .dropdown-toggle').attr('aria-expanded', 'false');
    }

    $mainNavbar.on('show.bs.collapse hidden.bs.collapse', function() {
        closeMobileFeatureMenus();
    });

    $mainNavbar.on('shown.bs.collapse', function() {
        if (isMobileNavbar()) {
            $('body').addClass('frontend-menu-open');
        }
    });

    $mainNavbar.on('hide.bs.collapse hidden.bs.collapse', function() {
        $('body').removeClass('frontend-menu-open');
    });

    $(document).on('click', function(event) {
        if (!isMobileNavbar() || !$mainNavbar.hasClass('show')) {
            return;
        }

        var clickedInsideNavbar = $(event.target).closest('#bbhNavbar').length > 0;
        if (!clickedInsideNavbar) {
            bootstrap.Collapse.getOrCreateInstance($mainNavbar[0]).hide();
        }
    });

    $(document).on('keydown', function(event) {
        if (event.key === 'Escape' && $mainNavbar.hasClass('show')) {
            bootstrap.Collapse.getOrCreateInstance($mainNavbar[0]).hide();
        }
    });

    $mainNavbar.on('show.bs.dropdown', '.dropdown', function() {
        if (!isMobileNavbar()) {
            return;
        }

        $(this)
            .siblings('.dropdown')
            .find('> .dropdown-menu')
            .removeClass('show')
            .removeAttr('style');

        $(this)
            .siblings('.dropdown')
            .find('> .dropdown-toggle')
            .attr('aria-expanded', 'false');
    });

    $(window).on('resize', function() {
        if (isMobileNavbar()) {
            closeMobileFeatureMenus();
        } else {
            $('body').removeClass('frontend-menu-open');
            $mainNavbar.find('.dropdown-menu').removeAttr('style');
        }
    });

    // Add Active Class to Current Nav Item
    var currentLocation = window.location.pathname;
    $('.navbar-nav .nav-link').each(function() {
        var $this = $(this);
        if ($this.attr('href') === currentLocation) {
            $this.addClass('active');
        }
    });

    // Newsletter Form Submit
    $('.newsletter-form').on('submit', function(e) {
        e.preventDefault();
        var email = $(this).find('input[type="email"]').val();
        if (email) {
            // Show success message
            alert('Thank you for subscribing to our newsletter!');
            $(this).find('input[type="email"]').val('');
        }
    });

    // Scroll Reveal Animation
    function revealOnScroll() {
        var windowHeight = $(window).height();
        var scrollTop = $(window).scrollTop();

        $('.wow').each(function() {
            var elementOffset = $(this).offset().top;
            var elementHeight = $(this).outerHeight();

            if (elementOffset < scrollTop + windowHeight - 100) {
                $(this).addClass('animated');
            }
        });
    }

    $(window).on('scroll', revealOnScroll);
    revealOnScroll();

    // Hover Effects
    $('.service-item, .feature-card, .team-card').hover(
        function() {
            $(this).find('i').addClass('fa-beat');
        },
        function() {
            $(this).find('i').removeClass('fa-beat');
        }
    );

    // Dropdown Hover for Desktop
    if ($(window).width() > 991) {
        $('.dropdown').hover(
            function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn(300);
            },
            function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut(300);
            }
        );
    }

    // Premium motion layer
    $(window).on('scroll', function() {
        var scroll = $(window).scrollTop();
        $('.hero-section, .page-hero').css('background-position-y', scroll * 0.18 + 'px');
        $('.hero-image').css('filter', 'saturate(' + Math.max(1, 1.08 - scroll / 5000) + ')');
    });

    $('.btn-purple, .btn-outline-purple').on('mousemove', function(e) {
        var rect = this.getBoundingClientRect();
        var x = e.clientX - rect.left - rect.width / 2;
        var y = e.clientY - rect.top - rect.height / 2;
        $(this).css('transform', 'translate(' + (x * 0.03) + 'px,' + (y * 0.05) + 'px)');
    }).on('mouseleave', function() {
        $(this).css('transform', '');
    });

    // Typed Effect for Hero Title (Optional)
    // You can uncomment this if you want typing animation
    /*
    var typed = new Typed('.typed-text', {
        strings: ['Projects', 'Tasks', 'Teams', 'Success'],
        typeSpeed: 100,
        backSpeed: 60,
        loop: true
    });
    */

})(jQuery);
