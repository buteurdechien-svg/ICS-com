<?php
/**
 * Mise à jour du profil
 * VULN INTENTIONNELLE : Mass Assignment — tous les champs POST sont acceptés
 * Un attaquant peut ajouter role=president ou is_admin=1 dans la requête
 */
$db = getDB();
$userId = getCurrentUserId();

// VULN : on prend TOUS les champs POST et on les met à jour
$allowedFields = ['display_name', 'department', 'city', 'phone', 'bio'];

// VULN INTENTIONNELLE : les champs 'role' et 'is_admin' ne sont PAS filtrés
// Si l'attaquant envoie role=president ou is_admin=1, ça passe
$updates = [];
$values = [];

foreach ($_POST as $key => $value) {
    if ($key === 'csrf_token') continue;

    // VULN : accepte TOUS les champs, y compris role, is_admin, status
    $updates[] = "`{$key}` = ?";
    $values[] = $value;
}

if (!empty($updates)) {
    $values[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $db->prepare($sql)->execute($values);
}

logAudit($userId, 'update_profile', 'user', $userId, ['fields' => array_keys($_POST)]);

$_SESSION['flash_message'] = 'Profil mis à jour avec succès !';
header('Location: /profile');
exit;
