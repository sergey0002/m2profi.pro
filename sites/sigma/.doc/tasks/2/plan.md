# План переименования «апартаменты» → «резиденции» (sites/sigma)

**Задача:** заменить пользовательскую терминологию **«апартаменты / апартамент / ап.»** на **«резиденции / резиденция / рез.»** во всём UI Sigma.  
**Дата аудита:** 23.06.2026  
**Статус:** план (код не менялся)  
**Предыдущая задача:** [name_appartament.md](../../name_appartament.md) — «квартира» → «апартаменты» через `unit_*`  
**Отчёт о внедрении:** [name_appartament_report.md](../../name_appartament_report.md)

### ⚠️ Область работ — только Sigma

| | |
|---|---|
| **Меняем** | `sites/sigma/**` — весь код, конфиг и UI этого тенанта |
| **Не трогаем** | `sites/em/**`, `core/**`, `core/etalon_site/**`, любые другие `sites/*` |

Поиск вхождений, правки, деплой и QA — **только внутри `sites/sigma/`**.  
Файлы EM и общий код репозитория **не копируем, не синхронизируем, не правим** — даже если там те же строки с «апартамент».

Все пути в плане ниже — относительно **`sites/sigma/`**, если не указано иное.

---

## 0. Результат аудита

### 0.1. Текущая архитектура

Терминология задаётся в **`sites/sigma/sahmatka/config.php`** (на сервере; эталон в git — `config.php.example`):

```php
$GLOBALS['config']['object_unit'] = [
    'mode'   => 'custom',
    'label'  => 'апартаменты',   // одна форма для ВСЕХ падежей
    'abbrev' => 'ап.',
    'legacy' => [ /* квартира, квартиры, … кв. */ ],
];
```

Хелперы (`unit_label`, `unit_label_cap`, `unit_abbrev`, `unit_num`, `unit_phrase`, `unit_room_type`) уже подключены в **~52 файлах `sites/sigma/sahmatka/`** — после смены конфига большая часть UI обновится автоматически.

### 0.2. Проблема склонений

В режиме `custom` функция `unit_label($case)` **игнорирует падеж** и всегда возвращает `label`:

| Фраза в UI | Сейчас (custom) | Нужно для «резиденции» |
|------------|-----------------|------------------------|
| Выбрать … | Выбрать апартаменты | Выбрать **резиденцию** (`acc`) |
| Статус … | Статус апартаменты | Статус **резиденции** (`gen`) |
| Каталог … | Каталог апартаменты | Каталог **резиденций** (`pl_gen`) |
| … не найдены | Апартаменты не найдены | **Резиденции** не найдены (`pl_nom`) |
| Забронировать … | Забронировать апартаменты | Забронировать **резиденцию** (`acc`) |

**Вывод:** для «резиденций» недостаточно поменять `label` — нужно **включить склонение** (расширить `unit_label()` или сменить `mode`).

### 0.3. Принцип: всё меняется в конфиге

**Цель задачи — не размазать «резиденции» по 14+ файлам, а сделать так, чтобы смена терминологии в будущем сводилась к правке одного блока в `config.php`.**

| Слой | Где живут слова | Что делаем в этой задаче |
|------|-----------------|--------------------------|
| **Конфиг** | `object_unit` в `config.php` — формы, сокращение, фразы | Меняем «апартаменты» → «резиденции» **только здесь** |
| **Хелперы** | `unit_label`, `unit_phrase`, `unit_count_label` в `config.php` | Дорабатываем логику (падежи, compred-фразы) |
| **UI-код** | шаблоны, контроллеры, JS | **Одноразово** заменяем хардкод на вызовы `unit_*`; тексты в код не пишем |

После внедрения повторное переименование (например, «резиденции» → «апартаменты») = правка **`sites/sigma/sahmatka/config.php`** (и синхронизация `config.php.example`), без обхода десятков файлов.

### 0.4. Сводка по объёму

| Категория | Файлов | Действие |
|-----------|:------:|----------|
| **Конфиг** (`config.php` + `.example`) | 2 | **PATCH** — все формы, фразы, JS-export |
| **Уже на `unit_*`** | ~52 | **VERIFY** — обновятся от конфига |
| **Хардкод → привязка к `unit_*`** (один раз) | ~13 | **PATCH** — убрать строки, оставить `unit_phrase('…')` |
| **Комментарии в PHP** | 4 | SKIP (не UI) |
| **Не трогать** | — | EM, core, БД, URL, фиды, LEGAL |

