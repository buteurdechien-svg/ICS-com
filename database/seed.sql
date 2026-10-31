-- ============================================================
-- ICS Community — Données fictives de démonstration
-- LABORATOIRE DE FORMATION — DONNÉES FICTIVES
-- NE PAS EXPOSER SUR INTERNET
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- 12 Membres ICS (mots de passe : "Password123!" pour tous)
-- Hash bcrypt de "Password123!"
-- ============================================================
INSERT INTO users (username, email, password_hash, display_name, role, status, department, bio, city, is_admin, avatar_url, created_at, activated_at) VALUES

-- Président
('thesocvolkoff', 'volkoff@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'The SOC Volkoff', 'president', 'actif', 'Présidence', 'Président fondateur d''Ivoire Cyber Shield. Passionné de cybersécurité offensive et défensive. Formateur certifié CEH, OSCP.', 'Abidjan', 1, '/img/members/thesocvolkoff.jpg', '2025-01-15 08:00:00', '2025-01-15 08:00:30'),

-- Vice-Président
('massambacyberhack', 'massamba@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'Massamba CyberHack', 'administrateur', 'actif', 'Vice-Présidence', 'Vice-Président ICS. Expert en analyse forensique et réponse aux incidents. Spécialiste SOC/SIEM.', 'Abidjan', 1, '/img/members/massambacyberhack.jpg', '2025-01-16 09:00:00', '2025-01-16 09:00:30'),

-- Responsable Partenariat
('camson', 'camson@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'CAMSON', 'moderateur', 'actif', 'Partenariat', 'Responsable du département Partenariat. Développement des relations avec les entreprises et institutions.', 'Abidjan', 0, '/img/members/camson.jpg', '2025-02-01 10:00:00', '2025-02-01 10:00:30'),

-- Responsable Technique
('mrshadow', 'shadow@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'MR SHADOW', 'moderateur', 'actif', 'Technique', 'Responsable Technique ICS. Pentester, développeur d''outils de sécurité. Bug bounty hunter.', 'Abidjan', 0, '/img/members/mrshadow.jpg', '2025-02-05 11:00:00', '2025-02-05 11:00:30'),

-- Responsable Formation
('legoatdeshacker', 'goat@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'LE GOAT DES HACKER', 'moderateur', 'actif', 'Formation', 'Responsable Formation. Créateur de CTF et laboratoires pratiques. Mentor pour les débutants.', 'Abidjan', 0, '/img/members/legoatdeshacker.jpg', '2025-02-10 08:30:00', '2025-02-10 08:30:30'),

-- Gestionnaire budget
('victoriait', 'victoria@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'VICTORIA IT', 'moderateur', 'actif', 'Budget & Finances', 'Gestion du portefeuille et du budget ICS. Transparence et traçabilité des fonds.', 'Abidjan', 0, '/img/members/victoriait.jpg', '2025-02-15 09:00:00', '2025-02-15 09:00:30'),

-- Formateur
('hassancamara', 'hassan@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'HASSAN CAMARA', 'membre', 'actif', 'Formation', 'Formateur en sécurité réseau et administration système. Passionné de Linux et infrastructure.', 'Bouaké', 0, '/img/members/hassancamara.jpg', '2025-03-01 10:00:00', '2025-03-01 10:00:30'),

-- Département Partenariat
('tehuayann', 'tehua@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'TEHUA YANN', 'membre', 'actif', 'Partenariat', 'Membre du département Partenariat. Relations publiques et événementiel cyber.', 'Yamoussoukro', 0, '/img/members/tehuayann.jpg', '2025-03-10 11:00:00', '2025-03-10 11:00:30'),

-- Département Formation
('hackerman', 'hackerman@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'HACKER MAN', 'membre', 'actif', 'Formation', 'Département Formation. Spécialiste en exploitation web et mobile. Contributeur CTF.', 'Abidjan', 0, '/img/members/hackerman.jpg', '2025-03-15 14:00:00', '2025-03-15 14:00:30'),

-- Département Communication & Partenariat
('ahizimarc', 'ahizi@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'AHIZI MARC YOANN', 'membre', 'actif', 'Communication & Partenariat', 'Communication et partenariat. Création de contenu, réseaux sociaux et relations presse.', 'Abidjan', 0, '/img/members/ahizimarc.jpg', '2025-04-01 08:00:00', '2025-04-01 08:00:30'),

-- Communication (ajouté par le client)
('gnalyemmanuel', 'gnaly@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'GNALY Emmanuel', 'membre', 'actif', 'Communication', 'Département Communication. Stratégie de communication digitale et gestion de la communauté en ligne.', 'Abidjan', 0, '/img/members/gnalyemmanuel.jpg', '2025-04-10 09:00:00', '2025-04-10 09:00:30'),

-- Secrétariat (ajouté par le client)
('dombamohamed', 'domba@ics-lab.ci', '$2y$10$LxVqEKpMzMVGfdANJRQy5eQK8jFqNzVZz8U1dMWjHR3Jn7sM6YXWK', 'DOMBA Mohamed', 'membre', 'actif', 'Secrétariat', 'Secrétariat ICS. Organisation, planification et gestion administrative de l''association.', 'Abidjan', 0, '/img/members/dombamohamed.jpg', '2025-04-15 10:00:00', '2025-04-15 10:00:30');


-- ============================================================
-- 24 Publications (2 par membre)
-- ============================================================
INSERT INTO posts (user_id, content, visibility, likes_count, comments_count, created_at) VALUES

-- The SOC Volkoff (id=1)
(1, 'Bienvenue sur la plateforme communautaire ICS ! 🛡️ Cet espace est le nôtre — échangeons, apprenons et grandissons ensemble dans le domaine de la cybersécurité. Chaque membre compte, chaque contribution enrichit notre communauté. Ensemble, nous construisons l''avenir de la cybersécurité en Côte d''Ivoire.', 'public', 24, 5, '2025-05-01 08:00:00'),
(1, 'Rappel important : notre prochaine session de formation sur le pentest web aura lieu ce samedi. Au programme : reconnaissance, énumération des sous-domaines, et exploitation de vulnérabilités OWASP Top 10. Préparez vos machines Kali, on va pratiquer en conditions réelles sur notre lab !', 'members', 18, 3, '2025-06-15 10:30:00'),

-- Massamba CyberHack (id=2)
(2, 'Analyse forensique en cours sur un cas de phishing ciblant des entreprises ivoiriennes. Les techniques utilisées sont de plus en plus sophistiquées : usurpation de domaine, certificats SSL valides, et ingénierie sociale personnalisée. Je prépare un rapport détaillé pour la communauté.', 'members', 15, 4, '2025-05-10 14:00:00'),
(2, 'Nouveau cours disponible : "Introduction à l''analyse de malware avec Ghidra". 5 modules progressifs, du reverse engineering basique à l''analyse de samples réels. Inscriptions ouvertes pour les membres ICS. Prérequis : bases en assembleur x86.', 'public', 20, 6, '2025-07-01 09:00:00'),

-- CAMSON (id=3)
(3, 'Partenariat signé avec CyberAfrica Academy ! Nos membres auront accès à des réductions sur les certifications CompTIA Security+ et CEH. Détails dans la section partenariats. C''est une belle avancée pour la professionnalisation de notre communauté.', 'public', 22, 7, '2025-05-20 11:00:00'),
(3, 'Appel à projets : nous recherchons des membres motivés pour représenter ICS lors du prochain forum de cybersécurité d''Afrique de l''Ouest. Budget pris en charge pour les intervenants sélectionnés. Envoyez vos propositions de talk avant le 30 du mois.', 'members', 12, 3, '2025-08-01 08:00:00'),

-- MR SHADOW (id=4)
(4, 'Nouveau tool release : j''ai développé un script Python pour automatiser la détection de sous-domaines avec résolution DNS passive. Testé sur plus de 50 cibles, taux de découverte supérieur à Subfinder dans certains cas. Code source partagé sur notre GitLab interne.', 'members', 19, 8, '2025-05-25 16:00:00'),
(4, 'Retour d''expérience sur le dernier CTF HackTheBox Cyber Apocalypse 2025. Notre équipe ICS a terminé dans le top 5% mondial ! Bravo à tous les participants. Les writeups des challenges seront publiés cette semaine.', 'public', 28, 10, '2025-06-20 20:00:00'),

-- LE GOAT DES HACKER (id=5)
(5, 'Nouveau lab pratique disponible : "Exploitation de vulnérabilités IDOR dans les API REST". Scénario réaliste basé sur une application e-commerce fictive. 3 niveaux de difficulté. Accessible depuis notre infrastructure de formation.', 'members', 16, 5, '2025-06-01 09:00:00'),
(5, 'Programme de mentorat ICS : si vous débutez en cybersécurité et cherchez un accompagnement personnalisé, inscrivez-vous au programme. Chaque mentor prend en charge 2 à 3 mentorés pour une durée de 3 mois. C''est gratuit et ouvert à tous les membres.', 'public', 25, 9, '2025-07-15 11:00:00'),

-- VICTORIA IT (id=6)
(6, 'Bilan financier du trimestre : les dons collectés ont permis de financer 3 sessions de formation, l''achat de 2 licences Burp Suite Pro pour le lab, et la participation de 4 membres au forum régional. Transparence totale, le détail est disponible sur demande.', 'members', 14, 2, '2025-06-05 10:00:00'),
(6, 'Campagne de dons ouverte pour le projet "Lab Mobile" : un kit portable de formation pentest que nous pourrons déployer dans les universités de l''intérieur du pays. Objectif : 2 000 000 FCFA. Chaque contribution compte !', 'public', 20, 6, '2025-08-10 08:30:00'),

-- HASSAN CAMARA (id=7)
(7, 'Tutoriel : Mise en place d''un serveur VPN WireGuard sécurisé sur Ubuntu 24.04. Configuration complète avec kill switch, DNS over HTTPS, et split tunneling. Idéal pour protéger vos connexions lors des audits à distance.', 'public', 17, 4, '2025-06-10 15:00:00'),
(7, 'Atelier pratique ce weekend à Bouaké : "Sécurisation d''un réseau d''entreprise avec pfSense". Places limitées à 15 participants. Matériel fourni. Inscription obligatoire via la messagerie.', 'members', 10, 3, '2025-09-01 09:00:00'),

-- TEHUA YANN (id=8)
(8, 'Compte rendu de notre rencontre avec le Ministère de la Transition Numérique : ICS est désormais reconnu comme partenaire technique pour les programmes de sensibilisation à la cybersécurité dans les établissements scolaires. Une grande victoire !', 'public', 30, 12, '2025-06-25 14:00:00'),
(8, 'Organisation du 1er hackathon ICS ! Thème : "Solutions de cybersécurité pour les PME africaines". 48h de coding, mentors experts, et prix pour les 3 meilleures équipes. Date : novembre 2025. Partenaires bienvenus !', 'public', 26, 8, '2025-08-20 10:00:00'),

-- HACKER MAN (id=9)
(9, 'Writeup : Comment j''ai trouvé une faille XSS stockée dans une application bancaire (programme de bug bounty). De la reconnaissance à l''exploitation, en passant par la rédaction du rapport. Tout est détaillé pour vous aider à progresser.', 'members', 21, 7, '2025-07-05 18:00:00'),
(9, 'Challenge de la semaine : Trouvez le flag caché dans ce fichier pcap ! Indice : le trafic DNS cache parfois des secrets. Premier à trouver gagne un accès premium à notre plateforme de labs pendant 1 mois.', 'public', 15, 11, '2025-09-10 16:00:00'),

-- AHIZI MARC YOANN (id=10)
(10, 'Nouveau contenu sur nos réseaux : série de vidéos "Cybersécurité pour tous" — 10 épisodes courts pour sensibiliser le grand public aux menaces numériques. Phishing, mots de passe, Wi-Fi public... Partagez autour de vous !', 'public', 23, 5, '2025-07-10 11:00:00'),
(10, 'Couverture média : ICS mentionné dans le rapport annuel de l''ARTCI sur l''état de la cybersécurité en Côte d''Ivoire. Notre travail de formation et de sensibilisation est reconnu au niveau national. Fierté !', 'public', 32, 14, '2025-09-15 09:00:00'),

-- GNALY Emmanuel (id=11)
(11, 'Stratégie com'' Q4 2025 : focus sur LinkedIn et Twitter/X pour augmenter notre visibilité auprès des professionnels IT. Objectif : +500 followers qualifiés d''ici décembre. Tout membre qui souhaite contribuer au contenu est le bienvenu !', 'members', 11, 3, '2025-07-20 10:00:00'),
(11, 'Infographie : "Les 10 métiers de la cybersécurité les plus demandés en Afrique de l''Ouest en 2025". Analyste SOC, pentester, architecte sécurité... Découvrez les opportunités et les compétences requises.', 'public', 19, 6, '2025-09-20 14:00:00'),

-- DOMBA Mohamed (id=12)
(12, 'Planning des réunions mensuelles mis à jour pour le dernier trimestre 2025. Chaque 1er samedi du mois, 10h-12h en visio. L''ordre du jour sera partagé 48h avant chaque réunion. Présence fortement encouragée !', 'members', 8, 2, '2025-08-01 08:00:00'),
(12, 'Mise à jour des statuts de l''association : le nouveau règlement intérieur a été validé en assemblée générale. Document disponible dans l''espace membres. Merci à tous pour vos contributions lors des discussions.', 'members', 13, 4, '2025-09-25 11:00:00');


-- ============================================================
-- Quelques commentaires
-- ============================================================
INSERT INTO comments (post_id, user_id, content, created_at) VALUES
(1, 2, 'Excellente initiative Président ! La communauté n''attend que ça. Ensemble on est plus forts. 💪', '2025-05-01 09:00:00'),
(1, 5, 'Hâte de partager mes labs et CTF avec tout le monde. ICS en force !', '2025-05-01 10:00:00'),
(1, 4, 'La plateforme est clean, bravo pour le travail. On va pouvoir collaborer efficacement.', '2025-05-01 11:00:00'),
(4, 1, 'Super boulot Shadow ! Partage le lien du repo quand c''est prêt.', '2025-05-25 17:00:00'),
(4, 9, 'Testé rapidement, les résultats sont impressionnants. J''ai quelques suggestions d''amélioration.', '2025-05-25 18:00:00'),
(8, 3, 'Top 5% mondial, c''est énorme ! ICS représente dignement la Côte d''Ivoire.', '2025-06-20 21:00:00'),
(15, 1, 'L''idée du lab mobile est excellente. Ça va changer la donne pour les étudiants en province.', '2025-08-10 09:00:00'),
(16, 5, 'Je m''inscris comme mentor ! Prêt à accompagner des débutants motivés.', '2025-06-25 15:00:00'),
(20, 4, 'Je relève le challenge ! Wireshark + tshark en action.', '2025-09-10 17:00:00'),
(22, 6, 'Bravo pour cette reconnaissance nationale ! Le travail paie.', '2025-09-15 10:00:00');


-- ============================================================
-- Réactions (likes)
-- ============================================================
INSERT INTO reactions (post_id, user_id, type, created_at) VALUES
(1, 2, 'love', '2025-05-01 08:30:00'),
(1, 3, 'fire', '2025-05-01 08:45:00'),
(1, 4, 'like', '2025-05-01 09:00:00'),
(1, 5, 'clap', '2025-05-01 09:15:00'),
(1, 6, 'love', '2025-05-01 09:30:00'),
(8, 1, 'fire', '2025-06-20 20:30:00'),
(8, 3, 'fire', '2025-06-20 21:00:00'),
(8, 5, 'clap', '2025-06-20 21:30:00'),
(16, 1, 'love', '2025-06-25 14:30:00'),
(16, 2, 'clap', '2025-06-25 14:45:00'),
(22, 1, 'love', '2025-09-15 09:30:00'),
(22, 2, 'fire', '2025-09-15 09:45:00'),
(22, 3, 'clap', '2025-09-15 10:15:00');


-- ============================================================
-- Messages privés fictifs
-- ============================================================
INSERT INTO messages (sender_id, receiver_id, content, is_read, created_at) VALUES
(2, 1, 'Président, le rapport forensique est prêt. Je te l''envoie par quel canal sécurisé ?', 1, '2025-05-11 08:00:00'),
(1, 2, 'Parfait Massamba ! Utilise notre canal chiffré Signal. Envoie-moi aussi un résumé ici.', 1, '2025-05-11 08:30:00'),
(4, 5, 'Salut GOAT, j''ai un nouveau challenge à te proposer pour le lab. On en parle ?', 1, '2025-06-01 10:00:00'),
(5, 4, 'Carrément Shadow ! Envoie les détails, je prépare l''environnement ce weekend.', 1, '2025-06-01 11:00:00'),
(3, 8, 'Tehua, peux-tu préparer une présentation pour le partenaire CyberAfrica ? Deadline vendredi.', 0, '2025-07-01 09:00:00'),
(7, 1, 'Président, l''atelier Bouaké est complet ! 15 inscrits en 2 jours. La demande est forte en province.', 1, '2025-09-02 08:00:00');


-- ============================================================
-- Donations simulées
-- ============================================================
INSERT INTO donations (user_id, donor_name, amount, payment_method, transaction_ref, status, message, created_at) VALUES
(1, 'The SOC Volkoff', 500000, 'wave', 'WAV-2025-001', 'completed', 'Pour le développement de la communauté', '2025-03-01 10:00:00'),
(2, 'Massamba CyberHack', 250000, 'orange_money', 'OM-2025-001', 'completed', 'Soutien au programme de formation', '2025-03-15 14:00:00'),
(NULL, 'Anonyme', 1000000, 'binance_btc', 'BTC-2025-001', 'completed', 'Keep hacking ethically!', '2025-04-01 08:00:00'),
(6, 'VICTORIA IT', 150000, 'moov_money', 'MOOV-2025-001', 'completed', 'Contribution mensuelle', '2025-05-01 09:00:00'),
(NULL, 'Entreprise TechCI', 5000000, 'visa', 'VISA-2025-001', 'completed', 'Sponsoring formation pentest', '2025-06-01 10:00:00'),
(3, 'CAMSON', 100000, 'wave', 'WAV-2025-002', 'completed', NULL, '2025-07-01 08:00:00'),
(8, 'TEHUA YANN', 75000, 'orange_money', 'OM-2025-002', 'completed', 'Pour le hackathon', '2025-08-15 11:00:00'),
(NULL, 'Bienfaiteur Anonyme', 2000000, 'visa', 'VISA-2025-002', 'completed', 'Courage à toute l''équipe !', '2025-09-01 10:00:00');


-- ============================================================
-- Cyber News
-- ============================================================
INSERT INTO cyber_news (title, content, category, author_id, published_at) VALUES
('Nouvelle vulnérabilité critique dans Apache HTTP Server (CVE-2025-XXXX)', 'Une faille de type path traversal a été découverte dans Apache HTTP Server versions 2.4.x. L''exploitation permet à un attaquant non authentifié de lire des fichiers arbitraires sur le serveur. Mise à jour recommandée immédiatement.', 'vulnerability', 4, '2025-08-01 08:00:00'),
('Campagne de phishing ciblant les banques ouest-africaines', 'Une campagne sophistiquée de phishing utilisant des domaines lookalike cible les clients de plusieurs banques en Côte d''Ivoire et au Sénégal. Les emails imitent parfaitement les communications officielles avec des certificats SSL valides.', 'data_breach', 2, '2025-08-15 10:00:00'),
('Sortie de Kali Linux 2025.3 — Nouveaux outils', 'La dernière version de Kali Linux intègre 15 nouveaux outils dont un scanner de vulnérabilités IA, un framework d''exploitation cloud, et des améliorations majeures de l''interface. Téléchargement disponible sur le site officiel.', 'tool', 5, '2025-09-01 09:00:00'),
('Formation gratuite : Introduction au Bug Bounty par ICS', 'Ivoire Cyber Shield lance une formation gratuite de 4 semaines sur le bug bounty. Au programme : méthodologie, outils, rédaction de rapports, et pratique sur des cibles autorisées. Inscription ouverte à tous.', 'tutorial', 1, '2025-09-10 11:00:00'),
('ICS participe au Forum Régional de Cybersécurité 2025', 'L''équipe ICS sera présente au Forum Régional de Cybersécurité qui se tiendra à Abidjan du 15 au 17 novembre. Stand de démonstration, talks techniques et networking au programme.', 'event', 3, '2025-09-20 14:00:00');


-- ============================================================
-- Quelques entrées d'audit
-- ============================================================
INSERT INTO audit_logs (user_id, action, target_type, target_id, details, ip_address, created_at) VALUES
(1, 'login', 'user', 1, '{"method": "password"}', '192.168.1.100', '2025-09-25 08:00:00'),
(1, 'create_post', 'post', 1, '{"visibility": "public"}', '192.168.1.100', '2025-05-01 08:00:00'),
(2, 'login', 'user', 2, '{"method": "password"}', '192.168.1.101', '2025-09-25 09:00:00'),
(4, 'create_post', 'post', 7, '{"visibility": "members"}', '192.168.1.104', '2025-05-25 16:00:00');
