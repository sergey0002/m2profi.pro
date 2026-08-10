# План: интерактивный генплан ЖК (EM) — production-спека

**Задача:** редактор + встраиваемый виджет генплана для `homes_kvartal`.  
**Дата:** 10.08.2026 · **решения:** [decisions.md](./decisions.md) · **аудит черновика:** [audit.md](./audit.md)  
**Статус:** ✅ план готов · ✅ код поставлен в `feature/10-interactive-genplan` (см. [delivery.md](./delivery.md))  
**Единственный источник истины для кода:** этот файл  
**Документ задачи:** [doc.md](./doc.md)  
**Макеты:** [ref/mockup-genplan-overview.png](./ref/mockup-genplan-overview.png) · [ref/mockup-genplan-tooltip.png](./ref/mockup-genplan-tooltip.png)  
**Эталон UI Sigma:** [ref/sigma/](./ref/sigma/)

### ⚠️ Область — строго `sites/em/**`

| | |
|---|---|
| **Меняем** | только `sites/em/` |
| **Не трогаем** | `sites/sigma/**`, `core/**`, другие тенанты, корневой `.gitignore` |
| **Reuse Sigma** | *читать* + *копировать* проверенный код **в** EM-файлы `genplan_*` (форк/адаптация, не shared package) |

---

## 0. Решения заказчика (канон)

Полный текст: [decisions.md](./decisions.md).

| Тема | Канон |
|------|--------|
| Привязка дома | Ручной `title` (HTML) + опциональный select; preview срока/статуса/адреса в редакторе |
| Клик | Свободный `link_url` |
| Иконки amenities | **Не делаем** |
| Тултип с домом | Статус / сдан / адрес — **живые из `homes`** на каждый `widget_data` |
| Без дома | Только ручной `title` (**обязателен**); HTML разрешён |
| Footer карточки | **Не делаем** (пока) |
| Explore mobile | **Обязателен** |
| Фон | Один файл на `kvartal_id` |
| `highlight.color` default | **`#5B8FB8`** как Sigma `facadeHighlight` |

---

## 1. Референсы и стратегия reuse

### 1.1. Обязательные пути Sigma (читать / копировать логику)

| Артефакт | Путь | Что переносить в EM |
|----------|------|---------------------|
| Контроллер фасада | `sites/sigma/sahmatka/fw/controllers/ctr__facades.php` | `require_admin`, path resolve/upload/absolute_url/`assert_within_*`, `normalize_points`, `upload_error_message`, `raster_ext_for_imagetype`, editor tpl payload, clear+upload acts |
| Шаблон editor | `…/fw/templates/facades/editor.php` | DOM panel-card / dirty / upload / messages / demo / map |
| CSS editor | `…/css/facade_editor.css` (+ снимок `ref/sigma/`) | **1:1 визуал**; rename `.facade-editor`→`.genplan-editor` |
| JS editor | `…/js/facade_editor.js` | dirty/`pendingDeletes`/Save-Cancel/`showMessage`/Draw/Path.Drag/`beforeunload`/upload/modals/`L.drawLocal` RU / min-zoom / maxBounds |
| Виджет | `…/js/facade_widget.js` | см. §1.2 — **блочный** перенос утилит + explore + highlight CSS vars |
| Demo | `…/templates/facades/widget_demo.php` | layout + snippet highlighter |
| Docs | `.doc/interactive-facades/{architecture,widget,api,porting}.md` | контракты координат / mount |
| Задачи | `sites/sigma/.doc/tasks/3/`, `tasks/4/` | история UX dirty/explore |

### 1.2. Что копировать из `facade_widget.js` почти дословно

Перенос **функций/паттернов** в `genplan_widget.js` (переименовать ctr в detectApiBase на `genplans`):

