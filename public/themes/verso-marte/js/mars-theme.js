/**
 * Mars Theme JavaScript
 * Advanced interactions and animations for the Verso Marte theme
 */

(function($) {
    'use strict';

    // Theme configuration
    const MarsTheme = {
        init: function() {
            this.setupNavigation();
            this.setupAnimations();
            this.setupInteractiveElements();
            this.setupSpaceEffects();
            this.setupSmoothScrolling();
            this.setupLoadingStates();
        },

        // Enhanced Navigation
        setupNavigation: function() {
            // Add active class to current page nav item
            const currentPath = window.location.pathname;
            $('.nav-menu a').each(function() {
                const href = $(this).attr('href');
                if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
                    $(this).addClass('active');
                }
            });

            // Mobile navigation toggle (if needed)
            $('.nav-toggle').on('click', function(e) {
                e.preventDefault();
                $('.nav-menu').toggleClass('mobile-active');
                $(this).toggleClass('active');
            });

            // Navigation hover effects with sound (visual feedback only)
            $('.nav-menu a').hover(
                function() {
                    $(this).find('i').addClass('pulse-animation');
                },
                function() {
                    $(this).find('i').removeClass('pulse-animation');
                }
            );
        },

        // Mars-themed animations
        setupAnimations: function() {
            // Intersection Observer for scroll animations
            if ('IntersectionObserver' in window) {
                const animationObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-in');
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });

                // Animate elements on scroll
                document.querySelectorAll('.article-card, .phase-item, .fact-card, .widget').forEach(el => {
                    animationObserver.observe(el);
                });
            }

            // Mars orbit animation for special elements
            $('.mars-icon').each(function(index) {
                $(this).css('animation-delay', (index * 2) + 's');
            });

            // Rocket animation for buttons
            $('.btn').hover(
                function() {
                    $(this).find('.fas').addClass('rocket-boost');
                },
                function() {
                    $(this).find('.fas').removeClass('rocket-boost');
                }
            );

            // Floating animation for debris and particles
            this.createFloatingParticles();
        },

        // Interactive Mars elements
        setupInteractiveElements: function() {
            // Article card hover effects
            $('.article-card').hover(
                function() {
                    $(this).addClass('mars-glow');
                },
                function() {
                    $(this).removeClass('mars-glow');
                }
            );

            // Widget interactive effects
            $('.widget').on('mouseenter', function() {
                $(this).find('.widget-title i').addClass('spin-animation');
            });

            // Search enhancement
            $('.search-input').on('focus', function() {
                $(this).closest('.search-input-wrapper').addClass('scanning');
            }).on('blur', function() {
                $(this).closest('.search-input-wrapper').removeClass('scanning');
            });

            // Mission status updates (simulated)
            this.updateMissionStatus();
        },

        // Space background effects
        setupSpaceEffects: function() {
            // Create dynamic star field
            this.createStarField();
            
            // Asteroid belt effect for 404 page
            if ($('.mars-404').length) {
                this.createAsteroidField();
            }

            // Nebula effect on hero sections
            $('.mars-hero, .error-hero').each(function() {
                $(this).append('<div class="nebula-overlay"></div>');
            });

            // Mars weather simulation
            this.simulateMarsWeather();
        },

        // Smooth scrolling implementation
        setupSmoothScrolling: function() {
            // Smooth scroll for anchor links
            $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') 
                    && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        event.preventDefault();
                        $('html, body').animate({
                            scrollTop: target.offset().top - 80
                        }, 800, 'easeInOutQuad');
                    }
                }
            });

            // Back to top functionality
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    if (!$('.back-to-orbit').length) {
                        $('body').append('<div class="back-to-orbit"><i class="fas fa-rocket"></i></div>');
                    }
                    $('.back-to-orbit').fadeIn();
                } else {
                    $('.back-to-orbit').fadeOut();
                }
            });

            $(document).on('click', '.back-to-orbit', function() {
                $('html, body').animate({scrollTop: 0}, 800);
            });
        },

        // Loading states and transitions
        setupLoadingStates: function() {
            // Page load animation
            $(window).on('load', function() {
                $('body').addClass('loaded');
                $('.loading').fadeOut(500);
            });

            // Form submission loading
            $('form').on('submit', function() {
                const submitBtn = $(this).find('[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-satellite fa-spin"></i> Trasmissione in corso...');
                submitBtn.prop('disabled', true);

                // Re-enable after 3 seconds (in case of errors)
                setTimeout(function() {
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }, 3000);
            });

            // AJAX loading indicators
            $(document).ajaxStart(function() {
                $('.loading-indicator').show();
            }).ajaxStop(function() {
                $('.loading-indicator').hide();
            });
        },

        // Create floating particles
        createFloatingParticles: function() {
            const particleContainer = $('<div class="space-particles"></div>');
            $('body').append(particleContainer);

            for (let i = 0; i < 20; i++) {
                const particle = $('<div class="particle"></div>');
                particle.css({
                    'left': Math.random() * 100 + '%',
                    'animation-delay': Math.random() * 10 + 's',
                    'animation-duration': (Math.random() * 10 + 15) + 's'
                });
                particleContainer.append(particle);
            }
        },

        // Dynamic star field
        createStarField: function() {
            const starContainer = $('<div class="star-field"></div>');
            $('body').prepend(starContainer);

            for (let i = 0; i < 100; i++) {
                const star = $('<div class="star"></div>');
                star.css({
                    'left': Math.random() * 100 + '%',
                    'top': Math.random() * 100 + '%',
                    'animation-delay': Math.random() * 3 + 's'
                });
                starContainer.append(star);
            }
        },

        // Asteroid field for 404 page
        createAsteroidField: function() {
            const asteroidContainer = $('.floating-debris');
            
            for (let i = 0; i < 5; i++) {
                const asteroid = $('<span class="asteroid"></span>');
                asteroid.css({
                    'left': Math.random() * 100 + '%',
                    'animation-delay': Math.random() * 5 + 's'
                });
                asteroidContainer.append(asteroid);
            }
        },

        // Mission status updates
        updateMissionStatus: function() {
            const statusElements = $('.mission-status, .weather-item');
            
            setInterval(function() {
                statusElements.each(function() {
                    $(this).addClass('data-update');
                    setTimeout(() => {
                        $(this).removeClass('data-update');
                    }, 1000);
                });
            }, 30000); // Update every 30 seconds
        },

        // Mars weather simulation
        simulateMarsWeather: function() {
            const weatherWidget = $('.mars-weather');
            if (weatherWidget.length) {
                const weatherData = [
                    { temp: '-63°C', wind: '97 km/h', visibility: 'Tempesta di sabbia', pressure: '0.6 kPa' },
                    { temp: '-45°C', wind: '32 km/h', visibility: 'Sereno', pressure: '0.8 kPa' },
                    { temp: '-78°C', wind: '156 km/h', visibility: 'Nebbia', pressure: '0.4 kPa' },
                    { temp: '-52°C', wind: '68 km/h', visibility: 'Parzialmente nuvoloso', pressure: '0.7 kPa' }
                ];

                let currentWeather = 0;
                setInterval(function() {
                    const weather = weatherData[currentWeather];
                    weatherWidget.find('.weather-item').each(function(index) {
                        const values = Object.values(weather);
                        $(this).find('span').text($(this).find('span').text().split(':')[0] + ': ' + values[index]);
                    });
                    currentWeather = (currentWeather + 1) % weatherData.length;
                }, 60000); // Change every minute
            }
        },

        // Utility functions
        utils: {
            // Generate random Mars fact
            getRandomMarsFact: function() {
                const facts = [
                    "Un anno su Marte dura 687 giorni terrestri.",
                    "Marte ha due lune: Phobos e Deimos.",
                    "La temperatura su Marte può variare da -143°C a 35°C.",
                    "Un giorno su Marte (Sol) dura 24 ore e 37 minuti.",
                    "Marte è chiamato il Pianeta Rosso per l'ossido di ferro sulla sua superficie."
                ];
                return facts[Math.floor(Math.random() * facts.length)];
            },

            // Format Mars date (Sol)
            formatSol: function(date) {
                const marsYear = Math.floor((date.getTime() - new Date('2000-01-01').getTime()) / (1000 * 60 * 60 * 24 * 687));
                const sol = Math.floor((date.getTime() - new Date('2000-01-01').getTime()) / (1000 * 60 * 60 * 24.65));
                return `Sol ${sol} - Anno marziano ${marsYear}`;
            },

            // Create notification
            showNotification: function(message, type = 'info') {
                const notification = $(`
                    <div class="mars-notification ${type}">
                        <i class="fas fa-satellite-dish"></i>
                        <span>${message}</span>
                        <button class="notification-close"><i class="fas fa-times"></i></button>
                    </div>
                `);
                
                $('body').append(notification);
                notification.fadeIn();
                
                setTimeout(() => {
                    notification.fadeOut(() => notification.remove());
                }, 5000);
                
                notification.find('.notification-close').click(() => {
                    notification.fadeOut(() => notification.remove());
                });
            }
        }
    };

    // Custom CSS animations added dynamically
    const addCustomStyles = function() {
        const styles = `
            <style>
            .pulse-animation { animation: pulse 1s infinite; }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.2); }
            }
            
            .rocket-boost { animation: rocket-boost 0.5s ease-in-out; }
            @keyframes rocket-boost {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-5px); }
            }
            
            .spin-animation { animation: spin 2s linear infinite; }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            .mars-glow {
                box-shadow: 0 0 20px rgba(205, 92, 92, 0.5) !important;
                border-color: var(--mars-orange) !important;
            }
            
            .animate-in {
                animation: slideInUp 0.8s ease-out forwards;
            }
            
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .space-particles {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: -1;
            }
            
            .particle {
                position: absolute;
                width: 2px;
                height: 2px;
                background: white;
                border-radius: 50%;
                animation: float-up linear infinite;
            }
            
            @keyframes float-up {
                0% {
                    opacity: 0;
                    transform: translateY(100vh);
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    opacity: 0;
                    transform: translateY(-100px);
                }
            }
            
            .star-field {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: -2;
            }
            
            .star {
                position: absolute;
                width: 1px;
                height: 1px;
                background: white;
                border-radius: 50%;
                animation: twinkle 3s ease-in-out infinite alternate;
            }
            
            @keyframes twinkle {
                0% { opacity: 0.3; }
                100% { opacity: 1; }
            }
            
            .back-to-orbit {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 50px;
                height: 50px;
                background: var(--mars-gradient);
                border-radius: 50%;
                display: none;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 1000;
                box-shadow: var(--shadow-mars);
                transition: all 0.3s ease;
            }
            
            .back-to-orbit:hover {
                transform: translateY(-5px) scale(1.1);
                box-shadow: 0 8px 25px rgba(205, 92, 92, 0.4);
            }
            
            .back-to-orbit i {
                color: white;
                font-size: 1.2rem;
            }
            
            .mars-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--nebula-gradient);
                color: white;
                padding: 1rem;
                border-radius: var(--border-radius);
                border: 2px solid var(--mars-orange);
                display: none;
                align-items: center;
                gap: 0.5rem;
                z-index: 10000;
                max-width: 400px;
                box-shadow: var(--shadow-mars);
            }
            
            .mars-notification.info { border-color: var(--mars-orange); }
            .mars-notification.success { border-color: #2ed573; }
            .mars-notification.warning { border-color: #ffa502; }
            .mars-notification.error { border-color: #ff4757; }
            
            .notification-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                margin-left: auto;
            }
            
            .scanning {
                border-color: var(--gold-accent) !important;
                box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
            }
            
            .data-update {
                animation: data-pulse 1s ease-in-out;
            }
            
            @keyframes data-pulse {
                0%, 100% { background-color: transparent; }
                50% { background-color: rgba(205, 92, 92, 0.2); }
            }
            
            body.loaded .loading {
                opacity: 0;
                visibility: hidden;
            }
            
            @media (max-width: 768px) {
                .back-to-orbit {
                    bottom: 20px;
                    right: 20px;
                    width: 45px;
                    height: 45px;
                }
                
                .mars-notification {
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }
            }
            </style>
        `;
        $('head').append(styles);
    };

    // Initialize theme when document is ready
    $(document).ready(function() {
        addCustomStyles();
        MarsTheme.init();
        
        // Show welcome message on first visit
        if (localStorage.getItem('mars-theme-welcome') !== 'shown') {
            setTimeout(() => {
                MarsTheme.utils.showNotification('Benvenuto nella missione verso Marte! 🚀', 'info');
                localStorage.setItem('mars-theme-welcome', 'shown');
            }, 2000);
        }
    });

    // Make MarsTheme globally available
    window.MarsTheme = MarsTheme;

})(jQuery);