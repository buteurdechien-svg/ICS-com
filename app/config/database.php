<?php
/**
 * ICS Community — Connexion base de données
 * VULNÉRABILITÉ INTENTIONNELLE : certaines requêtes n'utilisent PAS
 * les requêtes préparées (scénarios SQL injection).
 */

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = getenv('MYSQL_HOST') ?: 'db';
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
