<?
class ctr__parking_floors extends ctr__
{  

	var $table = 'parking_floors'; //Главная таблица
	var $key_filed = 'parking_floor_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking_floors';
    var $title = 'Поэтажные планы парковок';
 
 
 
	function __construct()
	{
		
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data = $data; // Сохраняем данные
			 
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		
		/*
		Перезагружать содержимое селектов при каждой выборке по хорошему? только те которые не указаны в гет запросе?
		+ в гет запросе указывать только не нулевые!
		
		+ как то псевдонимы прикрутить к гет запросам?!
		*/
		
		// Выводимые столбцы
		$titles = array();
		$titles[$this->key_filed] = 'id';
		$titles['adress_disp'] = 'Адрес';
		$titles['floor'] = 'Этаж';
		$titles['plan_file'] = 'План'; 
		$titles['c'] = 'Мест'; 
		$titles['edit'] = 'Действия'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		// $nowrap['date']=1;
		
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['adress_disp']='adress_disp';
		$this->ajcrud_table_order=$order; 
		
		$this->aj_crud_addbutton=1;
		// $this->display_table_exrow=1; // раскрывать строки
	}
	
	
	
	
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		//if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		// ПРиоритетно ставим гет переменные
		foreach($_GET as $k=>$v){  $filtr_data[$k]=$v;	}
 
		$q = 'SELECT '.$this->table.'.* ,
		count(`parking_spaces`.`parking_space_id` ) as c ,
		parking_buildings.adress,parking_buildings.adress_disp, parking_buildings.street
		FROM  '.$this->table.'   ';
		$q.=' LEFT JOIN parking_buildings ON parking_buildings.parking_building_id = parking_floors.parking_building_id ';
		$q.=' LEFT JOIN parking_spaces ON parking_spaces.parking_floor_id = parking_floors.parking_floor_id ';
		$q.='  WHERE 1=1 ';
		
		// $q.=' AND `parking_spaces`.`del`="0" '; // Перестает отображаться в списках выбора места так как нет парковок в нем!)
		$q.=' AND `parking_buildings`.`del`="0" ';
		$q.=' AND `parking_floors`.`del`="0" ';
		
		
		if(!$filtr_data['showdel']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		if($filtr_data['id']){	$q.=' AND `'.$this->table.'`.`'.$this->key_filed.'`="'.$filtr_data['id'].'" ';	}
		
		if($filtr_data['parking_building_id']){	$q.=' AND `parking_buildings`.`parking_building_id`="'.$filtr_data['parking_building_id'].'" ';	}
		
		
		
		
		
		$q.=' GROUP BY `'.$this->table.'`.`'.$this->key_filed.'` ';
		// if($_GET['id']){$q.=''}
		// print $q;
		return $q;
	}
	
	
	 
	 
	 
	 
	#######################
	####################### ОТОБРАЖЕНИЕ В КАБИНЕТЕ И НА ПУБЛИЧНОМ ПЛАНЕ 
	#######################
	
	
	
