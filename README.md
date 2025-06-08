SYSTÈME DE GESTION DE RÉSERVATION DE TERRAINS SPORTIFS

Description du Projet
Application web développée en PHP permettant la gestion complète d'un centre sportif avec système de réservation en ligne. La solution offre deux interfaces distinctes : une pour les joueurs souhaitant réserver des terrains et une pour les administrateurs gérant l'ensemble du système.

FONCTIONNALITÉS PRINCIPALES

1. Gestion des Utilisateurs
• Inscription et authentification sécurisée des joueurs
• Gestion des profils utilisateurs
• Système de rôles (Administrateur/Joueur)
• Gestion des statuts (Actif/Bloqué)

2. Gestion des Terrains
• Support multi-sports : Football, Tennis, Basketball, Volleyball, Badminton
• États des terrains : Bon, En maintenance, Hors service
• Tarification flexible par heure
• Gestion des images de terrains

3. Système de Réservation
• Créneaux horaires de 08h00 à 22h00
• Vérification des disponibilités en temps réel
• Prévention des conflits de réservation
• Calcul automatique des montants
• Gestion des annulations

4. Interface Administrateur
• Tableau de bord avec statistiques globales
• CRUD complet des terrains
• Gestion des joueurs (activation/blocage)
• Supervision des réservations

5. Interface Joueur
• Tableau de bord personnel avec statistiques
• Historique des réservations avec filtres
• Gestion du profil
• Réservation intuitive des terrains

TECHNOLOGIES UTILISÉES
Backend : PHP 7+ avec PDO
Frontend : HTML5, CSS3, JavaScript (jQuery)
Base de données : MySQL
Serveur : Apache (XAMPP)
Design : CSS Grid/Flexbox, Font Awesome

Base de Données
• 4 tables principales : joueurs, terrains, reservations, admin
• Relations optimisées avec clés étrangères
• Requêtes préparées pour la sécurité

SÉCURITÉ IMPLÉMENTÉE
• Protection SQL Injection via requêtes préparées
• Protection XSS avec échappement HTML
• Authentification par sessions sécurisées
• Contrôle d'accès basé sur les rôles
• Validation des données côté serveur

LOGIQUE MÉTIER AVANCÉE

Règles Automatiques
• Changement d'état terrain → Annulation automatique des réservations
• Blocage joueur → Annulation de toutes ses réservations actives
• Vérification disponibilité → Contrôle en temps réel
• Gestion des créneaux → Génération automatique sans chevauchement

Algorithmes Intelligents
• Détection des conflits temporels
• Calcul dynamique des disponibilités
• Mise à jour en cascade des statuts
• Optimisation des créneaux horaires

INTERFACE UTILISATEUR
• Design responsive adaptatif mobile/desktop
• Interface moderne avec variables CSS
• Navigation intuitive avec breadcrumbs visuels
• Feedback utilisateur avec messages contextuels
• Validation temps réel des formulaires

STATISTIQUES ET RAPPORTS

Pour les Joueurs
• Nombre total de réservations
• Sports les plus pratiqués
• Historique complet des activités
• Montants dépensés

Pour les Administrateurs
• Vue d'ensemble du système
• Statistiques d'utilisation des terrains
• Gestion des revenus
• Activité des utilisateurs

AVANTAGES DU SYSTÈME
• Solution complète pour centres sportifs
• Interface dual adaptée aux besoins
• Sécurité robuste pour données sensibles
• Scalabilité pour croissance future
• Maintenance facile avec code structuré

CAS D'USAGE

Idéal pour :
• Centres sportifs municipaux ou privés
• Complexes de loisirs multi-activités
• Clubs sportifs souhaitant digitaliser leurs réservations
• Associations gérant des équipements sportifs


CONCLUSION
Ce projet représente une solution professionnelle complète pour la gestion de terrains sportifs, intégrant toutes les fonctionnalités nécessaires pour un usage commercial : gestion des utilisateurs, réservations intelligentes, interface d'administration, sécurité robuste et design moderne.

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
