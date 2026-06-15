<?
class ctr__nodes extends ctr__
{  

	var $table = 'fw_nodes'; //Главная таблица
	var $key_filed = 'fw_node_id'; // Ключевое поле главной таблицы
	var $ctr = 'nodes';
   
  
   /*
   
   0 !!!!!!!!! В ГЕТ НОДЕ НЕ РАБОТАЕТ ОПЦИЯ ВКЛЮЧЕНИЯ ТЕКУЩЕЙ!  БЕЗ НЕЕ НЕ СТРОИТСЯ ДЕРЕВОД В jsoon!!!!
   
  В дирах регенерация пачей и name
  
  ПЕРЕДЕЛАТЬ НА НОДЫ из place
  +На их базе новости , и места одна таблица ноды
  
  +++ ВЫВОД СДЕЛАТЬ ОДЕЖДЫ ИТП С ПОДКЛЮЧАЕМОЙ ПОСТРАНИЧКОЙ
  + МЕНЮ КАТЕГОРИЙ
  
   
   1. Валидация
   2. Карта этажа (с ид магазина )
   3. ОЧИСТКА ПОЛЕЙ ФАЙЛОВ (удаление!)
   
   4. 
*/

 
  
	function __construct()
	{
		global $mysql;
		
		$this->title='Арендаторы';
		$GLOBALS['t']['title']=$this->title;
		//$this->post_type = '3'; // Тип поста
		
		// $this->session_form_save();
		
		// Получаем данные 
		$this->data_arr = $mysql->get_arr($this->get_base_sql());
		
		 
		 
		/*
		В кострукторе  
		  1. Отображаемые поля $table_fieds[]='filed';
		  2. Отображаемые по умолчанию $table_fieds_show[]='filed';
		  3. по которым производится сортировка $table_fieds_order[]='filed';
		  4. Сортировка по умолчанию $table_fieds_order_default='filed';
		  5. Метод Панели рдактирования
		  6. Метод кнопки добавления 
		  
		  поля для текстового поиска
		*/
		  
  
		$dir_data_arr = $mysql->get_arr('SELECT * FROM `dir` WHERE  dir_type="'.$this->post_type.'"',true,'dir_id' ); // Данные для селекта Разделов
		$this->dir_date[0]='- Не указан - ';
		foreach($dir_data_arr as $k=>$v)
		{
			$this->dir_date[$k] = $v['dir_title'];
		}
		 
		 
		 
		 
		// Этажи
		$this->floor_date[0]=0;
		$this->floor_date[1]=1;
		$this->floor_date[2]=2;
		$this->floor_date[3]=3;
		$this->floor_date[4]=4;
		$this->floor_date[5]=5;
		
		$this->pin_node[0] = 'Стандартное размещение';
		$this->pin_node[1] = 'Первое место';
		$this->pin_node[2] = 'Второе место';
		$this->pin_node[3] = 'Третье место';
	}
	
  
	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		
		$filtr_data = $_GET;
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.*,    CONCAT(dir.dir_id, "#") AS dirids,  CONCAT(dir.dir_title, "#") AS dirttls';

		//if( $_REQUEST['dir'] ) { $q.= ', count(dir) as c ';}

        $q.=' FROM `'.$this->table.'`
		
		LEFT JOIN dir2node ON dir2node.node_id = `'.$this->table.'`.`'.$this->key_filed.'`
		LEFT JOIN dir ON dir2node.dir_id = dir.dir_id
				
		
		WHERE  `'.$this->table.'`.`del` != "2" ';
		
		// Типы постов
		if( $this->post_type ){ $q.=' AND type = "'.$this->post_type.'" ';}
		
		
		// Фильтрация раздела
		if( $_REQUEST['dir']){ $q.=' AND dir.dir_id = "'. $_REQUEST['dir'].'" ';}
		
		
		
		if( $_REQUEST['id']){ $q.=' AND '.$this->table.'.'.$this->key_filed.' = "'. $_REQUEST['id'].'" ';}
		
		// Удаленные
		if( !$_REQUEST['show_dell']){ $q.=' AND '.$this->table.'.del = "0" '; }
		else{ }
		 
		if( $_REQUEST['act']!='sel_dir' || 1==1 )
		{
			$search_arr = array();
			$search_arr[]=''.$this->table.'.title';
			$search_arr[]=''.$this->table.'.descr';
			if( $_REQUEST['search']){ $q.=$mysql->search($search_arr,$_REQUEST['search']); }
		}
		
		if($where){$q.=' '.$where.' ';}
		if( $_GET['pin_node'] || $_GET['pin_node']=="0") { $q.=' AND '.$this->table.'.pin_node = "'.$_GET['pin_node'].'" ';}
		
		$q.=' GROUP BY `' . $this->table . '`.`'.$this->key_filed.'`  ';
		
		// Группировка (для вывода селектов)
		// if( $_GET['act']=='sel_dir' ){ $q.=' GROUP BY dir ORDER BY dir';}
		// elseif( $_GET['act']=='sel_section' ){ $q.='GROUP BY section ORDER BY section';}
		
