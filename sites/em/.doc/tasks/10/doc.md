# Задача 10 (EM): Интерактивный план ЖК (редактор + виджет)

**Дата:** 10.08.2026 (обновлено 11.08.2026)  
**Уровень:** **production**  
**Область:** только `sites/em/**`  
**БД:** `m2profi_em`  
**Ветка:** `master` (было `feature/10-interactive-genplan`)  
**Виджет:** `GenplanWidget` **v2.4.11**  
**Статус:** ✅ Stage 1 + Stage 2 + polish UX (hover / meta / mobile explore reset)

| Документ | Роль |
|----------|------|
| [plan.md](./plan.md) | Stage 1 — источник истины (сделано) |
| [plan-stage2.md](./plan-stage2.md) | Stage 2 — канон (title/content, tip, apt, point-only) |
| [decisions.md](./decisions.md) | Все ответы заказчика + polish 11.08 |
| [delivery.md](./delivery.md) | Что сдано, API виджета, UX, QA, changelog |
| [audit.md](./audit.md) | Аудит черновика Stage 1 |
| [audit-stage2.md](./audit-stage2.md) | Аудит Stage 2 |
| [ref/](./ref/) | Макеты + снимок UI Sigma |

**Вход:** `ctr=homes_kvartal` → edit → «Открыть редактор интерактивного плана».  
**Демо:** `iframe_router.php?ctr=genplans&act=widget_demo&kvartal_id=`

---

## Цель

Интерактивный план ЖК: один фон → полигоны и/или точки → title/content + опциональный дом → виджет с tip/title expand-маркерами, live-блоком из `homes`, apt-ссылками, свободным URL, explore, `highlight` как у FacadeWidget (`#5B8FB8` / 0.58 / idle 0).

## Канон (кратко)

- Title HTML; **обязателен**, если дом не выбран.
- Stage 2: content HTML; idle = chip+▲ (зелёный/красный/**синий ждёт**/серый); card opacity 0.8; badges этажи/секции; CTA.
- С домом: статус/сдача/адрес **живые** из `homes` на каждый `widget_data`.
- Срок сдачи в card: **`N квартал YYYY`** (арабская цифра).
- Stage 2: опционально ссылки на свободные квартиры по комнатам (каталог `PUBLIC_URL`).
- Stage 2: **point-only** объекты без полигона (`points=[]` + `label_x/y`).
- Footer карточки — **не делаем**.
- Иконки amenities — нет.
- Подсветка: `highlight` = API Sigma `facadeHighlight`, дефолт color `#5B8FB8`.
- Explore — в поставке (мобилка: pan/zoom **только** в увеличенном режиме).
- Закрытие explore → полный сброс тултипов + прежний масштаб компакта; повторный вход — тултипы работают.

Открытых вопросов нет. Актуальная поставка: [delivery.md](./delivery.md).

**Следующая задача:** [#12 Жизнь на плане](../12/doc.md) (треки, машины/люди/собаки, птицы, облака) — ветка `feature/12-genplan-life`.
