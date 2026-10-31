/**
 * ICS Community — Effets visuels
 * Lumière qui suit la souris, grille révélée, constellation interactive,
 * curseur, cartes éclairées/inclinées, boutons, compteurs, titres décryptés.
 */
(function () {
    'use strict';

    const body = document.body;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    // ── Calques d'ambiance ──────────────────────────────────────────
    function layer(tag, cls) {
        const el = document.createElement(tag);
        el.className = cls;
        el.setAttribute('aria-hidden', 'true');
        body.appendChild(el);
        return el;
    }
    const canvas = layer('canvas', 'fx-constellation');
    const gridEl = layer('div', 'fx-grid');
    const spotEl = layer('div', 'fx-spotlight');

    // ── Position de la souris (lissée) ──────────────────────────────
    const mouse = { x: innerWidth / 2, y: innerHeight * 0.4, sx: innerWidth / 2, sy: innerHeight * 0.4, active: false };
    window.addEventListener('pointermove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
        mouse.active = true;
        body.classList.remove('fx-pointer-out');
    }, { passive: true });
    document.addEventListener('pointerleave', () => { mouse.active = false; body.classList.add('fx-pointer-out'); });
    document.addEventListener('mouseout', (e) => { if (!e.relatedTarget) { mouse.active = false; body.classList.add('fx-pointer-out'); } });

    // ── Curseur personnalisé ────────────────────────────────────────
    let dot = null, ring = null;
    if (finePointer) {
        dot = layer('div', 'fx-cursor-dot');
        ring = layer('div', 'fx-cursor-ring');
        const hoverSel = 'a, button, .btn, label, input, textarea, select, [data-disc], .donation-method';
        document.addEventListener('pointerover', (e) => {
            if (e.target.closest(hoverSel)) body.classList.add('fx-hovering');
        });
        document.addEventListener('pointerout', (e) => {
            if (e.target.closest(hoverSel) && !(e.relatedTarget && e.relatedTarget.closest && e.relatedTarget.closest(hoverSel))) {
                body.classList.remove('fx-hovering');
            }
        });
        document.addEventListener('pointerdown', () => body.classList.add('fx-pressing'));
        document.addEventListener('pointerup', () => body.classList.remove('fx-pressing'));
    }

    // ── Constellation interactive (attirée par la souris) ───────────
    const ctx = canvas.getContext('2d');
    let W = 0, H = 0, DPR = 0.6;   // rendu en résolution réduite : ambiance douce, 3x moins de pixels
    let nodes = [];
    function resize() {
        W = innerWidth; H = innerHeight;
        canvas.width = W * DPR; canvas.height = H * DPR;
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        const count = Math.round(Math.min(80, Math.max(30, (W * H) / 18000)));
        nodes = Array.from({ length: count }, () => ({
            x: Math.random() * W,
            y: Math.random() * H,
            vx: (Math.random() - 0.5) * 0.35,
            vy: (Math.random() - 0.5) * 0.35,
            r: Math.random() * 1.6 + 0.6,
            hue: Math.random() < 0.7 ? 'rgba(88,166,255,' : 'rgba(163,113,247,'
        }));
    }
    resize();
    window.addEventListener('resize', resize);

    function drawConstellation() {
        ctx.clearRect(0, 0, W, H);
        const link = 130, mouseLink = 190;
        for (let i = 0; i < nodes.length; i++) {
            const n = nodes[i];
            if (!reduced) {
                n.x += n.vx * 2; n.y += n.vy * 2;
                if (n.x < -20) n.x = W + 20; else if (n.x > W + 20) n.x = -20;
                if (n.y < -20) n.y = H + 20; else if (n.y > H + 20) n.y = -20;
            }
            // Attraction douce vers la souris
            if (mouse.active) {
                const dx = mouse.sx - n.x, dy = mouse.sy - n.y;
                const d = Math.hypot(dx, dy);
                if (d < mouseLink) {
                    n.x += dx * 0.012; n.y += dy * 0.012;
                    ctx.strokeStyle = 'rgba(88,166,255,' + (1 - d / mouseLink) * 0.75 + ')';
                    ctx.lineWidth = 1;
                    ctx.beginPath(); ctx.moveTo(n.x, n.y); ctx.lineTo(mouse.sx, mouse.sy); ctx.stroke();
                }
            }
            for (let j = i + 1; j < nodes.length; j++) {
                const m = nodes[j];
                const d = Math.hypot(n.x - m.x, n.y - m.y);
                if (d < link) {
                    ctx.strokeStyle = 'rgba(88,166,255,' + (1 - d / link) * 0.22 + ')';
                    ctx.lineWidth = 0.8;
                    ctx.beginPath(); ctx.moveTo(n.x, n.y); ctx.lineTo(m.x, m.y); ctx.stroke();
                }
            }
            ctx.fillStyle = n.hue + '0.18)';
            ctx.beginPath(); ctx.arc(n.x, n.y, n.r * 3.2, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = n.hue + '0.95)';
            ctx.beginPath(); ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2); ctx.fill();
        }
    }

    // ── Boucle d'animation unique ───────────────────────────────────
    let ringX = mouse.x, ringY = mouse.y, lightPlaced = false, tickCount = 0;
    function frame() {
        const ox = mouse.sx, oy = mouse.sy;
        mouse.sx += (mouse.x - mouse.sx) * 0.18;
        mouse.sy += (mouse.y - mouse.sy) * 0.18;
        if (Math.abs(mouse.sx - ox) + Math.abs(mouse.sy - oy) > 0.3 || !lightPlaced) {
            // déplacement par transform uniquement (composité par le GPU, pas de repeinte plein écran)
            const gx = Math.round(mouse.sx - 280), gy = Math.round(mouse.sy - 280);
            gridEl.style.transform = 'translate3d(' + gx + 'px,' + gy + 'px,0)';
            gridEl.style.backgroundPosition = (-gx) + 'px ' + (-gy) + 'px';
            spotEl.style.transform = 'translate3d(' + Math.round(mouse.sx - 550) + 'px,' + Math.round(mouse.sy - 550) + 'px,0)';
            lightPlaced = true;
        }
        if (dot && (Math.abs(mouse.x - ringX) + Math.abs(mouse.y - ringY) > 0.2)) {
            dot.style.transform = 'translate3d(' + mouse.x + 'px,' + mouse.y + 'px,0)';
            ringX += (mouse.x - ringX) * 0.16;
            ringY += (mouse.y - ringY) * 0.16;
            ring.style.transform = 'translate3d(' + ringX + 'px,' + ringY + 'px,0)';
        }
        // constellation à 30 i/s : suffisant visuellement, deux fois moins coûteux
        if (!document.hidden && (tickCount++ & 1) === 0) drawConstellation();
        requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);

    // ── Cartes : halo/bordure qui suivent la souris ─────────────────
    const glowSel = '.card, .post-card, .sidebar-widget, .auth-card, .profile-header';
    document.addEventListener('pointermove', (e) => {
        const card = e.target.closest && e.target.closest(glowSel);
        if (!card) return;
        const r = card.getBoundingClientRect();
        card.style.setProperty('--gx', (e.clientX - r.left) + 'px');
        card.style.setProperty('--gy', (e.clientY - r.top) + 'px');
    }, { passive: true });

    // ── Inclinaison 3D ──────────────────────────────────────────────
    document.querySelectorAll('.post-card, .sidebar-widget .card, .hero-stat, .donation-method, .member-card').forEach(el => {
        if (el.closest('form')) return;
        el.setAttribute('data-tilt', '');
    });
    if (finePointer) {
        document.querySelectorAll('[data-tilt]').forEach(el => {
            el.addEventListener('pointermove', (e) => {
                const r = el.getBoundingClientRect();
                const px = (e.clientX - r.left) / r.width - 0.5;
                const py = (e.clientY - r.top) / r.height - 0.5;
                const max = r.width > 500 ? 2.5 : 7;
                el.classList.add('is-tilting');
                el.style.transform = 'perspective(900px) rotateX(' + (-py * max) + 'deg) rotateY(' + (px * max) + 'deg) translateY(-3px)';
            });
            el.addEventListener('pointerleave', () => {
                el.classList.remove('is-tilting');
                el.style.transform = '';
            });
        });
    }

    // ── Boutons : onde au clic ──────────────────────────────────────
    document.addEventListener('pointerdown', (e) => {
        const btn = e.target.closest && e.target.closest('.btn');
        if (!btn) return;
        const r = btn.getBoundingClientRect();
        const s = document.createElement('span');
        s.className = 'fx-ripple';
        s.style.left = (e.clientX - r.left) + 'px';
        s.style.top = (e.clientY - r.top) + 'px';
        btn.appendChild(s);
        setTimeout(() => s.remove(), 700);
    });

    // ── Révélation au scroll (décalage par lot) ─────────────────────
    const revealSel = '.post-card, .sidebar-widget, .card, .hero-stat, .profile-header, .news-card, .auth-card';
    const io = new IntersectionObserver((entries) => {
        let k = 0;
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.style.setProperty('--reveal-delay', (k++ * 70) + 'ms');
            entry.target.classList.add('fx-visible');
            io.unobserve(entry.target);
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll(revealSel).forEach(el => {
        if (el.closest('.discs-page')) return;
        el.classList.remove('animate-in');
        el.classList.add('fx-reveal');
        io.observe(el);
    });

    // ── Titres : effet de décryptage ────────────────────────────────
    const glyphs = '01<>/\\{}[]#$%&@*+=ABCDEF';
    function decrypt(el, duration) {
        const finalText = el.textContent;
        if (!finalText.trim() || reduced) return;
        const start = performance.now();
        (function tick(now) {
            const p = Math.min(1, (now - start) / duration);
            const reveal = Math.floor(p * finalText.length);
            let out = '';
            for (let i = 0; i < finalText.length; i++) {
                const c = finalText[i];
                out += (i < reveal || c === ' ' || c === '\n') ? c : glyphs[(Math.random() * glyphs.length) | 0];
            }
            el.textContent = out;
            if (p < 1) requestAnimationFrame(tick); else el.textContent = finalText;
        })(start);
    }
    window.ICSDecrypt = decrypt;
    document.querySelectorAll('.hero h1, .auth-card h1, .page-content > h2:first-child, .profile-info h1 .fx-name').forEach(el => decrypt(el, 1100));

    // ── Compteurs animés ────────────────────────────────────────────
    function animateCount(el) {
        const raw = el.textContent.trim();
        const m = raw.match(/^([\d\s  ]+)(.*)$/);
        if (!m) return;
        const target = parseInt(m[1].replace(/[\s  ]/g, ''), 10);
        if (!target) return;
        const suffix = m[2].trim() ? ' ' + m[2].trim() : '';
        const start = performance.now(), dur = 1600;
        (function tick(now) {
            const p = Math.min(1, (now - start) / dur);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = new Intl.NumberFormat('fr-FR').format(Math.round(target * eased)).replace(/ /g, ' ') + suffix;
            if (p < 1) requestAnimationFrame(tick);
        })(start);
    }
    const countIO = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { animateCount(e.target); countIO.unobserve(e.target); } });
    });
    document.querySelectorAll('.hero-stat-value, .donation-amount').forEach(el => countIO.observe(el));

    // ── Transition de sortie sur les liens internes ─────────────────
    document.addEventListener('click', (e) => {
        const a = e.target.closest && e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;
        const url = new URL(a.href, location.href);
        if (url.origin !== location.origin || url.hash || a.hasAttribute('download') || a.getAttribute('onclick')) return;
        e.preventDefault();
        body.classList.add('fx-leaving');
        setTimeout(() => { location.href = url.href; }, 180);
    });
    window.addEventListener('pageshow', () => body.classList.remove('fx-leaving'));
})();
