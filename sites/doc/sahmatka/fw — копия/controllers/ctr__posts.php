<?
class ctr__posts extends ctr__
{ 

	var $table = 'fw_posts'; //Главная таблица
	var $key_filed = 'fw_posts'; // Ключевое поле главной таблицы
	var $ctr = 'posts';
  
	function __construct()
	{
		$this->title='Новости и акции'; 
		$GLOBALS['t']['title']=$this->title;
		$this->post_type = '2'; // Тип поста
		
		global $mysql;
		// $this->session_form_save();
		
		// Получаем данные 
		$this->data_arr = $mysql->get_arr($this->get_base_sql());
		
		
		
		
		
		
		$dir_data_arr = $mysql->get_arr('SELECT * FROM `dir` WHERE  dir_type="'.$this->post_type.'"',true,'dir_id' ); // Данные для селекта Разделов
		$this->dir_date[0]='- Не указан - ';
		foreach($dir_data_arr as $k=>$v)
		{
			$this->dir_date[$k] = $v['dir_title'];
		}
		
		
		
		
		
		// Заголовки магазинов
		$places_data_arr = $mysql->get_arr('SELECT * FROM `fw_posts` ',true,'fw_posts' ); // Данные для селекта Разделов
		$this->places_date[0]='- Не указан - ';
		foreach($dir_data_arr as $k=>$v)
		{
			$this->places_date[$k] = $v['title'];
		}
 
	}
	
  
	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		global $mysql;
		$q = 'SELECT '.$this->table.'.*, dir.dir_id, dir.dir_title';

		//if( $_REQUEST['dir'] ) { $q.= ', count(dir) as c ';}

        $q.=' FROM `'.$this->table.'`
		LEFT JOIN dir ON dir.dir_id = `'.$this->table.'`.`dir`
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
	 
		// Группировка (для вывода селектов)
		if( $_GET['act']=='sel_dir' ){ $q.=' GROUP BY dir ORDER BY dir';}
		// elseif( $_GET['act']=='sel_section' ){ $q.='GROUP BY section ORDER BY section';}
		
