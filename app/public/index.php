<?php
/**
 * ICS Community — Routeur principal
 * LABORATOIRE DE FORMATION — DONNÉES FICTIVES
 */

require_once __DIR__ . '/../src/Auth/auth.php';

startSecureSession();

// Activer les comptes en attente qui ont dépassé 30 secondes
try {
    $db = getDB();
    $db->exec("UPDATE users SET status = 'actif', activated_at = NOW() WHERE status = 'en_attente' AND TIMESTAMPDIFF(SECOND, created_at, NOW()) >= " . AUTO_ACTIVATION_DELAY);
} catch (Exception $e) {}

$route = $_GET['route'] ?? '';
$route = trim($route, '/');
$method = $_SERVER['REQUEST_METHOD'];

// CORS — ouvert pour permettre aux applications tierces d'appeler l'API ICS.
if (str_starts_with($route, 'api/')) {
    header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($method === 'OPTIONS') { http_response_code(204); exit; }
}

// Routing
switch (true) {
    // Redirection de partage — utilisée pour les liens externes et le suivi.
    case $route === 'go':
        $to = $_GET['url'] ?? $_GET['next'] ?? '/';
        header('Location: ' . $to, true, 302);
        break;

    case $route === '' || $route === 'home':
        include __DIR__ . '/../templates/home.php';
        break;

    case $route === 'login':
        include __DIR__ . '/../templates/login.php';
        break;

    case $route === 'register':
        include __DIR__ . '/../templates/register.php';
        break;

    case $route === 'logout':
        logoutUser();
        break;

    case $route === 'feed':
        requireLogin();
        include __DIR__ . '/../templates/feed.php';
        break;

    case preg_match('#^profile/(\d+)$#', $route, $m) === 1:
        include __DIR__ . '/../templates/profile.php';
        break;

    case $route === 'profile':
        requireLogin();
        $m = [1 => getCurrentUserId()];
        include __DIR__ . '/../templates/profile.php';
        break;

    case $route === 'messages':
        requireLogin();
        include __DIR__ . '/../templates/messages.php';
        break;

    case $route === 'donations':
        include __DIR__ . '/../templates/donations.php';
        break;

    case $route === 'news':
        include __DIR__ . '/../templates/news.php';
        break;

    case $route === 'members':
        include __DIR__ . '/../templates/members.php';
        break;

    // ---- API endpoints ----

    case $route === 'api/posts' && $method === 'POST':
        requireLogin();
        include __DIR__ . '/../src/Controllers/PostController.php';
        break;

    case $route === 'api/posts/like' && $method === 'POST':
        requireLogin();
        include __DIR__ . '/../src/Controllers/LikeController.php';
        break;

    case $route === 'api/comments' && $method === 'POST':
        requireLogin();
        include __DIR__ . '/../src/Controllers/CommentController.php';
        break;

    case $route === 'api/messages' && $method === 'POST':
        requireLogin();
        include __DIR__ . '/../src/Controllers/MessageController.php';
        break;

    case $route === 'api/donations' && $method === 'POST':
        include __DIR__ . '/../src/Controllers/DonationController.php';
        break;

    case $route === 'api/profile' && $method === 'POST':
        requireLogin();
        include __DIR__ . '/../src/Controllers/ProfileController.php';
        break;

    // ---- VULN: API user info (IDOR) ----
    case preg_match('#^api/users/(\d+)$#', $route, $m) === 1:
        // VULN INTENTIONNELLE : pas de contrôle d'accès, retourne les infos de n'importe quel user
        header('Content-Type: application/json');
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email, display_name, role, status, department, bio, phone, city, is_admin, created_at, last_login FROM users WHERE id = ?");
        $stmt->execute([$m[1]]);
        $user = $stmt->fetch();
        echo json_encode($user ?: ['error' => 'User not found']);
        break;

    // ---- VULN: Search with SQL injection ----
    case $route === 'api/search':
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        $db = getDB();
        // VULN INTENTIONNELLE : concaténation directe dans la requête SQL
        $results = $db->query("SELECT id, username, display_name, department FROM users WHERE display_name LIKE '%{$q}%' OR username LIKE '%{$q}%'")->fetchAll();
        echo json_encode($results);
        break;

    // ---- VULN: SSRF — fetch remote avatar/news ----
    case $route === 'api/fetch-url':
        requireLogin();
        include __DIR__ . '/../src/Controllers/FetchUrlController.php';
        break;

    // ---- VULN: Privilege escalation — role change request ----
    case $route === 'api/request-role' && $method === 'POST':
        requireLogin();
        include __DIR__ . '/../src/Controllers/RoleRequestController.php';
        break;

    // ---- VULN: Export user data (chained — IDOR + info disclosure) ----
    case preg_match('#^api/export/(\d+)$#', $route, $m) === 1:
        requireLogin();
        include __DIR__ . '/../src/Controllers/ExportController.php';
        break;

    // ---- VULN: Session debug endpoint (info disclosure) ----
    case $route === 'api/debug/session':
        header('Content-Type: application/json');
        // VULN INTENTIONNELLE : expose les données de session y compris le token CSRF
        echo json_encode([
            'session_id' => session_id(),
            'session_data' => $_SESSION,
            'server' => [
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? '',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            ],
            'cookies' => $_COOKIE,
        ]);
        break;

    // ---- Admin ----
    case $route === 'admin':
    case $route === 'admin/dashboard':
        // VULN INTENTIONNELLE : contrôle d'accès basé uniquement sur le rôle en session
        // (pas de vérification du champ is_admin en base)
        requireLogin();
        include __DIR__ . '/../templates/admin/dashboard.php';
        break;

    case $route === 'admin/users':
        requireLogin();
        include __DIR__ . '/../templates/admin/users.php';
        break;

    case $route === 'admin/logs':
        requireLogin();
        include __DIR__ . '/../templates/admin/logs.php';
        break;

    default:
        http_response_code(404);
        include __DIR__ . '/../templates/404.php';
        break;
}
