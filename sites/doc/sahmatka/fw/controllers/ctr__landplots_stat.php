<?
class ctr__landplots_stat extends ctr__
{  

	var $table = 'landplots'; // Главная таблица
	var $key_filed = 'lp_id'; // Ключевое поле главной таблицы
	var $ctr = 'landplots_stat';
    var $title = 'Статистика участков';
 
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		// ПРиоритетно ставим гет переменные
		foreach($_GET as $k=>$v)
		{
			// $filtr_data[$k]=$v;
		}
		
		global $mysql;
		$q = 'SELECT landplots.status,
		count(landplots.lp_id) as c 
	 
		  

		FROM  '.$this->table.'   ';
		
		 
		$q.='  WHERE 1=1 AND landplots.del=0  ';
		
		// if(!$_GET['showdel']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		// if($filtr_data['id']){	$q.=' AND `'.$this->table.'`.`'.$this->key_filed.'`="'.$filtr_data['id'].'" ';	}
		
		
		
		$q.=' GROUP BY ';
	 
		$q.= ' landplots.status ';
		
		$q.='   ';
		
		// if($_GET['id']){$q.=''}
		// print $q;
		return $q;
	}
	
	
	
	
	
	
	
	

	
	
	
	function act__index()
	{
		global $r;
		global $mysql;
		global $t;
		
		$t['h1'] = 'Статистика участков';
		?>	
		<div style="text-align:right; width:100%; padding:20px; padding-left:0; padding-right:0;" class="add_buttons">
		</div>
			<div id="ajaxcontent" class="stat">
				<div class="stat-top">
				
				<div class="stat-top-filter">
					<a href="#" class="stat-top-btn btn btn_arrow-long" style="display:none;">ДЕТАЛЬНАЯ СТАТИСТИКА<i></i></a>
				</div>
				<!-- Панель поиска -->
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
				</div>
		<?
		############################################## Все обекты
		$filtr_data = array();
		$filtr_data['allobjects'] = true;
		$sql = $this->get_base_sql( $filtr_data );
		$data_all_objects = $mysql->get_arr($sql);

		foreach($data_all_objects as $k=>$v)
		{
			$data_all[$v['status']]=$v['c'];
		}
		$data_all[2] = $data_all[2]+$data_all[0]; // Добавляем к свободным не заданный статус
		
		// print '<pre>';
		// print_r($data_all_objects);
		// print '</pre>';

		 ?>		
		 <div class="stat-total">
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$data_all[2]?></div>
					<div class="stat-total-item__btn" style="background-color: #8DFFA9; color:#000;">Свободно</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$data_all[4]?></div>
					<div class="stat-total-item__btn" style="background-color: #FEFF52; color:#000;">ЗАБРОНИРОВАНО</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$data_all[3]?></div>
					<div class="stat-total-item__btn" style="background-color: #FF8A90; color:#000;">ПРОДАНО</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$data_all[5]?></div>
					<div class="stat-total-item__btn" style="background-color: #D5E6FE; color:#000;">БРОНЬ ЗАСТРОЙЩИКА</div>
				</div>
				<div class="stat-total-item">
					<div class="stat-total-item__num"><?=$data_all[6]?></div>
					<div class="stat-total-item__btn" style="background-color: #991DFB;">БРОНЬ ПОДРЯДЧИКА</div>
				</div>
			</div>
			
			<?
			
			
			#################################### ПО ОБЕКТАМ
			$filtr_data = array();
			$filtr_data['allobjects'] = false; // Группируем по обектам
			  $sql = $this->get_base_sql( $filtr_data );
			$data_objectsx = $mysql->get_arr($sql);

			foreach($data_objectsx as $k=>$v)
			{
				$data_objectsn[$v['parking_building_id']][$v['status']]=$v['c'];
				$data_objectst[$v['parking_building_id']] = $v['adress_disp'];
				$data_objects_show[$v['parking_building_id']] = $v['show'];
				
			}
			//$data_all[2] = $data_all[2]+$data_all[0]; // Добавляем к свободным не заданный статус
			
			 print '<pre>';
			 //print_r($data_objectsn);
			 // print_r($data_objectst);
			// print_r($data_objectsx);
			 print '</pre>';
			
		
			?>
			<div class="stat-rooms">
			<?
			foreach($data_objectsn as $k=>$v )
			{
				
				$data=array();
				$data['title'] = $data_objectst[$k];
				
				$data[2] = $v[0]+$v[2];
				
				if(!$v[0]){$v[0]=0;}
				if(!$v[1]){$v[1]=0;}
				if(!$v[2]){$v[2]=0;}
				if(!$v[3]){$v[3]=0;}
				if(!$v[4]){$v[4]=0;}
				if(!$v[5]){$v[5]=0;}
				if(!$v[6]){$v[6]=0;}
				
				$data['all'] = $v[0]+$v[2]+$v[3]+$v[4]+$v[5]+$v[6];

				$data[3] = $v[3];
				$data[4] = $v[4];
				$data[5] = $v[5];
				$data[6] = $v[6];
				
				if($data_objects_show[$k]){	$data['class']='stat-rooms-item'; 	$data['class2']='stat-rooms-item-list';  $data['class3']='stat-rooms-item__title';}
				else{	$data['class']='stat-rooms-item2'; $data['class2']='stat-rooms-item-list2'; $data['class3']='stat-rooms-item__title2';}
			?>
			
					<div class="<?=$data['class']?>"> 
					<?
					
					//print_r($v);
					?>
						<div class="<?=$data['class3']?>"><?=$data['title']?> </div>
						 <ul class="<?=$data['class2']?>">
							<li><span>Всего :</span> <span><?=$data['all']?></span></li>
							<li><span>Продано</span> <span> <?=$data[3]?></span></li>
							<li><span>Бронь агента:</span> <span> <?=$data[4]?></span></li>
							<li><span>Бронь застройщика:</span> <span> <?=$data[5]?></span></li>
							<li><span>Бронь подрядчика:</span> <span> <?=$data[6]?></span></li>
					 
							<li><span>Cвободно для бронирования агентами:</span> <span> <?=$data[2]?></span></li>
							 
						</ul>
					</div>
			
			
			<?
			}
			?>
			</div>
						<div class="stat-table stat-table-user stat-table_notpd table" id="fw_ajax_data">
							 <?
							 	//$this->act__indexdata();
							 ?>
						</div>
					</div>			
			<?
		
 
	}
	
	
	 
	
	
	
	
	
}