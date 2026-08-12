/**
 * One-off: dump person_w1 8-frame walk cycle into a horizontal SVG sprite sheet.
 * Run: node export_person_w1_sheet.js
 */
var fs = require('fs');
var path = require('path');
var vm = require('vm');

var spritesPath = path.resolve(__dirname, '../../js/genplan_life_sprites.js');
var code = fs.readFileSync(spritesPath, 'utf8');
var sandbox = { window: {}, console: console };
sandbox.window = sandbox;
vm.runInNewContext(code, sandbox);

var sprites = sandbox.GenplanLifeSprites || sandbox.window.GenplanLifeSprites;
if (!sprites || !sprites.get) {
  throw new Error('GenplanLifeSprites not found');
}
var data = sprites.get('person_w1');
if (!data || !data.frames || !data.frames.length) {
  throw new Error('person_w1 has no frames');
}

var fw = data.w;
var fh = data.h;
var n = data.frames.length;
var parts = [
  '<?xml version="1.0" encoding="UTF-8"?>',
  '<svg xmlns="http://www.w3.org/2000/svg" width="' + (fw * n) + '" height="' + fh + '" viewBox="0 0 ' + (fw * n) + ' ' + fh + '">'
];
var i;
for (i = 0; i < n; i++) {
  parts.push(
    '<svg x="' + (i * fw) + '" y="0" width="' + fw + '" height="' + fh + '" viewBox="' + data.viewBox + '">' +
      data.frames[i] +
    '</svg>'
  );
}
parts.push('</svg>');

var out = path.join(__dirname, 'person_w1_walk.svg');
fs.writeFileSync(out, parts.join(''), 'utf8');
console.log('Wrote', out, n + ' frames', fw + 'x' + fh);