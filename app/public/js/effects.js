/**
 * ICS Community — Effets visuels & animations
 * Lumières suivant la souris, particules cyber, animations d'entrée
 */
(function () {
    'use strict';

    // ── Mouse-following glow ────────────────────────────────────────
    const glowOrb = document.createElement('div');
    glowOrb.className = 'mouse-glow-orb';
    document.body.appendChild(glowOrb);

    let mouseX = -200, mouseY = -200;
    let orbX = -200, orbY = -200;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    function animateOrb() {
        orbX += (mouseX - orbX) * 0.08;
        orbY += (mouseY - orbY) * 0.08;
        glowOrb.style.left = orbX + 'px';
        glowOrb.style.top = orbY + 'px';
        requestAnimationFrame(animateOrb);
    }
    animateOrb();

    // ── Card hover glow effect ──────────────────────────────────────
    document.querySelectorAll('.card, .post-card, .sidebar-widget, .donation-method').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--glow-x', x + 'px');
            card.style.setProperty('--glow-y', y + 'px');
        });
    });

    // ── Cyber particles background ──────────────────────────────────
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'cyber-particles';
    document.body.insertBefore(particlesContainer, document.body.firstChild);

    function createParticle() {
        const particle = document.createElement('div');
        particle.className = 'cyber-particle';
        const size = Math.random() * 3 + 1;
        const startX = Math.random() * 100;
        const duration = Math.random() * 15 + 10;
        const delay = Math.random() * 5;
        const hue = Math.random() > 0.5 ? '217' : '261'; // electric blue or iris purple

        particle.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${startX}%;
            animation-duration: ${duration}s;
            animation-delay: ${delay}s;
            --particle-hue: ${hue};
        `;
        particlesContainer.appendChild(particle);

        setTimeout(() => particle.remove(), (duration + delay) * 1000);
    }

    // Generate particles periodically
    setInterval(createParticle, 800);
    for (let i = 0; i < 15; i++) createParticle();

    // ── Scroll-triggered fade-in animations ─────────────────────────
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
    };

    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                scrollObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe cards, posts, widgets
    document.querySelectorAll('.card, .post-card, .sidebar-widget, .member-card, .stat-card').forEach((el, i) => {
        el.classList.add('scroll-reveal');
        el.style.transitionDelay = `${i * 60}ms`;
        scrollObserver.observe(el);
    });

    // ── Typing effect for headings on landing page ──────────────────
    document.querySelectorAll('.hero-title, .cyber-glitch').forEach(el => {
        el.classList.add('text-glow-pulse');
    });

    // ── Nav glow underline on active link ───────────────────────────
    const activeLink = document.querySelector('.nav-links a.active');
    if (activeLink) {
        activeLink.classList.add('nav-glow-active');
    }

    // ── Scan line effect ────────────────────────────────────────────
    const scanline = document.createElement('div');
    scanline.className = 'scanline-effect';
    document.body.appendChild(scanline);

    // ── Button ripple effect ────────────────────────────────────────
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.className = 'btn-ripple';
            const rect = this.getBoundingClientRect();
            ripple.style.left = (e.clientX - rect.left) + 'px';
            ripple.style.top = (e.clientY - rect.top) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // ── Glitch text effect on hover ─────────────────────────────────
    document.querySelectorAll('.nav-brand').forEach(el => {
        el.addEventListener('mouseenter', () => {
            el.classList.add('glitch-hover');
            setTimeout(() => el.classList.remove('glitch-hover'), 500);
        });
    });

    // ── Number counter animation for donation amount ────────────────
    document.querySelectorAll('.donation-amount').forEach(el => {
        const target = el.textContent;
        const numMatch = target.match(/[\d\s]+/);
        if (numMatch) {
            const finalNum = parseInt(numMatch[0].replace(/\s/g, ''), 10);
            let current = 0;
            const increment = Math.ceil(finalNum / 60);
            const suffix = target.replace(numMatch[0], '').trim();
            const timer = setInterval(() => {
                current += increment;
                if (current >= finalNum) {
                    current = finalNum;
                    clearInterval(timer);
                }
                el.textContent = new Intl.NumberFormat('fr-FR').format(current) + ' ' + suffix;
            }, 25);
        }
    });

    // ── Reduced motion respect ──────────────────────────────────────
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        glowOrb.style.display = 'none';
        particlesContainer.style.display = 'none';
        scanline.style.display = 'none';
        document.querySelectorAll('.scroll-reveal').forEach(el => {
            el.classList.add('visible');
        });
    }

})();
