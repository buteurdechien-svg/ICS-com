/**
 * ICS Community — JavaScript client
 * Laboratoire de formation — données fictives
 */
(function () {
    'use strict';

    // ── Navigation mobile ───────────────────────────────────────────
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            navToggle.setAttribute('aria-expanded',
                navLinks.classList.contains('active'));
        });
    }

    // ── Flash messages — auto-dismiss (succès/info seulement : les erreurs restent visibles)
    document.querySelectorAll('.alert-success, .alert-info').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    });

    // ── Likes (AJAX) ────────────────────────────────────────────────
    document.querySelectorAll('.btn-like').forEach(btn => {
        btn.addEventListener('click', async () => {
            const postId = btn.dataset.postId;
            if (!postId) return;
            try {
                const res = await fetch('/api/posts/like', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${postId}&csrf_token=${getCSRF()}`
                });
                const data = await res.json();
                if (data.success) {
                    const countEl = btn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.count;
                    btn.classList.toggle('liked', data.liked);
                }
            } catch (e) { console.error('Like error', e); }
        });
    });

    // ── Comments (AJAX) ─────────────────────────────────────────────
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const postId = form.dataset.postId;
            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            if (!content) return;

            try {
                const res = await fetch('/api/comments', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${postId}&content=${encodeURIComponent(content)}&csrf_token=${getCSRF()}`
                });
                const data = await res.json();
                if (data.success) {
                    const list = form.closest('.post-card').querySelector('.comment-list');
                    if (list) {
                        const div = document.createElement('div');
                        div.className = 'comment';
                        div.innerHTML = `<strong>${escapeHtml(data.user)}</strong> <span class="text-muted">à l'instant</span><p>${escapeHtml(content)}</p>`;
                        list.appendChild(div);
                    }
                    input.value = '';
                }
            } catch (e) { console.error('Comment error', e); }
        });
    });

    // ── Search (vuln endpoint) ──────────────────────────────────────
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const q = searchInput.value.trim();
            if (q.length < 2) { searchResults.innerHTML = ''; return; }
            searchTimeout = setTimeout(async () => {
                try {
                    // VULN INTENTIONNELLE : le paramètre q est injecté directement dans SQL côté serveur
                    const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    searchResults.innerHTML = '';
                    if (data.results && data.results.length) {
                        data.results.forEach(u => {
                            const a = document.createElement('a');
                            a.href = `/profile/${u.id}`;
                            a.className = 'search-result-item';
                            a.textContent = `${u.display_name} — ${u.department || ''}`;
                            searchResults.appendChild(a);
                        });
                    } else {
                        searchResults.innerHTML = '<div class="text-muted" style="padding:8px;">Aucun résultat</div>';
                    }
                } catch (e) { console.error('Search error', e); }
            }, 300);
        });
    }

    // ── Messages — auto-scroll ──────────────────────────────────────
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // ── Donation — payment method selector ──────────────────────────
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', () => {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            method.classList.add('selected');
            const radio = method.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // ── Donation amount — client-only validation (vuln: no server check) ─
    const donationForm = document.getElementById('donation-form');
    if (donationForm) {
        donationForm.addEventListener('submit', (e) => {
            const amount = parseInt(donationForm.querySelector('[name="amount"]').value, 10);
            // Validation côté client uniquement — contournable
            if (amount < 100) {
                e.preventDefault();
                alert('Le montant minimum est de 100 FCFA');
            }
        });
    }

    // ── Visibility selector for posts ───────────────────────────────
    const visSelect = document.getElementById('post-visibility');
    if (visSelect) {
        visSelect.addEventListener('change', () => {
            const label = visSelect.options[visSelect.selectedIndex].text;
            const indicator = document.getElementById('visibility-indicator');
            if (indicator) indicator.textContent = label;
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────
    function getCSRF() {
        const el = document.querySelector('input[name="csrf_token"]');
        return el ? el.value : '';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
