<?
class ctr__rentobjects extends ctr__
{  

	var $table = 'rent_objects'; //Главная таблица
	var $key_filed = 'rent_objects_id'; // Ключевое поле главной таблицы
	var $ctr = 'rentobjects';
    var $title = 'Аренда ';
   
   
   
   
   
	function __construct()
	{
		
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
		$titles[$this->key_filed] = 'id';
	 
		$titles['h_adress'] = 'Здание';
		$titles['adress'] = 'Офис';
		$titles['floor'] = 'Этаж';
		$titles['area'] = 'Площадь';
	 	$titles['status'] = 'Статус';
		$titles['show'] = '  На сайте';
		$titles['show_b'] = 'С комиссией';
	 
		$titles['edit'] = 'Действия'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		 
		 
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['status']='`rent_objects`.`status`';
		$this->aj_crud_table_order=$order; 
		 
		
		
		 
	   global $broni_status;
	   $broni_status[0] = 'Не задан';
	   $broni_status[2] = 'Свободно';
	   $broni_status[4] = 'Забронировано';
	   $broni_status[3] = 'В аренде';	
	   $broni_status[5] = 'Продано';	

	   global $broni_colors;
	   $broni_colors[0] = '#8DFFA9';
	   $broni_colors[2] = '#8DFFA9';
	   $broni_colors[4] = '#FEFF52';
	   $broni_colors[3] = '#FF8A90';
	   
	   $this->aj_crud_addbutton=1;
	}
	
	
   
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		global $mysql;
		
		
		$filtr_data=$_GET;
		
		//	print_r($filtr_data);
		// print '<br/>';
		 
		////Array ( [area_min] => 1 [area_max] => 2 [appointment] => 1 [separate_entrance] => 2 [rooms] => 1 [home] => 1 [live] => 2 [separate_entrance_ch] => 3 [place_for_unloading] => 4 )
 
		$q = 'SELECT '.$this->table.'.*, rent_homes.adress as h_adress,
		 

		rent_homes.build_type as h_build_type , rent_broni.date, users.*,agency.* , rent_objects.comment
		FROM  '.$this->table.' 
		LEFT JOIN rent_homes ON rent_homes.rent_home_id = rent_objects.rent_home_id  
	 
		LEFT JOIN rent_broni  ON rent_broni.rent_broni_id = rent_objects.status_broni_id		
		LEFT JOIN users ON users.id =rent_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		
		
		 
		WHERE 1=1 ';
	 
		
		// id  
		if( $_GET['act']!='hide' && $_GET['act']!='show' && $_GET['act']!='del' && $filtr_data['id'] )
		{
			  $q.=' AND `rent_objects`.`rent_objects_id`  = "'.$filtr_data['id'].'" ';  
		}
		else
		{
			if($_GET['sale'])
			{
				 $q.=' AND (`rent_objects`.`sale`  = "1" OR `rent_objects`.`sale` = "2" )';
			}
			else
			{
				$q.=' AND ( (`rent_objects`.`sale`  = "0" OR `rent_objects`.`sale`  IS NULL ) OR `rent_objects`.`sale` = "2" )';
			}
			  
			//if( $filtr_data['id']    ){ $q.=' AND `rent_objects`.`rent_objects_id`  = "'.$filtr_data['id'].'" ';  }
			
			
			// show  
			if( !$filtr_data['showhide'] ){ $q.=' AND `rent_objects`.`show`  = "1" ';  }
			 
			// del  
			if( !$filtr_data['show_dell'] ){ $q.=' AND `rent_objects`.`del`  = "0" ';  }
			
	 
			// id дома
			if( $filtr_data['rent_home_id']    ){ $q.=' AND `rent_objects`.`rent_home_id`  = "'.$filtr_data['rent_home_id'].'" ';  }
			if( $filtr_data['area_min'] ){ $q.=' AND `rent_objects`.`area` >= "'.$filtr_data['area_min'].'" ';  }
			if( $filtr_data['area_max'] ){ $q.=' AND `rent_objects`.`area` <= "'.$filtr_data['area_max'].'" ';  }
			if( $filtr_data['appointment'] ){ $q.=' AND (`rent_objects`.`appointment`  = "'.$filtr_data['appointment'].'" or `rent_objects`.`appointment`  = "0") ';  }
			if( $filtr_data['separate_entrance'] ){ $q.=' AND `rent_objects`.`separate_entrance`  = "'.$filtr_data['separate_entrance'].'" ';  }
			if( $filtr_data['rooms'] ){ $q.=' AND `rent_objects`.`rooms`  = "'.$filtr_data['rooms'].'" ';  }
			if( $filtr_data['floor']  || $filtr_data['floor'] =="0" ){ $q.=' AND `rent_objects`.`floor`  = "'.$filtr_data['floor'].'" ';  }
			if( $filtr_data['place_for_unloading']  ){ $q.=' AND `rent_objects`.`place_for_unloading`  > "0" ';  }
			if( $filtr_data['live']  ){ $q.=' AND rent_homes.build_type  like "%жил%" ';  }
			if( $filtr_data['otd']  ){ $q.=' AND rent_homes.build_type  like "%отдель%" ';  }
		}
		
		if( $filtr_data['status']   )
		{
			if( $filtr_data['status'] == "2")
			{
				$q.=' AND ( `rent_objects`.`status`  = "0" OR `rent_objects`.`status`  = "2" ) '; 
			}
			else
			{
				$q.=' AND `rent_objects`.`status`  = "'.$filtr_data['status'].'" '; 
			}
		}
		
		
		
		if( $filtr_data['street'] ){ $q.=' AND rent_homes.street  like "%'.$filtr_data['street'].'%" ';  }



		if( $filtr_data['show_b'] ){ $q.=' AND `rent_objects`.`show_b`="'.$filtr_data['show_b'].'" ';  }




		// публичное отображение
		if($_GET['act']=='display')
		{
			$q.=' ORDER BY `rent_objects`.`show_b` desc, plan DESC '; 
		}
		else
		{
			$q.=' ORDER BY `rent_objects`.`show_b` desc , plan DESC'; 
		}
		
