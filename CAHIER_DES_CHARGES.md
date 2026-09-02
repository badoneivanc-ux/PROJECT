# Cahier des charges - Atelier du Museau

## Sommaire

1. Présentation du projet
2. Objectifs
3. Public cible et profils utilisateurs
4. Périmètre fonctionnel
5. Spécifications fonctionnelles détaillées
6. Règles de gestion
7. Exigences non fonctionnelles
8. Contraintes techniques
9. Modèle de données (aperçu)
10. Livrables attendus
11. Hors périmètre
12. Critères de recette
13. Planning indicatif

---

## 1. Présentation du projet

**Atelier du Museau** est le site internet d'une activité de toilettage canin. Le projet consiste à concevoir et développer une application web permettant de présenter l'activité au public et de gérer, de bout en bout, le parcours de prise de rendez-vous d'un client.

### 1.1 Contexte et porteur de projet

Le site est destiné à une toiletteuse canin qui débute son activité. Elle a besoin d'une présence en ligne professionnelle, simple à utiliser au quotidien, qui lui permette de présenter son activité et de recevoir des demandes de rendez-vous sans intervention manuelle (téléphone, messages).

### 1.2 Problématique

Sans outil dédié, la prise de rendez-vous repose sur des échanges téléphoniques ou des messages, sans vue d'ensemble ni suivi structuré. L'application doit centraliser ces échanges : présentation de l'offre, création de compte client, enregistrement des chiens, prise de rendez-vous en ligne et suivi des demandes côté professionnel.

---

## 2. Objectifs

- offrir une vitrine claire et professionnelle de l'activité (services, tarifs, races toilettées) ;
- permettre à un visiteur de devenir client en quelques étapes (inscription) ;
- permettre à un client d'enregistrer un ou plusieurs chiens et de réserver un créneau en ligne ;
- permettre à la professionnelle de suivre et de traiter les demandes de rendez-vous depuis un espace dédié ;
- garantir la confidentialité des données personnelles et la sécurité des accès ;
- proposer une interface utilisable aussi bien sur ordinateur que sur mobile.

---

## 3. Public cible et profils utilisateurs

| Profil | Description | Accès |
|---|---|---|
| Visiteur | Toute personne consultant le site sans compte | Pages publiques (accueil, tarifs, races) |
| Client | Personne inscrite souhaitant faire toiletter son ou ses chiens | Espace personnel (profil, chiens, réservations) |
| Administrateur | La professionnelle, gestionnaire du site | Tableau de bord et outils de gestion |

---

## 4. Périmètre fonctionnel

Le site doit couvrir les grandes fonctions suivantes :

1. Vitrine publique (accueil, tarifs, catalogue des races et fiches détaillées).
2. Gestion de compte client (inscription, connexion, déconnexion, profil).
3. Gestion des chiens d'un client (ajout, modification).
4. Prise de rendez-vous en ligne et suivi de son statut.
5. Notifications par email liées à la réservation.
6. Espace d'administration (statistiques, gestion des réservations, des utilisateurs, des chiens et du référentiel des races).

---

## 5. Spécifications fonctionnelles détaillées

### 5.1 Parcours visiteur

Le visiteur doit pouvoir :

1. arriver sur une page d'accueil présentant l'activité ;
2. consulter la page des tarifs ;
3. parcourir le catalogue des races toilettées ;
4. consulter la fiche détaillée d'une race (description, poids moyen, entretien, conseils) ;
5. accéder aux formulaires de création de compte ou de connexion.

### 5.2 Parcours client

Le client doit pouvoir :

1. créer un compte (nom, prénom, email, mot de passe, adresse d'intervention : adresse, code postal, ville) ;
2. se connecter et se déconnecter ;
3. consulter et modifier son adresse d'intervention depuis son profil ;
4. ajouter un ou plusieurs chiens (nom, race, poids, âge, sexe, photo facultative) ;
5. modifier les informations d'un chien lui appartenant ;
6. réserver un rendez-vous pour l'un de ses chiens, à une date future et sur un créneau disponible ;
7. consulter la liste de ses rendez-vous et leur statut ;
8. annuler un rendez-vous encore en attente.

### 5.3 Parcours administrateur

L'administrateur doit pouvoir :

1. se connecter à un espace protégé dédié ;
2. consulter un tableau de bord avec des statistiques générales (utilisateurs, chiens, réservations, demandes en attente) ;
3. consulter l'ensemble des rendez-vous et modifier leur statut (en attente, confirmé, annulé, terminé) ;
4. consulter et supprimer des comptes utilisateurs ;
5. consulter et supprimer des fiches de chiens ;
6. créer, modifier et supprimer les fiches du référentiel des races.

### 5.4 Notifications par email

- un email est envoyé au client dès la création d'une demande de rendez-vous, pour accuser réception (statut en attente) ;
- un second email est envoyé au client uniquement lorsque l'administrateur confirme le rendez-vous (statut confirmé) ;
- un échec d'envoi d'email ne doit pas empêcher l'enregistrement de la réservation.

---

## 6. Règles de gestion

- une adresse email ne peut être associée qu'à un seul compte ;
- un client doit renseigner une adresse d'intervention dès l'inscription ;
- un chien appartient à un seul utilisateur ;
- un chien peut être rattaché à une race du référentiel, mais ce rattachement est facultatif ;
- une réservation appartient à un utilisateur et concerne un chien de cet utilisateur ;
- la date et l'heure d'une nouvelle réservation doivent être dans le futur ;
- les rendez-vous sont proposés sur des créneaux fixes : le matin (08h30, 09h00, 09h30) et l'après-midi (13h30, 14h00, 14h30) ;
- une seule réservation active (en attente ou confirmée) est autorisée par demi-journée, tous clients confondus ;
- toute nouvelle réservation est créée avec le statut « en attente » ;
- un client ne peut consulter ou modifier que ses propres chiens et réservations ;
- seul un compte disposant du rôle administrateur peut accéder aux pages d'administration ;
- un administrateur ne peut pas supprimer son propre compte.

---

## 7. Exigences non fonctionnelles

### 7.1 Sécurité

- les mots de passe doivent être stockés sous forme de hash non réversible ;
- les requêtes SQL doivent utiliser des requêtes préparées pour prévenir les injections ;
- les formulaires sensibles doivent être protégés contre les attaques CSRF ;
- les données affichées provenant des utilisateurs doivent être échappées pour prévenir les failles XSS ;
- l'accès à l'espace client et à l'espace administrateur doit être contrôlé par la session utilisateur, à chaque requête ;
- les fichiers envoyés (photos) doivent être contrôlés en type et en taille avant d'être stockés.

### 7.2 Ergonomie et accessibilité

- l'interface doit être utilisable par une personne non technique (langage simple, messages d'erreur clairs) ;
- les formulaires doivent utiliser des champs et des types adaptés (email, date, mot de passe, etc.) ;
- les pages doivent rester lisibles et utilisables sur un écran d'ordinateur comme sur un smartphone.

