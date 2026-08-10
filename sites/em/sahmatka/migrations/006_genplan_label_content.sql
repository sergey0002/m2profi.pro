-- genplan_polygons Stage 2: content + title visibility + apt links (EM, task 10)
-- БД: m2profi_em
-- Ветка: feature/10-interactive-genplan

ALTER TABLE `genplan_polygons`
  ADD COLUMN `content` TEXT NULL COMMENT 'ручной HTML-контент тела маркера' AFTER `title`,
  ADD COLUMN `show_title_desktop` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'показывать title-bubble на desktop' AFTER `content`,
  ADD COLUMN `show_title_mobile` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'показывать title-bubble на mobile' AFTER `show_title_desktop`,
  ADD COLUMN `show_apt_links` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'ссылки на свободные квартиры по комнатам; только с home_id' AFTER `show_title_mobile`;
