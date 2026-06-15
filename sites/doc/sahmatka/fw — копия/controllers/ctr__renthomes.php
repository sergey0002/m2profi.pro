<?
class ctr__renthomes extends ctr__
{  

	var $table = 'rent_homes'; //Главная таблица
	var $key_filed = 'rent_home_id'; // Ключевое поле главной таблицы
	var $ctr = 'renthomes';
    var $title = 'Аренда - здания';
   
   
   
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
		$titles['adress'] = 'Адрес';
		$titles['build_type'] = 'Тип здания';
		$titles['edit'] = 'Действия';  
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
	
	
	

 
 
 	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		
		foreach($_GET as $k=>$v){  $filtr_data[$k]=$v;	}
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.* FROM  '.$this->table.'    WHERE 1=1 ';
		
		if(!$filtr_data['showdel']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		
		// if($_GET['id']){$q.=''}
		 //  print $q;
		return $q;
	}
	
	
	

	// Метод содержимого столбца
	function display_table__edit($row)
	{
		global $t;
		$t['h1'] = 'Аренда - здания';
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
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
		$t['h1'] = 'Редактирование здания';
		
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
		
		
		if(!$_POST) ############# ФОРМА
		{
		?>
		
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
					
			<form action="<?=$r->acturl($this->ctr,'edit');?>&id=<?=$_GET['id']?>" method="POST" id="editform"  >
		 
			<div id="tree_check"></div>

			<br/><br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
			<div class="row">
				<div class="col-md-6">
				<?
					// print$filed->text('caption','Заголовок',$data['caption']); print '<br/>';
					print $filed->text('build_type','Тип здания',$data['build_type']); print '<br/>';
					
					print $filed->text('street','Улица (для поиска)',$data['street']); print '<br/>';
					//print $filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 100px;" '); print '<br/>';
				?>
				</div>
				<div class="col-md-6">
				<?
					//$filed->image('photo','Фото ',$data['image'],'34256'); print '<br/>';
				?>
				</div>
			</div>
			<?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['lat'];
				$data_map['lon'] = $data['lon'];
				$data_map['adress'] = $data['adress'];
				$filed->map('map',$data_map);  
			?>
			</form>
		
 		
			</div>
	
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
			if(!$id){$id=$_GET['id'];}
			
			//print '<pre>';
			//print_r($_POST);
			//print '</pre>';

			$data = array();
			$data['caption'] = $_POST['caption'];
			$data['build_type'] = $_POST['build_type'];
			$data['descr'] = $_POST['descr'];
			$data['adress'] = $_POST['map_adress'];
			$data['lat'] = $_POST['map_lat'];
			$data['lon'] = $_POST['map_lon'];
			$data['street'] = $_POST['street'];
			
			
			if($id) // Редактирваоние существующей записи
			{
				//	print 'Изменения сохранены!';
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
	
	
 
 
	
	function act__index()
	{
		
		global $t;
		$t['h1'] = 'Аренда - здания';
	 
		$this->display_ajax_crud();
	}
	
	
	 
	// Поиск по id обекта и ид дома добавить
	// Склонение смотреть обект обекты
	
	function act__map($href_perfix='',$object_id='')
	{
		global $mysql;
		if(!$href_perfix){$href_perfix='';}
		
		 
		//Получаем все обекты
		$sql = ' SELECT rent_objects.*, rent_homes.adress as h_adress FROM  rent_objects LEFT JOIN rent_homes ON rent_homes.rent_home_id = rent_objects.rent_home_id  WHERE 1=1 AND rent_objects.del=0';
		$data_obj = $mysql->get_arr($sql,'','rent_home_id');
		// [дом][0]=обект
		////////////////// 
		
		
		$data=$mysql->get_arr( $this->get_base_sql() ) ;
		
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
	
			$oc = count($data_obj[$v['rent_home_id']]);
			
			//if($oc>1){$show_text='Все помещения';}
			//else{$show_text='Подробнее о помещении';}
			
			if($oc>1){ $oc = '('.$oc.')'; }
			else{$oc='';}
				
				 
			$baloon_content=' <img src=\''.$src.'\' style=\'max-width:100%; max-height:100px;\' /> <div style=\'color:#445C79; font-size:16px; font-weight:bold; padding-bottom:5px; padding-bottom:5px;\'>'.$area.'</div> <div style=\'max-width:200px;\'>'.$text.'</div> <div><a href=\''.$href_perfix.'?id=x'.'\' style=\'color:#56A4ED;\'>'.$show_text.' '.$oc.'</a></div>';
			
			
			$items_text.='{center: ['.$v['lat'].', '.$v['lon'].'], name: "'.$name.'",htmlcontent: "'.$baloon_content.'"}';
			if($i<count($data)){$items_text.=',';}
			}
		}
		?>
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=9998badd-d4f7-462f-b4a5-9c3aa51768c0" type="text/javascript"></script>
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
            center: [50.443705, 30.530946],
            zoom: 14
        }, {
            searchControlProvider: 'yandex#search'
        }),
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
    // Выставляем масштаб карты чтобы были видны все группы.
    myMap.setBounds(myMap.geoObjects.getBounds());
}

</script>
    <style type="text/css">
        #map {
            width: 95%;
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
	
	
	
	
	
	
}