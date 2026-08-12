/**
 * Export all person_* walk cycles as horizontal SVG sprite sheets.
 * Run: node export_people_sheets.js
 */
var fs = require('fs');
var path = require('path');
var vm = require('vm');

var PEOPLE = [
  'person_m1',
  'person_m2',
  'person_w1',
  'person_w2',
  'person_w3',
  'person_k1',
  'person_k2'
];

var spritesPath = path.resolve(__dirname, '../../js/genplan_life_sprites.js');
var code = fs.readFileSync(spritesPath, 'utf8');
var sandbox = { window: {}, console: console };
sandbox.window = sandbox;
vm.runInNewContext(code, sandbox);

var sprites = sandbox.GenplanLifeSprites || sandbox.window.GenplanLifeSprites;
if (!sprites || !sprites.get) {
  throw new Error('GenplanLifeSprites not found');
}

function toSheet(key) {
  var data = sprites.get(key);
  var fw;
  var fh;
  var n;
  var parts;
  var i;
  var out;
  if (!data || !data.frames || !data.frames.length) {
    throw new Error(key + ' has no frames');
  }
  fw = data.w;
  fh = data.h;
  n = data.frames.length;
  parts = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<svg xmlns="http://www.w3.org/2000/svg" width="' + (fw * n) + '" height="' + fh + '" viewBox="0 0 ' + (fw * n) + ' ' + fh + '">'
  ];
  for (i = 0; i < n; i++) {
    parts.push(
      '<svg x="' + (i * fw) + '" y="0" width="' + fw + '" height="' + fh + '" viewBox="' + data.viewBox + '">' +
      data.frames[i] +
      '</svg>'
    );
  }
  parts.push('</svg>');
  out = path.join(__dirname, key + '_walk.svg');
  fs.writeFileSync(out, parts.join(''), 'utf8');
  console.log('Wrote', path.basename(out), n + ' frames', fw + 'x' + fh);
}

PEOPLE.forEach(toSheet);
