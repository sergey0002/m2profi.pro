// --------------------------
// Toolbar show/hide
// --------------------------
function showToolbar() { $('.toolbar').show(); }
function hideToolbar() { $('.toolbar').hide(); }

// --------------------------
// Обновление родительской папки в дереве файлов (с debug)
// --------------------------
// --------------------------
// Обновление родительской папки в дереве файлов (с debug + fallback)
// --------------------------
function refreshParentDirInTree(childPath) {
  console.log('[FWE] childPath:', childPath);

  if (!window.$ || !$('#filesajaxtree').length) {
    console.warn('[FWE] File tree not found, skip refresh');
    return;
  }
  const tree = $('#filesajaxtree').jstree(true);
  let parentPath = childPath.split('/').slice(0, -1).join('/');
  if (!parentPath) parentPath = '#'; // Корень дерева

  console.log('[FWE] parentPath:', parentPath);

  if (!tree) {
    console.error('[FWE] jstree instance not found');
    return;
  }

  // Для корня всегда полный refresh
  if (parentPath === '#') {
    console.log('[FWE] Выполняем полный refresh дерева (parentPath === "#")');
    tree.refresh();
    return;
  }

  // Для остальных директорий
  const node = tree.get_node(parentPath);
  if (!node) {
    console.warn('[FWE] Родительский узел "' + parentPath + '" не найден, делаем полное обновление дерева');
    tree.refresh();
    return;
  }

  try {
    tree.refresh_node(parentPath, function(updated) {
      if (!updated || (Array.isArray(updated) && !updated.length)) {
        console.warn('[FWE] Не удалось обновить ветку "' + parentPath + '", делаем полное обновление дерева');
        tree.refresh();
      } else {
        console.log('[FWE] Ветка "' + parentPath + '" успешно обновлена');
      }
    });
  } catch (e) {
    console.error('[FWE] Ошибка при обновлении ветки "' + parentPath + '":', e, 'Делаем полное обновление дерева');
    tree.refresh();
  }
}



// --------------------------
// Update Toolbar (без лишнего ajax, с кэшем метаданных)
// --------------------------
function updateToolbar(ti) {
  if (!TabsManager.tabsarray || !TabsManager.tabsarray[ti]) {
   // console.log('[FWE] updateToolbar: No tab, hiding toolbar');
    hideToolbar();
    return;
  }
  const tab = TabsManager.tabsarray[ti];
  const type = tab.type;
  const path = type === 'file' ? tab.file : tab.dir;
  const name = path.split('/').pop() || path;

 // console.log('[FWE] updateToolbar:', { ti, type, path, name });

  showToolbar();
  $('#toolbar-rename-input').val(name);
  $('#toolbar-save-btn').toggle(type === 'file');
  
  
  
  $('#toolbar-download-btn').off('click').on('click', () => {
  const { path, type } = $('.toolbar').data();
  if (type === 'dir') {
    // 1. Сначала запросим размер папки
    AjaxManager.request({
      url: `index.php?ajax=1&act=dirinfo&d=${encodeURIComponent(path)}`,
      type: 'GET',
      dataType: 'json',
      success(json) {
        let sizeStr = json && json.totalSize ? json.totalSize : '—';
        // Парсим размер (в байтах, если возможно)
        let sizeMb = 0;
        if (json && json.totalSize) {
          // Если строка вида "123.45 MB"
          const m = json.totalSize.match(/([\d.]+)\s*MB/i);
          if (m) sizeMb = parseFloat(m[1]);
          else if (/GB/i.test(json.totalSize)) sizeMb = 100;
        }
        let warn = '';
        if (sizeMb > 10) {
          warn = "\n\nВнимание: папка больше 10 мегабайт. Скачивание может завершиться с ошибкой или быть неполным.";
        }
        Modal.confirm(
          `Размер папки: ${sizeStr}.${warn}\nПродолжить скачивание архива?`,
          () => startDownloadDir(path)
        );
      }
    });
  } else {
    startDownloadFile(path);
  }
});

function startDownloadDir(path) {
  let iframe = $('#download-iframe');
  if (!iframe.length) {
    iframe = $('<iframe id="download-iframe" style="display:none"></iframe>').appendTo('body');
  }
  iframe.attr('src', `index.php?ajax=1&act=downloaddir&d=${encodeURIComponent(path)}`);
}

function startDownloadFile(path) {
  let iframe = $('#download-iframe');
  if (!iframe.length) {
    iframe = $('<iframe id="download-iframe" style="display:none"></iframe>').appendTo('body');
  }
  iframe.attr('src', `index.php?ajax=1&act=downloadfile&f=${encodeURIComponent(path)}`);
}
  
  
  
  

  $('#toolbar-upload-input').prop('multiple', type === 'dir');
  $('#toolbar-upload-btn').off('click').on('click', function() {
    $('#toolbar-upload-input').click();
   // console.log('[FWE] Upload button clicked');
  });
  $('#toolbar-delete-btn').show();

  if (type === 'file') {
    $('#toolbar-size').text(tab.size ? `Размер: ${tab.size}` : 'Размер: —');
    $('#toolbar-mtime').show().text(tab.mtime ? `Изменён: ${tab.mtime}` : 'Изменён: —');
    if (!tab.size || !tab.mtime) {
    //  console.log('[FWE] Fetch file info for:', path);
      AjaxManager.request({
        url: `index.php?ajax=1&act=fileinfo&f=${encodeURIComponent(path)}`,
        type: 'GET',
        dataType: 'json',
        success(json) {
         // console.log('[FWE] fileinfo response:', json);
          tab.size  = json.size;
          tab.mtime = json.mtime;
          $('#toolbar-size').text(`Размер: ${json.size}`);
          $('#toolbar-mtime').text(`Изменён: ${json.mtime}`);
        }
      });
    }
  } else {
    $('#toolbar-mtime').hide();
    $('#toolbar-size').text(tab.totalSize ? `Размер: ${tab.totalSize}` : 'Размер: —');
    if (!tab.totalSize) {
    //  console.log('[FWE] Fetch dir info for:', path);
      AjaxManager.request({
        url: `index.php?ajax=1&act=dirinfo&d=${encodeURIComponent(path)}`,
        type: 'GET',
        dataType: 'json',
        success(json) {
       //   console.log('[FWE] dirinfo response:', json);
          tab.totalSize = json.totalSize;
          $('#toolbar-size').text(`Размер: ${json.totalSize}`);
        }
      });
    }
  }
  $('.toolbar').data({ path, type, ti });
}