| Блок Sigma | Назначение в генплане |
|------------|------------------------|
| `detectApiBase()` | `…/ajax_router.php?ctr=genplans` |
| `normalizeWidth`, `resolveEl` | mount |
| `normalizeHexColor`, `clamp01`, `normalizeHighlightOpts` | опция `highlight` |
| `_applyHighlightCssVars` | CSS variables на shadow root |
| SVG poly idle/hover через CSS vars | подсветка объектов |
| `acquireScrollLock` / `releaseScrollLock` | explore fullscreen |
| Explore layer + pan/pinch/transform stage | **обязательный** mobile/desktop explore |
| Shadow DOM host / destroy | изоляция стилей |
| `prefersReducedMotion` | отключение лишних transition |

**Не переносить:** chessboard, floor plan, apartment card, booking, breadcrumbs фасада, hash `#fw=`, scrollReveal этажей, glass-tooltip фасада.

**Визуал карточки/labels** — по макетам заказчика (белая card), не glass `.fw-tooltip`.

### 1.3. Макеты

| Файл | Фиксирует |
|------|-----------|
| `ref/mockup-genplan-overview.png` | labels + стрелка + status-dot + заливка hover (**иконки на скрине — не реализуем**, Q3) |
| `ref/mockup-genplan-tooltip.png` | белая карточка: title, статус, meta, footer-link |

---

## 2. Продуктовая цель (production)

Для каждого `homes_kvartal_id`:

1. Админ загружает **один** фон генплана.
2. Обводит объекты полигонами (Leaflet.Draw, UX как фасад).
3. В режиме «Подписи»: ручной **title** (HTML); если дом не выбран — title **обязателен**; опциональный select дома (preview живых полей); свободный URL клика; якорь label.
4. `GenplanWidget.mount({ kvartalId, highlight?… })`:
   - labels всегда (текст title без HTML-тегов / sanitized plain);
   - hover → заливка `highlight` + карточка (§6.3);
   - click → `linkUrl`;
   - explore обязателен.

**Вне scope:** FacadeWidget/планы на EM; иконки amenities; footer карточки; несколько ракурсов; правки Sigma; overlap-check.

---

## 3. Модель данных

### 3.1. Миграция `sites/em/sahmatka/migrations/005_genplan_polygons.sql`

