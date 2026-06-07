// Default Blue Theme - Interactive Features
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Enhanced navbar scroll effect
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar-fixed');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add/remove shadow based on scroll position
            if (scrollTop > 20) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
            
            // Auto-hide navbar on scroll down (optional, disabled by default)
            // if (scrollTop > lastScrollTop && scrollTop > 100) {
            //     navbar.style.transform = 'translateY(-100%)';
            // } else {
            //     navbar.style.transform = 'translateY(0)';
            // }
            
            lastScrollTop = scrollTop;
        });
    }

    // Article card hover effects enhancement
    const articleCards = document.querySelectorAll('.article-card');
    articleCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-2px)';
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Auto-dismiss flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.flash-messages .alert');
    flashMessages.forEach(message => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(message);
            bsAlert.close();
        }, 5000);
    });

    // Enhanced dropdown menu behavior
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            // Ensure proper dropdown behavior
            e.stopPropagation();
        });
    });

    // Search enhancement (if search field exists)
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Add search functionality here if needed
                console.log('Search for:', this.value);
            }, 300);
        });
    }

    // Responsive table handling
    const tables = document.querySelectorAll('table:not(.table-responsive table)');
    tables.forEach(table => {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });

    // Print styles optimization
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('printing');
    });

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing');
    });

    // Performance monitoring
    if ('performance' in window) {
        window.addEventListener('load', function() {
            setTimeout(() => {
                const navigation = performance.getEntriesByType('navigation')[0];
                console.log('Page load time:', navigation.loadEventEnd - navigation.fetchStart, 'ms');
            }, 0);
        });
    }

    // Accessibility improvements
    // Focus management for mobile menu
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            // Ensure proper focus management
            setTimeout(() => {
                if (navbarCollapse.classList.contains('show')) {
                    const firstLink = navbarCollapse.querySelector('.nav-link');
                    if (firstLink) firstLink.focus();
                }
            }, 350);
        });
    }

    console.log('Default Blue Theme initialized successfully');
});