---

## 1. Склонения и сокращения (целевые значения)

### 1.1. Таблица падежей

| Ключ `$case` | Форма | Пример в фразе |
|--------------|-------|----------------|
| `nom` | резиденция | «иначе резиденция будут освобождены» → лучше править фразу |
| `gen` | резиденции | Статус **резиденции** изменён |
| `dat` | резиденции | История броней по **резиденции** |
| `acc` | резиденцию | Выбрать **резиденцию**, Забронировать **резиденцию** |
| `ins` | резиденцией | (редко) |
| `pl_nom` | резиденции | **Резиденции** не найдены |
| `pl_gen` | резиденций | Каталог **резиденций**, Номера **резиденций** |
| `pl_acc` | резиденции | (если понадобится) |
| `abbrev` | рез. | `рез. 12`, `рез-12`, `рез/мес` |

### 1.2. Счётное склонение (для compred и подобного)

| Число | Форма | Пример |
|-------|-------|--------|
| 1, 21, 31… (не 11) | резиденция | 1 резиденция |
| 2–4, 22–24… (не 12–14) | резиденции | 3 резиденции |
| 0, 5–20, 25–30… | резиденций | 10 резиденций |

Сейчас это захардкожено в `compred_apartments_count_label()` — нужно обобщить (см. §3.1).

### 1.3. Типы по комнатности

| Было (custom) | Станет |
|---------------|--------|
| `1-комн. апартаменты` | `1-комн. резиденции` |
| `2-комн. апартаменты` | `2-комн. резиденции` |

Генерируется `unit_room_type()` из `unit_label('pl_nom')`.

### 1.4. Единый блок конфига (единственное место смены слов)

Все пользовательские формы слова, сокращение и **дополнительные фразы** (в т.ч. compred, JS) задаются в **`$GLOBALS['config']['object_unit']`**. Хелперы только читают этот массив.

```php
$GLOBALS['config']['object_unit'] = [
    'mode'   => 'custom',
    'label'  => 'резиденции',          // fallback, если нет forms[$case]
    'abbrev' => 'рез.',

    // Склонения — единственный источник для unit_label()
    'forms'  => [
        'nom'    => 'резиденция',
        'gen'    => 'резиденции',
        'dat'    => 'резиденции',
        'acc'    => 'резиденцию',
        'ins'    => 'резиденцией',
        'pl_nom' => 'резиденции',
        'pl_gen' => 'резиденций',
        'pl_acc' => 'резиденции',
    ],

    // Счётное склонение для unit_count_label($n) — 1 / 2–4 / 5+
    'count_forms' => [
        'one'  => 'резиденция',
        'few'  => 'резиденции',
        'many' => 'резиденций',
    ],

    // Бейдж compred (верхний регистр от nom)
    'badge_upper' => null,  // null = auto mb_strtoupper(forms.nom)

    // Классическая терминология (mode=legacy)
    'legacy' => [
        'nom' => 'квартира', 'gen' => 'квартиры', 'dat' => 'квартире', 'acc' => 'квартиру',
        'ins' => 'квартирой', 'pl_nom' => 'квартиры', 'pl_gen' => 'квартир', 'pl_acc' => 'квартиры',
        'abbrev' => 'кв.',
    ],
];
```

**Правило:** в PHP/JS/HTML **не писать** «резиденция», «резиденции» и т.д. — только `unit_label()`, `unit_phrase()`, `unit_count_label()`, `unit_badge()`.

### 1.5. Расширение `unit_label()` (логика в config.php, данные в `forms`)

```php
function unit_label($case = 'nom') {
    $cfg = unit_cfg();
    if (($cfg['mode'] ?? '') === 'custom') {
        $forms = $cfg['forms'] ?? [];
        if (isset($forms[$case])) {
            return $forms[$case];
        }
        return $cfg['label'] ?? 'резиденции';
    }
    // legacy — без изменений
}
```

### 1.6. Новые хелперы (тоже в config.php, данные из `object_unit`)

| Функция | Назначение | Откуда берёт слова |
|---------|------------|-------------------|
| `unit_count_label(int $n)` | 1 резиденция / 3 резиденции / 10 резиденций | `count_forms` или `forms` |
| `unit_badge()` | `РЕЗИДЕНЦИЯ` на карточке compred | `badge_upper` или `mb_strtoupper(forms.nom)` |
| `unit_js_export()` | JSON для `window.UNIT_LABEL` в шаблоне | все `forms` + ключевые `unit_phrase` |

