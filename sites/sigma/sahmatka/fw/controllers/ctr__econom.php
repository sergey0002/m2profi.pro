<?
$GLOBALS['t']['title']='Экономика';

class ctr__econom extends ctr__
{ 

 
	var $table = 'homes'; //Главная таблица
	var $key_filed = 'homes_id'; // Ключевое поле главной таблицы
	var $ctr = 'homeseditor';
	var $title = 'Объекты';
	var $title_act1 = 'Объект';
	var $title_act2 = 'Объекта';
	
	 
	  
	
	
	function act__index()
	{
  
		global $t;
		global $mysql;
		$t['h1'] = 'Экономика домов';
		
		
		
		$h = $mysql->get_arr('SELECT * FROM homes WHERE `show`="1" ');
		
		foreach($h as $k=>$v)
		{
			if($_GET['home_id'] == $v['home_id']){$st = 'style="font-weight:bold;"';}
			else{$st='';}
			
			print ' <a href="ctrind.php?ctr=econom&home_id='.$v['home_id'].'" '.$st.'>'.$v['title'].'</a> |';
		}
		print '<br/>';
		//print_r($h);
		
		if($_GET['home_id'])
		{
			$home_id = $_GET['home_id'];
			$price_summ = $mysql->get_arr('SELECT `status`,  SUM(`price`) AS total_price FROM  `apartaments` WHERE  `home_id` = "'.$home_id.'" GROUP BY  `status`;');
		
		
		
			foreach($price_summ as $k=>$v)
			{
				if( $v['status']==2|| $v['status']==0)
				{
					$new_arr[2] = $new_arr[2] + $v['total_price'];
				}
				else
				{
					// Брони продажи итп
					$new_arr[3] = $new_arr[3] + $v['total_price'];
				}
			 
			}
			
			print '<br><br>';
			
			print '<h2>Текущие данные по дому</h2>';
			
			print '<br><br>';
			
			print unit_phrase('free_sum') . ' : <b>'.number_format( $new_arr[2], 0, '', ' ').'</b><br/>';
			print unit_phrase('not_free_sum') . ' : <b>'.number_format( $new_arr[3], 0, '', ' ').'</b><br/>';
			print 'Получаем при продаже по текущим ценам с дома: <b>'.number_format( $new_arr[3]+$new_arr[2], 0, '', ' ').'</b><br/>';
			
			
			if(!$_GET['my_summ']){$_GET['my_summ'] = $new_arr[3]+$new_arr[2];}
			
			?>
			<br/><br/>
			<form action="ctrind.php" method="GET">
				<input type="hidden" name="ctr" value="<?=$_GET['ctr']?>" />
				<input type="hidden" name="act" value="<?=$_GET['act']?>" />
				<input type="hidden" name="home_id" value="<?=$_GET['home_id']?>" />
 



				Нужно получить c продаж дома: <input type="number" name="my_summ" value="<?=$_GET['my_summ']?>"  />
				<input type="submit">			
			</form>
			<br/><br/>
			<?
			if($_GET['my_summ'])
			{
				$mysumm = $_GET['my_summ'];
				print '<h2>Расчет наценки</h2>';
				
				print number_format($mysumm, 0, '', ' ') .' - '.number_format($new_arr[3], 0, '', ' ').' '  ;
				print ' = ';
				print '<b>'.number_format($mysumm-$new_arr[3], 0, '', ' ').'</b>'  ;
				
				print ' <br/>(необходимая, для выхода на целевой итог, cуммарная стоимость свободных ' . unit_label('pl_gen') . ')<br/>';
				
				
				// ( ( количество денег которое нужно получить с свободных квартир - текущая сумарная стоимость свободных квартир) / текущая сумарная стоимость свободных квартир ) * 100 = необходимая наценка в процентах которую необходимо прибавить к текущей стоимости свободных квартир 
				
				
				print '<br/>';
				
				
			
				$nacenka = (($mysumm-$new_arr[3] - $new_arr[2]) / $new_arr[2])*100;
				
				
				print '( (';
				print number_format($mysumm-$new_arr[3], 0, '', ' ');
				print ' - ';
				print number_format($new_arr[2], 0, '', ' ') ;
				print ')/';
				print  number_format($new_arr[2], 0, '', ' ') ;
				print ') * 100';
				print ' = '.$nacenka.'%';
				print '<br/>';
				
				
				print '<br/><br/>';
				print 'Необходимая наценка на свободные ' . unit_label('pl_acc') . ': <b>' . $nacenka.'%</b>';
				
				
			
				
				
				$apparts = $mysql->get_arr('SELECT * FROM apartaments WHERE `home_id` ="'.$_GET['home_id'].'" AND ( `status` = "2" OR `status` = "0" ) ');
				
				print '<br/><br/><br/><br/>';
				//print_r($apparts);
				
				
				print '<h2>' . unit_phrase('calc_prices') . '</h2>';
				// 
				print '<br>';
				print '(Цена округляется до 1000 в большую сторону)';
				print '<br/>';
				
				$my_summ = floatval($_GET['my_summ']); // Если у вас целые числа
				foreach($apparts  as $k=>$v)
				{
					$price = floatval($v['price']); // Если у вас целые числа
					
					
					
					
					$new_price = $v['price'] * (1 + $nacenka / 100);
					$rounded_price = ceil($new_price / 1000) * 1000;
					
					
					
					print unit_label_cap('nom') . ' <b>№'.$v['apartment_num'].'</b> Старая цена: <b>'.number_format($v['price'], 0, '', ' ') .'</b> новая цена <b>'. number_format( $rounded_price, 0, '', ' ').'</b> ';
					
					$r = $rounded_price - $price ;
					print ' (наценка: '. 	$r.')';
					print '<br/>';
					/*
					[apartament_id] => 23106
					[home_id] => 40
					[section_id] => 3
					[apartment_num] => 149
					[apartments] => 0
					[floor] => 8
					[price] => 7160000
					[price_m] => 0
					[area] => 67.40
					[rooms] => 2Б
					[kitchen_area] => 0
					[text] => 
					[house_adress] => 
					[adress] => 
					[plan_code] => 0
					[status] => 2
					[status2] => 0
					[status_broni_id] => 
					[status_broni_date] => 
					[date] => 
					[image_pb] => https://xdemo.m2profi.pro/sahmatka/pbplans/40/901_3_sec_2_fl_2K_67_4.svg
					[image_pb_png] => https://xdemo.m2profi.pro/svg2png/cache/sahmatka/pbplans/40/901_3_sec_2_fl_2K_67_4.png
					[plan_type] => 0
					[image] => 0
					[area2] => 0.00
					[area_t] => 0.00
					[i] => 24
					*/
					
					$itog_rounded_price = $itog_rounded_price + $rounded_price;
				}
				
				print '<br/><br/>';
			//print $itog_rounded_price;
			
			$nsz =  $itog_rounded_price - $new_arr[2];
				print unit_phrase('new_free_sum') . ': <b>'.number_format( $itog_rounded_price, 0, '', ' ').'</b> - (Наценка: <b>'.number_format( $nsz, 0, '', ' ') .'</b>)</b><br/>';
				
				print '<br/><br/>';
				
				print 'Получаем c дома, при продаже по новым ценам : <b>'.number_format( $new_arr[3]+$itog_rounded_price, 0, '', ' ').'</b> <br/>';
				
				
				$pogr = ($new_arr[3]+$itog_rounded_price) - $my_summ;
				print '(при применении наценки сумма может быть больше заданной, за счет ' . unit_phrase('round_prices') . ' до ближайшей целой тысяци)<br/>';
				print 'За счет округлений сумма больше на '.number_format(  $pogr, 0, '', ' ');
				print '<br/>';
					
					
				
				print '<br>';print '<br>';
					print '<br><br/><br><br/>ТУТ МОЖЕТ БЫТЬ БОЛЬШАЯ КНОПКА "' . unit_phrase('apply_markup');
				print '<br>';print '<br>';
			}			
			
			
		 
		}
	}
	
	
	
}