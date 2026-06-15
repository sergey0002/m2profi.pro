<?
class ctr__parking_buildings extends ctr__
{  

	var $table = 'parking_buildings'; //Главная таблица
	var $key_filed = 'parking_building_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking_buildings';
    var $title = 'Парковки - здания';
   
 
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
		$titles['order'] = 'Сортировка';
		$titles['adress'] = 'Адрес';
		$titles['show'] = 'Показывать';
		$titles['complite'] = 'Сдан';
		$titles['edit'] = 'Действия'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['adress']='adress';
		$order['show']='`show`';
		 
		$this->ajcrud_table_order=$order; 
		$this->aj_crud_addbutton=1;
		$this->aj_crud_addbutton_iframe=0; // Новый обект в ифрейме
	}
 
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.* FROM  '.$this->table.'    WHERE 1=1 ';
		
		if(!$_GET['show_dell']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		else{$q.=' AND `'.$this->table.'`.`del`="1" ';}
		
		
		if( $filtr_data['parking_building_id'] )
		{
			$q.=' AND parking_buildings.parking_building_id = "'.$filtr_data['parking_building_id'].'" '; // Только актуальные брони без истории
		}
		
		if( $filtr_data['order_filed'] )
		{
			//$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			//if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				//$q.=' ORDER BY parking_broni.date'; 
				//$q.='  DESC ';  
			
		}
		// if($_GET['id']){$q.=''}
		 //  print $q;
		return $q;
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
					// $filed->text('caption','Заголовок',$data['caption']); print '<br/>';
					// $filed->text('build_type','Тип здания',$data['build_type']); print '<br/>';
					
					$filed->text('street','Улица (для поиска)',$data['street']); print '<br/>';
					$filed->text('adress_disp','Адрес (для поиска)',$data['adress_disp']); print '<br/>';
					?>
					  
					<?=$filed->text('order','Порядок сортировки',$data['order']);?><br/>
				
					<?
						$data_sel=array();
						$data_sel[1] = 'Всем';
						$data_sel[2] = 'Админам';
						$data_sel[3] = 'Админам и ОП';
						$data_sel[0] = 'НЕТ';
					?>
					<?=$filed->select('show','Показывать',$data_sel,$data['show']);?><br/>
 
					<?
					$filed->date('delivery_date','Дата сдачи',$data['delivery_date']);
					?><br/>
					<?=$filed->text('complite_text','Состояние готовности',$data['complite_text']);?><br/>
					<?=$filed->checkbox('complite','Дом сдан',$data['complite']);?><br/>
					
					
					<?
					
					
					//$filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 100px;" '); print '<br/>';
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
			// $data['build_type'] = $_POST['build_type'];
			//$data['descr'] = $_POST['descr'];
			$data['adress'] = $_POST['map_adress'];
			$data['lat'] = $_POST['map_lat'];
			$data['lon'] = $_POST['map_lon'];
			$data['street'] = $_POST['street'];
			
			$data['adress_disp'] = $_POST['adress_disp'];
			$data['order'] = $_POST['order'];
			$data['show'] = $_POST['show'];
			$data['delivery_date'] = $_POST['delivery_date'];
			$data['complite_text'] = $_POST['complite_text'];
			$data['complite'] = $_POST['complite'];
				
				
				
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
	
	

	function display_table__show($data)
	{
		$show=array();
		$show[0] = 'НЕТ';
		$show[1] = 'Всем';
		$show[2] = 'Админу';
		$show[3] = 'Админу и ОП';
		
		return $show[$data['show']];
	}
	function display_table__complite($data)
	{
		$show=array();
		$show[0] = 'НЕТ';
		$show[1] = 'ДА';
 
		return $show[$data['complite']];
	}
	
	
	
	
	
	
    
	
	function act__index()
	{
		global $t;
		$t['h1'] = 'Парковки - здания';
  
		$this->display_ajax_crud();
	}
	 
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
	// Поиск по id обекта и ид дома добавить
	// Склонение смотреть обект обекты
	
	function act__map($parking_building_id='2',$href_perfix='https://rent.d-at.ru/parking_one/?id=' ) 
	{
		?>
		
		<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=9998badd-d4f7-462f-b4a5-9c3aa51768c0" type="text/javascript"></script>
	
		<?
		global $mysql;
	   
		$q = 'SELECT * FROM  parking_buildings    WHERE 1=1 ';
		if($parking_building_id)
		{
			$q .= ' AND parking_buildings.parking_building_id = "'.$parking_building_id.'" ';
		}
		
		$data = $mysql->get_arr( $q ) ;
 
		  
		  /*
		    [parking_building_id] => 1
            [caption] => 
            [adress] => Новосибирск, Калининский район, микрорайон Родники, улица Свечникова, 4
            [adress_disp] => Свечникова, 4
            [lat] => 55.11238841356582
            [lon] => 82.94587539814762
            [street] => Свечникова
            [show] => 1
            [order] => 2
            [delivery_date] => 
            [complite] => 1
            [complite_text] => сдана
            [del] => 0
            [i] => 0
        )
		*/
		
		$i=0;
		foreach($data as $k=>$v)
		{
			if($v['lat'] && $v['lon'] && $v['adress'] )
			{
			$i++;
			 
			$name=$v['adress_disp'];
  
			$baloon_content = '<a href=\''.$href_perfix.$v['parking_building_id'].''.'\' style=\'color:#56A4ED;\'>Перейти</a>';
			 
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
	
	
	
	
	
	
	
	
}