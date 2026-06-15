<?
class ctr__agdocs extends ctr__
{  
 

	var $table = 'agency_docs'; //Главная таблица
	var $key_filed = 'agency_doc_id'; // Ключевое поле главной таблицы
	var $ctr = 'agdocs';
    var $title = 'Документы агентства';
   
   
   

	
	
	
	function __construct()
	{
		$this->doc_company_data = array(''=>'Компания не указана','1'=>'Сталкер','2'=>'M2');
		$this->doc_type_data = array(''=>'Тип не указан','1'=>'Договор','2'=>'Доп. Соглашение','3'=>'Акт');

		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		  
		$titles[$this->key_filed] = 'id';
		 
		//$titles['add_datetime'] = 'Регистрация';
		$titles['update'] = 'Дата обновления';
		$titles['doc_company'] = 'Компания'; 
		$titles['doc_type'] = 'Тип документа'; 
		//$titles['doc_start'] = 'Дата начала действия'; 
		$titles['doc_and'] = 'Дата окончания действия'; 
		$titles['agency_file'] = 'Документ'; 
		$titles['comment'] = 'Комментарий'; 
		$titles['edit'] = ''; 
		
		$this->ajcrud_table_titles=$titles; 

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
		agency_docs.*
		FROM  agency_docs    
		WHERE 1=1 
		';
		
		// Удаленные
		if( !$_REQUEST['show_dell']){ $q.=' AND '.$this->table.'.del = "0" '; }
		else{ $q.=' AND '.$this->table.'.del = "1" '; }
		

		if( $filtr_data['doc_type'] )
		{
			$q.=' AND agency_docs.doc_type = "'.$filtr_data['doc_type'].'" '; 
		}

		if( $filtr_data['agency_id'] )
		{
			$q.=' AND agency_docs.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
	
		if( $filtr_data['doc_company'] )
		{
			$q.=' AND agency_docs.doc_company = "'.$filtr_data['doc_company'].'" '; 
		}
		
		$q.=' order by `update`   ';
		// if($_GET['id']){$q.=''}
		 // print $q;
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
	function display_table__update($v)
	{
		
		if($v['update'])
		{
			 $r = date('d.m.Y H:m:s',$v['update']);
		}
		else
		{
			 $r = '-';
		}
		
		return $r;
		
	}
	
	function display_table__doc_start($v)
	{
		$phpdate = strtotime( $v['doc_start'] );
		return $mysqldate = date( 'd.m.Y', $phpdate );
	}
	
	function display_table__doc_and($v)
	{
		$phpdate = strtotime( $v['doc_and'] );
		
		if( $phpdate < time() )
		{
			$style='color:red';
		}
		else
		{
			$style='color:green';
		}
		return '<span style="'.$style.'">'.$mysqldate = date( 'd.m.Y', $phpdate ).'</span>'; 
	}
	
	
	
	

	
	function display_table__doc_company($v)
	{
		return $this->doc_company_data[$v['doc_company']];
	}
	
	function display_table__doc_type($v)
	{
		return $this->doc_type_data[$v['doc_type']];
	}

 
 	function display_table__agency_file($v)
	{
		
		 $r='<a href="'.$v['agency_file'].'" style="color:blue;" target="_blank">'.basename($v['agency_file']).'</a>';
		
		return $r;
		
	}
 
 	
 
 
 
 

 
 
 // Панель редактирования
	function display_table__edit($result)
	{
		if($result['del']){$del_class = 'dtable_del_class';}
		else{ $del_class = ''; }
			return '<a href="iframe_router.php?ctr='.$this->ctr.'&act=edit&agency_id='.$result['agency_id'].'&id='.$result[$this->key_filed].'" class="table-edit fw_iframeajax"  ></a>
			&nbsp;
			<a href="ajax_router.php?ctr='.$this->ctr.'&act=del&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент?" style="color:red; font-size: 18px;">X</a>';
 	 
	}
	
	
	
 
	function ajcrud_filtr()
	{
		$agency_id = $_GET[agency_id];
		 
		?>
		<input type="hidden" name="agency_id" value="<?=$agency_id?>"  />
		<div class="filter-item"  > 
			<span class="input_title">Тип документа</span>
			<select  name="doc_type" class="input_edit"  >	
			<option value="">Все</option>
			<option value="1">Договор</option>
			<option value="2">Доп соглашение</option>
			<option value="3">Акт</option>
			</select>
		</div>	
	 
		<div class="filter-item"  > 
			<span class="input_title">Компания</span>
			<select  name="doc_company" class="input_edit"  >	
			<option value="">Все</option>
			<option value="1">Сталкер</option>
			<option value="2">М2</option>
			</select>
		</div>	
		 
		<div class="filter-item"  > 
		 <a href="iframe_router.php?ctr=agdocs&act=edit&agency_id=<?=$agency_id?>" class="fw_iframeajax">Добавить документ</a>
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
	 
		if($_GET['agency_id'])
		{
			$ag = $mysql->get_for_key('agency','agency_id',$_GET['agency_id'],1);
			
			$t['h1'] = 'Документы агентства';
			print '<h2>'.$ag['caption'].'</h2>';
			
			print '<a href="ctrind.php?ctr=agfiles&act=index">&#8592; Вернуться к списку агентств</a><br/><br/>';
			
			$this->display_ajax_crud(1);
		}
		else
		{
			print 'Агентство не  найдено';
		}
		
	}
	
	
 


function act__edit()
{
	global $r;
	global $mysql;
	global $t;
	global $filed;
	$agency_id = $_GET['agency_id'];
	
	$doc_id = $_GET['doc_id'];
	if(!$doc_id){$doc_id = $_GET['id'];}
	
	if($doc_id)
	{
		$data = $mysql->get_for_key('agency_docs','agency_doc_id',$doc_id); 
	}
	else
	{	 

	//print '<pre>';
	//print_r($_GET);
	//print '</pre>';


		$data = $_GET;
	}
	 
	 
	if( $_POST )
	{
		
		 print_r($_POST);
		$data = array();
		$data['agency_id'] = $agency_id;
		$data['doc_company'] = $_POST['doc_company'];
		$data['doc_type'] = $_POST['doc_type'];
		//$data['doc_start'] = $_POST['doc_start'];
		$data['doc_and'] = $_POST['doc_and'];
		$data['agency_file'] = $_POST['agency_file'];
		$data['comment'] = $_POST['comment'];
		$data['update'] = time();
	 
	 
	 if($_POST['delete'])
	 {
		 $data['del'] = 1;
	 }
	 
	 print_r($data);
		if( $doc_id )
		{
			$mysql->update_for_key('agency_docs','agency_doc_id',$doc_id,$data);
		}
		else
		{
			//print_r($data);
			$doc_id = $mysql->insert('agency_docs',$data);
		}
	}
	
	
		 
	?>
	<div style="padding:30px; ">
	<?
	if($doc_id)
	{
		print '<h2>Редактирование документа</h2>';
	}
	else 
	{
		print '<h2>Новый документ</h2>';
	}
	?>
	<form method="post" action="iframe_router.php?ctr=agdocs&act=edit&agency_id=<?=$agency_id?>&id=<?=$doc_id?>">
	<?
	  // select($name, $caption, $data, $value = '', $style = 'text-transform:none; height:auto;')
		// Компания
		$filed->select('doc_company', 'Компания', $this->doc_company_data, $data['doc_company'],'','required');
		// Тип документа
		$filed->select('doc_type', 'Тип документа', $this->doc_type_data, $data['doc_type'],'','required');
		//$filed->date('doc_start', 'Дата начала действия документа', $data['doc_start'],'required'); 
		$filed->date('doc_and', 'Дата завершения действия документа', $data['doc_and'],'required');
		$filed->file2('agency_file', 'Документ', $data['agency_file'], $agency_id , true , true,false);
		
		$filed->text('comment', 'Комментарий',   $data['comment'] );
		$filed->checkbox( 'delete', 'Удалить документ',0) ;
		print '<br/><br/>';
		$filed->submit();
	?>
	</form>
	</div>
	<?
	

}













}