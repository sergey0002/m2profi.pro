# #16 Genplan mobile: scroll, overlay enter, ghost-tap

| | |
|---|---|
| **Branch** | `feature/16-genplan-mobile-touch` |
| **Статус** | план после аудита (код ещё не пишем) |
| **Версия as-is** | `GenplanWidget` **2.5.20** |
| **Главный файл** | `sites/em/sahmatka/template/default/js/genplan_widget.js` |
| **Демо** | `sites/em/sahmatka/fw/templates/genplans/widget_demo.php` |
| **Где лежит план** | `sites/em/.doc/tasks/16-genplan-mobile-touch.md` |
| **Связанные стейджи** | #10 виджет, #14 CTA/marker, #15 tooltip skins |
| **Аудит** | 2026-08-13 — код виджета + demo + em-nsk.ru + сравнение с Sigma Facade |

---

## 0. Вердикт аудита (кратко)

План в целом **верный**: оверлей с центральной кнопкой + убийство pan/tap→explore + suppress ghost-CTA.

Но предыдущая версия плана **недооценивала** несколько точек входа и поверхность hit-testing. Исправления ниже обязательны до кода.

### Критические находки

1. **Три** пути в explore на mobile, не два:
   - pan > 8px → `_enterExplore` (~2197–2202);
   - short tap anywhere → `_shouldUseInlineExploreTap` в `endPointer` (~2231–2234);
   - **тап по дому** → `_handleObjectActivate` → тот же `_shouldUseInlineExploreTap` (~2024–2026) — на свёрнутом coarse дом **никогда не открывает тултип**, только explore.
2. **Scroll lock не виноват в «не скроллится»** в свёрнутом виде: `acquireScrollLock` только внутри `_enterExplore`. Страницу ломает **нежелательный вход в explore** (и уже потом lock).
3. Слушатели Pointer Events висят на **viewport**, не на stage. `pointer-events: none` только на `.gw-stage` **недостаточен** — события всё равно приходят на viewport.
4. Ghost-CTA guard **сейчас отсутствует**. Suppress должен покрывать не только `.gw-label__cta`, но и **`.gw-label__apts a`**, и оба скина (`card` → `.gw-label__card`, `expand` → `.gw-label__box`).
5. На **em-nsk.ru** виджет пока **не встроен** (статичные картинки планов). QA — demo `iframe_router` + локальный mount; iframe/parent scroll — отдельная матрица.
6. **jQuery Mobile / Hammer не нужны.** Виджет — Shadow DOM, без jQ, уже на Pointer Events. Sigma Facade хуже (всегда `setPointerCapture` на collapsed) — **не копировать**.

---

## 1. Цель стейджа

На мобиле:

1. **Свёрнутый inline** — страница скроллится поверх карты; визуальный **оверлей + кнопка по центру**; случайный тап/свайп **не** открывает explore.
2. **Explore** — pan/pinch/тултипы; тап по дому открывает тултип **без** мгновенного перехода по CTA под пальцем.

Десктоп не ломаем.

---

## 2. As-is: карта входов в explore (mobile)

Общие гейты: `isCoarsePointer()` / `allowsExploreMode()` (~201–220: `(hover:none) and (pointer:coarse)` **или** `max-width: 768px`), `exploreFullscreen` (дефолт `true`), `_enterExplore` early-out если уже exploring / destroyed / `exploreFullscreen === false`.

| # | Путь | Цепочка | ~Строки | Условие |
|---|------|---------|---------|---------|
| A | Кнопка «Увеличить» | `.gw-btn-explore` click → `_enterExplore` | 830–838; CSS 467–468 | coarse + not explore (CSS) |
| B | Pan по карте | `pointermove` → `_enterExplore` | 2197–2202 | inline, `_moved` (hypot > **8**), coarse |
| C | Тап anywhere | `endPointer` → `_shouldUseInlineExploreTap` → `_enterExplore` | 2214–2234; helper 1999–2000 | `!_moved` && duration < **500** ms |
| D | Тап по дому / poly | `endPointer` → `_handleObjectActivate` → `_shouldUseInlineExploreTap` | 2252–2256; 2022–2026 | то же; **тултип на collapsed не открывается** |
| E | Keyboard Enter/Space | stage keydown → `_handleObjectActivate` | 2300–2308 | редко на телефоне; тот же helper |

