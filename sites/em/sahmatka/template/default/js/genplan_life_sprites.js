/**
 * GenplanLifeSprites — inline SVG art for genplan life layer (EM task 12).
 * Top-down shapes with soft shading / ground shadow, tuned to sit on architectural renders.
 *
 * Sprite conventions:
 *   - Forward direction is +X (sprite "nose" points right); runtime rotates by heading.
 *   - Tintable parts carry class `gw-tint` (base), `gw-tint-dark` (shade), `gw-tint-light` (highlight).
 *   - Animatable parts carry class `gw-leg-a|gw-leg-b`, `gw-arm-a|gw-arm-b`, `gw-wing-a|gw-wing-b`
 *     and `data-pivot="x,y"` where a transform pivot is needed.
 *   - People: `frames[]` = 8-кадровый walk cycle; runtime меняет кадр по пройденной дистанции.
 *     Опционально `sheet: { src, frames, cols, frameW, frameH }` — PNG спрайтшит поверх SVG.
 *
 * API: GenplanLifeSprites.get(key) → { viewBox, svgInner, frames, sheet, w, h, baseColor } | null
 *      GenplanLifeSprites.keysFor(species) → string[]
 */
(function (global) {
  'use strict';

  var GLASS = '#2f3a45';
  var RUBBER = '#2c3238';
  var HEADLIGHT = '#f4efe2';
  var TAILLIGHT = '#b8564c';

  function shadow(cx, cy, rx, ry, op) {
    return '<ellipse class="gw-shadow" cx="' + cx + '" cy="' + cy + '" rx="' + rx + '" ry="' + ry +
      '" fill="#0b1016" opacity="' + (op || 0.18) + '"/>';
  }

  /**
   * Soft ground shadow faked with stacked ellipses (no SVG filters → no id collisions).
   * Light in the EM renders comes from upper right, so the shadow leans down-left.
   */
  function softShadow(cx, cy, rx, ry) {
    // почти под кузовом — без сильного смещения, иначе кажется что машина «летит»
    return shadow(cx - 0.35, cy + 0.55, rx, ry, 0.1) +
      shadow(cx - 0.2, cy + 0.35, rx * 0.88, ry * 0.86, 0.12) +
      shadow(cx, cy + 0.15, rx * 0.72, ry * 0.7, 0.14);
  }

  function wheel(x, y, w, h) {
    return '<rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + h +
      '" rx="' + (Math.min(w, h) / 2).toFixed(2) + '" fill="' + RUBBER + '" opacity="0.5"/>';
  }

  /** Disney / Barbie–Ken: светлая кукольная кожа, крупная голова, модельные пропорции. */
  var SKIN = '#f6d3b5';
  var SKIN_SH = '#e8b894';
  var SKIN_HL = '#ffe8d4';
  var SHOE = '#3a3230';
  var SHOE_SOLE = '#1e1a18';

  function personShadow(cx, cy, rx) {
    var r = rx || 2.8;
    return shadow(cx - 0.15, cy + 0.15, r, r * 0.38, 0.14) +
      shadow(cx, cy, r * 0.78, r * 0.28, 0.32) +
      shadow(cx, cy - 0.08, r * 0.42, r * 0.16, 0.42);
  }

  function disneyEyes(x, y, lashes) {
    return '<ellipse cx="' + (x - 0.58) + '" cy="' + (y - 0.12) + '" rx="0.42" ry="0.52" fill="#fff"/>' +
      '<ellipse cx="' + (x + 0.58) + '" cy="' + (y - 0.12) + '" rx="0.42" ry="0.52" fill="#fff"/>' +
      '<circle cx="' + (x - 0.52) + '" cy="' + (y - 0.06) + '" r="0.24" fill="#3b2416"/>' +
      '<circle cx="' + (x + 0.64) + '" cy="' + (y - 0.06) + '" r="0.24" fill="#3b2416"/>' +
      '<circle cx="' + (x - 0.44) + '" cy="' + (y - 0.16) + '" r="0.09" fill="#fff"/>' +
      '<circle cx="' + (x + 0.72) + '" cy="' + (y - 0.16) + '" r="0.09" fill="#fff"/>' +
      (lashes
        ? '<path d="M' + (x - 0.95) + ' ' + (y - 0.55) + 'c0.18-0.28 0.42-0.42 0.7-0.48" stroke="#2a1c14" stroke-width="0.18" fill="none" stroke-linecap="round"/>' +
          '<path d="M' + (x + 0.22) + ' ' + (y - 0.55) + 'c0.18-0.28 0.42-0.42 0.7-0.48" stroke="#2a1c14" stroke-width="0.18" fill="none" stroke-linecap="round"/>'
        : '');
  }

  function barbieFace(x, y) {
    return '<ellipse cx="' + x + '" cy="' + y + '" rx="1.95" ry="2.25" fill="' + SKIN + '"/>' +
      '<ellipse cx="' + (x - 0.35) + '" cy="' + (y - 0.15) + '" rx="1.15" ry="1.45" fill="' + SKIN_HL + '" opacity="0.45"/>' +
      disneyEyes(x, y, true) +
      '<ellipse cx="' + (x - 0.85) + '" cy="' + (y + 0.72) + '" rx="0.42" ry="0.22" fill="#f4a0b0" opacity="0.55"/>' +
      '<ellipse cx="' + (x + 0.85) + '" cy="' + (y + 0.72) + '" rx="0.42" ry="0.22" fill="#f4a0b0" opacity="0.55"/>' +
      '<path d="M' + (x - 0.38) + ' ' + (y + 1.05) + 'c0.22 0.32 0.55 0.32 0.76 0" stroke="#d07080" stroke-width="0.22" fill="none" stroke-linecap="round"/>';
  }

  function kenFace(x, y) {
    return '<ellipse cx="' + x + '" cy="' + y + '" rx="1.75" ry="2.05" fill="' + SKIN + '"/>' +
      '<ellipse cx="' + (x - 0.28) + '" cy="' + (y - 0.1) + '" rx="0.95" ry="1.2" fill="' + SKIN_HL + '" opacity="0.38"/>' +
      disneyEyes(x, y - 0.05, false) +
      '<path d="M' + (x - 0.28) + ' ' + (y + 0.95) + 'c0.16 0.18 0.4 0.18 0.56 0" stroke="#c88870" stroke-width="0.2" fill="none" stroke-linecap="round"/>';
  }

  function kidFace(x, y, lashes) {
    return '<circle cx="' + x + '" cy="' + y + '" r="1.55" fill="' + SKIN + '"/>' +
      '<ellipse cx="' + (x - 0.22) + '" cy="' + (y - 0.12) + '" rx="0.75" ry="0.9" fill="' + SKIN_HL + '" opacity="0.4"/>' +
      disneyEyes(x, y + 0.05, !!lashes) +
      '<ellipse cx="' + (x - 0.7) + '" cy="' + (y + 0.55) + '" rx="0.32" ry="0.16" fill="#f4a0b0" opacity="0.5"/>' +
      '<ellipse cx="' + (x + 0.7) + '" cy="' + (y + 0.55) + '" rx="0.32" ry="0.16" fill="#f4a0b0" opacity="0.5"/>' +
      '<path d="M' + (x - 0.28) + ' ' + (y + 0.82) + 'c0.16 0.22 0.4 0.22 0.56 0" stroke="#d07080" stroke-width="0.18" fill="none" stroke-linecap="round"/>';
  }

  function n2(v) {
    return (Math.round(v * 100) / 100).toFixed(2);
  }

  function joint(x, y, deg, len) {
    var a = (deg * Math.PI) / 180;
    return { x: x + Math.sin(a) * len, y: y + Math.cos(a) * len };
  }

  function limbPath(a, b, w, color) {
    return '<path d="M' + n2(a.x) + ' ' + n2(a.y) + ' L' + n2(b.x) + ' ' + n2(b.y) +
      '" stroke="' + color + '" stroke-width="' + w + '" fill="none" stroke-linecap="round"/>';
  }

  /** 8 кадров walk cycle: 4 фазы × 2 шага. Углы от вертикали, + = вправо. */
  var WALK_HALF = [
    { bob: 0.18, lean: 2.4, rH: 26, rK: 8, lH: -22, lK: 40, rA: -20, lA: 22 },
    { bob: 0.52, lean: 3.2, rH: 12, rK: 24, lH: -8, lK: 56, rA: -12, lA: 14 },
    { bob: 0.06, lean: 1.1, rH: 2, rK: 10, lH: 10, lK: 76, rA: -4, lA: 6 },
    { bob: -0.3, lean: 2.1, rH: -12, rK: 16, lH: 24, lK: 18, rA: 12, lA: -14 }
  ];

  function swapWalkPose(p) {
    return { bob: p.bob, lean: -p.lean, rH: p.lH, rK: p.lK, lH: p.rH, lK: p.rK, rA: p.lA, lA: p.rA };
  }

  function walkPoses(runMul) {
    var m = runMul > 1 ? runMul : 1;
    var i;
    var p;
    var out = [];
    function amp(src) {
      return {
        bob: src.bob * (0.7 + m * 0.3),
        lean: src.lean * m,
        rH: src.rH * m,
        rK: src.rK * (0.85 + m * 0.15),
        lH: src.lH * m,
        lK: src.lK * (0.85 + m * 0.15),
        rA: src.rA * m,
        lA: src.lA * m
      };
    }
    for (i = 0; i < 4; i++) out.push(amp(WALK_HALF[i]));
    for (i = 0; i < 4; i++) {
      p = amp(WALK_HALF[i]);
      out.push(swapWalkPose(p));
    }
    return out;
  }

  function hairSvg(kind, cx, hy, sway) {
    if (kind === 'ken-dark') {
      return '<ellipse cx="' + n2(cx) + '" cy="' + n2(hy - 1.55) + '" rx="2.15" ry="1.55" fill="#2a241c"/>' +
        '<path d="M' + n2(cx - 1.95) + ' ' + n2(hy - 1.1) + 'c0.55-0.85 1.25-1.15 1.95-1.15s1.4 0.3 1.95 1.15" stroke="#2a241c" stroke-width="0.7" fill="none" stroke-linecap="round"/>';
    }
    if (kind === 'ken-brown') {
      return '<ellipse cx="' + n2(cx) + '" cy="' + n2(hy - 1.5) + '" rx="2.05" ry="1.45" fill="#6a5040"/>' +
        '<path d="M' + n2(cx - 1.85) + ' ' + n2(hy - 1.05) + 'c0.5-0.75 1.15-1.05 1.85-1.05s1.35 0.3 1.85 1.05" stroke="#6a5040" stroke-width="0.65" fill="none" stroke-linecap="round"/>';
    }
    if (kind === 'wave') {
      return '<ellipse cx="' + n2(cx + sway) + '" cy="' + n2(hy - 1.05) + '" rx="3.15" ry="2.55" fill="#f2c86a"/>' +
        '<ellipse cx="' + n2(cx - 2.85 + sway) + '" cy="' + n2(hy + 1.25) + '" rx="1.15" ry="2.05" fill="#f2c86a"/>' +
        '<ellipse cx="' + n2(cx + 2.85 + sway) + '" cy="' + n2(hy + 1.25) + '" rx="1.15" ry="2.05" fill="#e8b85a"/>' +
        '<path d="M' + n2(cx - 2.95 + sway) + ' ' + n2(hy + 3.25) + 'c0.85 1.55 1.85 2.15 2.95 2.15s2.1-0.6 2.95-2.15" fill="#f2c86a"/>';
    }
    if (kind === 'pony') {
      return '<ellipse cx="' + n2(cx) + '" cy="' + n2(hy - 2.05) + '" rx="1.55" ry="1.25" fill="#f4d078"/>' +
        '<path d="M' + n2(cx - 0.85) + ' ' + n2(hy - 1.4) + 'c-0.15-1.85 0.35-2.65 0.85-2.65s1 0.8 0.85 2.65" fill="#f4d078"/>' +
        '<ellipse cx="' + n2(cx + sway * 1.2) + '" cy="' + n2(hy - 3.7) + '" rx="0.85" ry="0.65" fill="#f4d078"/>' +
        '<ellipse cx="' + n2(cx - 2.85 + sway) + '" cy="' + n2(hy + 0.25) + '" rx="1.05" ry="1.55" fill="#f4d078"/>' +
        '<ellipse cx="' + n2(cx + 2.85 + sway) + '" cy="' + n2(hy + 0.25) + '" rx="1.05" ry="1.55" fill="#e8c060"/>';
    }
    if (kind === 'long') {
      return '<ellipse cx="' + n2(cx + sway) + '" cy="' + n2(hy - 0.75) + '" rx="3.35" ry="2.85" fill="#4a3024"/>' +
        '<path d="M' + n2(cx - 3.35 + sway) + ' ' + n2(hy + 1.55) + 'c1.05 2.55 2.15 3.55 3.35 3.55s2.3-1 3.35-3.55c-0.85 0.55-2.05 0.85-3.35 0.85s-2.5-0.3-3.35-0.85z" fill="#4a3024"/>' +
        '<ellipse cx="' + n2(cx - 3.15 + sway) + '" cy="' + n2(hy + 2.95) + '" rx="0.95" ry="1.85" fill="#4a3024"/>' +
        '<ellipse cx="' + n2(cx + 3.15 + sway) + '" cy="' + n2(hy + 2.95) + '" rx="0.95" ry="1.85" fill="#3a241c"/>';
    }
    if (kind === 'pigtails') {
      return '<ellipse cx="' + n2(cx - 2.35 + sway) + '" cy="' + n2(hy - 0.4) + '" rx="0.85" ry="1.15" fill="#f2c86a"/>' +
        '<ellipse cx="' + n2(cx + 2.35 + sway) + '" cy="' + n2(hy - 0.4) + '" rx="0.85" ry="1.15" fill="#e8b85a"/>' +
        '<ellipse cx="' + n2(cx) + '" cy="' + n2(hy - 1.2) + '" rx="1.85" ry="1.25" fill="#f2c86a"/>';
    }
    return '<ellipse cx="' + n2(cx) + '" cy="' + n2(hy - 1.0) + '" rx="1.85" ry="1.35" fill="#3a3028"/>';
  }

  function buildPersonFrame(spec, pose) {
    var cx = spec.cx;
    var bob = pose.bob;
    var hipY = spec.hipY + bob;
    var shY = spec.shoulderY + bob;
    var headY = spec.headY + bob;
    var hipL = { x: cx - spec.hipW, y: hipY };
    var hipR = { x: cx + spec.hipW, y: hipY };
    var shL = { x: cx - spec.shoulderW + pose.lean * 0.08, y: shY };
    var shR = { x: cx + spec.shoulderW + pose.lean * 0.08, y: shY };
    var kneeL = joint(hipL.x, hipL.y, pose.lH, spec.thigh);
    var kneeR = joint(hipR.x, hipR.y, pose.rH, spec.thigh);
    var footL = joint(kneeL.x, kneeL.y, pose.lH + pose.lK, spec.shin);
    var footR = joint(kneeR.x, kneeR.y, pose.rH + pose.rK, spec.shin);
    var handL = joint(shL.x, shL.y, pose.lA, spec.armLen);
    var handR = joint(shR.x, shR.y, pose.rA, spec.armLen);
    var ground = spec.h - 0.55;
    var lowest = Math.max(footL.y, footR.y) + (spec.heel ? 1.05 : 0.72);
    var dy = ground - lowest;
    function lift(p) { return { x: p.x, y: p.y + dy }; }
    hipL = lift(hipL); hipR = lift(hipR);
    kneeL = lift(kneeL); kneeR = lift(kneeR);
    footL = lift(footL); footR = lift(footR);
    handL = lift(handL); handR = lift(handR);
    shL = lift(shL); shR = lift(shR);
    headY += dy;
    hipY += dy;
    var sway = pose.lean * 0.12 + (pose.rH - pose.lH) * 0.02;
    var skirtSwing = (pose.lH + pose.rH) * 0.04;
    var svg = personShadow(cx, ground, spec.kind === 'k' ? 2.15 : 3.1);
    var backIsLeft = pose.lH < pose.rH;

    function drawLeg(hip, knee, foot, w) {
      return limbPath(hip, knee, w, SKIN) +
        limbPath(knee, foot, w * 0.92, SKIN) +
        '<ellipse cx="' + n2(foot.x) + '" cy="' + n2(foot.y + 0.15) + '" rx="' + n2(w * 0.42) + '" ry="' + n2(w * 0.55) + '" fill="' + SKIN + '"/>' +
        '<ellipse cx="' + n2(foot.x + (spec.heel ? 0.15 : 0)) + '" cy="' + n2(foot.y + 0.62) + '" rx="' + n2(spec.heel ? 1.05 : 0.95) + '" ry="0.38" fill="' + SHOE + '"/>' +
        (spec.heel
          ? '<path d="M' + n2(foot.x + 0.2) + ' ' + n2(foot.y + 0.7) + ' L' + n2(foot.x + 0.08) + ' ' + n2(foot.y + 1.35) + '" stroke="' + SHOE + '" stroke-width="0.28" stroke-linecap="round"/>'
          : '<ellipse cx="' + n2(foot.x) + '" cy="' + n2(foot.y + 0.82) + '" rx="0.82" ry="0.16" fill="' + SHOE_SOLE + '" opacity="0.7"/>');
    }

    function drawArm(sh, hand, w) {
      return limbPath(sh, hand, w, SKIN) +
        '<circle cx="' + n2(hand.x) + '" cy="' + n2(hand.y) + '" r="' + n2(w * 0.42) + '" fill="' + SKIN_SH + '"/>';
    }

    if (backIsLeft) {
      svg += drawLeg(hipL, kneeL, footL, spec.legW);
      svg += drawArm(shL, handL, spec.armW);
    } else {
      svg += drawLeg(hipR, kneeR, footR, spec.legW);
      svg += drawArm(shR, handR, spec.armW);
    }

    if (spec.clothes === 'dress' || spec.clothes === 'sundress') {
      svg += '<path class="gw-tint" d="M' + n2(cx - 1.05) + ' ' + n2(headY + 2.4) +
        ' C' + n2(cx - 1.55) + ' ' + n2(hipY - 0.4) + ' ' + n2(cx - 1.85 + skirtSwing) + ' ' + n2(hipY + 2.2) +
        ' ' + n2(cx - 2.55 + skirtSwing) + ' ' + n2(hipY + 4.8) +
        ' L' + n2(cx + 2.55 + skirtSwing) + ' ' + n2(hipY + 4.8) +
        ' C' + n2(cx + 1.85 + skirtSwing) + ' ' + n2(hipY + 2.2) + ' ' + n2(cx + 1.55) + ' ' + n2(hipY - 0.4) +
        ' ' + n2(cx + 1.05) + ' ' + n2(headY + 2.4) + ' Z" fill="' + spec.baseColor + '"/>' +
        '<path class="gw-tint-light" d="M' + n2(cx - 0.7) + ' ' + n2(headY + 2.55) + 'h' + n2(1.4) +
        'c0.35 0 0.55 0.2 0.55 0.7v1.9c0 0.45-0.2 0.7-0.55 0.7h-1.4c-0.35 0-0.55-0.25-0.55-0.7v-1.9c0-0.5 0.2-0.7 0.55-0.7z" fill="#fff" opacity="0.28"/>';
    } else {
      svg += '<path class="gw-tint" d="M' + n2(cx - spec.torsoW) + ' ' + n2(headY + 2.35) +
        ' C' + n2(cx - spec.torsoW - 0.15) + ' ' + n2(shY + 0.2) + ' ' + n2(cx - spec.torsoW) + ' ' + n2(hipY - 0.2) +
        ' ' + n2(cx - spec.hipW - 0.15) + ' ' + n2(hipY + 0.35) +
        ' L' + n2(cx + spec.hipW + 0.15) + ' ' + n2(hipY + 0.35) +
        ' C' + n2(cx + spec.torsoW) + ' ' + n2(hipY - 0.2) + ' ' + n2(cx + spec.torsoW + 0.15) + ' ' + n2(shY + 0.2) +
        ' ' + n2(cx + spec.torsoW) + ' ' + n2(headY + 2.35) + ' Z" fill="' + spec.baseColor + '"/>';
      if (spec.clothes === 'shirt') {
        svg += '<path class="gw-tint-light" d="M' + n2(cx - spec.torsoW + 0.15) + ' ' + n2(headY + 2.45) +
          'h' + n2(spec.torsoW * 2 - 0.3) + 'l-0.12 1.7H' + n2(cx - spec.torsoW + 0.27) + 'z" fill="#f4f0e8" opacity="0.7"/>';
      } else {
        svg += '<path class="gw-tint-light" d="M' + n2(cx - 0.85) + ' ' + n2(headY + 2.55) +
          'h1.7l-0.1 1.35H' + n2(cx - 0.75) + 'z" fill="#fff" opacity="0.28"/>';
      }
      if (spec.clothes === 'shorts' || spec.clothes === 'crop') {
        svg += '<path class="gw-tint-dark" d="M' + n2(hipL.x - 0.15) + ' ' + n2(hipY + 0.15) +
          'h' + n2(spec.hipW * 2 + 0.3) + 'l-0.12 1.55H' + n2(hipL.x + 0.05) + 'z" fill="#2a3038" opacity="0.85"/>';
      }
      if (spec.clothes === 'pants' || spec.clothes === 'shirt') {
        svg += '<path d="M' + n2(hipL.x) + ' ' + n2(hipY + 0.2) + ' L' + n2(kneeL.x) + ' ' + n2(kneeL.y + 0.4) +
          '" stroke="#c8bca8" stroke-width="' + n2(spec.legW + 0.35) + '" fill="none" stroke-linecap="round"/>' +
          '<path d="M' + n2(hipR.x) + ' ' + n2(hipY + 0.2) + ' L' + n2(kneeR.x) + ' ' + n2(kneeR.y + 0.4) +
          '" stroke="#c8bca8" stroke-width="' + n2(spec.legW + 0.35) + '" fill="none" stroke-linecap="round"/>';
      }
    }

    if (backIsLeft) {
      svg += drawLeg(hipR, kneeR, footR, spec.legW);
      svg += drawArm(shR, handR, spec.armW);
    } else {
      svg += drawLeg(hipL, kneeL, footL, spec.legW);
      svg += drawArm(shL, handL, spec.armW);
    }

    svg += '<ellipse cx="' + n2(cx) + '" cy="' + n2(headY + 1.55) + '" rx="0.52" ry="0.7" fill="' + SKIN_SH + '"/>';
    svg += hairSvg(spec.hair, cx, headY, sway);
    if (spec.kind === 'w') svg += barbieFace(cx, headY);
    else if (spec.kind === 'k') svg += kidFace(cx, headY, spec.hair === 'pigtails');
    else svg += kenFace(cx, headY);
    return svg;
  }

  function makePersonSprite(spec) {
    var poses = walkPoses(spec.run || 1);
    var frames = [];
    var i;
    for (i = 0; i < poses.length; i++) frames.push(buildPersonFrame(spec, poses[i]));
    return {
      viewBox: '0 0 ' + spec.w + ' ' + spec.h,
      w: spec.w,
      h: spec.h,
      baseColor: spec.baseColor,
      frames: frames,
      svgInner: frames[0]
    };
  }

  /** @type {Object.<string, {viewBox:string, svgInner:string, w:number, h:number, baseColor:string}>} */
  var SPRITES = {
    // ─── Cars (top-down, nose right) ───────────────────────
    // Sedan: light body, brighter roof plate, thin glass bands — reads like the render at 20–40 px.
    car_a: {
      viewBox: '0 0 30 14',
      w: 30,
      h: 14,
      baseColor: '#c8ced5',
      svgInner:
        softShadow(14.4, 8.2, 13, 5.2) +
        wheel(6.4, 1.7, 4.4, 1.7) + wheel(19.8, 1.7, 4.4, 1.7) +
        wheel(6.4, 10.6, 4.4, 1.7) + wheel(19.8, 10.6, 4.4, 1.7) +
        '<path class="gw-tint" d="M7.6 2.5h11.9c3.3 0 5.8.7 7.5 2.1.8.7 1.2 1.5 1.2 2.4s-.4 1.7-1.2 2.4c-1.7 1.4-4.2 2.1-7.5 2.1H7.6c-2.4 0-4.1-.3-5.1-1-.6-.5-.9-1.7-.9-3.5s.3-3 .9-3.5c1-.7 2.7-1.1 5.1-1.1z" fill="#c8ced5"/>' +
        '<path class="gw-tint-light" d="M10.2 3.9h6.9c1 0 1.7.2 2.1.6.4.4.6 1.2.6 2.5s-.2 2.1-.6 2.5c-.4.4-1.1.6-2.1.6h-6.9c-.9 0-1.5-.2-1.9-.6-.4-.4-.6-1.2-.6-2.5s.2-2.1.6-2.5c.4-.4 1-.6 1.9-.6z" fill="#e1e5e9" opacity="0.9"/>' +
        '<path d="M19.8 4.2c1.5.4 2.6 1 3.4 1.9.3.3.3 1.5 0 1.8-.8.9-1.9 1.5-3.4 1.9-.3-1.9-.3-3.7 0-5.6z" fill="' + GLASS + '" opacity="0.5"/>' +
        '<path d="M9.6 4.5c-1.1.3-2 .8-2.6 1.5-.3.3-.3 1.2 0 1.5.6.7 1.5 1.2 2.6 1.5.2-1.5.2-3 0-4.5z" fill="' + GLASS + '" opacity="0.34"/>' +
        '<rect x="10.6" y="3.6" width="7.6" height="0.8" rx="0.4" fill="' + GLASS + '" opacity="0.26"/>' +
        '<rect x="10.6" y="9.6" width="7.6" height="0.8" rx="0.4" fill="' + GLASS + '" opacity="0.26"/>' +
        '<rect x="10.8" y="4.5" width="6.6" height="1.1" rx="0.55" fill="#ffffff" opacity="0.4"/>' +
        '<rect x="5.6" y="2.8" width="19.4" height="0.7" rx="0.35" fill="#ffffff" opacity="0.22"/>' +
        '<rect x="26.6" y="4.5" width="1.3" height="1.2" rx="0.5" fill="' + HEADLIGHT + '" opacity="0.6"/>' +
        '<rect x="26.6" y="8.3" width="1.3" height="1.2" rx="0.5" fill="' + HEADLIGHT + '" opacity="0.6"/>' +
        '<rect class="gw-brake" x="2.3" y="4.6" width="1.2" height="1.1" rx="0.45" fill="' + TAILLIGHT + '" opacity="0.5"/>' +
        '<rect class="gw-brake" x="2.3" y="8.3" width="1.2" height="1.1" rx="0.45" fill="' + TAILLIGHT + '" opacity="0.5"/>'
    },
    // Crossover / van: longer, squarer roof.
    car_b: {
      viewBox: '0 0 33 15',
      w: 33,
      h: 15,
      baseColor: '#d3d8dd',
      svgInner:
        softShadow(16, 8.8, 14.6, 5.6) +
        wheel(6.8, 2, 4.8, 1.8) + wheel(21.8, 2, 4.8, 1.8) +
        wheel(6.8, 11.2, 4.8, 1.8) + wheel(21.8, 11.2, 4.8, 1.8) +
        '<path class="gw-tint" d="M8 2.7h13.8c3.6 0 6.3.8 8.1 2.3.8.7 1.2 1.5 1.2 2.5s-.4 1.8-1.2 2.5c-1.8 1.5-4.5 2.3-8.1 2.3H8c-2.7 0-4.6-.4-5.7-1.1-.7-.5-1-1.8-1-3.7s.3-3.2 1-3.7c1.1-.7 3-1.1 5.7-1.1z" fill="#d3d8dd"/>' +
        '<path class="gw-tint-light" d="M9.8 4h10.4c1.1 0 1.9.2 2.3.7.4.4.6 1.3.6 2.8s-.2 2.4-.6 2.8c-.4.5-1.2.7-2.3.7H9.8c-1 0-1.7-.2-2.1-.7-.4-.4-.6-1.3-.6-2.8s.2-2.4.6-2.8c.4-.5 1.1-.7 2.1-.7z" fill="#eaedf0" opacity="0.9"/>' +
        '<path d="M22.8 4.4c1.7.4 3 1.1 3.9 2.1.3.4.3 1.6 0 2-.9 1-2.2 1.7-3.9 2.1-.3-2.1-.3-4.1 0-6.2z" fill="' + GLASS + '" opacity="0.48"/>' +
        '<path d="M9 4.6c-1.2.3-2.1.9-2.7 1.7-.3.3-.3 1.4 0 1.7.6.8 1.5 1.4 2.7 1.7.2-1.7.2-3.4 0-5.1z" fill="' + GLASS + '" opacity="0.32"/>' +
        '<rect x="10.2" y="3.8" width="11.4" height="0.9" rx="0.45" fill="' + GLASS + '" opacity="0.25"/>' +
        '<rect x="10.2" y="10.3" width="11.4" height="0.9" rx="0.45" fill="' + GLASS + '" opacity="0.25"/>' +
        '<rect x="10.4" y="4.8" width="10.4" height="1.2" rx="0.6" fill="#ffffff" opacity="0.36"/>' +
        '<rect x="6" y="3.1" width="21.6" height="0.7" rx="0.35" fill="#ffffff" opacity="0.2"/>' +
        '<rect x="29.4" y="4.9" width="1.4" height="1.3" rx="0.5" fill="' + HEADLIGHT + '" opacity="0.58"/>' +
        '<rect x="29.4" y="8.9" width="1.4" height="1.3" rx="0.5" fill="' + HEADLIGHT + '" opacity="0.58"/>' +
        '<rect class="gw-brake" x="2.5" y="5" width="1.3" height="1.2" rx="0.45" fill="' + TAILLIGHT + '" opacity="0.48"/>' +
        '<rect class="gw-brake" x="2.5" y="8.9" width="1.3" height="1.2" rx="0.45" fill="' + TAILLIGHT + '" opacity="0.48"/>'
    },
    // Hatchback: short, near-white.
    car_c: {
      viewBox: '0 0 26 13',
      w: 26,
      h: 13,
      baseColor: '#e2e6ea',
      svgInner:
        softShadow(12.8, 7.6, 11.2, 4.7) +
        wheel(5.4, 1.5, 4, 1.6) + wheel(17, 1.5, 4, 1.6) +
        wheel(5.4, 9.9, 4, 1.6) + wheel(17, 9.9, 4, 1.6) +
        '<path class="gw-tint" d="M7 2.3h9.6c3 0 5.3.7 6.8 2 .7.6 1.1 1.4 1.1 2.2s-.4 1.6-1.1 2.2c-1.5 1.3-3.8 2-6.8 2H7c-2.2 0-3.7-.3-4.6-.9-.6-.5-.8-1.6-.8-3.3s.2-2.8.8-3.3c.9-.6 2.4-.9 4.6-.9z" fill="#e2e6ea"/>' +
        '<path class="gw-tint-light" d="M9.2 3.6h5.6c.9 0 1.5.2 1.9.6.3.4.5 1.1.5 2.3s-.2 1.9-.5 2.3c-.4.4-1 .6-1.9.6H9.2c-.8 0-1.4-.2-1.7-.6-.4-.4-.5-1.1-.5-2.3s.1-1.9.5-2.3c.3-.4.9-.6 1.7-.6z" fill="#f3f5f7" opacity="0.9"/>' +
        '<path d="M17 3.9c1.3.3 2.3.9 3 1.7.3.3.3 1.3 0 1.6-.7.8-1.7 1.4-3 1.7-.2-1.7-.2-3.3 0-5z" fill="' + GLASS + '" opacity="0.46"/>' +
        '<path d="M8.6 4.2c-1 .3-1.7.7-2.2 1.3-.3.3-.3 1.1 0 1.4.5.6 1.2 1 2.2 1.3.2-1.3.2-2.7 0-4z" fill="' + GLASS + '" opacity="0.3"/>' +
        '<rect x="9.4" y="3.4" width="6.2" height="0.75" rx="0.37" fill="' + GLASS + '" opacity="0.24"/>' +
        '<rect x="9.4" y="8.8" width="6.2" height="0.75" rx="0.37" fill="' + GLASS + '" opacity="0.24"/>' +
        '<rect x="9.6" y="4.2" width="5.6" height="1" rx="0.5" fill="#ffffff" opacity="0.42"/>' +
        '<rect x="5" y="2.6" width="16.4" height="0.65" rx="0.32" fill="#ffffff" opacity="0.24"/>' +
        '<rect x="22.6" y="4.2" width="1.2" height="1.1" rx="0.45" fill="' + HEADLIGHT + '" opacity="0.55"/>' +
        '<rect x="22.6" y="7.7" width="1.2" height="1.1" rx="0.45" fill="' + HEADLIGHT + '" opacity="0.55"/>' +
        '<rect class="gw-brake" x="2.2" y="4.3" width="1.1" height="1" rx="0.4" fill="' + TAILLIGHT + '" opacity="0.45"/>' +
        '<rect class="gw-brake" x="2.2" y="7.7" width="1.1" height="1" rx="0.4" fill="' + TAILLIGHT + '" opacity="0.45"/>'
    },

    // ─── People: Disney / Barbie–Ken + 8-кадровый walk cycle ───
    person_m1: makePersonSprite({
      kind: 'm', w: 16, h: 26, cx: 8, hipY: 14.2, shoulderY: 8.6, headY: 4.55,
      hipW: 1.25, shoulderW: 2.15, torsoW: 1.85, thigh: 5.1, shin: 5.0, armLen: 4.4,
      legW: 1.08, armW: 0.95, heel: false, clothes: 'shorts', hair: 'ken-dark', baseColor: '#4a6888'
    }),
    person_m2: makePersonSprite({
      kind: 'm', w: 16, h: 26, cx: 8, hipY: 14.3, shoulderY: 8.5, headY: 4.4,
      hipW: 1.2, shoulderW: 2.2, torsoW: 1.9, thigh: 5.15, shin: 5.05, armLen: 4.5,
      legW: 1.05, armW: 0.95, heel: false, clothes: 'shirt', hair: 'ken-brown', baseColor: '#6a8898'
    }),
    person_w1: makePersonSprite({
      kind: 'w', w: 18, h: 28, cx: 9, hipY: 15.1, shoulderY: 8.7, headY: 4.55,
      hipW: 1.05, shoulderW: 1.85, torsoW: 1.35, thigh: 5.6, shin: 5.4, armLen: 4.5,
      legW: 0.82, armW: 0.72, heel: true, clothes: 'dress', hair: 'wave', baseColor: '#c06068'
    }),
    person_w2: makePersonSprite({
      kind: 'w', w: 18, h: 28, cx: 9, hipY: 14.6, shoulderY: 8.55, headY: 4.45,
      hipW: 1.0, shoulderW: 1.8, torsoW: 1.3, thigh: 5.5, shin: 5.3, armLen: 4.4,
      legW: 0.8, armW: 0.72, heel: false, clothes: 'crop', hair: 'pony', baseColor: '#7a98b8'
    }),
    person_w3: makePersonSprite({
      kind: 'w', w: 18, h: 28, cx: 9, hipY: 15.4, shoulderY: 8.65, headY: 4.5,
      hipW: 1.08, shoulderW: 1.82, torsoW: 1.32, thigh: 5.7, shin: 5.5, armLen: 4.55,
      legW: 0.8, armW: 0.7, heel: true, clothes: 'sundress', hair: 'long', baseColor: '#d898a8'
    }),
    person_k1: makePersonSprite({
      kind: 'k', w: 13, h: 20, cx: 6.5, hipY: 10.2, shoulderY: 6.4, headY: 3.55,
      hipW: 0.95, shoulderW: 1.45, torsoW: 1.15, thigh: 3.5, shin: 3.4, armLen: 3.1,
      legW: 0.85, armW: 0.72, heel: false, clothes: 'shorts', hair: 'boy', baseColor: '#e04848', run: 1.18
    }),
    person_k2: makePersonSprite({
      kind: 'k', w: 13, h: 20, cx: 6.5, hipY: 10.1, shoulderY: 6.3, headY: 3.45,
      hipW: 0.92, shoulderW: 1.4, torsoW: 1.12, thigh: 3.45, shin: 3.35, armLen: 3.05,
      legW: 0.82, armW: 0.7, heel: false, clothes: 'dress', hair: 'pigtails', baseColor: '#6ec050', run: 1.18
    }),

    // ─── Dogs ──────────────────────────────────────────────
    dog_a: {
      viewBox: '0 0 16 10',
      w: 16,
      h: 10,
      baseColor: '#7a6a58',
      svgInner:
        shadow(8, 5.6, 5.4, 2.6, 0.15) +
        '<ellipse class="gw-leg-a" data-pivot="6,3.8" cx="6.2" cy="3.7" rx="0.8" ry="0.55" fill="#5a4e40" opacity="0.85"/>' +
        '<ellipse class="gw-leg-b" data-pivot="6,7.2" cx="6.2" cy="7.3" rx="0.8" ry="0.55" fill="#5a4e40" opacity="0.85"/>' +
        '<ellipse class="gw-tint" cx="8" cy="5.5" rx="4.2" ry="2.1" fill="#7a6a58"/>' +
        '<ellipse class="gw-tint-light" cx="7.4" cy="5.4" rx="2.6" ry="1.2" fill="#9a8a76" opacity="0.5"/>' +
        '<ellipse class="gw-tint-dark" cx="12.2" cy="4.7" rx="2" ry="1.5" fill="#6b5c4c"/>' +
        '<ellipse class="gw-tint-dark" cx="13.8" cy="3.9" rx="0.65" ry="0.85" fill="#6b5c4c"/>' +
        '<path class="gw-tail" data-pivot="3.9,5.2" d="M4 5.2c-.9-.3-1.9-.2-2.6.5" stroke="#6e5f4e" stroke-width="0.95" fill="none" opacity="0.85" stroke-linecap="round"/>'
    },
    dog_b: {
      viewBox: '0 0 15 9',
      w: 15,
      h: 9,
      baseColor: '#8a7a68',
      svgInner:
        shadow(7.4, 5.2, 4.9, 2.3, 0.14) +
        '<ellipse class="gw-leg-a" data-pivot="5.6,3.6" cx="5.8" cy="3.5" rx="0.75" ry="0.5" fill="#6a5c4c" opacity="0.85"/>' +
        '<ellipse class="gw-leg-b" data-pivot="5.6,6.6" cx="5.8" cy="6.7" rx="0.75" ry="0.5" fill="#6a5c4c" opacity="0.85"/>' +
        '<ellipse class="gw-tint" cx="7.4" cy="5.1" rx="3.9" ry="1.95" fill="#8a7a68"/>' +
        '<ellipse class="gw-tint-light" cx="6.8" cy="5" rx="2.4" ry="1.1" fill="#a3937f" opacity="0.5"/>' +
        '<circle class="gw-tint-dark" cx="11.5" cy="4.2" r="1.7" fill="#7a6b5a"/>' +
        '<path class="gw-tail" data-pivot="3.5,4.9" d="M3.6 4.9l-2 .8" stroke="#7a6b5a" stroke-width="0.95" fill="none" opacity="0.8" stroke-linecap="round"/>'
    },

    // ─── Birds (flap via wing scaleY around pivot) ─────────
    bird_a: {
      viewBox: '0 0 16 10',
      w: 16,
      h: 10,
      baseColor: '#4a5260',
      svgInner:
        '<path class="gw-wing-a" data-pivot="8.4,5" d="M8.4 4.5C7 2.7 5 1.4 2.5 1.1c.9 1.9 2.9 3.2 5.4 3.9z" fill="#525c6c" opacity="0.92"/>' +
        '<path class="gw-wing-b" data-pivot="8.4,5" d="M8.4 5.5C7 7.3 5 8.6 2.5 8.9c.9-1.9 2.9-3.2 5.4-3.9z" fill="#4a5464" opacity="0.92"/>' +
        '<polygon points="6.6,5 4.4,3.9 4.4,6.1" fill="#454e5c" opacity="0.9"/>' +
        '<ellipse cx="8.9" cy="5" rx="2.5" ry="1.05" fill="#4a5260" opacity="0.95"/>' +
        '<circle cx="11.4" cy="4.75" r="0.85" fill="#434b58"/>' +
        '<polygon points="12.2,4.75 13.5,5.05 12.2,5.35" fill="#c08a3e" opacity="0.9"/>'
    },
    bird_b: {
      viewBox: '0 0 13 8',
      w: 13,
      h: 8,
      baseColor: '#586274',
      svgInner:
        '<path class="gw-wing-a" data-pivot="6.9,4" d="M6.9 3.6C5.8 2.2 4.2 1.2 2.2 1c.7 1.5 2.3 2.6 4.4 3.1z" fill="#5f697b" opacity="0.9"/>' +
        '<path class="gw-wing-b" data-pivot="6.9,4" d="M6.9 4.4C5.8 5.8 4.2 6.8 2.2 7c.7-1.5 2.3-2.6 4.4-3.1z" fill="#565f70" opacity="0.9"/>' +
        '<ellipse cx="7.3" cy="4" rx="2" ry="0.85" fill="#586274" opacity="0.95"/>' +
        '<circle cx="9.3" cy="3.8" r="0.7" fill="#4e5666"/>'
    },

    // ─── Clouds (мягкие, крупные — читаются как лёгкая дымка) ─
    cloud_a: {
      viewBox: '0 0 120 48',
      w: 120,
      h: 48,
      baseColor: '#e8eef4',
      svgInner:
        '<ellipse cx="34" cy="30" rx="28" ry="14" fill="#d5dde8" opacity="0.38"/>' +
        '<ellipse cx="62" cy="24" rx="34" ry="18" fill="#eaf0f6" opacity="0.48"/>' +
        '<ellipse cx="90" cy="30" rx="24" ry="13" fill="#d8e0ea" opacity="0.4"/>' +
        '<ellipse cx="54" cy="18" rx="22" ry="12" fill="#f5f8fb" opacity="0.42"/>' +
        '<ellipse cx="76" cy="22" rx="18" ry="10" fill="#ffffff" opacity="0.28"/>'
    },
    cloud_b: {
      viewBox: '0 0 140 44',
      w: 140,
      h: 44,
      baseColor: '#e2e8ef',
      svgInner:
        '<ellipse cx="32" cy="28" rx="24" ry="12" fill="#cfd8e4" opacity="0.34"/>' +
        '<ellipse cx="68" cy="22" rx="38" ry="16" fill="#e8eef5" opacity="0.44"/>' +
        '<ellipse cx="104" cy="28" rx="26" ry="13" fill="#d4dce6" opacity="0.36"/>' +
        '<ellipse cx="58" cy="16" rx="20" ry="10" fill="#f4f7fa" opacity="0.38"/>' +
        '<ellipse cx="86" cy="20" rx="16" ry="9" fill="#ffffff" opacity="0.26"/>'
    }
  };

  var SPECIES_KEYS = {
    car: ['car_a', 'car_b', 'car_c'],
    person: ['person_m1', 'person_m2', 'person_w1', 'person_w2', 'person_w3', 'person_k1', 'person_k2'],
    dog: ['dog_a', 'dog_b'],
    bird: ['bird_a', 'bird_b'],
    cloud: ['cloud_a', 'cloud_b']
  };

  function get(key) {
    if (!key || !SPRITES[key]) return null;
    var s = SPRITES[key];
    return {
      viewBox: s.viewBox,
      svgInner: s.svgInner || (s.frames && s.frames[0]) || '',
      frames: s.frames || null,
      sheet: s.sheet || null,
      w: s.w,
      h: s.h,
      baseColor: s.baseColor || null
    };
  }

  function keysFor(species) {
    var list = SPECIES_KEYS[species];
    if (!list) return [];
    return list.slice();
  }

  function resolveSheetSrc(src, baseUrl) {
    if (!src) return '';
    if (/^https?:\/\//i.test(src) || src.charAt(0) === '/') return src;
    return String(baseUrl || '') + src;
  }

  function normalizeSheet(cfg, baseUrl) {
    var frames;
    var cols;
    var src;
    var srcLeft;
    if (!cfg || typeof cfg !== 'object') return null;
    src = resolveSheetSrc(typeof cfg.src === 'string' ? cfg.src.trim() : '', baseUrl);
    if (!src) return null;
    frames = Math.max(1, parseInt(cfg.frames, 10) || 1);
    cols = Math.max(1, parseInt(cfg.cols, 10) || frames);
    srcLeft = '';
    if (typeof cfg.srcLeft === 'string' && cfg.srcLeft.trim()) {
      srcLeft = resolveSheetSrc(cfg.srcLeft.trim(), baseUrl);
    }
    return {
      src: src,
      srcLeft: srcLeft || null,
      frames: frames,
      cols: cols
    };
  }

  /**
   * Apply external manifest, e.g. raster sprite sheets for people.
   * Expected: { people: { person_w1: { sheet: { src, frames, cols } }, ... } }
   */
  function applyManifest(manifest, baseUrl) {
    var people;
    var key;
    var row;
    var applied = 0;
    if (!manifest || typeof manifest !== 'object') return 0;
    people = manifest.people;
    if (!people || typeof people !== 'object') return 0;
    for (key in people) {
      if (!Object.prototype.hasOwnProperty.call(people, key)) continue;
      if (!SPRITES[key]) continue;
      row = people[key];
      if (!row || typeof row !== 'object') continue;
      SPRITES[key].sheet = normalizeSheet(row.sheet, baseUrl);
      if (SPRITES[key].sheet) applied += 1;
    }
    return applied;
  }

  global.GenplanLifeSprites = {
    get: get,
    keysFor: keysFor,
    applyManifest: applyManifest
  };
})(typeof window !== 'undefined' ? window : this);
