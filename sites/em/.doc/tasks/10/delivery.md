# Задача 10 — поставка (delivery)

**Дата:** 10.08.2026 (обновлено 11.08.2026)  
**Ветка:** `master` (было `feature/10-interactive-genplan`)  
**Виджет:** `GenplanWidget` **v2.4.11**  
**Миграции:** `005_genplan_polygons.sql`, `006_genplan_label_content.sql`

---

## Артефакты в коде

| Путь | Назначение |
|------|------------|
| `sahmatka/fw/controllers/ctr__genplans.php` | CRUD, upload, clear, `widget_data`, editor/demo |
| `sahmatka/fw/templates/genplans/editor.php` | админ-редактор |
| `sahmatka/fw/templates/genplans/widget_demo.php` | демо + сниппеты вставки |
| `sahmatka/template/default/js/genplan_editor.js` | Leaflet.Draw + dirty/save + drawGuide fix |
| `sahmatka/template/default/js/genplan_widget.js` | публичный GenplanWidget (Shadow DOM) |
| `sahmatka/template/default/css/genplan_editor.css` | UI редактора (fork facade) |
| `sahmatka/template/default/libs/leaflet*` | копия libs для редактора |
| `sites/em/genplans/` | фон `{kvartal_id}.{jpg\|png\|webp}` (в git только `.gitkeep`) |
| `sites/em/.gitignore` | игнор медиа генпланов |
| `ctr__homes_kvartal.php` | карточка «Интерактивный план» → editor |

---

## Админ-потоки

1. ЖК edit → **Открыть редактор интерактивного плана** (`ctr=genplans&act=editor&kvartal_id=`).
2. Режимы: **Полигоны** / **Подписи** (недорисованный полигон сбрасывается при смене режима).
3. Upload / clear фона; Save/Cancel dirty; demo-ссылка на виджет.
4. Поля объекта: title/content (HTML), дом (опц.), чекбоксы заголовка desktop/mobile, apt-ссылки, `link_url`, точки без полигона.

---

## Stage 2 — виджет (chip + card 0.8)

| Поле объекта | Описание |
|--------------|----------|
| `contentHtml` | ручной HTML тела |
| `showTitleDesktop` / `showTitleMobile` | показ title-chip |
| `showAptLinks` | группы free по комнатам |
| `statusTone` | `ok` / `warn` / `wait` / `muted` |
| `statusText`, `metaDelivery`, `metaAddress` | live из `homes` |
| `floorsLabel`, `sectionsLabel` | badges |
| `aptLinks` | `[{ rooms, free, label, url }]` |
| `ctaLabel`, `ctaUrl` | кнопка «Выбрать квартиру» / «Сообщить о старте продаж» |
| `kind` | `polygon` \| `point` |

Idle: белый chip + ▲ (зел/крас/син/сер). Expand в том же DOM, фон `rgba(255,255,255,0.8)`. Floating tooltip не используется.

### Live meta (дом)

| Поле | Формат |
|------|--------|
| `metaDelivery` | `Срок сдачи: N квартал YYYY` — арабская цифра квартала + слово «квартал» (не римские `IV`) |
| `metaAddress` | `Адрес: …` |
| Шрифт meta-строк | `11px` (чуть меньше тела карточки) |

Источник: `delivery_date` → иначе `ready_quarter` + `built_year` (`ctr__genplans::home_delivery_meta`).

---

## Виджет `GenplanWidget.mount`

```js
GenplanWidget.mount({
  el: '#genplan',
  kvartalId: 6,
  apiBase: '…/ajax_router.php?ctr=genplans', // опц.
  width: '100%',         // px или '100%'
  maxHeight: 600,        // высота viewport
  offsetBottom: 100,     // стартовый сдвиг от низа, px
  offsetX: 0, offsetY: 0,// 0…1 (если нет offsetBottom)
  minZoom: 1, maxZoom: 4,
  idleHighlight: true,   // поочерёдная подсветка домов
  exploreFullscreen: true,
  openLinksInNewTab: true,
  highlight: { color: '#5B8FB8', opacity: 0.58, idleOpacity: 0 }
});
```

Версия скрипта: `genplan_widget.js?v=2.4.11` (менять cache-bust вместе с `GenplanWidget.version`).

### Поведение UX (зафиксировано при приёмке + правки 11.08.2026)

| Контекст | Поведение |
|----------|-----------|
| Desktop hover | card по наведению; быстрый переход дом→дом не «залипает» без тултипа |
| Клик / тап по дому | раскрытие card; повторный тап по тому же — закрытие |
| `idleHighlight` | подсветка домов по очереди; стоп при курсоре в карте / mobile explore / active |
| Desktop | pan + wheel-zoom в обычном виджете |
| Mobile компакт | **без** pan/zoom карты; жест pan / кнопка «Увеличить» → explore; idle-анимация да; chips видны (compact) |
| Mobile explore | pan / pinch / wheel-zoom; тап по дому открывает card |
| Закрытие explore (✕) | **полный сброс**: все тултипы скрыты, выбор сброшен, масштаб/пан компакта **как до** «Увеличить» |
| Повторный вход в explore | чистый viewport (без stale listeners); тултипы снова работают |

Демо: `iframe_router.php?ctr=genplans&act=widget_demo&kvartal_id=6`  
(по умолчанию width `100%`, maxHeight `600`, offsetBottom `100`; query `?maxHeight=` / `?width=` / `?offsetBottom=`).

### Внутренние инварианты (для правок JS)

1. **Hit-test** при открытой карточке временно снимает `has-label-expanded` / `has-expanded` **только у активного viewport** (не у первого overlay в shadow — иначе ломается explore).
2. На **coarse** чужие poly **не** получают `pointer-events: none` при открытой карточке (иначе тап по соседнему дому «пролетает»).
3. При каждом `_cloneStageIntoExplore` **пересоздаётся** `.gw-viewport` explore — иначе pointer-listeners копятся и кликают по detached stage.
4. Перед explore сохраняется `_inlineSnap` (scale/tx/ty/offsets); на exit — restore + `_layoutFit`.

---

## QA checklist

- [ ] Миграция `005` / `006` на стенде
- [ ] Upload фона, draw polygon, rubber-band видна поверх фона
- [ ] Подписи / link_url / save → `widget_data` отдаёт `linkUrl`
- [ ] Desktop: hover card, быстрый переход между домами, pan/zoom, idle highlight вне курсора
- [ ] Срок сдачи в card: `N квартал YYYY` (арабская), шрифт meta чуть меньше
- [ ] Mobile: idle highlight; «Увеличить» → tap house → card; закрыть ✕ → компакт без тултипов, тот же масштаб
- [ ] Mobile: повторно открыть explore → тап по домам снова выделяет / открывает card
- [ ] Смена режима Подписи сбрасывает недорисованный полигон

---

## Changelog виджета (после Stage 2)

| Версия | Что |
|--------|-----|
| 2.4.7 | Hover: hit-test видит соседний дом при открытой карточке; sticky переключает poly |
| 2.4.8 | Meta: `N квартал YYYY` + font-size 11px |
| 2.4.9–2.4.10 | Exit explore: сброс selection + restore inline camera |
| 2.4.11 | Re-open explore: recreate viewport (fix stale listeners на mobile) |

---

## Сопутствующие фиксы (ранний коммит Stage 1)

- PHP 8: quoted keys в `in_head.php`, `admin_object.php` (доступ к админке/объектам).
