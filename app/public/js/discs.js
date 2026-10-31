/**
 * ICS Community — Disques membres
 * Carrousel 3D (molette, glisser, clavier, miniatures), inclinaison et
 * reflet qui suivent la souris, cercle tracé au survol, zoom vers le profil.
 * Gère aussi le disque de la page profil (.profile-disc).
 */
(function () {
    'use strict';

    const mouse = { x: innerWidth / 2, y: innerHeight / 2 };
    window.addEventListener('pointermove', (e) => { mouse.x = e.clientX; mouse.y = e.clientY; }, { passive: true });

    // ── Rotation : le disque reste droit, tourne au survol puis revient à l'endroit,
    //    le reflet reste orienté vers la souris (comme une vraie lumière)
    function makeSpinner(discEl) {
        const bodyEl = discEl.querySelector('.disc-body');
        return {
            disc: discEl,
            angle: 0,
            vel: 0,
            target: 0,          // vitesse voulue en degrés/seconde (0 = repos)
            last: performance.now(),
            tx: null, ty: null, lastAngle: null, lastSheen: null, lastT: '',
            // cx, cy : centre du disque à l'écran (calculé par l'appelant, sans relire le DOM)
            update(cx, cy) {
                const now = performance.now();
                const dt = Math.min(0.05, (now - this.last) / 1000);
                this.last = now;
                if (this.target > 0) {
                    this.vel += (this.target - this.vel) * 0.06;
                    this.angle += this.vel * dt;
                } else {
                    // ressort vers le tour complet suivant (photo à l'endroit)
                    const rest = Math.ceil((this.angle - 1) / 360) * 360;
                    this.vel *= 0.9;
                    this.angle += (rest - this.angle) * 0.07 + this.vel * dt * 0.2;
                    if (Math.abs(rest - this.angle) < 0.05) { this.angle = rest; this.vel = 0; }
                }
                if (this.angle !== this.lastAngle) {
                    bodyEl.style.setProperty('--spin', this.angle.toFixed(2) + 'deg');
                    this.lastAngle = this.angle;
                }
                const light = Math.atan2(mouse.y - cy, mouse.x - cx) * 180 / Math.PI + 90;
                const sheen = Math.round((light - this.angle) * 2) / 2;
                if (sheen !== this.lastSheen) {
                    discEl.style.setProperty('--sheen', sheen + 'deg');
                    this.lastSheen = sheen;
                }
            },
            tilt(cx, cy, baseX, baseY, rangeX, rangeY) {
                const nx = Math.max(-1, Math.min(1, (mouse.x - cx) / (innerWidth / 2)));
                const ny = Math.max(-1, Math.min(1, (mouse.y - cy) / (innerHeight / 2)));
                const wantX = baseX - ny * rangeX, wantY = baseY + nx * rangeY;
                this.tx = this.tx == null ? wantX : this.tx + (wantX - this.tx) * 0.1;
                this.ty = this.ty == null ? wantY : this.ty + (wantY - this.ty) * 0.1;
                const key = this.tx.toFixed(1) + '|' + this.ty.toFixed(1);
                if (key === this.lastT) return;
                this.lastT = key;
                discEl.style.setProperty('--tx', this.tx.toFixed(1) + 'deg');
                discEl.style.setProperty('--ty', this.ty.toFixed(1) + 'deg');
            },
            resetTilt() {
                this.tx = this.ty = null;
                this.lastT = '';
                discEl.style.removeProperty('--tx');
                discEl.style.removeProperty('--ty');
            }
        };
    }

    // ── Page profil ─────────────────────────────────────────────────
    document.querySelectorAll('.profile-disc').forEach(wrap => {
        const sp = makeSpinner(wrap.querySelector('.disc'));
        wrap.addEventListener('pointerenter', () => { sp.target = 160; });
        wrap.addEventListener('pointerleave', () => { sp.target = 0; });
        (function loop() {
            const r = wrap.getBoundingClientRect();   // une seule lecture, avant les écritures
            const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
            sp.tilt(cx, cy, 24, -16, 22, 30);
            sp.update(cx, cy);
            requestAnimationFrame(loop);
        })();
    });

    // ── Page membres : carrousel ────────────────────────────────────
    const page = document.querySelector('[data-discs]');
    if (!page) return;

    const stage = page.querySelector('[data-disc-stage]');
    const slots = Array.from(page.querySelectorAll('[data-disc]'));
    const thumbs = Array.from(page.querySelectorAll('[data-disc-thumb]'));
    const titleEl = page.querySelector('[data-disc-title]');
    const indexEl = page.querySelector('[data-disc-index]');
    const fields = {};
    page.querySelectorAll('[data-disc-field]').forEach(el => { fields[el.dataset.discField] = el; });
    const n = slots.length;
    if (!n) return;

    const spinners = slots.map(s => makeSpinner(s.querySelector('.disc')));
    let target = 0, pos = 0, current = -1, size = 360, slotCenterY = 0, lastFrame = performance.now();
    let launching = false;

    function measure() {
        const r = stage.getBoundingClientRect();
        const wide = innerWidth > 720;
        size = Math.round(Math.max(170, wide
            ? Math.min(r.height * 0.5, r.width * 0.36, 420)
            : Math.min(r.height * 0.78, r.width * 0.62, 340)));
        stage.style.setProperty('--slot', size + 'px');
        const s0 = slots[0];
        slotCenterY = s0 ? s0.offsetTop + s0.offsetHeight / 2 : r.height / 2;
    }
    measure();
    window.addEventListener('resize', measure);

    function pad(i) { return String(i).padStart(2, '0'); }

    function setCurrent(i) {
        if (i === current) return;
        current = i;
        const s = slots[i];
        slots.forEach((el, k) => {
            el.classList.toggle('is-current', k === i);
            el.tabIndex = k === i ? 0 : -1;
        });
        thumbs.forEach((t, k) => t.classList.toggle('is-current', k === i));
        indexEl.textContent = pad(i + 1);
        titleEl.textContent = s.dataset.name;
        if (window.ICSDecrypt) window.ICSDecrypt(titleEl, 600);
        Object.keys(fields).forEach(key => { fields[key].textContent = s.dataset[key] || '—'; });
        spinners.forEach((sp, k) => { if (k !== i) { sp.resetTilt(); sp.target = 0; } });
        stage.classList.remove('is-hovering-current');
    }

    function go(i) {
        target = Math.max(0, Math.min(n - 1, i));
    }

    let lastPos = null, lastSize = null, lastXs = [];
    function layout() {
        if (pos === lastPos && size === lastSize) return lastXs;
        lastPos = pos; lastSize = size;
        const step = size * 0.95, far = size * 0.5;
        const xs = [];
        slots.forEach((el, i) => {
            const d = i - pos, a = Math.abs(d), sign = Math.sign(d);
            const x = sign * (a <= 1 ? a * step : step + (a - 1) * far);
            const scale = a <= 1 ? 1 - a * 0.46 : Math.max(0.3, 0.54 - (a - 1) * 0.1);
            const z = -a * 180;
            const ry = -d * 12;
            const opacity = a > 3 ? 0 : a > 2 ? 1 - (a - 2) : 1;
            el.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + (a * 18).toFixed(1) + 'px,' + z.toFixed(1) + 'px) rotateY(' + ry.toFixed(2) + 'deg) scale(' + scale.toFixed(3) + ')';
            el.style.opacity = opacity.toFixed(3);
            el.style.zIndex = String(100 - Math.round(a * 10));
            el.style.visibility = opacity <= 0.01 ? 'hidden' : 'visible';
            xs.push({ x: x, y: a * 18 });
        });
        lastXs = xs;
        return xs;
    }

    function frame() {
        // lecture unique de la géométrie avant toute écriture (pas de layout forcé)
        const sr = stage.getBoundingClientRect();
        const baseX = sr.left + sr.width / 2, baseY = sr.top + slotCenterY;
        const now = performance.now();
        const dt = Math.min(0.1, (now - lastFrame) / 1000);
        lastFrame = now;
        // amortissement basé sur le temps : même vitesse quel que soit le nombre d'images/s
        if (!dragging) pos += (target - pos) * (1 - Math.pow(0.0004, dt));
        if (Math.abs(target - pos) < 0.002) pos = target;
        const xs = layout();
        setCurrent(Math.max(0, Math.min(n - 1, Math.round(pos))));
        slots.forEach((el, i) => {
            if (Math.abs(i - pos) > 2.5) return;
            const cx = baseX + xs[i].x, cy = baseY + xs[i].y;
            if (i === current && !launching) spinners[i].tilt(cx, cy, 32, -18, 18, 28);
            spinners[i].update(cx, cy);
        });
        requestAnimationFrame(frame);
    }

    // Molette (sur la scène uniquement)
    let wheelAcc = 0, wheelLock = 0;
    stage.addEventListener('wheel', (e) => {
        const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
        const atEdge = (delta < 0 && target === 0) || (delta > 0 && target === n - 1);
        if (atEdge) return; // laisser défiler la page aux extrémités
        e.preventDefault();
        if (performance.now() < wheelLock) return;
        wheelAcc += delta;
        if (Math.abs(wheelAcc) > 40) {
            go(target + Math.sign(wheelAcc));
            wheelAcc = 0;
            wheelLock = performance.now() + 280;
        }
    }, { passive: false });

    // Glisser (souris + tactile)
    let dragging = false, startX = 0, startPos = 0, moved = 0, suppressClick = false;
    stage.addEventListener('pointerdown', (e) => {
        if (e.button !== 0 || launching) return;
        dragging = true; moved = 0; startX = e.clientX; startPos = pos;
        stage.classList.add('is-dragging');
    });
    window.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const dx = e.clientX - startX;
        moved = Math.max(moved, Math.abs(dx));
        let p = startPos - dx / (size * 0.95);
        if (p < 0) p = p * 0.3; else if (p > n - 1) p = n - 1 + (p - n + 1) * 0.3;
        pos = p;
    });
    window.addEventListener('pointerup', () => {
        if (!dragging) return;
        dragging = false;
        stage.classList.remove('is-dragging');
        if (moved > 6) { suppressClick = true; go(Math.round(pos)); setTimeout(() => { suppressClick = false; }, 50); }
    });

    // Clic : disque latéral → le centrer ; disque central → ouvrir le profil
    slots.forEach((el, i) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            if (suppressClick || launching) return;
            if (i !== current) { go(i); return; }
            launch(el, i);
        });
        el.addEventListener('pointerenter', () => {
            if (i === current) { stage.classList.add('is-hovering-current'); spinners[i].target = 160; }
        });
        el.addEventListener('pointerleave', () => {
            stage.classList.remove('is-hovering-current'); if (!launching) spinners[i].target = 0;
        });
        el.addEventListener('focus', () => { if (i !== current) go(i); });
    });

    function launch(el, i) {
        launching = true;
        spinners[i].target = 1400;
        spinners[i].resetTilt();
        el.classList.add('is-launching');
        page.classList.add('is-launching');
        setTimeout(() => { document.body.classList.add('fx-leaving'); }, 450);
        setTimeout(() => { location.href = el.getAttribute('href'); }, 650);
    }
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            launching = false;
            page.classList.remove('is-launching');
            slots.forEach(s => s.classList.remove('is-launching'));
            spinners.forEach(sp => { sp.target = 0; });
        }
    });

    // Miniatures et flèches
    thumbs.forEach((t, i) => t.addEventListener('click', () => go(i)));
    page.querySelector('[data-disc-prev]').addEventListener('click', () => go(target - 1));
    page.querySelector('[data-disc-next]').addEventListener('click', () => go(target + 1));

    // Clavier
    document.addEventListener('keydown', (e) => {
        if (e.target.closest('input, textarea, select')) return;
        if (e.key === 'ArrowRight') { go(target + 1); e.preventDefault(); }
        else if (e.key === 'ArrowLeft') { go(target - 1); e.preventDefault(); }
        else if (e.key === 'Home') { go(0); e.preventDefault(); }
        else if (e.key === 'End') { go(n - 1); e.preventDefault(); }
    });

    setCurrent(0);
    requestAnimationFrame(frame);
})();
