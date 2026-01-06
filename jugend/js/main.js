/**
 * ASV Petri Heil Großostheim
 * Epic JavaScript - Engaging Animations & Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modules
    Loader.init();
    Header.init();
    MobileNav.init();
    CounterAnimation.init();
    ScrollAnimations.init();
    BackToTop.init();
    ParticleEffect.init();

    // Add smooth hover effects
    addHoverEffects();
});

/**
 * Cinematic Loader
 */
const Loader = {
    init() {
        const loader = document.getElementById('loader');
        if (!loader) return;

        // Prevent scrolling while loading
        document.body.style.overflow = 'hidden';

        // Function to hide loader
        const hideLoader = () => {
            if (loader.classList.contains('hidden')) return; // Already hidden
            loader.classList.add('hidden');
            document.body.style.overflow = '';
            this.triggerEntranceAnimations();
        };

        // Hide loader when page is fully loaded
        window.addEventListener('load', () => {
            setTimeout(hideLoader, 1000);
        });

        // Fallback: Hide loader after 5 seconds max (in case resources fail to load)
        setTimeout(hideLoader, 5000);
    },

    triggerEntranceAnimations() {
        const animatedElements = document.querySelectorAll('.animate-fade-in, .animate-slide-up');
        animatedElements.forEach(el => {
            el.style.animationPlayState = 'running';
        });
    }
};

/**
 * Header - Dynamic scroll effects
 */
const Header = {
    header: null,
    lastScrollY: 0,
    ticking: false,

    init() {
        this.header = document.getElementById('header');
        if (!this.header) return;

        window.addEventListener('scroll', () => this.onScroll(), { passive: true });
        this.onScroll();
    },

    onScroll() {
        this.lastScrollY = window.scrollY;

        if (!this.ticking) {
            window.requestAnimationFrame(() => {
                this.updateHeader();
                this.ticking = false;
            });
            this.ticking = true;
        }
    },

    updateHeader() {
        if (this.lastScrollY > 50) {
            this.header.classList.add('scrolled');
        } else {
            this.header.classList.remove('scrolled');
        }
    }
};

/**
 * Mobile Navigation
 */
const MobileNav = {
    toggle: null,
    menu: null,
    isOpen: false,

    init() {
        this.toggle = document.getElementById('nav-toggle');
        this.menu = document.getElementById('nav-menu');

        if (!this.toggle || !this.menu) return;

        this.toggle.addEventListener('click', () => this.toggleMenu());

        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.menu.contains(e.target) && !this.toggle.contains(e.target)) {
                this.closeMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeMenu();
            }
        });

        // Handle dropdown toggles on mobile
        this.setupMobileDropdowns();
    },

    toggleMenu() {
        this.isOpen = !this.isOpen;
        this.toggle.classList.toggle('active');
        this.menu.classList.toggle('active');
        document.body.style.overflow = this.isOpen ? 'hidden' : '';
    },

    closeMenu() {
        this.isOpen = false;
        this.toggle.classList.remove('active');
        this.menu.classList.remove('active');
        document.body.style.overflow = '';
    },

    setupMobileDropdowns() {
        const dropdowns = this.menu.querySelectorAll('.nav-item.dropdown');

        dropdowns.forEach(dropdown => {
            const link = dropdown.querySelector('.nav-link');

            link.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        });
    }
};

/**
 * Animated Counters - Epic number animation
 */
const CounterAnimation = {
    counters: [],
    hasAnimated: false,

    init() {
        this.counters = document.querySelectorAll('.counter, .fact-number[data-target]');
        if (this.counters.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.hasAnimated) {
                    this.hasAnimated = true;
                    this.animateCounters();
                }
            });
        }, { threshold: 0.3 });

        // Observe the section containing the counters
        const statsSection = this.counters[0].closest('section');
        if (statsSection) {
            observer.observe(statsSection);
        } else {
            // Fallback: observe individual counters
            this.counters.forEach(counter => observer.observe(counter));
        }
    },

    animateCounters() {
        this.counters.forEach(counter => {
            let target = parseInt(counter.dataset.target);
            const duration = 2500;
            const start = Date.now();

            // CRITICAL HIT LOGIC 🎲
            // Check if this is the "Spaß garantiert" counter (target 100)
            let isCritical = false;
            if (target === 100 && Math.random() < 0.2) { // 20% Chance
                isCritical = true;
                target = 9999; // OVER 9000!
            }

            const animate = () => {
                const elapsed = Date.now() - start;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (easeOutExpo)
                const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const current = Math.floor(eased * target);

                counter.textContent = this.formatNumber(current);

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else if (isCritical) {
                    // Trigger Critical Hit Effect when finished
                    this.triggerCriticalHit(counter);
                }
            };

            animate();
        });
    },

    triggerCriticalHit(element) {
        element.classList.add('critical-hit');

        // Spawn particles
        const emojis = ['🔥', '✨', '⚡', '💥', '🚀', '💯'];
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2 + window.scrollX;
        const centerY = rect.top + rect.height / 2 + window.scrollY;

        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'crit-particle';
            particle.textContent = emojis[Math.floor(Math.random() * emojis.length)];

            // Random direction
            const angle = Math.random() * Math.PI * 2;
            const velocity = Math.random() * 100 + 50;
            const tx = Math.cos(angle) * velocity + 'px';
            const ty = Math.sin(angle) * velocity + 'px';

            particle.style.setProperty('--tx', tx);
            particle.style.setProperty('--ty', ty);
            particle.style.left = centerX + 'px';
            particle.style.top = centerY + 'px';

            document.body.appendChild(particle);

            // Cleanup
            setTimeout(() => particle.remove(), 1000);
        }
    },

    formatNumber(num) {
        if (num >= 1000) {
            return num.toLocaleString('de-DE');
        }
        return num;
    }
};

