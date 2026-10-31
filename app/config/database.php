<?php
/**
 * ICS Community — Connexion base de données
 * VULNÉRABILITÉ INTENTIONNELLE : certaines requêtes n'utilisent PAS
 * les requêtes préparées (scénarios SQL injection).
 */

/**
 * Charge le fichier .env (format KEY=VALUE, pas de guillemets imbriqués)
 */
function loadEnv(): void {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    // Chercher .env dans le répertoire parent de public/
    $envPaths = [
        __DIR__ . '/../.env',           // /var/www/ics-community/.env
        __DIR__ . '/../../.env',         // fallback racine projet (Docker)
    ];

    foreach ($envPaths as $path) {
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                if (strpos($line, '=') === false) continue;
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, " \t\n\r\0\x0B\"'");
                if (!getenv($key)) {
                    putenv("{$key}={$val}");
                }
            }
            break;
        }
    }
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        loadEnv();

        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $db   = getenv('MYSQL_DATABASE') ?: 'ics_community';
        $user = getenv('MYSQL_USER') ?: 'ics_user';
        $pass = getenv('MYSQL_PASSWORD') ?: '';

        $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
