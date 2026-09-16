# EM: SVG → PNG планировок (`pbplans` → `pbplans_png`)

| | |
|---|---|
| **Задача** | [`../.doc/tasks/22/doc.md`](../.doc/tasks/22/doc.md) |
| **Ветка** | `feature/22-svg2jpg-plans` |
| **Скрипт** | `convert.py` |

---

## Что делает

- Обходит уникальные `*.svg` в `../sahmatka/pbplans/`
- Пишет PNG в зеркальные пути `../sahmatka/pbplans_png/` (`plan.svg` → `plan.png`)
- Большая сторона = **1000px**, меньшая пропорционально; прозрачность → белый
- Lossless: **oxipng** (`-o max`), fallback Zopfli
- HTML-отчёт: группы по `home_id`, размеры было/стало в КБ
- JPG (`--format jpg`) — legacy, **по умолчанию выключен**

HTTP к этой папке закрыт (`.htaccess` → `Require all denied`).

---

## Где используется результат (потребители `pbplans_png`)

Общий хелпер: [`../sahmatka/inc/pbplans_raster.php`](../sahmatka/inc/pbplans_raster.php) — **если PNG на диске есть → PNG, иначе исходный SVG** (объявление не выкидываем).

| Место | Роль |
|-------|------|
| [`../sahmatka/avito_feedx.php`](../sahmatka/avito_feedx.php) | Avito XML: `em_pbplans_prefer_png()` |
| [`../sahmatka/yandex_feedx.php`](../sahmatka/yandex_feedx.php) | Яндекс Realty (актуальный): то же |
| [`../sahmatka/yandex_feedn.php`](../sahmatka/yandex_feedn.php) | Яндекс Realty (legacy): то же |

### Не путать с другим пайплайном

| Место | Что это |
|-------|---------|
| [`../svg2png/index.php`](../svg2png/index.php) | Старый Inkscape SVG→PNG в `svg2png/cache/…`, поле БД `image_pb_png` |
| [`../sahmatka/domclick_feedx.php`](../sahmatka/domclick_feedx.php) | Domclick: подмена `image_pb` на `image_pb_png` из кэша выше |
| `sites/sigma/.../avito_feedx.php` | Копия фида Sigma — пока на `pbplans_jpg` / `..jpg` |

---

## Установка

```bash
cd sites/em/svg_python
pip install -r requirements.txt
winget install --id Shssoichiro.Oxipng -e
# при необходимости: set OXIPNG=C:\path\to\oxipng.exe
```

## Запуск

```bash
# все дома → PNG
python convert.py --force

# один дом
python convert.py --home 33 --force
```

Отчёт: `reports/report.html` (перезаписывается каждый запуск).

## Структура

```
sites/em/
  sahmatka/pbplans/       # исходные SVG
  sahmatka/pbplans_png/   # результат PNG (gitignore contents)
  svg_python/             # этот инструмент (в git; HTTP запрещён)
```
