# Замена «квартира» → настраиваемое название объекта (Sigma)

**Проект:** `sites/sigma`  
**Дата аудита:** 2026-05-25  
**Цель:** заменить пользовательские тексты «квартира / квартиры / квартир / квартиру» и сокращения **«кв.»** на значение из `config.php` (для Sigma — **«апартаменты»**, сокращение **«ап.»**, без склонения), сохранив возможность вернуть классическую терминологию через `mode=legacy`.

---

## 1. Краткое резюме аудита

| Метрика | Значение |
|---------|----------|
| Файлов с вхождениями (код) | **~75 PHP** + txt/csv/oferta |
| Строк с совпадениями | **~340** (слово «квартир*») + **~20** (сокращения `кв.`/`кв-`) |
| К замене через helper (UI+ABBREV) | **~185** |
| Не трогать (LEGAL+FEED+SKIP) | **~155** |
| FIX (не терминология, а контекст) | **4 файла, 8 строк** |
| Область поиска | `sites/sigma/**/*.{php,js,html,htm,txt,csv,md}` |
| Паттерн слова | `[Кк]вартир` (все формы слова) |
| Паттерн сокращений | `\bкв\.`, `кв-`, `к\. кв\.`, `кв/мес`, `№ кв\.` — **не** `кв. м` (м²) |

**Важно:** таблица БД `apartaments`, поля `apartment_num`, URL `ctr=apartments` — **не меняются**. Меняется только **отображаемый текст**.

### Статус

| Компонент | Статус |
|-----------|--------|
| План и реестр вхождений | ✅ |
| **Детальный аудит файлов** | ✅ §11 (2026-05-25) |
| Код (`config.php`, helpers, замены) | ⏳ не начато |

---

## 2. Архитектура конфигурации

### 2.1. Блок в `sahmatka/config.php`

Добавить после существующих `$kv_type` (или рядом с ними):

```php
/**
 * Название объекта недвижимости в интерфейсе.
 *
 * mode:
 *   legacy  — склоняемое «квартира» (формы через helper)
 *   custom  — одно слово «апартаменты» из label, без склонения (Sigma)
 *   abbrev  — «ап.» (legacy: «кв.»)
 */
$GLOBALS['config']['object_unit'] = [
    'mode'  => 'custom',
    'label' => 'апартаменты',
    'abbrev' => 'ап.',
    'legacy' => [
        'nom'    => 'квартира',
        'gen'    => 'квартиры',
        'dat'    => 'квартире',
        'acc'    => 'квартиру',
        'ins'    => 'квартирой',
        'pl_nom' => 'квартиры',
        'pl_gen' => 'квартир',
        'pl_acc' => 'квартиры',
        'abbrev' => 'кв.',
    ],
];
```

**Для Sigma (текущие настройки):**

```php
$GLOBALS['config']['object_unit'] = [
    'mode'   => 'custom',
    'label'  => 'апартаменты',
    'abbrev' => 'ап.',
];
```

**Для сайтов с классической терминологией:**

```php
$GLOBALS['config']['object_unit'] = ['mode' => 'legacy'];
```

### 2.2. Helper-функции (план — добавить в `config.php` на фазе 0)

| Функция | Назначение | Пример (Sigma) |
|---------|------------|----------------|
| `unit_label($case)` | Полное название | `апартаменты` (все падежи) |
| `unit_label_cap($case)` | С заглавной | `Апартаменты` |
| `unit_abbrev()` | Сокращение | `ап.` |
| `unit_num($num, $style)` | Номер объекта | `unit_num(12)` → `ап. 12`; `dash` → `ап-12`; `colon` → `ап:12` |
| `unit_rate_abbrev()` | Единица скорости | `ап/мес` |
| `unit_phrase($key)` | Готовые фразы UI | `unit_phrase('column_header')` |
| `unit_room_type($n)` | Тип по комнатам | `1-комн. апартаменты` |

```php
// unit_abbrev() — «ап.» (custom) или «кв.» (legacy)

// unit_num(12, 'dot')   → «ап. 12»
// unit_num(12, 'dash')  → «ап-12»     (без точки — как legacy «кв-12»)
// unit_num(12, 'hash')  → «ап.№12»
// unit_num(12, 'colon') → «ап:12»     (тема письма)
```

**⚠️ Аудит:** для стиля `dash` helper должен отрезать точку у `abbrev` (`ап.` → `ап-`), иначе получится `ап.12` вместо `ап-12`.

**⚠️ Не заменять:** `кв. м` — квадратные метры в Yandex-фидах (`<unit>кв. м</unit>`).

### 2.3. Использование в PHP-шаблонах

