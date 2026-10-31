<?php
/**
 * Envoi d'un message privé
 */
$db = getDB();
$userId = getCurrentUserId();

$receiverId = (int)($_POST['receiver_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$receiverId || empty($content)) {
    header('Location: /messages');
    exit;
}

// VULN INTENTIONNELLE : pas de vérification CSRF sur cette route
$stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->execute([$userId, $receiverId, $content]);

logAudit($userId, 'send_message', 'message', $db->lastInsertId());

header("Location: /messages?to={$receiverId}");
exit;
