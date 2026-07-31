<?
class ctr__agfiles extends ctr__
{  
 

	var $table = 'agency'; //Главная таблица
	var $key_filed = 'agency_id'; // Ключевое поле главной таблицы
	var $ctr = 'agfiles';
    var $title = 'Документы агентств';
   
	function __construct()
	{
		 
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		 
		/*
		Акты к договору 
		столбцы
		
		м2
			договор
				- доп соглашения 			
				- акты
				
		сталкер
			договор от- до
			- доп соглашения
			- акты
			
		
		РА хочет
		
		- акты
			- сталкер
				- файлы (просто поле stalker_akt)
			- М2
				- файлы (полу stalker_gorovor)
		Договоры
			- Сталкер 
				- файлы
			- М2
				- файлы
				
				
				
		отдельный контроллер 
		
		договоры
		отдельгный контроллер акты
		+ юр лицо 
		+ документ 
		
		-------
		files2node контроллер ! 
		там табличка с файлами 
		он же обрабатывает загрузку аплоад
		
		он же имеет вызов через filed с аргументами скин строки ! и колонки ? нави  
		
		-------
		
		filed2node контроллер 
		запуск через filed()
		группы полей?
		
		
		
		
		файлы с доп полями?
		произвольные поля к сущности файл? 
		
		*/
		
		
		
		$titles[$this->key_filed] = 'id';
		 
		//$titles['add_datetime'] = 'Регистрация';
		$titles['caption'] = 'Агентство';
	 
		// $titles['inn'] = 'ИНН';
		$titles['gl_name'] = 'Контакт';
		
 
	 
		$titles['doc'] = 'Документы'; 
		$titles['uptime'] = 'Дата обновления'; 
		
		//$titles['exrow'] = ''; // плюсик
		$this->ajcrud_table_titles=$titles;
		 
		$order=array();
		$order[$this->key_filed]=$this->table.'.'.$this->key_filed;
		$order['add_datetime']='agency.add_datetime';
		$order['activ']='activ';
		$order['usc']='usc';
		$order['lastactiv']='lastactiv';
		$this->ajcrud_table_order=$order; 
		 
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
 
		$this->aj_crud_addbutton=1;
		//$this->display_table_exrow=1; // раскрывать строки
		
		$this->aj_crud_edit_iframe=1; // РЕдактор в фреймах 
	}
	
 
	 
	
 
 
 	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		$filtr_data =  $_REQUEST;
	  
		global $mysql;
		$q = 'SELECT  
		agency.*,
		gl_user.name as gl_name,
		gl_user.e_mail as gl_e_mail, 
		gl_user.phone,
		max( files2node.uptime ) as uptime ,
		
		count(files2node_id) as c_agency_file_id
		 
		FROM  agency    
		LEFT JOIN users as gl_user ON agency.admin_user_id = gl_user.id 
		LEFT JOIN files2node  ON agency.agency_id = files2node.node_id 
		
		
		WHERE 1=1 
		
		/* AND users.`password` LIKE "%!%" */
		';
		
		// Удаленные
		if( !$_REQUEST['show_dell']){ $q.=' AND '.$this->table.'.del = "0" '; }
		else{ $q.=' AND '.$this->table.'.del = "1" '; }
		
		// Заблокированные 
		if( !$_REQUEST['show_block']){ $q.=' AND '.$this->table.'.unactiv = "0" '; }
		else{ $q.=' AND '.$this->table.'.unactiv = "1" '; }  
		
		
		if( $_REQUEST['act']!='sel_dir' || 1==1 )
		{
			$search_arr = array();
			$search_arr[]=''.$this->table.'.caption';
			if( $_REQUEST['search']){ $q.=$mysql->search($search_arr,$_REQUEST['search']); }			 
		}
		