| Было | Стало (Sigma) |
|------|---------------|
| `Квартира` | `<?= unit_label_cap('nom') ?>` → **Апартаменты** |
| `Квартиры` | `<?= unit_label_cap('nom') ?>` → **Апартаменты** |
| `Выбрать квартиру` | `<?= unit_phrase('select') ?>` → **Выбрать апартаменты** |
| `Квартира не найдена` | `<?= unit_phrase('not_found') ?>` |
| `Квартира подрядчика` | `<?= unit_phrase('contractor') ?>` |
| `№ кв.` / `кв. 12` | `<?= unit_phrase('num_column') ?>` / `<?= unit_num(12) ?>` |
| `кв-12` в email | `<?= unit_num(12, 'dash') ?>` → **ап-12** |
| `кв.:12` в теме письма | `<?= unit_num(12, 'colon') ?>` → **ап:12** |
| `Продано N кв.` | `Продано <?= $n ?> <?= unit_abbrev() ?>` → **ап.** |
| `кв/мес` | `<?= unit_rate_abbrev() ?>` → **ап/мес** |
| `<option value="">Квартира</option>` | `<option value=""><?= unit_label_cap('nom') ?></option>` |

### 2.4. Использование в контроллерах

```php
// Было:
var $title = 'Квартиры';
// Стало:
var $title; // в __construct: $this->title = unit_label_cap('pl_nom');

// Было:
$titles['apartment_num'] = 'Квартира';
// Стало:
$titles['apartment_num'] = unit_label_cap('nom');

// Было:
$done_message = "Квартира успешно забронирована!";
// Стало:
$done_message = unit_phrase('booked_success') . '!';
```

### 2.5. JavaScript (опционально, фаза 2)

В `template/default/in_head.php` или отдельном inline-блоке после `config.php`:

```php
<script>
window.UNIT_LABEL = {
    nom: <?= json_encode(unit_label('nom'), JSON_UNESCAPED_UNICODE) ?>,
    nomCap: <?= json_encode(unit_label_cap('nom'), JSON_UNESCAPED_UNICODE) ?>,
    abbrev: <?= json_encode(unit_abbrev(), JSON_UNESCAPED_UNICODE) ?>,
};
</script>
```

### 2.6. `$kv_type` — план (фаза 0)

В custom-режиме: `1-комн. апартаменты`, `2-комн. апартаменты` … через `unit_room_type()`.

### 2.7. Реестр сокращений «кв.» → `unit_abbrev()` (фаза 1–2)

| Файл | Строка | Было | Стало |
|------|--------|------|-------|
| `fw/controllers/ctr__zapisx.php` | 832 | ` кв.'.$num` | ` unit_num($num)` |
| `fw/controllers/ctr__zapisx.php` | 1099–1102 | ` кв.:` в теме mail | ` unit_num($num, 'colon')` |
| `fw/controllers/ctr__stat_econom.php` | 257,261 | ` кв.` | ` unit_abbrev()` |
| `fw/controllers/!ctr__stat_econom.php` | 247,251 | ` кв.` | ` unit_abbrev()` |
| `fw/controllers/ctr__stat_econom_arh.php` | 671,817 | `кв/мес`, ` кв.` | `unit_rate_abbrev()`, `unit_abbrev()` |
| `fw/controllers/ctr__stat_sales_dynamic.php` | 68 | ` кв /` | ` unit_abbrev()` |
| `ctr__stat_econom.php` | 257,261 | ` кв.` | ` unit_abbrev()` |
| `iframe_apart.php` | 255 | ` кв-` | ` unit_num($n,'dash')` |
| `form_order.php` | 44 | ` кв-` | ` unit_num($n,'dash')` |
| `form_order_custom.php` | 417 | ` кв-` | ` unit_num($n,'dash')` |
| `fw/controllers/ctr__stat_sales_dynamic.php` | 68 | ` кв /` | `unit_abbrev()` + `/` |
| `fw/controllers/ctr__parking_spaces.php` | 662 | ` кв-` | FIX: `м/м-` (не unit_num — другое сущность) |
| `cron_clear_expired_broni.php` | 64,134,136 | `кв.№`, «квартиру» | ABBREV + UI в теле письма |
| `yandex_feedx.php` | 118 | ` к. кв.` | ` к. ` + `unit_abbrev()` (не `кв. м`!) |
| `yandex_feedn.php` | 90 | ` к. кв.` | аналогично |
| `actions/sdan.txt`, `sdan.txt` | 1 | `№ кв.` | `unit_phrase('num_column')` |

**Не трогать:** `oferta/*.htm` — «кв. 7» в юридическом адресе; `<unit>кв. м</unit>` в фидах.

---

## 3. Правила классификации вхождений

