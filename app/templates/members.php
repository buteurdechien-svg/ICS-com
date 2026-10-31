<?php
$pageTitle = 'Membres';
$extraCss = ['/css/discs.css'];
$extraJs = ['/js/discs.js'];
include __DIR__ . '/partials/header.php';
$db = getDB();

$members = $db->query("SELECT * FROM users WHERE status = 'actif' ORDER BY FIELD(role, 'president','administrateur','moderateur','membre') ASC, created_at ASC")->fetchAll();
$total = count($members);
?>

<section class="discs-page" data-discs aria-label="Membres ICS">
    <header class="discs-info">
        <div>
            <h1 class="discs-title" data-disc-title aria-live="polite"><?= $total ? e($members[0]['display_name']) : 'Membres ICS' ?></h1>
            <dl class="discs-meta">
                <div><dt>PSEUDO</dt><dd data-disc-field="username"></dd></div>
                <div><dt>RÔLE</dt><dd data-disc-field="role"></dd></div>
                <div><dt>DÉPARTEMENT</dt><dd data-disc-field="department"></dd></div>
                <div><dt>VILLE</dt><dd data-disc-field="city"></dd></div>
                <div><dt>MEMBRE DEPUIS</dt><dd data-disc-field="since"></dd></div>
            </dl>
        </div>
        <div class="discs-counter"><b data-disc-index>01</b>/ <?= str_pad((string)$total, 2, '0', STR_PAD_LEFT) ?></div>
    </header>

    <div class="discs-stage" data-disc-stage>
        <svg class="disc-scribble" viewBox="0 0 400 340" aria-hidden="true">
            <path d="M210 28 C 310 22, 392 92, 384 178 C 376 268, 288 322, 196 318 C 98 314, 14 254, 18 164 C 22 80, 104 24, 214 26 C 300 30, 360 74, 372 120"/>
        </svg>
        <div class="discs-track">
            <?php foreach ($members as $i => $member):
                $since = (int)date('n', strtotime($member['created_at']));
                $mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'][$since - 1] . ' ' . date('Y', strtotime($member['created_at']));
            ?>
            <a class="disc-slot" href="/profile/<?= (int)$member['id'] ?>" data-disc
               data-name="<?= e($member['display_name']) ?>"
               data-username="@<?= e($member['username']) ?>"
               data-role="<?= e($member['role']) ?>"
               data-department="<?= e($member['department'] ?? '—') ?>"
               data-city="<?= e($member['city'] ?: '—') ?>"
               data-since="<?= e($mois) ?>"
               aria-label="Voir le profil de <?= e($member['display_name']) ?>">
                <div class="planet">
                    <div class="planet-atmo"></div>
                    <div class="planet-surface"<?php if (!empty($member['avatar_url'])): ?> style="background-image: url('<?= e($member['avatar_url']) ?>')"<?php endif; ?>>
                        <?php if (empty($member['avatar_url'])): ?>
                            <div class="planet-initials"><?= e(getInitials($member['display_name'])) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="planet-shade"></div>
                    <div class="planet-dim"></div>
                    <div class="planet-label">
                        <strong><?= e($member['username']) ?></strong>
                        <span>ICS · <?= e(strtoupper($member['role'])) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="disc-cta" aria-hidden="true">CLIQUER POUR OUVRIR LE PROFIL</div>
    </div>

    <nav class="discs-strip" aria-label="Index des membres">
        <button type="button" class="discs-arrow" data-disc-prev aria-label="Membre précédent">←</button>
        <?php foreach ($members as $i => $member): ?>
            <button type="button" class="discs-thumb" data-disc-thumb="<?= $i ?>" aria-label="<?= e($member['display_name']) ?>">
                <?php if (!empty($member['avatar_url'])): ?>
                    <img src="<?= e($member['avatar_url']) ?>" alt="">
                <?php else: ?>
                    <?= e(getInitials($member['display_name'])) ?>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
        <button type="button" class="discs-arrow" data-disc-next aria-label="Membre suivant">→</button>
    </nav>
    <p class="discs-hint">Molette, glisser ou flèches du clavier pour parcourir les planètes.</p>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
