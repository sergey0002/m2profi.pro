(function () {
    var wrgsv = {
        idBox: 'wrgsv',
        url_widget: 'https://lg.m2profi.pro/sahmatka/display_home_public.php',

        init: function (url, id) {
            if (!id) id = this.idBox;
            if (!url) url = this.url_widget;
            const el = document.getElementById(id);
            if (!el) {
                console.warn('Element with id="' + id + '" not found');
                return;
            }

            this.ensureJQuery(() => {
                this.ensureFancybox(() => {
                    this.loadWidget(url, el);
                });
            });
        },

        loadWidget: function (url, container) {
            const shadow = container.shadowRoot || container.attachShadow({ mode: 'open' });

            fetch(url).then(res => res.text()).then(html => {
                shadow.innerHTML = '';

                const style = document.createElement('link');
                style.rel = 'stylesheet';
                style.href = 'https://lg.m2profi.pro/wiget_home.css';
                shadow.appendChild(style);

                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                shadow.appendChild(wrapper);

                this.initGlobalTooltipStyle();
                this.initTooltip(wrapper);
                this.initFancybox(wrapper);

                // Локальная фильтрация внутри конкретного экземпляра
                const updateFilter = () => {
                    const checkboxes = wrapper.querySelectorAll('.sch-ftr-chk input[type="checkbox"]');
                    const allRoomElements = wrapper.querySelectorAll('[data-rooms_int]');

                    const visibleTypes = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.name.toLowerCase());

                    if (visibleTypes.length === 0) {
                        allRoomElements.forEach(el => el.classList.remove('hide_01'));
                        return;
                    }

                    allRoomElements.forEach(el => {
                        const roomType = el.getAttribute('data-rooms_int')?.toLowerCase();
                        if (roomType && visibleTypes.includes(roomType)) {
                            el.classList.remove('hide_01');
                        } else {
                            el.classList.add('hide_01');
                        }
                    });
                };

                wrapper.addEventListener('change', function (e) {
                    if (e.target.matches('.sch-ftr-chk input[type="checkbox"]')) {
                        updateFilter();
                    }
                });

                setTimeout(updateFilter, 100);

                document.dispatchEvent(new CustomEvent('wiget_home_loaded', { detail: { id: container.id } }));
            }).catch(err => {
                console.error('Ошибка загрузки виджета:', err);
                container.style.display = 'none';
            });
        },

        ensureJQuery: function (callback) {
            if (typeof window.jQuery === 'undefined') {
                let jqScript = document.createElement('script');
                jqScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                jqScript.onload = callback;
                document.head.appendChild(jqScript);
            } else {
                callback();
            }
        },

        ensureFancybox: function (callback) {
            const $ = window.jQuery;
            if (!$ || typeof $.fancybox === 'undefined') {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://em.m2profi.pro.test/fancybox-3.0/dist/jquery.fancybox.min.css';
                document.head.appendChild(css);

                const js = document.createElement('script');
                js.src = 'https://em.m2profi.pro.test/fancybox-3.0/dist/jquery.fancybox.min.js';
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
    border: 1px solid #ccc;
     padding: 10px;
    font-size: 14px;
    line-height: 1.4;
    max-width: 280px;
 
    display: none;
    color: #333;
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
            let tooltip = $('#wrgsv-global-tooltip');
            if (!tooltip.length) {
                tooltip = $('<div id="wrgsv-global-tooltip" class="wrgsv-global-tooltip"></div>').appendTo('body').hide();
            }

            $(container).on('mouseenter', '[rel~="tooltip"]', function () {
                const target = $(this);
                const tip = target.attr('title');
                const color = target.attr('data-color');
                const rooms = target.attr('data-rooms');
                const area = target.data('area');
                const image = target.data('image');
                const num = target.data('num');
                const price = target.data('price')?.toString().replace(/\s+/g, '') || null;
                const status = target.data('status');

                if (!tip && !color && !rooms && !area && !num && !price && !image) return;

                const originalTitle = tip;
                target.removeAttr('title');

                let content = '';
                content += `<div class="tooltip-main">
                    <div class="tooltip-header">
                        <span class="tooltip-color" style="background:${color || '#ccc'}"></span>
                        <span>№${num || '—'}</span>
                        <span>|</span>
                        <span>${rooms || '—'}</span>
                        <span>|</span>
                        <span>${area || '—'} м²</span>
                    </div>
                    <div class="tooltip-price">${price ? parseInt(price).toLocaleString('ru-RU') : '—'} ₽</div>
                </div>`;

                if (status) content += `<div class="tooltip-status">${status}</div>`;
                if (image) content += `<div class="tooltip-image"><img src="${image.trim()}" alt=""></div>`;
                if (tip) content += `<div class="tooltip-title">${tip}</div>`;

                tooltip.html(content).css({ opacity: 0, display: 'block' });

                const rect = this.getBoundingClientRect();
                tooltip.css({
                    top: window.scrollY + rect.top - tooltip.outerHeight() - 10,
                    left: window.scrollX + rect.left + (rect.width / 2) - (tooltip.outerWidth() / 2)
                }).animate({ opacity: 1 }, 200);

                $(window).on('resize.tooltip scroll.tooltip', () => {
                    const rect = target[0].getBoundingClientRect();
                    tooltip.css({
                        top: window.scrollY + rect.top - tooltip.outerHeight() - 10,
                        left: window.scrollX + rect.left + (rect.width / 2) - (tooltip.outerWidth() / 2)
                    });
                });

                const remove = () => {
                    tooltip.fadeOut(100);
                    target.attr('title', originalTitle);
                    $(window).off('resize.tooltip scroll.tooltip');
                };

                target.on('mouseleave.tooltip', remove);
                tooltip.on('click.tooltip', remove);
            });
        },

        initFancybox: function (container) {
            const $ = window.jQuery;
            $(container).find('a.iframe').fancybox({
                maxWidth: 1000,
                maxHeight: 600,
                width: '90%',
                height: '70%',
                type: 'iframe',
                scrolling: 'auto',
                fitToView: true,
                autoSize: true,
                helpers: { overlay: { closeClick: false } }
            });
        }
    };

    window.wrgsv = wrgsv;
})();
