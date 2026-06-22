-- compred: вводный текст для публичной страницы
-- БД: m2profi_em

ALTER TABLE `compred`
  ADD COLUMN `intro_text` TEXT NULL DEFAULT NULL AFTER `caption`;
