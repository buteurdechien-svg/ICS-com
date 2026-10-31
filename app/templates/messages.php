<?php
$pageTitle = 'Messages';
include __DIR__ . '/partials/header.php';
$db = getDB();
$userId = getCurrentUserId();

// Liste des conversations
$conversations = $db->prepare("
    SELECT u.id, u.display_name, u.role, u.avatar_url,
           (SELECT content FROM messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) as last_time,
           (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread
    FROM users u
    WHERE u.id != ? AND u.id IN (
        SELECT DISTINCT sender_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT DISTINCT receiver_id FROM messages WHERE sender_id = ?
    )
    ORDER BY last_time DESC
");
$conversations->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$convos = $conversations->fetchAll();

// Conversation active
$activeChat = $_GET['to'] ?? ($convos[0]['id'] ?? null);
$chatMessages = [];
$chatUser = null;

if ($activeChat) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$activeChat]);
    $chatUser = $stmt->fetch();

    if ($chatUser) {
        // VULN INTENTIONNELLE : IDOR — pas de vérification que l'utilisateur a le droit de lire ces messages
        $msgs = $db->prepare("
            SELECT m.*, u.display_name as sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $msgs->execute([$userId, $activeChat, $activeChat, $userId]);
        $chatMessages = $msgs->fetchAll();

        // Marquer comme lu
        $db->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")->execute([$activeChat, $userId]);
    }
}
?>

<div class="container page-content">
    <h2 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: var(--space-lg);">Messagerie</h2>

    <div class="messages-layout">
        <!-- Sidebar contacts -->
        <div class="messages-sidebar">
            <?php foreach ($convos as $convo): ?>
                <a href="/messages?to=<?= $convo['id'] ?>" class="messages-contact <?= $activeChat == $convo['id'] ? 'active' : '' ?>" style="text-decoration: none; color: inherit;">
                    <div class="member-list-avatar">
                        <?php if (!empty($convo['avatar_url'])): ?>
                            <img src="<?= e($convo['avatar_url']) ?>" alt="" class="avatar-img">
                        <?php else: ?>
                            <?= getInitials($convo['display_name']) ?>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="flex flex-between flex-center">
                            <div class="member-list-name"><?= e($convo['display_name']) ?></div>
                            <?php if ($convo['unread'] > 0): ?>
                                <span class="notif-badge"><?= $convo['unread'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= e(mb_substr($convo['last_message'] ?? '', 0, 40)) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($convos)): ?>
                <div class="text-center text-muted" style="padding: var(--space-xl);">
                    Aucune conversation
                </div>
            <?php endif; ?>
        </div>

        <!-- Chat -->
        <div class="messages-chat">
            <?php if ($chatUser): ?>
                <div class="messages-chat-header">
                    <div class="flex flex-center gap-sm">
                        <div class="member-list-avatar">
                            <?php if (!empty($chatUser['avatar_url'])): ?>
                                <img src="<?= e($chatUser['avatar_url']) ?>" alt="" class="avatar-img">
                            <?php else: ?>
                                <?= getInitials($chatUser['display_name']) ?>
                            <?php endif; ?>
                        </div>
                        <?= e($chatUser['display_name']) ?>
                        <span class="post-badge badge-<?= $chatUser['role'] ?>"><?= $chatUser['role'] ?></span>
                    </div>
                </div>
                <div class="messages-chat-body" id="chat-body">
                    <?php foreach ($chatMessages as $msg): ?>
                        <div class="message-bubble <?= $msg['sender_id'] == $userId ? 'message-sent' : 'message-received' ?>">
                            <?= e($msg['content']) ?>
                            <div style="font-size: 0.625rem; opacity: 0.7; margin-top: 4px;"><?= timeAgo($msg['created_at']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form class="messages-input" method="POST" action="/api/messages">
                    <?= csrfField() ?>
                    <input type="hidden" name="receiver_id" value="<?= $chatUser['id'] ?>">
                    <input type="text" name="content" class="form-input" placeholder="Écrire un message..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            <?php else: ?>
                <div class="text-center text-muted" style="padding: var(--space-3xl);">
                    Sélectionnez une conversation ou démarrez-en une depuis le profil d'un membre.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Scroll to bottom of chat
const chatBody = document.getElementById('chat-body');
if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
