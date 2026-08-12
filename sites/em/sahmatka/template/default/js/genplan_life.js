/**
 * GenplanLife — life animation runtime for EM GenplanWidget (task 12).
 * API: GenplanLife.create({ layerEl, lightEl, imageWidth, imageHeight, lifeData, flags })
 *   → { destroy(), setFlags(flags), pause(), resume() }
 *
 * Coordinates: CRS.Simple points with y=0 at bottom → CSS y = imageHeight - y.
 * Motion model: agents are simulated (accel / corner braking / stops), not lerped along a clock,
 * so cars slow into turns and people keep a walking cadence.
 * Depends on GenplanLifeSprites (graceful fallback if missing).
 */
(function (global) {
  'use strict';

  var MAX_CARS = 8;
  var MAX_PEOPLE = 10;
  var MAX_DOGS = 4;
  var MAX_BIRDS = 16;
  var MAX_CLOUDS = 2;

  var DIAGONALS = [
    { name: 'SE', dx: 1, dyCss: 1 },
    { name: 'SW', dx: -1, dyCss: 1 },
    { name: 'NE', dx: 1, dyCss: -1 },
    { name: 'NW', dx: -1, dyCss: -1 }
  ];

  var DEFAULT_BIRDS = {
    flockSize: 5,
    flockPeriodMs: 26000,
    singles: 2,
    singlePeriodMs: 15000
  };

  var DEFAULT_CLOUDS = {
    count: 2,
    opacity: 0.42,
    shade: 0.18,
    speed: 3.5
  };

  /**
   * Освещение сцены по теням домов на рендере: солнце сверху-слева,
   * тени тянутся вниз-вправо (~48°).
   * lightFromDeg: 0 = сверху, 90 = справа, 180 = снизу, 270 = слева (CSS).
   * Тень: dx=sin(deg)*L, dy=cos(deg)*L (dy+ = вниз).
   */
  var DEFAULT_SUN = {
    lightFromDeg: 48,
    shadowLen: 7,
    shadowOpacity: 0.34
  };

  /**
   * Лёгкий наклон под aerial-рендер.
   * Важно: не давить по экрану после rotate — иначе машины «едут боком».
   * pitch влияет на лёгкое локальное сплющивание кузова (поперёк), skew — слабый увод к сходy.
   */
  var DEFAULT_GROUND = {
    pitch: 0.32,
    skew: 0.22,
    personBillboard: true
  };

  /** Базовый масштаб видов относительно рендера. */
  var SPECIES_SCALE = {
    car: 0.88,
    person: 0.59,
    dog: 0.7
  };

  /** Общий множитель скорости (1 = как в БД/редакторе). */
  var MOTION_SPEED_SCALE = 1;

  /** Дети: быстрее ×1.3, меньше; бег и плавное смещение от линии по пройденной дистанции. */
  var CHILD_SPEED_MUL = 1.3;
  var CHILD_SCALE_MUL = 0.66;
  var CHILD_STRIDE_FACTOR = 0.9;
  var CHILD_WALK_AMP = 24;
  var CHILD_LATERAL_AMP = 9;
  var CHILD_LATERAL_WAVE1 = 0.1;
  var CHILD_LATERAL_WAVE2 = 0.041;
  var LIFE_ASSET_MANIFEST_SRC = '/sahmatka/template/default/img/genplan_life/manifest.json';

  function defaultLifeAssetManifestSrc() {
    var scripts;
    var i;
    var src;
    try {
      scripts = document.getElementsByTagName('script');
      for (i = 0; i < scripts.length; i++) {
        src = scripts[i].src || '';
        if (src.indexOf('genplan_life.js') !== -1) {
          return src.replace(/\/js\/genplan_life\.js(?:\?.*)?$/, '/img/genplan_life/manifest.json');
        }
      }
    } catch (e) { /* ignore */ }
    return LIFE_ASSET_MANIFEST_SRC;
  }

  /** Палитра машин для rotateVariants — id совпадают с редактором. */
  var CAR_VARIANT_PALETTE = [
    { id: 'white', sprite: 'car_c', color: '#e4e8ec' },
    { id: 'gray', sprite: 'car_a', color: '#c3c9d0' },
    { id: 'graphite', sprite: 'car_b', color: '#8b939c' },
    { id: 'blue', sprite: 'car_b', color: '#7d97b4' },
    { id: 'red', sprite: 'car_a', color: '#a84a4a' },
    { id: 'dark', sprite: 'car_b', color: '#5c636b' },
    { id: 'green', sprite: 'car_c', color: '#8aa294' }
  ];

  /** Палитра людей: муж/жен/дети, летняя одежда; child → скорость×1.3 + суета. */
  var PERSON_VARIANT_PALETTE = [
    { id: 'm_navy', sprite: 'person_m1', color: '#2f5a8a', child: false },
    { id: 'm_white', sprite: 'person_m1', color: '#e6e0d4', child: false },
    { id: 'm_teal', sprite: 'person_m2', color: '#2f9a96', child: false },
    { id: 'm_sand', sprite: 'person_m2', color: '#c9a66b', child: false },
    { id: 'w_coral', sprite: 'person_w1', color: '#e07068', child: false },
    { id: 'w_mint', sprite: 'person_w1', color: '#6cbc98', child: false },
    { id: 'w_lilac', sprite: 'person_w2', color: '#9a78b8', child: false },
    { id: 'w_sky', sprite: 'person_w2', color: '#5aa8d8', child: false },
    { id: 'w_sun', sprite: 'person_w3', color: '#e8c040', child: false },
    { id: 'w_rose', sprite: 'person_w3', color: '#d87898', child: false },
    { id: 'k_red', sprite: 'person_k1', color: '#e04848', child: true },
    { id: 'k_blue', sprite: 'person_k1', color: '#3f8ad8', child: true },
    { id: 'k_lime', sprite: 'person_k2', color: '#6ec050', child: true },
    { id: 'k_orange', sprite: 'person_k2', color: '#ef9038', child: true }
  ];

  function prefersReducedMotion() {
    try {
      return !!(global.matchMedia && global.matchMedia('(prefers-reduced-motion: reduce)').matches);
    } catch (e) {
      return false;
    }
  }

  function clamp(n, a, b) {
    if (n < a) return a;
    if (n > b) return b;
    return n;
  }

  function toNum(v, fallback) {
    var n = Number(v);
    return isFinite(n) ? n : fallback;
  }

  function toInt(v, fallback) {
    var n = parseInt(v, 10);
    return isFinite(n) ? n : fallback;
  }

  function nowMs() {
    return (global.performance && performance.now) ? performance.now() : Date.now();
  }

  /** Shortest signed delta between two angles, in (-π, π]. */
  function angleDelta(from, to) {
    var d = (to - from) % (Math.PI * 2);
    if (d > Math.PI) d -= Math.PI * 2;
    if (d < -Math.PI) d += Math.PI * 2;
    return d;
  }

  function densityFactor(density) {
    if (density === 'low') return 0.55;
    if (density === 'high') return 1.35;
    return 1;
  }

  function limitFor(species, density, birdCfg) {
    var f = densityFactor(density);
    if (species === 'car') return Math.max(1, Math.round(MAX_CARS * f));
    if (species === 'person') return Math.max(1, Math.round(MAX_PEOPLE * f));
    if (species === 'dog') return Math.max(1, Math.round(MAX_DOGS * f));
    if (species === 'bird') {
      return Math.max(1, Math.min(MAX_BIRDS, Math.round(
        ((birdCfg ? birdCfg.flockSize : 0) + (birdCfg ? birdCfg.singles : 0) + 2) * f
      )));
    }
    if (species === 'cloud') return Math.max(1, Math.min(MAX_CLOUDS, Math.round(MAX_CLOUDS * f)));
    return 1;
  }

  function normalizeFlags(flags) {
    flags = flags || {};
    return {
      life: flags.life !== false,
      lifeCars: flags.lifeCars !== false,
      lifePeople: flags.lifePeople !== false,
      lifeDogs: flags.lifeDogs !== false,
      lifeBirds: flags.lifeBirds !== false,
      lifeClouds: flags.lifeClouds !== false,
      lifeLight: flags.lifeLight !== false,
      lifeLightMode: flags.lifeLightMode || null,
      lifeDensity: flags.lifeDensity || 'auto',
      lifeBirdFlockSize: flags.lifeBirdFlockSize != null ? flags.lifeBirdFlockSize : null,
      lifeBirdFlockPeriodMs: flags.lifeBirdFlockPeriodMs != null ? flags.lifeBirdFlockPeriodMs : null,
      lifeBirdSingles: flags.lifeBirdSingles != null ? flags.lifeBirdSingles : null,
      lifeBirdSinglePeriodMs: flags.lifeBirdSinglePeriodMs != null ? flags.lifeBirdSinglePeriodMs : null,
      lifeCloudCount: flags.lifeCloudCount != null ? flags.lifeCloudCount : null,
      lifeCloudOpacity: flags.lifeCloudOpacity != null ? flags.lifeCloudOpacity : null,
      lifeCloudShade: flags.lifeCloudShade != null ? flags.lifeCloudShade : null,
      lifeCloudSpeed: flags.lifeCloudSpeed != null ? flags.lifeCloudSpeed : null,
      lifeLightFromDeg: flags.lifeLightFromDeg != null ? flags.lifeLightFromDeg : null,
      lifeShadowLen: flags.lifeShadowLen != null ? flags.lifeShadowLen : null,
      lifeShadowOpacity: flags.lifeShadowOpacity != null ? flags.lifeShadowOpacity : null,
      lifeGroundPitch: flags.lifeGroundPitch != null ? flags.lifeGroundPitch : null,
      lifeGroundSkew: flags.lifeGroundSkew != null ? flags.lifeGroundSkew : null,
      lifePersonBillboard: flags.lifePersonBillboard != null ? flags.lifePersonBillboard : null
    };
  }

  function settingsFromLife(lifeData) {
    var s = (lifeData && lifeData.settings) || {};
    return {
      cars: s.cars !== false,
      people: s.people !== false,
      dogs: s.dogs !== false,
      birds: s.birds !== false,
      clouds: s.clouds !== false,
      light: s.light || 'day',
      perspective: s.perspective || (lifeData && lifeData.perspective) || null,
      birdFlockSize: toInt(s.birdFlockSize, DEFAULT_BIRDS.flockSize),
      birdFlockPeriodMs: toInt(s.birdFlockPeriodMs, DEFAULT_BIRDS.flockPeriodMs),
      birdSingles: toInt(s.birdSingles, DEFAULT_BIRDS.singles),
      birdSinglePeriodMs: toInt(s.birdSinglePeriodMs, DEFAULT_BIRDS.singlePeriodMs),
      cloudCount: toInt(s.cloudCount, DEFAULT_CLOUDS.count),
      cloudOpacity: toNum(s.cloudOpacity, DEFAULT_CLOUDS.opacity),
      cloudShade: toNum(s.cloudShade, DEFAULT_CLOUDS.shade),
      cloudSpeed: toNum(s.cloudSpeed, DEFAULT_CLOUDS.speed),
      lightFromDeg: toNum(s.lightFromDeg, DEFAULT_SUN.lightFromDeg),
      shadowLen: toNum(s.shadowLen, DEFAULT_SUN.shadowLen),
      shadowOpacity: toNum(s.shadowOpacity, DEFAULT_SUN.shadowOpacity),
      groundPitch: toNum(s.groundPitch, DEFAULT_GROUND.pitch),
      groundSkew: toNum(s.groundSkew, DEFAULT_GROUND.skew),
      personBillboard: s.personBillboard !== false
    };
  }

  /** Смещение тени в CSS-пикселях: свет FROM deg → тень в противоположную сторону. */
  function shadowOffsetFromLight(fromDeg, len) {
    var rad = (toNum(fromDeg, DEFAULT_SUN.lightFromDeg) * Math.PI) / 180;
    var L = Math.max(0, toNum(len, DEFAULT_SUN.shadowLen));
    return {
      dx: Math.sin(rad) * L,
      dy: Math.cos(rad) * L
    };
  }

  function buildTrackIndex(tracks) {
    var map = {};
    var i;
    var t;
    if (!tracks || !tracks.length) return map;
    for (i = 0; i < tracks.length; i++) {
      t = tracks[i];
      if (!t || t.id == null) continue;
      map[String(t.id)] = t;
    }
    return map;
  }

  function polylineMetrics(points) {
    var segs = [];
    var total = 0;
    var i;
    var a;
    var b;
    var dx;
    var dy;
    var len;
    if (!points || points.length < 2) {
      return { segs: segs, total: 0, corners: [] };
    }
    for (i = 0; i < points.length - 1; i++) {
      a = points[i];
      b = points[i + 1];
      if (!a || !b || a.length < 2 || b.length < 2) continue;
      dx = Number(b[0]) - Number(a[0]);
      dy = Number(b[1]) - Number(a[1]);
      len = Math.sqrt(dx * dx + dy * dy);
      if (!(len > 0)) continue;
      segs.push({
        x0: Number(a[0]),
        y0: Number(a[1]),
        x1: Number(b[0]),
        y1: Number(b[1]),
        dx: dx,
        dy: dy,
        len: len,
        angle: Math.atan2(dy, dx),
        start: total
      });
      total += len;
    }
    return { segs: segs, total: total, corners: cornersOf(segs) };
  }

  /** Turn severity at every interior vertex → speed ceiling factor (1 = straight). */
  function cornersOf(segs) {
    var out = [];
    var i;
    var turn;
    var factor;
    for (i = 1; i < segs.length; i++) {
      turn = Math.abs(angleDelta(segs[i - 1].angle, segs[i].angle));
      if (turn < 0.12) continue;
      factor = clamp(1 - turn / (Math.PI * 0.85), 0.16, 1);
      out.push({ dist: segs[i].start, factor: factor });
    }
    return out;
  }

  function samplePolyline(metrics, dist) {
    var segs = metrics.segs;
    var total = metrics.total;
    var i;
    var s;
    var local;
    var t;
    if (!segs.length || !(total > 0)) {
      return { x: 0, y: 0, angle: 0 };
    }
    dist = clamp(dist, 0, total);
    for (i = 0; i < segs.length; i++) {
      s = segs[i];
      if (dist <= s.start + s.len || i === segs.length - 1) {
        local = dist - s.start;
        t = s.len > 0 ? clamp(local / s.len, 0, 1) : 0;
        return {
          x: s.x0 + s.dx * t,
          y: s.y0 + s.dy * t,
          angle: s.angle
        };
      }
    }
    s = segs[segs.length - 1];
    return { x: s.x1, y: s.y1, angle: s.angle };
  }

  function pickSpriteKey(species, spriteKey) {
    var Sprites = global.GenplanLifeSprites;
    var keys;
    if (spriteKey && Sprites && Sprites.get(spriteKey)) return spriteKey;
    if (Sprites && Sprites.keysFor) {
      keys = Sprites.keysFor(species);
      if (keys && keys.length) return keys[0];
    }
    if (species === 'car') return 'car_a';
    if (species === 'person') return 'person_m1';
    if (species === 'dog') return 'dog_a';
    if (species === 'bird') return 'bird_a';
    if (species === 'cloud') return 'cloud_a';
    return spriteKey || null;
  }

  function parseHex(color) {
    var c = String(color || '').replace('#', '');
    if (c.length === 3) {
      c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    }
    if (c.length < 6) return null;
    return {
      r: parseInt(c.slice(0, 2), 16),
      g: parseInt(c.slice(2, 4), 16),
      b: parseInt(c.slice(4, 6), 16)
    };
  }

  function mixHex(color, target, amount) {
    var c = parseHex(color);
    if (!c) return color;
    function ch(v) {
      return Math.round(clamp(v + (target - v) * amount, 0, 255));
    }
    return 'rgb(' + ch(c.r) + ',' + ch(c.g) + ',' + ch(c.b) + ')';
  }

  function applyTint(svg, color) {
    var base;
    var dark;
    var light;
    var i;
    var nodes;
    if (!svg || !color || !parseHex(color)) return;
    base = color;
    dark = mixHex(color, 0, 0.2);
    // крыша ловит небо и всегда заметно светлее борта — как на рендерах
    light = mixHex(color, 255, 0.45);
    nodes = svg.querySelectorAll('.gw-tint');
    for (i = 0; i < nodes.length; i++) nodes[i].setAttribute('fill', base);
    nodes = svg.querySelectorAll('.gw-tint-dark');
    for (i = 0; i < nodes.length; i++) nodes[i].setAttribute('fill', dark);
    nodes = svg.querySelectorAll('.gw-tint-light');
    for (i = 0; i < nodes.length; i++) nodes[i].setAttribute('fill', light);
  }

  function createSvgEl(spriteKey, scale, opacity, tintColor) {
    var Sprites = global.GenplanLifeSprites;
    var data = Sprites && Sprites.get ? Sprites.get(spriteKey) : null;
    var wrap = document.createElement('div');
    var frameEl;
    var sheet;
    var svg;
    var w;
    var h;
    var sc = scale > 0 ? scale : 1;
    var op = opacity != null ? opacity : 1;

    wrap.className = 'gw-life-agent';
    wrap.style.position = 'absolute';
    wrap.style.left = '0';
    wrap.style.top = '0';
    wrap.style.pointerEvents = 'none';
    wrap.style.willChange = 'transform';
    wrap.style.transformOrigin = 'center center';
    wrap.style.opacity = String(op);

    if (!data) {
      wrap.style.width = '8px';
      wrap.style.height = '8px';
      wrap.style.marginLeft = '-4px';
      wrap.style.marginTop = '-4px';
      wrap.style.borderRadius = '50%';
      wrap.style.background = tintColor || 'rgba(90,100,110,0.7)';
      return wrap;
    }

    w = data.w * sc;
    h = data.h * sc;
    wrap.style.width = w + 'px';
    wrap.style.height = h + 'px';
    wrap.style.marginLeft = (-w / 2) + 'px';
    wrap.style.marginTop = (-h / 2) + 'px';

    svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', data.viewBox);
    svg.setAttribute('width', String(w));
    svg.setAttribute('height', String(h));
    svg.setAttribute('aria-hidden', 'true');
    svg.style.display = 'block';
    svg.style.overflow = 'visible';
    svg.innerHTML = data.svgInner;
    applyTint(svg, tintColor);
    wrap.appendChild(svg);
    wrap._gwSvg = svg;
    sheet = data.sheet;
    if (sheet && sheet.src) {
      var sheetHost = document.createElement('div');
      sheetHost.style.position = 'absolute';
      sheetHost.style.left = '0';
      sheetHost.style.top = '0';
      sheetHost.style.width = '100%';
      sheetHost.style.height = '100%';
      sheetHost.style.display = 'none';
      sheetHost.style.transition = 'opacity 0.11s linear';
      sheetHost.style.opacity = '1';
      function makeSheetLayer(z) {
        var el = document.createElement('div');
        el.style.position = 'absolute';
        el.style.left = '0';
        el.style.top = '0';
        el.style.width = '100%';
        el.style.height = '100%';
        el.style.backgroundRepeat = 'no-repeat';
        el.style.backgroundImage = 'url("' + sheet.src + '")';
        el.style.backgroundSize = (Math.max(1, sheet.cols) * 100) + '% 100%';
        el.style.backgroundPosition = '0% 0%';
        el.style.opacity = z === 1 ? '1' : '0';
        el.style.transition = 'opacity 0.1s linear';
        el.style.zIndex = String(z);
        sheetHost.appendChild(el);
        return el;
      }
      var layerA = makeSheetLayer(1);
      var layerB = makeSheetLayer(0);
      wrap.appendChild(sheetHost);
      // Для растр-спрайтов возвращаем вариативность через мягкий цветовой оверлей.
      if (tintColor) {
        var tintEl = document.createElement('div');
        tintEl.style.position = 'absolute';
        tintEl.style.left = '0';
        tintEl.style.top = '0';
        tintEl.style.width = '100%';
        tintEl.style.height = '100%';
        tintEl.style.pointerEvents = 'none';
        tintEl.style.display = 'none';
        tintEl.style.background = tintColor;
        tintEl.style.opacity = '0.18';
        tintEl.style.mixBlendMode = 'multiply';
        tintEl.style.maskImage = 'url("' + sheet.src + '")';
        tintEl.style.maskRepeat = 'no-repeat';
        tintEl.style.maskSize = (Math.max(1, sheet.cols) * 100) + '% 100%';
        tintEl.style.maskPosition = '0% 0%';
        tintEl.style.zIndex = '3';
        sheetHost.appendChild(tintEl);
        wrap._gwSheetTint = tintEl;
      }
      wrap._gwSheet = {
        host: sheetHost,
        el: layerA,
        layerA: layerA,
        layerB: layerB,
        active: 'A',
        srcRight: sheet.src,
        srcLeft: sheet.srcLeft || null,
        facingLeft: false,
        frames: Math.max(1, sheet.frames || 1),
        cols: Math.max(1, sheet.cols || sheet.frames || 1),
        idx: 0,
        loaded: false
      };
      (function (host, cfg, sWrap) {
        var img = new Image();
        img.onload = function () {
          if (!host.parentNode) return;
          cfg.loaded = true;
          host.style.display = 'block';
          if (sWrap._gwSheetTint) sWrap._gwSheetTint.style.display = 'block';
          if (sWrap._gwSvg) sWrap._gwSvg.style.display = 'none';
        };
        img.onerror = function () {
          cfg.loaded = false;
          host.style.display = 'none';
          if (sWrap._gwSheetTint) sWrap._gwSheetTint.style.display = 'none';
          if (sWrap._gwSvg) sWrap._gwSvg.style.display = 'block';
        };
        img.src = sheet.src;
        if (sheet.srcLeft) {
          var imgL = new Image();
          imgL.src = sheet.srcLeft;
        }
      })(sheetHost, wrap._gwSheet, wrap);
    }
    return wrap;
  }

  function sheetPosPct(sheet, idx) {
    var n = Math.max(1, sheet.frames || 1);
    var cols = Math.max(1, sheet.cols || n);
    var col = idx % cols;
    if (cols <= 1) return '0% 0%';
    return ((col / (cols - 1)) * 100).toFixed(4) + '% 0%';
  }

  function setSheetFacing(rt, faceLeft) {
    var wrap = rt && rt.el;
    var sheet = wrap && wrap._gwSheet;
    var src;
    if (!sheet || !sheet.loaded) return;
    faceLeft = !!faceLeft;
    if (!sheet.srcLeft) return;
    if (sheet.facingLeft === faceLeft) return;
    sheet.facingLeft = faceLeft;
    src = faceLeft ? sheet.srcLeft : sheet.srcRight;
    sheet.layerA.style.backgroundImage = 'url("' + src + '")';
    sheet.layerB.style.backgroundImage = 'url("' + src + '")';
    if (wrap._gwSheetTint) wrap._gwSheetTint.style.maskImage = 'url("' + src + '")';
    // короткий fade при развороте лица
    sheet.host.style.opacity = '0.5';
    window.setTimeout(function () {
      if (sheet.host && sheet.host.parentNode) sheet.host.style.opacity = '1';
    }, 110);
  }

  function setSheetFrame(rt, idx) {
    var wrap = rt && rt.el;
    var sheet = wrap && wrap._gwSheet;
    var n;
    var pos;
    var next;
    var prev;
    if (!sheet || !sheet.loaded) return;
    n = Math.max(1, sheet.frames || 1);
    idx = ((idx % n) + n) % n;
    if (sheet.idx === idx) return;
    sheet.idx = idx;
    pos = sheetPosPct(sheet, idx);
    // короткий crossfade между кадрами шага (~100ms)
    next = sheet.active === 'A' ? sheet.layerB : sheet.layerA;
    prev = sheet.active === 'A' ? sheet.layerA : sheet.layerB;
    next.style.backgroundPosition = pos;
    next.style.opacity = '1';
    prev.style.opacity = '0';
    sheet.active = sheet.active === 'A' ? 'B' : 'A';
    sheet.el = next;
    if (wrap._gwSheetTint) wrap._gwSheetTint.style.maskPosition = pos;
  }

  function collectParts(wrap) {
    var svg = wrap && wrap._gwSvg;
    if (!svg) return { legs: [], arms: [], wings: [], brakes: [] };
    return {
      legs: [svg.querySelector('.gw-leg-a'), svg.querySelector('.gw-leg-b')],
      arms: [svg.querySelector('.gw-arm-a'), svg.querySelector('.gw-arm-b')],
      wings: [svg.querySelector('.gw-wing-a'), svg.querySelector('.gw-wing-b')],
      brakes: svg.querySelectorAll ? svg.querySelectorAll('.gw-brake') : []
    };
  }

  function shiftPart(el, dx, dy) {
    if (!el) return;
    el.setAttribute('transform', 'translate(' + dx.toFixed(2) + ',' + dy.toFixed(2) + ')');
  }

  /** Поворот конечности вокруг data-pivot (для прогулочного шага). */
  function swingPart(el, deg) {
    var p = pivotOf(el);
    if (!el) return;
    if (!p) {
      el.setAttribute('transform', 'rotate(' + deg.toFixed(2) + ')');
      return;
    }
    el.setAttribute(
      'transform',
      'translate(' + p.x + ',' + p.y + ') rotate(' + deg.toFixed(2) + ') translate(' + (-p.x) + ',' + (-p.y) + ')'
    );
  }

  function pivotOf(el) {
    var raw = el && el.getAttribute ? el.getAttribute('data-pivot') : null;
    var parts;
    if (!raw) return null;
    parts = raw.split(',');
    if (parts.length < 2) return null;
    return { x: Number(parts[0]) || 0, y: Number(parts[1]) || 0 };
  }

  function flapPart(el, k) {
    var p = pivotOf(el);
    if (!el) return;
    if (!p) {
      el.setAttribute('transform', 'scale(1,' + k.toFixed(3) + ')');
      return;
    }
    el.setAttribute(
      'transform',
      'translate(' + p.x + ',' + p.y + ') scale(1,' + k.toFixed(3) + ') translate(' + (-p.x) + ',' + (-p.y) + ')'
    );
  }

  /**
   * Perspective triangle: points[0]=vanishing (far), [1]=left near, [2]=right near (CRS y=0 bottom).
   */
  function perspectiveScaleAt(y, persp) {
    var V;
    var L;
    var R;
    var yFar;
    var yNear;
    var t;
    var sNear;
    var sFar;
    if (!persp || persp.enabled === false || !persp.points || persp.points.length < 3) {
      return 1;
    }
    V = persp.points[0];
    L = persp.points[1];
    R = persp.points[2];
    if (!V || !L || !R) return 1;
    yFar = Number(V[1]);
    yNear = (Number(L[1]) + Number(R[1])) / 2;
    if (!isFinite(yFar) || !isFinite(yNear) || Math.abs(yNear - yFar) < 1) return 1;
    t = (Number(y) - yFar) / (yNear - yFar);
    t = clamp(t, 0, 1);
    sNear = toNum(persp.scaleNear, 1);
    sFar = toNum(persp.scaleFar, 0.35);
    return sFar + t * (sNear - sFar);
  }

  /**
   * Полный warp: размер по глубине + сплющивание плоскости + боковой skew к сходy.
   * x,y — CRS; imageWidth нужен для нормализации skew без треугольника.
   */
  function perspectiveWarpAt(x, y, persp, ground, imageW) {
    var pitch = clamp(toNum(ground && ground.pitch, DEFAULT_GROUND.pitch), 0, 1);
    var skewAmt = clamp(toNum(ground && ground.skew, DEFAULT_GROUND.skew), 0, 1);
    // локальное сплющивание кузова (поперёк машины), не screen-Y после rotate
    var bodyFlat = 1 - pitch * 0.22;
    var skewDeg = 0;
    var scale = perspectiveScaleAt(y, persp);
    var V;
    var L;
    var R;
    var yFar;
    var yNear;
    var t;
    var midNearX;
    var vanX;
    var span;
    var w = imageW > 0 ? imageW : 1200;
    // чуть «вдавить» в асфальт (CSS Y вниз)
    var sink = 1.2 + pitch * 1.4;

    if (persp && persp.enabled !== false && persp.points && persp.points.length >= 3) {
      V = persp.points[0];
      L = persp.points[1];
      R = persp.points[2];
      yFar = Number(V[1]);
      yNear = (Number(L[1]) + Number(R[1])) / 2;
      if (isFinite(yFar) && isFinite(yNear) && Math.abs(yNear - yFar) >= 1) {
        t = clamp((Number(y) - yFar) / (yNear - yFar), 0, 1);
        midNearX = (Number(L[0]) + Number(R[0])) / 2;
        vanX = Number(V[0]) + t * (midNearX - Number(V[0]));
        span = Math.max(90, Math.abs(Number(R[0]) - Number(L[0])) * 0.55 + w * 0.12);
        skewDeg = clamp((Number(x) - vanX) / span, -1, 1) * skewAmt * 10 * pitch;
      }
    } else if (skewAmt > 0 && pitch > 0) {
      skewDeg = clamp((Number(x) - w * 0.5) / (w * 0.55), -1, 1) * skewAmt * 7 * pitch;
    }

    return {
      scale: scale,
      bodyFlat: Math.max(0.78, bodyFlat),
      pitchY: Math.max(0.78, bodyFlat),
      skewDeg: skewDeg,
      sink: sink,
      billboard: !(ground && ground.personBillboard === false)
    };
  }

  function applyLight(lightEl, mode, allowPulse) {
    if (!lightEl) return;
    lightEl.style.display = '';
    lightEl.style.pointerEvents = 'none';
    lightEl.style.transition = 'opacity 0.6s ease';
    lightEl.className = (lightEl.className || '').replace(/\bis-pulse\b/g, '').replace(/\s+/g, ' ').trim();

    if (!mode || mode === 'off') {
      lightEl.style.background = 'transparent';
      lightEl.style.opacity = '0';
      return;
    }

    if (mode === 'evening') {
      lightEl.style.background =
        'linear-gradient(180deg, rgba(255,140,60,0.14) 0%, rgba(40,30,50,0.08) 45%, rgba(20,25,45,0.12) 100%)';
      lightEl.style.opacity = '1';
      return;
    }

    if (mode === 'pulse') {
      lightEl.style.background =
        'radial-gradient(ellipse at 50% 40%, rgba(255,220,160,0.1) 0%, rgba(30,40,60,0.1) 70%, rgba(20,25,40,0.14) 100%)';
      lightEl.style.opacity = allowPulse ? '0.85' : '0.55';
      lightEl.className = (lightEl.className ? lightEl.className + ' ' : '') + 'is-pulse';
      return;
    }

    lightEl.style.background =
      'radial-gradient(ellipse at 50% 45%, transparent 40%, rgba(20,30,45,0.08) 100%)';
    lightEl.style.opacity = '1';
  }

  function resolveDiagonal(name) {
    var i;
    var n = String(name || 'random').toLowerCase();
    if (n === 'random' || n === '') {
      return DIAGONALS[Math.floor(Math.random() * DIAGONALS.length)];
    }
    if (n === 'se' || n === '↘') return DIAGONALS[0];
    if (n === 'sw' || n === '↙') return DIAGONALS[1];
    if (n === 'ne' || n === '↗') return DIAGONALS[2];
    if (n === 'nw' || n === '↖') return DIAGONALS[3];
    for (i = 0; i < DIAGONALS.length; i++) {
      if (DIAGONALS[i].name.toLowerCase() === n) return DIAGONALS[i];
    }
    return DIAGONALS[Math.floor(Math.random() * DIAGONALS.length)];
  }

  function create(options) {
    options = options || {};
    var layerEl = options.layerEl;
    var lightEl = options.lightEl;
    var imageWidth = toNum(options.imageWidth, 0);
    var imageHeight = toNum(options.imageHeight, 0);
    var lifeData = options.lifeData || {};
    var flags = normalizeFlags(options.flags);
    var settings = settingsFromLife(lifeData);

    var destroyed = false;
    var paused = false;
    var reduced = prefersReducedMotion();
    var rafId = 0;
    var lastTs = 0;
    var pulseT = 0;

    var trackById = buildTrackIndex(lifeData.tracks);
    var perspective = settings.perspective;
    var trackAgents = [];
    var birdSpawners = [];
    var activeBirds = [];
    var clouds = [];
    var counts = { car: 0, person: 0, dog: 0, bird: 0, cloud: 0 };
    /** Лимит видимых агентов — не ниже числа треков в данных (иначе часть «мёртвая»). */
    var speciesLimits = { car: MAX_CARS, person: MAX_PEOPLE, dog: MAX_DOGS };

    function trackLimitFor(species) {
      var configured = speciesLimits[species];
      if (configured == null) return limitFor(species, flags.lifeDensity);
      var f = densityFactor(flags.lifeDensity);
      return Math.max(configured, Math.round(configured * f));
    }

    function syncSpeciesLimits() {
      var tallies = { car: 0, person: 0, dog: 0 };
      var i;
      var sp;
      for (i = 0; i < trackAgents.length; i++) {
        sp = trackAgents[i] && trackAgents[i].species;
        if (tallies[sp] != null) tallies[sp] += 1;
      }
      speciesLimits.car = Math.max(MAX_CARS, tallies.car);
      speciesLimits.person = Math.max(MAX_PEOPLE, tallies.person);
      speciesLimits.dog = Math.max(MAX_DOGS, tallies.dog);
    }

    var mq = null;
    var onVis = null;
    var onMq = null;

    function birdConfig() {
      return {
        flockSize: clamp(toInt(flags.lifeBirdFlockSize != null ? flags.lifeBirdFlockSize : settings.birdFlockSize, DEFAULT_BIRDS.flockSize), 0, 12),
        flockPeriodMs: Math.max(4000, toInt(flags.lifeBirdFlockPeriodMs != null ? flags.lifeBirdFlockPeriodMs : settings.birdFlockPeriodMs, DEFAULT_BIRDS.flockPeriodMs)),
        singles: clamp(toInt(flags.lifeBirdSingles != null ? flags.lifeBirdSingles : settings.birdSingles, DEFAULT_BIRDS.singles), 0, 8),
        singlePeriodMs: Math.max(3000, toInt(flags.lifeBirdSinglePeriodMs != null ? flags.lifeBirdSinglePeriodMs : settings.birdSinglePeriodMs, DEFAULT_BIRDS.singlePeriodMs))
      };
    }

    function cloudConfig() {
      return {
        count: clamp(toInt(flags.lifeCloudCount != null ? flags.lifeCloudCount : settings.cloudCount, DEFAULT_CLOUDS.count), 0, 4),
        opacity: clamp(toNum(flags.lifeCloudOpacity != null ? flags.lifeCloudOpacity : settings.cloudOpacity, DEFAULT_CLOUDS.opacity), 0.05, 0.85),
        shade: clamp(toNum(flags.lifeCloudShade != null ? flags.lifeCloudShade : settings.cloudShade, DEFAULT_CLOUDS.shade), 0, 0.45),
        speed: clamp(toNum(flags.lifeCloudSpeed != null ? flags.lifeCloudSpeed : settings.cloudSpeed, DEFAULT_CLOUDS.speed), 0.5, 20)
      };
    }

    function sunConfig() {
      return {
        lightFromDeg: ((toNum(flags.lifeLightFromDeg != null ? flags.lifeLightFromDeg : settings.lightFromDeg, DEFAULT_SUN.lightFromDeg) % 360) + 360) % 360,
        shadowLen: clamp(toNum(flags.lifeShadowLen != null ? flags.lifeShadowLen : settings.shadowLen, DEFAULT_SUN.shadowLen), 0, 24),
        shadowOpacity: clamp(toNum(flags.lifeShadowOpacity != null ? flags.lifeShadowOpacity : settings.shadowOpacity, DEFAULT_SUN.shadowOpacity), 0, 0.5)
      };
    }

    function groundConfig() {
      var billboardFlag = flags.lifePersonBillboard;
      return {
        pitch: clamp(toNum(flags.lifeGroundPitch != null ? flags.lifeGroundPitch : settings.groundPitch, DEFAULT_GROUND.pitch), 0, 1),
        skew: clamp(toNum(flags.lifeGroundSkew != null ? flags.lifeGroundSkew : settings.groundSkew, DEFAULT_GROUND.skew), 0, 1),
        personBillboard: billboardFlag != null ? !!billboardFlag : settings.personBillboard !== false
      };
    }

    function toCssY(y) {
      return imageHeight - y;
    }

    function speciesEnabled(species) {
      if (!flags.life || reduced) return false;
      if (species === 'car') return flags.lifeCars && settings.cars;
      if (species === 'person') return flags.lifePeople && settings.people;
      if (species === 'dog') return flags.lifeDogs && settings.dogs;
      if (species === 'bird') return flags.lifeBirds && settings.birds;
      if (species === 'cloud') return flags.lifeClouds && settings.clouds;
      return false;
    }

    function resolveLightMode() {
      if (!flags.lifeLight) return 'off';
      if (flags.lifeLightMode) return flags.lifeLightMode;
      return settings.light || 'day';
    }

    function refreshLight() {
      var mode = resolveLightMode();
      if (reduced && mode === 'pulse') mode = 'day';
      if (!flags.life || !flags.lifeLight) mode = 'off';
      applyLight(lightEl, mode, !reduced);
    }

    function removeNode(el) {
      if (el && el.parentNode) el.parentNode.removeChild(el);
    }

    function clearLayer() {
      if (!layerEl) return;
      while (layerEl.firstChild) {
        layerEl.removeChild(layerEl.firstChild);
      }
    }

    function setAgentTransform(el, x, yCss, angleRad, warp, species) {
      var deg = (angleRad * 180) / Math.PI;
      var s = warp && warp.scale > 0 ? warp.scale : 1;
      var flat = warp && warp.bodyFlat > 0 ? warp.bodyFlat : 1;
      var skew = warp && warp.skewDeg ? warp.skewDeg : 0;
      var sink = warp && warp.sink != null ? warp.sink : 1.5;
      var face;
      var lean;
      var yPlant = yCss + sink;
      var personPlantNudge = 1.2;

      if (species === 'person' && warp && warp.billboard) {
        // ноги в точке трека: без лишнего sink, иначе «летят»
        // walk.png смотрит вправо, walk_left — влево. При srcLeft не зеркалим CSS.
        var faceLeft = Math.cos(angleRad) < 0;
        if (el._gwSheet && el._gwSheet.srcLeft) {
          setSheetFacing({ el: el }, faceLeft);
          face = 1;
        } else {
          // без левого листа — зеркало; правый лист смотрит вправо
          face = faceLeft ? -1 : 1;
        }
        lean = Math.sin(angleRad) * -5;
        el.style.transform =
          'translate(' + x.toFixed(2) + 'px,' + (yCss + personPlantNudge).toFixed(2) + 'px) ' +
          'skewX(' + (skew * 0.25).toFixed(2) + 'deg) ' +
          'rotate(' + lean.toFixed(2) + 'deg) ' +
          'scale(' + (s * face).toFixed(3) + ',' + s.toFixed(3) + ')';
        return;
      }

      // машины: сначала локальный scale (чуть сплющить поперёк кузова), потом rotate по курсу —
      // так нос остаётся вдоль дороги, без «езда боком» от screen-Y squash
      el.style.transform =
        'translate(' + x.toFixed(2) + 'px,' + yPlant.toFixed(2) + 'px) ' +
        'skewX(' + skew.toFixed(2) + 'deg) ' +
        'rotate(' + deg.toFixed(2) + 'deg) ' +
        'scale(' + s.toFixed(3) + ',' + (s * flat).toFixed(3) + ')';
    }

    function makeGroundShadow(species, baseScale) {
      var isCar = species === 'car';
      var isDog = species === 'dog';
      var isPerson = species === 'person';
      var w = (isCar ? 22 : isDog ? 12 : isPerson ? 12 : 5) * (baseScale > 0 ? baseScale : 1);
      var h = (isCar ? 9 : isDog ? 5 : isPerson ? 5.5 : 3) * (baseScale > 0 ? baseScale : 1);
      var el = document.createElement('div');
      el.className = 'gw-life-ground-shadow';
      el.style.position = 'absolute';
      el.style.left = '0';
      el.style.top = '0';
      el.style.width = w + 'px';
      el.style.height = h + 'px';
      el.style.marginLeft = (-w / 2) + 'px';
      el.style.marginTop = (-h / 2) + 'px';
      el.style.pointerEvents = 'none';
      el.style.willChange = 'transform, opacity';
      el.style.borderRadius = '50%';
      el.style.background = isPerson
        ? 'radial-gradient(ellipse at 50% 42%, rgba(8,12,18,0.62) 0%, rgba(8,12,18,0.28) 48%, rgba(8,12,18,0) 78%)'
        : 'radial-gradient(ellipse at 50% 50%, rgba(12,18,28,0.5) 0%, rgba(12,18,28,0.18) 52%, rgba(12,18,28,0) 78%)';
      // У людей должна быть "прилепленная" тень под стопами, иначе создаётся ощущение полёта.
      el.style.filter = isPerson ? 'blur(0.4px)' : 'blur(1.4px)';
      el.style.visibility = 'hidden';
      el.style.opacity = '0';
      if (layerEl) layerEl.appendChild(el);
      return el;
    }

    function placeGroundShadow(rt, x, yCss, warp, fade) {
      var sun;
      var off;
      var s;
      var sink;
      var headingDeg;
      var op;
      var flat;
      var personContactY = yCss + 1.2;
      if (!rt.shadowEl) return;
      sun = sunConfig();
      if ((rt.species === 'car' || rt.species === 'dog') &&
        (!(sun.shadowLen > 0) || !(sun.shadowOpacity > 0) || !(fade > 0.02))) {
        rt.shadowEl.style.visibility = 'hidden';
        return;
      }
      if (rt.species === 'person' && !(fade > 0.02)) {
        rt.shadowEl.style.visibility = 'hidden';
        return;
      }
      s = warp && warp.scale > 0 ? warp.scale : 1;
      flat = warp && warp.bodyFlat > 0 ? warp.bodyFlat : 1;
      sink = warp && warp.sink != null ? warp.sink : 1.5;
      headingDeg = rt.headingReady ? (rt.heading * 180) / Math.PI : 0;
      rt.shadowEl.style.visibility = 'visible';
      if (rt.species === 'car' || rt.species === 'dog') {
        off = shadowOffsetFromLight(sun.lightFromDeg, Math.min(sun.shadowLen, 5) * s * 0.55);
        op = sun.shadowOpacity * fade;
        rt.shadowEl.style.opacity = op.toFixed(3);
        rt.shadowEl.style.transform =
          'translate(' + (x + off.dx).toFixed(2) + 'px,' + (yCss + sink + off.dy).toFixed(2) + 'px) ' +
          'rotate(' + headingDeg.toFixed(2) + 'deg) ' +
          'scale(' + s.toFixed(3) + ',' + (s * flat * 0.7).toFixed(3) + ')';
      } else {
        // контактная тень прямо под стопами — фигура «стоит», не летит
        off = shadowOffsetFromLight(sun.lightFromDeg, Math.min(sun.shadowLen, 6) * s * 0.35);
        // Даже при выключенном "глобальном" солнце у людей оставляем минимальный контактный след.
        op = Math.max(0.28, Math.min(0.84, sun.shadowOpacity * 2.6)) * fade;
        rt.shadowEl.style.opacity = op.toFixed(3);
        rt.shadowEl.style.transform =
          // Сильно уменьшаем сдвиг по Y (off.dy), т.к. warp/yCss уже задают позицию на земле.
          'translate(' + (x + off.dx * 0.3).toFixed(2) + 'px,' + (personContactY + off.dy * 0.08).toFixed(2) + 'px) ' +
          'scale(' + (s * 1.05).toFixed(3) + ',' + (s * 0.72).toFixed(3) + ')';
      }
    }

    // ─── Track agents (cars / people / dogs) ───────────────

    function makeTrackRuntime(agent) {
      var track = trackById[String(agent.trackId)];
      var metrics = polylineMetrics(track && track.points);
      var params = agent.params || {};
      var species = agent.species;
      var speed = Math.max(0.5, toNum(agent.speed, 40) * MOTION_SPEED_SCALE);
      var periodMs = Math.max(1000, toNum(agent.periodMs, 8000));
      var phase = clamp(toNum(params.phase, 0), 0, 1);
      var direction = params.direction === -1 ? -1 : 1;
      var speciesScale = SPECIES_SCALE[species] != null ? SPECIES_SCALE[species] : 1;
      var scale = (toNum(params.scale, 1) || 1) * speciesScale;
      var opacity = params.opacity != null ? toNum(params.opacity, 1) : 1;
      var tint = params.color || null;
      var spriteKey = pickSpriteKey(species, agent.spriteKey);
      var isCar = species === 'car';
      var el;
      var shadowEl;

      var personStop = clamp(toNum(params.stopChance, 0.015), 0, 0.5);

      if (!(metrics.total > 0)) return null;

      shadowEl = makeGroundShadow(species, scale);
      el = createSvgEl(spriteKey, scale, opacity, tint);
      if (species === 'person') {
        // опора в ногах: низ спрайта в точке трека
        el.style.transformOrigin = '50% 100%';
        el.style.marginTop = (-parseFloat(el.style.height || '16')).toFixed(2) + 'px';
      }
      if (layerEl) layerEl.appendChild(el);
      el.style.visibility = 'hidden';
      el.style.opacity = '0';

      return {
        species: species,
        metrics: metrics,
        el: el,
        shadowEl: shadowEl,
        parts: collectParts(el),
        baseSpeed: speed,
        rawBaseSpeed: speed,
        baseOpacity: opacity,
        baseScale: scale,
        rawBaseScale: scale,
        periodMs: periodMs,
        direction: direction,
        yJitter: toNum(params.yJitter, 0),
        accel: isCar ? speed * 0.95 : speed * 1.2,
        decel: isCar ? speed * 1.35 : speed * 1.7,
        turnRate: isCar ? 5.5 : 2.4,
        strideLen: species === 'person' ? 6.2 : 3.4,
        walkAmp: species === 'person' ? 18 : 0,
        baseStopChance: isCar
          ? clamp(toNum(params.stopChance, 0.05), 0, 0.5)
          : personStop,
        stopChance: isCar
          ? clamp(toNum(params.stopChance, 0.05), 0, 0.5)
          : personStop,
        isChild: false,
        lateralOffset: 0,
        lateralPhase: 0,
        lateralPhase2: 0,
        // появление/исчезновение в конечных точках трека
        fadeInMs: 700,
        fadeOutMs: 650,
        fadeOutDist: Math.min(metrics.total * 0.35, Math.max(12, speed * 0.65)),
        // машины и люди: при появлении брать вариант, которого меньше всего видно
        rotateVariants: (species === 'car' || species === 'person') && params.rotateVariants !== false,
        activeVariantId: null,
        palette: species === 'car'
          ? CAR_VARIANT_PALETTE
          : (species === 'person' ? PERSON_VARIANT_PALETTE : null),
        baseSpriteKey: spriteKey,
        baseTint: tint,
        // simulation state
        visible: false,
        running: false,
        dist: 0,
        v: 0,
        dirSign: direction >= 0 ? 1 : -1,
        fade: 0,
        fadeState: 'idle',
        lastOpacity: null,
        heading: 0,
        headingReady: false,
        stepPhase: 0,
        stopUntil: 0,
        stopRollAt: 0,
        speedBias: 0.85 + Math.random() * 0.3,
        // старт: bootstrap выставит часть сразу; остальным — короткий джиттер, не полный period
        nextDepartAt: nowMs() + 900 + Math.random() * 4500
      };
    }

    function applyPersonVariant(rt, pick) {
      var isChild;
      var tint;
      var spriteKey;
      if (rt.species !== 'person') return;
      isChild = !!(pick && pick.child);
      rt.isChild = isChild;
      rt.baseSpeed = rt.rawBaseSpeed * (isChild ? CHILD_SPEED_MUL : 1);
      rt.baseScale = rt.rawBaseScale * (isChild ? CHILD_SCALE_MUL : 1);
      rt.stopChance = isChild ? 0 : rt.baseStopChance;
      rt.strideLen = isChild
        ? Math.max(4.8, rt.baseSpeed * CHILD_STRIDE_FACTOR)
        : 6.2;
      rt.walkAmp = isChild ? CHILD_WALK_AMP : 18;
      rt.accel = isChild ? rt.baseSpeed * 1.55 : rt.baseSpeed * 1.2;
      if (isChild) {
        rt.lateralPhase = Math.random() * Math.PI * 2;
        rt.lateralPhase2 = Math.random() * Math.PI * 2;
        rt.lateralOffset =
          Math.sin(rt.lateralPhase) * CHILD_LATERAL_AMP +
          Math.sin(rt.lateralPhase2) * CHILD_LATERAL_AMP * 0.42;
      } else {
        rt.lateralPhase = 0;
        rt.lateralPhase2 = 0;
        rt.lateralOffset = 0;
      }
      if (pick) {
        tint = pick.color;
        spriteKey = pick.sprite;
      } else {
        tint = rt.baseTint;
        spriteKey = rt.baseSpriteKey;
      }
      replaceTrackSprite(rt, spriteKey, tint);
    }

    function replaceTrackSprite(rt, spriteKey, tint) {
      var next = createSvgEl(spriteKey, rt.baseScale || 1, rt.baseOpacity, tint);
      var old = rt.el;
      if (rt.species === 'person') {
        next.style.transformOrigin = '50% 100%';
        next.style.marginTop = (-parseFloat(next.style.height || '16')).toFixed(2) + 'px';
      }
      next.style.visibility = old.style.visibility;
      if (rt.visible && rt.fade >= 1) {
        next.style.opacity = String(rt.baseOpacity != null ? rt.baseOpacity : 1);
      } else if (rt.visible && rt.fade > 0) {
        next.style.opacity = String((rt.baseOpacity != null ? rt.baseOpacity : 1) * rt.fade);
      } else {
        next.style.opacity = old.style.opacity || '0';
      }
      next.style.transform = old.style.transform || '';
      if (old.parentNode) old.parentNode.replaceChild(next, old);
      else if (layerEl) layerEl.appendChild(next);
      removeNode(old);
      rt.el = next;
      rt.parts = collectParts(next);
      rt.lastOpacity = null;
      rt.brakeOn = null;
    }

    function showTrackAgent(rt) {
      if (rt.visible) return false;
      if (counts[rt.species] >= trackLimitFor(rt.species)) return false;
      rt.visible = true;
      counts[rt.species] += 1;
      rt.el.style.visibility = 'visible';
      return true;
    }

    function hideTrackAgent(rt) {
      if (!rt.visible) return;
      rt.visible = false;
      rt.activeVariantId = null;
      counts[rt.species] = Math.max(0, counts[rt.species] - 1);
      rt.el.style.visibility = 'hidden';
      if (rt.shadowEl) rt.shadowEl.style.visibility = 'hidden';
      setAgentOpacity(rt, 0);
    }

    /** Выбрать цвет/спрайт, которых сейчас меньше всего среди видимых того же вида. */
    function pickLeastUsedVariant(rt) {
      var palette = rt.palette || (rt.species === 'person' ? PERSON_VARIANT_PALETTE : CAR_VARIANT_PALETTE);
      var used = {};
      var i;
      var other;
      var id;
      var minCount;
      var candidates;
      var pickIdx;

      for (i = 0; i < palette.length; i++) {
        used[palette[i].id] = 0;
      }
      for (i = 0; i < trackAgents.length; i++) {
        other = trackAgents[i];
        if (!other || other === rt || !other.visible || other.species !== rt.species) continue;
        id = other.activeVariantId;
        if (id && used[id] != null) used[id] += 1;
      }

      minCount = Infinity;
      candidates = [];
      for (i = 0; i < palette.length; i++) {
        id = palette[i].id;
        if (used[id] < minCount) {
          minCount = used[id];
          candidates = [i];
        } else if (used[id] === minCount) {
          candidates.push(i);
        }
      }
      pickIdx = candidates[Math.floor(Math.random() * candidates.length)];
      return palette[pickIdx];
    }

    function setAgentOpacity(rt, k) {
      var op = rt.baseOpacity * clamp(k, 0, 1);
      if (rt.lastOpacity != null && Math.abs(rt.lastOpacity - op) < 0.008) return;
      rt.lastOpacity = op;
      rt.el.style.opacity = op.toFixed(3);
    }

    function departTrackAgent(rt, ts, opts) {
      var pick;
      var seeded = !!(opts && opts.seeded);
      var margin;
      var tAlong;
      var sample;
      var wantAngle;
      var y;
      var x;
      var warp;
      var perpX;
      var perpY;
      if (!showTrackAgent(rt)) {
        rt.nextDepartAt = ts + 1200;
        return;
      }
      rt.running = true;
      rt.dirSign = rt.direction >= 0 ? 1 : -1;
      rt.headingReady = false;
      rt.stopUntil = 0;
      rt.stopRollAt = ts + 1500;
      rt.speedBias = 0.85 + Math.random() * 0.3;
      rt.stepPhase = Math.random() * (rt.strideLen || 6);

      if (rt.rotateVariants && rt.palette && rt.palette.length) {
        pick = pickLeastUsedVariant(rt);
        rt.activeVariantId = pick.id;
        if (rt.species === 'person') {
          applyPersonVariant(rt, pick);
        } else {
          replaceTrackSprite(rt, pick.sprite, pick.color);
        }
      } else if (rt.species === 'person') {
        applyPersonVariant(rt, null);
      }

      if (seeded && rt.metrics.total > 0) {
        // сразу на треке, уже в пути — вся сцена заполнена при загрузке
        margin = Math.max(2, Math.min(rt.metrics.total * 0.12, rt.fadeOutDist + 4));
        if (rt.metrics.total <= margin * 2 + 2) {
          rt.dist = rt.metrics.total * (0.22 + Math.random() * 0.56);
        } else {
          tAlong = 0.2 + Math.random() * 0.55;
          rt.dist = clamp(tAlong * rt.metrics.total, margin, Math.max(margin, rt.metrics.total - margin));
          if (rt.dirSign < 0) {
            rt.dist = rt.metrics.total - rt.dist;
            rt.dist = clamp(rt.dist, margin, Math.max(margin, rt.metrics.total - margin));
          }
        }
        rt.v = rt.baseSpeed * rt.speedBias * (0.9 + Math.random() * 0.15);
        rt.fade = 1;
        rt.fadeState = 'run';
        setAgentOpacity(rt, 1);
        sample = samplePolyline(rt.metrics, clamp(rt.dist, 0, rt.metrics.total));
        wantAngle = -sample.angle;
        if (rt.dirSign < 0) wantAngle += Math.PI;
        rt.heading = wantAngle;
        rt.headingReady = true;
        x = sample.x;
        y = sample.y + rt.yJitter;
        if (rt.isChild) {
          perpX = -Math.sin(sample.angle) * rt.lateralOffset;
          perpY = Math.cos(sample.angle) * rt.lateralOffset;
          x += perpX;
          y += perpY;
        }
        warp = perspectiveWarpAt(x, y, perspective, groundConfig(), imageWidth);
        setAgentTransform(rt.el, x, toCssY(y), rt.heading, warp, rt.species);
        placeGroundShadow(rt, x, toCssY(y), warp, 1);
        return;
      }

      // обычный выезд: с конца трека + fade-in
      rt.dist = rt.dirSign > 0 ? 0 : rt.metrics.total;
      rt.v = 0;
      rt.fade = 0;
      rt.fadeState = 'in';
      setAgentOpacity(rt, 0);
    }

    /**
     * Стартовый посев: все машины и люди сразу на сцене (на своих треках).
     */
    function bootstrapTrackAgents() {
      var ts = nowMs();
      var bySp = { car: [], person: [], dog: [] };
      var sp;
      var list;
      var i;
      var a;

      for (i = 0; i < trackAgents.length; i++) {
        a = trackAgents[i];
        if (!a || !bySp[a.species]) continue;
        if (!speciesEnabled(a.species)) continue;
        bySp[a.species].push(a);
      }

      for (sp in bySp) {
        if (!Object.prototype.hasOwnProperty.call(bySp, sp)) continue;
        list = bySp[sp];
        if (!list.length) continue;
        for (i = 0; i < list.length; i++) {
          departTrackAgent(list[i], ts, { seeded: true });
        }
      }
    }

    function finishTrackAgent(rt, ts) {
      rt.running = false;
      rt.fade = 0;
      rt.fadeState = 'idle';
      hideTrackAgent(rt);
      // направление не меняем — следующий выезд с той же стороны
      rt.nextDepartAt = ts + rt.periodMs * (0.85 + Math.random() * 0.3);
    }

    /** Speed ceiling from upcoming corners and the end of the polyline. */
    function targetSpeedFor(rt) {
      var base = rt.baseSpeed * rt.speedBias;
      var lookahead = Math.max(10, (rt.v * rt.v) / (2 * rt.decel) + rt.v * 0.4);
      var target = base;
      var corners = rt.metrics.corners || [];
      var i;
      var c;
      var gap;
      var allowed;
      var distToEnd;

      for (i = 0; i < corners.length; i++) {
        c = corners[i];
        gap = rt.dirSign > 0 ? c.dist - rt.dist : rt.dist - c.dist;
        if (gap < -6 || gap > lookahead) continue;
        allowed = Math.sqrt(
          Math.pow(base * c.factor, 2) + 2 * rt.decel * Math.max(0, gap)
        );
        if (allowed < target) target = allowed;
      }

      // у конечной точки агент уже растворяется — тормозить в ноль не нужно
      if (rt.fadeState !== 'out') {
        distToEnd = rt.dirSign > 0 ? rt.metrics.total - rt.dist : rt.dist;
        allowed = Math.sqrt(2 * rt.decel * Math.max(0, distToEnd));
        if (allowed < target) target = allowed;
      }

      return Math.max(0, target);
    }

    function animateWalk(rt, moved) {
      var amp;
      var cycle;
      var legDeg;
      var armDeg;
      var n;
      var idx;
      var legA = rt.parts.legs[0];
      var legB = rt.parts.legs[1];
      var armA = rt.parts.arms[0];
      var armB = rt.parts.arms[1];

      // фаза шага строго от пройденной дистанции (px)
      rt.stepPhase += Math.max(0, moved);

      if (rt.el && rt.el._gwSheet && rt.el._gwSheet.loaded) {
        n = Math.max(1, rt.el._gwSheet.frames || 1);
        if (rt.v < rt.baseSpeed * 0.08) {
          setSheetFrame(rt, 0);
          return;
        }
        // чуть длиннее цикл кадра — меньше рывков; fade внутри setSheetFrame
        idx = Math.floor((rt.stepPhase / Math.max(1, rt.strideLen * 1.15)) * (n / 2));
        setSheetFrame(rt, idx);
        return;
      }

      if (!legA && !legB && !armA && !armB) return;
      if (rt.v < rt.baseSpeed * 0.08) {
        swingPart(legA, 0);
        swingPart(legB, 0);
        swingPart(armA, 0);
        swingPart(armB, 0);
        return;
      }

      if (rt.species === 'person') {
        var speedK = clamp(rt.v / Math.max(0.5, rt.baseSpeed), 0, 1.12);
        cycle = (rt.stepPhase / Math.max(1, rt.strideLen)) * Math.PI * 2;
        legDeg = Math.sin(cycle) * (rt.walkAmp != null ? rt.walkAmp : 18) * speedK;
        armDeg = -legDeg * (rt.isChild ? 0.68 : 0.55);
        swingPart(legA, legDeg);
        swingPart(legB, -legDeg);
        swingPart(armA, armDeg);
        swingPart(armB, -armDeg);
        return;
      }

      amp = 0.85;
      cycle = (rt.stepPhase / Math.max(1, rt.strideLen)) * Math.PI;
      shiftPart(legA, Math.sin(cycle) * amp, 0);
      shiftPart(legB, -Math.sin(cycle) * amp, 0);
      shiftPart(armA, -Math.sin(cycle) * amp * 0.7, 0);
      shiftPart(armB, Math.sin(cycle) * amp * 0.7, 0);
    }

    function setBrakeLights(rt, on) {
      var nodes = rt.parts.brakes;
      var i;
      if (!nodes || !nodes.length) return;
      if (rt.brakeOn === on) return;
      rt.brakeOn = on;
      for (i = 0; i < nodes.length; i++) {
        nodes[i].setAttribute('opacity', on ? '1' : '0.85');
        nodes[i].setAttribute('fill', on ? '#ff5a4d' : '#c9483f');
      }
    }

    function updateTrackAgent(rt, ts, dt) {
      var dtSec = dt / 1000;
      var target;
      var moved;
      var distToEnd;
      var sample;
      var wantAngle;
      var k;
      var y;
      var x;
      var bob = 0;
      var warp;
      var perpX;
      var perpY;

      if (!speciesEnabled(rt.species)) {
        if (rt.running) {
          rt.running = false;
        }
        hideTrackAgent(rt);
        return;
      }

      if (!rt.running) {
        if (ts >= rt.nextDepartAt) departTrackAgent(rt, ts);
        if (!rt.running) return;
      }

      // random dwell: parked car / person pausing
      if (rt.stopUntil > ts) {
        target = 0;
      } else {
        if (ts >= rt.stopRollAt) {
          rt.stopRollAt = ts + 1000;
          if (Math.random() < rt.stopChance) {
            rt.stopUntil = ts + (rt.species === 'car' ? 900 + Math.random() * 2200 : 700 + Math.random() * 1800);
          }
        }
        target = targetSpeedFor(rt);
      }

      if (target > rt.v) {
        rt.v = Math.min(target, rt.v + rt.accel * dtSec);
      } else {
        rt.v = Math.max(target, rt.v - rt.decel * dtSec);
      }
      if (rt.v < 0) rt.v = 0;

      moved = rt.v * dtSec;
      rt.dist = clamp(rt.dist + moved * rt.dirSign, 0, rt.metrics.total);

      // фейд: проявление на старте, затухание у конечной точки
      distToEnd = rt.dirSign > 0 ? rt.metrics.total - rt.dist : rt.dist;
      if (rt.fadeState === 'in') {
        rt.fade = Math.min(1, rt.fade + dt / rt.fadeInMs);
        if (rt.fade >= 1) rt.fadeState = 'run';
      }
      if (rt.fadeState !== 'out' && distToEnd <= rt.fadeOutDist) {
        rt.fadeState = 'out';
      }
      if (rt.fadeState === 'out') {
        rt.fade = Math.max(0, rt.fade - dt / rt.fadeOutMs);
      }
      setAgentOpacity(rt, rt.fade);
      if (rt.fadeState === 'out' && rt.fade <= 0) {
        finishTrackAgent(rt, ts);
        return;
      }

      sample = samplePolyline(rt.metrics, clamp(rt.dist, 0, rt.metrics.total));
      // CRS atan2(dy,dx) → CSS (y flipped): negate angle
      wantAngle = -sample.angle;
      if (rt.dirSign < 0) wantAngle += Math.PI;

      if (!rt.headingReady) {
        rt.heading = wantAngle;
        rt.headingReady = true;
      } else {
        k = 1 - Math.exp(-rt.turnRate * dtSec);
        rt.heading += angleDelta(rt.heading, wantAngle) * k;
      }

      if (rt.species === 'car') {
        setBrakeLights(rt, rt.v < rt.baseSpeed * 0.45);
      } else {
        animateWalk(rt, moved);
        if (rt.v > rt.baseSpeed * 0.08) {
          bob = Math.sin((rt.stepPhase / Math.max(1, rt.strideLen)) * Math.PI * 2) *
            (rt.isChild ? 0.09 : 0.06) * clamp(rt.v / Math.max(0.5, rt.baseSpeed), 0, 1.1);
        }
      }

      x = sample.x;
      y = sample.y + rt.yJitter;
      if (rt.isChild && moved > 0) {
        var latK = clamp(rt.v / Math.max(0.5, rt.baseSpeed), 0.12, 1);
        rt.lateralPhase += moved * CHILD_LATERAL_WAVE1;
        rt.lateralPhase2 += moved * CHILD_LATERAL_WAVE2;
        rt.lateralOffset =
          (Math.sin(rt.lateralPhase) * CHILD_LATERAL_AMP +
            Math.sin(rt.lateralPhase2) * CHILD_LATERAL_AMP * 0.42) * latK;
        perpX = -Math.sin(sample.angle) * rt.lateralOffset;
        perpY = Math.cos(sample.angle) * rt.lateralOffset;
        x += perpX;
        y += perpY;
      } else if (rt.isChild && rt.lateralOffset) {
        perpX = -Math.sin(sample.angle) * rt.lateralOffset;
        perpY = Math.cos(sample.angle) * rt.lateralOffset;
        x += perpX;
        y += perpY;
      }

      warp = perspectiveWarpAt(x, y, perspective, groundConfig(), imageWidth);
      setAgentTransform(
        rt.el,
        x,
        toCssY(y) + bob,
        rt.heading,
        warp,
        rt.species
      );
      placeGroundShadow(
        rt,
        x,
        toCssY(y) + bob,
        warp,
        rt.fade
      );
    }

    // ─── Birds: flocks + singles ───────────────────────────

    function makeBird(ts, agentCfg, path, offAlong, offSide, sizeMul) {
      var params = (agentCfg && agentCfg.params) || {};
      var scale = (toNum(params.scale, 1) || 1) * (sizeMul || 1);
      var opacity = params.opacity != null ? toNum(params.opacity, 0.85) : 0.85;
      var spriteKey = pickSpriteKey('bird', agentCfg && agentCfg.spriteKey);
      var el = createSvgEl(spriteKey, scale, opacity, params.color || null);
      var cos = Math.cos(path.angle);
      var sin = Math.sin(path.angle);

      layerEl.appendChild(el);
      counts.bird += 1;

      return {
        el: el,
        parts: collectParts(el),
        t0: ts,
        lifespan: path.lifespan,
        x0: path.x0,
        y0: path.y0,
        x1: path.x1,
        y1: path.y1,
        // formation offset rotated into flight direction
        dx: offAlong * cos - offSide * sin,
        dy: offAlong * sin + offSide * cos,
        angle: path.angle,
        wobble: 3 + Math.random() * 5,
        wobbleFreq: 0.004 + Math.random() * 0.003,
        flapFreq: 0.011 + Math.random() * 0.005,
        flapPhase: Math.random() * Math.PI * 2
      };
    }

    function makeBirdPath(agentCfg) {
      var params = (agentCfg && agentCfg.params) || {};
      var birdP = params.bird || {};
      var lifespan = Math.max(6000, toNum(birdP.lifespanMs, 28000));
      var altMin = toNum(birdP.altitudeMin, imageHeight * 0.12);
      var altMax = toNum(birdP.altitudeMax, imageHeight * 0.5);
      var diag = resolveDiagonal(birdP.diagonal);
      var margin = 90;
      var band = altMin + Math.random() * Math.max(1, altMax - altMin);
      var x0;
      var x1;
      var y0;
      var y1;

      if (diag.dx > 0) {
        x0 = -margin;
        x1 = imageWidth + margin;
      } else {
        x0 = imageWidth + margin;
        x1 = -margin;
      }
      if (diag.dyCss > 0) {
        y0 = band;
        y1 = imageHeight - band;
      } else {
        y0 = imageHeight - band;
        y1 = band;
      }
      y0 += (Math.random() - 0.5) * 40;
      y1 += (Math.random() - 0.5) * 40;

      return {
        x0: x0,
        y0: y0,
        x1: x1,
        y1: y1,
        lifespan: lifespan,
        angle: Math.atan2(y1 - y0, x1 - x0)
      };
    }

    /** V-formation: leader in front, wings trailing behind on both sides. */
    function spawnFlock(ts, agentCfg, size) {
      var cfg = birdConfig();
      var limit = limitFor('bird', flags.lifeDensity, cfg);
      var path;
      var i;
      var rank;
      var side;
      var spacingAlong = 16;
      var spacingSide = 11;

      if (!speciesEnabled('bird') || !layerEl) return;
      if (!(imageWidth > 0) || !(imageHeight > 0)) return;
      size = Math.min(size, Math.max(0, limit - counts.bird));
      if (size <= 0) return;

      path = makeBirdPath(agentCfg);
      for (i = 0; i < size; i++) {
        rank = Math.ceil(i / 2);
        side = i === 0 ? 0 : (i % 2 === 1 ? -1 : 1);
        activeBirds.push(makeBird(
          ts,
          agentCfg,
          path,
          -rank * spacingAlong - Math.random() * 4,
          side * rank * spacingSide + (Math.random() - 0.5) * 5,
          0.85 + Math.random() * 0.25
        ));
      }
    }

    function spawnSingleBird(ts, agentCfg) {
      var cfg = birdConfig();
      if (!speciesEnabled('bird') || !layerEl) return;
      if (!(imageWidth > 0) || !(imageHeight > 0)) return;
      if (counts.bird >= limitFor('bird', flags.lifeDensity, cfg)) return;
      activeBirds.push(makeBird(ts, agentCfg, makeBirdPath(agentCfg), 0, 0, 0.9 + Math.random() * 0.3));
    }

    function updateBirds(ts) {
      var i;
      var b;
      var t;
      var x;
      var y;
      var flap;
      var keep = [];
      var enabled = speciesEnabled('bird');

      for (i = 0; i < activeBirds.length; i++) {
        b = activeBirds[i];
        t = (ts - b.t0) / b.lifespan;
        if (t >= 1 || !enabled) {
          removeNode(b.el);
          counts.bird = Math.max(0, counts.bird - 1);
          continue;
        }
        x = b.x0 + (b.x1 - b.x0) * t + b.dx;
        y = b.y0 + (b.y1 - b.y0) * t + b.dy + Math.sin(ts * b.wobbleFreq) * b.wobble;
        setAgentTransform(b.el, x, y, b.angle, { scale: 1, bodyFlat: 1, skewDeg: 0, sink: 0 }, 'bird');
        flap = 0.28 + 0.72 * (0.5 + 0.5 * Math.cos(ts * b.flapFreq + b.flapPhase));
        flapPart(b.parts.wings[0], flap);
        flapPart(b.parts.wings[1], flap);
        b.el.style.opacity = String(t < 0.08 ? t / 0.08 : (t > 0.88 ? (1 - t) / 0.12 : 1));
        keep.push(b);
      }
      activeBirds = keep;
    }

    // ─── Clouds: только естественные тени (без светлых пятен), внутри рендера ─

    function makeCloudShadeBlob(w, h, shade, soft) {
      var el = document.createElement('div');
      var core = clamp(shade, 0, 0.45);
      var mid = core * 0.55;
      el.className = 'gw-life-cloud-shade';
      el.style.position = 'absolute';
      el.style.left = '0';
      el.style.top = '0';
      el.style.width = w + 'px';
      el.style.height = h + 'px';
      el.style.marginLeft = (-w / 2) + 'px';
      el.style.marginTop = (-h / 2) + 'px';
      el.style.pointerEvents = 'none';
      el.style.willChange = 'transform, opacity';
      el.style.borderRadius = '50%';
      // тёпло-серый холодный тон асфальта/неба — без белого блика
      el.style.background =
        'radial-gradient(ellipse at 50% 45%, rgba(22,32,48,' + core.toFixed(3) + ') 0%, rgba(22,32,48,' +
        mid.toFixed(3) + ') ' + (soft ? '38%' : '48%') + ', rgba(22,32,48,0) 76%)';
      el.style.opacity = '1';
      el.style.filter = soft ? 'blur(10px)' : 'blur(6px)';
      return el;
    }

    function makeCloud(agentCfg, index, cfg) {
      var params = (agentCfg && agentCfg.params) || {};
      var cloudP = params.cloud || {};
      // тени живут в средней/верхней зоне плана
      var bandY = clamp(toNum(cloudP.bandY, 0.18 + index * 0.12), 0.12, 0.55);
      var speed = Math.max(0.5, toNum(agentCfg && agentCfg.speed, cfg.speed));
      // opacity в настройках = сила тени; shade — базовое затемнение
      var shade = clamp(
        toNum(cloudP.shade, cfg.shade) * (0.7 + toNum(params.opacity, cfg.opacity)),
        0.04,
        0.4
      );
      var scale = toNum(params.scale, 1 + index * 0.15) || 1;
      var shadeW = Math.round((220 + index * 50) * scale);
      var shadeH = Math.round((130 + index * 28) * scale);
      var wrap;
      var main;
      var side;
      var soft;
      var yCss;
      var phase;
      var x;
      // зона плавного наплыва ≈ ширина тени (часть облака уже в кадре, пока прозрачность растёт)
      var fadeZone = Math.max(120, shadeW * 0.85);
      // старт полностью за левым краем — overflow:hidden на слое режет хвост
      var xMin = -shadeW * 0.55;
      var xMax = imageWidth + shadeW * 0.55;

      wrap = document.createElement('div');
      wrap.className = 'gw-life-cloud';
      wrap.style.position = 'absolute';
      wrap.style.left = '0';
      wrap.style.top = '0';
      wrap.style.width = '0';
      wrap.style.height = '0';
      wrap.style.pointerEvents = 'none';
      wrap.style.willChange = 'transform, opacity';
      wrap.style.opacity = '0';

      // несколько мягких тёмных пятен = «облачная» тень, без белых спрайтов
      main = makeCloudShadeBlob(shadeW, shadeH, shade, false);
      side = makeCloudShadeBlob(shadeW * 0.62, shadeH * 0.7, shade * 0.75, true);
      soft = makeCloudShadeBlob(shadeW * 1.15, shadeH * 0.85, shade * 0.45, true);
      side.style.marginLeft = (-shadeW * 0.2) + 'px';
      side.style.marginTop = (-shadeH * 0.15) + 'px';
      soft.style.marginLeft = (-shadeW * 0.55) + 'px';
      soft.style.marginTop = (-shadeH * 0.35) + 'px';
      wrap.appendChild(soft);
      wrap.appendChild(side);
      wrap.appendChild(main);

      yCss = imageHeight * bandY;
      phase = clamp(toNum(params.phase, index * 0.41), 0, 1);
      // распределяем старты по пути, включая зону за левым краем
      x = xMin + phase * Math.max(1, xMax - xMin);

      if (layerEl) layerEl.appendChild(wrap);
      setAgentTransform(wrap, x, yCss, 0, { scale: 1, bodyFlat: 1, skewDeg: 0, sink: 0 }, 'cloud');
      counts.cloud += 1;

      return {
        el: wrap,
        shadeEl: null,
        speed: speed * MOTION_SPEED_SCALE * (0.85 + Math.random() * 0.3),
        yCss: yCss,
        x: x,
        xMin: xMin,
        xMax: xMax,
        fadeZone: fadeZone,
        shadeW: shadeW,
        wobbleAmp: 2 + Math.random() * 3,
        wobbleFreq: 0.0003 + Math.random() * 0.0002,
        breathPhase: Math.random() * Math.PI * 2
      };
    }

    /** Плавный наплыв: 0 за кадром → 1 в центре → 0 у противоположного края. */
    function cloudEnterExitOpacity(x, fadeZone, width) {
      var enter;
      var exit;
      var t;
      // smoothstep
      function smooth(u) {
        u = clamp(u, 0, 1);
        return u * u * (3 - 2 * u);
      }
      // центр тени входит слева: от 0 до fadeZone внутри кадра
      enter = smooth(x / fadeZone);
      // уходит справа
      exit = smooth((width - x) / fadeZone);
      t = Math.min(enter, exit);
      return clamp(t, 0, 1);
    }

    function updateClouds(ts, dt) {
      var i;
      var c;
      var y;
      var edge;
      var breath;
      var enabled = speciesEnabled('cloud');
      for (i = 0; i < clouds.length; i++) {
        c = clouds[i];
        if (!enabled) {
          c.el.style.visibility = 'hidden';
          continue;
        }
        c.el.style.visibility = 'visible';
        c.x += c.speed * (dt / 1000);
        // ушло за правый край → снова за левым (невидимо из‑за overflow + opacity 0)
        if (c.x > c.xMax) {
          c.x = c.xMin;
        }
        y = c.yCss + Math.sin(ts * c.wobbleFreq) * c.wobbleAmp;
        breath = 0.9 + 0.1 * Math.sin(ts * 0.0004 + c.breathPhase);
        edge = cloudEnterExitOpacity(c.x, c.fadeZone, imageWidth);
        c.el.style.opacity = (breath * edge).toFixed(3);
        setAgentTransform(c.el, c.x, y, 0, { scale: 1, bodyFlat: 1, skewDeg: 0, sink: 0 }, 'cloud');
      }
    }

    // ─── Wiring ────────────────────────────────────────────

    function initAgents() {
      var agents = lifeData.agents || [];
      var cfg = birdConfig();
      var ccfg = cloudConfig();
      var i;
      var a;
      var rt;
      var cloudAgents = [];
      var birdAgents = [];
      var birdAgent;

      trackAgents = [];
      birdSpawners = [];
      clouds = [];
      activeBirds = [];
      counts = { car: 0, person: 0, dog: 0, bird: 0, cloud: 0 };
      clearLayer();

      if (!flags.life || reduced || lifeData.enabled === false) {
        refreshLight();
        return;
      }

      for (i = 0; i < agents.length; i++) {
        a = agents[i];
        if (!a || a.enabled === false) continue;
        if (a.species === 'car' || a.species === 'person' || a.species === 'dog') {
          rt = makeTrackRuntime(a);
          if (rt) trackAgents.push(rt);
        } else if (a.species === 'bird') {
          birdAgents.push(a);
        } else if (a.species === 'cloud') {
          cloudAgents.push(a);
        }
      }

      syncSpeciesLimits();
      bootstrapTrackAgents();

      birdAgent = birdAgents.length
        ? birdAgents[0]
        : { species: 'bird', spriteKey: 'bird_a', speed: 70, periodMs: cfg.singlePeriodMs, params: {} };

      if (cfg.flockSize > 1) {
        birdSpawners.push({
          kind: 'flock',
          agent: birdAgent,
          periodMs: cfg.flockPeriodMs,
          nextAt: nowMs() + cfg.flockPeriodMs * (0.85 + Math.random() * 0.3)
        });
        // сразу часть стаи на старте
        if (speciesEnabled('bird')) {
          spawnFlock(nowMs(), birdAgent, Math.max(2, Math.round(cfg.flockSize * (0.65 + Math.random() * 0.2))));
        }
      }
      if (cfg.singles > 0) {
        birdSpawners.push({
          kind: 'single',
          agent: birdAgents.length > 1 ? birdAgents[1] : birdAgent,
          periodMs: cfg.singlePeriodMs,
          nextAt: nowMs() + 1200 + Math.random() * 2500
        });
        if (speciesEnabled('bird') && cfg.singles > 0) {
          for (i = 0; i < Math.max(1, Math.round(cfg.singles * 0.7)); i++) {
            spawnSingleBird(nowMs() + i * 180, birdAgents.length > 1 ? birdAgents[1] : birdAgent);
          }
        }
      }

      if (cloudAgents.length) {
        for (i = 0; i < cloudAgents.length && clouds.length < MAX_CLOUDS; i++) {
          clouds.push(makeCloud(cloudAgents[i], i, ccfg));
        }
      } else if (speciesEnabled('cloud') && ccfg.count > 0) {
        for (i = 0; i < ccfg.count && i < MAX_CLOUDS; i++) {
          clouds.push(makeCloud({
            species: 'cloud',
            spriteKey: i % 2 === 0 ? 'cloud_a' : 'cloud_b',
            speed: ccfg.speed * (0.9 + i * 0.12),
            periodMs: 60000,
            params: {
              cloud: { bandY: 0.2 + i * 0.12, shade: ccfg.shade },
              opacity: ccfg.opacity,
              scale: 1 + i * 0.18,
              phase: (i * 0.37) % 1
            }
          }, i, ccfg));
        }
      }

      refreshLight();
    }

    function loadAssetManifestAndReinit() {
      var Sprites = global.GenplanLifeSprites;
      var src = options.assetManifestSrc || defaultLifeAssetManifestSrc();
      var base;
      if (!Sprites || !Sprites.applyManifest || !global.fetch || !src) return;
      base = String(src).replace(/[^/?#]+(?:[?#].*)?$/, '');
      global.fetch(src, { cache: 'no-store' })
        .then(function (res) {
          if (!res || !res.ok) return null;
          return res.json();
        })
        .then(function (json) {
          var applied;
          if (!json) return;
          applied = Sprites.applyManifest(json, base);
          if (applied > 0 && !destroyed) {
            initAgents();
          }
        })
        .catch(function () { /* keep SVG fallback */ });
    }

    function tickBirdSpawners(ts) {
      var cfg = birdConfig();
      var dens = densityFactor(flags.lifeDensity);
      var i;
      var sp;
      var singlesLeft;
      if (!speciesEnabled('bird')) return;
      for (i = 0; i < birdSpawners.length; i++) {
        sp = birdSpawners[i];
        if (ts < sp.nextAt) continue;
        if (sp.kind === 'flock') {
          spawnFlock(ts, sp.agent, cfg.flockSize);
          sp.nextAt = ts + (cfg.flockPeriodMs / dens) * (0.8 + Math.random() * 0.4);
        } else {
          singlesLeft = cfg.singles;
          if (singlesLeft > 0) {
            spawnSingleBird(ts, sp.agent);
            // stagger the rest of the wave over the next seconds
            sp.pending = singlesLeft - 1;
            sp.pendingAt = ts + 900 + Math.random() * 1600;
          }
          sp.nextAt = ts + (cfg.singlePeriodMs / dens) * (0.75 + Math.random() * 0.5);
        }
      }
      for (i = 0; i < birdSpawners.length; i++) {
        sp = birdSpawners[i];
        if (sp.kind !== 'single' || !sp.pending) continue;
        if (ts >= sp.pendingAt) {
          spawnSingleBird(ts, sp.agent);
          sp.pending -= 1;
          sp.pendingAt = ts + 900 + Math.random() * 1600;
        }
      }
    }

    function frame(ts) {
      var dt;
      var i;
      var mode;
      if (destroyed) return;
      rafId = global.requestAnimationFrame(frame);
      if (paused || document.hidden) {
        lastTs = ts;
        return;
      }
      if (!lastTs) lastTs = ts;
      dt = Math.min(64, ts - lastTs);
      lastTs = ts;

      if (!flags.life || reduced) {
        refreshLight();
        return;
      }

      for (i = 0; i < trackAgents.length; i++) {
        updateTrackAgent(trackAgents[i], ts, dt);
      }
      tickBirdSpawners(ts);
      updateBirds(ts);
      updateClouds(ts, dt);

      mode = resolveLightMode();
      if (mode === 'pulse' && lightEl && !reduced) {
        pulseT += dt;
        lightEl.style.opacity = String(0.45 + 0.4 * (0.5 + 0.5 * Math.sin(pulseT / 10000 * Math.PI * 2)));
      }
    }

    function startLoop() {
      if (destroyed) return;
      if (!rafId) {
        lastTs = 0;
        rafId = global.requestAnimationFrame(frame);
      }
    }

    function stopLoop() {
      if (rafId) {
        global.cancelAnimationFrame(rafId);
        rafId = 0;
      }
    }

    function destroy() {
      var i;
      if (destroyed) return;
      destroyed = true;
      stopLoop();
      if (onVis) {
        document.removeEventListener('visibilitychange', onVis);
        onVis = null;
      }
      if (mq && onMq) {
        if (mq.removeEventListener) {
          try { mq.removeEventListener('change', onMq); } catch (e1) { /* ignore */ }
        } else if (mq.removeListener) {
          try { mq.removeListener(onMq); } catch (e2) { /* ignore */ }
        }
      }
      for (i = 0; i < trackAgents.length; i++) {
        removeNode(trackAgents[i].el);
        removeNode(trackAgents[i].shadowEl);
      }
      for (i = 0; i < activeBirds.length; i++) removeNode(activeBirds[i].el);
      for (i = 0; i < clouds.length; i++) {
        removeNode(clouds[i].el);
      }
      trackAgents = [];
      activeBirds = [];
      clouds = [];
      birdSpawners = [];
      clearLayer();
      if (lightEl) {
        lightEl.style.background = 'transparent';
        lightEl.style.opacity = '0';
      }
    }

    function setFlags(next) {
      flags = normalizeFlags(next || flags);
      initAgents();
      if (!paused) startLoop();
    }

    function pause() {
      paused = true;
    }

    function resume() {
      if (destroyed) return;
      paused = false;
      lastTs = 0;
      startLoop();
    }

    onVis = function () {
      if (document.hidden) lastTs = 0;
    };
    document.addEventListener('visibilitychange', onVis);

    if (global.matchMedia) {
      mq = global.matchMedia('(prefers-reduced-motion: reduce)');
      onMq = function () {
        reduced = prefersReducedMotion();
        initAgents();
      };
      if (mq.addEventListener) mq.addEventListener('change', onMq);
      else if (mq.addListener) mq.addListener(onMq);
    }

    initAgents();
    loadAssetManifestAndReinit();
    if (!paused) startLoop();

    return {
      destroy: destroy,
      setFlags: setFlags,
      pause: pause,
      resume: resume
    };
  }

  global.GenplanLife = {
    create: create
  };
})(typeof window !== 'undefined' ? window : this);