		// if($_GET['id']){$q.=''}
		     // print $q;
			 // print '<br><br>';
		return $q;
	}
	
	 
	
	
	
	

	// Метод содержимого столбца
	function display_table__edit($row)
	{
		$link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit" title="Редактировать"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;" title="Удалить"> X </a>
		';
		
		
		if(!$row['show'])
		{
			$link.=' &nbsp; &nbsp; <a href="?ctr='.$this->ctr.'&act=show&id='.$row[$this->key_filed].'" style="color:#C0C0C0;  font-size: 21px;" title="Отображать на сайте"> S </a>';
		}
		else
		{
			$link.=' &nbsp; &nbsp; <a href="?ctr='.$this->ctr.'&act=hide&id='.$row[$this->key_filed].'" style="color:#000;  font-size: 21px;" title="Скрыть с сайта"> H </a>';
		}
		
		
		return $link;
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
	
	# скрыть
	function act__show()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['show'] = 1;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
		
	# Удаление показать скрытый
	function act__hide()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['show'] = 0;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	
	
	
	 
	
	
	
	
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование объекта';
		
		global $filed;
		global $mysql;
		global $r;
		global $broni_status;
		  
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			 
			$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			 
			
			//print '<h2>Редактирование объекта </h2>';
		}
		else
		{
			//print '<h2>Добавление объекта</h2>';
		}
		
		// local_id	hotel_id	corpus_id	area	caption		sort
		if($_POST) ############# Обработка данных пост
		{
			// print_r($_POST);
			$data = array();
			$data['rent_home_id'] = $_POST['rent_home_id'];
			$data['adress'] = ''.$_POST['adress'];
			$data['floor'] = $_POST['floor'];
			$data['area'] = $_POST['area'];
			$data['free_plan'] = $_POST['free_plan'];
			$data['max_power_grid'] = $_POST['max_power_grid'];
			$data['plan'] = $_POST['plan'];
			$data['comment'] = $_POST['comment'];
			$data['separate_entrance'] = $_POST['separate_entrance'];
			$data['appointment'] = $_POST['appointment'];
			$data['rooms'] = $_POST['rooms'];
			$data['sale'] = $_POST['sale'];
			$data['sale_price'] = $_POST['sale_price'];
			if($_POST['status']){$data['status'] = $_POST['status'];}
		//	$data['street'] = $_POST['street'];
			
			
			if(!$_POST['show'])	{	$data['show'] = 0;	}else{	$data['show'] = 1;	}
			
		 	if(!$_POST['show_b'])	{	$data['show_b'] = 0;	}else{	$data['show_b'] = 1;	}
			
			if(!$_POST['place_for_unloading'])	{	$data['place_for_unloading'] = 0;	}else{	$data['place_for_unloading'] = 1;	}
				 
				 
			if( !$data['floor'] ){ $data['floor'] =1; } 
			if( !$data['area'] ){ $data['area'] = 0; }	 
			if( !$data['rooms'] ){ $data['rooms'] =0; }
				  
			//	 print_r($data);
			if($id) // Редактирваоние существующей записи
			{
				print 'Изменения сохранены!';
			 	$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );

				$this -> add_broni( $id , $_POST['status'] );
				
				
			}
			else // Добавление новой записи
			{
				//	print 'Запись добавлена!';
				$mysql -> insert( $this->table , $data );
			}
			// $this->act__index();
		}
		
		if(!$_POST || 1==1 ) ############# ФОРМА
		{
		?>
		
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
		
		<div id="ajaxcontent" class="sta!t">
		<form action="<?=$action?>" method="POST" id="editform"  >
 
		<br/><br/>
		<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
		<div class="row">
			<div class="col-md-6">
			<?
				 
				
				 // Здание
				 $sel_arr = array();
				 $sel_data = $mysql->get_arr('SELECT * FROM rent_homes WHERE del=0 ');
				 foreach( $sel_data as $k=>$v)
				 {
					 $v['adress'] = str_replace('Россия, ','',$v['adress']);
					 $v['adress'] = str_replace('Новосибирск, ','',$v['adress']);
					 $sel_arr[$v['rent_home_id']] = $v['adress']; 
				 }
				  
				 print $filed->select('rent_home_id','Здание',$sel_arr,$data['rent_home_id']);
				 print $filed->text('adress','Адрес (Кабинет/Офис)',$data['adress']); print '<br/>';
				 print $filed->text_num('floor','Этаж',$data['floor']); print '<br/>';
				 print $filed->text_float('area','Площадь',$data['area']); print '<br/>';
				  
			 $sel_arr=array();
			 $sel_arr[1]='Общий';
			 $sel_arr[2]='Отдельный';
			 $sel_arr[3]='Коридорный';
	 		 $filed->select('separate_entrance','Вход',$sel_arr,$data['separate_entrance']);
		   
			 $sel_arr=array();
			 $sel_arr[0]='Свободное';
			 $sel_arr[1]='Офис';
			 $sel_arr[2]='Магазин ';
			 $sel_arr[3]='Под детский центр';
	 		 $filed->select('appointment','Назначение',$sel_arr,$data['appointment']);

				  // $filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 100px;" '); print '<br/>';
				  // $filed->text_num('order','Порядок',$data['order']); print '<br/>';
			?>
			</div>
			<div class="col-md-6">
			<?
			
			 $sel_arr=array();
			 $sel_arr[0]='Аренда';
			 $sel_arr[1]='Продажа';
			 $sel_arr[2]='Продажа + аренда';
	 		 $filed->select('sale','Тип предложения',$sel_arr,$data['sale']);
			 
			 
			 
			 
			print $filed->text('sale_price','Цена продажи',$data['sale_price']); print '<br/>';
				
				
			 
			 print '<br><br>';
			$filed->select('status','Стаус помещения',$broni_status,$data['status']);
			
			print '<br><br>';
			$filed->checkbox('show_b','Доступно к бронированию (помещение с комиссией)',$data['show_b']); print '<br/>';
			
			
			$filed->checkbox('show','Отображать на сайте',$data['show']); print '<br/>';
			
			
			
			print $filed->text('plan','Ссылка на планировку',$data['plan']); print '<br/>';
			print $filed->text('free_plan','Свободная планировка',$data['free_plan']); print '<br/>';
			print $filed->text('max_power_grid','Нагрузка электросетей',$data['max_power_grid']); print '<br/>';
			print $filed->textarea('comment','Описние',$data['comment']); print '<br/>';
			print $filed->text('rooms','Помещений',$data['rooms']); print '<br/>';
			print '<br><br>';
			 
			$filed->checkbox('place_for_unloading','Место под разгрузку',$data['place_for_unloading']); print '<br/>';
			 
			//	$filed->image('image','Фото ',$data['image'],'34256'); print '<br/>';
			//  $filed->images('images','Изображения ',$data['images'],'5000000'); print '<br/>';
			//	$filed->image('logo','Лого ',$data['logo'],'24663'); print '<br/>';
			?>
			</div>
		</div>
			<?
			//$filed->textarea_html('content','Текст',$data['content'],' rows="20" '); print '<br/>';
			/*
			$xxx = new ctr__areas();
			$fl = 0;
			if($data['floor'] == '-1' || !$data['floor'] ){$fl=0;}
			else{ $fl = $data['floor']; }
			$xxx-> area_select( $fl , $data['data_area'] ,1);
			*/
			?>
		</form>

		</div>			
			
		<?
		}
		
		$this->act__broni_history($id);
 
	
	}
	
	
	
	
	
	// Ajax поиск улицы
	function act__sel_street()
	{
		global $mysql;
		$arr = $mysql ->get_arr(' SELECT count(*) as c, `rent_homes`.`street` FROM `rent_objects` LEFT JOIN rent_homes ON rent_homes.rent_home_id = rent_objects.rent_home_id WHERE    `rent_objects`.`show`  = "1" AND   `rent_objects`.`del`  = "0"  GROUP BY `rent_homes`.`street`   ');
		
		print '<option value=""> - </option>';
		foreach($arr as $k=>$v)
		{
			if($v['street'])
			{
				print '<option value="'.$v['street'].'">'.$v['street'].' ('.$v['c'].')</option>';
			}
		}
	}
	
	
	 
	
	
	
	
	
	
	
		############ Методы для переноса в CTR класс
	function display_dev($data)
	{
		print '<pre>';
		print_r($data);
		print '</pre>';
	}
	
	// Много элементов
	function display( $data )
	{
		foreach( $data as $k => $v )
		{
			$this->disp($v);
		}
	}
	
	// Один элементы
	function disp( $data , $tpl='' )
	{
		print_r($data);
		//	$this->tpl($_POST,'rentobjects','orderfom'); 	
	}
	
	###################=
	
	
	
 
	
	
	
	
	
	
	
	
	
	
	
	// Аякс загрузка для агентств - контент страницы
	function act__display_ag_ajax()
	{
		global $mysql;
		$filtr_data = $_REQUEST;
		$filtr_data['show_b'] = 1;
		 
		 
		$q = $this-> get_base_sql( $filtr_data );
		
		$data  = $mysql->get_arr( $q );
				
		$c = count($data);
		
		 
		$wh = declOfNum($c, array('Найдено %d помещение', 'Найдено %d помещения', 'Найдено %d помещений'));


		if($c){$ct = $wh;}
		else{$ct = 'Помещений не найдено';}
		?>
		
		<div class="container  ">
			<?
			if($_GET['rent_home_id'])
			{
				?>
				<div class="p16">
				<label for="h_adress"><b> <input type="checkbox" value="1" class="form_checkbox" id="h_adress" checked="checked" style="margin-left: 0;"> Помещения по адресу: <?=$data[0]['h_adress']?></b></label>
				</div>
				<?
			}
			?>
		</div>
		
		<br/> 
		<br/>
		
		<div class="container flex_box">
			  <div class="rent_h2"><?=$ct?> </div>
		</div>
		
		<div class="container rent_p">
		<div id="rentobjects" class="rent_spoller">
		<?
		foreach($data as $k=>$v)
		{
			// Назначения помещений
			$this->appointment[0] = 'Свободное назначение';
			$this->appointment[1] = 'Офисное';
			$this->appointment[2] = 'Магазин (торговое)';
			$this->appointment[3] = 'Под детский центр';
			  
			$this->separate_entrance[2]='Общий вход';
			$this->separate_entrance[2]='Отдельный вход';
			$this->separate_entrance[3]='Коридорный вход';
	 
			$v['h_adress'] = str_replace('Россия,','',$v['h_adress']);
			$v['h_adress'] = str_replace('Калининский район,','',$v['h_adress']);
		 
			if(ctype_digit(str_replace('.','',$v['area'])))
			{
				$area = 'Помещение – '.$v['area'].'м<sup>2</sup>';
			}
			elseif($v['area']){$area =$v['area']; }
			else{$area = 'Помещение';}
			$params='';

			// Параметры 
			$params.='<li>'.$v['h_build_type'].' </li>';
				
			//Мощность квт
			if( ctype_digit(str_replace(',','',$v['max_power_grid'])) )	{	$params.='<li>'.$v['max_power_grid'].' кВт</li>';	}
			elseif($v['max_power_grid'])	{		$params.='<li>'.$v['max_power_grid'].'</li>';	}
			
			
			if($v['floor'] && $v['floor']!=0){	$params.='<li>'.$v['floor'].' этаж </li>'; }
			elseif( $v['floor']==0){	$params.='<li> Цокольный этаж </li>'; }
			else{	  }
			
			if($v['appointment'] || $v['appointment']=="0"){$params.='<li>'.$this->appointment[$v['appointment']].' </li>';}
				   
			//if( $v['separate_entrance']  ){ $params.='<li>'.$this->separate_entrance[$v['separate_entrance']].'</li>'; }
			if( $v['place_for_unloading']  ){ $params.='<li>Место под разгрузку </li>'; }
			
			
			if( $v['rooms']>1  ){ $params.='<li> Помещений '.$v['rooms'].' </li>'; }
			else{ $params.='<li> Одно помещение </li>';}
		 
			 $v['area_n'] = $area;
			 $v['params_n']=$params;

			$this->tpl($v,'rentobjects','ag_one_item'); // ШАБЛОН ЗАПИСИ ДЛЯ АГЕНТСТВ
		}
		?>
		</div>

		<div id="maprentobjects" class="rent_spoller" style="margin-top:20px;"> 
		<?
		  if($c)
		  {
			// $this->act__map();
		  }
		 ?>
		</div>
	</div>

	<?
	}
	
	
	
	
	
	
	
	function act__index_ag()
	{		
	global $t;
	
	if($_GET['sale'])
	{
		$t['h1'] = 'Продажа ';
	}
	else{
		$t['h1'] = 'Аренда ';
	}
	
	?>
	<!-- форма поиска -->
	<?
	//
	$this->tpl($_POST,'rentobjects','display_ag_form'); 		 
	?>
 
 <div id="rent_search_result"></div>
  
 <div style="width: 100%; max-width: 100vw; text-align: center; padding: 50px; display: none;" id="progressbar">
 <img src="loader.gif">
 </div>
   
 <script>
 $( document ).ready(function() {
 
/*
resultto - ид тега для загрузки результата
formid - ид формы 
url - 
append - добавлять к содержимому ajax
*/
function sendAjaxForm(resultto, formid, url,append=1,progressid='progressbar',preload='',postload='') {
  
if(!append){$('#'+resultto).html('');	}
$('#'+progressid).show();
$('#'+progressid).removeClass('hide');
$("#"+resultto).fadeOut(100);

    $.ajax({
        url:     url, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: $("#"+formid).serialize(),  // Сеарилизуем объект
        success: function(response) { //Данные отправлены успешно

			if(preload)	{	preload();	}

			$("#"+resultto).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка    

			/////////////  ЗАТЫЧКА ДЛЯ СЕЛЕКТОВ!!!
			$('#'+resultto).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
			$('#'+resultto).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены

			if(append)
			{
				$('#'+resultto).append(response);
			}
			else
			{
				$('#'+resultto).html(response);
			}
			$("#"+resultto).prop('disabled', ''); // Блокируем селекты в которые предстоит загрузка    
			$('#'+progressid).hide();

			if(postload){	postload(response);	}
			  //$('select').multipleSelect('refresh');
			 // alert($('#'+resultto).prop('outerHTML') );
 
$("#"+resultto).fadeIn(600);

// Затычка преопределение Fancybox			  
   
     $("a.iframe").fancybox({
            maxWidth    : 600,
            maxHeight   : 12600,
            
            width       : '1000px',
            height      : '10000px',
            closeClick  : true,
        }); 
		  
  $('.iframe_r').magnificPopup({type:'iframe',
 // removalDelay: 100,
 // fixedContentPos: true, 
  //disableOn:1,
  mainClass: 'mfp-fade',
    mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
      removalDelay: 300,
   tLoading: 'Загрузка #%curr%...',
    callbacks: {
    open: function() {
      // Will fire when this exact popup is opened
      // this - is Magnific Popup object
    },
    close: function() {
        // parent.location.reload(true);  
    },
	open: function() {
          location.href = location.href.split('#')[0] + "#pop";
        } 
	 
    // e.t.c.
  }
   
  });
  
  
  
// СНятие чекбокса дома
$('#h_adress').on('change', function() {
	$('#rent_home_id').attr('value',''); // Очистка дома
    sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/sahmatka/ajax_router.php?ctr=rentobjects&act=display_ag_ajax&sale=<?=$_GET['sale']?>',0); // Грузим содержимое селек
});
 
    	},
    	error: function(response) { // Данные не отправлены
            // $('#'+resultto).html('Ошибка. Данные не отправлены.');
			//alert('ajax error');
			//$('#'+progressid).hide();
			//$('#'+progressid).prop("display", 'none !important;');
			$('#'+progressid).addClass('hide');
    	}
 	});
}
	 
 
// Начальная загрузка данных 
sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/sahmatka/ajax_router.php?ctr=rentobjects&act=display_ag_ajax&sale=<?=$_GET['sale']?>',0); // Грузим содержимое селек
// Улицы
sendAjaxForm( 'street' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/sahmatka/ajax_router.php?ctr=rentobjects&act=sel_street',0); // Грузим содержимое селек
 
// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#rentsearch_ag input,#rentsearch_ag select" ).change(function() {
	   $('#rent_home_id').attr('value',''); // Очистка дома
	 sendAjaxForm( 'rent_search_result' , 'rentsearch_ag' , 'https://' . $GLOBALS['config']['domain'] . '/sahmatka/ajax_router.php?ctr=rentobjects&act=display_ag_ajax&sale=<?=$_GET['sale']?>',0,'progressbar','',function postload() {}); // Грузим содержимое селек
   // $('#maprentobjects').hide();
});

  
$('body').on('click', '#showmap', function(){
  $('#rentobjects').fadeOut(100);
  $('#showmap').fadeOut(100);
  $('#maprentobjects').fadeIn(600);
  $('#hidemap').fadeIn(300);
   return false;
});

$('body').on('click', '#hidemap', function(){
  $('#rentobjects').fadeIn(600);
  $('#showmap').fadeIn(300);
  $('#maprentobjects').fadeOut(100);
  $('#hidemap').fadeOut(100);
   return false;
});
 
});
 </script>
  
  <?
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function act__display()
	{		
		global $mysql;
		$q = $this-> get_base_sql();
		
		 // //print_r($_GET);
		 // print $q;
		  
		$data  = $mysql->get_arr( $q );
		//print_r($data);
		
		$c = count($data);
		if($c){$ct = 'НАЙДЕНО '.$c.' ПОМЕЩЕНИЙ';}
		else{$ct = 'Помещений не найдено';}
		
		
	?>
  <!-- Заголовок -->
  <section>
  
 
    <div class="container  ">
	<?
	if($_GET['rent_home_id'])
		{
			?>
			<div class="p16">
               <label for="h_adress"><b> <input type="checkbox" value="1" class="form_checkbox" id="h_adress" checked="checked" style="margin-left: 0;"> Помещения по адресу: <?=$data[0]['h_adress']?></b></label>
            </div>
			<?
		}
	?>
	</div>
	
	
    <div class="container flex_box">
      <div class="rent_h2"><?=$ct?> </div>
	  <?
	  if($c)
	  {
		?>
        <div class="rent_h2_a"><img src="https://em-nsk.ru/m2rent/images/map.svg" alt=""> &nbsp;  
		<a href="#" class="rent_a " id="showmap">Показать помещения на карте</a>
		<a href="#" class="rent_a " id="hidemap">Скрыть карту</a>
		</div>
        <?
	  }
	  ?>
	</div>
  </section> 
  <!--   -->
  

  <section>
    <div class="container rent_p">
 
	

<div id="rentobjects" class="rent_spoller">
<?
foreach($data as $k=>$v)
{
	
	// Назначения помещений
	$this->appointment[0] = 'Свободное назначение';
	$this->appointment[1] = 'Офисное';
	$this->appointment[2] = 'Магазин (торговое)';
	$this->appointment[3] = 'Под детский центр';
	  
	$this->separate_entrance[2]='Общий вход';
	$this->separate_entrance[2]='Отдельный вход';
	$this->separate_entrance[3]='Коридорный вход';
	    
	$v['h_adress'] = str_replace('Россия,','',$v['h_adress']);
	$v['h_adress'] = str_replace('Калининский район,','',$v['h_adress']);
	 
	if(ctype_digit(str_replace('.','',$v['area'])))
	{
		$area = 'Помещение – '.$v['area'].'м<sup>2</sup>';
	}
	elseif($v['area']){$area =$v['area']; }
	else{$area = 'Помещение';}
	$params='';
	
	
	
	// Параметры 
	$params.='<li>'.$v['h_build_type'].' </li>';
		
	//Мощность квт
	if( ctype_digit(str_replace(',','',$v['max_power_grid'])) )	{	$params.='<li>'.$v['max_power_grid'].' кВт</li>';	}
	elseif($v['max_power_grid'])	{		$params.='<li>'.$v['max_power_grid'].'</li>';	}
	
	
	if($v['floor'] && $v['floor']!=0){	$params.='<li>'.$v['floor'].' этаж </li>'; }
	elseif( $v['floor']==0){	$params.='<li> Цокольный этаж </li>'; }
	else{	  }
	
	if($v['appointment'] || $v['appointment']=="0"){$params.='<li>'.$this->appointment[$v['appointment']].' </li>';}
		   
	//if( $v['separate_entrance']  ){ $params.='<li>'.$this->separate_entrance[$v['separate_entrance']].'</li>'; }
	if( $v['place_for_unloading']  ){ $params.='<li>Место под разгрузку </li>'; }
	
	
	if( $v['rooms']>1  ){ $params.='<li> Помещений '.$v['rooms'].' </li>'; }
	else{ $params.='<li> Одно помещение </li>';}
	
	$v['params'] = $params;
	
	
	// вывод шаблонов 
	 if($v['show_b'])
	 {
		$this->tpl($v,'rentobjects','cat_one_item'); 
	 }
	 else
	 {
		$this->tpl($v,'rentobjects','cat_one_item'); 
		//$this->tpl($v,'rentobjects','cat_nb_one_item');  
	 }
				
}
?></div>


<div id="maprentobjects" class="rent_spoller" style="margin-top:20px;"> 
 <?
  if($c)
  {
	$this->act__map();
  }
  ?>
</div>



	   </div>
  </section>
  
  <?
	}
	
	
	
	
	
	
	
	
	function act__convert()
	{
		/*
		global $mysql;
		
		
		$arr = $mysql->get_arr('SELECT * FROM rent_objectsr ');
		
		foreach($arr as $k=>$v)
		{
			
			$data = array();
			$data['area'] = str_replace( ',', '.' , $v['area'] );
			
			if( $data['area'] )
			{
			 print $data['area'];
			 print '<br/>';
			
			 $mysql->update_for_key('rent_objects','rent_objects_id',$v['rent_objects_id'],$data);
			}
		}
		*/
	}
	
	/*
	.table-edit {
    display: inline-block;
    width: 16px;
    height: 16px;
    background: url(../images/edit.svg) 0 0 no-repeat;
    background-size: 100%;
}
	*/
	
	// Стили для скрытых и удаленных
	function display_table_col_attr($v)
	{
		if( !$v['show'] )
		{
			return 'style="color:#C0C0C0;" ';
		}
		if( $v['del'] )
		{
			return ' style="text-decoration:line-through;" ';
		}
	}
	
	
	
	// Стили  
	function display_table__h_adress($v)
	{
		// АДРЕС С ЯНДЕКС ПРИВОДИМ В ЧЕЛОВЕЧИЙ ВИД
		$adress_arr = explode(',',$v['h_adress']);
		$adress_arr_c = count($adress_arr);
		$adress = $adress_arr[$adress_arr_c-2].','.$adress_arr[$adress_arr_c-1];
		$adress = str_replace('улица ','ул. ',$adress);
		
		// $adress.=$v['h_adress'];
		 return $adress;
	}
	
	// Стили  
	function display_table__show_b($v)
	{
		if( $v['show_b'] ){ $show_b = '<b>ДА</b>'; }
	    else{ $show_b = 'НЕТ'; }
		// $adress.=$v['h_adress'];
		return $show_b;
	}
	
	// Стили для скрытых и удаленных
	function display_table__show($v)
	{
		if( $v['show'] ){ $show = '<b>ДА</b>'; }
	    else{ $show = 'НЕТ'; }
		// $adress.=$v['h_adress'];
		return $show;
	}
	
	
	
	
 
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"> 
			<? $this->filtr_select('Здание','rent_home_id','h_adress');	?>
		</div>	 
		
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="show_dell" name="show_dell" value="1" <? if($_GET['show_dell']){print ' checked="checked" ';} ?>> <label for="show_dell">Удаленные</label><br/>
		</div>
		
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="showhide" name="showhide" value="1" <? if($_GET['showhide']){print ' checked="checked" ';} ?>> <label for="showhide">Скрытые</label><br/>
		</div>
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="show_b" name="show_b" value="1" <? if($_GET['show_b']){print ' checked="checked" ';} ?>> <label for="show_b">С коммисией</label><br/>
		</div>
		
		
			
			 
		
		
		
		<?
	}
	
	
	
	function act__index()
	{
		global $mysql;
		global $t;
		$t['h1'] = 'Аренда - объекты';
 
		print '<pre>';
		//print_r($this->data);
		print '</pre>';
		$this->display_ajax_crud();
	}
	
	
	 

	
	
	
	
	
	
	
	
	
	
	// Поиск по id обекта и ид дома добавить
	// Склонение смотреть обект обекты
	
	function act__map($rent_home_id='',$href_perfix='https://em-nsk.ru/m2rent/?rent_home_id=' ) 
	{
		global $mysql;
		if(!$href_perfix){$href_perfix='';}
		
		$href_perfix='https://' . $GLOBALS['config']['domain'] . '/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=';
		
		 
		//Получаем все обекты
		$sql = $this->get_base_sql();
		$data_obj = $mysql->get_arr($sql,'','rent_home_id');
		// [дом][0]=обект
		////////////////// 
		 
		$q = 'SELECT rent_homes.* FROM  rent_homes    WHERE 1=1 ';
		if($rent_home_id)
		{
			$q .= ' AND rent_homes.rent_home_id = "'.$rent_home_id.'" ';
		}
		
		$data = $mysql->get_arr( $q ) ;
		 
		
		 // print '<pre>';
	 	 // print_r($data_obj); 
		 // print '</pre>';
		
		$i=0;
		foreach($data as $k=>$v)
		{
			if($v['lat'] && $v['lon'] && $v['adress'] && $data_obj[$v['rent_home_id']][0] )
			{
			$i++;
			 
			$name=$v['h_adress'].' '.$v['adress'] .' '. $v['build_type'];
		    $name='';
			
			$src = $data_obj[$v['rent_home_id']][0]['plan'];
			$area = 'Помещение: '.$data_obj[$v['rent_home_id']][0]['area'].'м<sup>2</sup>';
			
			$data_obj[$v['rent_home_id']][0]['h_adress'] = str_replace('Россия, Новосибирск, ','',$data_obj[$v['rent_home_id']][0]['h_adress']);
			$data_obj[$v['rent_home_id']][0]['h_adress'] = str_replace('Россия, ','',$data_obj[$v['rent_home_id']][0]['h_adress']);
			
			$text = $data_obj[$v['rent_home_id']][0]['h_adress'].'<br/> '.$data_obj[$v['rent_home_id']][0]['build_type'].'<br/> '.$data_obj[$v['rent_home_id']][0]['comment'];
	
			$cx='';
			foreach( $data_obj[$v['rent_home_id']] as $kkk => $vvv)
			{
				$cx .='<a class=\"rent_a iframerent\" style=\"color:#56A4ED;\" href=\"'.$href_perfix.$vvv['rent_objects_id'].'\">Помещение: '.$vvv['area'].'м<sup>2</sup></a><br/>';
			}
	 
			$oc = count($data_obj[$v['rent_home_id']]);
			
			if( !$_GET[id] ) // ТОлько для общей карты
			{	
				if($oc>1){$show_text='Все помещения';}
				else{$show_text='Подробнее о помещении';}
			}
			
			if($oc>1){ $oc = '('.$oc.')'; }
			else{$oc='';}
			
			// $baloon_content=' <img src=\''.$src.'\' style=\'max-width:100%; max-height:100px;\' /> <div style=\'color:#445C79; font-size:16px; font-weight:bold; padding-bottom:5px; padding-bottom:5px;\'>'.$area.'</div> <div style=\'max-width:200px;\'>'.$text.'</div> <div><a href=\''.$href_perfix.$v['rent_home_id'].''.'\' style=\'color:#56A4ED;\'>'.$show_text.' '.$oc.'</a></div>';
			 
			$baloon_content = $cx.'<a href=\'https://em-nsk.ru/m2rent/?rent_home_id='.$v['rent_home_id'].''.'\' style=\'color:#56A4ED;\'>'.$show_text.' '.$oc.'</a>';
			 
			$items_text.='{center: ['.$v['lat'].', '.$v['lon'].'], name: "'.$name.'",htmlcontent: "'.$baloon_content.'"}';
			if($i<count($data)){$items_text.=',';}
			}
		}
		?> 
<script>
	// Группы объектов
	var groups = [
			{
				name: "Обьекты недвижимости",
				style: "islands#redIcon",
				items: [
					<?=$items_text?>
				]}    
		];
	 	
	ymaps.ready(init);
	function init() {

    // Создание экземпляра карты.
    var myMap = new ymaps.Map('map', {
            center: [55.11141538533996, 82.93576671412082],
            zoom: 16 ,
            minZoom: 16,
            maxZoom: 18,
			controls: ['geolocationControl','routeButtonControl','zoomControl']
        } ),
        // Контейнер для меню.
        menu = $('<ul class="menu"/>');
        
    for (var i = 0, l = groups.length; i < l; i++) {
        createMenuGroup(groups[i]);
    }

    function createMenuGroup (group) {
        // Пункт меню.
        var menuItem = $('<li><a href="#">' + group.name + '</a></li>'),
        // Коллекция для геообъектов группы.
            collection = new ymaps.GeoObjectCollection(null, { preset: group.style }),
        // Контейнер для подменю.
            submenu = $('<ul class="submenu"/>');

        // Добавляем коллекцию на карту.
        myMap.geoObjects.add(collection);
        // Добавляем подменю.
        menuItem
            .append(submenu)
            // Добавляем пункт в меню.
            .appendTo(menu)
            // По клику удаляем/добавляем коллекцию на карту и скрываем/отображаем подменю.
            .find('a')
            .bind('click', function () {
                if (collection.getParent()) {
                    myMap.geoObjects.remove(collection);
                   // submenu.hide();
                } else {
                    myMap.geoObjects.add(collection);
                    //submenu.show();
                }
            });
        for (var j = 0, m = group.items.length; j < m; j++) {
            createSubMenu(group.items[j], collection, submenu);
        }
    }

    function createSubMenu (item, collection, submenu) {
        // Пункт подменю.
        var submenuItem = $('<li><a href="#">' + item.name + '</a></li>'),
        // Создаем метку.
            placemark = new ymaps.Placemark(item.center, { balloonContentFooter: item.name,balloonContentBody: item.htmlcontent});
			
			// Произвольный HTML балуна
				placemark2 = new ymaps.Placemark(myMap.getCenter(), {
				// Зададим содержимое заголовка балуна.
				balloonContentHeader: '<a href = "#">Рога и копыта</a><br>' +
					'<span class="description">Сеть кинотеатров</span>',
				// Зададим содержимое основной части балуна.
				balloonContentBody: '<img src="img/cinema.jpg" height="150" width="200"> <br/> ' +
					'<a href="tel:+7-123-456-78-90">+7 (123) 456-78-90</a><br/>' +
					'<b>Ближайшие сеансы</b> <br/> Сеансов нет.',
				// Зададим содержимое нижней части балуна.
				balloonContentFooter: 'Информация предоставлена:<br/>OOO "Рога и копыта"',
				// Зададим содержимое всплывающей подсказки.
				hintContent: 'Рога и копыта'
			});
			
			

        // Добавляем метку в коллекцию.
        collection.add(placemark);
        // Добавляем пункт в подменю.
        submenuItem
            .appendTo(submenu)
            // При клике по пункту подменю открываем/закрываем баллун у метки.
            .find('a')
            .bind('click', function () {
                if (!placemark.balloon.isOpen()) {
                    placemark.balloon.open();
                } else {
                    placemark.balloon.close();
                }
                return false;
            });
    }

    // Добавляем меню в тэг BODY.
    menu.appendTo($('#map_menu'));
 
	myMap.setBounds(myMap.geoObjects.getBounds(), {checkZoomRange:true}).then(function(){ if(myMap.getZoom() > 17) myMap.setZoom(17);});
	//myMap.setZoom(17);
	
	
	//на мобильных устройствах... (проверяем по userAgent браузера)
	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
		//... отключаем перетаскивание карты
		myMap.behaviors.disable('drag');
	}
	else
	{
		// myMap.behaviors.disable('scrollZoom'); // Запрет зума скролом
	}
	 

		
  myMap.geoObjects.events.add('balloonopen', function (e) {
            // Ссылку на объект, вызвавший событие,

            // можно получить из поля 'target'.

           // e.get('target').options.set('preset', 'islands#greenIcon');
		 
		   
		   
		   
		     $('.iframerent').magnificPopup({type:'iframe',
 // removalDelay: 100,
 // fixedContentPos: true, 
  //disableOn:1,
  mainClass: 'mfp-fade',
    mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
      removalDelay: 300,
   tLoading: 'Загрузка #%curr%...',
    callbacks: {
    open: function() {
      // Will fire when this exact popup is opened
      // this - is Magnific Popup object
    },
    close: function() {
        // parent.location.reload(true);  
    },
	open: function() {
          location.href = location.href.split('#')[0] + "#pop";
        } 
	 
    // e.t.c.
  }
   
  });
  
  
  

        })



  
  
  
}
</script>
    <style type="text/css">
        #map {
            width: 100%;
            height: 500px;
        }
         /* Оформление меню (начало)*/
        .menu {
            list-style: none;
            padding: 5px;

            margin: 0;
        }
        .submenu {
            list-style: none;

            margin: 0 0 0 20px;
            padding: 0;
        }
        .submenu li {
            font-size: 90%;
        }
            /* Оформление меню (конец)*/
    </style>
	
	
	<div id="map"></div>
	<div id="map_menu" style="display:none;"></div>
