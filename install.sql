-- =============================================================================
-- Atelier du Museau — script d'installation de la base de données
-- Généré à partir des requêtes SQL réellement utilisées dans les Models PHP.
-- ATTENTION : ce script supprime et recrée toutes les tables.
-- À utiliser uniquement sur une base vide ou une base de test à réinitialiser.
-- Utilisation : importer ce fichier via phpMyAdmin (onglet Importer)
--               ou en ligne de commande : mysql -u USER -p NOM_BASE < install.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Table dog_base — référentiel public des races
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `dog_base`;
CREATE TABLE `dog_base` (
    `id_race_PK`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nom_race`           VARCHAR(100)  NOT NULL,
    `poids`              DECIMAL(5,2)  NOT NULL,
    `photo_race`         VARCHAR(50)   DEFAULT NULL,
    `description`        TEXT,
    `entretien`          TEXT,
    `historique`         TEXT,
    `caracteristiques`   TEXT,
    `astuces_toilettage` TEXT,
    UNIQUE KEY `uq_nom_race` (`nom_race`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table user_dog — utilisateurs (clients et administrateurs)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_dog`;
CREATE TABLE `user_dog` (
    `id_utilisateur_PK` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nom`               VARCHAR(100) NOT NULL,
    `prenom`            VARCHAR(100) NOT NULL,
    `email`             VARCHAR(150) NOT NULL,
    `mdp`               VARCHAR(255) NOT NULL,
    `id_role`           VARCHAR(20)  NOT NULL DEFAULT 'user',
    `date_inscription`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `adresse`           VARCHAR(150) DEFAULT '',
    `code_postal`       VARCHAR(10)  DEFAULT '',
    `ville`             VARCHAR(100) DEFAULT '',
    `telephone`         VARCHAR(10)  NOT NULL,
    UNIQUE KEY `uq_email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table chien — chiens appartenant à un utilisateur
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `chien`;
CREATE TABLE `chien` (
    `id_chien_PK` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nom`         VARCHAR(100)  NOT NULL,
    `nom_race`    VARCHAR(100)  NOT NULL,
    `poids`       DECIMAL(5,2)  NOT NULL,
    `age`         DECIMAL(4,1)  NOT NULL,
    `sexe`        TINYINT(1)    NOT NULL,
    `photo_chien` VARCHAR(255)  DEFAULT '',
    `id_user_FK`  INT UNSIGNED  NOT NULL,
    `id_race_FK`  INT UNSIGNED  DEFAULT NULL,
    CONSTRAINT `fk_chien_user`
        FOREIGN KEY (`id_user_FK`) REFERENCES `user_dog` (`id_utilisateur_PK`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_chien_race`
        FOREIGN KEY (`id_race_FK`) REFERENCES `dog_base` (`id_race_PK`)
        ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table reservation — rendez-vous de toilettage
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `reservation`;
CREATE TABLE `reservation` (
    `id_rdv`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `date_rdv`           DATE         NOT NULL,
    `heure_rdv`          TIME         NOT NULL,
    `duree_minutes`      INT UNSIGNED NOT NULL DEFAULT 60,
    `statut`             VARCHAR(20)  NOT NULL DEFAULT 'en attente',
    `id_utilisateur_PK`  INT UNSIGNED NOT NULL,
    `id_chien_PK`        INT UNSIGNED NOT NULL,
    CONSTRAINT `fk_reservation_user`
        FOREIGN KEY (`id_utilisateur_PK`) REFERENCES `user_dog` (`id_utilisateur_PK`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_reservation_chien`
        FOREIGN KEY (`id_chien_PK`) REFERENCES `chien` (`id_chien_PK`)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- Compte administrateur de démonstration
-- Mot de passe : ChangeMoi123!  (à changer immédiatement après la première connexion)
-- Hash généré avec password_hash('ChangeMoi123!', PASSWORD_DEFAULT)
-- -----------------------------------------------------------------------------
INSERT INTO `user_dog` (`nom`, `prenom`, `email`, `mdp`, `id_role`, `telephone`)
VALUES (
    'Admin',
    'Atelier',
    'admin@atelierdumuseau.fr',
    '$2y$12$f0x.PrCCDFa.VT3QQqbieuO3UbegXZswzPQ9pnv4AzedoS8g/dyWC',
    'admin',
    '0600000000'
);