```sql
-- genplan_polygons: объекты интерактивного генплана ЖК (EM, production)
CREATE TABLE IF NOT EXISTS `genplan_polygons` (
  `genplan_polygon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kvartal_id`         INT UNSIGNED NOT NULL COMMENT 'homes_kvartal.homes_kvartal_id',
  `home_id`            INT UNSIGNED NULL DEFAULT NULL COMMENT 'опц. soft FK homes.home_id; публичные status/meta — LIVE из homes',
  `title`              TEXT NOT NULL COMMENT 'ручной HTML-заголовок; обязателен если home_id IS NULL',
  `link_url`           VARCHAR(512) NULL DEFAULT NULL COMMENT 'свободный URL клика',
  `label_x`            INT NULL DEFAULT NULL COMMENT 'якорь label px; NULL=центроид',
  `label_y`            INT NULL DEFAULT NULL COMMENT 'CRS.Simple y=0 у низа',
  `points`             TEXT NOT NULL COMMENT 'JSON [[x,y],…] px оригинала',
  `sort_order`         INT NOT NULL DEFAULT 0,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`                TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`genplan_polygon_id`),
  KEY `idx_gp_kvartal` (`kvartal_id`, `del`),
  KEY `idx_gp_home` (`home_id`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Не храним:** `status_*`, `meta_*`, `footer_*`, `icon`, `object_type`, `color` — статус/сдача/адрес при `home_id` резолвятся **живо** из `homes` в `widget_data`; заливка — только `highlight` в mount; footer вне scope.

### 3.2. Правила полей

| Поле | Правило |
|------|---------|
| `title` | Ручной ввод, **HTML разрешён** (`TEXT`). Валидация save: если `home_id` пуст — после `strip_tags` строка не пустая. Если `home_id` задан — title может быть пустым → label/карточка берут `homes.title` live |
| `home_id` | Опционально; при save — дом ∈ кварталу |
| `link_url` | Свободный URL |
| `points` | ≥3; `normalize_points` как facades |
| `del` | soft-delete |
| Overlap | не проверяем |

**HTML в title (allowlist для публички):** `b`, `strong`, `i`, `em`, `br`, `span`, `a[href]` (только `http`/`https`/`mailto`). Остальное вырезать на выдаче `widget_data` и/или в виджете перед `innerHTML`. Label-пузырь: **plain text** = `strip_tags(title)` (или live home title). Карточка: sanitized HTML.

### 3.3. Фон

```
FS:   sites/em/genplans/{kvartal_id}.{jpg|jpeg|png|webp}
URL:  /genplans/{kvartal_id}.{ext}
ABS:  https://{HTTP_HOST}/genplans/{kvartal_id}.{ext}?v={mtime}
```

Priority ext / 20 MB / sibling cleanup / `assert_within_genplans` — **копия** логики facades.  
`sites/em/.gitignore`:

```gitignore
/genplans/*
!/genplans/.gitkeep
```

### 3.4. Координаты

Как `.doc/interactive-facades/architecture.md` и facade editor/widget:

1. Хранение px оригинала, y=0 низ (CRS.Simple).
2. Leaflet admin: `x=lng`, `y=lat`.
3. SVG widget: `ySvg = H - y`.
4. Label HTML: один способ (`left%` + `top%` от `H-labelY`) — зафиксировать в коде.

---

## 4. Backend `ctr__genplans`

**Файл:** `sites/em/sahmatka/fw/controllers/ctr__genplans.php`  
**База:** адаптированный порт хелперов из `ctr__facades.php`.

```php
class ctr__genplans extends ctr__
{
    var $table = 'genplan_polygons';
    var $key_filed = 'genplan_polygon_id';
    var $ctr = 'genplans';
    var $title = 'Интерактивный генплан';
    const IMAGE_EXT_PRIORITY = ['jpg', 'jpeg', 'png', 'webp'];
    const MAX_UPLOAD_BYTES = 20971520;
}
```

### 4.1. Acts

| act | Access | Назначение |
|-----|--------|------------|
| `index` | admin | список ЖК + фон?/кол-во полигонов + ссылки |
| `editor` | admin | Leaflet UI |
| `widget_demo` | admin | demo + snippet |
| `get_polygons` | admin | JSON объектов |
| `save_polygon` | admin | insert/update |
| `delete_polygon` | admin | soft-delete |
| `upload_image` | admin | фон |
| `clear_all` | admin | soft-delete все + удалить файл |
| `homes_options` | admin | select домов квартала |
| `home_autofill` | admin | данные дома для подстановки в форму (при change select) |
| `widget_data` | **public** | payload виджета |

Каждый admin act: `require_admin()` как в facades. EM `ajax_router` имеет `1==1` — на роутер не полагаться.

### 4.2. Path helpers

Зеркало `fasades_*` → `genplans_*` (`dirname(__DIR__, 3)/genplans`).  
`absolute_url` + `?v=mtime` — обязательно для embed.

### 4.3. `act__editor` tpl data

```php
$tpl = [
  'kvartal_id'       => $kvartal_id,
  'kvartal_title'    => $kvartal['title'],
  'image_url'        => $image_url,
  'image_w'          => $image_w,
  'image_h'          => $image_h,
  'ajax_base'        => '/sahmatka/ajax_router.php?ctr=genplans',
  'max_upload_bytes' => self::MAX_UPLOAD_BYTES,
  'widget_demo_url'  => $r->acturl('genplans', 'widget_demo', 'iframe_router.php')
                        . '&kvartal_id=' . $kvartal_id,
];
```

### 4.4. Admin JSON-контракты

#### `get_polygons` — GET `kvartal_id` → массив

```json
[{
  "id": 1,
  "homeId": 48,
  "title": "Дом <b>26</b>",
  "linkUrl": "https://…",
  "labelX": 1420,
  "labelY": 980,
  "points": [[x,y],…],
  "sortOrder": 0
}]
```

Сырой `title` из БД (HTML). Полей status/meta/footer в admin get **нет**.

#### `save_polygon` — POST

Обязательны: `kvartal_id`, `points` (≥3).  
Поля: `title`, `home_id`, `link_url`, `label_x`, `label_y`, `sort_order`, `points`.  
`genplan_polygon_id=0` create; update только при совпадении `kvartal_id`.

**Валидация title:** если `home_id` пуст → `trim(strip_tags(title)) !== ''`, иначе `{success:false,message:'Укажите заголовок'}`. При заданном `home_id` title может быть пустым.

`home_id` пустой → NULL; иначе:

```sql
SELECT home_id FROM homes
WHERE home_id = {id}
  AND CAST(kvartal AS UNSIGNED) = {kvartal_id}
  AND (del = 0 OR del IS NULL)
LIMIT 1
```

(Если есть `homes_kvartal_id` — OR; проверить `SHOW COLUMNS`.)

Успех: `{success:true,id:N}`.

#### `delete_polygon` / `upload_image` / `clear_all`

Как facades (kvartal вместо home; clear_all = clear_facade).

#### `homes_options` — GET `kvartal_id`

```json
{ "success": true, "homes": [ { "id": 48, "title": "712" } ] }
```

Сортировка: `order`, `title`.

#### `home_autofill` — GET `kvartal_id`, `home_id`

Только **preview в редакторе** (в таблицу генплана status/meta **не пишутся**). OnChange select.

```json
{
  "success": true,
  "titleSuggest": "Дом 712",
  "statusText": "Дом сдан",
  "statusTone": "ok",
  "metaDelivery": "Сдан: IV 2021",
  "metaAddress": "Адрес: …"
}
```

| Источник | Поле |
|----------|------|
| `homes.title` / `long_title` | `titleSuggest` → в input title **только если title пуст** |
| `complite=1` | preview «Дом сдан» / `ok`; иначе «Строится» / `warn` |
| delivery / ready_quarter+built_year | preview metaDelivery |
| adress | preview metaAddress |

UI: read-only блок «На сайте (живые данные дома): …». Сброс select → `home_id=null`, title не трогать, скрыть preview.

### 4.5. `widget_data` — GET `kvartal_id` (public)

```json
{
  "success": true,
  "kvartalId": 7,
  "title": "ЖК …",
  "imageUrl": "https://…/genplans/7.jpg?v=…",
  "imageWidth": 3200,
  "imageHeight": 2000,
  "objects": [
    {
      "id": 1,
      "homeId": 48,
      "titleHtml": "Дом <b>26</b>",
      "titleText": "Дом 26",
      "statusText": "Дом сдан",
      "statusTone": "ok",
      "metaDelivery": "Сдан: 4 квартал 2021",
      "metaAddress": "Адрес: ул. …",
      "linkUrl": "https://…",
      "labelX": 1420,
      "labelY": 980,
      "points": [[x,y],…]
    }
  ]
}
```

**Резолв объекта (live):**

1. `titleHtml` = sanitize allowlist(`polygon.title`); `titleText` = strip_tags.
2. Если `titleText` пуст и есть `home_id` → взять `homes.title` live; `titleHtml` = escape(text).
3. Если `home_id` и дом найден → `status*` / `meta*` **LIVE** (те же правила, что autofill). Иначе все null.
4. Footer не отдаём.
5. Дом пропал/чужой квартал → без status/meta, не валить весь ответ.
---

## 5. Admin UI — оформление = Sigma

### 5.1. CSS

1. Copy `facade_editor.css` → `genplan_editor.css`.
2. Rename `.facade-editor`→`.genplan-editor`, `.facade-draw-`→`.genplan-draw-`, `.facade-tool-`→`.genplan-tool-`.
3. **Не менять** визуальные токены.
4. В конец — только блоки mode-toggle / meta-panel на существующих btn/input стилях.

Эталон: [ref/sigma/facade_editor.css](./ref/sigma/facade_editor.css).

### 5.2. Шаблон `fw/templates/genplans/editor.php`

Каркас = [ref/sigma/facades_editor.php](./ref/sigma/facades_editor.php):

- head «Разметка генплана — {title}»
- mode: **Полигоны** | **Подписи**
- dirty Save / Cancel
- upload «Загрузить генплан» / clear «Очистить генплан»
- meta-panel (режим Подписи):  
  - `title` — textarea, **HTML разрешён**; hint «обязателен, если дом не выбран»  
  - `home_id` select («— не выбран —» + названия)  
  - read-only preview live-полей дома (после autofill; не save)  
  - `link_url`  
  - якорь label на карте  
  - **нет** полей status/meta/footer для сохранения  
- messages + «Демо виджет»
- `#genplan_map`

**Не переносить:** селекты секция/этаж, clear floor, copy-to-floor.

**Перенести инструменты:** Draw polygon, edit vertices, remove, Path.Drag move, pick-select объекта, RU `L.drawLocal`, modal 3-кнопки, beforeunload.

### 5.3. `GENPLAN_CONFIG`

```js
window.GENPLAN_CONFIG = {
  kvartalId: N,
  imageUrl: '',
  imageWidth: 0,
  imageHeight: 0,
  ajaxBase: '/sahmatka/ajax_router.php?ctr=genplans',
  maxUploadBytes: 20971520
};
```

### 5.4. `genplan_editor.js`

Порт паттернов `facade_editor.js`:

| Тема | Правило |
|------|---------|
| Dirty | на **весь** квартал (нет этажей): geometry + meta + pendingDeletes |
| Save | flush deletes → save изменённых слоёв |
| Cancel | reload get_polygons |
| Режим Подписи | Draw off; клик → форма; change home select → `home_autofill` |
| Label marker | circleMarker; default centroid |
| Нет фона | блок Draw + message как у фасада |

### 5.5. Ссылка из `homes_kvartal::act__edit`

```php
<?php if ($id): ?>
<h2>Интерактивный генплан</h2>
<p>
  <a href="/sahmatka/iframe_router.php?ctr=genplans&amp;act=editor&amp;kvartal_id=<?= (int)$id ?>"
     target="_blank" rel="noopener">Редактировать интерактивный план</a>
  (полный экран)
</p>
<?php endif; ?>
```

Паттерн = Sigma homeseditor → facades.

---

## 6. Публичный виджет `GenplanWidget` (production)

**Файл:** `template/default/js/genplan_widget.js`  
**Версия:** `1.0.0` · глобал `window.GenplanWidget`

### 6.1. Embed

```html
<div id="genplan"></div>
<script src="https://{HOST}/sahmatka/template/default/js/genplan_widget.js?v=1.0.0"></script>
<script>
GenplanWidget.mount({
  el: '#genplan',
  kvartalId: 7
  // highlight / exploreFullscreen — по желанию; дефолты = production
});
</script>
```

### 6.2. Опции mount (зеркало Sigma highlight API)

| Опция | Default | Смысл |
|-------|---------|--------|
| `el` | — | обязателен |
| `kvartalId` | — | обязателен |
| `apiBase` | auto `ctr=genplans` | как `detectApiBase` |
| `width` | `'100%'` | `normalizeWidth` |
| `maxHeight` | `Math.round(innerWidth * 2)` | как фасад |
| `exploreFullscreen` | `true` | **обязательный дефолт** |
| `openLinksInNewTab` | `true` | |
| `highlight` | см. ниже | аналог **`facadeHighlight`** |
| `onObjectClick` | `null` | `preventDefault` → не открывать URL |
| `locale` | RU | строки UI (Загрузка…, закрыть explore) |

#### `highlight` — контракт как `facadeHighlight` в Sigma

Источник: `facade_widget.js` → `normalizeHighlightOpts` + `_applyHighlightCssVars` + CSS `.fw-poly`.  
Документация: `.doc/interactive-facades/widget.md` (§ `facadeHighlight`).

```js
highlight: {
  color: '#5B8FB8',   // === Sigma facadeHighlight.color (decisions U4)
  opacity: 0.58,      // === facadeHighlight.opacity
  idleOpacity: 0,     // в покое полигон невидим
  // revealOpacity — не используем (нет scrollReveal); игнор при normalize
}
```

Правила обводки — **как в Sigma**: тот же `color`, `stroke-opacity = opacity * 0.7`, `stroke-width: 2`.

CSS variables на shadow root (префикс `--gw-`):

```text
--gw-hl-color
--gw-hl-opacity
--gw-idle-opacity
--gw-stroke-opacity   (= opacity * 0.7)
```

Полигоны:

```css
.gw-poly { fill-opacity: var(--gw-idle-opacity, 0); stroke-opacity: 0; … }
.gw-poly.is-hover, .gw-poly.is-active {
  fill: var(--gw-hl-color);
  stroke: var(--gw-hl-color);
  fill-opacity: var(--gw-hl-opacity);
  stroke-opacity: var(--gw-stroke-opacity);
}
```

**Не** брать цвет из строки БД полигона.

### 6.3. Labels и тултип

#### Label

Показывать, если `titleText` непустой (из title HTML или live `homes.title`).

- Белый пузырь + стрелка (макет overview).
- Текст = **`titleText`** (plain, без тегов).
- Status-dot справа: только если live `statusText`/`statusTone` (объект с домом).
- `pointer-events: none`.

#### Карточка hover

| Режим | Карточка |
|-------|----------|
| `homeId` + live meta | titleHtml (sanitized) · статус · metaDelivery · metaAddress — **без footer** |
| Без дома | только **titleHtml** (ручной HTML; обязателен при save) |
| Пустые meta | строки не рендерить |

Макет tooltip.png: блок footer на скрине **не реализуем** (U2).

### 6.4. Click

1. Если `onObjectClick` и `preventDefault` — стоп.
2. Иначе если `linkUrl` — `window.open` / location по `openLinksInNewTab`.
3. Без URL — no-op (заливка/card остаются).

### 6.5. Explore (обязателен)

Порт из FacadeWidget:

- fullscreen/explore layer + scroll-lock refcount;
- pan/pinch stage;
- кнопка закрытия;
- desktop: можно enter explore по кнопке/дабл-тапу (как удобно портировать; минимум — mobile explore при взаимодействии с картой в узком viewport);
- breakpoint UI: ориентир Sigma `min-width: 900px`.

Mobile tap: 1) highlight+card 2) повтор / CTA → linkUrl 3) вне → сброс.

