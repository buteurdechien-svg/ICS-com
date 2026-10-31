<?php
/**
 * Traitement d'un don simulé
 * VULN INTENTIONNELLE : pas de validation serveur du montant (business logic flaw)
 */
$db = getDB();

$donorName = trim($_POST['donor_name'] ?? '');
$amount = (int)($_POST['amount'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';
$message = trim($_POST['message'] ?? '');

$validMethods = ['wave', 'orange_money', 'moov_money', 'binance_btc', 'visa'];

if (empty($donorName) || empty($paymentMethod)) {
    $_SESSION['flash_message'] = 'Nom et méthode de paiement requis.';
    header('Location: /donations');
    exit;
}

if (!in_array($paymentMethod, $validMethods)) {
    $_SESSION['flash_message'] = 'Méthode de paiement invalide.';
    header('Location: /donations');
    exit;
}

// VULN INTENTIONNELLE : pas de validation du montant côté serveur
// Un montant négatif ou absurdement élevé sera accepté
$userId = isLoggedIn() ? getCurrentUserId() : null;
$transactionRef = strtoupper(substr($paymentMethod, 0, 4)) . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

$stmt = $db->prepare("INSERT INTO donations (user_id, donor_name, amount, payment_method, transaction_ref, status, message) VALUES (?, ?, ?, ?, ?, 'completed', ?)");
$stmt->execute([$userId, $donorName, $amount, $paymentMethod, $transactionRef, $message ?: null]);

// Mettre à jour le total
$db->exec("UPDATE donation_settings SET setting_value = setting_value + {$amount} WHERE setting_key = 'total_collected'");

if ($userId) {
    logAudit($userId, 'donation', 'donation', $db->lastInsertId(), ['amount' => $amount, 'method' => $paymentMethod]);
}

$_SESSION['flash_message'] = "Don de " . formatCurrency($amount) . " enregistré avec succès ! Réf: {$transactionRef}";
header('Location: /donations');
exit;
