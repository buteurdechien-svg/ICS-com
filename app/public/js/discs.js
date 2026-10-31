/**
 * ICS Community — Planètes membres
 * Carrousel fluide (amortissement à ressort, inertie au glisser, molette,
 * clavier, miniatures), lumière qui suit la souris, zoom plein écran
 * vers le profil puis « atterrissage » de la planète sur la page profil.
 */
(function () {
    'use strict';

    const mouse = { x: innerWidth * 0.3, y: innerHeight * 0.2 };
    window.addEventListener('pointermove', (e) => { mouse.x = e.clientX; mouse.y = e.clientY; }, { passive: true });

    const clamp = (v, a, b) => Math.max(a, Math.min(b, v));

    // Amortissement critique (comme SmoothDamp) : départ et arrivée en douceur,
    // vitesse conservée quand la cible change en cours de route.
    function smoothDamp(cur, target, state, smoothTime, dt) {
        const omega = 2 / smoothTime;
        const x = omega * dt;
        const exp = 1 / (1 + x + 0.48 * x * x + 0.235 * x * x * x);
        const change = cur - target;
        const temp = (state.v + omega * change) * dt;
        state.v = (state.v - omega * temp) * exp;
        return target + (change + temp) * exp;
    }

    // ── Lumière d'une planète : la souris est le soleil ─────────────
    function makeLight(planetEl) {
        return {
            el: planetEl, lx: 35, ly: 30, px: 0, py: 0, last: '',
            update(cx, cy, size, dt) {
                const dx = mouse.x - cx, dy = mouse.y - cy;
                const reach = size * 1.1;
                const nx = clamp(dx / reach, -1, 1), ny = clamp(dy / reach, -1, 1);
                const k = 1 - Math.exp(-dt * 9);
                this.lx += (50 + nx * 30 - this.lx) * k;
                this.ly += (50 + ny * 30 - this.ly) * k;
                this.px += (nx * 3 - this.px) * k;
                this.py += (ny * 3 - this.py) * k;
                const key = this.lx.toFixed(1) + this.ly.toFixed(1) + this.px.toFixed(2) + this.py.toFixed(2);
                if (key === this.last) return;
                this.last = key;
                const st = this.el.style;
                st.setProperty('--lx', this.lx.toFixed(1) + '%');
                st.setProperty('--ly', this.ly.toFixed(1) + '%');
                st.setProperty('--px', this.px.toFixed(2) + '%');
                st.setProperty('--py', this.py.toFixed(2) + '%');
            }
        };
    }

    // ── Page profil ─────────────────────────────────────────────────
    const profilePlanet = document.querySelector('.profile-planet');
    if (profilePlanet) {
        try {
            if (sessionStorage.getItem('ics-arrive') === '1') {
                sessionStorage.removeItem('ics-arrive');
                document.body.classList.add('is-arriving');
            }
        } catch (e) { /* stockage indisponible : pas d'animation d'arrivée */ }
        const light = makeLight(profilePlanet.querySelector('.planet'));
        let last = performance.now();
        const loop = (now) => {
            const dt = clamp((now - last) / 1000, 0.001, 0.1);
            last = now;
            const r = profilePlanet.getBoundingClientRect();
            light.update(r.left + r.width / 2, r.top + r.height / 2, r.width, dt);
            requestAnimationFrame(loop);
        };
        requestAnimationFrame(loop);
    }

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

    const planets = slots.map(s => s.querySelector('.planet'));
    const dims = planets.map(p => p.querySelector('.planet-dim'));
    const lights = planets.map(makeLight);
    const motion = { v: 0 };
    let target = 0, pos = 0, current = -1, size = 360, slotCenterY = 0;
    let lastFrame = performance.now(), launching = false;
    let lastPos = null, xs = [];

    function measure() {
        const r = stage.getBoundingClientRect();
        const wide = innerWidth > 720;
        size = Math.round(Math.max(170, wide
            ? Math.min(r.height * 0.5, r.width * 0.34, 400)
            : Math.min(r.height * 0.74, r.width * 0.6, 320)));
        stage.style.setProperty('--slot', size + 'px');
        slotCenterY = slots[0].offsetTop + slots[0].offsetHeight / 2;
        lastPos = null; // forcer un nouveau placement
    }

    function pad(i) { return String(i).padStart(2, '0'); }

    // Fondu des informations : Web Animations (pas de recalcul de mise en page forcé)
    function swap(el, text) {
        el.textContent = text;
        if (el.animate) {
            el.animate([
                { opacity: 0, transform: 'translateY(8px)' },
                { opacity: 1, transform: 'translateY(0)' }
            ], { duration: 520, easing: 'cubic-bezier(.16,1,.3,1)' });
        }
    }

    function setCurrent(i) {
        if (i === current) return;
        current = i;
        const s = slots[i];
        slots.forEach((el, k) => {
            el.classList.toggle('is-current', k === i);
            el.tabIndex = k === i ? 0 : -1;
        });
        thumbs.forEach((t, k) => t.classList.toggle('is-current', k === i));
        swap(indexEl, pad(i + 1));
        titleEl.textContent = s.dataset.name;
        if (window.ICSDecrypt) window.ICSDecrypt(titleEl, 650);
        Object.keys(fields).forEach(key => swap(fields[key], s.dataset[key] || '—'));
        stage.classList.toggle('is-hovering-current', s.matches(':hover'));
    }

    function go(i) { target = clamp(Math.round(i), 0, n - 1); }

    // Placement : arc orbital, planètes latérales plus petites et assombries
    function layout() {
        if (pos === lastPos) return;
        lastPos = pos;
        const step = size * 0.98, far = size * 0.52;
        xs = slots.map((el, i) => {
            const d = i - pos, a = Math.abs(d), sign = Math.sign(d);
            const x = sign * (a <= 1 ? a * step : step + (a - 1) * far);
            const y = Math.min(a, 3) * Math.min(a, 3) * 10;
            const scale = a <= 1 ? 1 - a * 0.48 : Math.max(0.28, 0.52 - (a - 1) * 0.1);
            const opacity = a > 3 ? 0 : a > 2 ? 1 - (a - 2) : 1;
            el.style.transform = 'translate3d(' + x.toFixed(2) + 'px,' + y.toFixed(2) + 'px,0) scale(' + scale.toFixed(4) + ')';
            el.style.opacity = opacity.toFixed(3);
            el.style.zIndex = String(100 - Math.round(a * 10));
            el.style.visibility = opacity <= 0.01 ? 'hidden' : '';
            dims[i].style.opacity = Math.min(a * 0.34, 0.6).toFixed(3);
            return { x: x, y: y, s: scale };
        });
    }

    // Molette / pavé tactile (sur la scène uniquement)
    let wheelAcc = 0, wheelLock = 0;
    stage.addEventListener('wheel', (e) => {
        const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
        if ((delta < 0 && target === 0) || (delta > 0 && target === n - 1)) return; // laisser défiler la page
        e.preventDefault();
        if (launching || performance.now() < wheelLock) return;
        wheelAcc += delta;
        if (Math.abs(wheelAcc) > 30) {
            go(target + Math.sign(wheelAcc));
            wheelAcc = 0;
            wheelLock = performance.now() + 200;
        }
    }, { passive: false });

    // Glisser avec inertie (souris + tactile)
    let dragging = false, startX = 0, startPos = 0, moved = 0, suppressClick = false;
    let lastX = 0, lastT = 0, dragVel = 0;
    stage.addEventListener('pointerdown', (e) => {
        if (e.button !== 0 || launching) return;
        dragging = true; moved = 0; startX = lastX = e.clientX; startPos = pos;
        lastT = performance.now(); dragVel = 0;
        stage.classList.add('is-dragging');
    });
    window.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const now = performance.now();
        const dx = e.clientX - startX;
        moved = Math.max(moved, Math.abs(dx));
        let p = startPos - dx / (size * 0.98);
        if (p < 0) p *= 0.3; else if (p > n - 1) p = n - 1 + (p - n + 1) * 0.3;
        const dtv = Math.max(1, now - lastT) / 1000;
        const inst = -(e.clientX - lastX) / (size * 0.98) / dtv; // positions par seconde
        dragVel = dragVel * 0.75 + inst * 0.25;
        lastX = e.clientX; lastT = now;
        pos = p;
    });
    window.addEventListener('pointerup', () => {
        if (!dragging) return;
        dragging = false;
        stage.classList.remove('is-dragging');
        if (performance.now() - lastT > 90) dragVel = 0; // relâché à l'arrêt
        if (moved > 6) {
            suppressClick = true;
            const v = clamp(dragVel, -12, 12);
            motion.v = v;              // l'inertie continue dans l'amortissement
            go(pos + v * 0.22);        // projection du lancer
            setTimeout(() => { suppressClick = false; }, 50);
        }
    });

    // Clic : planète latérale → la centrer ; planète centrale → ouvrir le profil
    slots.forEach((el, i) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            if (suppressClick || launching) return;
            if (i !== current) { go(i); return; }
            launch(el);
        });
        el.addEventListener('pointerenter', () => { if (i === current) stage.classList.add('is-hovering-current'); });
        el.addEventListener('pointerleave', () => stage.classList.remove('is-hovering-current'));
        el.addEventListener('focus', () => { if (i !== current) go(i); });
    });

    // Zoom plein écran : la planète grossit jusqu'à couvrir l'écran puis on ouvre le profil
    let warp = null;
    function launch(el) {
        launching = true;
        const surface = el.querySelector('.planet-surface');
        const r = surface.getBoundingClientRect();
        warp = document.createElement('div');
        warp.className = 'planet-warp';
        warp.setAttribute('aria-hidden', 'true');
        warp.style.left = r.left + 'px';
        warp.style.top = r.top + 'px';
        warp.style.width = r.width + 'px';
        warp.style.height = r.height + 'px';
        const bg = surface.style.backgroundImage;
        warp.style.background = bg
            ? bg + ' center 32% / 116% no-repeat, #1c2128'
            : 'radial-gradient(circle at 35% 30%, #4b8ff8, #2f81f7 40%, #8957e5)';
        document.body.appendChild(warp);
        el.style.visibility = 'hidden';
        page.classList.add('is-launching');
        const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
        const cover = 2 * Math.hypot(Math.max(cx, innerWidth - cx), Math.max(cy, innerHeight - cy)) / r.width * 1.04;
        try { sessionStorage.setItem('ics-arrive', '1'); } catch (e) { /* ignoré */ }
        const href = el.getAttribute('href');
        if (warp.animate) {
            warp.animate([
                { transform: 'scale(1)' },
                { transform: 'scale(1.1)', offset: 0.2 },
                { transform: 'scale(' + cover.toFixed(2) + ')' }
            ], { duration: 780, easing: 'cubic-bezier(.65,0,.2,1)', fill: 'forwards' });
        }
        setTimeout(() => { location.href = href; }, 760);
    }
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        launching = false;
        if (warp) { warp.remove(); warp = null; }
        page.classList.remove('is-launching');
        lastPos = null;
    });

    // Miniatures, flèches, clavier
    thumbs.forEach((t, i) => t.addEventListener('click', () => go(i)));
    page.querySelector('[data-disc-prev]').addEventListener('click', () => go(target - 1));
    page.querySelector('[data-disc-next]').addEventListener('click', () => go(target + 1));
    document.addEventListener('keydown', (e) => {
        if (launching || (e.target.closest && e.target.closest('input, textarea, select'))) return;
        if (e.key === 'ArrowRight') { go(target + 1); e.preventDefault(); }
        else if (e.key === 'ArrowLeft') { go(target - 1); e.preventDefault(); }
        else if (e.key === 'Home') { go(0); e.preventDefault(); }
        else if (e.key === 'End') { go(n - 1); e.preventDefault(); }
    });

    function frame(now) {
        const dt = clamp((now - lastFrame) / 1000, 0.001, 0.1);
        lastFrame = now;
        const sr = stage.getBoundingClientRect(); // lecture unique avant les écritures
        if (!dragging) {
            pos = smoothDamp(pos, target, motion, 0.42, dt);
            if (Math.abs(target - pos) < 0.0008 && Math.abs(motion.v) < 0.002) { pos = target; motion.v = 0; }
        }
        layout();
        setCurrent(clamp(Math.round(pos), 0, n - 1));
        // Pendant le défilement, l'éclairage est figé (pas de repeinte des dégradés) :
        // seuls des transforms bougent, entièrement gérés par le GPU. Il se recale à l'arrêt.
        const moving = dragging || Math.abs(target - pos) > 0.01 || Math.abs(motion.v) > 0.05;
        if (!moving) {
            const baseX = sr.left + sr.width / 2, baseY = sr.top + slotCenterY;
            for (let i = 0; i < n; i++) {
                if (Math.abs(i - pos) > 2.5 || !xs[i]) continue;
                lights[i].update(baseX + xs[i].x, baseY + xs[i].y, size * xs[i].s, dt);
            }
        }
        requestAnimationFrame(frame);
    }

    measure();
    window.addEventListener('resize', measure);
    setCurrent(0);
    requestAnimationFrame((t) => { lastFrame = t; frame(t); });
})();
