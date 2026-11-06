# ICS Community

> **LABORATOIRE DE FORMATION — DONNÉES FICTIVES — NE PAS EXPOSER SUR INTERNET**

Plateforme communautaire volontairement vulnérable pour la formation au pentest web. Développée par [Ivoire Cyber Shield](https://ivoirecybershield.ci).

## Méthodologie — pentest web 100 % assisté par IA (sans Burp Suite)

Ce laboratoire s'audite **sans proxy d'interception**, avec trois outils :
- **le navigateur** (code source, DevTools : onglets *Réseau*, *Console*, *Cookies*, et modification d'un formulaire avant envoi) ;
- **`curl`** (rejouer/forger des requêtes : en-têtes, `-I` pour les redirections et le CORS, cookies, méthodes) ;
- **une IA** à qui l'on fait lire une page, un fichier JS ou une réponse HTTP pour repérer la faille et rédiger la preuve.

Les failles vont du **plus simple** (secrets oubliés dans le code source, page de debug, sauvegarde `.bak` servie en clair, CORS ouvert, redirection ouverte) au **plus avancé** (contrôle d'accès, mass-assignment, élévation de rôle, SSRF, chaînage). Plusieurs imitent les erreurs typiques des sites **générés à l'IA par des « vibe-codeurs »**. Rien n'est affiché (pas de flag, pas de niveau) : le livrable est le **rapport d'audit**. Le corrigé détaillé est réservé au formateur et **n'est pas publié** dans ce dépôt.

## Stack technique

- **Serveur :** Ubuntu Server 22.04 LTS
- **Backend :** PHP 8.2 + Apache 2
- **Base de données :** MySQL 8.0 (InnoDB)
- **Email :** Mailpit (capture SMTP locale)
- **CVE Lab :** Conteneur Docker isolé (optionnel, port 8081)

## Installation

### Prérequis

- Ubuntu Server 22.04 LTS (fresh install ou existant)
- Accès root (sudo)
- Connexion Internet (pour les paquets)
- Docker (optionnel, uniquement pour le scénario CVE n°15)

### Lancement

```bash
# Cloner le projet
git clone https://github.com/buteurdechien-svg/ICS-com.git
cd ICS-com

# Lancer l'installation (génère automatiquement les mots de passe)
sudo bash install.sh
```

Le script installe et configure automatiquement :
- Apache 2 + PHP 8.2 + extensions (mysql, mbstring, xml, curl, gd, zip)
- MySQL 8.0 avec schéma et données seed
- Mailpit pour la capture des emails
- Le fichier `.env` avec des credentials aléatoires
- Le VirtualHost Apache

### Accès

| Service | URL | Description |
|---------|-----|-------------|
| Application | http://localhost | Plateforme communautaire ICS |
| Mailpit | http://localhost:8025 | Interface email de test |
| CVE Lab | http://localhost:8081 | Simulation CVE PHP 2026 (si Docker) |

### Comptes de test

Chaque compte a un mot de passe unique. Les mots de passe ne sont pas publiés dans le dépôt : ils sont fournis au formateur séparément.

| Pseudo | Email | Rôle |
|--------|-------|------|
| thesocvolkoff | volkoff@ics-lab.ci | president |
| massambacyberhack | massamba@ics-lab.ci | administrateur |
| camson | camson@ics-lab.ci | moderateur |
| mrshadow | shadow@ics-lab.ci | moderateur |
| legoatdeshacker | goathacker@ics-lab.ci | moderateur |
| victoriait | victoria@ics-lab.ci | moderateur |
| hassancamara | hassan@ics-lab.ci | membre |
| tehuayann | tehua@ics-lab.ci | membre |
| hackerman | hackerman@ics-lab.ci | membre |
| ahizimarc | ahizi@ics-lab.ci | membre |
| gnalyemmanuel | gnaly@ics-lab.ci | membre |
| dombamohamed | domba@ics-lab.ci | membre |
| neloumgrace | neloum@ics-lab.ci | membre |
| yohananfredo | yohanan@ics-lab.ci | membre |

### Mise à jour (après un nouveau push sur GitHub)

```bash
cd ~/ics-community                 # dossier où le dépôt a été cloné
sudo chown -R $USER: .             # répare les fichiers laissés par d'anciens « sudo git »
git pull origin main               # SANS sudo (git refuse « sudo git pull » : dubious ownership)
sudo bash update.sh                # redéploie le code, réinitialise la base, vérifie tout
```

Le numéro de version déployé s'affiche en bas de chaque page (« build xxxxxxx ») et doit correspondre à `git log --oneline -1`.
Dans le navigateur, recharger avec **Ctrl+Shift+R**.

### Réinitialisation

```bash
# Remettre la base de données à l'état initial
sudo bash reset-lab.sh
```

## Vulnérabilités

La plateforme contient **15 scénarios de vulnérabilités** intentionnelles, de sévérité Faible à Critique :

1. Information Disclosure
2. Account Enumeration
3. Brute Force Login
4. Broken Access Control
5. IDOR / BOLA
6. SQL Injection
7. Stored XSS
8. CSRF Faible
9. Session Management
10. Mass Assignment
11. Business Logic (Donations)
12. Privilege Escalation
13. SSRF
14. Vulnérabilités Chaînées
15. Simulation CVE PHP 2026

Voir **[VULNERABILITIES.md](VULNERABILITIES.md)** pour les détails complets d'exploitation.

## Architecture

```
ICS-com/
├── app/
│   ├── config/          # Configuration BDD et application
│   ├── public/          # DocumentRoot Apache
│   │   ├── css/         # Feuilles de style
│   │   ├── js/          # JavaScript client
│   │   └── index.php    # Routeur principal
│   ├── src/
│   │   ├── Auth/        # Authentification et helpers
│   │   └── Controllers/ # Contrôleurs API
│   └── templates/       # Vues PHP
│       ├── admin/       # Pages admin
│       └── partials/    # Header et footer
├── cve-simulation/      # Conteneur isolé CVE
├── database/
│   ├── schema.sql       # Schéma MySQL
│   └── seed.sql         # Données initiales
├── docker-compose.yml
├── Dockerfile
└── VULNERABILITIES.md   # Documentation des 15 vulnérabilités
```

## Fonctionnalités

- **Inscription avec activation automatique** (30 secondes, notification Mailpit)
- **Fil d'actualité** avec publications, likes et commentaires
- **Messagerie privée** entre membres
- **Dons simulés** (Wave, Orange Money, Moov Money, Binance BTC, VISA)
- **Cyber news** avec catégories colorées
- **Panneau d'administration** (dashboard, gestion utilisateurs, logs d'audit)
- **Annuaire des membres** avec rôles et départements

## Avertissements

- Cette plateforme est **volontairement vulnérable**
- **Ne jamais l'exposer sur Internet** ni sur un réseau public
- Toutes les données sont **fictives** (noms, emails, dons, messages)
- Les paiements sont **simulés** — aucune transaction réelle
- Le conteneur CVE Lab est **isolé** sans accès réseau externe

## Équipe ICS

- **Michael Ange AHIZÉ** — Président
- **Kevin KOUADIO** — Vice-président
- **Salimata TRAORE** — Secrétaire générale
- **Awa COULIBALY** — Trésorière
- **Fanta BAMBA** — Sécurité offensive
- **David KONAN** — Sécurité défensive
- **Nathalie KONE** — Événements
- **Jean DIALLO** — Formation
- **Emmanuel YAO** — Partenariats
- **Rachid OUATTARA** — Recherche
- **GNALY Emmanuel** — Communication
- **DOMBA Mohamed** — Secrétariat

## Licence

Usage exclusivement pédagogique — Ivoire Cyber Shield.
