# #16 Genplan mobile: scroll + ghost tap

**Branch:** `feature/16-genplan-mobile-touch`  
**File:** `sites/em/sahmatka/template/default/js/genplan_widget.js`  
**Status:** plan only (no code yet)

## Проблемы

### 1. Ghost-tap на CTA в explore
На телефоне в увеличенном режиме тап по дому открывает тултип **под пальцем**. В том же жесте кнопка CTA оказывается под пальцем → срабатывает переход на страницу дома.

### 2. Свёрнутая карта перехватывает скролл
Механика сворачивания была для того, чтобы **в свёрнутом режиме страница скроллилась за карту**, а при **клике** карта разворачивалась (explore). Сейчас при попытке скролла по свёрнутой карте происходит клик/жест и карта увеличивается.

## Корневые причины (текущий код)

| Место | Поведение |
|-------|-----------|
| `pointermove` ~2197–2202 | coarse + inline + `_moved` (>8px) → `_enterExplore()` — **скролл открывает explore** |
| `endPointer` + `_shouldUseInlineExploreTap` | short tap по карте → explore (это ок для клика) |
| CTA `<a class="gw-label__cta">` | после открытия тултипа тот же touch/click попадает в ссылку |

## План фикса

### A. Свёрнутый режим — скролл страницы
1. **Убрать** вход в explore по pan/скроллу (`_moved` на coarse inline).
2. Оставить вход в explore только:
   - short tap по карте (без движения);
   - кнопка «Увеличить».
3. Проверить `touch-action` на `.gw-viewport` в `is-coarse:not(.is-explore)` — страница должна pan-y без перехвата JS.
4. Не вызывать `setPointerCapture` в coarse inline (уже так) — не ломать.

### B. Ghost-tap CTA
1. После открытия тултипа на coarse выставить окно подавления кликов (~300–400 ms) или до следующего `pointerdown`.
2. В обработчиках CTA / apt-ссылок игнорировать клик в этом окне.
3. Опционально: на карточке `pointer-events: none` → через rAF/`setTimeout` вернуть `auto`.
4. Не менять десктопный hover/click.

### C. Регрессии / тесты
- [ ] Mobile свёрнуто: скролл страницы поверх карты **не** открывает explore
- [ ] Mobile свёрнуто: короткий тап / «Увеличить» → explore
- [ ] Mobile explore: тап по дому → тултип, **без** перехода
- [ ] Mobile explore: второй тап по CTA → переход / hash-scroll
- [ ] Desktop: hover, pan, CTA — без изменений

## Вне скоупа
- Правки макета тултипа / скинов card|expand (уже в #15)
- Десктопный viewport clamp
