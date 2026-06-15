// --------------------------


// ClosedTabsHistory — история закрытых вкладок
// --------------------------
class ClosedTabsHistory {
  static key = 'fwe_closed_tabs';
  static max = 10; // Максимум 10 файлов/папок в истории

  static add(tab) {
    let history = this.getAll();
    const path = tab.file || tab.dir;
    // Удаляем все дубли по path (оставляем только новые)
    history = history.filter(entry => entry.path !== path);
    // Добавляем новую запись в начало
    history.unshift({
      path,
      type: tab.type,
      closedAt: new Date().toLocaleString()
    });
    history = history.slice(0, this.max); // максимум 10 последних
    try { localStorage.setItem(this.key, JSON.stringify(history)); } catch {}
  }

  static getAll() {
    try {
      return JSON.parse(localStorage.getItem(this.key)) || [];
    } catch {
      return [];
    }
  }

  static clear() {
    localStorage.removeItem(this.key);
  }

  static render(containerSelector = '#fileshistorys') {
    const history = this.getAll();
    const $container = $(containerSelector);
    if (!history.length) {
      $container.html('<em>История закрытых файлов пуста.</em>');  
      return;
    }
    let html = `<div style="display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;">Недавно закрытые файлы</h3>
      <button id="clear_closed_tabs" style="font-size:12px;">Очистить</button>
    </div>
    <ul style="list-style:none;padding:0;">`;
    history.forEach(entry => {
      html += `<li>
        <a href="#" class="restore-closed-tab" data-path="${entry.path}" data-type="${entry.type}">
          <span class="mdi ${entry.type==='dir'?'mdi-folder-outline':'mdi-file-outline'}"></span>
          ${escapeHtml(entry.path)}
        </a>
        <span style="color:#888;font-size:90%;">(${entry.closedAt})</span>
      </li>`;
    });
    html += '</ul>';
    $container.html(html);
  }
}

