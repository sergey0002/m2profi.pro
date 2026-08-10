# Аудит плана задачи 10 (EM генплан)

**Дата:** 10.08.2026  
**Область проверки:** [plan.md](./plan.md) / [doc.md](./doc.md) ↔ код Sigma (facades) ↔ код EM (`homes_kvartal`, ajax_router) ↔ макеты  
**Статус:** дефекты плана найдены; исправления внесены в обновлённый [plan.md](./plan.md)  
**Код не реализован** — правки только в документации + `ref/`

---

## 1. Метод аудита

| Источник | Путь |
|----------|------|
| Черновик плана | `sites/em/.doc/tasks/10/plan.md` (до аудита) |
| Sigma редактор фасада | `sites/sigma/sahmatka/fw/controllers/ctr__facades.php` |
| Sigma шаблон/CSS/JS | `fw/templates/facades/editor.php`, `css/facade_editor.css`, `js/facade_editor.js` |
| Sigma виджет | `js/facade_widget.js` (Shadow DOM, explore, tooltip) |
| Sigma задача 3/4 | `.doc/tasks/3/`, `.doc/tasks/4/`, `.doc/interactive-facades/` |
| Канон переноса | `.doc/interactive-facades/porting.md`, `architecture.md`, `widget.md` |
| EM ЖК | `sites/em/sahmatka/fw/controllers/ctr__homes_kvartal.php` |
| EM ajax | `sites/em/sahmatka/ajax_router.php` (`Allow-Origin: *`, сессия `\|\| 1==1`) |
| EM миграции | `001…004_*.sql` → следующий номер **005** |
| Макеты заказчика | `ref/mockup-genplan-overview.png`, `ref/mockup-genplan-tooltip.png` |
| Снимок UI Sigma | `ref/sigma/facade_editor.css`, `facades_editor.php`, `facades_widget_demo.php` |

---

## 2. Найденные дефекты черновика плана

### D1 — HIGH: «overlap-check как у фасада» — в текущем Sigma **нет**

**Черновик:** «Overlap-check, soft delete — по паттерну facade».

**Факт:** в `ctr__facades::act__save_polygon` нет проверки пересечения/bbox. Упоминание в `tasks/3/audit.md` устарело относительно кода.  
**Исправление:** в MVP генплана **не** обещать overlap-check. Soft-delete — да. Overlap — backlog (опционально).

### D2 — HIGH: оформление редактора описано двусмысленно («неймспейс genplan-editor»)

**Черновик:** «те же CSS-классы / неймспейс `genplan-editor`».

**Заказчик:** все стили и оформление редактора — **с Sigma**.  
**Исправление:** обязательно **скопировать** `facade_editor.css` в EM; визуальные правила **1:1**. Допускается только механический rename `.facade-editor` → `.genplan-editor` (чтобы не путать с будущим портом фасада). Запрещены свои цвета/радиусы/типографика панели. Снимок эталона лежит в `ref/sigma/`.

### D3 — HIGH: тултип виджета ≠ glass-tooltip фасада

**Черновик** ссылался на паттерны FacadeWidget.  
**Макет** (`mockup-genplan-tooltip.png`): **белая** карточка, тень, зелёный статус, синяя ссылка — не тёмный glass `.fw-tooltip`.  
**Исправление:** labels + tooltip card проектировать **по макетам заказчика**; у фасада брать только Shadow DOM / SVG / explore / `detectApiBase`.

### D4 — MEDIUM: `.gitignore` в корне репо противоречит «только EM»

Черновик предлагал править корневой `.gitignore` (`sites/*/genplans/*`).  
**Исправление:** игнор только через **`sites/em/.gitignore`** (`genplans/*`), без правок корня и без `sites/*/…`.

### D5 — MEDIUM: сохранение «по этажу» vs генплан «все объекты сразу»

Фасад dirty-save привязан к section+floor. У генплана нет этажей — dirty относится ко **всем** объектам карты.  
**Исправление:** явно: одна кнопка «Сохранить» / «Отменить» на весь квартал; модель `pendingDeletes` + `isNew` / geometryDirty как в `facade_editor.js`, но без селектов секции/этажа.

### D6 — MEDIUM: не зафиксирован wireframe панели редактора

Нужна таблица «что убрать из facade UI / что добавить» (секция/этаж → режим Полигоны|Подписи + поля meta).  
**Исправление:** §4 обновлённого plan.

### D7 — MEDIUM: неполная спецификация API (нет точных POST-полей и ошибок)

**Исправление:** каждый act — таблица входов/выходов/кодов ошибок.

### D8 — MEDIUM: `homes.kvartal` vs `homes_kvartal_id`

В EM join: `` homes_kvartal_id = homes.kvartal `` (поле называется `kvartal`, не `homes_kvartal_id`).  
В compred иногда `IF(h.kvartal > 0, h.kvartal, h.homes_kvartal_id)` — проверить наличие второй колонки на EM.  
**Исправление:** в plan SQL для `homes_options` зафиксирован запрос + fallback.

### D9 — LOW: оценка стейджей без чеклиста файлов/копирования libs

**Исправление:** полный file manifest + PowerShell copy-list только **в** `sites/em/`.

### D10 — LOW: открытые вопросы без принятых дефолтов «для реализации сейчас»

**Исправление:** §9 — все Q закрыты дефолтами; реализация идёт по ним, пока заказчик не отменит.

---

## 3. Что в черновике было верно (сохраняем)

- Область только EM; отдельный `ctr=genplans`, не порт FacadeWidget.
- Родитель = `homes_kvartal_id`; фон вне БД.
- Leaflet только в admin; публичка Shadow DOM + SVG; y-flip контракт.
- Абсолютные URL картинок; `detectApiBase` → `ctr=genplans`.
- `check_access('admin')` в каждом admin act (EM ajax_router дырявый: `1==1`).
- CORS уже `Access-Control-Allow-Origin: *` на EM ajax — для публичного embed ок.
- Структурированные поля тултипа (не raw HTML).
- Ссылка из `homes_kvartal` edit.

---

## 4. Вердикт

Черновик годился как эскиз, но был **неоднозначен для кодирования** (CSS, dirty-модель, overlap, gitignore, API, тултип).  
Обновлённый [plan.md](./plan.md) — единственный источник истины для реализации.