**Старый вариант B** (`mode => 'legacy'` как единственный способ склонения) — **не используем**; склонение через `forms` в `custom`.

---

## 2. Этапы работ (рекомендуемый порядок)

| Этап | Содержание | Где меняются слова |
|------|------------|-------------------|
| **1** | Расширить `object_unit`: `forms`, `count_forms`, доработать `unit_label()` | **только config.php** |
| **2** | Добавить `unit_count_label()`, `unit_badge()`, `unit_js_export()` | **только config.php** |
| **3** | Расширить `unit_phrase()` — все UI-фразы + compred (§3.3) | **только config.php** |
| **4** | Убрать 2 захардкоженные фразы в `$map` (`stats_title`, `monthly_sales`) | **только config.php** |
| **5** | Одноразовая привязка ~13 файлов: хардкод → `unit_phrase()` / `unit_label()` | код без литералов |
| **6** | Деплой `config.php` на сервер sigma | сервер |
| **7** | Smoke-тест §8 | — |

**Оценка:** ~0.5–1 д.  
**Повторная смена терминологии:** только этапы 1–4 + деплой конфига (этап 5 уже не нужен).

---

## 3. Конфиг и хелперы — полная спецификация

> **В этом разделе — единственное место, где появляются русские слова «резиденция*».**  
> Все остальные файлы sigma только вызывают хелперы по ключам.

### 3.1. `sites/sigma/sahmatka/config.php` + `config.php.example`

#### 3.1.1. Блок `object_unit` (данные)

| Ключ | Было | Стало |
|------|------|-------|
| `label` | `апартаменты` | `резиденции` |
| `abbrev` | `ап.` | `рез.` |
| `forms` | *(отсутствует)* | таблица §1.1 |
| `count_forms` | *(отсутствует)* | `one` / `few` / `many` §1.2 |

#### 3.1.2. Логика хелперов (строки ~206–346)

| Что | Было | Стало |
|-----|------|-------|
| `unit_label()` custom | всегда `label` | `forms[$case]` → fallback `label` |
| fallback в `unit_label` | `'апартаменты'` | `'резиденции'` |
| fallback в `unit_abbrev` | `'ап.'` | `'рез.'` |
| `unit_phrase` L269 | `'Статистика апартаментов'` | `'Статистика ' . unit_label('pl_gen')` |
| `unit_phrase` L322 | `'…типам аппартаментов'` | `'…типам ' . unit_label('pl_gen')` |
| **новое** | — | `unit_count_label($n)` |
| **новое** | — | `unit_badge()` |
| **новое** | — | `unit_js_export()` → массив для фронта |

#### 3.1.3. `unit_count_label(int $n)` — реализация в config.php

```php
function unit_count_label(int $count): string {
    $cfg = unit_cfg();
    $cf = $cfg['count_forms'] ?? [];
    $n = abs($count) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $cf['many'] ?? unit_label('pl_gen');
    }
    if ($n1 > 1 && $n1 < 5) {
        return $cf['few'] ?? unit_label('pl_nom');
    }
    if ($n1 === 1) {
        return $cf['one'] ?? unit_label('nom');
    }
    return $cf['many'] ?? unit_label('pl_gen');
}
```

Заменяет `compred_apartments_count_label()` — в compred_helpers только вызов `unit_count_label()`.

#### 3.1.4. Экспорт в JS (`unit_js_export` + `in_head.php`)

В **config.php** формируется массив; в **in_head.php** один раз выводится:

```php
<script>
window.UNIT_LABEL = <?= json_encode(unit_js_export(), JSON_UNESCAPED_UNICODE) ?>;
</script>
```

Содержимое `unit_js_export()` (строки собираются из `forms` + `unit_phrase`):

| Ключ JS | Источник в конфиге / phrase |
|---------|----------------------------|
| `nom`, `gen`, `acc`, `pl_nom`, `pl_gen` | `forms.*` |
| `nomCap`, `pl_nomCap` | `unit_label_cap()` |
| `abbrev` | `abbrev` |
| `compred_delete_confirm` | `unit_phrase('compred_delete_confirm')` |

### 3.2. Фразы `unit_phrase()` — существующие (~48 ключей)

