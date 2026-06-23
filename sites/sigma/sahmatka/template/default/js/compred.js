(function ($) {
    'use strict';

    var ajaxBase = '/sahmatka/ajax_router.php?ctr=compred&act=';
    var editBase = '/sahmatka/ctrind.php?ctr=compred&act=edit&id=';
    var listUrl = '/sahmatka/ctrind.php?ctr=compred&act=index';
    var commentTimers = {};

    function cpObjId($el) {
        return parseInt($el.attr('data-compred-obj-id'), 10) || 0;
    }

    function cpPost(act, data) {
        return $.ajax({
            url: ajaxBase + act,
            method: 'POST',
            data: data,
            dataType: 'json'
        });
    }

    function cpFailAlert(xhr) {
        var msg = 'Ошибка сети';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
            msg = xhr.responseJSON.error;
        } else if (xhr && xhr.responseText) {
            var text = $.trim(xhr.responseText);
            if (text && text.charAt(0) === '{') {
                try {
                    var parsed = JSON.parse(text);
                    if (parsed && parsed.error) {
                        msg = parsed.error;
                    }
                } catch (e) {}
            }
        }
        alert(msg);
    }

    window.cpInitBlock = function () {
        var $select = $('#cp-select-proposal');
        if (!$select.length) {
            return;
        }

        var $captionWrap = $('#cp-new-caption-wrap');
        var $caption = $('#cp-caption-new');
        var $viewLink = $('#cp-view-link');

        function cpApplyBlockMode() {
            var id = $select.val();
            if (id) {
                $captionWrap.hide();
                $caption.prop('disabled', true).removeAttr('required');
                $viewLink.attr('href', editBase + id).show();
            } else {
                $captionWrap.show();
                $caption.prop('disabled', false).attr('required', 'required');
                $viewLink.hide();
            }
        }

        $select.on('change', cpApplyBlockMode);

        $('#cp-add-form').on('submit', function (e) {
            if (!$select.val() && !$.trim($caption.val())) {
                e.preventDefault();
                alert('Укажите название нового предложения или выберите существующее.');
                $caption.focus();
                return false;
            }
        });

        cpApplyBlockMode();
    };

    function cpCopyLink() {
        var $input = $('#cp-permalink-result');
        if (!$input.length) {
            return;
        }
        $input.trigger('select');
        var value = $input.val();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value);
        } else {
            document.execCommand('copy');
        }
    }

    function cpInitShareControls() {
        $('#cp-copy-link').on('click', cpCopyLink);

        $('#cp-share-toggle').on('click', function () {
            var $menu = $('#cp-share-menu');
            if (!$menu.length) {
                return;
            }
            var open = !$menu.is(':visible');
            $menu.prop('hidden', !open);
            $(this).attr('aria-expanded', open ? 'true' : 'false');
        });

        $(document).on('click', function (e) {
            var $menu = $('#cp-share-menu');
            var $toggle = $('#cp-share-toggle');
            if (!$menu.length || !$toggle.length || $menu.prop('hidden')) {
                return;
            }
            if (!$(e.target).closest('#cp-share-menu, #cp-share-toggle').length) {
                $menu.prop('hidden', true);
                $toggle.attr('aria-expanded', 'false');
            }
        });
    }

    window.cpInitPublicShare = function () {
        cpInitShareControls();
    };

    window.cpInitEdit = function (compredId) {
        if (!compredId) {
            return;
        }

        cpInitShareControls();

        $('#cp-save-details').on('click', function () {
            var $btn = $(this);
            var caption = $.trim($('#cp-edit-caption').val());
            if (!caption) {
                alert('Укажите название');
                return;
            }
            $btn.prop('disabled', true);
            cpPost('save_details', {
                compred_id: compredId,
                caption: caption,
                intro_text: $('#cp-edit-intro').val()
            }).done(function (res) {
                if (res && res.ok) {
                    $('#cp-main-title').text(res.caption);
                    $('#cp-details-status').show().delay(1500).fadeOut();
                } else {
                    alert((res && res.error) ? res.error : 'Ошибка сохранения');
                }
            }).fail(cpFailAlert).always(function () {
                $btn.prop('disabled', false);
            });
        });

        $('#cp-delete-proposal').on('click', function () {
            if (!confirm('Удалить это предложение? Его нельзя будет восстановить.')) {
                return;
            }
            cpPost('del', { compred_id: compredId }).done(function (res) {
                if (res && res.ok) {
                    window.location.href = res.redirect || listUrl;
                } else {
                    alert((res && res.error) ? res.error : 'Не удалось удалить');
                }
            }).fail(cpFailAlert);
        });

        $(document).on('input', '.cp-note-input', function () {
            var $ta = $(this);
            var objId = cpObjId($ta);
            var $status = $ta.siblings('.cp-note-status');
            clearTimeout(commentTimers[objId]);
            commentTimers[objId] = setTimeout(function () {
                cpPost('save_comment', {
                    compred_obj_id: objId,
                    comment: $ta.val()
                }).done(function (res) {
                    if (res && res.ok) {
                        $status.show().delay(1500).fadeOut();
                    }
                }).fail(cpFailAlert);
            }, 600);
        });

        $(document).on('click', '.cp-remove-object', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var objId = cpObjId($btn);
            if (!objId) {
                alert('Не удалось определить объект');
                return;
            }
            if (!confirm((window.UNIT_LABEL && window.UNIT_LABEL.compred_delete_confirm) || 'Удалить из предложения?')) {
                return;
            }
            var $card = $btn.closest('.cp-card');
            cpPost('remove_item', { compred_obj_id: objId }).done(function (res) {
                if (res && res.ok) {
                    $card.fadeOut(function () {
                        $(this).remove();
                        var n = $('.cp-card--edit').length;
                        $('#cp-objects-count').text(n);
                        if (n === 0) {
                            window.location.reload();
                        }
                    });
                } else {
                    alert((res && res.error) ? res.error : 'Не удалось удалить');
                }
            }).fail(cpFailAlert);
        });
    };

    window.cpInitIndex = function () {
        $(document).on('click', '.cp-index-delete', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var id = parseInt($btn.attr('data-compred-id'), 10) || 0;
            if (!id) {
                return;
            }
            if (!confirm('Удалить предложение «' + $.trim($btn.attr('data-compred-caption')) + '»?')) {
                return;
            }
            cpPost('del', { compred_id: id }).done(function (res) {
                if (res && res.ok) {
                    $btn.closest('.cp-index-item').fadeOut(function () {
                        $(this).remove();
                        if ($('.cp-index-item').length === 0) {
                            window.location.reload();
                        }
                    });
                } else {
                    alert((res && res.error) ? res.error : 'Не удалось удалить');
                }
            }).fail(cpFailAlert);
        });
    };

})(jQuery);
