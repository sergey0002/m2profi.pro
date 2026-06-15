<?

if(!$_GET['year']){$_GET['year']='2023';}





class ctr__agfiles extends ctr__
{  
 

	var $table = 'agency'; //Главная таблица
	var $key_filed = 'agency_id'; // Ключевое поле главной таблицы
	var $ctr = 'agfiles';
    var $title = 'Документы агентств';
   
   
   #### ########################## ПЕРЕПИСЫВАЕМ МЕТОДЫ ДЛЯ ТОГО ЧТОБЫ ПЕРЕЗАГРУЖАТЬ АЯКС ВМЕСТЕ С ШАПКОЙ
	function display_ajax_crud( ) // выполняется из action 
	{ 
		$datatpl = array();
		ob_start();
		$this ->display_tablex_head( $data , $this->ajcrud_table_titles, $this->aj_crud_table_order );
		$datatpl['tablehead']=ob_get_clean();
		
		// форма поиска
		ob_start();
		
		 $this->ajcrud_searchform(); 
		$datatpl['searchform']=ob_get_clean();
	 
	 
		// ФОрма массовых действий
		ob_start();
		$this->ajcrud_checkform();
		$datatpl['checkform']=ob_get_clean();
		 
		$this->tpl($datatpl,'core','tableedit_uphead');
		 
		$x=array();
		$this->tpl($x,'core','ajaxeditor');
	}
   
   function act__ajax_data()
	{
		$data=$this->data;
		// Заголовки 
		$titles=$this->ajcrud_table_titles;
 
		// Не переносить по словам
		$nowrap=$this->ajcrud_table_nowrap;
		 
		if($data)
		{
			?>
			<table class="dtable" id="fwcrudtable" >
			<thead>
			<tr class="dtable">
			<?=$this ->display_tablex_head( $data , $titles, $this->aj_crud_table_order );?>
			</tr>
			</thead>
			<tbody >
			<?=$this ->display_tablex_body( $data , $titles ,$nowrap,$this->display_table_exrow);?>
			</tbody>
			</table>
			<?
			//
			//
		}
		else
		{
			// print '<tr><td colspan="10" style="text-align:center"> - нет данных -</td></tr>';
			$this->tpl([],'core','tableedit_nulldata');
		}
	}
	#### ########################## ПЕРЕПИСЫВАЕМ МЕТОДЫ ДЛЯ ТОГО ЧТОБЫ ПЕРЕЗАГРУЖАТЬ АЯКС ВМЕСТЕ С ШАПКОЙ
	
