<?php
$pageTitle = 'Membres';
include __DIR__ . '/partials/header.php';
$db = getDB();

$members = $db->query("SELECT * FROM users WHERE status = 'actif' ORDER BY FIELD(role, 'president','administrateur','moderateur','membre') ASC, created_at ASC")->fetchAll();
?>

<div class="container page-content">
    <h2 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: var(--space-lg);">Membres ICS</h2>
    <p class="text-muted mb-lg"><?= count($members) ?> membres actifs</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-md);">
        <?php foreach ($members as $member): ?>
            <a href="/profile/<?= $member['id'] ?>" class="card animate-in" style="text-decoration: none; color: inherit;">
                <div class="flex flex-center gap-md">
                    <div class="post-avatar" style="width: 56px; height: 56px; font-size: 1.125rem;">
                        <?php if (!empty($member['avatar_url'])): ?>
                            <img src="<?= e($member['avatar_url']) ?>" alt="<?= e($member['display_name']) ?>" class="avatar-img">
                        <?php else: ?>
                            <?= getInitials($member['display_name']) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?= e($member['display_name']) ?></div>
                        <div class="text-muted" style="font-size: 0.8125rem;">
                            <?= e($member['department'] ?? '') ?>
                        </div>
                        <span class="post-badge badge-<?= $member['role'] ?>" style="margin-top: 4px;">
                            <?= $member['role'] ?>
                        </span>
                    </div>
                </div>
                <?php if ($member['bio']): ?>
                    <div class="text-muted mt-md" style="font-size: 0.8125rem; line-height: 1.5;">
                        <?= e(mb_substr($member['bio'], 0, 120)) ?><?= mb_strlen($member['bio']) > 120 ? '...' : '' ?>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
