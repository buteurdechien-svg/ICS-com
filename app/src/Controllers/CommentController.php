<?php
/**
 * Ajout d'un commentaire
 */
header('Content-Type: application/json');
$db = getDB();
$userId = getCurrentUserId();

$data = json_decode(file_get_contents('php://input'), true);
$postId = (int)($data['post_id'] ?? 0);
$content = trim($data['content'] ?? '');

if (!$postId || empty($content)) {
    echo json_encode(['error' => 'Post ID et contenu requis']);
    exit;
}

$stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$postId, $userId, $content]);

$db->prepare("UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?")->execute([$postId]);

$user = getCurrentUser();
echo json_encode([
    'success' => true,
    'comment' => [
        'display_name' => $user['display_name'],
        'content' => htmlspecialchars($content),
        'time' => "à l'instant",
    ]
]);