	function __construct()
	{
		
		global $mysql;
		 
		$this->doc_company_data = array('1'=>'Сталкер','2'=>'M2');
		$this->doc_type_data = array('1'=>'Договор','2'=>'Доп. Соглашение' );
  
		$titles[$this->key_filed] = 'id';
		 
		//$titles['add_datetime'] = 'Регистрация';
		$titles['caption'] = 'Агентство';
	 
		// $titles['inn'] = 'ИНН';
		$titles['gl_name'] = 'Контакт';
		 
		//$titles['doc'] = 'Документы'; 
	 
		
		// Проверка документов
		#$titles['dog_stalker'] = 'Сталкер'; 
		#$titles['dog_m2'] = 'М2'; 
		
		 $filtr_data =  $_REQUEST;
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 
		############ // СОБИРАЕМ ДАТЫ (не зависимо от компании)///////////////////////////
		$q_b = ' SELECT agency_docs.doc_and FROM agency_docs WHERE agency_docs.del="0" ';
		
		//  Год документов
		if( $filtr_data['year'] )
		{
			$q_b.=' AND YEAR( agency_docs.doc_and ) = "'.$filtr_data['year'].'" '; 	
		}
		$q_b.=' GROUP BY agency_docs.doc_and ';
		$unidatre_arr = $mysql->get_arr( $q_b );
		$i=0;
		
		
		
		foreach($unidatre_arr as $k=>$v)
		{ 
		$i++;
			$ts = strtotime( $v['doc_and'] );
			$date = date( 'd.m.Y', $ts ); 
			  
			$this->unidatre_sql_period[$i] = $v['doc_and'];
			$this->unidatre_sql_period2[$v['doc_and']] = $i;
			$titles['period_'.$i] = $date;
		}
		 // print_r($this->unidatre_sql_period);
		// print '</pre>';
		############ /////////////////////////////////////////////
		
		 
		$docs_arr = array();
		// Кривая загрузка всех файлов в массив...
		foreach( $mysql->get_arr('SELECT * FROM agency_docs WHERE del="0" ') as $k=> $v )
		{
			$period = $this->unidatre_sql_period2[trim($v['doc_and'])];
			if($period)
			{
				$docs_arr[$v['agency_id']][$v['doc_company']][$period][$v['doc_type']][] = $v;	 
			}
			else
			{ 
				// Изза того что периоды собираются без учета года а файлы с учетом!
				// print trim($v['doc_and']).'!!! ';
			}
	 
		}
 
		$data=$this->getfiltr($filtr);
 
 
		foreach($data as $k=>$v)
		{	
			 foreach($this->unidatre_sql_period as $kp=>$vp )// Периоды
			 {		 
				 foreach($this->doc_company_data as $kk=>$vk) // Компания 
				 {
					 foreach($this->doc_company_data as $kd=>$vd) // Тип документа 
					 {
						if( $docs_arr[$v['agency_id']][ $kk ][ $kp ][ $kd ] )
						{
						 $data[$k]['agency_docs__'.$kp.'_'.$kd.'_'.$kk.'_file'] = $docs_arr[$v['agency_id']][ $kk ][ $kp ][ $kd ][0]['agency_file'];
						 $data[$k]['agency_docs__'.$kp.'_'.$kd.'_'.$kk.'_date'] =  $docs_arr[$v['agency_id']][ $kk ][ $kp ][ $kd ][0]['doc_and'];
						 $data[$k]['agency_docs__'.$kp.'_'.$kd.'_'.$kk.'_comment'] = $docs_arr[$v['agency_id']][ $kk ][ $kp ][ $kd ][0]['comment'];
						 $data[$k]['agency_docs__'.$kp.'_'.$kd.'_'.$kk.'_doc_id'] = $docs_arr[$v['agency_id']][ $kk ][ $kp ][ $kd ][0]['agency_doc_id'];
						}
					 }
				 }
			 }
		}
		 
		// print '<pre>';
		 //  print_r($docs_arr);
		//print_r($data);
		// print '</pre>';
		
		$this->data=$data; // Сохраняем данные	
		
		//$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		//$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		 
		  
		 $titles['update'] = 'Обновление'; 
		
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
		$nowrap['doc'] = 1;
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
		gl_user.password as gl_password,
		gl_user.phone,
		max( agency_docs.update ) as `update` , 
		count(agency_docs.agency_doc_id) as c_agency_file_id
	 
		';
		 	
		 
		  
		$q.='
		FROM  agency    
		LEFT JOIN users as gl_user ON agency.admin_user_id = gl_user.id 
		LEFT JOIN agency_docs  ON agency.agency_id = agency_docs.agency_id AND agency_docs.del="0"   
		';
		if( $filtr_data['year'] ){ $q.=' AND YEAR(agency_docs.doc_and) = "'.$filtr_data['year'].'" '; }
		 
		 
		$q .='
		 
		WHERE 1=1 
		 
		AND gl_user.password NOT LIKE "%!%"
		AND hidden ="0"
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
		
		
	
		  
		$q.=' GROUP BY agency.agency_id ';
			 
		$q.=' ORDER BY `update` '; 
		$q.=' DESC ';  	
		 
		// Лимиты
		if( $filtr_data['start'] || $filtr_data['stop'] || 1==1 )
		{
			if( !$filtr_data['start'] ){ $filtr_data['start'] = 0; }  // Стартовая позиция
			if( !$filtr_data['stop'] ){ $filtr_data['stop'] = 1000; } // Сколько выводим
 
			$q.=' LIMIT '.$filtr_data['start'].' , '.$filtr_data['stop'];
			
		}
			 
		// if($_GET['id']){$q.=''}
		  //  print $q; print '<br><br>';
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
	
	
	 
	
 
	function display_datecol($v,$period)
	{
		
		
		// print '<pre>';
		 // print_r($v);
		// print '</pre>';
		 
		 
		//$r=$period;
		foreach ( $this->doc_company_data as $k1=>$v1 ) // Цикл по компаниям
		{
			
			$doc_id = '';
			$doc_file = '';  
			$doc_comment = '';
				 
				 
			$r.='<b>'.$v1.'</b><br/>';
			//$r.='<b>'.$v1.'</b><br/>';
			foreach( $this->doc_type_data as $k2=>$v2 ) // Цикл по типам документов
			{
				
			 // 'agency_docs__'.$kp.'_'.$kd.'_'.$kk.'_file'
				 //$r.='agency_docs__'.$period.'_'.$k2.'_'.$k1.'_doc_id';
				  
				 $doc_id = $v['agency_docs__'.$period.'_'.$k2.'_'.$k1.'_doc_id'];
				 $doc_file = $v['agency_docs__'.$period.'_'.$k2.'_'.$k1.'_file'];  
				 $doc_comment = $v['agency_docs__'.$period.'_'.$k2.'_'.$k1.'_comment'];
				 
				 
				if($doc_id)
				{
					if($doc_file) // Есть файл
					{
						$r.= '<a class="fw_iframeajax"  rel="tooltip"  title="sdsdsd"   style="color:green; white-space: nowrap; line-height:1.2em;"
						href="iframe_router.php?ctr=agdocs&act=edit&agency_id='.$v['agency_id'].'&id='.$doc_id.'&doc_company='.$k1.'&doc_type='.$k2.'&doc_and='.$this->unidatre_sql_period['period_'.$period].'">
						'.$v2.'
						<b>&#10003;</b>
						</a>
						<br/>';
					}
					else
					{
						$r.= '<a class="fw_iframeajax"  rel="tooltip"  title="sdsdsd"   style="color:green; white-space: nowrap; line-height:1.2em;"
						href="iframe_router.php?ctr=agdocs&act=edit&agency_id='.$v['agency_id'].'&id='.$doc_id.'&doc_company='.$k1.'&doc_type='.$k2.'&doc_and='.$this->unidatre_sql_period['period_'.$period].'">
						'.$v2.'
						<b style="color:red;">&#10006;</b>
						</a>
						<br/>';
					}
				}
				else
				{
					$r.= '<a class="fw_iframeajax"  rel="tooltip"  title="sdsdsd"   style="color:red; white-space: nowrap; line-height:1.2em;"
					href="iframe_router.php?ctr=agdocs&act=edit&agency_id='.$v['agency_id'].'&id='.$doc_id.'&doc_company='.$k1.'&doc_type='.$k2.'&doc_and='.$this->unidatre_sql_period['period_'.$period].'">
					'.$v2.'
					</a>
					<br/>';
				}
			}
		}
 
		return $r;
 
	}

	// Контакт
	 function display_table__period_1($v){ return $this->display_datecol($v,1); }
	 function display_table__period_2($v){ return $this->display_datecol($v,2); }
	 function display_table__period_3($v){ return $this->display_datecol($v,3); }
	 function display_table__period_4($v){ return $this->display_datecol($v,4); }
	 function display_table__period_5($v){ return $this->display_datecol($v,5); }
	 function display_table__period_6($v){ return $this->display_datecol($v,6); }
	 function display_table__period_7($v){ return $this->display_datecol($v,7); }
	 function display_table__period_8($v){ return $this->display_datecol($v,8); }
	 function display_table__period_9($v){ return $this->display_datecol($v,9); }
	 function display_table__period_10($v){ return $this->display_datecol($v,10); }
  	 function display_table__period_11($v){ return $this->display_datecol($v,12); }
	 function display_table__period_12($v){ return $this->display_datecol($v,12); }	 
	 function display_table__period_13($v){ return $this->display_datecol($v,13); }
	 function display_table__period_14($v){ return $this->display_datecol($v,14); }
	 function display_table__period_15($v){ return $this->display_datecol($v,15); }
  
   function display_table__caption($v)
   {
	   return ' <a href="ctrind.php?ctr=agdocs&act=index&agency_id='.$v['agency_id'].'" style="color:green"  >'.$v['caption'].'<br>  <span style="color:#000000">Документы</span></a>';
   }
 
  
  
  
  
  
  
	// Контакт
	function display_table__gl_name($v)
	{
		return ' '.$v['gl_name'].' <br/> '.$v['gl_e_mail'].' <br/>'.$v['phone'];
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
	 
			$files = explode('#',$v['agency_files_t']);
			$files =  array_filter($files );
			$load_files =count($files);
			
			
 			 $r = '<a href="ctrind.php?ctr=agdocs&act=index&agency_id='.$v['agency_id'].'" style="color:green"  >
			 <b>Всего: </b>  '.$v['c_agency_file_id'].'<br/> </a>';
			 
			 
			 
			 if($load_files<$v['c_agency_file_id']){$style="color:red";}
			 else{$style="color:green;";}
			  $r .= '<a href="ctrind.php?ctr=agdocs&act=index&agency_id='.$v['agency_id'].'" style="'.$style.'"  >
			 <b>Загружено: </b>  '.$load_files.'<br/> </a>';
			 
		}
		else
		{
			 $r = '<a href="ctrind.php?ctr=agdocs&act=index&agency_id='.$v['agency_id'].'" style="color:red"  ><b>Нет документов</b></a>';
		}
		return $r;
	}
	
	 
	
