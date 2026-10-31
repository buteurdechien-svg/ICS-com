<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p class="footer-text">
            &copy; 2025 <a href="/">Ivoire Cyber Shield</a> — Plateforme communautaire de formation en cybersécurité
        </p>
        <p class="footer-text mt-sm" style="font-family: var(--font-mono); font-size: 0.6875rem;">
            Environnement de laboratoire — Données fictives uniquement · build <?= e(appBuild()) ?>
        </p>
    </div>
</footer>

<script src="<?= asset('/js/app.js') ?>"></script>
<script src="<?= asset('/js/effects.js') ?>"></script>
<?php foreach (($extraJs ?? []) as $js): ?>
<script src="<?= asset($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