Комментарий в коде ~2198: «мобилка: жест pan → вход в explore (**как Sigma**)» — осознанный порт, сейчас мешает скроллу страницы.

**To-be:** оставить только **осознанный tap по центральной gate-кнопке** (замена пути A). Пути B/C/D/E на coarse+inline — **выключить**.

---

## 3. As-is: почему «не скроллится»

### 3.1. Scroll lock (не причина в collapsed)

```
acquireScrollLock  → body.overflow=hidden + document touchmove preventDefault {passive:false}
releaseScrollLock  → refcount; на 0 снимает listener
```

- Вызов: только `_enterExplore` (~2422) + `_scrollLockHeld++`.
- Снятие: `_exitExplore`, `destroy` (while-loop).
- Refcount общий на страницу (несколько виджетов) — ок.
- **Collapsed lock не ставит.**

Риски lock (уже в explore, не #16 core, но учесть в тестах):

- блокирует `document` touchmove целиком (iframe document / nested scrollers);
- не трогает `html` / `position:fixed` — возможны iOS rubber-band quirks;
- `removeEventListener` без `{passive:false}` — обычно ок, на экзотике проверить.

### 3.2. Реальная причина

JS на viewport:

1. Pan > 8px → explore → затем lock.
2. Short tap → explore → затем lock.
3. `setPointerCapture` на coarse inline **уже отключён** (~2110–2113) — хорошо, Sigma так не делает.
4. `.gw-poly { pointer-events: all }` даже в collapsed — poly участвует в hit-test; labels уже `pointer-events: none` на coarse collapsed (~454).

`touch-action: manipulation` на coarse viewport **не** отменяет Pointer Events handlers.

---

## 4. As-is: ghost-tap CTA

Цепочка:

1. В explore тап по дому → `_setActive` → `_setLabelExpanded`.
2. Тултип появляется под координатами пальца.
3. Браузер шлёт compat `click` → попадает в новый `<a class="gw-label__cta">` (или apt-link).

Сейчас:

- `_bindHostLink` (~1020–1041): hash → `preventDefault` + scroll; external → optional `_blank`, только `stopPropagation`.
- Apt links (~1086–1094): только `stopPropagation`, **не** через `_bindHostLink`.
- `pointerdown` early-return для уже существующего `<a>` (~2102–2108) — **не** защищает от click на **только что** появившейся ссылке.
- **Нет** `_suppressLinkClicksUntil`, **нет** временного `pointer-events: none` на expanded surface.

Skin nuance:

| Skin | Где CTA | Риск under-finger |
|------|---------|-------------------|
| `card` | `.gw-label__card` (chip остаётся на якоре) | card может сесть над/рядом с пальцем; `::before` bridge снизу card (~496) тоже `pointer-events: auto` |
| `expand` | внутри `.gw-label__box` (чип морфится в карточку) | весь box растёт под пальцем — **высокий** риск |

---

## 5. Целевой UX (утверждённый)

### 5.1. Mobile свёрнут

```
┌─────────────────────────────┐
│  карта (приглушена)         │
│                             │
│      ┌───────────────┐      │
│      │  Увеличить    │      │  ← ЕДИНСТВЕННЫЙ вход в explore
│      └───────────────┘      │
│        .gw-explore-gate     │
└─────────────────────────────┘
        ↕ скролл страницы
```

Правила:

1. Скролл страницы, начатый на области карты — **работает**, explore не открывается.
2. Тап/свайп по карте вне кнопки — **no-op**.
3. Тултипы / poly hit / activate — **выкл**.
4. Вход в explore — **только** gate-кнопка.
5. Старой `.gw-btn-explore` (right/bottom) нет.

### 5.2. Mobile explore

1. Как сейчас: fullscreen layer, pan/pinch, close, Escape, scroll lock.
2. Тап по дому → тултип; **первый** жест не активирует ссылки.
3. Отдельный тап по CTA / apt → переход / hash-scroll.
4. Закрытие → снова gate-оверлей.

### 5.3. Desktop

Без mobile-gate. Hover/pan/click как сейчас.

### 5.4. `exploreFullscreen: false`

Gate **не показывать**. `_enterExplore` не вызывать. Coarse class для прочих правил может остаться, но без гейта/explore.

---

## 6. Жесты и зависимости

### 6.1. Решение: нативные Pointer Events

| Подход | Вердикт |
|--------|---------|
| jQuery Mobile | **Нет** — мёртвый, тяжёлый, виджет без jQ + Shadow DOM |
| Hammer.js / @use-gesture | **Нет** в этом стейдже; только если QA провалит native |
| Pointer Events + правила | **Да** — уже в файле |

Классификатор на collapsed **упрощается**: для карты жесты игнорируем; для gate-кнопки — обычный `click` на `<button>` (плюс защита: если pointer ушёл > threshold с кнопки — не открывать explore).

Полноценный `_classifyPointerGesture` нужен в основном в **explore** (tap vs pan), там он уже почти есть (`_moved`, 8px, 500ms). На collapsed — проще early-return всего PE pipeline.

### 6.2. Не «просто click» для CTA

Проблема — **click-through после мутации DOM**, не классический 300ms delay (современный Safari + `touch-action`). Лечится suppress + pe:none, не jQuery Mobile.

---

## 7. UI: `.gw-explore-gate`

### 7.1. DOM

Внутри `.gw-root` (sibling inline viewport), на instance:

```html
<div class="gw-explore-gate" aria-hidden="true">
  <button type="button" class="gw-explore-gate__btn">Увеличить</button>
</div>
```

Показ CSS-ом:

```css
.gw-explore-gate { display: none; }
.gw-root.is-coarse:not(.is-explore) .gw-explore-gate { display: flex; }
/* + JS: если !exploreFullscreen → не рендерить / не показывать */
```

### 7.2. CSS

```css
.gw-explore-gate {
  position: absolute;
  inset: 0;
  z-index: 7;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.4);
  pointer-events: none;
  box-sizing: border-box;
}
.gw-explore-gate__btn {
  pointer-events: auto;
  min-height: 44px;
  min-width: 44px;
  padding: 12px 22px;
  border: 0;
  border-radius: 8px;
  font: inherit;
  font-weight: 600;
  font-size: 15px;
  background: rgba(255,255,255,0.95);
  color: #111;
  box-shadow: 0 1px 6px rgba(0,0,0,0.22);
  -webkit-tap-highlight-color: transparent;
  cursor: pointer;
}
.gw-root.is-explore .gw-explore-gate { display: none !important; }
```

Старую `.gw-btn-explore` — не показывать (убрать из DOM или CSS forever `display:none`).

### 7.3. Почему pe:none на фоне + pe:none на карте

Только оверлей с pe:none **недостаточен**, если viewport всё ещё слушает pointerdown/move/up и зовёт `_enterExplore`. Нужен **двойной барьер**:

1. **JS:** coarse + !explore → не входить в explore ни по pan, ни по tap, ни по activate; early-return PE handler (не стартовать `_panStart` для activate).
2. **CSS:**

```css
.gw-root.is-coarse:not(.is-explore) .gw-poly {
  pointer-events: none !important; /* бить .gw-poly { pointer-events: all } */
}
.gw-root.is-coarse:not(.is-explore) .gw-stage,
.gw-root.is-coarse:not(.is-explore) .gw-labels-overlay {
  pointer-events: none !important;
}
.gw-root.is-coarse:not(.is-explore) .gw-viewport {
  touch-action: pan-y;
}
```

3. Gate-кнопка — единственный hit-target с `pointer-events: auto`.

### 7.4. A11y / locale

- Текст: `locale.explore` («Увеличить»).
- `aria-label` = тот же / «Увеличить интерактивный план».
- После `_exitExplore` — `gateBtn.focus()` (nice-to-have).
- `aria-hidden` на gate: `false` когда виден, `true` в explore.

---

## 8. Логика to-be (чеклист правок в коде)

### 8.1. Обязательно удалить / отключить на coarse+inline

| Что | Где | Действие |
|-----|-----|----------|
| Pan → explore | `pointermove` ~2197–2202 | **Удалить** блок |
| Tap anywhere → explore | `endPointer` ~2231–2234 | **Удалить** / сделать no-op |
| Object tap → explore | `_handleObjectActivate` ~2024–2026 | **Удалить** ветку `_shouldUseInlineExploreTap` |
| Keyboard → explore через helper | ~2300–2308 | отпадёт вместе с helper |
| Угловая кнопка | DOM + CSS `.gw-btn-explore` | Заменить gate |

`_shouldUseInlineExploreTap` после правок либо удалить, либо `return false` с комментарием.

### 8.2. Обязательно добавить

1. DOM/CSS `.gw-explore-gate` (§7).
2. `gateBtn.addEventListener('click', …)` → `_enterExplore()` + `stopPropagation`.
3. Guard: pointerdown на кнопке + move > threshold → не открывать explore.
4. Ghost-tap:
   - `CTA_SUPPRESS_MS = 400`;
   - `_suppressLinkClicksUntil` при открытии тултипа на coarse;
   - проверка в `_bindHostLink` **и** apt-links;
   - класс `.is-click-guard` на expanded surface (~400ms).
5. Collapsed CSS pe:none + `touch-action: pan-y` (§7.3).
6. Early-return в `_bindStageInteractions` для `!isExplore && isCoarsePointer()`.

### 8.3. Оставить

- `_enterExplore` / `_exitExplore` / scroll lock / pinch / desktop.
- `#15` placement/skins — кроме click-guard.
- API `mount({ exploreFullscreen, labelSkin, … })`.

### 8.4. Secondary (если A+B мало на QA)

**Вариант C:** на coarse при открытии тултипа предпочитать сторону **away from `clientY`**. Не блокер MVP.

---

## 9. Ghost-tap: алгоритм (уточнённый)

```js
var CTA_SUPPRESS_MS = 400;

// при expand на coarse:
self._suppressLinkClicksUntil = Date.now() + CTA_SUPPRESS_MS;
var surface = el.querySelector('.gw-label__card') || el.querySelector('.gw-label__box') || el;
surface.classList.add('is-click-guard');
clearTimeout(el._gwClickGuardTimer);
el._gwClickGuardTimer = setTimeout(function () {
  surface.classList.remove('is-click-guard');
}, CTA_SUPPRESS_MS);

// CSS:
// .gw-label.is-click-guard .gw-label__card,
// .gw-label.is-click-guard .gw-label__box { pointer-events: none !important; }

// в каждом link click (CTA + apt + hash):
if (Date.now() < (self._suppressLinkClicksUntil || 0)) {
  e.preventDefault();
  e.stopPropagation();
  return;
}
```

Покрыть **оба** скина и **все** интерактивные `<a>` в label.

---

## 10. Сравнение с Sigma Facade (не копировать)

| | Genplan (as-is) | Sigma Facade |
|--|-----------------|--------------|
| Shadow DOM, без jQ | да | да |
| Scroll lock | тот же паттерн | тот же |
| Угловая «Увеличить» | да | нет |
| Pan/tap → explore | да | да |
| `setPointerCapture` collapsed | **пропущен** (лучше) | **всегда** (хуже) |
| Gate-оверлей | to-be #16 | нет |

Genplan #16 — **осознанный fork** от «как Sigma».

---

## 11. Встраивание / окружение

| Контекст | Статус |
|----------|--------|
| Demo `widget_demo` (2 скина) | есть; основной QA-стенд |
| `iframe_router.php?ctr=genplans&act=widget_demo` | есть; без логина |
| em-nsk.ru live mount | **не найден** (статика) |
| Hash CTA `postMessage` | уже есть |

---

## 12. Файлы и оценка

| Файл | Работа |
|------|--------|
| `template/default/js/genplan_widget.js` | gate, CSS, удаление B/C/D, suppress, pe:none |
| `fw/templates/genplans/widget_demo.php` | bump `?v=` → 2.5.21+ |
| `.doc/tasks/16-genplan-mobile-touch.md` | этот план |

Зависимости: **0 новых**. Оценка: **0.5–1 день** + iOS Safari + Android Chrome.

---

## 13. Порядок реализации

1. [ ] DOM/CSS `.gw-explore-gate`; спрятать `.gw-btn-explore`.
2. [ ] Wire click gate → `_enterExplore`; учесть `exploreFullscreen: false`.
3. [ ] Удалить pan→explore (~2197–2202).
4. [ ] Удалить tap-anywhere → explore (~2231–2234).
5. [ ] Удалить/нейтрализовать `_shouldUseInlineExploreTap` в `_handleObjectActivate` (~2024–2026).
6. [ ] Collapsed coarse: early-return PE; CSS pe:none; `touch-action: pan-y`.
7. [ ] Ручной тест скролла на телефоне / DevTools.
8. [ ] Suppress A+B для CTA **и** apt links, оба скина.
9. [ ] Ghost-tap тест iOS + Android.
10. [ ] Desktop smoke.
11. [ ] Version bump + demo cache-bust.
12. [ ] Коммит / PR.

---

## 14. Тест-план (приёмка)

### Mobile свёрнуто

- [ ] Скролл, начатый **на карте** — страница едет, explore **нет**.
- [ ] Тап по карте (не кнопка) — explore **нет**.
- [ ] Тап по дому/poly — explore **нет**, тултипа **нет**.
- [ ] Оверлей + кнопка **по центру**.
- [ ] Тап «Увеличить» — explore.
- [ ] Скролл с кнопки вниз — explore желательно **не** открывать.
- [ ] Угловой «Увеличить» нет.
- [ ] Два виджета на demo — независимые gate.

### Mobile explore

- [ ] Pan / pinch ок.
- [ ] Тап дом → тултип; **нет** мгновенного ухода.
- [ ] Повторный тап CTA → переход / hash-scroll.
- [ ] Apt-ссылка: без ghost на первом жесте.
- [ ] Прогнать **card** и **expand**.
- [ ] Close → снова gate.

### Desktop

- [ ] Нет gate-оверлея.
- [ ] Hover / pan / CTA без регрессий.

### Опции / края

- [ ] `exploreFullscreen: false` — нет gate, нет explore.
- [ ] Demo в iframe_router.
- [ ] Два instance: explore на одном — lock; закрыть — скролл снова ок.

---

## 15. Риски (обновлённые)

| Риск | Митигация |
|------|-----------|
| pe:none только на stage, listeners на viewport | early-return JS **обязателен** + pe:none на poly `!important` |
| Фон gate pe:none, карта снова ловит события | pe:none на stage/poly/labels + JS no-op |
| Suppress 400ms мало | константа + pe class; подкрутить после QA |
| expand-skin under finger | A+B; при провале — вариант C |
| Привычка tap-anywhere | затемнение + большая центральная кнопка |
| Scroll lock в iframe | локальный document; ок для demo |
| Копирование Sigma capture | **запрещено** |

---

## 16. Вне скоупа

- Редизайн тултипов (#15).
- Десктопный clamp.
- jQuery Mobile / Hammer.
- Вёрстка em-nsk (виджета там нет).
- Рефактор scroll lock под `position:fixed` body.
- API `widget_data` / бэкенд.

---

## 17. Итог решения

1. **Оверлей + кнопка по центру** — единственный вход в explore на mobile.
2. **Убить все** pan/tap/object пути в explore на collapsed (включая `_handleObjectActivate`).
3. **Не** подключать jQuery Mobile; допилить Pointer Events.
4. **Ghost-tap:** suppress + pe:none на expanded surface для CTA и apt, оба скина.
5. **Скролл collapsed:** JS no-op + CSS pe:none + `touch-action: pan-y`; lock в collapsed не трогать.

Код — после подтверждения плана; чеклист §13.
