#!/usr/bin/env bash
#
# ICS Community — Réinitialisation du laboratoire
# Remet la base de données à l'état initial (seed)
#
# Usage : sudo bash reset-lab.sh
#

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m'

[[ $EUID -ne 0 ]] && { echo -e "${RED}[✗] Exécuter en root : sudo bash reset-lab.sh${NC}"; exit 1; }

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
INSTALL_DIR="/var/www/ics-community"

echo -e "${CYAN}──── Réinitialisation ICS Community ────${NC}"

# Lire les credentials depuis le .env
if [[ -f "${INSTALL_DIR}/.env" ]]; then
    DB_NAME=$(grep MYSQL_DATABASE "${INSTALL_DIR}/.env" | cut -d= -f2)
    DB_USER=$(grep MYSQL_USER "${INSTALL_DIR}/.env" | cut -d= -f2)
    DB_PASS=$(grep MYSQL_PASSWORD "${INSTALL_DIR}/.env" | cut -d= -f2)
else
    echo -e "${RED}[✗] Fichier .env introuvable dans ${INSTALL_DIR}${NC}"
    exit 1
fi

echo -e "${GREEN}[1/3]${NC} Suppression des tables existantes..."
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
SET FOREIGN_KEY_CHECKS = 0;
SET @tables = NULL;
SELECT GROUP_CONCAT(table_name) INTO @tables
FROM information_schema.tables
WHERE table_schema = '${DB_NAME}';
SET @tables = IFNULL(CONCAT('DROP TABLE IF EXISTS ', @tables), 'SELECT 1');
PREPARE stmt FROM @tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET FOREIGN_KEY_CHECKS = 1;
"

echo -e "${GREEN}[2/3]${NC} Import du schéma..."
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SCRIPT_DIR}/database/schema.sql"

echo -e "${GREEN}[3/3]${NC} Import des données seed..."
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SCRIPT_DIR}/database/seed.sql"

# Nettoyer les uploads
rm -rf /var/www/ics-storage/uploads/*

# Nettoyer les sessions PHP
rm -f /var/lib/php/sessions/sess_* 2>/dev/null || true

echo ""
echo -e "${GREEN}[✓] Laboratoire réinitialisé avec succès !${NC}"
echo -e "    Credentials : voir le fichier credentials envoyé par mail"
echo ""
