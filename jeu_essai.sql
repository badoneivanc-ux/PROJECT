-- =============================================================================
-- Atelier du Museau - jeu d'essai non destructif pour la soutenance DWWM
-- A importer apres install.sql et referentiel_races.sql sur une base de test.
-- Une reimportation remplace uniquement les deux comptes de demonstration.
-- Les autres utilisateurs, chiens, reservations et races sont conserves.
-- =============================================================================

SET NAMES utf8mb4;
START TRANSACTION;

-- La suppression de ces comptes supprime uniquement leurs chiens et reservations
-- grace aux contraintes ON DELETE CASCADE.
DELETE FROM `user_dog`
WHERE `email` IN ('client.a@test.fr', 'client.b@test.fr');

-- Mot de passe des deux comptes : Test1234!
INSERT INTO `user_dog` (`nom`, `prenom`, `email`, `mdp`, `id_role`, `adresse`, `code_postal`, `ville`, `telephone`)
VALUES
    ('Dupont', 'Alice', 'client.a@test.fr', '$2y$12$v2i2YS.TiKgaLXsU.dmFqOnLCFXkWcXY.StojmUMqx2ZrioiWxcDO', 'user', '12 rue des Lilas', '94300', 'Vincennes', '0611111111'),
    ('Martin', 'Bruno', 'client.b@test.fr', '$2y$12$v2i2YS.TiKgaLXsU.dmFqOnLCFXkWcXY.StojmUMqx2ZrioiWxcDO', 'user', '5 avenue du Parc', '93200', 'Saint-Denis', '0622222222');

SET @client_a := (SELECT `id_utilisateur_PK` FROM `user_dog` WHERE `email` = 'client.a@test.fr');
SET @client_b := (SELECT `id_utilisateur_PK` FROM `user_dog` WHERE `email` = 'client.b@test.fr');
SET @race_labrador := (SELECT `id_race_PK` FROM `dog_base` WHERE `nom_race` = 'Labrador Retriever');
SET @race_bouledogue := (SELECT `id_race_PK` FROM `dog_base` WHERE `nom_race` = 'Bouledogue Français');

INSERT INTO `chien` (`nom`, `nom_race`, `poids`, `age`, `sexe`, `photo_chien`, `id_user_FK`, `id_race_FK`)
VALUES
    ('Rex', 'Labrador Retriever', 28.5, 3.0, 1, '', @client_a, @race_labrador),
    ('Nala', 'Bâtard', 9.0, 1.5, 0, '', @client_a, NULL),
    ('Milo', 'Bouledogue français', 11.0, 2.0, 1, '', @client_b, @race_bouledogue);

SET @chien_rex := (SELECT `id_chien_PK` FROM `chien` WHERE `nom` = 'Rex' AND `id_user_FK` = @client_a);
SET @chien_nala := (SELECT `id_chien_PK` FROM `chien` WHERE `nom` = 'Nala' AND `id_user_FK` = @client_a);
SET @chien_milo := (SELECT `id_chien_PK` FROM `chien` WHERE `nom` = 'Milo' AND `id_user_FK` = @client_b);

-- Lundi de la semaine suivant la semaine en cours (entre J+7 et J+13).
SET @demo_monday := DATE_ADD(CURDATE(), INTERVAL (((7 - WEEKDAY(CURDATE())) % 7) + 7) DAY);

INSERT INTO `reservation` (`date_rdv`, `heure_rdv`, `statut`, `id_utilisateur_PK`, `id_chien_PK`)
VALUES
    (DATE_SUB(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'terminé', @client_a, @chien_rex),
    (@demo_monday, '08:30:00', 'confirmé', @client_a, @chien_rex),
    (@demo_monday, '14:00:00', 'en attente', @client_b, @chien_milo),
    (DATE_ADD(@demo_monday, INTERVAL 3 DAY), '09:00:00', 'en attente', @client_a, @chien_nala),
    (DATE_ADD(@demo_monday, INTERVAL 7 DAY), '08:30:00', 'annulé', @client_b, @chien_milo),
    (DATE_ADD(@demo_monday, INTERVAL 7 DAY), '09:00:00', 'confirmé', @client_a, @chien_nala);

COMMIT;
