SYSTÈME DE GESTION DE RÉSERVATION DE TERRAINS SPORTIFS

Le projet consiste en une application web développée en PHP permettant la gestion complète d’un centre sportif, avec un système de réservation en ligne. Elle propose deux interfaces principales :

Une pour les joueurs (réservation, profil, historique)
Une pour les administrateurs (gestion des terrains, utilisateurs et statistiques)


Fonctionnalités Clés
🔹 Gestion des Utilisateurs

Inscription, authentification sécurisée

Rôles (Administrateur / Joueur), statut actif ou bloqué


🔹 Gestion des Terrains

Multi-sports (Football, Tennis, Basketball, etc.)

États : bon, en maintenance, hors service

Tarification horaire flexible, images associées


🔹 Réservations

Créneaux de 08h à 22h

Vérification des disponibilités en temps réel

Prévention des conflits et calcul des montants

Annulations gérées automatiquement selon les cas


🔹 Interfaces

Administrateur : tableau de bord, gestion des utilisateurs, terrains, réservations

Joueur : tableau de bord personnel, historique, réservation intuitive


🔹 Statistiques

Pour joueurs : historique, sports favoris, dépenses

Pour admin : utilisation des terrains, revenus, activité globale


🔹 Technologies Utilisées :
Backend : PHP 7+ avec PDO

Frontend : HTML5, CSS3, JavaScript (jQuery)

Base de données : MySQL (4 tables principales)

Serveur : Apache (XAMPP)

Design : Responsive, CSS Grid/Flexbox


🔹Sécurité et Logique Métier :
Sécurité renforcée : SQLi, XSS, sessions sécurisées, rôles


🔹Logique métier avancée :

Mise à jour automatique des réservations selon l’état du terrain ou le statut joueur

Algorithmes de détection de conflits et gestion dynamique des créneaux


🔹Avantages
Solution complète, intuitive et sécurisée

Adaptée aux centres sportifs, clubs et complexes multi-activités

Facilement scalable et maintenable


images de app : 

parti utilisateur : 
![Capture d’écran (6)](https://github.com/user-attachments/assets/e16c1545-7c69-452f-8ba3-5d4349bee8df)
![Capture d’écran (7)](https://github.com/user-attachments/assets/fc672239-fce2-41d6-bc0f-be20dc928a92)
![Capture d’écran (8)](https://github.com/user-attachments/assets/e46b7273-6abc-41ce-936c-9d537fd0989d)
![Capture d’écran (4)](https://github.com/user-attachments/assets/fe7d1db5-2d45-4887-97aa-c2e907c11f5f)
![Capture d’écran (5)](https://github.com/user-attachments/assets/e28d2a00-e75c-4676-a6af-e733019a78d7)

parti joueur : 

![Capture d’écran (9)](https://github.com/user-attachments/assets/5f8105cf-9aa2-463c-bde5-5ed5a678b6ca)
![Capture d’écran (10)](https://github.com/user-attachments/assets/1d68f42d-0c3e-47b7-8085-2d94bee3ed53)
![Capture d’écran (11)](https://github.com/user-attachments/assets/838efeba-eb01-4455-b0df-b5ab4eb5827e)
![Capture d’écran (10)](https://github.com/user-attachments/assets/91679637-472f-4619-b8ff-309e4dfe02bf)

parti admin

![Capture d’écran (13)](https://github.com/user-attachments/assets/a35d3a0f-df51-44a6-90b2-ce85a294d16d)
![Capture d’écran (15)](https://github.com/user-attachments/assets/9cd05aa9-297d-494f-ad10-dad0742a438e)
![Capture d’écran (16)](https://github.com/user-attachments/assets/376c9e7a-274f-4bfb-b253-54030dc1b673)
![Capture d’écran (17)](https://github.com/user-attachments/assets/8ec6d770-f9f9-48f9-a767-557be122c315)
![Capture d’écran (18)](https://github.com/user-attachments/assets/621057c2-a8cb-426c-aac7-0fde3b48a191)
![Capture d’écran (19)](https://github.com/user-attachments/assets/0a0850f7-1834-413d-9570-c316c902068f)
![Capture d’écran (20)](https://github.com/user-attachments/assets/fb05fc89-db16-49b2-9c20-81af3056c586)

bd : 

![Capture d’écran (24)](https://github.com/user-attachments/assets/d717d277-48a5-47cf-a84e-33b78f471476)
![Capture d’écran (22)](https://github.com/user-attachments/assets/0c7359db-11fd-421f-b59c-2c271b40f8c7)
![Capture d’écran (21)](https://github.com/user-attachments/assets/6f13ae0e-52f1-46cf-924c-c4352263f531)
create base name bd

-- Table Admin 
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255)
);

-- Table Joueur (ajout de num_telephone)
CREATE TABLE joueur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255),
    num_telephone VARCHAR(20),
    statut ENUM('actif', 'bloqué') DEFAULT 'actif'
);

-- Table Terrain (ajout de tarif_horaire)
CREATE TABLE terrain (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    type ENUM('football', 'volleyball', 'basketball'),
    etat ENUM('bon', 'en maintenance', 'hors service') DEFAULT 'bon',
    tarif_horaire DECIMAL(10,2),
    images TEXT
);

-- Table Reservation (ajout de paiement et montant_total)
CREATE TABLE reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    joueur_id INT,
    terrain_id INT,
    date DATE,
    heure_debut TIME,
    heure_fin TIME,
    paiement ENUM('payé', 'non payé') DEFAULT 'non payé',
    montant_total DECIMAL(10,2),
    statut ENUM('confirmé', 'annulé') DEFAULT 'confirmé',
    FOREIGN KEY (joueur_id) REFERENCES joueur(id) ON DELETE CASCADE,
    FOREIGN KEY (terrain_id) REFERENCES terrain(id) ON DELETE CASCADE
);
