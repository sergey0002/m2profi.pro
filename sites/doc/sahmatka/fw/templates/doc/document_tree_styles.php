<style>
/* Сброс и уточнение стилей jsTree */

/* Обнуляем стандартные отступы и границы у контейнера дерева */
#doc_tree {
    margin: 0;
    padding: 0;
}

/* Обнуляем стандартные стили у узлов дерева + вертикальные линии */
.jstree-default .jstree-node {
    min-height: 0;
    margin-left: 20px; /* Возвращаем отступ для вложенности */
    position: relative; /* Для правильного позиционирования wholerow */
    border-left: 1px dotted #999 !important; /* Вертикальная пунктирная линия */
    padding-left: 10px !important;
    white-space: nowrap; /* Запрещаем перенос между иконкой и текстом */
	padding:1px;
}

/* Убираем линию у последнего дочернего элемента */
.jstree-default .jstree-node:last-child {
    border-left: none !important;
}

/* КРИТИЧНО: Стрелочка/отступ должна быть inline-block */
.jstree-default .jstree-ocl {
    display: inline-block !important;
    vertical-align: top !important;
    width: 24px !important; /* Фиксированная ширина */
    height: 24px !important;
    margin-right: 0 !important;
    margin-top: 5px !important; /* Центрируем относительно первой строки текста */
    /* background-position: center center !important;  - УБИРАЕМ, ломает спрайт */
    cursor: pointer !important;
    position: relative !important; /* Поднимаем над wholerow */
    z-index: 2 !important;
}

/* Убираем стандартные иконки темы */
.jstree-default .jstree-themeicon {
    display: none !important;
}

/* Индикатор места вставки при drag-and-drop */
.jstree-default .jstree-insert,
#doc_tree .jstree-insert,
.jstree-insert {
    background: #4CAF50 !important;
    height: 4px !important;
    width: 100% !important;
    position: relative !important;
    display: block !important;
    margin: 2px 0 !important;
    z-index: 999 !important;
}

.jstree-default .jstree-insert:before,
#doc_tree .jstree-insert:before,
.jstree-insert:before {
    content: '' !important;
    position: absolute !important;
    left: 0 !important;
    top: -4px !important;
    width: 10px !important;
    height: 10px !important;
    background: #4CAF50 !important;
    border-radius: 50% !important;
    border: 2px solid #fff !important;
    z-index: 1000 !important;
}

.jstree-default .jstree-insert:after,
#doc_tree .jstree-insert:after,
.jstree-insert:after {
    content: '' !important;
    position: absolute !important;
    right: 0 !important;
    top: -4px !important;
    width: 10px !important;
    height: 10px !important;
    background: #4CAF50 !important;
    border-radius: 50% !important;
    border: 2px solid #fff !important;
    z-index: 1000 !important;
}

/* Альтернативные стили для индикатора вставки */
.jstree-default .jstree-marker,
#doc_tree .jstree-marker {
    background: #4CAF50 !important;
    height: 4px !important;
    width: 100% !important;
    position: absolute !important;
    left: 0 !important;
    z-index: 999 !important;
}

/* Подсветка целевого узла при hover во время drag */
.jstree-default .jstree-hovered,
.jstree-default .jstree-wholerow-hovered {
    background: rgba(76, 175, 80, 0.2) !important;
    border: 2px dashed #4CAF50 !important;
}

/* Стилизация узлов-папок */
.jstree-node.type-folder > .jstree-wholerow {
    display: none !important;
}

.jstree-node.type-folder > .jstree-anchor {
    background: #3d535f !important;
    color: #FFF !important;
    font-size: 16px;
    font-weight: bold;
    padding: 8px 110px 8px 5px !important; /* Увеличен правый отступ для кнопок */
    min-height: 36px;
    text-decoration: none;
    display: inline-block !important;
    vertical-align: top !important;
    position: relative;
    z-index: 2 !important; /* Текст выше фона */
    white-space: normal !important;
    word-break: break-word;
    overflow-wrap: break-word;
    border-radius: 4px;
    box-sizing: border-box;
    width: calc(100% - 24px) !important; /* Вычитаем ширину ocl */
}

.jstree-node.type-folder:hover > .jstree-anchor {
    background: #2d434f !important;
    color: #FFF !important;
}

/* Стилизация узлов-файлов */
.jstree-node.type-file > .jstree-wholerow {
    display: none !important;
}

