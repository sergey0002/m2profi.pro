<?
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: post-check=0,pre-check=0", false);
  header("Cache-Control: max-age=0", false);
  header("Pragma: no-cache");
 
include('config.php');
 
	
 
 

function print_hprice($home_id=5,$section_id=1)
{
		
	$sql = 'SELECT home_id, section_id, rooms, plan_code, area, price, GROUP_CONCAT(apartment_num SEPARATOR ";") as apartment_num  
	FROM `apartaments` WHERE home_id="'.$home_id.'"  
	group by  area ORDER by rooms';
	 
	$arr = array();
	$query = mysqli_query($GLOBALS['connection'], $sql); 
	while ($result = mysqli_fetch_array($query)) 
	{
		if(is_array($result) && $result['rooms'])
		{
			$arr[$result['rooms']][]=$result;
		}
	}
	 
	 $type[1] = 'Однокомнатные';
	 $type[2] = 'Двухкомнатные';
	 $type[3] = 'Трехкомнатные';
	 $type[4] = 'Четырехкомнатные';
	 $type[5] = 'Пятикомнатные';
	 
	?>
	<table border="0" cellpadding="0" cellspacing="0" class="stripy2">
	<tbody>
	<?
	foreach($arr as $k1 => $v1)
	{
		?>
		<tr style="background-color:#FFE8A1;">
			<th><b>Тип <?= unit_label('gen') ?></b></th>
			<th><b>Площадь <?= unit_label('gen') ?></b></th>
			<th><b>Стоимость <?= unit_label('gen') ?></b></th>
		</tr>
		<?
		foreach($v1 as  $k=>$v)
		{
			$aparts = explode(';',$v['apartment_num']);
			$v['apartment_num'] = str_replace(';',', ',$v['apartment_num']); // Номера квартир черз запятую
			
			?>
			<tr>
				<? if($k==0)
				{ 
					?> 
					<td align="center" rowspan="<?=count($v1);?>"><b><?=$type[$v['rooms']]?></b>
					</td>
					<? 
				}
				?>
				<td><?=$v['area']?></td>
				<td><?=$v['price']?></td>
			</tr>
			
			
			<?
		}
	}
	?>
	</tbody>
	</table>
	<?
}

print_hprice();