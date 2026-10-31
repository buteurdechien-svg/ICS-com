<?php
$pageTitle = 'Feed';
include __DIR__ . '/partials/header.php';
$db = getDB();
$currentUser = getCurrentUser();

// Récupérer les posts
$posts = $db->query("
    SELECT p.*, u.display_name, u.role, u.username, u.department
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.visibility IN ('public', 'members')
    ORDER BY p.created_at DESC
    LIMIT 50
")->fetchAll();

$flashMsg = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>

<div class="container page-content">
    <div class="two-col">
        <div>
            <h2 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: var(--space-lg);">Fil d'actualité</h2>

            <?php if ($flashMsg): ?>
                <div class="alert alert-success"><?= e($flashMsg) ?></div>
            <?php endif; ?>

            <!-- Nouveau post -->
            <div class="card mb-lg">
                <form method="POST" action="/api/posts">
                    <?= csrfField() ?>
                    <div class="form-group" style="margin-bottom: var(--space-md);">
                        <textarea name="content" class="form-textarea" placeholder="Quoi de neuf dans le monde cyber ?" rows="3" required></textarea>
                    </div>
                    <div class="flex flex-between flex-center">
                        <select name="visibility" class="form-select" style="width: auto;">
                            <option value="public">Public</option>
                            <option value="members" selected>Membres</option>
                            <option value="private">Privé</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Publier</button>
                    </div>
                </form>
            </div>

            <!-- Posts -->
            <?php foreach ($posts as $post): ?>
                <div class="post-card animate-in">
                    <div class="post-header">
                        <a href="/profile/<?= $post['user_id'] ?>" class="post-avatar"><?= getInitials($post['display_name']) ?></a>
                        <div class="post-meta">
                            <div class="post-author">
                                <a href="/profile/<?= $post['user_id'] ?>"><?= e($post['display_name']) ?></a>
                                <span class="post-badge badge-<?= $post['role'] ?>"><?= $post['role'] ?></span>
                            </div>
                            <div class="post-time"><?= timeAgo($post['created_at']) ?> · <?= e($post['visibility']) ?></div>
                        </div>
                    </div>
                    <!-- VULN INTENTIONNELLE : XSS stocké — le contenu n'est PAS échappé ici -->
                    <div class="post-content"><?= nl2br($post['content']) ?></div>
                    <div class="post-actions">
                        <button class="post-action" onclick="likePost(<?= $post['id'] ?>, this)">
                            ❤ <span><?= $post['likes_count'] ?></span>
                        </button>
                        <button class="post-action" onclick="toggleComments(<?= $post['id'] ?>)">
                            💬 <span><?= $post['comments_count'] ?></span>
                        </button>
                    </div>
                    <!-- Commentaires -->
                    <div id="comments-<?= $post['id'] ?>" class="hidden mt-md">
                        <?php
                            $comments = $db->prepare("SELECT c.*, u.display_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                            $comments->execute([$post['id']]);
                            foreach ($comments->fetchAll() as $comment):
                        ?>
                            <div class="comment">
                                <div class="comment-avatar"><?= getInitials($comment['display_name']) ?></div>
                                <div class="comment-body">
                                    <div class="comment-author"><?= e($comment['display_name']) ?></div>
                                    <div class="comment-text"><?= e($comment['content']) ?></div>
                                    <div class="comment-time"><?= timeAgo($comment['created_at']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <form onsubmit="postComment(event, <?= $post['id'] ?>)" class="mt-md flex gap-sm">
                            <input type="text" class="form-input" placeholder="Écrire un commentaire..." id="comment-input-<?= $post['id'] ?>" style="flex:1;">
                            <button type="submit" class="btn btn-sm btn-primary">Envoyer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar -->
        <aside>
            <div class="sidebar-widget">
                <h3>Mon profil</h3>
                <div class="member-list-item">
                    <div class="member-list-avatar" style="width: 48px; height: 48px; font-size: 1rem;"><?= getInitials($currentUser['display_name']) ?></div>
                    <div>
                        <div class="member-list-name" style="font-size: 1rem;"><?= e($currentUser['display_name']) ?></div>
                        <div class="member-list-role"><?= e($currentUser['department'] ?? $currentUser['role']) ?></div>
                    </div>
                </div>
                <a href="/profile" class="btn btn-sm btn-secondary mt-md" style="width: 100%; justify-content: center;">Voir mon profil</a>
            </div>

            <div class="sidebar-widget">
                <h3>Recherche</h3>
                <input type="text" class="form-input" id="search-input" placeholder="Rechercher un membre..."
                       oninput="searchUsers(this.value)">
                <div id="search-results" class="mt-sm"></div>
            </div>

            <div class="sidebar-widget">
                <h3>Membres en ligne</h3>
                <?php
                    $onlineMembers = $db->query("SELECT * FROM users WHERE status = 'actif' ORDER BY last_login DESC LIMIT 5")->fetchAll();
                    foreach ($onlineMembers as $m):
                ?>
                    <a href="/profile/<?= $m['id'] ?>" class="member-list-item" style="text-decoration: none;">
                        <div class="member-list-avatar"><?= getInitials($m['display_name']) ?></div>
                        <div>
                            <div class="member-list-name"><?= e($m['display_name']) ?></div>
                            <div class="member-list-role"><?= e($m['role']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