| Класс | Действие | Примеры |
|-------|----------|---------|
| **A — UI / интерфейс** | Заменить через `unit_label()` / `unit_phrase()` | Меню, заголовки таблиц, `<option>`, сообщения бронирования |
| **B — Пользовательские тексты** | Заменить с ревью | `reglament.php`, части `novoselam.php` (не юридические термины) |
| **C — Юридические / нормативные** | **Не менять** | «многоквартирного дома», «ДДУ», «акт приема-передачи квартиры» в офертах |
| **D — Внешние фиды (Avito, DomClick, Yandex)** | **Не менять** или вынести в конфиг `feed_use_legacy_terms=true` | `<ObjectType>Квартира</ObjectType>` — требование площадки |
| **E — Комментарии / TODO / import** | Не трогать (низкий приоритет) | `// статус квартиры`, `import/new.php` |
| **F — Ошибочные копипасты** | Исправить текст на корректный для контекста | `ctr__parking_*` — «квартиры» вместо «парковочных мест» |
| **G — CSV / email subject** | Заменить через helper | `csv_feed.php`, темы писем |

### 3.1. Юридические исключения (не заменять)

Фразы, где «квартира» — термин закона или договора:

- **многоквартирного дома** — термин ГК/214-ФЗ  
- **акт приема-передачи квартиры** — в офертах и `novoselam.php` (если юрист не согласовал замену)  
- **участник долевого строительства (покупатель) квартиры** — в `zapis_card.php`  
- файлы `oferta/*.htm` — только после согласования с юристом  

### 3.2. Рекомендуемый флаг для юридических блоков

```php
$GLOBALS['config']['object_unit']['keep_legal_terms'] = true; // default true
```

Helper `unit_label_legal($case)` — всегда возвращает legacy-формы независимо от mode.

---

## 4. План внедрения по фазам

### Фаза 0 — Инфраструктура (0.5 дня)

1. Добавить блок `$GLOBALS['config']['object_unit']` в `sahmatka/config.php`
2. Добавить функции `unit_label()`, `unit_label_cap()`, `unit_phrase()`
3. Подключить helper во всех точках входа: `config.php` (уже include), `ajax_router.php`, `ctrind.php`, `iframe_router.php`
4. Smoke-тест: `<?= unit_label_cap('nom') ?>` на тестовой странице

### Фаза 1 — Ядро UI (1–2 дня, ~40 замен)

Приоритетные файлы с наибольшим пользовательским эффектом:

| Файл | Вхождений UI | Метод |
|------|--------------|-------|
| `fw/controllers/ctr__apartments.php` | 27 | `$title`, `$titles`, echo-сообщения → helper |
| `fw/controllers/ctr__apartments_admin.php` | 5 | `$title`, filtr, status option |
| `fw/controllers/ctr__apartments_broni.php` | 3 | `$title`, `$titles`, option |
| `iframe_apart.php` | **19** (16 UI + 1 ABBREV + 2 SKIP) | labels, errors, `<th>`, options |
| `fw/templates/apartments/*.php` | **18** (16 UI + 2 SKIP-комментария) | все видимые «Квартира» |
| `template/default/in_head.php` | 1 | пункт меню |
| `fw/controllers/ctr__uniajax.php` | 3 | `<option>` |
| `fw/controllers/ctr__zapisx.php` | **13** (8 UI + 3 ABBREV + 2 SKIP) | select, option, `кв.` L832, mail L1099–1102 |
| `fw/controllers/ctr__zapiskeys.php` | 4 | заголовки, `$titles` |
| `fw/templates/zapiskeys/*.php` | 13 | таблица + карточки (UI; юридический текст — класс C) |

### Фаза 2 — Legacy actions + формы (1–2 дня, ~80 замен)

| Файл | Вхождений | Метод |
|------|-----------|-------|
| `actions/admin_object.php` | 12 | UI labels; комментарии — skip |
| `actions/show_broni.php` | 8 | `<th>`, итог «Квартир:» |
| `actions/broni_history.php` | 7 | `<th>`, status text |
| `actions/stat_sale*.php` | ~25 | `$s_arr[6]`, заголовки |
| `actions/stat_salen*.php` | ~15 | заголовки колонок |
| `actions/obj_stat.php` | 3 | «Квартир не продано» |
| `actions/catalog.php` | 1 | H1 |
| `form_order.php` | **8** (3 UI + 1 ABBREV + 4 SKIP) | L542,654 UI; L44 `кв-`; L48–49 тема письма |
| `form_order_custom.php` | **6** (3 UI + 1 ABBREV + 2 SKIP) | L439,517 UI; L417 `кв-`; L420–421 тема |
| `ajax_actions.php` | 2 | placeholder |
| `prices_func.php` | 4 | `<th>` |

### Фаза 3 — Статистика и экономика (1 день, ~35 замен)