/* Применяем стили к anchor */
.jstree-node.type-file > .jstree-anchor {
    background: #fff !important;
    border: 1px solid #CCC !important;
    color: #3d535f !important;
    font-weight: bold;
    font-size: 14px;
    padding: 5px 40px 5px 35px !important; /* Увеличен правый отступ для кнопки удаления */
    padding-left: 35px !important; /* Отступ для иконки файла */
    text-decoration: none;
    display: inline-block !important; /* Возвращаем inline-block для нахождения на одной строке с ocl */
    vertical-align: top !important;
    position: relative;
    z-index: 2 !important; /* Текст выше фона */
    background-image: url('<?=$GLOBALS['config']['base_url']?>/sahmatka/template/download.png') !important;
    background-repeat: no-repeat !important;
    background-position: 10px 8px !important;
    background-size: 15px !important;
    border-radius: 0;
    white-space: normal !important; /* Разрешаем перенос текста */
    word-break: break-word; /* Переносим длинные слова */
    overflow-wrap: break-word;
    height: auto;
    line-height: 1.4;
    box-sizing: border-box;
    width: calc(100% - 24px) !important; /* Вычитаем ширину ocl */
}

.jstree-node.type-file:hover > .jstree-anchor {
    background-color: #f0f0f0 !important;
    color: #000 !important;
}

/* Удаленные элементы - полупрозрачные */
.jstree-node.node-deleted {
    opacity: 0.5;
}

/* Кнопки действий */
.tree-actions {
    position: absolute !important;
    right: 5px !important;
    top: 0 !important;
    bottom: 0 !important;
    height: 100% !important;
    z-index: 3 !important;
    display: flex !important;
    align-items: center !important;
    pointer-events: auto !important;
}

.action-btn {
    display: inline-block;
    margin-left: 5px;
    cursor: pointer;
    text-decoration: none !important;
    font-weight: bold;
    font-size: 16px;
    line-height: 20px;
    padding: 0 5px;
}