		 if( $_REQUEST['order_filed'] )
		{
			 $q .= ' ORDER BY '. $_REQUEST['order_filed']; 
			 if( $_REQUEST['order_asc'] ){  $q .= ' ASC ';  }
			 else{  $q .= ' DESC ';  }
		}
		else{ 
		
		$q .= ' ORDER BY '.$this->table.'.add_time DESC '; 
		}
		
		// Лимит записей
		if( $_GET['limit'] )
		{
			$q .= ' LIMIT 0,'.$_GET['limit'].' '; 
		}
		
		
		
		 
 
		 //  print $q;
		return $q;
	}
	
	
	
	function act__show( )
	{
		global $mysql;
		if(!$_GET['id'])
		{
			$sql = $this->get_base_sql();
			$data = $mysql->get_arr($sql);
			return $data;
		}
		else
		{ 
			$sql = $this->get_base_sql();
			$data = $mysql->get_arr($sql,1);
			return $data;
		}
	}
	
	
	
	// Панель редактирования
	function actions_panel($result)
	{
		if($result['del']){$del_class = 'dtable_del_class';}
		else{ $del_class = ''; }
			
		if(!$result['del'])
					{
						print '<td class="dtable '.$del_class.'">'.$result['order'].'&nbsp; &nbsp;  
						
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderup&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8593; </a> 
						&nbsp; 
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderdown&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8595;	</a> 
						</td>';
			
						print '<td class="dtable" style="">';
						print '<a href="index.php?ctr='.$this->ctr.'&act=editpage&id='.$result[$this->key_filed].'" class="table-edit"  ></a>
						&nbsp;
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=del&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент?" style="color:red; font-size: 18px;">X</a>';
						 print '</td>';
					}
					else
					{
						print '<td></td>';
						 print '
						 <td>
						 <a href="ajax_router.php?ctr='.$this->ctr.'&act=del2&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1"  data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент без возможности восстановления?" style="color:red; font-size: 18px;">X</a> 
						 &nbsp;
						 <a href="ajax_router.php?ctr='.$this->ctr.'&act=recovery&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите восстановить элемент?" style="color:GREEN; font-size: 18px;">R</a> 
						 </td>';
			}
	}
	 
	 
	##### 
	function act__sel_dir()
	{	
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['dir_id']?>" <?=$this->aj_select('dir',$v['dir_id'])?>><?=$v['dir_title']?>  </option>
			<?
		}
	}
	#####  
	 
	 
	 
	 
	//Аякс загрузка тела тблицы
	function act__ajax_data()
	{
		global $mysql;
		//$sql = $this ->get_base_sql();
		//$this->data_arr  = $mysql->get_arr( $sql );
		
		//print '<pre>';
		//print_r($this->data_arr );
		//print '</pre>';
		
		if($this->data_arr)
		{
			foreach( $this->data_arr as $k => $result )
			{
				$this->act__ajax_data_one($result);
			}
		}
		else{ print '<tr><td colspan="100" style="text-align:center; font-size: 18px;   padding: 30px; color:#3C96E1;  text-transform: none;">Ничего не найдено :(</td></tr>';}
	}
	
	 
	 
	 
	 // РАскрывающаяся строка
	 function act__ajax_data_one_slider($result)
	 {
		 	 print '<tr class="akk_slide"><td colspan="1000">';
			 ?>
			 <div class="row">
				 <div class="col-md-3"><img src="<?=$result['image']?>" width="100%" /></div>
				 <div class="col-md-8">
					<?
					print '<pre>';
					print_r($result);
					print '</pre>';
					?>
				 </div>
			 </div>
			 <?
			 print '</td></tr>'; 
	 }
	 
	 
	// disp
	function act__ajax_data_one($result)
	{
		global $filed;
			$desc_cut = mb_substr($result['descr'], 0, 50);
			if(mb_strlen($desc_cut)<mb_strlen($result['descr'])){$desc_cut.='...';}
			$desc_cut = $result['descr'];
			
			if($result['del']){$del_class = 'dtable_del_class';}
			else{ $del_class = ''; }
			
			
			
			if($result['logo'])	{	$logo = $result['logo']; 	}
			else{	$logo = '/admin/nofoto.png'; 	}
			
			$rubric = 'Рубрики';
			
			 print ' <tr class="dtable " style="background-color:#EEE;  border: solid 2px #FFF;"   id="ajaxitem_'.$result[$this->key_filed].'">';
			 print ' <td class="dtable '.$del_class.'">'.$result[$this->key_filed].'</td>'.
					'<td class="dtable '.$del_class.'"><img src="'.$logo.'" style="max-height:60px; max-width:80%; margin:20px;" /></td>'.
					'<td class="dtable '.$del_class.'" style=" "><span>'.$result['title'].'</td>'.
					'<td class="dtable '.$del_class.'">'.$desc_cut.'</td>'.
					'<td class="dtable '.$del_class.'"> '.$rubric.'</td>'.
					'<td class="dtable '.$del_class.'"> '.$result['floor'].'</td>' ;
					print $this->actions_panel($result); // Панель редактирования
			 print '</tr>';	
			 
			// добавить класс akk в tr чтобы строка под ней раскрываласть
			// $this->act__ajax_data_one_slider($result);
	} 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	// Служебные поля для формы поиска
 	function form_sfileds($fid)
	{
		?>
		<input type="hidden" name="ctr" value="<?=$this->ctr?>">
		<input type="hidden" name="act" value="index">
		<input type="hidden" name="formid" value="<?=$this->formid($fid)?>">
		<input type="hidden" id="order_filed" name="order_filed" value="<?=$this->get_form_sval( $this->formid($fid)  ,'order_filed','request')?>">		
		<input type="hidden" id="order_asc" name="order_asc" value="<?=$this->get_form_sval( $this->formid($fid)  ,'order_asc','request')?>">	
		<?
	}
	
	// форма поиска
	function searchform()
	{
		?>
		<form method="GET" id="filtrform" data-controller="<?=$this->ctr?>" data-router="/admin/ajax_router.php" data-ajaxurl="/admin/ajax_router.php?ctr=<?=$this->ctr?>&act=ajax_data" style="width:100%;" >
		<?=$this->form_sfileds('searchform')?>
		<div class="filter-block" >
		<div class="filter-item" style="display:none;"> 
		<select name="dir" id="sel_dir" data-placeholder="Раздел" class="filtr_input">
			<option value="">- Раздел -</option>
			<?
				//$this->act__sel_dir();
			?>
		</select>
		</div>				 	
		<div class="filter-item"> 
			<input type="text" id="search" name="search" class="filtr_input" value="" placeholder="Поиск текста"/><br/>
		</div> 
		<div class="filter-item"> 
			<input type="checkbox"  id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
		</div>
								
		</div>
		</form>
		<?
	}
	
	
	
	
	
	
	
	
	
	
	

	
	function act__index()
	{
		global $r;
		global $mysql;
		?>
		<style>
		.dtable_del_class{text-decoration:line-through; color:#CCC;}
		</style>
	 
		<section class="section-stat">
			<div class="container mobc"  >
				<div class="page-header">
					<div class="page-header__logo"><img src="/admin/adm_template/images/logo_green.png" alt="" /></div>
					<div class="page-header__title"><?=$this->title?></span></div>
				</div>
		 
				<div style="text-align:right; width:100%; padding:20px; padding-left:0; padding-right:0;">
					<a href="<?=$r->acturl($this->ctr,'editpage','index.php');?>" class="btn_2 ">Добавить </a>
				</div>

					<div id="ajaxcontent" class="stat">
						<div class="stat-top">
							<?=$this->searchform()?>
							<a href="JavaScript:window.print();" class="stat-top__print" ></a>
						</div>
						<div class="stat-table stat-table-user stat-table_notpd table">
						
						
						
						
						
							<table class="dtable" >
							<thead>
							<tr class="dtable">
								<th class="dtable"><a href="#" data-filed="<?=$this->key_filed?>"><b>id</b></a> </th>	 
								<th class="dtable"> <b>Лого</b> </th> 
								<th class="dtable"><a href="#" data-filed="title"><b>Заголовок</b></a> </th> 
								<th class="dtable"><b>Описание</b></th>
								<th class="dtable"><a href="#" data-filed="dir"><b>Рубрики</b></a></th>
								<th class="dtable"><a href="#" data-filed="floor"><b>Этаж</b></a></th>
								<th class="dtable"><a href="#" data-filed="fw_nodes.order" class="bottom-active"><b>Порядок</b></a></th>
								<th class="dtable" style="min-width:70px;"  class="dtable"> </th>
							</tr>
							</thead>
							<tbody id="fw_ajaxdata">
							</tbody>
							</table>
							
							
							
							<div style="width:100%; max-width:100vw; text-align:center; padding:50px; " id="progressbar"  >
								<img src="/admin/loader.gif"  />
							</div>
						</div>
					</div>			
					 

			</div>
		</section>
		
		<div id="fw_data_tbody2"></div>

		<?
		ob_start();
		?>
		<script>
		// Перед аякс запросом
		var predcallback = function (item){}
		
		// Перед загрузкой результата запроса в тег результата
		var predcallback2 = function (item)	{}
		
		// Перед загрузкой результата запроса в тег результата
		var postcallback = function (item)
		{  			  
			// ajax действия кнопки внутри контейнера 
			$('.fw_ajaxlink').click(function() 
			{
				var confirm = $(this).attr('data-confirm');
				var datacontainer = $(this).parents('tr:first');
				var url = $(this).attr('href');
				var data_id =$(this).attr('data-id') ;
				
				 // alert(data_id);
				// #ajaxitem_43
				
				
				if(confirm)
				{
					if (window.confirm(confirm)) 
					{
						
						$.ajax({  
						   type: "POST",  
						    dataType:"html", //формат данных
						    url: url,
						    success: function(response){  
							
								/* Скрытие контейнера если указано */
								if($(this).attr('data-actionhide'))
								{
									$(datacontainer).hide(500);
								}
								// alert(response);
								//Обновляем данные
								if( $(this).attr('data-reloadall') )
								{
									sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
								}
								else
								{
									sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
								}
								
								$('#ajaxitem_'+data_id).css('border-right','solid 5px #3C96E1');
						   }  
						 });  
					}
				}
				else
				{
					$.ajax({  
						  type: "POST",  
						  dataType:"html", //формат данных
						  url: url,
						  success: function(response){  
						  
							/* Скрытие контейнера если указано */
							if($(this).attr('data-actionhide'))
							{
								$(datacontainer).hide(500);
							}
						
							//alert(response);
							//Обновляем данные
							if( $(this).attr('data-reloadall') )
							{
								sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
							}
							else
							{
								sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
							}
							//alert('#ajaxitem_'+data_id);
							$('#ajaxitem_'+data_id).css('border-right','solid 5px #3C96E1');
						}  
					});  
				}
				
				
				return false;
			});
 
			// Раскрытие строк таблицы
			$('tr.akk').click(function() 
			{
				$('.akk_slide').hide(1); 
				$(this).next('.akk_slide').toggle(300);
			});
			
			
			 // Модальные окна редактирвоания
			   $('.fw_iframeajax').magnificPopup({type:'iframe',
				  removalDelay: 100,
				  fixedContentPos: true, 
				  disableOn:1,
				   tLoading: 'Загрузка #%curr%...',
					callbacks: {
					open: function() {
					  // Will fire when this exact popup is opened
					  // this - is Magnific Popup object
					},
					close: function() {
						// Перезагрузить отображение!
						sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек	 
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					// e.t.c.
				  }
				  });
				  
		}
		
		 
		// Стартовая загрузка ajax - нельзя ставить в   $(document).ready иначе зациклится
		$(window).load(function() {
		  // Селект разделов - стартовая загрузка
		  sendAjaxForm( 'sel_dir' , 'filtrform' , '/admin/ajax_router.php?ctr=<?=$this->ctr?>&act=sel_dir',1,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
		  
		  // Контент - стартовая загрузка
		    sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
		});


		  $(document).ready(function() {
			$('#sel_dir').on('change', function() {
				 // relate_ajax_select(this,'');
			});
			
			// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
			$( "#filtrform input,#filtrform select" ).change(function() {
			  sendAjaxForm( 'fw_ajaxdata' , 'filtrform','',0,'',predcallback,predcallback2,postcallback); //  
			});
 	  
		});
		
		
				
		


		// СОртировка 
		$('.stat-table th a').on('click', function (e) {
			
			form = $(this).parents('form:first');
			form_id = $(form).attr('id');
			
			if ($(this).hasClass('top-active')) {
				$(this).removeClass('top-active');
				
				$('#ajaxcontent a').removeClass('top-active');
				$('#ajaxcontent a').removeClass('bottom-active');
			 
				 $('#order_filed').val( $(this).attr('data-filed') );
				 $('#order_asc').val(1);
				 $('#order_asc').change();
				 
				$(this).addClass('bottom-active');
		 
			} else {
				$(this).removeClass('bottom-active');
				
				$('#ajaxcontent a').removeClass('top-active');
				$('#ajaxcontent a').removeClass('bottom-active');
			 
			 
				$('#order_filed').val( $(this).attr('data-filed') );
				$('#order_asc').val(0);
				$('#order_asc').change();

				$(this).addClass('top-active');
			}
			e.preventDefault();
		});
</script>
<?
$GLOBALS['tpl']['footer'].=ob_get_clean();
?>
		
		
 

		<?
		//print_R($mysql);
	}
	
	 
	 
	 
	 
	// Не ajax 
	function act__editpage()
	{
		 global $r;
		 $id = $_GET['id'];
		 $action = $r->acturl($this->ctr,'editpage','index.php').'&id='.$id;
		 
		 $this->backlink = $r->acturl($this->ctr,'index','index.php');
		 
		 $this->act__edit($action );
		 
		 
	}
	
	
	
	
	
	function formpanel($backlink='')
	{
		?>
		<div class="row">
			<div class="col-md-3"  style="text-align:left;"><a href="<?=$this->backlink?>" class="forminformpanel_link">Назад</a> </div>
			<div class="col-md-6"  style="text-align:center;" class="forminform"><?=$this->forminform?> </div>
			<div class="col-md-3"  style="text-align:right;"><a href="#" onclick="document.getElementById('editform').submit(); return false;" class="forminformpanel_link" style="text-align:right;">Сохранить</a></div>
		 </div>
		<br/>
		<hr/>
		<?
	}
	
	########################################################################################################################
	####################### РАБОТА С КАТЕГОРИЯМИ
	function act__dir_navigation()
	{
		global $mysql;
		$result = $mysql->get_arr('SELECT * FROM dir  WHERE del=0  order by dir_id',0, 'parent_dir_id' ); // Массив    [parent][child]
		
		return  $this->arr_child_dir($result,2);
	}
	
	function act__demo()
	{
			global $mysql;

			$result = $mysql->get_arr('SELECT * FROM dir  WHERE del=0  order by dir_id',0, 'parent_dir_id' ); // Массив    [parent][child]
			print '<pre>';
			//print_r($result);
			print '</pre>';
			
			//$this->view_cat($result,2) ;
 
			//Рекурсивное получение всех дочерих категорий (вывести категории дочерние данной)
			$arr = $this->arr_child_dir($result,2);
			print '<pre>';
			print_r($arr);
			print '</pre>';
			
			//$this->add_dir2node(14,1);
 
	}
	
	
	//Рекурсивное получение всех ид дочерих категорий (вывести категории дочерние данной)
	function arr_child_dir($arr,$parent_id = 0,$add_this=false,$level='',$path='',$res='') 
	{
		 $level++; // Уровень вложенности
		 
		 //Условия выхода из рекурсии
		 if(empty($arr[$parent_id])) { return; }
		 
		 if(!$res){$res=array();}
		 
		 //перебираем в цикле массив и выводим на экран
		 for($i = 0; $i < count($arr[$parent_id]);$i++) 
		 { 
			$item = $arr[$parent_id][$i];
			
			$item['lavel_calc'] = $level; // Вычесленный уровень вложенности
			$item['dir_name_calc'] = $arr[$parent_id][$i]['dir_name']; // вычесленное NAME  
			$item['path_calc'] =$path.'/'.$item ['dir_name_calc']; // Вычесленный PATH  
			 
			$res[] = $item;
			
			if($arr[ $arr[$parent_id][$i]['dir_id'] ]) // Если есть дочерние у директории
			{
				//рекурсия - проверяем нет ли дочерних категорий 
				$res  = $this->arr_child_dir($arr,$arr[$parent_id][$i]['dir_id'],$add_this,$level,$item['path_calc'],$res);
			}
		 }
		 return $res;
	} 

 
	//Построение хлебных крошек от текущей категории
	function arr_breadcrumbs_dir($arr,$parent_id = 0,$level='',$path='',$res='') 
	{
		 // массиы [level]=Диреткория 
	} 


	### НОДЫ+Категории
	
	// Получить категории которые указанны для данной ноды
	function get_dir2node($node_id='')
	{
		global $mysql;
		if(!$node_id){ $node_id=$_GET['id']; } 	
		return $result = $mysql->get_arr('SELECT * FROM dir2node  WHERE `dir2node`.`node_id`="'.$node_id.'" ',1, 'dir_id'); // Массив 
		
	}
	// ДОБАВИТЬ НОДУ В КАТЕГОРИЮ
	function add_dir2node($node_id='',$dir_id='')
	{
		global $mysql;
		if(!$node_id){ $node_id=$_GET['id']; } 	
		if(!$dir_id){ $dir_id=$_GET['dir_id']; } 
		
		$result = $mysql->get_arr('SELECT * FROM dir2node  WHERE `dir2node`.`node_id`="'.$node_id.'" AND `dir2node`.`dir_id`="'.$dir_id.'" ' ); // Массив 
		
		// print_r($result);
		if(!$result)
		{
			$data = array();
			$data['node_id'] = $node_id;
			$data['dir_id'] = $dir_id;
			
			return $mysql->insert('dir2node',$data);
		}
		else
		{
			return true;
		}
	}

	// удалить НОДУ из категории
	function del_dir2node($node_id='',$dir_id='')
	{
		global $mysql;
		if(!$node_id){ $node_id=$_GET['id']; } 	 	
		if(!$dir_id){ $node_id=$_GET['dir_id']; } 
 
		return $mysql->get_arr(' DELETE FROM `dir2node` WHERE `dir2node`.`node_id`="'.$node_id.'" AND `dir2node`.`dir_id`="'.$dir_id.'" ' ); //   
	}
	
	// ОБновить список категорий для ноды
	function refresh_dir2node($node_id,$arr)
	{
		global $mysql;
		if(!$node_id){ $node_id=$_GET['id']; } 	
		if(!$node_id){ print 'Ошибка refresh_dir2node '; exit(); }
		
		if( is_array($arr) )
		{
			// Удаляем все категории ноды --- !!! ОПАСНО ТАК КАК В СЛУЧАЕ КОСЯКА С ДОБАВЛЕНИЕМ ПОТЕРЯЕМ ДАННЫЕ
			$result = $mysql->get_arr('	DELETE FROM `dir2node` WHERE `dir2node`.`node_id`="'.$node_id.'" ' ); //   
			
			// Добавляем категории указанные в массиве
			foreach($arr as $k=>$v)
			{
				if($v && $node_id)
				{
					$this->add_dir2node($node_id,$v);
				}
			}
		}
		return true;

	}
	
	
	
	 

 
 
 
 
	############################################################################

	// Отрисовка дерева jsoon
	function act__get_node_jsoon()
	{
		global $mysql;
		header('Content-Type: application/json; charset=utf-8');
		 
		// Нода для получения отмеченных
		$node_id = $_REQUEST['node_id'];
		/*
		{
		  id          : "string" // will be autogenerated if omitted
		  text        : "string" // node text
		  icon        : "string" // string for custom
		  state       : {
			opened    : boolean  // is the node open
			disabled  : boolean  // is the node disabled
			selected  : boolean  // is the node selected
		  },
		  children    : []  // array of strings or objects
		  li_attr     : {}  // attributes for the generated LI node
		  a_attr      : {}  // attributes for the generated A node
		}
		*/
 
		// добавляем информацию о отмеченных
		// получаем массив отмеченных 
		 
		 
		$da = $mysql->get_arr('SELECT * FROM dir  WHERE del=0  order by dir_id',0, 'parent_dir_id' ); // Массив    [parent][child]
		//print '<pre>';
		//print_r($da);
		//print '</pre>';
			 
		$start_dir = 0; 
		//Рекурсивное получение всех дочерих категорий (вывести категории дочерние данной)
		$result = $this->arr_child_dir($da,$start_dir);
			 
		// Получаем список диреткорий для этой ноды
		$ch_arr = $this->get_dir2node($node_id);
		// print_r($ch_arr);
		
		$i=0;
		foreach( $result as $k=>$v )
		{
			
			if( !$v['parent_dir_id'] || $start_dir == $item['id'] || $i==0){$v['parent_dir_id'] = '#';}
			$i++;
			
			$item['id'] =  $v['dir_id'];
			$item['text'] = $v['dir_title'];
			$item['parent'] = $v['parent_dir_id'];
			if(  $ch_arr[$item['id']] )
			{
				$item['state']['checked'] = true;
				$item['state']['opened'] = true;
				//$item['state']['disabled'] = true;
			
			}
			else
			{ 
				$item['state']['checked'] = false;
				$item['state']['opened'] = true;
				   
			//	$item['state']['selected'] = false;
			}
 			$result2[] = $item;
		}

		 // print_r($result2);
		 print json_encode( $result2 );     
	} 
	#######################################################################################################################################
	
	
	
	
	
	
	
	
	
	
	
	
	function act__ajax_delimg()
	{
		global $mysql;
		$files2node_id = (int) $_REQUEST['files2node_id'];
		
		// $arr = $mysql->get_arr('SELECT * FROM files2node WHERE files2node.files2node_id="'.$files2node_id.'" ',1);
		
		$data = array();
		$data['del']=1;
		$mysql ->update_for_key('files2node','files2node_id',$files2node_id,$data);				
	}
	
	
	
	
		
	function act__ajax_mainimg()
	{
		
		$arr = $mysql->get_arr('SELECT * FROM files2node WHERE files2node.files2node_id="'.$files2node_id.'" ',1);
		 
		 
		$data = array();
		$data['main']=1;
		$mysql ->update_for_key('files2node','files2node_id',$files2node_id,$data);		

		$mysql->get_arr('UPDATE `files2node` SET `main` = "0" WHERE `files2node_id` != "'.$arr['files2node_id'].'" AND  node_id="'.$arr['node_id'].'" ');
	 
		
	}
	
	
	
	
	
	
	
	
	
	
	
	function act__edit($action='',$backlink='')
	{
		global $filed;
		global $mysql;
		global $r;
		global $t;
		
		if(!$action){$action = $r->acturl($this->ctr,'edit','if_router.php').'&id='.$id;}
		
		if($_POST) ############# Обработка данных пост
		{
			$this->post__edit();
		 
		}
		 
		
		# Данные редактирования
		$id = $_GET['id'];
		if($id) 
		{
			$sql = $this->get_base_sql(' AND '.$this->key_filed.'="'.$id.'"');
			$data = $mysql->get_arr( $sql,1 );
			//$data = $this->data_arr[0]; // Данные до редактирования можно использовать
			$this->forminform = 'Редактирование #'.$id.'<br/>'.$this->forminform;
			
			//print '<pre>';
			//print_r($_POST);
			//print '</pre>';
			
			
			if($_GET['updated']){ $this->forminform .= '<br/> Данные сохранены!'; }
		}
		else
		{
			$this->forminform = 'Добавление<br/>'.$this->forminform;
		}

		// Порядок по умолчанию (больше максимального)
		if(!$data['order'])
		{
			$o_arr = $mysql->get_arr('SELECT MAX(`'.$this->table.'`.`order`) as max_order FROM `'.$this->table.'` ',1);
			$data['order'] = $o_arr['max_order']+1;
		}
		
		if($_POST){$data = $_POST;}

		if(!$_POST || 1==1) ############# ФОРМА
		{
		?>
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
		
		<form action="<?=$action?>" method="POST" id="editform"  >
	 
		<div id="tree_check"></div>

		<br/><br/>
		<?=$this->formpanel();?>
		<div class="row">
			<div class="col-md-6">
			<?
				$filed->text('title','Заголовок',$data['title']); print '<br/>';
				$filed->text('show_date','Отображаемая дата',$data['show_date']); print '<br/>';
				
				
				//$sel_arr = $this->dir_date;
				// print $filed->select('dir','Раздел',$sel_arr,$data['dir']);
				
				$filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 100px;" '); print '<br/>';
				
				?>
				<div>
				<span class="input_title">Рубрики</span>
				<div id="tree"></div>
				</div>
				<?
	  
				$sel_arr = $this->pin_node;
				print $filed->select('pin_node','Специальное размещение',$sel_arr,$data['pin_node']);
				 
				//$filed->date('date_p_start','Дата начала публикации',$data['date_p_start']); print '<br/>';
				//$filed->date('date_p_final','Дата окончания публикации',$data['date_p_final']); print '<br/>';
	 
				$filed->text_num('order','Порядок',$data['order']); print '<br/>';
				
				
				
				$sel_arr = $this->floor_date;
				print $filed->select('floor','Этаж',$sel_arr,$data['floor']);
	 
			?>
		
			
			</div>
			<div class="col-md-6">
			<?
			
				if($id)
				{
				$filed->image('logo','Лого ',$data['logo'],$id); print '<br/>';
				$filed->image('image','Основное фото ',$data['image'],$id); print '<br/>';
				$filed->images('images','Дополнительные изображения ',$data['images'],$id); print '<br/>';				
				}
				else
				{
					
					print '<div style="border: solid 1px #3C96E1; margin-top: 45px; padding:20px;">Для добавления изображений - сохраните материал</div>';
				}
				
				
				
				
			?>
			</div>
		</div>
			<?
			$filed->textarea_html('content','Текст',$data['content'],' rows="20" '); print '<br/>';
			?> 
			
			
			
			<?
			$xxx = new ctr__areas();
			$fl = 0;
			if($data['floor'] == '-1' || !$data['floor'] ){$fl=0;}
			else{ $fl = $data['floor']; }
			$xxx-> area_select( $fl , $data['data_area'] ,1);
			?>
		</form>
		


<?
/*
filed->file('name'.$dir,$filename);
filed->files('name',$dir,$filename);

filed->image();
filed->immages();

Логика 
1. из формы передается - название временной директории 
2. 
3.

*/

?>
 



<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/themes/default/style.min.css" />
<script src="/admin/adm_template/libs/jstree/jstree.min.js"></script>
		<script>
		$(function () {
			
			 $('#editform').on('submit', function(e){
			 
				$("#tree_check").html('0'); // Чистим инпуты
				var checked_ids = []; 
				var selectedNodes = $('#tree').jstree("get_checked", true);
				
				$.each(selectedNodes, function() {
					checked_ids.push(this.id); // ДОбавляем хиден инпут с name dir[] value рубрики
					$("#tree_check").append('<input name="dir[]" value="'+this.id+'"/>');
				});
				// You can assign checked_ids to a hidden field of a form before submitting to the server
				$('#selected').val("!"+checked_ids);
				return false;
			});
			
			
			
			$(window).resize(function () {
				var h = Math.max($(window).height() - 0, 420);
				//$('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
			}).resize();


			$('#tree')
				.jstree({
					'core' : {
						'data' : {
							'url' : '/admin/ajax_router.php?ctr=nodes&act=get_node_jsoon&node_id=<?=$_GET['id']?>',
							'data' : function (node) {
								return { 'id' : node.id };
							}
						},
						'check_callback' : true,
						'themes' : {
							'responsive' : false
						}
					}, 
					'force_text' : true,
					checkbox: {       
					  three_state : true, // to avoid that fact that checking a node also check others
					  whole_node : true,  // to avoid checking the box just clicking the node 
					  tie_selection : false // for checking without selecting and selecting without checking
					},
					'plugins' : ['checkbox','search', "themes"]
		 			
				
				})
				.on("check_node.jstree uncheck_node.jstree", function(e, data) {
						$("#tree_check").html(''); // Чистим инпуты
						var checked_ids = []; 
						var selectedNodes = $('#tree').jstree("get_checked", true);
						
						$.each(selectedNodes, function() {
							// checked_ids.push(this.id); // ДОбавляем хиден инпут с name dir[] value рубрики
							$("#tree_check").append('<input type="hidden" name="dir[]" value="'+this.id+'"/>');
						});
						// You can assign checked_ids to a hidden field of a form before submitting to the server
						$('#selected').val("!"+checked_ids);
				})
				
			 
			 
		});
 
		</script>
		
		<?
		}
	}  
	
	
	
	
	
	// обновление порядка сортировки 
	// $id - id ноды , $order_array - массив ид картинок в порядке необходимой сортировки 
	function update_files2node_order($id,$order_array)
	{
		global $mysql;
		//print_r($order_array);
		foreach($order_array as $k=>$v)
		{
			 $data = array();
			 $data['order']=$k;
			 $mysql -> update_for_key( 'files2node' , 'files2node_id' , $v , $data  );
			// print '<br>';
			 
			 
		}
		
		 //print '<pre>';
		 //print_r($order_array);
		 //print '</pre>';
		
	}
	
	
	
	
	
	
	
	### ОБРАБОТКА ФОРМЫ  
	
	function post__edit()
	{
			global $r;
			global $mysql;
			global $guest;
			global $filed;
			global $v;
		 
			//valid_conf['all_vars'][] = array("valid"=>"num"  );
			//$valid_conf['dir'][] = array("valid"=>"num", "min"=>"1"); 
			$valid_conf['floor'][] = array("valid"=>"num" , "max"=>"5", "min"=>"1" );
			$valid_conf['order'][] = array("valid"=>"num" , "min"=>"1");
			$valid_conf['title'][] = array("valid"=>"string", "max"=>"100", "min"=>"2" );
			$valid_conf['image'][] = array("valid"=>"url");
			$valid_conf['logo'][] = array("valid"=>"url");
			
			//$valid_conf['content'][] = array("valid"=>"html");
			//$valid_conf['descr'][] = array("valid"=>"html");
			
			 
			$v = new fw_validate($_POST,$valid_conf);
			//$valid->fileds['var2'] = 'Заголовок переменной';
	  
			// print '<pre>';
			//  print_r($_POST);
			//   print_r($v->log);
			//  print_r($v->result);
			// print '</pre>';
	 

			// Не прошла форма
			if(!$v->final_result)
			{
				//print $v->perr('');
			}

			$v->finalresult=1; // ОТКЛЮЧЕМ ВАЛИДАЦИЮ!!!!!!!!
			
			 
			//print '<pre>';
			//print_r($_POST);
			//print '</pre>';
			
		 if($v->finalresult)
		 {
			
			$data = array();
			$data['title'] = $_POST['title'];
			$data['descr'] = $_POST['descr'];
			$data['image'] = $_POST['image'];
			$data['logo'] = $_POST['logo'];
			$data['floor'] = $_POST['floor'];
		//	$data['dir'] = $_POST['dir'][0];
			$data['order'] = $_POST['order'];
			$data['content'] = $_POST['content'];
			$data['show_date'] = $_POST['show_date'];
			
			
			$data['data_area'] = $_POST['data_area'];
			
			
			
			
			$data['pin_node'] = $_POST['pin_node'];
			//$data['date_p_start'] = $_POST['date_p_start'];
			//$data['date_p_final'] = $_POST['date_p_final'];
			
			//ТИП ПОСТА
			$data['type'] = $this->post_type;
			// $data['price'] = $_POST['price'];
							
						 
					
				 
	 
	 
	 
			$id=(int) $_GET['id'];
			if($id) // Редактирваоние существующей записи
			{
				$this->forminform='Изменения сохранены!';
				// Дата время  пользователь
				$data['last_edit_user_id'] = $_SESSION['fw_user_id'];
				$data['last_edit_time'] =  time();
				
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
				
				// ОБНОВЛяЕМ КАТЕГОРИИ
				if($_POST['dir'] && is_array($_POST['dir']) )
				{
					$this-> refresh_dir2node($id,$_POST['dir']);
				}
				
				//print_r($_POST);
				if($_POST['images__sort'] && is_array( $_POST['images__sort'] ))
				{
					$this->update_files2node_order( $id, $_POST['images__sort'] );
				}
			 
			
			}
			else // Добавление новой записи
			{
				// Дата время  пользователь
				$data['last_edit_user_id'] = $_SESSION['fw_user_id'];
				$data['last_edit_time'] =  time();
				$data['add_user_id'] = $_SESSION['fw_user_id'];
				$data['add_time'] =  time();
	
				##################################### !!!!!!!!!!!!!!!!!!!!!Тут проверять на занятость!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				$newid = $mysql -> insert( $this->table , $data );
				 
				if($newid)
				{
					$id=$newid;
					$this->forminform= 'Добавлена запись '.$newid;
					
					// ОБНОВЛяЕМ КАТЕГОРИИ
					if($_POST['dir'] && is_array($_POST['dir']) )
					{
						print 'Обновляем рубрики '.$id;
						print_r($_POST['dir']);
						$this-> refresh_dir2node($id,$_POST['dir']);
					}
			
				}
				else
				{
					$this->forminform='Ошибка, попробуйте позже';
					//exit();
				}
			}
			
			
			// Редирект на редактирование
			$editurl = $r->acturl($this->ctr,'editpage','index.php'); 
			$editurl.='&id='.$id.'&updated=1';
			print $editurl;
			
			  header("Location: $editurl");
			exit();
		
		}
		 else
		 {
			 $this->forminform='<span style="color:red; font-weight:bold;">Проверьте корректность заполнения формы</span>';
		 }
			
	}
	
	
	
	
	

	
	
	
	

	
	
	 
	
	
	 
	
	
}