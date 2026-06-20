<?
  
		 
class ctr__objects
{
	
	function __construct()
	{
		
		
		 if(!isset($_GET['sdan'])){$_GET['sdan']=0;}
 
 
		// $this->sa = new sahmatka( $_SESSION , $connection );
		// $this->h = $this->sa->get_home_arr( $_GET['home'] );
		 
		 //print_r($h);
		// if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,0);}
		 
		 // ТОлько для админов
		 // if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,2);}
		 
		 // ТОлько для ОП
		//  if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,3);} 
		 
		 # Массив домов (Из базы)
			//$this->h_arr = $this->sa->get_homes_arr();
			//print '<pre>';
		//	print_r($h_arr);
			//print '</pre>';
   
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	function object_menux($action='objects')
	{
		$sa = $GLOBALS['sa'];
		$h = $sa->get_homes_arr();
		
		//print_r($h);
 
		foreach($h as $k=>$v)
		{
			if(isset($_GET['sdan']))
			{
				if($_GET['sdan']){if($v['complite']=="0"){continue;}}
				else{if($v['complite']=="1"){continue;}}
			}
			
			if( $_GET['home'] == $v['home_id'] )
			{
				$class='  class="mdef mdefth " ';  
			}
			else
			{ 
				$class='  class="mdef mdef" '; 
				if($v['show']==2){ $class='   class="mdef mdefa"     ';  }// ТОлько админам
				elseif($v['show']==3){ $class='   class="mdef mdefaop"     ';  } // Админам и отделу продаж
	 
				else{$class='  class="mdef"   '; }
				//$class.='" ';
			}
			
			
			?>
			
			<li style="padding:0;"><a href="ctrind.php?ctr=objects&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <?=$class?> ><?=$v['title']?></a> </li>
			<?
		}
	}
	
	
	
	 	function object_menu($action='objects')
	{
			$h_arr = $GLOBALS['sa']->get_homes_arr();
		?>
<style>
@media screen and (min-width: 1000px) {
  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
  .mobilenav{display:none;}
}
@media screen and (max-width: 1000px) {
  .mmenu{	display:none;		}
  .mobilenav{display:block; width:100%;}
  .nomobile{display:none;}
}
</style>
<script src="fancybox-3.0/dist/jquery.fancybox.min.js"></script>
<link rel="stylesheet" type="text/css" href="fancybox-3.0/dist/jquery.fancybox.min.css">

	 <script type="text/javascript">
	 
	 
	 
	 
 
 
 
 
 if( window.innerWidth >= 1000 ){
     
	 
	 
	 $(document).ready(function() {
     $("a.iframe").fancybox({
            maxWidth    : 600,
            maxHeight   : 600,
            
            width       : '1000px',
            height      : '70%',
            closeClick  : true,
 	 
 	'scrolling' : 'yes',

 afterClose: function () { // USE THIS IT IS YOUR ANSWER THE KEY WORD IS "afterClose"
       // parent.location.reload(true);
    },
	beforeLoad: function() {   
            if (this.width = $(this.element).attr('width')) {this.maxWidth = $(this.element).attr('width');} else {this.width = '800';}
            if (this.height = $(this.element).attr('height')) {this.maxHeight = $(this.element).attr('height');}    else {this.height = '100%';}
            },
            type : 'iframe',
            openEffect : 'elastic',
            closeEffect : 'elastic',
            arrows : false,
            closeClick : false,
            scrolling: 'auto',
            fitToView    : true,
            autoSize: true,
            //width: 300, // Вот отсюда я вытащил размеры
            //height: 200, //

            margin      : [10, 10, 10, 10],
            padding:    [39, 10, 10, 10],
            helpers : {
                overlay : {closeClick : false},
                title    : {type : 'inside_top' },
            }
			
        });
 });
 
 
 
 } else {
      //не выполнять
 } 
 </script>
			 <div style="width:100%; margin-bottom:10px;">
				<a href="ctrind.php?ctr=objects&sdan=0" class="mdef <? if(!$_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 12px; font-weight:bold; ">СТРОЯЩИЕСЯ</a> 
				<a href="ctrind.php?ctr=objects&sdan=1" class="mdef <? if($_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">СДАННЫЕ</a>
			 </div>
			 
			 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header"    >
			
			 <br/>
			 <ul class="mmenu">
		<?
		
		$this->object_menux($action);
		$class=' class="mdef" ';
		if( $_GET['action'] == 'objects2' )
		{
			$class='  class="mdef mdefth " ';  
		}
		?>
		
		<?
		if(($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  ) ||1==1)
		{
			if($_GET['sdan'])
			{
				
				foreach($GLOBALS['custom_apparts_all'] as $k=>$v)
				{
					?>
					<li style="padding:0; "><a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="mdef m2catalog_item_order iframe"><?=$v['homecaption']?></a></li>
					<?
				}
				 
			 
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
								?><option value="sahmatka/ctrind.php?ctr=objects&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <? if($v['home_id']==$_GET['home']){ print ' selected="selected" ';}?>><?=$v['long_title']?></option><?
							}
							?>
							<?
							if($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'demo_admin')
							{
								if(isset($_GET['sdan']))
								{
								?>
							 
								<option value="/sahmatka/form_order_custom.php?custom_home_id=101&custom_appart_id=1">Свечникова, 4/1</option>
								<option value="/sahmatka/form_order_custom.php?custom_home_id=102&custom_appart_id=1">Краузе 21/1 </option>
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
	
	
	
	
	
	
	
	
	function act__index()
	{
		if($_GET['home'])
		{
			$this->act__object($_GET['home']);
		}
		else
		{
			$this->act__objects();
		}
		
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 // все обекты
	 function act__objects()
	 {
		global $t;
		$t['h1'] = 'ОБЪЕКТЫ';
		
		$sa = new sahmatka( $_SESSION , $connection );
		$h = $sa->get_homes_arr();
		?>
		<style>
		.slick-initialized .slick-slide {
			display: inline-block;
		}
		.slick-slide {float:none;}
 
		.mdef{ padding:5px; padding-left:13px; padding-right:13px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	


		.objmenua .mdef{color:#000;  }
		  .mdefa{color:#FFA500;} /* ТОлько админам */
		.mdefaop{color:#999999;} /*  Админам и отделу продаж */


		.mdefth{color:#FFF; background-color:#00CDAD;  }			 
		.mdef:hover{color:#FFF; background-color:#00CDAD;}	


@media (max-width: 767px) {.show-mobile{display:auto;} .hide-mobile{display:none;}  }
	@media (min-width: 768px) {.show-mobile{display:none;} .hide-mobile{display:auto;} }		
								
		</style>
		 
 
 <section class="section-objects">
	<div class="container mobc">
 	
 
		
 <div class="page-header" style="margin-bottom:0;">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Объекты</div>
			 
			<div style="width:100%; text-align:right; padding-top:30px; cursor:pointer;" class="open_xxpanel hide-mobile">
				<div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
					<div style="display:table-cell; text-align:left; vertical-align: top;">
						<span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br/>
						<span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку в телефон</span>
					</div>
					<div style="display:table-cell; text-align:left; vertical-align: baseline;">
						<span style="font-size:14px;">И заходите в М2 PROFI как в приложение.</span>
					</div>
					<div style="display:table-cell; ">
					<img src="/l.png" />
					</div>
				</div>
			</div>
			 
		</div>
		
		
		
		<div style="width:100%;  padding-top:30px; padding-bottom:30px; cursor:pointer;" class="open_xxpanel show-mobile">
				<div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
					<div style="display:table-cell; text-align:left; vertical-align: top; width: 100%;">
						<span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br/>
						<span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку <br/>в телефон</span><br/><br/>
						<span style="font-size:14px;">И заходите в М2 PROFI<br/> как в приложение.</span>
					</div>
					<div style="display:table-cell; text-align:right; ">
					<img src="/l2.png" width="100" />
					</div>
				</div>
			</div>
			
			
		<?
				$this->object_menu();		
		?>
 
		<div class="objects">
			<div class="row">
				<?
				foreach($h as $k=>$v)
				{
					
					if(isset($_GET['sdan']))
					{
						if($_GET['sdan']){if($v['complite']=="0"){continue;}}
						else{if($v['complite']=="1"){continue;}}
					}
				?>
				
				<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
					<div class="object">
						<div class="object__title"><?=$v[long_title]?></div>
						<div class="object__pict">
							<img src="render/<?=$v[home_id]?>.jpg" alt="">
							<div class="object__info">
							<?
							// Не выводим адрес!
							if($v[adress333])
							{
								?>
								<div class="object__location"><?=$v[adress]?></div>
								<?
							}
							?>
							<div class="object__status object__status_sale"><?=$v[complite_text]?></div>
							</div>
						</div>
						<a href="ctrind.php?ctr=objects&home=<?=$v[home_id]?>&sdan=<?=$v['complite']?>" class="object__btn btn btn_arrow">К объекту<i></i></a>
					</div>
				</div>
					 <?
				}
				
				
				if($_GET['sdan'])
				{
					foreach($custom_apparts_all as $k=>$v)
					{
					?>
					<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
						<div class="object">
						<div class="object__title"><?=$v['homecaption']?></div>
						<div class="object__pict" style="border: solid 1px #EEE; text-align:center;">
							<img src="<?=$v['image_pb']?>" alt="" style="width:auto; display:inline-block;">
							<div class="object__info">
								<div class="object__status object__status_sale">сдан</div>
							</div>
						</div>
						<a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="iframe object__btn btn btn_arrow">К объекту<i></i></a>
						</div>
					</div>
					<?
					}
				}
					
					
					
				?> 

				
			</div>
		<a href="/sahmatka/yandex_feedx.php">XML Фид в формате Yandex</a>
		</div>
	</div>
</section>


<?		
		
	 }
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 // Отображение одного обекта
	function act__object()
	{
			global $t;
		$t['h1'] = 'ОБЪЕКТЫ';
		
		$sa = new sahmatka( $_SESSION , $connection );
		$h = $sa->get_homes_arr();
		?>
		<style>
		.slick-initialized .slick-slide {
			display: inline-block;
		}
		.slick-slide {float:none;}
 
		.mdef{ padding:5px; padding-left:13px; padding-right:13px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	


		.objmenua .mdef{color:#000;  }
		  .mdefa{color:#FFA500;} /* ТОлько админам */
		.mdefaop{color:#999999;} /*  Админам и отделу продаж */


		.mdefth{color:#FFF; background-color:#00CDAD;  }			 
		.mdef:hover{color:#FFF; background-color:#00CDAD;}	


		media (max-width: 767px) {.show-mobile{display:auto;} .hide-mobile{display:none;}  }
		@media (min-width: 768px) {.show-mobile{display:none;} .hide-mobile{display:auto;} }		
								
		</style>
		<?	
	}
	
	
	
	
	
 
 
 	// Массовая  обработка квартир 
	function mass_editor()
	{
		 if(trim($_POST[newprice]) &&  $_SESSION['sh_login'] == 'admin' )
		 {
				$_POST['newprice'] = str_replace(' ','',$_POST['newprice']); 
				$query = 'UPDATE `apartaments` SET `price` = "'.$_POST[newprice].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';

				foreach($_POST[editapart][$_GET[home]] as $k=>$v )
				{
					//print 'Новая цена для квартиры '.$k.' : '.$_POST[newprice].' <br/>';
					// формируем запрос на обновление цены по ид дома и хаты
					$query.= ' OR `apartment_num` = "'.$k.'" ';
				}
				$query.= ');';
				// print $query;
				$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
				 
		 }
		 if(trim($_POST[newplan]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
		 {
				$query = 'UPDATE `apartaments` SET `image_pb` = "'.$_POST[newplan].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
				foreach($_POST[editapart][$_GET[home]] as $k=>$v )
				{
					print 'Новая планировка для квартиры '.$k.' : '.$_POST[newplan].' <br/>';
					// формируем запрос на обновление цены по ид дома и хаты
					$query.= ' OR `apartment_num` = "'.$k.'" ';
				}
				$query.= ');';
				// print $query;
					$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
			
		 }	  
		  if(trim($_POST[newarea]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
		 {
				$query = 'UPDATE `apartaments` SET `area` = "'.$_POST[newarea].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
				foreach($_POST[editapart][$_GET[home]] as $k=>$v )
				{
					print 'Новая площадь  для квартиры '.$k.' : '.$_POST[newarea].' <br/>';
					// формируем запрос на обновление цены по ид дома и хаты
					$query.= ' OR `apartment_num` = "'.$k.'" ';
				}
				$query.= ');';
				// print $query;
					$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
			
		 }	 
		 if(trim($_POST[newrooms]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
		 {
				$query = 'UPDATE `apartaments` SET `rooms` = "'.$_POST[newrooms].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
				foreach($_POST[editapart][$_GET[home]] as $k=>$v )
				{
					print 'Новое количество комнат для квартиры '.$k.' : '.$_POST[newrooms].' <br/>';
					// формируем запрос на обновление цены по ид дома и хаты
					$query.= ' OR `apartment_num` = "'.$k.'" ';
				}
				$query.= ');';
				
				//print $query;
				
					$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
			
		 }	 
		  if(trim($_POST[newtext]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
		 {
				$query = 'UPDATE `apartaments` SET `text` = "'.$_POST[newtext].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
				foreach($_POST[editapart][$_GET[home]] as $k=>$v )
				{
					print 'Новое примечание для квартиры '.$k.' : '.$_POST[newtext].' <br/>';
					// формируем запрос на обновление цены по ид дома и хаты
					$query.= ' OR `apartment_num` = "'.$k.'" ';
				}
				$query.= ');';
				// print $query;
					$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
			
		 }	
		if(trim($_POST[newstatus]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование Статуса
		 {
		 
		 
				foreach($_POST[editapart][$_GET[home]] as $k=>$v )
				{
					// print '<pre>';
					//  print_r($_POST);
					// print '</pre>';
					 
					$home_id=(int) $_GET[home];
					$section_id=$_POST[editapart_section][$_GET[home]][$k];
					$floor=$_POST[editapart_floor][$_GET[home]][$k];
					$apartments=$_POST[editapart_apartaments][$_GET[home]][$k];
		 
					$user_id=1;
					$status=$_POST[newstatus];
					
				// print 'Новый статус для квартиры '.$k.' : '.$_POST[newstatus].' <br/>';
								 
				 
					$bron_id = $sa->new_broni($home_id,$k,$status,0);
				
					add_log('Статус квартиры изменен администратором');
	 
				}
		 }
		
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
	 
 
	
	
}