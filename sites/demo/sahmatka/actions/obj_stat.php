<?
// Все квартиры с бронями пользователлями и агентствами 
$sql="select 
  apartaments.home_id, homes.show, 
  count(apartaments.apartment_num) as c, 
  COUNT(
    IF(apartaments.status2 = 0, 1, NULL)
  ) as cn, 
  COUNT(
    IF(apartaments.status2 = 1, 1, NULL)
  ) as c1, 
  COUNT(
    IF(apartaments.status2 = 2, 1, NULL)
  ) as c2, 
  COUNT(
    IF(apartaments.status2 = 3, 1, NULL)
  ) as c3, 
  COUNT(
    IF(apartaments.status2 = 4, 1, NULL)
  ) as c4, 
  COUNT(
    IF(apartaments.status2 = 5, 1, NULL)
  ) as c5, 
  COUNT(
    IF(apartaments.status2 = 6, 1, NULL)
  ) as c6 ,

IF(homes.show>0, 1, NULL) as sh ,
homes.title

from 
  apartaments 
  LEFT JOIN homes on ( homes.home_id = apartaments.home_id )
 
WHERE 
  apartaments.home_id != 18 
  AND apartaments.home_id != 19 
  AND homes.show>0 /*Где то не бьется 5 квартир по старым домам хз где*/
group by 
  apartaments.home_id
  
  order by sh DESC,  title
";

$query = mysqli_query($connection, $sql);
while ($result = mysqli_fetch_array($query)) 
{
	$row=array();
	$row[1]=$result['c1'];
	$row[2]=$result['c2'];
	$row[3]=$result['c3'];
	$row[4]=$result['c4'];
	$row[5]=$result['c5'];
	$row[6]=$result['c6'];
	 
	$row['c']=$result['c'];
	$row['home_id']=$result['home_id'];
	$row['show']=$result['show'];
	$arr[]=$row;
	
	//[статуc][дом]=количество
	
	#суммы по домам
	$new_arr[3]['all'] = $new_arr[3]['all'] + $result['c3'];
	$new_arr[4]['all'] = $new_arr[4]['all'] + $result['c4'];
	$new_arr[5]['all'] = $new_arr[5]['all'] + $result['c5'];
	$new_arr[6]['all'] = $new_arr[6]['all'] + $result['c6'];
	$new_arr['c']['all'] = $new_arr['c']['all'] + $result['c'];
	
	
	
	#отделные дома
	// Свободные квартиры
	$new_arr[2][$result['home_id']] = $result['c'] - $result['c3'] - $result['c4'] - $result['c5'] - $result['c6'] ;
	$new_arr[3][$result['home_id']] = $result['c3'];
	$new_arr[4][$result['home_id']] = $result['c4'];
	$new_arr[5][$result['home_id']] = $result['c5'];
	$new_arr[6][$result['home_id']] = $result['c6'];
	$new_arr['c'][$result['home_id']] = $result['c'];
	
	 $new_arr;
	 
	 $homesn[ $result['home_id']]['caption'] = $result['title']; // заголовки домов
	
}	

$apart_status[0]='';
$apart_status[1]='Нет данных';
$apart_status[2]='Свободна';
$apart_status[3]='Продана';
$apart_status[4]='Бронь агента';
$apart_status[5]='Квартира застройщика';
$apart_status[6]='Квартира подрядчика';

// Свободные по всем домам
$new_arr[2]['all'] = $new_arr['c']['all']-$new_arr[3]['all']-$new_arr[4]['all']-$new_arr[5]['all']-$new_arr[6]['all'] ;

print '<pre>';
//print_r( $new_arr );
print '</pre>';
$homes = $homesn;