// --------------------------
// TabsManager (refactored & AJAX-optimized)
// --------------------------
const TabsManager = {
  tabsarray: {},
  url_tabsarray: {},
  global_ti: null,

  addTab(file, caption = '', meta = {}) {
    if (this.url_tabsarray[file]) {
      this.selectTab(this.url_tabsarray[file]);
      return false;
    }
    const ti = this._nextTi();
    this.tabsarray[ti] = {
      file,
      editor: null,
      unsaved: false,
      type: 'file',
      ...meta
    };
    this.url_tabsarray[file] = ti;

    $('#tabs_headers').append(`
      <div class="fwe_tabhead_link" id="fwe_hi_${ti}" data-ti="${ti}">
        <span class="mdi mdi-file-outline"></span> ${escapeHtml(caption)}
        <button class="tabclose" data-ti="${ti}" title="Закрыть">×</button>
      </div>
    `);
    $('#tabs_body').append(`
      <div class="fwe_tabbody" id="fwe_bi_${ti}" data-ti="${ti}">
        <form id="fwe_form_${ti}">
          <input type="text" id="fwe_bi_urlf_${ti}" class="finput" readonly value="${escapeHtml(file)}"/>
          <div class="fwe_data" id="fwe_bi_dataf_${ti}"></div>
        </form>
      </div>
    `);

    this.selectTab(ti);
    if (typeof FileTree !== "undefined" && FileTree.lightFile) FileTree.lightFile(file);
    StateManager.saveTabs(this.tabsarray, this.global_ti);
    updateContentDisplay();
    return ti;
  },

  addDirTab(dirPath, caption = '') {
    const key = 'dir:' + dirPath;
    if (this.url_tabsarray[key]) {
      this.selectTab(this.url_tabsarray[key]);
      return false;
    }
    const ti = this._nextTi();
    this.tabsarray[ti] = { dir: dirPath, editor: null, unsaved: false, type: 'dir' };
    this.url_tabsarray[key] = ti;

    $('#tabs_headers').append(`
      <div class="fwe_tabhead_link" id="fwe_hi_${ti}" data-ti="${ti}">
        <span class="mdi mdi-folder-outline"></span> ${escapeHtml(caption || dirPath)}
        <button class="tabclose" data-ti="${ti}" title="Закрыть">×</button>
      </div>
    `);
    $('#tabs_body').append(`
      <div class="fwe_tabbody" id="fwe_bi_${ti}" data-ti="${ti}">
        <div class="fwe_dir_listing" id="fwe_dir_listing_${ti}"></div>
      </div>
    `);

    this.selectTab(ti);
    StateManager.saveTabs(this.tabsarray, this.global_ti);
    updateContentDisplay();
    return ti;
  },

  closeTab(ti, force = false, skipSaveAsk = false, afterClose = null) {
    if (!this.tabsarray[ti]) { afterClose && afterClose(false); return; }
    let file, isDir = false, urlKey;
    if (this.tabsarray[ti].file)     { file = this.tabsarray[ti].file; urlKey = file; }
    else if (this.tabsarray[ti].dir) { file = this.tabsarray[ti].dir; isDir = true; urlKey = 'dir:' + file; }

    if (!force && this.isUnsaved(ti) && !skipSaveAsk) {
      if (typeof Modal !== "undefined" && Modal.confirm) {
        Modal.confirm(
          `Документ "${file}" не сохранён!\nУверены, что хотите закрыть его без сохранения?`,
          () => { this._doCloseTab(ti, afterClose, isDir, urlKey, false); },
          () => { afterClose && afterClose(true); }
        );
      } else {
        this._doCloseTab(ti, afterClose, isDir, urlKey, false);
      }
      return;
    }
    this._doCloseTab(ti, afterClose, isDir, urlKey, false);
  },

  _doCloseTab(ti, afterClose, isDir = false, urlKey = null, canceled = false) {
    let file;
    if (this.tabsarray[ti]?.file) file = this.tabsarray[ti].file;
    else if (this.tabsarray[ti]?.dir) file = this.tabsarray[ti].dir;

    // --- сохраняем в историю закрытых вкладок ---
    if (!canceled && this.tabsarray[ti]) ClosedTabsHistory.add(this.tabsarray[ti]);

    $('#fwe_hi_' + ti).remove();
    $('#fwe_bi_' + ti).remove();
    if (file && !isDir && typeof FileTree !== "undefined" && FileTree.unlightFile) FileTree.unlightFile(file);

    this.unmarkUnsaved(ti, file);
    if (urlKey) delete this.url_tabsarray[urlKey];
    delete this.tabsarray[ti];

    const keys = Object.keys(this.tabsarray);
    if (keys.length) {
      this.selectTab(keys[keys.length - 1]);
      if (typeof updateToolbar === "function") updateToolbar(keys[keys.length - 1]);
    } else {
      this.global_ti = null;
      if (typeof hideToolbar === "function") hideToolbar();
    }

    StateManager.saveTabs(this.tabsarray, this.global_ti);

    updateContentDisplay();
    afterClose && afterClose(canceled);
  },

  isUnsaved(ti) {
    return $('#fwe_hi_' + ti).hasClass('opened_file_unsave');
  },

  selectTab(ti) {
    $('.fwe_tabhead_link').removeClass('fwe_tabhead_link_active');
    $('#fwe_hi_' + ti).addClass('fwe_tabhead_link_active');
    $('.fwe_tabbody').hide();
    $('#fwe_bi_' + ti).show();
    this.global_ti = ti;
    if (typeof updateToolbar === "function") updateToolbar(ti);
    StateManager.saveTabs(this.tabsarray, this.global_ti);
    updateContentDisplay();
  },

  markUnsaved(ti, file) {
    if (file) $(`a[dpath="${file}"]`).addClass('opened_file_unsave');
    $(`#fwe_hi_${ti}`).addClass('opened_file_unsave');
    if (this.tabsarray[ti]) this.tabsarray[ti].unsaved = true;
  },

  unmarkUnsaved(ti, file) {
    if (file) $(`a[dpath="${file}"]`).removeClass('opened_file_unsave');
    $(`#fwe_hi_${ti}`).removeClass('opened_file_unsave');
    if (this.tabsarray[ti]) this.tabsarray[ti].unsaved = false;
  },

  restoreTabsFromState() {
    const state = StateManager.loadTabs();
    if (state?.open?.length) {
      let toActivate = null;
      state.open.forEach(tab => {
        let ti = null;
        if (tab.type === 'dir') {
          ti = this.addDirTab(tab.dir, tab.caption);
          if (typeof DirectoryHandler !== "undefined" && ti) DirectoryHandler.handle(tab.dir);
          if (tab.dir === state.active) toActivate = ti;
        } else {
          ti = this.addTab(tab.file, tab.caption);
          if (typeof EditorManager !== "undefined" && ti) {
            EditorManager.loadFileToEditor(
              `index.php?ajax=1&act=loadfile&f=${encodeURIComponent(tab.file)}`,
              tab.file, `fwe_bi_dataf_${ti}`, ti
            );
          }
          if (tab.file === state.active) toActivate = ti;
        }
      });
      if (toActivate) {
        this.selectTab(toActivate);
      }
    }
  },

  _nextTi() {
    const keys = Object.keys(this.tabsarray).map(Number);
    return keys.length ? Math.max(...keys) + 1 : 1;
  }
};

