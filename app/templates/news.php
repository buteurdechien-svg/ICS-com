<?php
$pageTitle = 'Cyber News';
include __DIR__ . '/partials/header.php';
$db = getDB();

$news = $db->query("SELECT n.*, u.display_name as author_name FROM cyber_news n LEFT JOIN users u ON n.author_id = u.id ORDER BY n.published_at DESC")->fetchAll();
?>

<div class="container page-content">
    <h2 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: var(--space-lg);">Actualités Cybersécurité</h2>

    <?php foreach ($news as $item): ?>
        <div class="card news-card animate-in">
            <div class="news-category <?= $item['category'] ?>"></div>
            <div>
                <div class="news-title"><?= e($item['title']) ?></div>
                <div class="news-excerpt"><?= e(mb_substr($item['content'], 0, 200)) ?>...</div>
                <div class="news-meta">
                    <?php if ($item['author_name']): ?>
                        Par <?= e($item['author_name']) ?> ·
                    <?php endif; ?>
                    <?= timeAgo($item['published_at']) ?>
                    · <span class="post-badge badge-membre"><?= e($item['category']) ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