После исправления `unit_label()` пересчитываются **автоматически** из `forms`. Проверить грамматику:

| Ключ | Ожидаемый результат |
|------|---------------------|
| `not_found` | Резиденции не найдены |
| `select` | Выбрать резиденцию |
| `booking_title` | Заявка на бронирование резиденции |
| `catalog_title` | Каталог резиденций |
| `contractor` | Резиденции подрядчика |
| `book_unit` | Забронировать резиденцию |
| `stats_title` | Статистика резиденций |
| `monthly_sales` | …по типам резиденций |

### 3.3. Новые ключи `unit_phrase()` — compred и прочий хардкод

Добавить в `$map` внутри `unit_phrase()` в **config.php** (не в отдельных файлах):

| Ключ | Шаблон (строится из `forms`) | Было в коде |
|------|------------------------------|-------------|
| `menu_pl_nom` | `unit_label_cap('pl_nom')` | `in_head.php:63` «Апартаменты» |
| `page_title_pl_nom` | то же | заголовки objects/admin/agadmin |
| `compred_badge` | `unit_badge()` | `compred_config.php` «АПАРТАМЕНТ» |
| `compred_collection` | `'Подборка ' . unit_label('pl_gen')` | compred_helpers |
| `compred_collection_personal` | `'Персональная подборка ' . unit_label('pl_gen') . ' от M2 Profi'` | compred_helpers:279 |
| `compred_collection_count` | `'Подборка из ' . '%d' . ' ' . /* unit_count_label в sprintf */` | compred_helpers:277 |
| `compred_not_found` | `unit_label_cap('nom') . ' не найдена'` | ctr__compred |
| `compred_added` | `'Добавлена ' . unit_label('nom') . ' в предложение'` | ctr__compred, ctr__apartments |
| `compred_delete_confirm` | `'Удалить ' . unit_label('acc') . ' из предложения?'` | compred.js |
| `compred_index_intro` | многострочный intro (см. §3.3.1) | compred/index.php |
| `compred_index_list_hint` | … | compred/index.php:22 |
| `compred_index_count_label` | `unit_label_cap('pl_gen') . ':'` | compred/index.php:39 |
| `compred_edit_count_label` | `unit_label_cap('pl_gen') . ' в подборке:'` | compred/edit.php:21 |
| `compred_edit_intro_placeholder` | … | compred/edit.php:41 |
| `compred_edit_empty_title` | `'В этом предложении пока нет ' . unit_label('pl_gen')` | compred/edit.php:52 |
| `compred_edit_empty_hint` | … | compred/edit.php:54 |

#### 3.3.1. Длинные тексты compred — тоже в конфиге

Для абзацев с несколькими падежами — ключи с плейсхолдерами в **`object_unit['phrase_templates']`** (читаются из `unit_phrase()`):

```php
'phrase_templates' => [
    'compred_index_intro' =>
        'Коммерческое предложение — это подборка {pl_gen} с вашими примечаниями…',
    'compred_index_step_open' =>
        'Откройте нужную {acc} на шахматке.',
    'compred_index_step_card' =>
        'В карточке {gen} нажмите «Добавить к предложению»…',
    // …
],
```

Хелпер подставляет `{nom}`, `{gen}`, `{acc}`, `{pl_nom}`, `{pl_gen}` из `forms`:

```php
function unit_template(string $key): string {
    $tpl = unit_cfg()['phrase_templates'][$key] ?? '';
    $repl = [
        '{nom}' => unit_label('nom'), '{gen}' => unit_label('gen'),
        '{acc}' => unit_label('acc'), '{pl_nom}' => unit_label('pl_nom'),
        '{pl_gen}' => unit_label('pl_gen'),
        '{nom_cap}' => unit_label_cap('nom'), '{pl_nom_cap}' => unit_label_cap('pl_nom'),
    ];
    return strtr($tpl, $repl);
}
```

Шаблоны compred в PHP: `<?= unit_template('compred_index_intro') ?>` — **без русских слов в файле**.

### 3.4. `compred_config.php` — читать из конфига

```php
// было: 'label' => 'АПАРТАМЕНТ'
'label' => function_exists('unit_badge') ? unit_badge() : 'АПАРТАМЕНТ',
```

Либо инициализация после `require config.php`:

```php
$GLOBALS['compred_obj_types']['apartment']['label'] = unit_badge();
```

