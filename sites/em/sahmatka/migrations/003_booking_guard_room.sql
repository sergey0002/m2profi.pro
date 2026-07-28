-- booking_guard_room: кэш режима «ручное бронирование» по дому + типу квартир (rooms)
-- БД: m2profi_em
-- Задача: sites/em/.doc/tasks/7/doc.md + plan.md
--
-- Одна строка = один тип квартир (1K, 1C, 2K…) в одном доме.
-- is_manual_mode = 1 → онлайн-бронь агентов для типа запрещена (сообщение ОП в форме).
--
-- source = auto  (по умолчанию): пересчёт при посещении каталога объектов (action=objects)
--               is_manual_mode = (free_percent < threshold)
-- source = manual: админ вручную включил/выключил; auto-sync не меняет is_manual_mode
--                  (обновляет только снимки free_*/threshold_*)
--
-- Scope: только sites/em/sahmatka (БД m2profi_em). Не применять на sigma/etalon без отдельной задачи.
-- Аудит плана: sites/em/.doc/tasks/7/audit_plan.md

CREATE TABLE IF NOT EXISTS `booking_guard_room` (
  `booking_guard_room_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `home_id`             INT UNSIGNED NOT NULL COMMENT 'FK → homes.home_id',
  `rooms`               VARCHAR(16)  NOT NULL COMMENT 'Тип квартиры, как TRIM(apartaments.rooms): 1K, 1C, 2K, 3K, 3C, 4K…',
  `is_manual_mode`      TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = ручное бронирование включено для данного типа в доме',
  `source`              ENUM('auto','manual') NOT NULL DEFAULT 'auto' COMMENT 'auto = по порогу free%; manual = зафиксировано админом',
  `free_percent`        DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Доля свободных % на момент последнего пересчёта',
  `free_count`          INT UNSIGNED NULL DEFAULT NULL COMMENT 'Свободных квартир на момент пересчёта',
  `total_count`         INT UNSIGNED NULL DEFAULT NULL COMMENT 'Всего квартир типа на момент пересчёта',
  `threshold_percent`   DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Порог free% из booking_guard на момент пересчёта',
  `set_by_user_id`      INT UNSIGNED NULL DEFAULT NULL COMMENT 'users.id — кто включил/выключил вручную (NULL если source=auto)',
  `note`                VARCHAR(255) NULL DEFAULT NULL COMMENT 'Комментарий админа при ручном переключении',
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_guard_room_id`),
  UNIQUE KEY `uq_booking_guard_room` (`home_id`, `rooms`),
  KEY `idx_booking_guard_room_active` (`home_id`, `is_manual_mode`),
  KEY `idx_booking_guard_room_manual` (`is_manual_mode`, `source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Режим ручного бронирования по дому и типу квартир (rooms)';

-- Опционально (раскомментировать после проверки типов home_id в homes):
-- ALTER TABLE `booking_guard_room`
--   ADD CONSTRAINT `fk_booking_guard_room_home`
--   FOREIGN KEY (`home_id`) REFERENCES `homes` (`home_id`)
--   ON DELETE CASCADE ON UPDATE CASCADE;
