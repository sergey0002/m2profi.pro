<?
// print_r($data);
if( $_SESSION['sh_login'] == 'admin' )
{
	$broni_status_arr[0]='Не задан';
	$broni_status_arr[2]='Свободен';
	$broni_status_arr[3]='Продан';
	$broni_status_arr[4]='Забронирован';
	$broni_status_arr[5]='Забронирован застройщиком';
	$broni_status_arr[6]='Участок подрядчика';					
}
elseif(  $_SESSION['sh_login'] == 'em_nsv' )
{
	$broni_status_arr[4]='Забронирован';
	$broni_status_arr[6]='Участок подрядчика';
	$broni_status_arr[5]='Забронирован застройщиком';				  
}

if(!$data['street']){$data['street']='Звездная';} 
if(!$data['htype']){$data['htype']='Family 180g';} 



$buf_area100 = $data['area']/100;
$calc_price_area = $buf_area100*600000;
if(!$data['price']){$data['price']='16000000';} 
  	
?>

<link href="https://g-lounge.ru/fonts/stylesheet.css" rel="stylesheet">


<style>


 
 
 
 
 
 
 
 
 
@media print {
    .noprint { display: none; }
}

 
body {
 font-family:"Exo 2"; color:#000;
	
 
    
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
	
	    margin-top: 10px;
    margin-bottom: 10px;
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
	margin:3px;
	color: #148F00;
	position: relative;
	text-align: center;
	border:solid 2px #42FF00;
	    border-radius: 20px;
		font-size:14px;
}
.tabs__caption li:not(.active) {
	cursor: pointer;
}
.tabs__caption li:not(.active):hover {
	 
	 
}
.tabs__caption .active {
	 background: #42FF00;
	 color:#148F00;
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


.fix1, .fix1:focus{
	margin: 3px;
    border: 1px solid rgba(0, 0, 0, 0.53);
    width: 100%;
    max-width: 300px;
    border-radius: 20px;
    padding-left: 10px;
    padding-right: 10px;
    line-height: 35px;
	padding-left: 10px;
    padding-right: 10px;
	
	
	margin:5px; 
	border: 1px solid rgba(0, 0, 0, 0.53);
	width:100%;
	max-width:300px;
	padding-left: 15px;
	
}

.form_sogl{margin:5px;}
	</style>
	
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
<?

if(!isset($_POST)){$_POST=array();}

if($_GET['action'] && $_POST['name'] && $_POST['phone'])
{	 
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
		
		if( $data['num'] )$message .= ' Участок  - <b>' . $data['num'] . '</b>';
	 
		$mess =  new messages('ssl://smtp.mail.ru','gl_order@mail.ru','WRmUfaALQcNkeYzLvV2p','g-lounge.ru'); 
 
	    $mess->send( '89236470002@mail.ru' ,'Заявка с сайта - ФОРМА КАРТОЧКИ УЧАСТКА -'.$act.' '.$_POST['name'].' ' , $message );
 	 // $mess->send( 'info@g-lounge.ru' ,'Заявка с сайта EM-NSK.ru - ФОРМА КАРТОЧКИ УЧАСТКА -'.$act.' '.$_POST['name'].' ', $message );
	 
		print '<br><br><h1>Заявка принята</h1><h2><br> Специалист отдела продаж свяжется с вами в ближайшее время.</h2>';
}
?>
<?


$result=$data;
	
	//print_r($data);
	
	?>
  <link href="https://gl.m2profi.pro/fonts/stylesheet.css" rel="stylesheet">
 <style>
 *{font-family: Finlandica;}
 </style>
  <div class="container-fluid" style="padding:20px;">
            <div class="row">
               <div class="col-md-12 col-xs-12" >
                  <h1 style="font-size:34px;"><b><?=$homes[$home_id]['caption'];?>  </b></h1>   
               </div>
			 
			   
			</div>
			<div class="row">
				<div class="col-md-12">
				<div style="display: inline-block; background: #FF7A00; padding: 10px; color: #FFF; font-weight: 700; font-size: 16px; width:auto; margin: 10px; margin-left:0;"><span style="display:none;"><?=$data['htype']?></span> 
				<?
				if(	  $data['raion']=="2")
				{
					print 'Green Lounge Dacha';
				}
				elseif(	  $data['raion']=="1")
				{
					print 'Green Lounge Residence';
				}
				?>
				
				</div>
				</div>
			
			</div>
			 <div class="row">
			 
				<div class="col-md-6 col-xs-12" style="text-align:center;">
				
                  <img src="/sahmatka/hrender/1.png?<?=rand(0,1000)?>" style=" width:100%; max-height:80vh"  >
                </div>
			    <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px; position:relative;">
				<span style="font-size:20px;"><b>Участок №<b> <?=$result['num'];?></b>  </b></span><br/>
				
				<div style="display:none;">
				<br/> <span style="font-size:16px;">ул.<?=$data['street']?></span><br/>
				</div>
				
				 <?
				 // print $buf_area100; // Полщадь участка в сотках
				 $price =  number_format($data['price'], 0, '.', ' ');
				 ?>
				 
				 <br/>
				 <span style="font-size:30px;"> <b><?=$price?> руб. </b></span> <br/> 
				 <span style="font-size:14px;">Площадь участка <?=$result['area']?> м<sup>2</sup> </span>
				  
				  
<?
if(!$stat || $stat==2)
{
?>
<br/>		 

	<div class="tabs noprint">
	
	<ul class="tabs__caption" style="padding-left:0;">
		<li style="display:none;" >Ипотека</li>
		<li class="active">Забронировать</li>
		<li>Записаться на экскурсию</li>
	</ul>

	<div class="tabs__content " style="padding-left: 0; font-size:16px;">
	<div class="row">
			<div class="col-md-6">
				Отправьте предварительную заявку на расчет ипотечного кредита: оставьте ваш контактный телефон и наш менеджер свяжется с вами. 
				<br/>
				За качество отвечает застройщик. <br/>Заходи и живи!
			</div>
			<div class="col-md-6">
			
			
			
			
				 <form action="iframe_router.php?ctr=landplots&act=order_pub&map_id=<?=$_GET['map_id'];?>&polygon_id=<?=$_GET['polygon_id']?>&action=epoteca" method="post" enctype="multipart/form-data" method="post">
				 <input type="text" name="name" class="fix1" required placeholder="Ваше имя" s  /><br/>
				 <input type="text" name="phone" class="fix1" required placeholder="Контактный телефон"   /><br/>			
				 <div style="width:100%; max-width:300px;">
				 
				<div class="form_sogl"> <input type="checkbox" name="offerta" checked="checked" value="1">	Я даю свое согласие на обработку персональных данных</div>
			     </div>
				 
			</div>
			
			<br/>
			<br/>
			
			<input type="submit" class="fix1" style="display: block; margin-top: 15px; background: #148F00;  color: #FFFFFF;  " value="Отправить заявку на ипотеку">
			
			</form>
		</div>
		
    </div>
	
	
	

	<div class="tabs__content active" style="padding-left: 0; font-size:16px;">
		<div class="row">
		
			<div class="col-md-6">
			
			Отправьте предварительную заявку на бронирование или экскурсию: оставьте ваш контактный телефон и наш менеджер свяжется с вами.
			
				 
				 
			</div>
			<div class="col-md-6">
				 <form action="iframe_router.php?ctr=landplots&act=order_pub&polygon_id=<?=$_GET['polygon_id']?>&action=bron" method="post" enctype="multipart/form-data" method="post">
				 <input type="text" name="name" required placeholder="Ваше имя" class="fix1"/><br/>
				 <input type="text" name="phone" required placeholder="Контактный телефон" class="fix1"  /><br/>			
				 <div style="width:100%; max-width:300px;">
					<div class="form_sogl"> <input type="checkbox" name="offerta" checked="checked" value="1">	Я даю свое согласие на обработку персональных данных</div>
			     </div>
				 
			</div><br/><br/>
		<input type="submit" class="fix1" style="  display: block;    margin-top: 15px; background: #148F00;  color: #FFFFFF;  " value="Забронировать"  > </form>
		</div>
		
	</div>

	<div class="tabs__content" style="padding-left: 0; font-size:16px;">
		<div class="row">
		
			<div class="col-md-6">
				Отправьте предварительную заявку на бронирование или экскурсию: оставьте ваш контактный телефон и наш менеджер свяжется с вами.
			</div>
			<div class="col-md-6">
				 <form action="iframe_router.php?ctr=landplots&act=order_pub&polygon_id=<?=$_GET['polygon_id']?>&action=bron" method="post" enctype="multipart/form-data" method="post">
				 <input type="text" name="name" required placeholder="Ваше имя" class="fix1" /><br/>
				 <input type="text" name="phone" required placeholder="Контактный телефон" class="fix1"  /><br/>			
				 <div style="width:100%; max-width:300px;">
					<div class="form_sogl"> <input type="checkbox" name="offerta" checked="checked" value="1">	Я даю свое согласие на обработку персональных данных</div>
			     </div>
								 
			</div>
			<br/><br/>
			 <input type="submit" class="fix1" style="  display: block;    margin-top: 15px; background: #148F00;  color: #FFFFFF;  " value="Записаться"  >


		</form>
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