### 6.6. Instance API

```js
var w = GenplanWidget.mount(opts);
w.destroy();
w.getState(); // { kvartalId, activeObjectId, exploring }
```

### 6.7. Demo

Адаптация `facades/widget_demo.php`: mount + snippet simple/full с перечислением `highlight` как в demo фасада для `facadeHighlight`.

---

## 7. File manifest (`sites/em/` only)

| Путь | Действие |
|------|----------|
| `sahmatka/migrations/005_genplan_polygons.sql` | create |
| `sahmatka/fw/controllers/ctr__genplans.php` | create (порт хелперов facades) |
| `sahmatka/fw/templates/genplans/editor.php` | create |
| `sahmatka/fw/templates/genplans/widget_demo.php` | create |
| `sahmatka/template/default/css/genplan_editor.css` | copy+rename Sigma CSS |
| `sahmatka/template/default/js/genplan_editor.js` | create (порт dirty/draw) |
| `sahmatka/template/default/js/genplan_widget.js` | create (порт highlight/explore/utils) |
| `sahmatka/template/default/libs/leaflet/**` | copy |
| `sahmatka/template/default/libs/leaflet-draw/**` | copy |
| `sahmatka/template/default/libs/leaflet-path-drag/**` | copy |
| `genplans/.gitkeep` | create |
| `.gitignore` | create (`genplans/*`) |
| `sahmatka/fw/controllers/ctr__homes_kvartal.php` | ссылка |

