# Отчёт: замена «квартира» → «апартаменты» (sites/sigma)

Дата: 2026-05-25  
План: [name_appartament.md](./name_appartament.md)

---

## 1. Изменённые файлы (по каталогам)

### `sites/sigma/restapi/`

| Файл | Суть правок |
|------|-------------|
| `index.php` | Сообщения API: `unit_phrase('api_not_found')`, `unit_phrase('api_fetch_error')` |

### `sites/sigma/sahmatka/` (корень)

| Файл | Суть правок |
|------|-------------|
| `config.php` | Конфиг `$GLOBALS['config']['object_unit']`, хелперы `unit_*`, динамический `$kv_type` |
| `ajax_actions.php` | UI-строки через `unit_phrase()` / `unit_label()` |
| `cron_clear_expired_broni.php` | Письма и заголовки таблиц: `unit_phrase()`, `unit_num()`, `unit_label_cap()` |
| `csv_feed.php` | Заголовки колонок и описание фида |
| `display_home_public2.php` | Публичные подписи объектов |
| `form_exc_backoffice.php` | Форма бэк-офиса |
| `form_order.php` | Заголовки, сообщения бронирования |
| `form_order_custom.php` | Аналогично `form_order.php` |
| `iframe_apart.php` | Карточка, логи, письма |
| `prices_func.php` | Заголовки таблицы цен |
| `yandex_feedn.php` | Аббревиатура в описании (`unit_abbrev()`) |
| `yandex_feedx.php` | Аббревиатура в `<description>`; тег `<category>квартира</category>` **не тронут** (FEED) |

### `sites/sigma/sahmatka/actions/`

| Файл | Суть правок |
|------|-------------|
| `admin_object.php` | Статусы, логи, фильтры |
| `agadmin_object.php` | «Статусы …» |
| `broni.php` | Заголовки таблиц, статусы |
| `broni_history.php` | Колонки, статусы |
| `catalog.php` | Заголовок каталога |
| `novoselam.php` | **Только UI-строки** (порядок просмотра, номера); юридические блоки с «квартиры» / «многоквартирного дома» **не тронуты** |
| `obj_stat.php` | Статистика, счётчики |
| `show_broni.php` | Статусы, заголовки (закомментированные строки не менялись) |
| `stat_sale.php` | Свободные объекты, статус «подрядчик» |
| `stat_sale2.php` | Аналогично |
| `stat_sale_new.php` | Аналогично |
| `stat_salen2.php` | Таблицы sold/unsold/booked |
| `stat_salen3.php` | Колонка «№ …» |
| `stat_saler.php` | «Свободные …» |
| `stat_zapis.php` | Колонки, фильтры |
| `us_object.php` | «Статусы …» |

### `sites/sigma/sahmatka/fw/controllers/`

| Файл | Суть правок |
|------|-------------|
| `ctr__apartments.php` | Основной контроллер шахматки |
| `ctr__apartments_admin.php` | Админ-режим |
| `ctr__apartments_broni.php` | Бронирование |
| `ctr__econom.php` | Калькулятор наценки |
| `ctr__objects.php` | Логи смены статуса |
| `ctr__stat_econom.php` | Заголовки таблиц отчёта |
| `ctr__stat_econom_arh.php` | Архивный отчёт |
| `ctr__stat_lifecycle.php` | Жизненный цикл |
| `ctr__stat_sales_dynamic.php` | Динамика продаж |
| `ctr__stat_sales_velocity.php` | Скорость продаж |
| `ctr__uniajax.php` | AJAX-ответы |
| `ctr__zapis_stat.php` | Статистика записей |
| `ctr__zapiskeys.php` | Выдача ключей |
| `ctr__zapisx.php` | Запись на просмотр |
| `ctr__parking_floors.php` | **FIX:** `act__deltedrag()` — раскомментирован `update_for_key`, удаление места при drag |
| `ctr__parking_spaces.php` | **FIX:** текст письма «Бронирование места … м/м-…» вместо «квартира/кв.» |

### `sites/sigma/sahmatka/fw/templates/apartments/`

| Файл | Суть правок |
|------|-------------|
| `broni_history.php` | Заголовки |
| `form_broni_ag.php` | Форма брони (агент) |
| `form_broni_pub.php` | Публичная форма |
| `form_broni_done.php` | Подтверждение |
| `form!_broni_ag.php` | Альт. форма |
| `public_card.php` | Публичная карточка |

