# L'Atelier du Museau — Documentation du projet

Site web de **toilettage canin à domicile à Vincennes (94)** : présentation de l'activité, guide des races, comptes clients, réservation de rendez-vous en ligne et espace d'administration.

- Site en production : https://latelierdumuseau.fr
- Hébergement : o2switch (serveur web + MySQL)
- Dépôt : `badoneivanc-ux/PROJECT` (branche `main`)

---

## Sommaire

1. [Vue d'ensemble](#1-vue-densemble)
2. [Fonctionnalités](#2-fonctionnalités)
3. [Règles de gestion de la réservation](#3-règles-de-gestion-de-la-réservation)
4. [Technologies](#4-technologies)
5. [Architecture](#5-architecture)
6. [Base de données](#6-base-de-données)
7. [Routes et pages](#7-routes-et-pages)
8. [Emails](#8-emails)
9. [Sécurité](#9-sécurité)
10. [SEO et pages légales](#10-seo-et-pages-légales)
11. [Configuration (`.env`)](#11-configuration-env)
12. [Installation en local](#12-installation-en-local)
13. [Déploiement automatique](#13-déploiement-automatique)
14. [Exploitation et maintenance](#14-exploitation-et-maintenance)
15. [Limites connues et évolutions possibles](#15-limites-connues-et-évolutions-possibles)

---

## 1. Vue d'ensemble

Trois profils utilisent le site :

| Profil | Accès | Usage |
|---|---|---|
| Visiteur | Pages publiques | Découvrir l'activité, les tarifs, le guide des races |
| Client | Compte (rôle `user`) | Gérer ses chiens, réserver, suivre et annuler ses rendez-vous |
| Administratrice | Compte (rôle `admin`) | Valider les rendez-vous, gérer clients, chiens et races |

Le projet a été réalisé pour une toiletteuse indépendante, dans le cadre d'une formation DWWM. Il est volontairement sans framework : PHP orienté objet, MVC maison, MySQL via PDO.

---

## 2. Fonctionnalités

### Espace public
- **Accueil** : présentation, services, appels à l'action (réserver / créer un compte).
- **Infos pratiques** (zone d'intervention) : communes desservies, déroulement du rendez-vous, bienfaits et FAQ native. Son ambiance crème et dorée a servi de modèle au thème de tout le site. Le lien « Infos pratiques » figure dans le menu commun ; le menu repliable s'applique jusqu'à 1 100 px. URL : `index.php?controller=home&action=infosPratiques`, comme les autres pages statiques ; l'ancienne adresse `action=zoneIntervention` redirige vers elle en 301. Les styles du contenu, préfixés `.zone-`, sont regroupés dans la section « PAGE ZONE D'INTERVENTION » de [`public/style.css`](public/style.css). Les images, en WebP redimensionnées à environ 2× leur taille d'affichage, sont dans `public/uploads/images-zoneIntervention/` (versionné et déployé) ; les originaux haute définition sont conservés dans `public/uploads/originaux/`, ignoré par git et donc non déployé.
- **Tarifs** : grilles Petit / Moyen / Grand chien (bain + brushing, tonte, coupe ciseaux, épilation, entretien visage/pattes), suppléments (démêlage, griffes, poil abîmé, comportement), déplacement gratuit à Vincennes. La page est générée depuis le catalogue [`Core/Prestations.php`](Core/Prestations.php), également utilisé par le formulaire de réservation.
- **Guide des races** : 24 races dans le référentiel, avec fiche détaillée par race (description, historique, conseils d'entretien, astuces de toilettage, photo).
- **Pages légales** : mentions légales, CGV, politique de confidentialité (RGPD).

### Espace client
- **Inscription** : nom, prénom, email, téléphone (10 chiffres), adresse d'intervention, code postal (5 chiffres), ville, mot de passe (8 caractères minimum) et acceptation des CGV (obligatoire, contrôlée côté serveur).
- **Connexion / déconnexion**.
- **Profil** : modification de ses informations (`updateProfile`), liste de ses chiens et de ses rendez-vous. Les titres « Mes chiens » et « Mes réservations » utilisent la même ligne de séparation ; les boutons « + Ajouter un chien » et « + Nouveau rendez-vous » sont placés sous leur titre, avant les fiches ou le tableau de l'historique.
- **Chiens** : ajout et modification (nom, race du référentiel ou saisie libre, poids, âge, sexe, photo facultative).
- **Fiche de ma race** : le lien du profil suit la race du référentiel sélectionnée, y compris après modification d'un chien initialement non répertorié. Le retour à une race saisie librement retire ce lien.
- **Réservation** : choix du chien, de la prestation, des suppléments éventuels, de la date et d'un créneau ; consultation des rendez-vous ; annulation tant que le rendez-vous est « en attente ». Le bouton « + Nouveau rendez-vous » se trouve au-dessus du tableau de l'historique et reste masqué lorsque le formulaire de création est déjà affiché.
- **Prestations** : une prestation principale obligatoire (boutons radio) et des suppléments facultatifs (cases à cocher). Le gabarit est déduit du poids enregistré du chien : petit jusqu'à 10 kg inclus, moyen au-delà de 10 kg et en dessous de 25 kg, grand à partir de 25 kg. Seules les prestations proposées pour ce gabarit sont affichées, avec leur prix, et un total estimé est calculé en direct (hors frais de déplacement hors secteur). « Débourrage / Mue » est sur devis (total « sur devis »). Le démêlage important et le temps supplémentaire lié au comportement peuvent être cochés, mais ils sont facturés par tranche de 30 minutes et évalués sur place : ils n'entrent pas dans le total. Le serveur revalide la prestation selon le gabarit du chien et reprend toujours les prix du catalogue ; les choix sont conservés après une erreur du formulaire. Libellés et prix sont figés dans la réservation, ce qui préserve l'historique en cas de changement de tarif. Les prestations apparaissent dans les rendez-vous du client, son profil, les listes administratives et les trois emails de réservation. Les réservations antérieures à la fonctionnalité affichent « — ».
- **Informations complémentaires** : champ facultatif de 1 000 caractères maximum (premier toilettage, état du poil, comportement particulier…). Le texte est conservé après une erreur du formulaire, enregistré avec la réservation et visible dans les rendez-vous du client, son profil et les listes administratives, y compris les prestations terminées. Un champ vide est enregistré comme `NULL`.

### Espace administrateur
- **Tableau de bord** : nombre d'utilisateurs, de chiens, de réservations, et de réservations en attente.
- **Indisponibilités** : bouton « Bloquer une séance » dans « Gestion des réservations », qui déplie un formulaire (date à partir d'aujourd'hui, matin ou après-midi, motif interne facultatif de 255 caractères maximum). Le volet utilise `<details>` / `<summary>` et fonctionne sans JavaScript : replié par défaut, il se rouvre après une erreur avec les valeurs saisies. Un raccourci du tableau de bord ouvre directement ce formulaire. Les séances bloquées à venir sont listées sur la même page avec un bouton « Débloquer ». Après blocage ou déblocage, l'admin revient à la gestion des réservations. Le blocage peut être immédiat, sans délai de 24 h ; le dimanche reste fermé aux clients. Il ne remplace jamais une réservation active et ne crée ni rendez-vous, ni email, ni entrée dans les statistiques de réservations. Aucun chien virtuel n'est nécessaire.
- **Réservations** : liste des réservations en cours, changement de statut (`en attente`, `confirmé`, `annulé`, `terminé`). Le bouton « Prestations terminées » affiche ou masque les rendez-vous terminés sur la même page ; cette liste est repliée par défaut. Les réservations annulées restent masquées dans l'administration, conservées en base et visibles dans l'espace client.
- **Utilisateurs** : liste et suppression (un admin ne peut pas se supprimer lui-même).
- **Chiens** : liste en cartes et suppression.
- **Confirmation renforcée des suppressions** : le bouton « Supprimer » ouvre une page identifiant l'utilisateur (nom et email) ou le chien (nom et propriétaire), avec le nombre de chiens et de réservations qui seront supprimés en cascade, tous statuts confondus. Il faut saisir exactement `SUPPRIMER` avant la suppression définitive ; « Annuler » est mis en avant. La vérification côté serveur exige un POST, le jeton CSRF et un jeton de confirmation à usage unique lié à la cible et à la session. Le formulaire reste utilisable sans JavaScript grâce à la validation HTML et serveur. Un utilisateur supprimé entraîne la suppression de ses chiens et réservations ; supprimer un chien conserve le compte et les autres chiens du propriétaire.

- **Races** : création, modification et suppression dans le référentiel, avec indicateur de fiche « complète » ou « partielle ».

Le test de régression ciblé s'exécute avec `php tests/deletion_confirmation.php` (extension PDO SQLite requise). Il utilise uniquement une base en mémoire et ne modifie aucune donnée de l'application.

Les indisponibilités se testent avec `php tests/session_unavailability.php` (SQLite en mémoire) et `php tests/session_unavailability.php --mysql` (connexion configurée, tables temporaires masquant les tables réelles uniquement pendant le test ; aucune donnée applicative modifiée). Le refus CSRF se vérifie avec `php tests/session_unavailability.php --invalid-csrf` (réponse 403 et marqueur `CSRF_BLOCKED`).

Les informations complémentaires se testent avec `php tests/reservation_informations.php` (SQLite en mémoire, emails simulés sans envoi réel) : champ absent ou vide, limite de 1 000 caractères accentués, refus des entrées invalides, conservation après erreur et affichage HTML sécurisé.

Les prestations se testent avec `php tests/reservation_prestations.php` (SQLite en mémoire, emails simulés) : seuils de gabarit, refus des prestations absentes, inconnues ou non proposées pour le gabarit, prix repris du catalogue, total estimé et cas sur devis, annulation de la réservation si l'enregistrement des prestations échoue, contenu des trois emails, affichage dans les tableaux et génération de la page Tarifs.

La page Infos pratiques se teste avec `php tests/infos_pratiques.php` (sans base de données ni email) : route publique, concordance des communes de la liste et du plan, absence de Paris 20e, SVG valide et autonome, mise en évidence de Vincennes, liens selon la session, métadonnées, images, ancres, FAQ et conservation du menu, du pied de page et des styles des autres pages.

Le plan fixe [`public/uploads/images-zoneIntervention/communes.svg`](public/uploads/images-zoneIntervention/communes.svg) utilise les contours de l'[API Découpage administratif](https://geo.api.gouv.fr/) (codes INSEE 94080, 93048, 94067, 94033, 94069, 94018, 94052, 94042 et 94068) et les contours du 12e arrondissement de la [Ville de Paris](https://opendata.paris.fr/explore/dataset/arrondissements/) (75112). Données consultées le 13 octobre 2026, projetées localement avec le nord en haut et simplifiées pour l'affichage. Paris 12e comprend le bois de Vincennes. Le SVG est inclus localement dans la page, avec titre et description accessibles : aucune carte distante, aucun script ni cookie tiers au chargement. Son affichage est limité à 520 px de large sur ordinateur et s'adapte à la largeur disponible sur mobile. Une légende et une liste textuelle dépliable complètent le plan. Toute modification des communes desservies doit être répercutée dans le contrôleur et dans ce SVG ; le test vérifie leur concordance.

Dans « Gestion des réservations », les rendez-vous et le volet « Prestations terminées » apparaissent en premier. La section « Disponibilités du planning » suit, séparée par un espace et une ligne dorée, avec une explication distinguant les blocages des rendez-vous clients, le bouton « Bloquer une séance », puis la liste des séances bloquées à venir.

---

## 3. Règles de gestion de la réservation

Elles sont appliquées côté serveur dans [`ReservationController`](Controllers/ReservationController.php) et [`ReservationModel`](Models/ReservationModel.php).

| Règle | Détail |
|---|---|
| Créneaux fixes | Matin : 08h30, 09h00, 09h30. Après-midi : 13h30, 14h00, 14h30 |
| Jour de fermeture | Dimanche |
| Délai minimal | Au moins 24 h avant le rendez-vous |
| Capacité | **Une seule réservation active par demi-journée** (matin ou après-midi) |
| Chien | Le chien doit appartenir au client connecté |
| Statut initial | `en attente` |
| Annulation par le client | Possible uniquement si le statut est `en attente` |
| Indisponibilité administrative | Interdit toute réservation de la demi-journée ; le motif n'est pas communiqué au client |
| Réactivation par l'admin | Refusée si une autre réservation active ou une indisponibilité occupe déjà la demi-journée |

Une réservation est « active » quand son statut est `en attente` ou `confirmé`. La création, le blocage administratif et la réactivation utilisent le même verrou MySQL (`GET_LOCK`) par date et demi-journée avant de vérifier la disponibilité et d'écrire. Les créneaux sont partagés dans [`ReservationSchedule`](Core/ReservationSchedule.php).

Cycle de vie d'un rendez-vous :

```
en attente ──(admin)──► confirmé ──(admin)──► terminé
    │                       │
    └──(client ou admin)──► annulé ◄──(admin)──┘
```

---

## 4. Technologies

| Domaine | Choix |
|---|---|
| Langage serveur | PHP 8 (typage strict dans `public/index.php`), POO |
| Architecture | MVC maison (routeur, contrôleurs, modèles, entités, vues) |
| Base de données | MySQL / MariaDB, accès par PDO (requêtes préparées) |
| Front | HTML5, CSS3 (`public/style.css`), JavaScript natif |
| Polices et icônes | Google Fonts (Playfair Display, Raleway), Font Awesome 6 |
| Emails | PHPMailer (embarqué dans `Core/PHPMailer`), SMTP |
| Hébergement | o2switch (cPanel, Apache) |
| Déploiement | GitHub Actions, envoi par FTPS |
| Environnement local | MAMP (Apache, MySQL, PHP) |

Aucune dépendance Composer ni étape de build : le dépôt contient directement le code exécuté en production.

**Thème graphique** : le contenu des pages est crème et doré ; le menu, les bandeaux d'appel à l'action (Tarifs, Infos pratiques), la barre latérale admin et le pied de page restent noirs. L'accueil alterne : bandeau d'accueil crème (logo crème [`public/uploads/logo/logoCreme.webp`](public/uploads/logo/logoCreme.webp) en filigrane, fondu par `mix-blend-mode: multiply` et un masque circulaire), « Nos services » en noir, puis appel à l'action crème. Toutes les couleurs sont déclarées dans `:root` en tête de [`public/style.css`](public/style.css), sur deux niveaux :
- la **palette** (`--noir`, `--or`, `--creme`, `--beige`, `--sable`, `--encre`…) ;
- les **rôles** utilisés par les composants (`--fond`, `--fond-carte`, `--fond-alt`, `--bordure`, `--texte`, `--texte-doux`, `--texte-discret`, `--accent`, `--accent-vif`). Ils ont des valeurs claires par défaut et sont redéfinis en sombre pour les zones noires (`.navbar`, `.services`, `.tarifs-cta`, `.zone-cta`, `.admin-sidebar`, `footer`).

Un composant écrit avec les rôles s'adapte donc seul au fond sur lequel il est placé. Sur fond clair, l'or vif (`--or`) n'est pas assez contrasté pour du texte : `--accent` utilise `--or-dark`.

---

## 5. Architecture

### Arborescence

```
atelierDuMuseau/
├── .github/workflows/deploy.yml   Déploiement automatique (FTPS)
├── Controllers/                   Logique des pages
│   ├── Controller.php             Classe abstraite (render, CSRF, droits, flash)
│   ├── HomeController.php         Accueil, tarifs, mentions légales, CGV, confidentialité
│   ├── UserController.php         Inscription, connexion, profil
│   ├── DogController.php          Guide des races, chiens du client, gestion des races (admin)
│   ├── ReservationController.php  Réservations du client
│   └── AdminController.php        Tableau de bord, réservations, utilisateurs, chiens, races
├── Core/
│   ├── Routeur.php                Routeur avec liste blanche
│   ├── dbConnect.php              Singleton PDO
│   ├── config.php                 Lecture du .env, constantes, BASE_URL
│   ├── Mailer.php                 Envoi d'emails via PHPMailer
│   ├── Prestations.php            Catalogue des prestations et tarifs, gabarits, total estimé
│   ├── ReservationSchedule.php    Créneaux et demi-journées
│   └── PHPMailer/                 Bibliothèque PHPMailer
├── Entities/                      Objets métier : User, Dog, Breed, Reservation, ReservationPrestation
├── Models/                        Accès aux données : UserModel, DogModel, ReservationModel
├── Views/
│   ├── Autoloader.php             Autoloader PSR-4 (namespace Project\)
│   ├── home/                      Gabarit base.php + pages publiques et légales
│   ├── user/                      Connexion, inscription, profil, formulaire chien
│   ├── dog/                       Guide des races (liste, fiche)
│   ├── reservation/               Réservations du client
│   └── admin/                     Pages d'administration
├── public/                        Seul dossier exposé par Apache
│   ├── index.php                  Point d'entrée unique
│   ├── .htaccess                  Réécriture, HTTPS, www
│   ├── style.css, logo, favicons
│   ├── robots.txt, sitemap-pages.xml
│   └── uploads/                   Photos (breeds/, dogs/), hors Git
├── install.sql                    Création des tables + compte admin
├── referentiel_races.sql          Référentiel des 24 races
├── jeu_essai.sql                  Données de démonstration
├── .env.example                   Modèle de configuration
└── atelierDuMuseau.md             Ce document
```

### Cycle d'une requête

1. Apache envoie toute requête vers `public/index.php` (réécriture dans `.htaccess`).
2. `index.php` configure le cookie de session, démarre la session, charge l'autoloader et la connexion PDO.
3. Le [`Routeur`](Core/Routeur.php) lit `?controller=…&action=…` (par défaut `home` / `index`).
4. Il vérifie que le contrôleur figure dans la **liste blanche** et que l'action est une méthode **publique déclarée dans la classe concrète** (les méthodes héritées comme `render()` ou `redirect()` sont inaccessibles). Sinon : page 404.
5. Le contrôleur contrôle les droits (`requireUser()`, `requireAdmin()`), appelle les modèles, puis `render()`.
6. `render()` injecte le jeton CSRF, inclut la vue demandée, puis l'enveloppe dans `Views/home/base.php` (navigation, message flash, pied de page).

### Rôle de chaque couche

- **Contrôleurs** : valident les entrées, appliquent les règles de gestion, choisissent la vue.
- **Modèles** : seuls à exécuter du SQL (PDO préparé) et renvoient des entités.
- **Entités** : objets simples avec `fromArray()` et quelques méthodes utiles (`User::isAdmin()`, `Reservation::isPending()`, `Breed::hasFullSheet()`…).
- **Vues** : HTML avec échappement systématique (`htmlspecialchars`).

---

## 6. Base de données

Six tables InnoDB en `utf8mb4_unicode_ci`, définies dans [`install.sql`](install.sql).

La table des indisponibilités est intégrée à [`install.sql`](install.sql) pour les nouvelles installations. Sur une base existante où elle a déjà été ajoutée, aucune nouvelle opération SQL n'est nécessaire. Ne pas réimporter `install.sql`, qui réinitialise les tables. Pour une base existante qui ne possède pas encore cette table, exécuter uniquement son instruction `CREATE TABLE` en ajoutant `IF NOT EXISTS`, sans le `DROP TABLE`. Un éventuel chien Joker reste inchangé et n'est pas utilisé par cette fonctionnalité.

### `indisponibilite` — blocages administratifs
| Colonne | Type | Remarque |
|---|---|---|
| `id` | INT UNSIGNED, auto-incrément | Clé primaire |
| `date_seance` | DATE | |
| `seance` | ENUM | `matin` ou `apres_midi` ; couple date/séance unique |
| `motif` | VARCHAR(255) | Facultatif, interne ; aucune relation avec un chien ou un client |

### `dog_base` — référentiel des races
| Colonne | Type | Remarque |
|---|---|---|
| `id_race_PK` | INT UNSIGNED, auto-incrément | Clé primaire |
| `nom_race` | VARCHAR(100) | Unique |
| `poids` | DECIMAL(5,2) | Poids moyen |
| `photo_race` | VARCHAR(50) | Chemin de la photo |
| `description`, `entretien`, `historique`, `caracteristiques`, `astuces_toilettage` | TEXT | Contenu de la fiche |

### `user_dog` — utilisateurs
| Colonne | Type | Remarque |
|---|---|---|
| `id_utilisateur_PK` | INT UNSIGNED, auto-incrément | Clé primaire |
| `nom`, `prenom` | VARCHAR(100) | |
| `email` | VARCHAR(150) | Unique |
| `mdp` | VARCHAR(255) | Hash bcrypt |
| `id_role` | VARCHAR(20) | `user` (défaut) ou `admin` |
| `date_inscription` | DATETIME | Défaut : date courante |
| `adresse`, `code_postal`, `ville` | VARCHAR | Adresse d'intervention |
| `telephone` | VARCHAR(10) | |

### `chien` — chiens des clients
| Colonne | Type | Remarque |
|---|---|---|
| `id_chien_PK` | INT UNSIGNED, auto-incrément | Clé primaire |
| `nom`, `nom_race` | VARCHAR(100) | `nom_race` toujours renseigné (race du référentiel ou saisie libre) |
| `poids` | DECIMAL(5,2) | |
| `age` | DECIMAL(4,1) | Pas de 0,5 an |
| `sexe` | TINYINT(1) | `1` = mâle, `0` = femelle |
| `photo_chien` | VARCHAR(255) | Facultative |
| `id_user_FK` | INT UNSIGNED | → `user_dog`, `ON DELETE CASCADE` |
| `id_race_FK` | INT UNSIGNED, nullable | → `dog_base`, `ON DELETE SET NULL` |

### `reservation` — rendez-vous
| Colonne | Type | Remarque |
|---|---|---|
| `id_rdv` | INT UNSIGNED, auto-incrément | Clé primaire |
| `date_rdv` | DATE | |
| `heure_rdv` | TIME | |
| `statut` | VARCHAR(20) | `en attente` (défaut), `confirmé`, `annulé`, `terminé` |
| `informations` | TEXT, nullable | Texte facultatif, limité à 1 000 caractères par le formulaire et le serveur |
| `gabarit` | ENUM, nullable | `petit`, `moyen` ou `grand`, déduit du poids du chien au moment de la demande |
| `prix_estime` | DECIMAL(7,2), nullable | Total des prix fixes ; `NULL` si sur devis ou réservation antérieure aux prestations |
| `id_utilisateur_PK` | INT UNSIGNED | → `user_dog`, `ON DELETE CASCADE` |
| `id_chien_PK` | INT UNSIGNED | → `chien`, `ON DELETE CASCADE` |

### `reservation_prestation` — prestations demandées
| Colonne | Type | Remarque |
|---|---|---|
| `id` | INT UNSIGNED, auto-incrément | Clé primaire |
| `id_rdv_FK` | INT UNSIGNED | → `reservation`, `ON DELETE CASCADE` |
| `code` | VARCHAR(50) | Code du catalogue (`bain_brushing`, `coupe_griffes`…) |
| `libelle` | VARCHAR(150) | Libellé figé au moment de la demande |
| `type` | ENUM | `principale` (une par réservation) ou `supplement` |
| `prix` | DECIMAL(6,2), nullable | Prix figé ; `NULL` = sur devis |
| `evalue_sur_place` | TINYINT(1) | `1` pour les suppléments par tranche de 30 min, exclus du total estimé |

### Relations

```
user_dog 1 ──── N chien 1 ──── N reservation N ──── 1 user_dog
                  │
dog_base 1 ──── N ┘  (lien facultatif)

reservation 1 ──── N reservation_prestation
```

- Supprimer un utilisateur supprime ses chiens et ses réservations.
- Supprimer un chien supprime ses réservations.
- Supprimer une réservation supprime ses prestations.
- Supprimer une race du référentiel conserve les chiens (le lien devient `NULL`, le nom de la race reste dans `chien.nom_race`).

### Scripts SQL

| Fichier | Rôle | Attention |
|---|---|---|
| `install.sql` | Supprime et recrée les 6 tables, crée le compte admin | **Destructif** : uniquement sur base vide ou de test |
| `referentiel_races.sql` | Insère ou met à jour les 24 races, supprime les doublons | Non destructif pour les autres données |
| `jeu_essai.sql` | Deux comptes clients, trois chiens, six réservations de démonstration | À importer après les deux précédents, sur une base de test |

Comptes créés par les scripts (à utiliser **en local uniquement**) :
- `admin@atelierdumuseau.fr` — mot de passe d'installation à changer dès la première connexion.
- `client.a@test.fr`, `client.b@test.fr` — comptes de démonstration du jeu d'essai.

---

## 7. Routes et pages

URL de la forme `/index.php?controller=<c>&action=<a>`. L'accueil est servi sur `/`.

### Publiques
| Contrôleur / action | Page |
|---|---|
| `home` / `index` | Accueil |
| `home` / `tarifs` | Tarifs |
| `home` / `infosPratiques` | Infos pratiques (zone d'intervention) ; `zoneIntervention` redirige en 301 vers cette page |
| `home` / `mentionsLegales` | Mentions légales |
| `home` / `cgv` | Conditions générales de vente |
| `home` / `confidentialite` | Politique de confidentialité |
| `dog` / `index` | Guide des races |
| `dog` / `show&id=N` | Fiche d'une race |
| `user` / `login`, `register` | Connexion, inscription |

### Client connecté
| Contrôleur / action | Page |
|---|---|
| `user` / `profile`, `updateProfile` (POST), `logout` | Profil, mise à jour, déconnexion |
| `dog` / `addDog`, `editDog&id=N` | Ajout, modification d'un chien |
| `reservation` / `index`, `create`, `cancel` (POST) | Liste, création, annulation |

### Administrateur
| Contrôleur / action | Page |
|---|---|
| `admin` / `dashboard` | Tableau de bord |
| `admin` / `blockSession` (POST), `unblockSession` (POST) | Bloquer ou libérer une demi-journée (admin et CSRF obligatoires) |
| `admin` / `reservations`, `updateReservation` (POST) | Liste et changement de statut |
| `admin` / `users`, `deleteUser` (POST) | Utilisateurs |
| `admin` / `confirmDeletion` (paramètres `type=user` ou `type=dog`, `id`) | Confirmation renforcée avant suppression |
| `admin` / `dogs`, `dog` / `delete` (POST) | Chiens |
| `admin` / `breeds`, `dog` / `createBreed`, `editBreed&id=N`, `deleteBreed` (POST) | Races |

---

## 8. Emails

Envoyés par [`Core/Mailer.php`](Core/Mailer.php) (PHPMailer, SMTP authentifié, corps HTML UTF-8). Un échec d'envoi est journalisé mais **ne bloque jamais** l'action (réservation créée, statut modifié).

| Déclencheur | Destinataire | Contenu |
|---|---|---|
| Création d'une réservation | Client | Accusé de réception, demande en attente de validation, prestations demandées avec total estimé, et informations complémentaires si renseignées |
| Création d'une réservation | Administratrice (`ADMIN_NOTIFICATION_EMAIL`, sinon `MAIL_FROM_ADDRESS`) | Nouvelle demande à traiter, prestations demandées avec total estimé, et informations complémentaires si renseignées |
| Passage du statut à `confirmé` | Client | Rendez-vous confirmé (date, heure, prestations et total estimé) |

Le client n'est prévenu qu'au passage réel à `confirmé`, pas à chaque enregistrement du statut.

Dans les deux emails de création, les informations complémentaires sont échappées pour l'affichage HTML et les retours à la ligne sont conservés. La rubrique est masquée lorsque le champ est vide. Le test `php tests/reservation_informations.php` vérifie les deux emails avec un envoi simulé.

La rubrique « Prestations demandées » (gabarit, prestations avec leur prix figé, total estimé, mention des frais de déplacement et, le cas échéant, des suppléments évalués sur place) est générée par `Prestations::recapitulatifHtml()` et identique dans les trois emails. Elle est masquée pour les réservations antérieures à la fonctionnalité.

---

## 9. Sécurité

| Sujet | Mise en œuvre |
|---|---|
| Injection SQL | Requêtes préparées PDO partout, `ATTR_EMULATE_PREPARES` désactivé |
| Mots de passe | `password_hash` (bcrypt), `password_verify` à la connexion |
| CSRF | Jeton de session sur tous les formulaires POST, comparé avec `hash_equals` |
| XSS | `htmlspecialchars` dans les vues et les emails |
| Session | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS ; régénération de l'identifiant à la connexion |
| Droits | `requireUser()` / `requireAdmin()` en tête de chaque action protégée |
| Routeur | Liste blanche de contrôleurs ; seules les méthodes publiques propres au contrôleur sont appelables |
| Propriété des données | Un client ne peut réserver ou modifier que ses propres chiens ; il n'annule que ses propres réservations |
| Téléversement de photos | Taille maximale 5 Mo ; type vérifié par contenu (`finfo`) : JPEG, PNG ou WebP ; nom de fichier aléatoire |
| Secrets | Variables lues depuis `.env`, exclu de Git et du déploiement |
| Erreurs | `APP_ENV=production` masque les erreurs PHP et les écrit dans les journaux |
| Réseau | Redirection HTTPS et vers le domaine sans `www` sur le domaine de production ; listing des dossiers désactivé |
| Concurrence | Verrou MySQL lors de la création d'une réservation |
| Conditions générales | L'acceptation des CGV est contrôlée côté serveur à l'inscription |

---

## 10. SEO et pages légales

- Balises `<title>`, `<meta description>` et URL canonique définies par page (variables passées au gabarit).
- Données structurées JSON-LD `LocalBusiness` sur l'accueil.
- `robots.txt` : interdit l'exploration des contrôleurs `admin`, `user` et `reservation` ; déclare le sitemap.
- `sitemap-pages.xml` : accueil, tarifs, infos pratiques, guide des races, pages légales.
- Balises Open Graph sur la page Infos pratiques pour le partage.
- Pages légales rédigées pour l'activité : mentions légales (éditeur, hébergeur o2switch), CGV (10 articles), politique de confidentialité (données collectées, finalités, destinataires, durée, droits RGPD, cookie de session unique).

---

## 11. Configuration (`.env`)

Le fichier `.env` est lu au démarrage par `Core/config.php`. Il **ne doit jamais être commité** (déjà dans `.gitignore`) ni écrasé par le déploiement. Modèle : [`.env.example`](.env.example).

| Variable | Rôle | Exemple |
|---|---|---|
| `APP_ENV` | `development` affiche les erreurs, `production` les masque | `production` |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` | Connexion MySQL | `localhost`, `3306`, … |
| `BASE_URL` | Préfixe d'URL (vide si le site est à la racine du domaine) | *(vide)* |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` | Serveur SMTP | `587`, `tls` |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Expéditeur des emails | |
| `ADMIN_NOTIFICATION_EMAIL` | Destinataire des nouvelles demandes (défaut : `MAIL_FROM_ADDRESS`) | |

Les valeurs par défaut sont non sensibles : sans `.env`, le site tente `root` sans mot de passe sur `localhost` et masque les erreurs.

---

## 12. Installation en local

Prérequis : MAMP (Apache, MySQL, PHP 8).

1. Placer le projet dans `htdocs/atelierDuMuseau`.
2. Démarrer Apache et MySQL.
3. Dans phpMyAdmin, créer une base `atelier_museau` (`utf8mb4_unicode_ci`).
4. Importer dans l'ordre : `install.sql`, `referentiel_races.sql`, puis `jeu_essai.sql` si l'on veut des données de démonstration.
5. Copier `.env.example` en `.env`, mettre `APP_ENV=development` et renseigner les accès MySQL de MAMP (souvent `root` / `root`, port `8889`).
6. Ouvrir le site : l'URL de base est le dossier `public/` du projet. `BASE_URL` est déduite automatiquement.

Les emails ne partent que si les variables `MAIL_*` sont renseignées ; sinon les réservations fonctionnent mais un message signale l'échec d'envoi.

### Mise à jour d'une base existante

Les colonnes `informations`, `gabarit`, `prix_estime` et la table `reservation_prestation` sont intégrées à [`install.sql`](install.sql) pour les nouvelles installations, et déjà appliquées en local et en production. Ne pas réimporter `install.sql` sur une base existante : il supprime les tables. Le déploiement FTPS n'envoie ni n'exécute aucun fichier SQL.

Pour une base plus ancienne qui ne les possède pas encore, après sauvegarde et **avant** de déployer le code PHP : exécuter dans phpMyAdmin `ALTER TABLE reservation ADD COLUMN …` pour chaque colonne manquante (définitions reprises de `install.sql`), puis l'instruction `CREATE TABLE reservation_prestation` de `install.sql` avec `IF NOT EXISTS`, sans le `DROP TABLE`. Les anciennes réservations sont conservées, sans prestation. Sans ces colonnes, les pages de réservation échouent.

---

## 13. Déploiement automatique

Chaque `git push` sur la branche `main` met à jour le site sur o2switch, sans passer par le gestionnaire de fichiers de cPanel. Le workflow est défini dans [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml).

### Pourquoi FTPS et pas SSH
Chez o2switch, l'accès SSH n'est ouvert qu'aux adresses IP déclarées dans cPanel (5 maximum). Les serveurs de GitHub changent d'adresse à chaque exécution : le SSH est donc inutilisable. Le **FTPS** (port 21) n'a pas cette restriction.

### Fonctionnement
- Déclencheur : push sur `main`, ou lancement manuel (*Actions → Déploiement o2switch → Run workflow*).
- Action utilisée : `SamKirkland/FTP-Deploy-Action`.
- L'action compare avec le dernier état envoyé (fichier `.ftp-deploy-sync-state.json` sur le serveur) et n'envoie que les fichiers modifiés. Elle ne supprime pas les fichiers inconnus du dépôt.
- Fichiers **jamais envoyés** : `.git*`, `.github/`, `.env`, `.env.*`, `Core/config.php`, `public/uploads/`, `tests/` (tests de développement), tous les `*.sql`, tous les `*.md` (documentation), `.DS_Store`.

### Mise en place (déjà faite)
1. **cPanel → Comptes FTP** : compte dédié `deploy@…`, limité au répertoire `atelierDuMuseau`.
2. **GitHub → Settings → Secrets and variables → Actions** : quatre secrets.

| Secret | Contenu |
|---|---|
| `FTP_HOST` | Adresse du serveur FTP |
| `FTP_USER` | Nom complet du compte FTP |
| `FTP_PASSWORD` | Mot de passe du compte FTP |
| `FTP_DIR` | `./` (le compte FTP est déjà limité au dossier du projet) |

### Procédure de mise à jour
```bash
git add .
git commit -m "Description du changement"
git push origin main
```
Le suivi se fait dans l'onglet **Actions** du dépôt : coche verte = site à jour, croix rouge = ouvrir l'étape en échec.

### Ce que le déploiement ne fait pas
- **Base de données** : toute évolution du schéma se fait dans phpMyAdmin (les `.sql` ne sont pas envoyés).
- **Fichiers exclus** : une modification de `.env` ou de `Core/config.php` doit être reportée à la main sur le serveur.
- **Photos téléversées** : elles vivent uniquement sur le serveur (`public/uploads/`) et ne sont pas dans Git ; les inclure dans les sauvegardes du serveur.
- **Retour arrière** : refaire un commit correctif ou restaurer une sauvegarde cPanel ; il n'y a pas de rollback automatique.

### Structure sur le serveur
Le projet complet est dans `/home/<compte>/atelierDuMuseau/`. Le domaine pointe sur le sous-dossier `public/`, qui est donc le seul répertoire accessible depuis le web ; `Controllers/`, `Core/`, `Models/`, `Views/` et `.env` restent hors d'atteinte.

---

## 14. Exploitation et maintenance

| Tâche | Comment |
|---|---|
| Valider un rendez-vous | Admin → Réservations → changer le statut en `confirmé` (le client reçoit un email) |
| Ajouter ou corriger une race | Admin → Races |
| Modifier les tarifs ou les prestations | Éditer le catalogue `Core/Prestations.php` (prix par gabarit, suppléments, seuils de poids) : la page Tarifs, le formulaire de réservation et la validation sont mis à jour ensemble. Les réservations existantes gardent leurs prix d'origine |
| Modifier les textes légaux | Éditer `cgv.php`, etc., puis pousser sur `main` |
| Modifier les créneaux | Constantes de `ReservationController` (`SESSION_MATIN`, `SESSION_APRES_MIDI`, `CRENEAUX_AUTORISES`) **et** listes de `AdminController::updateReservation()` et de la vue `Views/reservation/index.php` |
| Changer le mot de passe admin | Se connecter avec le compte admin, ou régénérer un hash avec `password_hash()` et le mettre à jour dans `user_dog.mdp` |
| Sauvegarder | cPanel : sauvegarde des fichiers (dont `public/uploads/`) et export MySQL via phpMyAdmin |
| Diagnostiquer une erreur | Journaux d'erreurs PHP dans cPanel ; passer temporairement `APP_ENV=development` **en local uniquement** |

---

## 15. Limites connues et évolutions possibles

**À corriger ou à surveiller**
- La déconnexion se fait par un lien GET (`user/logout`) sans jeton CSRF.
- Pas de limitation du nombre de tentatives de connexion.
- Pas de réinitialisation de mot de passe par email, ni de vérification de l'adresse email à l'inscription.
- La modification d'un chien met à jour `nom_race` mais **pas** le lien `id_race_FK` vers le référentiel.
- `ReservationModel::deleteReservation()` existe mais n'est exposée dans aucune page.
- Les créneaux sont définis à plusieurs endroits (contrôleur, administration, vue) ; une constante partagée éviterait les écarts.
- Les anciennes photos de chiens ne sont pas supprimées du serveur lorsqu'elles sont remplacées.

**Évolutions envisageables**
- Création et modification de rendez-vous par l'administratrice.
- Rappel par email la veille du rendez-vous.
- Calendrier visuel des disponibilités côté client.
- Calcul d'un tarif indicatif selon la race (le gabarit et la prestation sont déjà pris en compte).
- En-têtes de sécurité HTTP (CSP, `X-Frame-Options`) via `.htaccess`.
- Tests automatisés et validation du déploiement (par exemple une vérification de syntaxe PHP dans le workflow avant l'envoi).