### 7.3 Compatibilité et performance

- le site doit fonctionner sur les navigateurs récents (Chrome, Firefox, Safari, Edge) ;
- les pages listant des données (races, réservations, utilisateurs) doivent rester consultables même lorsque leur nombre augmente.

### 7.4 Maintenabilité

- le code doit être organisé selon une architecture claire séparant les données, la logique métier et l'affichage ;
- la configuration sensible (accès base de données, SMTP) doit être externalisée et ne jamais être publiée dans le dépôt de code.

---

## 8. Contraintes techniques

| Élément | Choix retenu |
|---|---|
| Langage serveur | PHP orienté objet |
| Architecture | MVC (Modèle - Vue - Contrôleur) |
| Base de données | MySQL, accédée via PDO |
| Frontend | HTML5, CSS3, JavaScript |
| Envoi d'emails | PHPMailer via un relais SMTP (Brevo) |
| Hébergement | Hébergement mutualisé compatible PHP/MySQL (OVH) |
| Conception d'interface | Wireframes et prototype réalisés sous Figma |
| Administration de la base | phpMyAdmin |

---

## 9. Modèle de données (aperçu)

Quatre entités principales structurent l'application :

- **Utilisateur** : identité, email, mot de passe, adresse d'intervention, rôle ;
- **Chien** : nom, race, poids, âge, sexe, photo, propriétaire ;
- **Race** (référentiel) : nom, poids moyen, description, entretien, historique, caractéristiques, astuces, photo ;
- **Réservation** : date, créneau, durée, statut, utilisateur concerné, chien concerné.

Le détail du modèle conceptuel et logique de données, ainsi que le script de création des tables, sont fournis dans [DOSSIER_PROJET.md](DOSSIER_PROJET.md) (section 5) et dans [install.sql](install.sql).

---

## 10. Livrables attendus

- le code source de l'application (contrôleurs, modèles, entités, vues) ;
- le script SQL d'installation de la base de données ;
- les wireframes et le prototype Figma ;
- la feuille de style et les assets graphiques ;
- un jeu de données de démonstration (compte administrateur inclus) ;
- le dossier de réalisation documentant les choix effectués.

---

## 11. Hors périmètre

Les éléments suivants ne font pas partie du périmètre de cette première version :

- paiement en ligne ;
- gestion de plusieurs intervenants ou plannings multiples ;
- réinitialisation du mot de passe par email ;
- rappels automatiques avant un rendez-vous ;
- pagination ou filtrage avancé des tableaux d'administration ;
- mentions légales et politique de confidentialité complètes.

Ces points sont identifiés comme des évolutions possibles (voir [DOSSIER_PROJET.md](DOSSIER_PROJET.md), section 18).

---

## 12. Critères de recette

L'application est considérée conforme lorsque :

- chaque parcours utilisateur décrit en section 5 peut être réalisé sans erreur bloquante ;
- les règles de gestion de la section 6 sont respectées, y compris dans les cas limites (double réservation, accès à des données d'un autre utilisateur, etc.) ;
- les contrôles de sécurité de la section 7.1 sont vérifiés (mot de passe non lisible en base, formulaire refusé sans jeton CSRF, contenu utilisateur affiché sans exécution de script) ;
- l'affichage reste correct sur un format mobile et sur un format ordinateur.

Un plan de tests détaillé, scénario par scénario, est fourni dans [DOSSIER_PROJET.md](DOSSIER_PROJET.md) (section 16).

---

## 13. Planning indicatif

1. Analyse du besoin et rédaction du cahier des charges.
2. Conception des wireframes (Figma).
3. Modélisation de la base de données (MCD puis MLD).
4. Mise en place de l'environnement de développement local.
5. Développement des fonctionnalités publiques (accueil, tarifs, races).
6. Développement de l'espace client (compte, chiens, réservations).
7. Développement de l'espace administrateur.
8. Sécurisation et tests de recette.
9. Déploiement en production et vérifications finales.
