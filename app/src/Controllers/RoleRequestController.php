<?php
/**
 * Demande de changement de rôle
 * VULN INTENTIONNELLE : Privilege Escalation
 * Le rôle demandé est appliqué directement sans validation côté serveur
 */
$db = getDB();
$userId = getCurrentUserId();

$requestedRole = $_POST['role'] ?? '';
$reason = $_POST['reason'] ?? '';

$validRoles = ['membre', 'moderateur', 'administrateur', 'president'];

if (!in_array($requestedRole, $validRoles)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Rôle invalide']);
    exit;
}

// VULN INTENTIONNELLE : le rôle est appliqué directement
// Pas de workflow d'approbation, pas de vérification des droits
// Un simple membre peut se promouvoir administrateur ou président
$stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$requestedRole, $userId]);

// Si le rôle est admin/president, activer aussi is_admin
if (in_array($requestedRole, ['administrateur', 'president'])) {
    $stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->execute([$userId]);
}

logAudit($userId, 'role_change', 'user', $userId, [
    'new_role' => $requestedRole,
    'reason' => $reason,
]);

// Mettre à jour la session
$_SESSION['user_role'] = $requestedRole;

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => "Rôle mis à jour : {$requestedRole}",
    'new_role' => $requestedRole,
]);
