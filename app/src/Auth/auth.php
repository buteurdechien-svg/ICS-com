<?php
/**
 * ICS Community — Gestion de l'authentification
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function getCurrentUserId(): ?int {
    startSecureSession();
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    $user = getCurrentUser();
    if (!$user || !in_array($user['role'], $roles)) {
        http_response_code(403);
        include __DIR__ . '/../../templates/403.php';
        exit;
    }
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Inscription d'un nouvel utilisateur.
 * Le compte est créé "en_attente" et sera activé automatiquement après 30 secondes.
 */
function registerUser(string $username, string $email, string $password, string $displayName): array {
    $db = getDB();

    // Vérifier si l'email existe déjà
    // VULN INTENTIONNELLE : message d'erreur différent si email existe (account enumeration)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Cette adresse email est déjà associée à un compte.'];
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => "Ce nom d'utilisateur est déjà pris."];
    }

    $hash = hashPassword($password);

    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, display_name, role, status, created_at) VALUES (?, ?, ?, ?, 'membre', 'en_attente', NOW())");
    $stmt->execute([$username, $email, $hash, $displayName]);
    $userId = $db->lastInsertId();

    // Envoi d'email de bienvenue via Mailpit
    sendWelcomeEmail($email, $displayName);

    // Log
    logAudit($userId, 'register', 'user', $userId);

    return ['success' => true, 'user_id' => $userId];
}

/**
 * Connexion utilisateur
 * VULN INTENTIONNELLE : pas de protection brute force suffisante
 */
function loginUser(string $email, string $password): array {
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // VULN : message différent si l'email n'existe pas
        return ['success' => false, 'error' => "Aucun compte trouvé avec cet email."];
    }

    if ($user['status'] === 'en_attente') {
        // Vérifier si 30 secondes se sont écoulées
        $created = new DateTime($user['created_at']);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $created->getTimestamp();

        if ($diff >= AUTO_ACTIVATION_DELAY) {
            // Activer automatiquement
            $stmt2 = $db->prepare("UPDATE users SET status = 'actif', activated_at = NOW() WHERE id = ?");
            $stmt2->execute([$user['id']]);
            $user['status'] = 'actif';
        } else {
            $remaining = AUTO_ACTIVATION_DELAY - $diff;
            return ['success' => false, 'error' => "Votre compte est en cours d'activation. Réessayez dans {$remaining} secondes."];
        }
    }

    if ($user['status'] === 'suspendu') {
        return ['success' => false, 'error' => 'Votre compte a été suspendu. Contactez un administrateur.'];
    }

    if (!verifyPassword($password, $user['password_hash'])) {
        // VULN : pas de verrouillage après N tentatives
        $db->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?")->execute([$user['id']]);
        return ['success' => false, 'error' => 'Mot de passe incorrect.'];
    }

    // Connexion réussie
    startSecureSession();
    // VULN INTENTIONNELLE : pas de regeneration de session_id (fixation de session)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['logged_in'] = true;
    $_SESSION['role'] = $user['role'];
    $_SESSION['display_name'] = $user['display_name'];

    // Reset login attempts
    $db->prepare("UPDATE users SET login_attempts = 0, last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    logAudit($user['id'], 'login', 'user', $user['id']);

    return ['success' => true, 'user' => $user];
}

function logoutUser(): void {
    startSecureSession();
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        logAudit($userId, 'logout', 'user', $userId);
    }
    session_destroy();
    header('Location: /');
    exit;
}

function logAudit(int $userId, string $action, ?string $targetType = null, ?int $targetId = null, ?array $details = null): void {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, target_type, target_id, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $targetType,
            $targetId,
            $details ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    } catch (Exception $e) {
        // Ne pas bloquer l'app si le log échoue
    }
}

function sendWelcomeEmail(string $to, string $name): void {
    try {
        $subject = "Bienvenue sur ICS Community, {$name} !";
        $body = "Bonjour {$name},\n\nVotre inscription sur ICS Community a bien été enregistrée.\n"
              . "Votre compte sera automatiquement activé dans " . AUTO_ACTIVATION_DELAY . " secondes.\n\n"
              . "Bienvenue dans la communauté Ivoire Cyber Shield !\n\n"
              . "— L'équipe ICS";

        $headers = "From: " . SMTP_FROM . "\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n";

        // Envoi via SMTP Mailpit
        $socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 5);
        if ($socket) {
            fgets($socket);
            fwrite($socket, "EHLO ics-community\r\n"); fgets($socket);
            fwrite($socket, "MAIL FROM:<" . SMTP_FROM . ">\r\n"); fgets($socket);
            fwrite($socket, "RCPT TO:<{$to}>\r\n"); fgets($socket);
            fwrite($socket, "DATA\r\n"); fgets($socket);
            fwrite($socket, "Subject: {$subject}\r\n{$headers}\r\n{$body}\r\n.\r\n"); fgets($socket);
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
        }
    } catch (Exception $e) {
        // Mailpit optionnel
    }
}

function getInitials(string $name): string {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    }
    return strtoupper(mb_substr($name, 0, 2));
}

function timeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return "il y a {$diff->y} an" . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0) return "il y a {$diff->m} mois";
    if ($diff->d > 0) return "il y a {$diff->d} jour" . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0) return "il y a {$diff->h}h";
    if ($diff->i > 0) return "il y a {$diff->i} min";
    return "à l'instant";
}

function formatCurrency(int $amount): string {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Génère un token CSRF
 * VULN INTENTIONNELLE : le token est trop prévisible sur certains formulaires
 */
function csrfToken(): string {
    startSecureSession();
    if (!isset($_SESSION['csrf_token'])) {
        // VULN : utilisation de md5(time()) au lieu de random_bytes
        $_SESSION['csrf_token'] = md5(time() . session_id());
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): bool {
    startSecureSession();
    $token = $_POST['csrf_token'] ?? '';
    return $token === ($_SESSION['csrf_token'] ?? '');
}
