<?
 

class ctr__areas extends ctr__
{ 

	var $table = 'areas'; //Главная таблица
	var $key_filed = 'area_id'; // Ключевое поле главной таблицы
 
	// ИНПУТ ВЫБОРА ПЛОЩАДИ (два текстовых поля этаж и код облати)
	function area_select($floor_code='',$area_code='',$notab='',$clicable=1)
	{
		 
		global $mysql;
		
		$arr = $mysql->get_arr('SELECT * FROM `fw_nodes` WHERE `data_area`>0 AND fw_nodes.del=0 AND fw_nodes.show=1 ORDER by floor ');
		foreach($arr as $k=>$v)
		{
			
			 
			
			if($v['floor'] =='-1'){ $v['floor'] = '0';}
			if(!$v['floor']  ){ $v['floor'] = '0';}
		
		
			$area_arr[$v['floor']][$v['data_area']]=$v;
		}
		//print '<pre>';
		 //print_r($area_arr);
		//print '</pre>';
		//print $GLOBALS['rootdir'];
		
		
		if(!$floor_code){ $floor_code = $_GET['floor_code'];}
		if(!$area_code){ $area_code = $_GET['area_code'];}
		
		
		if($floor_code =='-1'){ $floor_code = '0';}
		if(!$floor_code  ){ $floor_code = '0';}
		
		if(!$area_code){ $area_code = 0;}
		
	 	//print $floor_code;
		print '<pre>';
		//print_r($area_arr);
		print '</pre>';
		?>		
		<style>
		  /*Карта*/
			.trc-plan-body img
			{
				width:100%;
			}
			.trc-plan{position:relative;}
			.trc-plan-main .trc-plan-body{display:block;  visibility: hidden; position:absolute; top:0; z-index:-1;}
			.trc-plan-main .open{display:block;visibility: visible; position:relative; top:auto; z-index:1;}
			 
			
			.plan-tabs{position:absolute; z-index:5;}
			
			.plan-tabs a
			{
			display: block;
			border-bottom: solid 1px #000;
			font-weight: bold;
			color: #FF8800;
			width: 30px;
			text-align: center;
			padding: 5px;
			color:#FF8800;
			} 
			 .plan-tabs .active{
				color: #000;
			}
 
  

.popup-rectangle {
box-sizing: border-box;

width: 176px;
 
background: #FFCC00;
border: 2px solid #FFFFFF;
padding: 13px;
}


.popup-rect-logo{
 
    padding: 10px;
   /* background: #E6973D; */
    background: #FFF;
	max-width:100%;
	max-height:50px;
	height:50px;
}



.popup-rect-title{

font-style: normal;
font-weight: 900;
font-size: 16px;
line-height: 120%;



color: #986122;
margin-top: 10px;
}



.popup-rect-text{

font-style: normal;
font-weight: 300;
font-size: 16px;
line-height: 120%;

color: #986122;


}



.popup-rect-a{

font-style: normal;
font-weight: 300;
font-size: 12px;
line-height: 120%;

text-decoration-line: underline;

color: #986122;
margin-top: 10px;



}

div.popup-rect-a a:hover {
color: #E6973D;
}


.popup-triangle {
    position: absolute;
    border-top: 16px solid #FFCC00;
    border-right: 41px solid transparent;
    margin: -4px 0px 0px 23px;
}


.popup-triangle-border {
    position: absolute;
    border-top: 16px solid #ffffff;
    border-right: 41px solid transparent;
    margin: 0px 0px 0px 20px;
}
 
		</style>
		
		    <style>
  #myTooltip {
   position: absolute;
  display: block;
  height:100px;
  z-index:1;
}
area{z-index:2;}
    </style>

