-- floor_plan_polygons: разметка квартир на поэтажном плане (Stage 1, задача 4)
-- Полигоны в пиксельных координатах оригинала JPG плана этажа, система координат
-- как у facade_polygons (Leaflet CRS.Simple, y=0 у нижнего края).
CREATE TABLE IF NOT EXISTS `floor_plan_polygons` (
  `floor_plan_polygon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `home_id`               INT UNSIGNED NOT NULL,
  `section`               INT NOT NULL DEFAULT 1 COMMENT 'номер секции = homes_sections.section_id',
  `floor`                 INT NOT NULL,
  `apartament_id`         INT UNSIGNED NULL COMMENT 'FK soft -> apartaments.apartament_id',
  `apartment_num`         INT NULL COMMENT 'денормализация для быстрого матча/тултипа',
  `label`                 VARCHAR(64) NULL DEFAULT NULL,
  `points`                TEXT NOT NULL COMMENT 'JSON [[x,y],...] px оригинала JPG плана',
  `color`                 VARCHAR(16) NULL DEFAULT '#4da3ff',
  `sort_order`            INT NOT NULL DEFAULT 0,
  `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`                   TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`floor_plan_polygon_id`),
  KEY `idx_fpp_home_section_floor` (`home_id`, `section`, `floor`, `del`),
  KEY `idx_fpp_apartament` (`apartament_id`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
