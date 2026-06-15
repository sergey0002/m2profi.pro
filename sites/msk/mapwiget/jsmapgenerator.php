<?

?>
<script>

// Подключение файла CSS
function loadCSS(url, callback) {
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = url;
    
    if (callback) {
        link.onload = function() {
            callback();
        };
    }
    document.getElementsByTagName("head")[0].appendChild(link);
}
 

// Подключение файла JS
function loadScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";

    if (script.readyState) {  // Для старых версий IE
        script.onreadystatechange = function () {
            if (script.readyState === "loaded" || script.readyState === "complete") {
                script.onreadystatechange = null;
                if (callback) callback();
            }
        };
    } else {  // Для всех других браузеров
        script.onload = function () {
            if (callback) callback();
        };
    }

    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);
}

</script>





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
  
