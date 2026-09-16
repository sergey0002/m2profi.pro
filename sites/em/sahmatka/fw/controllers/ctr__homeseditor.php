<?
$GLOBALS['t']['title']='Редактор домов';

class ctr__homeseditor extends ctr__
{ 

 
	var $table = 'homes'; //Главная таблица
	var $key_filed = 'homes_id'; // Ключевое поле главной таблицы
	var $ctr = 'homeseditor';
	var $title = 'Объекты';
	var $title_act1 = 'Объект';
	var $title_act2 = 'Объекта';
	
	function __construct()
	{
		
		$this->show_array=array();
		$this->show_array[1] = 'Всем';
		$this->show_array[2] = 'Админам';
		$this->show_array[3] = 'Админам и ОП';
		$this->show_array[0] = 'НЕТ';
						
						
						
		$this->mysql=$GLOBALS['mysql'];
	 
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data=$data; // Сохраняем данные
		 	
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
	  
		// Выводимые столбцы
		$titles = array();
		$titles['home_id'] = 'ID';
		$titles['show'] = 'Отображение';
		$titles['title'] = 'Название';
		$titles['kvartal'] = 'Комплекс'; 
		$titles['complite'] = 'Статус';
	//	$titles['description'] = 'Описание'; 
		$titles['order'] = 'Сортировка'; 
	    $titles['show_keys'] = 'Выдача ключей'; 
		
		 $titles['domclick'] = 'Домклик'; 
		
		
		
	    $titles['edit'] = 'Редактирование'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		//$order['adress']='adress';
		//$order['show']='`show`';
		 
		$this->ajcrud_table_order=$order; 
		$this->aj_crud_addbutton=1;		
	}

	function require_admin()
	{
		if (!check_access('admin')) {
			die('Доступ запрещен');
		}
	}

	function get_homes_importer()
	{
		static $importer = null;
		if (!$importer) {
			require_once dirname(__DIR__, 2) . '/inc/homes_import.php';
			global $mysql;
			$importer = new homes_import($mysql);
		}
		return $importer;
	}

	function get_home_by_pk($homes_id_pk)
	{
		global $mysql;
		$homes_id_pk = (int) $homes_id_pk;
		if (!$homes_id_pk) {
			return null;
		}
		return $mysql->get_for_key($this->table, $this->key_filed, $homes_id_pk);
	}

	function home_edit_url($homes_id_pk)
	{
		global $r;
		return $r->acturl($this->ctr, 'edit') . '&id=' . (int) $homes_id_pk;
	}

	function get_sa()
	{
		if (!empty($GLOBALS['sa'])) {
			return $GLOBALS['sa'];
		}
		global $connection;
		return new sahmatka($_SESSION, $connection);
	}

	/**
	 * start_num секции = конец нумерации предыдущей секции
	 * (floor * apartments + start_num − пустые клетки), как в disp_home.
	 */
	function compute_section_start_num($homes_id_pk, $section_id)
	{
		global $mysql;
		$homes_id_pk = (int) $homes_id_pk;
		$section_id = (int) $section_id;
		if ($section_id <= 1) {
			return 0;
		}

		$prev = $mysql->get_arr(
			'SELECT * FROM homes_sections WHERE homes_id="' . $homes_id_pk . '" AND section_id<"'.$section_id.'" ORDER BY section_id'
		);
		if (!$prev) {
			return 0;
		}

		$start = 0;
		foreach ($prev as $sec) {
			$sid = (int) $sec['homes_sections_id'];
			$holes_row = $mysql->get_arr(
				'SELECT COUNT(*) AS c FROM homes_sections_cl WHERE homes_sections_id="' . $sid . '"',
				1
			);
			$holes = (int) ($holes_row['c'] ?? 0);
			$start = ((int) $sec['floor'] * (int) $sec['apartments']) + (int) $sec['start_num'] - $holes;
		}
		return max(0, $start);
	}

