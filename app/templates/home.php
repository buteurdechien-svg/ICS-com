<?php
$pageTitle = 'Accueil';
include __DIR__ . '/partials/header.php';
$db = getDB();

$memberCount = $db->query("SELECT COUNT(*) FROM users WHERE status = 'actif'")->fetchColumn();
$postCount = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$donationTotal = $db->query("SELECT setting_value FROM donation_settings WHERE setting_key = 'total_collected'")->fetchColumn();

// Derniers posts publics
$recentPosts = $db->query("SELECT p.*, u.display_name, u.role, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.visibility = 'public' ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

// Dernières news
$recentNews = $db->query("SELECT * FROM cyber_news ORDER BY published_at DESC LIMIT 3")->fetchAll();
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="hero-content animate-in">
            <h1>La communauté cybersécurité de Côte d'Ivoire</h1>
            <p>Ivoire Cyber Shield rassemble les passionnés de cybersécurité pour apprendre, partager et protéger le cyberespace ivoirien.</p>
            <div>
                <?php if (!isLoggedIn()): ?>
                    <a href="/register" class="btn btn-primary btn-lg">Rejoindre la communauté</a>
                    <a href="/login" class="btn btn-secondary btn-lg" style="margin-left: 12px;">Se connecter</a>
                <?php else: ?>
                    <a href="/feed" class="btn btn-primary btn-lg">Voir le feed</a>
                <?php endif; ?>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= $memberCount ?></div>
                    <div class="hero-stat-label">Membres actifs</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= $postCount ?></div>
                    <div class="hero-stat-label">Publications</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?= formatCurrency((int)$donationTotal) ?></div>
                    <div class="hero-stat-label">Fonds collectés</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container page-content">
    <div class="two-col">
        <!-- Dernières publications -->
        <div>
            <h2 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: var(--space-lg);">Dernières publications</h2>
            <?php foreach ($recentPosts as $post): ?>
                <div class="post-card animate-in">
                    <div class="post-header">
                        <div class="post-avatar"><?= getInitials($post['display_name']) ?></div>
                        <div class="post-meta">
                            <div class="post-author">
                                <a href="/profile/<?= $post['user_id'] ?>"><?= e($post['display_name']) ?></a>
                                <span class="post-badge badge-<?= $post['role'] ?>"><?= $post['role'] ?></span>
                            </div>
                            <div class="post-time"><?= timeAgo($post['created_at']) ?></div>
                        </div>
                    </div>
                    <div class="post-content"><?= nl2br(e(mb_substr($post['content'], 0, 300))) ?><?= mb_strlen($post['content']) > 300 ? '...' : '' ?></div>
                    <div class="post-actions">
                        <span class="post-action">❤ <?= $post['likes_count'] ?></span>
                        <span class="post-action">💬 <?= $post['comments_count'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar -->
        <aside>
            <!-- Cyber News -->
            <div class="sidebar-widget">
                <h3>Cyber News</h3>
                <?php foreach ($recentNews as $news): ?>
                    <div class="card" style="padding: var(--space-md); margin-bottom: var(--space-sm);">
                        <div class="news-title"><?= e($news['title']) ?></div>
                        <div class="news-meta"><?= timeAgo($news['published_at']) ?> · <?= e($news['category']) ?></div>
                    </div>
                <?php endforeach; ?>
                <a href="/news" class="btn btn-sm btn-secondary mt-md" style="width: 100%; justify-content: center;">Toutes les news</a>
            </div>

            <!-- Dons -->
            <div class="sidebar-widget">
                <h3>Fonds de formation</h3>
                <?php
                    $goal = (int)$db->query("SELECT setting_value FROM donation_settings WHERE setting_key = 'goal_amount'")->fetchColumn();
                    $collected = (int)$donationTotal;
                    $pct = min(100, round($collected / $goal * 100));
                ?>
                <div class="donation-amount"><?= formatCurrency($collected) ?></div>
                <div class="text-muted" style="font-size: 0.8125rem;">sur <?= formatCurrency($goal) ?></div>
                <div class="donation-progress">
                    <div class="donation-progress-bar" style="width: <?= $pct ?>%;"></div>
                </div>
                <a href="/donations" class="btn btn-sm btn-iris mt-sm" style="width: 100%; justify-content: center;">Contribuer</a>
            </div>

            <!-- Membres -->
            <div class="sidebar-widget">
                <h3>Équipe dirigeante</h3>
                <?php
                    $leaders = $db->query("SELECT * FROM users WHERE role IN ('president','administrateur','moderateur') AND status = 'actif' ORDER BY FIELD(role, 'president','administrateur','moderateur') LIMIT 6")->fetchAll();
                    foreach ($leaders as $leader):
                ?>
                    <a href="/profile/<?= $leader['id'] ?>" class="member-list-item" style="text-decoration: none;">
                        <div class="member-list-avatar"><?= getInitials($leader['display_name']) ?></div>
                        <div>
                            <div class="member-list-name"><?= e($leader['display_name']) ?></div>
                            <div class="member-list-role"><?= e($leader['department'] ?? $leader['role']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <a href="/members" class="btn btn-sm btn-secondary mt-md" style="width: 100%; justify-content: center;">Tous les membres</a>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
