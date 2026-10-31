<?php
/**
 * ICS Community — Configuration générale
 */

define('APP_NAME', getenv('APP_NAME') ?: 'ICS Community');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080');
define('APP_ENV', getenv('APP_ENV') ?: 'lab');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('APP_SECRET', getenv('APP_SECRET') ?: 'change_me');

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'mailpit');
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
