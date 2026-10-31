#!/usr/bin/env bash
#
# ICS Community — Script d'installation pour Ubuntu Server 22.04 LTS
# LABORATOIRE DE FORMATION — DONNÉES FICTIVES — NE PAS EXPOSER SUR INTERNET
#
# Usage : sudo bash install.sh
#

set -euo pipefail

# ── Couleurs ─────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m'

banner() {
    echo -e "\n${CYAN}╔══════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC}   ${GREEN}ICS Community — Installation Ubuntu 22.04${NC}     ${CYAN}║${NC}"
    echo -e "${CYAN}║${NC}   ${RED}LABORATOIRE DE FORMATION — DONNÉES FICTIVES${NC}   ${CYAN}║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════╝${NC}\n"
}

info()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
error() { echo -e "${RED}[✗]${NC} $1"; exit 1; }
step()  { echo -e "\n${CYAN}──── $1 ────${NC}"; }

# ── Vérifications ────────────────────────────────────────────────
banner

[[ $EUID -ne 0 ]] && error "Ce script doit être exécuté en tant que root (sudo bash install.sh)"

if ! grep -q "22.04" /etc/os-release 2>/dev/null; then
    warn "Ce script est prévu pour Ubuntu 22.04 LTS. Autre version détectée."
    read -rp "Continuer quand même ? (o/N) " confirm
    [[ "$confirm" != "o" && "$confirm" != "O" ]] && exit 0
fi

# ── Variables ────────────────────────────────────────────────────
INSTALL_DIR="/var/www/ics-community"
DB_NAME="ics_community"
DB_USER="ics_user"
DB_PASS="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 24)"
DB_ROOT_PASS="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 24)"
APP_SECRET="$(openssl rand -hex 32)"
UPLOAD_DIR="/var/www/ics-storage/uploads"
MAILPIT_VERSION="v1.21.0"

# ── 1. Mise à jour système ───────────────────────────────────────
step "1/7 — Mise à jour du système"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq
info "Système à jour"

# ── 2. Installation des paquets ──────────────────────────────────
step "2/7 — Installation Apache + PHP 8.2 + MySQL 8"

# Ajouter le PPA PHP si nécessaire
if ! command -v php8.2 &>/dev/null && ! dpkg -l php8.2 &>/dev/null; then
    apt-get install -y -qq software-properties-common
    add-apt-repository -y ppa:ondrej/php
    apt-get update -qq
fi

apt-get install -y -qq \
    apache2 \
    php8.2 \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-gd \
    php8.2-zip \
    libapache2-mod-php8.2 \
    mysql-server \
    unzip \
    curl \
    git

info "Apache, PHP 8.2 et MySQL installés"

# ── 3. Configuration MySQL ───────────────────────────────────────
step "3/7 — Configuration de la base de données"

# Sécuriser MySQL
systemctl start mysql
systemctl enable mysql

# Créer la base et l'utilisateur
mysql -u root <<EOSQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASS}';
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOSQL

info "Base de données '${DB_NAME}' créée"
info "Utilisateur '${DB_USER}' configuré"

# ── 4. Déploiement de l'application ─────────────────────────────
step "4/7 — Déploiement de l'application"

# Copier les fichiers
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
rm -rf "${INSTALL_DIR}"
mkdir -p "${INSTALL_DIR}"
cp -r "${SCRIPT_DIR}/app/"* "${INSTALL_DIR}/"
cp -f "${SCRIPT_DIR}/reset-lab.sh" "${INSTALL_DIR}/reset-lab.sh"
cp -r "${SCRIPT_DIR}/database" "${INSTALL_DIR}/database"
chmod +x "${INSTALL_DIR}/reset-lab.sh"

# Créer le répertoire d'uploads (hors webroot)
mkdir -p "${UPLOAD_DIR}"
chown -R www-data:www-data "${UPLOAD_DIR}"
chmod 755 "${UPLOAD_DIR}"

# Droits
chown -R www-data:www-data "${INSTALL_DIR}"
chmod -R 755 "${INSTALL_DIR}"

# Créer le fichier .env
cat > "${INSTALL_DIR}/.env" <<ENVFILE
# ICS Community — Configuration (généré par install.sh)
MYSQL_HOST=localhost
MYSQL_DATABASE=${DB_NAME}
MYSQL_USER=${DB_USER}
MYSQL_PASSWORD=${DB_PASS}

APP_NAME="ICS Community"
APP_URL=http://localhost
APP_ENV=lab
APP_DEBUG=true
APP_SECRET=${APP_SECRET}

SMTP_HOST=127.0.0.1
SMTP_PORT=1025
SMTP_FROM=noreply@ics-community.lab

UPLOAD_MAX_SIZE=5242880
UPLOAD_DIR=${UPLOAD_DIR}
ENVFILE

chown www-data:www-data "${INSTALL_DIR}/.env"
chmod 600 "${INSTALL_DIR}/.env"

info "Application déployée dans ${INSTALL_DIR}"

# Importer le schéma et les données
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SCRIPT_DIR}/database/schema.sql"
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SCRIPT_DIR}/database/seed.sql"
info "Schéma et données seed importés"