		<?
		for($i=0; $i<10; $i++) // Этажи 
		{
			for( $k=0; $k < 100; $k++ ) // Магазины
			{
			if($area_arr[$i][$k]['title'])
			{
				$color='FFCC00';
			}
			else
			{
				$color='EEEEEE';
			}
			
			 ob_start();
			?>
			<div>
				<div class="popup-rectangle"> 
					<?if($area_arr[$i][$k]['logo'] && $area_arr[$i][$k]['logo']!='/admin/nofoto.png') {?><img class="popup-rect-logo" src="<?=$area_arr[$i][$k]['logo']?>" alt=""> <? } ?>
					<div class="popup-rect-title"><?=$area_arr[$i][$k]['title']?></div>
					<div class="popup-rect-text" style="display:none;">10:00–20:00 </div>
					<div class="popup-rect-a" style="display:none;"> <a href="">Подробнее</a> </div>
				</div>
				<div class="popup-triangle-border"> </div>
				<div class="popup-triangle"> </div>
			</div>
			<?
			 $x=ob_get_clean();
			 if( $area_arr[$i][$k]['title'] )
			 {
				$x = str_replace('"','\'',$x);
			 }
			 else
			 {
				$x ='';
			 }
				$areamap[$i][$k] = ' data-title="'.$x.'" data-floor="'.$i.'" data-area="'.$k.'" data-href="http://trckristall.ru/admin/index.php?ctr='.$_GET["ctr"].'&act='.$_GET["act"].'&id='.$_GET[id].'&floor_code='.$i.'&area_code='.$k.'" ' ;
				$areamap[$i][$k] .= ' class="_area_'.$k.'" data-maphilight=\'{"stroke":"false", "strokeColor":"FFFFFF","strokeWidth":3,"fillColor":"'.$color.'","fillOpacity":1,"shadow":true,"shadowBackground":"000000"}\' ';
			}
		} 
		
		
		
		  $idir =  $_SERVER['DOCUMENT_ROOT']; 
		
		
		//$idir = '/home/u493494/trckristall.ru/www';
		?>
		<div class="container">
			<div class="trc-plan" style="text-align: center;   ">
			
				<div class="plan-tabs" <? if($notab){print 'style="display:n!one;"';}?>>
					<a href="#plan-0" class="trc-plan-tabs__item <?if($floor_code=='0'){ print 'active';}?>">-1</a>
					<a href="#plan-1" class="trc-plan-tabs__item <?if($floor_code=='1'){ print 'active';}?>">1</a>
					<a href="#plan-2" class="trc-plan-tabs__item <?if($floor_code=='2'){ print 'active';}?>">2</a>
					<a href="#plan-3" class="trc-plan-tabs__item <?if($floor_code=='3'){ print 'active';}?>">3</a>
					<a href="#plan-4" class="trc-plan-tabs__item <?if($floor_code=='4'){ print 'active';}?>">4</a>
				    <a href="#plan-5" class="trc-plan-tabs__item <?if($floor_code=='5'){ print 'active';}?>">5</a>
				</div>

				<div class="trc-plan-main"  >
					<div id="plan-0" class="trc-plan-body <?if($floor_code=='0'){ print 'open';}?>">
						<? include_once($idir.'/template/floors/0.php'); ?>
					</div>
					<div id="plan-1" class="trc-plan-body  <?if($floor_code=='1'){ print 'open';}?>">
						<? include_once($idir.'/template/floors/1.php'); ?>
					</div>
					<div id="plan-2" class="trc-plan-body <?if($floor_code=='2'){ print 'open';}?>">
						<? include_once($idir.'/template/floors/2.php'); ?>
					</div>
					<div id="plan-3" class="trc-plan-body <?if($floor_code=='3'){ print 'open';}?>">
						<? include_once($idir.'/template/floors/3.php'); ?>
					</div>
					<div id="plan-4" class="trc-plan-body <?if($floor_code=='4'){ print 'open';}?>">
						<? include_once($idir.'/template/floors/4.php'); ?>
					</div>
					<div id="plan-5" class="trc-plan-body <?if($floor_code=='5'){ print 'open';}?>"> 
						<? include_once($idir.'/template/floors/5.php'); ?>
					</div>
					<!-- Подсказка всплывающая -->
					
