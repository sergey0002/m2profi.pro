# Task #2 — JS-модалки документов (doc)

Краткий отчёт по ветке `feature/2-doc-modals` (тенант `sites/doc`).

Полная документация задачи (gitignored): `sites/doc/.doc/tasks/2/result.md`.

## Коммиты

| SHA | Содержание |
|-----|------------|
| `6b92de4` | Stage 1: page modal + `act=save` вместо iframe edit/card |
| `f5f0837` | Stage 2–3: download/404, zoom/confirm, soft-delete UX, clean titles |

## Архитектура

- Форма/карточка: фрагмент `ajax_router?ctr=doc&act=…&modal=1` в `#doc-modal-overlay` (не Magnific).
- Диск: `POST /sahmatka/upload.php` через уже загруженный `myfw_iframe.js` (`.fw_file`).
- Метаданные: `POST ajax_router?ctr=doc&act=save` → JSON → `pendingReveal` / refresh / highlight.
- `iframe_router` / `myfw_iframe.js` / `upload.php` / `filed.php` **не менялись**.

## Основные файлы

- `sahmatka/fw/controllers/ctr__doc.php`
- `sahmatka/fw/templates/doc/document_tree_js.php`
- `sahmatka/fw/templates/doc/document_tree_styles.php`
- `sahmatka/fw/templates/doc/document_card.php`
- `sahmatka/fw/templates/doc/edit_form.php` (stage 1)

## Исправления UX после выката модалки

1. Скачивание и «История» не уводят кабинет (`act=download`, `_blank`; 404 без пути).
2. Zoom-in оверлея как Magnific; confirm при закрытии после выбора файла.
3. Soft-delete учитывает «Показывать удаленные»; кнопки действий ветки не пропадают.
4. Имена: без `!!!!!` и двойного `.pdf.pdf` (меню ⋮ не в `node.text`).
