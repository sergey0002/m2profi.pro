<?php
$home_id = (int) ($data['home_id'] ?? 60);
$api_base = $data['api_base'] ?? '';
$script_src = $data['script_src'] ?? '';
if ($api_base === '' || $script_src === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $origin = $scheme . '://' . $host;
    if ($api_base === '') {
        $api_base = $origin . '/sahmatka/ajax_router.php?ctr=facades';
    }
    if ($script_src === '') {
        $script_src = $origin . '/sahmatka/template/default/js/facade_widget.js';
    }
}
?>
<style>
  html, body { margin: 0; padding: 0; width: 100%; background: #e8ecef; color: #1a1a1a; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
  .fw-page-head { max-width: 1200px; margin: 0 auto; padding: 24px 20px 12px; }
  .fw-page-head h1 { margin: 0 0 6px; font-size: 22px; font-weight: 700; }
  .fw-page-head p { margin: 0; color: #666; font-size: 14px; line-height: 1.45; }
  .fw-widget-frame {
    width: 100%;
    background: #f0f2f4;
    border-top: 1px solid #c5ccd3;
    border-bottom: 1px solid #c5ccd3;
    box-shadow: 0 1px 0 rgba(0,0,0,0.06);
  }
  #facade_demo_mount { width: 100%; margin: 0; background: #f0f2f4; }
  .fw-embed {
    max-width: 960px;
    margin: 0 auto;
    padding: 32px 20px 48px;
  }
  .fw-embed h2 { font-size: 18px; font-weight: 600; margin: 0 0 8px; }
  .fw-embed h3 { font-size: 14px; font-weight: 600; margin: 20px 0 8px; color: #333; }
  .fw-embed h3:first-of-type { margin-top: 0; }
  .fw-embed p { margin: 0 0 16px; color: #666; font-size: 14px; line-height: 1.45; }
  .fw-embed pre {
    margin: 0; padding: 18px 20px; background: #1e1e1e; color: #d4d4d4;
    border-radius: 10px; overflow: auto;
    font: 13px/1.55 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    white-space: pre;
  }
  .fw-embed pre + h3 { margin-top: 28px; }
  .fw-embed .tok-tag { color: #569cd6; }
  .fw-embed .tok-attr { color: #9cdcfe; }
  .fw-embed .tok-str { color: #ce9178; }
  .fw-embed .tok-key { color: #9cdcfe; }
  .fw-embed .tok-num { color: #b5cea8; }
  .fw-embed .tok-comment { color: #6a9955; }
  .fw-embed .tok-punct { color: #d4d4d4; }
</style>

<header class="fw-page-head">
  <h1>Фасад — интерактивный виджет</h1>
</header>

<div class="fw-widget-frame">
  <div id="facade_demo_mount"></div>
</div>

<section class="fw-embed">
  <h2>Код для вставки</h2>
  <p>Демо смонтировано минимальным вызовом — остальные настройки уже по умолчанию как на этой странице. На чужом сайте оставьте absolute URL скрипта (apiBase можно не указывать).</p>
  <h3>Простая вставка</h3>
  <pre id="fw_demo_snippet_simple"></pre>
  <h3>Все параметры (значения = дефолты, указывать не обязательно)</h3>
  <pre id="fw_demo_snippet_full"></pre>
</section>

<script src="<?= htmlspecialchars($script_src, ENT_QUOTES, 'UTF-8') ?>?v=1.2.24"></script>
<script>
(function () {
  var SCRIPT_SRC = <?= json_encode($script_src, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var API_BASE = <?= json_encode($api_base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var HOME_ID = <?= (int) $home_id ?>;
  var VER = '1.2.24';
  var MAX_HEIGHT_DEFAULT = 'Math.round(window.innerWidth * 2)';

  FacadeWidget.mount({
    el: '#facade_demo_mount',
    homeId: HOME_ID,
    apiBase: API_BASE
  });

  document.getElementById('fw_demo_snippet_simple').innerHTML = [
    '<span class="tok-comment">&lt;!-- Контейнер + скрипт + минимальный mount --&gt;</span>',
    '<span class="tok-tag">&lt;div</span> <span class="tok-attr">id</span>=<span class="tok-str">"facade"</span><span class="tok-tag">&gt;&lt;/div&gt;</span>',
    '',
    '<span class="tok-tag">&lt;script</span> <span class="tok-attr">src</span>=<span class="tok-str">"' + SCRIPT_SRC + '?v=' + VER + '"</span><span class="tok-tag">&gt;&lt;/script&gt;</span>',
    '',
    '<span class="tok-tag">&lt;script&gt;</span>',
    '<span class="tok-punct">FacadeWidget.mount({</span>',
    '  <span class="tok-key">el</span><span class="tok-punct">:</span> <span class="tok-str">\'#facade\'</span><span class="tok-punct">,</span>',
    '  <span class="tok-key">homeId</span><span class="tok-punct">:</span> <span class="tok-num">' + HOME_ID + '</span>',
    '  <span class="tok-comment">// apiBase опционален: возьмётся из src скрипта (на чужом сайте — absolute URL скрипта на Sigma)</span>',
    '<span class="tok-punct">});</span>',
    '<span class="tok-tag">&lt;/script&gt;</span>'
  ].join('\n');

  document.getElementById('fw_demo_snippet_full').innerHTML = [
    '<span class="tok-tag">&lt;script&gt;</span>',
    '<span class="tok-punct">FacadeWidget.mount({</span>',
    '  <span class="tok-key">el</span><span class="tok-punct">:</span> <span class="tok-str">\'#facade\'</span><span class="tok-punct">,</span>                    <span class="tok-comment">// куда встроить (селектор или HTMLElement)</span>',
    '  <span class="tok-key">homeId</span><span class="tok-punct">:</span> <span class="tok-num">' + HOME_ID + '</span><span class="tok-punct">,</span>                     <span class="tok-comment">// обязательно — homes.home_id</span>',
    '  <span class="tok-key">apiBase</span><span class="tok-punct">:</span> <span class="tok-str">\'' + API_BASE + '\'</span><span class="tok-punct">,</span>',
    '  <span class="tok-comment">// опционально; AJAX: widget_data → floor_plan_data → apartment_card_data → widget_booking_submit</span>',
    '',
    '  <span class="tok-key">width</span><span class="tok-punct">:</span> <span class="tok-str">\'100%\'</span><span class="tok-punct">,</span>                      <span class="tok-comment">// дефолт: на всю ширину контейнера (или число px)</span>',
    '  <span class="tok-key">maxHeight</span><span class="tok-punct">:</span> <span class="tok-num">' + MAX_HEIGHT_DEFAULT + '</span><span class="tok-punct">,</span>',
    '  <span class="tok-comment">// дефолт: innerWidth*2 — фасад не обрезается по ширине; для низкой колонки задайте 700…900</span>',
    '',
    '  <span class="tok-key">fadeMs</span><span class="tok-punct">:</span> <span class="tok-num">280</span><span class="tok-punct">,</span>                         <span class="tok-comment">// fade фасад↔план↔карточка, мс (0 — без)</span>',
    '  <span class="tok-key">urlState</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span>                      <span class="tok-comment">// hash #fw=секция.этаж.квартира; на чужом сайте можно false</span>',
    '  <span class="tok-key">scrollReveal</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span>                 <span class="tok-comment">// подсветка этажей при появлении в viewport</span>',
    '  <span class="tok-key">scrollRevealSpeed</span><span class="tok-punct">:</span> <span class="tok-num">1</span><span class="tok-punct">,</span>                <span class="tok-comment">// множитель скорости reveal</span>',
    '  <span class="tok-key">exploreFullscreen</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span>            <span class="tok-comment">// pan/zoom фасада (мобилка)</span>',
    '  <span class="tok-key">floorPlan</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">enabled</span><span class="tok-punct">:</span> <span class="tok-key">true</span> <span class="tok-punct">},</span>     <span class="tok-comment">// клик по этажу → план</span>',
    '  <span class="tok-key">floorPlanZoom</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">desktop</span><span class="tok-punct">:</span> <span class="tok-key">false</span><span class="tok-punct">,</span> <span class="tok-key">mobile</span><span class="tok-punct">:</span> <span class="tok-key">true</span> <span class="tok-punct">},</span>',
    '',
    '  <span class="tok-key">facadeHighlight</span><span class="tok-punct">:</span> <span class="tok-punct">{</span>',
    '    <span class="tok-key">color</span><span class="tok-punct">:</span> <span class="tok-str">\'#5B8FB8\'</span><span class="tok-punct">,</span>               <span class="tok-comment">// чуть синее accent</span>',
    '    <span class="tok-key">opacity</span><span class="tok-punct">:</span> <span class="tok-num">0.58</span><span class="tok-punct">,</span>                   <span class="tok-comment">// hover / active</span>',
    '    <span class="tok-key">idleOpacity</span><span class="tok-punct">:</span> <span class="tok-num">0.12</span><span class="tok-punct">,</span>               <span class="tok-comment">// в покое</span>',
    '    <span class="tok-key">revealOpacity</span><span class="tok-punct">:</span> <span class="tok-num">0.65</span>              <span class="tok-comment">// scrollReveal</span>',
    '  <span class="tok-punct">},</span>',
    '  <span class="tok-key">apartmentHighlight</span><span class="tok-punct">:</span> <span class="tok-punct">{</span>',
    '    <span class="tok-key">color</span><span class="tok-punct">:</span> <span class="tok-str">\'#76939D\'</span><span class="tok-punct">,</span>',
    '    <span class="tok-key">opacity</span><span class="tok-punct">:</span> <span class="tok-num">0.28</span><span class="tok-punct">,</span>                   <span class="tok-comment">// вкладка «На этаже»</span>',
    '    <span class="tok-key">hoverOpacity</span><span class="tok-punct">:</span> <span class="tok-num">0.45</span>               <span class="tok-comment">// hover + тултип</span>',
    '  <span class="tok-punct">},</span>',
    '  <span class="tok-key">breadcrumbs</span><span class="tok-punct">:</span> <span class="tok-punct">{</span>',
    '    <span class="tok-key">home</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">show</span><span class="tok-punct">:</span> <span class="tok-key">false</span><span class="tok-punct">,</span> <span class="tok-key">clickable</span><span class="tok-punct">:</span> <span class="tok-key">false</span> <span class="tok-punct">},</span>',
    '    <span class="tok-key">section</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">show</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span> <span class="tok-key">clickable</span><span class="tok-punct">:</span> <span class="tok-key">false</span> <span class="tok-punct">},</span>',
    '    <span class="tok-key">floor</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">show</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span> <span class="tok-key">clickable</span><span class="tok-punct">:</span> <span class="tok-key">true</span> <span class="tok-punct">},</span>',
    '    <span class="tok-key">apartment</span><span class="tok-punct">:</span> <span class="tok-punct">{</span> <span class="tok-key">show</span><span class="tok-punct">:</span> <span class="tok-key">true</span><span class="tok-punct">,</span> <span class="tok-key">clickable</span><span class="tok-punct">:</span> <span class="tok-key">false</span> <span class="tok-punct">}</span>',
    '  <span class="tok-punct">},</span>',
    '',
    '  <span class="tok-key">onFloorClick</span><span class="tok-punct">:</span> <span class="tok-key">function</span> <span class="tok-punct">(p)</span> <span class="tok-punct">{},</span>       <span class="tok-comment">// preventDefault → не открывать план</span>',
    '  <span class="tok-key">onApartmentClick</span><span class="tok-punct">:</span> <span class="tok-key">function</span> <span class="tok-punct">(p)</span> <span class="tok-punct">{},</span>   <span class="tok-comment">// preventDefault → не открывать карточку</span>',
    '  <span class="tok-key">onNavigate</span><span class="tok-punct">:</span> <span class="tok-key">function</span> <span class="tok-punct">(state)</span> <span class="tok-punct">{},</span>',
    '  <span class="tok-key">onBookingSuccess</span><span class="tok-punct">:</span> <span class="tok-key">function</span> <span class="tok-punct">(msg)</span> <span class="tok-punct">{}</span>',
    '<span class="tok-punct">});</span>',
    '<span class="tok-tag">&lt;/script&gt;</span>'
  ].join('\n');
})();
</script>
