<?
class ctr__parking extends ctr__
{  

	var $table = 'parking'; //Главная таблица
	var $key_filed = 'parking_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking';
    var $title = 'Парковки';
   
   
  
   
 	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		global $mysql;
		
		$filtr_data = $_REQUEST;
	 
		$q='SELECT rent_broni.*, rent_homes.adress as h_adress, rent_homes.build_type as h_build_type 
		FROM rent_broni
		LEFT JOIN rent_objects ON rent_broni.rent_objects_id= rent_objects.rent_objects_id
		LEFT JOIN rent_homes ON rent_homes.rent_home_id = rent_objects.rent_home_id
		WHERE 1=1 
		AND `rent_objects`.`del` = "0" ';
		 
		/*
		// id  
		if( $_GET['act']!='hide' && $_GET['act']!='show' && $_GET['act']!='del'  )
		{
			if( $filtr_data['id']    ){ $q.=' AND `rent_objects`.`rent_objects_id`  = "'.$filtr_data['id'].'" ';  }
		}
		// show  
		if( !$filtr_data['showhide'] ){ $q.=' AND `rent_objects`.`show`  = "1" ';  }
		
		// del  
		if( !$filtr_data['shodel'] ){ $q.=' AND `rent_objects`.`del`  = "0" ';  }
		
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
		if( $filtr_data['street'] ){ $q.=' AND rent_homes.street  like "%'.$filtr_data['street'].'%" ';  }

		// if($_GET['id']){$q.=''}
		//  print $q;		
		*/
		return $q;
	}
	
	
	

	// Метод содержимого столбца
	function display_table__edit($row)
	{
		 
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
	
		
	
	function formpanel($backlink='')
	{
		if(!$backlink){$backlink = $this->backlink;}
		?>
		<div class="row">
			<div class="col-md-3"  style="text-align:left;"><a href="<?=$backlink?>" class="forminformpanel_link">Назад</a> </div>
			<div class="col-md-6"  style="text-align:center;" class="forminform"><?=$this->forminform?> </div>
			<div class="col-md-3"  style="text-align:right;"><a href="#" onclick="document.getElementById('editform').submit(); return false;" class="forminformpanel_link" style="text-align:right;">Сохранить</a></div>
		 </div>
		<br/>
		<hr/>
		<?
	}
	
	
	
	
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование объекта';
		
		global $filed;
		global $mysql;
		global $r;
		 
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
		
		if(!$_POST) ############# ФОРМА
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
				  
				  $filed->select('rent_home_id','Здание',$sel_arr,$data['rent_home_id']);
				  $filed->text('adress','Адрес (Кабинет/Офис)',$data['adress']); print '<br/>';
				  $filed->text_num('floor','Этаж',$data['floor']); print '<br/>';
				  $filed->text_float('area','Площадь',$data['area']); print '<br/>';
				  
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
			print '<br><br>';
	
			$filed->checkbox('show','Отображать на сайте',$data['show']); print '<br/>';
			$filed->text('plan','Ссылка на планировку',$data['plan']); print '<br/>';
			$filed->text('free_plan','Свободная планировка',$data['free_plan']); print '<br/>';
			$filed->text('max_power_grid','Нагрузка электросетей',$data['max_power_grid']); print '<br/>';
			$filed->textarea('comment','Описние',$data['comment']); print '<br/>';
			$filed->text('rooms','Помещений',$data['rooms']); print '<br/>';
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
 
		if($_POST) ############# Обработка данных пост
		{
			// print_r($_POST);
			$data = array();
			$data['rent_home_id'] = $_POST['rent_home_id'];
			$data['adress'] = $_POST['adress'];
			$data['floor'] = $_POST['floor'];
			$data['area'] = $_POST['area'];
			$data['free_plan'] = $_POST['free_plan'];
			$data['max_power_grid'] = $_POST['max_power_grid'];
			$data['plan'] = $_POST['plan'];
			$data['comment'] = $_POST['comment'];
			$data['separate_entrance'] = $_POST['separate_entrance'];
			$data['appointment'] = $_POST['appointment'];
			$data['rooms'] = $_POST['rooms'];
		//	$data['street'] = $_POST['street'];
			
			
			if(!$_POST['show'])	{	$data['show'] = 0;	}else{	$data['show'] = 1;	}
			if(!$_POST['place_for_unloading'])	{	$data['place_for_unloading'] = 0;	}else{	$data['place_for_unloading'] = 1;	}
				 
			if($id) // Редактирваоние существующей записи
			{
				print 'Изменения сохранены!';
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
			}
			else // Добавление новой записи
			{
				//	print 'Запись добавлена!';
				$mysql -> insert( $this->table , $data );
			}
			$this->act__index();
		}
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
        <div class="rent_h2_a"><img src="/m2rent/images/map.svg" alt=""> &nbsp;  
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
	
	
 
?>
      <!-- Карточка объекта -->
      <div class="row rentcard">
        <div class="col-md-12">
          <div class="grey_shadow">
            <div class="row" style="padding: 13px 0px 13px 0px;">
              <div class="col-lg-4 p10" style="text-align:center;"> <img src="<?=$v['plan']?>" alt="" class="rent_img" style="max-height:250px;"> </div>
              <div class="col-lg-5 p10">
                <p class="rent_h3"><?=$area?></p>
                <p><?=$v['h_adress']?></p>
                <p>
                  <a href="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>#map" class="rent_a iframerent"><img src="/m2rent/images/map.svg" alt="">Помещение на карте</a>
                </p>
				<br/>
                <p><?=$v['comment']?></p>
 
                <p><a href="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>" class="rent_a iframerent" style="text-decoration: underline;">Подробнее о помещении</a></p>
              </div>
              <div class="col-lg-3 p10"> 
                <div class="rent_tech">
                  <div class="rent_subtitle">Технические характеристики</div>
                  <div style="margin-top: 12px; margin-bottom: 20px;">
                    <ul class="rent_list">
						<?=$params?>
                    </ul>
                  </div>
                </div>
                <a class="iframerent" href="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>">
                  <button class="btn_bg_border">
                    <div class="btn_bg_text p20"> ОТПРАВИТЬ ЗАЯВКУ <i class="btn_arrowx"></i> </div>
                  </button>
                </a>

              </div>
            </div>
          </div>
        </div>
        <!--  -->

      </div>

<?			
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
	
	
	
	function act__index()
	{
		
		global $t;
		$t['h1'] = 'Парковки';
		
		global $r;
		/*
		
		
		$_REQUEST['showhide'] = 1; // показывать скрытое
		$sql =  $this->get_base_sql();
		$arr = $this->mysql->get_arr($sql);
		
		//print '<pre>';
		//print_r($arr);
		//print '</pre>';
		
		$titles['rent_objects_id'] = 'id';
		$titles['h_adress'] = 'Адрес';
		$titles['adress'] = 'Офис';
		$titles['h_build_type'] = 'Тип здания';
		$titles['floor'] = 'Этаж';
		$titles['area'] = 'Площадь';
		$titles['max_power_grid'] = 'эл.сети';
		$titles['free_plan'] = 'Свободная планировка';
		$titles['edit'] = 'Действия'; 
		  
		?>
		
		<div style="text-align:right; width:100%; padding:20px; padding-left:0; padding-right:0;" class="add_buttons">
		<a href="<?=$r->acturl($this->ctr,'edit')?>" class="btn_2">Добавить </a>
		</div>

			<div id="ajaxcontent" class="stat">
				<div class="stat-top">
					<!-- Панель поиска -->
					<a href="JavaScript:window.print();" class="stat-top__print" ></a>
				</div>
				<div class="stat-table stat-table-user stat-table_notpd table">
					 <?
					 	$this ->display_table($arr,$titles);
					 ?>		 
				</div>
			</div>			
			<?
			*/
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
	
	
	
	
	
	 
	 
	
	
	
	
	
	 
	
	
 
	
	
	
	
	
	
	
	
	
}