/**
 * FacadeWidget — встраиваемый read-only виджет интерактивного фасада.
 * Изоляция: Shadow DOM. Без Leaflet / внешних deps.
 * API: FacadeWidget.mount({ el, homeId, apiBase?, width?, onFloorClick?, … })
 */
(function (global) {
  'use strict';

  // Кеш src текущего скрипта (нужен до любого async — document.currentScript потом null).
  var SCRIPT_SRC = (function () {
    try {
      if (document.currentScript && document.currentScript.src) {
        return document.currentScript.src;
      }
    } catch (e) { /* ignore */ }
    return '';
  })();

  var DEFAULT_LOCALE = {
    loading: 'Загрузка…',
    error: 'Не удалось загрузить данные',
    explore: 'Увеличить',
    close: 'Закрыть',
    floor: 'этаж',
    section: 'секция',
    back: 'Назад',
    backToChoice: 'Назад к выбору',
    planEmpty: 'Квартиры на этом этаже ещё не размечены',
    bookingTitle: 'Заявка на бронирование резиденции',
    tabLayout: 'Планировка',
    tabOnFloor: 'На этаже',
    book: 'Забронировать',
    fio: 'ФИО',
    phone: 'Номер телефона для связи',
    message: 'Ваши вопросы и пожелания',
    consent: 'Нажимая кнопку «Забронировать», вы подтверждаете свое согласие на обработку персональных данных и получение рекламных рассылок.',
    jk: 'Жилой комплекс',
    tower: 'Башня',
    floorLabel: 'Этаж',
    residence: 'Квартира',
    status: 'Статус',
    bookingSuccess: 'Ваша заявка успешно отправлена',
    bookingError: 'Не удалось отправить заявку',
    crumbsLabel: 'Навигация',
    crumbHome: 'Дом',
    visual: 'Визуально',
    onPlan: 'На плане',
    chooseResidence: 'Выбор резиденции',
    showResidences: 'Показать резиденции:',
    statusFree: 'Свободна',
    statusReserved: 'Бронь',
    statusSold: 'Продана'
  };

  // Общий счётчик блокировки скролла страницы (несколько виджетов на одной странице).
  var scrollLockCount = 0;
  var savedBodyOverflow = '';
  var touchMoveBlocked = false;

  function onTouchMoveBlock(e) {
    e.preventDefault();
  }

  function acquireScrollLock() {
    scrollLockCount += 1;
    if (scrollLockCount === 1) {
      savedBodyOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      if (!touchMoveBlocked) {
        document.addEventListener('touchmove', onTouchMoveBlock, { passive: false });
        touchMoveBlocked = true;
      }
    }
  }

  function releaseScrollLock() {
    if (scrollLockCount <= 0) return;
    scrollLockCount -= 1;
    if (scrollLockCount === 0) {
      document.body.style.overflow = savedBodyOverflow;
      savedBodyOverflow = '';
      if (touchMoveBlocked) {
        document.removeEventListener('touchmove', onTouchMoveBlock);
        touchMoveBlocked = false;
      }
    }
  }

  function resolveEl(el) {
    if (!el) return null;
    if (typeof el === 'string') return document.querySelector(el);
    return el;
  }

  function normalizeWidth(width) {
    if (width === '100%' || width == null) return { type: 'pct', value: 100 };
    if (typeof width === 'number' && isFinite(width) && width > 0) {
      return { type: 'px', value: width };
    }
    if (typeof console !== 'undefined' && console.warn) {
      console.warn('[FacadeWidget] width must be "100%" or a number (px). Falling back to 100%. Got:', width);
    }
    return { type: 'pct', value: 100 };
  }

  function detectApiBase() {
    if (!SCRIPT_SRC) return '';
    try {
      var u = new URL(SCRIPT_SRC);
      // …/sahmatka/template/default/js/facade_widget.js → …/sahmatka/ajax_router.php?ctr=facades
      var path = u.pathname.replace(/\/template\/default\/js\/[^/]+$/, '');
      return u.origin + path + '/ajax_router.php?ctr=facades';
    } catch (e) {
      return '';
    }
  }

  function resolveExportLib(relPath) {
    if (SCRIPT_SRC) {
      try {
        var u = new URL(SCRIPT_SRC);
        var base = u.pathname.replace(/\/js\/[^/]+$/, '/libs/ultimate-export/libs/');
        return u.origin + base + relPath;
      } catch (e) { /* fall through */ }
    }
    if (relPath.indexOf('html2canvas') >= 0) {
      return 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    }
    return 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  }

  var _scriptLoadCache = {};
  function loadScriptOnce(src) {
    if (_scriptLoadCache[src]) return _scriptLoadCache[src];
    _scriptLoadCache[src] = new Promise(function (resolve, reject) {
      var found = document.querySelector('script[src="' + src.replace(/"/g, '\\"') + '"]');
      if (found) {
        resolve();
        return;
      }
      var s = document.createElement('script');
      s.src = src;
      s.async = true;
      s.onload = function () { resolve(); };
      s.onerror = function () {
        delete _scriptLoadCache[src];
        reject(new Error('Не удалось загрузить ' + src));
      };
      document.head.appendChild(s);
    });
    return _scriptLoadCache[src];
  }

  function waitForImages(root, timeoutMs) {
    return new Promise(function (resolve) {
      var imgs = root.querySelectorAll ? root.querySelectorAll('img') : [];
      imgs = Array.prototype.slice.call(imgs);
      if (!imgs.length) {
        resolve();
        return;
      }
      var left = imgs.length;
      var done = false;
      function tick() {
        left -= 1;
        if (left <= 0 && !done) {
          done = true;
          resolve();
        }
      }
      imgs.forEach(function (img) {
        if (img.complete) {
          tick();
          return;
        }
        img.addEventListener('load', tick);
        img.addEventListener('error', tick);
      });
      setTimeout(function () {
        if (!done) {
          done = true;
          resolve();
        }
      }, timeoutMs || 4000);
    });
  }

  function escapeHtmlAttr(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/"/g, '&quot;');
  }

  function floorKey(section, floor) {
    return String(section) + ':' + String(floor);
  }

  /** Desktop layout (inline plan/card) — breakpoint как в CSS виджета. */
  function isDesktopUi() {
    try {
      return window.matchMedia('(min-width: 900px)').matches;
    } catch (e) {
      return typeof window !== 'undefined' && window.innerWidth >= 900;
    }
  }

  function formatAreaRu(area) {
    if (area == null || area === '') return '';
    var s = String(area).replace('.', ',');
    return s;
  }

  function clamp01(n, fallback) {
    n = typeof n === 'number' ? n : parseFloat(n);
    if (!isFinite(n)) return fallback;
    if (n < 0) return 0;
    if (n > 1) return 1;
    return n;
  }

  function normalizeHexColor(raw, fallback) {
    if (typeof raw !== 'string') return fallback;
    var s = raw.trim();
    if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(s)) return s;
    if (/^rgba?\(/i.test(s) || /^hsla?\(/i.test(s)) return s;
    return fallback;
  }

  /**
   * facadeHighlight / apartmentHighlight: { color, opacity, idleOpacity?, hoverOpacity?, revealOpacity? }
   * На фасаде обводка = тот же color, opacity×0.7, stroke-width 2px (не задаётся отдельно).
   */
  function normalizeHighlightOpts(opt, defaults) {
    opt = opt || {};
    return {
      color: normalizeHexColor(opt.color, defaults.color),
      opacity: clamp01(opt.opacity, defaults.opacity),
      idleOpacity: clamp01(opt.idleOpacity, defaults.idleOpacity),
      hoverOpacity: clamp01(opt.hoverOpacity, defaults.hoverOpacity),
      revealOpacity: clamp01(opt.revealOpacity, defaults.revealOpacity)
    };
  }

  function formatRoomsK(rooms) {
    if (!rooms && rooms !== 0) return '';
    return String(rooms) + 'K';
  }

  function prefersReducedMotion() {
    try {
      return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {
      return false;
    }
  }

  function normalizeCrumbItem(raw, defaults) {
    if (raw === false) return { show: false, clickable: false };
    if (raw === true) return { show: true, clickable: !!defaults.clickable };
    raw = raw || {};
    return {
      show: raw.show != null ? !!raw.show : !!defaults.show,
      clickable: raw.clickable != null ? !!raw.clickable : !!defaults.clickable
    };
  }

  /**
   * breadcrumbs: { home, section, floor, apartment }
   * каждый: false | true | { show?, clickable? }
   * defaults: дом скрыт; секция видима некликабельно; этаж видим кликабельно; квартира видима некликабельно
   */
  function normalizeBreadcrumbs(opt) {
    opt = opt || {};
    return {
      home: normalizeCrumbItem(opt.home, { show: false, clickable: false }),
      section: normalizeCrumbItem(opt.section, { show: true, clickable: false }),
      floor: normalizeCrumbItem(opt.floor, { show: true, clickable: true }),
      apartment: normalizeCrumbItem(opt.apartment, { show: true, clickable: false })
    };
  }

  /** #fw=1.12.458 | #fw=c.1 (шахматка) */
  function parseFwHash(hash) {
    var raw = String(hash || '');
    if (raw.charAt(0) === '#') raw = raw.slice(1);
    var m = raw.match(/(?:^|&)fw=([^&]*)/);
    if (!m) {
      if (/^\d+\.\d+/.test(raw) || /^c\.\d+/i.test(raw)) {
        m = [null, raw];
      } else {
        return null;
      }
    }
    var parts = String(m[1] || '').split('.');
    if (parts.length >= 2 && (parts[0] === 'c' || parts[0] === 'C')) {
      var cSec = parseInt(parts[1], 10);
      if (!cSec) return null;
      return { mode: 'chessboard', section: cSec };
    }
    if (parts.length < 2) return null;
    var section = parseInt(parts[0], 10);
    var floor = parseInt(parts[1], 10);
    if (!section || !floor) return null;
    var out = { section: section, floor: floor };
    if (parts.length >= 3 && parts[2] !== '') {
      var apt = parseInt(parts[2], 10);
      if (apt) out.apartmentNum = apt;
    }
    return out;
  }

  function buildFwHashValue(state) {
    if (!state || !state.section || !state.floor) return '';
    var s = String(state.section) + '.' + String(state.floor);
    if (state.apartmentNum) s += '.' + String(state.apartmentNum);
    return s;
  }

  /** Explore (fullscreen pan/zoom + ✕) — только мобильный UX, на десктопе не включаем. */
  function allowsExploreMode() {
    try {
      if (window.matchMedia('(hover: none) and (pointer: coarse)').matches) return true;
      if (window.matchMedia('(max-width: 768px)').matches) return true;
    } catch (e) {
      return typeof window !== 'undefined'
        && ('ontouchstart' in window)
        && window.innerWidth <= 768;
    }
    return false;
  }

  function pointsToSvgAttr(points, imageHeight) {
    var parts = [];
    var h = Number(imageHeight) || 0;
    for (var i = 0; i < points.length; i++) {
      var p = points[i];
      if (!p || p.length < 2) continue;
      // Редактор (Leaflet CRS.Simple): y = 0 у нижнего края картинки.
      // SVG viewBox: y = 0 у верхнего края → переворот оси.
      var x = Number(p[0]);
      var y = h > 0 ? (h - Number(p[1])) : Number(p[1]);
      parts.push(x + ',' + y);
    }
    return parts.join(' ');
  }

  /* Цвета — цветопроба макета Stage 3 (colorprobe-mockup.png): accent #76939D */
  var ACCENT = '#76939D';
  var ACCENT_HOVER = '#65838D';
  var ACCENT_SOFT = '#E4ECEF';
  /* подсветка этажа на фасаде — чуть синее и плотнее accent */
  var FACADE_HL = '#5B8FB8';
  /* шахматка — цветопроба по макету (не green из wiget_home2) */
  var CHESS_FREE = '#6B95A5';
  var CHESS_RESERVED = '#E5A35D';
  var CHESS_SOLD = '#9B938F';
  var CHESS_FILTERED = '#D1D1D1';
  var TEXT_MAIN = '#1A1A1A';
  var TEXT_MUTED = '#666666';
  var BORDER_SOFT = '#D1D5D8';

  var WIDGET_CSS = [
    ':host { display: block; box-sizing: border-box; }',
    '*, *::before, *::after { box-sizing: border-box; }',
    '.fw-root { position: relative; width: 100%; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color: ' + TEXT_MAIN + '; background: #f0f2f4; --fw-fade-ms: 280ms; }',
    '.fw-viewport { position: relative; overflow: hidden; background: #f0f2f4; touch-action: manipulation; -webkit-user-select: none; user-select: none; }',
    '.fw-stage { position: relative; transform-origin: 0 0; will-change: transform; margin: 0; width: 100%; }',
    '.fw-stage img { display: block; width: 100%; height: auto; border: 0; max-width: none; pointer-events: none; -webkit-user-drag: none; vertical-align: top; }',
    '.fw-stage svg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; overflow: visible; }',
    '.fw-poly { fill-opacity: var(--fw-facade-idle-opacity, 0); stroke-width: 2; stroke-opacity: 0; cursor: pointer; transition: fill-opacity 0.18s ease, stroke-width 0.18s ease, stroke-opacity 0.18s ease, fill 0.18s ease, stroke 0.18s ease; outline: none; }',
    '.fw-poly.is-hover, .fw-poly.is-active, .fw-poly:focus-visible { fill: var(--fw-facade-hl-color, ' + FACADE_HL + ') !important; stroke: var(--fw-facade-hl-color, ' + FACADE_HL + ') !important; fill-opacity: var(--fw-facade-hl-opacity, 0.58); stroke-opacity: var(--fw-facade-stroke-opacity, 0.4); stroke-width: 2; }',
    '.fw-poly.is-scroll-reveal { fill: var(--fw-facade-hl-color, ' + FACADE_HL + ') !important; stroke: var(--fw-facade-hl-color, ' + FACADE_HL + ') !important; fill-opacity: var(--fw-facade-reveal-opacity, 0.65); stroke-opacity: var(--fw-facade-reveal-stroke-opacity, 0.45); stroke-width: 2; }',
    '@media (prefers-reduced-motion: reduce) { .fw-poly { transition: none; } }',
    /* тултип фасада = тот же glass-стиль, что у плана (fw-apt-tooltip) */
    '.fw-tooltip, .fw-apt-tooltip { position: absolute; z-index: 8; pointer-events: none; padding: 12px 16px; border-radius: 0; background: rgba(36,36,40,0.58); color: #fff; text-align: left; line-height: 1.3; opacity: 0; transform: translate(-50%, calc(-100% - 12px)); transition: none; white-space: nowrap; -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); box-shadow: 0 2px 12px rgba(0,0,0,0.22); }',
    '.fw-tooltip.is-visible, .fw-apt-tooltip.is-visible { opacity: 1; transition: opacity 300ms ease; }',
    '@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) { .fw-tooltip, .fw-apt-tooltip { background: rgba(36,36,40,0.72); } }',
    '.fw-tooltip__code, .fw-apt-tooltip__code { display: block; font-weight: 700; font-size: 20px; letter-spacing: 0.01em; margin-bottom: 4px; color: #fff; }',
    '.fw-tooltip__spec, .fw-apt-tooltip__spec { display: block; font-weight: 400; font-size: 17px; color: rgba(255,255,255,0.95); }',
    '.fw-btn { appearance: none; border: 0; border-radius: 8px; background: rgba(255,255,255,0.95); color: #111; box-shadow: 0 1px 6px rgba(0,0,0,0.22); cursor: pointer; font: inherit; line-height: 1; margin: 0; padding: 0; }',
    '.fw-btn-close { position: absolute; top: 12px; right: 12px; z-index: 20; width: 40px; height: 40px; padding: 0; display: none; align-items: center; justify-content: center; }',
    '.fw-btn-close svg { display: block; width: 18px; height: 18px; }',
    '.fw-root.is-explore .fw-btn-close { display: flex; }',
    '.fw-msg { padding: 16px; font-size: 14px; line-height: 1.45; color: #444; background: transparent; border: 0; border-radius: 0; }',
    '.fw-msg.is-error { color: #8a1f11; background: #fdecea; border: 1px solid #f5c2c0; border-radius: 6px; }',
    '.fw-msg.is-loading { color: #666; background: transparent; border: 0; box-shadow: none; margin: 0; }',
    /* начальная загрузка виджета — только текст по центру на белом */
    '.fw-root.is-boot-loading { min-height: 240px; background: #fff; display: flex; align-items: center; justify-content: center; }',
    '.fw-root.is-boot-loading > .fw-msg.is-loading { padding: 24px; font-size: 15px; font-weight: 500; color: #666; text-align: center; }',
    /* загрузка карточки — тоже без серого «плинтуса» */
    '.fw-card > .fw-msg.is-loading { display: flex; align-items: center; justify-content: center; min-height: 240px; width: 100%; background: #fff; }',
    '.fw-plan-viewport .fw-msg.is-loading { background: #fff; border: 0; box-shadow: none; margin: 24px auto; }',
    /* fullscreen takeover explore */
    '.fw-explore-layer { display: none; position: fixed; inset: 0; z-index: 2147483000; background: rgba(0,0,0,0.72); padding: 0; }',
    '.fw-root.is-explore .fw-explore-layer { display: block; }',
    '.fw-explore-inner { position: absolute; inset: 0; width: 100%; height: 100%; overflow: hidden; background: #111; }',
    '@media (min-width: 900px) {',
    '  .fw-explore-inner { inset: 5vh auto auto 50%; left: 50%; top: 5vh; width: min(96vw, 1400px); height: 90vh; transform: translateX(-50%); border-radius: 10px; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.45); }',
    '}',
    '.fw-explore-inner .fw-viewport { width: 100%; height: 100%; background: #1a1a1a; }',
    '.fw-explore-inner .fw-stage { cursor: grab; }',
    '.fw-explore-inner .fw-stage.is-dragging { cursor: grabbing; }',
    '.fw-body { position: relative; width: 100%; }',
    '.fw-view { display: block; width: 100%; opacity: 1; transition: opacity var(--fw-fade-ms) ease; }',
    '.fw-view--floor, .fw-view--card, .fw-view--chessboard { display: none; }',
    '.fw-root.is-view-floor .fw-view--facade { display: none; }',
    '.fw-root.is-view-floor .fw-view--floor { display: flex; flex-direction: column; }',
    '.fw-root.is-view-card.is-desktop .fw-view--facade, .fw-root.is-view-card.is-desktop .fw-view--floor, .fw-root.is-view-card.is-desktop .fw-view--chessboard { display: none; }',
    '.fw-root.is-view-card.is-desktop .fw-view--card { display: block; }',
    '.fw-root.is-view-chessboard .fw-view--facade { display: none; }',
    '.fw-root.is-view-chessboard .fw-view--chessboard { display: block; }',
    '.fw-view.is-fading-out, .fw-view.is-fading-in { opacity: 0; }',
    '@media (prefers-reduced-motion: reduce) { .fw-view { transition: none; } }',
    /* переключатель Визуально / На плане — хост: фасад (под заголовком) или шахматка (справа сверху) */
    '.fw-mode-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 0; padding: 0; margin: 0; background: transparent; pointer-events: auto; }',
    '.fw-root.is-view-floor .fw-mode-bar, .fw-root.is-view-card .fw-mode-bar { display: none; }',
    '.fw-mode-btn { appearance: none; border: 1px solid ' + ACCENT + '; background: #fff; color: ' + ACCENT + '; font: inherit; font-size: 13px; font-weight: 600; padding: 8px 16px; cursor: pointer; line-height: 1.2; border-radius: 999px; margin: 0 6px 0 0; }',
    '.fw-mode-btn:last-child { margin-right: 0; }',
    '.fw-mode-btn.is-active { background: ' + ACCENT + '; color: #fff; }',
    '.fw-mode-btn:focus-visible { outline: 2px solid ' + ACCENT + '; outline-offset: 2px; }',
    /* оверлей фасада: заголовок + режимы слева снизу от title */
    '.fw-facade-hero { position: absolute; left: 0; top: 0; z-index: 6; max-width: min(420px, 72%); padding: 28px 24px 20px; pointer-events: none; }',
    '.fw-facade-title { margin: 0 0 18px; font-size: 28px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #2c2c2c; line-height: 1.2; text-shadow: 0 1px 0 rgba(255,255,255,0.35); }',
    '@media (min-width: 900px) { .fw-facade-title { font-size: 34px; letter-spacing: 0.1em; } .fw-facade-hero { padding: 36px 40px 24px; } }',
    '.fw-facade-hero .fw-mode-bar { margin-top: 0; }',
    /* шахматка */
    '.fw-chess { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; padding: 16px 16px 28px; background: #fff; color: ' + TEXT_MAIN + '; }',
    '@media (min-width: 900px) {',
    '  .fw-chess { grid-template-columns: minmax(0, 1fr) minmax(200px, 280px); column-gap: 32px; row-gap: 0; padding: 20px 20px 36px; max-width: none; margin: 0; }',
    '}',
    '.fw-chess-mode-host { display: flex; justify-content: flex-end; margin: 0 0 8px; }',
    '@media (min-width: 900px) {',
    '  .fw-chess-mode-host { position: absolute; top: 20px; right: 20px; z-index: 3; margin: 0; }',
    '}',
    '.fw-chess-main { min-width: 0; }',
    '.fw-chess-title { margin: 0 0 12px; font-size: 26px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #2c2c2c; line-height: 1.15; padding-right: 0; }',
    '@media (min-width: 900px) { .fw-chess-title { padding-right: 220px; } }',
    '.fw-chess-sections { display: flex; flex-wrap: wrap; align-items: baseline; gap: 6px 10px; margin: 0 0 12px; font-size: 14px; line-height: 1.35; }',
    '.fw-chess-sec { appearance: none; border: 0; background: transparent; padding: 0; margin: 0; font: inherit; color: ' + TEXT_MUTED + '; cursor: pointer; text-decoration: none; }',
    '.fw-chess-sec.is-active { color: ' + TEXT_MAIN + '; font-weight: 700; }',
    '.fw-chess-sec:hover, .fw-chess-sec:focus-visible { color: ' + ACCENT + '; outline: none; }',
    '.fw-chess-sec-sep { opacity: 0.35; user-select: none; }',
    '.fw-chess-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; margin: 0 0 14px; font-size: 14px; color: ' + TEXT_MUTED + '; }',
    '.fw-chess-filter__label { margin-right: 2px; color: ' + TEXT_MAIN + '; }',
    '.fw-chess-chk { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; user-select: none; color: ' + TEXT_MAIN + '; }',
    '.fw-chess-chk input { margin: 0; width: 14px; height: 14px; }',
    '.fw-chess-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 0; margin: 0; }',
    '.fw-chess-table { border-collapse: separate; border-spacing: 3px; margin: 0; }',
    '.fw-chess-floor { width: 18px; min-width: 18px; text-align: right; font-size: 12px; font-weight: 500; color: ' + TEXT_MUTED + '; vertical-align: middle; padding: 0 2px 0 0; line-height: 1; }',
    '.fw-chess-cell { width: 34px; height: 34px; border: 0; padding: 0; margin: 0; border-radius: 3px; font: inherit; font-size: 11px; font-weight: 600; color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; letter-spacing: 0.02em; }',
    '.fw-chess-cell.is-empty { visibility: hidden; pointer-events: none; cursor: default; }',
    '.fw-chess-cell.is-filtered-out { background: var(--fw-chess-filtered, ' + CHESS_FILTERED + ') !important; color: rgba(255,255,255,0.65); cursor: default; pointer-events: none; }',
    '.fw-chess-cell[data-status-key="free"] { background: var(--fw-chess-free, ' + CHESS_FREE + '); }',
    '.fw-chess-cell[data-status-key="reserved"] { background: var(--fw-chess-reserved, ' + CHESS_RESERVED + '); }',
    '.fw-chess-cell[data-status-key="sold"] { background: var(--fw-chess-sold, ' + CHESS_SOLD + '); }',
    '.fw-chess-side { display: none; }',
    '@media (min-width: 900px) { .fw-chess-side { display: block; padding-top: 0; margin-top: 0; } }',
    '.fw-chess-side-title { margin: 0 0 12px; font-size: 20px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #2c2c2c; line-height: 1.2; }',
    '.fw-chess-visual { display: block; width: 100%; height: auto; border: 0; border-radius: 0; background: #e8ecef; }',
    '.fw-chess-tip { position: absolute; z-index: 12; pointer-events: none; opacity: 0; transform: translate(-50%, calc(-100% - 10px)); transition: none; }',
    '.fw-chess-tip.is-visible { opacity: 1; }',
    '.fw-chess-tip.is-chip { padding: 8px 12px; font-size: 13px; font-weight: 700; color: #fff; white-space: nowrap; box-shadow: 0 2px 10px rgba(0,0,0,0.18); border-radius: 0; }',
    '.fw-chess-tip.is-chip[data-status-key="reserved"] { background: var(--fw-chess-reserved, ' + CHESS_RESERVED + '); }',
    '.fw-chess-tip.is-chip[data-status-key="sold"] { background: var(--fw-chess-sold, ' + CHESS_SOLD + '); }',
    '.fw-chess-tip.is-preview { min-width: 180px; max-width: 240px; padding: 12px 14px; background: rgba(255,255,255,0.92); color: ' + TEXT_MAIN + '; text-align: left; box-shadow: 0 4px 18px rgba(0,0,0,0.16); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); }',
    '.fw-chess-tip__code { display: block; font-weight: 700; font-size: 16px; margin-bottom: 4px; }',
    '.fw-chess-tip__spec { display: block; font-size: 14px; color: ' + TEXT_MUTED + '; margin-bottom: 8px; }',
    '.fw-chess-tip__img { display: block; max-width: 100%; max-height: 120px; margin: 0 auto; }',
    /* хлебные крошки */
    '.fw-crumbs { display: none; flex-wrap: wrap; align-items: center; gap: 4px 8px; padding: 10px 16px; background: #e8ecef; border-bottom: 1px solid ' + BORDER_SOFT + '; font-size: 13px; line-height: 1.35; color: ' + TEXT_MUTED + '; }',
    '.fw-crumbs.is-visible { display: flex; }',
    '.fw-crumb { appearance: none; border: 0; background: transparent; margin: 0; padding: 0; font: inherit; color: inherit; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }',
    '.fw-crumb.is-link { color: ' + ACCENT + '; cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }',
    '.fw-crumb.is-link:hover, .fw-crumb.is-link:focus-visible { color: ' + ACCENT_HOVER + '; outline: none; }',
    '.fw-crumb.is-current { color: ' + TEXT_MAIN + '; font-weight: 600; cursor: default; }',
    '.fw-crumb-sep { opacity: 0.4; user-select: none; }',
    /* на плане этажа крошки стоят под «Назад» в стиле заголовка макета «БАШНЯ …» */
    '.fw-plan-head .fw-crumbs { padding: 0; background: transparent; border: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #2c2c2c; line-height: 1.2; gap: 8px 12px; max-width: 100%; }',
    '.fw-plan-head .fw-crumbs .fw-crumb { color: #2c2c2c; font-weight: 700; }',
    '.fw-plan-head .fw-crumbs .fw-crumb.is-link { color: #2c2c2c; text-decoration: none; cursor: pointer; }',
    '.fw-plan-head .fw-crumbs .fw-crumb.is-link:hover, .fw-plan-head .fw-crumbs .fw-crumb.is-link:focus-visible { color: ' + ACCENT + '; }',
    '.fw-plan-head .fw-crumbs .fw-crumb.is-current { color: #2c2c2c; font-weight: 700; }',
    '.fw-plan-head .fw-crumbs .fw-crumb-sep { opacity: 0.4; font-weight: 700; }',
    '@media (min-width: 900px) {',
    '  .fw-plan-head .fw-crumbs { font-size: 28px; letter-spacing: 0.08em; }',
    '}',
    /* на карточке — крошки под «Назад» (стиль заголовка-навигации) */
    '.fw-card-crumbs-host { margin: 4px 0 0; max-width: 100%; }',
    '.fw-card-crumbs-host .fw-crumbs { padding: 0; background: transparent; border: 0; font-size: 18px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #3d3d3d; line-height: 1.25; gap: 6px 10px; max-width: 100%; }',
    '.fw-card-crumbs-host .fw-crumbs .fw-crumb { color: #3d3d3d; font-weight: 700; }',
    '.fw-card-crumbs-host .fw-crumbs .fw-crumb.is-link { color: #3d3d3d; text-decoration: none; cursor: pointer; }',
    '.fw-card-crumbs-host .fw-crumbs .fw-crumb.is-link:hover, .fw-card-crumbs-host .fw-crumbs .fw-crumb.is-link:focus-visible { color: ' + ACCENT + '; }',
    '.fw-card-crumbs-host .fw-crumbs .fw-crumb.is-current { color: #3d3d3d; font-weight: 700; }',
    '.fw-card-crumbs-host .fw-crumbs .fw-crumb-sep { opacity: 0.45; font-weight: 700; }',
    '@media (min-width: 900px) {',
    '  .fw-card-crumbs-host { display: block; margin: 6px 0 0; }',
    '  .fw-card-crumbs-host .fw-crumbs { font-size: 18px; letter-spacing: 0.05em; }',
    '}',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-stage-wrap { display: none !important; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-plan-host { display: flex !important; flex: 1; flex-direction: column; min-height: 0; overflow: hidden; height: 100%; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-inner { background: #fff; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-plan-host .fw-plan-layer { display: flex !important; flex: 1; flex-direction: column; min-height: 0; height: 100%; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-plan-viewport { flex: 1 1 auto; min-height: 200px; height: auto; }',
    '.fw-explore-plan-host { display: none; }',
    '.fw-explore-stage-wrap { flex: 1; min-height: 0; display: flex; flex-direction: column; }',
    '.fw-explore-inner { display: flex; flex-direction: column; }',
    /* поэтажный план — Stage 3 / макет desktop */
    '.fw-plan-layer { display: none; flex-direction: column; background: #fff; color: ' + TEXT_MAIN + '; min-height: 0; width: 100%; }',
    '.fw-root.is-view-floor .fw-plan-layer, .fw-root.is-view-card .fw-plan-layer { display: flex; }',
    '.fw-plan-head { flex: 0 0 auto; display: flex; flex-direction: column; align-items: flex-start; gap: 14px; padding: 16px 16px 12px; background: #fff; }',
    '.fw-plan-back { appearance: none; border: 1px solid rgba(118,147,157,0.35); background: #C5D6DC; color: #fff; border-radius: 999px; padding: 7px 14px 7px 12px; font: inherit; font-size: 13px; font-weight: 500; line-height: 1; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: none; }',
    '.fw-plan-back svg { display: block; flex: 0 0 auto; }',
    '.fw-plan-back:hover, .fw-plan-back:focus-visible { background: ' + ACCENT + '; border-color: ' + ACCENT + '; outline: none; }',
    '.fw-plan-viewport { position: relative; flex: 0 0 auto; overflow: hidden; touch-action: none; min-height: 0; height: auto; background: #fff; padding: 0 8px 12px; }',
    '@media (min-width: 900px) {',
    '  .fw-root.is-view-floor .fw-view--floor { width: 100%; box-sizing: border-box; padding: 0 24px; background: #fff; }',
    '  .fw-root.is-view-floor .fw-plan-layer { width: 100%; max-width: 1140px; margin: 0 auto; padding: 28px 0; box-sizing: border-box; }',
    '  .fw-root.is-view-floor .fw-plan-head { padding: 0 0 24px; gap: 16px; }',
    '  .fw-root.is-view-floor .fw-plan-viewport { padding: 0; }',
    '  .fw-root.is-view-floor .fw-plan-back { padding: 8px 16px 8px 14px; font-size: 14px; background: #B8CDD4; border-color: transparent; }',
    '}',
    '@media (min-width: 1200px) {',
    '  .fw-root.is-view-floor .fw-view--floor { padding: 0 32px; }',
    '  .fw-root.is-view-floor .fw-plan-layer { max-width: 1320px; padding: 32px 0; }',
    '}',
    '.fw-plan-stage { position: absolute; left: 0; top: 0; transform-origin: 0 0; cursor: grab; touch-action: none; -webkit-user-select: none; user-select: none; }',
    '.fw-plan-stage.is-dragging { cursor: grabbing; }',
    '.fw-plan-stage.is-readonly { cursor: default; }',
    '.fw-plan-stage img { display: block; max-width: none; border: 0; pointer-events: none; -webkit-user-drag: none; }',
    '.fw-plan-stage svg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; overflow: visible; }',
    '.fw-apt-poly { fill-opacity: 0 !important; stroke-opacity: 0 !important; stroke-width: 0; cursor: pointer; }',
    '.fw-apt-poly.is-dim { fill-opacity: 0 !important; stroke-opacity: 0 !important; pointer-events: none; }',
    '.fw-apt-poly.is-hover, .fw-apt-poly:focus-visible { fill: var(--fw-apt-hl-color, ' + ACCENT + ') !important; stroke: var(--fw-apt-hl-color, ' + ACCENT + ') !important; fill-opacity: var(--fw-apt-hover-opacity, 0.45) !important; stroke-opacity: 1 !important; stroke-width: 2.5 !important; }',
    '.fw-apt-poly.is-highlight { fill: var(--fw-apt-hl-color, ' + ACCENT + ') !important; stroke: var(--fw-apt-hl-color, ' + ACCENT + ') !important; fill-opacity: var(--fw-apt-hl-opacity, 0.28) !important; stroke-opacity: 0.95 !important; stroke-width: 2.5 !important; }',
    '.fw-plan-banner { position: absolute; left: 50%; bottom: 16px; transform: translateX(-50%); max-width: min(92%, 520px); padding: 10px 16px; border-radius: 8px; background: rgba(20,20,20,0.85); color: #fff; font-size: 13px; text-align: center; line-height: 1.4; }',
    '.fw-plan-viewport .fw-msg { max-width: min(92%, 420px); text-align: center; }',
    /* карточка — desktop макет / цветопроба */
    '.fw-card { display: grid; grid-template-columns: minmax(360px, 460px) minmax(0, 1fr); gap: 0; background: #fff; align-items: stretch; overflow: hidden; min-height: 0; }',
    '@media (max-width: 899.98px) {',
    '  .fw-card { display: flex; flex-direction: column; grid-template-columns: none; }',
    '  .fw-card-side { display: contents; border: 0; padding: 0; }',
    '  .fw-card-head-row { order: 1; padding: 16px 16px 0; }',
    '  .fw-card-crumbs-host { order: 2; padding: 8px 16px 4px; margin: 0; display: block; }',
    '  .fw-card-title { order: 2; padding: 4px 16px 0; }',
    '  .fw-card-visual { order: 3; }',
    '  .fw-card-spec { order: 4; padding: 12px 16px 0; }',
    '  .fw-card-meta { order: 5; padding: 0 16px; }',
    '  .fw-card-price { order: 6; padding: 0 16px; }',
    '  .fw-card-status { order: 7; margin: 8px 16px 0; }',
    '  .fw-card-form { order: 8; padding: 8px 16px 16px; }',
    '}',
    '.fw-card-side { padding: 24px 24px 28px; border-right: 1px solid #E8E8E8; display: flex; flex-direction: column; gap: 14px; background: #fff; }',
    '@media (min-width: 900px) {',
    '  .fw-card-side { padding: 24px 28px 32px; gap: 14px; }',
    '}',
    '@media (max-width: 899.98px) { .fw-card-side { border-right: 0; } }',
    '.fw-card-head-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 4px; }',
    '.fw-card-back { appearance: none; border: 1px solid transparent; background: #A8BEC6; color: #fff; border-radius: 999px; padding: 0 16px 0 14px; height: 32px; font: inherit; font-size: 14px; font-weight: 600; line-height: 1; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }',
    '.fw-card-back svg { display: block; flex: 0 0 auto; }',
    '.fw-card-back:hover, .fw-card-back:focus-visible { background: ' + ACCENT + '; outline: none; }',
    '.fw-card-tools { display: flex; gap: 12px; flex-shrink: 0; align-items: center; }',
    '.fw-card-tool { width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #A8BEC6; background: #fff; color: #6B8088; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; padding: 0; text-decoration: none; box-sizing: border-box; transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease; }',
    '.fw-card-tool:hover, .fw-card-tool:focus-visible { background: ' + ACCENT_SOFT + '; color: ' + ACCENT + '; border-color: ' + ACCENT + '; outline: none; }',
    'a.fw-card-tool { color: #6B8088; }',
    '.fw-card-tool svg { width: 16px; height: 16px; }',
    '.fw-card-tool img { width: 16px; height: 16px; display: block; pointer-events: none; }',
    '.fw-card-title { font-size: 17px; font-weight: 500; color: ' + TEXT_MAIN + '; margin: 4px 0 2px; line-height: 1.35; }',
    '@media (min-width: 900px) { .fw-card-title { font-size: 18px; margin: 8px 0 4px; } }',
    '.fw-card-spec { font-size: 32px; font-weight: 700; color: ' + TEXT_MAIN + '; line-height: 1.15; margin: 0; letter-spacing: -0.01em; }',
    '@media (min-width: 900px) { .fw-card-spec { font-size: 36px; } }',
    '.fw-card-meta { font-size: 14px; line-height: 1.65; color: ' + TEXT_MUTED + '; margin: 0; }',
    '.fw-card-meta div { margin-bottom: 2px; }',
    '.fw-card-meta strong { font-weight: 600; color: ' + TEXT_MUTED + '; }',
    '.fw-card-price { font-size: 28px; font-weight: 700; color: ' + TEXT_MAIN + '; margin: 4px 0 8px; line-height: 1.2; }',
    '@media (min-width: 900px) { .fw-card-price { font-size: 34px; margin: 8px 0 12px; } }',
    '.fw-card-status { padding: 8px 12px; border-radius: 8px; font-size: 14px; font-weight: 600; }',
    '.fw-card-form { display: flex; flex-direction: column; gap: 12px; margin-top: 4px; width: 100%; }',
    '@media (min-width: 900px) { .fw-card-form { gap: 14px; margin-top: 8px; } }',
    '.fw-card-field { display: flex; flex-direction: column; gap: 4px; width: 100%; }',
    '.fw-card-form input, .fw-card-form textarea { width: 100%; border: 1px solid ' + BORDER_SOFT + '; border-radius: 16px; padding: 15px 18px; font: inherit; font-size: 14px; background: #fff; color: ' + TEXT_MAIN + '; box-sizing: border-box; transition: border-color 0.15s ease, box-shadow 0.15s ease; }',
    '.fw-card-form input::placeholder, .fw-card-form textarea::placeholder { color: #9AA0A6; opacity: 1; }',
    '.fw-card-form textarea { min-height: 110px; resize: vertical; border-radius: 16px; line-height: 1.45; }',
    '@media (min-width: 900px) {',
    '  .fw-card-form input, .fw-card-form textarea { border-radius: 18px; padding: 16px 20px; }',
    '  .fw-card-form textarea { min-height: 120px; }',
    '}',
    '.fw-card-form input:focus, .fw-card-form textarea:focus { outline: none; border-color: ' + ACCENT + '; box-shadow: 0 0 0 3px rgba(118,147,157,0.2); }',
    '.fw-card-form input.is-error, .fw-card-form textarea.is-error { border-color: #c62828; background: #fff8f8; box-shadow: 0 0 0 3px rgba(198,40,40,0.12); }',
    '.fw-card-field-error { display: none; font-size: 12px; line-height: 1.35; color: #c62828; padding: 0 2px; }',
    '.fw-card-field-error.is-visible { display: block; }',
    '.fw-card-submit { appearance: none; border: 0; border-radius: 999px; background: ' + ACCENT + '; color: #fff; font: inherit; font-size: 16px; font-weight: 600; padding: 16px 24px; cursor: pointer; margin-top: 4px; width: 100%; text-align: center; line-height: 1.2; }',
    '@media (min-width: 900px) { .fw-card-submit { padding: 17px 24px; font-size: 17px; margin-top: 6px; } }',
    '.fw-card-submit:hover { background: ' + ACCENT_HOVER + '; }',
    '.fw-card-submit:disabled { opacity: 0.6; cursor: wait; }',
    '.fw-card-consent { font-size: 11px; line-height: 1.45; color: #A0A4A8; margin: 2px 0 0; }',
    '.fw-card-msg { font-size: 14px; padding: 10px 12px; border-radius: 8px; }',
    '.fw-card-msg.is-ok { background: #e8f5e9; color: #2e7d32; }',
    '.fw-card-msg.is-err { background: #fdecea; color: #8a1f11; }',
    '.fw-card-msg ul { margin: 6px 0 0; padding-left: 18px; }',
    '.fw-card-visual { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; padding: 16px 20px 20px; background: #fff; }',
    '@media (min-width: 900px) { .fw-card-visual { padding: 20px 24px 28px; background: #fff; } }',
    '@media (max-width: 899.98px) { .fw-card-visual { padding: 12px 16px 16px; border-bottom: 1px solid #ececec; background: #fff; } }',
    '.fw-card-tabs { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; }',
    '@media (min-width: 900px) { .fw-card-tabs { gap: 12px; margin-bottom: 20px; } }',
    '.fw-card-tab { appearance: none; border: 1.5px solid #A8BEC6; background: #fff; color: #5A6E75; border-radius: 999px; padding: 0 20px; height: 32px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; line-height: 1; display: inline-flex; align-items: center; justify-content: center; transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease; }',
    '.fw-card-tab:hover:not(.is-active) { background: ' + ACCENT_SOFT + '; color: ' + ACCENT + '; border-color: ' + ACCENT + '; }',
    '.fw-card-tab.is-active { background: ' + ACCENT + '; color: #fff; border-color: ' + ACCENT + '; }',
    '.fw-card-panel { display: none; flex-direction: column; flex: 1 1 auto; width: 100%; min-width: 0; }',
    '.fw-card-panel.is-active { display: flex; }',
    '.fw-card-panel[data-panel="layout"].is-active { align-items: stretch; justify-content: flex-start; min-height: min(58vh, 560px); }',
    '.fw-card-pln-label { text-align: center; font-size: 13px; color: ' + TEXT_MUTED + '; margin: 4px 0; }',
    '.fw-card-pln-img { display: block; width: 100%; max-width: 100%; height: auto; max-height: min(68vh, 680px); margin: 8px auto; object-fit: contain; }',
    '.fw-card-floor-vp { position: relative; flex: 1 1 auto; width: 100%; min-height: min(52vh, 480px); height: min(60vh, 560px); overflow: hidden; background: #fff; border-radius: 8px; }',
    '.fw-card-layer-mobile { display: none; position: fixed; inset: 0; z-index: 2147483002; background: #fff; overflow: auto; -webkit-overflow-scrolling: touch; }',
    '.fw-root.is-view-card.is-mobile-ui .fw-card-layer-mobile { display: block; }',
    '@media (min-width: 900px) {',
    '  .fw-root.is-view-card .fw-view--card { width: 100%; box-sizing: border-box; padding: 0 24px 28px; background: #fff; }',
    '  .fw-root.is-view-card .fw-card { width: 100%; max-width: 1140px; margin: 0 auto; min-height: min(88vh, 920px); box-sizing: border-box; }',
    '}',
    '@media (min-width: 1200px) {',
    '  .fw-root.is-view-card .fw-view--card { padding: 0 32px 32px; }',
    '  .fw-root.is-view-card .fw-card { max-width: 1320px; }',
    '}',
    /* печать: только карточка, крупная планировка */
    '@media print {',
    '  .fw-view--facade, .fw-view--chessboard, .fw-view--floor, .fw-explore-layer, .fw-crumbs, .fw-mode-bar { display: none !important; }',
    '  .fw-root { background: #fff !important; }',
    '  .fw-root.is-view-card .fw-view--card { display: block !important; }',
    '  .fw-root.is-view-card.is-mobile-ui .fw-view--card { display: none !important; }',
    '  .fw-root.is-view-card.is-mobile-ui .fw-card-layer-mobile { display: block !important; position: static !important; inset: auto !important; overflow: visible !important; height: auto !important; z-index: auto !important; }',
    '  .fw-card { display: flex !important; flex-direction: column !important; grid-template-columns: none !important; overflow: visible !important; background: #fff !important; }',
    '  .fw-card-side { display: flex !important; flex-direction: column !important; border: 0 !important; padding: 0 0 12px !important; width: 100% !important; gap: 8px !important; }',
    '  .fw-card-back, .fw-card-tools, .fw-card-tabs, .fw-card-form, .fw-card-consent, .fw-card-submit, .fw-card-msg, .fw-btn-close { display: none !important; }',
    '  .fw-card-visual { padding: 0 !important; }',
    '  .fw-card-panel { display: none !important; }',
    '  .fw-card-panel[data-panel="layout"] { display: flex !important; flex-direction: column !important; align-items: center !important; min-height: 0 !important; break-before: avoid; page-break-before: avoid; }',
    '  .fw-card-pln-img { display: block !important; width: auto !important; max-width: 100% !important; max-height: 165mm !important; height: auto !important; object-fit: contain !important; margin: 4px auto !important; }',
    '  .fw-card-pln-label { font-size: 12px !important; color: #555 !important; }',
    '  .fw-card-floor-vp { display: none !important; }',
    '  .fw-card-crumbs-host .fw-crumbs { display: flex !important; padding: 0 !important; background: transparent !important; border: 0 !important; }',
    '}'
  ].join('\n');

  var PRINT_DOC_CSS = [
    '@page { margin: 10mm; size: auto; }',
    'html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; color: #1a1a1a; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; height: auto !important; min-height: 0 !important; overflow: visible !important; }',
    '*, *::before, *::after { box-sizing: border-box; }',
    /* колонка: текст + планировка на одной странице (картинка сжимается под остаток листа) */
    '.fw-print-sheet { width: 100%; max-width: 100%; margin: 0; padding: 0; background: #fff; }',
    '.fw-card { display: flex !important; flex-direction: column !important; width: 100% !important; max-width: 100% !important; background: #fff !important; overflow: visible !important; gap: 0 !important; }',
    '.fw-card-side { display: flex !important; flex-direction: column !important; width: 100% !important; gap: 8px; padding: 0 0 8px !important; border: 0 !important; order: 1 !important; break-after: avoid; page-break-after: avoid; }',
    '.fw-card-visual { display: flex !important; flex-direction: column !important; width: 100% !important; padding: 0 !important; order: 2 !important; background: #fff !important; break-before: avoid; page-break-before: avoid; }',
    '.fw-card-head-row, .fw-card-back, .fw-card-tools, .fw-card-tabs, .fw-card-form, .fw-card-consent, .fw-card-submit, .fw-card-msg, .fw-card-floor-vp, .fw-card-panel[data-panel="floor"] { display: none !important; }',
    '.fw-card-crumbs-host { margin: 0 0 8px; width: 100%; }',
    '.fw-card-crumbs-host .fw-crumbs { display: flex !important; flex-wrap: wrap; align-items: center; gap: 6px 10px; padding: 0 !important; background: transparent !important; border: 0 !important; font-size: 16px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #3d3d3d; line-height: 1.3; }',
    '.fw-card-crumbs-host .fw-crumb { color: #3d3d3d; font-weight: 700; }',
    '.fw-card-crumbs-host .fw-crumb-sep { opacity: 0.45; font-weight: 700; }',
    '.fw-card-spec { font-size: 22px; font-weight: 700; margin: 0; line-height: 1.15; width: 100%; }',
    '.fw-card-meta { font-size: 12px; line-height: 1.45; color: #555; margin: 0; width: 100%; }',
    '.fw-card-price { font-size: 20px; font-weight: 700; margin: 4px 0 0; width: 100%; }',
    '.fw-card-status { padding: 8px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-top: 6px; width: 100%; }',
    '.fw-card-panel { display: none !important; }',
    '.fw-card-panel[data-panel="layout"] { display: flex !important; flex-direction: column !important; align-items: center !important; width: 100% !important; min-height: 0 !important; flex: 0 0 auto !important; break-before: avoid; page-break-before: avoid; break-inside: avoid; page-break-inside: avoid; }',
    '.fw-card-pln-label { text-align: center; font-size: 11px; color: #666; margin: 2px 0; width: 100%; }',
    /* без лимита высоты браузер выносит крупный PNG на 2-ю страницу и оставляет дыру */
    '.fw-card-pln-img { display: block !important; width: auto !important; max-width: 100% !important; height: auto !important; max-height: 165mm !important; margin: 4px auto 0 !important; object-fit: contain !important; break-inside: avoid; page-break-inside: avoid; }'
  ].join('\n');

  function FacadeWidgetInstance(host, options) {
    this.host = host;
    this.options = options;
    this.shadow = null;
    this.data = null;
    this.destroyed = false;
    this.exploring = false;
    this.sectionCaptions = {};
    this.widthSpec = normalizeWidth(options.width);
    this.locale = Object.assign({}, DEFAULT_LOCALE, options.locale || {});
    this.exploreFullscreen = options.exploreFullscreen !== false;

    this._fitScale = 1;
    this._stageW = 0;
    this._stageH = 0;
    this._imgW = 0;
    this._imgH = 0;

    // explore transform
    this._scale = 1;
    this._tx = 0;
    this._ty = 0;
    this._minScale = 1;
    this._maxScale = 6;

    this._pointers = new Map();
    this._pinchStartDist = 0;
    this._pinchStartScale = 1;
    this._panStart = null;
    this._moved = false;
    this._hoverKey = null;
    this._activeKey = null;
    this._scrollReveal = options.scrollReveal !== false;
    this._scrollRevealSpeed = typeof options.scrollRevealSpeed === 'number' ? options.scrollRevealSpeed : 1;
    this._scrollFloorMap = {};
    this._scrollRevealSteps = 0;
    this._scrollRevealIndex = -1;
    this._revealTimer = null;
    this._revealLoopIdx = 0;
    this._io = null;
    this._hostHovered = false;
    this._onHostPointerEnter = this._onHostPointerEnter.bind(this);
    this._onHostPointerLeave = this._onHostPointerLeave.bind(this);

    // Stage 4: fade / breadcrumbs / optional hash
    this._fadeMs = typeof options.fadeMs === 'number'
      ? Math.max(0, options.fadeMs)
      : (typeof options.fadeDuration === 'number' ? Math.max(0, options.fadeDuration) : 280);
    this._breadcrumbs = normalizeBreadcrumbs(options.breadcrumbs);
    this._urlState = options.urlState !== false;
    this._facadeHighlight = normalizeHighlightOpts(options.facadeHighlight, {
      color: FACADE_HL,
      opacity: 0.58,
      idleOpacity: 0,
      hoverOpacity: 0.58,
      revealOpacity: 0.65
    });
    this._apartmentHighlight = normalizeHighlightOpts(options.apartmentHighlight, {
      color: ACCENT,
      opacity: 0.28,
      idleOpacity: 0.12,
      hoverOpacity: 0.45,
      revealOpacity: 0.28
    });
    // Зум плана: desktop выкл., mobile вкл. — floorPlanZoom: true|false|{desktop,mobile}
    this._planZoom = (function (opt) {
      if (opt === true) return { desktop: true, mobile: true };
      if (opt === false) return { desktop: false, mobile: false };
      opt = opt || {};
      return {
        desktop: opt.desktop === true,
        mobile: opt.mobile !== false
      };
    })(options.floorPlanZoom != null ? options.floorPlanZoom : options.planZoom);
    this._fadeTimer = null;
    this._fadeTimer2 = null;
    this._fadeLock = false;
    this._urlApplying = false;
    this._onHashChange = this._onHashChange.bind(this);

    this._onResize = this._onResize.bind(this);
    this._onKeyDown = this._onKeyDown.bind(this);
    this._ro = null;

    // Stage 3–5: навигация facade | chessboard | floor | card
    this.currentView = 'facade';
    this._navContext = {};
    this._cardData = null;
    this._cardTab = 'layout';
    this._cardHighlightId = 0;
    this._chessboardEnabled = !(options.chessboard && options.chessboard.enabled === false);
    this._chessSection = 0;
    this._chessData = null;
    this._chessRoomFilter = { 1: true, 2: true, 3: true, 4: true };
    this._chessStatusColors = Object.assign({
      free: CHESS_FREE,
      reserved: CHESS_RESERVED,
      sold: CHESS_SOLD,
      filteredOut: CHESS_FILTERED
    }, (options.chessboardStatusColors || (options.chessboard && options.chessboard.statusColors) || {}));
    this._defaultVisualMode = (options.defaultVisualMode === 'chessboard') ? 'chessboard' : 'facade';

    // Поэтажный план
    this.planOpen = false;
    this._planData = null;
    this._scrollLockHeld = 0;
    this._planPointers = new Map();
    this._planPanStart = null;
    this._planMoved = false;
    this._planPinchStartDist = 0;
    this._planPinchStartScale = 1;
    this._planScale = 1;
    this._planTx = 0;
    this._planTy = 0;
    this._planMinScale = 1;
    this._planMaxScale = 6;
    this._planImgW = 0;
    this._planImgH = 0;
    this._planHoverKey = null;
    this._onPlanKeyDown = this._onPlanKeyDown.bind(this);
    this._onNavKeyDown = this._onNavKeyDown.bind(this);
    this._chessEls = {};
    this._planEls = {};
    this._cardEls = {};

    this._els = {};
  }

  FacadeWidgetInstance.prototype.mount = function () {
    var self = this;
    this.host.innerHTML = '';
    this.shadow = this.host.attachShadow({ mode: 'open' });

    var style = document.createElement('style');
    style.textContent = WIDGET_CSS;
    this.shadow.appendChild(style);

    var root = document.createElement('div');
    root.className = 'fw-root is-boot-loading';
    root.setAttribute('role', 'region');
    root.setAttribute('aria-label', 'Интерактивный фасад');
    this.shadow.appendChild(root);
    this._els.root = root;
    this._applyHighlightCssVars();

    var msg = document.createElement('div');
    msg.className = 'fw-msg is-loading';
    msg.textContent = this.locale.loading;
    root.appendChild(msg);
    this._els.msg = msg;

    this._load().then(function () {
      if (self.destroyed) return;
      root.classList.remove('is-boot-loading');
      self._render();
    }).catch(function (err) {
      if (self.destroyed) return;
      root.classList.remove('is-boot-loading');
      self._showError((err && err.message) || self.locale.error);
    });

    return this;
  };

  FacadeWidgetInstance.prototype.destroy = function () {
    if (this.destroyed) return;
    this.destroyed = true;
    clearTimeout(this._fadeTimer);
    clearTimeout(this._fadeTimer2);
    window.removeEventListener('hashchange', this._onHashChange);
    // Порядок важен (аудит C3/§5.4): сначала план, потом explore — так refcount
    // scroll-lock гарантированно опустошается только для ЭТОГО инстанса.
    if (this.currentView !== 'facade') {
      this._closeFloorPlan(true);
      this.currentView = 'facade';
      this._cardData = null;
    }
    if (this.exploring) {
      this._exitExplore(true);
    }
    if (this._ro) {
      try { this._ro.disconnect(); } catch (e) { /* ignore */ }
      this._ro = null;
    }
    this._teardownScrollReveal();
    this._unbindHostHoverPause();
    window.removeEventListener('resize', this._onResize);
    document.removeEventListener('keydown', this._onKeyDown);
    if (this.shadow) {
      this.shadow.innerHTML = '';
    }
    this.host.innerHTML = '';
    this._els = {};
    this.data = null;
  };

  FacadeWidgetInstance.prototype._showError = function (text) {
    var root = this._els.root;
    if (!root) return;
    root.innerHTML = '';
    var msg = document.createElement('div');
    msg.className = 'fw-msg is-error';
    msg.setAttribute('role', 'alert');
    msg.textContent = text || this.locale.error;
    root.appendChild(msg);
    this._els.msg = msg;
  };

  FacadeWidgetInstance.prototype._apiUrl = function () {
    var base = this.options.apiBase || detectApiBase();
    if (!base) {
      throw new Error('Не задан apiBase и не удалось определить URL из script.src. Передайте options.apiBase.');
    }
    var join = base.indexOf('?') >= 0 ? '&' : '?';
    // apiBase обычно уже содержит ?ctr=facades
    if (/[?&]ctr=/.test(base)) {
      return base + '&act=widget_data&home_id=' + encodeURIComponent(this.options.homeId);
    }
    return base + join + 'ctr=facades&act=widget_data&home_id=' + encodeURIComponent(this.options.homeId);
  };

  FacadeWidgetInstance.prototype._load = function () {
    var self = this;
    var url = this._apiUrl();
    return fetch(url, { credentials: 'omit', cache: 'no-cache' }).then(function (res) {
      if (!res.ok) throw new Error(self.locale.error + ' (HTTP ' + res.status + ')');
      return res.json();
    }).then(function (json) {
      if (!json || json.success === false) {
        throw new Error((json && json.message) || self.locale.error);
      }
      if (self.options.sectionsFilter && self.options.sectionsFilter.length) {
        var allow = {};
        self.options.sectionsFilter.forEach(function (id) { allow[String(id)] = true; });
        json.polygons = (json.polygons || []).filter(function (p) {
          return allow[String(p.section)];
        });
        json.sections = (json.sections || []).filter(function (s) {
          return allow[String(s.id)];
        });
      }
      self.data = json;
      self.sectionCaptions = {};
      (json.sections || []).forEach(function (s) {
        self.sectionCaptions[String(s.id)] = s.caption;
      });
      if (json.unitLabels) {
        if (json.unitLabels.nomCap) self.locale.residence = json.unitLabels.nomCap;
      }
      return json;
    });
  };

  FacadeWidgetInstance.prototype._render = function () {
    var self = this;
    var data = this.data;
    var root = this._els.root;
    root.innerHTML = '';

    this._imgW = data.imageWidth;
    this._imgH = data.imageHeight;

    var body = document.createElement('div');
    body.className = 'fw-body';
    root.appendChild(body);
    this._els.body = body;

    var crumbs = document.createElement('nav');
    crumbs.className = 'fw-crumbs';
    crumbs.setAttribute('aria-label', this.locale.crumbsLabel || 'Навигация');
    root.insertBefore(crumbs, body);
    this._els.crumbs = crumbs;

    var facadeView = document.createElement('div');
    facadeView.className = 'fw-view fw-view--facade';
    body.appendChild(facadeView);
    this._els.facadeView = facadeView;

    var viewport = document.createElement('div');
    viewport.className = 'fw-viewport';
    facadeView.appendChild(viewport);
    this._els.viewport = viewport;

    var stage = this._buildStage(true);
    viewport.appendChild(stage);
    this._els.stage = stage;

    var tooltip = document.createElement('div');
    tooltip.className = 'fw-tooltip';
    tooltip.setAttribute('aria-hidden', 'true');
    viewport.appendChild(tooltip);
    this._els.tooltip = tooltip;

    if (this._chessboardEnabled) {
      var facadeHero = document.createElement('div');
      facadeHero.className = 'fw-facade-hero';
      var facadeTitle = document.createElement('div');
      facadeTitle.className = 'fw-facade-title';
      facadeTitle.textContent = this.locale.chooseResidence || 'Выбор резиденции';
      facadeHero.appendChild(facadeTitle);
      var facadeModeHost = document.createElement('div');
      facadeModeHost.className = 'fw-facade-mode-host';
      facadeHero.appendChild(facadeModeHost);
      viewport.appendChild(facadeHero);
      this._els.facadeHero = facadeHero;
      this._els.facadeModeHost = facadeModeHost;

      var modeBar = document.createElement('div');
      modeBar.className = 'fw-mode-bar';
      modeBar.setAttribute('role', 'tablist');
      modeBar.setAttribute('aria-label', 'Режим просмотра');
      var btnVisual = document.createElement('button');
      btnVisual.type = 'button';
      btnVisual.className = 'fw-mode-btn is-active';
      btnVisual.setAttribute('role', 'tab');
      btnVisual.setAttribute('aria-selected', 'true');
      btnVisual.dataset.mode = 'facade';
      btnVisual.textContent = this.locale.visual || 'Визуально';
      var btnPlan = document.createElement('button');
      btnPlan.type = 'button';
      btnPlan.className = 'fw-mode-btn';
      btnPlan.setAttribute('role', 'tab');
      btnPlan.setAttribute('aria-selected', 'false');
      btnPlan.dataset.mode = 'chessboard';
      btnPlan.textContent = this.locale.onPlan || 'На плане';
      modeBar.appendChild(btnVisual);
      modeBar.appendChild(btnPlan);
      facadeModeHost.appendChild(modeBar);
      this._els.modeBar = modeBar;
      this._els.modeBtnVisual = btnVisual;
      this._els.modeBtnPlan = btnPlan;
      modeBar.addEventListener('click', function (ev) {
        var btn = ev.target && ev.target.closest ? ev.target.closest('.fw-mode-btn') : null;
        if (!btn || !modeBar.contains(btn)) return;
        var mode = btn.dataset.mode;
        if (mode === 'chessboard') self._showChessboard();
        else self._showFacadeMode();
      });
    }

    var chessView = document.createElement('div');
    chessView.className = 'fw-view fw-view--chessboard';
    body.appendChild(chessView);
    this._els.chessboardView = chessView;
    this._buildChessboardShell(chessView);

    var floorView = document.createElement('div');
    floorView.className = 'fw-view fw-view--floor';
    body.appendChild(floorView);
    this._els.floorView = floorView;

    var cardView = document.createElement('div');
    cardView.className = 'fw-view fw-view--card';
    body.appendChild(cardView);
    this._els.cardView = cardView;

    // Explore overlay (mobile)
    var exploreLayer = document.createElement('div');
    exploreLayer.className = 'fw-explore-layer';
    exploreLayer.setAttribute('aria-hidden', 'true');
    var exploreInner = document.createElement('div');
    exploreInner.className = 'fw-explore-inner';

    var exploreStageWrap = document.createElement('div');
    exploreStageWrap.className = 'fw-explore-stage-wrap';
    var exploreViewport = document.createElement('div');
    exploreViewport.className = 'fw-viewport';
    exploreStageWrap.appendChild(exploreViewport);
    exploreInner.appendChild(exploreStageWrap);

    var explorePlanHost = document.createElement('div');
    explorePlanHost.className = 'fw-explore-plan-host';
    exploreInner.appendChild(explorePlanHost);
    this._els.explorePlanHost = explorePlanHost;

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'fw-btn fw-btn-close';
    closeBtn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6.4 6.4l11.2 11.2M17.6 6.4L6.4 17.6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
    closeBtn.setAttribute('aria-label', this.locale.close);
    closeBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (self.currentView === 'floor') {
        self._navPop();
      } else {
        self._exitExplore();
      }
    });
    exploreInner.appendChild(closeBtn);

    exploreLayer.appendChild(exploreInner);
    root.appendChild(exploreLayer);
    this._els.exploreLayer = exploreLayer;
    this._els.exploreInner = exploreInner;
    this._els.exploreStageWrap = exploreStageWrap;
    this._els.exploreViewport = exploreViewport;
    this._els.closeBtn = closeBtn;

    exploreViewport.addEventListener('wheel', function (ev) {
      if (!self.exploring || self.currentView !== 'facade') return;
      ev.preventDefault();
      var factor = ev.deltaY < 0 ? 1.08 : 1 / 1.08;
      self._setScaleAt(self._scale * factor, ev.clientX, ev.clientY, exploreViewport);
    }, { passive: false });

    this._buildPlanLayer(floorView);
    this._buildCardShell(cardView);

    var cardMobile = document.createElement('div');
    cardMobile.className = 'fw-card-layer-mobile';
    root.appendChild(cardMobile);
    this._els.cardMobile = cardMobile;
    this._buildCardShell(cardMobile);

    this._bindStageInteractions(stage, viewport, false);
    this._syncUiClasses();
    this._setupScrollReveal();
    this._bindHostHoverPause();

    if (typeof ResizeObserver !== 'undefined') {
      this._ro = new ResizeObserver(function () { self._onResize(); });
      this._ro.observe(this.host);
    }
    window.addEventListener('resize', this._onResize);

    this._els.root.style.setProperty('--fw-fade-ms', this._fadeMs + 'ms');
    this._layoutFit();
    this._updateBreadcrumbs();
    this._bindUrlState();
    this._applyUrlStateOnLoad();
    if (this._chessboardEnabled && this._defaultVisualMode === 'chessboard' && !parseFwHash(location.hash)) {
      this._showChessboard();
    }
  };

  FacadeWidgetInstance.prototype._buildStage = function (storeEls) {
    var data = this.data;
    var stage = document.createElement('div');
    stage.className = 'fw-stage';

    var img = document.createElement('img');
    img.src = data.imageUrl;
    img.alt = data.title || 'Фасад';
    img.draggable = false;
    img.setAttribute('role', 'img');
    stage.appendChild(img);

    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + data.imageWidth + ' ' + data.imageHeight);
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    svg.setAttribute('aria-hidden', 'false');
    stage.appendChild(svg);

    var polys = data.polygons || [];
    for (var i = 0; i < polys.length; i++) {
      var p = polys[i];
      var poly = document.createElementNS(svgNS, 'polygon');
      poly.setAttribute('points', pointsToSvgAttr(p.points, data.imageHeight));
      poly.setAttribute('fill', p.color || '#3388ff');
      poly.setAttribute('stroke', p.color || '#3388ff');
      poly.classList.add('fw-poly');
      poly.setAttribute('tabindex', '0');
      poly.dataset.section = String(p.section);
      poly.dataset.floor = String(p.floor);
      poly.dataset.polygonId = String(p.id);
      poly.dataset.label = p.label || '';
      var caption = this.sectionCaptions[String(p.section)] || (this.locale.section + ' ' + p.section);
      var aria = caption + ' · ' + this.locale.floor + ' ' + p.floor;
      if (p.label) aria += ' — ' + p.label;
      poly.setAttribute('aria-label', aria);
      poly.setAttribute('role', 'button');
      svg.appendChild(poly);
    }

    if (storeEls) {
      this._els.img = img;
      this._els.svg = svg;
    }

    return stage;
  };

  FacadeWidgetInstance.prototype._cloneStageIntoExplore = function () {
    var exploreViewport = this._els.exploreViewport;
    exploreViewport.innerHTML = '';
    var clone = this._buildStage(false);
    exploreViewport.appendChild(clone);
    this._els.exploreStage = clone;

    var tooltip = document.createElement('div');
    tooltip.className = 'fw-tooltip';
    tooltip.setAttribute('aria-hidden', 'true');
    exploreViewport.appendChild(tooltip);
    this._els.exploreTooltip = tooltip;

    this._bindStageInteractions(clone, exploreViewport, true);
    this._applyExploreTransform();
  };

  FacadeWidgetInstance.prototype._polysByKey = function (stage, key) {
    var list = [];
    var nodes = stage.querySelectorAll('.fw-poly');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (floorKey(el.dataset.section, el.dataset.floor) === key) list.push(el);
    }
    return list;
  };

  FacadeWidgetInstance.prototype._fillFacadeTooltip = function (sample, tooltipEl) {
    if (!sample || !tooltipEl) return;
    var floor = sample.dataset.floor || '';
    var caption = this.sectionCaptions[sample.dataset.section]
      || ((this.locale.section || 'секция') + ' ' + sample.dataset.section);
    var codeLine = document.createElement('span');
    codeLine.className = 'fw-tooltip__code';
    codeLine.textContent = (this.locale.floorLabel || 'Этаж') + ' ' + floor;
    var specLine = document.createElement('span');
    specLine.className = 'fw-tooltip__spec';
    var spec = caption;
    if (sample.dataset.label) {
      spec += (spec ? ' · ' : '') + sample.dataset.label;
    }
    specLine.textContent = spec;
    tooltipEl.innerHTML = '';
    tooltipEl.appendChild(codeLine);
    if (spec) tooltipEl.appendChild(specLine);
  };

  FacadeWidgetInstance.prototype._setHover = function (stage, key, tooltipEl, clientX, clientY, viewport) {
    var prev = this._hoverKey;
    if (prev && prev !== key) {
      this._polysByKey(stage, prev).forEach(function (el) { el.classList.remove('is-hover'); });
    }
    this._hoverKey = key;
    if (key) {
      this._applyScrollRevealIndex(-1);
      this._polysByKey(stage, key).forEach(function (el) { el.classList.add('is-hover'); });
      var sample = this._polysByKey(stage, key)[0];
      if (sample && tooltipEl && viewport) {
        this._fillFacadeTooltip(sample, tooltipEl);
        tooltipEl.classList.add('is-visible');
        var rect = viewport.getBoundingClientRect();
        var x = clientX - rect.left;
        var y = clientY - rect.top;
        var tw = tooltipEl.offsetWidth || 120;
        var th = tooltipEl.offsetHeight || 28;
        x = Math.max(tw / 2 + 4, Math.min(rect.width - tw / 2 - 4, x));
        y = Math.max(th + 14, Math.min(rect.height - 8, y));
        tooltipEl.style.left = x + 'px';
        tooltipEl.style.top = y + 'px';
      }
    } else {
      if (tooltipEl) tooltipEl.classList.remove('is-visible');
      if (!this.exploring && this.currentView === 'facade') this._resumeRevealHighlight();
    }
  };

  FacadeWidgetInstance.prototype._floorPayload = function (poly) {
    return {
      homeId: this.options.homeId,
      section: parseInt(poly.dataset.section, 10) || 1,
      floor: parseInt(poly.dataset.floor, 10) || 0,
      label: poly.dataset.label || '',
      polygonId: parseInt(poly.dataset.polygonId, 10) || 0
    };
  };

  FacadeWidgetInstance.prototype._fireFloorClick = function (poly) {
    var payload = this._floorPayload(poly);
    var hasCallback = typeof this.options.onFloorClick === 'function';
    var result;
    if (hasCallback) {
      result = this.options.onFloorClick(payload);
    }
    // Кастомный onFloorClick может явно запретить открытие модалки плана (§5.1).
    if (result && result.preventDefault) return;

    var fp = this.options.floorPlan;
    var floorPlanEnabled = !fp || fp.enabled !== false;
    if (floorPlanEnabled) {
      this._openFloorPlan(payload);
      return;
    }
    // floorPlan.enabled === false и нет кастомного callback — старое поведение (alert).
    if (!hasCallback) {
      alert(this.locale.floor + ' ' + payload.floor + ' (' + this.locale.section + ' ' + payload.section + ')');
    }
  };

  FacadeWidgetInstance.prototype._bindStageInteractions = function (stage, viewport, isExplore) {
    var self = this;
    var tooltipEl = isExplore ? this._els.exploreTooltip : this._els.tooltip;

    function targetPoly(ev) {
      var t = ev.target;
      if (t && t.classList && t.classList.contains('fw-poly')) return t;
      return null;
    }

    stage.addEventListener('pointerdown', function (ev) {
      if (self.destroyed) return;
      stage.setPointerCapture && stage.setPointerCapture(ev.pointerId);
      self._pointers.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
      self._moved = false;

      if (self._pointers.size === 2 && isExplore) {
        var pts = Array.from(self._pointers.values());
        var dx = pts[0].x - pts[1].x;
        var dy = pts[0].y - pts[1].y;
        self._pinchStartDist = Math.hypot(dx, dy) || 1;
        self._pinchStartScale = self._scale;
        self._panStart = null;
        return;
      }

      var poly = targetPoly(ev);
      self._panStart = {
        x: ev.clientX,
        y: ev.clientY,
        tx: self._tx,
        ty: self._ty,
        poly: poly,
        time: Date.now()
      };

      if (!isExplore && !poly) {
        // мобильный: фон может вести в explore; на десктопе фон игнорируем
      }
    });

    stage.addEventListener('pointermove', function (ev) {
      if (self.destroyed) return;
      if (self._pointers.has(ev.pointerId)) {
        self._pointers.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
      }

      if (isExplore && self._pointers.size === 2) {
        var pts = Array.from(self._pointers.values());
        var dx = pts[0].x - pts[1].x;
        var dy = pts[0].y - pts[1].y;
        var dist = Math.hypot(dx, dy) || 1;
        var next = self._pinchStartScale * (dist / self._pinchStartDist);
        self._setScaleAt(next, (pts[0].x + pts[1].x) / 2, (pts[0].y + pts[1].y) / 2, viewport);
        self._moved = true;
        return;
      }

      if (!self._panStart) {
        // desktop hover
        if (!isExplore || self.exploring) {
          var hpoly = targetPoly(ev);
          if (hpoly) {
            self._setHover(stage, floorKey(hpoly.dataset.section, hpoly.dataset.floor), tooltipEl, ev.clientX, ev.clientY, viewport);
          } else {
            self._setHover(stage, null, tooltipEl);
          }
        }
        return;
      }

      var dx2 = ev.clientX - self._panStart.x;
      var dy2 = ev.clientY - self._panStart.y;
      if (Math.hypot(dx2, dy2) > 8) self._moved = true;

      if (isExplore && self.exploring) {
        self._tx = self._panStart.tx + dx2;
        self._ty = self._panStart.ty + dy2;
        self._clampTransform(viewport);
        self._applyExploreTransform();
        stage.classList.add('is-dragging');
      } else if (!isExplore && self._moved && allowsExploreMode()) {
        // жест pan → вход в explore (mobile, в т.ч. с полигона)
        try { stage.releasePointerCapture && stage.releasePointerCapture(ev.pointerId); } catch (err) { /* ignore */ }
        self._pointers.clear();
        self._panStart = null;
        self._enterExplore();
      }
    });

    function endPointer(ev) {
      if (self.destroyed) return;
      self._pointers.delete(ev.pointerId);
      stage.classList.remove('is-dragging');

      if (self._pointers.size < 2) {
        self._pinchStartDist = 0;
      }

      if (!self._panStart) return;
      var start = self._panStart;
      self._panStart = null;

      var shortTap = !self._moved && (Date.now() - start.time < 500);
      if (!shortTap) return;

      if (start.poly) {
        // Mobile вне explore: любой тап (в т.ч. по этажу) → сначала увеличение; этаж — только в explore.
        // Desktop: этаж кликабелен сразу.
        if (isExplore) {
          self._fireFloorClick(start.poly);
        } else if (allowsExploreMode()) {
          self._enterExplore();
        } else {
          self._fireFloorClick(start.poly);
        }
        return;
      }
      if (!isExplore && allowsExploreMode()) {
        self._enterExplore();
      }
    }

    stage.addEventListener('pointerup', endPointer);
    stage.addEventListener('pointercancel', endPointer);
    stage.addEventListener('pointerleave', function () {
      if (!self._panStart) self._setHover(stage, null, tooltipEl);
    });

    // keyboard on polygons
    stage.addEventListener('keydown', function (ev) {
      var poly = targetPoly(ev);
      if (!poly) return;
      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        if (isExplore) {
          self._fireFloorClick(poly);
        } else if (allowsExploreMode()) {
          self._enterExplore();
        } else {
          self._fireFloorClick(poly);
        }
      }
    });

    stage.addEventListener('focusin', function (ev) {
      var poly = targetPoly(ev);
      if (poly) {
        self._setHover(stage, floorKey(poly.dataset.section, poly.dataset.floor), tooltipEl,
          viewport.getBoundingClientRect().left + viewport.clientWidth / 2,
          viewport.getBoundingClientRect().top + 40,
          viewport);
      }
    });
    // wheel-зум на exploreViewport биндится один раз в _render() — сам viewport
    // переживает несколько входов/выходов из explore, а эта функция вызывается
    // на каждый _enterExplore() (новый stage-клон), поэтому здесь listener не добавляем.
  };

  /* ------------------------------------------------------------------ */
  /* Stage 3–5: навигация + fade + крошки + hash + план + карточка       */
  /* ------------------------------------------------------------------ */

  FacadeWidgetInstance.prototype._viewEl = function (view) {
    if (view === 'facade') return this._els.facadeView;
    if (view === 'chessboard') return this._els.chessboardView;
    if (view === 'floor') return this._els.floorView;
    if (view === 'card') return this._els.cardView;
    return null;
  };

  FacadeWidgetInstance.prototype._buildChessboardShell = function (parent) {
    var wrap = document.createElement('div');
    wrap.className = 'fw-chess';

    var modeHost = document.createElement('div');
    modeHost.className = 'fw-chess-mode-host';
    wrap.appendChild(modeHost);

    var main = document.createElement('div');
    main.className = 'fw-chess-main';

    var title = document.createElement('h2');
    title.className = 'fw-chess-title';
    title.textContent = this.locale.chooseResidence || 'Выбор резиденции';
    main.appendChild(title);

    var sections = document.createElement('div');
    sections.className = 'fw-chess-sections';
    main.appendChild(sections);

    var filter = document.createElement('div');
    filter.className = 'fw-chess-filter';
    main.appendChild(filter);

    var scroll = document.createElement('div');
    scroll.className = 'fw-chess-scroll';
    main.appendChild(scroll);

    var side = document.createElement('aside');
    side.className = 'fw-chess-side';
    var sideTitle = document.createElement('div');
    sideTitle.className = 'fw-chess-side-title';
    side.appendChild(sideTitle);
    var visual = document.createElement('img');
    visual.className = 'fw-chess-visual';
    visual.alt = '';
    side.appendChild(visual);

    wrap.appendChild(main);
    wrap.appendChild(side);

    var tip = document.createElement('div');
    tip.className = 'fw-chess-tip';
    tip.setAttribute('aria-hidden', 'true');
    wrap.appendChild(tip);

    parent.appendChild(wrap);
    this._chessEls = {
      wrap: wrap,
      modeHost: modeHost,
      main: main,
      title: title,
      sections: sections,
      filter: filter,
      scroll: scroll,
      side: side,
      sideTitle: sideTitle,
      visual: visual,
      tip: tip
    };
  };

  FacadeWidgetInstance.prototype._chessboardApiUrl = function (section) {
    var base = this.options.apiBase || detectApiBase();
    if (!base) return '';
    var q = 'act=chessboard_data&home_id=' + encodeURIComponent(this.options.homeId) +
      '&section=' + encodeURIComponent(section || 0);
    if (/[?&]ctr=/.test(base)) {
      return base + '&' + q;
    }
    var join = base.indexOf('?') >= 0 ? '&' : '?';
    return base + join + 'ctr=facades&' + q;
  };

  FacadeWidgetInstance.prototype._syncModeBar = function () {
    var visual = this._els.modeBtnVisual;
    var plan = this._els.modeBtnPlan;
    var bar = this._els.modeBar;
    if (!visual || !plan || !bar) return;
    var isChess = this.currentView === 'chessboard';
    visual.classList.toggle('is-active', !isChess);
    plan.classList.toggle('is-active', isChess);
    visual.setAttribute('aria-selected', isChess ? 'false' : 'true');
    plan.setAttribute('aria-selected', isChess ? 'true' : 'false');
    var host = null;
    if (isChess && this._chessEls && this._chessEls.modeHost) host = this._chessEls.modeHost;
    else if (this._els.facadeModeHost) host = this._els.facadeModeHost;
    if (host && bar.parentNode !== host) host.appendChild(bar);
  };

  FacadeWidgetInstance.prototype._showFacadeMode = function () {
    var self = this;
    if (this.destroyed) return;
    if (this.currentView === 'facade') {
      this._syncModeBar();
      return;
    }
    this._hideChessTip();
    this._transitionTo('facade', function () {
      self._navContext = Object.assign({}, self._navContext, { fromChessboard: false });
    });
    this._syncModeBar();
    this._resumeRevealHighlight();
  };

  FacadeWidgetInstance.prototype._showChessboard = function (section) {
    var self = this;
    if (this.destroyed || !this._chessboardEnabled) return;
    if (this.exploring) this._exitExplore();
    this._applyScrollRevealIndex(-1);
    var sec = section || this._chessSection || (this.data && this.data.sections && this.data.sections[0] && this.data.sections[0].id) || 1;
    this._chessSection = sec;
    this._navPush('chessboard', { section: sec, fromChessboard: true, floor: null, apartmentNum: null });
    this._syncModeBar();
    this._loadChessboard(sec);
  };

  FacadeWidgetInstance.prototype._loadChessboard = function (section) {
    var self = this;
    var els = this._chessEls;
    if (!els || !els.scroll) return Promise.resolve(null);
    els.scroll.innerHTML = '<div class="fw-msg is-loading">' + this.locale.loading + '</div>';
    this._hideChessTip();
    var url = this._chessboardApiUrl(section);
    if (!url) {
      els.scroll.innerHTML = '<div class="fw-msg is-error">' + this.locale.error + '</div>';
      return Promise.resolve(null);
    }
    return fetch(url, { credentials: 'omit', cache: 'no-cache' }).then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    }).then(function (json) {
      if (self.destroyed || self.currentView !== 'chessboard') return null;
      if (!json || json.success === false) {
        els.scroll.innerHTML = '<div class="fw-msg is-error">' + ((json && json.message) || self.locale.error) + '</div>';
        return null;
      }
      self._chessData = json;
      self._chessSection = (json.section && json.section.id) || section;
      if (json.statusColors) {
        self._chessStatusColors = Object.assign({}, self._chessStatusColors, json.statusColors);
        self._applyHighlightCssVars();
      }
      self._renderChessboard(json);
      self._writeUrlState();
      return json;
    }).catch(function (err) {
      if (self.destroyed) return null;
      els.scroll.innerHTML = '<div class="fw-msg is-error">' + self.locale.error + (err && err.message ? ' (' + err.message + ')' : '') + '</div>';
      return null;
    });
  };

  FacadeWidgetInstance.prototype._renderChessboard = function (data) {
    var self = this;
    var els = this._chessEls;
    if (!els) return;

    var caption = (data.section && data.section.caption) || '';
    els.sideTitle.textContent = (this.locale.tower || 'Башня') + (caption ? ' «' + caption + '»' : '');
    if (data.visualUrl) {
      els.visual.src = data.visualUrl;
      els.visual.style.display = '';
    } else {
      els.visual.removeAttribute('src');
      els.visual.style.display = 'none';
    }

    els.sections.innerHTML = '';
    (data.sections || []).forEach(function (sec, idx) {
      if (idx > 0) {
        var sep = document.createElement('span');
        sep.className = 'fw-chess-sec-sep';
        sep.textContent = '|';
        sep.setAttribute('aria-hidden', 'true');
        els.sections.appendChild(sep);
      }
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'fw-chess-sec' + ((sec.id === self._chessSection) ? ' is-active' : '');
      btn.textContent = (self.locale.tower || 'Башня') + ' «' + (sec.caption || sec.id) + '»';
      btn.addEventListener('click', function () {
        if (sec.id === self._chessSection) return;
        self._chessSection = sec.id;
        self._navContext.section = sec.id;
        self._loadChessboard(sec.id);
      });
      els.sections.appendChild(btn);
    });

    els.filter.innerHTML = '';
    var flabel = document.createElement('span');
    flabel.className = 'fw-chess-filter__label';
    flabel.textContent = this.locale.showResidences || 'Показать резиденции:';
    els.filter.appendChild(flabel);
    [1, 2, 3, 4].forEach(function (n) {
      var lab = document.createElement('label');
      lab.className = 'fw-chess-chk';
      var inp = document.createElement('input');
      inp.type = 'checkbox';
      inp.checked = !!self._chessRoomFilter[n];
      inp.addEventListener('change', function () {
        self._chessRoomFilter[n] = !!inp.checked;
        self._applyChessRoomFilter();
      });
      var span = document.createElement('span');
      span.textContent = n + 'K';
      lab.appendChild(inp);
      lab.appendChild(span);
      els.filter.appendChild(lab);
    });

    els.scroll.innerHTML = '';
    var table = document.createElement('table');
    table.className = 'fw-chess-table';
    var tbody = document.createElement('tbody');
    (data.rows || []).forEach(function (row) {
      var tr = document.createElement('tr');
      var tdFloor = document.createElement('td');
      tdFloor.className = 'fw-chess-floor';
      tdFloor.textContent = String(row.floor);
      tr.appendChild(tdFloor);
      (row.cells || []).forEach(function (cell) {
        var td = document.createElement('td');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'fw-chess-cell';
        if (cell.empty) {
          btn.classList.add('is-empty');
          btn.disabled = true;
          btn.tabIndex = -1;
          btn.setAttribute('aria-hidden', 'true');
        } else {
          btn.dataset.statusKey = cell.statusKey || 'free';
          btn.dataset.apartmentNum = String(cell.apartmentNum || '');
          btn.dataset.apartamentId = String(cell.apartamentId || '');
          btn.dataset.rooms = String(cell.rooms || '');
          btn.dataset.area = cell.area != null ? String(cell.area) : '';
          btn.dataset.floor = String(row.floor);
          btn.dataset.section = String(self._chessSection);
          btn.dataset.statusLabel = cell.statusLabel || '';
          btn.dataset.imageUrl = cell.imageUrl || '';
          btn.dataset.label = cell.label || (cell.rooms ? cell.rooms + 'K' : '');
          btn.textContent = btn.dataset.label || '';
          btn.setAttribute('aria-label', '№' + cell.apartmentNum + ' ' + (cell.statusLabel || ''));
        }
        td.appendChild(btn);
        tr.appendChild(td);
      });
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    els.scroll.appendChild(table);

    if (!els._bound) {
      els._bound = true;
      els.scroll.addEventListener('pointerover', function (ev) {
        if (!isDesktopUi()) return;
        var cell = ev.target && ev.target.closest ? ev.target.closest('.fw-chess-cell') : null;
        if (!cell || !els.scroll.contains(cell) || cell.classList.contains('is-empty') || cell.classList.contains('is-filtered-out')) {
          self._hideChessTip();
          return;
        }
        self._showChessTip(cell, ev);
      });
      els.scroll.addEventListener('pointerout', function (ev) {
        var related = ev.relatedTarget;
        if (related && els.scroll.contains(related) && related.closest && related.closest('.fw-chess-cell')) return;
        self._hideChessTip();
      });
      els.scroll.addEventListener('click', function (ev) {
        var cell = ev.target && ev.target.closest ? ev.target.closest('.fw-chess-cell') : null;
        if (!cell || !els.scroll.contains(cell)) return;
        if (cell.classList.contains('is-empty') || cell.classList.contains('is-filtered-out')) return;
        self._fireChessCellClick(cell);
      });
    }

    this._applyChessRoomFilter();
  };

  FacadeWidgetInstance.prototype._applyChessRoomFilter = function () {
    var els = this._chessEls;
    if (!els || !els.scroll) return;
    var filter = this._chessRoomFilter;
    var nodes = els.scroll.querySelectorAll('.fw-chess-cell:not(.is-empty)');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      var rooms = parseInt(el.dataset.rooms, 10) || 0;
      var active = rooms >= 1 && rooms <= 4 ? !!filter[rooms] : true;
      el.classList.toggle('is-filtered-out', !active);
    }
    this._hideChessTip();
  };

  FacadeWidgetInstance.prototype._showChessTip = function (cell, ev) {
    var tip = this._chessEls && this._chessEls.tip;
    var wrap = this._chessEls && this._chessEls.wrap;
    if (!tip || !wrap || !cell) return;
    var key = cell.dataset.statusKey || 'free';
    tip.className = 'fw-chess-tip is-visible';
    tip.dataset.statusKey = key;
    tip.innerHTML = '';
    if (key === 'reserved' || key === 'sold') {
      tip.classList.add('is-chip');
      tip.textContent = cell.dataset.statusLabel
        || (key === 'reserved' ? (this.locale.statusReserved || 'Бронь') : (this.locale.statusSold || 'Продана'));
    } else {
      tip.classList.add('is-preview');
      var code = document.createElement('span');
      code.className = 'fw-chess-tip__code';
      code.textContent = '№' + (cell.dataset.apartmentNum || '');
      var spec = document.createElement('span');
      spec.className = 'fw-chess-tip__spec';
      var parts = [];
      if (cell.dataset.label) parts.push(cell.dataset.label);
      if (cell.dataset.area) parts.push(formatAreaRu(cell.dataset.area) + ' м²');
      spec.textContent = parts.join(' | ');
      tip.appendChild(code);
      if (spec.textContent) tip.appendChild(spec);
      if (cell.dataset.imageUrl) {
        var img = document.createElement('img');
        img.className = 'fw-chess-tip__img';
        img.src = cell.dataset.imageUrl;
        img.alt = '';
        tip.appendChild(img);
      }
    }
    var rect = wrap.getBoundingClientRect();
    var x = 0;
    var y = 0;
    if (cell.getBoundingClientRect) {
      var cr = cell.getBoundingClientRect();
      x = cr.left + cr.width / 2 - rect.left;
      y = cr.top - rect.top;
    } else if (ev) {
      x = ev.clientX - rect.left;
      y = ev.clientY - rect.top;
    }
    tip.style.left = x + 'px';
    tip.style.top = y + 'px';
  };

  FacadeWidgetInstance.prototype._hideChessTip = function () {
    var tip = this._chessEls && this._chessEls.tip;
    if (!tip) return;
    tip.classList.remove('is-visible');
    tip.innerHTML = '';
  };

  FacadeWidgetInstance.prototype._fireChessCellClick = function (cell) {
    this._hideChessTip();
    var payload = {
      homeId: this.options.homeId,
      section: parseInt(cell.dataset.section, 10) || this._chessSection || 1,
      floor: parseInt(cell.dataset.floor, 10) || 0,
      apartamentId: parseInt(cell.dataset.apartamentId, 10) || 0,
      apartmentNum: parseInt(cell.dataset.apartmentNum, 10) || 0,
      displayCode: cell.dataset.apartmentNum || '',
      rooms: cell.dataset.rooms || '',
      area: cell.dataset.area || '',
      fromChessboard: true
    };
    if (typeof this.options.onApartmentClick === 'function') {
      var result = this.options.onApartmentClick(payload);
      if (result && result.preventDefault) return;
    }
    if (!payload.apartmentNum && !payload.apartamentId) return;
    this._navContext.fromChessboard = true;
    this._openApartmentCard(payload);
  };

  FacadeWidgetInstance.prototype._applyHighlightCssVars = function () {
    var root = this._els.root;
    if (!root) return;
    var f = this._facadeHighlight;
    var a = this._apartmentHighlight;
    root.style.setProperty('--fw-facade-hl-color', f.color);
    root.style.setProperty('--fw-facade-hl-opacity', String(f.opacity));
    root.style.setProperty('--fw-facade-idle-opacity', String(f.idleOpacity));
    root.style.setProperty('--fw-facade-reveal-opacity', String(f.revealOpacity));
    /* обводка = тот же цвет, чуть прозрачнее заливки — мягкий край */
    root.style.setProperty('--fw-facade-stroke-opacity', String(Math.max(0, Math.min(1, f.opacity * 0.7))));
    root.style.setProperty('--fw-facade-reveal-stroke-opacity', String(Math.max(0, Math.min(1, f.revealOpacity * 0.7))));
    root.style.setProperty('--fw-apt-hl-color', a.color);
    root.style.setProperty('--fw-apt-hl-opacity', String(a.opacity));
    root.style.setProperty('--fw-apt-hover-opacity', String(a.hoverOpacity));
    var c = this._chessStatusColors || {};
    root.style.setProperty('--fw-chess-free', c.free || CHESS_FREE);
    root.style.setProperty('--fw-chess-reserved', c.reserved || CHESS_RESERVED);
    root.style.setProperty('--fw-chess-sold', c.sold || CHESS_SOLD);
    root.style.setProperty('--fw-chess-filtered', c.filteredOut || CHESS_FILTERED);
  };

  FacadeWidgetInstance.prototype._clearViewFadeStyles = function () {
    ['facadeView', 'chessboardView', 'floorView', 'cardView'].forEach(function (key) {
      var el = this._els[key];
      if (!el) return;
      el.classList.remove('is-fading-out', 'is-fading-in');
      el.style.opacity = '';
      el.style.transition = '';
    }, this);
  };

  /**
   * Плавный переход между экранами. currentView меняется сразу (для API/fetch),
   * визуальная смена классов — после fade-out (или мгновенно).
   */
  FacadeWidgetInstance.prototype._transitionTo = function (nextView, mutate) {
    var self = this;
    var fromView = this.currentView;
    if (typeof mutate === 'function') mutate();
    this.currentView = nextView;
    if (nextView === 'floor') this.planOpen = true;
    if (nextView === 'card') this.planOpen = false;
    if (nextView === 'facade' || nextView === 'chessboard') this.planOpen = false;

    document.removeEventListener('keydown', this._onNavKeyDown);
    if (nextView !== 'facade' && nextView !== 'chessboard') {
      document.addEventListener('keydown', this._onNavKeyDown);
    }

      var finishVisual = function () {
      self._syncUiClasses();
      self._reparentPlanLayer();
      self._updateBreadcrumbs();
      self._writeUrlState();
      if (nextView === 'floor') {
        self._layoutFloorView();
        if (self._planImgW) self._planFitAndCenter(self._planEls.viewport, self._planEls);
      }
      if (typeof self.options.onNavigate === 'function') {
        try { self.options.onNavigate(self.getState()); } catch (e) { /* host */ }
      }
    };

    var ms = this._fadeMs;
    if (!ms || prefersReducedMotion() || fromView === nextView || !this._els.body) {
      clearTimeout(this._fadeTimer);
      clearTimeout(this._fadeTimer2);
      this._clearViewFadeStyles();
      this._fadeLock = false;
      finishVisual();
      return;
    }

    clearTimeout(this._fadeTimer);
    clearTimeout(this._fadeTimer2);
    this._fadeLock = true;
    var body = this._els.body;
    var fromEl = this._viewEl(fromView);
    body.style.minHeight = body.offsetHeight + 'px';

    if (fromEl) {
      fromEl.style.transition = 'opacity ' + ms + 'ms ease';
      fromEl.classList.add('is-fading-out');
    }

    this._fadeTimer = setTimeout(function () {
      if (self.destroyed) return;
      finishVisual();
      var toEl = self._viewEl(nextView);
      if (fromEl) fromEl.classList.remove('is-fading-out');
      if (toEl) {
        toEl.style.transition = 'opacity ' + ms + 'ms ease';
        toEl.classList.add('is-fading-in');
        void toEl.offsetWidth;
        toEl.classList.remove('is-fading-in');
      }
      self._fadeTimer2 = setTimeout(function () {
        if (self.destroyed) return;
        self._clearViewFadeStyles();
        body.style.minHeight = '';
        self._fadeLock = false;
      }, ms);
    }, ms);
  };

  FacadeWidgetInstance.prototype.getState = function () {
    var ctx = this._navContext || {};
    var card = this._cardData || {};
    return {
      view: this.currentView,
      homeId: this.options.homeId,
      section: ctx.section || this._chessSection || (this._planData && this._planData.section) || null,
      floor: ctx.floor || (this._planData && this._planData.floor) || null,
      apartmentNum: ctx.apartmentNum || card.apartmentNum || null,
      apartamentId: ctx.apartamentId || card.apartamentId || null,
      displayCode: ctx.displayCode || card.displayCode || ''
    };
  };

  FacadeWidgetInstance.prototype._updateBreadcrumbs = function () {
    var nav = this._els.crumbs;
    if (!nav) return;
    nav.innerHTML = '';
    var cfg = this._breadcrumbs;
    var view = this.currentView;
    if (view === 'facade' || view === 'chessboard') {
      nav.classList.remove('is-visible');
      return;
    }

    var state = this.getState();
    var items = [];
    var self = this;

    if (cfg.home.show) {
      items.push({
        key: 'home',
        label: (this.data && this.data.title) || this.locale.crumbHome || 'Дом',
        clickable: cfg.home.clickable,
        current: false,
        action: function () { self._crumbGoHome(); }
      });
    }
    if (cfg.section.show && state.section) {
      var secLabel = this.sectionCaptions[String(state.section)]
        || ((this.locale.section || 'секция') + ' ' + state.section);
      items.push({
        key: 'section',
        label: secLabel,
        clickable: cfg.section.clickable && view !== 'floor',
        current: view === 'floor',
        action: function () { self._crumbGoHome(); }
      });
    }
    if (cfg.floor.show && state.floor && view !== 'floor') {
      items.push({
        key: 'floor',
        label: (this.locale.floorLabel || 'Этаж') + ' ' + state.floor,
        clickable: cfg.floor.clickable && view === 'card',
        current: false,
        action: function () { self._crumbGoFloor(); }
      });
    }
    if (cfg.apartment.show && view === 'card') {
      var aptLabel = state.apartmentNum
        ? ('№' + state.apartmentNum)
        : (this.locale.residence || 'Квартира');
      items.push({
        key: 'apartment',
        label: aptLabel,
        clickable: cfg.apartment.clickable,
        current: true,
        action: function () { /* already here */ }
      });
    }

    if (!items.length) {
      nav.classList.remove('is-visible');
      return;
    }
    nav.classList.add('is-visible');

    // На плане и карточке крошки — под «Назад» вместо заголовка.
    var planHead = this._planEls && this._planEls.head;
    var cardHost = null;
    if (view === 'card') {
      cardHost = isDesktopUi()
        ? (this._cardEls && this._cardEls.crumbsHost)
        : ((this._cardEls && this._cardEls.crumbsHostMobile) || (this._cardEls && this._cardEls.crumbsHost));
    }
    var root = this._els.root;
    var body = this._els.body;
    if (view === 'floor' && planHead) {
      if (nav.parentNode !== planHead) planHead.appendChild(nav);
    } else if (view === 'card' && cardHost) {
      if (nav.parentNode !== cardHost) cardHost.appendChild(nav);
    } else if (root && body) {
      if (nav.parentNode !== root || nav.nextSibling !== body) {
        root.insertBefore(nav, body);
      }
    }

    items.forEach(function (item, idx) {
      if (idx > 0) {
        var sep = document.createElement('span');
        sep.className = 'fw-crumb-sep';
        sep.setAttribute('aria-hidden', 'true');
        sep.textContent = '/';
        nav.appendChild(sep);
      }
      var el;
      if (item.clickable && !item.current) {
        el = document.createElement('button');
        el.type = 'button';
        el.className = 'fw-crumb is-link';
        el.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          item.action();
        });
      } else {
        el = document.createElement('span');
        el.className = 'fw-crumb' + (item.current ? ' is-current' : '');
        if (item.current) el.setAttribute('aria-current', 'page');
      }
      el.textContent = item.label;
      nav.appendChild(el);
    });
  };

  FacadeWidgetInstance.prototype._crumbGoHome = function () {
    if (this.currentView === 'facade') return;
    if (this.currentView === 'chessboard') {
      this._showFacadeMode();
      return;
    }
    if (this._navContext && this._navContext.fromChessboard) {
      this._showChessboard(this._navContext.section || this._chessSection);
      return;
    }
    this._closeFloorPlan(false);
  };

  FacadeWidgetInstance.prototype._crumbGoFloor = function () {
    if (this.currentView !== 'card') return;
    var self = this;
    if (this._navContext && this._navContext.fromChessboard) {
      this._showChessboard(this._navContext.section || this._chessSection);
      return;
    }
    this._transitionTo('floor', function () {
      self._cardData = null;
      self.planOpen = true;
    });
  };

  FacadeWidgetInstance.prototype._hostHashIsOurs = function () {
    var h = String(location.hash || '');
    if (!h || h === '#') return true;
    if (/^#fw=/.test(h)) return true;
    if (/^#\d+\.\d+/.test(h)) return true;
    if (/^#c\.\d+/i.test(h)) return true;
    return false;
  };

  FacadeWidgetInstance.prototype._writeUrlState = function () {
    if (!this._urlState || this._urlApplying || this.destroyed) return;
    if (!this._hostHashIsOurs()) return;
    var state = this.getState();
    var value = '';
    if (this.currentView === 'chessboard') {
      var sec = state.section || this._chessSection;
      if (sec) value = 'c.' + String(sec);
    } else if (this.currentView === 'floor' || this.currentView === 'card') {
      value = buildFwHashValue({
        section: state.section,
        floor: state.floor,
        apartmentNum: this.currentView === 'card' ? state.apartmentNum : null
      });
    }
    var target = value ? ('#fw=' + value) : '';
    var cur = location.hash || '';
    if (cur === target) return;
    if (!target && (!cur || cur === '#')) return;
    this._urlApplying = true;
    try {
      var url = location.pathname + location.search + target;
      history.replaceState(null, '', url);
    } catch (e) {
      try { location.hash = target ? target.slice(1) : ''; } catch (e2) { /* ignore */ }
    }
    var self = this;
    setTimeout(function () { self._urlApplying = false; }, 0);
  };

  FacadeWidgetInstance.prototype._bindUrlState = function () {
    if (!this._urlState) return;
    window.addEventListener('hashchange', this._onHashChange);
  };

  FacadeWidgetInstance.prototype._onHashChange = function () {
    if (!this._urlState || this._urlApplying || this.destroyed) return;
    this._applyUrlStateOnLoad();
  };

  FacadeWidgetInstance.prototype._applyUrlStateOnLoad = function () {
    if (!this._urlState || this.destroyed) return;
    var parsed = parseFwHash(location.hash);
    if (!parsed) {
      if (this.currentView !== 'facade' && this.currentView !== 'chessboard' && this._hostHashIsOurs()) {
        this._crumbGoHome();
      }
      return;
    }
    this._urlApplying = true;
    var self = this;
    if (parsed.mode === 'chessboard') {
      this._showChessboard(parsed.section);
      this._urlApplying = false;
      this._writeUrlState();
      return;
    }
    var payload = { section: parsed.section, floor: parsed.floor };
    Promise.resolve(this._openFloorPlan(payload)).then(function () {
      if (self.destroyed) return;
      if (parsed.apartmentNum) {
        self._openApartmentCard({
          section: parsed.section,
          floor: parsed.floor,
          apartmentNum: parsed.apartmentNum,
          apartamentId: 0,
          displayCode: '',
          rooms: '',
          area: ''
        });
      }
      self._urlApplying = false;
      self._writeUrlState();
    }).catch(function () {
      self._urlApplying = false;
    });
  };

  FacadeWidgetInstance.prototype._syncUiClasses = function () {
    if (!this._els.root) return;
    var root = this._els.root;
    if (allowsExploreMode()) {
      root.classList.add('is-mobile-ui');
    } else {
      root.classList.remove('is-mobile-ui');
      if (this.exploring) this._exitExplore();
    }
    root.classList.toggle('is-desktop', isDesktopUi());
    root.classList.remove('is-view-facade', 'is-view-chessboard', 'is-view-floor', 'is-view-card');
    root.classList.add('is-view-' + this.currentView);
    root.classList.toggle('is-plan-open', this.currentView === 'floor' || this.currentView === 'card');
    this._syncModeBar();
  };

  FacadeWidgetInstance.prototype._reparentPlanLayer = function () {
    var layer = this._planEls.layer;
    if (!layer) return;
    var target;
    if (isDesktopUi()) {
      target = this._els.floorView;
    } else if (this.exploring && this.currentView === 'floor') {
      target = this._els.explorePlanHost;
    } else {
      target = this._els.floorView;
    }
    if (target && layer.parentNode !== target) {
      target.appendChild(layer);
    }
  };

  FacadeWidgetInstance.prototype._navPush = function (view, ctx) {
    var self = this;
    this._transitionTo(view, function () {
      self._navContext = Object.assign({}, self._navContext, ctx || {});
    });
  };

  FacadeWidgetInstance.prototype._navPop = function (fromDestroy) {
    if (fromDestroy) {
      if (this.currentView === 'card' || this.currentView === 'floor') {
        this.currentView = 'facade';
        this.planOpen = false;
        this._navContext = {};
        this._cardData = null;
        this._syncUiClasses();
      }
      return;
    }
    if (this.currentView === 'card') {
      this._crumbGoFloor();
      return;
    }
    if (this.currentView === 'floor') {
      this._closeFloorPlan(false);
      return;
    }
  };

  FacadeWidgetInstance.prototype._onNavKeyDown = function (ev) {
    if (ev.key === 'Escape') {
      ev.preventDefault();
      this._navPop();
    }
  };

  FacadeWidgetInstance.prototype._buildPlanLayer = function (parent) {
    var self = this;
    var layer = document.createElement('div');
    layer.className = 'fw-plan-layer';
    layer.setAttribute('aria-hidden', 'true');

    var head = document.createElement('div');
    head.className = 'fw-plan-head';

    var backBtn = document.createElement('button');
    backBtn.type = 'button';
    backBtn.className = 'fw-plan-back';
    backBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>' + (this.locale.back || 'Назад') + '</span>';
    backBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      self._navPop();
    });
    head.appendChild(backBtn);

    layer.appendChild(head);

    var viewport = document.createElement('div');
    viewport.className = 'fw-plan-viewport';
    layer.appendChild(viewport);

    parent.appendChild(layer);

    viewport.addEventListener('wheel', function (ev) {
      if (!self.planOpen || self.currentView !== 'floor') return;
      if (!self._isPlanZoomEnabled()) return;
      ev.preventDefault();
      var factor = ev.deltaY < 0 ? 1.08 : 1 / 1.08;
      self._setPlanScaleAt(self._planScale * factor, ev.clientX, ev.clientY, viewport);
    }, { passive: false });

    this._planEls.layer = layer;
    this._planEls.head = head;
    this._planEls.viewport = viewport;
    this._planEls.backBtn = backBtn;
  };

  FacadeWidgetInstance.prototype._buildCardShell = function (parent) {
    var shell = document.createElement('div');
    shell.className = 'fw-card';
    shell.setAttribute('id', parent === this._els.cardMobile ? 'fw-printable-mobile' : 'fw-printable');
    parent.appendChild(shell);
    if (parent === this._els.cardView) {
      this._cardEls.shell = shell;
    } else {
      this._cardEls.shellMobile = shell;
    }
  };

  FacadeWidgetInstance.prototype._activeCardShell = function () {
    if (isDesktopUi()) return this._cardEls.shell;
    return this._cardEls.shellMobile || this._cardEls.shell;
  };

  /**
   * Клон карточки для печати / PDF: без формы, вкладок и UI-кнопок.
   */
  FacadeWidgetInstance.prototype._buildPrintableCardClone = function () {
    var shell = this._activeCardShell();
    if (!shell) return null;

    var clone = shell.cloneNode(true);
    clone.removeAttribute('id');

    var crumbs = this._els.crumbs;
    var crumbsHost = clone.querySelector('.fw-card-crumbs-host');
    if (crumbsHost && crumbs) {
      crumbsHost.innerHTML = '';
      crumbsHost.appendChild(crumbs.cloneNode(true));
      var clonedCrumbs = crumbsHost.querySelector('.fw-crumbs');
      if (clonedCrumbs) clonedCrumbs.classList.add('is-visible');
    }

    Array.prototype.forEach.call(clone.querySelectorAll(
      '.fw-card-head-row, .fw-card-back, .fw-card-tools, .fw-card-tabs, .fw-card-form, .fw-card-consent, .fw-card-submit, .fw-card-msg, .fw-card-floor-vp, .fw-card-panel[data-panel="floor"]'
    ), function (el) {
      if (el && el.parentNode) el.parentNode.removeChild(el);
    });

    Array.prototype.forEach.call(clone.querySelectorAll('.fw-card-panel'), function (panel) {
      if (panel.getAttribute('data-panel') === 'layout') {
        panel.classList.add('is-active');
        panel.style.display = 'flex';
        panel.style.minHeight = '0';
      } else if (panel.parentNode) {
        panel.parentNode.removeChild(panel);
      }
    });

    Array.prototype.forEach.call(clone.querySelectorAll('.fw-card-pln-img'), function (img) {
      img.removeAttribute('width');
      img.removeAttribute('height');
      img.style.maxHeight = '165mm';
      img.style.width = 'auto';
      img.style.height = 'auto';
    });

    var title = (this.locale && this.locale.residence) || 'Квартира';
    var apt = this._cardData && this._cardData.apartmentNum;
    if (apt) title += ' №' + apt;

    var fileBase = 'kvartira';
    if (apt) fileBase += '-' + apt;

    return { clone: clone, title: title, fileName: fileBase + '.pdf' };
  };

  /**
   * Печать только карточки: скрытый iframe (без пустого popup),
   * вёрстка колонкой как на мобилке, план на всю ширину.
   */
  FacadeWidgetInstance.prototype._printApartmentCard = function () {
    var payload = this._buildPrintableCardClone();
    if (!payload) return;

    var prev = document.getElementById('fw-print-frame');
    if (prev && prev.parentNode) prev.parentNode.removeChild(prev);

    var iframe = document.createElement('iframe');
    iframe.id = 'fw-print-frame';
    iframe.setAttribute('aria-hidden', 'true');
    iframe.setAttribute('title', 'print');
    iframe.style.cssText = 'position:fixed;left:0;top:0;width:0;height:0;border:0;opacity:0;pointer-events:none;';
    document.body.appendChild(iframe);

    var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
    if (!doc) {
      if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
      return;
    }

    doc.open();
    doc.write(
      '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">' +
      '<meta name="viewport" content="width=device-width, initial-scale=1">' +
      '<title>' + escapeHtmlAttr(payload.title) + '</title>' +
      '<style>' + PRINT_DOC_CSS + '</style></head><body>' +
      '<div class="fw-print-sheet">' + payload.clone.outerHTML + '</div>' +
      '</body></html>'
    );
    doc.close();

    var win = iframe.contentWindow;
    var done = false;

    function cleanup() {
      setTimeout(function () {
        if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
      }, 400);
    }

    function doPrint() {
      if (done) return;
      done = true;
      try {
        if (win) {
          win.focus();
          win.print();
        }
      } catch (err) { /* ignore */ }
      cleanup();
    }

    waitForImages(doc.body || doc.documentElement, 2000).then(function () {
      setTimeout(doPrint, 60);
    });
  };

  /**
   * Скачать PDF карточки (та же вёрстка, что для печати) — без диалога печати.
   */
  FacadeWidgetInstance.prototype._downloadApartmentCardPdf = function () {
    var self = this;
    if (self._pdfBusy) return;
    var payload = self._buildPrintableCardClone();
    if (!payload) return;

    self._pdfBusy = true;

    var host = document.createElement('div');
    host.id = 'fw-pdf-host';
    host.setAttribute('aria-hidden', 'true');
    host.style.cssText = 'position:fixed;left:-12000px;top:0;width:794px;max-width:794px;background:#fff;z-index:-1;pointer-events:none;';

    var style = document.createElement('style');
    style.textContent = PRINT_DOC_CSS;
    host.appendChild(style);

    var sheet = document.createElement('div');
    sheet.className = 'fw-print-sheet';
    sheet.appendChild(payload.clone);
    host.appendChild(sheet);
    document.body.appendChild(host);

    function cleanupHost() {
      if (host.parentNode) host.parentNode.removeChild(host);
      self._pdfBusy = false;
    }

    function failAndPrint() {
      cleanupHost();
      self._printApartmentCard();
    }

    Promise.resolve()
      .then(function () {
        return waitForImages(sheet, 4000);
      })
      .then(function () {
        return loadScriptOnce(resolveExportLib('html2canvas.min.js'));
      })
      .then(function () {
        return loadScriptOnce(resolveExportLib('jsPDF/jspdf.umd.min.js'));
      })
      .then(function () {
        if (typeof html2canvas !== 'function') {
          throw new Error('html2canvas unavailable');
        }
        var JsPDF = (window.jspdf && window.jspdf.jsPDF) || window.jsPDF;
        if (typeof JsPDF !== 'function') {
          throw new Error('jsPDF unavailable');
        }
        return html2canvas(sheet, {
          scale: 2,
          useCORS: true,
          allowTaint: false,
          backgroundColor: '#ffffff',
          logging: false
        }).then(function (canvas) {
          var pdf = new JsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
          var pageW = pdf.internal.pageSize.getWidth();
          var pageH = pdf.internal.pageSize.getHeight();
          var margin = 10;
          var maxW = pageW - margin * 2;
          var maxH = pageH - margin * 2;
          var imgW = maxW;
          var imgH = (canvas.height * imgW) / canvas.width;
          if (imgH > maxH) {
            imgH = maxH;
            imgW = (canvas.width * imgH) / canvas.height;
          }
          var x = margin + (maxW - imgW) / 2;
          var y = margin;
          var imgData = canvas.toDataURL('image/jpeg', 0.92);
          pdf.addImage(imgData, 'JPEG', x, y, imgW, imgH);
          pdf.save(payload.fileName);
        });
      })
      .then(function () {
        cleanupHost();
      })
      .catch(function () {
        failAndPrint();
      });
  };

  FacadeWidgetInstance.prototype._floorPlanApiUrl = function (section, floor) {
    var base = this.options.apiBase || detectApiBase();
    if (!base) return '';
    var q = 'act=floor_plan_data&home_id=' + encodeURIComponent(this.options.homeId) +
      '&section=' + encodeURIComponent(section) + '&floor=' + encodeURIComponent(floor);
    if (/[?&]ctr=/.test(base)) {
      return base + '&' + q;
    }
    var join = base.indexOf('?') >= 0 ? '&' : '?';
    return base + join + 'ctr=facades&' + q;
  };

  FacadeWidgetInstance.prototype._apartmentCardApiUrl = function (apartmentNum, apartamentId) {
    var base = this.options.apiBase || detectApiBase();
    if (!base) return '';
    var q = 'act=apartment_card_data&home_id=' + encodeURIComponent(this.options.homeId) +
      '&apartment_num=' + encodeURIComponent(apartmentNum || 0);
    if (apartamentId) {
      q += '&apartament_id=' + encodeURIComponent(apartamentId);
    }
    if (/[?&]ctr=/.test(base)) {
      return base + '&' + q;
    }
    var join = base.indexOf('?') >= 0 ? '&' : '?';
    return base + join + 'ctr=facades&' + q;
  };

  FacadeWidgetInstance.prototype._openFloorPlan = function (payload) {
    if (this.destroyed) return Promise.resolve(null);
    if (this.planOpen && this.currentView !== 'facade') {
      this._navContext = Object.assign({}, this._navContext, payload || {});
      this._layoutFloorView();
      return this._loadFloorPlan(payload);
    }
    this._applyScrollRevealIndex(-1);
    this._navContext.floorPayload = payload;
    this._navPush('floor', payload);

    if (!isDesktopUi()) {
      if (!this.exploring) {
        this._enterExplore();
      }
      this._reparentPlanLayer();
    }

    if (this._planEls.layer) this._planEls.layer.setAttribute('aria-hidden', 'false');
    if (this._planEls.backBtn) this._planEls.backBtn.focus();
    var self = this;
    // После explore/reparent высота viewport стабилизируется на следующем кадре
    requestAnimationFrame(function () {
      if (self.destroyed) return;
      self._layoutFloorView();
      if (self._planImgW) self._planFitAndCenter(self._planEls.viewport, self._planEls);
    });
    return this._loadFloorPlan(payload);
  };

  FacadeWidgetInstance.prototype._closeFloorPlan = function (fromDestroy) {
    if (this.currentView !== 'floor' && this.currentView !== 'card' && !this.planOpen) return;
    var self = this;
    var finish = function () {
      self.planOpen = false;
      self._navContext = {};
      self._cardData = null;
      if (self._planEls.layer) self._planEls.layer.setAttribute('aria-hidden', 'true');
      if (self._planEls.viewport) self._planEls.viewport.innerHTML = '';
      self._planData = null;
      self._planPointers.clear();
      document.removeEventListener('keydown', self._onNavKeyDown);
      document.removeEventListener('keydown', self._onPlanKeyDown);
    };
    if (fromDestroy) {
      this.currentView = 'facade';
      finish();
      this._syncUiClasses();
      return;
    }
    this._transitionTo('facade', finish);
    this._resumeRevealHighlight();
  };

  FacadeWidgetInstance.prototype._onPlanKeyDown = function (ev) {
    if (ev.key === 'Escape' && this.planOpen) {
      ev.preventDefault();
      this._navPop();
    }
  };

  FacadeWidgetInstance.prototype._setPlanMessage = function (text, type, viewport) {
    var vp = viewport || this._planEls.viewport;
    if (!vp) return;
    vp.innerHTML = '';
    var msg = document.createElement('div');
    msg.className = 'fw-msg' + (type === 'error' ? ' is-error' : (type === 'loading' ? ' is-loading' : ''));
    if (type === 'error') msg.setAttribute('role', 'alert');
    msg.textContent = text;
    vp.appendChild(msg);
  };

  FacadeWidgetInstance.prototype._loadFloorPlan = function (payload, targetEls, options) {
    var self = this;
    options = options || {};
    var els = targetEls || this._planEls;
    this._setPlanMessage(this.locale.loading, 'loading', els.viewport);

    var url = this._floorPlanApiUrl(payload.section, payload.floor);
    if (!url) {
      this._setPlanMessage(this.locale.error, 'error', els.viewport);
      return Promise.resolve(null);
    }

    return fetch(url, { credentials: 'omit', cache: 'no-cache' }).then(function (res) {
      return res.json();
    }).then(function (json) {
      if (self.destroyed) return null;
      if (!options.forCard && self.currentView !== 'floor') return null;
      if (!json || json.success === false) {
        self._setPlanMessage((json && json.message) || self.locale.error, 'error', els.viewport);
        return null;
      }
      if (!options.forCard) {
        self._planData = json;
        self._navContext = Object.assign({}, self._navContext, {
          section: json.section || self._navContext.section,
          floor: json.floor || self._navContext.floor
        });
        self._updateBreadcrumbs();
        self._writeUrlState();
      }
      self._renderPlanStage(json, els, options);
      return json;
    }).catch(function () {
      if (self.destroyed) return null;
      self._setPlanMessage(self.locale.error, 'error', els.viewport);
      return null;
    });
  };

  FacadeWidgetInstance.prototype._renderPlanStage = function (data, targetEls, options) {
    options = options || {};
    var els = targetEls || this._planEls;
    var vp = els.viewport;
    if (!vp) return;
    vp.innerHTML = '';

    var titleText = data.sectionCaption || (this.locale.section + ' ' + data.section);

    this._planImgW = data.imageWidth;
    this._planImgH = data.imageHeight;

    var stage = document.createElement('div');
    stage.className = 'fw-plan-stage' + (options.readonly ? ' is-readonly' : '');

    var img = document.createElement('img');
    img.src = data.imageUrl;
    img.alt = titleText || 'План этажа';
    img.draggable = false;
    var selfFit = this;
    img.addEventListener('load', function () {
      if (selfFit.destroyed) return;
      selfFit._layoutFloorView();
      selfFit._planFitAndCenter(vp, els);
    });
    stage.appendChild(img);

    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + data.imageWidth + ' ' + data.imageHeight);
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    stage.appendChild(svg);

    var highlightId = options.highlightApartamentId ? String(options.highlightApartamentId) : null;
    var apartments = data.apartments || [];
    for (var i = 0; i < apartments.length; i++) {
      var apt = apartments[i];
      var poly = document.createElementNS(svgNS, 'polygon');
      poly.setAttribute('points', pointsToSvgAttr(apt.points, data.imageHeight));
      poly.setAttribute('fill', apt.color || '#4da3ff');
      poly.setAttribute('stroke', apt.color || '#4da3ff');
      poly.classList.add('fw-poly', 'fw-apt-poly');
      if (highlightId) {
        if (String(apt.apartamentId) === highlightId) {
          poly.classList.add('is-highlight');
        } else {
          poly.classList.add('is-dim');
        }
      }
      if (!options.readonly) {
        poly.setAttribute('tabindex', '0');
        poly.setAttribute('role', 'button');
      }
      poly.dataset.apartamentId = String(apt.apartamentId || 0);
      poly.dataset.apartmentNum = String(apt.apartmentNum || 0);
      poly.dataset.displayCode = apt.displayCode || '';
      poly.dataset.rooms = apt.rooms || '';
      poly.dataset.area = apt.area || '';
      svg.appendChild(poly);
    }

    vp.appendChild(stage);

    var tooltip = document.createElement('div');
    tooltip.className = 'fw-apt-tooltip';
    tooltip.setAttribute('aria-hidden', 'true');
    vp.appendChild(tooltip);

    els.stage = stage;
    els.svg = svg;
    els.tooltip = tooltip;

    if (!apartments.length && !options.forCard) {
      var banner = document.createElement('div');
      banner.className = 'fw-plan-banner';
      banner.textContent = this.locale.planEmpty;
      vp.appendChild(banner);
    }

    if (!options.readonly) {
      this._bindPlanInteractions(stage, vp, els);
    }
    this._planFitAndCenter(vp, els);
  };

  FacadeWidgetInstance.prototype._applyPlanTransform = function (els) {
    els = els || this._planEls;
    var stage = els.stage;
    if (!stage) return;
    stage.style.width = this._planImgW + 'px';
    stage.style.height = this._planImgH + 'px';
    stage.style.transform = 'translate(' + this._planTx + 'px,' + this._planTy + 'px) scale(' + this._planScale + ')';
  };

  FacadeWidgetInstance.prototype._clampPlanTransform = function (viewport) {
    if (!viewport) return;
    var vw = viewport.clientWidth;
    var vh = viewport.clientHeight;
    var sw = this._planImgW * this._planScale;
    var sh = this._planImgH * this._planScale;

    if (sw <= vw) {
      this._planTx = (vw - sw) / 2;
    } else {
      this._planTx = Math.min(0, Math.max(vw - sw, this._planTx));
    }
    // Не центрируем по вертикали — иначе над планом появляется пустое поле.
    if (sh <= vh) {
      this._planTy = Math.min(12, Math.max(0, vh - sh));
    } else {
      this._planTy = Math.min(0, Math.max(vh - sh, this._planTy));
    }
  };

  FacadeWidgetInstance.prototype._setPlanScaleAt = function (nextScale, clientX, clientY, viewport, els) {
    if (!this._isPlanZoomEnabled()) return;
    nextScale = Math.max(this._planMinScale, Math.min(this._planMaxScale, nextScale));
    var rect = viewport.getBoundingClientRect();
    var cx = clientX - rect.left;
    var cy = clientY - rect.top;
    var sx = (cx - this._planTx) / this._planScale;
    var sy = (cy - this._planTy) / this._planScale;
    this._planScale = nextScale;
    this._planTx = cx - sx * this._planScale;
    this._planTy = cy - sy * this._planScale;
    this._clampPlanTransform(viewport);
    this._applyPlanTransform(els);
  };

  FacadeWidgetInstance.prototype._fillAptTooltip = function (el, tooltipEl) {
    if (!el || !tooltipEl) return;
    var code = el.dataset.apartmentNum || '';
    var codeLine = document.createElement('span');
    codeLine.className = 'fw-apt-tooltip__code';
    codeLine.textContent = code ? ('\u2116' + code) : (this.locale.residence || 'Квартира');
    var specLine = document.createElement('span');
    specLine.className = 'fw-apt-tooltip__spec';
    var spec = formatRoomsK(el.dataset.rooms);
    if (el.dataset.area) {
      spec += (spec ? ' | ' : '') + formatAreaRu(el.dataset.area) + ' \u043c\u00b2';
    }
    specLine.textContent = spec;
    tooltipEl.innerHTML = '';
    tooltipEl.appendChild(codeLine);
    if (spec) tooltipEl.appendChild(specLine);
  };

  FacadeWidgetInstance.prototype._setPlanHover = function (stage, apartamentId, clientX, clientY, viewport, els) {
    els = els || this._planEls;
    var tooltipEl = els.tooltip;
    var key = apartamentId ? String(apartamentId) : null;
    var prev = this._planHoverKey;
    if (prev && prev !== key) {
      var prevEl = stage.querySelector('.fw-apt-poly[data-apartament-id="' + prev + '"]');
      if (prevEl) prevEl.classList.remove('is-hover');
    }
    this._planHoverKey = key;
    if (key) {
      var el = stage.querySelector('.fw-apt-poly[data-apartament-id="' + key + '"]');
      if (el) el.classList.add('is-hover');
      if (el && tooltipEl && viewport) {
        this._fillAptTooltip(el, tooltipEl);
        tooltipEl.classList.add('is-visible');
        var rect = viewport.getBoundingClientRect();
        var cx = clientX - rect.left;
        var cy = clientY - rect.top;
        var tw = tooltipEl.offsetWidth || 140;
        var th = tooltipEl.offsetHeight || 48;
        var gap = 12;
        var x = Math.max(tw / 2 + 4, Math.min(rect.width - tw / 2 - 4, cx));
        var above = cy - gap >= th + 4;
        var y;
        if (above) {
          tooltipEl.style.transform = 'translate(-50%, calc(-100% - ' + gap + 'px))';
          y = Math.max(th + gap + 4, Math.min(rect.height - 4, cy));
        } else {
          tooltipEl.style.transform = 'translate(-50%, ' + gap + 'px)';
          y = Math.max(4, Math.min(rect.height - th - gap - 4, cy));
        }
        tooltipEl.style.left = x + 'px';
        tooltipEl.style.top = y + 'px';
      }
    } else if (tooltipEl) {
      tooltipEl.classList.remove('is-visible');
      tooltipEl.style.transform = 'translate(-50%, calc(-100% - 12px))';
    }
  };

  FacadeWidgetInstance.prototype._fireApartmentClick = function (poly) {
    var payload = {
      homeId: this.options.homeId,
      section: (this._planData && this._planData.section) || this._navContext.section || 1,
      floor: (this._planData && this._planData.floor) || this._navContext.floor || 0,
      apartamentId: parseInt(poly.dataset.apartamentId, 10) || 0,
      apartmentNum: parseInt(poly.dataset.apartmentNum, 10) || 0,
      displayCode: poly.dataset.displayCode || '',
      rooms: poly.dataset.rooms || '',
      area: poly.dataset.area || ''
    };
    if (typeof this.options.onApartmentClick === 'function') {
      var result = this.options.onApartmentClick(payload);
      if (result && result.preventDefault) return;
    }
    if (!payload.apartmentNum && !payload.apartamentId) {
      return;
    }
    this._openApartmentCard(payload);
  };

  FacadeWidgetInstance.prototype._openApartmentCard = function (payload) {
    var self = this;
    if (this.destroyed) return;
    var shell = this._activeCardShell();
    if (!shell) return;

    if (!(payload && payload.fromChessboard) && !(this._navContext && this._navContext.fromChessboard && this.currentView === 'chessboard')) {
      if (this.currentView === 'floor' || this.currentView === 'facade') {
        this._navContext.fromChessboard = false;
      }
    }

    shell.innerHTML = '<div class="fw-msg is-loading">' + this.locale.loading + '</div>';
    this._navPush('card', payload);
    this._layoutCardView();

    var url = this._apartmentCardApiUrl(payload.apartmentNum, payload.apartamentId);
    // omit: публичный API; credentials:include + CORS ACAO:* браузер режет (в т.ч. file:// Origin null)
    fetch(url, { credentials: 'omit', cache: 'no-cache' }).then(function (res) {
      if (!res.ok) {
        throw new Error('HTTP ' + res.status);
      }
      return res.json();
    }).then(function (json) {
      if (self.destroyed || self.currentView !== 'card') return;
      if (!json || json.success === false) {
        shell.innerHTML = '<div class="fw-msg is-error">' + ((json && json.message) || self.locale.error) + '</div>';
        return;
      }
      self._cardData = json;
      if (json.unitLabelNomCap) self.locale.residence = json.unitLabelNomCap;
      self._cardHighlightId = payload.apartamentId || json.apartamentId;
      self._cardTab = 'layout';
      self._navContext = Object.assign({}, self._navContext, {
        apartmentNum: json.apartmentNum || payload.apartmentNum,
        apartamentId: json.apartamentId || payload.apartamentId,
        displayCode: json.displayCode || payload.displayCode || '',
        fromChessboard: !!(payload.fromChessboard || self._navContext.fromChessboard),
        section: payload.section || self._navContext.section,
        floor: payload.floor || self._navContext.floor
      });
      self._renderApartmentCard(json, payload);
      self._updateBreadcrumbs();
      self._writeUrlState();
    }).catch(function (err) {
      if (self.destroyed) return;
      shell.innerHTML = '<div class="fw-msg is-error">' + self.locale.error + (err && err.message ? ' (' + err.message + ')' : '') + '</div>';
    });
  };

  FacadeWidgetInstance.prototype._renderApartmentCard = function (data, navPayload) {
    var self = this;
    var crumbs = this._els.crumbs;
    var root = this._els.root;
    var body = this._els.body;
    // Не даём shell.innerHTML уничтожить общий nav крошек.
    if (crumbs && root && body && crumbs.parentNode && crumbs.parentNode !== root) {
      root.insertBefore(crumbs, body);
    }
    var shells = [this._cardEls.shell, this._cardEls.shellMobile].filter(Boolean);
    shells.forEach(function (shell) {
      shell.innerHTML = '';
      self._buildCardContent(shell, data, navPayload);
    });
    this._updateBreadcrumbs();
    if (!isDesktopUi() && this._cardEls.shellMobile) {
      this._cardEls.shellMobile.scrollTop = 0;
    }
  };

  FacadeWidgetInstance.prototype._buildCardContent = function (shell, data, navPayload) {
    var self = this;
    var L = this.locale;

    var side = document.createElement('div');
    side.className = 'fw-card-side';

    var headRow = document.createElement('div');
    headRow.className = 'fw-card-head-row';

    var backBtn = document.createElement('button');
    backBtn.type = 'button';
    backBtn.className = 'fw-card-back';
    backBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>' + (L.backToChoice || 'Назад к выбору') + '</span>';
    backBtn.addEventListener('click', function (e) {
      e.preventDefault();
      self._navPop();
    });
    headRow.appendChild(backBtn);

    var tools = document.createElement('div');
    tools.className = 'fw-card-tools';
    var printBtn = document.createElement('button');
    printBtn.type = 'button';
    printBtn.className = 'fw-card-tool';
    printBtn.setAttribute('aria-label', 'Печать');
    printBtn.setAttribute('title', 'Печать');
    printBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>';
    printBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      self._printApartmentCard();
    });
    tools.appendChild(printBtn);

    // PDF — скачивание файла с той же вёрсткой, что у печати (html2canvas + jsPDF).
    var pdfBtn = document.createElement('button');
    pdfBtn.type = 'button';
    pdfBtn.className = 'fw-card-tool';
    pdfBtn.setAttribute('aria-label', 'Скачать PDF');
    pdfBtn.setAttribute('title', 'Скачать PDF');
    pdfBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/></svg>';
    pdfBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      self._downloadApartmentCardPdf();
    });
    tools.appendChild(pdfBtn);
    headRow.appendChild(tools);
    side.appendChild(headRow);

    var crumbsHost = document.createElement('div');
    crumbsHost.className = 'fw-card-crumbs-host';
    side.appendChild(crumbsHost);
    if (shell === this._cardEls.shell) {
      this._cardEls.crumbsHost = crumbsHost;
    } else if (shell === this._cardEls.shellMobile) {
      this._cardEls.crumbsHostMobile = crumbsHost;
    }

    var title = document.createElement('h2');
    title.className = 'fw-card-title';
    title.textContent = L.bookingTitle || 'Заявка на бронирование';
    side.appendChild(title);

    var spec = document.createElement('p');
    spec.className = 'fw-card-spec';
    spec.textContent = formatRoomsK(data.rooms) + ' | ' + formatAreaRu(data.area) + ' \u043c\u00b2';
    side.appendChild(spec);

    var meta = document.createElement('div');
    meta.className = 'fw-card-meta';
      meta.innerHTML =
      '<div>' + (L.jk || 'Жилой комплекс') + ': ' + (data.kvartalTitle ? '«' + data.kvartalTitle + '»' : '—') + '</div>' +
      '<div>' + (L.tower || 'Башня') + ' ' + (data.sectionCaption || '') + '</div>' +
      '<div>' + (L.floorLabel || 'Этаж') + ': ' + data.floor + '/' + data.floorsTotal + '</div>' +
      '<div>' + (data.unitLabelNomCap || L.residence || 'Квартира') + ': \u2116' + data.apartmentNum + '</div>';
    side.appendChild(meta);

    if (data.priceFormatted) {
      var price = document.createElement('div');
      price.className = 'fw-card-price';
      price.textContent = data.priceFormatted + ' \u20bd';
      side.appendChild(price);
    }

    if (data.showBookingForm) {
      var form = document.createElement('form');
      form.className = 'fw-card-form';
      form.noValidate = true;

      function makeField(tag, attrs) {
        var wrap = document.createElement('div');
        wrap.className = 'fw-card-field';
        var el = document.createElement(tag);
        Object.keys(attrs).forEach(function (k) {
          if (k === 'textContent') el.textContent = attrs[k];
          else el[k] = attrs[k];
        });
        var err = document.createElement('div');
        err.className = 'fw-card-field-error';
        err.setAttribute('data-for', attrs.name || '');
        wrap.appendChild(el);
        wrap.appendChild(err);
        form.appendChild(wrap);
        el.addEventListener('input', function () {
          self._clearFieldError(el);
        });
        return el;
      }

      makeField('input', { type: 'text', name: 'fio', placeholder: L.fio || 'ФИО', required: true, autocomplete: 'name' });
      var phoneInput = makeField('input', {
        type: 'tel',
        name: 'phone',
        placeholder: L.phone || 'Номер телефона для связи',
        required: true,
        autocomplete: 'tel',
        inputMode: 'tel'
      });
      this._bindPhoneMask(phoneInput);
      makeField('textarea', { name: 'message', placeholder: L.message || 'Ваши вопросы и пожелания' });

      var booking = data.booking || {};
      var hidden = booking.hiddenFields || {};
      Object.keys(hidden).forEach(function (k) {
        if (k === '_fp_hp') return;
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = k;
        inp.value = hidden[k];
        form.appendChild(inp);
      });
      var hp = document.createElement('input');
      hp.type = 'text';
      hp.name = '_fp_hp';
      hp.value = '';
      hp.tabIndex = -1;
      hp.autocomplete = 'off';
      hp.setAttribute('aria-hidden', 'true');
      hp.style.cssText = 'position:absolute;left:-9999px;height:0;width:0;opacity:0;';
      form.appendChild(hp);

      var formMsg = document.createElement('div');
      formMsg.className = 'fw-card-msg';
      formMsg.style.display = 'none';
      form.appendChild(formMsg);

      var submit = document.createElement('button');
      submit.type = 'submit';
      submit.className = 'fw-card-submit';
      submit.textContent = L.book || 'Забронировать';
      form.appendChild(submit);

      var consent = document.createElement('p');
      consent.className = 'fw-card-consent';
      consent.textContent = L.consent || '';
      form.appendChild(consent);

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        self._submitBookingForm(form, booking, formMsg, submit);
      });
      side.appendChild(form);
    } else {
      var st = document.createElement('div');
      st.className = 'fw-card-status';
      st.style.background = data.statusColor || '#eee';
      st.textContent = (L.status || 'Статус') + ': ' + (data.statusLabel || data.status);
      side.appendChild(st);
    }

    shell.appendChild(side);

    var visual = document.createElement('div');
    visual.className = 'fw-card-visual';

    var tabs = document.createElement('div');
    tabs.className = 'fw-card-tabs';
    var tabLayout = document.createElement('button');
    tabLayout.type = 'button';
    tabLayout.className = 'fw-card-tab is-active';
    tabLayout.textContent = L.tabLayout || 'Планировка';
    var tabFloor = document.createElement('button');
    tabFloor.type = 'button';
    tabFloor.className = 'fw-card-tab';
    tabFloor.textContent = L.tabOnFloor || 'На этаже';
    tabs.appendChild(tabLayout);
    tabs.appendChild(tabFloor);
    visual.appendChild(tabs);

    var panelLayout = document.createElement('div');
    panelLayout.className = 'fw-card-panel is-active';
    panelLayout.dataset.panel = 'layout';
    var plnTop = document.createElement('div');
    plnTop.className = 'fw-card-pln-label';
    plnTop.textContent = data.sectionCaption || '';
    panelLayout.appendChild(plnTop);
    if (data.imageLayoutUrl) {
      var plnImg = document.createElement('img');
      plnImg.className = 'fw-card-pln-img';
      plnImg.src = data.imageLayoutUrl;
      plnImg.alt = 'Планировка';
      panelLayout.appendChild(plnImg);
    } else {
      panelLayout.appendChild(document.createElement('div')).className = 'fw-msg';
      panelLayout.lastChild.textContent = 'Планировка недоступна';
    }
    var plnBottom = document.createElement('div');
    plnBottom.className = 'fw-card-pln-label';
    plnBottom.textContent = data.addressLabel || '';
    panelLayout.appendChild(plnBottom);
    visual.appendChild(panelLayout);

    var panelFloor = document.createElement('div');
    panelFloor.className = 'fw-card-panel';
    panelFloor.dataset.panel = 'floor';
    var floorVp = document.createElement('div');
    floorVp.className = 'fw-card-floor-vp';
    panelFloor.appendChild(floorVp);
    visual.appendChild(panelFloor);

    shell.appendChild(visual);

    var cardPlanEls = { viewport: floorVp, title: null };
    this._cardEls.planViewport = floorVp;

    function activateTab(name) {
      tabLayout.classList.toggle('is-active', name === 'layout');
      tabFloor.classList.toggle('is-active', name === 'floor');
      panelLayout.classList.toggle('is-active', name === 'layout');
      panelFloor.classList.toggle('is-active', name === 'floor');
      self._cardTab = name;
      if (name === 'floor' && !floorVp.dataset.loaded) {
        floorVp.dataset.loaded = '1';
        var fp = {
          section: data.section || navPayload.section,
          floor: data.floor || navPayload.floor
        };
        self._loadFloorPlan(fp, cardPlanEls, {
          forCard: true,
          readonly: true,
          highlightApartamentId: self._cardHighlightId
        });
      }
    }

    tabLayout.addEventListener('click', function () { activateTab('layout'); });
    tabFloor.addEventListener('click', function () { activateTab('floor'); });
  };

  FacadeWidgetInstance.prototype._clearFieldError = function (input) {
    if (!input) return;
    input.classList.remove('is-error');
    var wrap = input.closest && input.closest('.fw-card-field');
    if (wrap) {
      var err = wrap.querySelector('.fw-card-field-error');
      if (err) {
        err.textContent = '';
        err.classList.remove('is-visible');
      }
    }
  };

  FacadeWidgetInstance.prototype._clearFormErrors = function (form) {
    if (!form) return;
    var nodes = form.querySelectorAll('.is-error');
    for (var i = 0; i < nodes.length; i++) nodes[i].classList.remove('is-error');
    var msgs = form.querySelectorAll('.fw-card-field-error');
    for (var j = 0; j < msgs.length; j++) {
      msgs[j].textContent = '';
      msgs[j].classList.remove('is-visible');
    }
  };

  FacadeWidgetInstance.prototype._phoneDigits = function (raw) {
    var digits = String(raw || '').replace(/\D/g, '');
    if (!digits) return '';
    // 8XXXXXXXXXX → 7XXXXXXXXXX
    if (digits.charAt(0) === '8') digits = '7' + digits.slice(1);
    // 9XXXXXXXXX (10 цифр без кода страны) → 79XXXXXXXXX
    if (digits.length === 10 && digits.charAt(0) === '9') digits = '7' + digits;
    // если вставили только хвост без 7/8 — дополняем 7
    if (digits.charAt(0) !== '7') digits = '7' + digits;
    return digits.slice(0, 11);
  };

  /** +7 (999) 123-45-67 из цифр (частичный ввод тоже) */
  FacadeWidgetInstance.prototype._formatRuPhone = function (digits) {
    digits = this._phoneDigits(digits);
    if (!digits) return '';
    var rest = digits.slice(1);
    var out = '+7';
    if (rest.length === 0) return out;
    out += ' (' + rest.slice(0, Math.min(3, rest.length));
    if (rest.length < 3) return out;
    out += ')';
    if (rest.length > 3) out += ' ' + rest.slice(3, Math.min(6, rest.length));
    if (rest.length > 6) out += '-' + rest.slice(6, Math.min(8, rest.length));
    if (rest.length > 8) out += '-' + rest.slice(8, Math.min(10, rest.length));
    return out;
  };

  FacadeWidgetInstance.prototype._isValidRuPhone = function (raw) {
    var digits = this._phoneDigits(raw);
    return digits.length === 11 && digits.charAt(0) === '7';
  };

  FacadeWidgetInstance.prototype._bindPhoneMask = function (input) {
    if (!input) return;
    var self = this;
    var applying = false;

    function applyMask(fromPaste) {
      if (applying) return;
      applying = true;
      var start = input.selectionStart;
      var before = input.value;
      var formatted = self._formatRuPhone(before);
      input.value = formatted;
      // Курсор к концу при печати/вставке — проще предсказуемо для маски
      try {
        var pos = fromPaste || start >= before.length - 1 ? formatted.length : Math.min(start + (formatted.length - before.length), formatted.length);
        if (pos < 4) pos = formatted.length;
        input.setSelectionRange(pos, pos);
      } catch (e) { /* ignore */ }
      applying = false;
    }

    input.addEventListener('focus', function () {
      if (!String(input.value || '').trim()) {
        input.value = '+7 (';
        try { input.setSelectionRange(input.value.length, input.value.length); } catch (e) { /* ignore */ }
      }
    });

    input.addEventListener('blur', function () {
      var v = String(input.value || '').trim();
      if (v === '+7' || v === '+7 (' || v === '+7 ()') {
        input.value = '';
        return;
      }
      if (v) input.value = self._formatRuPhone(v);
    });

    input.addEventListener('paste', function () {
      setTimeout(function () { applyMask(true); }, 0);
    });

    input.addEventListener('input', function () {
      applyMask(false);
    });
  };

  FacadeWidgetInstance.prototype._setFieldError = function (form, name, message) {
    var input = form.querySelector('[name="' + name + '"]');
    if (!input || input.type === 'hidden') return false;
    input.classList.add('is-error');
    var wrap = input.closest && input.closest('.fw-card-field');
    if (wrap) {
      var err = wrap.querySelector('.fw-card-field-error');
      if (err) {
        err.textContent = message || 'Ошибка';
        err.classList.add('is-visible');
      }
    }
    return true;
  };

  FacadeWidgetInstance.prototype._validateBookingClient = function (form) {
    var errors = {};
    var fio = (form.querySelector('[name="fio"]') || {}).value || '';
    var phone = (form.querySelector('[name="phone"]') || {}).value || '';
    var message = (form.querySelector('[name="message"]') || {}).value || '';
    fio = String(fio).trim();
    phone = String(phone).trim();
    message = String(message).trim();

    if (fio.length < 3) errors.fio = 'Минимальная длина — 3 символа';
    if (!phone) {
      errors.phone = 'Обязательное поле';
    } else if (!this._isValidRuPhone(phone)) {
      errors.phone = 'Введите номер полностью: +7 (XXX) XXX-XX-XX';
    }
    if (message.length > 300) errors.message = 'Максимальная длина — 300 символов';
    if (/https?:\/\//i.test(message) || /www\./i.test(message)) {
      errors.message = 'Сообщение не должно содержать ссылки';
    }

    return errors;
  };

  FacadeWidgetInstance.prototype._applyBookingErrors = function (form, msgEl, json) {
    var errors = (json && json.errors) || {};
    var shownVisible = false;
    var hiddenLines = [];
    var labels = {
      fio: 'ФИО',
      phone: 'Телефон',
      message: 'Сообщение',
      home: 'Дом',
      section_caption: 'Секция',
      apartment_num: 'Номер'
    };

    Object.keys(errors).forEach(function (name) {
      var msg = errors[name];
      if (typeof msg !== 'string') msg = String(msg || 'Ошибка');
      if (this._setFieldError(form, name, msg)) {
        shownVisible = true;
      } else {
        hiddenLines.push((labels[name] || name) + ': ' + msg);
      }
    }, this);

    msgEl.style.display = 'block';
    msgEl.className = 'fw-card-msg is-err';
    msgEl.innerHTML = '';
    var title = document.createElement('div');
    title.textContent = (json && json.message) || this.locale.bookingError;
    msgEl.appendChild(title);
    if (hiddenLines.length) {
      var ul = document.createElement('ul');
      hiddenLines.forEach(function (line) {
        var li = document.createElement('li');
        li.textContent = line;
        ul.appendChild(li);
      });
      msgEl.appendChild(ul);
    } else if (shownVisible) {
      var hint = document.createElement('div');
      hint.style.marginTop = '4px';
      hint.style.fontSize = '12px';
      hint.textContent = 'Проверьте подсвеченные поля';
      msgEl.appendChild(hint);
    }

    var firstErr = form.querySelector('.is-error');
    if (firstErr && firstErr.focus) {
      try { firstErr.focus(); } catch (e) { /* ignore */ }
    }
  };

  FacadeWidgetInstance.prototype._submitBookingForm = function (form, booking, msgEl, submitBtn) {
    var self = this;
    var L = this.locale;
    this._clearFormErrors(form);
    msgEl.style.display = 'none';
    msgEl.innerHTML = '';

    var clientErrors = this._validateBookingClient(form);
    if (Object.keys(clientErrors).length) {
      this._applyBookingErrors(form, msgEl, {
        message: 'Исправьте ошибки в форме',
        errors: clientErrors
      });
      return;
    }

    var phoneEl = form.querySelector('[name="phone"]');
    if (phoneEl) phoneEl.value = this._formatRuPhone(phoneEl.value);

    var fd = new FormData(form);
    if (!fd.get('_fp_js')) fd.set('_fp_js', '1');
    submitBtn.disabled = true;

    fetch(booking.actionUrl, {
      method: 'POST',
      body: fd,
      credentials: 'omit'
    }).then(function (res) { return res.json(); }).then(function (json) {
      submitBtn.disabled = false;
      if (json && json.success) {
        self._clearFormErrors(form);
        msgEl.style.display = 'block';
        msgEl.className = 'fw-card-msg is-ok';
        msgEl.textContent = json.message || L.bookingSuccess;
        form.reset();
        if (typeof self.options.onBookingSuccess === 'function') {
          self.options.onBookingSuccess(json.message);
        }
      } else {
        self._applyBookingErrors(form, msgEl, json || {});
      }
    }).catch(function () {
      submitBtn.disabled = false;
      msgEl.style.display = 'block';
      msgEl.className = 'fw-card-msg is-err';
      msgEl.textContent = L.bookingError;
    });
  };

  FacadeWidgetInstance.prototype._bindPlanInteractions = function (stage, viewport, els) {
    var self = this;
    els = els || this._planEls;

    function targetPoly(ev) {
      var t = ev.target;
      if (t && t.classList && t.classList.contains('fw-apt-poly') && !t.classList.contains('is-dim')) return t;
      return null;
    }

    stage.addEventListener('pointerdown', function (ev) {
      if (self.destroyed || self.currentView !== 'floor') return;
      stage.setPointerCapture && stage.setPointerCapture(ev.pointerId);
      self._planPointers.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
      self._planMoved = false;

      if (self._planPointers.size === 2) {
        if (!self._isPlanZoomEnabled()) return;
        var pts = Array.from(self._planPointers.values());
        var dx = pts[0].x - pts[1].x;
        var dy = pts[0].y - pts[1].y;
        self._planPinchStartDist = Math.hypot(dx, dy) || 1;
        self._planPinchStartScale = self._planScale;
        self._planPanStart = null;
        return;
      }

      var poly = targetPoly(ev);
      if (!self._isPlanZoomEnabled()) {
        // без зума — только тап по квартире, без pan
        self._planPanStart = { x: ev.clientX, y: ev.clientY, tx: self._planTx, ty: self._planTy, poly: poly, time: Date.now(), noPan: true };
        return;
      }
      self._planPanStart = { x: ev.clientX, y: ev.clientY, tx: self._planTx, ty: self._planTy, poly: poly, time: Date.now() };
    });

    stage.addEventListener('pointermove', function (ev) {
      if (self.destroyed || self.currentView !== 'floor') return;
      if (self._planPointers.has(ev.pointerId)) {
        self._planPointers.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
      }

      if (self._planPointers.size === 2) {
        if (!self._isPlanZoomEnabled()) return;
        var pts = Array.from(self._planPointers.values());
        var dx = pts[0].x - pts[1].x;
        var dy = pts[0].y - pts[1].y;
        var dist = Math.hypot(dx, dy) || 1;
        var next = self._planPinchStartScale * (dist / self._planPinchStartDist);
        self._setPlanScaleAt(next, (pts[0].x + pts[1].x) / 2, (pts[0].y + pts[1].y) / 2, viewport, els);
        self._planMoved = true;
        return;
      }

      if (!self._planPanStart) {
        var hpoly = targetPoly(ev);
        if (hpoly) {
          self._setPlanHover(stage, hpoly.dataset.apartamentId, ev.clientX, ev.clientY, viewport, els);
        } else {
          self._setPlanHover(stage, null, 0, 0, viewport, els);
        }
        return;
      }

      if (self._planPanStart.noPan || !self._isPlanZoomEnabled()) {
        var dNo = Math.hypot(ev.clientX - self._planPanStart.x, ev.clientY - self._planPanStart.y);
        if (dNo > 6) self._planMoved = true;
        return;
      }

      var dx2 = ev.clientX - self._planPanStart.x;
      var dy2 = ev.clientY - self._planPanStart.y;
      if (Math.hypot(dx2, dy2) > 6) self._planMoved = true;

      self._planTx = self._planPanStart.tx + dx2;
      self._planTy = self._planPanStart.ty + dy2;
      self._clampPlanTransform(viewport);
      self._applyPlanTransform(els);
      stage.classList.add('is-dragging');
    });

    function endPointer(ev) {
      if (self.destroyed) return;
      self._planPointers.delete(ev.pointerId);
      stage.classList.remove('is-dragging');
      if (self._planPointers.size < 2) self._planPinchStartDist = 0;
      if (!self._planPanStart) return;
      var start = self._planPanStart;
      self._planPanStart = null;
      var shortTap = !self._planMoved && (Date.now() - start.time < 500);
      if (shortTap && start.poly) {
        self._fireApartmentClick(start.poly);
      }
    }

    stage.addEventListener('pointerup', endPointer);
    stage.addEventListener('pointercancel', endPointer);
    stage.addEventListener('pointerleave', function () {
      if (!self._planPanStart) self._setPlanHover(stage, null, 0, 0, viewport, els);
    });

    stage.addEventListener('keydown', function (ev) {
      var poly = targetPoly(ev);
      if (!poly) return;
      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        self._fireApartmentClick(poly);
      }
    });
  };

  FacadeWidgetInstance.prototype._prefersReducedMotion = function () {
    try {
      return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {
      return false;
    }
  };

  /**
   * Список этажей снизу вверх (по номеру этажа) для КАЖДОЙ секции отдельно.
   * Секции нумеруются независимо, поэтому подсветку нельзя синхронизировать
   * по абсолютному номеру этажа — иначе при разной высоте секций подсветка
   * появляется только там, где такой номер этажа существует. Вместо этого
   * каждая секция листает свой список по одному и тому же локальному индексу,
   * так подсветка идёт синхронно и последовательно в обеих секциях сразу.
   */
  FacadeWidgetInstance.prototype._buildScrollFloorMap = function () {
    var stage = this._els.stage;
    if (!stage) return {};
    var bySection = {};
    var nodes = stage.querySelectorAll('.fw-poly');
    for (var i = 0; i < nodes.length; i++) {
      var section = String(nodes[i].dataset.section || '');
      var fl = parseInt(nodes[i].dataset.floor, 10) || 0;
      if (!section || fl <= 0) continue;
      if (!bySection[section]) bySection[section] = {};
      bySection[section][fl] = true;
    }

    var map = {};
    var maxLen = 0;
    Object.keys(bySection).forEach(function (section) {
      var floors = Object.keys(bySection[section])
        .map(function (f) { return parseInt(f, 10); })
        .sort(function (a, b) { return a - b; })
        .map(function (f) { return String(f); });
      map[section] = floors;
      if (floors.length > maxLen) maxLen = floors.length;
    });
    this._scrollRevealSteps = maxLen;
    return map;
  };

  FacadeWidgetInstance.prototype._polysBySectionFloor = function (stage, section, floorStr) {
    var list = [];
    var nodes = stage.querySelectorAll('.fw-poly');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (String(el.dataset.section) === String(section) && String(el.dataset.floor) === String(floorStr)) {
        list.push(el);
      }
    }
    return list;
  };

  /**
   * Подсветка — простая зацикленная анимация: пока виджет виден в области
   * видимости, этажи по очереди подсвечиваются по кругу (1,2,3...N,1,2,3...),
   * это не привязано к тому, насколько именно проскроллили — только к факту,
   * что блок сейчас на экране. Как только блок уходит из вида — анимация
   * останавливается и подсветка снимается; при возвращении — стартует заново.
   */
  FacadeWidgetInstance.prototype._bindHostHoverPause = function () {
    if (!this.host) return;
    this.host.addEventListener('pointerenter', this._onHostPointerEnter);
    this.host.addEventListener('pointerleave', this._onHostPointerLeave);
  };

  FacadeWidgetInstance.prototype._unbindHostHoverPause = function () {
    if (!this.host) return;
    this.host.removeEventListener('pointerenter', this._onHostPointerEnter);
    this.host.removeEventListener('pointerleave', this._onHostPointerLeave);
    this._hostHovered = false;
  };

  /** Наведение на тело виджета — стоп анимации подсветки этажей. */
  FacadeWidgetInstance.prototype._onHostPointerEnter = function () {
    this._hostHovered = true;
    this._applyScrollRevealIndex(-1);
  };

  FacadeWidgetInstance.prototype._onHostPointerLeave = function () {
    this._hostHovered = false;
    this._resumeRevealHighlight();
  };

  FacadeWidgetInstance.prototype._revealAllowed = function () {
    return !this.exploring
      && this.currentView === 'facade'
      && !this._hoverKey
      && !this._hostHovered;
  };

  FacadeWidgetInstance.prototype._setupScrollReveal = function () {
    this._teardownScrollReveal();
    if (!this._scrollReveal || this._prefersReducedMotion()) return;
    this._scrollFloorMap = this._buildScrollFloorMap();
    if (!this._scrollRevealSteps) return;

    var self = this;
    if (typeof IntersectionObserver !== 'undefined') {
      this._io = new IntersectionObserver(function (entries) {
        var visible = entries.some(function (e) { return e.isIntersecting; });
        if (visible) self._startRevealLoop();
        else self._stopRevealLoop();
      }, { threshold: 0.1 });
      this._io.observe(this.host);
    } else {
      this._startRevealLoop();
    }
  };

  FacadeWidgetInstance.prototype._teardownScrollReveal = function () {
    if (this._io) {
      try { this._io.disconnect(); } catch (e) { /* ignore */ }
      this._io = null;
    }
    this._stopRevealLoop();
  };

  FacadeWidgetInstance.prototype._startRevealLoop = function () {
    if (this.destroyed || this._revealTimer || !this._scrollRevealSteps) return;
    var self = this;
    var speed = this._scrollRevealSpeed > 0 ? this._scrollRevealSpeed : 1;
    var stepMs = Math.max(150, Math.round(900 / speed));
    this._revealLoopIdx = 0;
    if (this._revealAllowed()) this._applyScrollRevealIndex(0);
    this._revealTimer = setInterval(function () {
      self._revealLoopIdx = (self._revealLoopIdx + 1) % self._scrollRevealSteps;
      if (self._revealAllowed()) {
        self._applyScrollRevealIndex(self._revealLoopIdx);
      }
    }, stepMs);
  };

  FacadeWidgetInstance.prototype._stopRevealLoop = function () {
    if (this._revealTimer) {
      clearInterval(this._revealTimer);
      this._revealTimer = null;
    }
    this._applyScrollRevealIndex(-1);
  };

  /** Немедленно показывает текущий шаг цикла (после ухода с полигона/из режима explore/плана/с виджета). */
  FacadeWidgetInstance.prototype._resumeRevealHighlight = function () {
    if (!this._revealAllowed() || !this._revealTimer) return;
    this._applyScrollRevealIndex(this._revealLoopIdx);
  };

  /**
   * Подсветка одного этажа в КАЖДОЙ секции одновременно (по одному и тому же
   * локальному индексу снизу вверх). Если в секции этажей меньше, чем idx,
   * подсвечивается её верхний этаж — секция «дожидается» более высокую.
   */
  FacadeWidgetInstance.prototype._applyScrollRevealIndex = function (idx) {
    var stage = this._els.stage;
    if (!stage) {
      this._scrollRevealIndex = idx;
      return;
    }
    if (idx === this._scrollRevealIndex) return;

    var nodes = stage.querySelectorAll('.fw-poly');
    for (var i = 0; i < nodes.length; i++) {
      nodes[i].classList.remove('is-scroll-reveal');
    }
    this._scrollRevealIndex = idx;
    if (idx < 0) return;

    var map = this._scrollFloorMap || {};
    var self = this;
    Object.keys(map).forEach(function (section) {
      var floors = map[section];
      if (!floors || !floors.length) return;
      var localIdx = Math.min(idx, floors.length - 1);
      self._polysBySectionFloor(stage, section, floors[localIdx]).forEach(function (el) {
        el.classList.add('is-scroll-reveal');
      });
    });
  };

  FacadeWidgetInstance.prototype._layoutFit = function () {
    if (this.destroyed || !this.data || !this._els.viewport) return;

    var hostW = this.host.clientWidth || this.host.offsetWidth || 300;
    var vw;
    if (this.widthSpec.type === 'px') {
      vw = Math.min(this.widthSpec.value, hostW || this.widthSpec.value);
      this._els.root.style.width = this.widthSpec.value + 'px';
      this._els.root.style.maxWidth = '100%';
    } else {
      vw = hostW;
      this._els.root.style.width = '100%';
    }

    var maxHOpt = this.options.maxHeight;
    // По умолчанию — как в демо: высокий потолок, чтобы фасад не обрезался по ширине 100%.
    var maxH = typeof maxHOpt === 'number'
      ? maxHOpt
      : Math.round((typeof window !== 'undefined' ? window.innerWidth : 1200) * 2);

    // Ширина = контейнер; высота строго по пропорции картинки (без пустого поля снизу).
    var scale = vw / this._imgW;
    if (this._imgH * scale > maxH) {
      scale = maxH / this._imgH;
    }
    var stageW = this._imgW * scale;
    var stageH = this._imgH * scale;

    this._fitScale = scale;
    this._stageW = stageW;
    this._stageH = stageH;

    var viewport = this._els.viewport;
    viewport.style.width = stageW + 'px';
    viewport.style.maxWidth = '100%';
    viewport.style.height = stageH + 'px';

    var stage = this._els.stage;
    stage.style.width = stageW + 'px';
    stage.style.height = stageH + 'px';
    stage.style.transform = 'none';
  };

  FacadeWidgetInstance.prototype._syncMobileUiClass = function () {
    this._syncUiClasses();
  };

  FacadeWidgetInstance.prototype._isPlanZoomEnabled = function () {
    if (!this._planZoom) return !isDesktopUi();
    return isDesktopUi() ? !!this._planZoom.desktop : !!this._planZoom.mobile;
  };

  FacadeWidgetInstance.prototype._layoutFloorView = function () {
    if (!this._planEls.viewport) return;

    // Mobile (explore): viewport обязан занять остаток высоты — иначе absolute-stage → height:0 → белый экран
    if (!isDesktopUi()) {
      var vp = this._planEls.viewport;
      var layer = this._planEls.layer;
      var head = this._planEls.head;
      var inner = this._els.exploreInner;
      if (layer) {
        layer.style.flex = '1';
        layer.style.minHeight = '0';
        layer.style.height = '100%';
        layer.style.display = 'flex';
        layer.style.flexDirection = 'column';
      }
      var headH = (head && head.offsetHeight) || 64;
      var avail = 0;
      if (inner && inner.clientHeight) {
        avail = Math.max(180, inner.clientHeight - headH);
      } else {
        avail = Math.max(180, Math.round((window.innerHeight || 600) * 0.75) - headH);
      }
      vp.style.flex = '1 1 auto';
      vp.style.minHeight = '180px';
      vp.style.height = avail + 'px';
      return;
    }

    if (!this._els.floorView) return;

    // Desktop: высота viewport = высота плана по РЕАЛЬНОЙ ширине контейнера
    // (раньше считали от host.clientWidth / высоте фасада → огромный пустой низ).
    this._els.floorView.style.minHeight = '';
    this._els.floorView.style.height = 'auto';
    if (this._planEls.layer) {
      this._planEls.layer.style.minHeight = '';
      this._planEls.layer.style.height = '';
      this._planEls.layer.style.flex = '';
    }

    var vpDesk = this._planEls.viewport;
    if (!vpDesk) return;
    vpDesk.style.flex = '';
    vpDesk.style.minHeight = '';
    vpDesk.style.width = '100%';
    // Сначала сбрасываем высоту, чтобы clientWidth был по контейнеру.
    vpDesk.style.height = '1px';

    var layerEl = this._planEls.layer;
    var availW = (vpDesk.clientWidth && vpDesk.clientWidth > 40)
      ? vpDesk.clientWidth
      : ((layerEl && layerEl.clientWidth) || (this._els.floorView.clientWidth) || 300);
    var vpH = 280;
    if (this._planImgW && this._planImgH) {
      vpH = Math.ceil((availW / this._planImgW) * this._planImgH);
    }
    // Мягкий потолок по окну — без привязки к огромному maxHeight фасада из демо.
    var softMax = Math.max(240, Math.round((window.innerHeight || 800) * 0.78));
    vpH = Math.max(160, Math.min(vpH, softMax));
    vpDesk.style.height = vpH + 'px';
  };

  FacadeWidgetInstance.prototype._planFitAndCenter = function (viewport, els) {
    var vp = viewport || this._planEls.viewport;
    if (!vp || !this._planImgW || !this._planImgH) return;
    if (els === this._planEls || (!els && this._planEls && vp === this._planEls.viewport)) {
      this._layoutFloorView();
    }
    var vw = vp.clientWidth || 300;
    var vh = vp.clientHeight || 280;
    if (vh < 40) vh = 280;
    var fit = vw / this._planImgW;
    if (vh > 0 && fit * this._planImgH > vh) {
      fit = vh / this._planImgH;
    }
    this._planMinScale = fit;
    if (this._isPlanZoomEnabled()) {
      this._planMaxScale = Math.max(fit * 6, 6);
    } else {
      this._planMaxScale = fit;
    }
    this._planScale = fit;
    // Равные поля слева/справа и сверху/снизу внутри viewport.
    this._planTx = (vw - this._planImgW * this._planScale) / 2;
    this._planTy = (vh - this._planImgH * this._planScale) / 2;
    this._applyPlanTransform(els);
  };

  FacadeWidgetInstance.prototype._layoutCardView = function () {
    if (!isDesktopUi() || !this._els.cardView) return;
    // Высота по контенту — не options.maxHeight (в демо он огромный ради полной ширины фасада).
    this._els.cardView.style.minHeight = '';
    this._els.cardView.style.height = 'auto';
  };

  FacadeWidgetInstance.prototype._onResize = function () {
    if (this.destroyed) return;
    this._syncUiClasses();
    if (this.currentView === 'floor') {
      this._layoutFloorView();
      this._planFitAndCenter(this._planEls.viewport, this._planEls);
    } else if (this.currentView === 'card') {
      this._layoutCardView();
      if (this._cardEls.planViewport && this._cardEls.planViewport.dataset.loaded) {
        this._planFitAndCenter(this._cardEls.planViewport, { stage: this._cardEls.planViewport.querySelector('.fw-plan-stage'), viewport: this._cardEls.planViewport });
      }
    } else if (this.exploring) {
      this._clampTransform(this._els.exploreViewport);
      this._applyExploreTransform();
    } else {
      this._layoutFit();
    }
  };

  FacadeWidgetInstance.prototype._enterExplore = function () {
    if (this.destroyed || this.exploring) return;
    if (!allowsExploreMode()) return;
    this.exploring = true;
    this._applyScrollRevealIndex(-1);
    this._els.root.classList.add('is-explore');
    this._els.exploreLayer.setAttribute('aria-hidden', 'false');
    this._cloneStageIntoExplore();

    var vp = this._els.exploreViewport;
    var vw = vp.clientWidth || window.innerWidth;
    var vh = vp.clientHeight || window.innerHeight;
    var fit = Math.min(vw / this._imgW, vh / this._imgH);
    this._minScale = fit;
    this._scale = Math.min(Math.max(fit * 1.15, fit), this._maxScale);
    this._tx = (vw - this._imgW * this._scale) / 2;
    this._ty = (vh - this._imgH * this._scale) / 2;
    this._applyExploreTransform();

    acquireScrollLock();
    document.addEventListener('keydown', this._onKeyDown);
    if (this._els.closeBtn) this._els.closeBtn.focus();
  };

  FacadeWidgetInstance.prototype._exitExplore = function (fromDestroy) {
    if (!this.exploring) return;
    this.exploring = false;
    if (this._els.root) this._els.root.classList.remove('is-explore');
    if (this._els.exploreLayer) this._els.exploreLayer.setAttribute('aria-hidden', 'true');
    if (this._els.exploreViewport) this._els.exploreViewport.innerHTML = '';
    this._els.exploreStage = null;
    this._scale = 1;
    this._tx = 0;
    this._ty = 0;
    releaseScrollLock();
    document.removeEventListener('keydown', this._onKeyDown);
    if (!fromDestroy) {
      this._layoutFit();
      this._resumeRevealHighlight();
    }
  };

  FacadeWidgetInstance.prototype._onKeyDown = function (ev) {
    if (ev.key === 'Escape' && this.exploring) {
      ev.preventDefault();
      this._exitExplore();
    }
  };

  FacadeWidgetInstance.prototype._applyExploreTransform = function () {
    var stage = this._els.exploreStage;
    if (!stage) return;
    stage.style.width = this._imgW + 'px';
    stage.style.height = this._imgH + 'px';
    stage.style.transform = 'translate(' + this._tx + 'px,' + this._ty + 'px) scale(' + this._scale + ')';
  };

  FacadeWidgetInstance.prototype._clampTransform = function (viewport) {
    if (!viewport) return;
    var vw = viewport.clientWidth;
    var vh = viewport.clientHeight;
    var sw = this._imgW * this._scale;
    var sh = this._imgH * this._scale;

    if (sw <= vw) {
      this._tx = (vw - sw) / 2;
    } else {
      this._tx = Math.min(0, Math.max(vw - sw, this._tx));
    }
    if (sh <= vh) {
      this._ty = (vh - sh) / 2;
    } else {
      this._ty = Math.min(0, Math.max(vh - sh, this._ty));
    }
  };

  FacadeWidgetInstance.prototype._setScaleAt = function (nextScale, clientX, clientY, viewport) {
    nextScale = Math.max(this._minScale, Math.min(this._maxScale, nextScale));
    var rect = viewport.getBoundingClientRect();
    var cx = clientX - rect.left;
    var cy = clientY - rect.top;
    // точка под курсором в координатах stage до зума
    var sx = (cx - this._tx) / this._scale;
    var sy = (cy - this._ty) / this._scale;
    this._scale = nextScale;
    this._tx = cx - sx * this._scale;
    this._ty = cy - sy * this._scale;
    this._clampTransform(viewport);
    this._applyExploreTransform();
  };

  function mount(options) {
    options = options || {};
    var host = resolveEl(options.el);
    if (!host) {
      throw new Error('[FacadeWidget] el not found');
    }
    if (!options.homeId) {
      throw new Error('[FacadeWidget] homeId is required');
    }
    // Дефолты = поведение демо (остальное нормализуется в конструкторе).
    if (options.width == null) options.width = '100%';
    if (options.fadeMs == null && options.fadeDuration == null) options.fadeMs = 280;
    if (options.urlState == null) options.urlState = true;
    if (options.scrollReveal == null) options.scrollReveal = true;
    if (options.scrollRevealSpeed == null) options.scrollRevealSpeed = 1;
    if (options.exploreFullscreen == null) options.exploreFullscreen = true;
    if (options.floorPlan == null) options.floorPlan = { enabled: true };
    if (options.chessboard == null) options.chessboard = { enabled: true };
    if (options.floorPlanZoom == null && options.planZoom == null) {
      options.floorPlanZoom = { desktop: false, mobile: true };
    }
    var inst = new FacadeWidgetInstance(host, options);
    inst.mount();
    return inst;
  }

  var api = {
    mount: mount,
    version: '1.3.2'
  };

  global.FacadeWidget = api;
})(typeof window !== 'undefined' ? window : this);