.add-doc-btn {
    color: #FFF !important;
    font-size: 20px !important;
}
.add-doc-btn:hover { color: #beebff !important; }

.add-folder-btn {
    color: #FFF !important;
    font-size: 18px !important;
}
.add-folder-btn:hover { color: #beebff !important; }

.rename-btn {
    color: #FFF !important;
    font-size: 16px !important;
}
.rename-btn:hover { color: #beebff !important; }

.delete-btn {
    color: #ffaaaa !important;
    font-size: 16px !important;
}
.delete-btn:hover { color: #ff5555 !important; }

.restore-btn {
    color: #4CAF50 !important;
    font-size: 16px !important;
}
.restore-btn:hover { color: #45a049 !important; }

/* Для файлов кнопка удаления темная, т.к. фон светлый */
.type-file .delete-btn {
    color: #cc0000 !important;
}
.type-file .delete-btn:hover {
    color: #ff0000 !important;
}

/* Анимация подсветки */
@keyframes highlight-node {
    0% { background-color: rgba(255, 255, 0, 0.5); }
    100% { background-color: transparent; }
}

.highlight-node > .jstree-wholerow {
    animation: highlight-node 2s ease-out;
}

/* Стили для дат документа */
.doc-dates {
    display: block;
    font-size: 11px;
    color: #666;
    margin-top: 2px;
    font-weight: normal;
}

/* ============================================
   СОВРЕМЕННЫЕ СТИЛИ ФОРМЫ ПОИСКА И ОПЦИЙ
   ============================================ */

.doc-controls-wrapper {
    margin-bottom: 20px;
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #eee;
}

.doc-search-container {
    position: relative;
    margin-bottom: 15px;
}

.doc-search-input {
    width: 100%;
    padding: 12px 40px 12px 15px; /* Справа место под крестик */
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}

.doc-search-input:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.doc-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #999;
    font-size: 18px;
    cursor: pointer;
    padding: 5px;
    line-height: 1;
    border-radius: 50%;
    transition: color 0.2s;
    display: none; /* Скрыт по умолчанию, JS должен показывать если есть текст */
}

.doc-search-input:not(:placeholder-shown) + .doc-search-clear {
    display: block;
}

.doc-search-clear:hover {
    color: #333;
    background: #f0f0f0;
}

.doc-options-container {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.doc-checkbox-label {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    font-size: 14px;
    color: #555;
}

.doc-checkbox {
    accent-color: #4CAF50;
    width: 18px;
    height: 18px;
    margin-right: 8px;
    cursor: pointer;
}

.doc-checkbox-text {
    position: relative;
    top: 1px;
}

/* Адаптивность для контролов */
@media screen and (max-width: 768px) {
    .doc-controls-wrapper {
        padding: 10px;
        margin-bottom: 15px;
    }
    
    .doc-search-input {
        padding: 10px 35px 10px 12px;
        font-size: 16px; /* Чтобы не зумило на айфоне */
    }
}
    
/* ============================================
   СТИЛИ ДЛЯ ФИЛЬТРОВ ДАТ
   ============================================ */

.date-filters-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
    align-items: center;
}

.date-filter-group {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
}

.date-filter-label {
    font-size: 14px;
    color: #555;
    font-weight: 500;
    white-space: nowrap;
}

.date-input-wrapper {
    position: relative;
    width: 150px;
}

.date-filter-input {
    width: 100%;
    padding: 8px 30px 8px 10px; /* Справа место под крестик */
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    height: 36px;
    transition: all 0.3s ease;
    background: #fff;
}

.date-filter-input:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.date-filter-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #999;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
    display: block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    transition: all 0.2s;
}

.date-filter-clear:hover {
    color: #fff;
    background: #f44336;
}

/* Адаптивность для дат */
@media screen and (max-width: 768px) {
    .date-filters-container {
        display: none;
    }
    
    .date-filter-group {
        flex-direction: row;
        align-items: center;
        gap: 10px;
        width: 100%;
    }
    
    .date-input-wrapper {
        flex: 1;
        width: auto;
        position: relative;
    }
    
    .date-filter-input {
        font-size: 16px; /* Чтобы не зумило */
        width: 100%;
    }
    
    .date-filter-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }
}

/* ============================================
   АДАПТИВНЫЕ СТИЛИ ДЛЯ МОБИЛЬНЫХ УСТРОЙСТВ
   ============================================ */

/* Планшеты и небольшие экраны (до 768px) */
/* Кнопка меню для мобильных */
/* Кнопка меню для мобильных - скрыта по умолчанию */
.mobile-menu-btn {
    display: none !important;
    cursor: pointer;
    padding: 0 10px;
    font-size: 24px;
    line-height: 36px;
    color: #666;
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    height: 100%;
    z-index: 5;
    background: transparent;
}

/* Явно скрываем на десктопе */
@media screen and (min-width: 769px) {
    .mobile-menu-btn {
        display: none !important;
    }
}

/* Планшеты и небольшие экраны (до 768px) */
@media screen and (max-width: 768px) {
    .doc-dates {
        font-size: 8px;
    }

    /* Заменяем стрелочки на + и - */
    .jstree-default .jstree-ocl {
        background: transparent !important; /* Убираем стандартную иконку */
        width: 24px !important;
        height: 24px !important;
        line-height: 24px !important;
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        color: #000 !important;
    }
    
    /* Белый цвет для папок (так как фон темный) */
    .jstree-default .jstree-node.type-folder > .jstree-ocl {
        color: #000 !important;
    }
    
    .jstree-default .jstree-closed > .jstree-ocl::before {
        content: '+';
    }
    
    .jstree-default .jstree-open > .jstree-ocl::before {
        content: '-';
    }
    
    .jstree-default .jstree-leaf > .jstree-ocl::before {
        content: ''; /* Для файлов ничего не показываем */
    }
    
    /* Белая кнопка меню для папок */
    .jstree-node.type-folder > .jstree-anchor > .mobile-menu-btn {
        color: #fff !important;
    }

    /* Убираем зеленые пунктиры (hover эффект) на мобильных */
    .jstree-default .jstree-hovered,
    .jstree-default .jstree-wholerow-hovered {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* Исправление стилей текста (убираем тень и жирность) */
    .jstree-default-responsive .jstree-anchor,
    .jstree-default .jstree-anchor {
        font-weight: normal !important;
        font-size: 14px !important;
        text-shadow: none !important;
        white-space: normal !important; /* Разрешаем перенос строк */
        height: auto !important;
    }

    /* Многострочный текст для папок */
    .jstree-node.type-folder > .jstree-anchor {
        padding: 6px 40px 6px 5px !important; /* Отступ справа для кнопки меню */
        width: calc(100% - 20px) !important;
        line-height: 1.4 !important;
    }

    /* Многострочный текст для файлов */
    .jstree-node.type-file > .jstree-anchor {
        padding: 6px 40px 6px 30px !important; /* Отступ справа для кнопки меню */
        width: calc(100% - 20px) !important;
        line-height: 1.4 !important;
        background-position: 8px 8px !important;
    }

    /* Показываем кнопку меню */
    .mobile-menu-btn {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    /* Скрываем обычные кнопки действий на мобильных */
    .tree-actions {
        display: none !important;
    }
    
    /* Показываем действия при клике на меню (выплывание влево) */
    .tree-actions.show-mobile {
        display: flex !important;
        position: absolute;
        right: 30px !important;
        top: 0 !important;
        bottom: 0 !important;
        height: 100%;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 0 5px;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        z-index: 100 !important;
        align-items: center;
        animation: slideInRight 0.2s ease-out;
    }
    
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* Уменьшаем отступы для узлов */
    .jstree-default .jstree-node {
        margin-left: 10px;
        padding-left: 0 !important;
    }
    
    /* Стрелочка */
    .jstree-default .jstree-ocl {
        margin-top: 5px !important;
    }

    /* Адаптация фильтров */
    #doc-search-container {
        margin-bottom: 10px !important;
    }
    
    .date-filters {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    
    #date-from, #date-to {
        width: 100% !important;
        max-width: 200px;
    }
}

/* Мобильные телефоны (до 480px) */
@media screen and (max-width: 480px) {
    .jstree-default .jstree-node {
        margin-left: 5px;
    }
    
    .jstree-default-responsive .jstree-anchor,
    .jstree-default .jstree-anchor {
        font-size: 12px !important;
    }
}

</style>
