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

/* Стрелочки для сворачивания/разворачивания - УБИРАЕМ кастомные текстовые стрелки, возвращаем стандартные */
/*
.jstree-default .jstree-ocl:before {
    content: '▶';
    font-size: 12px;
    color: #666;
    display: inline-block;
    width: 100%;
    text-align: center;
    line-height: 24px;
}

.jstree-default .jstree-open > .jstree-ocl:before {
    content: '▼';
}

.jstree-default .jstree-leaf > .jstree-ocl:before {
    content: '';
}
*/

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
    background: #3d535f !important;
    margin: 1px 0;
    height: auto !important;
    min-height: 36px !important;
    border-radius: 4px;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1 !important; /* Уровень фона */
    pointer-events: none !important;
}

.jstree-node.type-folder > .jstree-anchor {
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
    background: transparent !important;
    border-radius: 4px;
    box-sizing: border-box;
    width: calc(100% - 24px) !important; /* Вычитаем ширину ocl */
}

.jstree-node.type-folder:hover > .jstree-wholerow {
    background: #2d434f !important;
}
.jstree-node.type-folder:hover > .jstree-anchor {
    color: #FFF !important;
}

/* Стилизация узлов-файлов */
.jstree-node.type-file > .jstree-wholerow {
    background: #fff !important;
    margin: 3px 0;
    border: 1px solid #CCC !important;
    border-radius: 0;
    min-height: 30px !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1 !important; /* Уровень фона */
    pointer-events: none !important;
}

/* Применяем стили к anchor */
.jstree-node.type-file > .jstree-anchor {
    color: #3d535f !important;
    font-weight: bold;
    font-size: 14px;
    border: none !important;
    padding: 5px 40px 5px 35px !important; /* Увеличен правый отступ для кнопки удаления */
    padding-left: 35px !important; /* Отступ для иконки файла */
    text-decoration: none;
    display: inline-block !important; /* Возвращаем inline-block для нахождения на одной строке с ocl */
    vertical-align: top !important;
    position: relative;
    z-index: 2 !important; /* Текст выше фона */
    background-image: url('/sahmatka/template/download.png') !important;
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

.jstree-node.type-file:hover > .jstree-wholerow {
    background-color: #f0f0f0 !important;
}
.jstree-node.type-file:hover > .jstree-anchor {
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
    top: 8px !important;
    z-index: 3 !important;
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
   АДАПТИВНЫЕ СТИЛИ ДЛЯ МОБИЛЬНЫХ УСТРОЙСТВ
   ============================================ */

/* Планшеты и небольшие экраны (до 768px) */
@media screen and (max-width: 768px) {
    /* Уменьшаем отступы для узлов */
    .jstree-default .jstree-node {
        margin-left: 15px;
        padding-left: 5px !important;
    }
    
    /* Уменьшаем размер стрелочки */
    .jstree-default .jstree-ocl {
        width: 20px !important;
        height: 20px !important;
        margin-top: 3px !important;
    }
    
    /* Папки - уменьшаем шрифт и отступы */
    .jstree-node.type-folder > .jstree-anchor {
        font-size: 14px;
        padding: 6px 90px 6px 5px !important;
        min-height: 32px;
        width: calc(100% - 20px) !important;
    }
    
    .jstree-node.type-folder > .jstree-wholerow {
        min-height: 32px !important;
    }
    
    /* Файлы - уменьшаем шрифт и отступы */
    .jstree-node.type-file > .jstree-anchor {
        font-size: 13px;
        padding: 4px 35px 4px 30px !important;
        background-position: 8px 6px !important;
        background-size: 13px !important;
        width: calc(100% - 20px) !important;
    }
    
    .jstree-node.type-file > .jstree-wholerow {
        min-height: 28px !important;
    }
    
    /* Кнопки действий - уменьшаем размер */
    .tree-actions {
        right: 3px !important;
        top: 6px !important;
    }
    
    .action-btn {
        margin-left: 3px;
        font-size: 14px;
        padding: 0 3px;
    }
    
    .add-doc-btn { font-size: 18px !important; }
    .add-folder-btn { font-size: 16px !important; }
    .rename-btn { font-size: 14px !important; }
    .delete-btn { font-size: 14px !important; }
}

/* Мобильные телефоны (до 480px) */
@media screen and (max-width: 480px) {
    /* Еще больше уменьшаем отступы */
    .jstree-default .jstree-node {
        margin-left: 10px;
        padding-left: 3px !important;
    }
    
    /* Минимальный размер стрелочки */
    .jstree-default .jstree-ocl {
        width: 18px !important;
        height: 18px !important;
        margin-top: 2px !important;
    }
    
    /* Папки - компактный вид */
    .jstree-node.type-folder > .jstree-anchor {
        font-size: 13px;
        padding: 5px 70px 5px 3px !important;
        min-height: 28px;
        width: calc(100% - 18px) !important;
    }
    
    .jstree-node.type-folder > .jstree-wholerow {
        min-height: 28px !important;
    }
    
    /* Файлы - компактный вид */
    .jstree-node.type-file > .jstree-anchor {
        font-size: 12px;
        padding: 3px 30px 3px 25px !important;
        background-position: 5px 5px !important;
        background-size: 12px !important;
        width: calc(100% - 18px) !important;
        line-height: 1.3;
    }
    
    .jstree-node.type-file > .jstree-wholerow {
        min-height: 26px !important;
    }
    
    /* Кнопки действий - скрываем некоторые на очень маленьких экранах */
    .tree-actions {
        right: 2px !important;
        top: 4px !important;
    }
    
    .action-btn {
        margin-left: 2px;
        font-size: 12px;
        padding: 0 2px;
    }
    
    /* Скрываем кнопки добавления папки и переименования на маленьких экранах */
    .add-folder-btn,
    .rename-btn {
        display: none !important;
    }
    
    .add-doc-btn { font-size: 16px !important; }
    .delete-btn { font-size: 13px !important; }
    
    /* Даты документа - еще меньше */
    .doc-dates {
        font-size: 10px;
        margin-top: 1px;
    }
    
    /* Поисковая строка и фильтры - адаптивность */
    #doc-search-container {
        margin-bottom: 10px !important;
    }
    
    #doc-search-input {
        padding: 6px !important;
        padding-right: 70px !important;
        font-size: 14px;
    }
    
    #doc-search-clear {
        padding: 4px 8px !important;
        font-size: 12px;
    }
    
    /* Фильтры дат - вертикальное расположение */
    div[style*="display: flex"] {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    
    #date-from,
    #date-to {
        width: 100% !important;
        max-width: 150px;
    }
    
    #date-clear {
        margin-top: 5px;
        padding: 4px 8px !important;
        font-size: 12px;
    }
}

</style>