| Файл | Вхождений |
|------|-----------|
| `fw/controllers/ctr__econom.php` | **10** | +L211 «К СВОБОДНЫМ КВАРТИРАМ» |
| `fw/controllers/ctr__stat_econom.php` | **4** | ⚠️ L90–92 уже «апартаменты» — заменить на helper; L257,261 `кв.` |
| `fw/controllers/!ctr__stat_econom.php` | **7** | **не грузится роутером** — архив/бэкап |
| `fw/controllers/ctr__stat_econom_arh.php` | **12** | +L671 `кв/мес` |
| `fw/controllers/ctr__stat_lifecycle.php` | 2 |
| `fw/controllers/ctr__stat_sales_dynamic.php` | **2** | L68 ` кв /` + L181 |
| `fw/controllers/ctr__stat_sales_velocity.php` | 2 |
| `sahmatka/ctr__stat_econom.php` (корень) | **7** | **мёртвая копия** — не грузится; удалить или синхронизировать |
| `actions/stat_saler.php` | 6 |

### Фаза 4 — Запись на ключи, новосёлы, регламент (1 день, ревью)

| Файл | Вхождений | Примечание |
|------|-----------|------------|
| `actions/novoselam.php` | **37** | UI ~14 / LEGAL ~23 — см. §11.3 |
| `fw/templates/zapiskeys/zapis_card.php` | 7 | 1 UI + 6 LEGAL |
| `reglament.php` | 5 | **LEGAL целиком** — не менять без юриста |
| `fw/controllers/ctr__zapis_stat.php` | 2 | заголовки колонок |

### Фаза 5 — Feeds, API, прочее (0.5 дня)

| Файл | Вхождений | Действие |
|------|-----------|----------|
| `avito_feedx.php` | 6 | XML-теги — **не менять**; описание `$desc` — опционально |
| `domclick_feedx.php`, `domclick_feedn.php` | 11 | комментарии + marketing text — ревью |
| `yandex_feedx.php`, `yandex_feedn.php` | **4+3** | L75 `<category>квартира</category>` FEED; L118/90 `к. кв.` ABBREV; L121/93 `кв. м` SKIP |
| `csv_feed.php` | 9 | заголовки CSV + email subject → helper |
| `restapi/index.php` | 9 | JSON error messages → helper; PHPDoc — skip |
| `cron_clear_expired_broni.php` | **5** | +L134,136 текст письма «квартиру/квартира» |
| `oferta/*.htm` | ~24 | **не трогать** без юриста |
| `keysbase/arh.csv` | 3 | заголовок колонки — helper при экспорте |

### Фаза 6 — Исправление копипаст (0.5 дня)

| Файл | Проблема |
|------|----------|
| `fw/controllers/ctr__parking_floors.php:153,171` | комментарий «квартиры» → «парковочные места» |
| `fw/controllers/ctr__parking_spaces.php:581,660,662` | FIX: текст про «бронирование квартиры» + `кв-` в parking-контексте → «м/м» |
| **`fw/controllers/ctr__parking_broni.php:90`** | **добавлено:** комментарий «КВАРТИРЫ» → «парковочные места» |
| `fw/controllers/ctr__rentbroni.php:89` | комментарий «КВАРТИРЫ» → «объектов аренды» |

---

## 5. Полный реестр вхождений по файлам

> **Легенда колонки «Действие»:**  
> `UI` — заменить helper | `LEGAL` — не менять | `SKIP` — комментарий/import | `FIX` — исправить контекст | `FEED` — фид/API

### 5.1. `sahmatka/config.php` (8)

| Строка | Текст | Действие |
|--------|-------|----------|
| 192–195 | `Однокомнатная квартира` … | UI — фаза 0, `$kv_type` |
| 202–205 | `Однокомнатная квартира` (к-keys) | UI — фаза 0 |

### 5.2. Контроллеры `fw/controllers/` (98)

#### `ctr__apartments.php` (27)

| Строка | Фрагмент | Действие |
|--------|----------|----------|
| 12 | `var $title = 'Квартиры'` | UI |
| 385 | `Квартир соответствующих условиям…` | UI |
| 421 | `Номера квартир:` | UI |
| 444 | `Не указан ID квартиры` | UI |
| 448,452,457 | история броней по квартире | UI |
| 490,697,716 | комментарии | SKIP |
| 703,1046 | `Квартира не найдена` | UI → `unit_phrase('not_found')` |
| 738 | `Квартира снова доступна…` | UI |
| 771,776 | бронь на эту квартиру | UI |
| 801–802 | статус квартиры изменён | UI |
| 816,819 | квартира свободна / забронирована | UI |
| 850 | `Квартира успешно забронирована` | UI |
| 907,934 | status_map `Квартира подрядчика` | UI |
| 981 | `$titles['apartment_num']='Квартира'` | UI |
| 1004 | тема письма «карточка квартиры» | UI |
| 1037,1059 | комментарии | SKIP |
| 1107 | `$t['h1'] = 'Квартиры'` | UI |

