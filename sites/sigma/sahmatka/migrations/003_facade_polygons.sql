-- facade_polygons: разметка этажей на фото фасада (полигоны в пиксельных координатах)
CREATE TABLE IF NOT EXISTS `facade_polygons` (
  `facade_polygon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `home_id`           INT UNSIGNED NOT NULL,
  `section`           INT NOT NULL DEFAULT 1 COMMENT 'номер секции = homes_sections.section_id',
  `floor`             INT NOT NULL,
  `label`             VARCHAR(64) NULL DEFAULT NULL,
  `points`            TEXT NOT NULL COMMENT 'JSON [[x,y],...] px относительно оригинала изображения',
  `color`             VARCHAR(16) NULL DEFAULT NULL,
  `sort_order`        INT NOT NULL DEFAULT 0,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`               TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`facade_polygon_id`),
  KEY `idx_facade_polygons_home` (`home_id`, `del`),
  KEY `idx_facade_polygons_home_section_floor` (`home_id`, `section`, `floor`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
