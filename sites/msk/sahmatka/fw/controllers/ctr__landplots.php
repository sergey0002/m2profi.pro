<?
/*

ПОЛИГОНЫ В ПВАЧИ
https://thednp.github.io/svg-path-commander/convert.html



/*
Перечет стоимости сотки в базе из цен и площади!

UPDATE landplots
SET landplots.price_area = ROUND( landplots.price/(landplots.area/100) ) 
WHERE landplots.price_area IS NULL AND price > 0 AND area > 0
*/



/*
1. КЛИКАБЕЛЬНЫМИ только участки в продаже!
(остальные серым цветом) и урать подсказки к ним!!!

2. При закрытии фенсибукс обновлять JSOON? или одну квартиру

3. Перетаскивание не клике
 

  
*/
class ctr__landplots extends ctr__
{
	var $table = 'landplots'; //Главная таблица
	var $key_filed = 'lp_id'; // Ключевое поле главной таблицы
	var $ctr = 'landplots';
    var $title = 'Участки';
 
   
	function __construct()
	{
		$this->min_home_price = 5156893; 	// МИНИМАЛЬНАЯ ЦЕНА ДОМА
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data=$data; // Сохраняем данные
			
			
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		
		/*
		Перезагружать содержимое селектов при каждой выборке по хорошему? только те которые не указаны в гет запросе?
		+ в гет запросе указывать только не нулевые!
		
		+ как то псевдонимы прикрутить к гет запросам?!
		*/
		
		// Выводимые столбцы
		$titles = array();
		
		
		if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
		{
			$titles['checkrow'] = '1';
		}
		// $titles[$this->key_filed] = 'id'; 1 019 118 000
		$titles['lp_id'] = 'ID'; 
		 
		 
	 
		$titles['num'] = 'Номер'; 
		$titles['area'] = 'Площадь';
		
		
		//$titles['htype'] = 'htype';
		//$titles['project_id'] = 'project_id';
		
	//	$titles['raion'] = 'Район';
		
		
		$titles['area_caption'] = 'Поселок';
		$titles['map_caption'] = 'Карта';
		//$titles['street'] = 'street';
			
		$titles['price_area'] = 'Цена сотки';
		$titles['price'] = 'Цена';
		$titles['status'] = 'Статус';
		// 			raion
		
		$this->display_table_exrow=0; // раскрывать строки
		 
		
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['date']=1;
		$nowrap['num'] = 1;
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order['num']='num';
	 
		$this->ajcrud_table_order=$order; 
 
	}
	
	
	function act__avitoxml()
	{
		return false;
		global $mysql;
		
		$area_id = $_GET['area_id'];
		if(!$area_id){$area_id="10";}
		
		 
		$data = $mysql->get_arr('
		SELECT landplots_area.*,landplots.* FROM landplots 
		LEFT JOIN landplots_maps ON landplots_maps.landplots_map_id = landplots.map_id
		LEFT JOIN landplots_area ON landplots_area.area_id = landplots_maps.area_id
		WHERE landplots.status = "2" AND price>0  AND area>0 AND landplots_area.area_id="'.$area_id.'"  ');
	
		?>
			<Ads formatVersion="3" target="Avito.ru">
		<?
		foreach($data as $k=>$v)
		{
			$area =  round($v['area']/100);
			?>
		<Ad>
			<Description><?=$v['caption']?>. <?=$v['avito_text']?> Продажа участка №<?=$v['num']?> площадью <?=$area?> соток.  </Description>
			<Images>
				<Image url="https://static.tildacdn.com/tild3162-6536-4432-a665-373732386633/usadbi_logo.svg"/>
			</Images>
			<Id><?=$v['lp_id']?></Id>
			<AdStatus>Free </AdStatus>
			<AvitoId></AvitoId>
			<ContactPhone>+ 7 499 390-50-00</ContactPhone>
			<Address><?=$v['adress']?></Address>
			<Longitude><?=$v['lon']?></Longitude>
			<Latitude><?=$v['lat']?></Latitude>
			<ContactMethod>По телефону и в сообщениях</ContactMethod>
			<Category>Земельные участки</Category>
			<Price><?=$v['price']?></Price>
			<OperationType>Продам</OperationType>
			<PropertyRights>Собственник</PropertyRights>
			<LandArea><?=$area?></LandArea>
			<ObjectType>Поселений (ИЖС)</ObjectType>
		</Ad>

		<?
		} 
		
		?></Ads><?
		//print '<pre>';
		//print_r($data);
		//print '</pre>';
	}
	
  
  
  
  
  
  
  
  
  
  
  
  

  
  	function act__cianxml()
	{
		global $mysql;
		
		$area_id = (int) $_GET['area_id'];
		//if(!$area_id){$area_id="10";}
		
		
		$map_id = (int) $_GET['map_id'];
		
		if(!$area_id && !$map_id ) { print 'Не указан ид поселка'; return;}
		 $sql = 'SELECT landplots_area.*,landplots.* ,landplots_maps.caption as map_caption FROM landplots 
		LEFT JOIN landplots_maps ON landplots_maps.landplots_map_id = landplots.map_id
		LEFT JOIN landplots_area ON landplots_area.area_id = landplots_maps.area_id
		WHERE landplots.status = "2" AND price>0  AND area>0 ';
		
	
	
		if($area_id){
			$sql.= ' AND landplots_area.area_id="'.$area_id.'" '; 
		}
		elseif($map_id){
			$sql.= ' AND landplots_maps.landplots_map_id="'.$map_id.'" '; 
		}
		
		//print $sql;
		$data = $mysql->get_arr($sql);
		
		
		//print_r($data);
		?>
		<feed>
		<feed_version>2</feed_version>
 		<?
		foreach($data as $k=>$v)
		{
			$area =  round($v['area']/100);
			
			if($map_id){$caption = $v['map_caption'];}
			else{$caption = $v['caption']; }
			?>
   <object>
    <Category>landSale</Category>
    <ExternalId><?=$v['lp_id']?></ExternalId>
    <Description><?=$caption?>. <?=$v['avito_text']?> Продажа участка №<?=$v['num']?> площадью <?=$area?> соток. </Description>
    <Address><?=$v['adress']?></Address>
    <Coordinates>
      <Lat><?=$v['lat']?></Lat>
      <Lng><?=$v['lon']?></Lng>
    </Coordinates>
    <CadastralNumber><?=$v['kadastrnum']?></CadastralNumber>
    <Phones>
      <PhoneSchema>
        <CountryCode>+7</CountryCode>
        <Number>4993905000</Number>
      </PhoneSchema>
    </Phones>
 
    <SettlementName><?=$caption?></SettlementName>
    
    <HasElectricity>true</HasElectricity>
    <HasWater>true</HasWater>
    
    <HasGas>true</HasGas>
    <HasDrainage>true</HasDrainage>
  
    
    <Title><?=$caption?>. <?=$v['avito_text']?> Продажа участка №<?=$v['num']?> площадью <?=$area?> соток.  </Title>
    <Land>
      <Area><?=$area?></Area>
      <AreaUnitType>sotka </AreaUnitType>
      <PermittedLandUseType>individualHousingConstruction </PermittedLandUseType>
      <LandCategory>settlements</LandCategory>
    </Land>
    <Gas>
      <Type>main</Type>
    </Gas>
    <Drainage>
      <Type>septicTank</Type>
    </Drainage>
    <Water>
      <SuburbanWaterType>central</SuburbanWaterType>
    </Water>
  
    <PublishTerms>
      <Terms>
        <PublishTermSchema>
          <IgnoreServicePackages>true</IgnoreServicePackages>
        </PublishTermSchema>
        <PublishTermSchema>
          <IgnoreServicePackages>true</IgnoreServicePackages>
        </PublishTermSchema>
      </Terms>
      <PromotionType>noPromotion</PromotionType>
    </PublishTerms>
 
    <BargainTerms>
      <Price><?=$v['price']?></Price>
      <Currency>rur</Currency>
    </BargainTerms>
  </object>
  
  
		<?
		} 
		
		?></feed><?
		//print '<pre>';
		//print_r($data);
		//print '</pre>';
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
 // БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		// ПРиоритетно ставим гет переменные
		foreach($_GET as $k=>$v)
		{
			// $filtr_data[$k]=$v;
		}
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.*   , 
		landplots_maps.caption as map_caption,
		landplots_area.caption as area_caption ,
		
		ROUND( landplots.price/(landplots.area/100) ) as calc_price_area
		 
		FROM  '.$this->table.'  
		
		
		LEFT JOIN landplots_maps ON landplots.map_id = landplots_maps.landplots_map_id 
		
		LEFT JOIN landplots_area ON  landplots_area.area_id = landplots_maps.area_id 
		';
		 
		 
		$q.='  WHERE 1=1 AND num >0';
		
		if(!$_GET['show_dell']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		else{$q.=' AND `'.$this->table.'`.`del`="1" ';}
		
		if($filtr_data['id']){	$q.=' AND `'.$this->table.'`.`'.$this->key_filed.'`="'.$filtr_data['id'].'" ';	}
		
		if( $filtr_data['price'] )
		{
			$q.=' AND landplots.price = "'.$filtr_data['price'].'" ';  
		}
		
		if( $filtr_data['area_id'] )
		{
			$q.=' AND landplots_area.area_id = "'.$filtr_data['area_id'].'" ';  
		}
		
		if( $filtr_data['map_id'] )
		{
			$q.=' AND landplots.map_id = "'.$filtr_data['map_id'].'" ';  
		}
		
		
		 
		
		
		
		
		if( $filtr_data['price_area'] )
		{
			$q.=' AND landplots.price_area = "'.$filtr_data['price_area'].'" ';  
		}
		
		
		
		if( $filtr_data['raion'] )
		{
			$q.=' AND landplots.raion = "'.$filtr_data['raion'].'" ';  
		}
		
		
		
		if( $filtr_data['numbers'] )
		{
			
			$buf = explode(',',$filtr_data['numbers']);
			$str_in = '';
			
			foreach($buf as $k=>$v)
			{
				if(!trim($v)){unset($buf[$k]);}
			}
			foreach($buf as $k=>$v)
			{
				
				$str_in.='"'.$v.'"';
				if($k<count($buf)-1){$str_in.=',';}
				
			}
			$q.=' AND landplots.num IN('.$str_in.') ';  
		}
		
		  
		
		if( $filtr_data['status'] )
		{
			if($filtr_data['status'] == 0 || $filtr_data['status'] == 2 )
			{
				$q.=' AND ( landplots.status = "0" OR   landplots.status = "2" ) ';  
			}
			else
			{
					$q.=' AND   landplots.status = "'.$filtr_data['status'].'" ';  
			}
		}
		
		
		
		if( $filtr_data['min_num'] )
		{  
			$q.=' AND CAST( landplots.num as UNSIGNED) > "'.$filtr_data['min_num'].'" ';  	 
		}
		
		if( $filtr_data['max_num'] )
		{
			$q.=' AND CAST( landplots.num as UNSIGNED) > "'.$filtr_data['max_num'].'" ';  
		}
		
		if( $filtr_data['order_filed'] )
		{
			$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				$q.=' ORDER BY CAST( landplots.num as SIGNED INTEGER) '; 
				$q.='  ASC ';  
			
		}
		
		
		
		// if($_GET['id']){$q.=''}
		 // print $q;
		return $q;
	}
	
	
	function ajcrud_filtr()
	{
		global $gl_raion;
		global $filed;
		global $mysql;
	
		$gl_area = $mysql->get_select_data('SELECT * FROM landplots_area WHERE  landplots_area.show>0','area_id','caption','все');
		
		
		$gl_map = $mysql->get_select_data('SELECT * FROM landplots_maps WHERE `landplots_maps`.`show`>"0" ','landplots_map_id','caption','все');
		
		
		$price_area = $mysql->get_select_data('SELECT * FROM landplots WHERE `landplots`.`map_id`="'.$_GET['map_id'].'" ','price_area','price_area','все');
		
		//$lp_group =  $mysql->get_select_data('SELECT * FROM landplots WHERE `landplots`.`map_id`="'.$_GET['map_id'].'" ','price_area','price_area','все');
		
		
		//print_r($price_area);
		?>
			 
		
		
		
		<?
		if($_SESSION['sh_login'] == 'admin' && 1==2 )  
		{
			?>
			<div class="filter-item"  > 
			<?	
			$this->filtr_select('Агентство','agency_id','caption');	
			?>
			</div>	
			<? 
		}
		?>
		
			<div class="filter-item" > 
				 <?	 $filed->select('area_id', 'Поселок', $gl_area,   '', $style = 'text-transform:none; height:auto;'); ?>
			</div> 
			<div class="filter-item" style="display:none;" >  
				 <?	 $filed->select('map_id', 'Карта', $gl_map,   '', $style = 'text-transform:none; height:auto;'); ?>
			</div>
	
			<div class="filter-item"  style="display:none;"> 
				 <?	 $filed->select('raion', 'район', $gl_raion,   '', $style = 'text-transform:none; height:auto;'); ?>
			</div> 
			
			<div class="filter-item"  style="display:none;"> 
				 <?	 $filed->select('price_area', 'Цена за сотку', $price_area, '', $style = 'text-transform:none; height:auto;'); ?>
			</div> 
			
			
			<?
		if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem'  )  
		{
			?>
			<div class="filter-item"  > 
			<span class="input_title">Номера через запятую</span>
			<input type="text"   name="numbers" class="input_edit" value="" placeholder="">	
			</div>		
			
			 
			<?
				}
		?>
		
		
		
			
		<?
		if($_SESSION['sh_login'] == 'admin' && 1==2 )  
		{
			?>
			<div class="filter-item"  > 
			<span class="input_title">Мин №</span>
			<input type="text"   name="min_num" class="input_edit" value="" placeholder="">	
			</div>		
			
			<div class="filter-item"  > 
			<span class="input_title">Max №  </span>
			<input type="text"   name="max_num" class="input_edit" value="" placeholder="">	
			</div>
			<?
				}
		?>
		
		
		
			<?
			
			if( $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
			?>
			<div class="filter-item"> 
				<span class="input_title">Статус</span>
				<select name="status" class="input_edit" style="text-transform:none; height:auto;">
					<option value="0" selected="selected">Не задан </option>
					<option value="2">Свободен</option>			
					<option value="3">Продан </option>
					<option value="4">Забронирован </option>
					<option value="5">Забронирован застройщиком </option>
					<option value="6">Участок подрядчика </option>
					<option value="7">Скоро в продаже</option>
				</select>
			</div> 
			<?
			}
			?>
			
			<div class="filter-item filter-item-checkbox" style="display:none;"> 
				<input type="checkbox"    id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
			</div>
		<?
	}
	
	
	# Удаление записи (пометка)
	function act__del()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['del'] = 1;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	// JSOON данные всех участков 
	function act__jsoondata()
	{
		 
		
		global $mysql;
		$map_id = (int) $_GET['map_id'];
		
		if( $map_id ){$where = ' WHERE map_id="'.$map_id.'" ';}
		else{$where = '';}
		
		$arr = $mysql->get_arr('SELECT landplots.* , ROUND( landplots.price/(landplots.area/100) ) as calc_price_area FROM landplots '.$where.' ',1,'polygon_id');
		
		
		 
		$prices = array();
		foreach($arr as $k=>$v) 
		{
			
			if(!$v['price_area'])
			{
				$area_sot = $v['area']/100;
				$price_area = $v['price'] / $area_sot;
			}
			else{$price_area = $v['price_area'];}
			$prices[$price_area]++;
		}


		$colors[] = '#000';
		$colors[] = 'red';
		$colors[] = 'blue';
		
		$i=-1;
		foreach($prices as $k=>$v)
		{
			$i++;
			$price_color[$k] = $colors[$i];
		}
			
			
			
			$broni_status_arr=array();
			$broni_status_arr[0]='Не задан';
			$broni_status_arr[2]='Свободен';
			$broni_status_arr[3]='Продан';
			$broni_status_arr[4]='Забронирован';
			$broni_status_arr[5]='Бронь Усадьбы';
			$broni_status_arr[6]='Участок подрядчика';					
			$broni_status_arr[7]='Скоро в продаже';
 
 
 
			$broni_status_color_arr=array();
			$broni_status_color_arr[0]='#8DFFA9';
			$broni_status_color_arr[2]='#8DFFA9';
			$broni_status_color_arr[3]='#FF8A90';
			$broni_status_color_arr[4]='#FEFF52';
			$broni_status_color_arr[5]='#87cefa';
			$broni_status_color_arr[6]='#991DFB';		
			$broni_status_color_arr[7]='#EEE';	
			
			
			
			
			$broni_status_arr2=array();
			$broni_status_arr2[0]='Не задан';
			$broni_status_arr2[2]='Свободен';
			$broni_status_arr2[3]='Продан';
			$broni_status_arr2[4]='Забронирован';
			$broni_status_arr2[5]='Продан';
			$broni_status_arr2[6]='Продан';					
			$broni_status_arr2[7]='Скоро в продаже';
			
			
			if($_SESSION['sh_login'] == 'admin'  )
			{
				$broni_status_arr2 = $broni_status_arr;
			}
			
			
			$broni_status_color_arr2=array();
			$broni_status_color_arr2[0]='#8DFFA9';
			$broni_status_color_arr2[2]='#32CD32';
			$broni_status_color_arr2[3]='#FF8A90';
			$broni_status_color_arr2[4]='#FFD700';
			$broni_status_color_arr2[5]='#FF8A90';
			$broni_status_color_arr2[6]='#FF8A90';		
			$broni_status_color_arr2[7]='#EEE';	
			
			
			
			
		foreach($arr as $k=>$v) 
		{
			
			if( $v['tmp_hide']=="1" )
			{
				$v['status'] = '7'; // Временно скрытые с созранением оригинального статуса
				$arr[$k]['status']='7';
			}
			
			
			//$price = ($v['area']/100)*600000;
			
			$price = $v['price'];
			$price = number_format($price, 0, ' ', ' ');
			
			
			if(!$v['price_area'])
			{
				$area_sot = $v['area']/100;
				$price_area = $v['price'] / $area_sot;
			}
			else{$price_area = $v['price_area'];}
			
			$price_area = number_format($price_area, 0, ' ', ' ');
			
			
			//$v['adress'] = 'ул.Центральная д.28';
			//$img_home = 'img/projects/1.png';
			
			
			$home_price = (int) $v['price'] + (int) $this->min_home_price;
			$home_price = number_format($home_price, 0, ' ', ' ');
			
			if( $_SESSION['sh_login'] == 'admin' )
			{
				$v['text'] = '';
			
			    $v['text'].='Статус:   <b style="color:'.$broni_status_color_arr2[$v['status']].'">'.$broni_status_arr2[$v['status']].' </b><br/>';
					
				if($v['area']){ $v['text'].='Площадь участка <br/><b>'.$v['area'].' м<sup>2</sup></b>';}
				if($price){ $v['text'].='<br/> Стоимость участка: <br/><b>'.$price.' руб  </b>';}
					
				// $v['price_area'] = $v['price_area']/1000;
				if($price_area){ $v['text'].='<br/>Стоимость сотки:<br/> <b>'.$price_area.' руб  </b>';}
			  
				if($home_price){ $v['text'].='<br/>Стоимость лота от:<br/> <b>'.$home_price.' руб  </b>';}
				
			}
			else // НЕ АДМИН
			{
				$v['text'] = '';
				if($v['status'] == '7')
				{
					$v['text'].='<b>Скоро в продаже ' .$v['tmp_hide'].'</b><br/>';
					if($v['area']){ $v['text'].='Площадь участка <br/><b>'.$v['area'].' м<sup>2</sup></b>';}
				}
				else
				{
					
				 
					 $v['text'].='Статус:   <b style="color:'.$broni_status_color_arr2[$v['status']].'">'.$broni_status_arr2[$v['status']].' </b><br/>';
					
					if($v['status'] == '2' || !$v['status'])
					{
						if($v['area']){ $v['text'].='Площадь участка <br/><b>'.$v['area'].' м<sup>2</sup></b>';}
						
						if($home_price){ $v['text'].='<br/>Стоимость лота от: <br/><b>'.$home_price.' руб  </b>';}
						//if($price){ $v['text'].='<br/>Дом от : <br/><b>'.$home_price.' руб  </b>';}
						
						// $v['price_area'] = $v['price_area']/1000;
						//if($price_area){ $v['text'].='<br/>Стоимость сотки:<br/> <b>'.$price_area.' руб  </b>';}
					}
				}
			}
			
			 
			  
			
			if($_SESSION['sh_login'] != 'admin' &&  $_SESSION['sh_login'] != 'goodzem' && $_SESSION['agency_id'] != "92")
			{
				if($v['status']==5 || $v['status']==6)
				{
					$arr[$k]['status']='3';
				}
				
			}
			if($v['status']==0  )
			{
				$arr[$k]['status']='2';
			}
				
			
 
 //<br/> Площадь участка<br/><b>'.$v['area'].'м<sup>2</sup></b>
 //<span class="tt_text">'.$v['adress'].'</span>
 
 
			$arr[$k]['map_id']=$v['map_id']; // ИД ОБЕКТА (КАРТЫ)
 
 
			$arr[$k]['status_text']=$broni_status_arr[$arr[$k]['status']];
			$arr[$k]['status_color']=$broni_status_color_arr[$arr[$k]['status']];
			$arr[$k]['tooltip']='
			<span class="tt_title" style="display:block;"> <b>Участок № '.$v['num'].'</b></span>
			
			 
			<span class="tt_text" style="line-height:1.5em;"> '.$v['text'].'</span>
			';
			
			if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
				$arr[$k]['class']='insale'; // В продаже! (для не даминов только участки свободные)
			}
			else
			{
				if($arr[$k]['status'] == 2)
				{
				$arr[$k]['class']='insale'; // В продаже! (для не даминов только участки свободные)
				}
				else
				{
					$arr[$k]['class']='noinsale'; // В продаже! (для не даминов только участки свободные)
				}
			}
		}
		print json_encode($arr);
	}
	
	### ДЕЙСТВИЯ КОНТРОЛЛЕРА
	
	
 
	
	

	function act__broni()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{
			$filtr = array();
			$filtr['id'] = $id;
			$data = $mysql->get_arr($this->get_base_sql($filtr));
			$data=$data[0];
			 
		}
		else
		{
			print 'Не указан объект';
			return;
		}
		$this->tpl($data,'parking_spaces','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
		
		if($_POST)
		{
			 
			// Обработка формы
		}
  
	}
	
	
	function objects_menu()
	{
			 return;
		?>
 
 
			 <div style="width:100%; margin-bottom:10px;">
				<a href="user.php?action=<?=$action?>&sdan=0" class="mdef <? if(!$_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 12px; font-weight:bold; ">СТРОЯЩИЕСЯ</a> 
				<a href="user.php?action=<?=$action?>&sdan=1" class="mdef <? if($_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">СДАННЫЕ</a>
			 </div>
			 
			 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header"    >
			
			 <br/>
			 <ul class="mmenu">
		<?
		
		//object_menux($action);
		$class=' class="mdef" ';
		if( $_GET['action'] == 'objects2' )
		{
			$class='  class="mdef mdefth " ';  
		}
		?>
		
		<?
		if(($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem' || $_SESSION['sh_login'] == 'fd'  ) ||1==1)
		{
			if($_GET['sdan'])
			{
				
				foreach($GLOBALS['custom_apparts_all'] as $k=>$v)
				{
					?>
					<li style="padding:0; "><a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="mdef m2catalog_item_order iframe"><?=$v['homecaption']?></a></li>
					<?
				}
				 
			?>
			 <li style="display:none; padding:0; "><a href="user.php?action=objects2&sdan=<?=$_GET['sdan']?>" <?=$class?>>Другие</a></li>
			
			<?
			}
		}
		?>
			</ul>
		 
 
		 
		 	          <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select"  >
						<div class="objects-head-nav__select"  >
						 
							<select  name="url" onChange="document.autosubmit_select.submit();" style="width:100%;  text-align: left; border-radius:0; ">
							<?
								?><option>Выбрать дом</option><?
							foreach($h_arr as $k=>$v)
							{
								if(isset($_GET['sdan']))
								{
									if($_GET['sdan']){if($v['complite']=="0"){continue;}}
									else{if($v['complite']=="1"){continue;}}
								}
								?><option value="/sahmatka/user.php?action=objects&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <? if($v['home_id']==$_GET['home']){ print ' selected="selected" ';}?>><?=$v['long_title']?></option><?
							}
							?>
							<?
							if($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
							{
								if(isset($_GET['sdan']))
								{
								?>
							 
								<option value="/sahmatka/form_order_custom.php?custom_home_id=101&custom_appart_id=1">Свечникова, 4/1</option>
								<?
								}
							}
							?>
							</select>
							
						</div>
					 	</form>
 		
		</div>			
		
		<hr style="margin-top: 12px; " class="nomobile"/>
		
	
						
						
						
		<?
	}
	
	
	
	

 
	
	function act__calcbuilding($lp_id)
	{



?>

<input type="hidden" id="lp_id" value="<?=$lp_id?>" />

<div class="input_title form_text">Проект дома</div>
<select id="building-select"></select>
<br/> 
<div class="input_title form_text">Комплектация</div>
<select id="compl-select"></select>
<br/>
<div id="dop-options"></div>
<div id="price-display" style=" font-size: 18px; font-weight: bold; padding-top: 10px;"></div>
<script>
class PriceCalculator {
    constructor() {
        // Элементы формы и URL для запросов
        this.$buildingSelect = $('#building-select');
        this.$complSelect = $('#compl-select');
        this.$dopOptions = $('#dop-options');
        this.$priceDisplay = $('#price-display');
        this.ajaxUrl = 'https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=get_buildings';

        // Значение скрытого поля
        this.lpId = $('#lp_id').val();

        // Инициализация
        this.init();
    }

    init() {
        // Загрузка строений и установка обработчиков событий
        this.loadBuildings();
        this.setupEventHandlers();
    }

    setupEventHandlers() {
        // Обработчики изменений
        this.$buildingSelect.on('change', () => this.loadCompletionsAndExtras());
        this.$complSelect.on('change', () => this.calculatePrice());
        this.$dopOptions.on('change', '.dop-checkbox', () => this.calculatePrice());
    }

    setLoadingState(isLoading) {
        if (isLoading) {
            // Блокируем элементы и показываем индикатор загрузки
            this.$buildingSelect.prop('disabled', true);
            this.$complSelect.prop('disabled', true);
            this.$dopOptions.find('.dop-checkbox').prop('disabled', true);
            this.showLoading();
        } else {
            // Разблокируем элементы
            this.$buildingSelect.prop('disabled', false);
            this.$complSelect.prop('disabled', false);
            this.$dopOptions.find('.dop-checkbox').prop('disabled', false);
        }
    }

    // Метод для загрузки строений
    loadBuildings() {
        this.setLoadingState(true);
        $.getJSON(this.ajaxUrl, { action: 'get_buildings', lp_id: this.lpId })
            .done((data) => {
                if (data.error) {
                    this.showError('Ошибка при загрузке строений.');
                } else {
                    this.$buildingSelect.empty();
                    $.each(data, (index, building) => {
                        this.$buildingSelect.append($('<option>', {
                            value: building.building_id,
                            text: building.caption
                        }));
                    });
                    this.loadCompletionsAndExtras();
                }
            })
            .fail(() => {
                this.showError('Ошибка при загрузке строений.');
            })
            .always(() => {
                this.setLoadingState(false);
            });
    }

    // Метод для загрузки комплектаций и дополнительных опций
    loadCompletionsAndExtras() {
        const building_id = this.$buildingSelect.val();
        this.setLoadingState(true);
        $.getJSON(this.ajaxUrl, { action: 'get_building_compl', building_id, lp_id: this.lpId })
            .done((data) => {
                if (data.error) {
                    this.showError('Ошибка при загрузке комплектаций.');
                } else {
                    this.$complSelect.empty();
                    $.each(data, (index, completion) => {
                        this.$complSelect.append($('<option>', {
                            value: completion.building_compl_id,
                            text: completion.caption
                        }));
                    });
                    this.loadExtras();
                }
            })
            .fail(() => {
                this.showError('Ошибка при загрузке комплектаций.');
            })
            .always(() => {
                this.setLoadingState(false);
            });
    }

    // Метод для загрузки дополнительных опций
    loadExtras() {
        this.setLoadingState(true);
        $.getJSON(this.ajaxUrl, { action: 'get_buildings_dop', lp_id: this.lpId })
            .done((data) => {
                if (data.error) {
                    console.error('Ошибка при загрузке дополнительных опций:', data.error);
                    this.showError('Ошибка при загрузке дополнительных опций.');
                } else {
                    // Проверка наличия дополнительных опций
                    if (Array.isArray(data) && data.length > 0) {
                        this.$dopOptions.empty();
                        $.each(data, (index, extra) => {
                            this.$dopOptions.append(
                                `<label><input type="checkbox" class="dop-checkbox" name="dop" value="${extra.dop_id}" /> ${extra.caption} (+${extra.price})</label><br>`
                            );
                        });
                        this.$dopOptions.show();
                    } else {
                        this.$dopOptions.empty().hide();
                    }
                    this.calculatePrice();
                }
            })
            .fail(() => {
                this.showError('Ошибка при загрузке дополнительных опций.');
            })
            .always(() => {
                this.setLoadingState(false);
            });
    }

    // Метод для отображения состояния загрузки
    showLoading() {
        this.$priceDisplay.text('...расчет');
    }

    // Метод для отображения ошибки
    showError(message) {
        this.$priceDisplay.text(message || 'Ошибка расчета, повторите попытку позже');
    }

    // Метод для расчета цены
    calculatePrice() {
        this.setLoadingState(true);

        const formData = {
            action: 'calculate_price',
            building_id: this.$buildingSelect.val(),
            building_compl_id: this.$complSelect.val(),
            dop: this.$dopOptions.find('.dop-checkbox:checked').map(function() { return this.value; }).get(),
            lp_id: this.lpId
        };

        $.post(this.ajaxUrl, formData, (data) => {
            if (data.error) {
                this.showError('Ошибка при расчете цены.');
            } else if (data.total_price) {
                const formattedPrice = Number(data.total_price).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				const h_price = Number(data.h_price).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				const lp_price = Number(data.lp_price).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				const price_area = Number(data.price_area).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
			 
                
				<?
		  
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['agency_id'] == "92" )
				{
					?>
					this.$priceDisplay.html(`Участок: ${lp_price} <br/>Сотка: ${price_area} <br/> Дом: ${h_price} <br/>Стоимость лота: ${formattedPrice}`); 
					<?
				}
				else
				{
					?>
					this.$priceDisplay.html(` Стоимость лота: ${formattedPrice} `); 
					<?
				}
					
				?>
            } else {
                this.showError('Ошибка при расчете цены.');
            }
        }, 'json').fail(() => {
            this.showError('Ошибка при расчете цены.');
        }).always(() => {
            this.setLoadingState(false);
        });
    }
}

$(document).ready(() => {
    new PriceCalculator();
});

</script>



<?
	}
	 
	

 
 
 
function act__get_buildings() {
    global $mysql;

    // Получение данных из POST-запроса
    $action = $_GET['action'] ?? null;
    $building_id = $_GET['building_id'] ?? null;
    $building_compl_id = $_GET['building_compl_id'] ?? null;
    $dop_ids = $_GET['dop'] ?? [];

	$lp_id = $_GET['lp_id'] ?? false;
	
	if(!$lp_id){ echo json_encode(['error' => 'lp_id not']); }
	
	 $lp_data =$mysql->get_for_key('landplots','lp_id',$lp_id);
	 if(!$lp_data){  echo json_encode(['error' => 'lp_id not FOUND']);   }
	 $price_area = (int) $lp_data['price_area'];
 
	 $lp_price = (int) $lp_data['price'];
 
    // Обработка запроса в зависимости от типа действия
    if ($action === 'get_buildings') {
        // Получение списка зданий
        $buildings = $mysql->get_arr(' SELECT building_id, caption FROM buildings WHERE `show`="1" ');
        if ($buildings) {
            echo json_encode($buildings);
        } else {
            echo json_encode(['error' => 'Failed to retrieve buildings']);
        }

    } elseif ($action === 'get_building_compl') {
        // Получение комплектаций для указанного здания
        if (!$building_id) {
            echo json_encode(['error' => 'Missing building_id']);
            return;
        }
        $building_id = intval($building_id);
        $completions = $mysql->get_arr("SELECT building_compl_id, caption, price FROM buildings_compl WHERE building_id = {$building_id} AND `show`='1' ");
        if ($completions) {
            echo json_encode($completions);
        } else {
            echo json_encode(['error' => 'Failed to retrieve building completions']);
        }

    } elseif ($action === 'get_buildings_dop') {
        // Получение дополнительного оснащения
        $extras = $mysql->get_arr("SELECT dop_id, caption, price FROM buildings_dop WHERE `show`='1'");
        if ($extras) {
            echo json_encode($extras);
        } else {
			
			   echo json_encode(array());
           // echo json_encode(['error' => 'Failed to retrieve extras']);
        }

    } elseif ($action === 'calculate_price') {
        // Расчет общей цены на основе выбранных параметров
        if (!$building_id || !$building_compl_id) {
            echo json_encode(['error' => 'Missing building_id or building_compl_id']);
            return;
        }
        $building_id = intval($building_id);
        $building_compl_id = intval($building_compl_id);

        // Получение данных о типе здания
        $building = $mysql->get_arr("SELECT * FROM buildings WHERE building_id = $building_id");
        if (empty($building)) {
            echo json_encode(['error' => 'Building not found']);
            return;
        }

        // Получение данных о комплектации
        $completion = $mysql->get_arr("SELECT * FROM buildings_compl WHERE building_compl_id = $building_compl_id AND building_id = $building_id");
        if (empty($completion)) {
            echo json_encode(['error' => 'Completion not found']);
            return;
        }

        // Получение данных о дополнительном оснащении
        $dop_ids_list = implode(',', array_map('intval', $dop_ids));
        $extras = [];
        if (!empty($dop_ids_list)) {
            $extras = $mysql->get_arr("SELECT * FROM buildings_dop WHERE dop_id IN ($dop_ids_list)");
        }

        // Расчет общей цены
        $total_price = $completion[0]['price'];
        foreach ($extras as $extra) {
            $total_price += $extra['price'];
        }
		
		$total_price2 =$total_price+$lp_price;
        // Возврат результата   
        echo json_encode(['total_price' => $total_price2,'h_price' => $total_price, 'lp_price' => $lp_price , 'price_area' => $price_area ]);

    } else {
        // Неизвестное действие
        echo json_encode(['error' => 'Invalid action']);
    }
}
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 function translateform_home($arr) {
    // Получаем данные из POST-запроса
    $buildingId = $arr['building_id'] ?? null;
    $completionId = $arr['completion_id'] ?? null;
    $dopIds = $arr['dop'] ?? [];

    // Проверка на наличие необходимых данных
    if (!$buildingId || !$completionId) {
        return ['error' => 'Недостаточно данных для обработки'];
    }

    // Инициализация результата
    $result = [
        'building-select' => 'Неизвестное строение',
        'compl-select' => 'Неизвестная комплектация',
        'dop' => ''
    ];

    // Получение caption строения
    $buildingSql = "SELECT caption FROM buildings WHERE building_id = '{$buildingId}'";
    $buildingData = $mysql->get_arr($buildingSql);
    if (!empty($buildingData)) {
        $result['building-select'] = $buildingData['caption'];
    }

    // Получение caption комплектации
    $completionSql = "SELECT caption FROM completions WHERE building_compl_id = '{$completionId}'";
    $completionData = $mysql->get_arr($completionSql);
    if (!empty($completionData)) {
        $result['compl-select'] = $completionData['caption'];
    }

    // Получение caption дополнительных опций
    if (!empty($dopIds)) {
        $dopIdsList = implode(',', array_map('intval', $dopIds));
        $dopSql = "SELECT caption FROM dop_options WHERE dop_id IN ({$dopIdsList})";
        $dopData = $mysql->get_arr($dopSql);
        if (!empty($dopData)) {
            $dopCaptions = array_map(function($item) {
                return $item['caption'];
            }, $dopData);
            $result['dop'] = implode(', ', $dopCaptions);
        }
    }

    return $result;
}




 
	
	### ФОрма брони для админов и агентств
	function act__order()
	{
		
		global $mysql;
		global $t;
		
		$map_id = (int) $_GET['map_id'];
		if( !$map_id ){ $map_id =1; }
		if( $map_id ){$where = ' AND map_id="'.$map_id.'" ';}
		else{$where = '';}
		
		
		$t['h1']='Бронирование участка';
		
		$pid = (int) $_GET['polygon_id'];
		$data = $mysql->get_arr('SELECT * FROM `landplots` WHERE `polygon_id`="'.$pid.'" '.$where.' ',1);
		
		
		// ОБНОВЛЯТЬ ТОЛЬКО ПО НЕМУ!!!!!!!
 
		$idx = $data['lp_id']; 
		
		print '<pre>';
		
		//print_r($_GET);
		if(!$data)
		{
			//	print 'Новый участок'; 
		
		}
		else
		{
			//print 'Редактирование участка';
			// print_r($data);
		}
		print '</pre>';
		 
		 
		 
		 
		 
		
		if($_POST)
		{
			if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] =='em_nsv' ||  $_SESSION['sh_login'] == 'goodzem' ) // Админ агентства или агент
			{
 
				// print 'Обработка админской формы';

				$datax = array();
				
				 $datax['map_id']  = $map_id ;
				
				
				if( $_POST['price'] ) { $datax['price'] = $_POST['price']; }
				
				
				// пересчитать цену за сотку
				if(  $_POST['price_area'] )
				{
					 
					//$lp = $mysql->get_for_key( $this->table , $this->key_filed , $k,1 );	
					 
					$new_price = $_POST['price_area']*($data['area']/100);
					$datax['price'] = $new_price ; 
					$datax['price_area'] =  $_POST['price_area']; 
					 
				}
				
			
			
				
				if( $_POST['area'] ) { $datax['area'] = $_POST['area']; }
				//if( $_POST['street'] ) { $datax['street'] = $_POST['street']; }
				//if( $_POST['raion'] ) { $datax['raion'] = $_POST['raion']; }
				if( $_POST['htype'] ) { $datax['htype'] = $_POST['htype']; }
				
				if( $_POST['kadastrnum'] ) { $datax['kadastrnum'] = $_POST['kadastrnum']; }
				
				
				
				
				// $datax['status'] = $_POST['status']; // Статус меняется спец методом add_broni()
				$datax['num'] = $_POST['num'];
				
				//$datax['del'] = $_POST['del'];
				//if(!$datax['del']){$datax['del']=0;}
				 
				//print_r($datax);
				if($data)
				{
					$datax['polygon_id'] = $pid;
					$mysql->update_for_key('landplots','lp_id',$idx ,$datax,0);
				 	$idx = $data['lp_id']; 
					print 'Данные обновлены';
				}
				else
				{
					$datax['polygon_id'] = $pid;
					$idx = $mysql->insert('landplots',$datax);
					print 'Данные добавленны';
				}
				
				 
				if( $_POST['status'] ) 
				{
				
					$broni_idx = $this->add_broni($idx,$_POST['status']);
					$datax['status_broni_id'] = $broni_idx; 
				}
				
				// Повторно получаем обновленные данные
				$data = $mysql->get_arr('SELECT * FROM `landplots` WHERE `polygon_id`="'.$pid.'" '.$where.' ',1);
				
				
				// 
				//$data = $this->get_id_arr($_GET['id']);
				$this->tpl($data,'landplots','form_broni_ag'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
			elseif( $_SESSION['sh_login'] )
			{
				// Обработка формы бронирования
				print '<pre>';
				//print_r($_GET);
				//print_r($_POST);
				//print_r($data);
				print '</pre>';
				 
				$this->add_broni_pf($data);
			}
			else
			{
				print 'ДОступ запрещен';
				return;
			}
		}
		else
		{ 
			//$data = $this->get_id_arr($_GET['id']);
			// print_r($_GET);
			if( $_SESSION['sh_login'] && $_GET['a'] == '/sites/gl/sahmatka/ctrind.php' ) 
			{
				$this->tpl($data,'landplots','form_broni_ag'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
			else
			{
				$this->tpl($data,'landplots','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
		}
		
		// История броней
		if( $idx && ($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem') && $_GET['a'] == '/sites/gl/sahmatka/ctrind.php' )
		{ 
			$this->act__broni_history($idx);
		}
		
	}
	
	function act__jsoon_landplot()
	{
		global $mysql;
		$data = $mysql->get_arr(' SELECT * FROM landplots WHERE `polygon_id`="'.$_GET['polygon_id'].'" AND `map_id` = "'.$_GET['map_id'].'" ',1);
		
		print json_encode( $data );
		 
	}
	
	
	
	### ФОРМА НА САЙТЕ ПУБЛИЧНАЯ !!!!!!!!
	function act__public_form()
	{
		
		
		
		global $mysql;
		
		
		
		$data = $mysql->get_arr(' 
		SELECT landplots.*,landplots_area.caption FROM landplots

		LEFT JOIN landplots_maps  ON landplots_maps.landplots_map_id = landplots.map_id   
		LEFT JOIN  landplots_area ON landplots_area.area_id = landplots_maps.area_id 
		WHERE `polygon_id`="'.$_REQUEST['polygon_id'].'" AND `map_id` = "'.$_REQUEST['map_id'].'" ',1);
		 //print_r($data);
		?>	
		
		<div class="zoom-anim-dialog white-block callback-block" style="    background-color: #fff;    margin: 0 auto;    padding: 30px;    position: relative;    max-width: 570px; border-radius: 50px;">
		<?
		
		
		
		
		//print_r($data);
		?>
		<style>
/* container */
.responsive-two-column-grid {
    display:block;
}

/* columns */
.responsive-two-column-grid > * {
    padding:1rem;
}

/* tablet breakpoint */
@media (min-width:768px) {
    .responsive-two-column-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
}

.form_title{
font-family: Halvar Breitschrift;
font-size: 22px;
font-weight: 700;
line-height: 25.48px;
text-align: left;
 font-family:'Halvar',Arial,sans-serif;
}
.form_text{
font-family: Halvar Breitschrift;
font-size: 13px;
font-weight: 700;
line-height: 15.6px;
text-align: left;
 font-family:'Halvar',Arial,sans-serif;
 padding-top: 17px;
}


.ft2
{
	font-family: Halvar Breitschrift;
	font-size: 12px;
	font-weight: 700;
	line-height: 15.6px;
	text-align: left;
	color: #803545;

 font-family:'Halvar',Arial,sans-serif;
}


.fix{
	border: 1px solid #803545;
	 
	width: 90%;
 
 
	border-radius: 30px;
 font-family:'Halvar',Arial,sans-serif;
 margin-top: 17px;
 padding:17px;

}


.colorbl{
	display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 20px;
    background: #000;
    line-height: 20px;
 
}
	</style>
	
			<form id ="lpform" action ="https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=public_form">
			<div class="responsive-two-column-grid">
			
			
				<div>
				<div class="form_title">Участок: <?=$data['num']?></div>
			 
		  		<div class="form_text">Статус: 
				<?
				if($data['status']==2 || !$data['status'] )	{ print '<div class="colorbl" style="background:#8FFF67;"></div> '; print 'Свободен';	}
				elseif($data['status']==3   )	{  print '<div class="colorbl" style="background:#FF8A90;"></div> '; print 'Продан';	}
				elseif($data['status']==4 || $data['status']==5 || $data['status']==6  )	{  print '<div class="colorbl" style="background:##FEFF52;"></div> '; print 'Бронь';	}
				elseif($data['status']==7   )	{  print '<div class="colorbl" style="background:#8FFF67;"></div> '; print 'Скоро в продаже';	}
				?>
				 
				
				</div>
<div class="form_text">Посёлок: <?=$data['caption']?></div>
<div class="form_text">Площадь участка: <?=$data['area']?> м<sup>2</sup></div>

<?
/*
<div class="form_text">Стоимость: <?=$data['price']?> руб.</div>
*/
 ?>
<div class="form_text">Кадастровый номер: <?=$data['kadastrnum']?>  </div>
 
<?=$this->act__calcbuilding($data['lp_id']);?>
 
				</div>
				<div>
					<div class="ft2">Отправьте предварительную заявку на бронирование</div>
					
					 <input required name="name" type="text" class="fix" placeholder="Ваше имя*">
					 <input required name="phone"  type="tel"  class="fix" placeholder="Телефон*"> 
					 <input required name="email"  type="email" class="fix" placeholder="E-Mail*">
					 <input type="submit" class="fix" style="background-color:#803545; color:#FFF; cursor:pointer;" value="Отправить"/>
			 
					 <input type="hidden" name="polygon_id" value="<?=$_GET['polygon_id']?>">
					 <input type="hidden" name="map_id" value="<?=$_GET['map_id']?>" />
			
					 <div class="form_text">* Все поля обязательны для заполнения</div>
	 
				</div>
				
				
				
				
			
			</div>
			</form>
			
				<div id ="lpform_ok" class="form_title" style="display:none;">
				
				<div class="form_title">Ваша заявка принята. </div>
				<div class="form_text">Cпециалист свяжется с вами в ближайшее время</div>
				</div>
				
		</div>
		</div>
		<?
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Пост обработка публичной формы
	function act__order_pub()
	{
		global $mysql;
		global $t;
		$pid = (int) $_GET['polygon_id'];
		$data = $mysql->get_arr('SELECT * FROM `landplots` WHERE `polygon_id`="'.$pid.'" ',1);
		
		$idx = $data['lp_id']; 
		$this->tpl($data,'landplots','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
		
		print '<pre>';
			//print_r($data);
			//print_r($_POST);
			//print_r($_GET);
		print '</pre>';
	}
	
	
	
	// Добавление брони в базу
	function add_broni($space_id,$status)
	{ 
		global $mysql;
		
		$data_space = $mysql->get_for_key('landplots','lp_id',$space_id,1);
		 
		//  print_r($data_space);
		
		// Проверяем если не изменился пользователь и статус
		if($data_space['status'] != $status )
		{
			 
			// Записываем бронь
			$data = array();
			$data['lp_id'] = $space_id;
			$data['status'] = $status;
			$data['date'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_first'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_fu'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['broni_up_counter'] = 0; // текущая дата
			$data['comment'] = ''; // текущая дата
			$data['user_id'] = $_SESSION['sh_id'];
			
			$data['price'] = $data_space['price'];
		  
			print $broni_id = $mysql->insert('landplots_broni',$data);
			 
			
			// Обновляем статус в основной таблице
			$data = array();
			$data['status'] = $status;
			$data['status_broni_id'] = $broni_id;
	 
			$mysql -> update_for_key( 'landplots', 'lp_id', $space_id , $data );
		}
		else{$broni_id = $data_space['status_broni_id'];}
		return $broni_id;
	}
	
	
	
	
	
	function act__broni_history($space_id='',$ttitle=false)
	{
		if(!$space_id){$space_id = $_GET['id'];}
		if($_GET['nott']){$ttitle =false;}
		
		global $mysql;
		global $status_arr;
		global $status_color_arr;
	   
		$q = 'SELECT landplots.*,users.*,agency.caption as agcaption , landplots_broni.status as b_status , landplots_broni.date  , landplots_broni.lp_broni_id ,landplots_broni.price
		FROM landplots  
		LEFT JOIN landplots_broni  ON landplots_broni.lp_id =landplots.lp_id
		 
		LEFT JOIN users ON users.id =landplots_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		';
		if($space_id){ $q.=' WHERE landplots.lp_id = "'.$space_id.'" '; }
		
		$q.=' ORDER BY landplots.lp_id, landplots_broni.date DESC';
		
		$space_data = $mysql->get_arr($q );
		if(!$space_data[0]['lp_broni_id']){return;}
		// print '<pre>'; 
		//  print_r($space_data );
		// print '</pre>';
		
		
		?>
		<table class="bronihtable">
		
		<?
		if($ttitle)
		{
		?>
		<tr>
			<td><b>Дата</b></td>
			<td><b>Агентство</b></td>
			<td><b>Пользовтель</b></td>
			<td><b>Статус</b></td>
			<td><b>Цена</b></td>
		</tr>
		<?
		}
		?>
		<tbody>
		<?
		$i=0;
		foreach($space_data as $k => $v )
		{
			$i_ps=0;
			if(!$v['lp_broni_id']){continue;}
						 
			if($lp_id!=$v['lp_id'] && !$space_id)
			{
				?>
				<tr><td colspan="10" align="center">
				<?
				  if($v['lp_broni_id'] != $v['status_broni_id'] && $i_ps==0 )
				  {
					  print $v['adress_disp'].' - '.$v['num'];
					  print '<h1>Ошибка</h1>';
					  
					  if($v['b_status'] == $v['status'])
					  {
						 print '';
					  }
					  else
					  {
						   print 'Нельзя исправить   ';
					  }
				  }
				 else
				 {
					 print $v['adress_disp'].' - '.$v['num'];
					// print $v['status_broni_id'];
				 }
				
				?>
				</td></tr><?
				$lp_id = $v['lp_id'];
			}
			else // То же место следующая бронь
			{
				$i_ps++;
			}
			
			if($v['lp_broni_id'] == $v['status_broni_id']) {	 $style="font-weight:bold;";	$tb_text='(Текущий статус)'; } else{$style=""; $tb_text='';}
			?>
			<tr>
			<td style="<?=$style?>"><?=fromsql_date($v['date'])?></td>
			<td style="<?=$style?>"><?=$v['agcaption']?></td>
			<td style="<?=$style?>"><?=$v['login']?> (<?=$v['name']?>)</td>
			<td  style="<?=$style?>"><span style="background-color:<?=$status_color_arr[$v['b_status']]?>;" > <?=$status_arr[$v['b_status']]?> <?=$tb_text?></span></td>
			<td style="<?=$style?>"><?=$v['price']?></td>
			</tr>
			<?
			$i++;
		}
		?>
		</tbody>
		</table>
	 
		<?
		
	}
	
	
	
 
	
	
	
	// Обработка формы
	function add_broni_pf($data='')
	{
		global $mysql;
 
		// Получаем данные места
		$space_data = $data;
		  
		$space_id = $data['lp_id'];
		if(!$space_id){print 'Не указан id '; return;}
		
		
		// print_r($space_data); insert
		
		// Проверяем текущий статус помещения  
		if( $space_data['status']  && $space_data['status']!='2' && 1==2) 
		{
			//	if($stat!='' && $stat!='2' && $stat!='5' && $stat!='0' ){print '<h2 style="color:red">Ошибка бронирования квартира уже забронирована другим пользователем</h2>';  $err_m[]='Квартира уже забронирована другим пользователем';}
		
			print 'Ошибка - конфликт статуса бронирования - вероятно место было забронированно другим пользователем, пока вы заполняли форму';
			return;
		}
		elseif($space_data['lp_id'])
		{
			//
			
			if( !$_FILES['passport_scan']['type'] || !$_FILES['passport_scan2']['type'] || !$_FILES['anket']['type'] )
			{
				?><h2 style="color:red">Для бронирования необходимо загрузить указанные файлы</h2><?
			}
			else
			{
				//print 'Применение брони успешно';
				//print ' ID Брони: '; 
				 $new_broni_id =  $this->add_broni($space_id,4);
				 
				$dir = "uploads_landplots/$new_broni_id/";
				mkdir($dir, 0777);
				 
				 
				if($_FILES['passport_scan']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['passport_scan']['name']), '.'), 1);
						$uploadfile = $dir . basename('passport_scan'.'.'.$ext);
						$files[0] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['passport_scan']['tmp_name'], $uploadfile))
						{
							// echo "Скан паспорта 1 - Файл был успешно загружен.\n <br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Скан паспорта не был загружен';
						}
				}

				if($_FILES['passport_scan2']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['passport_scan2']['name']), '.'), 1);
						$uploadfile = $dir . basename('passport_scan2'.'.'.$ext);
						$files[1] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['passport_scan2']['tmp_name'], $uploadfile))
						{
							// echo "Скан паспорта 2 - Файл был успешно загружен.\n<br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Скан паспорта 2 не был загружен';
						}
				}

				if($_FILES['anket']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['anket']['name']), '.'), 1);
						$uploadfile = $dir . basename('anket'.'.'.$ext);
						 $files[2] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['anket']['tmp_name'], $uploadfile))
						{
							echo "Анкета- Файл был успешно загружен.\n<br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Анкета-  не был загружен';
						}
				}
				
				if($_POST && !$err_m)
				{
					?>
					<h2 style="color:#000; text-align:center;">Участок забронирован</h2>
					<p style="color:#00CDAD; font-weight:bold;; font-size:20px; text-align:center;">Срок действия брони - 10 календарных дней, по прошествии 10 дней бронь будет анулирована автоматически</h2>
					<hr/>
					<?
					
					
						$message = "Бронирование участка №".$data['num']."\r\n <br/>";
						$message .= "Заявка поступила от пользователя - <b>".$_SESSION['sh_name'].'</b> Представителя агентства - <b>'.$_SESSION['ucaption']."</b>\r\n </b><br/> ";
					 		
						//  XMail('89236470002@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);
						# XMail( 'site@em-nsk.ru', 'em-opd@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);

				 
						 
				 
						include_once('SendMailSmtpClass11.php');
 	
						 $mailSMTP = new SendMailSmtpClass('gl_order@mail.ru', 'T2jpnmqSyxbpb8C7MtiU', 'ssl://smtp.mail.ru',465,"UTF-8"); // создаем экземпляр класса
						// от кого
						$from = array(
							"msk.m2profi.pro", // Имя отправителя
							"gl_order@mail.ru" // почта отправителя
						);

						// кому отправка. Можно указывать несколько получателей через запятую
						$to = 'info@g-lounge.ru,   89236470002@mail.ru';
						 $to = '89236470002@mail.ru';
						// добавляем файлы
						$mailSMTP->addFile($files[0]);
						$mailSMTP->addFile($files[1]);
						$mailSMTP->addFile($files[2]);
						
						// отправляем письмо
						  $result =  $mailSMTP->send($to,  'Бронирование участка - №'.$data['num'], $message, $from); 
						 if($result === true){	echo "Done";	}
						 else{echo "Error: " . $result;	}
					 
				}
				
			}
			
			
			//
		}
		else
		{
			print 'Не корректный id';
			return ;
		}
		
		return;
		
  
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	1. Выгрузить карту в отдельные файлы
	2. Режим редактора
	- при клике на участке ajax грузится данные в окошко 
	поля номер, площадь, цена, проект дома (выбрать)
	статус - впродаже/не в продаже и как в эм, тек которые не в продаже серым закрывать 
	Окошко позиционируется абсолютно внутри карты 

	+ Редактор проектов домов


