# Задача 12 — поставка (delivery)

**Дата:** 11.08.2026  
**Ветка:** `feature/12-genplan-life`  
**Статус:** 📋 план готов · код не начат  
**Спека:** [plan.md](./plan.md)

---

## Артефакты (план)

| Путь | Назначение |
|------|------------|
| `migrations/007_genplan_life.sql` | `genplan_life_tracks`, `genplan_life_agents` |
| `ctr__genplans.php` | `life_*` acts + `life` в `widget_data` |
| `genplan_editor.js` + CSS | режим «Жизнь» |
| `genplan_life.js` + `genplan_life_sprites.js` | runtime + SVG |
| `genplan_widget.js` | слой + опция `life` |

---

## Стейджи

| Stage | Содержание | Статус |
|-------|------------|--------|
| 1 | Машина/Человек open-polyline; cars+people+clouds; mount-флаги; light day/evening | pending |
| 2 | Собаки; birds диагональ + toggle | pending |
| 3 | Light pulse + density/polish | pending |

---

## QA

См. §10 [plan.md](./plan.md). Заполнять чеклист по мере мержа стейджей.

---

## Changelog

| Дата | Что |
|------|-----|
| 11.08.2026 | Создан план реализации |
