-- À exécuter une seule fois sur une base déjà installée.
-- Ne pas réimporter install.sql, qui supprime puis recrée toutes les tables.
CREATE TABLE IF NOT EXISTS `password_reset` (
    `user_id`      INT UNSIGNED NOT NULL PRIMARY KEY,
    `token_hash`   CHAR(64)     DEFAULT NULL,
    `expires_at`   DATETIME     DEFAULT NULL,
    `requested_at` DATETIME     NOT NULL,
    UNIQUE KEY `uq_password_reset_token_hash` (`token_hash`),
    CONSTRAINT `fk_password_reset_user`
        FOREIGN KEY (`user_id`) REFERENCES `user_dog` (`id_utilisateur_PK`)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