Ключ `'apartment'` не менять — только отображаемый `label` из конфига.

### 3.5. `$kv_type` — уже из конфига

Строки 348–367: `unit_room_type()` → автоматически «N-комн. резиденции» после смены `forms.pl_nom`. **Правок вне config не нужно.**

## 4. Одноразовая привязка файлов к хелперам (без литералов)

Эти файлы правятся **один раз**: убираем русские слова «апартамент*», ставим вызовы из §3.  
**Тексты «резиденция*» в них не появляются** — только `unit_phrase('…')`, `unit_label()`, `unit_template()`, `UNIT_LABEL.*`.

### 4.1. Меню и заголовки

| Файл | Строка | Было | Стало |
|------|--------|------|--------|
| `template/default/in_head.php` | 63 | `Апартаменты` | `<?= unit_phrase('menu_pl_nom') ?>` |
| `actions/objects_index.php` | 25 | `Апартаменты` | `<?= unit_phrase('page_title_pl_nom') ?>` |
| `actions/admin_object.php` | 196 | то же | то же |
| `actions/agadmin_object.php` | 36 | то же | то же |

### 4.2. Compred — helpers (только вызовы)

| Файл | Строки | Было | Стало |
|------|--------|------|--------|
| `inc/compred_helpers.php` | 66 | fallback `'Апартамент'` | `unit_label_cap('nom')` |
| `inc/compred_helpers.php` | 231–244 | `compred_apartments_count_label()` | удалить функцию → `unit_count_label($count)` |
| `inc/compred_helpers.php` | 251, 269 | `'Подборка апартаментов'` | `unit_phrase('compred_collection')` |
| `inc/compred_helpers.php` | 277 | строка с count | `sprintf(unit_phrase('compred_collection_count'), $count, unit_count_label($count))` |
| `inc/compred_helpers.php` | 279 | персональная подборка | `unit_phrase('compred_collection_personal')` |

### 4.3. Compred — шаблоны

| Файл | Строка | Стало |
|------|--------|--------|
| `fw/templates/compred/index.php` | 7, 10–12 | `<?= unit_template('compred_index_…') ?>` |
| `fw/templates/compred/index.php` | 22 | `unit_phrase('compred_index_list_hint')` |
| `fw/templates/compred/index.php` | 39 | `<?= unit_phrase('compred_index_count_label') ?>` |
| `fw/templates/compred/edit.php` | 21, 41, 52, 54 | ключи из §3.3 |

### 4.4. Compred — контроллеры и JS

| Файл | Строка | Стало |
|------|--------|--------|
| `fw/controllers/ctr__compred.php` | 296, 298 | `unit_phrase('compred_not_found')` |
| `fw/controllers/ctr__compred.php` | 355 | `unit_phrase('compred_added')` |
| `fw/controllers/ctr__apartments.php` | 715 | `unit_phrase('compred_added')` |
| `template/default/js/compred.js` | 190 | `confirm(window.UNIT_LABEL.compred_delete_confirm)` |
| `template/default/in_head.php` | *(новый блок)* | `window.UNIT_LABEL = …` из `unit_js_export()` |

### 4.5. `inc/compred_config.php`

| Строка | Стало |
|--------|--------|
| 3 | `'label' => unit_badge()` (после подключения config) |

---

## 5. Файлы на `unit_*` — только проверка (автообновление)

После этапов 1–3 эти файлы должны показать «резиденции» без правок кода. Прогнать выборочно по чеклисту.

### 5.1. `sahmatka/` (корень)

`ajax_actions.php`, `cron_clear_expired_broni.php`, `csv_feed.php`, `display_home_public2.php`, `form_exc_backoffice.php`, `form_order.php`, `form_order_custom.php`, `iframe_apart.php`, `prices_func.php`, `yandex_feedn.php`, `yandex_feedx.php`

### 5.2. `sahmatka/actions/`

`broni.php` (UI-строки), `broni_history.php`, `catalog.php`, `novoselam.php` (только UI, не LEGAL), `obj_stat.php`, `show_broni.php`, `stat_sale.php`, `stat_sale2.php`, `stat_sale_new.php`, `stat_salen2.php`, `stat_salen3.php`, `stat_saler.php`, `stat_zapis.php`, `us_object.php`

### 5.3. `sahmatka/fw/controllers/`

