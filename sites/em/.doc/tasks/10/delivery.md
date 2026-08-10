# Задача 10 — поставка (delivery)

**Дата:** 10.08.2026  
**Ветка:** `feature/10-interactive-genplan`  
**Миграция:** `sites/em/sahmatka/migrations/005_genplan_polygons.sql` → таблица `genplan_polygons`

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
4. Поля объекта: title (HTML), дом (опц.), `link_url`.

---

## Виджет `GenplanWidget.mount`

```js
GenplanWidget.mount({
  el: '#genplan',
  kvartalId: 6,
  apiBase: '…/ajax_router.php?ctr=genplans', // опц.
  width: 700,            // px или '100%'
  maxHeight: 400,        // высота viewport
  offsetBottom: 100,     // стартовый сдвиг от низа, px
  offsetX: 0, offsetY: 0,// 0…1 (если нет offsetBottom)
  minZoom: 1, maxZoom: 4,
  idleHighlight: true,   // поочерёдная подсветка домов
  exploreFullscreen: true,
  openLinksInNewTab: true,
  highlight: { color: '#5B8FB8', opacity: 0.58, idleOpacity: 0 }
});
```

### Поведение UX (зафиксировано при приёмке)

| Контекст | Поведение |
|----------|-----------|
| Клик по полигону | `link_url` (desktop сразу; mobile: 1-й тап card, 2-й navigate); тап по label — сразу URL |
| Тултип vs label | при доп.инфо label скрывается, card у якоря + стрелка; иначе без дубля |
| `idleHighlight` | подсветка домов по очереди; стоп при курсоре в карте / mobile explore / active |
| Desktop | pan + wheel-zoom в обычном виджете |
| Mobile компакт | **без** pan/zoom карты; жест pan / кнопка → explore; idle-анимация да |
| Mobile explore | pan / pinch / wheel-zoom; labels видны |
| Mobile labels (компакт) | скрыты, чтобы не наползали |

Демо: `iframe_router.php?ctr=genplans&act=widget_demo&kvartal_id=6`  
(по умолчанию width 700, maxHeight 400, offsetBottom 100; query `?maxHeight=` / `?width=` / `?offsetBottom=`).

---

## QA checklist

- [ ] Миграция `005` на стенде
- [ ] Upload фона, draw polygon, rubber-band видна поверх фона
- [ ] Подписи / link_url / save → `widget_data` отдаёт `linkUrl`
- [ ] Desktop: hover card, click URL, pan/zoom, idle highlight вне курсора
- [ ] Mobile: idle highlight; tap house; explore pan/zoom; labels в explore
- [ ] Смена режима Подписи сбрасывает недорисованный полигон

---

## Сопутствующие фиксы (в том же коммите)

- PHP 8: quoted keys в `in_head.php`, `admin_object.php` (доступ к админке/объектам).
