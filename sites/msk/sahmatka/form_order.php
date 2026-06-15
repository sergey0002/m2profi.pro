<?
session_start();
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: post-check=0,pre-check=0", false);
  header("Cache-Control: max-age=0", false);
  header("Pragma: no-cache");
 
 include('config.php');
 
 
 
		

//check_count_zapis(1,'01.02.2020','13:00'); Количество записей на экскурсию 
function check_count_zapis($home,$data,$time)
{
	$tsql = 'SELECT count(*) as c FROM `excurs` WHERE "'.$data.'" = DATE_FORMAT(date,"%d.%m.%Y") AND `home` = "'.$home.'" ';
	if($time){$tsql .= ' AND `time` = "'.$time.'" ';}
	$query = mysqli_query($GLOBALS[connection], $tsql); 
	while ($result = mysqli_fetch_array($query)) { $x = $result[0] ; }	
	return $x;
}

 

?>
<html>
<head>
<!DOCTYPE html>
<html>
  <head>
    <title>Энергомонтаж</title>
	 
    <link rel="icon" href="/templates/jv-framework/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://em-nsk.ru/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
	<meta charset="utf-8">
	<link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Exo+2" rel="stylesheet">

	<link href="https://em-nsk.ru/fonts/ptsans/ptsans.css" rel="stylesheet">
	<link href="https://em-nsk.ru/fonts/exo2/exotwo.css" rel="stylesheet">

<style>

@media print {
    .noprint { display: none; }
}

	body{font-family:"Exo 2"; color:#000;}
body {
    font: 90.5%/1.3 normal Helvetica, sans-serif;
    padding-top: 50px;
    margin-top: 50px;
	
	    font: 90.5%/1.3 normal Helvetica, sans-serif;
    padding-top: 50px;
    margin-top: 50px;
}

.actfix {
    padding-left: 10px;
    width: 100%;
    display: inline-block;
}
 
.vcardem{margin-top:5px;margin-bottom:5px;}
.tabs__caption {
	display: -webkit-flex;
	display: -ms-flexbox;
	display: flex;
	-webkit-flex-wrap: wrap;
	    -ms-flex-wrap: wrap;
	        flex-wrap: wrap;
	list-style: none;
	position: relative;
	margin: -1px 0 0 -1px;
	
	
	font-family: Exo 2; 
	font-size:18px;
	font-weight:bold;
	width:100%;
}
.tabs__caption li:last-child:before {
	content: '';
	position: absolute;
	bottom: -5px;
	left: 0;
	right: -2px;
	z-index: -1;
	height: 5px;
 
}
.tabs__caption:after {
	content: '';
	display: table;
	clear: both;
}
.tabs__caption li {
	padding: 9px 15px;
	margin: 1px 0 0 1px;
	color: #000;
	position: relative;
	text-align: center;
}
.tabs__caption li:not(.active) {
	cursor: pointer;
}
.tabs__caption li:not(.active):hover {
	 
	border-bottom:solid 5px #E30613;
}
.tabs__caption .active {
	border-bottom:solid 5px #E30613;
}
.tabs__caption .active:after {
	content: '';
	position: absolute;
	bottom: -5px;
	left: 0;
	right: 0;
	height: 5px;
	 
}
.tabs__content {
	transition: 1s; 
	display: none;
	padding: 7px 15px;
	font-family: Exo 2;
	font-size:14px;
	
	
	line-height: 1.7em;
    padding-left: 40px;
    padding-top: 20px;
	opacity:0;
	
}
.tabs__content.active {
	display: block;
	transition: 1s; 
	opacity:100;
}

.vertical .tabs__caption {
	float: left;
	display: block;
}
.vertical .tabs__caption li {
	float: none;
	border-width: 2px 0 2px 2px;
	border-radius: 5px 0 0 5px;
}
.vertical .tabs__caption li:last-child:before {
	display: none;
}
.vertical .tabs__caption .active:after {
	left: auto;
	top: 0;
	right: -2px;
	bottom: 0;
	width: 2px;
	height: auto;
}
.vertical .tabs__content {
	overflow: hidden;
}

@media screen and (max-width: 650px) {

	.tabs__caption li {
		-webkit-flex: 1 0 auto;
		    -ms-flex: 1 0 auto;
		        flex: 1 0 auto;
	}
	.vertical .tabs__caption {
		float: none;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
	}
	.vertical .tabs__caption li {
		border-width: 2px 2px 0;
		border-radius: 5px 5px 0 0;
	}
	.vertical .tabs__caption li:last-child:before {
		display: block;
	}
	.vertical .tabs__caption .active:after {
		top: auto;
		bottom: -5px;
		left: 0;
		right: 0;
		width: auto;
		height: 5px;
		background: #FFF;
	}

}

.room-tabnav {
  margin-bottom: 23px;
}

.room-tabnav li {
  display: inline-block;
  vertical-align: top;
  margin-right: 23px;
}

.room-tabnav li:last-child {
  margin-right: 0;
}

.room-tabnav li a {
  position: relative;
  display: inline-block;
  padding-bottom: 17px;
  font-size: 18px;
  font-weight: 700;
}

.room-tabnav li a:before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  -webkit-transform: translateX(-50%);
      -ms-transform: translateX(-50%);
          transform: translateX(-50%);
  width: 0;
  height: 5px;
  -webkit-transition: all .25s ease;
  -o-transition: all .25s ease;
  transition: all .25s ease;
  background: #E30613;
}

