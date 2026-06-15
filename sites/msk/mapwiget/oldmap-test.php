<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Печать документа</title>
    <!-- Подключение jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <button id="printButton">Печать документа</button>

    <script>
      function printDocument(url) {
            // Создаем div контейнер для iframe
            var div = $('<div></div>').css({
                overflow: 'hidden',
                width: '1000px',
                height: '1000px'
            });

            // Создаем iframe
            var iframe = $('<iframe></iframe>').attr('src', url).css({
                display: 'block'
            });

            // Добавляем iframe в div и div на страницу
            div.append(iframe).appendTo('body');

            // Функция для печати документа после его загрузки
            iframe.on('load', function() {
                // Применяем стили к iframe
                iframe.css({
                    display: 'block',
                    position: 'fixed',
                    width: '1px',
                    height: '1px',
                    border: 'none'
                });

               // iframe[0].contentWindow.focus(); // Фокусируемся на содержимом iframe
                //iframe[0].contentWindow.print(); // Запускаем печать

                // Удаляем div и iframe через 10 секунд
                setTimeout(function() {div.remove();}, 10000);
            });
        }
 
		
		
		
		$('#printButton').click(function() {
			// URL документа для печати
			var url = 'https://msk.m2profi.pro/mapwiget/mapprint.php?p=1';
			printDocument(url);
        });
			
			
    </script>
	
	 
	
	
	
	
	
	
 
	
<div style="padding:0; border:solid 2px #000">
	
	
	
	<!--Масштабирование карты -->
<script src="https://msk.m2profi.pro/maps/frontend/wheel-zoom.min.js" type="text/javascript"></script>

 <!-- Подсказки при наведении -->
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.min.css"/>
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/sahmatka/tooltip/tooltipster-sideTip-punk.min.css"/>
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/sahmatka/tooltip/style.css"/>
<script type="text/javascript" src="https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.js"></script>
  
<!-- Попап окна -->
<link rel="stylesheet" href="https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/magnific-popup.css">
<script src="https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/jquery.magnific-popup.js"></script>

  
<link rel="stylesheet" type="text/css" href="https://msk.m2profi.pro/mapwiget/m.css"/>
<!-- интерактивность участков карты -->
<script type="text/javascript" src="https://msk.m2profi.pro/mapwiget/m.js"></script> 
 	<!-- Стили нумерации участков -->
	<style>
	 .label-text {
		font-size: 9px;
		font-weight: bold;
		fill: #FFF; 
		transform: translate(0, 3px);
		font-family: 'Halvar', Arial, sans-serif;
	}
	</style>
 

	<script>	 	
	var mapid="58"; // id карты 

	
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
	
	

  
	document.write('<div id="map__'+mapid+'" class="noselect" style="position:relative">');
	document.write('<div style="position: absolute; left: 0; top: 30px; z-index: 3;">');
	document.write('<div>');
	document.write('<button class="zmb" data-zoom-down  style="width: auto;">-</button> ');
	document.write('<button class="zmb" data-zoom-up  style="width: auto;" >+</button>');
	document.write('<button class="zmb" id="printButton" style="width: auto;" >  <img src="https://msk.m2profi.pro/mapwiget/print.png" style="width:30px;" />  </button>');
	document.write('</div>');
	document.write('</div>');
	document.write('<div class="ratio ratio-4x3 " style="overflow:hidden">');
	document.write('<div id="myViewport" class="myViewport"> ');
	document.write('<div class="myContent" id="mapcontent__'+mapid+'">');
	document.write('<div class="scheme" style="position:absolute;  width:100%; left:0; "></div> ');
	document.write('<img id="map" class="mapbg" src="https://gl.m2profi.pro/maps/'+mapid+'/map.png" alt="">');
	document.write('</div>');
	document.write('</div>');
	document.write('</div>');
	document.write('</div>');

	</script> 
	
	
	</div>
	
	
</body>
</html>
