-- genplan_polygons: объекты интерактивного генплана ЖК (EM, task 10)
CREATE TABLE IF NOT EXISTS `genplan_polygons` (
  `genplan_polygon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kvartal_id`         INT UNSIGNED NOT NULL COMMENT 'homes_kvartal.homes_kvartal_id',
  `home_id`            INT UNSIGNED NULL DEFAULT NULL COMMENT 'опц. soft FK homes.home_id; status/meta LIVE из homes',
  `title`              TEXT NOT NULL COMMENT 'ручной HTML-заголовок; обязателен если home_id IS NULL',
  `link_url`           VARCHAR(512) NULL DEFAULT NULL COMMENT 'свободный URL клика',
  `label_x`            INT NULL DEFAULT NULL COMMENT 'якорь label px; NULL=центроид',
  `label_y`            INT NULL DEFAULT NULL COMMENT 'CRS.Simple y=0 у низа',
  `points`             TEXT NOT NULL COMMENT 'JSON [[x,y],…] px оригинала',
  `sort_order`         INT NOT NULL DEFAULT 0,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`                TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`genplan_polygon_id`),
  KEY `idx_gp_kvartal` (`kvartal_id`, `del`),
  KEY `idx_gp_home` (`home_id`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
