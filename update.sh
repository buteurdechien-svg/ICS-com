#!/usr/bin/env bash
#
# ICS Community — Mise à jour en une commande
# LABORATOIRE DE FORMATION — DONNÉES FICTIVES — NE PAS EXPOSER SUR INTERNET
#
# Usage (depuis le dossier du dépôt cloné) :  sudo bash update.sh
#
#  1. récupère la dernière version du dépôt (git pull)
#  2. redéploie le code dans /var/www/ics-community (le .env est conservé)
#  3. réinitialise la base (schéma + comptes membres)
#  4. vérifie que tout est en place
#
# Tout est dans main() : bash lit la fonction entière avant de l'exécuter,
# donc le git pull peut modifier ce fichier sans casser l'exécution en cours.

main() {
    set -euo pipefail

    local RED='\033[0;31m' GREEN='\033[0;32m' CYAN='\033[0;36m' YELLOW='\033[1;33m' NC='\033[0m'
    ok()   { echo -e "${GREEN}[✓]${NC} $1"; }
    warn() { echo -e "${YELLOW}[!]${NC} $1"; }
    fail() { echo -e "${RED}[✗]${NC} $1"; exit 1; }
    step() { echo -e "\n${CYAN}──── $1 ────${NC}"; }

    [[ $EUID -ne 0 ]] && fail "Lancer avec sudo : sudo bash update.sh"

    local REPO_DIR INSTALL_DIR="/var/www/ics-community" OWNER
    REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    OWNER="$(stat -c %U "${REPO_DIR}")"

    [[ -f "${INSTALL_DIR}/.env" ]] || fail "Aucune installation trouvée dans ${INSTALL_DIR}. Lancez d'abord : sudo bash install.sh"

    # ── 1. Récupérer la dernière version ────────────────────────────
    if [[ "${1:-}" != "--no-pull" ]]; then
        step "1/4 — Récupération de la dernière version (git pull)"
        # git refuse « sudo git pull » sur un dossier appartenant à un autre utilisateur
        # (« dubious ownership ») : on exécute git en tant que propriétaire du dépôt.
        local BEFORE AFTER
        # remettre tout le dépôt au nom de son propriétaire (d'anciens « sudo git » laissent des fichiers root)
        chown -R "${OWNER}": "${REPO_DIR}"
        BEFORE="$(sudo -u "${OWNER}" git -C "${REPO_DIR}" rev-parse --short HEAD)"
        if ! sudo -u "${OWNER}" git -C "${REPO_DIR}" pull --ff-only origin main; then
            fail "git pull a échoué. Si vous avez modifié des fichiers du dépôt : cd ${REPO_DIR} && git stash && sudo bash update.sh"
        fi
        AFTER="$(sudo -u "${OWNER}" git -C "${REPO_DIR}" rev-parse --short HEAD)"
        if [[ "${BEFORE}" == "${AFTER}" ]]; then ok "Déjà à jour (${AFTER})"; else ok "Mis à jour : ${BEFORE} → ${AFTER}"; fi
        # relancer la version fraîchement téléchargée de ce script
        exec bash "${REPO_DIR}/update.sh" --no-pull
    fi

    local BUILD
    BUILD="$(sudo -u "${OWNER}" git -C "${REPO_DIR}" rev-parse --short HEAD)"

    # ── 2. Redéployer le code ───────────────────────────────────────
    step "2/4 — Déploiement du code (build ${BUILD})"
    find "${INSTALL_DIR}" -mindepth 1 -maxdepth 1 ! -name '.env' -exec rm -rf {} +
    cp -r "${REPO_DIR}/app/." "${INSTALL_DIR}/"
    cp -r "${REPO_DIR}/database" "${INSTALL_DIR}/database"
    cp -f "${REPO_DIR}/reset-lab.sh" "${INSTALL_DIR}/reset-lab.sh"
    echo "${BUILD} $(date '+%Y-%m-%d %H:%M')" > "${INSTALL_DIR}/VERSION"
    chown -R www-data:www-data "${INSTALL_DIR}"
    chmod -R 755 "${INSTALL_DIR}"
    chmod 600 "${INSTALL_DIR}/.env"
    ok "Code copié dans ${INSTALL_DIR}"

    # ── 3. Réinitialiser la base ────────────────────────────────────
    step "3/4 — Réinitialisation de la base de données"
    local DB_NAME DB_USER DB_PASS
    DB_NAME="$(grep '^MYSQL_DATABASE=' "${INSTALL_DIR}/.env" | cut -d= -f2-)"
    DB_USER="$(grep '^MYSQL_USER=' "${INSTALL_DIR}/.env" | cut -d= -f2-)"
    DB_PASS="$(grep '^MYSQL_PASSWORD=' "${INSTALL_DIR}/.env" | cut -d= -f2-)"
    export MYSQL_PWD="${DB_PASS}"   # évite d'exposer le mot de passe dans la ligne de commande

    if ! mysql -u "${DB_USER}" "${DB_NAME}" -e "SELECT 1" >/dev/null 2>&1; then
        fail "Connexion MySQL impossible avec les identifiants du .env. Réinstallez : sudo bash install.sh"
    fi

    mysql -u "${DB_USER}" "${DB_NAME}" <<EOSQL
SET FOREIGN_KEY_CHECKS = 0;
SET @tables = NULL;
SELECT GROUP_CONCAT('\`', table_name, '\`') INTO @tables FROM information_schema.tables WHERE table_schema = '${DB_NAME}';
SET @tables = IFNULL(CONCAT('DROP TABLE IF EXISTS ', @tables), 'SELECT 1');
PREPARE stmt FROM @tables; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET FOREIGN_KEY_CHECKS = 1;
EOSQL
    mysql -u "${DB_USER}" "${DB_NAME}" < "${REPO_DIR}/database/schema.sql"
    mysql -u "${DB_USER}" "${DB_NAME}" < "${REPO_DIR}/database/seed.sql"
    rm -rf /var/www/ics-storage/uploads/* 2>/dev/null || true
    rm -f /var/lib/php/sessions/sess_* 2>/dev/null || true
    ok "Base réinitialisée, sessions vidées (reconnectez-vous)"

    systemctl reload apache2 2>/dev/null || systemctl restart apache2 2>/dev/null || warn "Apache n'a pas pu être rechargé (sudo systemctl restart apache2)"

    # ── 4. Vérifications ────────────────────────────────────────────
    step "4/4 — Vérifications"
    local USERS EXPECTED_HASH DB_HASH ERRORS=0
    USERS="$(mysql -N -u "${DB_USER}" "${DB_NAME}" -e "SELECT COUNT(*) FROM users WHERE status='actif'")"
    [[ "${USERS}" == "14" ]] && ok "14 comptes membres actifs" || { warn "Comptes actifs : ${USERS} (attendu : 14)"; ERRORS=1; }

    # Le hash en base doit être celui du dépôt (prouve que les nouveaux mots de passe sont actifs)
    EXPECTED_HASH="$(grep -o "'volkoff@ics-lab.ci', '[^']*'" "${REPO_DIR}/database/seed.sql" | cut -d"'" -f4 || true)"
    DB_HASH="$(mysql -N -u "${DB_USER}" "${DB_NAME}" -e "SELECT password_hash FROM users WHERE email='volkoff@ics-lab.ci'")"
    [[ -n "${EXPECTED_HASH}" && "${EXPECTED_HASH}" == "${DB_HASH}" ]] && ok "Mots de passe uniques chargés" || { warn "Les mots de passe en base ne correspondent pas au dépôt"; ERRORS=1; }
    unset MYSQL_PWD

    local CODE
    for path in / /login /members /css/effects.css /css/discs.css /js/effects.js /js/discs.js /img/logo-ics.png; do
        CODE="$(curl -s -o /dev/null -w '%{http_code}' "http://localhost${path}")"
        if [[ "${CODE}" == "200" ]]; then ok "http://localhost${path} → 200"; else warn "http://localhost${path} → ${CODE}"; ERRORS=1; fi
    done
    local PAGE
    PAGE="$(curl -s http://localhost/members || true)"
    [[ "${PAGE}" == *data-disc-stage* ]] && ok "Page Membres : carrousel de planètes présent" || { warn "Carrousel de planètes absent de /members"; ERRORS=1; }
    PAGE="$(curl -s http://localhost/ || true)"
    [[ "${PAGE}" == *"build ${BUILD}"* ]] && ok "Le site affiche bien le build ${BUILD} (bas de page)" || { warn "Le bas de page n'affiche pas le build ${BUILD}"; ERRORS=1; }

    echo ""
    if [[ "${ERRORS}" == "0" ]]; then
        echo -e "${GREEN}Mise à jour terminée — build ${BUILD}${NC}"
        echo -e "Dans le navigateur : rechargez avec ${CYAN}Ctrl+Shift+R${NC} et vérifiez « build ${BUILD} » en bas de page."
    else
        echo -e "${YELLOW}Mise à jour terminée avec des avertissements (voir ci-dessus).${NC}"
    fi
    echo -e "${RED}⚠  LABORATOIRE DE FORMATION — NE PAS EXPOSER SUR INTERNET${NC}"
}

main "$@"