<?		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
 
	function act__card() 
	{
		global $mysql;
  
		//Получаем все обекты
		
		$sql = $this->get_base_sql();
		
		//print $sql;
		$v = $mysql->get_arr($sql,1);
		//print '<pre>';
		//print_r($v);
		//print '</pre>';
		
		 // Назначения помещений
		$this->appointment[0] = 'Свободное назначение';
		$this->appointment[1] = 'Офисное';
		$this->appointment[2] = 'Магазин (торговое)';
		$this->appointment[3] = 'Под детский центр';
		  
		$this->separate_entrance[2]='Общий вход';
		$this->separate_entrance[2]='Отдельный вход';
		$this->separate_entrance[3]='Коридорный вход';
		   
		   
	//	print_R($v);
		$v['h_adress'] = str_replace('Россия,','',$v['h_adress']);
		$v['h_adress'] = str_replace('Калининский район,','',$v['h_adress']);
		 
		if(ctype_digit(str_replace('.','',$v['area'])))
		{
			$area = 'Помещение – '.$v['area'].'м<sup>2</sup>';
		}
		elseif($v['area']){$area =$v['area']; }
		else{$area = 'Помещение';}
		
		$params='';
		// Параметры 
		$params.='<li>'.$v['build_type'].' </li>';
			
		//Мощность квт
		if( ctype_digit(str_replace(',','',$v['max_power_grid'])) )	{	$params.='<li>'.$v['max_power_grid'].' кВт</li>';	}
		elseif($v['max_power_grid'])	{		$params.='<li>'.$v['max_power_grid'].'</li>';	}
		
		if($v['floor'] && $v['floor']!=0){	$params.='<li>'.$v['floor'].' этаж </li>'; }
		elseif( $v['floor']==0){	$params.='<li> Цокольный этаж </li>'; }
		else{	  }
		
		if($v['appointment'] || $v['appointment']=="0"){$params.='<li>'.$this->appointment[$v['appointment']].' </li>';}
			   
		//if( $v['separate_entrance']  ){ $params.='<li>'.$this->separate_entrance[$v['separate_entrance']].'</li>'; }
		if( $v['place_for_unloading']  ){ $params.='<li>Место под разгрузку </li>'; }
		
		if( $v['rooms']>1  ){ $params.='<li> Помещений '.$v['rooms'].' </li>'; }
		else{ $params.='<li> Одно помещение </li>';}
	
	 
		
		?>	
<section>
  <div class="container-fluid rent_p ">
    <h1 class="rent_h3" style="margin: 46px 0px 31px 0px; font-family: 'Exo 2'"><?=$area?></h1>
    <div class="row">
      <div class="col-md-6 p10" style="text-align:center;">
	  
	   <style>
		.minimg img{ height:90px; margin:5px; }
		.slick-next
		{
		right:0;
		height: 100%;
		width:5%;
		z-index: 1;
		}
			
		.slick-prev
		{
			left:0;
			height: 100%;
			width: 5%;
			z-index: 1;
		}

		.slick-prev:before, .slick-next:before {
			padding-top: 5px;
			color:#333;
			font-family: 'slick';
			font-size: 40px;
			line-height: 1;
			opacity: .55;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}
		
		
		
		/* --- */
		.slick-slide img {
			display: inline-block;
			 max-width: 100%;
			 
		}
		.slick-slide   {
			 
			 height:auto;
		}
		 
		.slider-controll .slick-slide {
		  width: 100px;
		  height: 1000xp;
		  cursor:pointer;
		  border:2px solid transparent;
		}

		.slider-controll .slick-slide img {
		  width: 100%;
		  height: 100%;
		  object-fit:cover;
		  display: block;
		}

		.slider-controll .slick-slide.slick-current {
		  border-color: skyblue;
		}


		 </style>
		 
		 
	  <?
	  	$dir = 'rent/objects/'.$v['rent_objects_id'].'/';
		$files = scandir($dir);
	  ?>
	  
	  
	  <div class="slider_img_main">
	 <div style="text-align:center;"> <img src="<?=$v['plan']?>" alt="" class="rent_img"  ></div>
	   <?
		// print_r($files);
 		foreach($files as $file) 
		{
			if(is_file($dir.$file))
			{		
				$src=$dir.$file;
				if('jpg'==end(explode(".", $src)))
				{
					//https://em.m2profi.pro/thumbs/?name=sahmatka/logo90.jpeg
					?>  <div style="text-align:center; width:100%;" ><img class="rent_img"  src="/thumbs/?name=/sahmatka/<?=$src;?>&w=800&h=800"  /> </div><? 
				}
				else
				{
					?>  <div style="text-align:center;"><img class="rent_img"  src="<?=$src;?>" /></div><? 
				}							
			} 
		}
	  ?>
	  </div>

	  <br/>
	  <div class="slider_img" style="height:100px;">
	  <div  style="padding:2px;"><img src="<?=$v['plan'];?>" height="90" style="padding:10px;" /> </div>  
	  <?
		// print_r($files);
 		foreach($files as $file) 
		{
			if(is_file($dir.$file))
			{		
				$src=$dir.$file;
				if('jpg'==end(explode(".", $src)))
				{
					//https://em.m2profi.pro/thumbs/?name=sahmatka/logo90.jpeg
					?><div style="padding:2px;"> <img src="/thumbs/?name=/sahmatka/<?=$src;?>&w=90&h=90" width="90" height="90" />  </div> <? 
				}
				else
				{
					?><div  style="padding:2px;">  <img src="<?=$src;?>" width="90" height="90" /> </div> <? 
				}							
			} 
		}
	  ?>
	  </div>
      </div>
 
	<script>
	$( document ).ready(function() 
	{
		$('.minimg').click(function(e)
		{ 
			$('#bigimg').attr('src',$(this).attr('href'));
			return false;
		});
		
		
		
		
		 


		$('.slider_img_main').slick({
		  speed: 300,
		  slidesToShow: 1,
		  slidesToScroll: 1,
		  centerMode: true,
		   
			fade: true,
		  asNavFor: '.slider_img',
		  adaptiveHeight: true
		 
			//prevArrow: "<img src='https://svgshare.com/i/6Ei.svg' class='prev' alt='1'>",
			//nextArrow: "<img src='https://svgshare.com/i/6Gf.svg' class='next' alt='2'>",
		});

		 


		$('.slider_img').slick({
		  asNavFor: '.slider_img_main',
		  speed: 300,
		  slidesToShow: 4,
		  slidesToScroll: 3,
		  centerMode: true,
		  infinite: true,
		    focusOnSelect: true,
		  variableWidth: true,
		  adaptiveHeight: true
		 
			//prevArrow: "<img src='https://svgshare.com/i/6Ei.svg' class='prev' alt='1'>",
			//nextArrow: "<img src='https://svgshare.com/i/6Gf.svg' class='next' alt='2'>",
		});

 $('.slider22').slick({
	  infinite: true,
	  speed: 300,
	  slidesToShow: 1,
	  adaptiveHeight: true
});



	});
	</script>
			
      <div class="col-md-6 p10">
        <div style="margin: 0 0 29px; display: flex; justify-content: space-between;">
          <p>ID помещения <?=$v['rent_objects_id']?></p>
          <div>
            <a href="javascript:(print());">
              <img src="https://em-nsk.ru/m2rent/images/p1.svg" alt=""></a>
            <a href="" style="display:none;">
              <img src="https://em-nsk.ru/m2rent/images/m1.svg" alt="" style="padding-left: 20px;"></a>
          </div>
        </div>
        <p class="rent_a" style="font-weight: 700;color: #000000;"><?=$v['h_adress']?> / <?=$v['adress']?></p>
        <p>
          <a href="#map" class="rent_a"><img src="https://em-nsk.ru/m2rent/images/map.svg" alt="">Помещение на карте</a>
        </p>
		<br/>
        <p>
			<?=$v['comment']?>
		</p>
         <div>
          <p class="rent_subtitle">Технические характеристики</p>
          <ul class="rent_list2">
            <?=$params?>
          </ul>
        </div>
		<?
		if($v['status'] == 2 || !$v['status'] )
					{
						?>	
					 <a href="https://{$GLOBALS['config']['domain']}/sahmatka/iframe_router.php?ctr=rentobjects&act=order&id=<?=$v['rent_objects_id']?>">
          <button class="btn_bg_border" style="margin: 30px 0px 17px 0px;">
            <div class="btn_bg_text p20"> ОТПРАВИТЬ ЗАЯВКУ <i class="btn_arrowx"></i> </div>
          </button>
        </a> 
						<?
					}
				?>	
       
        <p class="form_rz_p"> Отправляя заявку вы автоматически даете свое <a href="https://em-nsk.ru/politics.docx"> согласие на обработку персональных данных</a></p>
      </div>
  
    </div>
  </div>
</section>
<br/>



<section>
<div class="container-fluid">
<div style="display: flex;">
	<img src="https://em-nsk.ru/m2rent/images/map.svg" alt="" style="margin: 0px 0px 13px 10px;">
	<p style="margin: 2px 0px 0px 10px;"><?=$v['h_adress']?> / <?=$v['adress']?></p>
</div>
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=9998badd-d4f7-462f-b4a5-9c3aa51768c0" type="text/javascript"></script>
<a name="map"></a>
<?
$this->act__map($v['rent_home_id']);
?>
</div>
</section>

<?
		
	}
		
		
		
		
		
		
		
	function act__order()
	{
		$this->act__fastorder();
		return;
		
		
		if( !$_POST )
		{
			$this->tpl($_POST,'rentobjects','orderfom'); 		 
		}
		elseif($_POST)
		{
			$mess_conf = array();
			$mess_conf['id']="ID";
			$mess_conf['name']="Имя";
			$mess_conf['phone']="Телефон";
			$mess_conf['area']="Площадь";
			$mess_conf['adress']="Адрес";
			$mess_conf['email']="E-Mail";
			$mess_conf['naz']="Назначение";
			$mess_conf['comname']="Название компании";
			$mess_conf['assortiment']="Ассортимент";
			$mess_conf['inn']="ИНН";
			$mess_conf['filial']="Действующие филиалы";
			$mess_conf['date']="Дата";
			$mess_conf['pozelan']="Пожелания";
			
			// 	$mess_conf['pozelan']="Пожелания";
			$mess = $this-> form_message( $_POST , $mess_conf,1);
			// print $message;
			
			$to = '89236470002@mail.ru,op@em-nsk.group';
 
			if( mail( $to , ' Заявка н аренду помещения m2profi ',$mess) )
			{
				?>
				<section>
					<div class="container " style=" text-align: center; max-width: 475px;">
					<h1 class="rent_h3" style="margin-top: 55px; font-family: 'Exo 2';">ВАША ЗАЯВКА ОТПРАВЛЕНА</h1>
					<p class="form_rz_text" style="margin-top: 47px;">
					Если вы не получите ответ в течение 3 рабочих дней,
					пожалуйста свяжитесь со службой качества 
					по телефону +7 (383) 347-47-00
					</p>
					</div>
				</section>
				<?
			}
			else
			{
				print 'Произошла ошибка при отправке формы, обратитесь в отдел арнеды по телефону';
			}
			
			
	 
		}
	}
	
	
	
	
	
	 
	
	function form_message( $data_arr='' , $mess_conf='' , $dev=0 )
	{
		$mess='';
		foreach( $data_arr as $k=>$v)
		{
			if( $mess_conf[$k] )
			{
				$mess.=$mess_conf[$k].': '. $data_arr[$k]."\r\n";
			}
			elseif( $dev )
			{
				$dev_mess.='<b>$mess_conf[\''.$k.'\']</b>="'.$data_arr[$k].'";<br/>';
			}
			else
			{
				
			}
		}
		if($dev){$mess.=$dev_mess;}
		return $mess;
	}
	
	
	
	
	
	 
	function act__broniform()
	{
		global $mysql;
	 
		$filtr = array();
		$filtr['id'] = $_GET['id'];
		$sql = $this->get_base_sql( $filtr );
		$data = $mysql->get_arr( $sql,1 );	
			 
		if(!$_POST)
		{
			if($data)
			{
				$this->tpl($data,'rentobjects','broniform'); 
			}
		}
		else
		{
			// print_r($_SESSION);
			$_POST['agency'] = $_SESSION['adm_caption'];
			$_POST['user'] = $_SESSION['sh_name'];
			$_POST['login'] = $_SESSION['sh_login'];
						
			$_POST['adress'] = $data['adress'];
			$_POST['area'] = $data['area'];
			$_POST['link'] = '<a href="https://em-nsk.ru/m2rent/?id='.$_GET['id'].'">Открыть на сайте</a>';
			  
			$mess_conf = array();
			$mess_conf['agency']="Агентство";
			$mess_conf['user']="Пользователь";
			$mess_conf['login']="Логин";

			$mess_conf['caption']="Название компании";
			$mess_conf['inn']="ИНН/ОГРН";
			$mess_conf['adress']="Интересующие адреса";
			$mess_conf['profile']="Профиль деятельности";
			$mess_conf['assortiment']="Ассортимент";
			$mess_conf['area']="Площадь";
			$mess_conf['opendate']="Планируемая дата открытия";
			$mess_conf['adress_d']="Адреса действующих филиалов";
			$mess_conf['treb']="Требования";
			$mess_conf['fio']="ФИО";
			$mess_conf['phone']="Телефон";
			$mess_conf['email']="E-Mail";
			$mess_conf['link']="Ссылка";
 
			$mess = form_message( $_POST , $mess_conf);
			
			// СТАВИМ БРОНЬ
			$this->add_broni( $data['rent_objects_id'] , 4 );		
			
			$to = '89236470002@mail.ru,op@em-nsk.group'; //
 
			//Собираем файлы
			 foreach($_FILES as $k=>$v)
			 {
				 $dir = sys_get_temp_dir();
				 $newfile = $dir.'/'.$_FILES[$k]["name"] ;
				 // print '<b>'.$newfile.'</b>';
				 if( move_uploaded_file($_FILES[$k]["tmp_name"],  $newfile ) )
				 {
					  $files[] =  $newfile;
				 }
			 }
			 
			if( $_SESSION['sh_login'] )
			{
				// Обработка формы бронирования
				  $this->add_broni_pf();
			}
			else
			{
				print 'ДОступ запрещен';
				return;
			}
			
			if( multi_attach_mail( $to , 'Бронь на аренду помещения', $mess, 'admin@m2profi.pro' , 'Бронь на аренду - em-nsk.ru'  , $files )  )
			{
				?>
				<section>
					<div class="container " style=" text-align: center; max-width: 475px;">
					<h1 class="rent_h3" style="margin-top: 55px; font-family: 'Exo 2';">ВАША ЗАЯВКА ОТПРАВЛЕНА!</h1>
					<p class="form_rz_text" style="margin-top: 47px;">
					Если вы не получите ответ в течение 3 рабочих дней,
					пожалуйста свяжитесь со службой качества 
					по телефону +7 (383) 347-47-00 
					</p>
					</div>
				</section>
				<?
			}
			else
			{
				print 'Произошла ошибка при отправке формы, обратитесь в отдел арнеды по телефону +7 (383) 207-30-15';
			}			
 
		}
	}
	
 
	
	





	
	
	function act__fastorder()
	{
		global $mysql;
		
		if( !$_POST )
		{
			if($_GET['id'])
			{
				$q= $this->get_base_sql();
				$data= $mysql->get_arr($q,1);
				
			// $data	
			}
			$this->tpl($data,'rentobjects','fastorderform'); 
		}
		elseif( $_POST )
		{
 
			$mess_conf = array();
			$mess_conf['caption']="Название компании";
			$mess_conf['inn']="ИНН/ОГРН";
			$mess_conf['adress']="Интересующие адреса";
			$mess_conf['profile']="Профиль деятельности";
			$mess_conf['assortiment']="Ассортимент";
			$mess_conf['area']="Площадь";
			$mess_conf['opendate']="Планируемая дата открытия";
			$mess_conf['adress_d']="Адреса действующих филиалов";
			$mess_conf['treb']="Требования";
			$mess_conf['fio']="ФИО";
			$mess_conf['phone']="Телефон";
			$mess_conf['email']="E-Mail";
 
			$mess = form_message( $_POST , $mess_conf);
 
			
 
			//Собираем файлы
			 foreach($_FILES as $k=>$v)
			 {
				 $dir = sys_get_temp_dir();
				 $newfile = $dir.'/'.$_FILES[$k]["name"] ;
				 // print '<b>'.$newfile.'</b>';
				 if( move_uploaded_file($_FILES[$k]["tmp_name"],  $newfile ) )
				 {
					  $files[] =  $newfile;
				 }
			 }
			 
			 
			$to = '89236470002@mail.ru,op@em-nsk.group'; //
			// multi_attach_mail( $to, 'Заявка на аренду помещения' , $mess, 'admin@m2profi.pro', $GLOBALS['config']['domain'] , $files);
				
				
			if( multi_attach_mail( $to , 'Заявка на аренду помещения', $mess, 'admin@m2profi.pro' , 'Заявка аренда - em-nsk.ru'  , $files ) )
			{
				?>
				<section>
					<div class="container " style=" text-align: center; max-width: 475px;">
					<h1 class="rent_h3" style="margin-top: 55px; font-family: 'Exo 2';">ВАША ЗАЯВКА ОТПРАВЛЕНА</h1>
					<p class="form_rz_text" style="margin-top: 47px;"> 
					Если вы не получите ответ в течение 3 рабочих дней,
					пожалуйста свяжитесь со службой качества 
					по телефону +7 (383) 347-47-00 
					</p>
					</div>
				</section>
				<?
			}
			else
			{
				print 'Произошла ошибка при отправке формы, обратитесь в отдел арнеды по телефону +7 (383) 207-30-15';
			}			
		
		
		
		 
		
		}
	}
	
	
	
	 
	
	
	
	
	
	
	
	
	
	
	
	
	// Обработка формы 
	function add_broni_pf()
	{
		global $mysql;
	 
		// Получаем данные места
		$data = $this->getid($_GET['id']);
		 
		// Проверяем текущий статус помещения  
		if( $data['status']  && $data['status']!='2' && 1==2 ) 
		{
			print 'Ошибка - конфликт статуса бронирования - вероятно место было забронированно другим пользователем, пока вы заполняли форму';
			return;
		}
		elseif($data['rent_objects_id'])
		{	
			if( 1==2 )
			{
				?><h2 style="color:red">Для бронирования необходимо загрузить указанные файлы</h2><?
			}
			else
			{
				//print 'Применение брони успешно';
				//print ' ID Брони: '; 
				$new_broni_id =  $this->add_broni($data['rent_objects_id'],4);
				
				 
			 
				
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
	
	
	
	
	
	// Добавление брони в базу
	function add_broni($id,$status)
	{ 
		global $mysql;
		 
		$data_space = $this->getid($id);
	  
		// Проверяем если не изменился пользователь и статус
		if($data_space['status'] != $status || 1==1)
		{
			// Записываем бронь
			$data = array();
			$data['rent_objects_id'] = $id;
			$data['status'] = $status;
			$data['date'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_first'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_fu'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['broni_up_counter'] = 0; // текущая дата
			$data['comment'] = ''; // текущая дата
			$data['user_id'] = $_SESSION['sh_id'];
			$broni_id = $mysql->insert('rent_broni',$data,1);
			
			// Обновляем статус в основной таблице
			$data = array();
			$data['status'] = $status;
			$data['status_broni_id'] = $broni_id;
	 
			$mysql -> update_for_key( 'rent_objects', 'rent_objects_id', $id , $data );
		}
		else{$broni_id = $broni_id;}
		return $broni_id;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function act__broni_history($space_id='',$ttitle=false)
	{
		if(!$space_id){$space_id = $_GET['id'];}
		if($_GET['nott']){$ttitle =false;}
		
		global $mysql;
		global $status_arr;
		global $status_color_arr;
	 
		$q = 'SELECT rent_objects.*,users.*,agency.caption as agcaption , rent_broni.status as b_status , rent_broni.date  , rent_broni.rent_broni_id ,rent_homes.*
		FROM rent_objects  
		LEFT JOIN rent_broni  ON rent_broni.rent_objects_id = rent_objects.rent_objects_id
		
		LEFT JOIN rent_homes  ON rent_homes.rent_home_id =rent_objects.rent_home_id
		 
		
		LEFT JOIN users ON users.id =rent_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		';
		if($space_id){ $q.=' WHERE rent_objects.rent_objects_id = "'.$space_id.'" '; }
		
		$q.=' ORDER BY rent_objects.rent_objects_id, rent_broni.date DESC';
	 
		$space_data = $mysql->get_arr($q );
		if(!$space_data[0]['rent_broni_id']){return;}
		// print '<pre>'; 
		// print_r($space_data );
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
			if(!$v['rent_broni_id']){continue;}
						 
			if($parking_space_id!=$v['parking_space_id'] && !$space_id)
			{
				?>
				<tr><td colspan="10" align="center">
				<?
				  if($v['rent_broni_id'] != $v['status_broni_id'] && $i_ps==0 )
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
				$parking_space_id = $v['parking_space_id'];
			}
			else // То же место следующая бронь
			{
				$i_ps++;
			}
			
			if($v['rent_broni_id'] == $v['status_broni_id']) {	 $style="font-weight:bold;";	$tb_text='(Текущий статус)'; } else{$style=""; $tb_text='';}
			?>
			<tr>
			<td style="<?=$style?>"><?=fromsql_date($v['date'])?></td>
			<td style="<?=$style?>"><?=$v['agcaption']?></td>
			<td style="<?=$style?>"><?=$v['login']?> (<?=$v['name']?>)</td>
			<td  style="<?=$style?>"><span style="background-color:<?=$status_color_arr[$v['b_status']]?>;" > <?=$status_arr[$v['b_status']]?> <?=$tb_text?></span></td>
			</tr>
			<?
			$i++;
		}
		?>
		</tbody>
		</table>
	 
		<?
		
	}
	
	
	
	
	
	
	
	
	
	
	
}