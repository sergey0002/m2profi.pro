<?php
$home_id = (int) ($data['home_id'] ?? 60);
$api_base = $data['api_base'] ?? '/sahmatka/ajax_router.php?ctr=facades';
$script_src = $data['script_src'] ?? '/sahmatka/template/default/js/facade_widget.js';
?>
<style>
  html, body { margin: 0; padding: 0; width: 100%; background: #fff; }
  #facade_demo_mount { width: 100%; }
</style>
<div id="facade_demo_mount"></div>
<script src="<?= htmlspecialchars($script_src) ?>"></script>
<script>
FacadeWidget.mount({
  el: '#facade_demo_mount',
  homeId: <?= $home_id ?>,
  width: '100%',
  apiBase: <?= json_encode($api_base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
});
</script>
