<?php
error_reporting(0);
// Разрешаем встраивание через iframe на всех доменах
header("Content-Security-Policy: frame-ancestors *");

if(!isset($_GET['map_id']) || !$_GET['map_id'] )
{
	 
	 $_GET['map_id']=58;
	 
}

?>
<html>
<head>
<script src="https://msk.m2profi.pro/mapwiget/new/map.js"></script>
</head>
<body>

<div class="m2-map-ladplots-widget" data-mapw="0" data-map_id="<?=$_GET['map_id']?>" data-numbers="1" style="width: 100%; height:100vh; overflow:visible;"></div>

</body>
</html>