Copy libs — PowerShell из plan-аудита (только запись в EM).

---

## 8. Стейджи (production roadmap)

Нет «потом explore / потом цвета». Explore и `highlight` входят в поставку виджета.

### Stage A — каркас редактора (Sigma-паритет admin)

- миграция, libs, CSS copy, `ctr__genplans` CRUD+upload+clear, editor draw/dirty, ссылка из ЖК, gitignore

### Stage B — подписи + дом

- режим Подписи, form fields, label anchor, `homes_options` + `home_autofill` on select change

### Stage C — виджет production

- `widget_data`, `GenplanWidget`: SVG, labels, white card, **highlight opts**, **explore+scroll-lock**, click URL, demo+snippet, mobile tap UX

### Stage D — приёмка

- pixel-check vs mockups, embed с чужого origin, regress admin dirty/upload, сверка CSS с `ref/sigma`, чеклист §9

---

## 9. Критерии приёмки

### Editor
- [ ] Визуал панели = Sigma facade editor
- [ ] Dirty Save/Cancel + beforeunload + RU Draw
- [ ] Без `home_id` → save без title (после strip_tags) **отклонён**
- [ ] С `home_id` → title может быть пустым; preview live-полей read-only
- [ ] Title сохраняет HTML
- [ ] Upload/replace/clear_all; ownership checks

