-- =============================================================================
-- Atelier du Museau — jeu d'essai pour la soutenance DWWM
-- À importer après install.sql, sur une base contenant déjà le compte admin.
-- Réimportable à tout moment pour repartir d'un état de démonstration propre :
--   mysql -u USER -p NOM_BASE < install.sql
--   mysql -u USER -p NOM_BASE < jeu_essai.sql
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- Nettoyage des données de démonstration précédentes (le compte admin est conservé)
-- -----------------------------------------------------------------------------
DELETE FROM `reservation`;
DELETE FROM `chien`;
DELETE FROM `user_dog` WHERE `email` != 'admin@atelierdumuseau.fr';
DELETE FROM `dog_base`;
ALTER TABLE `reservation` AUTO_INCREMENT = 1;
ALTER TABLE `chien` AUTO_INCREMENT = 1;
ALTER TABLE `dog_base` AUTO_INCREMENT = 1;

-- -----------------------------------------------------------------------------
-- Référentiel des races
-- -----------------------------------------------------------------------------
INSERT INTO `dog_base` (`nom_race`, `poids`, `description`, `entretien`)
VALUES
    ('Labrador', 30.00, 'Chien de famille robuste et affectueux, poil court dense.', 'Brossage hebdomadaire, mue importante deux fois par an.'),
    ('Bouledogue français', 12.00, 'Petit molosse au caractère joueur, très populaire en ville.', 'Nettoyage régulier des plis du visage, brossage doux.'),
    ('Caniche', 6.00, 'Chien intelligent au poil frisé, ne perd pas ses poils.', 'Tonte régulière indispensable toutes les 6 à 8 semaines.');

-- -----------------------------------------------------------------------------
-- Comptes clients de démonstration
-- Mot de passe des deux comptes : Test1234!
-- -----------------------------------------------------------------------------
INSERT INTO `user_dog` (`nom`, `prenom`, `email`, `mdp`, `id_role`, `adresse`, `code_postal`, `ville`, `telephone`)
VALUES
    ('Dupont', 'Alice', 'client.a@test.fr', '$2y$12$v2i2YS.TiKgaLXsU.dmFqOnLCFXkWcXY.StojmUMqx2ZrioiWxcDO', 'user', '12 rue des Lilas', '69000', 'Lyon', '0611111111'),
    ('Martin', 'Bruno', 'client.b@test.fr', '$2y$12$v2i2YS.TiKgaLXsU.dmFqOnLCFXkWcXY.StojmUMqx2ZrioiWxcDO', 'user', '5 avenue du Parc', '69100', 'Villeurbanne', '0622222222');

-- Identifiants réutilisés ensuite pour créer les chiens et les réservations
SET @client_a  := (SELECT `id_utilisateur_PK` FROM `user_dog` WHERE `email` = 'client.a@test.fr');
SET @client_b  := (SELECT `id_utilisateur_PK` FROM `user_dog` WHERE `email` = 'client.b@test.fr');
SET @race_labrador := (SELECT `id_race_PK` FROM `dog_base` WHERE `nom_race` = 'Labrador');
SET @race_bouledogue := (SELECT `id_race_PK` FROM `dog_base` WHERE `nom_race` = 'Bouledogue français');

-- -----------------------------------------------------------------------------
-- Chiens de démonstration
-- Rex : race + photo absente | Nala : sans race (rattachement facultatif) | Milo : race, sans photo
-- -----------------------------------------------------------------------------
INSERT INTO `chien` (`nom`, `nom_race`, `poids`, `age`, `sexe`, `photo_chien`, `id_user_FK`, `id_race_FK`)
VALUES
    ('Rex', 'Labrador', 28.5, 3.0, 1, '', @client_a, @race_labrador),
    ('Nala', 'Bâtard', 9.0, 1.5, 0, '', @client_a, NULL),
    ('Milo', 'Bouledogue français', 11.0, 2.0, 1, '', @client_b, @race_bouledogue);

SET @chien_rex  := (SELECT `id_chien_PK` FROM `chien` WHERE `nom` = 'Rex' AND `id_user_FK` = @client_a);
SET @chien_nala := (SELECT `id_chien_PK` FROM `chien` WHERE `nom` = 'Nala' AND `id_user_FK` = @client_a);
SET @chien_milo := (SELECT `id_chien_PK` FROM `chien` WHERE `nom` = 'Milo' AND `id_user_FK` = @client_b);

-- -----------------------------------------------------------------------------
-- Réservations de démonstration
-- Les dates sont calculées à l'import pour rester utilisables le jour de la soutenance.
-- @demo_monday désigne le lundi de la semaine suivant la semaine en cours.
-- -----------------------------------------------------------------------------
SET @demo_monday := DATE_ADD(CURDATE(), INTERVAL (((7 - WEEKDAY(CURDATE())) % 7) + 7) DAY);

INSERT INTO `reservation` (`date_rdv`, `heure_rdv`, `statut`, `id_utilisateur_PK`, `id_chien_PK`)
VALUES
    -- Rendez-vous passé, déjà honoré : alimente les statistiques du tableau de bord
    (DATE_SUB(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'terminé', @client_a, @chien_rex),
    -- Lundi matin occupé : sert à démontrer le refus d'un second rendez-vous le même matin
    (@demo_monday, '08:30:00', 'confirmé', @client_a, @chien_rex),
    -- Lundi après-midi : démontre que matin et après-midi sont des créneaux indépendants
    (@demo_monday, '14:00:00', 'en attente', @client_b, @chien_milo),
    -- En attente : utilisable pour démontrer l'annulation par le client propriétaire
    (DATE_ADD(@demo_monday, INTERVAL 3 DAY), '09:00:00', 'en attente', @client_a, @chien_nala),
    -- Annulée puis créneau repris par un autre client : démontre le refus de réactivation sur créneau occupé
    (DATE_ADD(@demo_monday, INTERVAL 7 DAY), '08:30:00', 'annulé', @client_b, @chien_milo),
    (DATE_ADD(@demo_monday, INTERVAL 7 DAY), '09:00:00', 'confirmé', @client_a, @chien_nala);