// --------------------------
// StateManager (совместимый)
// --------------------------
const StateManager = {
  key: 'fwe_open_tabs',
  saveTabs(tabsarray, global_ti) {
    const state = {
      open: Object.keys(tabsarray).map(ti => {
        const t = tabsarray[ti];
        const caption = $(`#fwe_hi_${ti}`).clone().children().remove().end().text().trim();
        const base = t.type === 'dir'
          ? { type: 'dir', dir: t.dir, caption }
          : { type: 'file', file: t.file, caption };
        if (t.size !== undefined) base.size = t.size;
        if (t.mtime !== undefined) base.mtime = t.mtime;
        return base;
      }),
      active: (() => {
        if (!global_ti) return null;
        const t = tabsarray[global_ti];
        return t.type === 'dir' ? t.dir : t.file;
      })()
    };
    try { localStorage.setItem(this.key, JSON.stringify(state)); } catch {}
  },
  loadTabs() {
    try { return JSON.parse(localStorage.getItem(this.key)); } catch { return null; }
  }
};

// --------------------------
// Управление показом дефолтного контента/шапки/редактора
// --------------------------
function updateContentDisplay() {
  const tabsCount = Object.keys(TabsManager.tabsarray || {}).length;
  $('#editor_header').toggle(tabsCount > 0);
  $('#editor_content').toggle(tabsCount > 0);
  $('#default_content').toggle(tabsCount === 0);
  if (tabsCount === 0) ClosedTabsHistory.render('#default_content');
}

// --------------------------
// Document Ready (обработчики)
// --------------------------
$(function() {
  if (typeof hideToolbar === "function") hideToolbar();
  updateContentDisplay();

  // Клик по табу
  $(document).on('click', '.fwe_tabhead_link', function() {
    const ti = $(this).data('ti');
    TabsManager.selectTab(ti);
    return false;
  });

  // Клик по крестику
  $(document).on('click', '.tabclose', function(e) {
    e.stopPropagation();
    const ti = $(this).data('ti');
    TabsManager.closeTab(ti);
    return false;
  });

  // Закрыть все
  $('#fwe_closeall').click(function() {
    let stopCloseAll = false;
    function closeNext() {
      if (stopCloseAll) return;
      const keys = Object.keys(TabsManager.tabsarray);
      if (keys.length === 0) return;
      TabsManager.closeTab(keys[keys.length - 1], false, false, function(canceled) {
        if (canceled) {
          stopCloseAll = true;
          return;
        }
        closeNext();
      });
    }
    closeNext();
    return false;
  });

  // Восстановление файла/папки из истории
  $(document).on('click', '.restore-closed-tab', function(e){
    e.preventDefault();
    const path = $(this).data('path');
    const type = $(this).data('type');
    if(type === 'file') {
      FileHandler.handle(path, path.split('/').pop());
    } else {
      DirectoryHandler.handle(path);
    }
  });

  // Очистить историю закрытых вкладок
  $(document).on('click', '#clear_closed_tabs', function(e) {
    ClosedTabsHistory.clear();
    ClosedTabsHistory.render('#default_content');
  });

  // При загрузке если есть вкладка
  if (TabsManager.global_ti && typeof updateToolbar === "function") updateToolbar(TabsManager.global_ti);
});

// --------------------------
// HTML-escape helper
// --------------------------
function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"'`=\/]/g, function(s) {
    return ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
      "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
    })[s];
  });
}
