<?
session_start();
 header("X-Frame-Options: ALLOW");
header('Access-Control-Allow-Origin: *'); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0", false);
header("Cache-Control: max-age=0", false);
header("Pragma: no-cache");
 
 
 
 
// Те же контроллеры и экшены без шаблона 
include('config.php');
$_GET=$_REQUEST;

if( $_SESSION['sh_login'] || 1==1 )
{
?>
<html lang="ru">
	<head>
		<meta charset="utf-8">
		 
		<meta name="robots" content="noindex, nofollow" />
		<meta name="googlebot" content="noindex, nofollow" />
		<meta name="yandex" content="none" />
		<title>M2 Profi</title>
		<meta name="description" content="">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="/sahmatka/template/default/images/favicon/favicon.png">
		<link rel="shortcut icon" href="/sahmatka/template/default/images/favicon/favicon.ico" type="image/x-icon">
		<link rel="apple-touch-icon" href="/sahmatka/template/default/images/favicon/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/sahmatka/template/default/images/favicon/apple-touch-icon-72x72.png">
		<meta property="og:image" content="/sahmatka/template/default/images/home-og.jpg">
		<link rel="stylesheet" href="/sahmatka/template/default/libs/air-datepicker/css/datepicker.min.css">
		<link rel="stylesheet" href="/sahmatka/template/default/libs/formstyler/jquery.formstyler.css">
		<style>
		.jq-checkbox{margin-right:0;}
		</style>
		<link rel="stylesheet" href="/sahmatka/template/default/libs/aos/aos.css">
		<link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick.css">
		<link rel="stylesheet" href="/sahmatka/template/default/css/style.css">
		<link rel="stylesheet" href="/sahmatka/template/default/css/media.css">
		<script src="/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js"></script>
		
		
		
 

<script src="/sahmatka/template/default/libs/formstyler/jquery.formstyler.min.js"></script>


<script src="/sahmatka/template/default/js/myfw_iframe.js"></script>


<link href="https://em-nsk.ru/fonts/montserrat/montserrat.css" rel="stylesheet" />
	<link href="https://em-nsk.ru/fonts/exo2/exotwo.css" rel="stylesheet">


	<link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick.css">
	<link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick-theme.css">
	
	<script src="/sahmatka/template/default/libs/slick/slick.min.js"></script>
 
 
	<link rel="stylesheet" href="/sahmatka/template/default/css/admin.css">
	<link rel="stylesheet" href="/sahmatka/template/default/css/iframe.css">
	
<link rel="stylesheet" href="/wiget_rent.css">
 
 
 
 <!-- Magnific Popup core CSS file -->
<link rel="stylesheet" href="/sahmatka/template/default/libs/mpop/magnific-popup.css">
<!-- Magnific Popup core JS file -->
<script src="/sahmatka/template/default/libs/mpop/jquery.magnific-popup.js"></script>





<style>

 .mfp-iframe-holder .mfp-content {
    width: 80vw;
    max-width: 100%;
    height: 70vh;
    max-height: 95vh;
	height:90%;
	overflow:hidden;
	
}
.mfp-iframe-scaler iframe {background:#FFF;}
 
 
 
 
 /**
 * Fade-zoom animation for first dialog
 **/
/* start state */
.my-mfp-zoom-in .zoom-anim-dialog {
 opacity: 0;
 -webkit-transition: all 0.2s ease-in-out; 
 -moz-transition: all 0.2s ease-in-out; 
 -o-transition: all 0.2s ease-in-out; 
 transition: all 0.2s ease-in-out; 
 -webkit-transform: scale(0.8); 
 -moz-transform: scale(0.8); 
 -ms-transform: scale(0.8); 
 -o-transform: scale(0.8); 
 transform: scale(0.8); 
}
/* animate in */
.my-mfp-zoom-in.mfp-ready .zoom-anim-dialog {
 opacity: 1;
 -webkit-transform: scale(1); 
 -moz-transform: scale(1); 
 -ms-transform: scale(1); 
 -o-transform: scale(1); 
 transform: scale(1); 
}
/* animate out */
.my-mfp-zoom-in.mfp-removing .zoom-anim-dialog {
 -webkit-transform: scale(0.8); 
 -moz-transform: scale(0.8); 
 -ms-transform: scale(0.8); 
 -o-transform: scale(0.8); 
 transform: scale(0.8);
 opacity: 0;
}
/* Dark overlay, start state */
.my-mfp-zoom-in.mfp-bg {
 opacity: 0;
 -webkit-transition: opacity 0.3s ease-out; 
 -moz-transition: opacity 0.3s ease-out; 
 -o-transition: opacity 0.3s ease-out; 
 transition: opacity 0.3s ease-out;
}
/* animate in */
.my-mfp-zoom-in.mfp-ready.mfp-bg {
 opacity: 0.8;
}
/* animate out */
.my-mfp-zoom-in.mfp-removing.mfp-bg {
 opacity: 0;
}
/**
 * Fade-move animation for second dialog
 */
/* at start */
.my-mfp-slide-bottom .zoom-anim-dialog {
 opacity: 0;
 -webkit-transition: all 0.2s ease-out;
 -moz-transition: all 0.2s ease-out;
 -o-transition: all 0.2s ease-out;
 transition: all 0.2s ease-out;
 -webkit-transform: translateY(-20px) perspective( 600px ) rotateX( 10deg );
 -moz-transform: translateY(-20px) perspective( 600px ) rotateX( 10deg );
 -ms-transform: translateY(-20px) perspective( 600px ) rotateX( 10deg );
 -o-transform: translateY(-20px) perspective( 600px ) rotateX( 10deg );
 transform: translateY(-20px) perspective( 600px ) rotateX( 10deg );
}
/* animate in */
.my-mfp-slide-bottom.mfp-ready .zoom-anim-dialog {
 opacity: 1;
 -webkit-transform: translateY(0) perspective( 600px ) rotateX( 0 ); 
 -moz-transform: translateY(0) perspective( 600px ) rotateX( 0 ); 
 -ms-transform: translateY(0) perspective( 600px ) rotateX( 0 ); 
 -o-transform: translateY(0) perspective( 600px ) rotateX( 0 ); 
 transform: translateY(0) perspective( 600px ) rotateX( 0 ); 
}
/* animate out */
.my-mfp-slide-bottom.mfp-removing .zoom-anim-dialog {
 opacity: 0;
 -webkit-transform: translateY(-10px) perspective( 600px ) rotateX( 10deg ); 
 -moz-transform: translateY(-10px) perspective( 600px ) rotateX( 10deg ); 
 -ms-transform: translateY(-10px) perspective( 600px ) rotateX( 10deg ); 
 -o-transform: translateY(-10px) perspective( 600px ) rotateX( 10deg ); 
 transform: translateY(-10px) perspective( 600px ) rotateX( 10deg ); 
}
/* Dark overlay, start state */
.my-mfp-slide-bottom.mfp-bg {
 opacity: 0;
 -webkit-transition: opacity 0.3s ease-out; 
 -moz-transition: opacity 0.3s ease-out; 
 -o-transition: opacity 0.3s ease-out; 
 transition: opacity 0.3s ease-out;
}
/* animate in */
.my-mfp-slide-bottom.mfp-ready.mfp-bg {
 opacity: 0.8;
}
/* animate out */
.my-mfp-slide-bottom.mfp-removing.mfp-bg {
 opacity: 0;
}




/*
			TOOLTIP
		*/

		#tooltip
		{
			font-family: Ubuntu, sans-serif;
			font-size: 0.875em;
			text-align: center;
			text-shadow: 0 1px rgba( 0, 0, 0, .5 );
			line-height: 1.5;
			color: #fff;
			background: #333;
			background: -webkit-gradient( linear, left top, left bottom, from( rgba( 0, 0, 0, .6 ) ), to( rgba( 0, 0, 0, .8 ) ) );
			background: -webkit-linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
			background: -moz-linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
			background: -ms-radial-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
			background: -o-linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
			background: linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			border-top: 1px solid #fff;
			-webkit-box-shadow: 0 3px 5px rgba( 0, 0, 0, .3 );
			-moz-box-shadow: 0 3px 5px rgba( 0, 0, 0, .3 );
			box-shadow: 0 3px 5px rgba( 0, 0, 0, .3 );
			position: absolute;
			z-index: 100;
			padding: 15px;
		}

			#tooltip:after
			{
		        width: 0;
		        height: 0;
		        border-left: 10px solid transparent;
		        border-right: 10px solid transparent;
		        border-top-color: #333;
				border-top: 10px solid rgba( 0, 0, 0, .7 );
				content: '';
				position: absolute;
				left: 50%;
				bottom: -10px;
				margin-left: -10px;
			}

				#tooltip.top:after
				{
			        border-top-color: transparent;
			        border-bottom-color: #333;
					border-bottom: 10px solid rgba( 0, 0, 0, .6 );
					top: -20px;
					bottom: auto;
				}

				#tooltip.left:after
				{
					left: 10px;
					margin: 0;
				}

				#tooltip.right:after
				{
					right: 10px;
					left: auto;
					margin: 0;
				}
				
				
				
				.fancybox-lock .fancybox-overlay {
    overflow: auto;
    overflow-y: scroll;
}


</style>


<script src="/sahmatka/template/default/jBox-1.3.3/dist/jBox.all.min.js"></script>
<link href="/sahmatka/template/default/jBox-1.3.3/dist/jBox.all.min.css" rel="stylesheet">
<style>
.jBox-TooltipDark .jBox-container{background: rgba(0, 0, 0, 0.5); border-radius:4px;}
.ttopt .jBox-content{ background: rgba(0, 0, 0, 0.5);  border-radius:4px; font-size:10px;}
.jBox-TooltipDark .jBox-pointer:after{background: rgba(0, 0, 0, 0.7);}
 
</style>






  <link rel="stylesheet" type="text/css" href="/sahmatka/tooltip/tooltipster.bundle.min.css"/>
  <link rel="stylesheet" type="text/css" href="/sahmatka/tooltip/tooltipster-sideTip-punk.min.css"/>
  <link rel="stylesheet" type="text/css" href="/sahmatka/tooltip/style.css"/>

 <link
					href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
					rel="stylesheet"> 
  <script type="text/javascript" src="/sahmatka/tooltip/tooltipster.bundle.js"></script>
 





	</head>
	<body style="padding-top: 0;">
		<center> 
		<div style="display:inline-block;  width:98%; font-size:16px; text-align:left;"> 
		<?	
		 include('router.php');
		?>
		</div>
		
		<script>
		
 $( document ).ready(function() {
     if( window.innerWidth >= 1000 ){
	 // Подсказки для десктопа
var options = {
 attach: '[rel~=tooltip]',
 responsiveWidth:true,
 responsiveHeight:true,
 animation:'zoomIn',
 theme:'TooltipDark',
 addClass:'ttopt',
 maxWidth:180,
 width:180
}; 
	 
 }
 else
 {
	 // Подсказки для мобильных
 var options = {
 attach: '[rel~=tooltip]',
 responsiveWidth:true,
 responsiveHeight:true,
 animation:'zoomIn',
 theme:'TooltipDark',
 addClass:'ttopt',
 closeOnMouseleave:true,
 maxWidth:150, 
 width:150
};
	 
 }
 //new jBox('Tooltip', options);
});
 
		</script>
	</body>
	</html>
	<?
}
 
// print '<pre>';
// print_r($log);
// print '</pre>';

?>