# ── 5. Configuration Apache ──────────────────────────────────────
step "5/7 — Configuration Apache"

a2enmod rewrite php8.2
a2dissite 000-default.conf 2>/dev/null || true

cat > /etc/apache2/sites-available/ics-community.conf <<'VHOST'
<VirtualHost *:80>
    ServerName ics-community.lab
    ServerAlias localhost

    DocumentRoot /var/www/ics-community/public

    <Directory /var/www/ics-community/public>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # Empêcher l'accès aux fichiers sensibles
    <FilesMatch "\.(env|sql|md|sh)$">
        Require all denied
    </FilesMatch>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/ics-community-error.log
    CustomLog ${APACHE_LOG_DIR}/ics-community-access.log combined

    # PHP settings
    php_value upload_max_filesize 5M
    php_value post_max_size 6M
    php_value max_execution_time 30
    php_value session.cookie_httponly 1
</VirtualHost>
VHOST

a2ensite ics-community.conf
systemctl restart apache2
systemctl enable apache2

info "Apache configuré — site accessible sur http://localhost"

# ── 6. Installation Mailpit ──────────────────────────────────────
step "6/7 — Installation Mailpit (capture email)"

if ! command -v mailpit &>/dev/null; then
    ARCH=$(dpkg --print-architecture)
    case "$ARCH" in
        amd64) MP_ARCH="linux-amd64" ;;
        arm64) MP_ARCH="linux-arm64" ;;
        *) warn "Architecture ${ARCH} non supportée pour Mailpit, skip"; MP_ARCH="" ;;
    esac

    if [[ -n "$MP_ARCH" ]]; then
        curl -fsSL "https://github.com/axllent/mailpit/releases/download/${MAILPIT_VERSION}/mailpit-${MP_ARCH}.tar.gz" \
            -o /tmp/mailpit.tar.gz
        tar -xzf /tmp/mailpit.tar.gz -C /usr/local/bin/ mailpit
        chmod +x /usr/local/bin/mailpit
        rm -f /tmp/mailpit.tar.gz

        # Service systemd
        cat > /etc/systemd/system/mailpit.service <<'MPSERVICE'
[Unit]
Description=Mailpit — SMTP testing for ICS Community
After=network.target

[Service]
Type=simple
ExecStart=/usr/local/bin/mailpit --smtp 0.0.0.0:1025 --listen 0.0.0.0:8025
Restart=always
User=www-data

[Install]
WantedBy=multi-user.target
MPSERVICE

        systemctl daemon-reload
        systemctl enable mailpit
        systemctl start mailpit
        info "Mailpit installé — interface web sur http://localhost:8025"
    fi
else
    info "Mailpit déjà installé"
fi

# ── 7. CVE Simulation (optionnel, conteneur isolé) ───────────────
step "7/7 — Simulation CVE PHP 2026"

# Le scénario CVE reste dans un conteneur isolé pour la sécurité
if command -v docker &>/dev/null; then
    info "Docker détecté — construction du conteneur CVE isolé"
    cd "${SCRIPT_DIR}/cve-simulation"
    docker build -t ics-cve-lab . 2>/dev/null
    docker rm -f ics-cve-lab 2>/dev/null || true
    docker run -d --name ics-cve-lab \
        --restart unless-stopped \
        --network none \
        -p 8081:80 \
        ics-cve-lab
    info "CVE Lab accessible sur http://localhost:8081"
else
    warn "Docker non installé — le scénario CVE (n°15) ne sera pas disponible."
    warn "Pour l'activer plus tard : apt install docker.io && bash install.sh"
fi

# ── Résumé ───────────────────────────────────────────────────────
echo ""
echo -e "${CYAN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║${NC}           ${GREEN}Installation terminée !${NC}                 ${CYAN}║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${GREEN}Application :${NC}  http://localhost"
echo -e "  ${GREEN}Mailpit :${NC}      http://localhost:8025"
echo -e "  ${GREEN}CVE Lab :${NC}      http://localhost:8081 (si Docker)"
echo ""
echo -e "  ${YELLOW}Credentials :${NC}  sauvegardés dans ${GREEN}credentials.txt${NC} (chmod 600)"
echo ""
echo -e "  ${RED}⚠  LABORATOIRE DE FORMATION — NE PAS EXPOSER SUR INTERNET${NC}"
echo ""

# Sauvegarder les credentials
CRED_FILE="${SCRIPT_DIR}/credentials.txt"
cat > "${CRED_FILE}" <<CREDS
# ICS Community — Credentials (généré le $(date))
# NE PAS COMMITER CE FICHIER

MySQL Root:     root / ${DB_ROOT_PASS}
MySQL App:      ${DB_USER} / ${DB_PASS}
App Secret:     ${APP_SECRET}
Install Dir:    ${INSTALL_DIR}
Upload Dir:     ${UPLOAD_DIR}

Comptes membres: chaque compte a un mot de passe unique (voir email credentials)
CREDS

chmod 600 "${CRED_FILE}"
info "Credentials sauvegardés dans ${CRED_FILE}"
echo ""