### Widget
- [ ] Embed absolute + CORS
- [ ] Default `highlight.color === '#5B8FB8'`, opacity 0.58, idle 0 — как Sigma
- [ ] Переопределение `highlight` с хоста работает
- [ ] С домом: status/delivery/address **меняются при смене данных в homes** без пересохранения полигона
- [ ] Без дома: label + card = title; HTML в card, plain в label
- [ ] Нет footer в карточке
- [ ] Click → свободный URL; explore + scroll-lock
- [ ] Нет иконок amenities; Shadow DOM; без Leaflet

### Scope
- [ ] Diff только `sites/em/**`

---

## 10. Матрица reuse (для ревьюера кода)

| Sigma (проверено) | EM genplan |
|-------------------|------------|
| `require_admin` + JSON access deny | copy |
| image resolve/upload/absolute_url | copy → genplans |
| `normalize_points` | copy |
| editor CSS | copy+rename |
| dirty / pendingDeletes / beforeunload | port |
| Leaflet CRS.Simple + Draw + Path.Drag | same libs copy |
| `normalizeHighlightOpts` + CSS vars | port → `highlight` |
| explore + scrollLock | port |
| `detectApiBase` | port, ctr=`genplans` |
| widget_demo snippet page | adapt |
| glass tooltip / chess / floor / booking | **не переносить** |

---

## 11. Антипаттерны

- Править Sigma «для общего кода»
- Цвет заливки из колонки БД вместо `highlight`
- Иконки amenities / footer карточки «раз уж на скрине»
- Откладывать explore
- Отдавать сырой HTML title без allowlist-sanitize
- Snapshot status/meta в БД вместо live JOIN
- Root-relative `imageUrl` в `widget_data`
- Полагаться на auth `ajax_router`
- Редизайн CSS редактора

---

## 12. Открытые вопросы

**Нет.** Все ответы зафиксированы в [decisions.md](./decisions.md).

---

## 13. Старт работ

1. Прочитать `decisions.md`, этот plan, макеты, `ref/sigma/facade_editor.css`.
2. Пробежать `facade_widget.js`: `normalizeHighlightOpts`, `_applyHighlightCssVars`, explore, scrollLock, `detectApiBase` (дефолт color `#5B8FB8`).
3. Copy libs+CSS → EM; миграция 005 (схема без status/meta/footer).
4. Stage A → B → C → D.

---

*Обновлено 10.08.2026: live homes; без footer; title HTML + обязателен без дома; highlight default Sigma `#5B8FB8`.*