	function wipe_home_data($homes_id_pk)
	{
		global $mysql;
		$homes_id_pk = (int) $homes_id_pk;
		$home = $this->get_home_by_pk($homes_id_pk);
		if (!$home) {
			return array('ok' => false, 'message' => 'Дом не найден');
		}
		$home_id_biz = (int) $home['home_id'];

		$report = array(
			'ok' => true,
			'homes_id' => $homes_id_pk,
			'home_id' => $home_id_biz,
			'broni' => 0,
			'apartaments' => 0,
			'sections_cl' => 0,
			'sections' => 0,
		);

		// 1) Брони дома
		$mysql->sql('DELETE FROM broni WHERE home_id="' . $home_id_biz . '"');
		$report['broni'] = (int) mysqli_affected_rows($mysql->c);

		// 2) Квартиры (apartaments.home_id = бизнес-номер)
		$mysql->sql('DELETE FROM apartaments WHERE home_id="' . $home_id_biz . '"');
		$report['apartaments'] = (int) mysqli_affected_rows($mysql->c);

		// 3) Пустые клетки секций (homes_sections_cl)
		$mysql->sql(
			'DELETE cl FROM homes_sections_cl AS cl
			 INNER JOIN homes_sections AS s ON s.homes_sections_id = cl.homes_sections_id
			 WHERE s.homes_id="' . $homes_id_pk . '"'
		);
		$report['sections_cl'] = (int) mysqli_affected_rows($mysql->c);

		// 4) Секции дома (homes_sections.homes_id = PK)
		$mysql->sql('DELETE FROM homes_sections WHERE homes_id="' . $homes_id_pk . '"');
		$report['sections'] = (int) mysqli_affected_rows($mysql->c);

		return $report;
	}

