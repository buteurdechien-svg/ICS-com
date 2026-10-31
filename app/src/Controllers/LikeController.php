<?php
/**
 * Like/Unlike un post
 */
header('Content-Type: application/json');
$db = getDB();
$userId = getCurrentUserId();
$postId = (int)($_POST['post_id'] ?? 0);

if (!$postId) {
    echo json_encode(['error' => 'Post ID requis']);
    exit;
}

// Vérifier si déjà liké
$stmt = $db->prepare("SELECT id FROM reactions WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);

if ($stmt->fetch()) {
    $db->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?")->execute([$postId, $userId]);
    $db->prepare("UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?")->execute([$postId]);
    echo json_encode(['liked' => false]);
} else {
    $db->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, 'like')")->execute([$postId, $userId]);
    $db->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?")->execute([$postId]);
    echo json_encode(['liked' => true]);
}
