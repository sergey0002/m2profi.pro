# #16 Genplan mobile: scroll, overlay enter, ghost-tap

| | |
|---|---|
| **Branch** | `feature/16-genplan-mobile-touch` |
| **Статус** | план (код ещё не пишем) |
| **Главный файл** | `sites/em/sahmatka/template/default/js/genplan_widget.js` |
| **Демо** | `sites/em/sahmatka/fw/templates/genplans/widget_demo.php` |
| **Где лежит этот план** | `sites/em/.doc/tasks/16-genplan-mobile-touch.md` |
| **Связанные стейджи** | #10 виджет, #14 CTA/marker, #15 tooltip skins |

---

## 1. Цель стейджа

На мобиле сделать предсказуемую двухрежимную механику:

1. **Свёрнутый inline-режим** — страница **нормально скроллится** поверх карты; карта визуально «приглашает» открыть себя через **оверлей + центральную кнопку**; случайный тап/свайп по карте **не** открывает explore.
2. **Explore (увеличенный)** — pan/pinch/тултипы работают; тап по дому открывает тултип **без** мгновенного перехода по CTA под пальцем.

Десктопную механику (hover → тултип, pan карты inline) не ломаем.

---

## 2. Проблемы (as-is)

### 2.1. Ghost-tap на CTA в explore

**Симптом:** в увеличенном режиме жмёшь дом → тултип появляется **под пальцем** → в том же жесте срабатывает CTA («Выбрать квартиру» / hash-ссылка) → мгновенный переход на страницу дома.

**Почему:** классический click-through / ghost activation:

1. `pointerup` / synthetic `click` ещё «висит» на координатах пальца.
2. Тултип (card/expand) монтируется/показывается в том же месте.
3. CTA (`<a class="gw-label__cta">`) оказывается под пальцем и получает клик.

Связанный код:

- `_setActive` / `_setLabelExpanded` — открытие тултипа.
- `_bindHostLink` — обработчик клика по CTA (~1020–1041).
- `pointerdown` early-return для `labelAnchor` (~2102–2108) — не мешает **позднему** `click` на только что появившейся ссылке.

### 2.2. Свёрнутая карта перехватывает скролл / сама открывается

**Симптом:** тянешь страницу за область карты → карта увеличивается. Механика «свернуть» как раз и нужна, чтобы **скроллить страницу за карту**, а разворачивать — осознанным действием.

**Почему (текущий код):**

| Место | Поведение | Оценка |
|-------|-----------|--------|
| `pointermove` ~2197–2202 | coarse + inline + сдвиг > 8px → `_enterExplore()` | **баг**: скролл = открытие |
| `endPointer` + `_shouldUseInlineExploreTap` ~2231–2234 | short tap по любой точке карты → explore | спорно: случайный тап тоже открывает |
| `.gw-btn-explore` справа снизу | единственная явная CTA «Увеличить» | мелкая, легко промахнуться; дублирует тап/пан |
| `touch-action: manipulation` на coarse viewport | задумано пускать скролл | JS всё равно перехватывает через Pointer Events |

### 2.3. Почему «кнопка в углу» недостаточна

- Маленькая зона нажатия.
- Пользователь всё равно может открыть explore случайным свайпом/тапом по карте (см. выше).
- Нет визуального «режима ожидания»: карта выглядит интерактивной целиком.

**Решение продукта:** заменить угол-кнопку на **полноразмерный оверлей** поверх свёрнутой карты с **кнопкой по центру**. Открыть explore можно **только** нажав эту кнопку.

---

## 3. Целевой UX

### 3.1. Mobile, свёрнут (`is-coarse` + не `is-explore`)

```
┌─────────────────────────────┐
│  [карта, приглушена]        │
│                             │
│      ┌───────────────┐      │
│      │  Увеличить    │      │  ← единственный способ войти в explore
│      └───────────────┘      │
│                             │
│  (полупрозрачный оверлей)   │
└─────────────────────────────┘
     ↕ страница скроллится сквозь/мимо карты
```

Правила:

1. **Скролл страницы** поверх карты — работает всегда (вертикальный pan документа).
2. **Тап / свайп по карте** (кроме центральной кнопки) — **ничего** не открывает, не зумит, не выбирает дом.
3. **Тултипы / чипы / poly hit** в свёрнутом режиме — выключены (уже частично: labels `pointer-events: none`).
4. **Единственный вход в explore** — тап по центральной кнопке на оверлее.
5. Оверлей **не** блокирует скролл страницы: события скролла не `preventDefault`; зона вне кнопки не захватывает жест как «клик по карте».