	function print_markup_cards($homes_id_pk)
	{
		global $r;
		$homes_id_pk = (int) $homes_id_pk;
		$wipe_url = $r->acturl($this->ctr, 'wipe_data') . '&id=' . $homes_id_pk;
		?>
		<style>
			.he-markup-row { margin: 0 0 20px; }
			.he-markup-card {
				background: #f5f7f8;
				border: 1px solid #d6dde2;
				border-radius: 8px;
				padding: 16px 18px 18px;
				min-height: 100%;
				box-sizing: border-box;
			}
			.he-markup-card h2 { margin: 0 0 12px; font-size: 18px; }
			.he-markup-card .btn_2-danger { border-color: #c62828; color: #c62828; }
			.he-markup-card .btn_2-danger:hover { background: #c62828; color: #fff; }
		</style>
		<div class="row he-markup-row">
			<div class="col-md-4">
				<div class="he-markup-card">
					<h2>Импорт данных квартир</h2>
					<a href="<?= htmlspecialchars($r->acturl($this->ctr, 'import') . '&id=' . $homes_id_pk) ?>" class="btn_2" target="_top">Открыть импорт</a>
				</div>
			</div>
			<div class="col-md-4">
				<div class="he-markup-card">
					<h2>Секции</h2>
					<a href="<?= htmlspecialchars($r->acturl($this->ctr, 'sections') . '&id=' . $homes_id_pk) ?>" class="btn_2" target="_top">Управление секциями</a>
				</div>
			</div>
			<div class="col-md-4">
				<div class="he-markup-card">
					<h2>Очистка данных</h2>
					<a href="<?= htmlspecialchars($wipe_url) ?>"
					   class="btn_2 btn_2-danger"
					   target="_top">Удалить все данные дома</a>
				</div>
			</div>
		</div>
		<?php
	}
	 
	
	// БАзовый запрос 
	function get_base_sql( )
	{
		foreach($_GET as $k=>$v){  $filtr_data[$k]=$v;	}
		$q = 'SELECT `homes`.* FROM `'.$this->table.'` ';
		
		$q.='  LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal` ';
		//$q.='  LEFT JOIN `apartaments` as ap_sale ON `apartaments`.`homes_id` = `homes`.`homes_id` AND  ';
		
		
		
		$q.='   WHERE 1=1 ';
		if(!$filtr_data['showhide']){$q .= ' AND (`homes`.`show`="1" or `homes`.`show`="2" or `homes`.`show`="3") '; }
		 
		if($filtr_data['show_keys']){$q .= 'AND show_keys = "'.$filtr_data['show_keys'].'" '; }
		return $q.'      ';
	}
	
	
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{
		global $t;
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="iframe_rajax table-edit"> </a> 
		 
		';
	}
	
	// Метод содержимого столбца
	function display_table__kvartal($row)
	{
		global $kvartal;
		return $kvartal[$row['kvartal']];
	}
	
	
	
	
	// Метод содержимого столбца
	function display_table__show_keys($row)
	{
		if($row['show_keys']){return '<b>ДА</b>';}
		else return 'НЕТ';
		 
	}
	
	
	function display_table__complite($row)
	{
		if($row['complite']){return '<b>сдан</b>';}
		else{return 'строится';}
	}
	function display_table__show($row)
	{
		 return $this->show_array[$row['show']]; 
	}
	// Метод содержимого столбца
	function display_table__domclick($row)
	{
		$dk = true;
		$dk_t = array();
		$dk_t2 = '';
	 $dk_t[] = $row['show'];
		if( !$row['title'] ){ $dk = false;  $dk_t[] = 'title';}
		if( !$row['floor'] ){ $dk = false;  $dk_t[] = 'floor';}
		if( $row['show']!="1" ){ $dk = false;  $dk_t[] = 'show';}
		if( !$row['kvartal'] ){ $dk = false;  $dk_t[] = 'kvartal';}
		if( !$row['wallmaterial'] ){ $dk = false;  $dk_t[] = 'wallmaterial';}	
		if( !$row['complex_domclick'] ){ $dk = false;  $dk_t[] = 'complex_domclick';}
		if( !$row['corpus_code_domclick'] ){ $dk = false;  $dk_t[] = 'corpus_code_domclick';}
		if( !$row['built_year'] ){ $dk = false;  $dk_t[] = 'built_year';}
		if( !$row['ready_quarter'] ){ $dk = false;  $dk_t[] = 'ready_quarter';}
		if( !$row['renovation'] ){ $dk = false;  $dk_t[] = 'renovation';}
		if( !$row['lat'] ){ $dk = false;  $dk_t[] = 'lat';}
		if( !$row['lon'] ){ $dk = false;  $dk_t[] = 'lon';}
		if( !$row['adress'] ){ $dk = false;  $dk_t[] = 'adress';}
		
		if($dk)
		{
			return '<b>ЕСТЬ</b>';
		}
		else
		{
			
			foreach($dk_t as $k=>$v)
			{
				$dk_t2.=$v.'<br/>';
			}
			return 'НЕТ ДАННЫХ<br>'.$dk_t2;
		}
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
		//	$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	
	
	function collect_home_post_data()
	{
		if (!$_POST['complite']) {
			$_POST['complite'] = 0;
		}

		$data = array();
		$data['home_id'] = $this->data_value($_POST['home_id'], 0);
		$data['complex_domclick'] = $this->data_value($_POST['complex_domclick'], 0);
		$data['corpus_code_domclick'] = $this->data_value($_POST['corpus_code_domclick'], 0);
		$data['yandex-house-id'] = $this->data_value($_POST['yandex-house-id'], 0);
		$data['yandex-building-id'] = $this->data_value($_POST['yandex-building-id'], 0);
		$data['title'] = $this->data_value($_POST['title'], '');
		$data['long_title'] = $this->data_value($_POST['long_title'], '');
		$data['show'] = $this->data_value($_POST['show'], 2);
		$data['complite_text'] = $this->data_value($_POST['complite_text'], '');
		$data['complite'] = $this->data_value($_POST['complite'], 0);
		$data['img'] = $this->data_value($_POST['img'], '');
		$data['description'] = $this->data_value($_POST['description'], '');
		$data['order'] = $this->data_value($_POST['order'], 0);
		$data['keys_message'] = $this->data_value($_POST['keys_message'], '');
		$data['lat'] = $this->data_value($_POST['map_lat'], '');
		$data['lon'] = $this->data_value($_POST['map_lon'], '');
		$data['kvartal'] = $this->data_value($_POST['kvartal'], 0);
		$data['adress'] = $this->data_value($_POST['map_adress'], '');
		$data['keys_adress'] = $this->data_value($_POST['keys_adress'], '');
		$data['wallmaterial'] = $this->data_value($_POST['wallmaterial'], '');
		$data['floor'] = $this->data_value($_POST['floor'], '');
		$data['show_keys'] = $this->data_value($_POST['show_keys'], 0);
		$data['map_mapkeys_adress'] = $this->data_value($_POST['map_mapkeys_adress'], '');
		$data['map_mapkeys_lat'] = $this->data_value($_POST['map_mapkeys_lat'], '');
		$data['map_mapkeys_lon'] = $this->data_value($_POST['map_mapkeys_lon'], '');
		$data['built_year'] = $this->data_value($_POST['built_year'], 0);
		$data['ready_quarter'] = $this->data_value($_POST['ready_quarter'], 1);
		$data['renovation'] = $this->data_value($_POST['renovation'], '');
		$data['avito_id'] = $this->data_value($_POST['avito_id'], 0);

		if ($_POST['delivery_date']) {
			$delivery_date = date('Y-m-d', strtotime($_POST['delivery_date']));
			if ($delivery_date) {
				$data['delivery_date'] = $this->data_value($delivery_date);
			}
		}

		if (trim((string) $data['long_title']) === '' && trim((string) $data['title']) !== '') {
			$data['long_title'] = $data['title'];
		}
		if (trim((string) ($data['img'] ?? '')) === '' && (int) $data['home_id'] > 0) {
			$data['img'] = 'http://em.m2profi.pro/render/' . (int) $data['home_id'] . '.jpg';
		}

		return $data;
	}

	function validate_home_save($data, $homes_id_pk = 0)
	{
		global $mysql;
		$errors = array();

		if (trim((string) ($data['title'] ?? '')) === '') {
			$errors['title'][] = 'Укажите краткий заголовок';
		}

		$home_id = (int) ($data['home_id'] ?? 0);
		if ($home_id <= 0) {
			$errors['home_id'][] = 'Укажите home_id';
		} else {
			$dup_sql = 'SELECT homes_id FROM `homes` WHERE home_id="' . $home_id . '"';
			if ($homes_id_pk) {
				$dup_sql .= ' AND homes_id!="' . (int) $homes_id_pk . '"';
			}
			if ($mysql->get_arr($dup_sql . ' LIMIT 1', 1)) {
				$errors['home_id'][] = 'Дом с таким home_id уже существует';
			}
		}

		return $errors;
	}

	function act__edit()
	{
		global $kvartal;
	
		global $filed;
		global $mysql;
		global $r;
		global $t;
		global $filed_errors;

		$filed_errors = array();
		$id = (int) ($_REQUEST['id'] ?? 0);
		$data = array();

		if ($_POST) {
			$data = $this->collect_home_post_data();
			$filed_errors = $this->validate_home_save($data, $id);

			if (!$filed_errors) {
				if ($id) {
					$mysql->update_for_key($this->table, $this->key_filed, $id, $data);
					$this->forminform = 'Изменения сохранены!';
					$data = $mysql->get_for_key($this->table, $this->key_filed, $id);
					if (!$data['img'] || 1 == 1) {
						$data['img'] = 'http://em.m2profi.pro/render/' . $data['home_id'] . '.jpg';
					}
				} else {
					$new_id = (int) $mysql->insert($this->table, $data);
					if ($new_id) {
						$url = $r->acturl($this->ctr, 'edit') . '&id=' . $new_id;
						print '<script>window.top.location.replace(' . json_encode($url) . ');</script>';
						return;
					}
					$filed_errors['_form'][] = 'Не удалось сохранить дом в БД';
				}
			}

			if ($filed_errors) {
				$this->forminform = 'Исправьте ошибки в форме';
			}
		} elseif ($id) {
			$data = $mysql->get_for_key($this->table, $this->key_filed, $id);
			if (!$data['img'] || 1 == 1) {
				$data['img'] = 'http://em.m2profi.pro/render/' . $data['home_id'] . '.jpg';
			}
		} else {
			$max_row = $mysql->get_arr(
				'SELECT COALESCE(MAX(`order`),0)+1 AS n, COALESCE(MAX(`home_id`),0)+1 AS hid FROM `homes`',
				1
			);
			$data['show'] = '2';
			$data['order'] = (int) ($max_row['n'] ?? 1);
			$data['home_id'] = (int) ($max_row['hid'] ?? 1);
			$data['title'] = '';
			$data['long_title'] = '';
			$data['complite_text'] = 'строится';
		}

		$t['h1'] = $id ? 'Редактирование объекта' : 'Добавление объекта';

		?>		
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>		
			<form action="<?=$r->acturl( $this->ctr , 'edit' );?>&id=<?=$id?>" method="POST" id="editform"  >
			<br/><br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>

			<?php if (!empty($filed_errors['_form'])): ?>
				<p style="color:#b00020;"><?= htmlspecialchars(implode('; ', $filed_errors['_form'])) ?></p>
			<?php endif; ?>

			<?php if (!empty($id)): ?>
				<?php $this->print_markup_cards($id); ?>
			<?php endif; ?>

			<?php if (empty($id)): ?>
			<script>
			(function () {
				var titleEl = document.querySelector('input[name="title"]');
				var longEl = document.querySelector('input[name="long_title"]');
				if (!titleEl || !longEl) { return; }
				var longEdited = (longEl.value || '').length > 0;
				longEl.addEventListener('input', function () { longEdited = true; });
				titleEl.addEventListener('input', function () {
					if (!longEdited) { longEl.value = titleEl.value; }
				});
			})();
			</script>
			<?php endif; ?>
			
			<div class="row">
				<div class="col-md-6">
				 <?=$filed->text('home_id','home_id',$data['home_id']);?><br/>
				 <?=$filed->text('title','Краткий заголовок',$data['title']);?><br/>
				 <?=$filed->text('long_title','Полный заголовок',$data['long_title']);?><br/>
				 <?=$filed->select('wallmaterial','Материал / технология',array(''=>'не указано','панельный'=>'панельный','монолитный'=>'монолитный','кирпичный'=>'кирпичный','кирпично-монолитный'=>'кирпично-монолитный'),$data['wallmaterial']);?><br/>
				 <?=$filed->text('floor','Этажей',$data['floor']);?><br/>
				 <?=$filed->select('kvartal','Комплекс',$mysql->get_select_data(' SELECT * FROM `homes_kvartal` ','homes_kvartal_id','title'),$data['kvartal']);?><br/>
				<?=$filed->text('img','Изображение',$data['img']);?><br/>
				</div>
				 
				
				<div class="col-md-6">
				<?=$filed->text('order','Порядок сортировки',$data['order']);?><br/>
					<?
						
					?>
					<?=$filed->select('show','Показывать',$this->show_array,$data['show']);?><br/>
					
					<?
					$filed->date('delivery_date','Дата сдачи',$data['delivery_date']);
					?><br/>
					<?=$filed->text('complite_text','Состояние готовности',$data['complite_text']);?><br/>
					<?=$filed->checkbox('complite','Дом сдан',$data['complite']);?><br/>
					<?=$filed->textarea('description','Описание',$data['description']);?><br/> 
				</div>
			</div>
			
			<h2>Объект на карте</h2>
			 <?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['lat'];
				$data_map['lon'] = $data['lon'];
				$data_map['adress'] = $data['adress'];
				$filed->map('map',$data_map);  
			?>

			<hr/>
				<h2>Выдача ключей</h2>
			
				<?=$filed->checkbox('show_keys','Выдача ключей',$data['show_keys']);?><br/>
				<?=$filed->text('keys_message','Сообщение при успешной записи на выдачу ключей',$data['keys_message']);?><br/>
				<?=$filed->text('keys_adress','Адрес выдачи ключей',$data['keys_adress']);?><br/>
			<?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['map_mapkeys_lat'];
				$data_map['lon'] = $data['map_mapkeys_lon'];
				$data_map['adress'] = $data['map_mapkeys_adress'];
				$filed->map('map_mapkeys',$data_map);  
			?>	
			<hr/>
				<h2>Домклик</h2>
				<?=$filed->text('complex_domclick','complex (Домлик)',$data['complex_domclick']);?><br/>
				<?=$filed->text('corpus_code_domclick','corpus_code (Домлик)',$data['corpus_code_domclick']);?><br/>
				<?=$filed->text('built_year','Год сдачи (Домлик)',$data['built_year']);?><br/>
				  
				 
				 
				
				 
				 
				<?=$filed->select('ready_quarter','квартал сдачи (Домлик)',array('1'=>'1','2'=>'2','3'=>'3','4'=>'4'),$data['ready_quarter']);?><br/>
  
				<?=$filed->select('renovation','Отделка',array(''=>'не указано','чистовая'=>'чистовая','черновая'=>'черновая','нет'=>'нет','предчистовая'=>'предчистовая'),$data['renovation']);?><br/>
				<br/><br/>
				<a href="https://domclick.ru/validation/validator" target="_blank">Валидатор домклик</a>  
					<a href="https://domclick.ru/validation/requirements" target="_blank">Поля фида</a> 
					<a href="https://domclick.ru/complexes/123__<?=$data['complex_domclick']?>" target="_blank">Страница комплекса (ЖК)</a> 
					
					<br/><br/>
				
				
				
				
				
				<b>Фид домклик:</b>  https://em.m2profi.pro/sahmatka/domclick-<?=$data['home_id']?>.xml
				
 
 
				
				<h2>Яндекс недвижимость</h2>
				
				  <?=$filed->text('yandex-building-id','yandex-building-id',$data['yandex-building-id']);?><br/>
				
				  <?=$filed->text('yandex-house-id','yandex-house-id',$data['yandex-house-id']);?><br/>
				 https://em.m2profi.pro/sahmatka/yandex_feedx.php
				 <h2>Авито</h2>
				  
				 
				     <?=$filed->text('avito_id','avito_id',$data['avito_id']);?><br/>
 				 https://em.m2profi.pro/sahmatka/avito_feedx.php 
				 https://autoload.avito.ru/format/xmlcheck/ 
			</form>
			
		<?
	}
	
	
	/*
	Адреса 800 серия 
	
	
	Даты выдачи ключей от сюда
	https://em-nsk.ru/projects/ 
	*/
	
	
	
	function ajcrud_filtr()
	{
		?>
		
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="showhide" name="showhide" value="1" <? if($_GET['showhide']){print ' checked="checked" ';} ?>> <label for="showhide">Скрытые</label><br/>
		</div>
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="show_keys" name="show_keys" value="1" <? if($_GET['show_keys']){print ' checked="checked" ';} ?>> <label for="show_keys">Выдача ключей</label><br/>
		</div>
 
		<?
	}
	
	
	
	
	
	function act__import()
	{
		$this->require_admin();
		$importer = $this->get_homes_importer();
		global $mysql, $r, $t;

		$homes_id_pk = (int) ($_REQUEST['id'] ?? 0);
		$home = $this->get_home_by_pk($homes_id_pk);
		if (!$home) {
			die('Дом не найден');
		}

		$home_id_biz = (int) $home['home_id'];
		$t['h1'] = 'Импорт данных квартир';

		$existing_rows = $mysql->get_arr(
			'SELECT apartment_num FROM apartaments WHERE home_id="' . $home_id_biz . '"'
		);
		$existing_nums = array();
		if (is_array($existing_rows)) {
			foreach ($existing_rows as $row) {
				$existing_nums[] = (int) $row['apartment_num'];
			}
		}

		$raw_text = '';
		$preview = null;
		$message = '';
		$report = null;
		$token = '';
		$green_count = 0;

		if (!empty($_POST['do']) && $_POST['do'] === 'parse') {
			$raw_text = (string) ($_POST['raw_text'] ?? '');
			$parsed = $importer->parse($raw_text);
			$preview = $importer->validate_rows($parsed, $home_id_biz, $existing_nums);
			$green = array();
			foreach ($preview as $row) {
				if (!empty($row['ok']) && !empty($row['normalized'])) {
					$green[] = $row['normalized'];
				}
			}
			$green_count = count($green);
			$token = bin2hex(random_bytes(16));
			$_SESSION['homes_import'][$homes_id_pk] = array(
				'token' => $token,
				'green' => $green,
				'home_id' => $home_id_biz,
			);
		} elseif (!empty($_POST['do']) && $_POST['do'] === 'approve') {
			$sess = $_SESSION['homes_import'][$homes_id_pk] ?? null;
			$post_token = (string) ($_POST['token'] ?? '');
			if (!$sess || !$post_token || !hash_equals($sess['token'], $post_token)) {
				$message = 'Сессия предпросмотра истекла — обработайте данные заново.';
			} elseif ((int) ($sess['home_id'] ?? 0) !== $home_id_biz) {
				$message = 'Неверный дом для одобрения импорта.';
			} else {
				$report = $importer->approve($homes_id_pk, $home_id_biz, $sess['green']);
				unset($_SESSION['homes_import'][$homes_id_pk]);
			}
		} elseif (!empty($_SESSION['homes_import'][$homes_id_pk])) {
			$sess = $_SESSION['homes_import'][$homes_id_pk];
			$token = (string) ($sess['token'] ?? '');
			$green_count = count($sess['green'] ?? array());
		}

		$this->tpl(array(
			'back_url' => $this->home_edit_url($homes_id_pk),
			'parse_url' => $r->acturl($this->ctr, 'import') . '&id=' . $homes_id_pk,
			'approve_url' => $r->acturl($this->ctr, 'import') . '&id=' . $homes_id_pk,
			'home_title' => (string) ($home['title'] ?: $home['long_title']),
			'home_id' => $home_id_biz,
			'raw_text' => $raw_text,
			'preview' => $preview,
			'columns' => $importer->preview_columns(),
			'token' => $token,
			'green_count' => $green_count,
			'message' => $message,
			'report' => $report,
		), 'homeseditor', 'import');
	}

	function act__sections()
	{
		$this->require_admin();
		global $mysql, $r, $t;

		$homes_id_pk = (int) ($_REQUEST['id'] ?? 0);
		$home = $this->get_home_by_pk($homes_id_pk);
		if (!$home) {
			die('Дом не найден');
		}

		$t['h1'] = 'Секции дома';
		$rows = $mysql->get_arr(
			'SELECT * FROM homes_sections WHERE homes_id="' . $homes_id_pk . '" ORDER BY section_id'
		);
		if (!$rows) {
			$rows = array();
		}

		$sections = array();
		foreach ($rows as $row) {
			$row['edit_url'] = $r->acturl($this->ctr, 'section_edit') . '&id=' . $homes_id_pk . '&section_id=' . (int) $row['section_id'];
			$sections[] = $row;
		}

		$this->tpl(array(
			'back_url' => $this->home_edit_url($homes_id_pk),
			'add_url' => $r->acturl($this->ctr, 'section_edit') . '&id=' . $homes_id_pk,
			'home_title' => (string) ($home['title'] ?: $home['long_title']),
			'sections' => $sections,
		), 'homeseditor', 'sections_list');
	}

	function act__section_edit()
	{
		$this->require_admin();
		$importer = $this->get_homes_importer();
		global $mysql, $r, $t;

		$homes_id_pk = (int) ($_REQUEST['id'] ?? 0);
		$home = $this->get_home_by_pk($homes_id_pk);
		if (!$home) {
			die('Дом не найден');
		}

		$section_id = isset($_REQUEST['section_id']) ? (int) $_REQUEST['section_id'] : 0;
		$t['h1'] = $section_id ? 'Редактирование секции' : 'Новая секция';
		$message = '';

		if (!empty($_POST['save_section'])) {
			$save_section_id = (int) ($_POST['section_id'] ?? 0);
			if (!$save_section_id) {
				die('Укажите № секции');
			}

			$start_raw = isset($_POST['start_num']) ? trim((string) $_POST['start_num']) : '';
			$start_num = ($start_raw === '')
				? $this->compute_section_start_num($homes_id_pk, $save_section_id)
				: (int) $start_raw;

			$sec_data = array(
				'homes_id' => $homes_id_pk,
				'section_id' => $save_section_id,
				'caption' => $this->data_value($_POST['caption'] ?? ''),
				'floor' => (int) ($_POST['floor'] ?? 0),
				'apartments' => (int) ($_POST['apartments'] ?? 0),
				'start_num' => $start_num,
			);

			$existing = $mysql->get_arr(
				'SELECT homes_sections_id FROM homes_sections WHERE homes_id="' . $homes_id_pk . '" AND section_id="' . $save_section_id . '" LIMIT 1',
				1
			);
			if ($existing) {
				$mysql->update_for_key('homes_sections', 'homes_sections_id', $existing['homes_sections_id'], $sec_data);
				$homes_sections_id = (int) $existing['homes_sections_id'];
			} else {
				if (trim($sec_data['caption']) === '') {
					$sec_data['caption'] = 'Секция №' . $save_section_id;
				}
				$homes_sections_id = (int) $mysql->insert('homes_sections', $sec_data);
			}

			if ($homes_sections_id && !empty($_POST['has_cl_grid'])) {
				$importer->save_section_cl($homes_sections_id, isset($_POST['cl']) ? $_POST['cl'] : array());
			}

			$redirect = $r->acturl($this->ctr, 'section_edit') . '&id=' . $homes_id_pk . '&section_id=' . $save_section_id;
			print '<script>window.location.replace(' . json_encode($redirect) . ');</script>';
			return;
		}

		$is_new = true;
		$auto_start = $section_id
			? $this->compute_section_start_num($homes_id_pk, $section_id)
			: 0;
		$section = array(
			'section_id' => $section_id ?: '',
			'caption' => $section_id ? ('Секция №' . $section_id) : '',
			'floor' => $home['floor'] ?? '',
			'apartments' => '',
			'start_num' => $auto_start,
		);
		$homes_sections_id = 0;
		$clean = array();
		$cell_nums = array();

		if ($section_id) {
			$row = $mysql->get_arr(
				'SELECT * FROM homes_sections WHERE homes_id="' . $homes_id_pk . '" AND section_id="' . $section_id . '" LIMIT 1',
				1
			);
			if ($row) {
				$is_new = false;
				$section = $row;
				if ($section['start_num'] === '' || $section['start_num'] === null) {
					$section['start_num'] = $auto_start;
				}
				$homes_sections_id = (int) $row['homes_sections_id'];
				$sa = $this->get_sa();
				$conf = $sa->get_sec_arr((int) $home['home_id'], $section_id);
				if (is_array($conf) && !empty($conf['clean_apartments'])) {
					$clean = $conf['clean_apartments'];
				}
			} else {
				$section['start_num'] = $auto_start;
			}
		}

		$floor_max = (int) ($section['floor'] ?? 0);
		$apartments = (int) ($section['apartments'] ?? 0);
		$start_num = (int) ($section['start_num'] ?? 0);
		if ($floor_max > 0 && $apartments > 0) {
			$cell_nums = $importer->simulate_numbers($floor_max, $apartments, $start_num, $clean);
		}

		$this->tpl(array(
			'back_url' => $r->acturl($this->ctr, 'sections') . '&id=' . $homes_id_pk,
			'form_url' => $r->acturl($this->ctr, 'section_edit') . '&id=' . $homes_id_pk . ($section_id ? ('&section_id=' . $section_id) : ''),
			'section' => $section,
			'homes_sections_id' => $homes_sections_id,
			'floor_max' => $floor_max,
			'apartments' => $apartments,
			'clean_apartments' => $clean,
			'cell_nums' => $cell_nums,
			'message' => $message,
			'is_new' => $is_new,
		), 'homeseditor', 'section_edit');
	}

	function act__wipe_data()
	{
		$this->require_admin();
		global $r, $t, $mysql;

		$homes_id_pk = (int) ($_REQUEST['id'] ?? 0);
		$home = $this->get_home_by_pk($homes_id_pk);
		if (!$home) {
			die('Дом не найден');
		}

		$home_id_biz = (int) $home['home_id'];
		$edit_url = $this->home_edit_url($homes_id_pk);

		// Предпросмотр: сколько строк сейчас в таблицах
		$before = array(
			'apartaments' => 0,
			'sections' => 0,
			'sections_cl' => 0,
			'broni' => 0,
		);
		$r1 = $mysql->get_arr('SELECT COUNT(*) AS c FROM apartaments WHERE home_id="' . $home_id_biz . '"', 1);
		$before['apartaments'] = (int) (is_array($r1) ? ($r1['c'] ?? 0) : 0);
		$r2 = $mysql->get_arr('SELECT COUNT(*) AS c FROM homes_sections WHERE homes_id="' . $homes_id_pk . '"', 1);
		$before['sections'] = (int) (is_array($r2) ? ($r2['c'] ?? 0) : 0);
		$r3 = $mysql->get_arr(
			'SELECT COUNT(*) AS c FROM homes_sections_cl cl
			 INNER JOIN homes_sections s ON s.homes_sections_id = cl.homes_sections_id
			 WHERE s.homes_id="' . $homes_id_pk . '"',
			1
		);
		$before['sections_cl'] = (int) (is_array($r3) ? ($r3['c'] ?? 0) : 0);
		$r4 = $mysql->get_arr('SELECT COUNT(*) AS c FROM broni WHERE home_id="' . $home_id_biz . '"', 1);
		$before['broni'] = (int) (is_array($r4) ? ($r4['c'] ?? 0) : 0);

		$t['h1'] = empty($_POST['confirm_wipe']) ? 'Удаление данных дома' : 'Данные дома удалены';
		$report = null;
		if (!empty($_POST['confirm_wipe'])) {
			$report = $this->wipe_home_data($homes_id_pk);
		}

		$this->tpl(array(
			'edit_url' => $edit_url,
			'form_url' => $r->acturl($this->ctr, 'wipe_data') . '&id=' . $homes_id_pk,
			'home' => $home,
			'homes_id_pk' => $homes_id_pk,
			'home_id_biz' => $home_id_biz,
			'before' => $before,
			'report' => $report,
		), 'homeseditor', 'wipe_data');
	}

	function act__index()
	{
  
		global $t;
		$t['h1'] = 'Настройки объектов';
	 
		$this->display_ajax_crud();
	}
	
	
	
}