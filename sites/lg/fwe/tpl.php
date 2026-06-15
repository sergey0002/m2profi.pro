<?php
// tpl.php — чистый адаптивный шаблон FWE-редактора (v8+)
//  • без Bootstrap
//  • поддержка drag-resize и spp_nav
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>FWE Online Editor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Иконки + jsTree -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.1.96/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/themes/default-dark/style.min.css">

  <!-- Ace-Editor (CSS-тема) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ace-builds@1.15.1/css/ace.min.css">
  <link rel="stylesheet" href="https://unpkg.com/ace-diff@latest/dist/ace-diff.min.css">

  <!-- Основные стили редактора -->
  <link rel="stylesheet" href="css/theme-base.css?v=9">
  <link rel="stylesheet" href="css/theme-components.css?v=9">
  <link rel="stylesheet" href="css/theme-editor.css?v=9">
</head>
<body>

<!-- ░░░ Прогресс-бар ░░░ -->
<div id="progress-bar-overlay">
  <div id="progress-bar-text">Загрузка…</div>
  <div class="progress"><div id="progress-bar-fill" class="progress-bar"></div></div>
</div>

<!-- ░░░ Гамбургеры (только <1000 px) ░░░ -->
<button id="btn-left"  class="panel-btn left"  title="Открыть файлы">&#9776;</button>
<button id="btn-right" class="panel-btn right" title="Открыть опции">&#9776;</button>

<!-- ░░░ Flex-layout ░░░ -->
<div id="layout">

  <!-- ◀ Левая панель (Файлы) -->
  <aside id="panel-left" class="side-panel1 panel left">
    <div class="header"><?= $fw_tpl->attr('leftpanel'); ?></div>
    <div class="body-scroll"><div id="filesajaxtree"></div></div>
  </aside>
  <!-- Вертикальная полоса-закрыватель слева -->
  <button id="close-left" class="spp_nav spp_nav1 hidden" title="Скрыть панель">×</button>

  <!-- Центр (Редактор) -->
  <main id="content" class="content">
    <header class="header" id="editor_header">
      <div id="tabs_headers"></div>
      <nav id="toolbar" class="toolbar">
        <form id="toolbar-rename-form">
          <input id="toolbar-rename-input" type="text" placeholder="Новое имя">
          <button type="submit" title="Переименовать"><i class="mdi mdi-rename-box"></i></button>
        </form>

        <button id="toolbar-save-btn"   title="Сохранить"><i class="mdi mdi-content-save-all"></i></button>
        <button id="toolbar-delete-btn" title="Удалить"><i class="mdi mdi-delete-outline"></i></button>
        <button id="toolbar-download-btn" title="Скачать"><i class="mdi mdi-download"></i></button>
        <button id="toolbar-upload-btn"   title="Загрузить"><i class="mdi mdi-upload"></i></button>
        <input type="file" id="toolbar-upload-input" hidden>

        <span id="toolbar-size"></span>
        <span id="toolbar-mtime"></span>
        <button id="fwe_closeall" title="Закрыть все">×</button>
      </nav>
    </header>
    <div class="body-scroll">
      <div id="editor_content"><div id="tabs_body"></div></div> 
      <div id="default_content" style="display:none;max-width:80%;margin:2em auto;padding:2em 1em 1em 1em;background:#232323;border-radius:16px;box-shadow:0 2px 8px #00000030;">
        <div id="fileshistory"></div>
        <!-- История закрытых файлов появится тут через JS -->
      </div>
    </div>
  </main>

  <!-- ▶ Правая панель (Опции и Outline) -->
  <aside id="panel-right" class="side-panel2 panel right">
    <div class="header">Структура кода</div>
    <div class="body-scroll" id="outline_panel"></div>

    <div class="header">Опции</div>
    <div class="body-scroll">
      <section class="fwe_eopt">
        <h4><span class="mdi mdi-cog-outline"></span> Настройки редактора</h4>
        <label><input type="checkbox" id="fwe_eopt__wrap"> Перенос по словам</label>
        <label>Размер шрифта <input type="number" id="fwe_eopt__fontsize" value="12" min="7" max="20"></label>
        <label>Тема
          <select id="fwe_eopt__theme">
            <option value="monokai">Тёмная</option>
            <option value="chrome">Светлая</option>
          </select>
        </label>
      </section>
      <div id="editorstatus"></div>
    </div>
  </aside>
  <!-- Вертикальная полоса-закрыватель справа -->
  <button id="close-right" class="spp_nav spp_nav2 hidden" title="Скрыть панель">×</button>

</div><!-- /#layout -->

<!-- ░░░ Скрытые чекбоксы управления панелями (для мобилы) ░░░ -->
<input type="checkbox" id="side-checkbox1" hidden>
<input type="checkbox" id="side-checkbox2" hidden>

<!-- ░░░ JS-библиотеки ░░░ -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/jstree.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.9.6/ace.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.9.6/ext-modelist.min.js"></script>
<script src="https://unpkg.com/ace-diff@latest/dist/ace-diff.min.js"></script>

<!-- ░░░ panels.js — управление панелями ░░░ -->
<script src="js/panels.js?v=9"></script>

<!-- ░░░ Скрипты редактора ░░░ -->
<script src="js/toolbar.js?v=8"></script>
<script src="js/tabsManager.js?v=8"></script>
<script src="js/editorManager.js?v=8"></script>
<script src="js/filestreeManager.js?v=8"></script>
<script src="js/codeEditor.js?v=8"></script>
<script src="js/script.js?v=8"></script>
</body>
</html>
