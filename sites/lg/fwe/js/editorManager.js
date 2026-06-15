// --------- OutlineManager ----------

const OutlineManager = {
    outline: [],
    editor: null,
    container: '#outline_panel',
    _bindedEditor: null,

    init(editor, container = '#outline_panel') {
        this.editor = editor;
        this.container = container;
        this._bindAceEvents();
        this.update();
    },

    clear(container = '#outline_panel') {
        $(container).empty();
        this.outline = [];
        this.editor = null;
        this._bindedEditor = null;
    },

    update() {
        if (!this.editor) return;
        let file = null;
        if (typeof TabsManager !== "undefined" && TabsManager.tabsarray) {
            for (let ti in TabsManager.tabsarray) {
                if (TabsManager.tabsarray[ti].editor === this.editor) {
                    file = TabsManager.tabsarray[ti].file || '';
                    break;
                }
            }
        }
        const ext = (file && file.split('.').pop().toLowerCase()) || '';
        const code = this.editor.getValue();
        if (!['php', 'css', 'js'].includes(ext)) {
            this.clear(this.container);
            return;
        }
        if (!code) return this.render([]);
        if (ext === 'php')      this.outline = this.parsePHP(code);
        else if (ext === 'css') this.outline = this.parseCSS(code);
        else if (ext === 'js')  this.outline = this.parseJS(code);
        else this.outline = [];
        this.render(this.outline);
        this._highlightActive(this._getActiveLines());
    },

    parsePHP(code) {
        const lines = code.split('\n');
        const outline = [];
        let currentClass = null;
        let classBraceCount = 0;
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            let m = line.match(/^\s*(?:abstract\s+|final\s+)?class\s+([a-zA-Z0-9_]+)/);
            if (m) {
                currentClass = { name: m[1], type: 'class', line: i + 1, children: [] };
                classBraceCount = 0;
                outline.push(currentClass);
                continue;
            }
            if (currentClass !== null) {
                classBraceCount += (line.match(/{/g) || []).length;
                classBraceCount -= (line.match(/}/g) || []).length;
                m = line.match(/^\s*(?:public|protected|private|static|\s)*function\s+([a-zA-Z0-9_]+)\s*\(/);
                if (m) {
                    currentClass.children.push({ name: m[1] + '()', type: 'method', line: i + 1 });
                }
                if (classBraceCount <= 0) {
                    currentClass = null;
                    classBraceCount = 0;
                }
                continue;
            }
            m = line.match(/^\s*function\s+([a-zA-Z0-9_]+)\s*\(/);
            if (m) {
                outline.push({ name: m[1] + '()', type: 'function', line: i + 1 });
            }
        }
        return outline;
    },

    parseCSS(code) {
        const lines = code.split('\n');
        const outline = [];
        // Парсинг селекторов
        for (let i = 0; i < lines.length; i++) {
            const m = lines[i].match(/^([^{]+)\s*\{/);
            if (m) {
                outline.push({ name: m[1].trim(), type: 'selector', line: i + 1 });
            }
        }
        // Парсинг комментариев /* ... */
        // Сканируем код на многострочные комментарии
        const commentRegex = /\/\*([\s\S]*?)\*\//g;
        let match;
        let codeSoFar = code;
        let offset = 0;
        while ((match = commentRegex.exec(codeSoFar))) {
            const before = codeSoFar.slice(0, match.index);
            const lineStart = before.split('\n').length;
            const commentText = match[1].replace(/\n/g, ' ').trim().slice(0, 20) + (match[1].length > 20 ? '…' : '');
            outline.push({
                name: '/* ' + commentText + ' */',
                type: 'comment',
                line: lineStart
            });
        }
        // Сортируем outline по line, чтобы порядок был последовательным
        outline.sort((a, b) => a.line - b.line);
        return outline;
    },

    parseJS(code) {
        const lines = code.split('\n');
        const outline = [];
        let currentClass = null;
        let classBraceCount = 0;
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            let m = line.match(/^\s*class\s+([a-zA-Z0-9_]+)/);
            if (m) {
                currentClass = { name: m[1], type: 'class', line: i + 1, children: [] };
                classBraceCount = 0;
                outline.push(currentClass);
                continue;
            }
            if (currentClass !== null) {
                classBraceCount += (line.match(/{/g) || []).length;
                classBraceCount -= (line.match(/}/g) || []).length;
                m = line.match(/^\s*([a-zA-Z0-9_]+)\s*\([^)]*\)\s*\{/);
                if (m) {
                    currentClass.children.push({ name: m[1] + '()', type: 'method', line: i + 1 });
                }
                if (classBraceCount <= 0) {
                    currentClass = null;
                    classBraceCount = 0;
                }
                continue;
            }
            m = line.match(/^\s*function\s+([a-zA-Z0-9_]+)\s*\(/);
            if (m) {
                outline.push({ name: m[1] + '()', type: 'function', line: i + 1 });
            }
        }
        return outline;
    },

    render(outline) {
        const $div = $(this.container);
        if (!outline.length) {
            $div.html('<em>Нет структуры для навигации</em>');
            return;
        }
        let html = '<ul class="outline-list">';
        for (const item of outline) {
            if (item.type === 'class') {
                html += `<li class="outline-class"><span data-line="${item.line}" class="outline-link"><span class="mdi mdi-alpha-c-circle"></span> ${item.name}</span>`;
                if (item.children && item.children.length) {
                    html += '<ul class="outline-methods">';
                    for (const method of item.children) {
                        html += `<li class="outline-method"><span data-line="${method.line}" class="outline-link"><span class="mdi mdi-function"></span> ${method.name}</span></li>`;
                    }
                    html += '</ul>';
                }
                html += '</li>';
            } else if (item.type === 'method') {
                html += `<li class="outline-method"><span data-line="${item.line}" class="outline-link"><span class="mdi mdi-function"></span> ${item.name}</span></li>`;
            } else if (item.type === 'function') {
                html += `<li class="outline-function"><span data-line="${item.line}" class="outline-link"><span class="mdi mdi-function"></span> ${item.name}</span></li>`;
            } else if (item.type === 'selector') {
                html += `<li class="outline-selector"><span data-line="${item.line}" class="outline-link"><span class="mdi mdi-pound-box-outline"></span> ${item.name}</span></li>`;
            } else if (item.type === 'comment') {
                html += `<li class="outline-comment"><span data-line="${item.line}" class="outline-link"><span class="mdi mdi-comment-outline"></span> ${item.name}</span></li>`;
            }
        }
        html += '</ul>';
        $div.html(html);

        $div.find('.outline-link').off('click').on('click', function () {
            const line = Number($(this).data('line'));
            OutlineManager.editor.gotoLine(line, 0, true);
            OutlineManager.editor.scrollToLine(line - 1, false, true, null);
            OutlineManager._highlightActive(OutlineManager._getActiveLines(line));
        });
    },

    // Для выделения класса, метода, комментария
    _getActiveLines(cursorOverride) {
        if (!this.editor) return [];
        const cursor = (cursorOverride || (this.editor.getCursorPosition().row + 1));
        let bestClass = null, bestMethod = null, bestComment = null;
        const walk = (list) => {
            for (const item of list) {
                if (item.type === 'class' && item.line <= cursor) {
                    bestClass = item;
                    if (item.children) {
                        for (const m of item.children) {
                            if (m.line <= cursor) bestMethod = m;
                        }
                    }
                } else if ((item.type === 'function' || item.type === 'method') && item.line <= cursor) {
                    bestMethod = item;
                } else if (item.type === 'comment' && item.line <= cursor) {
                    bestComment = item;
                }
            }
        };
        walk(this.outline);
        // Выделяем и комментарий, и текущий метод/класс
        let result = [];
        if (bestClass)   result.push(bestClass.line);
        if (bestMethod)  result.push(bestMethod.line);
        if (bestComment) result.push(bestComment.line);
        // Уникализируем
        return Array.from(new Set(result));
    },

    _highlightActive(lines) {
        const $links = $(this.container).find('.outline-link');
        $links.removeClass('outline-active');
        if (!lines) return;
        if (!Array.isArray(lines)) lines = [lines];
        lines.forEach(line => {
            $links.filter(`[data-line="${line}"]`).addClass('outline-active');
        });
    },

    _bindAceEvents() {
        if (!this.editor) return;
        if (this._bindedEditor === this.editor) return;
        this._bindedEditor = this.editor;
        this.editor.session.on('change', () => this.update());
        this.editor.selection.on('changeCursor', () => {
            this._highlightActive(this._getActiveLines());
        });
    }
};


// ---------- EditorManager ----------

const EditorManager = {
    loadFileToEditor(url, file, toid, ti) {
        ProgressBar.show('Загрузка файла: ' + file);
        TabsManager.tabsarray[ti].editor = null;
        AjaxManager.request({
            type: 'GET',
            url: url,
            dataType: 'json',
            progressText: 'Загрузка файла: ' + file,
            success: function(json) {
                if (json.error) {
                    Modal.alert(json.error);
                    ProgressBar.hide();
                    TabsManager.closeTab(ti, true);
                    return;
                }
                const el = $('#' + toid);
                el.empty();
                const editor = ace.edit(toid);
                editor.setTheme("ace/theme/monokai");
                const modelist = ace.require("ace/ext/modelist");
                const mode = modelist.getModeForPath(file);
                editor.session.setMode(mode.mode);
                editor.session.setTabSize(4);
                editor.session.setUseWrapMode(true);
                editor.setShowPrintMargin(false);
                editor.setValue(json.data || '', -1);
                editor.focus();
                TabsManager.tabsarray[ti].editor = editor;
                editor.on('change', function() {
                    TabsManager.markUnsaved(ti, file);
                });
                Logger.add('Загружен файл: ' + file);

                // Outline только для поддерживаемых расширений
                const supported = ['php', 'css', 'js'];
                const ext = (file.split('.').pop() || '').toLowerCase();
                if (supported.includes(ext)) {
                    OutlineManager.init(editor, '#outline_panel');
                } else {
                    OutlineManager.clear('#outline_panel');
                }
            }
        });
    },

    saveFile(ti = null, options = null) {
        if (!ti) ti = TabsManager.global_ti;
        if (!TabsManager.tabsarray[ti] || !TabsManager.tabsarray[ti].editor) {
            if (typeof options === 'function') options(false);
            else if (options && typeof options.success === 'function') options.success({ok: false});
            return;
        }
        ProgressBar.show('Сохранение файла...');
        const file = TabsManager.tabsarray[ti].file;
        const dataform = { 'finput': TabsManager.tabsarray[ti].editor.getValue() };
        AjaxManager.request({
            type: 'POST',
            url: 'index.php?ajax=1&act=savefile&f=' + encodeURIComponent(file),
            data: dataform,
            dataType: 'json',
            progressText: 'Сохранение файла...',
            success: function(json) {
                if (json && json.error) {
                    Modal.alert(json.error);
                    if (typeof options === 'function') options(false, json);
                    else if (options && typeof options.success === 'function') options.success(json);
                    return;
                }
                TabsManager.unmarkUnsaved(ti, file);
                Logger.add('Сохранён файл: ' + file);
                if (typeof options === 'function') options(true, json);
                else if (options && typeof options.success === 'function') options.success(json);
            }
        });
    }
};


// ------ Интеграция с TabsManager (outline для активного editor) --------

(function() {
    const origSelectTab = TabsManager.selectTab;
    TabsManager.selectTab = function(ti) {
        origSelectTab.call(TabsManager, ti);
        const tab = TabsManager.tabsarray[ti];
        if (tab && tab.editor) {
            const file = tab.file || '';
            const ext = (file.split('.').pop() || '').toLowerCase();
            if (['php', 'css', 'js'].includes(ext)) {
                OutlineManager.init(tab.editor, '#outline_panel');
            } else {
                OutlineManager.clear('#outline_panel');
            }
        } else {
            OutlineManager.clear('#outline_panel');
        }
    };
})();

if (typeof DirectoryHandler !== 'undefined') {
    const origDirHandle = DirectoryHandler.handle;
    DirectoryHandler.handle = function(dpath) {
        OutlineManager.clear('#outline_panel');
        return origDirHandle.apply(this, arguments);
    };
}
