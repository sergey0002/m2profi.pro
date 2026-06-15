// ---------- FileTree, DirectoryHandler, FileHandler для FWE ----------

// ---------- FileTree ----------

const FileTree = {
    init() {
        $('#filesajaxtree').jstree({
            core: {
                data: {
                    url: "index.php?act=jsoonajaxftree&ajax=1",
                    data: function(node) { return { id: node.id }; },
                    dataType: "json"
                },
                themes: {
                    name: "default-dark",
                    dots: true, icons: true, responsive: false,
                    variant: 'medium', stripes: true
                }
            },
            sort: function(a, b) {
                return this.get_type(a) === this.get_type(b)
                    ? (this.get_text(a) > this.get_text(b) ? 1 : -1)
                    : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
            },
            types: {
                default: { icon: 'folder' },
                file: { valid_children: [], icon: 'file' }
            },
            unique: {
                duplicate: function(name, counter) { return name + ' ' + counter; }
            },
            plugins: ['state', 'dnd', 'sort', 'types', 'unique', 'wholerow']
        });

        // Обработка выбора узла дерева
        $('#filesajaxtree').on('select_node.jstree', function(e, data) {
            const dtype = data.node.a_attr.dtype;
            const dpath = data.node.a_attr.dpath;
            const caption = data.node.text.replace(/<[^>]+>/g, '');
            if (dtype === "file") {
                FileHandler.handle(dpath, caption);
            } else if (dtype === "dir") {
                DirectoryHandler.handle(dpath);
            }
            return false;
        });
    },
    lightFile(file) { $(`a[dpath="${file}"]`).addClass('opened_file'); },
    unlightFile(file) { $(`a[dpath="${file}"]`).removeClass('opened_file opened_file_unsave'); }
};

// ---------- DirectoryHandler КЛИК ПО ПАПКЕ ----------

const DirectoryHandler = {
    handle(dpath) {
        // Открываем директорию как вкладку
        const ti = TabsManager.addDirTab(dpath, dpath.split('/').pop() || '/');
        if (ti) {
            ProgressBar.show('Загрузка содержимого директории...');
            AjaxManager.request({
                type: 'GET',
                url: 'index.php?ajax=1&act=dirinfo&d=' + encodeURIComponent(dpath),
                dataType: 'json',
                success: function(json) {
                    ProgressBar.hide();
                    if (json.error) Modal.alert(json.error);
                    else {
                        Logger.add('Открыта директория: ' + dpath);
                        showDirListingInTab(json.files, dpath, ti);
                    }
                }
            });
        }
    }
};

// ---------- FileHandler КЛИК ПО ФАЙЛУ ----------

const FileHandler = {
    handle(fpath, caption) {
        const url = 'index.php?ajax=1&act=loadfile&f=' + encodeURIComponent(fpath);
        const ti = TabsManager.addTab(fpath, caption);
        if (ti) {
            EditorManager.loadFileToEditor(url, fpath, 'fwe_bi_dataf_' + ti, ti);
            Logger.add('Открыт файл: ' + fpath);
        }
    }
};

// ---------- Рендер содержимого директории во вкладке ----------

function showDirListingInTab(files, dirPath, ti) {
    let html = `<div class="dir-listing"><h5>Папка: /${dirPath}</h5><ul style="list-style:none;padding-left:0">`;
    if (!files.length) {
        html += '<li><em>Пусто</em></li>';
    } else {
        files.forEach(f => {
            if (f.type === 'dir') {
                html += `<li>
                    <span class="mdi mdi-folder-outline"></span>
                    <a href="#" class="open-dir-in-tab" data-path="${f.path}">${f.name}</a>
                </li>`;
            } else {
                html += `<li>
                    <span class="mdi mdi-file-outline"></span>
                    <a href="#" class="open-file-in-tab" data-path="${f.path}">${f.name}</a>
                </li>`;
            }
        });
    }
    html += '</ul></div>';
    $('#fwe_dir_listing_' + ti).html(html);
}

// ---------- Делегированные обработчики внутри вкладок-директорий ----------

// Переход в подпапку
$(document).on('click', '.open-dir-in-tab', function(e) {
    e.preventDefault();
    const dpath = $(this).data('path');
    DirectoryHandler.handle(dpath);
});

// Открытие файла из вкладки-папки
$(document).on('click', '.open-file-in-tab', function(e) {
    e.preventDefault();
    const fpath = $(this).data('path');
    FileHandler.handle(fpath, fpath.split('/').pop());
});








// Для переименования файла 

function updateTreeAfterRename(oldPath, newPath, newName) {
  const tree = $('#filesajaxtree').jstree(true);
  const node = tree.get_node(oldPath);
  if (node) {
    // Обновить текст узла
    tree.rename_node(node, `<span class="fwe_jstree_fn">${escapeHtml(newName)}</span>`);
    // Обновить id и dpath (path/id обязаны совпадать с данными из backend)
    tree.set_id(node, newPath);
    // Также обновить атрибуты
    tree.get_node(newPath).a_attr.dpath = newPath;
    // Если нужно обновить data (например, для поиска/открытия) — тоже здесь
  } else {
    // Если не нашли узел — можно перерисовать родительскую папку:
    // Получаем родителя:
    const parentPath = oldPath.split('/').slice(0, -1).join('/') || '#';
    tree.refresh_node(parentPath);
  }
}
