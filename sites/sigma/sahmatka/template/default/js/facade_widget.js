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
    consent: 'Нажимая кнопку «Забронировать», вы подтверждаете согласие на обработку персональных данных.',
    jk: 'Жилой комплекс',
    tower: 'Башня',
    floorLabel: 'Этаж',
    residence: 'Квартира',
    status: 'Статус',
    bookingSuccess: 'Ваша заявка успешно отправлена',
    bookingError: 'Не удалось отправить заявку'
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

  function formatRoomsK(rooms) {
    if (!rooms && rooms !== 0) return '';
    return String(rooms) + 'K';
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
  var TEXT_MAIN = '#1A1A1A';
  var TEXT_MUTED = '#666666';
  var BORDER_SOFT = '#D1D5D8';

  var WIDGET_CSS = [
    ':host { display: block; box-sizing: border-box; }',
    '*, *::before, *::after { box-sizing: border-box; }',
    '.fw-root { position: relative; width: 100%; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color: ' + TEXT_MAIN + '; }',
    '.fw-viewport { position: relative; overflow: hidden; background: transparent; touch-action: manipulation; -webkit-user-select: none; user-select: none; }',
    '.fw-stage { position: relative; transform-origin: 0 0; will-change: transform; margin: 0; width: 100%; }',
    '.fw-stage img { display: block; width: 100%; height: auto; border: 0; max-width: none; pointer-events: none; -webkit-user-drag: none; vertical-align: top; }',
    '.fw-stage svg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; overflow: visible; }',
    '.fw-poly { fill-opacity: 0.12; stroke-width: 1.5; stroke-opacity: 0.55; cursor: pointer; transition: fill-opacity 0.18s ease, stroke-width 0.18s ease, stroke-opacity 0.18s ease; outline: none; }',
    '.fw-poly.is-hover, .fw-poly.is-active, .fw-poly:focus-visible { fill-opacity: 0.45; stroke-opacity: 1; stroke-width: 2.5; }',
    '.fw-poly.is-scroll-reveal { fill-opacity: 0.52; stroke-opacity: 1; stroke-width: 2.75; }',
    '@media (prefers-reduced-motion: reduce) { .fw-poly { transition: none; } }',
    '.fw-tooltip { position: absolute; z-index: 5; pointer-events: none; max-width: min(240px, 80%); padding: 6px 10px; border-radius: 6px; background: rgba(20,20,20,0.88); color: #fff; font-size: 13px; line-height: 1.35; opacity: 0; transform: translate(-50%, -120%); transition: opacity 0.1s ease; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }',
    '.fw-tooltip.is-visible { opacity: 1; }',
    '.fw-btn { appearance: none; border: 0; border-radius: 8px; background: rgba(255,255,255,0.95); color: #111; box-shadow: 0 1px 6px rgba(0,0,0,0.22); cursor: pointer; font: inherit; line-height: 1; margin: 0; padding: 0; }',
    '.fw-btn-close { position: absolute; top: 12px; right: 12px; z-index: 20; width: 40px; height: 40px; padding: 0; display: none; align-items: center; justify-content: center; }',
    '.fw-btn-close svg { display: block; width: 18px; height: 18px; }',
    '.fw-root.is-explore .fw-btn-close { display: flex; }',
    '.fw-msg { padding: 16px; font-size: 14px; line-height: 1.45; color: #444; background: #f4f4f4; border: 1px solid #ddd; border-radius: 6px; }',
    '.fw-msg.is-error { color: #8a1f11; background: #fdecea; border-color: #f5c2c0; }',
    '.fw-msg.is-loading { color: #555; }',
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
    '.fw-view { display: block; width: 100%; }',
    '.fw-view--floor, .fw-view--card { display: none; }',
    '.fw-root.is-view-floor .fw-view--facade { display: none; }',
    '.fw-root.is-view-floor .fw-view--floor { display: flex; flex-direction: column; }',
    '.fw-root.is-view-card.is-desktop .fw-view--facade, .fw-root.is-view-card.is-desktop .fw-view--floor { display: none; }',
    '.fw-root.is-view-card.is-desktop .fw-view--card { display: block; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-stage-wrap { display: none !important; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-plan-host { display: flex !important; flex: 1; flex-direction: column; min-height: 0; overflow: hidden; }',
    '.fw-root.is-mobile-ui.is-view-floor .fw-explore-inner { background: #f7f7f7; }',
    '.fw-explore-plan-host { display: none; }',
    '.fw-explore-stage-wrap { flex: 1; min-height: 0; display: flex; flex-direction: column; }',
    '.fw-explore-inner { display: flex; flex-direction: column; }',
    /* поэтажный план — Stage 3 */
    '.fw-plan-layer { display: none; flex-direction: column; background: #f7f7f7; color: ' + TEXT_MAIN + '; min-height: 320px; width: 100%; }',
    '.fw-root.is-view-floor .fw-plan-layer, .fw-root.is-view-card .fw-plan-layer { display: flex; }',
    '.fw-plan-head { flex: 0 0 auto; display: flex; flex-direction: column; align-items: flex-start; gap: 8px; padding: 16px 16px 8px; }',
    '.fw-plan-back { appearance: none; border: 0; background: #A8BEC6; color: #fff; border-radius: 999px; padding: 10px 18px; font: inherit; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }',
    '.fw-plan-back:hover, .fw-plan-back:focus-visible { background: ' + ACCENT + '; }',
    '.fw-plan-title { font-size: 22px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #3d3d3d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }',
    '.fw-plan-viewport { position: relative; flex: 1 1 auto; overflow: hidden; touch-action: none; display: flex; align-items: center; justify-content: center; min-height: 280px; background: #fff; }',
    '.fw-plan-stage { position: absolute; left: 0; top: 0; transform-origin: 0 0; cursor: grab; touch-action: none; -webkit-user-select: none; user-select: none; }',
    '.fw-plan-stage.is-dragging { cursor: grabbing; }',
    '.fw-plan-stage.is-readonly { cursor: default; }',
    '.fw-plan-stage img { display: block; max-width: none; border: 0; pointer-events: none; -webkit-user-drag: none; }',
    '.fw-plan-stage svg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; overflow: visible; }',
    '.fw-apt-poly.is-dim { fill-opacity: 0.06 !important; stroke-opacity: 0.2 !important; pointer-events: none; }',
    '.fw-apt-poly.is-highlight { fill: ' + ACCENT + ' !important; stroke: ' + ACCENT + ' !important; fill-opacity: 0.28 !important; stroke-opacity: 0.95 !important; stroke-width: 2.5 !important; }',
    '.fw-plan-banner { position: absolute; left: 50%; bottom: 16px; transform: translateX(-50%); max-width: min(92%, 520px); padding: 10px 16px; border-radius: 8px; background: rgba(20,20,20,0.85); color: #fff; font-size: 13px; text-align: center; line-height: 1.4; }',
    '.fw-plan-viewport .fw-msg { max-width: min(92%, 420px); text-align: center; }',
    '.fw-apt-tooltip { position: absolute; z-index: 8; pointer-events: none; padding: 10px 16px; border-radius: 10px; background: rgba(45,45,48,0.92); color: #fff; text-align: center; line-height: 1.35; opacity: 0; transform: translate(-50%, calc(-100% - 10px)); transition: opacity 0.12s ease; white-space: nowrap; }',
    '.fw-apt-tooltip.is-visible { opacity: 1; }',
    '.fw-apt-tooltip__code { display: block; font-weight: 600; font-size: 14px; margin-bottom: 2px; }',
    '.fw-apt-tooltip__spec { display: block; font-size: 13px; opacity: 0.95; }',
    /* карточка — цветопроба макета */
    '.fw-card { display: grid; grid-template-columns: minmax(300px, 380px) 1fr; gap: 0; background: #fff; min-height: 480px; overflow: hidden; }',
    '@media (max-width: 899.98px) { .fw-card { grid-template-columns: 1fr; min-height: auto; } }',
    '.fw-card-side { padding: 28px 32px 36px; border-right: 1px solid #ececec; display: flex; flex-direction: column; gap: 14px; background: #fff; }',
    '@media (max-width: 899.98px) { .fw-card-side { border-right: 0; border-bottom: 1px solid #ececec; padding: 16px; } }',
    '.fw-card-head-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }',
    '.fw-card-back { appearance: none; border: 0; background: #A8BEC6; color: #fff; border-radius: 999px; padding: 10px 16px; font: inherit; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }',
    '.fw-card-back:hover { background: ' + ACCENT + '; }',
    '.fw-card-tools { display: flex; gap: 8px; }',
    '.fw-card-tool { width: 36px; height: 36px; border-radius: 50%; border: 1px solid ' + BORDER_SOFT + '; background: #fff; color: ' + TEXT_MAIN + '; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; }',
    '.fw-card-tool svg { width: 18px; height: 18px; }',
    '.fw-card-title { font-size: 18px; font-weight: 500; color: ' + TEXT_MAIN + '; margin: 4px 0 0; line-height: 1.3; }',
    '.fw-card-spec { font-size: 34px; font-weight: 700; color: ' + TEXT_MAIN + '; line-height: 1.15; margin: 0; }',
    '.fw-card-meta { font-size: 14px; line-height: 1.6; color: ' + TEXT_MUTED + '; margin: 0; }',
    '.fw-card-meta div { margin-bottom: 2px; }',
    '.fw-card-meta strong { font-weight: 600; color: ' + TEXT_MUTED + '; }',
    '.fw-card-price { font-size: 30px; font-weight: 700; color: ' + TEXT_MAIN + '; margin: 8px 0 4px; }',
    '.fw-card-status { padding: 8px 12px; border-radius: 8px; font-size: 14px; font-weight: 600; }',
    '.fw-card-form { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; }',
    '.fw-card-field { display: flex; flex-direction: column; gap: 4px; }',
    '.fw-card-form input, .fw-card-form textarea { width: 100%; border: 1px solid ' + BORDER_SOFT + '; border-radius: 12px; padding: 14px 16px; font: inherit; font-size: 14px; background: #fff; color: ' + TEXT_MAIN + '; }',
    '.fw-card-form textarea { min-height: 96px; resize: vertical; }',
    '.fw-card-form input:focus, .fw-card-form textarea:focus { outline: 2px solid rgba(118,147,157,0.35); border-color: ' + ACCENT + '; }',
    '.fw-card-form input.is-error, .fw-card-form textarea.is-error { border-color: #c62828; background: #fff8f8; outline: 2px solid rgba(198,40,40,0.18); }',
    '.fw-card-field-error { display: none; font-size: 12px; line-height: 1.35; color: #c62828; padding: 0 2px; }',
    '.fw-card-field-error.is-visible { display: block; }',
    '.fw-card-submit { appearance: none; border: 0; border-radius: 999px; background: ' + ACCENT + '; color: #fff; font: inherit; font-size: 16px; font-weight: 600; padding: 16px 24px; cursor: pointer; margin-top: 4px; }',
    '.fw-card-submit:hover { background: ' + ACCENT_HOVER + '; }',
    '.fw-card-submit:disabled { opacity: 0.6; cursor: wait; }',
    '.fw-card-consent { font-size: 11px; line-height: 1.45; color: #999; margin: 0; }',
    '.fw-card-msg { font-size: 14px; padding: 10px 12px; border-radius: 8px; }',
    '.fw-card-msg.is-ok { background: #e8f5e9; color: #2e7d32; }',
    '.fw-card-msg.is-err { background: #fdecea; color: #8a1f11; }',
    '.fw-card-msg ul { margin: 6px 0 0; padding-left: 18px; }',
    '.fw-card-visual { display: flex; flex-direction: column; padding: 24px 28px 28px; min-height: 360px; background: #fafafa; }',
    '@media (max-width: 899.98px) { .fw-card-visual { padding: 16px; min-height: 280px; } }',
    '.fw-card-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }',
    '.fw-card-tab { appearance: none; border: 1px solid ' + ACCENT + '; background: #fff; color: ' + ACCENT + '; border-radius: 999px; padding: 10px 22px; font: inherit; font-size: 14px; cursor: pointer; }',
    '.fw-card-tab.is-active { background: ' + ACCENT + '; color: #fff; border-color: ' + ACCENT + '; }',
    '.fw-card-panel { display: none; flex: 1; flex-direction: column; min-height: 0; }',
    '.fw-card-panel.is-active { display: flex; }',
    '.fw-card-pln-label { text-align: center; font-size: 13px; color: ' + TEXT_MUTED + '; margin: 8px 0; }',
    '.fw-card-pln-img { display: block; max-width: 100%; max-height: min(70vh, 520px); margin: 0 auto; object-fit: contain; }',
    '.fw-card-floor-vp { position: relative; flex: 1; min-height: 280px; overflow: hidden; background: #fff; border-radius: 8px; }',
    '.fw-card-layer-mobile { display: none; position: fixed; inset: 0; z-index: 2147483002; background: #fff; overflow: auto; -webkit-overflow-scrolling: touch; }',
    '.fw-root.is-view-card.is-mobile-ui .fw-card-layer-mobile { display: block; }',
    '@media print { .fw-card-back, .fw-card-tools, .fw-card-tabs, .fw-card-form, .fw-card-consent, .fw-btn-close { display: none !important; } }'
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

    this._onResize = this._onResize.bind(this);
    this._onKeyDown = this._onKeyDown.bind(this);
    this._ro = null;

    // Stage 3: навигация facade | floor | card
    this.currentView = 'facade';
    this._navContext = {};
    this._cardData = null;
    this._cardTab = 'layout';
    this._cardHighlightId = 0;

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
    root.className = 'fw-root';
    root.setAttribute('role', 'region');
    root.setAttribute('aria-label', 'Интерактивный фасад');
    this.shadow.appendChild(root);
    this._els.root = root;

    var msg = document.createElement('div');
    msg.className = 'fw-msg is-loading';
    msg.textContent = this.locale.loading;
    root.appendChild(msg);
    this._els.msg = msg;

    this._load().then(function () {
      if (self.destroyed) return;
      self._render();
    }).catch(function (err) {
      if (self.destroyed) return;
      self._showError((err && err.message) || self.locale.error);
    });

    return this;
  };

  FacadeWidgetInstance.prototype.destroy = function () {
    if (this.destroyed) return;
    this.destroyed = true;
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

    this._layoutFit();
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
        var caption = this.sectionCaptions[sample.dataset.section] || (this.locale.section + ' ' + sample.dataset.section);
        var text = caption + ' · ' + this.locale.floor + ' ' + sample.dataset.floor;
        if (sample.dataset.label) text += ' — ' + sample.dataset.label;
        tooltipEl.textContent = text;
        tooltipEl.classList.add('is-visible');
        var rect = viewport.getBoundingClientRect();
        var x = clientX - rect.left;
        var y = clientY - rect.top;
        var tw = tooltipEl.offsetWidth || 120;
        var th = tooltipEl.offsetHeight || 28;
        x = Math.max(tw / 2 + 4, Math.min(rect.width - tw / 2 - 4, x));
        y = Math.max(th + 8, Math.min(rect.height - 8, y));
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
  /* Stage 3: навигация + поэтажный план + карточка квартиры             */
  /* ------------------------------------------------------------------ */

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
    root.classList.remove('is-view-facade', 'is-view-floor', 'is-view-card');
    root.classList.add('is-view-' + this.currentView);
    root.classList.toggle('is-plan-open', this.currentView === 'floor' || this.currentView === 'card');
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
    this.currentView = view;
    this._navContext = Object.assign({}, this._navContext, ctx || {});
    if (view === 'floor') this.planOpen = true;
    if (view === 'card') this.planOpen = false;
    this._syncUiClasses();
    this._reparentPlanLayer();
    document.removeEventListener('keydown', this._onNavKeyDown);
    if (view !== 'facade') {
      document.addEventListener('keydown', this._onNavKeyDown);
    }
  };

  FacadeWidgetInstance.prototype._navPop = function (fromDestroy) {
    if (this.currentView === 'card') {
      this.currentView = 'floor';
      this.planOpen = true;
      this._cardData = null;
      this._syncUiClasses();
      this._reparentPlanLayer();
      if (!fromDestroy) this._resumeRevealHighlight();
      return;
    }
    if (this.currentView === 'floor') {
      this._closeFloorPlan(fromDestroy);
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
    backBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>' + (this.locale.back || 'Назад') + '</span>';
    backBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      self._navPop();
    });
    head.appendChild(backBtn);

    var title = document.createElement('div');
    title.className = 'fw-plan-title';
    head.appendChild(title);

    layer.appendChild(head);

    var viewport = document.createElement('div');
    viewport.className = 'fw-plan-viewport';
    layer.appendChild(viewport);

    parent.appendChild(layer);

    viewport.addEventListener('wheel', function (ev) {
      if (!self.planOpen || self.currentView !== 'floor') return;
      ev.preventDefault();
      var factor = ev.deltaY < 0 ? 1.08 : 1 / 1.08;
      self._setPlanScaleAt(self._planScale * factor, ev.clientX, ev.clientY, viewport);
    }, { passive: false });

    this._planEls.layer = layer;
    this._planEls.title = title;
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
    if (this.destroyed || this.planOpen) return;
    this._applyScrollRevealIndex(-1);
    this._navContext.floorPayload = payload;
    this._navPush('floor', payload);

    if (!isDesktopUi()) {
      if (!this.exploring) {
        this._enterExplore();
      }
      this._reparentPlanLayer();
      if (this._scrollLockHeld === 0 && this.exploring) {
        /* explore уже держит lock */
      }
    }

    if (this._planEls.layer) this._planEls.layer.setAttribute('aria-hidden', 'false');
    if (this._planEls.backBtn) this._planEls.backBtn.focus();
    this._layoutFloorView();
    this._loadFloorPlan(payload);
  };

  FacadeWidgetInstance.prototype._closeFloorPlan = function (fromDestroy) {
    if (this.currentView !== 'floor' && !this.planOpen) return;
    this.planOpen = false;
    this.currentView = 'facade';
    this._navContext = {};
    this._syncUiClasses();
    if (this._planEls.layer) this._planEls.layer.setAttribute('aria-hidden', 'true');
    if (this._planEls.viewport) this._planEls.viewport.innerHTML = '';
    if (this._planEls.title) this._planEls.title.textContent = '';
    this._planData = null;
    this._planPointers.clear();
    document.removeEventListener('keydown', this._onNavKeyDown);
    document.removeEventListener('keydown', this._onPlanKeyDown);
    if (!fromDestroy) {
      this._resumeRevealHighlight();
    }
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
    if (els.title) {
      var cap = this.sectionCaptions[String(payload.section)] || '';
      els.title.textContent = cap || (this.data && this.data.title) || ('' + payload.section);
    }

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
    if (els.title && !options.forCard) {
      els.title.textContent = titleText;
    }

    this._planImgW = data.imageWidth;
    this._planImgH = data.imageHeight;

    var stage = document.createElement('div');
    stage.className = 'fw-plan-stage' + (options.readonly ? ' is-readonly' : '');

    var img = document.createElement('img');
    img.src = data.imageUrl;
    img.alt = titleText || 'План этажа';
    img.draggable = false;
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

  FacadeWidgetInstance.prototype._planFitAndCenter = function (viewport, els) {
    var vp = viewport || this._planEls.viewport;
    if (!vp || !this._planImgW || !this._planImgH) return;
    var vw = vp.clientWidth || 300;
    var vh = vp.clientHeight || 280;
    var fit = Math.min(vw / this._planImgW, vh / this._planImgH);
    this._planMinScale = fit;
    this._planMaxScale = Math.max(fit * 6, 6);
    this._planScale = fit;
    this._planTx = (vw - this._planImgW * this._planScale) / 2;
    this._planTy = (vh - this._planImgH * this._planScale) / 2;
    this._applyPlanTransform(els);
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
    if (sh <= vh) {
      this._planTy = (vh - sh) / 2;
    } else {
      this._planTy = Math.min(0, Math.max(vh - sh, this._planTy));
    }
  };

  FacadeWidgetInstance.prototype._setPlanScaleAt = function (nextScale, clientX, clientY, viewport, els) {
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
    var code = el.dataset.displayCode || el.dataset.apartmentNum;
    var codeLine = document.createElement('span');
    codeLine.className = 'fw-apt-tooltip__code';
    codeLine.textContent = '\u2116' + code;
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
        var x = clientX - rect.left;
        var y = clientY - rect.top;
        var tw = tooltipEl.offsetWidth || 120;
        var th = tooltipEl.offsetHeight || 40;
        x = Math.max(tw / 2 + 4, Math.min(rect.width - tw / 2 - 4, x));
        y = Math.max(th + 12, Math.min(rect.height - 8, y));
        tooltipEl.style.left = x + 'px';
        tooltipEl.style.top = y + 'px';
      }
    } else if (tooltipEl) {
      tooltipEl.classList.remove('is-visible');
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
      self._renderApartmentCard(json, payload);
    }).catch(function (err) {
      if (self.destroyed) return;
      shell.innerHTML = '<div class="fw-msg is-error">' + self.locale.error + (err && err.message ? ' (' + err.message + ')' : '') + '</div>';
    });
  };

  FacadeWidgetInstance.prototype._renderApartmentCard = function (data, navPayload) {
    var self = this;
    var shells = [this._cardEls.shell, this._cardEls.shellMobile].filter(Boolean);
    shells.forEach(function (shell) {
      shell.innerHTML = '';
      self._buildCardContent(shell, data, navPayload);
    });
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
    backBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>' + (L.backToChoice || 'Назад к выбору') + '</span>';
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
    printBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>';
    printBtn.addEventListener('click', function () { window.print(); });
    tools.appendChild(printBtn);
    headRow.appendChild(tools);
    side.appendChild(headRow);

    var title = document.createElement('h2');
    title.className = 'fw-card-title';
    title.textContent = L.bookingTitle || 'Заявка на бронирование резиденции';
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
      makeField('input', { type: 'tel', name: 'phone', placeholder: L.phone || 'Телефон', required: true, autocomplete: 'tel' });
      makeField('textarea', { name: 'message', placeholder: L.message || 'Сообщение' });

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
    } else {
      var digits = phone.replace(/\D/g, '');
      var phoneOk = /^(?:\+7|8)\d{10}$/.test(phone.replace(/[\s\-()]/g, ''))
        || (digits.length === 11 && (digits[0] === '7' || digits[0] === '8'));
      if (!phoneOk) errors.phone = 'Номер телефона не соответствует формату';
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
        var pts = Array.from(self._planPointers.values());
        var dx = pts[0].x - pts[1].x;
        var dy = pts[0].y - pts[1].y;
        self._planPinchStartDist = Math.hypot(dx, dy) || 1;
        self._planPinchStartScale = self._planScale;
        self._planPanStart = null;
        return;
      }

      var poly = targetPoly(ev);
      self._planPanStart = { x: ev.clientX, y: ev.clientY, tx: self._planTx, ty: self._planTy, poly: poly, time: Date.now() };
    });

    stage.addEventListener('pointermove', function (ev) {
      if (self.destroyed || self.currentView !== 'floor') return;
      if (self._planPointers.has(ev.pointerId)) {
        self._planPointers.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
      }

      if (self._planPointers.size === 2) {
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
    var maxH = typeof maxHOpt === 'number' ? maxHOpt : Math.min(window.innerHeight * 0.9, 900);

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

  FacadeWidgetInstance.prototype._layoutFloorView = function () {
    if (!this._els.floorView || !isDesktopUi()) return;
    var maxH = typeof this.options.maxHeight === 'number'
      ? this.options.maxHeight
      : Math.min(window.innerHeight * 0.85, 720);
    this._els.floorView.style.minHeight = maxH + 'px';
    if (this._planEls.viewport) {
      this._planEls.viewport.style.minHeight = (maxH - 80) + 'px';
    }
  };

  FacadeWidgetInstance.prototype._layoutCardView = function () {
    if (!isDesktopUi() || !this._els.cardView) return;
    var maxH = typeof this.options.maxHeight === 'number'
      ? this.options.maxHeight
      : Math.min(window.innerHeight * 0.9, 800);
    this._els.cardView.style.minHeight = maxH + 'px';
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
    var inst = new FacadeWidgetInstance(host, options);
    inst.mount();
    return inst;
  }

  var api = {
    mount: mount,
    version: '1.1.0'
  };

  global.FacadeWidget = api;
})(typeof window !== 'undefined' ? window : this);
