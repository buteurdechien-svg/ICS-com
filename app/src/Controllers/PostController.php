<?php
/**
 * Création de post
 * VULN INTENTIONNELLE : le contenu n'est pas assaini (XSS stocké possible)
 */
$db = getDB();
$userId = getCurrentUserId();

$content = $_POST['content'] ?? '';
$visibility = $_POST['visibility'] ?? 'members';

if (empty(trim($content))) {
    $_SESSION['flash_message'] = 'Le contenu ne peut pas être vide.';
    header('Location: /feed');
    exit;
}

// VULN : pas d'échappement du contenu — XSS stocké
$stmt = $db->prepare("INSERT INTO posts (user_id, content, visibility) VALUES (?, ?, ?)");
$stmt->execute([$userId, $content, $visibility]);

// Mettre à jour le compteur
$postId = $db->lastInsertId();
logAudit($userId, 'create_post', 'post', $postId, ['visibility' => $visibility]);

$_SESSION['flash_message'] = 'Publication créée avec succès !';
header('Location: /feed');
exit;
