-- genplan_polygons: цвет треугольника + кнопка тултипа (текст + URL + показ)
-- БД: m2profi_em

ALTER TABLE `genplan_polygons`
  ADD COLUMN `marker_color` VARCHAR(7) NULL DEFAULT NULL COMMENT 'hex #RRGGBB; NULL=авто по статусу / #009DFF без home_id' AFTER `link_url`,
  ADD COLUMN `cta_label` VARCHAR(160) NULL DEFAULT NULL COMMENT 'текст кнопки тултипа; NULL=Сообщить о старте продаж' AFTER `marker_color`,
  ADD COLUMN `cta_url` VARCHAR(512) NULL DEFAULT NULL COMMENT 'URL кнопки тултипа; якорь #id — на родительской странице' AFTER `cta_label`,
  ADD COLUMN `show_cta` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'показывать кнопку в тултипе' AFTER `cta_url`;
