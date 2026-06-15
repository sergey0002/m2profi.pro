<?
// Фильтр с первого числа месяца до сегодня
 $firstDayOfMonth = date('Y-m-01', time());
 $yastoday = date('Y-m-d', time());
if(!$_GET[date_limit]){ $_GET[date_limit] = $firstDayOfMonth.' : '.$yastoday ; }
 
 
  
  
  
  
  
  
  
  function object_menux_s($action='objects')
	{
		$sa = $GLOBALS['sa'];
		$h = $sa->get_homes_arr();
		
		//print_r($h);
		 ?>
	
				<?
				foreach($h as $k=>$v)
				{
					if(isset($_GET['sdan']))
					{
						if($_GET['sdan']==1){if($v['complite']=="0"){continue;}}
						elseif($_GET['sdan']==0 ){if($v['complite']=="1"){continue;}}
					}
					
					if( $_GET['home'] == $v['home_id'] )
					{
						$class='  class="mdef mdefth " ';  
					}
					else
					{ 
						$class='  class="mdef mdef" '; 
						if($v['show']==2){ $class='   class="mdef mdefa"     ';  }// ТОлько админам
						elseif($v['show']==3){ $class='   class="mdef mdefaop"     ';  } // Админам и отделу продаж
			 
						else{$class='  class="mdef"   '; }
						//$class.='" ';
					}
					
					
					?>
					
					<li style="padding:0;"><a href="user.php?action=<?=$action?>&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <?=$class?> ><?=$v['title']?></a> </li>
					<?
				}
				?>  
				
				
			<?
		  
		 
	}
  
  
  
  
  
  
  
	
	function object_menu_s($action='stat_salen2')
	{
			$h_arr = $GLOBALS['sa']->get_homes_arr();
		?>
<style>
@media screen and (min-width: 1000px) {
  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
  .mobilenav{display:none;}
}
@media screen and (max-width: 1000px) {
  .mmenu{	display:none;		}
  .mobilenav{display:block; width:100%;}
  .nomobile{display:none;}
}
</style>
<script src="fancybox-3.0/dist/jquery.fancybox.min.js"></script>
<link rel="stylesheet" type="text/css" href="fancybox-3.0/dist/jquery.fancybox.min.css">

 <script type="text/javascript">
 if( window.innerWidth >= 1000 ){
	 $(document).ready(function() {
     $("a.iframe").fancybox({
            maxWidth    : 600,
            maxHeight   : 600,
            
            width       : '1000px',
            height      : '70%',
            closeClick  : true,
 	 
 	'scrolling' : 'yes',

 afterClose: function () { // USE THIS IT IS YOUR ANSWER THE KEY WORD IS "afterClose"
       // parent.location.reload(true);
    },
	beforeLoad: function() {   
            if (this.width = $(this.element).attr('width')) {this.maxWidth = $(this.element).attr('width');} else {this.width = '800';}
            if (this.height = $(this.element).attr('height')) {this.maxHeight = $(this.element).attr('height');}    else {this.height = '100%';}
            },
            type : 'iframe',
            openEffect : 'elastic',
            closeEffect : 'elastic',
            arrows : false,
            closeClick : false,
            scrolling: 'auto',
            fitToView    : true,
            autoSize: true,
            //width: 300, // Вот отсюда я вытащил размеры
            //height: 200, //

            margin      : [10, 10, 10, 10],
            padding:    [39, 10, 10, 10],
            helpers : {
                overlay : {closeClick : false},
                title    : {type : 'inside_top' },
            }
			
        });
 });
 
 
 
 } else {
      //не выполнять
 } 
 </script>
			 <div style="width:100%; margin-bottom:10px;">
				<a href="user.php?action=<?=$action?>&sdan=3" class="mdef <? if($_GET['sdan']==3){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">ВСЕ</a>
				<a href="user.php?action=<?=$action?>&sdan=0" class="mdef <? if(!$_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 12px; font-weight:bold; ">СТРОЯЩИЕСЯ</a> 
				<a href="user.php?action=<?=$action?>&sdan=1" class="mdef <? if($_GET['sdan']==1){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">СДАННЫЕ</a>
			 </div>
			 
			 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header"    >
			
			 <br/>
			 <ul class="mmenu">
		<?
		
		object_menux_s($action);
		$class=' class="mdef" ';
		if( $_GET['action'] == $action )
		{
			$class='  class="mdef mdefth " ';  
		}
		?>
			</ul>
			
			
			

			
		 	          <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select"  >
						<div class="objects-head-nav__select"  >
						 
							<select  name="url" onChange="document.autosubmit_select.submit();" style="width:100%;  text-align: left; border-radius:0; ">
							<?
								?><option value="/sahmatka/user.php?action=<?=$action?>&sdan=<?=$_GET['sdan']?>">Выбрать дом</option><?
							foreach($h_arr as $k=>$v)
							{
								if(isset($_GET['sdan']))
								{
									if($_GET['sdan']==1){if($v['complite']=="0"){continue;}}
									elseif($_GET['sdan']==0 ){if($v['complite']=="1"){continue;}}
								}
								?><option value="/sahmatka/user.php?action=<?=$action?>&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <? if($v['home_id']==$_GET['home']){ print ' selected="selected" ';}?>><?=$v['long_title']?></option><?
							}
							?>
							
							</select>
							
						</div>
					 	</form>
						
						
						
						
		</div>			
		
		<hr style="margin-top: 12px; " class="nomobile"/>			
		<?
		
	}