	// Дата обновления документов
	function display_table__update($v)
	{
		if($v['update'])
		{
			 $r = date('d.m.Y',$v['update']).'<br/>'.date('H:m:s',$v['update']);
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
		global $filed;
		 
		$data = array(
		'2019'=>'2019',
		'2020'=>'2020',
		'2021'=>'2021',
		'2022'=>'2022',
		'2023'=>'2023',
		'2024'=>'2024'	
		);
		
		?>
	 
		<div class="filter-item"  > 
			 <?
			 $filed->select('year', 'Год', $data, $_GET['year'] );
			 ?>
		</div>	
		
	 
		
		
		<div class="filter-item"  > 
			<span class="input_title">Найти агентство</span>
			<input type="text" id="search" name="search" class="input_edit" value="" placeholder="" value="<?=urldecode($_GET['search'])?>">	
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
 
 ?>
 <style>
 
#fw_ajaxdata {
  overflow-x: scroll;
  white-space: nowrap;
  width: 100%;
 
  transform: scaleY(-1);
}
 
#fwcrudtable {
  transform: scaleY(-1);
}
 
 
 
#fwcrudtable tr td:nth-child(2) {
    word-break: break-all;
	 word-wrap: break-word; /* Перенос слов */ 
	 
    width: 230px;
    max-width: 230px;
}
 
 



 </style>
 

<div class="scroll">
	<div class="h_scroll_content"> 
	
	 <?	$this->display_ajax_crud(1);?>
	
	</div>
</div>

<?
	}
	
	
 


 




 








}