### `sites/sigma/sahmatka/fw/templates/zapiskeys/`

| Файл | Суть правок |
|------|-------------|
| `index_table.php` | Колонки таблицы |
| `zapis_card.php` | Строка «… - №» через `unit_label_cap('nom')`; **памятка (LEGAL) не тронута** |
| `zapis_card2.php` | Строка «… - №» через `unit_label_cap('nom')`; **памятка (LEGAL) не тронута** |

### `sites/sigma/sahmatka/template/default/`

| Файл | Суть правок |
|------|-------------|
| `in_head.php` | Пункт меню «Статистика …» |

---

## 2. Конфигурация и хелперы (`config.php`)

```php
$GLOBALS['config']['object_unit'] = [
    'mode'   => 'custom',
    'label'  => 'апартаменты',   // не склоняется
    'abbrev' => 'ап.',
    'legacy' => [ /* квартира, квартиры, … кв. */ ],
];
```

| Функция | Назначение |
|---------|------------|
| `unit_cfg()` | Весь конфиг |
| `unit_label($case)` | Подпись (`nom`, `gen`, `pl_gen`, …) |
| `unit_label_cap($case)` | С заглавной буквы |
| `unit_abbrev()` | `ап.` или `кв.` (legacy) |
| `unit_num($num, $style)` | `ап. 12`, `ап-12`, `ап.№12`, `ап:12` |
| `unit_rate_abbrev()` | `ап/мес` |
| `unit_phrase($key)` | ~50 готовых фраз для UI/API/писем |
| `unit_room_type($rooms)` | `N-комн. апартаменты` (custom) |

Переключение на legacy: `'mode' => 'legacy'` — все хелперы вернут «квартира» / «кв.».

---

## 3. Сводка по объёму

| Категория | Кол-во |
|-----------|--------|
| Файлов с `unit_*` (миграция терминологии) | **52** |
| Доп. правки parking (без `unit_*`) | **2** |
| **Итого затронуто** | **54** |
| Пропущено (LEGAL) | ~15 блоков в `novoselam.php`, `zapis_card.php`, `zapis_card2.php` |
| Пропущено (FEED) | `avito_feedx.php`, `domclick_*`, `<category>` в Yandex |
| Пропущено (прочее) | `reglament.php`, `oferta/*`, дубликаты `!ctr__stat_econom.php`, корневой `ctr__stat_econom.php` |
| Только комментарии | `stat_salen.php`, `show_broni.php`, `ctr__parking_spaces.php` L581, `uniajax.php` |

---

## 4. Намеренно не изменено

- **«кв. м»** — единица площади, не путать с «кв.»
- **Юридические тексты:** акты приёма-передачи, ДДУ, многоквартирный дом, постановления
- **XML-фиды:** `<ObjectType>`, `<Status>`, `<category>квартира</category>` — требования площадок
- **Закомментированный код** с «квартира» (история, отладка)
- **Имена таблиц/полей:** `apartaments`, `apartment_num` — без изменений

---

## 5. Чек-лист проверки

- [ ] Шахматка (`ctr__apartments`): заголовки, статусы, бронирование — «апартаменты», «ап.»
- [ ] Каталог, статистика продаж, econom — таблицы и фильтры
- [ ] Формы брони (публичная / агент / iframe)
- [ ] Запись на ключи: поле «Апартаменты - №» (не LEGAL-блок)
- [ ] REST API: 404/500 с «Апартаменты не найдены…»
- [ ] Cron письма об истечении брони
- [ ] Yandex feed: в description «ап.», category без изменений
- [ ] Parking floors: drag в «Удаление» → `del=1` в БД
- [ ] Parking spaces: письмо «м/м-», не «кв.»
- [ ] Переключение `mode=legacy` → интерфейс снова «квартира» / «кв.»

---

## 6. Примечания

1. Слово **«апартаменты»** в режиме `custom` не склоняется — во всех падежах одна форма.
2. Аббревиатура по умолчанию: **«ап.»**; legacy: **«кв.»**
3. PHP syntax check локально не выполнялся (`php` не в PATH); рекомендуется проверить через Laragon PHP.
4. Отдельная задача (вне scope): `ctr__stat_econom.php` L93 — строка «Остатки…» захардкожена, не через `unit_phrase()`.