#### `ctr__apartments_admin.php` (5)

| 8 | `$title = 'Квартиры'` | UI |
| 35 | `$titles['apartment_num'] = 'Квартира'` | UI |
| 179 | `filtr_select('Квартира',…)` | UI |
| 207 | `<option>Квартира подрядчика` | UI |
| 312 | `$t['h1'] = 'Квартиры'` | UI |

#### `ctr__apartments_broni.php` (3)

| 14 | `$title = 'Брони квартир'` | UI |
| 41 | `$titles['apartment_num']` | UI |
| 214 | option подрядчика | UI |

#### `ctr__econom.php` (9)

| 67–68,98,101,123,135,154,196,204,211 | свободных квартир, расчёт цен | UI |

#### `ctr__zapisx.php` (13 — было 8)

| Строка | Фрагмент | Действие |
|--------|----------|----------|
| 336,1212 | `<option>Выбрать/Квартира` | UI |
| 453,455,490,564,567 | комментарии / debug | SKIP |
| **832** | `' кв.'.$num` | **ABBREV** → `unit_num()` |
| 1097 | закомментированный mail | SKIP |
| **1099–1102** | тема mail `кв.:` | **ABBREV** → `unit_num($n,'colon')` |

#### `ctr__econom.php` (10 — было 9)

| 67–68,98,101,123,135,154,196,204 | UI | helper |
| **211** | `К СВОБОДНЫМ КВАРТИРАМ` | UI — **пропущено в первой версии плана** |

#### `ctr__zapiskeys.php` (4)

| 195 | `<h2>Квартира:` | UI |
| 272 | комментарий | SKIP |
| 642 | `<option>Выбрать квартиру` | UI |
| 815 | `$titles['apartment_num']` | UI |

#### `ctr__zapis_stat.php` (2)

| 135 | `$titles['all_app'] = 'Квартир'` | UI |
| 159 | комментарий | SKIP |

#### `ctr__uniajax.php` (3)

| 4 | комментарий | SKIP |
| 74,81,92,123 | option + комментарии | UI (option) / SKIP (comments) |

#### `ctr__stat_econom.php`, `!ctr__stat_econom.php`, `ctr__stat_econom_arh.php`

| Файл | Статус | Действие |
|------|--------|----------|
| `fw/controllers/ctr__stat_econom.php` | **активный** (роутер) | L90–92: убрать хардкод «апартаменты» → `unit_label_cap()`; L257,261: `кв.` → `unit_abbrev()` |
| `fw/controllers/!ctr__stat_econom.php` | **не грузится** (`!` в имени) | не трогать или удалить как архив |
| `sahmatka/ctr__stat_econom.php` | **мёртвая копия** в корне | удалить или пометить deprecated |
| `ctr__stat_econom_arh.php` | активный | UI + L671 `кв/мес`, L817 `кв.` |

#### `ctr__stat_lifecycle.php`, `ctr__stat_sales_*.php` (5)

| 81–83, 181 | графики «% от кол-ва квартир» | UI |

#### `ctr__objects.php` (8)

| 483–583 | массовая обработка — комментарии и print | UI (print) / SKIP (comments) |

#### `ctr__parking_floors.php` (2)

| 153,171 | комментарий «квартиры» | FIX → парковочные места |

| `fw/controllers/ctr__parking_spaces.php` (6) | 581,660 FIX; 662 ABBREV+FIX (`кв-` → `м/м-` или `unit_num` если поле общее); 664–688 SKIP |

#### `ctr__parking_broni.php` (1) — **добавлено аудитом**

| 90 | комментарий «НЕ СВОБОДНЫЕ КВАРТИРЫ» | FIX → парковочные места |

#### `ctr__rentbroni.php` (1)

| 89 | комментарий НЕ СВОБОДНЫЕ КВАРТИРЫ | FIX |

#### `ctr__agency.php` (2)

| 8,53–54 | комментарии в шапке файла | SKIP |

#### `ctr__metrika.php` (2)

| 124,127 | TODO комментарии | SKIP |

### 5.3. Шаблоны `fw/templates/` (33)

#### `apartments/` (16)

| Файл | Строки | Примеры | Действие |
|------|--------|---------|----------|
| `public_card.php` | 649,663,724,753 | «Назад к выбору квартир», «бронирование квартиры» | UI |
| `form_broni_ag.php` | 14,26,48 | `Квартира -`, option подрядчика | UI |
| `form_broni_pub.php` | 10,41 | коммент + label | UI / SKIP |
| `form_broni_done.php` | 81,118–119,143 | labels + сообщения | UI |
| `form!_broni_ag.php` | 17,39 | legacy duplicate | UI |
| `broni_history.php` | 7,73,108 | not found, цена квартиры | UI |