		elseif( $_REQUEST['order_filed'] )
		{
			 $q .= ' ORDER BY '. $_REQUEST['order_filed']; 
			 if( $_REQUEST['order_asc'] ){  $q .= ' ASC ';  }
			 else{  $q .= ' DESC ';  }
		}
		else{ $q .= ' ORDER BY '.$this->table.'.order '; }
		 
 
		//  print $q;
		return $q;
	}
	
	 
	 
	 
	##### ФОРМА ПОИСКА 
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
	
	 
	 
	 
	 
	 
	// disp
	function act__ajax_data_one($result)
	{
			$desc_cut = mb_substr($result['descr'], 0, 50);
			if(mb_strlen($desc_cut)<mb_strlen($result['descr'])){$desc_cut.='...';}
			$desc_cut = $result['descr'];
			
			if($result['del']){$del_class = 'dtable_del_class';}
			else{ $del_class = ''; }
			
			 print ' <tr class="dtable akk" style="background-color:#EEE;  border: solid 2px #FFF;"   id="ajaxitem_'.$result[$this->key_filed].'">';
			 print ' <td class="dtable '.$del_class.'">'.$result[$this->key_filed].'</td>'.
					'<td class="dtable '.$del_class.'" style="white-space: nowrap;"><span>'.$result['title'].'</td>'.
					'<td class="dtable '.$del_class.'">'.$desc_cut.'</td>'.
					'<td class="dtable '.$del_class.'"> '.$result['logo'].'</td>' ;
					
					if(!$result['del'])
					{
						print '<td class="dtable '.$del_class.'">'.$result['order'].'&nbsp; &nbsp;  
						
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderup&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8593; </a> 
						&nbsp; 
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderdown&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8595;	</a> 
						</td>';
			
						print '<td class="dtable" style="white-space:nowrap;">';
						print '<a href="if_router.php?ctr='.$this->ctr.'&act=edit&id='.$result[$this->key_filed].'" class="fw_iframeajax table-edit" data-actionhide="0" data-id="'.$result[$this->key_filed].'" data-reloadall="1" data-confirm="0" ></a>
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

			 print '</tr>';	
			 
			 print '<tr class="akk_slide"><td colspan="1000">';
			 ?>
			 <div class="row">
				 <div class="col-md-3"><img src="<?=$result['ima!ge']?>" width="100%" /></div>
				 <div class="col-md-8">
					<?=$result['descr']?>
				 </div>
			 </div>
			 <?
			 print '</td></tr>';
	 
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
					<a href="<?=$r->acturl($this->ctr,'edit','if_router.php');?>" class="btn_2  fw_iframeajax">Добавить </a>
				</div>

					<div id="ajaxcontent" class="stat">
						<div class="stat-top">
						
							<form method="GET" id="filtrform" data-controller="<?=$this->ctr?>" data-router="/admin/ajax_router.php" data-ajaxurl="/admin/ajax_router.php?ctr=<?=$this->ctr?>&act=ajax_data" style="width:100%;" >
							<input type="hidden" name="ctr" value="<?=$this->ctr?>">
							<input type="hidden" name="act" value="index">
							<input type="hidden" name="formid" value="<?=$this->formid('indexsearch')?>">
							
							<input type="hidden" id="order_filed" name="order_filed" value="<?=$this->get_form_sval( $this->formid('indexsearch')  ,'order_filed','request')?>">		
							<input type="hidden" id="order_asc" name="order_asc" value="<?=$this->get_form_sval( $this->formid('indexsearch')  ,'order_asc','request')?>">	
							
							<div class="filter-block" >
 
								<div class="filter-item"> 
									<select name="dir" id="sel_dir" data-placeholder="Раздел" class="filtr_input">
										<option value="">- Рубрика -</option>
										<?
										//$this->act__sel_dir();
										?>
									</select>
								</div>
								 
								
								<div class="filter-item"> 
									<input type="text" id="search" name="search" class="filtr_input" value="" /><br/>
								</div> 
								<div class="filter-item"> 
									<input type="checkbox"  id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
								</div>
								
								
							</div>
							</form>
							<a href="JavaScript:window.print();" class="stat-top__print" ></a>
							
						</div>
						<div class="stat-table stat-table-user stat-table_notpd table">
							
							<table class="dtable" >
							<thead >
							<tr class="dtable">
								<th class="dtable"><a href="#" data-filed="<?=$this->key_filed?>"><b>id</b></a> </th>	 
								<th class="dtable"> <b>Лого</b> </th> 
								<th class="dtable"><a href="#" data-filed="title"><b>Заголовок</b></a> </th> 
								<th class="dtable"><b>Описание</b></th>
								<th class="dtable"><a href="#" data-filed="dir"><b>Рурика</b></a></th>
								<th class="dtable"><a href="#" data-filed="corpus"><b>Корпус</b></a></th>
								<th class="dtable"><a href="#" data-filed="floor"><b>Этаж</b></a></th>
								<th class="dtable"><a href="#" data-filed="fw_places.order" class="bottom-active"><b>Порядок</b></a></th>
								<th class="dtable"><b>Видимость</b></th>
								<th class="dtable" style="min-width:70px;"  class="dtable"> </th>
							</tr>
							</thead>
							<tbody id="fw_ajaxdata">
							<?			
								// $this->act__ajax_data();
							?>
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
	
	 
	 
	 
	 
	 
	 
	 
	
	
	function act__edit()
	{
			
		global $filed;
		global $mysql;
		global $r;
		global $t;
		
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
 
			$t['h1'] = 'Редактирование';
		}
		else
		{
			$t['h1'] = 'Добавление';
		}

		// Порядок по умолчанию (больше максимального)
		if(!$data['order'])
		{
			$o_arr = $mysql->get_arr('SELECT MAX(`'.$this->table.'`.`order`) as max_order FROM `'.$this->table.'` ',1);
			$data['order'] = $o_arr['max_order']+1;
		}

		
		if(!$_POST || 1==1) ############# ФОРМА
		{
		?>
		<h2><?=$t['h1']?></h2>
		<form action="<?=$r->acturl($this->ctr,'edit','if_router.php');?>&id=<?=$id?>" method="POST">
			<?
			$filed->text('title','Название',$data['title']); print '<br/>';
			$filed->textarea('descr','Описание',$data['descr']); print '<br/>';
			$filed->text('image','Ссылка на изображение',$data['image']); print '<br/>';
			$filed->text('order','Порядок',$data['order']); print '<br/>';
			
	 
			$sel_arr = $this->dir_date;
		 
			
			print $filed->select('dir','Раздел',$sel_arr,$data['dir']);
			?>
			<br/>
			<br/>
			<?
			$filed->textarea_html('content','Текст',$data['content'],' rows="20" '); print '<br/>';
			?> 
			
			<?=$filed->submit();?><br/>
		</form>
		<?
		}
	}  
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	### ОБРАБОТКА ФОРМЫ  
	
	function post__edit()
	{
			global $mysql;
			global $guest;
			global $filed;
			
			$data = array();
			$data['title'] = $_POST['title'];
			$data['descr'] = $_POST['descr'];
			$data['image'] = $_POST['image'];
			$data['dir'] = $_POST['dir'];
			$data['order'] = $_POST['order'];
			
			$data['content'] = $_POST['content'];


			

			//ТИП ПОСТА
			$data['type'] = $this->post_type;
			
			
			$data['price'] = $_POST['price'];
							
			$id=$_GET['id'];
			if($id) // Редактирваоние существующей записи
			{
				print '<h2>Изменения сохранены!</h2>';
				
				// Дата время  пользователь
				$data['last_edit_user_id'] = $_SESSION['fw_user_id'];
				$data['last_edit_time'] =  time();
				
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
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
					print '<h2>Добавлена запись '.$newid.'</h2>';
				}
				else
				{
					print 'Ошибка, попробуйте позже';
				}
			}
			
	}
	
	
	
	
	

	
	
	
	

	
	
	 
	
	
	 
	
	
}