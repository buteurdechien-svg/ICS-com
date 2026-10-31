<?php
/**
 * Export des données utilisateur
 * VULN INTENTIONNELLE : Vulnérabilités chaînées
 * 1. IDOR/BOLA : pas de vérification que l'utilisateur exporte SES propres données
 * 2. Information Disclosure : expose le hash du mot de passe, les tokens de session
 * 3. Peut être combiné avec le mass assignment pour exporter après escalade
 */

header('Content-Type: application/json');
$db = getDB();

$targetUserId = $m[1]; // ID extrait de l'URL

// VULN INTENTIONNELLE : pas de vérification que $targetUserId == getCurrentUserId()
// N'importe quel utilisateur connecté peut exporter les données de n'importe qui

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$targetUserId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'Utilisateur introuvable']);
    exit;
}

// VULN : expose TOUT y compris password_hash
$posts = $db->prepare("SELECT * FROM posts WHERE user_id = ?");
$posts->execute([$targetUserId]);

$messages = $db->prepare("SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ?");
$messages->execute([$targetUserId, $targetUserId]);

$donations = $db->prepare("SELECT * FROM donations WHERE user_id = ?");
$donations->execute([$targetUserId]);

$sessions = $db->prepare("SELECT * FROM sessions WHERE user_id = ?");
$sessions->execute([$targetUserId]);

echo json_encode([
    'export_date' => date('Y-m-d H:i:s'),
    'user' => $user, // Inclut password_hash, is_admin, etc.
    'posts' => $posts->fetchAll(),
    'messages' => $messages->fetchAll(), // Messages privés d'autres utilisateurs
    'donations' => $donations->fetchAll(),
    'active_sessions' => $sessions->fetchAll(), // Tokens de session actifs
], JSON_PRETTY_PRINT);
