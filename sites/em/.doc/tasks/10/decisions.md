# Решения заказчика (задача 10)

**Дата фиксации:** 10.08.2026 (ответы закрыты)  
**Доп. UX:** 11.08.2026 (polish виджета)  
**Статус поставки:** **production**  
**Спека:** [plan.md](./plan.md) · поставка: [delivery.md](./delivery.md)

---

## Блок 1 — продукт

| # | Вопрос | Решение |
|---|--------|---------|
| Q1 | Привязка к дому | Ручной `title` + опциональный select дома. При выборе — в редакторе preview срока/статуса/адреса |
| Q2 | Клик | Свободный `link_url` + `onObjectClick` |
| Q3 | Иконки amenities | **Нет** |
| Q4 | Тултип | С домом → статус/сдан/адрес **живые из `homes`**. Без дома → только ручной **title** (обязателен) |
| Q5 | Explore | Обязателен |
| Q6 | Фон | Один на `kvartal_id` |
| Q7 | Цвета заливки | Опция `highlight` = API `facadeHighlight` Sigma |

---

## Блок 2 — уточнения (закрыты)

| # | Вопрос | Решение |
|---|--------|---------|
| U1 | Live vs snapshot | **Живые** из `homes` при каждом `widget_data`, если задан `home_id` |
| U2 | Footer («1 кладовая») | **Пока не нужно** — не делать в UI/API/карточке. Ручной контент = **title с поддержкой HTML** |
| U3 | Без дома | Label/карточка = ручной **title**; **title обязателен**, если дом не выбран |
| U4 | Дефолт `highlight.color` | **Как в Sigma:** `#5B8FB8` (`facadeHighlight.color`) |

Открытых вопросов нет.

---

## Блок 3 — UX приёмки (10.08.2026, зафиксировано в коде)

| Тема | Решение |
|------|---------|
| Naming UI | «Интерактивный план» (вместо «Разметка генплана») |
| Mobile pan/zoom | Только в explore («Увеличить»), как Sigma |
| Desktop pan/zoom | В обычном виджете (width/maxHeight/clip) |
| idleHighlight | Вкл. по умолчанию; стоп при курсоре в карте / mobile explore |
| Labels mobile | Chips видны в компакте (compact) и в explore |
| Тултип | Стрелка вниз; без дубля title; скрытие при pan/zoom |

Детали API и QA: [delivery.md](./delivery.md).

---

## Блок 4 — Stage 2 (подписи / chip+▲ / card 0.8 / apt / point-only)

**Дата фиксации:** 10.08.2026 (UI = `ref/mockup-genplan-tooltips-v2.png`, дополнено)  
**Спека:** [plan-stage2.md](./plan-stage2.md) · аудит: [audit-stage2.md](./audit-stage2.md)

| Тема | Решение |
|------|---------|
| Title / content | Раздельные HTML; body = content + live; оба → content сверху |
| Idle | Белый chip + ▲ + title (не цветной круг) |
| ▲ | Зелёный сдан · красный строится · **синий** `aptTotal=0` («Ждет начала строительства») · **серый** `show=0` или sold-out (`freeTotal=0`) |
| Expand | Карточка opacity **0.8** в том же DOM |
| Badges | Этажи (`homes.floor`) + секции (`COUNT homes_sections`), RU-склонение на сервере |
| CTA | «Выбрать квартиру» (ok/warn) · «Сообщить о старте продаж» (wait, нужен `link_url`) · нет при muted |
| Apt links | Чекбокс только с `home_id`; free by rooms; URL с `PUBLIC_URL` |
| Point-only | `points=[]` + `label_x/y` |
| Показ chip | `show_title_*`; off → compact только ▲ |

Открытых вопросов по Stage 2 нет.

---

## Блок 5 — polish виджета (11.08.2026)

| Тема | Решение |
|------|---------|
| Срок сдачи в card | Текст **`N квартал YYYY`** (арабская цифра + слово «квартал»), не римские |
| Шрифт meta | Срок сдачи и адрес — чуть меньше тела (`11px`) |
| Desktop hover A→B | Тултип не должен «пропадать» до следующего движения курсора |
| Закрытие explore | Вернуть компакт **как до** разворота: тултипы скрыты, тот же масштаб/пан |
| Повторный explore | Тултипы/выделение домов обязаны работать снова |

Код: `genplan_widget.js` **v2.4.11**, `home_delivery_meta` в `ctr__genplans.php`.  
Чеклист: [delivery.md](./delivery.md).
