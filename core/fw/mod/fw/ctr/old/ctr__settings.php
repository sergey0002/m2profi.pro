<?
$GLOBALS['t']['title']='Заявки и брони';






class ctr__settings extends ctr__
{ 

	var $table = 'fw_settings'; //Главная таблица
	var $key_filed = 'fw_settings_id'; // Ключевое поле главной таблицы
	var $ctr = 'settings';
	
	function __construct()
	{
		global $mysql;
		$this->mysql = $mysql; 
 		 
		// Получаем данные 
		$this->data_arr = $mysql->get_arr($this->get_base_sql());
	}
	
	
	
	 
	// БАзовый запрос 
	function get_base_sql($where='')
	{
		$q = 'SELECT fw_settings.* FROM fw_settings WHERE  fw_settings.del != "2" ';
		
		// Фильтрация раздела
		if( $_REQUEST['dir']){ $q.=' AND fw_settings.dir = "'. $_REQUEST['dir'].'" ';}
	 
		// Удаленные
		if( !$_REQUEST['show_dell']){ $q.=' AND fw_settings.del = "0" '; }
		else{ }
		 
		
		if($where){$q.=' '.$where.' ';}
	 
		// Группировка (для вывода селектов)
		if( $_GET['act']=='sel_dir' ){ $q.=' GROUP BY dir ORDER BY dir';}
		 
		elseif( $_REQUEST['order_filed'] )
		{
			 $q .= ' ORDER BY '. $_REQUEST['order_filed']; 
			 if( $_REQUEST['order_asc'] ){  $q .= ' ASC ';  }
			 else{  $q .= ' DESC ';  }
		}
		else{ $q .= ' ORDER BY fw_settings.order '; }
 
		//  print $q;
		return $q;
	}
	
	
 
	
	
	
	 
	 
	 
	##### ФОРМА ПОИСКА 
	function act__sel_dir()
	{	
	    
		foreach($this->data_arr as $k => $v)
		{
			 
			?>
			<option value="<?=$v['dir_id']?>"  ><?=$v['dir_title']?>  </option>
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
			
			 print ' <tr class="dtable akk" style="background-color:#EEE;  border: solid 2px #FFF;" data-href="index.php?ctr=tasks&amp;act=show&amp;id=11" id="ajaxitem_'.$result[$this->key_filed].'">';
			 print ' <td class="dtable '.$del_class.'">'.$result[$this->key_filed].'</td>'.
					'<td class="dtable '.$del_class.'" style="white-space: nowrap;"><span>'.$result['title'].'</td>'.
					'<td class="dtable '.$del_class.'">'.$desc_cut.'</td>'.
					'<td class="dtable '.$del_class.'" style="white-space: nowrap;">'.$result['price'].'</td>' .
					'<td class="dtable '.$del_class.'"> '.$result['image'].'</td>' ;
					
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
		<section class="section-stat">
			<div class="container mobc"  >
				<div class="page-header">
					<div class="page-header__logo"><img src="/admin/adm_template/images/logo_green.png" alt="" /></div>
					<div class="page-header__title">Настройки сайта</span></div>
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
										<option value="">- Раздел -</option>
										<?
										//$this->act__sel_dir();
										?>
									</select>
								</div>
								 
								
								<div class="filter-item"> 
									<input type="text" id="search" name="search" class="filtr_input" value="<?=$this->get_form_sval( $this->formid('indexsearch')  ,'search','request')?>" /><br/>
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
								<th class="dtable"><a href="#" data-filed="title"><b>Название</b></a> </th> 
								<th class="dtable"><a href="#" data-filed="descr"><b>Описание (кратк)</b></a></th>
								<th class="dtable"><a href="#" data-filed="price"><b>Цена</b></a> </th>
								<th class="dtable"><a href="#" data-filed="image"><b>Фото</b></a> </th>
								<th class="dtable"><a href="#" data-filed="menu.order" class="bottom-active"><b>Порядок</b></a></th>
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
			$o_arr = $mysql->get_arr('SELECT MAX(`menu`.`order`) as max_order FROM `'.$this->table.'` ',1);
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
			$filed->text('price','Цена',$data['price']); print '<br/>';
			$filed->text('image','Ссылка на изображение',$data['image']); print '<br/>';
			$filed->text('order','Порядок',$data['order']); print '<br/>';
			
			$sel_arr = array();
			$sel_arr[0]='- Не указан - ';
			$sel_arr[1]='Ланч-боксы';
			$sel_arr[2]='Кейтеринг';
			
			print $filed->select('dir','Раздел',$sel_arr,$data['dir']);
			?>
			<br/>
			<br/>
			<?=$filed->submit();?><br/>
		</form>
		<?
		}
	}  
	
	
	
	
	### ОБРАБОТКА ФОРМЫ БРОНИ
	
	function post__edit()
	{
			global $mysql;
			global $guest;
			global $filed;
			
			$data = array();
			$data['title'] = $_POST['title'];
			$data['descr'] = $_POST['descr'];
			$data['price'] = $_POST['price'];
			$data['image'] = $_POST['image'];
			$data['dir'] = $_POST['dir'];
			$data['order'] = $_POST['order'];
						
			$id=$_GET['id'];
			if($id) // Редактирваоние существующей записи
			{
				print '<h2>Изменения сохранены!</h2>';
				$mysql -> update_for_key( 'menu' , 'menu_id' , $id , $data );
			}
			else // Добавление новой записи
			{
				##################################### !!!!!!!!!!!!!!!!!!!!!Тут проверять на занятость!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				$newid = $mysql -> insert( 'menu', $data );
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