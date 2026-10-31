<?php
/**
 * Récupération d'URL distante (proxy interne)
 * VULN INTENTIONNELLE : SSRF — Server-Side Request Forgery
 * L'utilisateur peut spécifier n'importe quelle URL, y compris des adresses internes
 * (http://localhost, http://127.0.0.1, http://db:3306, file:///etc/passwd)
 */

header('Content-Type: application/json');

$url = $_GET['url'] ?? '';

if (empty($url)) {
    echo json_encode(['error' => 'Paramètre url requis']);
    exit;
}

// VULN INTENTIONNELLE : aucune validation de l'URL
// Pas de blocage des adresses internes, pas de whitelist de domaines
// Permet d'accéder à des services internes Docker (db, mailpit)

$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'follow_location' => 1,
        'max_redirects' => 3,
        'user_agent' => 'ICS-Community/1.0',
    ],
    'ssl' => [
        'verify_peer' => false, // VULN : pas de vérification SSL
    ],
]);

$content = @file_get_contents($url, false, $context);

if ($content === false) {
    echo json_encode(['error' => 'Impossible de récupérer l\'URL', 'url' => $url]);
} else {
    // Retourne le contenu brut — permet l'exfiltration de données internes
    echo json_encode([
        'success' => true,
        'url' => $url,
        'length' => strlen($content),
        'content' => mb_substr($content, 0, 10000), // Limite à 10k pour l'affichage
        'headers' => $http_response_header ?? [],
    ]);
}