`ctr__apartments.php`, `ctr__apartments_admin.php`, `ctr__apartments_broni.php`, `ctr__econom.php`, `ctr__objects.php`, `ctr__stat_econom.php`, `ctr__stat_econom_arh.php`, `ctr__stat_lifecycle.php`, `ctr__stat_sales_dynamic.php`, `ctr__stat_sales_velocity.php`, `ctr__uniajax.php`, `ctr__zapis_stat.php`, `ctr__zapiskeys.php`, `ctr__zapisx.php`

### 5.4. `sahmatka/fw/templates/apartments/`

`broni_history.php`, `form_broni_ag.php`, `form_broni_pub.php`, `form_broni_done.php`, `form!_broni_ag.php`, `public_card.php`, `compred_block.php` (без слова «апартамент» — OK)

### 5.5. `sahmatka/fw/templates/zapiskeys/`

`index_table.php`, `zapis_card.php`, `zapis_card2.php`

### 5.6. `sites/sigma/restapi/`

`index.php` — сообщения через `unit_phrase('api_*')`

### 5.7. `template/default/in_head.php`

| Строка | Уже на helper? |
|--------|----------------|
| 215 | `unit_phrase('stats_title')` → «Статистика резиденций» после правки L269 |

---

## 6. Намеренно НЕ менять

### 6.1. Вне scope задачи (другие каталоги репозитория)

| Каталог | Примеры | Причина |
|---------|---------|---------|
| **`sites/em/**`** | `sahmatka/actions/broni.php`, `ctr__metrika.php`, compred на EM | другой тенант, терминология «квартиры» |
| **`core/**`** | `classes.php`, `functions.php` | общий код для всех сайтов |
| **`core/etalon_site/**`** | эталонные контроллеры | не sigma |
| **Любой `sites/*` кроме sigma** | dep, другие проекты | вне задачи |

### 6.2. Внутри sigma — не менять (техническое / юридическое)

| Область | Примеры | Причина |
|---------|---------|---------|
| **БД** | таблица `apartaments`, поля `apartment_num`, `apartament_id` | схема данных |
| **URL / роутинг** | `ctr=apartments`, папка `fw/templates/apartments/` | кодовая структура |
| **Compred internal** | `obj_type=apartment`, ключ `'apartment'` в `compred_obj_types` | API/БД compred |
| **XML-фиды** | `<category>квартира</category>`, Avito/Domclick ObjectType | требования площадок |
| **LEGAL-тексты** | «многоквартирный дом», ДДУ, акты в `zapis_card.php`, `novoselam.php` | юридическая терминология |
| **«кв. м»** | площадь в фидах | не путать с номером объекта |
| **Комментарии в PHP** | `broni.php:81,87,104,113,126,240`, `show_broni.php:283,639,653`, `stat_salen3.php:158` | не видны пользователю (опционально) |
| **Документация task-1** | `.doc/tasks/1/` | исторический контекст переноса compred |

---

## 7. Документация (после внедрения)

| Файл | Действие |
|------|----------|
| `.doc/name_appartament.md` | Добавить примечание: актуальная терминология Sigma — «резиденции» |
| `.doc/name_appartament_report.md` | Новый § или отдельный `name_residence_report.md` |
| `.doc/tasks/2/doc.md` | Краткое ТЗ (опционально) |

---

## 8. Чек-лист проверки (QA)

### 8.1. Навигация и заголовки

- [ ] Меню: пункт «Резиденции» (`in_head.php`)
- [ ] `user.php?action=objects` — заголовок «Резиденции»
- [ ] Шахматка / каталог — колонка «Резиденции», фильтр «Выбрать резиденцию»
- [ ] Статистика — «Статистика резиденций»

### 8.2. Бронирование и карточки

- [ ] Публичная карточка: «Заявка на бронирование резиденции»
- [ ] Ошибки: «Резиденции не найдены», «Резиденции уже забронированы»
- [ ] Статус 6: «Резиденции подрядчика»
- [ ] Номер объекта: `рез. 12` / `рез-12` в письмах

### 8.3. Запись на ключи

- [ ] Select и колонка: «Резиденции» (не LEGAL-блок памятки)

### 8.4. Compred

- [ ] Список предложений: «Резиденций: N»
- [ ] Пустое состояние: «нет резиденций»
- [ ] Публичная страница / OG: «Подборка резиденций»
- [ ] Счёт: 1 резиденция / 2 резиденции / 5 резиденций
- [ ] Бейдж на карточке: «РЕЗИДЕНЦИЯ»