### 3.2. Mobile, explore

1. Полноэкранный/почти полноэкранный слой (как сейчас `_enterExplore`).
2. Pan / pinch работают.
3. Тап по дому → тултип; **первый** жест не активирует CTA.
4. Отдельный осознанный тап по CTA → переход / hash-scroll.
5. Закрытие — крестик / Escape (как сейчас).

### 3.3. Desktop

Без изменений по смыслу:

- hover → тултип;
- click по дому → sticky/active;
- pan/zoom inline (если pannable);
- кнопки «Увеличить» на десктопе **нет** (сейчас `is-coarse` only) — оверлей тоже **только coarse**.

---

## 4. Жесты: что использовать (рекомендация)

### 4.1. Варианты

| Подход | Плюсы | Минусы | Вердикт |
|--------|-------|--------|---------|
| **jQuery Mobile** | когда-то были `tap` / `swipe` | проект **мёртв**, тяжёлый, конфликты с jQuery 3, не нужен виджету (shadow DOM, без jQ) | **не подключать** |
| **Hammer.js** | зрелые жесты | лишняя зависимость, поддержка средняя, виджет уже на Pointer Events | запасной план |
| **@use-gesture / ZingTouch** | современные | оверхед для одного виджета, bundling | не нужно |
| **Нативные Pointer Events + классификатор жеста** (уже есть зачатки) | уже в `genplan_widget.js`, без зависимостей, работает в shadow DOM | нужно **допилить** правила | **основной план** |

Виджет монтируется в **Shadow DOM** и **не зависит от jQuery**. Тащить jQuery Mobile / jQuery UI ради 2 жестов — плохо: вес, конфликты, дублирование с уже существующими `pointerdown/move/up`.

### 4.2. Модель жестов (свой лёгкий классификатор)

На каждом `pointerdown` → `pointerup` / `pointercancel` вычисляем:

```
start = { x, y, t, targetKind }
moved = hypot(dx, dy) > MOVE_THRESHOLD   // 8–12 px
duration = now - start.t
gesture =
  moved && duration > …     → "pan" | "scroll-intent"
  !moved && duration < 500  → "tap"
  иначе                     → "ignore"
```

Применение:

| Контекст | `tap` | `pan` / scroll-intent |
|----------|-------|------------------------|
| coarse + inline + hit на **кнопку оверлея** | `_enterExplore()` | не открывать explore; не мешать скроллу |
| coarse + inline + hit на карту/оверлей-фон | **ничего** | **ничего** (страница скроллит) |
| coarse + explore + hit на дом | открыть тултип + **suppress CTA** | pan карты |
| coarse + explore + hit на CTA (и suppress истёк) | переход | — |
| desktop | как сейчас | pan/hover |

Пороги (стартовые, подогнать на устройстве):

- `MOVE_THRESHOLD = 10` px
- `TAP_MAX_MS = 450`
- `CTA_SUPPRESS_MS = 400` после открытия тултипа
- опционально: suppress до следующего `pointerdown`, если он не является частью opening-gesture

### 4.3. Почему не «просто click»

На iOS/Android после `touch` браузер генерирует совместимостный `click`. Если DOM под пальцем поменялся между `touchstart` и `click`, клик попадает в **новый** элемент (CTA). Поэтому:

- нельзя полагаться только на `click`;
- нужен suppress-window **и/или** `pointer-events: none` на карточке на 1–2 кадра после expand.

---

## 5. UI: оверлей вместо `.gw-btn-explore`

### 5.1. Разметка (черновик)

Внутри `.gw-root` (рядом с inline viewport), только для coarse:

```html
<div class="gw-explore-gate" hidden>
  <button type="button" class="gw-explore-gate__btn">Увеличить</button>
</div>
```

Или без `hidden`, показывать CSS-ом:

```css
.gw-explore-gate { display: none; }
.gw-root.is-coarse:not(.is-explore) .gw-explore-gate { display: flex; }
```

### 5.2. CSS-поведение