		if( $filtr_data['agency_id'] )
		{
			//$q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			//$q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		 
		$q.=' GROUP BY agency.agency_id  ';
			
		 
		$q.=' ORDER BY uptime'; 
		$q.=' DESC ';  	
		 
		// Лимиты
		if( $filtr_data['start'] || $filtr_data['stop'] || 1==1 )
		{
			if( !$filtr_data['start'] ){ $filtr_data['start'] = 0; }  // Стартовая позиция
			if( !$filtr_data['stop'] ){ $filtr_data['stop'] = 1000; } // Сколько выводим
 
			$q.=' LIMIT '.$filtr_data['start'].' , '.$filtr_data['stop'];
			
		}
			
		
		// if($_GET['id']){$q.=''}
		// print $q;
		return $q;
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
	
	
	 
	
 



 
	 
	 
 
 
	// Контакт
	function display_table__gl_name($v)
	{
		return ' '.$v['gl_name'].' <br/> '.$v['gl_e_mail'].' <br/>'.$v['gl_phone'];
	}
	 
		
	//Регистрация
	function display_table__add_datetime($v)
	{
		if($v['add_datetime'] && $v['add_datetime']!='0000-00-00 00:00:00')
		{
			$r .= fromsql_date($v['add_datetime'],1);
		}
		else{$r='-';}
		return $r;
		
	}
 
	
	// Документы
	function display_table__doc($v)
	{
		
		if($v['c_agency_file_id'])
		{
			 $r = '<a href="iframe_router.php?ctr=agfiles&act=doc&agency_id='.$v['agency_id'].'" style="color:green" class=" fw_iframeajax">Загруженно документов - '.$v['c_agency_file_id'].'</a>';
		}
		else
		{
			 $r = '<a href="iframe_router.php?ctr=agfiles&act=doc&agency_id='.$v['agency_id'].'" style="color:red" class=" fw_iframeajax">Нет документов</a>';
		}
		
		return $r;
		
	}
	
	
		// Документы
	function display_table__uptime($v)
	{
		
		if($v['uptime'])
		{
			 $r = date('d.m.Y H:m:s',$v['uptime']);
		}
		else
		{
			 $r = '-';
		}
		
		return $r;
		
	}
  
	
	
	
	
	
	/*
	// Контент раскрывающейся строки
	function display_hr_content($v)
	{
		return '<div style="width:100%; max-width:100vw; text-align:center; padding:5px; " class="loader"><img src="loader.gif" height=40 /></div>';
	}
	
	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		 return '/sahmatka/ajax_router.php?ctr=users&act=exrow_users&id='.$v['agency_id'];
	}
	
	*/
 
	
 
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"  > 
			<span class="input_title">Найти агентство</span>
			<input type="text" id="search" name="search" class="input_edit" value="" placeholder="">	
		</div>	
		 
		 
		<?
		/*
		<div class="filter-item  filter-item-checkbox "> 
			<input type="checkbox"  id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
		</div>
		<div class="filter-item  filter-item-checkbox "> 
			<input type="checkbox"  id="show_block" name="show_block" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_block','request')?>> <label for="show_block">Заблокированные</label><br/>
		</div>	
		
		*/
		
	}
	
	
	
