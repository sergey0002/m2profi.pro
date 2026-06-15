(function () {
    var wrgsv = {
        idBox: 'wrgsv',
        url_widget: 'https://em.m2profi.pro/sahmatka/display_home_public2.php  ',

        init: function (url, id) {
            if (!id) id = this.idBox;
            if (!url) url = this.url_widget;

            const el = document.getElementById(id);
            if (!el) {
                console.warn('Элемент с id="' + id + '" не найден');
                return;
            }

            console.log('Инициализация виджета...');

          this.ensureJQuery(() => {
                console.log('jQuery подключен.');
                this.ensurePopper(() => {
                    console.log('Popper.js подключен.');
                    this.ensureFancybox(() => {
                        console.log('Fancybox подключен.');
                        this.ensureSwiper(() => {
                            console.log('Swiper подключен.');
                            this.loadWidget(url, el);
                        });
                    });
                });
            });
        },

        loadWidget: function (url, container) {
            console.log('Загрузка виджета с URL:', url);

            const shadow = container.shadowRoot || container.attachShadow({ mode: 'open' });

            // Заменили fetch на $.ajax для загрузки данных
            $.ajax({
                url: url,
                method: 'GET',  // Используем GET запрос
                success: (html) => {
                    shadow.innerHTML = '';


					const style = document.createElement('link');
                    style.rel = 'stylesheet';
                    style.href = 'https://em.m2profi.pro/wiget_home2.css';
                    shadow.appendChild(style);
					
					
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    shadow.appendChild(wrapper);

                    // Отладка: Проверим, что данные корректно загружаются
                    console.log('Загруженные данные:', wrapper.innerHTML);

                    // Пытаемся вставить содержимое правильно
                    const swiperWrapper = shadow.querySelector('.swiper-wrapper');
                    const swiperSlides = wrapper.querySelectorAll('.sch-item');
                    if (swiperWrapper) {
                        swiperWrapper.innerHTML = ''; // Очищаем старые слайды
                        swiperSlides.forEach((slide) => {
                            const slideClone = slide.cloneNode(true);  // Клонируем слайды
                            swiperWrapper.appendChild(slideClone);   // Вставляем слайды
                        });
                    }



					this.initGlobalTooltipStyle();
                    this.initTooltip(wrapper);
                    this.initFancybox(wrapper);
					
					
					
                    this.initSwiper(wrapper);  // Инициализация Swiper после загрузки данных

                    document.dispatchEvent(
                        new CustomEvent('wiget_home_loaded', { detail: { id: container.id } })
                    );
                    console.log('Виджет успешно загружен и инициализирован.');
                },
                error: (err) => {
                    console.error('Ошибка загрузки виджета:', err);
                    container.style.display = 'none';
                }
            });
        },

        ensureJQuery: function (callback) {
            if (typeof window.jQuery === 'undefined') {
                const jqScript = document.createElement('script');
                jqScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                jqScript.onload = callback;
                document.head.appendChild(jqScript);
            } else {
                callback();
            }
        },
		ensurePopper: function (callback) {
            if (typeof window.Popper === 'undefined') {
                const popperScript = document.createElement('script');
                popperScript.src =
                    'https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.10.2/umd/popper.min.js';
                popperScript.onload = callback;
                document.head.appendChild(popperScript);
            } else {
                callback();
            }
        },
        ensureSwiper: function (callback) {
            if (typeof window.Swiper === 'undefined') {
                // Стили для Swiper
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
                document.head.appendChild(css);

                // Скрипт для Swiper
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
                script.onload = callback;
                document.head.appendChild(script);
            } else {
                callback();
            }
        },



























//////////////////
 

 ensureFancybox: function (callback) {
            const $ = window.jQuery;
            if (!$ || typeof $.fancybox === 'undefined') {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css';
                document.head.appendChild(css);

                const js = document.createElement('script');
                js.src = 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js';
                js.onload = callback;
                document.head.appendChild(js);
            } else {
                callback();
            } 
        },

        initGlobalTooltipStyle: function () {
            if (document.getElementById('wrgsv-global-tooltip-style')) return;

            const style = document.createElement('style');
            style.id = 'wrgsv-global-tooltip-style';
            style.textContent = `
                .wrgsv-global-tooltip {
                    position: absolute;
                    z-index: 9999;
                    background: #fff;
                   /* border: 1px solid #ccc; */
                    padding: 10px;
                    font-size: 14px;
                    line-height: 1.4;
                    max-width: 280px;
                    color: #333;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
					margin:0px;
                    visibility: hidden;
                    opacity: 0;
                    transition: opacity 0.3s ease, visibility 0.3s ease;
					display:none;
                }
                .wrgsv-global-tooltip.show {
                    visibility: visible;
                    opacity: 0.95;
					display:block;
                }
                
                .wrgsv-global-tooltip .tooltip-header {
                    font-weight: bold;
                    margin-bottom: 5px;
                    display: flex;
                    gap: 6px;
                    align-items: center;
                }
                .wrgsv-global-tooltip .tooltip-color {
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    display: inline-block;
                    border: 1px solid #ccc;
                }
                .wrgsv-global-tooltip .tooltip-price {
                    margin: 5px 0;
                    font-weight: bold;
                    color: #1a8917;
                    font-size: 15px;
                }
                .wrgsv-global-tooltip .tooltip-image {
                    margin-top: 5px;
                    text-align: center;
					height:150px;
					width:auto;
                }
                .wrgsv-global-tooltip .tooltip-image img {
                    max-width: 100%;
                    max-height: 140px;
                    display: block;
                    margin: 0 auto;
                }
                .wrgsv-global-tooltip .tooltip-title {
                    margin-top: 6px;
                    font-size: 13px;
                    color: #666;
                }`;
            document.head.appendChild(style);
        },

        initTooltip: function (container) {
            const $ = window.jQuery;
            let $tooltip = $('#wrgsv-global-tooltip');
            if (!$tooltip.length) {
                $tooltip = $('<div id="wrgsv-global-tooltip" class="wrgsv-global-tooltip"></div>').appendTo('body');
            }

            let popper = null;
            let hideTimer = null;
            const HIDE_DELAY = 200;

            $(container)
                .off('.tooltipCell')
                .on('mouseenter.tooltipCell', '[rel~="tooltip"]', function () {
                    const $cell = $(this);
                    const tip = $cell.attr('title');
                    const color = $cell.attr('data-color');
                    const rooms = $cell.attr('data-rooms');
                    const area = $cell.data('area');
                    const image = $cell.data('image');
                    const num = $cell.data('num');
                    const price = ($cell.data('price') || '').toString().replace(/\s+/g, '');
                    const status = $cell.data('status');

                    if (!tip && !color && !rooms && !area && !num && !price && !image) return;

                    $cell.data('original-title', tip);
                    $cell.removeAttr('title');

                    let html = `
                        <div class="tooltip-header">
                            <span class="tooltip-color" style="background:${color || '#ccc'}"></span>
                            <span>№${num || '—'}</span>
                            <span>|</span>
                            <span>${rooms || '—'}</span>
                            <span>|</span>
                            <span>${area || '—'} м²</span>
                        </div>
                        <div class="tooltip-price">${price ? parseInt(price, 10).toLocaleString('ru-RU') + '₽' : ''}</div>`;
                    if (status) html += `<div class="tooltip-status">${status}</div>`;
                    if (image) html += `<div class="tooltip-image"><img src="${String(image).trim()}" alt=""></div>`;
                    if (tip) html += `<div class="tooltip-title">${tip}</div>`;

                    $tooltip.html(html).addClass('show');

                    if (popper) popper.destroy();
                    popper = Popper.createPopper($cell[0], $tooltip[0], {
                        placement: 'top',
                        modifiers: [
                            { name: 'offset', options: { offset: [0, 10] } },
                            { name: 'preventOverflow', options: { padding: 10 } },
                            { name: 'flip', options: { fallbackPlacements: ['bottom', 'top-start', 'top-end'] } }
                        ]
                    });

                    const img = $tooltip.find('img')[0];
                    if (img && !img.complete) {
                        img.onload = () => popper && popper.update();
                    }
                    clearTimeout(hideTimer);
                })
                .on('mouseleave.tooltipCell', '[rel~="tooltip"]', scheduleHide);

            $tooltip
                .off('.tooltipBox')
                .on('mouseenter.tooltipBox', cancelHide)
                .on('mouseleave.tooltipBox', scheduleHide);

            function scheduleHide() {
                clearTimeout(hideTimer);
                hideTimer = setTimeout(() => {
                    $tooltip.removeClass('show');
                    $('[rel~="tooltip"]').each(function () {
                        const $c = $(this);
                        if (!$c.attr('title') && $c.data('original-title')) {
                            $c.attr('title', $c.data('original-title'));
                        }
                    });
                    if (popper) {
                        popper.destroy();
                        popper = null;
                    }
                }, HIDE_DELAY);
            }

            function cancelHide() {
                clearTimeout(hideTimer);
            }
        },



      initFancybox: function (container) {
    const $ = window.jQuery;
    $(container).find('a.iframe').fancybox({
        type: 'iframe', // Принудительно указываем тип iframe
        scrolling: 'yes', // Включаем скроллинг
        maxWidth: 800,
        maxHeight: 600,
		
        width: '90%',
        height: '70%',
       'iframe': {'scrolling': 'yes'},
        helpers: {
            overlay: { closeClick: false } // Отключаем закрытие по клику на оверлее
        },
        afterLoad: function () {
            // Явно включаем скроллинг через CSS
            $('.fancybox-content').css('overflow', 'auto');
        }
    });
},
		
		
		
		////////////////////////////
        initSwiper: function (container) {
            // Проверка, что слайдер правильно найден
            const swiperContainer = container.querySelector('.sch-sl');
            if (!swiperContainer) {
                console.error('Swiper контейнер не найден!');
                return;
            }

            const swiperWrapper = swiperContainer.querySelector('.swiper-wrapper');
            if (!swiperWrapper) {
                console.error('Swiper wrapper не найден!');
                return;
            }

            console.log('Swiper контейнер и wrapper найдены, инициализация слайдера...');

            // Добавление необходимых стилей для правильного отображения
           

            let schSlider = new Swiper(swiperContainer, {
                slidesPerView: 'auto',  // Автоматическое определение размера слайдов
                spaceBetween: 20,       // Расстояние между слайдами
                loop: false,            // Без зацикливания
                freeMode: true,         // Свободный режим
                preventClicks: false,   // Разрешение кликов
                preventClicksPropagation: false,
                speed: 800,             // Скорость анимации
                observer: true,         // Наблюдатель за DOM
                observeParents: true,   // Наблюдение за родителями
                watchOverflow: true,    // Наблюдение за переполнением
                breakpoints: {
                    768: {
                        freeMode: false,   // Для мобильных устройств отключаем freeMode
                    },
                },
            });

            console.log('Swiper инициализирован', schSlider);
        }
    };

    window.wrgsv = wrgsv;
})();