```text
.gw-explore-gate
  position: absolute; inset: 0;   /* покрывает виджет / viewport */
  z-index: 7;                     /* выше карты, ниже explore-layer */
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.35–0.45);  /* приглушить карту */
  /* КРИТИЧНО для скролла: */
  pointer-events: none;           /* фон не ест жесты */

.gw-explore-gate__btn
  pointer-events: auto;           /* только кнопка кликабельна */
  min-height: 44px;               /* hit-target ≥ 44×44 */
  padding: 12px 22px;
  border-radius: …;
  font-weight: 600;
  /* визуально «главный» CTA по центру */
```

Важно:

- Фон оверлея с `pointer-events: none` → палец «проваливается» к странице/родителю для скролла.
- Под оверлеем карта в coarse-collapsed **не должна** сама вызывать `_enterExplore` (см. §6).
- Старую `.gw-btn-explore` (right/bottom) — **убрать** или оставить hidden forever на coarse (заменить гейтом).

### 5.3. A11y

- Кнопка — настоящий `<button type="button">`.
- `aria-label="Увеличить интерактивный план"` (или через `locale.explore`).
- Фокус: после закрытия explore вернуть фокус на gate-кнопку (nice-to-have).

### 5.4. Копирайт / locale

Сейчас: `locale.explore = 'Увеличить'`.
Можно оставить или уточнить: «Открыть план» / «Смотреть план» — решить при вёрстке; в плане по умолчанию оставляем `'Увеличить'`.

---

## 6. Изменения в логике (as-is → to-be)

### 6.1. Удалить / отключить

1. **Pan → explore** в `pointermove` (~2197–2202) — удалить целиком.
2. **`_shouldUseInlineExploreTap` → `_enterExplore` в `endPointer`** — удалить: тап по карте больше **не** открывает explore.
3. Показ `.gw-btn-explore` в углу — заменить на `.gw-explore-gate`.

### 6.2. Добавить

1. DOM + CSS оверлея (§5).
2. `click` / `pointerup`-tap **только** на `.gw-explore-gate__btn` → `_enterExplore()`.
3. `_suppressLinkClicksUntil` (timestamp) + проверка в `_bindHostLink` / общем click-guard.
4. После `_setLabelExpanded(..., true)` на coarse:

   ```js
   this._suppressLinkClicksUntil = Date.now() + CTA_SUPPRESS_MS;
   // и/или card.style.pointerEvents = 'none'; requestAnimationFrame ×2 → restore
   ```

5. Хелпер `_classifyPointerGesture(start, end)` — единая точка для tap vs pan (чтобы не плодить пороги).

### 6.3. Оставить как есть

