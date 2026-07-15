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
    background: #fff;
    border-top: 1px solid #d1d5d8;
    border-bottom: 1px solid #d1d5d8;
    box-shadow: 0 1px 0 rgba(0,0,0,0.04);
  }
  #facade_demo_mount { width: 100%; margin: 0; }
  .fw-embed {
    max-width: 960px;
    margin: 0 auto;
    padding: 32px 20px 48px;
  }
  .fw-embed h2 { font-size: 18px; font-weight: 600; margin: 0 0 8px; }
  .fw-embed p { margin: 0 0 16px; color: #666; font-size: 14px; line-height: 1.45; }
  .fw-embed pre {
    margin: 0; padding: 18px 20px; background: #1e1e1e; color: #d4d4d4;
    border-radius: 10px; overflow: auto;
    font: 13px/1.55 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    white-space: pre;
  }
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
  <p>Demo · home_id=<?= (int) $home_id ?> · клик по этажу → план → карточка квартиры</p>
</header>

<div class="fw-widget-frame">
  <div id="facade_demo_mount"></div>
</div>

<section class="fw-embed">
  <h2>Код для вставки</h2>
  <p>Скопируйте блок на свой сайт. Параметры и комментарии — в примере.</p>
  <pre id="fw_demo_snippet"></pre>
</section>

<script src="<?= htmlspecialchars($script_src, ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
(function () {
  var SCRIPT_SRC = <?= json_encode($script_src, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var API_BASE = <?= json_encode($api_base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var HOME_ID = <?= (int) $home_id ?>;

  FacadeWidget.mount({
    el: '#facade_demo_mount',
    homeId: HOME_ID,
    width: '100%',
    maxHeight: Math.round(window.innerWidth * 2),
    apiBase: API_BASE
  });

  document.getElementById('fw_demo_snippet').innerHTML = [
    '<span class="tok-comment">&lt;!-- Контейнер виджета --&gt;</span>',
    '<span class="tok-tag">&lt;div</span> <span class="tok-attr">id</span>=<span class="tok-str">"facade"</span><span class="tok-tag">&gt;&lt;/div&gt;</span>',
    '',
    '<span class="tok-comment">&lt;!-- Полный URL скрипта (обязательно absolute для чужого домена) --&gt;</span>',
    '<span class="tok-tag">&lt;script</span> <span class="tok-attr">src</span>=<span class="tok-str">"' + SCRIPT_SRC + '"</span><span class="tok-tag">&gt;&lt;/script&gt;</span>',
    '',
    '<span class="tok-tag">&lt;script&gt;</span>',
    '<span class="tok-punct">FacadeWidget.mount({</span>',
    '  <span class="tok-key">el</span><span class="tok-punct">:</span> <span class="tok-str">\'#facade\'</span><span class="tok-punct">,</span>  <span class="tok-comment">// CSS-селектор или DOM-элемент</span>',
    '  <span class="tok-key">homeId</span><span class="tok-punct">:</span> <span class="tok-num">' + HOME_ID + '</span><span class="tok-punct">,</span>          <span class="tok-comment">// ID дома в шахматке (homes.home_id)</span>',
    '  <span class="tok-key">width</span><span class="tok-punct">:</span> <span class="tok-str">\'100%\'</span><span class="tok-punct">,</span>         <span class="tok-comment">// \'100%\' или число (px)</span>',
    '  <span class="tok-key">apiBase</span><span class="tok-punct">:</span> <span class="tok-str">\'' + API_BASE + '\'</span><span class="tok-punct">,</span>',
    '  <span class="tok-comment">// полный URL API: widget_data / floor_plan_data / apartment_card_data</span>',
    '',
    '  <span class="tok-comment">// Опционально:</span>',
    '  <span class="tok-comment">// maxHeight: 900,                 // верхний предел высоты фасада (px)</span>',
    '  <span class="tok-comment">// scrollReveal: true,             // анимация подсветки этажей</span>',
    '  <span class="tok-comment">// floorPlan: { enabled: true },   // false — не открывать план по клику</span>',
    '  <span class="tok-comment">// onFloorClick: function (p) {},  // return { preventDefault: true }</span>',
    '  <span class="tok-comment">// onApartmentClick: function (p) {}, // return { preventDefault: true }</span>',
    '  <span class="tok-comment">// onBookingSuccess: function (msg) {}</span>',
    '<span class="tok-punct">});</span>',
    '<span class="tok-tag">&lt;/script&gt;</span>'
  ].join('\n');
})();
</script>