?>

 
 

 
 
 
 
  
			<div class="sales" style="display:none;">
				<div class="sales-wrap">
					<div class="sales-item">
						<div class="sales-item__num"><?=$new_arr[2]['all']?></div>
						<div class="sales-item__status sales-item__status_green">Свободно</div>
						<div class="sales-item__info">
							<ul class="sales-item-list">
									<?
									foreach($new_arr[2] as $k=>$v){	if($homes[$k]['caption'])	{	?>	<li><?=$v ?> - <?=$homes[$k]['caption']?></li><?	}}
									?>
							</ul>
						</div>
						<a href="#" style="display:none;" class="sales-inlink">Детальная статистика</a>
					</div>
					<div class="sales-item">
						<div class="sales-item__num"><?=$new_arr[4]['all']?></div>
						<div class="sales-item__status sales-item__status_yellow">Забронировано</div>
						<div class="sales-item__info">
							<ul class="sales-item-list">
								   <?
									foreach($new_arr[4] as $k=>$v){	if($homes[$k]['caption'])	{	?>	<li><?=$v ?> - <?=$homes[$k]['caption']?></li><?	}}
									?>
							</ul>
						</div>
						<a href="#" class="sales-inlink" style="display:none;" >Детальная статистика</a>
					</div>
					<div class="sales-item">
						<div class="sales-item__num"><?=$new_arr[3]['all']?></div>
						<div class="sales-item__status sales-item__status_red">Продано</div>
						<div class="sales-item__info">
							<ul class="sales-item-list">
								    <?
									foreach($new_arr[3] as $k=>$v){	if($homes[$k]['caption'])	{	?>	<li><?=$v ?> - <?=$homes[$k]['caption']?></li><?	}}
									?>
							</ul>
						</div>
						<a href="#" class="sales-inlink" style="display:none;" >Детальная статистика</a>
					</div>
					<div class="sales-item">
						<div class="sales-item__num"><?=$new_arr[5]['all']?></div>
						<div class="sales-item__status sales-item__status_grey">Бронь <br>застройщика</div>
						<div class="sales-item__info">
							<ul class="sales-item-list">
								   <?
									foreach($new_arr[5] as $k=>$v){	if($homes[$k]['caption'])	{	?>	<li><?=$v ?> - <?=$homes[$k]['caption']?></li><?	}}
									?>
							</ul>
						</div>
						<a href="#" class="sales-inlink">Детальная статистика</a>
					</div>
					<div class="sales-item">
						<div class="sales-item__num"><?=$new_arr[6]['all']?></div>
						<div class="sales-item__status sales-item__status_blue">Бронь <br>подрядчика</div>
						<div class="sales-item__info">
							<ul class="sales-item-list">
								   <?
									foreach($new_arr[6] as $k=>$v){	if($homes[$k]['caption'])	{	?>	<li><?=$v ?> - <?=$homes[$k]['caption']?></li><?	}}
									?>
							</ul>
						</div>
						<a href="#" class="sales-inlink">Детальная статистика</a>
					</div>
				</div>
				<a href="#" class="sales-link">Детальная статистика</a>
			</div>
			
			
			
			
			
			
			
			
	 
		 
<?




ob_start();
$ccc=-1;
foreach($arr as $k=>$v)
{
	if($v['home_id'] && $homes[$v['home_id']]['caption']   )
	{
		 if($v['show']){   $cll = ''; }
		 else{   $cll = '2'; }
	?>

					<div class="stat-rooms-item<?=$cll?>"> 
						<div class="stat-rooms-item__title<?=$cll?>"><?=$homes[$v['home_id']]['caption']; ?> </div>
						 <ul class="stat-rooms-item-list<?=$cll?>">
						<li><span>Всего квартир:</span> <span><?=$v['c']?></span>
				 
							<li><span>Продано</span> <span> <?=$v[3]?></span></li>
							<li><span>Бронь агента:</span> <span> <?=$v[4]?></span></li>
							<li><span>Бронь застройщика:</span> <span> <?=$v[5]?></span></li>
							<li><span>Бронь подрядчика:</span> <span> <?=$v[6]?></span></li>
					 
							<li><span>Cвободно для бронирования агентами:</span> <span> <?=$v['c']-$v[3]-$v[4]-$v[5]-$v[6]?></span></li>
							<li><span>Квартир не продано:</span> <span> <?=$v['c']-$v[3] ?></span></li>
						</ul>
					</div>
 
	<?
	}
}
$obj_stat = ob_get_clean();
?>

 
























<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">СТАТИСТИКА <span>квартир</span></div>
		</div>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_room">
				<div class="stat-top-filter" >
					<a   href="#" class="stat-top-btn btn btn_arrow-long" style="display:none;">ДЕТАЛЬНАЯ СТАТИСТИКА<i></i></a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-total">
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$new_arr[2]['all']?></div>
					<div class="stat-total-item__btn" style="background-color: #8DFFA9; color:#000;">Свободно</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$new_arr[4]['all']?></div>
					<div class="stat-total-item__btn" style="background-color: #FEFF52; color:#000;">ЗАБРОНИРОВАНО</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$new_arr[3]['all']?></div>
					<div class="stat-total-item__btn" style="background-color: #FF8A90; color:#000;">ПРОДАНО</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$new_arr[5]['all']?></div>
					<div class="stat-total-item__btn" style="background-color: #D5E6FE; color:#000;">БРОНЬ ЗАСТРОЙЩИКА</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$new_arr[6]['all']?></div>
					<div class="stat-total-item__btn" style="background-color: #991DFB;">БРОНЬ ПОДРЯДЧИКА</div>
				</div>
			</div>
			<div class="stat-rooms">
				 <?=$obj_stat?>
 
			</div>
		</div>
	</div>
</section>

<?
