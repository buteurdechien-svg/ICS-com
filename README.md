# ICS Community

> **LABORATOIRE DE FORMATION — DONNÉES FICTIVES — NE PAS EXPOSER SUR INTERNET**

Plateforme communautaire volontairement vulnérable pour la formation au pentest web. Développée par [Ivoire Cyber Shield](https://ivoirecybershield.ci).

## Stack technique

- **Backend :** PHP 8.2 + Apache
- **Base de données :** MySQL 8.0 (InnoDB)
- **Conteneurisation :** Docker Compose
- **Email :** Mailpit (capture SMTP locale)
- **CVE Lab :** Conteneur isolé PHP (port 8081)

## Installation

### Prérequis

- Docker et Docker Compose
- Git

### Lancement

```bash
# Cloner le projet
git clone https://github.com/buteurdechien-svg/ICS-com.git
cd ICS-com

# Copier la configuration
cp .env.example .env

# Lancer les services
docker compose up -d --build

# Attendre ~30 secondes que MySQL initialise le schéma et les données seed
```

### Accès

| Service | URL | Description |
|---------|-----|-------------|
| Application | http://localhost:8080 | Plateforme communautaire ICS |
| Mailpit | http://localhost:8025 | Interface email de test |
| CVE Lab | http://localhost:8081 | Simulation CVE PHP 2026 |

### Comptes de test

Tous les comptes utilisent le mot de passe : `Password123!`

| Utilisateur | Email | Rôle |
|-------------|-------|------|
| m.ahize | ahize@ics.ci | Président |
| k.kouadio | kouadio@ics.ci | Administrateur |
| s.traore | traore@ics.ci | Modérateur |
| a.coulibaly | coulibaly@ics.ci | Membre |
| f.bamba | bamba@ics.ci | Membre |
| d.konan | konan@ics.ci | Membre |
| n.kone | kone@ics.ci | Membre |
| j.diallo | diallo@ics.ci | Membre |
| e.yao | yao@ics.ci | Membre |
| r.ouattara | ouattara@ics.ci | Membre |
| g.emmanuel | emmanuel@ics.ci | Membre |
| d.mohamed | mohamed@ics.ci | Membre |

### Réinitialisation

```bash
# Tout reconstruire (supprime les données)
docker compose down -v
docker compose up -d --build
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