+ если не режим редактирования форма брони аякс в таком же окошке 
+ всплывающие подсказки !!! к участкам как к домам

+ копировать базу ЭМ
- подключить статистику , заявки с сайта, 
	
	*/
	
	function act__index()
	{
		
		
		$this->act__area();
		 
	 
	}
	
	
	
	
	
	
	
	function act__index_pub()
	{
		global $t;
		$t['h1'] = 'Участки';
		//$this->tpl('','landplots','map_css'); // Легенда со статусами
		?>
	
		<div id="zoomcontainer" class="noselect dragscroll" style="position:relative; max-width:100%; overflow:hidden; "  >
			<div id="slide">			  
				<div class="scheme" style="width:100%; " >
					<img src="https://gl.m2profi.pro/sahmatka/landplots/bgx2.png" width="100%" alt=""> 
					<? print file_get_contents('https://gl.m2profi.pro/sahmatka/landplots/polygons2.svg'); ?>
				</div>
			</div>
		</div>
		<?
		//$this->tpl('','landplots','map_js');  
		?>
		 
		
		<?
	}
	
	
	
 
 function object_menu()
 {
	 global $mysql;
	 
	 $landplots_area = $mysql->get_arr('SELECT * FROM landplots_area order by `order` ');
	 ?>
	 

<style>
.mdef{ padding:5px; padding-left:5px; padding-right:5px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	


.objmenua .mdef{color:#000;  }
  .mdefa{color:#FFA500;} /* ТОлько админам */
.mdefaop{color:#999999;} /*  Админам и отделу продаж */


.mdefth{color:#FFF; background-color:#00CDAD;  }			 
.mdef:hover{color:#FFF; background-color:#00CDAD;}					
						
 
@media screen and (min-width: 1000px) {
  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
  .mobilenav{display:none;}
}
@media screen and (max-width: 1000px) {
  .mmenu{	display:none;		}
  .mobilenav{display:block; width:100%;}
  .nomobile{display:none;}
}

.mmenu li {
line-height: 2.1em;
}
</style>


	<?
	if( !$_GET['dir'] && $_GET['dir']!="0" ){$_GET['dir']='1';}
	
	
	/*
	
	
	<div style="width:100%; margin-bottom:10px;" style="display:none;">
		<a href="ctrind.php?ctr=landplots&dir=1" class="mdef <?if($_GET['dir']=='1'){print 'mdefth';}?>" style=" display:inline-block; padding-left: 12px; font-weight:bold; ">УСАДЬБЫ</a> 
		<a href="ctrind.php?ctr=landplots&dir=0" class="mdef <?if($_GET['dir']=='0'){print 'mdefth';}?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">ГУДЗЕМ</a>
	 </div>
	 
	 */
	?>

	 
			 
			 
			 
	

	 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
			
			 <br>
			 <ul class="mmenu">
					<?
					foreach($landplots_area as $k=>$v)
					{
						if( $v['dir']== $_GET['dir'] )
						{
							$class ='';
							if($_GET['area_id']==$v['area_id']){ $class = 'mdefth'; }
							
							if($v['show']==1)
							{
								?>
									<li style="padding:0;"><a href="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>&dir=<?=$v['dir']?>" class="mdef <?=$class?>"><?=$v['caption']?></a> </li>
								<?
							}
							elseif( ( $v['show']==3 || $v['show']==2 ) && ($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem') )
							{					
								?>
									<li style="padding:0;"><a href="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>&dir=<?=$v['dir']?>" class="mdef mdefa <?=$class?>"><?=$v['caption']?></a> </li>
								<?
							}
						}
					}
					?>
				</ul>
	 
			  <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select">
				<div class="objects-head-nav__select">
					<select name="url" onchange="document.autosubmit_select.submit();" style="width:100%;  text-align: left; border-radius:0; ">
					<option>Выбрать поселок</option>
					<?
					foreach($landplots_area as $k=>$v)
					{
						if( $v['dir']== $_GET['dir'] )
						{
							
							if($v['show']==1)
							{
								?>
									<option value="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>&dir=<?=$v['dir']?>"><?=$v['caption']?></option>
								<?
							}
							elseif( ( $v['show']==3 || $v['show']==2 ) && ($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem') )
							{					
								?>
									<option value="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>&dir=<?=$v['dir']?>"><?=$v['caption']?></option>
								<?
							}
						}
					}
					?>
				</select>	
				</div>
			</form>
 		
		</div>
		<hr style="margin-top: 12px; " class="nomobile">
		<?
 }
 
 
 
 
 
 
 function act__jqsvg()
 {
	 $map_id = $_GET['map_id'];
	 $svg_path =  'https://gl.m2profi.pro/maps/'.$map_id.'/map.svg';
	 print file_get_contents( $svg_path  );
 }
	
	
	
	
	
	function act__map_dop( $area_id )
	{
		//$this->tpl('','landplots','map_css_new'); // Легенда со статусами
		// $this->tpl('','landplots','map_js_new');  
		 
		// print 123;
		// print 123;
		 //$this->objects_menu();
		global $t;
		global $mysql;
		
		//if(!$map_id){ $map_id = (int) $_GET['map_id']; }
		//$arr = $mysql->get_arr('SELECT * FROM  landplots_maps WHERE  landplots_map_id="'.$map_id.'"');
		//$map_id = $arr[0]['landplots_map_id'];
		
 		//print_r($arr);
		$t['h1'] = 'Участки';
	   
		$svg_path =  'https://gl.m2profi.pro/maps/area_map_'.$area_id.'.php';
		$bg_path =  'https://gl.m2profi.pro/maps/area_map_'.$area_id.'.png';
		?>
		<div id="map_actions"></div>
		<div id="map__<?=$area_id?>" class="noselect" style="position:relative">
			<div style="position: absolute;    left: 0;    top: 30px;    z-index: 3; display:none;">
				<div class=" ">
					<button class="zmb" data-zoom-down  style="width: auto;">-</button> 
					 
					<button class="zmb" data-zoom-up  style="width: auto;" >+</button>
				</div>
			</div>
			<div class="ratio ratio-4x3 " style="overflow:hidden">
				<div id="myViewport" class="myViewport">
					<div class="myContent" id="mapcontent__<?=$area_id?>">
						<div class="scheme"  style="position:absolute;  width:100%; left:0; ">
						<? print file_get_contents( $svg_path ); ?>
						</div>
						<img class="mapbg"   src="<?=$bg_path?>"   alt="">
					</div>
				</div>		
			</div>
		</div>
 
		<div>
		<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
			<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободен</li>
			<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирован</li>
			<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продан</li>
			<?
			//print_r($_SESSION);
			if($_SESSION['sh_login']=='admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
				?>
					<li class="objects-head-status__grey" style="display:inline-block; margin-right: 20px;">Бронь Усадьбы</li>
					<li class="objects-head-status__blue" style="display:inline-block; margin-right: 20px;">Забронирован подрядчиком</li>	
				<?
			}
			?>
		</ul>
		</div>
		<script src="/maps/frontend/wheel-zoom.min.js" type="text/javascript"></script>
  
		<script>
		$( document ).ready(function() {
			
			// updatejsoon(<?=$map_id?>,<?=$arr[0]['numbers']?>);
		});
		
		
		
	


		</script>
		<?
	}
	
	
	
	function act__map($map_id=false)
	{
		
		 //$this->objects_menu();
		global $t;
		global $mysql;
		
		if(!$map_id){ $map_id = (int) $_GET['map_id']; }
		$arr = $mysql->get_arr('SELECT * FROM  landplots_maps WHERE  landplots_map_id="'.$map_id.'"');
		$map_id = $arr[0]['landplots_map_id'];
		
 		 
		$t['h1'] = 'Участки';
		   
		$svg_path =  'https://gl.m2profi.pro/maps/'.$map_id.'/map.svg';
		$bg_path =  'https://gl.m2profi.pro/maps/'.$map_id.'/map.png';
		?>
		 
		 
		 
		 
<script>	
	$( document ).ready(function() 
	{
   	// печать карты через ифрейм
      function printDocument(url) {
            // Создаем div контейнер для iframe
            var div = $('<div></div>').css({
                overflow: 'hidden',
                width: '1',
                height: '1'
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
			var url = 'https://msk.m2profi.pro/mapwiget/mapprint.php?p=1&map_id=<?=$map_id?>';
			printDocument(url);
        });
   
   
}); 
 </script>
		
	<div class="stat-top stat-top_lp stat-top_user">		
		<div style=""></div>
		<a href="#" id="printButton" class="stat-top__print"></a>
	</div>
			
	
	
 <style>
		 
		 <?=$arr[0]['customcss']?>
		 </style>
		
<div id="map__<?=$map_id?>" class="noselect" style="position:relative">
	<div style="position: absolute;    left: 0;    top: 30px;    z-index: 3; display:none;">
		<div class=" ">
			<button class="zmb" data-zoom-down  style="width: auto;">-</button> 
			 
			<button class="zmb" data-zoom-up  style="width: auto;" >+</button>
		</div>
	</div>
	<div class="ratio ratio-4x3 " style="overflow:hidden">
		<div id="myViewport" class="myViewport">
			<div class="myContent" id="mapcontent__<?=$map_id?>">
				<div class="scheme"  style="position:absolute;  width:100%; left:0; ">
				<? print file_get_contents( $svg_path ); ?>
				</div>
				<img class="mapbg"   src="<?=$bg_path?>"   alt="">
			</div>
		</div>		
	</div>
</div>
<?
if($_SESSION['sh_login']=='admin')
{
	?>
	
	<style>
	
	#map_actions label{font-size:12px; cursor:pointer;}
	</style>
<div style="font-weight: bold; font-size: 12px; padding-bottom: 7px;">Цена за сотку:</div>
<div id="map_actions"></div>
 
 
 <form action="">
  <div id="map_landplots_check_num"></div>
  <div id="map_landplots_check" style="display:none;"></div>
  </form>
  <br><br>
  
  
  <span style="display:none;">
  <input type="checkbox" id="map_editmode" ><label for="map_editmode">Режим правки</label>
</span>
 <?
}
?>
		<div>
		<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
			<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободен</li>
			<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирован</li>
			<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продан</li>
			<?
			//print_r($_SESSION);
			if($_SESSION['sh_login']=='admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
				?>
					<li class="objects-head-status__grey" style="display:inline-block; margin-right: 20px;">Бронь Усадьбы</li>
					<li class="objects-head-status__blue" style="display:inline-block; margin-right: 20px;">Забронирован подрядчиком</li>	
				<?
			}
			?>
		</ul>
		</div>
		<script src="/maps/frontend/wheel-zoom.min.js" type="text/javascript"></script>
 
 
 <?
 if($map_id)
 {
	 ?>
		<script>
		
	 
 console.log('---');
			$(document).ready(function() {
				
				console.log('ДОкумент загружен реди');
				
			/*
			// Выбираем изображение по его идентификатору или классу
			$('svg').on('load', function() {
				console.log('SVG загружен');
				updatejsoon(<?=$map_id?>,<?=$arr[0]['numbers']?>);
			});

			// Если изображение уже было загружено до установки обработчика событий
			if ($('svg').prop('complete')) {
				console.log('SVG загружен ранее');
				updatejsoon(<?=$map_id?>,<?=$arr[0]['numbers']?>);
			}
			
			*/
			updatejsoon(<?=$map_id?>,<?=$arr[0]['numbers']?>);
		});
		</script>
		<?
 } 
		
	 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
 // Все карты поселка
 function act__area()
 {
	 
	 global $t;
	 $t['h1'] = 'Участки';
		
	 global $mysql;
	 $area_id = (int) $_GET['area_id'];
 
	  $this->tpl('','landplots','map_css_new'); // Легенда со статусами
	  $this->tpl('','landplots','map_js_new');  
	 
	 
	 $this->object_menu();
		?>
		
	
	
	

			
		
	  <STYLE>
		  	.ratio {
  position: relative;
  width: 100%;

  &::before {
    display: block;
    padding-top: 75%;
    content: "";
  }

  > * {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }
}
 
		  @media (max-width: 750px) 
		  { 
			.ratio {
			  position: relative;
			  width: 100%;

			  &::before {
				display: block;
				padding-top: 150%;
				content: "";
			  }

			  > * {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			  }
			}
		  }
		  
		  
		  .path_area:hover{display:none; }
		  

		  </STYLE>
		  
		  <?
		  
	//  if(!$area_id){$area_id=1;}
		  
	 if($area_id)
	 { 
		 $arr = $mysql->get_arr('SELECT * FROM landplots_maps WHERE area_id="'.$area_id.'" ');		 
		 
 
		 
		 if(count($arr)>1)
		 {
			 
			 ?>
			 <style>
			  .och_menu{
				  text-align:center;
			  }
			 .och_menu a { 
			 background-color:#42ff00;
			 color:#148f00;
			 display:inline-block;
			 border-radius:20px;
			 padding:5px;
			 padding-left:15px;
			 padding-right:15px;
			 
			 margin-left:5px;
			 margin-right:5px;
			 }
			 </style>
			 <div style="width: 100%; text-align: center; padding: 8px; <?if(!$_GET['map_id']){ print 'font-weight:bold;';}?>"><a href="ctrind.php?ctr=landplots&act=area&area_id=<?=$_GET['area_id']?>">Генплан поселка</a></div>
			 <div class="och_menu">
			 <?
			 foreach( $arr as $k=>$v )
			 {
				 if( $v['landplots_map_id'] == $_GET['map_id'] ){ $style='font-weight:bold'; }
				 else{$style='';}
				 print '<a style="'.$style.'" href="ctrind.php?ctr=landplots&act=area&area_id='.$_GET['area_id'].'&map_id='.$v['landplots_map_id'].'">'.$v['caption'].'</a>  ';
			 }
			 ?></div><?
		 }
			 
			 
			 
		 if( $_GET['map_id'] )
		 {
			 $this->act__map( $_GET['map_id'] );
		 }
		 else
		 {
			 
			
			
			 
			if(count($arr)>1)
			{
				?>
				<style>
				.scheme polygon, .scheme path {
					fill:#FFF;
					opacity: 0.1;
					
					/*Бордюры*/
					stroke: #FFF;
					stroke-width: 1;
				}

				/*ХОВЕР*/
				.scheme polygon:hover , .scheme path:hover 
				{
					fill: #FFF;
					opacity: 0.5;
				}
				</style>
				<?
				$this->act__map_dop( $_GET['area_id'] );
				
			}
			else
			{
 
				foreach( $arr as $k=>$v )
				{
					$this->act__map( $v['landplots_map_id'] ); 
				}				 
			}
			 
			  
			 
			 
		 }
			 
			 
		
	 }
	 else
	 {
		 
		$arr = $mysql->get_arr('SELECT * FROM landplots_area ORDER by `order` ');
		?>
		<div class="objects">
			<div class="row">
		<?
		foreach($arr as $k=>$v)
		{
			
			if($v['dir']!=$_GET['dir']){continue;}
			
		$status = 'в продаже';
			
		// доступ
		if($_SESSION['sh_login'] === 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
		{
			if( $v['show']!=1 && $v['show']!=2 && $v['show']!=3   ){continue;}
		}
		elseif(  $_SESSION['agency_id'] == "92")
		{
			if( $v['show']!=1 && $v['show']!=3  ){continue;}
		}
		else 
		{
			if( $v['show']!=1   ){continue;}
 
		}
		
		
		if( $v['show']!=1  ){$status = 'скрыт';}
	
		?>		
		
		
		
			<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3" <?if( $v['show']!=1 ){?> style="opacity:0.6" <?}?>>
				<div class="object">
				
			 
					<div class="object__title"><?=$v['caption']?></div>
					<div class="object__pict">
						<img src="/area_render/<?=$v['area_id']?>.jpg" alt="">
						<div class="object__info">
						<div class="object__status object__status_sale"><?=$status?></div>
						</div>
					</div>
					<a href="ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>" class="object__btn btn btn_arrow">К объекту<i></i></a>
				</div>
			</div>
		<?
		}
		?>
		</div>
		</div>
		
		<?
	 }
 
 }
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 

function act__parsexml()
{
	global $mysql;
	$map_id = 59;
 
	$s=file_get_contents('../maps/'.$map_id.'/map.svg');
	if(!$s){print 'Нет файла'; return;}
	
	$s = preg_replace_callback('/"([^"]+)"/', function ($matches) 
	{
	 return htmlspecialchars($matches[0],ENT_NOQUOTES);
	}, $s);
 
	libxml_use_internal_errors(true);
	$doc = simplexml_load_string($s);
	  
	if (!$doc) 
	{
		$errors = libxml_get_errors();
		foreach ($errors as $error) 
		{
			print_r($error);
		}
		libxml_clear_errors();
	}

	$nodes = $doc->children();
	
	print '<pre>';
//print_r($nodes);
	
	$outstr = '';
	foreach( $nodes as $k => $v )
	{
		
		
		$attr = current($v->attributes());   
 
		
		
		
		/*
		[class] => area area-status-free
		[data-status] => free
		[data-area-number] => 2 НОМЕР УЧАСТКА
		[data-area-id] => 10
		[data-tippy-content] => <h4>Участок №2</h4><p>Площадь 8.59 сот.</p><p class='rouble-ico'><strong>1 580 560</strong></p>
		[d] => M941.5 242L942 179.5H971.5V242.5L941.5 242Z
		*/
	 
		$data = array();
		$data['map_id'] = $map_id;
		$data['polygon_id'] = $attr['data-area-id'];
		$data['num'] =$attr['data-area-number'] ;
		if($attr['data-status'] =='reserved'){ $data['status'] = '4';}
		if($attr['data-status'] =='free'){ $data['status'] = '2';} // Свободен
		if($attr['data-status'] =='sold'){ $data['status'] = '3';} // продан
		
		if($attr['data-status'] =='stock is-disable'){ $data['status'] = '6';} // не в продаже
	
	 
	 
		preg_match('~<strong>(.*?)</strong>~is', $attr['data-tippy-content'], $p ); // ценаf 
		$data['price'] = str_replace(' ','',$p[1]);
		 
		preg_match('~Площадь (.*?) сот.~is', $attr['data-tippy-content'], $m );  
		$data['area'] = $m[1]*100;
		
		
		
		$attr['id'] = str_replace('path','',$attr['id']);
		
		
		
		$svg[$attr['d']]='<path data-id="'.$attr['data-id'].'" d="'.$attr['d'].'"></path>
		';
		
		// print '<br>';
		// print_r($data); 
	
		if(!$data['polygon_id']){$data['polygon_id']=0;}
		if(!$data['num']){$data['num']=0;}
		if(!$data['price']){$data['price']=0;}
		if(!$data['num']){$data['num']=0;}
		if(!$data['status']){$data['status']=2;}
		if(!$data['area']){$data['area']=0;}
		
		print_r($data);
		$arr = $mysql->get_arr('SELECT * FROM landplots WHERE map_id="'.$map_id.'" AND polygon_id="'.$attr['data-area-id'].'" ',1);
		if($arr) // Обновление существующих записей
		{
			//РАЗБОКИРОВАТЬ
			//print $mysql->update_for_key('landplots','lp_id',$arr['lp_id'],$data,1);
			print '</br>';
		}
		else
		{
			 //$idx = $mysql->insert('landplots',$data);
		}
		//print_r($arr);
	}

		print '</pre>';
		
			print '<textarea>';
		print implode($svg);
		print '</textarea>';
}
 
	
	function act__import()
	{
		global $mysql;
		
		$file =file('import.csv');
		// print_r($file);
		
		foreach($file as $k=>$v)
		{
			if($v)
			{
				$str_arr = explode('	',$v);
				$new_arr[$str_arr[0]] = $str_arr;
			}
		}
 
		$arr = $mysql->get_arr('SELECT * FROM landplots WHERE status!=3');
		
		print '<pre>';
		// print_r($arr);
		print '</pre>';
		
		foreach($arr as $k=>$v)
		{
			$new_arr_sq[$v['num']]=$v;
			//print $v['num']; 
			//if($new_arr[$v['num']]){print '<b>Есть</b>';}
			//else{print '<b>Нет!!!!!</b>';}
			//print '<br/>';
		}
		
		
		foreach($new_arr as $k=>$v)
		{
			$data_array = array();
			$data_array['area']=trim($v[2]);
			# $mysql->update_for_key('landplots','num',$k,$data_array,1);
			print '<br/>';
			
			//print $k; 
			//if($new_arr_sq[$k]){print '<b>Есть</b>';}
			//else{print '<b>Нет!!!!!</b>';}
			//print '<br/>';
		}
		
		
			
	}
	
	function act__editor()
	{
		global $t;

		$this->act__index();
		$t['h1'] = 'Редактор карты';
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	 
	function display_table__status($v)
	{
		global $status_arr;
		global $status_color_arr;
		return  '<span style="background-color:'. $status_color_arr[$v['status']].';  "><b>'. $status_arr[$v['status']].'</b></span>';
	}
	
	function display_table__edit($row)
	{
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
	}
	
	
	
	function display_table__raion($row)
	{
		global $gl_raion;
		return $gl_raion[$row['raion']]; 
	}

	function display_table__price($row)
	{
		//return $row['area']/100*600000; 
		return $row['price'];
	}
 

	function ajcrud_checkform()
	{
		global $filed;
		if( $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
		{
		?>
		<div style="margin-top: 20px;   border-top: 1px solid #000;    padding-top: 20px;">
		<div>Массовая смена статуса (для отмеченных строк)</div>
		<select name="status" class="input_edit" style="text-transform:none; height:auto; border: solid #00CDAD 2px;">
			<option value="0">Не задан</option>			
			<option value="2">Свободен</option>			
			<option value="3">Продан</option>			
			<option value="4">Забронирован</option>			
			<option value="5">Забронирован застройщиком</option>			
			<option value="6">Участок подрядчика</option>		
			<option value="7">Скоро в продаже</option>	
		</select>
		
		
		 
		<?
		$filed->text('new_area_price','Пересчитать цену за сотку','');
		?>
		
		
		<input class="stat-top-btn btn btn_arrow-long" type="submit" value="Сохранить" style="padding: 10px; height:35px; width:auto; "/>
	 
		</div>
		
		
		 
		
		
		
		
		
		
		<?
		}
		
	}


	function act__ajax_crud_check()
	{
		global $r;
		global $mysql;
		 print_r($_POST);
		 
	//	 if(!$_POST['checkrow'] || $_POST['status'] ){return;}
		foreach($_POST['checkrow'] as $k => $v )
		{
			// $k - id брони! нам нужно передавать ид участка и менять статус его с сохранением транзакции (добавление новой брони админа)
			//$data = $mysql->get_for_key( $this->table , $this->key_filed , $k); 
			//print_r($data['lp_id'] );
   
			// $landplots = $r->get_object('landplots'); // контроллер лендплотс
			
			$datax = array();
			if( $_POST['status'] )
			{
				$broni_idx = $this->add_broni( $k , $_POST['status'] );
			    $datax['status_broni_id'] = $broni_idx; 
				$datax['status'] = $_POST['status']; 
			}
			
			// пересчитать цену за сотку
			if(  $_POST['new_area_price'] )
			{
				$lp = $mysql->get_for_key( $this->table , $this->key_filed , $k,1 );	
				 
				 $new_price = $_POST['new_area_price']*($lp['area']/100);
				 $datax['price'] = $new_price ; 
				  $datax['price_area'] =  $_POST['new_area_price']; 
				 
			}
			if($datax)
			{
				$mysql->update_for_key('landplots','lp_id', $k , $datax , 0);
			}
		}
	}


	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		return '/sahmatka/ajax_router.php?ctr=landplots&act=broni_history&id='.$v['lp_id'];
	}
	
	function act__price()
	{
		global $t;
		$t['h1'] = 'Участки';
 
		$this->display_ajax_crud();
	}
}