?>
 			
<?	 
ob_start();


 



 
// print_r($_REQUEST); sdan
 
$home_id = $_REQUEST['home'];


   
$sql='SELECT *, round(sum(apartaments.area),2) as summ_area, sum(apartaments.price) as summ_price,  REGEXP_SUBSTR(apartaments.rooms,"[0-9]+") as roomsx, count(apartaments.apartament_id) as c  FROM  apartaments  
LEFT JOIN homes ON homes.home_id = apartaments.home_id
WHERE ( `apartaments`.`status2` != "3" /*Все  не проданные квартиры*/
) AND rooms>0 AND  (homes.show>0 ) ';

if($_REQUEST['sdan']=='0')
{
	$sql.= " AND homes.complite='0' ";
}
elseif($_REQUEST['sdan']==1)
{
	$sql.= " AND homes.complite='1' ";
}
elseif( $_REQUEST['sdan']=='3'  )
{
	$sql.= "  ";
}






if($home_id)
{
	$sql.= " AND apartaments.home_id=".$home_id." ";
}
$sql.= " GROUP BY ";

if($home_id)
{
	
	$sql.= " apartaments.home_id, ";
}
 


$sql.= " roomsx; ";
  
 
$query = mysqli_query($GLOBALS['connection'], $sql); 
 
 
?> <table ><?
 
?>
<tr>
<th> к </th>
<th> Количество квартир </th>
<th> Суммарная площадь </th>
<th> Суммарная стоимость</th>
<th> Средняя стоимость м<sup>2</sup></th>
</tr>
<?
//цикл по строкам (комнат)
while($r = mysqli_fetch_ASSOC($query))
{	
	?>
	<tr>
	<td><?=$r[roomsx]?></td>
	<td><?=$r[c]?></td>
	<td><?=number_format($r[summ_area], 2, ',', ' ') ?></td>
	<td><?=number_format($r[summ_price], 2, ',', ' ')?></td>
	<td><?=number_format(round($r[summ_price]/$r[summ_area],2), 2, ',', ' ')?></td>
	</tr>
	<?
	
	$summ_arr[c] = $summ_arr[c] + $r[c];
	$summ_arr[area] = $summ_arr[area] + $r[summ_area];
	$summ_arr[price] = $summ_arr[price] + $r[summ_price];
	$avg_metr[]=$r[summ_price]/$r[summ_area]; 
}

 
 // средняя стоимость метра
 $avg_metr_ = array_sum($avg_metr)/count($avg_metr);
  ?>
  
  
  <tr>
	<td></td>
	<td><b><?=number_format($summ_arr[c], 0, ',', ' ') ?> </b></td>
	<td><b><?=number_format($summ_arr[area], 2, ',', ' ') ?> м<sup>2<sup></b></td>
	<td><b><?=number_format( $summ_arr[price], 2, ',', ' ') ?></b></td>
	<td><b><?=number_format( $avg_metr_, 2, ',', ' ') ?></b></td>
	</tr>
  
  </table> 
  <?
  $table=ob_get_clean();
  ?>
  
  
 

<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Сводная статистика</div>
		</div>

		<div class="stat">
			<div class="stat-top">
				<div class="stat-top-filter">
					<div class="stat-top-items">
 				 
						<? object_menu_s('stat_salen2');?>
					</div>
					<div class="stat-top-btns">
						 
						<a href="JavaScript:window.print();" class="stat-top__print"></a>
					</div>
				</div>
			</div>
			<div class="stat-table stat-table-agency table">
			
			 <?=$table?>
			 
			</div>

		</div>

	</div>
</section> 

 
 
 
 
 
 
  