<?php
/**
 * ICS Community — Configuration générale
 */

// Charger le .env (si pas déjà fait)
require_once __DIR__ . '/database.php';
loadEnv();

define('APP_NAME', getenv('APP_NAME') ?: 'ICS Community');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'lab');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('APP_SECRET', getenv('APP_SECRET') ?: 'change_me');

define('SMTP_HOST', getenv('SMTP_HOST') ?: '127.0.0.1');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 1025));
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@ics-community.lab');

define('UPLOAD_MAX_SIZE', (int)(getenv('UPLOAD_MAX_SIZE') ?: 5242880));
define('UPLOAD_DIR', getenv('UPLOAD_DIR') ?: '/var/www/storage/uploads');

// Rôles utilisateur
define('ROLE_VISITEUR', 'visiteur');
define('ROLE_MEMBRE', 'membre');
define('ROLE_MODERATEUR', 'moderateur');
define('ROLE_ADMIN', 'administrateur');
define('ROLE_PRESIDENT', 'president');

// Statuts de compte
define('STATUS_EN_ATTENTE', 'en_attente');
define('STATUS_ACTIF', 'actif');
define('STATUS_SUSPENDU', 'suspendu');

// Donation — montant initial simulé
define('DONATION_INITIAL_TOTAL', 23987485);

// Activation automatique après 30 secondes
define('AUTO_ACTIVATION_DELAY', 30);

/**
 * URL d'un fichier statique avec version (évite le cache navigateur après mise à jour)
 */
function asset(string $path): string {
    $file = __DIR__ . '/../public' . $path;
    $v = is_file($file) ? filemtime($file) : time();
    return $path . '?v=' . $v;
}

/**
 * Version déployée (écrite par install.sh / update.sh)
 */
function appBuild(): string {
    $file = __DIR__ . '/../VERSION';
    return is_file($file) ? trim((string)file_get_contents($file)) : 'dev';
}
