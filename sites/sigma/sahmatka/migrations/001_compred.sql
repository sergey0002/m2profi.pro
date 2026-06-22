-- compred: коммерческие предложения (MVP — квартиры)
-- БД: m2profi_em

CREATE TABLE IF NOT EXISTS `compred` (
  `compred_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `caption`      VARCHAR(255) NOT NULL,
  `intro_text`   TEXT NULL DEFAULT NULL,
  `user_id`      INT UNSIGNED NOT NULL,
  `share_token`  CHAR(32) NULL DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`          TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`compred_id`),
  UNIQUE KEY `uq_compred_share_token` (`share_token`),
  KEY `idx_compred_user` (`user_id`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `compred_obj` (
  `compred_obj_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `compred_id`     INT UNSIGNED NOT NULL,
  `obj_type`       VARCHAR(32) NOT NULL DEFAULT 'apartment',
  `obj_id`         INT UNSIGNED NOT NULL,
  `comment`        TEXT NULL,
  `sort_order`     INT NOT NULL DEFAULT 0,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`compred_obj_id`),
  UNIQUE KEY `uq_compred_obj` (`compred_id`, `obj_type`, `obj_id`),
  KEY `idx_compred_obj_compred` (`compred_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
