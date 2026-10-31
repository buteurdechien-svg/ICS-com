<?php
$pageTitle = 'Profil';
$extraCss = ['/css/discs.css'];
$extraJs = ['/js/discs.js'];
include __DIR__ . '/partials/header.php';
$db = getDB();

$profileId = $m[1] ?? getCurrentUserId();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profileId]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    echo '<div class="container page-content"><div class="alert alert-error">Utilisateur introuvable.</div></div>';
    include __DIR__ . '/partials/footer.php';
    return;
}

$isOwnProfile = isLoggedIn() && getCurrentUserId() == $profile['id'];
$flashMsg = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

// Posts de cet utilisateur
$posts = $db->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$posts->execute([$profileId]);
$userPosts = $posts->fetchAll();
$postCount = count($userPosts);
?>

<div class="container page-content">

    <?php if ($flashMsg): ?>
        <div class="alert alert-success"><?= e($flashMsg) ?></div>
    <?php endif; ?>

    <!-- En-tête profil -->
    <div class="profile-header animate-in">
        <div class="profile-top">
            <div class="profile-planet" role="img" aria-label="Planète de <?= e($profile['display_name']) ?>">
                <div class="planet">
                    <div class="planet-atmo"></div>
                    <div class="planet-surface"<?php if (!empty($profile['avatar_url'])): ?> style="background-image: url('<?= e($profile['avatar_url']) ?>')"<?php endif; ?>>
                        <?php if (empty($profile['avatar_url'])): ?>
                            <div class="planet-initials"><?= e(getInitials($profile['display_name'])) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="planet-shade"></div>
                    <div class="planet-label">
                        <strong><?= e($profile['username']) ?></strong>
                        <span>ICS · <?= e(strtoupper($profile['role'])) ?></span>
                    </div>
                </div>
            </div>
            <div class="profile-info">
                <h1>
                    <span class="fx-name"><?= e($profile['display_name']) ?></span>
                    <span class="post-badge badge-<?= $profile['role'] ?>"><?= $profile['role'] ?></span>
                </h1>
                <div class="profile-department">
                    <?= e($profile['department'] ?? '') ?>
                    <?php if ($profile['city']): ?>
                        · <?= e($profile['city']) ?>
                    <?php endif; ?>
                </div>
                <?php if ($profile['bio']): ?>
                    <div class="profile-bio"><?= e($profile['bio']) ?></div>
                <?php endif; ?>
                <div class="mt-md text-muted" style="font-size: 0.8125rem;">
                    Membre depuis <?= ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'][(int)date('n', strtotime($profile['created_at'])) - 1] . ' ' . date('Y', strtotime($profile['created_at'])) ?>
                    · <?= $postCount ?> publication<?= $postCount > 1 ? 's' : '' ?>
                </div>
            </div>
            <?php if ($isOwnProfile): ?>
                <div style="margin-left: auto;">
                    <a href="#edit-profile" class="btn btn-secondary" onclick="document.getElementById('edit-form').classList.toggle('hidden')">Modifier</a>
                </div>
            <?php elseif (isLoggedIn()): ?>
                <div style="margin-left: auto;">
                    <a href="/messages?to=<?= $profile['id'] ?>" class="btn btn-primary">Envoyer un message</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isOwnProfile): ?>
        <!-- VULN INTENTIONNELLE : mass assignment — tous les champs POST sont mis à jour -->
        <div id="edit-form" class="hidden mt-lg" style="border-top: 1px solid var(--border-default); padding-top: var(--space-lg);">
            <form method="POST" action="/api/profile">
                <?= csrfField() ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="display_name" class="form-input" value="<?= e($profile['display_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Département</label>
                        <input type="text" name="department" class="form-input" value="<?= e($profile['department'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ville</label>
                        <input type="text" name="city" class="form-input" value="<?= e($profile['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input" value="<?= e($profile['phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-textarea" rows="3"><?= e($profile['bio'] ?? '') ?></textarea>
                </div>
                <!-- VULN : champs cachés modifiables (mass assignment / privilege escalation) -->
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Publications du profil -->
    <h2 style="font-family: var(--font-heading); font-size: 1.125rem; margin: var(--space-xl) 0 var(--space-lg);">Publications</h2>
    <?php if (empty($userPosts)): ?>
        <div class="card text-center text-muted" style="padding: var(--space-2xl);">Aucune publication pour le moment.</div>
    <?php endif; ?>
    <?php foreach ($userPosts as $post): ?>
        <div class="post-card animate-in">
            <div class="post-header">
                <div class="post-avatar">
                    <?php if (!empty($profile['avatar_url'])): ?>
                        <img src="<?= e($profile['avatar_url']) ?>" alt="" class="avatar-img">
                    <?php else: ?>
                        <?= getInitials($profile['display_name']) ?>
                    <?php endif; ?>
                </div>
                <div class="post-meta">
                    <div class="post-author"><?= e($profile['display_name']) ?></div>
                    <div class="post-time"><?= timeAgo($post['created_at']) ?> · <?= e($post['visibility']) ?></div>
                </div>
            </div>
            <div class="post-content"><?= nl2br(e($post['content'])) ?></div>
            <div class="post-actions">
                <span class="post-action">❤ <?= $post['likes_count'] ?></span>
                <span class="post-action">💬 <?= $post['comments_count'] ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