// --------------------------
// Toolbar Event Handlers
// --------------------------

// Переименование (с debug POST путём и именем)
$('#toolbar-rename-form').on('submit', function(e) {
  e.preventDefault();
  const { path, type, ti } = $('.toolbar').data();

  if (!path) {
    Logger.add('Путь не указан. Попробуйте ещё раз.');
    return;
  }
  let newName = $('#toolbar-rename-input').val().trim();

  if (!newName) {
    Logger.add('Имя не может быть пустым');
    return;
  }
  if (newName === path.split('/').pop()) {
    Logger.add('Новое имя совпадает со старым');
    return;
  }
  const basePath = path.split('/').slice(0, -1).join('/');
  const newPath = basePath ? (basePath + '/' + newName) : newName;

  function doRename() {
    let postData = { newName };
    if (type === 'file') postData.f = path;
    else postData.d = path;
  //  console.log('[FWE] doRename, sending:', postData);

    AjaxManager.request({
      url: `index.php?ajax=1&act=rename`,
      type: 'POST',
      dataType: 'json',
      data: postData,
      success(response) {
      //  console.log('[FWE] rename response:', response);
        if (!response || !response.ok || !response.newPath) {
          const msg = (response && response.error) ? response.error : 'Ошибка переименования на сервере!';
          Modal.confirm(msg + "\nПопробовать ещё раз?", doRename);
          return;
        }
        Logger.add('Переименовано в ' + newName);

        // --- Обновление состояния
        let tab = TabsManager.tabsarray[ti];
        let oldUrlKey = (type === 'file') ? path : 'dir:' + path;
        let newUrlKey = (type === 'file') ? response.newPath : 'dir:' + response.newPath;

        delete TabsManager.url_tabsarray[oldUrlKey];
        if (type === 'file') tab.file = response.newPath;
        else tab.dir = response.newPath;
        TabsManager.url_tabsarray[newUrlKey] = ti;

        $(`#fwe_bi_urlf_${ti}`).val(response.newPath);
        $(`#fwe_hi_${ti}`).html(`
          <span class="mdi ${type === 'file' ? 'mdi-file-outline' : 'mdi-folder-outline'}"></span> ${escapeHtml(newName)}
          <button class="tabclose" data-ti="${ti}" title="Закрыть">×</button>
        `);

        // Сбросить кэш метаданных
        if (type === 'file') {
          delete tab.size; delete tab.mtime;
        } else {
          delete tab.totalSize;
        }
        if (typeof FileTree !== "undefined") {
          if (FileTree.unlightFile && path) FileTree.unlightFile(path);
          if (FileTree.lightFile && response.newPath) FileTree.lightFile(response.newPath);
        }

        // --- Обновить родительскую папку в дереве
        refreshParentDirInTree(path);

        updateToolbar(ti);
        StateManager.saveTabs(TabsManager.tabsarray, TabsManager.global_ti);
      }
    });
  }
  doRename();
});

