-- Задача 8: срок сдачи домов (квартал + год)
-- БД: m2profi_em
-- Ветка: feature/8-free-apartments-report
--
-- Где задаётся в UI (Настройки → Настройки объектов → ctr=homeseditor):
--   1) homes.delivery_date     — «Дата сдачи» (date picker) — основной для отчёта/сортировки
--   2) homes.ready_quarter     — «квартал сдачи (Домлик)» (1|2|3|4)
--   3) homes.built_year        — «Год сдачи (Домлик)»
--
-- В отчёте «Свободные квартиры» выводим ТОЛЬКО квартал + год (например «III 2026»),
-- не полный календарный день. Для хранения delivery_date используется
-- ПЕРВЫЙ ДЕНЬ НАЧАЛА КВАРТАЛА:
--   I   → YYYY-01-01
--   II  → YYYY-04-01
--   III → YYYY-07-01
--   IV  → YYYY-10-01
--
-- Параллельно обновляем ready_quarter + built_year (Домлик), чтобы поля не расходились.
-- Ключ домов: homes.title (907, 909, …), не home_id.

UPDATE homes
SET
  delivery_date = '2026-07-01',
  ready_quarter = 3,
  built_year = 2026
WHERE title = '907';

UPDATE homes
SET
  delivery_date = '2026-10-01',
  ready_quarter = 4,
  built_year = 2026
WHERE title = '909';

UPDATE homes
SET
  delivery_date = '2026-10-01',
  ready_quarter = 4,
  built_year = 2026
WHERE title = '713';

UPDATE homes
SET
  delivery_date = '2026-10-01',
  ready_quarter = 4,
  built_year = 2026
WHERE title = '905';

UPDATE homes
SET
  delivery_date = '2027-01-01',
  ready_quarter = 1,
  built_year = 2027
WHERE title = '910';

UPDATE homes
SET
  delivery_date = '2027-01-01',
  ready_quarter = 1,
  built_year = 2027
WHERE title = '912';
