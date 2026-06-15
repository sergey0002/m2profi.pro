// === FWE Online Editor — script.js (Refactored, Modular Style) ===

// ---------- ProgressBar (Глобальный, для всех AJAX) ----------
const ProgressBar = {
    show(text = 'Загрузка...', percent = 35) {
        $('#progress-bar-overlay').show();
        $('#progress-bar-text').text(text);
        $('#progress-bar-fill').css('width', percent + '%');
    },
    update(percent) {
        $('#progress-bar-fill').css('width', percent + '%');
    },
    hide() {
        $('#progress-bar-overlay').fadeOut(150);
    }
};

// ---------- Modal/Dialogs ----------
const Modal = {
    confirm(msg, yes, no) {
        // Можно заменить на кастомный modal
        if (confirm(msg)) yes && yes();
        else no && no();
    },
    alert(msg) {
        alert(msg);
    }
};

// ---------- Logger ----------
const Logger = {
    add(text) {
        const now = new Date();
        const time = [now.getHours(), now.getMinutes(), now.getSeconds()].map(v => String(v).padStart(2, '0')).join(':');
        $('#editorstatus').prepend(`<div>${time} — ${text}</div>`);
    }
};

 

// ---------- AjaxManager (единая точка для всех AJAX) ----------
const AjaxManager = {
    request(opts) {
        ProgressBar.show(opts.progressText || 'Загрузка...');
        return $.ajax({
            ...opts,
            complete: (xhr, status) => {
                ProgressBar.hide();
                if (typeof opts.complete === 'function') opts.complete(xhr, status);
            },
            error: (xhr, status, error) => {
                ProgressBar.hide();
                // Получаем URL (он может быть как строка, так и функция)
                let url = (typeof opts.url === 'function') ? opts.url() : opts.url;
                let msg = `Ошибка связи с сервером!\n\nURL: ${url}`;
                if (opts.noRetry) {
                    Modal.alert(msg);
                } else {
                    Modal.confirm(
                        msg + "\n\nПопробовать ещё раз?",
                        () => AjaxManager.request(opts)
                    );
                }
            }
        });
    }
};






// ---------- Быстрое сохранение по Ctrl+S ----------
$(document).on('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        EditorManager.saveFile();
    }
});


// ---------- Инициализация интерфейса ----------
$(function() {
    ProgressBar.hide();
    FileTree.init();
    TabsManager.restoreTabsFromState();
    $("#fwe_save").click(function(e) {
        e.preventDefault();
        EditorManager.saveFile();
    });
    $(window).resize(resizeEditors);
    $('.fwe_tabbody').hide();
    if (TabsManager.global_ti) TabsManager.selectTab(TabsManager.global_ti);
});

// ---------- Защита от выхода с несохранёнными вкладками ----------
window.onbeforeunload = function() {
    if ($('.opened_file_unsave').length) return "Внесённые изменения не были сохранены!";
    return undefined;
};
