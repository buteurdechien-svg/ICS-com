<?php
$pageTitle = 'Dons';
include __DIR__ . '/partials/header.php';
$db = getDB();

$totalCollected = (int)$db->query("SELECT setting_value FROM donation_settings WHERE setting_key = 'total_collected'")->fetchColumn();
$goalAmount = (int)$db->query("SELECT setting_value FROM donation_settings WHERE setting_key = 'goal_amount'")->fetchColumn();
$goalLabel = $db->query("SELECT setting_value FROM donation_settings WHERE setting_key = 'goal_label'")->fetchColumn();
$pct = min(100, round($totalCollected / $goalAmount * 100));

// Derniers dons
$donations = $db->query("SELECT d.*, u.display_name as user_display FROM donations d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC LIMIT 10")->fetchAll();

$flashMsg = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>

<div class="container page-content">
    <div class="two-col">
        <div>
            <h2 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: var(--space-lg);">Soutenir ICS</h2>

            <?php if ($flashMsg): ?>
                <div class="alert alert-success"><?= e($flashMsg) ?></div>
            <?php endif; ?>

            <!-- Progression -->
            <div class="card mb-lg animate-in">
                <div class="text-muted" style="font-size: 0.875rem;"><?= e($goalLabel) ?></div>
                <div class="donation-amount mt-sm"><?= formatCurrency($totalCollected) ?></div>
                <div class="text-muted" style="font-size: 0.8125rem;">collectés sur <?= formatCurrency($goalAmount) ?> (<?= $pct ?>%)</div>
                <div class="donation-progress">
                    <div class="donation-progress-bar" style="width: <?= $pct ?>%;"></div>
                </div>
            </div>

            <!-- Formulaire de don -->
            <div class="card animate-in">
                <h3 class="card-title" style="margin-bottom: var(--space-lg);">Faire un don</h3>
                <form method="POST" action="/api/donations" id="donation-form">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label">Votre nom</label>
                        <input type="text" name="donor_name" class="form-input" required
                               value="<?= isLoggedIn() ? e(getCurrentUser()['display_name']) : '' ?>"
                               placeholder="Nom du donateur">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Montant (FCFA)</label>
                        <!-- VULN INTENTIONNELLE : le montant est validé côté client uniquement -->
                        <input type="number" name="amount" class="form-input" required min="100"
                               placeholder="Ex: 10000" id="donation-amount">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Moyen de paiement</label>
                        <div class="donation-grid">
                            <label class="donation-method" onclick="selectMethod(this, 'wave')">
                                <div style="font-size: 1.5rem;">📱</div>
                                <div class="donation-method-name">Wave</div>
                                <input type="radio" name="payment_method" value="wave" class="hidden" required>
                            </label>
                            <label class="donation-method" onclick="selectMethod(this, 'orange_money')">
                                <div style="font-size: 1.5rem;">📱</div>
                                <div class="donation-method-name">Orange Money</div>
                                <input type="radio" name="payment_method" value="orange_money" class="hidden">
                            </label>
                            <label class="donation-method" onclick="selectMethod(this, 'moov_money')">
                                <div style="font-size: 1.5rem;">📱</div>
                                <div class="donation-method-name">Moov Money</div>
                                <input type="radio" name="payment_method" value="moov_money" class="hidden">
                            </label>
                            <label class="donation-method" onclick="selectMethod(this, 'binance_btc')">
                                <div style="font-size: 1.5rem;">₿</div>
                                <div class="donation-method-name">Binance BTC</div>
                                <input type="radio" name="payment_method" value="binance_btc" class="hidden">
                            </label>
                            <label class="donation-method" onclick="selectMethod(this, 'visa')">
                                <div style="font-size: 1.5rem;">💳</div>
                                <div class="donation-method-name">VISA</div>
                                <input type="radio" name="payment_method" value="visa" class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message (optionnel)</label>
                        <textarea name="message" class="form-textarea" rows="2" placeholder="Un mot d'encouragement..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-iris btn-lg" style="width: 100%; justify-content: center;">
                        Confirmer le don
                    </button>
                </form>
            </div>
        </div>

        <!-- Historique des dons -->
        <aside>
            <div class="sidebar-widget">
                <h3>Derniers dons</h3>
                <?php foreach ($donations as $don): ?>
                    <div style="padding: var(--space-sm) 0; border-bottom: 1px solid var(--border-muted);">
                        <div class="flex flex-between flex-center">
                            <span style="font-weight: 500; font-size: 0.875rem;"><?= e($don['donor_name']) ?></span>
                            <span class="text-phosphor" style="font-family: var(--font-heading); font-weight: 600; font-size: 0.875rem;">
                                <?= formatCurrency($don['amount']) ?>
                            </span>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <?= e($don['payment_method']) ?> · <?= timeAgo($don['created_at']) ?>
                        </div>
                        <?php if ($don['message']): ?>
                            <div style="font-size: 0.8125rem; color: var(--text-secondary); margin-top: 2px; font-style: italic;">
                                "<?= e(mb_substr($don['message'], 0, 80)) ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sidebar-widget">
                <h3>Moyens de paiement acceptés</h3>
                <p class="text-muted" style="font-size: 0.8125rem; line-height: 1.6;">
                    Wave, Orange Money, Moov Money, Binance (BTC), et VISA.
                </p>
            </div>
        </aside>
    </div>
</div>

<script>
function selectMethod(el, method) {
    document.querySelectorAll('.donation-method').forEach(m => m.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