.room-tabnav li a:hover:before, .room-tabnav li a.active:before {
  width: 100%;
}

.room-tabbody {
  display: none;
}

.room-tabbody:before, .room-tabbody:after {
  content: " ";
  display: table;
}

.room-tabbody:after {
  clear: both;
}

.room-tabbody.open {
  display: block;
}

.room-tabtext {
  float: left;
  width: 50%;
  padding-right: 20px;
  font-size: 16px;
  line-height: 1.5;
}

.room-tabform {
  float: left;
  width: 50%;
  padding-right: 45px;
  text-align: right;
}

.room-tabform form {
  display: inline-block;
  max-width: 300px;
}

.room-tabform input {
  display: block;
  width: 100%;
  max-width: 300px;
  height: 37px;
  margin-bottom: 10px;
  padding-left: 4px;
  font-size: 16px;
  border: 1px solid rgba(0, 0, 0, 0.53);
}

.room-tabform__accept {
  display: block;
  margin-bottom: 10px;
  text-align: left;
}

.room-tabform__accept .jq-checkbox {
  float: left;
  top: 3px;
  width: 16px;
  height: 16px;
  margin-right: 8px;
  border-radius: 0;
  border: 1px solid #C3C3C3;
  -webkit-box-shadow: none;
          box-shadow: none;
  background: none;
}

.room-tabform__accept .jq-checkbox.focused {
  border: 1px solid #C3C3C3;
}

.room-tabform__accept .jq-checkbox.checked .jq-checkbox__div {
  width: 12px;
  height: 12px;
  border-radius: 0;
  -webkit-box-shadow: none;
          box-shadow: none;
  background: url(../images/check.svg) 0 0 no-repeat;
  background-size: 100%;
}

.room-tabform__accept span {
  display: table;
  font-size: 16px;
  line-height: 1.4;
}

.room-tabform__btn {
  width: 100%;
  height: 37px;
  line-height: 35px;
}


select{
	
	    padding: 4px;
    margin: 3px;
    border: 1px solid rgba(0, 0, 0, 0.53);
 
    max-width: 300px;
}
	</style>
 
	<script src="/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js"></script>
	<script>
	
	(function($) {
$(function() {

	$('ul.tabs__caption').on('click', 'li:not(.active)', function() {
		$(this)
			.addClass('active').siblings().removeClass('active')
			.closest('div.tabs').find('div.tabs__content').removeClass('active').eq($(this).index()).addClass('active');
	});

});
})(jQuery);
	 
		$(".room-tabnav li a").click(function() {
		    $('.room-tabnav li a').removeClass("active");
		    $(this).addClass("active");
		    $(".room-tabbody").removeClass('open').hide();
		    var activeTab = $(this).attr("href");
		    $(activeTab).addClass('open').fadeIn(700);
		    return false;
		});
		
	</script>

  </head>

<body style="margin-top:0; padding-top:0">


 <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-126816776-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-126816776-1');
</script>


  </head>
  <body>
  
  
   <!-- Rating@Mail.ru counter -->