	// ОТОБРАЖЕНИЕ МЕСТА В КАБИНЕТАХ И ПУБЛИЧНОМ САЙТЕ
	function printspace_disp($data='')
	{
		global $status_arr;
		$price =  number_format($data['price'], 0, '.', ' ');
		 
		if(!$data['status']){$data['status']=2; }
		
		if($_SESSION['sh_login'] == 'admin' || $_SESSION['agency_id'] == 92 )
		{
			if($data['status']==2){$status_class='car_g'; }
			elseif($data['status']==3){$status_class='car_r'; $href ='#';}
			elseif($data['status']==4){$status_class='car_y'; $href ='#';}
			elseif($data['status']==5){$status_class='car_b'; $href ='#';}
			else{$data['status']==6; $status_class='car_f'; $href ='#';}
		}
		else
		{
			if($data['status']==2){$status_class='car_g'; }
			elseif($data['status']==3){$status_class='car_r'; $href ='#';}
			elseif($data['status']==4){$status_class='car_y'; $href ='#';}
			elseif($data['status']==5){$status_class='car_r'; $href ='#';}
			else{$data['status']==6; $status_class='car_r'; $href ='#';}
		}
		if($_SESSION['sh_login']=='admin')
		{
			$href   = $GLOBALS['config']['domains']['em'].'/sahmatka/iframe_router.php?ctr=parking_spaces&act=order&id='.$data['parking_space_id'].'';
	 
			if($data['status_broni_id'])
			{
				if($data['login']=='admin'){  $data['caption']='';}
				$br_info='<br/><br/><b>'.$status_arr[$data['status']].'</b> <br/>'.fromsql_date($data['date']).'<br/>'.$data['login'].' - '.$data['name'].' <br> <b>'.$data['caption'].'</b><br/>';
			}
 
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место №<?=$data['num']?><br/><b><?=$price?></b><br/><?=$data['area']?>м<sup>2</sup> <?=$br_info?>" data-id="<?=$data['parking_space_id']?>" class="iframe_r car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<input class="place_ch" style="display:none;" type="checkbox" name="places[]" id="place__<?=$data['parking_space_id']?>" value="<?=$data['parking_space_id']?>" />
			<?
		}
		elseif( $_SESSION['sh_login'] &&  $_SESSION['agency_id'] == 92 ) // Все залогиненые - кликть можно на все квартиры карточка должна меняться в зависимости от прав
		{
			if($data['status_broni_id'])
			{
				if($data['login']=='admin'){   $data['caption']='';}
				$br_info='<br/><br/><b>'.$status_arr[$data['status']].'</b> <br/>'.fromsql_date($data['date']).'<br/>'.$data['login'].' - '.$data['name'].' <br> <b>'.$data['caption'].'</b><br/>';
			}			
			
			if($data['status']!=2){$price='';}else{$price.='<br/>';}
			$href   = $GLOBALS['config']['domains']['em'].'/sahmatka/iframe_router.php?ctr=parking_spaces&act=order&id='.$data['parking_space_id'].'';
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место №<?=$data['num']?><br/><?=$price?><br/><?=$data['area']?>м<sup>2</sup> <?=$br_info?>" data-id="<?=$data['parking_space_id']?>" class="iframe_r car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<?
		}
		elseif( $_SESSION['sh_login'] ) // Все залогиненые - кликть можно на все квартиры карточка должна меняться в зависимости от прав
		{
			if($data['status']!=2){$price='';}else{$price.='<br/>';}
			$href   = $GLOBALS['config']['domains']['em'].'/sahmatka/iframe_router.php?ctr=parking_spaces&act=order&id='.$data['parking_space_id'].'';
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место №<?=$data['num']?><br/><?=$price?> <?=$data['area']?>м<sup>2</sup>" data-id="<?=$data['parking_space_id']?>" class="iframe_r car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<?
		}
		else
		{
			if($data['status']!=2){$price='';}else{$price.='<br/>';}
			$href   = $GLOBALS['config']['domains']['em'].'/sahmatka/iframe_router.php?ctr=parking_spaces&act=broni&id='.$data['parking_space_id'].'';
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место № <?=$data['num']?><br/><?=$price?>  <?=$data['area']?>м<sup>2</sup>" data-id="<?=$data['parking_space_id']?>" class="iframe  car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<?
		}
	}
	
 
 
 
 
 
 
 
 
 
 
 
	// ВЫВОД ЭТАЖА 
	function disp_floor($id='',$space_tpl='')
	{
		global $t;
		global $filed;
		global $mysql;
		global $r;

		$filtr = array();
		$filtr['id'] = $id;
		$q = $this->get_base_sql($filtr);
		$data = $mysql->get_arr($q);
		$data=$data[0];
		//print '<h2>Редактирование объекта </h2>';
		?>
			<div style="text-align:center; font-weight:bold; font-size:20px; text-transform: uppercase; over"><?=$data['floor']?> этаж</div>
		 	<div class="row">
				<div class="col-md-12">
				<div id="save_text"><br/></div>	
				<div style="max-width:100%; overflow-x: scroll;">
				<?
				 $width = $data['plan_width'];
				 if($data['parking_floor_id'] == '10' || $data['parking_floor_id'] == '11')
				 {
					$bp = '  background-position: 0 30px; ';
				 }
				?>
					<div class="de_plan" style="width:<?=$width?>px; <?=$bp?>  min-height: 500px;  background-image:url('<?=$data['plan_file']?>'); background-size: contain; background-repeat: no-repeat; position:relative;">
					<img src="<?=$data['plan_file']?>" style="visibility:hidden; margin-bottom:50px; width:<?=$width?>px;" />
					<?
						// получаем и выводим места
						//$spaces_data_arr = $mysql->get_arr('SELECT * FROM parking_spaces WHERE parking_floor_id = "'.$id.'" AND `parking_spaces`.`del`="0" ');
						
						$spaces_data_arr = $mysql->get_arr('SELECT parking_spaces.* ,users.*, agency.caption,parking_broni.date FROM parking_spaces 
						
						LEFT JOIN parking_broni ON   parking_broni.parking_broni_id = parking_spaces.status_broni_id
						LEFT JOIN users ON users.id =parking_broni.user_id
						LEFT JOIN agency ON agency.agency_id = users.agency_id
		
						WHERE 
						
						parking_spaces.parking_floor_id = "'.$id.'" AND `parking_spaces`.`del`="0" ');
						
						
						
						foreach($spaces_data_arr as $k=>$v)
						{
							$this->printspace_disp( $v );
						}
					?> 
					</div> 
				</div> 
				</div>
			</div>
			 
		<?
		$this->tpl('','parking_floors','status_legend'); // Легенда со статусами
	}
	
	
	
	### ВИДЖЕТ ПОЭТАЖНЫХ ПЛАНОВ вывод дома
	function act__public_wiget_display($parking_building_id='',$parking_floor_id='')
	{
		$this->act__display_bf($parking_building_id='',$parking_floor_id='');
	}
	
	
	
	
	// ОТОБРАЖАТЬ ВСЕ ЭТАЖИ ИЛИ УКАЗАННЫЙ ЭТАЖ ЗДАНИЯ  
	function act__display_bf($parking_building_id='',$parking_floor_id='')
	{
		global $mysql;
		
		if(!$parking_building_id) {  $parking_building_id = (int) $_GET['parking_building_id']; }
		if(!$parking_floor_id) {  $parking_floor_id = (int) $_GET['parking_floor_id']; }
		
		// Стили отображения этажа
		$this->tpl('','parking_floors','style_floor_display');
		
		if($parking_floor_id) // Один этаж
		{
			$this->disp_floor($parking_floor_id);
		}
		else
		{
			$floors = $mysql ->get_arr(' SELECT * FROM parking_floors WHERE parking_building_id = "'.$_GET['parking_building_id'].'" AND del=0 ORDER by parking_floors.floor');
			foreach($floors as $k=>$v)
			{
				$this->disp_floor($v['parking_floor_id']);
			}
		}
	}		 
			 

	////////////////////////////////////////////
	function act__catalog()
	{
		global $mysql;
		global $t;
		$t['h1'] = 'Парковки';
		
		$parking_building_id = (int) $_GET['parking_building_id'];
		 
		$adress_arr = $mysql ->get_arr('SELECT * FROM parking_buildings  WHERE parking_buildings.del="0" ORDER by parking_buildings.order ');
		foreach($adress_arr as $k=>$v)
		{
			$sreet_arr[$v['street']][] = $v;
		}
		?>
		<style>
			.mdef{ padding:5px; padding-left:7px; padding-right:7px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	
			.objmenua .mdef{ color:#000;  }
			.mdefa{ color:#FFA500;} /* ТОлько админам */
			.mdefaop{ color:#999999;} /*  Админам и отделу продаж */
			.mdefth{ color:#FFF; background-color:#00CDAD;  }			 
			.mdef:hover{ color:#FFF; background-color:#00CDAD;}	
			@media screen and (min-width: 1000px) {
			  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
			  .mobilenav{display:none;}
			}
			@media screen and (max-width: 1000px) {
			  .mmenu{	display:none;		}
			  .mobilenav{display:block; width:100%;}
			  .nomobile{display:none;}
			}
			.mdef ul{display:none;}
		</style>
		<div style="padding-right:0; padding-left:0; min-height:auto;     margin-bottom: 0;    margin-top: 15px;" class="page-header">	 
			 <ul class="mmenu">		
			 <?
			 foreach($adress_arr as $k=>$v)
			 {
				if($v['show']==0){$opt = ' opacity: 0.4;  ';}else{$opt ='';}
				if($v['parking_building_id'] == $parking_building_id){$actclass=' mdefth '; }else{$actclass='';}
					if( ($v['show']==0  ) && $_SESSION['sh_login'] == 'admin' ) // Админу показывать все
					{
						?>
						<li style="padding:0; <?=$opt?>"><a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" class="mdef <?=$actclass?>"><?=$v['adress_disp']?></a> </li>	
						<? 
					}
					elseif(  $v['show']==3  &&  ( $_SESSION['agency_id'] == 92 || $_SESSION['sh_login'] == 'admin') ) // ТОЛЬКО ОТДЕЛУ ПРОДАЖ
					{
						?>
						<li style="padding:0; <?=$opt?>"><a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" class="mdef <?=$actclass?>"><?=$v['adress_disp']?></a> </li>	
						<? 
					}
					elseif( $v['show']==1  ) // ПОКАЗЫВАТЬ ВСЕМ!
					{
						?>
						<li style="padding:0; <?=$opt?>"><a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" class="mdef <?=$actclass?>"><?=$v['adress_disp']?></a> </li>	
						<? 
					}
			 }
			 ?>
			 </ul>
		</div>
		<hr style="margin-top: 12px; " class="nomobile">
		 <?
		 if(!$parking_building_id)
		 {
			 ?>
			<div class="objects">
			<div class="row">
				<?
				foreach($adress_arr as $k=>$v)
				{
					if( $v['show']==0 && $_SESSION['sh_login'] == 'admin' ){ $opt = 'opacity: 0.4; '; }else{ $opt =''; }
					$v['opt'] = $opt;
					
					if( $v['show']==0 && $_SESSION['sh_login'] == 'admin' ) // Админу показывать все
					{
						$this->tpl($v,'parking_floors','catalog_card');
					}
					elseif(  $v['show']==3  &&  ( $_SESSION['agency_id'] == 92 || $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'em_nsv') ) // ТОЛЬКО ОТДЕЛУ ПРОДАЖ
					{
						$this->tpl($v,'parking_floors','catalog_card');
					}
					elseif( $v['show']==1  ) // ПОКАЗЫВАТЬ ВСЕМ!
					{
						$this->tpl($v,'parking_floors','catalog_card');
					}
				}
				?> 
 
			</div>
		</div>
		
		<?
		}
		else
		{ 	
			$this->act__display_bf($parking_building_id);
		}
	}


 
 
 
 
 
 
 
 
 
 
 
	#######################
	####################### РЕДАКТОР  ПЛАНА ЭТАЖА
	#######################
 
	
	// ОТОБРАЖЕНИЕ МЕСТА  - В РЕДАКТОРЕ ПОЭТАЖНОГО ПЛАНА 
	function printspace_editor($data='')
	{
		$price =  number_format($data['price'], 0, '.', ' ');
		
		if(!$data['status']){$data['status']=2; }
		if($data['status']==2){$status_class='car_g';}
		if($data['status']==4){$status_class='car_y';}
		if($data['status']==3){$status_class='car_r';}
		if($data['status']==6){$status_class='car_b';}
		if($data['status']==5){$status_class='car_f';}
			 
		?>
		<div data-for="place__<?=$data['parking_space_id']?>"  data-id="<?=$data['parking_space_id']?>" class="car <?=$status_class?>"  data-deg="<?=$data['rotate']?>" style="left:<?=$data['x']?>px; top:<?=$data['y'