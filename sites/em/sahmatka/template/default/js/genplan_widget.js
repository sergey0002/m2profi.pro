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
    return Math.round((typeof window !== 'undefined' ? window.innerWidth : 1200) * 2);
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

  function statusToneColor(tone) {
    if (tone === 'ok') return '#28a745';
    if (tone === 'warn') return '#e6a23c';
    if (tone === 'danger') return '#dc3545';
    return '#999';
  }

  function flipY(y, imageHeight) {
    var h = Number(imageHeight) || 0;
    return h > 0 ? (h - Number(y)) : Number(y);
  }

  var UI_STACK = 'Arial, Helvetica, sans-serif';
  var HL = HL_DEFAULT.color;

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
    '.gw-labels { position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none; z-index: 3; overflow: visible; }',
    '.gw-label { position: absolute; transform: translate(-50%, calc(-100% - 10px)); pointer-events: none; z-index: 3; }',
    '.gw-label__bubble { position: relative; display: inline-flex; align-items: center; gap: 7px; max-width: 220px; padding: 6px 11px; background: #fff; color: #1a1a1a; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.18); font-size: 13px; font-weight: 600; line-height: 1.25; white-space: nowrap; }',
    '.gw-label__text { overflow: hidden; text-overflow: ellipsis; }',
    '.gw-label__dot { width: 8px; height: 8px; border-radius: 50%; flex: 0 0 auto; box-shadow: 0 0 0 1px rgba(255,255,255,0.8); }',
    '.gw-label__arrow { position: absolute; left: 50%; bottom: -6px; width: 0; height: 0; margin-left: -7px; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 7px solid #fff; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.08)); }',
    /* при hover/active с карточкой — прячем подпись, чтобы не дублировать заголовок */
    '.gw-label.is-suppressed { visibility: hidden; }',
    /* mobile: компактные «тултипные» подписи + тап по ним = переход */
    /* mobile: подписи только после зума / в explore — иначе наползают */
    '.gw-root.is-coarse:not(.is-labels-visible) .gw-labels { opacity: 0; visibility: hidden; }',
    '.gw-root.is-coarse:not(.is-labels-visible) .gw-label { pointer-events: none !important; }',
    '.gw-root.is-coarse .gw-label { transform: translate(-50%, calc(-100% - 5px)); pointer-events: auto; cursor: pointer; -webkit-tap-highlight-color: transparent; }',
    '.gw-root.is-coarse .gw-label__bubble { gap: 4px; max-width: 120px; padding: 2px 6px; border-radius: 5px; font-size: 10px; font-weight: 600; line-height: 1.2; box-shadow: 0 1px 5px rgba(0,0,0,0.16); }',
    '.gw-root.is-coarse .gw-label__dot { width: 5px; height: 5px; }',
    '.gw-root.is-coarse .gw-label__arrow { bottom: -4px; margin-left: -4px; border-left-width: 4px; border-right-width: 4px; border-top-width: 4px; }',
    /* белая карточка + стрелка вниз как у постоянных подписей */
    '.gw-tooltip { position: absolute; z-index: 8; pointer-events: none; min-width: 160px; max-width: min(260px, 92%); padding: 10px 12px; border-radius: 8px; background: #fff; color: #1a1a1a; text-align: left; line-height: 1.3; opacity: 0; transform: translate(-50%, calc(-100% - 10px)); transition: none; box-shadow: 0 2px 10px rgba(0,0,0,0.18); }',
    '.gw-tooltip.is-visible { opacity: 1; transition: opacity 220ms ease; }',
    '@media (prefers-reduced-motion: reduce) { .gw-tooltip.is-visible { transition: none; } }',
    '.gw-tooltip__head { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; margin: 0 0 2px; }',
    '.gw-tooltip__title { font-weight: 700; font-size: 13px; color: #1a1a1a; line-height: 1.25; }',
    '.gw-tooltip__status { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; line-height: 1.2; }',
    '.gw-tooltip__status-dot { width: 6px; height: 6px; border-radius: 50%; flex: 0 0 auto; }',
    '.gw-tooltip__meta { margin-top: 6px; font-size: 11px; color: #777; line-height: 1.4; }',
    '.gw-tooltip__meta-line { display: block; }',
    '.gw-tooltip__arrow { position: absolute; left: 50%; bottom: -6px; width: 0; height: 0; margin-left: -7px; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 7px solid #fff; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.08)); }',
    '.gw-root.is-coarse .gw-tooltip { min-width: 140px; max-width: min(220px, 90%); padding: 8px 10px; border-radius: 7px; transform: translate(-50%, calc(-100% - 8px)); }',
    '.gw-root.is-coarse .gw-tooltip__title { font-size: 12px; }',
    '.gw-root.is-coarse .gw-tooltip__status { font-size: 10px; gap: 4px; }',
    '.gw-root.is-coarse .gw-tooltip__status-dot { width: 5px; height: 5px; }',
    '.gw-root.is-coarse .gw-tooltip__meta { margin-top: 5px; font-size: 10px; }',
    '.gw-root.is-coarse .gw-tooltip__arrow { bottom: -5px; margin-left: -5px; border-left-width: 5px; border-right-width: 5px; border-top-width: 5px; }',
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
    // desktop — как есть
    if (!isCoarsePointer()) {
      this._els.root.classList.add('is-labels-visible');
      return;
    }
    // explore («Увеличить») — показываем подписи
    if (this.exploring) {
      this._els.root.classList.add('is-labels-visible');
      return;
    }
    var base = this._labelsBaseScale || this._minScale || 0;
    var show = base > 0 && this._scale >= base * 1.18;
    this._els.root.classList.toggle('is-labels-visible', show);
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

    var stage = this._buildStage(true);
    viewport.appendChild(stage);
    this._els.stage = stage;

    var tooltip = document.createElement('div');
    tooltip.className = 'gw-tooltip';
    tooltip.setAttribute('aria-hidden', 'true');
    viewport.appendChild(tooltip);
    this._els.tooltip = tooltip;

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

  GenplanWidgetInstance.prototype._buildStage = function (storeEls) {
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
      if (!obj || !obj.points || obj.points.length < 3) continue;
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

    var labels = document.createElement('div');
    labels.className = 'gw-labels';
    stage.appendChild(labels);

    for (var j = 0; j < objects.length; j++) {
      var o = objects[j];
      if (!o || !o.titleText) continue;
      var anchor = this._labelAnchor(o);
      var label = document.createElement('div');
      label.className = 'gw-label';
      label.style.left = ((anchor.xSvg / data.imageWidth) * 100) + '%';
      label.style.top = ((anchor.ySvg / data.imageHeight) * 100) + '%';
      label.dataset.objectId = String(o.id);

      var bubble = document.createElement('div');
      bubble.className = 'gw-label__bubble';
      var text = document.createElement('span');
      text.className = 'gw-label__text';
      text.textContent = o.titleText;
      bubble.appendChild(text);
      if (o.statusTone) {
        var dot = document.createElement('span');
        dot.className = 'gw-label__dot';
        dot.style.background = statusToneColor(o.statusTone);
        bubble.appendChild(dot);
      }
      label.appendChild(bubble);
      var arrow = document.createElement('div');
      arrow.className = 'gw-label__arrow';
      label.appendChild(arrow);
      labels.appendChild(label);
    }

    if (storeEls) {
      this._els.img = img;
      this._els.svg = svg;
      this._els.labels = labels;
    }

    return stage;
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

  GenplanWidgetInstance.prototype._fillTooltip = function (obj, tooltipEl) {
    if (!obj || !tooltipEl) return;
    tooltipEl.innerHTML = '';

    var head = document.createElement('div');
    head.className = 'gw-tooltip__head';

    var title = document.createElement('div');
    title.className = 'gw-tooltip__title';
    // titleHtml уже санитизирован на сервере
    title.innerHTML = obj.titleHtml || '';
    if (!title.textContent && obj.titleText) {
      title.textContent = obj.titleText;
    }
    head.appendChild(title);

    if (obj.statusText) {
      var status = document.createElement('div');
      status.className = 'gw-tooltip__status';
      var tone = statusToneColor(obj.statusTone);
      status.style.color = tone;
      var sdot = document.createElement('span');
      sdot.className = 'gw-tooltip__status-dot';
      sdot.style.background = tone;
      status.appendChild(sdot);
      var slabel = document.createElement('span');
      slabel.textContent = obj.statusText;
      status.appendChild(slabel);
      head.appendChild(status);
    }

    tooltipEl.appendChild(head);

    var metaLines = [];
    if (obj.metaDelivery) metaLines.push(obj.metaDelivery);
    if (obj.metaAddress) metaLines.push(obj.metaAddress);
    if (metaLines.length) {
      var meta = document.createElement('div');
      meta.className = 'gw-tooltip__meta';
      metaLines.forEach(function (line) {
        var row = document.createElement('span');
        row.className = 'gw-tooltip__meta-line';
        row.textContent = line;
        meta.appendChild(row);
      });
      tooltipEl.appendChild(meta);
    }

    var arrow = document.createElement('div');
    arrow.className = 'gw-tooltip__arrow';
    tooltipEl.appendChild(arrow);
  };

  GenplanWidgetInstance.prototype._placeTooltip = function (tooltipEl, viewport, clientX, clientY) {
    if (!tooltipEl || !viewport) return;
    var rect = viewport.getBoundingClientRect();
    var x = clientX - rect.left;
    var y = clientY - rect.top;
    var tw = tooltipEl.offsetWidth || 200;
    var th = tooltipEl.offsetHeight || 60;
    x = Math.max(tw / 2 + 8, Math.min(rect.width - tw / 2 - 8, x));
    y = Math.max(th + 18, Math.min(rect.height - 8, y));
    tooltipEl.style.left = x + 'px';
    tooltipEl.style.top = y + 'px';
  };

  GenplanWidgetInstance.prototype._objectHasExtraInfo = function (obj) {
    return !!(obj && (obj.statusText || obj.metaDelivery || obj.metaAddress));
  };

  GenplanWidgetInstance.prototype._syncLabelSuppression = function (stage) {
    if (!stage) return;
    var hide = {};
    var hoverId = this._hoverObjectId;
    var activeId = this._activeObjectId;
    if (hoverId != null) {
      var hobj = this._objectsById[String(hoverId)];
      if (this._objectHasExtraInfo(hobj)) hide[String(hoverId)] = true;
    }
    if (activeId != null) {
      var aobj = this._objectsById[String(activeId)];
      if (this._objectHasExtraInfo(aobj)) hide[String(activeId)] = true;
    }
    var nodes = stage.querySelectorAll('.gw-label');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (hide[String(el.dataset.objectId)]) el.classList.add('is-suppressed');
      else el.classList.remove('is-suppressed');
    }
  };

  GenplanWidgetInstance.prototype._showObjectCard = function (obj, stage, tooltipEl, viewport, clientX, clientY) {
    if (!obj || !tooltipEl || !viewport) return false;
    // нет статуса/меты — карточка дублировала бы только заголовок подписи
    if (!this._objectHasExtraInfo(obj)) return false;
    this._fillTooltip(obj, tooltipEl);
    tooltipEl.classList.add('is-visible');
    tooltipEl.setAttribute('aria-hidden', 'false');
    // у якоря подписи — визуально заменяет title-bubble
    if (stage) {
      this._placeTooltipNearObject(obj, tooltipEl, viewport, stage);
    } else if (clientX != null && clientY != null) {
      this._placeTooltip(tooltipEl, viewport, clientX, clientY);
    }
    return true;
  };

  GenplanWidgetInstance.prototype._hideObjectCard = function (tooltipEl) {
    if (!tooltipEl) return;
    tooltipEl.classList.remove('is-visible');
    tooltipEl.setAttribute('aria-hidden', 'true');
  };

  GenplanWidgetInstance.prototype._setHover = function (stage, objectId, tooltipEl, clientX, clientY, viewport) {
    this._clearShowcaseHighlight();
    var prev = this._hoverObjectId;
    if (prev && String(prev) !== String(objectId)) {
      this._polysById(stage, prev).forEach(function (el) {
        if (!el.classList.contains('is-active')) el.classList.remove('is-hover');
      });
    }
    this._hoverObjectId = objectId || null;
    if (objectId != null) {
      this._polysById(stage, objectId).forEach(function (el) { el.classList.add('is-hover'); });
      var obj = this._objectsById[String(objectId)];
      // если уже есть active-карточка другого объекта — не перетираем её hover'ом
      if (this._activeObjectId != null && String(this._activeObjectId) !== String(objectId)) {
        this._syncLabelSuppression(stage);
        return;
      }
      if (!this._showObjectCard(obj, stage, tooltipEl, viewport, clientX, clientY)) {
        if (tooltipEl && this._activeObjectId == null) this._hideObjectCard(tooltipEl);
      }
    } else if (tooltipEl && this._activeObjectId == null) {
      this._hideObjectCard(tooltipEl);
    } else if (tooltipEl && this._activeObjectId != null) {
      // вернуть карточку active, если hover ушёл
      var aobj = this._objectsById[String(this._activeObjectId)];
      if (!this._showObjectCard(aobj, stage, tooltipEl, viewport, null, null)) {
        this._hideObjectCard(tooltipEl);
      }
    }
    this._syncLabelSuppression(stage);
    if (objectId == null) this._syncIdleHighlight();
  };

  GenplanWidgetInstance.prototype._setActive = function (stage, objectId, tooltipEl, clientX, clientY, viewport) {
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
      if (!this._showObjectCard(obj, stage, tooltipEl, viewport, clientX, clientY)) {
        this._hideObjectCard(tooltipEl);
      }
    } else if (tooltipEl) {
      this._hideObjectCard(tooltipEl);
    }
    this._syncLabelSuppression(stage);
    if (objectId == null) this._syncIdleHighlight();
  };

  GenplanWidgetInstance.prototype._placeTooltipNearObject = function (obj, tooltipEl, viewport, stage) {
    if (!obj || !tooltipEl || !viewport || !stage) return;
    var anchor = this._labelAnchor(obj);
    var rect = viewport.getBoundingClientRect();
    var stageRect = stage.getBoundingClientRect();
    var x = stageRect.left - rect.left + (anchor.xSvg / this._imgW) * stageRect.width;
    var y = stageRect.top - rect.top + (anchor.ySvg / this._imgH) * stageRect.height;
    this._placeTooltip(tooltipEl, viewport, rect.left + x, rect.top + y);
  };

  GenplanWidgetInstance.prototype._clearSelection = function (stage, tooltipEl) {
    this._setHover(stage, null, tooltipEl);
    this._setActive(stage, null, tooltipEl);
  };

  GenplanWidgetInstance.prototype._fireObjectClick = function (obj) {
    if (!obj) return;
    var payload = {
      id: obj.id,
      homeId: obj.homeId,
      titleText: obj.titleText,
      titleHtml: obj.titleHtml,
      linkUrl: obj.linkUrl || '',
      statusText: obj.statusText,
      statusTone: obj.statusTone,
      metaDelivery: obj.metaDelivery,
      metaAddress: obj.metaAddress
    };
    var result;
    if (typeof this.options.onObjectClick === 'function') {
      try {
        result = this.options.onObjectClick(payload);
      } catch (e) {
        if (typeof console !== 'undefined' && console.error) console.error(e);
      }
    }
    if (result && result.preventDefault) return;
    var url = (payload.linkUrl || '').trim();
    if (!url) return;
    // гарантируем абсолютный/валидный переход; внутри iframe window.open может глотаться
    try {
      if (this.openLinksInNewTab) {
        var w = window.open(url, '_blank', 'noopener,noreferrer');
        if (!w) {
          var a = document.createElement('a');
          a.href = url;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          a.style.display = 'none';
          (this.shadow || document.body).appendChild(a);
          a.click();
          a.remove();
        }
      } else {
        window.top.location.href = url;
      }
    } catch (err) {
      try { window.location.href = url; } catch (e2) { /* ignore */ }
    }
  };

  GenplanWidgetInstance.prototype._handleObjectActivate = function (obj, stage, tooltipEl, clientX, clientY, viewport) {
    if (!obj) return;
    if (isCoarsePointer()) {
      // Mobile: 1-й тап — highlight+card; 2-й по тому же — navigate
      if (this._activeObjectId != null && String(this._activeObjectId) === String(obj.id)) {
        this._fireObjectClick(obj);
        return;
      }
      this._setActive(stage, obj.id, tooltipEl, clientX, clientY, viewport);
      return;
    }
    this._setActive(stage, obj.id, tooltipEl, clientX, clientY, viewport);
    this._fireObjectClick(obj);
  };

  GenplanWidgetInstance.prototype._bindStageInteractions = function (stage, viewport, isExplore) {
    var self = this;
    var tooltipEl = isExplore ? this._els.exploreTooltip : this._els.tooltip;

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

    stage.addEventListener('pointerdown', function (ev) {
      if (self.destroyed) return;
      // на мобилке в компактном виде не captur'им — иначе ломается скролл страницы
      if (!(isCoarsePointer() && !isExplore)) {
        stage.setPointerCapture && stage.setPointerCapture(ev.pointerId);
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
      var poly = label ? null : targetPoly(ev);
      self._panStart = {
        x: ev.clientX,
        y: ev.clientY,
        tx: self._tx,
        ty: self._ty,
        poly: poly,
        label: label,
        time: Date.now()
      };
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
        if (!isCoarsePointer() || isExplore) {
          var hpoly = targetPoly(ev);
          if (hpoly) {
            var hobj = self._objectByPoly(hpoly);
            self._setHover(stage, hobj && hobj.id, tooltipEl, ev.clientX, ev.clientY, viewport);
          } else if (self._activeObjectId == null) {
            self._setHover(stage, null, tooltipEl);
          } else {
            // keep active card; clear transient hover only
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
          self._clearSelection(stage, tooltipEl);
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
        try { stage.releasePointerCapture && stage.releasePointerCapture(ev.pointerId); } catch (err) { /* ignore */ }
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

      // тап по подписи → сразу переход (mobile/desktop)
      if (start.label) {
        var labelObj = self._objectById(start.label.dataset && start.label.dataset.objectId);
        if (labelObj) {
          self._setActive(stage, labelObj.id, tooltipEl, ev.clientX, ev.clientY, viewport);
          self._fireObjectClick(labelObj);
        }
        return;
      }

      if (start.poly) {
        var obj = self._objectByPoly(start.poly);
        self._handleObjectActivate(obj, stage, tooltipEl, ev.clientX, ev.clientY, viewport);
        return;
      }

      // тап вне полигона — сброс выделения (mobile) / игнор desktop
      if (isCoarsePointer() || self._activeObjectId != null) {
        self._clearSelection(stage, tooltipEl);
      }
    }

    stage.addEventListener('pointerup', endPointer);
    stage.addEventListener('pointercancel', endPointer);
    stage.addEventListener('pointerleave', function () {
      if (!self._panStart && !isCoarsePointer() && self._activeObjectId == null) {
        self._setHover(stage, null, tooltipEl);
      }
    });

    stage.addEventListener('keydown', function (ev) {
      var poly = targetPoly(ev);
      if (!poly) return;
      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        var obj = self._objectByPoly(poly);
        self._handleObjectActivate(obj, stage, tooltipEl, null, null, viewport);
      }
    });
  };

  GenplanWidgetInstance.prototype._cloneStageIntoExplore = function () {
    var exploreViewport = this._els.exploreViewport;
    exploreViewport.innerHTML = '';
    var clone = this._buildStage(false);
    exploreViewport.appendChild(clone);
    this._els.exploreStage = clone;

    var tooltip = document.createElement('div');
    tooltip.className = 'gw-tooltip';
    tooltip.setAttribute('aria-hidden', 'true');
    exploreViewport.appendChild(tooltip);
    this._els.exploreTooltip = tooltip;

    this._bindStageInteractions(clone, exploreViewport, true);
    if (this._activeObjectId != null) {
      this._setActive(clone, this._activeObjectId, tooltip, null, null, exploreViewport);
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
    this._els.exploreTooltip = null;
    if (this._scrollLockHeld > 0) {
      releaseScrollLock();
      this._scrollLockHeld -= 1;
    }
    document.removeEventListener('keydown', this._onKeyDown);
    if (!fromDestroy) {
      // синхронизируем active на основной stage
      if (this._els.stage && this._els.tooltip && this._els.viewport) {
        if (this._activeObjectId != null) {
          this._setActive(this._els.stage, this._activeObjectId, this._els.tooltip, null, null, this._els.viewport);
        } else {
          this._clearSelection(this._els.stage, this._els.tooltip);
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
      this._clearSelection(this._els.exploreStage, this._els.exploreTooltip);
    } else {
      this._clearSelection(this._els.stage, this._els.tooltip);
    }
  };

  GenplanWidgetInstance.prototype._applyInlineTransform = function () {
    var stage = this._els.stage;
    if (!stage || this.exploring) return;
    stage.style.width = this._imgW + 'px';
    stage.style.height = this._imgH + 'px';
    stage.style.transformOrigin = '0 0';
    stage.style.transform = 'translate(' + this._tx + 'px,' + this._ty + 'px) scale(' + this._scale + ')';
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
  };

  GenplanWidgetInstance.prototype._onResize = function () {
    if (this.destroyed) return;
    this._syncCoarseClass();
    if (!this.exploring) this._layoutFit();
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
    version: '1.0.9'
  };
})(typeof window !== 'undefined' ? window : this);
