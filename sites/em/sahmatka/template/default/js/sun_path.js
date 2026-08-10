/**
 * Sun path overlay on apartment floor plan (EM task 11).
 * θ: 0=N top, 90=E right, 180=S bottom, 270=W left (географически).
 * Круг/лейблы/солнце смещаются на window_orient_deg — как роза компаса.
 * Картинку планировки НЕ крутим.
 */
(function (window, document) {
  'use strict';

  /** Орбита поверх планировки (чуть внутри бокса картинки) */
  var ELLIPSE_SCALE = 0.96;
  /** Запас под подписи/солнце */
  var EDGE_PAD = 22;
  /** Вынос подписи от пунктира — вне маркера на конце дуги */
  var LABEL_OUTSET = 38;
  var ANIM_MS = 400;
  var FADE_MS = 400;
  /** Дуга дня: восход → день → закат (180°) */
  var DAY_SWEEP = 180;
  /** Продление дуги за восход/закат (одинаково с обеих сторон), ° */
  var ARC_OVERSHOOT = 22;
  /** Локальные углы суток относительно севера (как у компаса) */
  var TIME_THETA = {
    morning: 90,  // Восток — Восход
    day: 180,     // Юг
    evening: 270  // Запад — Закат
  };
  /** Код ориентации окон → угол розы компаса (как SVG rotate) */
  var ORIENT_DEG = {
    1: 0,
    2: 45,
    3: 90,
    4: 135,
    5: 180,
    6: 225,
    7: 270,
    8: 315
  };

  function isMobileLayout() {
    return window.matchMedia && window.matchMedia('(max-width: 767.98px)').matches;
  }

  function layoutParams() {
    if (isMobileLayout()) {
      return {
        ellipseScale: 0.94,
        edgePad: 24,
        labelOutset: 32
      };
    }
    return {
      ellipseScale: ELLIPSE_SCALE,
      edgePad: EDGE_PAD,
      labelOutset: LABEL_OUTSET
    };
  }

  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function easeInOut(t) {
    return t * t * (3 - 2 * t);
  }

  /** Точка на эллипсе: θ по часовой от севера (верх экрана до поворота) */
  function pointOnEllipse(cx, cy, rx, ry, thetaDeg) {
    var rad = (thetaDeg * Math.PI) / 180;
    return {
      x: cx + rx * Math.sin(rad),
      y: cy - ry * Math.cos(rad)
    };
  }

  /** SVG-путь дуги восход→закат по тем же точкам, что маркеры/солнце */
  function buildDayArcPath(cx, cy, rx, ry, startDeg, sweepDeg, steps) {
    steps = steps || 64;
    var d = '';
    var i;
    for (i = 0; i <= steps; i++) {
      var th = startDeg + (sweepDeg * i) / steps;
      var p = pointOnEllipse(cx, cy, rx, ry, th);
      d += (i === 0 ? 'M ' : ' L ') + p.x.toFixed(2) + ' ' + p.y.toFixed(2);
    }
    return d;
  }

  function orientDegFromRoot(root) {
    var o1 = parseInt(root.getAttribute('data-orient-1'), 10) || 0;
    var o2 = parseInt(root.getAttribute('data-orient-2'), 10) || 0;
    var code = o1 || o2;
    if (ORIENT_DEG[code] != null) {
      return ORIENT_DEG[code];
    }
    var attr = parseInt(root.getAttribute('data-orient-deg'), 10);
    return isNaN(attr) ? 0 : attr;
  }

  function mount(root) {
    if (!root || root.getAttribute('data-sun-mounted') === '1') {
      return;
    }

    var frame = root.querySelector('.plan-with-sun__frame');
    var img = root.querySelector('.plan-with-sun__img');
    var stage = root.querySelector('.sun-path__stage');
    var orbit = root.querySelector('.sun-path__orbit');
    var arc = root.querySelector('.sun-path__arc');
    var sun = root.querySelector('.sun-path__sun');
    var markerRise = root.querySelector('.sun-path__marker--rise');
    var markerSet = root.querySelector('.sun-path__marker--set');
    var labelRise = root.querySelector('.sun-path__label--rise');
    var labelSet = root.querySelector('.sun-path__label--set');
    var toggle = root.querySelector('.sun-path__toggle');
    var controls = root.querySelector('.sun-path__controls');
    var timeButtons = root.querySelectorAll('.sun-path__controls [data-sun-time]');

    if (!frame || !img || !stage || !orbit || !arc || !sun) {
      return;
    }

    root.setAttribute('data-sun-mounted', '1');

    var state = {
      cx: 0,
      cy: 0,
      rx: 0,
      ry: 0,
      /** поворот СК по компасу (градусы); планировка не крутится */
      orientDeg: orientDegFromRoot(root),
      theta: TIME_THETA.day,
      time: 'day',
      animId: 0,
      fadeTimer: 0,
      on: !stage.classList.contains('is-off'),
      ready: false
    };

    root.setAttribute('data-orient-deg', String(state.orientDeg));

    var defaultTime = root.getAttribute('data-sun-default') || 'day';
    var activeBtn = root.querySelector('.sun-path__controls .is-active[data-sun-time]');
    if (activeBtn && TIME_THETA[activeBtn.getAttribute('data-sun-time')] != null) {
      defaultTime = activeBtn.getAttribute('data-sun-time');
    }
    if (TIME_THETA[defaultTime] != null) {
      state.theta = TIME_THETA[defaultTime];
      state.time = defaultTime;
    }

    /** Географический угол → экранный с учётом компаса */
    function screenTheta(geoTheta) {
      return geoTheta + state.orientDeg;
    }

    function placeAt(el, x, y) {
      el.style.left = x + 'px';
      el.style.top = y + 'px';
    }

    function applySun(geoTheta) {
      if (state.rx < 1 || state.ry < 1) {
        return;
      }
      var p = pointOnEllipse(state.cx, state.cy, state.rx, state.ry, screenTheta(geoTheta));
      placeAt(sun, p.x, p.y);
    }

    function layout() {
      var w = img.clientWidth || img.offsetWidth;
      var h = img.clientHeight || img.offsetHeight;
      if (w < 8 || h < 8) {
        return false;
      }

      var params = layoutParams();
      var ellipseScale = params.ellipseScale;
      var edgePad = params.edgePad;
      var labelOutset = params.labelOutset;

      // Полуоси по реальному размеру картинки (квадрат / вытянутая / широкая)
      var rx = (w * 0.5) * ellipseScale;
      var ry = (h * 0.5) * ellipseScale;

      // Запас под подписи; при scale≤1 орбита на плане — пад минимальный
      var overhangX = Math.max(0, rx - w * 0.5) + labelOutset + (isMobileLayout() ? 8 : 12);
      var overhangY = Math.max(0, ry - h * 0.5) + labelOutset + (isMobileLayout() ? 10 : 14);
      var padX = Math.max(edgePad, overhangX);
      var padY = Math.max(edgePad, overhangY);

      root.style.paddingLeft = padX + 'px';
      root.style.paddingRight = padX + 'px';
      frame.style.marginTop = padY + 'px';
      frame.style.marginBottom = padY + 'px';
      frame.style.paddingLeft = '';
      frame.style.paddingRight = '';
      frame.style.paddingTop = '';
      frame.style.paddingBottom = '';

      var frameRect = frame.getBoundingClientRect();
      var imgRect = img.getBoundingClientRect();
      var cx = imgRect.left - frameRect.left + imgRect.width / 2;
      var cy = imgRect.top - frameRect.top + imgRect.height / 2;

      w = imgRect.width;
      h = imgRect.height;
      rx = (w * 0.5) * ellipseScale;
      ry = (h * 0.5) * ellipseScale;

      state.cx = cx;
      state.cy = cy;
      state.rx = rx;
      state.ry = ry;

      // Дуга дня: чуть дальше восхода и заката (солнышки на линии, не на концах)
      var riseAng = screenTheta(TIME_THETA.morning);
      var setAng = riseAng + DAY_SWEEP;
      var arcStart = riseAng - ARC_OVERSHOOT;
      var arcSweep = DAY_SWEEP + ARC_OVERSHOOT * 2;
      var fw = Math.max(frame.clientWidth || frameRect.width, 1);
      var fh = Math.max(frame.clientHeight || frameRect.height, 1);
      orbit.setAttribute('viewBox', '0 0 ' + fw + ' ' + fh);
      orbit.setAttribute('width', String(fw));
      orbit.setAttribute('height', String(fh));
      orbit.style.left = '0';
      orbit.style.top = '0';
      arc.setAttribute('d', buildDayArcPath(cx, cy, rx, ry, arcStart, arcSweep, 72));

      // Маркеры — на точках восход/закат (на линии, не на концах дуги)
      var pRise = pointOnEllipse(cx, cy, rx, ry, riseAng);
      var pSet = pointOnEllipse(cx, cy, rx, ry, setAng);

      if (markerRise) {
        placeAt(markerRise, pRise.x, pRise.y);
        markerRise.style.transform = 'translate(-50%, -50%)';
      }
      if (markerSet) {
        placeAt(markerSet, pSet.x, pSet.y);
        markerSet.style.transform = 'translate(-50%, -50%)';
      }

      // Подписи снаружи концов дуги (не на линии)
      if (labelRise) {
        var pr = pointOnEllipse(cx, cy, rx + labelOutset, ry + labelOutset, riseAng);
        placeAt(labelRise, pr.x, pr.y);
        labelRise.style.transform = 'translate(-50%, -50%)';
      }
      if (labelSet) {
        var ps = pointOnEllipse(cx, cy, rx + labelOutset, ry + labelOutset, setAng);
        placeAt(labelSet, ps.x, ps.y);
        labelSet.style.transform = 'translate(-50%, -50%)';
      }

      applySun(state.theta);
      if (!state.ready) {
        state.ready = true;
        stage.classList.add('is-ready');
      }
      return true;
    }

    function layoutRetry(attempt) {
      if (layout()) {
        return;
      }
      if (attempt > 60) {
        return;
      }
      window.setTimeout(function () {
        layoutRetry(attempt + 1);
      }, 50);
    }

    function cancelAnim() {
      if (state.animId) {
        window.cancelAnimationFrame(state.animId);
        state.animId = 0;
      }
    }

    function animateTo(targetTheta) {
      layout();
      cancelAnim();
      var from = state.theta;
      var to = targetTheta;
      if (from === to || state.rx < 1) {
        state.theta = to;
        applySun(to);
        return;
      }
      if (prefersReducedMotion()) {
        state.theta = to;
        applySun(to);
        return;
      }
      var start = null;
      function frameAnim(ts) {
        if (start == null) {
          start = ts;
        }
        var t = Math.min(1, (ts - start) / ANIM_MS);
        state.theta = from + (to - from) * easeInOut(t);
        applySun(state.theta);
        if (t < 1) {
          state.animId = window.requestAnimationFrame(frameAnim);
        } else {
          state.theta = to;
          state.animId = 0;
          applySun(to);
        }
      }
      state.animId = window.requestAnimationFrame(frameAnim);
    }

    function selectTime(value) {
      var th = TIME_THETA[value];
      if (th == null) {
        return;
      }
      state.time = value;
      Array.prototype.forEach.call(timeButtons, function (btn) {
        var on = btn.getAttribute('data-sun-time') === value;
        if (on) {
          btn.classList.add('is-active');
        } else {
          btn.classList.remove('is-active');
        }
        btn.setAttribute('aria-checked', on ? 'true' : 'false');
      });
      animateTo(th);
    }

    function clearFadeTimer() {
      if (state.fadeTimer) {
        window.clearTimeout(state.fadeTimer);
        state.fadeTimer = 0;
      }
    }

    function setOn(on) {
      state.on = !!on;
      clearFadeTimer();

      if (toggle) {
        toggle.classList.toggle('is-on', state.on);
        toggle.setAttribute('aria-pressed', state.on ? 'true' : 'false');
      }
      stage.setAttribute('aria-hidden', state.on ? 'false' : 'true');

      if (state.on) {
        stage.classList.remove('is-off');
        if (controls) {
          controls.classList.remove('is-off');
        }
        layoutRetry(0);
        // следующий кадр — fade-in (иначе transition не сработает)
        window.requestAnimationFrame(function () {
          window.requestAnimationFrame(function () {
            if (!state.on) {
              return;
            }
            stage.classList.add('is-visible');
            if (controls) {
              controls.classList.add('is-visible');
            }
          });
        });
      } else {
        stage.classList.remove('is-visible');
        if (controls) {
          controls.classList.remove('is-visible');
        }
        var delay = prefersReducedMotion() ? 0 : FADE_MS;
        state.fadeTimer = window.setTimeout(function () {
          state.fadeTimer = 0;
          if (state.on) {
            return;
          }
          stage.classList.add('is-off');
          if (controls) {
            controls.classList.add('is-off');
          }
          root.style.paddingLeft = '';
          root.style.paddingRight = '';
          frame.style.marginTop = '';
          frame.style.marginBottom = '';
        }, delay);
      }
    }

    if (toggle) {
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        setOn(!state.on);
      });
    }

    if (controls) {
      controls.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('[data-sun-time]') : null;
        if (!btn || !controls.contains(btn)) {
          return;
        }
        e.preventDefault();
        e.stopPropagation();
        var value = btn.getAttribute('data-sun-time');
        if (value) {
          selectTime(value);
        }
      });
    }

    selectTime(state.time);

    function onImgReady() {
      layoutRetry(0);
    }

    img.addEventListener('load', onImgReady);
    if (img.complete) {
      onImgReady();
    }
    window.setTimeout(function () {
      layoutRetry(0);
    }, 0);
    window.setTimeout(function () {
      layoutRetry(0);
    }, 300);

    if (typeof ResizeObserver !== 'undefined') {
      var ro = new ResizeObserver(function () {
        layout();
      });
      ro.observe(img);
    } else {
      window.addEventListener('resize', layout);
    }
  }

  function mountAll(scope) {
    var root = scope && scope.querySelectorAll ? scope : document;
    Array.prototype.forEach.call(root.querySelectorAll('[data-sun-path]'), mount);
  }

  window.SunPath = { mount: mount, mountAll: mountAll };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      mountAll();
    });
  } else {
    mountAll();
  }
})(window, document);