- `_enterExplore` / `_exitExplore` / scroll lock.
- Explore pan/pinch.
- Desktop hover/active.
- Tooltip placement / skins (#15) — вне скоупа, кроме suppress кликов.
- Публичный API `GenplanWidget.mount({ exploreFullscreen })`.

### 6.4. Псевдокод входа

```js
// было: tap anywhere / pan → explore
// стало:
gateBtn.addEventListener('click', (e) => {
  e.preventDefault();
  e.stopPropagation();
  if (self.exploreFullscreen) self._enterExplore();
});

// inline coarse pointer handlers:
// - НЕ setPointerCapture
// - НЕ _enterExplore на move/up
// - touch-action на viewport: pan-y (или manipulation) без JS preventDefault
```

### 6.5. `touch-action` (уточнение)

Сейчас:

```css
.gw-root.is-coarse:not(.is-explore) .gw-viewport { touch-action: manipulation; }
```

Рекомендация to-be:

```css
.gw-root.is-coarse:not(.is-explore) .gw-viewport { touch-action: pan-y; }
/* или даже: pointer-events: none на stage/poly в collapsed,
   клики только через gate button */
```

Дополнительно рассмотреть в collapsed coarse:

```css
.gw-root.is-coarse:not(.is-explore) .gw-poly { pointer-events: none !important; }
.gw-root.is-coarse:not(.is-explore) .gw-stage { pointer-events: none; }
```

Тогда жесты гарантированно уходят в скролл страницы; интерактив — только у gate-кнопки.

---

## 7. Ghost-tap: детальный алгоритм

### Вариант A (обязательный минимум) — suppress window

```js
// при открытии тултипа на coarse:
this._suppressLinkClicksUntil = Date.now() + 400;

// в _bindHostLink click handler:
if (Date.now() < (self._suppressLinkClicksUntil || 0)) {
  e.preventDefault();
  e.stopPropagation();
  return;
}
```

### Вариант B (усиление) — pointer-events

```js
card.style.pointerEvents = 'none';
requestAnimationFrame(() => {
  requestAnimationFrame(() => {
    card.style.pointerEvents = '';
  });
});
```

Или CSS-класс `.is-click-guard` на 400ms.

### Вариант C (опционально) — не открывать тултип под пальцем

Placement уже умеет flip; на coarse после tap можно предпочитать сторону **away from touch** (если `clientY` известен). Это secondary — сначала A+B.

**В стейдж включаем A + B; C — если останется клик-through на реальных девайсах.**

---

## 8. Файлы и объём работ

| Файл | Что сделать |
|------|-------------|
| `template/default/js/genplan_widget.js` | оверлей, жесты, suppress, убрать pan/tap→explore, CSS в STYLE |
| `fw/templates/genplans/widget_demo.php` | bump `?v=2.5.x`, при необходимости подпись «mobile gate» |
| `.doc/tasks/16-genplan-mobile-touch.md` | этот план (обновлять по ходу) |

Новых npm/CDN-зависимостей **не** добавляем (без jQuery Mobile / Hammer), пока нативный классификатор не докажет обратное на QA.

Оценка: ~0.5–1 день реализации + проход на iOS Safari + Android Chrome.

---

## 9. Порядок реализации (чеклист)

1. [ ] CSS/DOM: `.gw-explore-gate` + центральная кнопка; скрыть `.gw-btn-explore`.
2. [ ] Wire: click по gate → `_enterExplore`.
3. [ ] Удалить pan→explore и tap-anywhere→explore на coarse inline.
4. [ ] Collapsed coarse: `pointer-events: none` на stage/poly (или эквивалент), `touch-action: pan-y`.
5. [ ] Проверить: скролл страницы поверх виджета на реальном телефоне.
6. [ ] Suppress CTA (A + B) после открытия тултипа в explore.
7. [ ] Проверить ghost-tap на iOS и Android.
8. [ ] Desktop smoke: hover, pan, CTA.
9. [ ] Bump version + demo cache-bust.
10. [ ] Коммит / PR в `feature/16-genplan-mobile-touch`.

---

## 10. Тест-план (приёмка)

### Mobile свёрнуто

- [ ] Страница скроллится пальцем **начиная жест на карте** — explore **не** открывается.
- [ ] Быстрый тап по карте (не по кнопке) — explore **не** открывается.
- [ ] Виден затемняющий оверлей и кнопка **по центру**.
- [ ] Тап по «Увеличить» — открывается explore.
- [ ] Старой кнопки в правом нижнем углу нет.

### Mobile explore

- [ ] Pan / pinch карты работают.
- [ ] Тап по дому — тултип; **нет** мгновенного ухода на страницу дома.
- [ ] Повторный тап по CTA — переход / hash-scroll как задумано.
- [ ] Закрытие крестиком возвращает свёрнутый вид с оверлеем.

### Desktop

- [ ] Нет mobile-оверлея.
- [ ] Hover-тултипы и pan без регрессий.

---

## 11. Риски и краевые случаи

| Риск | Митигация |
|------|-----------|
| Оверлей с `pointer-events: none` «ломает» скролл в iframe | проверить вложение на em-nsk; при необходимости `touch-action` на host |
| Пользователи привыкли тапать «куда угодно» | центральная кнопка + затемнение делают affordance явным |
| Suppress 400ms мало/много на разных ОС | константа + возможность подкрутить; вариант B как страховка |
| Два виджета на demo-странице | gate у каждого instance отдельно |
| `exploreFullscreen: false` | gate не показывать / не входить в explore |

---

## 12. Вне скоупа этого стейджа

- Редизайн тултипов / скинов card|expand (#15 уже в master).
- Десктопный clamp тултипа.
- Подключение jQuery Mobile / Hammer (только если нативный путь провалит QA — отдельное решение).
- Изменение API `widget_data` / бэкенда.

---

## 13. Решение по зависимостям (итог)

**Не подключаем jQuery Mobile.**
Используем уже существующие **Pointer Events** + явный классификатор `tap` / `pan` / `ignore` + UI-гейт (оверлей).
Это согласовано с архитектурой виджета (Shadow DOM, без jQ) и закрывает оба бага без новых библиотек.

Если после QA на iOS/Android останутся систематические ложные срабатывания — тогда точечно рассмотреть **Hammer.js только для explore-слоя**, не для свёрнутого режима.