#### `zapiskeys/` (13)

| Файл | Строки | Действие |
|------|--------|----------|
| `index_table.php` | 28,70 | UI |
| `zapis_card.php` | 27 UI; 55,68–76 LEGAL | mixed |
| `zapis_card2.php` | 27 UI; 37,46,53 LEGAL | mixed |

### 5.4. Legacy `actions/` (~120)

| Файл | Кол-во | Ключевые строки | Действие |
|------|--------|-----------------|----------|
| `novoselam.php` | 30 | приемка/просмотр/номера квартир | UI + LEGAL |
| `admin_object.php` | 12 | статусы квартир, свойства | UI |
| `show_broni.php` | 8 | `<th>Квартира`, `Квартир:` | UI |
| `broni_history.php` | 7 | th, option, status | UI |
| `broni.php` | 7 | комментарии + th | mixed |
| `stat_sale.php` | 2 | status array | UI |
| `stat_sale2.php` | 7 | `$s_arr[6]` | UI |
| `stat_sale_new.php` | 7 | `$s_arr[6]` | UI |
| `stat_salen.php` | 1 | заголовок | UI |
| `stat_salen2.php` | 5 | th, labels | UI |
| `stat_salen3.php` | 5 | th | UI |
| `stat_saler.php` | 6 | h3 «Свободные квартиры» | UI |
| `stat_zapis.php` | 2 | th, option | UI |
| `obj_stat.php` | 3 | «Квартир не продано» | UI |
| `catalog.php` | 1 | «Каталог квартир» | UI |
| `admin_agency.php` | 1 | комментарий email | SKIP |
| `admin_users.php` | 1 | комментарий | SKIP |
| `agadmin_object.php` | 1 | «Статусы квартир» | UI |
| `us_object.php` | 1 | UI | UI |
| `todo.txt`, `sdan2.txt` | 6 | заметки | SKIP |

### 5.5. Корневые скрипты `sahmatka/` (~90)

| Файл | Кол-во | Действие |
|------|--------|----------|
| `iframe_apart.php` | 18 | UI — высокий приоритет |
| `form_order.php` | 5 | UI (542,654) |
| `form_order_custom.php` | 3 | UI |
| `ajax_actions.php` | 2 | UI |
| `ajax_form_credit.php` | 6 | комментарии SKIP |
| `csv_feed.php` | 9 | UI (заголовки, email) |
| `avito_feedx.php` | 6 | FEED (XML не менять) |
| `domclick_feedn.php` | 7 | FEED / marketing |
| `domclick_feedx.php` | 4 | FEED |
| `yandex_feedx.php` | 2 | SKIP/FEED |
| `yandex_feedn.php` | 1 | SKIP |
| `reglament.php` | 5 | LEGAL — ревью |
| `prices_func.php` | 4 | UI |
| `cron_clear_expired_broni.php` | 4 | UI |
| `display_home_public2.php` | 1 | UI |
| `form_exc_backoffice.php` | 1 | UI |
| `ctr__stat_econom.php` | 5 | UI |
| `import/new.php`, `import333.php`, `uni_table_import.php!` | 10 | SKIP |
| `template/default/in_head.php` | 1 | UI «Статистика квартир» |
| `template/default/js/scripts.js` | 1 | SKIP (comment) |

### 5.6. Вне `sahmatka/` (19)

| Файл | Кол-во | Действие |
|------|--------|----------|
| `restapi/index.php` | 9 | UI (error JSON) + SKIP (docblock) |
| `oferta/*.htm` | ~24 | LEGAL — не трогать |
| `keysbase/arh.csv` | 3 | UI при генерации |

---

## 6. Примеры замены (Sigma: апартаменты + ап.)

| Контекст | Было | Стало (custom mode) |
|----------|------|---------------------|
| Заголовок раздела | Квартиры | Апартаменты |
| Колонка таблицы | Квартира | Апартаменты |
| Select placeholder | Выбрать квартиру | Выбрать апартаменты |
| Ошибка | Квартира не найдена | Апартаменты не найдены |
| Статус 6 | Квартира подрядчика | Апартаменты подрядчика |
| Меню | Статистика квартир | Статистика апартаментов |
| Счётчик | Продано 42 кв. | Продано 42 ап. |
| Email | …этаж / кв-12 | …этаж / ап-12 |
| Тип комнат | Однокомнатная квартира | 1-комн. апартаменты |
| Юридический | многокvартирного дома | **без изменений** |
| Avito XML | `<ObjectType>Квартира</ObjectType>` | **без изменений** |