	// форма поиска
	function ajcrud_searchform()
	{
		global $filed;
		?>
		<form method="GET" action="" id="filtrform" data-controller="<?=$this->ctr?>" data-router="/admin/ajax_router.php" data-ajaxurl="/sahmatka/ajax_router.php?ctr=<?=$this->ctr?>&act=ajax_data" style="width:100%;" >
		<?=$this->form_sfileds('searchform')?>
		<style>.admfiltr *{display:inline-block;}</style>
		<div>
			<div class="admfiltr" style="display:table-cell; width: 100%;">
			<?=$this->ajcrud_filtr();?>
		</div>
		 
		</div>
		</form>
		<?
	}



	
	function act__index()
	{
		global $r;
		global $mysql;
		global $t;
		global $f;
		$t['h1'] = 'Документы агентств';
 
		$this->display_ajax_crud(1);
	}
	
	
	function act__doc()
	{
		//print_r($_SESSION);
		global $r;
		global $mysql;
		global $t;
		global $filed;
		$t['h1'] = 'Документы агентства';
		
		$agency_id = (int) $_GET['agency_id'];
		
		
		
		$agency_data = $mysql->get_arr('SELECT * FROM agency WHERE agency_id="'.$agency_id.'" ',1);
		
		if(!$agency_data){ print 'Ошибка. Агентство не найденно'; return;}
		
		
		
		
		if($id) //
		{
			 $data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			// print '<h2>Редактирование объекта </h2>';
			 
			if($_POST) ############# Обработка данных пост
			{
				$data_agency = array();
				if($_POST['caption']){$data_agency['caption'] = $_POST['caption'];}
				if($_POST['unactiv']){$data_agency['unactiv'] = 1;}else{$data_agency['unactiv']=0;}
				if(!$data_agency['caption']){$filed_errors['caption'][] = 'Заполните поле';}
				
				$mysql->update_for_key('agency','agency_id',$id,$data_agency);
				$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			}
		} 
			
			
			
			//print_r($agency_data);
		?>
		<h2><?=$agency_data['caption']?></h2>
		<hr/>
		<br/><br/>
		
		
 
		<table style="width:100%">
		
		<tr>
		<td colspan="2" style="text-align:center;">
			<a href="/sahmatka/iframe_router.php?ctr=agfiles&act=doc&agency_id=<?=$_GET['agency_id']?>">Все документы</a>
		</td>
		</tr>
		<tr>
			<td>
			<b>Документы M2</b><br/><br/>
			
			
			<a href="/sahmatka/iframe_router.php?ctr=agfiles&act=doc&agency_id=<?=$_GET['agency_id']?>&doc_type=11" <? if($_GET['doc_type']=='11'){print 'style="background-color: #00CDAD;"';}?>>Договоры</a><br/>
			<a href="/sahmatka/iframe_router.php?ctr=agfiles&act=doc&agency_id=<?=$_GET['agency_id']?>&doc_type=12" <? if($_GET['doc_type']=='12'){print 'style="background-color: #00CDAD;"';}?>>Акты</a><br/>
					
					
			</td>
			<td style="text-align:right;">
			<b>Документы "Сталкер"</b><br/><br/>
			
			
			<a href="/sahmatka/iframe_router.php?ctr=agfiles&act=doc&agency_id=<?=$_GET['agency_id']?>&doc_type=21" <? if($_GET['doc_type']=='21'){print 'style="background-color: #00CDAD;"';}?>>Договоры</a><br/>
			<a href="/sahmatka/iframe_router.php?ctr=agfiles&act=doc&agency_id=<?=$_GET['agency_id']?>&doc_type=22" <? if($_GET['doc_type']=='22'){print 'style="background-color: #00CDAD;"';}?>>Акты</a><br/>
					
			</td>
		</tr>
		</table>


		 
		<br><br>
		<?
		//$agency_files_data = $mysql->get_arr('SELECT * FROM agency_files WHERE agency_id="'.$agency_id.'" ');
	

		$doc_type  = (int) $_GET['doc_type'];
		
		$sql='
		SELECT files2node.*,users.login,users.name FROM files2node  
		LEFT JOIN users ON files2node.user_id = users.id 
		WHERE node_name = "agency_files" AND node_id = "'.$agency_id.'" AND files2node.del=0 
		';
		
		if( $doc_type ){$sql.=' AND file_type="'.$doc_type.'" ';}
	 	$sql.= ' ORDER BY files2node.uptime desc';
		 
		$agency_files_data = $mysql->get_arr($sql);
		
		
		if($_GET['doc_type']){$add =true;}
		else{$add=false;}
 
 
		// files2( $name, $caption='', $value ='' , $id , $add=true, $edit = false )
		$filed->files2('agency_files', 'Документы', $agency_files_data, $agency_id,$doc_type,$add,false,'ajax_router.php?ctr=ag_files&id=');
	
		
	}
}