/**
 * GenplanWidget — встраиваемый read-only виджет интерактивного плана ЖК (EM).
 * Изоляция: Shadow DOM. Без Leaflet / внешних deps.
 * API: GenplanWidget.mount({ el, kvartalId, apiBase?, width?, maxHeight?, minZoom?, maxZoom?,
 *   offsetX?, offsetY?, offsetBottom?, idleHighlight?, highlight?, … })
 * idleHighlight (дефолт true): по очереди подсвечивает дома, пока курсор вне карты / мобилка не в explore.
 */
(function (global) {
  'use strict';

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
    ariaLabel: 'Интерактивный план'
  };

  var HL_DEFAULT = {
    color: '#5B8FB8',
    opacity: 0.58,
    idleOpacity: 0,
    hoverOpacity: 0.58,
    revealOpacity: 0.58
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
      console.warn('[GenplanWidget] width: "100%" или число px. Fallback 100%. Got:', width);
    }
    return { type: 'pct', value: 100 };
  }

  function normalizeOffset01(v, fallback) {
    if (v == null || v === '') return fallback;
    var n = Number(v);
    if (!isFinite(n)) return fallback;
    if (n < 0) return 0;
    if (n > 1) return 1;
    return n;
  }

  function normalizeMaxHeight(v) {
    if (typeof v === 'number' && isFinite(v) && v > 0) return v;
    return 600;
  }

  function normalizeZoomMult(v, fallback) {
    var n = Number(v);
    if (!isFinite(n) || n <= 0) return fallback;
    return n;
  }

  function normalizeOffsetPx(v) {
    if (v == null || v === '') return null;
    var n = Number(v);
    if (!isFinite(n)) return null;
    return n;
  }

  function detectApiBase() {
    if (!SCRIPT_SRC) return '';
    try {
      var u = new URL(SCRIPT_SRC);
      // …/sahmatka/template/default/js/genplan_widget.js → …/sahmatka/ajax_router.php?ctr=genplans
      var path = u.pathname.replace(/\/template\/default\/js\/[^/]+$/, '');
      return u.origin + path + '/ajax_router.php?ctr=genplans';
    } catch (e) {
      return '';
    }
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
   * highlight: { color, opacity, idleOpacity?, hoverOpacity?, revealOpacity? }
   * Обводка = тот же color, opacity×0.7, stroke-width 2px (как facadeHighlight).
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

  function prefersReducedMotion() {
    try {
      return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {
      return false;
    }
  }

  /** Explore (fullscreen pan/zoom + ✕) — мобильный UX + узкий viewport. */
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

  function isCoarsePointer() {
    try {
      return window.matchMedia('(hover: none) and (pointer: coarse)').matches
        || window.matchMedia('(max-width: 768px)').matches;
    } catch (e) {
      return allowsExploreMode();
    }
  }

  function pointsToSvgAttr(points, imageHeight) {
    var parts = [];
    var h = Number(imageHeight) || 0;
    for (var i = 0; i < points.length; i++) {
      var p = points[i];
      if (!p || p.length < 2) continue;
      var x = Number(p[0]);
      var y = h > 0 ? (h - Number(p[1])) : Number(p[1]);
      parts.push(x + ',' + y);
    }
    return parts.join(' ');
  }

  function polygonCentroid(points) {
    var sx = 0;
    var sy = 0;
    var n = 0;
    for (var i = 0; i < points.length; i++) {
      var p = points[i];
      if (!p || p.length < 2) continue;
      sx += Number(p[0]);
      sy += Number(p[1]);
      n += 1;
    }
    if (!n) return { x: 0, y: 0 };
    return { x: sx / n, y: sy / n };
  }

  function markerToneColor(tone) {
    if (tone === 'ok') return '#28a745';
    if (tone === 'warn') return '#e53935';
    if (tone === 'wait') return '#2f80ed';
    if (tone === 'danger') return '#dc3545';
    return '#9aa0a6';
  }

  function statusToneColor(tone) {
    return markerToneColor(tone);
  }

  function flipY(y, imageHeight) {
    var h = Number(imageHeight) || 0;
    return h > 0 ? (h - Number(y)) : Number(y);
  }

  var UI_STACK = 'Arial, Helvetica, sans-serif';
  var HL = HL_DEFAULT.color;
  var LABEL_LIFT_PX = 18;

  var WIDGET_CSS = [
    ':host { display: block; box-sizing: border-box; }',
    '*, *::before, *::after { box-sizing: border-box; }',
    '.gw-root { position: relative; width: 100%; font-family: ' + UI_STACK + '; color: #1a1a1a; background: #f0f2f4; }',
    '.gw-root button { font-family: inherit; }',
    '.gw-viewport { position: relative; overflow: hidden; background: #f0f2f4; touch-action: manipulation; -webkit-user-select: none; user-select: none; margin: 0 auto; }',
    '.gw-stage { position: relative; transform-origin: 0 0; will-change: transform; margin: 0; width: 100%; }',
    /* pan/zoom жестами: десктоп всегда; мобилка — только в explore */
    '.gw-root.is-pannable:not(.is-coarse) .gw-viewport { cursor: grab; touch-action: none; }',
    '.gw-root.is-pannable:not(.is-coarse) .gw-stage.is-dragging { cursor: grabbing; }',
    '.gw-root.is-explore .gw-explore-inner .gw-viewport { touch-action: none; cursor: grab; }',
    '.gw-root.is-explore .gw-explore-inner .gw-stage.is-dragging { cursor: grabbing; }',
    '.gw-root.is-coarse:not(.is-explore) .gw-viewport { touch-action: manipulation; cursor: default; }',
    '.gw-stage img { display: block; width: 100%; height: auto; border: 0; max-width: none; pointer-events: none; -webkit-user-drag: none; vertical-align: top; }',
    '.gw-stage svg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; overflow: visible; }',
    /* pointer-events:all — иначе при idle fill-opacity:0 клик «пролетает» сквозь polygon (SVG visiblePainted) */
    '.gw-poly { fill: var(--gw-hl-color, ' + HL + '); fill-opacity: var(--gw-idle-opacity, 0); stroke: var(--gw-hl-color, ' + HL + '); stroke-width: 2; stroke-opacity: 0; cursor: pointer; pointer-events: all; transition: fill-opacity 0.18s ease, stroke-opacity 0.18s ease, stroke-width 0.18s ease; outline: none; }',
    '.gw-poly.is-hover, .gw-poly.is-active, .gw-poly.is-showcase, .gw-poly:focus-visible { fill: var(--gw-hl-color, ' + HL + ') !important; stroke: var(--gw-hl-color, ' + HL + ') !important; fill-opacity: var(--gw-hl-opacity, 0.58); stroke-opacity: var(--gw-stroke-opacity, 0.4); stroke-width: 2; }',
    '@media (prefers-reduced-motion: reduce) { .gw-poly { transition: none; } }',
    '.gw-labels-overlay { position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none; z-index: 5; overflow: visible; }',
    '.gw-label { position: absolute; transform: translate(-50%, -100%); transform-origin: center bottom; pointer-events: auto; z-index: 5; cursor: pointer; -webkit-tap-highlight-color: transparent; }',
    '.gw-label.is-expanded { z-index: 200; }',
    '.gw-label.is-expanded.is-below { transform-origin: center top; }',
    /* пока открыт один tooltip — соседние chips не перехватывают hit (иначе «не тот дом») */
    '.gw-labels-overlay.has-expanded .gw-label:not(.is-expanded) { pointer-events: none !important; }',
    '.gw-label__box { display: inline-block; width: fit-content; max-width: 220px; text-align: left; background: #fff; border-radius: 6px; box-shadow: 0 1px 5px rgba(0,0,0,0.18); overflow: hidden; transition: border-radius 0.28s ease, box-shadow 0.28s ease, background 0.28s ease; }',
    '.gw-label.is-expanded .gw-label__box { background: rgba(255,255,255,0.9); border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.18); box-sizing: border-box; width: 260px; min-width: 260px; max-width: 260px; }',
    '.gw-label__head { display: inline-flex; align-items: center; gap: 6px; padding: 4px 9px; color: #1a1a1a; font-size: 13px; font-weight: 600; line-height: 1.25; white-space: nowrap; width: fit-content; max-width: 100%; }',
    '.gw-label.is-compact .gw-label__head { padding: 4px 6px; }',
    '.gw-label.is-expanded .gw-label__head { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; padding: 12px 12px 8px; white-space: normal; width: 100%; max-width: none; box-sizing: border-box; border-bottom: 1px solid #000; }',
    '.gw-label__tri { width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 8px solid #9aa0a6; flex: 0 0 auto; }',
    '.gw-label__text { flex: 0 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; }',
    '.gw-label.is-expanded .gw-label__text { overflow: visible; text-overflow: unset; white-space: normal; flex: 1 1 auto; min-width: 0; }',
    '.gw-label:not(.is-expanded) .gw-label__status { display: none !important; }',
    '.gw-label__status { font-weight: 400; font-size: 12px; line-height: 1.25; }',
    '.gw-label.is-expanded .gw-label__status { display: inline; }',
    '.gw-label__body-wrap { display: grid; grid-template-rows: 0fr; max-width: 0; min-width: 0; overflow: hidden; opacity: 0; transition: grid-template-rows 0.28s ease, max-width 0.28s ease, opacity 0.22s ease; }',
    '.gw-label.is-expanded .gw-label__body-wrap { grid-template-rows: 1fr; max-width: none; width: 100%; opacity: 1; }',
    '.gw-label__body { overflow: hidden; min-height: 0; font-size: 12px; line-height: 1.4; color: #333; padding: 0 12px; box-sizing: border-box; width: 100%; transition: padding 0.28s ease; }',
    '.gw-label.is-expanded .gw-label__body { padding: 8px 12px 12px; }',
    '.gw-label__content { margin-bottom: 6px; }',
    '.gw-label__meta-line { display: block; color: #555; margin-bottom: 4px; }',
    '.gw-label__badges { display: flex; flex-wrap: wrap; gap: 8px; margin: 8px 0; }',
    '.gw-label__badge { display: inline-block; padding: 4px 8px; border: 1px solid #e53935; border-radius: 6px; font-size: 11px; color: #444; background: rgba(255,255,255,0.6); }',
    '.gw-label__apts { display: flex; flex-direction: column; gap: 4px; margin: 6px 0; }',
    '.gw-label__apts a { color: #056bf5; text-decoration: none; font-size: 12px; }',
    '.gw-label__apts a:hover { text-decoration: underline; }',
    '.gw-label__cta { display: block; margin-top: 10px; padding: 10px 12px; border-radius: 6px; background: #e53935; color: #fff !important; font-size: 13px; font-weight: 600; text-align: center; text-decoration: none !important; }',
    '.gw-label__cta:hover { background: #c62828; }',
    '.gw-root.is-coarse:not(.is-explore) .gw-label { pointer-events: none !important; }',
    '.gw-root.is-coarse.is-explore .gw-label { pointer-events: auto; }',
    '.gw-root.is-coarse .gw-label:not(.is-expanded) .gw-label__head { gap: 4px; max-width: 120px; padding: 2px 6px; font-size: 10px; }',
    '.gw-root.is-coarse .gw-label__tri { border-left-width: 4px; border-right-width: 4px; border-bottom-width: 7px; }',
    '.gw-root.is-coarse .gw-label.is-expanded .gw-label__box { width: min(280px, calc(100vw - 24px)); min-width: min(280px, calc(100vw - 24px)); max-width: min(280px, calc(100vw - 24px)); }',
    '.gw-root.is-coarse .gw-label.is-expanded .gw-label__head { font-size: 11px; padding: 10px 10px 6px; max-width: none; }',
    '.gw-root.is-coarse .gw-label.is-expanded .gw-label__body { font-size: 11px; padding: 6px 10px 10px; }',
    '@media (prefers-reduced-motion: reduce) { .gw-label__box, .gw-label__body-wrap, .gw-label__body { transition: none; } }',
    '.gw-btn { appearance: none; border: 0; border-radius: 8px; background: rgba(255,255,255,0.95); color: #111; box-shadow: 0 1px 6px rgba(0,0,0,0.22); cursor: pointer; font: inherit; line-height: 1; margin: 0; padding: 0; }',
    '.gw-btn-close { position: absolute; top: 12px; right: 12px; z-index: 20; width: 40px; height: 40px; padding: 0; display: none; align-items: center; justify-content: center; }',
    '.gw-btn-close svg { display: block; width: 18px; height: 18px; }',
    '.gw-root.is-explore .gw-btn-close { display: flex; }',
    '.gw-btn-explore { position: absolute; right: 12px; bottom: 12px; z-index: 6; height: 40px; padding: 0 14px; display: none; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; }',
    '.gw-root.is-coarse:not(.is-explore) .gw-btn-explore { display: inline-flex; }',
    '.gw-msg { padding: 16px; font-size: 14px; line-height: 1.45; color: #444; background: transparent; border: 0; border-radius: 0; }',
    '.gw-msg.is-error { color: #8a1f11; background: #fdecea; border: 1px solid #f5c2c0; border-radius: 6px; }',
    '.gw-msg.is-loading { color: #666; background: transparent; border: 0; box-shadow: none; margin: 0; }',
    '.gw-root.is-boot-loading { min-height: 240px; background: #fff; display: flex; align-items: center; justify-content: center; }',
    '.gw-root.is-boot-loading > .gw-msg.is-loading { padding: 24px; font-size: 15px; font-weight: 500; color: #666; text-align: center; }',
    '.gw-explore-layer { display: none; position: fixed; inset: 0; z-index: 2147483000; background: rgba(0,0,0,0.72); padding: 0; }',
    '.gw-root.is-explore .gw-explore-layer { display: block; }',
    '.gw-explore-inner { position: absolute; inset: 0; width: 100%; height: 100%; overflow: hidden; background: #111; }',
    '@media (min-width: 900px) {',
    '  .gw-explore-inner { inset: 5vh auto auto 50%; left: 50%; top: 5vh; width: min(96vw, 1400px); height: 90vh; transform: translateX(-50%); border-radius: 10px; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.45); }',
    '}',
    '.gw-explore-inner .gw-viewport { width: 100%; height: 100%; background: #1a1a1a; margin: 0; }',
    '.gw-explore-inner .gw-stage { cursor: grab; }',
    '.gw-explore-inner .gw-stage.is-dragging { cursor: grabbing; }',
    '.gw-root.is-explore .gw-btn-explore { display: none !important; }'
  ].join('\n');

  function GenplanWidgetInstance(host, options) {
    this.host = host;
    this.options = options;
    this.shadow = null;
    this.data = null;
    this.destroyed = false;
    this.exploring = false;
    this.widthSpec = normalizeWidth(options.width);
    this.maxHeight = normalizeMaxHeight(options.maxHeight);
    this.minZoom = normalizeZoomMult(options.minZoom, 1);
    this.maxZoom = Math.max(this.minZoom, normalizeZoomMult(options.maxZoom, 4));
    this._offsetXFrac = normalizeOffset01(options.offsetX, 0);
    this._offsetYFrac = normalizeOffset01(options.offsetY, 0);
    this.offsetBottom = normalizeOffsetPx(options.offsetBottom);
    this.locale = Object.assign({}, DEFAULT_LOCALE, options.locale || {});
    this.exploreFullscreen = options.exploreFullscreen !== false;
    this.openLinksInNewTab = options.openLinksInNewTab !== false;
    this.idleHighlight = options.idleHighlight !== false;
    this._highlight = normalizeHighlightOpts(options.highlight, HL_DEFAULT);

    this._fitScale = 1;
    this._stageW = 0;
    this._stageH = 0;
    this._vpW = 0;
    this._vpH = 0;
    this._imgW = 0;
    this._imgH = 0;
    this._inlinePannable = false;
    this._inlineReady = false;
    this._labelsBaseScale = 0;
    this._pointerInside = false;
    this._showcaseTimer = null;
    this._showcaseIndex = 0;
    this._showcaseIds = [];
    this._showcaseObjectId = null;
    this._onShowcaseTick = this._onShowcaseTick.bind(this);

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
    this._activeObjectId = null;
    this._hoverObjectId = null;
    this._hoverClearTimer = null;
    this._objectsById = {};
    this._scrollLockHeld = 0;

    this._onResize = this._onResize.bind(this);
    this._onKeyDown = this._onKeyDown.bind(this);
    this._ro = null;
    this._els = {};
  }

  GenplanWidgetInstance.prototype.mount = function () {
    var self = this;
    this.host.innerHTML = '';
    this.shadow = this.host.attachShadow({ mode: 'open' });

    var style = document.createElement('style');
    style.textContent = WIDGET_CSS;
    this.shadow.appendChild(style);

    var root = document.createElement('div');
    root.className = 'gw-root is-boot-loading';
    root.setAttribute('role', 'region');
    root.setAttribute('aria-label', this.locale.ariaLabel);
    this.shadow.appendChild(root);
    this._els.root = root;
    this._applyHighlightCssVars();
    this._syncCoarseClass();

    var msg = document.createElement('div');
    msg.className = 'gw-msg is-loading';
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

  GenplanWidgetInstance.prototype.destroy = function () {
    if (this.destroyed) return;
    this.destroyed = true;
    this._cancelHoverClear();
    this._stopIdleHighlight();
    if (this.exploring) {
      this._exitExplore(true);
    }
    while (this._scrollLockHeld > 0) {
      releaseScrollLock();
      this._scrollLockHeld -= 1;
    }
    if (this._ro) {
      try { this._ro.disconnect(); } catch (e) { /* ignore */ }
      this._ro = null;
    }
    window.removeEventListener('resize', this._onResize);
    document.removeEventListener('keydown', this._onKeyDown);
    if (this.shadow) {
      this.shadow.innerHTML = '';
    }
    this.host.innerHTML = '';
    this._els = {};
    this.data = null;
    this._objectsById = {};
    this._activeObjectId = null;
    this._hoverObjectId = null;
  };

  GenplanWidgetInstance.prototype.getState = function () {
    return {
      kvartalId: this.options.kvartalId,
      activeObjectId: this._activeObjectId,
      exploring: !!this.exploring
    };
  };

  GenplanWidgetInstance.prototype._showError = function (text) {
    var root = this._els.root;
    if (!root) return;
    root.innerHTML = '';
    root.classList.remove('is-boot-loading', 'is-explore');
    var msg = document.createElement('div');
    msg.className = 'gw-msg is-error';
    msg.setAttribute('role', 'alert');
    msg.textContent = text || this.locale.error;
    root.appendChild(msg);
    this._els.msg = msg;
  };

  GenplanWidgetInstance.prototype._applyHighlightCssVars = function () {
    var root = this._els.root;
    if (!root) return;
    var h = this._highlight;
    root.style.setProperty('--gw-hl-color', h.color);
    root.style.setProperty('--gw-hl-opacity', String(h.opacity));
    root.style.setProperty('--gw-idle-opacity', String(h.idleOpacity));
    root.style.setProperty('--gw-stroke-opacity', String(Math.max(0, Math.min(1, h.opacity * 0.7))));
  };

  GenplanWidgetInstance.prototype._syncCoarseClass = function () {
    if (!this._els.root) return;
    this._els.root.classList.toggle('is-coarse', isCoarsePointer());
    this._syncMobileLabelsVisibility();
  };

  GenplanWidgetInstance.prototype._syncMobileLabelsVisibility = function () {
    if (!this._els.root) return;
    // desktop и mobile — заголовки видны (на mobile compact: только ▲ если showTitleMobile=0)
    this._els.root.classList.add('is-labels-visible');
  };

  GenplanWidgetInstance.prototype._stopIdleHighlight = function () {
    if (this._showcaseTimer) {
      clearInterval(this._showcaseTimer);
      this._showcaseTimer = null;
    }
    this._clearShowcaseHighlight();
  };

  GenplanWidgetInstance.prototype._clearShowcaseHighlight = function () {
    var stage = this._els.stage;
    if (stage) {
      stage.querySelectorAll('.gw-poly.is-showcase').forEach(function (el) {
        el.classList.remove('is-showcase');
      });
    }
    this._showcaseObjectId = null;
  };

  GenplanWidgetInstance.prototype._shouldRunIdleHighlight = function () {
    if (!this.idleHighlight || this.destroyed) return false;
    if (prefersReducedMotion()) return false;
    if (!this.data || !this._els.stage) return false;
    if (this._activeObjectId != null) return false;
    if (this._hoverObjectId != null) return false;
    if (isCoarsePointer()) {
      // мобилка: только неувеличенный вид (не explore)
      return !this.exploring;
    }
    // десктоп: пока курсор вне карты
    return !this._pointerInside;
  };

  GenplanWidgetInstance.prototype._rebuildShowcaseIds = function () {
    var ids = [];
    var objects = (this.data && this.data.objects) || [];
    for (var i = 0; i < objects.length; i++) {
      var o = objects[i];
      if (o && o.id != null && o.points && o.points.length >= 3) ids.push(String(o.id));
    }
    this._showcaseIds = ids;
    if (this._showcaseIndex >= ids.length) this._showcaseIndex = 0;
  };

  GenplanWidgetInstance.prototype._onShowcaseTick = function () {
    if (!this._shouldRunIdleHighlight()) {
      this._clearShowcaseHighlight();
      return;
    }
    var stage = this._els.stage;
    if (!stage || !this._showcaseIds.length) return;
    this._clearShowcaseHighlight();
    var id = this._showcaseIds[this._showcaseIndex % this._showcaseIds.length];
    this._showcaseIndex = (this._showcaseIndex + 1) % this._showcaseIds.length;
    this._showcaseObjectId = id;
    this._polysById(stage, id).forEach(function (el) {
      if (!el.classList.contains('is-active')) el.classList.add('is-showcase');
    });
  };

  GenplanWidgetInstance.prototype._startIdleHighlight = function () {
    this._stopIdleHighlight();
    if (!this.idleHighlight || prefersReducedMotion()) return;
    this._rebuildShowcaseIds();
    if (!this._showcaseIds.length) return;
    this._onShowcaseTick();
    this._showcaseTimer = setInterval(this._onShowcaseTick, 1400);
  };

  GenplanWidgetInstance.prototype._syncIdleHighlight = function () {
    if (this._shouldRunIdleHighlight()) {
      if (!this._showcaseTimer) this._startIdleHighlight();
    } else {
      this._clearShowcaseHighlight();
    }
  };

  GenplanWidgetInstance.prototype._apiUrl = function () {
    var base = this.options.apiBase || detectApiBase();
    if (!base) {
      throw new Error('Не задан apiBase и не удалось определить URL из script.src. Передайте options.apiBase.');
    }
    // apiBase обычно уже содержит ?ctr=genplans
    var join = base.indexOf('?') >= 0 ? '&' : '?';
    return base + join + 'act=widget_data&kvartal_id=' + encodeURIComponent(this.options.kvartalId);
  };

  GenplanWidgetInstance.prototype._load = function () {
    var self = this;
    var url = this._apiUrl();
    return fetch(url, { credentials: 'omit', cache: 'no-cache' }).then(function (res) {
      if (!res.ok) throw new Error(self.locale.error + ' (HTTP ' + res.status + ')');
      return res.json();
    }).then(function (json) {
      if (!json || json.success === false) {
        throw new Error((json && json.message) || self.locale.error);
      }
      self.data = json;
      self._objectsById = {};
      (json.objects || []).forEach(function (obj) {
        if (obj && obj.id != null) self._objectsById[String(obj.id)] = obj;
      });
      return json;
    });
  };

  GenplanWidgetInstance.prototype._render = function () {
    var self = this;
    var data = this.data;
    var root = this._els.root;
    root.innerHTML = '';

    this._imgW = data.imageWidth;
    this._imgH = data.imageHeight;

    var viewport = document.createElement('div');
    viewport.className = 'gw-viewport';
    root.appendChild(viewport);
    this._els.viewport = viewport;

    var stage = this._buildMapStage(true);
    viewport.appendChild(stage);
    this._els.stage = stage;
    var labelsOverlay = this._buildLabelsOverlay(true);
    viewport.appendChild(labelsOverlay);

    var exploreBtn = document.createElement('button');
    exploreBtn.type = 'button';
    exploreBtn.className = 'gw-btn gw-btn-explore';
    exploreBtn.textContent = this.locale.explore;
    exploreBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (self.exploreFullscreen) self._enterExplore();
    });
    root.appendChild(exploreBtn);
    this._els.exploreBtn = exploreBtn;

    // Explore overlay
    var exploreLayer = document.createElement('div');
    exploreLayer.className = 'gw-explore-layer';
    exploreLayer.setAttribute('aria-hidden', 'true');
    var exploreInner = document.createElement('div');
    exploreInner.className = 'gw-explore-inner';

    var exploreViewport = document.createElement('div');
    exploreViewport.className = 'gw-viewport';
    exploreInner.appendChild(exploreViewport);

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'gw-btn gw-btn-close';
    closeBtn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6.4 6.4l11.2 11.2M17.6 6.4L6.4 17.6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
    closeBtn.setAttribute('aria-label', this.locale.close);
    closeBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      self._exitExplore();
    });
    exploreInner.appendChild(closeBtn);

    exploreLayer.appendChild(exploreInner);
    root.appendChild(exploreLayer);
    this._els.exploreLayer = exploreLayer;
    this._els.exploreInner = exploreInner;
    this._els.exploreViewport = exploreViewport;
    this._els.closeBtn = closeBtn;

    exploreViewport.addEventListener('wheel', function (ev) {
      if (!self.exploring) return;
      ev.preventDefault();
      var factor = ev.deltaY < 0 ? 1.08 : 1 / 1.08;
      self._setScaleAt(self._scale * factor, ev.clientX, ev.clientY, exploreViewport);
    }, { passive: false });

    viewport.addEventListener('wheel', function (ev) {
      if (self.destroyed || self.exploring) return;
      // мобилка: зум/скролл карты только в режиме «Увеличить»
      if (isCoarsePointer()) return;
      ev.preventDefault();
      var factor = ev.deltaY < 0 ? 1.1 : 1 / 1.1;
      self._setScaleAt(self._scale * factor, ev.clientX, ev.clientY, viewport);
      self._syncOffsetFromPan();
    }, { passive: false });

    // десктоп: курсор в карте → стоп showcase; вне → снова крутим
    viewport.addEventListener('pointerenter', function () {
      self._pointerInside = true;
      self._clearShowcaseHighlight();
    });
    viewport.addEventListener('pointerleave', function () {
      self._pointerInside = false;
      self._syncIdleHighlight();
    });

    this._bindStageInteractions(stage, viewport, false);

    if (typeof ResizeObserver !== 'undefined') {
      this._ro = new ResizeObserver(function () { self._onResize(); });
      this._ro.observe(this.host);
    }
    window.addEventListener('resize', this._onResize);

    this._syncCoarseClass();
    this._layoutFit();
    this._startIdleHighlight();
  };

  GenplanWidgetInstance.prototype._buildMapStage = function (storeEls) {
    var data = this.data;
    var stage = document.createElement('div');
    stage.className = 'gw-stage';

    var img = document.createElement('img');
    img.src = data.imageUrl;
    img.alt = data.title || 'Интерактивный план';
    img.draggable = false;
    img.setAttribute('role', 'img');
    stage.appendChild(img);

    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + data.imageWidth + ' ' + data.imageHeight);
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    svg.setAttribute('aria-hidden', 'false');
    stage.appendChild(svg);

    var objects = data.objects || [];
    for (var i = 0; i < objects.length; i++) {
      var obj = objects[i];
      if (!obj) continue;
      var isPoint = obj.kind === 'point' || !obj.points || obj.points.length < 3;
      if (!isPoint && obj.points.length >= 3) {
        var poly = document.createElementNS(svgNS, 'polygon');
        poly.setAttribute('points', pointsToSvgAttr(obj.points, data.imageHeight));
        poly.setAttribute('class', 'gw-poly');
        poly.setAttribute('tabindex', '0');
        poly.setAttribute('role', 'button');
        poly.setAttribute('aria-label', obj.titleText || ('Объект ' + obj.id));
        poly.dataset.objectId = String(obj.id);
        if (this._activeObjectId && String(this._activeObjectId) === String(obj.id)) {
          poly.classList.add('is-active');
        }
        svg.appendChild(poly);
      }
    }

    if (storeEls) {
      this._els.img = img;
      this._els.svg = svg;
    }

    return stage;
  };

  GenplanWidgetInstance.prototype._buildLabelsOverlay = function (storeEls) {
    var data = this.data;
    var labels = document.createElement('div');
    labels.className = 'gw-labels-overlay';

    var objects = data.objects || [];
    for (var j = 0; j < objects.length; j++) {
      var o = objects[j];
      if (!o || (o.labelX == null && o.labelY == null && (!o.points || !o.points.length))) continue;
      labels.appendChild(this._buildLabelElement(o, data));
    }

    if (storeEls) {
      this._els.labels = labels;
    }

    return labels;
  };

  GenplanWidgetInstance.prototype._shouldShowTitleChip = function (obj) {
    if (!obj) return true;
    if (isCoarsePointer()) return obj.showTitleMobile !== false;
    return obj.showTitleDesktop !== false;
  };

  GenplanWidgetInstance.prototype._objectHasExpandableBody = function (obj) {
    if (!obj) return false;
    if (obj.statusText) return true;
    return this._objectHasLabelBody(obj);
  };

  GenplanWidgetInstance.prototype._objectHasLabelBody = function (obj) {
    if (!obj) return false;
    if (obj.contentHtml) return true;
    if (obj.metaDelivery || obj.metaAddress) return true;
    if (obj.floorsLabel || obj.sectionsLabel) return true;
    if (obj.aptLinks && obj.aptLinks.length) return true;
    if (obj.ctaLabel && obj.ctaUrl) return true;
    return false;
  };

  GenplanWidgetInstance.prototype._makeTri = function (tone) {
    var tri = document.createElement('span');
    tri.className = 'gw-label__tri';
    tri.style.borderBottomColor = markerToneColor(tone || 'muted');
    tri.setAttribute('aria-hidden', 'true');
    return tri;
  };

  GenplanWidgetInstance.prototype._buildLabelBody = function (o, self) {
    var body = document.createElement('div');
    body.className = 'gw-label__body';

    if (o.contentHtml) {
      var content = document.createElement('div');
      content.className = 'gw-label__content';
      content.innerHTML = o.contentHtml;
      body.appendChild(content);
    }
    if (o.metaDelivery) {
      var d1 = document.createElement('span');
      d1.className = 'gw-label__meta-line';
      d1.textContent = o.metaDelivery;
      body.appendChild(d1);
    }
    if (o.metaAddress) {
      var d2 = document.createElement('span');
      d2.className = 'gw-label__meta-line';
      d2.textContent = o.metaAddress;
      body.appendChild(d2);
    }
    if (o.floorsLabel || o.sectionsLabel) {
      var badges = document.createElement('div');
      badges.className = 'gw-label__badges';
      if (o.floorsLabel) {
        var b1 = document.createElement('span');
        b1.className = 'gw-label__badge';
        b1.textContent = o.floorsLabel;
        badges.appendChild(b1);
      }
      if (o.sectionsLabel) {
        var b2 = document.createElement('span');
        b2.className = 'gw-label__badge';
        b2.textContent = o.sectionsLabel;
        badges.appendChild(b2);
      }
      body.appendChild(badges);
    }
    if (o.aptLinks && o.aptLinks.length) {
      var apts = document.createElement('div');
      apts.className = 'gw-label__apts';
      o.aptLinks.forEach(function (link) {
        var a = document.createElement('a');
        a.href = link.url;
        a.textContent = link.label;
        if (self.openLinksInNewTab) {
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
        }
        a.addEventListener('click', function (e) { e.stopPropagation(); });
        apts.appendChild(a);
      });
      body.appendChild(apts);
    }
    if (o.ctaLabel && o.ctaUrl) {
      var cta = document.createElement('a');
      cta.className = 'gw-label__cta';
      cta.href = o.ctaUrl;
      cta.textContent = o.ctaLabel;
      if (self.openLinksInNewTab) {
        cta.target = '_blank';
        cta.rel = 'noopener noreferrer';
      }
      cta.addEventListener('click', function (e) { e.stopPropagation(); });
      body.appendChild(cta);
    }

    return body;
  };

  GenplanWidgetInstance.prototype._buildLabelElement = function (o, data) {
    var self = this;
    var anchor = this._labelAnchor(o);
    var showTitle = this._shouldShowTitleChip(o);
    var tone = o.statusTone || 'muted';
    var expandable = this._objectHasExpandableBody(o);
    var hasBody = this._objectHasLabelBody(o);

    var label = document.createElement('div');
    label.className = 'gw-label' + (showTitle ? ' is-title' : ' is-compact') + (expandable ? ' gw-label--expandable' : '');
    label.dataset.objectId = String(o.id);
    label.dataset.anchorX = String(anchor.xSvg);
    label.dataset.anchorY = String(anchor.ySvg);
    label.dataset.shiftX = '0';
    label.dataset.shiftY = '0';

    var box = document.createElement('div');
    box.className = 'gw-label__box';

    var head = document.createElement('div');
    head.className = 'gw-label__head';
    head.appendChild(this._makeTri(tone));
    if (showTitle) {
      var chipText = document.createElement('span');
      chipText.className = 'gw-label__text';
      if (o.titleHtml) chipText.innerHTML = o.titleHtml;
      else chipText.textContent = o.titleText || '';
      head.appendChild(chipText);
    }
    if (o.statusText) {
      var st = document.createElement('span');
      st.className = 'gw-label__status';
      st.style.color = markerToneColor(tone);
      st.textContent = o.statusText;
      head.appendChild(st);
    }
    box.appendChild(head);

    if (hasBody) {
      var bodyWrap = document.createElement('div');
      bodyWrap.className = 'gw-label__body-wrap';
      bodyWrap.appendChild(this._buildLabelBody(o, self));
      box.appendChild(bodyWrap);
    }

    label.appendChild(box);
    return label;
  };

  GenplanWidgetInstance.prototype._viewportForStage = function (stage) {
    return stage && stage.parentElement ? stage.parentElement : null;
  };

  GenplanWidgetInstance.prototype._labelsOverlayForStage = function (stage) {
    var vp = this._viewportForStage(stage);
    return vp ? vp.querySelector('.gw-labels-overlay') : null;
  };

  GenplanWidgetInstance.prototype._syncExpandedOverlayClass = function (stage) {
    var overlay = this._labelsOverlayForStage(stage);
    if (!overlay) return;
    var has = !!overlay.querySelector('.gw-label.is-expanded');
    overlay.classList.toggle('has-expanded', has);
  };

  GenplanWidgetInstance.prototype._labelElById = function (stage, objectId) {
    var root = this._labelsOverlayForStage(stage);
    if (!root || objectId == null) return null;
    return root.querySelector('.gw-label[data-object-id="' + String(objectId) + '"]');
  };

  GenplanWidgetInstance.prototype._applyLabelPlacement = function (el, viewport) {
    if (!el || !viewport) return;
    var S = this._scale;
    var ax = parseFloat(el.dataset.anchorX || '0') || 0;
    var ay = parseFloat(el.dataset.anchorY || '0') || 0;
    var dx = parseFloat(el.dataset.shiftX || '0') || 0;
    var dy = parseFloat(el.dataset.shiftY || '0') || 0;
    var expanded = el.classList.contains('is-expanded');
    var below = el.classList.contains('is-below');
    var lift = expanded ? (below ? LABEL_LIFT_PX : -LABEL_LIFT_PX) : 0;
    el.style.left = (this._tx + ax * S) + 'px';
    el.style.top = (this._ty + ay * S) + 'px';
    if (expanded && below) {
      el.style.transform = 'translate(calc(-50% + ' + dx + 'px), ' + (lift + dy) + 'px)';
    } else if (expanded) {
      el.style.transform = 'translate(calc(-50% + ' + dx + 'px), calc(-100% + ' + lift + 'px + ' + dy + 'px))';
    } else {
      // idle chip всегда над точкой
      el.style.transform = 'translate(calc(-50% + ' + dx + 'px), calc(-100% + ' + dy + 'px))';
    }
  };

  GenplanWidgetInstance.prototype._clearMeasureStyles = function (el) {
    if (!el) return;
    el.style.visibility = '';
    var bodyWrap = el.querySelector('.gw-label__body-wrap');
    var body = el.querySelector('.gw-label__body');
    var head = el.querySelector('.gw-label__head');
    if (bodyWrap) {
      bodyWrap.style.transition = '';
      bodyWrap.style.gridTemplateRows = '';
      bodyWrap.style.maxWidth = '';
      bodyWrap.style.width = '';
      bodyWrap.style.opacity = '';
    }
    if (body) {
      body.style.transition = '';
      body.style.padding = '';
    }
    if (head) {
      head.style.transition = '';
    }
  };

  /**
   * До раскрытия: измерить полную высоту карточки и выбрать сторону (выше/ниже),
   * чтобы анимация сразу шла в корректную сторону без прыжка.
   */
  GenplanWidgetInstance.prototype._prepareExpandPlacement = function (el, viewport) {
    if (!el || !viewport) return;
    var margin = 10;
    var bodyWrap = el.querySelector('.gw-label__body-wrap');
    var body = el.querySelector('.gw-label__body');

    el.classList.add('is-expanded');
    el.style.visibility = 'hidden';
    if (bodyWrap) {
      bodyWrap.style.transition = 'none';
      bodyWrap.style.gridTemplateRows = '1fr';
      bodyWrap.style.maxWidth = 'none';
      bodyWrap.style.width = '100%';
      bodyWrap.style.opacity = '1';
    }
    if (body) {
      body.style.transition = 'none';
      body.style.padding = isCoarsePointer() ? '6px 10px 10px' : '8px 12px 12px';
    }

    el.classList.remove('is-below');
    el.dataset.shiftX = '0';
    el.dataset.shiftY = '0';
    this._applyLabelPlacement(el, viewport);

    var box = el.getBoundingClientRect();
    var h = box.height || 0;
    var w = box.width || 0;

    var S = this._scale;
    var ax = parseFloat(el.dataset.anchorX || '0') || 0;
    var ay = parseFloat(el.dataset.anchorY || '0') || 0;
    var anchorX = this._tx + ax * S;
    var anchorY = this._ty + ay * S;
    var vw = viewport.clientWidth;
    var vh = viewport.clientHeight;

    var spaceAbove = Math.max(0, anchorY - LABEL_LIFT_PX - margin);
    var spaceBelow = Math.max(0, vh - (anchorY + LABEL_LIFT_PX) - margin);
    var below = spaceAbove < h;
    if (spaceAbove < h && spaceBelow < h) {
      below = spaceBelow >= spaceAbove;
    }

    // горизонтальный сдвиг от якоря (карточка центрируется по X)
    var left = anchorX - w / 2;
    var right = anchorX + w / 2;
    var dx = 0;
    if (right > vw - margin) dx = (vw - margin) - right;
    if (left + dx < margin) dx = margin - left;

    var dy = 0;
    if (below) {
      var bottom = anchorY + LABEL_LIFT_PX + h;
      if (bottom > vh - margin) dy = (vh - margin) - bottom;
      var top = anchorY + LABEL_LIFT_PX + dy;
      if (top < margin) dy += margin - top;
    } else {
      var top2 = anchorY - LABEL_LIFT_PX - h;
      if (top2 < margin) dy = margin - top2;
      var bottom2 = anchorY - LABEL_LIFT_PX + dy;
      if (bottom2 > vh - margin) dy += (vh - margin) - bottom2;
    }

    this._clearMeasureStyles(el);
    el.classList.remove('is-expanded');
    // явно вернуть collapsed без transition — иначе следующий expand не анимируется
    if (bodyWrap) {
      bodyWrap.style.transition = 'none';
      bodyWrap.style.gridTemplateRows = '0fr';
      bodyWrap.style.maxWidth = '0';
      bodyWrap.style.width = '';
      bodyWrap.style.opacity = '0';
    }
    if (body) {
      body.style.transition = 'none';
      body.style.padding = '0 12px';
    }
    void el.offsetWidth;
    if (bodyWrap) {
      bodyWrap.style.transition = '';
      bodyWrap.style.gridTemplateRows = '';
      bodyWrap.style.maxWidth = '';
      bodyWrap.style.opacity = '';
    }
    if (body) {
      body.style.transition = '';
      body.style.padding = '';
    }

    if (below) el.classList.add('is-below');
    else el.classList.remove('is-below');
    el.dataset.shiftX = String(dx);
    el.dataset.shiftY = String(dy);
    el.dataset.placementReady = '1';
    this._applyLabelPlacement(el, viewport);
  };

  GenplanWidgetInstance.prototype._syncLabelPositionsForViewport = function (viewport) {
    if (!viewport) return;
    var overlay = viewport.querySelector('.gw-labels-overlay');
    if (!overlay) return;
    var nodes = overlay.querySelectorAll('.gw-label');
    for (var i = 0; i < nodes.length; i++) {
      this._applyLabelPlacement(nodes[i], viewport);
    }
  };

  GenplanWidgetInstance.prototype._syncLabelsForStage = function (stage) {
    var vp = this._viewportForStage(stage);
    if (vp) this._syncLabelPositionsForViewport(vp);
  };

  GenplanWidgetInstance.prototype._viewportEl = function () {
    if (this.exploring && this._els.exploreViewport) return this._els.exploreViewport;
    return this._els.viewport;
  };

  GenplanWidgetInstance.prototype._nudgeExpandedIntoViewport = function (el, viewport) {
    if (!el || !viewport) return;
    var margin = 10;
    var vr = viewport.getBoundingClientRect();
    this._applyLabelPlacement(el, viewport);
    var r = el.getBoundingClientRect();
    var dx = parseFloat(el.dataset.shiftX || '0') || 0;
    var dy = parseFloat(el.dataset.shiftY || '0') || 0;
    var changed = false;
    if (r.right > vr.right - margin) { dx += (vr.right - margin) - r.right; changed = true; }
    if (r.left < vr.left + margin) { dx += (vr.left + margin) - r.left; changed = true; }
    if (r.bottom > vr.bottom - margin) { dy += (vr.bottom - margin) - r.bottom; changed = true; }
    if (r.top < vr.top + margin) { dy += (vr.top + margin) - r.top; changed = true; }
    if (!changed) return;
    el.dataset.shiftX = String(dx);
    el.dataset.shiftY = String(dy);
    this._applyLabelPlacement(el, viewport);
  };

  GenplanWidgetInstance.prototype._reclampExpandedLabels = function (stage) {
    if (!stage) return;
    var self = this;
    var root = this._labelsOverlayForStage(stage);
    if (!root) return;
    var viewport = this._viewportForStage(stage);
    root.querySelectorAll('.gw-label.is-expanded').forEach(function (el) {
      self._nudgeExpandedIntoViewport(el, viewport);
    });
  };

  GenplanWidgetInstance.prototype._setLabelExpanded = function (stage, objectId, expanded) {
    if (!stage) return;
    var self = this;
    var root = this._labelsOverlayForStage(stage);
    if (!root) return;
    var viewport = this._viewportForStage(stage);
    var nodes = root.querySelectorAll('.gw-label');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (String(el.dataset.objectId) === String(objectId)) {
        if (expanded) {
          if (el.classList.contains('is-expanded') || el.dataset.expandPending === '1') continue;
          // важно: захватить el в локальную переменную — иначе rAF видит последний узел цикла
          var targetEl = el;
          var targetId = String(objectId);
          self._prepareExpandPlacement(targetEl, viewport);
          void targetEl.offsetWidth;
          targetEl.dataset.expandPending = '1';
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              targetEl.dataset.expandPending = '0';
              if (self.destroyed) return;
              var stillHover = self._hoverObjectId != null
                && String(self._hoverObjectId) === targetId;
              var stillActive = self._activeObjectId != null
                && String(self._activeObjectId) === targetId;
              if (!stillHover && !stillActive) {
                targetEl.classList.remove('is-below');
                targetEl.dataset.shiftX = '0';
                targetEl.dataset.shiftY = '0';
                self._applyLabelPlacement(targetEl, viewport);
                self._syncExpandedOverlayClass(stage);
                return;
              }
              targetEl.classList.add('is-expanded');
              self._applyLabelPlacement(targetEl, viewport);
              self._syncExpandedOverlayClass(stage);
              if (targetEl._gwClampTimer) clearTimeout(targetEl._gwClampTimer);
              targetEl._gwClampTimer = setTimeout(function () {
                targetEl._gwClampTimer = null;
                if (!targetEl.classList.contains('is-expanded')) return;
                self._nudgeExpandedIntoViewport(targetEl, viewport);
              }, 320);
            });
          });
        } else {
          el.dataset.expandPending = '0';
          el.classList.remove('is-expanded');
          el.classList.remove('is-below');
          el.dataset.shiftX = '0';
          el.dataset.shiftY = '0';
          el.dataset.placementReady = '0';
          self._clearMeasureStyles(el);
          if (el._gwClampTimer) {
            clearTimeout(el._gwClampTimer);
            el._gwClampTimer = null;
          }
          self._applyLabelPlacement(el, viewport);
        }
      } else if (expanded) {
        el.dataset.expandPending = '0';
        el.classList.remove('is-expanded');
        el.classList.remove('is-below');
        el.dataset.shiftX = '0';
        el.dataset.shiftY = '0';
        el.dataset.placementReady = '0';
        self._clearMeasureStyles(el);
        if (el._gwClampTimer) {
          clearTimeout(el._gwClampTimer);
          el._gwClampTimer = null;
        }
        self._applyLabelPlacement(el, viewport);
      }
    }
    self._syncExpandedOverlayClass(stage);
  };

  GenplanWidgetInstance.prototype._collapseAllLabels = function (stage) {
    if (!stage) return;
    var self = this;
    var root = this._labelsOverlayForStage(stage);
    if (!root) return;
    var viewport = this._viewportForStage(stage);
    root.querySelectorAll('.gw-label.is-expanded, .gw-label[data-expand-pending="1"]').forEach(function (el) {
      el.dataset.expandPending = '0';
      el.classList.remove('is-expanded');
      el.classList.remove('is-below');
      el.dataset.shiftX = '0';
      el.dataset.shiftY = '0';
      el.dataset.placementReady = '0';
      self._clearMeasureStyles(el);
      if (el._gwClampTimer) {
        clearTimeout(el._gwClampTimer);
        el._gwClampTimer = null;
      }
      self._applyLabelPlacement(el, viewport);
    });
    self._syncExpandedOverlayClass(stage);
  };

  GenplanWidgetInstance.prototype._labelAnchor = function (obj) {
    var h = this._imgH || (this.data && this.data.imageHeight) || 0;
    var x;
    var y;
    if (obj.labelX != null && obj.labelY != null) {
      x = Number(obj.labelX);
      y = Number(obj.labelY);
    } else {
      var c = polygonCentroid(obj.points || []);
      x = c.x;
      y = c.y;
    }
    return { x: x, y: y, xSvg: x, ySvg: flipY(y, h) };
  };

  GenplanWidgetInstance.prototype._objectByPoly = function (poly) {
    if (!poly || !poly.dataset) return null;
    return this._objectsById[String(poly.dataset.objectId)] || null;
  };

  GenplanWidgetInstance.prototype._objectById = function (id) {
    if (id == null) return null;
    return this._objectsById[String(id)] || null;
  };

  GenplanWidgetInstance.prototype._polysById = function (stage, id) {
    var list = [];
    if (!stage || id == null) return list;
    var key = String(id);
    var nodes = stage.querySelectorAll('.gw-poly');
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i].dataset.objectId === key) list.push(nodes[i]);
    }
    return list;
  };

  GenplanWidgetInstance.prototype._cancelHoverClear = function () {
    if (this._hoverClearTimer) {
      clearTimeout(this._hoverClearTimer);
      this._hoverClearTimer = null;
    }
  };

  GenplanWidgetInstance.prototype._scheduleHoverClear = function (stage) {
    var self = this;
    this._cancelHoverClear();
    this._hoverClearTimer = setTimeout(function () {
      self._hoverClearTimer = null;
      if (self._activeObjectId != null) return;
      self._setHover(stage, null, true);
    }, 220);
  };

  GenplanWidgetInstance.prototype._hitTestObjectId = function (clientX, clientY) {
    var root = this.shadow;
    if (!root || clientX == null || clientY == null) return null;

    var stack = null;
    if (typeof root.elementsFromPoint === 'function') {
      try { stack = root.elementsFromPoint(clientX, clientY); } catch (e) { stack = null; }
    }
    if (!stack || !stack.length) {
      var one = typeof root.elementFromPoint === 'function' ? root.elementFromPoint(clientX, clientY) : null;
      stack = one ? [one] : [];
    }

    var expandedLabelId = null;
    var polyId = null;
    var chipId = null;

    for (var i = 0; i < stack.length; i++) {
      var n = stack[i];
      if (!n || !n.classList) continue;

      var label = (n.classList.contains('gw-label') ? n : (n.closest && n.closest('.gw-label')));
      if (label && label.dataset && label.dataset.objectId) {
        if (label.classList.contains('is-expanded')) {
          if (!expandedLabelId) expandedLabelId = String(label.dataset.objectId);
        } else if (!chipId) {
          chipId = String(label.dataset.objectId);
        }
        continue;
      }

      if (n.classList.contains('gw-poly') && n.dataset && n.dataset.objectId) {
        if (!polyId) polyId = String(n.dataset.objectId);
      }
    }

    // приоритет: открытый tooltip → полигон дома → chip
    // (chip не перекрывает соседний poly, если tooltip другого дома открыт)
    if (expandedLabelId) return expandedLabelId;
    if (polyId) return polyId;
    if (chipId) return chipId;
    return null;
  };

  GenplanWidgetInstance.prototype._setHover = function (stage, objectId, fromTimer) {
    if (objectId != null) this._cancelHoverClear();
    else if (!fromTimer) this._cancelHoverClear();

    // тот же дом — не пересоздаём expand/clamp (иначе дергается)
    if (objectId != null && this._hoverObjectId != null
      && String(this._hoverObjectId) === String(objectId)) {
      return;
    }

    this._clearShowcaseHighlight();
    var prev = this._hoverObjectId;
    if (prev && String(prev) !== String(objectId)) {
      this._polysById(stage, prev).forEach(function (el) {
        if (!el.classList.contains('is-active')) el.classList.remove('is-hover');
      });
      if (this._activeObjectId == null) {
        this._setLabelExpanded(stage, prev, false);
      }
    }
    this._hoverObjectId = objectId || null;
    if (objectId != null) {
      this._polysById(stage, objectId).forEach(function (el) { el.classList.add('is-hover'); });
      if (this._activeObjectId != null && String(this._activeObjectId) !== String(objectId)) {
        return;
      }
      var obj = this._objectsById[String(objectId)];
      if (obj && this._objectHasExpandableBody(obj)) {
        this._setLabelExpanded(stage, objectId, true);
      }
    } else if (this._activeObjectId == null) {
      this._collapseAllLabels(stage);
    }
    if (objectId == null) this._syncIdleHighlight();
  };

  GenplanWidgetInstance.prototype._setActive = function (stage, objectId) {
    this._clearShowcaseHighlight();
    var prev = this._activeObjectId;
    if (prev != null) {
      this._polysById(stage, prev).forEach(function (el) {
        el.classList.remove('is-active');
        el.classList.remove('is-hover');
      });
    }
    this._activeObjectId = objectId != null ? objectId : null;
    if (objectId != null) {
      this._polysById(stage, objectId).forEach(function (el) {
        el.classList.add('is-active');
        el.classList.remove('is-hover');
        el.classList.remove('is-showcase');
      });
      var obj = this._objectsById[String(objectId)];
      if (obj && this._objectHasExpandableBody(obj)) {
        this._setLabelExpanded(stage, objectId, true);
      } else {
        this._setLabelExpanded(stage, objectId, false);
      }
    } else {
      this._collapseAllLabels(stage);
    }
    if (objectId == null) this._syncIdleHighlight();
  };

  GenplanWidgetInstance.prototype._clearSelection = function (stage) {
    this._setHover(stage, null);
    this._setActive(stage, null);
  };

  GenplanWidgetInstance.prototype._shouldUseInlineExploreTap = function (isExplore) {
    return !isExplore && isCoarsePointer() && this.exploreFullscreen && allowsExploreMode();
  };

  GenplanWidgetInstance.prototype._notifyObjectSelect = function (obj) {
    if (!obj || typeof this.options.onObjectClick !== 'function') return;
    try {
      this.options.onObjectClick({
        id: obj.id,
        homeId: obj.homeId,
        titleText: obj.titleText,
        titleHtml: obj.titleHtml,
        linkUrl: obj.linkUrl || '',
        statusText: obj.statusText,
        statusTone: obj.statusTone,
        metaDelivery: obj.metaDelivery,
        metaAddress: obj.metaAddress
      });
    } catch (e) {
      if (typeof console !== 'undefined' && console.error) console.error(e);
    }
  };

  GenplanWidgetInstance.prototype._handleObjectActivate = function (obj, stage, isExplore) {
    if (!obj) return;
    if (this._shouldUseInlineExploreTap(isExplore)) {
      this._enterExplore();
      return;
    }
    if (this._activeObjectId != null && String(this._activeObjectId) === String(obj.id)) {
      this._clearSelection(stage);
      return;
    }
    this._setActive(stage, obj.id);
    this._notifyObjectSelect(obj);
  };

  GenplanWidgetInstance.prototype._bindStageInteractions = function (stage, viewport, isExplore) {
    var self = this;
    var surface = viewport;

    function targetPoly(ev) {
      var t = ev.target;
      if (t && t.classList && t.classList.contains('gw-poly')) return t;
      // fallback: pointercapture / labels могут сдвинуть target
      if (typeof ev.composedPath === 'function') {
        var path = ev.composedPath();
        for (var i = 0; i < path.length; i++) {
          var n = path[i];
          if (n && n.classList && n.classList.contains('gw-poly')) return n;
        }
      }
      return null;
    }

    function targetLabel(ev) {
      var t = ev.target;
      if (!t) return null;
      if (t.closest) {
        var fromClosest = t.closest('.gw-label');
        if (fromClosest) return fromClosest;
      }
      if (t.classList && t.classList.contains('gw-label')) return t;
      if (typeof ev.composedPath === 'function') {
        var path = ev.composedPath();
        for (var i = 0; i < path.length; i++) {
          var n = path[i];
          if (n && n.classList && n.classList.contains('gw-label')) return n;
        }
      }
      return null;
    }

    function resolveHoverId(ev) {
      return self._hitTestObjectId(ev.clientX, ev.clientY);
    }

    surface.addEventListener('pointerdown', function (ev) {
      if (self.destroyed) return;
      // на мобилке в компактном виде не captur'им — иначе ломается скролл страницы
      if (!(isCoarsePointer() && !isExplore)) {
        surface.setPointerCapture && surface.setPointerCapture(ev.pointerId);
      }
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

      var label = targetLabel(ev);
      var labelAnchor = null;
      if (label && ev.target && ev.target.closest) {
        var hitA = ev.target.closest('a');
        if (hitA && label.contains(hitA)) labelAnchor = hitA;
      }
      var poly = label ? null : targetPoly(ev);
      self._panStart = {
        x: ev.clientX,
        y: ev.clientY,
        tx: self._tx,
        ty: self._ty,
        poly: poly,
        label: label,
        labelAnchor: labelAnchor,
        time: Date.now()
      };
    });

    surface.addEventListener('pointermove', function (ev) {
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
        if (!isCoarsePointer() || isExplore) {
          var hoverId = resolveHoverId(ev);
          if (hoverId != null) {
            self._cancelHoverClear();
            self._setHover(stage, hoverId);
          } else if (self._activeObjectId == null) {
            self._scheduleHoverClear(stage);
          } else {
            self._hoverObjectId = null;
            stage.querySelectorAll('.gw-poly.is-hover').forEach(function (el) {
              if (!el.classList.contains('is-active')) el.classList.remove('is-hover');
            });
          }
        }
        return;
      }

      var dx2 = ev.clientX - self._panStart.x;
      var dy2 = ev.clientY - self._panStart.y;
      if (Math.hypot(dx2, dy2) > 8) {
        if (!self._moved) {
          self._clearSelection(stage);
        }
        self._moved = true;
      }

      if (isExplore && self.exploring) {
        // увеличенный режим: pan/zoom карты
        self._tx = self._panStart.tx + dx2;
        self._ty = self._panStart.ty + dy2;
        self._clampTransform(viewport);
        self._applyExploreTransform();
        stage.classList.add('is-dragging');
      } else if (!isExplore && self._moved && isCoarsePointer() && self.exploreFullscreen && allowsExploreMode()) {
        // мобилка: жест pan → вход в explore (как Sigma), без inline-зума
        try { surface.releasePointerCapture && surface.releasePointerCapture(ev.pointerId); } catch (err) { /* ignore */ }
        self._pointers.clear();
        self._panStart = null;
        self._enterExplore();
      } else if (!isExplore && self._moved && !isCoarsePointer()) {
        // десктоп: pan в обычном виджете
        self._tx = self._panStart.tx + dx2;
        self._ty = self._panStart.ty + dy2;
        self._clampTransform(viewport);
        self._applyInlineTransform();
        self._syncOffsetFromPan();
        stage.classList.add('is-dragging');
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

      // мобилка, компактный вид: любой тап по карте → explore (без ссылок/тултипов)
      if (self._shouldUseInlineExploreTap(isExplore)) {
        self._enterExplore();
        return;
      }

      // тап по подписи → выбор объекта (переход только по <a> внутри tooltip)
      if (start.labelAnchor) return;

      if (start.label) {
        var labelObj = self._objectById(start.label.dataset && start.label.dataset.objectId);
        if (labelObj) {
          self._handleObjectActivate(labelObj, stage, isExplore);
        }
        return;
      }

      if (start.poly) {
        var obj = self._objectByPoly(start.poly);
        self._handleObjectActivate(obj, stage, isExplore);
        return;
      }

      if (isCoarsePointer() || self._activeObjectId != null) {
        self._clearSelection(stage);
      }
    }

    surface.addEventListener('pointerup', endPointer);
    surface.addEventListener('pointercancel', endPointer);
    surface.addEventListener('pointerleave', function () {
      if (!self._panStart && !isCoarsePointer() && self._activeObjectId == null) {
        self._scheduleHoverClear(stage);
      }
    });

    var labelsOverlay = viewport.querySelector('.gw-labels-overlay');
    if (labelsOverlay) {
      labelsOverlay.addEventListener('pointerover', function (ev) {
        if (isCoarsePointer() && !isExplore) return;
        var label = ev.target.closest && ev.target.closest('.gw-label');
        if (!label || !label.dataset.objectId) return;
        self._cancelHoverClear();
        self._setHover(stage, label.dataset.objectId);
      });
      labelsOverlay.addEventListener('pointerout', function (ev) {
        if (isCoarsePointer() && !isExplore) return;
        var label = ev.target.closest && ev.target.closest('.gw-label');
        if (!label) return;
        var related = ev.relatedTarget;
        if (related && label.contains(related)) return;
        if (self._activeObjectId != null) return;
        // не сбрасываем сразу: pointermove на surface решит через hit-test / delay
        if (self._hoverObjectId != null
          && String(self._hoverObjectId) === String(label.dataset.objectId)) {
          self._scheduleHoverClear(stage);
        }
      });
    }

    stage.addEventListener('keydown', function (ev) {
      var poly = targetPoly(ev);
      if (!poly) return;
      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        var obj = self._objectByPoly(poly);
        self._handleObjectActivate(obj, stage, isExplore);
      }
    });
  };

  GenplanWidgetInstance.prototype._cloneStageIntoExplore = function () {
    var exploreViewport = this._els.exploreViewport;
    exploreViewport.innerHTML = '';
    var stage = this._buildMapStage(false);
    exploreViewport.appendChild(stage);
    exploreViewport.appendChild(this._buildLabelsOverlay(false));
    this._els.exploreStage = stage;

    this._bindStageInteractions(stage, exploreViewport, true);
    if (this._activeObjectId != null) {
      this._setActive(stage, this._activeObjectId);
    }
    this._applyExploreTransform();
  };

  GenplanWidgetInstance.prototype._enterExplore = function () {
    if (this.destroyed || this.exploring) return;
    if (!this.exploreFullscreen) return;
    this._syncOffsetFromPan();
    this.exploring = true;
    this._els.root.classList.add('is-explore');
    this._els.exploreLayer.setAttribute('aria-hidden', 'false');
    this._cloneStageIntoExplore();
    this._syncMobileLabelsVisibility();
    this._clearShowcaseHighlight();

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
    this._scrollLockHeld += 1;
    document.addEventListener('keydown', this._onKeyDown);
    if (this._els.closeBtn) this._els.closeBtn.focus();
  };

  GenplanWidgetInstance.prototype._exitExplore = function (fromDestroy) {
    if (!this.exploring) return;
    this.exploring = false;
    if (this._els.root) this._els.root.classList.remove('is-explore');
    if (this._els.exploreLayer) this._els.exploreLayer.setAttribute('aria-hidden', 'true');
    if (this._els.exploreViewport) this._els.exploreViewport.innerHTML = '';
    this._els.exploreStage = null;
    if (this._scrollLockHeld > 0) {
      releaseScrollLock();
      this._scrollLockHeld -= 1;
    }
    document.removeEventListener('keydown', this._onKeyDown);
    if (!fromDestroy) {
      if (this._els.stage) {
        if (this._activeObjectId != null) {
          this._setActive(this._els.stage, this._activeObjectId);
        } else {
          this._clearSelection(this._els.stage);
        }
      }
      this._layoutFit();
      this._syncIdleHighlight();
    }
  };

  GenplanWidgetInstance.prototype._onKeyDown = function (ev) {
    if (ev.key === 'Escape' && this.exploring) {
      ev.preventDefault();
      this._exitExplore();
    }
  };

  GenplanWidgetInstance.prototype._applyExploreTransform = function () {
    var stage = this._els.exploreStage;
    if (!stage) return;
    stage.style.width = this._imgW + 'px';
    stage.style.height = this._imgH + 'px';
    stage.style.transform = 'translate(' + this._tx + 'px,' + this._ty + 'px) scale(' + this._scale + ')';
    this._syncLabelPositionsForViewport(this._els.exploreViewport);
    this._reclampExpandedLabels(stage);
  };

  GenplanWidgetInstance.prototype._clampTransform = function (viewport) {
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

  GenplanWidgetInstance.prototype._setScaleAt = function (nextScale, clientX, clientY, viewport) {
    nextScale = Math.max(this._minScale, Math.min(this._maxScale, nextScale));
    var rect = viewport.getBoundingClientRect();
    var cx = clientX - rect.left;
    var cy = clientY - rect.top;
    var sx = (cx - this._tx) / this._scale;
    var sy = (cy - this._ty) / this._scale;
    this._scale = nextScale;
    this._tx = cx - sx * this._scale;
    this._ty = cy - sy * this._scale;
    this._clampTransform(viewport);
    if (this.exploring) this._applyExploreTransform();
    else this._applyInlineTransform();
    this._updatePannableClass();
    this._syncMobileLabelsVisibility();
    // при зуме скрываем активный/hover тултип
    if (this.exploring) {
      this._clearSelection(this._els.exploreStage);
    } else {
      this._clearSelection(this._els.stage);
    }
  };

  GenplanWidgetInstance.prototype._applyInlineTransform = function () {
    var stage = this._els.stage;
    if (!stage || this.exploring) return;
    stage.style.width = this._imgW + 'px';
    stage.style.height = this._imgH + 'px';
    stage.style.transformOrigin = '0 0';
    stage.style.transform = 'translate(' + this._tx + 'px,' + this._ty + 'px) scale(' + this._scale + ')';
    this._syncLabelPositionsForViewport(this._els.viewport);
    this._reclampExpandedLabels(stage);
  };

  GenplanWidgetInstance.prototype._updatePannableClass = function () {
    if (!this._els.root) return;
    var vw = this._vpW;
    var vh = this._vpH;
    var sw = this._imgW * this._scale;
    var sh = this._imgH * this._scale;
    this._inlinePannable = (sw > vw + 0.5) || (sh > vh + 0.5);
    this._els.root.classList.add('is-pannable');
  };

  GenplanWidgetInstance.prototype._syncOffsetFromPan = function () {
    var vw = this._vpW;
    var vh = this._vpH;
    var sw = this._imgW * this._scale;
    var sh = this._imgH * this._scale;
    var ox = Math.max(0, sw - vw);
    var oy = Math.max(0, sh - vh);
    this._offsetXFrac = ox > 0 ? Math.max(0, Math.min(1, -this._tx / ox)) : 0;
    this._offsetYFrac = oy > 0 ? Math.max(0, Math.min(1, -this._ty / oy)) : 0;
  };

  GenplanWidgetInstance.prototype._layoutFit = function () {
    if (this.destroyed || !this.data || !this._els.viewport) return;

    var hostW = this.host.clientWidth || this.host.offsetWidth || 300;
    var vw;
    if (this.widthSpec.type === 'px') {
      vw = Math.min(this.widthSpec.value, hostW || this.widthSpec.value);
      this._els.root.style.width = this.widthSpec.value + 'px';
      this._els.root.style.maxWidth = '100%';
    } else {
      vw = Math.max(1, hostW);
      this._els.root.style.width = '100%';
    }
    var vh = Math.max(1, this.maxHeight);

    var prevZoom = this._inlineReady && this._minScale > 0
      ? (this._scale / this._minScale)
      : null;

    this._minScale = Math.min(vw / this._imgW, vh / this._imgH) * this.minZoom;
    this._maxScale = Math.min(vw / this._imgW, vh / this._imgH) * this.maxZoom;
    if (this._maxScale < this._minScale) this._maxScale = this._minScale;

    this._vpW = vw;
    this._vpH = vh;
    this._fitScale = this._minScale;

    var viewport = this._els.viewport;
    viewport.style.width = '100%';
    viewport.style.maxWidth = '100%';
    viewport.style.height = vh + 'px';

    var stage = this._els.stage;
    if (stage) {
      stage.style.width = this._imgW + 'px';
      stage.style.height = this._imgH + 'px';
      stage.style.transformOrigin = '0 0';
    }

    if (!this._inlineReady) {
      // стартовый масштаб — cover, чтобы был pan по обеим осям (если аспект не совпал)
      var cover = Math.max(vw / this._imgW, vh / this._imgH);
      this._scale = Math.max(this._minScale, Math.min(this._maxScale, cover));
      this._labelsBaseScale = this._scale;
      var sw = this._imgW * this._scale;
      var sh = this._imgH * this._scale;
      var ox = Math.max(0, sw - vw);
      var oy = Math.max(0, sh - vh);
      this._tx = ox > 0 ? -ox * this._offsetXFrac : (vw - sw) / 2;
      if (this.offsetBottom != null && oy > 0) {
        // 100px от низа = снизу вверх на offsetBottom
        this._ty = vh - sh + this.offsetBottom;
      } else if (oy > 0) {
        this._ty = -oy * this._offsetYFrac;
      } else {
        this._ty = (vh - sh) / 2;
      }
      this._inlineReady = true;
    } else if (prevZoom != null) {
      this._scale = Math.max(this._minScale, Math.min(this._maxScale, this._minScale * prevZoom));
      // база для скрытия подписей — cover текущего viewport
      this._labelsBaseScale = Math.max(this._minScale, Math.min(this._maxScale, Math.max(vw / this._imgW, vh / this._imgH)));
      var sw2 = this._imgW * this._scale;
      var sh2 = this._imgH * this._scale;
      var ox2 = Math.max(0, sw2 - vw);
      var oy2 = Math.max(0, sh2 - vh);
      this._tx = ox2 > 0 ? -ox2 * this._offsetXFrac : (vw - sw2) / 2;
      this._ty = oy2 > 0 ? -oy2 * this._offsetYFrac : (vh - sh2) / 2;
    }

    this._clampTransform(viewport);
    this._applyInlineTransform();
    this._updatePannableClass();
    this._syncMobileLabelsVisibility();
    if (this._els.stage) this._reclampExpandedLabels(this._els.stage);
  };

  GenplanWidgetInstance.prototype._onResize = function () {
    if (this.destroyed) return;
    this._syncCoarseClass();
    if (!this.exploring) this._layoutFit();
    else if (this._els.exploreStage) {
      this._syncLabelPositionsForViewport(this._els.exploreViewport);
      this._reclampExpandedLabels(this._els.exploreStage);
    }
  };

  function mount(options) {
    options = options || {};
    var host = resolveEl(options.el);
    if (!host) {
      throw new Error('[GenplanWidget] el not found');
    }
    if (!options.kvartalId) {
      throw new Error('[GenplanWidget] kvartalId is required');
    }
    if (options.width == null) options.width = '100%';
    if (options.maxHeight == null) {
      options.maxHeight = normalizeMaxHeight(null);
    }
    if (options.minZoom == null) options.minZoom = 1;
    if (options.maxZoom == null) options.maxZoom = 4;
    if (options.offsetX == null) options.offsetX = 0;
    if (options.offsetY == null) options.offsetY = 0;
    if (options.exploreFullscreen == null) options.exploreFullscreen = true;
    if (options.openLinksInNewTab == null) options.openLinksInNewTab = true;
    if (options.idleHighlight == null) options.idleHighlight = true;
    var inst = new GenplanWidgetInstance(host, options);
    inst.mount();
    return inst;
  }

  global.GenplanWidget = {
    mount: mount,
    version: '2.4.0'
  };
})(typeof window !== 'undefined' ? window : this);