// Удаление (с debug)
$('#toolbar-delete-btn').on('click', function() {
  const { path, type, ti } = $('.toolbar').data();
  function doDelete() {
    let postData = {};
    if (type === 'file') postData.f = path;
    else postData.d = path;
   // console.log('[FWE] doDelete, sending:', postData);

    AjaxManager.request({
      url: `index.php?ajax=1&act=delete`,
      type: 'POST',
      dataType: 'json',
      data: postData,
      success(response) {
     //   console.log('[FWE] delete response:', response);
        if (!response || !response.ok) {
          const msg = (response && response.error) ? response.error : 'Ошибка удаления!';
          Modal.confirm(msg + "\nПопробовать ещё раз?", doDelete);
          return;
        }
        Logger.add('Удалено: ' + path);
        TabsManager.closeTab(ti);

        // --- Обновить родительскую папку в дереве
        refreshParentDirInTree(path);
      }
    });
  }
  Modal.confirm('Вы уверены, что хотите удалить?', doDelete);
});

// Сохранение (с debug)
// Сохранение (с debug, полностью безопасно)
$('#toolbar-save-btn').on('click', () => {
  const { ti } = $('.toolbar').data();
  function doSave() {
    EditorManager.saveFile(ti, {
      success(response) {
        // Debug для консоли:
        console.log('[FWE] save response:', response);

        // Универсальная проверка результата
        if (!response || !(response.ok || response.status === 'ok')) {
          const msg = (response && response.error) ? response.error : 'Ошибка сохранения!';
          Modal.confirm(msg + "\nПопробовать ещё раз?", doSave);
          return;
        }
        Logger.add('Файл успешно сохранён');
        if (TabsManager.tabsarray[ti]) {
          delete TabsManager.tabsarray[ti].size;
          delete TabsManager.tabsarray[ti].mtime;
        }
        updateToolbar(ti);
      }
    });
  }
  doSave();
});

// Upload (single or multiple, с debug)
$('#toolbar-upload-input').on('change', function() {
  const files = this.files;
  const { path, type, ti } = $('.toolbar').data();
  if (!files.length) return;
  if (type === 'file') {
    uploadSingle(files[0], path, ti);
  } else {
    uploadMultiple(files, path, ti);
  }
  $(this).val(''); // сброс выбора
});

// Функция загрузки одиночного файла (с debug)
function uploadSingle(file, path, ti) {
  const ext = path.split('.').pop().toLowerCase();
  if (file.name.split('.').pop().toLowerCase() !== ext) {
    Logger.add('Неверное расширение: .' + ext);
    return;
  }
  const form = new FormData();
  form.append('file', file);

  function doUpload() {
   // console.log('[FWE] doUpload single, path:', path);
    AjaxManager.request({
      url: `index.php?ajax=1&act=upload&f=${encodeURIComponent(path)}`,
      type: 'POST',
      dataType: 'json',
      data: form,
      processData: false,
      contentType: false,
      success(response) {
      //  console.log('[FWE] upload response:', response);
        if (!response || !response.ok) {
          const msg = (response && response.error) ? response.error : 'Ошибка загрузки файла!';
          Modal.confirm(msg + "\nПопробовать ещё раз?", doUpload);
          return;
        }
        Logger.add('Файл обновлён');
        if (TabsManager.tabsarray[ti]) {
          delete TabsManager.tabsarray[ti].size;
          delete TabsManager.tabsarray[ti].mtime;
        }
        updateToolbar(ti);
        refreshParentDirInTree(path); // Если нужно обновление дерева после upload
      }
    });
  }
  doUpload();
}

// Функция загрузки нескольких файлов в директорию (с debug)
function uploadMultiple(files, dir, ti) {
  let i = 0;
  function next() {
    if (i >= files.length) {
      Logger.add('Все файлы загружены');
      if (TabsManager.tabsarray[ti]) delete TabsManager.tabsarray[ti].totalSize;
      updateToolbar(ti);
       refreshParentDirInTree(dir); // Если нужно обновление дерева после upload
      return;
    }
    const f = files[i++];
    const form = new FormData();
    form.append('file', f);

    function doUpload() {
      //console.log('[FWE] doUpload multiple, dir:', dir, 'file:', f.name);
      AjaxManager.request({
        url: `index.php?ajax=1&act=upload&d=${encodeURIComponent(dir)}`,
        type: 'POST',
        dataType: 'json',
        data: form,
        processData: false,
        contentType: false,
        success(response) {
         // console.log('[FWE] upload (multiple) response:', response);
          if (!response || !response.ok) {
            const msg = (response && response.error) ? response.error : ('Ошибка загрузки файла: ' + f.name);
            Modal.confirm(msg + "\nПопробовать ещё раз?", doUpload);
            return;
          }
          Logger.add(f.name + ' загружен');
          next();
        }
      });
    }
    doUpload();
  }
  next();
}

// --------------------------
// Автоматически скрывать тулбар если нет вкладок
// --------------------------
function checkToolbarVisibility() {
  const tabsOpen = Object.keys(TabsManager.tabsarray || {}).length;
  if (tabsOpen) {
    showToolbar();
  } else {
    hideToolbar();
  }
}

$(function() {
  checkToolbarVisibility();
 // console.log('[FWE] toolbar.js initialized');
});
