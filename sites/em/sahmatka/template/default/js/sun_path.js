/**
 * Sun path overlay on apartment floor plan (EM task 11).
 * θ: 0=N top, 90=E right, 180=S bottom, 270=W left. rotate overlay = 0.
 */
(function (window, document) {
  'use strict';

  var ELLIPSE_SCALE = 1.15;
  var LABEL_OUTSET = 14;
  var ANIM_MS = 400;
  var TIME_THETA = {
    morning: 90,
    day: 180,
    evening: 270
  };

  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function easeInOut(t) {
    return t * t * (3 - 2 * t);
  }

  function pointOnCircle(cx, cy, r, thetaDeg) {
    var rad = (thetaDeg * Math.PI) / 180;
    return {
      x: cx + r * Math.sin(rad),
      y: cy - r * Math.cos(rad)
    };
  }

  function mount(root) {
    if (!root || root.getAttribute('data-sun-mounted') === '1') {
      return;
    }

    var img = root.querySelector('.plan-with-sun__img');
    var stage = root.querySelector('.sun-path__stage');
    var ellipse = root.querySelector('.sun-path__ellipse');
    var sun = root.querySelector('.sun-path__sun');
    var labelRise = root.querySelector('.sun-path__label--rise');
    var labelSet = root.querySelector('.sun-path__label--set');
    var toggle = root.querySelector('.sun-path__toggle');
    var controls = root.querySelector('.sun-path__controls');
    var radios = root.querySelectorAll('.sun-path__controls input[type="radio"]');

    if (!img || !stage || !ellipse || !sun) {
      return;
    }

    root.setAttribute('data-sun-mounted', '1');

    var state = {
      cx: 0,
      cy: 0,
      r: 0,
      theta: TIME_THETA.day,
      animId: 0,
      on: !stage.classList.contains('is-off')
    };

    var checked = root.querySelector('.sun-path__controls input[type="radio"]:checked');
    if (checked && TIME_THETA[checked.value] != null) {
      state.theta = TIME_THETA[checked.value];
    } else if (root.getAttribute('data-sun-default') && TIME_THETA[root.getAttribute('data-sun-default')] != null) {
      state.theta = TIME_THETA[root.getAttribute('data-sun-default')];
    }

    function placeAt(el, x, y) {
      el.style.left = x + 'px';
      el.style.top = y + 'px';
    }

    function applySun(theta) {
      var p = pointOnCircle(state.cx, state.cy, state.r, theta);
      placeAt(sun, p.x, p.y);
    }

    function layout() {
      var rootRect = root.getBoundingClientRect();
      var imgRect = img.getBoundingClientRect();
      if (imgRect.width < 2 || imgRect.height < 2) {
        return;
      }

      var cx = imgRect.left - rootRect.left + imgRect.width / 2;
      var cy = imgRect.top - rootRect.top + imgRect.height / 2;
      var r = 0.5 * Math.min(imgRect.width, imgRect.height) * ELLIPSE_SCALE;

      state.cx = cx;
      state.cy = cy;
      state.r = r;

      ellipse.style.width = r * 2 + 'px';
      ellipse.style.height = r * 2 + 'px';
      ellipse.style.left = cx - r + 'px';
      ellipse.style.top = cy - r + 'px';

      if (labelRise) {
        var pr = pointOnCircle(cx, cy, r + LABEL_OUTSET, 90);
        placeAt(labelRise, pr.x, pr.y);
      }
      if (labelSet) {
        var ps = pointOnCircle(cx, cy, r + LABEL_OUTSET, 270);
        placeAt(labelSet, ps.x, ps.y);
      }

      applySun(state.theta);
    }

    function cancelAnim() {
      if (state.animId) {
        window.cancelAnimationFrame(state.animId);
        state.animId = 0;
      }
    }

    function animateTo(targetTheta) {
      cancelAnim();
      var from = state.theta;
      var to = targetTheta;
      if (from === to) {
        applySun(to);
        return;
      }

      if (prefersReducedMotion()) {
        state.theta = to;
        applySun(to);
        return;
      }

      var start = null;
      function frame(ts) {
        if (start == null) {
          start = ts;
        }
        var t = Math.min(1, (ts - start) / ANIM_MS);
        var e = easeInOut(t);
        state.theta = from + (to - from) * e;
        applySun(state.theta);
        if (t < 1) {
          state.animId = window.requestAnimationFrame(frame);
        } else {
          state.theta = to;
          state.animId = 0;
          applySun(to);
        }
      }
      state.animId = window.requestAnimationFrame(frame);
    }

    function setOn(on) {
      state.on = !!on;
      if (toggle) {
        toggle.classList.toggle('is-on', state.on);
        toggle.setAttribute('aria-pressed', state.on ? 'true' : 'false');
      }
      stage.classList.toggle('is-off', !state.on);
      if (controls) {
        controls.classList.toggle('is-off', !state.on);
      }
      if (state.on) {
        layout();
      }
    }

    if (toggle) {
      toggle.addEventListener('click', function () {
        setOn(!state.on);
      });
    }

    Array.prototype.forEach.call(radios, function (radio) {
      radio.addEventListener('change', function () {
        if (!radio.checked) {
          return;
        }
        var th = TIME_THETA[radio.value];
        if (th == null) {
          return;
        }
        animateTo(th);
      });
    });

    if (img.complete) {
      layout();
    } else {
      img.addEventListener('load', layout);
    }

    if (typeof ResizeObserver !== 'undefined') {
      var ro = new ResizeObserver(function () {
        layout();
      });
      ro.observe(root);
      ro.observe(img);
    } else {
      window.addEventListener('resize', layout);
    }
  }

  function mountAll(scope) {
    var root = scope && scope.querySelectorAll ? scope : document;
    var nodes = root.querySelectorAll('[data-sun-path]');
    Array.prototype.forEach.call(nodes, mount);
  }

  window.SunPath = {
    mount: mount,
    mountAll: mountAll
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      mountAll();
    });
  } else {
    mountAll();
  }
})(window, document);