				</div>
				
			</div>
		 <div id="myTooltip"></div>
		<script>
		 $(".trc-plan-tabs__item").click(function() { 
		    $('.trc-plan-tabs__item').removeClass("active");
		    $(this).addClass("active");
		   // $(".trc-plan-body").removeClass('open').hide();
		    $(".trc-plan-body").removeClass('open');
		    var activeTab = $(this).attr("href");
			
			//$(activeTab).addClass('open').show();
			$(activeTab).addClass('open');
			
			
			$('.map').maphilight({ fillColor: '008800' });
			$('map').imageMapResize();
			// подсветка
		
			
			
			//$(activeTab).addClass('open').fadeIn(300);
			//$('map').imageMapResize(); 
			
		    return false;
		});

 
		$(document).ready(function() {

			$(window).resize(function(){
				// Ресайз
				$('map').imageMapResize();
				// подсветка
				$('.map').maphilight({ fillColor: '008800' });
			})
			// Ресайз
			$('map').imageMapResize();
			// подсветка
			$('.map').maphilight({ fillColor: '008800' });
			
			
			<? if($clicable)
			{		
			?>		
			// Клик на области
			$("area").click(function(){
				//alert(  );
				// document.location.href = $(this).data('href');
				
				$('#data_area').val($(this).data('area'));
				$('#data_floor').val($(this).data('floor'));
				
				// Снимаем отметку со всех элементов
				$('area').each(function(i,elem) 
				{
					var data = $(elem).mouseout().data('maphilight') || {};
					data.alwaysOn = false;
					$(elem).data('maphilight',data).trigger('alwaysOn.maphilight');
				});

				// Отмечаем текущий элемент
				var data = $(this).mouseout().data('maphilight') || {};
				data.alwaysOn = true;
				$(this).data('maphilight',data).trigger('alwaysOn.maphilight');
				
				return false;
			});
			<?
			}
			
			?>
			
			
			
			
			<?
			if($area_code || $area_code ==0)
			{
			?>
			//	e.preventDefault();
            var data = $('._area_<?=$area_code?>').mouseout().data('maphilight') || {};
            data.alwaysOn = !data.alwaysOn;
            $('._area_<?=$area_code?>').data('maphilight',data).trigger('alwaysOn.maphilight');
			<?
			}
			?>
			
			
		});

	 
		
		
 /*
    $( "area" ).tooltip({
		  track: true ,
		  content: function ()
		  {
			 return this.getAttribute("title");
		  },
		  position: {
			my: "center bottom-20",
			at: "center top",
			using: function( position, feedback ) {
			  $( this ).css( position );
			  $( "<div>" )
				.addClass( "arrow" )
				.addClass( feedback.vertical )
				.addClass( feedback.horizontal )
				.appendTo( this );
			}
		  }
    });
 
 */

 

$(document).ready(function () {
         
            $('area').hover(function () {
			
                $('.imgpopover').css({ "display": "block", "top": $(this).attr("coords").split(',')[1]+"px", "left": $(this).attr("coords").split(',')[0]+"px" })
                // $('.imgpopover label').text($(this).attr("title"))
            }, )
        });
		
		
		

function is_touch_device() {
  return !!('ontouchstart' in window);
}



// Показываем подсказку при наведении мышки
$('area').mouseover(function(){
	var title = $(this).data("title");
	$('#myTooltip').html(title);
	
	$('#myTooltip').fadeIn(300);
	var th =  $('#myTooltip').height(); 
  
  /// Получаем верхнюю левую координату полигона 
	var i, x = [], y = [],  x1 = [], y1 = [] ;
	var z = new Map();
	  
	console.log($(this).attr('coords'));
	var c = $(this).attr('coords').split(',');
	for (i=0; i < c.length-1; i++)
	{
	  x.push( Number(c[i++] ) );
	  y.push( Number(c[i]) );
	}
  
    var min   ;
    for (i = 0; i < y.length; ++i)
	{
		if (!min || min > y[i]) { min = y[i];}
	}
 
	var t = min;
	l = x[y.indexOf(min)];
	///////////////////
 
 
    var l2 = $(this).attr("coords").split(',')[0];
  	var t2 = Number($(this).attr("coords").split(',')[1]);
	
	 //	alert(l+'-'+l2);
	 // alert( t+'-'+t2 );

	$('#myTooltip').css('top', Number(t)+$('.maphilighted').offset().top-th+"px" );
	$('#myTooltip').css('left', Number(l)+$('.maphilighted').offset().left+"px" );
 
});
// Показываем подсказку при клике на мобильных девайсах
$('area').click(function(){
		var title = $(this).data("title");
	$('#myTooltip').html(title);
	
	 
	$('#myTooltip').fadeIn(300);
		
	var offset = $('.maphilighted').offset();  
	var th =  $('#myTooltip').height(); 
  
 
  /// Получаем верхнюю левую координату полигона 
	var i, x = [], y = [],  x1 = [], y1 = [] ;
	var z = new Map();
	  
	console.log($(this).attr('coords'));
	var c = $(this).attr('coords').split(',');
	for (i=0; i < c.length-1; i++)
	{
	  x.push( Number(c[i++] ) );
	  y.push( Number(c[i]) );
	}
  
    var min   ;
    for (i = 0; i < y.length; ++i)
	{
		if (!min || min > y[i]) { min = y[i];}
	}
 
	var t = min;
	l = x[y.indexOf(min)];
	///////////////////
 
 
    var l2 = $(this).attr("coords").split(',')[0];
  	var t2 = Number($(this).attr("coords").split(',')[1]);
	
	 //	alert(l+'-'+l2);
	 // alert( t+'-'+t2 );

	$('#myTooltip').css('top', Number(t)+offset.top-th+"px" );
	$('#myTooltip').css('left', Number(l)+offset.left-30+"px" );
});

 
$(document).mouseup( function(e){ // событие клика по веб-документу
	var div = $( "area" ); // тут указываем ID элемента
	if ( !div.is(e.target) // если клик был не по нашему блоку
	    && div.has(e.target).length === 0 ) { // и не по его дочерним элементам
		div.hide(); // скрываем его
	}
});
 



// При уходе мышки скрываем 
$('.trc-plan-main').mouseout(function(){
	 
		$('#myTooltip').hide();
 
});


 
 
document.addEventListener('mousemove', function(e){
    /*console.log(e.pageX);
    console.log(e.pageY);*/
    //document.getElementById('myTooltip').style.left = (e.pageX+5)+"px";
   // document.getElementById('myTooltip').style.top = (e.pageY-100)+"px";
 
});
		</script>
				
		</div>
	

		<input type="hidden" name="data_floor" id="data_floor" value="<?=$floor_code?>" />
		<input type="hidden" name="data_area" id="data_area" value="<?=$area_code?>"/>
		
		<?
		
		
	}
		

	
	function act__index()
	{
		global $t;
		global $r;
		$t['h1'] = 'Площади';
		global $filed;
		global $mysql;
	?>
	<form action="" method="post">
	<?
	$sql = 'SELECT * FROM fw_nodes ';
	$arr = $mysql->get_arr($sql);
	?><select><?
	foreach($arr as $k=>$v)
	{
		?><option value="<?=$v['title']?>"><?=$v['title']?></option><?
	}
	
	?>
	</select>
	<?
	$this->area_select();
	$filed->submit();
	?>
	</form>
	<?
	if($_POST)
	{
		print_r($_POST);
		
	}
	
	}
	
	
	 
	
	
}