/**
 * Scroll Animations - Reveal on scroll
 */
const ScrollAnimations = {
    init() {
        this.setupAnimatedElements();

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -80px 0px'
        });

        document.querySelectorAll('.reveal-on-scroll').forEach(el => {
            observer.observe(el);
        });
    },

    setupAnimatedElements() {
        // Add reveal classes to elements
        const selectors = [
            '.impact-card',
            '.experience-card',
            '.why-feature',
            '.explore-card',
            '.quote-card'
        ];

        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach((el, index) => {
                el.classList.add('reveal-on-scroll');
                el.style.setProperty('--reveal-delay', `${index * 0.1}s`);
            });
        });
    }
};

/**
 * Back to Top Button
 */
const BackToTop = {
    button: null,

    init() {
        this.button = document.getElementById('backToTop');
        if (!this.button) return;

        window.addEventListener('scroll', () => this.toggleVisibility(), { passive: true });
        this.button.addEventListener('click', () => this.scrollToTop());
    },

    toggleVisibility() {
        if (window.scrollY > 600) {
            this.button.classList.add('visible');
        } else {
            this.button.classList.remove('visible');
        }
    },

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
};

/**
 * Particle Effect for Hero
 */
const ParticleEffect = {
    init() {
        const container = document.getElementById('particles');
        if (!container) return;

        // Create floating particles
        for (let i = 0; i < 30; i++) {
            this.createParticle(container);
        }
    },

    createParticle(container) {
        const particle = document.createElement('div');
        particle.className = 'particle';

        const size = Math.random() * 4 + 2;
        const x = Math.random() * 100;
        const duration = Math.random() * 15 + 10;
        const delay = Math.random() * 10;

        particle.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: rgba(0, 212, 255, ${Math.random() * 0.3 + 0.1});
            border-radius: 50%;
            left: ${x}%;
            bottom: -20px;
            animation: particleFloat ${duration}s linear ${delay}s infinite;
            pointer-events: none;
        `;

        container.appendChild(particle);
    }
};

/**
 * Add hover effects to interactive elements
 */
function addHoverEffects() {
    // Magnetic effect for buttons
    document.querySelectorAll('.btn-epic').forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            btn.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
        });

        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });

    // Tilt effect for cards
    document.querySelectorAll('.experience-card, .impact-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

/**
 * Smooth Scroll for Anchor Links
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        e.preventDefault();
        const target = document.querySelector(href);

        if (target) {
            const headerHeight = document.getElementById('header')?.offsetHeight || 0;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });

            // Close mobile menu if open
            if (MobileNav.isOpen) {
                MobileNav.closeMenu();
            }
        }
    });
});

/**
 * Add dynamic CSS for animations
 */
const dynamicStyles = document.createElement('style');
dynamicStyles.textContent = `
    @keyframes particleFloat {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            transform: translateY(-100vh) translateX(${Math.random() * 100 - 50}px);
            opacity: 0;
        }
    }
    
    .reveal-on-scroll {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.6s ease, transform 0.6s ease;
        transition-delay: var(--reveal-delay, 0s);
    }
    
    .reveal-on-scroll.revealed {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Smooth card transitions */
    .experience-card,
    .impact-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
`;
document.head.appendChild(dynamicStyles);

/**
 * Intersection Observer for lazy loading images (future use)
 */
const lazyLoadImages = () => {
    const images = document.querySelectorAll('img[data-src]');

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }
};

// Initialize lazy loading when DOM is ready
document.addEventListener('DOMContentLoaded', lazyLoadImages);