---

## 7. Чек-лист тестирования

- [ ] `config.php`: `mode=custom` — UI показывает «апартаменты», сокращение «ап.»
- [ ] `config.php`: `mode=legacy` — все формы «квартира/квартиры/…» как раньше
- [ ] Шахматка: клик по ячейке → карточка объекта
- [ ] Бронирование: форма агента + публичная карточка
- [ ] Запись на ключи: select дом → секция → **апартамент**
- [ ] Статистика: заголовки таблиц и графиков
- [ ] Email/CSV: тема и заголовки колонок
- [ ] Avito/Yandex фид: валидация XML не сломана
- [ ] REST API: JSON error messages
- [ ] Поиск по коду: `rg -n "[Кк]вартир" sites/sigma` — остались только LEGAL/SKIP/FEED

---

## 8. Команда для контроля прогресса

```powershell
# Все оставшиеся вхождения
rg -n "[Кк]вартир" sites/sigma -g "*.php" -g "*.js" -g "*.html"

# Только пользовательский UI (исключить oferta, import, comments — вручную)
rg -n "[Кк]вартир" sites/sigma/sahmatka/fw/templates sites/sigma/sahmatka/fw/controllers sites/sigma/sahmatka/actions sites/sigma/sahmatka/iframe_apart.php
```

---

## 9. Оценка трудозатрат

| Фаза | Часы | Риск |
|------|------|------|
| 0 — инфраструктура | 2–4 | низкий |
| 1 — ядро UI | 8–12 | средний |
| 2 — legacy actions | 8–12 | средний |
| 3 — статистика | 4–6 | низкий |
| 4 — юридические тексты | 4–8 + юрист | высокий |
| 5 — feeds/API | 2–4 | средний |
| 6 — копипаст parking | 1–2 | низкий |
| **Итого** | **~30–50 ч** | |

---

## 10. Рекомендуемый порядок первого PR

1. `config.php` + helpers  
2. `ctr__apartments.php` + `fw/templates/apartments/*`  
3. `iframe_apart.php` + `in_head.php`  
4. Smoke-тест на `sigma.m2profi.pro`  
5. Остальные фазы отдельными PR

---

## 11. Детальный аудит правок (2026-05-25)

Проверены все файлы из §4–§5 построчно. Ниже — расхождения с первой версией плана и обязательные корректировки.

### 11.1. Критические находки

#### A. Частичная миграция без helpers

В **`fw/controllers/ctr__stat_econom.php`** (единственный файл, который грузит роутер для `ctr=stat_econom`) строки 90–92 уже содержат хардкод **«апартаменты»**:

```php
$table_sold_html = $this->_generate_stats_table("Проданные апартаменты", [3]);
```

**Проблема:** при `mode=legacy` отчёт всё равно покажет «апартаменты».  
**Действие:** заменить на `unit_label_cap('pl_nom')` в фразах заголовков таблиц; убрать дублирование вручную прописанного текста.

#### B. Мёртвые дубликаты (не трогать при миграции UI)

| Файл | Почему мёртвый |
|------|----------------|
| `sahmatka/ctr__stat_econom.php` | Роутер ищет только `fw/controllers/ctr__stat_econom.php` |
| `fw/controllers/!ctr__stat_econom.php` | Имя с `!` — не матчится `ctr__stat_econom.php` |

**Действие:** пометить в репозитории как deprecated или удалить отдельным PR, чтобы не путать.

#### C. Parking — не подмена терминологии, а FIX контекста

`ctr__parking_spaces.php`, `ctr__parking_broni.php`, `ctr__rentbroni.php` — copy-paste из модуля квартир.  
**Нельзя** просто заменить «квартира» → «апартаменты» в email parking: там должно быть **«м/м» / «парковочное место»**.

| Файл | Строка | Сейчас | Должно быть |
|------|--------|--------|-------------|
| `parking_spaces.php` | 660–662 | `Бронирование квартиры` … `кв-N` | «Бронирование места» … `м/м-N` |
| `parking_broni.php` | 90 | комментарий КВАРТИРЫ | «парковочные места» |
| `parking_floors.php` | 153,171 | комментарий квартиры | «парковочные места» |

#### D. `unit_num(..., 'dash')` и legacy «кв-»

В коде паттерн **`кв-<b>12</b>`** (без точки). Helper `dash` должен давать **`ап-12`**, не `ап.12`. В §2.1 заложить:

```php
// dash: rtrim($abbr, '.') . '-' . $num
```

### 11.2. Сводка корректировок счётчиков

