<?
error_reporting(0);
$map_id = $_GET['map_id'];
if(!$map_id){$map_id='3';}
?>

<html>
<head>

 
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<script
  src="https://code.jquery.com/jquery-3.7.1.js"
  integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
  crossorigin="anonymous"></script>
  
  <style>
		body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
         
    </style>
  
<!--Масштабирование карты -->
<script src="https://msk.m2profi.pro/maps/frontend/wheel-zoom.min.js" type="text/javascript"></script>

 <!-- Подсказки при наведении -->
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.min.css"/>
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/sahmatka/tooltip/tooltipster-sideTip-punk.min.css"/>
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/sahmatka/tooltip/style.css"/>
 <link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/mapwiget/m.css"/>
<script type="text/javascript" src="https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.js"></script>
  
<!-- Попап окна -->
<link rel="stylesheet" href="https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/magnific-popup.css">
<script src="https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/jquery.magnific-popup.js"></script>
 
<!-- интерактивность участков карты -->


 
<script type="text/javascript" src="https://msk.m2profi.pro/mapwiget/m-nozoom.js?x=<?=rand(0,1000);?>"></script> 
 	<!-- Стили нумерации участков -->
	<style>
 
	#myViewport{height:100vh;}
	
	
	
	 	<!-- Стили нумерации участков -->
	 
	 .label-text { 
		font-size: 9px;
		font-weight: bold;
		fill: #000; 
		transform: translate(0, 3px);
		font-family: 'Halvar', Arial, sans-serif;
	}
	
	
	</style>
	
	
	</style>
 

	<script>	 	
	var mapid="<?=$map_id?>"; // id карты 

	
	$( document ).ready(function() {
		
		 $(".scheme").load("https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jqsvg&map_id="+mapid,
		 function(response, status, xhr) {
			 if (status == "error") {
				 $("#content").html("An error occured: " + xhr.status + " " + xhr.statusText);
			 }
			 else
			 {
			  updatejsoon(mapid,1); // id карты, нумерация
			 }
		 }); 
	});
	
			


 

</script>
</head>
<body>
<?
if($_GET['p']) // версия для печати 
{
	$style='position: relative; width: 100%;';
	//$imgs= 'width:100%;';
	print '<h1>Карта</h1>';
	echo date("d.m.Y");
	print '<br/>';
}
else // Версия для мобильного
{
	$style = 'position: relative; max-width: 100vw;  max-height: 90vh;  overflow: scroll;';
}

?>

<script>

	document.write('<div id="map__'+mapid+'" class="noselect" style="">');
	document.write('<div id="myViewport" style="justify-content: left;"> ');
	document.write('<div class="myContent" id="mapcontent__'+mapid+'">');
	document.write('<div class="scheme" style="   width:100%; left:0; "></div> ');
	document.write('<img id="map" class="mapbg" style="<?=$imgs?>" src="https://msk.m2profi.pro/maps/'+mapid+'/map.png" alt="">');
	document.write('</div>');
	document.write('</div>');
 
	document.write('</div>');
	  
	 
	 <?
	 if($_GET['p'])
	 {
	 ?>
	 





   
  document.addEventListener("mapload", function(event) { // (1)
 
  // Функция для масштабирования элемента по произвольному селектору с сохранением пропорций l00% ширины окна
    function scaleElementToWindowWidth(selector) {
        var windowWidth = $(window).width();
        var elementWidth = $('#map').outerWidth();
        var scale = windowWidth / elementWidth;

        $(selector).css({
            'transform': 'scale(' + scale + ')', // Масштабирование по обеим осям
            'transform-origin': '0 0' // Установить начальную точку масштабирования в верхний левый угол
        });
		
		window.print();
		return true;
    }
	
	
	 
	
	
function scaleElementToA4Page(selector) {
        // Размеры A4 в пикселях при 96 DPI для вертикальной и горизонтальной ориентации
        var a4PortraitWidthPx = 210 / 25.4 * 96;  // ширина A4 в пикселях для вертикальной ориентации
        var a4PortraitHeightPx = 297 / 25.4 * 96; // высота A4 в пикселях для вертикальной ориентации

        var a4LandscapeWidthPx = 297 / 25.4 * 96;  // ширина A4 в пикселях для горизонтальной ориентации
        var a4LandscapeHeightPx = 210 / 25.4 * 96; // высота A4 в пикселях для горизонтальной ориентации

        // Учтем примерные отступы, например, по 20 мм с каждой стороны
        var marginPx = 20 / 25.4 * 96;
        var availablePortraitWidth = a4PortraitWidthPx - 2 * marginPx;
        var availablePortraitHeight = a4PortraitHeightPx - 2 * marginPx;

        var availableLandscapeWidth = a4LandscapeWidthPx - 2 * marginPx;
        var availableLandscapeHeight = a4LandscapeHeightPx - 2 * marginPx;

        // Размер элемента
        var elementWidth = $('#map').outerWidth();
        var elementHeight = $('#map').outerHeight();

        // Вычисление масштабирования для портретной ориентации
        var scalePortraitWidth = availablePortraitWidth / elementWidth;
        var scalePortraitHeight = availablePortraitHeight / elementHeight;
        var scalePortrait = Math.min(scalePortraitWidth, scalePortraitHeight);

        // Вычисление масштабирования для альбомной ориентации
        var scaleLandscapeWidth = availableLandscapeWidth / elementWidth;
        var scaleLandscapeHeight = availableLandscapeHeight / elementHeight;
        var scaleLandscape = Math.min(scaleLandscapeWidth, scaleLandscapeHeight);

        // Выбираем наибольший масштаб, чтобы максимально использовать доступную площадь
        var finalScale = Math.max(scalePortrait, scaleLandscape);

        $(selector).css({
            'transform': 'scale(' + finalScale + ')',
            'transform-origin': 'top left', // масштабирование от верхнего левого угла
            'position': 'relative', // сохраняем положение элемента
            'left': (marginPx) + 'px',
            'top': (marginPx) + 'px'
        });
		window.print();
    }






    // Вызов функции при загрузке страницы для произвольного элемента
    scaleElementToA4Page('#map__'+mapid) ; // вызов функций по очереди

    // Вызов функции при изменении размера окна
    $(window).resize(function() {
        //scaleElementToWindowWidth('#map__'+mapid); // Замените '#yourElementId' на ваш селектор
    });
 
	
    //
   });
 



  
  
  <?
  }
  ?>
  
	</script> 
	
 


</body>
</html>