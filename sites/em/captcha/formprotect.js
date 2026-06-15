 
 
window.YM_COUNTER_ID = '18713149';


/*!
 * formprotect.js — simple-captcha only (2025.07.29 + diagnostics v3 + captcha fix + YM goals)
 * Интеграция с Яндекс.Метрикой: автоматические цели на основе data-fp-id
 */
(function ($) {
    'use strict';

    // === ГЛОБАЛЬНЫЕ НАСТРОЙКИ ПО УМОЛЧАНИЮ ===
    var defaults = {
        formClass: 'formprotect',
        captchaContainerClass: 'fp-captcha-box',
        fieldErrorClass: 'fp-field-error',
        fieldErrorMessageClass: 'fp-field-error-message',
        generalErrorContainerClass: 'fp-general-errors',
        successMessageClass: 'fp-success-message',
        processingClass: 'fp-form-processing',
        preventDoubleSubmit: true,
        submittingButtonText: 'Отправка...',
        debug: true,
        // === НОВАЯ ОПЦИЯ: ВКЛЮЧЕНИЕ/ОТКЛЮЧЕНИЕ ЯНДЕКС.МЕТРИКИ ===
        yandexMetrika: {
            enabled: true,                   // Включить/выключить интеграцию с ЯМ
            counterId: window.YM_COUNTER_ID || null, // ID счётчика (берётся из глобальной переменной)
            goalPrefix: 'fp_'               // Префикс для автоматических целей
        }
    };

    // === ВСПОМОГАТЕЛЬНАЯ ФУНКЦИЯ ДЛЯ DEBUG-ЛОГА ===
    function debugLog() {
        if (defaults.debug) {
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[FP-DEBUG]');
            console.log.apply(console, args);
        }
    }

    // === НОВАЯ ФУНКЦИЯ: ОТПРАВКА ЦЕЛЕЙ В ЯНДЕКС.МЕТРИКУ ===
    /**
     * Отправляет цель в Яндекс.Метрику, если интеграция включена
     * @param {jQuery} $form - jQuery объект формы
     * @param {string} goalSuffix - Суффикс цели (_start, _success, _fail)
     * @param {Object} [params] - Дополнительные параметры для передачи в цель
     */
    function sendYmGoal($form, goalSuffix, params) {
        // Проверяем, включена ли интеграция с ЯМ
        if (!defaults.yandexMetrika.enabled) {
            debugLog('[FP-YM] Интеграция с Яндекс.Метрикой отключена');
            return;
        }

        // Проверяем наличие объекта ym и ID счётчика
        if (typeof ym === 'undefined') {
            debugLog('[FP-YM] Объект ym не найден. Убедитесь, что код Яндекс.Метрики загружен.');
            return;
        }

        var counterId = defaults.yandexMetrika.counterId;
        if (!counterId) {
            debugLog('[FP-YM] ID счётчика Яндекс.Метрики не задан. Установите window.YM_COUNTER_ID или передайте в настройках.');
            return;
        }

        // Получаем ID формы для формирования имени цели
        var formId = $form.attr('data-fp-id') || $form.attr('id') || 'default_form';
        // Формируем полное имя цели: префикс + ID формы + суффикс
        // Например: fp_form3_start, fp_form3_success, fp_form3_fail
        var fullGoalName = defaults.yandexMetrika.goalPrefix + formId + goalSuffix;

        try {
            // Отправляем цель с параметрами, если они переданы
            if (params) {
                ym(counterId, 'reachGoal', fullGoalName, params);
            } else {
                ym(counterId, 'reachGoal', fullGoalName);
            }
            debugLog('[FP-YM] Цель отправлена:', fullGoalName, 'ID счётчика:', counterId, params ? 'Параметры:' + JSON.stringify(params) : '');
        } catch (e) {
            console.error('[FP-YM] Ошибка отправки цели:', fullGoalName, e);
        }
    }

    // === МЕНЕДЖЕР SIMPLE-КАПЧИ ===
    var CaptchaManager = {
        states: {},
        show: function ($form, showLoading, callback) {
            var formId = $form.attr('data-fp-id') || $form.attr('id') || 'default';
            if (!this.states[formId]) this.states[formId] = {};
            debugLog('CaptchaManager.show', { formId: formId, showLoading: showLoading });
            var $container = $form.find('.' + defaults.captchaContainerClass);
            $container.show();
            $form.find('input[name="_fp_captcha"]').remove();
            $container.html('');
            if (showLoading) {
                $container.html('<div class="fp-captcha-loading">Загрузка капчи...</div>');
            }
            debugLog('Показываем simple-капчу');
            var img = new Image();
            var imgSrc = '/captcha/image.php?form=' + encodeURIComponent(formId) + '&_=' + Date.now();
            img.onload = function () {
                debugLog('Изображение simple-капчи загружено');
                var html =
                    `<div class="fp-captcha-img-wrapper">
    <img src="${imgSrc}" class="fp-captcha-img" alt="CAPTCHA">
    <button type="button" class="fp-captcha-refresh">↻</button>
</div>
<div class="fp-captcha-input-wrapper" style="margin: 8px 0;">
    <input type="text" class="fp-captcha-input" autocomplete="off" placeholder="Введите код с картинки" maxlength="3"> 
</div>`;
                $container.html(html);
                $form.find('.fp-captcha-refresh').off('click').on('click', function (e) {
                    e.preventDefault();
                    $container.html('<div class="fp-captcha-loading">Загрузка капчи...</div>');
                    setTimeout(function () {
                        reloadSimpleCaptcha($form, $container, formId, callback);
                    }, 100);
                });
                $form.find('.fp-captcha-input').focus();
                if (typeof callback === 'function') callback();
            };
            img.onerror = function () {
                debugLog('Ошибка загрузки изображения simple-капчи');
                $container.html('<div class="fp-captcha-error">Ошибка загрузки изображения</div>');
                if (typeof callback === 'function') callback();
            };
            img.src = imgSrc;
        },
        reset: function ($form) {
            var formId = $form.attr('data-fp-id') || $form.attr('id') || 'default';
            debugLog('CaptchaManager.reset', formId);
            this.states[formId] = { passed: false, lastToken: null };
            var $container = $form.find('.' + defaults.captchaContainerClass);
            $container.html('').hide();
            $form.find('input[name="_fp_captcha"]').remove();
        }
    };

    // === ВСПОМОГАТЕЛЬНАЯ ФУНКЦИЯ ДЛЯ ОБНОВЛЕНИЯ SIMPLE-КАПЧИ ===
    function reloadSimpleCaptcha($form, $container, formId, callback) {
        var img = new Image();
        var imgSrc = '/captcha/image.php?form=' + encodeURIComponent(formId) + '&_=' + Date.now();
        img.onload = function () {
            var html =
                `<div class="fp-captcha-img-wrapper">
    <img src="${imgSrc}" class="fp-captcha-img" alt="CAPTCHA">
    <button type="button" class="fp-captcha-refresh">↻</button>
</div>
<div class="fp-captcha-input-wrapper" style="margin: 8px 0;">
    <input type="text" class="fp-captcha-input" autocomplete="off" placeholder="Введите код с картинки" maxlength="3"> 
</div>`;
            $container.html(html);
            $form.find('.fp-captcha-refresh').off('click').on('click', function (e) {
                e.preventDefault();
                $container.html('<div class="fp-captcha-loading">Загрузка капчи...</div>');
                setTimeout(function () {
                    reloadSimpleCaptcha($form, $container, formId, callback);
                }, 100);
            });
            $form.find('.fp-captcha-input').focus();
            if (typeof callback === 'function') callback();
        };
        img.onerror = function () {
            $container.html('<div class="fp-captcha-error">Ошибка загрузки изображения</div>');
            if (typeof callback === 'function') callback();
        };
        img.src = imgSrc;
    }

    // === ПЛАГИН ЗАЩИТЫ ФОРМЫ ===
    $.fn.fpFormProtect = function (options) {
        // Объединяем настройки по умолчанию с пользовательскими
        var settings = $.extend(true, {}, defaults, options);
        return this.each(function () {
            var $form = $(this);
            if ($form.data('fpFormProtectInitialized')) return;
            $form.data('fpFormProtectInitialized', true);

            // --- АВТОДОБАВЛЕНИЕ КОНТЕЙНЕРОВ ---
            if ($form.find('.' + settings.captchaContainerClass).length === 0) {
                $form.append('<div class="fw-captcha-container ' + settings.captchaContainerClass + '"></div>');
                debugLog('Автодобавлен контейнер для капчи');
            }
            if ($form.find('.' + settings.generalErrorContainerClass).length === 0) {
                $form.append('<div class="' + settings.generalErrorContainerClass + '"></div>');
                debugLog('Автодобавлен контейнер для ошибок');
            }
            if ($form.find('.' + settings.successMessageClass).length === 0) {
                $form.append('<div class="' + settings.successMessageClass + '" style="display:none;"></div>');
                debugLog('Автодобавлен контейнер для успеха');
            }
            debugLog('Инициализация формы', $form.attr('data-fp-id') || $form.attr('id'));

            // Скрываем сообщение об ошибках, если исправлены все поля с ошибкой
            $form.on('input change', 'input,textarea,select', function () {
                clearFieldError($(this), settings);
                if ($form.find('.' + settings.fieldErrorClass).length === 0) {
                    $form.find('.' + settings.generalErrorContainerClass).hide();
                }
                if ($(this).closest('.' + settings.captchaContainerClass).length) return;
                CaptchaManager.reset($form);
            });

            // === САБМИТ ФОРМЫ ===
            $form.on('submit', function (e) {
                e.preventDefault();
                debugLog('Сабмит формы');

                // === НОВАЯ ЛОГИКА: ОТПРАВКА ЦЕЛИ "_start" ===
                // Отправляем цель при начале отправки формы
                sendYmGoal($form, '_start');
                // === КОНЕЦ НОВОЙ ЛОГИКИ ===

                var $container = $form.find('.' + settings.captchaContainerClass);
                var $input = $form.find('.fp-captcha-input');

                // Проверка на встроенную капчу
                if ($container.is(':visible') && $input.length && !$input.val().trim()) {
                    debugLog('Simple-капча показана, но код не введён');
                    showGeneralError($form, 'Введите код с картинки!', settings);
                    return false;
                }

                sendAjaxForm($form, settings);
            });
            CaptchaManager.reset($form);
        });
    };

    // === AJAX ОТПРАВКА ===
    function sendAjaxForm($form, settings) {
        debugLog('Отправка формы на сервер');
        var $btn = $form.find('button[type="submit"]');
        var origText = $btn.text();
        $btn.prop('disabled', true).text(settings.submittingButtonText);
        $form.addClass(settings.processingClass);

        // === КЛЮЧЕВОЕ ИЗМЕНЕНИЕ ===
        // Перед отправкой всегда добавляем значение из поля .fp-captcha-input 
        var $captchaInput = $form.find('.fp-captcha-input');
        $form.find('input[name="_fp_captcha"]').remove();
        if ($captchaInput.length) {
            var captchaValue = $captchaInput.val().trim();
            $('<input>').attr({
                type: 'hidden',
                name: '_fp_captcha',
                value: captchaValue
            }).appendTo($form);
            debugLog('Добавлено временное поле _fp_captcha со значением:', captchaValue);
        }
        // === КОНЕЦ ИЗМЕНЕНИЯ ===

        var formData = new FormData($form[0]);
        formData.append('_fp_js', '1');

        $.ajax({
            url: $form.attr('action') || window.location.href,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                debugLog('Ответ сервера:', response);

                if (response.force_captcha) {
                    // === НОВАЯ ЛОГИКА: ОТПРАВКА ЦЕЛИ "_fail" ПРИ НЕОБХОДИМОСТИ КАПЧИ ===
                    // Отправляем цель при необходимости показа капчи (часто это ошибка валидации)
                    sendYmGoal($form, '_fail', { reason: 'captcha_required' });
                    // === КОНЕЦ НОВОЙ ЛОГИКИ ===

                    CaptchaManager.reset($form);
                    showGeneralError($form, response.message || 'Подтвердите, что вы не робот. Введите код с картинки ниже.', settings);
                    CaptchaManager.show($form, true, function () {
                        var $input = $form.find('.fp-captcha-input');
                        if (response.message && /неверно|не верно/i.test(response.message)) {
                            if ($input.length) {
                                $input.val('');
                                debugLog('Поле ввода капчи очищено из-за ошибки "Неверно"');
                            }
                        }
                        if ($input.length) $input.focus();
                    });
                    return;
                }

                if (response.validation_failed_early) {
                    debugLog('Ошибка валидации полей:', response.errors);

                    // === НОВАЯ ЛОГИКА: ОТПРАВКА ЦЕЛИ "_fail" ПРИ ОШИБКЕ ВАЛИДАЦИИ ===
                    // Отправляем цель при ошибке валидации на стороне сервера
                    sendYmGoal($form, '_fail', { reason: 'validation_error' });
                    // === КОНЕЦ НОВОЙ ЛОГИКИ ===

                    if (response.errors) showFieldErrors($form, response.errors, settings);
                    if (response.message) showGeneralError($form, response.message, settings);
                    CaptchaManager.reset($form);
                    return;
                }

                if (response.success) {
                    debugLog('Успешная отправка формы');

                    // === НОВАЯ ЛОГИКА: ОТПРАВКА ЦЕЛИ "_success" ===
                    // Отправляем цель при успешной отправке формы
                    sendYmGoal($form, '_success');
                    // === КОНЕЦ НОВОЙ ЛОГИКИ ===

                    $form[0].reset();
                    showSuccess($form, response.message || 'Форма отправлена!', settings);
                    CaptchaManager.reset($form);
                    return;
                }

                // Обработка других ошибок сервера
                debugLog('Ошибка сервера:', response.message || response.errors);

                // === НОВАЯ ЛОГИКА: ОТПРАВКА ЦЕЛИ "_fail" ПРИ ДРУГИХ ОШИБКАХ ===
                // Отправляем цель при других ошибках сервера
                sendYmGoal($form, '_fail', { reason: 'server_error' });
                // === КОНЕЦ НОВОЙ ЛОГИКИ ===

                if (response.message && response.message.includes('Неверно')) {
                    debugLog('Ошибка simple-капчи, перезагружаем');
                    CaptchaManager.reset($form);
                    showGeneralError($form, 'Загрузка капчи...', settings);
                    CaptchaManager.show($form, true, function () {
                        showGeneralError($form, response.message, settings);
                        var $input = $form.find('.fp-captcha-input');
                        if ($input.length) {
                            $input.val('').focus();
                            debugLog('Поле ввода капчи очищено и получило фокус после перезагрузки');
                        }
                    });
                } else {
                    debugLog('Ошибка сервера:', response.message || response.errors);
                    if (response.errors) showFieldErrors($form, response.errors, settings);
                    if (response.message) showGeneralError($form, response.message, settings);
                    CaptchaManager.reset($form);
                }
            },
            error: function (jqXHR, status) {
                debugLog('AJAX ошибка', status);

                // === НОВАЯ ЛОГИКА: ОТПРАВКА ЦЕЛИ "_fail" ПРИ AJAX ОШИБКЕ ===
                // Отправляем цель при сетевой ошибке или ошибке HTTP
                sendYmGoal($form, '_fail', { reason: 'ajax_error', status: status });
                // === КОНЕЦ НОВОЙ ЛОГИКИ ===

                showGeneralError($form, 'Ошибка отправки формы: ' + status, settings);
                CaptchaManager.reset($form);
            },
            complete: function () {
                $btn.prop('disabled', false).text(origText);
                $form.removeClass(settings.processingClass);
                $form.find('input[name="_fp_captcha"]').remove();
                debugLog('Временное поле _fp_captcha удалено после завершения запроса');
            }
        });
    }

    // === UI ФУНКЦИИ ===
    function showSuccess($form, msg, settings) {
        $form.find('.' + settings.successMessageClass).hide().text('');
        var $el = $form.find('.' + settings.successMessageClass);
        $form.find('.' + settings.generalErrorContainerClass).hide();
        $form.find('.' + settings.fieldErrorClass).removeClass(settings.fieldErrorClass);
        $form.find('.' + settings.fieldErrorMessageClass).remove();
        $el.text(msg).fadeIn();
    }

    function showGeneralError($form, msg, settings) {
        $form.find('.' + settings.successMessageClass).hide();
        var $el = $form.find('.' + settings.generalErrorContainerClass);
        if (!$el.length) $el = $('<div>').addClass(settings.generalErrorContainerClass).appendTo($form);
        $el.text(msg).show();
    }

    function showFieldErrors($form, errors, settings) {
        debugLog('showFieldErrors:', errors);
        $.each(errors, function (field, msg) {
            var fieldName = field.replace(/\./g, '\\.');
            var $input = $form.find('[name="' + fieldName + '"],[name="' + fieldName + '[]"]');
            $input.addClass(settings.fieldErrorClass);
            var $error = $input.next('.' + settings.fieldErrorMessageClass);
            if (!$error.length) {
                $error = $('<div>').addClass(settings.fieldErrorMessageClass).insertAfter($input);
            }
            $error.text(msg).show();
        });
    }

    function clearFieldError($input, settings) {
        $input.removeClass(settings.fieldErrorClass);
        $input.next('.' + settings.fieldErrorMessageClass).remove();
    }

    // Автоинициализация всех форм с нужным классом
    $(function () {
        debugLog('Автоинициализация форм с классом', defaults.formClass);
        $('.' + defaults.formClass).fpFormProtect();
    });
})(jQuery);