| Файл | Было в плане | Аудит | Δ |
|------|--------------|-------|---|
| `novoselam.php` | 30 | **37** | +7 (КВАРТИРУ caps) |
| `ctr__zapisx.php` | 8 | **13** | +5 |
| `iframe_apart.php` | 18 | **19** | +1 |
| `form_order.php` | 5 | **8** | +3 (темы писем) |
| `form_order_custom.php` | 3 | **6** | +3 |
| `ctr__econom.php` | 9 | **10** | +1 |
| `cron_clear_expired_broni.php` | 4 | **5** | +1 |
| `apartments/*.php` | 16 | **18** | +2 |
| `yandex_feedx.php` | 2 | **4** | +2 |
| `ctr__uniajax.php` | 3 | **5** | уточнение |

### 11.3. `novoselam.php` — построчная классификация (37 строк)

**LEGAL — не менять (23 строки):**

| Строки | Причина |
|--------|---------|
| 45, 107, 203, 258, 401, 436, 466 | «акт приема-передачи **квартиры**» — термин договора |
| 62, 121, 216, 270, 409, 440, 475 | «ПОЛНОЙ ОПЛАТЕ ЗА **КВАРТИРУ**» |
| 98, 193, 247, 393, 432, 459 | «**многоквартирного** дома» — 214-ФЗ |

**UI — заменить helper (14 строк):**

| Строки | Текст |
|--------|-------|
| 33, 37 | заголовки «приемки квартиры» |
| 39 | «Приемка квартиры» / «осмотра квартиры» (процедурный — согласовать с юристом) |
| 103, 198, 253, 397, 434, 463 | «Приемка/Просмотр квартиры» |
| 233–234, 237, 240, 425–428 | «номера **квартир** с № …» в расписании заселения |

**Рекомендация:** фазу 4 для `novoselam.php` разбить: сначала UI-расписание (233–428), LEGAL-блоки — только после подписи юриста.

### 11.4. `zapiskeys` — LEGAL vs UI

| Файл | UI | LEGAL |
|------|-----|-------|
| `zapis_card.php` | L27 «Квартира:» | L55,68–69,72,74,76 (ДДУ, многоквартирный, акт, покупатель) |
| `zapis_card2.php` | L27 | L37,46,53 |
| `index_table.php` | L28,70 | — |

### 11.5. Фиды — уточнение

| Файл | Строка | Класс | Действие |
|------|--------|-------|----------|
| `avito_feedx.php` | 247–259 | FEED | XML-теги не менять |
| `avito_feedx.php` | 153 | FEED? | «N-комнатная квартира» в `$desc` — опционально helper |
| `yandex_feedx.php` | 75 | FEED | `<category>квартира</category>` — схема Yandex |
| `yandex_feedx.php` | 118 | ABBREV | `N к. кв.` → `N к. ап.` |
| `yandex_feedx.php` | 121 | **SKIP** | `кв. м` = м² |
| `domclick_feedn.php` | 582–585 | SKIP/FEED | маркетинговый текст в `$jk_d_diskr` — «квартир» внутри длинного описания; не UI |

### 11.6. Файлы вне первоначального списка (фаза 2, уже в §5.4)

`actions/broni.php`, `broni_history.php`, `stat_sale*.php`, `stat_salen*.php`, `stat_saler.php`, `stat_zapis.php`, `obj_stat.php`, `catalog.php`, `agadmin_object.php` — **~50 UI-вхождений**, учтены в фазе 2, детализация достаточна.

**Не включать в scope:** `import/*`, `actions/todo.txt`, `sdan*.txt`, `oferta/*` (LEGAL).

### 11.7. Доработка helpers (по итогам аудита)

Добавить в `unit_phrase()`:

| Ключ | Назначение |
|------|------------|
| `search_not_found` | «Апартаменты, не соответствующие условиям…» |
| `email_card_subject` | «ФОРМА КАРТОЧКИ АПАРТАМЕНТОВ» |
| `feed_room_desc` | «N-комн. ап.» для Yandex L118 |

Добавить **`unit_label_legal($case)`** — всегда legacy-формы для блоков с `keep_legal_terms=true`.

### 11.8. Пересмотренная оценка

| Фаза | Было (ч) | Стало (ч) | Причина |
|------|----------|-----------|---------|
| 0 | 2–4 | 3–5 | +`unit_num dash`, +legal helper |
| 1 | 8–12 | 10–14 | zapisx abbrev, stat_econom refactor |
| 4 | 4–8 | 6–10 | novoselam 37 строк, LEGAL split |
| 6 | 1–2 | 2–3 | parking_broni + parking email FIX |
| **Итого** | 30–50 | **32–52** | |

---

**Статус документа:** план + детальный аудит (2026-05-25). Код не менялся.

**Согласовано для Sigma:** базовое слово **«апартаменты»**, сокращение **«ап.»** (legacy: «кв.»).
