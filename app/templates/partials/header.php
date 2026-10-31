<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'ICS Community') ?> — ICS Community</title>
    <link rel="stylesheet" href="<?= asset('/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('/css/effects.css') ?>">
    <?php foreach (($extraCss ?? []) as $css): ?>
    <link rel="stylesheet" href="<?= asset($css) ?>">
    <?php endforeach; ?>
</head>
<body>

<!-- Bannière laboratoire -->
<div class="lab-banner">
    ⚠ LABORATOIRE DE FORMATION — DONNÉES FICTIVES — NE PAS EXPOSER SUR INTERNET ⚠
</div>

<!-- Navigation -->
<nav class="nav">
    <div class="nav-inner">
        <a href="/" class="nav-brand">
            <img src="<?= asset('/img/logo-ics.png') ?>" alt="Ivoire Cyber Shield" class="nav-logo">
            <span class="nav-brand-text">ICS Community</span>
        </a>

        <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')" aria-label="Menu">☰</button>

        <ul class="nav-links">
            <li><a href="/" class="<?= ($route ?? '') === '' ? 'active' : '' ?>">Accueil</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="/feed" class="<?= ($route ?? '') === 'feed' ? 'active' : '' ?>">Feed</a></li>
                <li><a href="/messages" class="<?= ($route ?? '') === 'messages' ? 'active' : '' ?>">Messages</a></li>
            <?php endif; ?>
            <li><a href="/members" class="<?= ($route ?? '') === 'members' ? 'active' : '' ?>">Membres</a></li>
            <li><a href="/news" class="<?= ($route ?? '') === 'news' ? 'active' : '' ?>">Cyber News</a></li>
            <li><a href="/donations" class="<?= ($route ?? '') === 'donations' ? 'active' : '' ?>">Dons</a></li>
        </ul>

        <div class="nav-user">
            <?php if (isLoggedIn()):
                $currentUser = getCurrentUser();
            ?>
                <?php if (in_array($currentUser['role'], ['administrateur', 'president'])): ?>
                    <a href="/admin" class="btn btn-sm btn-secondary">Admin</a>
                <?php endif; ?>
                <a href="/profile" class="nav-avatar" title="<?= e($currentUser['display_name']) ?>">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?= e($currentUser['avatar_url']) ?>" alt="<?= e($currentUser['display_name']) ?>" class="avatar-img">
                    <?php else: ?>
                        <?= getInitials($currentUser['display_name']) ?>
                    <?php endif; ?>
                </a>
                <a href="/logout" class="btn btn-sm btn-secondary">Déconnexion</a>
            <?php else: ?>
                <a href="/login" class="btn btn-sm btn-secondary">Connexion</a>
                <a href="/register" class="btn btn-sm btn-primary">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