### 8.5. API и отчёты

- [ ] REST API 404: «Резиденции не найдены или недоступны»
- [ ] `ctr__stat_econom`: «Проданные резиденции», график «Продано N рез.»
- [ ] Типы комнат: «1-комн. резиденции»

### 8.6. Конфиг — единая точка правды

- [ ] Смена `forms.nom` в config → меняется бейдж compred, карточка, ошибки
- [ ] Смена `abbrev` → `рез. N` в таблицах и письмах
- [ ] `phrase_templates` compred — все абзацы на инструкции обновляются
- [ ] В коде sigma (кроме config) **нет** литералов «апартамент» / «резиденц» (`rg` §9)

### 8.7. Регрессия

- [ ] `mode=legacy` в config → снова «квартира» / «кв.»
- [ ] Парковки — по-прежнему «м/м», не «рез.»
- [ ] Yandex feed: `рез.` в description, `<category>квартира</category>` без изменений

---

## 9. Команды для поиска остатков

```powershell
# Только sigma — не искать по всему репо
$root = "sites/sigma"

# После внедрения: в UI-коде не должно остаться «апартамент» (кроме комментариев)
rg -i "апартамент|аппартамент|резиденц" $root/sahmatka --glob "!config.php*"
# ожидается: 0 совпадений в .php/.js (или только комментарии)

# Все пользовательские слова — только в конфиге
rg -i "резиденц|апартамент" $root/sahmatka/config.php
# ожидается: все вхождения в object_unit / phrase_templates / unit_phrase $map

# Убедиться, что EM не затронут (должно быть без изменений в git diff)
git diff --name-only | Select-String "sites/em"
# ожидается: пусто

# Проверка helpers в sigma
rg "unit_label|unit_phrase|unit_abbrev" $root/sahmatka -c

# Остатки хардкода
rg "Апартамент" $root/sahmatka
```

---

## 10. Риски

| Риск | Митигация |
|------|-----------|
| Случайные правки в `sites/em/` | Работать только в `sites/sigma/`; перед коммитом `git diff --name-only` без `sites/em` |
| `config.php` только на сервере | Синхронизировать с `config.php.example`; зафиксировать diff в задаче |
| Грамматические ошибки | `forms` + `count_forms` + `phrase_templates` только в config |
| JS confirm в compred | `unit_js_export()` в config → `in_head.php` |
| Забыли привязать хардкод | `rg` без config — 0 литералов в UI |
| Плейсхолдеры в HTML | `unit_template()` с `{gen}`, `{pl_gen}` из config |

---

## 11. Матрица файлов (краткая)

### A. Где меняются слова «резиденция*» (только конфиг)

| # | Путь | Содержание |
|---|------|------------|
| 1 | `sites/sigma/sahmatka/config.php` | `object_unit`, `forms`, `count_forms`, `phrase_templates`, хелперы, `unit_phrase` $map |
| 2 | `sites/sigma/sahmatka/config.php.example` | синхронно с п.1 (git) |

### B. Одноразовая привязка к хелперам (без русских литералов)

| # | Путь | Действие |
|---|------|----------|
| 3 | `template/default/in_head.php` | `unit_phrase('menu_pl_nom')` + `unit_js_export()` |
| 4 | `actions/objects_index.php` | `unit_phrase('page_title_pl_nom')` |
| 5 | `actions/admin_object.php` | то же |
| 6 | `actions/agadmin_object.php` | то же |
| 7 | `inc/compred_config.php` | `unit_badge()` |
| 8 | `inc/compred_helpers.php` | `unit_count_label`, `unit_phrase`, удалить старую count-функцию |
| 9–10 | `fw/templates/compred/index.php`, `edit.php` | `unit_template` / `unit_phrase` |
| 11–12 | `fw/controllers/ctr__compred.php`, `ctr__apartments.php` | `unit_phrase('compred_*')` |
| 13 | `template/default/js/compred.js` | `window.UNIT_LABEL.*` |

### C. Только проверка (уже на `unit_*`)

| # | Путь | Действие |
|---|------|----------|
| 14–65 | ~52 файла в `sites/sigma/sahmatka/` | **VERIFY** |
| 66 | `sites/sigma/restapi/index.php` | **VERIFY** |

### D. Не трогать

`sites/em/**`, `core/**`, БД, URL, фиды, LEGAL
