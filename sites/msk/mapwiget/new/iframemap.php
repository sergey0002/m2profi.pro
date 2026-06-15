<html>
<head>
<body>

<div id="mapwiget"></div>
<script src="https://msk.m2profi.pro/mapwiget/new/iframemodal.js"></script>
<script>
var mapWidget = new MapWidget();
mapWidget.load('https://msk.m2profi.pro/mapwiget/mapiframe.php?map_id=58'); // Загрузка предварителеная iframe
var element = document.getElementById("mapwiget");
mapWidget.showInPlace(element,{ width: '100%', height: '500px' }, true); // Последний аргумент Оверлей
</script>





</body>
</html>