<script type="text/javascript">
var _tmr = window._tmr || (window._tmr = []);
_tmr.push({id: "3065813", type: "pageView", start: (new Date()).getTime(), pid: "USER_ID"});
(function (d, w, id) {
  if (d.getElementById(id)) return;
  var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
  ts.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//top-fwz1.mail.ru/js/code.js";
  var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
  if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
})(document, window, "topmailru-code");
</script><noscript><div>
<img src="//top-fwz1.mail.ru/counter?id=3065813;js=na" style="border:0;position:absolute;left:-9999px;" alt="" />
</div></noscript>
<!-- //Rating@Mail.ru counter -->


 
 
 
 <!-- Yandex.Metrika counter --> <script type="text/javascript" > (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}; m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)}) (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym"); ym(18713149, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); </script> <noscript><div><img src="https://mc.yandex.ru/watch/18713149" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
 
<?




if(!isset($_POST)){$_POST=array();}
 
@$apartments = $_GET['apartments'];
@$apartments_num=$_GET['apartment_num'];
@$apartment_num=$_GET['apartment_num'];
@$home_id = (int) $_GET['home_id'];
 


if($_GET['action']  && $_POST[name] && $_POST[phone])
 {	 
	// print '<pre>';
	// print_r($_GET);
	// print_r($_POST); 
 //print '</pre>';
 
		 if($_GET['action']=='ex'){$act='Экскурсия';}
		 elseif($_GET['action']=='epoteca'){$act='Ипотека';}
		 elseif($_GET['action']=='bron'){$act='Бронь';}
		 
		# формирвем сообщение на почту о бронировании квартиры
		// вложения файлов
		$message = "Заявка посетителя сайта   \r\n <br/>";
		$message .= "Цель заявки - $act \r\n <br/>";
		
		
		if($_POST[date])
		{
		$message .= "Дата - $_POST[date] \r\n <br/>";
		
		$message .= "Время   - $_POST[time] \r\n <br/>";
		}
		
		$message .= "Имя  - <b>".$_POST['name'].'</b> <br/> Телефон -  <b>'.$_POST['phone']."</b></b><br/> \r\n ";
		
		$sa = $GLOBALS['sa'];
		$homes = $sa->get_homes_arr();
		
		
		
		$new_homes = array();
		foreach($homes as $k=>$v){	 $new_homes[$v['home_id']] = $v['title'];}
		$message .= "Дом <b>".$new_homes[$home_id] ."</b> секция-<b>".$_GET[section_id]."</b> Этаж-<b>". $_GET[floor]."</b> кв-<b>".$apartment_num."</b> ";
 
 
 
		//print $message;	 
	  
	  $mess =  new messages();
	  $mess->send( '89236470002@mail.ru' ,'Заявка с сайта EM-NSK.ru - ФОРМА КАРТОЧКИ КВАРТИРЫ -'.$act.' '.$_POST['name'].' ' , $message );
 	  $mess->send( 'op@em-nsk.group,op@em-nsk.group' ,'Заявка с сайта EM-NSK.ru - ФОРМА КАРТОЧКИ КВАРТИРЫ -'.$act.' '.$_POST['name'].' ', $message );
	 
	 
		  print '<br><br><h1>Заявка принята</h1><h2><br> Специалист отдела продаж свяжется с вами в ближайшее время.</h2>';
		 
		
	}
  
 
 
 

 

/*
$home_id=5;
$apartment_num = 64;
@$apartments = 4;
*/

 
  	$sql = 'SELECT * FROM `apartaments` WHERE home_id="'.$home_id.'" AND apartment_num="'.$apartment_num.'" ';
	 
	$arr = array();
	$query = mysqli_query($GLOBALS['connection'], $sql); 
	$result = mysqli_fetch_array($query);
	
	$result['price'] =  number_format($result['price'], 0, '.', ' ');
	$result['price'].=' руб';
	
?>



					
	<?			
	// Получаем статус квартиры
	  $sql = 'SELECT * FROM broni LEFT JOIN users ON broni.user_id = users.id  WHERE home_id="'.$home_id.'"  AND apartments_num="'.$apartment_num.'" ORDER BY date asc;';
	$query = mysqli_query($connection, $sql); 
	while ($result2 = mysqli_fetch_array($query)) {	$stat = $result2['status'];	}	
	
	 # НЕ у всех квартир тут есть тстатусы почемуто  ! нет броней потому что 
	 
	//print $stat;
	// $stat='';
 
	?>
	
	




         <div class="container-fluid" style="padding:20px;">
            <div class="row">
               <div class="col-md-12 col-xs-12" >
                  <h1 style="font-size:34px;"><b><?=$homes[$home_id]['caption'];?></b></h1>   
               </div>
			   <div class="col-md-12 col-xs-12" style="font-size:16px; text-transform: uppercase;">
				Секция -  <?=$result['section_id'];?> <br/>Этаж -  <?=$result['floor'];?>  <br/> Квартира -   <?=$result['apartment_num'];?> 
			   </div>
			   
			</div>
			
			 <div class="row">
			 
				<div class="col-md-6 col-xs-12" style="text-align:center;">
                  <img src="<?=$result['image_pb'];?>?x=123" style="max-height:600px; max-width:100%"  >
                </div>
			    <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px; position:relative;">
				 <b> <?=$result['floor'];?> этаж   </b>
				 <hr/>
				 Количество комнат - <b><?=$result['rooms']?></b><br/>
				 Площадь - <b><?=$result['area']?></b> м<sup>2</sup>
				 <hr/> 
				

<?

if(!$stat || $stat==2)
{
	?>
	
	 Цена - <b><?=$result['price'];?></b> 
				 <br/>
				 
				 
				 <div style="margin:10px; margin-left:0;" class="noprint">
				 
					 <a href="#" onClick="window.print();"><img src="images/p.png" width="40px" style="margin:5px; margin-left:0;"></a>
					 <a onclick="$('#one').slideToggle('slow');" href="javascript://"><img src="images/m.png" width="40px" style="margin:5px;"></a>
				
					<div id="one" style="display: none;">
						 <form action="form_order.php?home_id=<?=$home_id?>&section_id=<?=$result['section_id']?>&floor=<?=$result['floor']?>&apartment_num=<?=$apartment_num;?>&apartments=<?=$apartments;?>&action=mail" method="post" enctype="multipart/form-data" method="post">
						 <input type="text" name="email" placeholder="E-Mail" style="font-size:14px; padding:4px; margin:4px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;" /><br/>
						 <input type="submit" style=" font-size:14px; padding:4px; margin:4px; background: #40C351; border: 1px solid #40C351; letter-spacing: 0.21em; text-transform: uppercase; color: #FFFFFF; width:100%; max-width:300px;">
						 </form>

					</div>
				</div>
				 
				 
			 
	 
					
					
					
			 
			 <div class="tabs noprint">

	<ul class="tabs__caption" style="padding-left:0;">
		
		<li class="active" >Рассчитать ипотеку</li>
		<li>Забронировать</li>
		<a href="https://em-nsk.ru/exc.php" target="_top"><li >Записаться на экскурсию</li></a>
	</ul>



	<div class="tabs__content active" style="padding-left: 0; font-size:16px;">
	<div class="row">
		
			<div class="col-md-6">
				Отправьте предварительную заявку на расчет ипотечного кредита: оставьте ваш контактный телефон и наш менеджер свяжется с вами. 
				<br/>
				За качество отвечает застройщик. <br/>Заходи и живи!
			</div>
			<div class="col-md-6">
				 <form action="form_order.php?home_id=<?=$home_id?>&section_id=<?=$result['section_id']?>&floor=<?=$result['floor']?>&apartment_num=<?=$apartment_num;?>&apartments=<?=$apartments;?>&action=epoteca" method="post" enctype="multipart/form-data" method="post">
				 <input type="text" name="name" required placeholder="Ваше имя" style="padding:4px; margin:3px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;" /><br/>
				 <input type="text" name="phone" required placeholder="Контактный телефон" style="padding:4px; margin:3px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;"  /><br/>			
				 <div style="width:100%; max-width:300px;">
				 <input type="checkbox" name="offerta" checked="checked" value="1">	Я даю свое согласие на обработку персональных данных
			     </div>
				 <input type="submit" style="padding:4px; margin:3px; background: #40C351; border: 1px solid #40C351; letter-spacing: 0.21em; text-transform: uppercase; color: #FFFFFF; width:100%; max-width:300px;" value="Рассчитать" onClick= "ga('send', 'event', 'submit', 'kv_order_botton'); ym(18713149,'reachGoal','kv_order_botton'); return true;">
				 </form>
			</div>
		
		</div>
		
    </div>
	
	
	

	<div class="tabs__content" style="padding-left: 0; font-size:16px;">
		<div class="row">
		
			<div class="col-md-6">
				Забронировать квартиру  в “Энергомонтаж” можно всего за пару минут: оставьте ваш контактный телефон и наш менеджер свяжется с вами. 
				<br/>
				За качество отвечает застройщик. <br/>Заходи и живи!
			</div>
			<div class="col-md-6">
				 <form action="form_order.php?home_id=<?=$home_id?>&section_id=<?=$result['section_id']?>&floor=<?=$result['floor']?>&apartment_num=<?=$apartment_num;?>&apartments=<?=$apartments;?>&action=bron" method="post" enctype="multipart/form-data" method="post">
				 <input type="text" name="name" required placeholder="Ваше имя" style="padding:4px; margin:3px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;" /><br/>
				 <input type="text" name="phone" required placeholder="Контактный телефон" style="padding:4px; margin:3px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;"  /><br/>			
				 <div style="width:100%; max-width:300px;">
				 <input type="checkbox" name="offerta" checked="checked" value="1">	Я даю свое согласие на обработку персональных данных
			     </div>
				 <input type="submit" style="padding:4px; margin:3px; background: #40C351; border: 1px solid #40C351; letter-spacing: 0.21em; text-transform: uppercase; color: #FFFFFF; width:100%; max-width:300px;" value="Забронировать" onClick= "ga('send', 'event', 'submit', 'kv_order_botton'); ym(18713149,'reachGoal','kv_order_botton'); return true;">
				 </form>
			</div>
		
		</div>
		
	</div>

 

</div><!-- .tabs -->

<?
}
?>


</div>



			    </div>
                  
				  
			   
			   
            </div>
			
			
			
			
			



         </div>
<?
 
?>
<!-- Google analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75002110-4', 'auto');
  ga('send', 'pageview');

</script>
<!-- /Google analytics -->
</body>
</html>

 