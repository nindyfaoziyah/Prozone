// Landing page JavaScript functionality

// Navbar scroll effect
(function() {
    const nav = document.getElementById('landingNav');
    if (!nav) return;
    const onScroll = () => {
        if (window.scrollY > 16) nav.classList.add('is-scrolled');
        else nav.classList.remove('is-scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

// Mobile menu toggle
(function() {
    const toggle = document.getElementById('navMobileToggle');
    const menu = document.getElementById('navMenu');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function() {
        const isOpen = menu.classList.toggle('is-mobile-open');
        toggle.setAttribute('aria-expanded', isOpen);
    });
    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('is-mobile-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();

// Reveal-on-scroll
(function() {
    const items = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        items.forEach(el => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const delay = Array.from(entry.target.parentElement.children)
                    .filter(c => c.classList.contains('reveal'))
                    .indexOf(entry.target) * 80;
                setTimeout(() => entry.target.classList.add('is-visible'), delay);
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    items.forEach(el => io.observe(el));
})();

// Animated counter
(function() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.count);
                const suffix = '+';
                const duration = 1500;
                const start = 0;
                const startTime = performance.now();
                function update(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // easeOutExpo
                    const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const current = Math.round(start + (target - start) * eased);
                    el.textContent = current.toLocaleString('id-ID') + suffix;
                    if (progress < 1) requestAnimationFrame(update);
                }
                requestAnimationFrame(update);
                io.unobserve(el);
            }
        });
    }, { threshold: 0.3 });
    counters.forEach(el => io.observe(el));
})();
