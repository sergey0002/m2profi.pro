-- Добавить секцию к уже существующей таблице facade_polygons.
-- Идемпотентно: на свежей установке колонка/индекс уже созданы миграцией 003,
-- поэтому просто пропускаем шаг вместо падения с "Duplicate column/key name".

SET @dbname = DATABASE();
SET @tablename = 'facade_polygons';

-- Колонка `section`
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'section'
);
SET @sql_col = IF(
  @col_exists = 0,
  'ALTER TABLE `facade_polygons` ADD COLUMN `section` INT NOT NULL DEFAULT 1 COMMENT ''номер секции = homes_sections.section_id'' AFTER `home_id`',
  'SELECT 1'
);
PREPARE stmt_col FROM @sql_col;
EXECUTE stmt_col;
DEALLOCATE PREPARE stmt_col;

-- Индекс idx_facade_polygons_home_section_floor
SET @idx_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_facade_polygons_home_section_floor'
);
SET @sql_idx = IF(
  @idx_exists = 0,
  'ALTER TABLE `facade_polygons` ADD KEY `idx_facade_polygons_home_section_floor` (`home_id`, `section`, `floor`, `del`)',
  'SELECT 1'
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;
