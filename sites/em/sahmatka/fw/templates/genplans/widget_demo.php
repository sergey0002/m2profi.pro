<?php
$kvartal_id = (int) ($data['kvartal_id'] ?? 0);
$api_base = $data['api_base'] ?? '';
$script_src = $data['script_src'] ?? '';
if ($api_base === '' || $script_src === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $origin = $scheme . '://' . $host;
    if ($api_base === '') {
        $api_base = $origin . '/sahmatka/ajax_router.php?ctr=genplans';
    }
    if ($script_src === '') {
        $script_src = $origin . '/sahmatka/template/default/js/genplan_widget.js';
    }
}
?>
<style>
  html, body { margin: 0; padding: 0; width: 100%; background: #e8ecef; color: #1a1a1a; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
  .gw-page-head { max-width: 1200px; margin: 0 auto; padding: 24px 20px 12px; }
  .gw-page-head h1 { margin: 0; font-size: 22px; font-weight: 700; }
  .gw-widget-frame {
    width: 100%;
    background: #f0f2f4;
    border-top: 1px solid #c5ccd3;
    border-bottom: 1px solid #c5ccd3;
    box-shadow: 0 1px 0 rgba(0,0,0,0.06);
  }
  #genplan_demo_mount { width: 100%; margin: 0 auto; background: #f0f2f4; }
  .gw-embed {
    max-width: 960px;
    margin: 0 auto;
    padding: 32px 20px 48px;
  }
  .gw-embed h2 { font-size: 18px; font-weight: 600; margin: 0 0 16px; }
  .gw-embed h3 { font-size: 14px; font-weight: 600; margin: 20px 0 8px; color: #333; }
  .gw-embed h3:first-of-type { margin-top: 0; }
  .gw-embed pre {
    margin: 0; padding: 18px 20px; background: #1e1e1e; color: #d4d4d4;
    border-radius: 10px; overflow: auto;
    font: 13px/1.55 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    white-space: pre;
  }
  .gw-embed pre + h3 { margin-top: 28px; }
  .gw-embed .tok-tag { color: #569cd6; }
  .gw-embed .tok-attr { color: #9cdcfe; }
  .gw-embed .tok-str { color: #ce9178; }
  .gw-embed .tok-key { color: #9cdcfe; }
  .gw-embed .tok-num { color: #b5cea8; }
  .gw-embed .tok-comment { color: #6a9955; }
  .gw-embed .tok-punct { color: #d4d4d4; }
</style>

<header class="gw-page-head">
  <h1>Интерактивный план — виджет</h1>
</header>

<div class="gw-widget-frame">
  <div id="genplan_demo_mount"></div>
</div>

<section class="gw-embed">
  <h2>Код для вставки</h2>
  <h3>Простая вставка</h3>
  <pre id="gw_demo_snippet_simple"></pre>
  <h3>Все параметры (значения = дефолты, указывать не обязательно)</h3>
  <pre id="gw_demo_snippet_full"></pre>
</section>

<script src="<?= htmlspecialchars($script_src, ENT_QUOTES, 'UTF-8') ?>?v=2.4.6"></script>
<script>
(function () {
  var SCRIPT_SRC = <?= json_encode($script_src, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var API_BASE = <?= json_encode($api_base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var KVARTAL_ID = <?= (int) $kvartal_id ?>;
  var VER = (window.GenplanWidget && GenplanWidget.version) || '2.4.6';

  var qs = new URLSearchParams(window.location.search || '');
  var maxHParam = parseInt(qs.get('maxHeight') || '', 10);
  var widthParam = parseInt(qs.get('width') || '', 10);
  var offsetXParam = parseFloat(qs.get('offsetX') || '');
  var offsetYParam = parseFloat(qs.get('offsetY') || '');
  var offsetBottomParam = parseFloat(qs.get('offsetBottom') || '');

  // width/maxHeight — дефолты GenplanWidget: '100%' / 600 (можно переопределить ?width=&maxHeight=)
  var mountOpts = {
    el: '#genplan_demo_mount',
    kvartalId: KVARTAL_ID,
    apiBase: API_BASE,
    offsetBottom: (isFinite(offsetBottomParam)) ? offsetBottomParam : 100,
    minZoom: 1,
    maxZoom: 4
  };
  if (isFinite(widthParam) && widthParam > 0) mountOpts.width = widthParam;
  if (isFinite(maxHParam) && maxHParam > 0) mountOpts.maxHeight = maxHParam;
  if (isFinite(offsetXParam)) mountOpts.offsetX = offsetXParam;
  if (isFinite(offsetYParam)) mountOpts.offsetY = offsetYParam;

  GenplanWidget.mount(mountOpts);

  document.getElementById('gw_demo_snippet_simple').innerHTML = [
    '<span class="tok-comment">&lt;!-- Контейнер + скрипт + mount --&gt;</span>',
    '<span class="tok-tag">&lt;div</span> <span class="tok-attr">id</span>=<span class="tok-str">"genplan"</span><span class="tok-tag">&gt;&lt;/div&gt;</span>',
    '',
    '<span class="tok-tag">&lt;script</span> <span class="tok-attr">src</span>=<span class="tok-str">"' + SCRIPT_SRC + '?v=' + VER + '"</span><span class="tok-tag">&gt;&lt;/script&gt;</span>',
    '',
    '<span class="tok-tag">&lt;script&gt;</span>',
    '<span class="tok-punct">GenplanWidget.mount({</span>',
    '  <span class="tok-key">el</span><span class="tok-punct">:</span> <span class="tok-str">\'#genplan\'</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">kvartalId</span><span class="tok-punct">:</span> <span class="tok-num">' + KVARTAL_ID + '</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">width</span><span class="tok-punct">:</span> <span class="tok-str">\'100%\'</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">maxHeight</span><span class="tok-punct">:</span> <span class="tok-num">600</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">offsetBottom</span><span class="tok-punct">:</span> <span class="tok-num">100</span>',
    '<span class="tok-punct">});</span>',
    '<span class="tok-tag">&lt;/script&gt;</span>'
  ].join('\n');

  document.getElementById('gw_demo_snippet_full').innerHTML = [
    '<span class="tok-tag">&lt;script&gt;</span>',
    '<span class="tok-punct">GenplanWidget.mount({</span>',
    '  <span class="tok-key">el</span><span class="tok-punct">:</span> <span class="tok-str">\'#genplan\'</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">kvartalId</span><span class="tok-punct">:</span> <span class="tok-num">' + KVARTAL_ID + '</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">apiBase</span><span class="tok-punct">:</span> <span class="tok-str">\'' + API_BASE + '\'</span><span class="tok-punct">,</span>',
    '',
    '  <span class="tok-key">width</span><span class="tok-punct">:</span> <span class="tok-str">\'100%\'</span><span class="tok-punct">,</span>                       <span class="tok-comment">// px или \'100%\'</span>',
    '  <span class="tok-key">maxHeight</span><span class="tok-punct">:</span> <span class="tok-num">600</span><span class="tok-punct">,</span>                     <span class="tok-comment">// высота viewport</span>',
    '  <span class="tok-key">offsetBottom</span><span class="tok-punct">:</span> <span class="tok-num">100</span><span class="tok-punct">,</span>                  <span class="tok-comment">// стартовый сдвиг от низа, px</span>',
    '  <span class="tok-key">offsetX</span><span class="tok-punct">:</span> <span class="tok-num">0</span><span class="tok-punct">,</span>                          <span class="tok-comment">// 0…1 по горизонтали</span>',
    '  <span class="tok-key">offsetY</span><span class="tok-punct">:</span> <span class="tok-num">0</span><span class="tok-punct">,</span>                          <span class="tok-comment">// 0…1 (если нет offsetBottom)</span>',
    '  <span class="tok-key">minZoom</span><span class="tok-punct">:</span> <span class="tok-num">1</span><span class="tok-punct">,</span>                         <span class="tok-comment">// мин. зум (× contain)</span>',
    '  <span class="tok-key">maxZoom</span><span class="tok-punct">:</span> <span class="tok-num">4</span><span class="tok-punct">,</span>                         <span class="tok-comment">// макс. зум колесом</span>',
    '  <span class="tok-key">idleHighlight</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span>                <span class="tok-comment">// поочерёдная подсветка домов (откл: false)</span>',
    '',
    '  <span class="tok-key">exploreFullscreen</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">openLinksInNewTab</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">highlight</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">color</span><span class="tok-punct">:</span> <span class="tok-str">\'#5B8FB8\'</span><span class="tok-punct">,</span> <span class="tok-key">opacity</span><span class="tok-punct">:</span> <span class="tok-num">0.58</span><span class="tok-punct">,</span> <span class="tok-key">idleOpacity</span><span class="tok-punct">:</span> <span class="tok-num">0</span> <span class="tok-punct">}</span>',
    '<span class="tok-punct">});</span>',
    '<span class="tok-tag">&lt;/script&gt;</span>'
  ].join('\n');
})();
</script>
