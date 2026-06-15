<?
class ctr__
{

    function __construct()
    {
        $this->mysql = $GLOBALS['mysql'];

    }
	
	
	
		 

 

/**
 * Экспортирует ассоциативные данные в CSV с фильтрацией по заголовкам.
 *
 * @param array  $data          Массив строк: [строка][ключ_столбца] => значение
 * @param array  $headers       Заголовки: [ключ_столбца => 'Отображаемое имя']
 * @param string $filename      Имя файла (опционально, без или с .csv)
 * @param array  $config        Конфиг: ['dir' => путь, 'url' => публичный URL]
 * @return string               Публичная ссылка на CSV-файл
 */
function exportArrayToCsvFile(array $data, array $headers, string $filename = '', array $config = []): string
{
    // Настройки по умолчанию
    $defaultConfig = [
        'dir' => '/home/m2profi/web/m2profi.pro/public_html/sites/em/keysbase',
        'url' => 'https://em.m2profi.pro/keysbase/',
    ];

    $config = array_merge($defaultConfig, $config);
    $config['url'] = rtrim(trim($config['url']), '/') . '/';

    // Создаём папку, если не существует
    if (!is_dir($config['dir'])) {
        if (!mkdir($config['dir'], 0755, true)) {
            throw new RuntimeException("Не удалось создать директорию: {$config['dir']}");
        }
    }

    // Генерация имени файла
    if (empty($filename)) {
        do {
            $filename = 'export_' . bin2hex(random_bytes(6)) . '.csv';
            $filepath = $config['dir'] . '/' . $filename;
        } while (file_exists($filepath));
    } else {
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
            $filename .= '.csv';
        }
        $filepath = $config['dir'] . '/' . $filename;
    }

    $fp = fopen($filepath, 'w');
    if (!$fp) {
        throw new RuntimeException("Не удалось открыть файл для записи: $filepath");
    }

    // BOM для UTF-8 в Excel
    fwrite($fp, "\xEF\xBB\xBF");

    // Извлекаем ключи столбцов в порядке, заданном в $headers
    $columnKeys = array_keys($headers);
    $displayHeaders = array_values($headers);

    // Записываем строку заголовков
    fputcsv($fp, $displayHeaders, ';', '"');

    // Записываем данные: только указанные столбцы, в нужном порядке
    foreach ($data as $row) {
        if (!is_array($row)) {
            continue; // пропускаем некорректные строки
        }

        $outputRow = [];
        foreach ($columnKeys as $key) {
            $value = $row[$key] ?? ''; // если ключа нет — пустая строка
            // Приводим к строке, чтобы избежать проблем с null/bool
            $outputRow[] = is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        fputcsv($fp, $outputRow, ';', '"');
    }

    fclose($fp);

    // Возвращаем публичную ссылку
    return $config['url'] . rawurlencode(basename($filepath));
}




	// Присвоение значения пост переменной при Mysql insert (значения по умолчанию при нулевом значении для чекбуксов итп)
	function data_value($val,$default=false,$data_type='')
	{
		//if( !$default ){ $default = false; }
		
		if( 
			( !$val && $val!==false && $val!==0 ) 
			&& 
			( $default || $defeult===false || $default === 0 )
		  )
		{
			$val = $default;
		}		
		return $val;
	}
	
	
	
	
    function tpl($data = '', $ctr, $tpl_name, $noptint = false)
    {
        // переменные в области видимости шаблона
        global $r;
        global $tpl;
        global $t;

        global $filed;

        $dir = 'fw/templates/' . $ctr . '/';
        $file = $dir . $tpl_name . '.php';

        add_logx('load template ' . $file);
        if ($_GET['dev'])
        {
            print '<!-- ' . $file . ' -->';
        }
        if (file_exists($file))
        {
            ob_start();
            if ($_GET['dev'])
            {
                print '<!-- ' . $file . '  - START -->';
            }
            include ($file);
            if ($_GET['dev'])
            {
                print '<!-- ' . $file . '  - STOP -->';
            }
            $c = ob_get_clean();

            add_logx(' template loaded');
        }
        else
        {
            if ($_GET['dev'])
            {
                print '<!-- ' . $file . ' NOT FOUND -->';
            }
            add_logx('  template file  ' . $file . ' dont exists');
        }

        if (!$noprint)
        {
            print $c;
        }
        return $c;

    }

    // В контролере обяхательно имя таблицы и ключевого поля + действия включить какие ? и механизмы
    

   
   
 
 
	 
    ######### ТАБЛИЦА CRUD
    // Метод содержимого столбца
 

    // отображение таблицы для двумерного массива полученного из mysql
    function display_table($arr, $titles, $all = false, $skin = '1',$colattr='')
    {

		if(!$colattr){$colattr = array();}
        // +СОРТИРОВКА
        /*
        вверх вниз + по какому столбцу
        + включение сортировки для столбцов
        */

        // СТолбцы с методами кастомными ???
        // Лези лоад
        /*
        у каждой строки доп атрибут дата data-page=1
        при прокрутке на 100рх от низа
        грузим следующую страницу
        
        постраничное меню скрываем явой
        */

        // скины таблиц
        $skin_arr[1]['tabletag'] = ' border="0" class="dtable" ';
        $skin_arr[1]['thtag'] = ' ';
        $skin_arr[1]['trtag'] = ' ';
        $skin_arr[1]['tdtag'] = ' ';

        // Все столбцы!
        if ($all)
        {
            foreach ($arr[0] as $k => $v)
            {
                if (!$titles[$k])
                {
                    $titles[$k] = $k;
                }
            }
        }
        $s = $skin_arr[$skin];
		?>
		<table <?=$s['tabletag'] ?> >
		<thead>
		<?
        foreach ($titles as $k => $v)
        {
		?><th <?=$s['tabletag'] ?>><?=$v ?></th><?
        }
		?>
		</thead>
		
		<tbody id="fw_data_tbody">
		<?
        foreach ($arr as $k => $v)
        {

            // Атрибуты строки
            if (method_exists($this, 'display_table_row_attr'))
            {
                $row_attr = $this->display_table_row_attr($v);
            }
			 
            // Атрибуты столба
            if (method_exists($this, 'display_table_col_attr'))
            {
                $col_attr = $this->display_table_col_attr($v);
            }

			?><tr <?=$s['tabletag'] ?> <?=$row_attr ?>><?
            foreach ($titles as $kt => $vt)
            {

                // теут проверяем метод $kt и если есть заменяем им $v[kt]!
                $met = 'display_table__' . $kt;
                if (method_exists($this, $met))
                {
                    $v[$kt] = $this->$met($v);
                }
				$col_attr2=$colattr[$kt];
				?><td <?=$s['tabletag'] ?> <?=$col_attr ?><?=$col_attr2 ?>><?=$v[$kt] ?></td><?

            }
		?></tr><?
        }
		?>
		</tbody>
		</table>
		<?
    }







	function formpanel($backlink='')
	{
		if(!$backlink){$backlink = $this->backlink;}
		
		$x=array();
		$x['forminform'] = $this->forminform;
		$x['backlink'] = $backlink;
		$this->tpl($x,'core','edit_form_panel');
	 
		// Если Ифрейм вызов кнопка назад закрывает ифрейм! и обновляет соответственно
		$f = basename($_SERVER['PHP_SELF']);
		
		if($f=='iframe_router.php'){
			?>
			<script>
			 $(document).ready(function(){
				$('.forminformpanel_link_close').click(function(e) 
				{
					e.preventDefault();
					if (window.parent == window.top) {
					window.parent.$.magnificPopup.close();
					}
					return false;
				});
			 });
			</script>
			<?
		}
		else
		{
			?>
			<script>
			 $(document).ready(function(){
				$('.forminformpanel_link_close').click(function(e) 
				{
					window.history.back();
					return false;
				});
			 });
			</script>
			<?
		}
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
		<?=$this->ajcrud_actions()?>
		</div>
		</form>
		<?
	}


	function ajcrud_filtr()
	{
		//print '123';
	}
	
	
	
	
	
	function display_table__edit($row)
	{
			if($this->aj_crud_edit_iframe)
			{
				$file='iframe_router.php';
				$ifclass='fw_iframeajax';
			}
			else
			{
				$file='ctrind.php';
				$ifclass='';
			}
		$link = '<a href="/sahmatka/'.$file.'?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit '.$ifclass.'"> </a> ';
		return $link;
	 
	}
	
	
	
	function ajcrud_actions()
	{
		?>
		<div style="display:table-cell; vertical-align: top;">
		<div class="filter-item filter-item_print"> 
			<a href=#" onclick="printDiv()"   class="filter-item-icon"><img src="/sahmatka/template/default/images/print.svg" /></a>
		</div>	
		</div>
	 
		<div style="display:table-cell; vertical-align: top;">
		<div class="filter-item filter-item_print"> 
			<a href=#" onclick="exportReportToExcel(this)" class="filter-item-icon"><img src="/sahmatka/template/default/images/excel.svg" width="32" title=""/></a>
		</div>	
		</div>
	 
		<div style="display:table-cell; vertical-align: top;">
		<div class="filter-item filter-item_print"> 
			<a href="#" onclick="exportReportToPdf(this)"  class="filter-item-icon"><img src="/sahmatka/template/default/images/pdf.svg"  width="32"/></a>
		</div>	
		</div>
		 <?
		if($this->aj_crud_addbutton)
		{
			if($this->aj_crud_edit_iframe)
			{
				$file='iframe_router.php';
				$ifclass='fw_iframeajax';
			}
			else
			{
				$file='ctrind.php';
				$ifclass='';
			}
			
		?>
		<div style="display:table-cell; vertical-align:top;">
		<div class="filter-item filter-item_print"> 
			<a href="/sahmatka/<?=$file?>?ctr=<?=$this->ctr?>&act=edit" class="filter-item-icon <?=$ifclass?>"><img src="/sahmatka/template/default/images/add.svg"  width="32"/></a>
		</div>	
		</div>
		<?
		}
	}
	
	// плюсик разворачивающихся строк
	function display_table__exrow($v)
	{
		return  '<a href="" class="aj_crud_rowplus">+</a>';
	}

	// форма массовых действий
	function ajcrud_checkform()
	{
		
	}
	
	
	#### НОВЫЙ CRUD AJAX
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
		 
		$this->tpl($datatpl,'core','tableedit');
		 
		$x=array();
		$this->tpl($x,'core','ajaxeditor');
	}
	
	// Шапка таблицы
    function display_tablex_head($arr, $titles, $order, $all = false, $skin = '1')
    {
        // скины таблиц
        $skin_arr[1]['tabletag'] = ' border="0" class="dtable" ';
        $skin_arr[1]['thtag'] = ' ';
        $skin_arr[1]['trtag'] = ' ';
        $skin_arr[1]['tdtag'] = ' ';

        // Все столбцы!
        if ($all)
        {
            foreach ($arr[0] as $k => $v)
            {
                if (!$titles[$k])
                {
                    $titles[$k] = $k;
                }
            }
        }
        $s = $skin_arr[$skin];
		
        foreach ($titles as $k => $v)
        {
			
			$met = 'display_th__' . $k;
			if (method_exists($this, $met))
			{
				?><th <?=$s['tabletag'] ?>><?=$this->$met($v); ?></th><?
			}
			else
			{
				if ($order[$k]) // указанно поле сортировки
				{
					?><th <?=$s['tabletag'] ?>><a href="#" data-filed="<?=$order[$k] ?>"> <?=$v ?></a></th><?
				}
				else
				{
					?><th <?=$s['tabletag'] ?>><?=$v ?></th><?
				}
			}
        }
    }
	
	// Шапка для чекров массовое выделение
	function display_th__checkrow($row)
	{
		?><input type="checkbox" class="crudcrh"><?
	}
	
	function display_table__checkrow($row)
	{
		return $link = '<input type="checkbox"  name="checkrow['.$row[$this->key_filed].']"  class="crud_checkrow"  value="1"/>';
	}
	
	
	
	// Добавлять атрибут в массив !  !!!!!!!!! [class]='';
	function display_table_col_attr($v,$k='')
	{
		if($v['del']==1){ return ' style="color:#CCC;" ';}
		if($k=='actions'){ return ' class="noprint" ';}
	}
	 
    // отображение тела таблицы для двумерного массива полученного из mysql
    function display_tablex_body($arr, $titles, $nowrap = false, $hr = false, $all = false, $skin = '1')
    {
        // скины таблиц
        if ($hr)
        {
            $skin_arr[1]['tabletag'] = ' border="0" class="dtable dtable_ch" ';
        }
		 
        else
        {
            $skin_arr[1]['tabletag'] = ' border="0" class="dtable" ';
        }
        $skin_arr[1]['thtag'] = ' ';
        $skin_arr[1]['trtag'] = ' ';
        $skin_arr[1]['tdtag'] = ' ';

        // Все столбцы!
        if ($all)
        {
			$i=0;
            foreach ($arr[0] as $k => $v)
            {
				$i++;
                if (!$titles[$k])
                {
                    $titles[$k] = $k;
                }
            }
        }
        $s = $skin_arr[$skin];

        foreach ($arr as $k => $v)
        {
            // Атрибуты строки
            if (method_exists($this, 'display_table_row_attr'))
            {
                $row_attr = $this->display_table_row_attr($v,$k);
            }

            // Атрибуты столба
            if (method_exists($this, 'display_table_col_attr'))
            {
                $col_attr = $this->display_table_col_attr($v);
            }

			?><tr <?=$s['tabletag'] ?> <?=$row_attr ?>><?
            foreach ($titles as $kt => $vt)
            {

                // теут проверяем метод $kt и если есть заменяем им $v[kt]!
                $met = 'display_table__' . $kt;
                if (method_exists($this, $met))
                {
                    $v[$kt] = $this->$met($v);
                }

                if ($nowrap[$kt])
                {
                    $nw = 'nowrap';
                }
                else
                {
                    $nw = '';
                }
				
				// Не отображать в экспорте
				if($kt=='edit' || $kt == 'actions')
				{
					// $col_attr.=' data-tableexport-display="none" ';
				}
				?><td <?=$s['tabletag'] ?> <?=$col_attr ?> data-col="<?=$kt?>" <?=$nw ?>><?=$v[$kt] ?></td><?

            }
			?>
			</tr>
			<?
            if ($hr)
            {
                if (method_exists($this, 'display_hr_content'))
                {
                    $hr3 = $this->display_hr_content($v);
                }

                if (method_exists($this, 'display_hr_ajax'))
                {
                    $hr2 = ' data-ajax="' . $this->display_hr_ajax($v) . '" ';
                }
				
				if($this->display_table_exrow)
				{
				?>
				<tr class="fw_hiderow" <?=$hr2 ?> ><td colspan="100"><?=$hr3 ?></td></tr>
				<?
				}
			
			
            }
        }
    }
	
	 
	
	
	
	// Получить массив элемента по ид
	function getid( $id )
	{
		global $mysql;
		$filtr = array();
		if( $id )
		{
			$filtr['id']=$id;
			$filtr['showhide']=1; // ПО УСЛОВИЯМ ФИЛЬТРУЮТСЯ СКРЫТЫЕ В НЕКОТОРЫХ КОНТРОЛЛЕРАХ
			$filtr['shodel']=1; // ПО УСЛОВИЯМ ФИЛЬТРУЮТСЯ СКРЫТЫЕ В НЕКОТОРЫХ КОНТРОЛЛЕРАХ в админке формы редактирования когда поиск по ид идет
			
		}
		else{print 'Не указан id элемента'; return;}
		
		$sql = $this->get_base_sql( $filtr );
		$data = $mysql->get_arr($sql,1);
		if(!$data){ print 'Не найден указанный ID:'.$id.' '; print $sql; return;}
		return $data;	
	}
	function get_id_arr($id='')
	{
		return $this->getid($id);
	}
	
	
	// Получить двумрный массив по фильтру
	function getfiltr($filtr=[])
	{
		global $mysql;
		$sql = $this->get_base_sql( $filtr );
		$data = $mysql->get_arr($sql);
		return $data;	
	}
	
	
	### ТИПОВОЙ AJAX CRUD
	
	// Вывод селект поля по данным (уникальные занчения)
	function filtr_select($title,$value_col,$caption_col,$value='',$data='',$select_data='')
	{
		global $filed;
		if( !$data ){ $data = $this->data_nofiltr; }
		
		$sel_data[0]=' - Все - ';
		foreach($data as $k=>$v)
		{
			$sel_data[$v[$value_col]] = $v[$caption_col];
		}
		if( $select_data ){ $sel_data = $select_data; }
		return $filed->select($value_col,$title,$sel_data,$_REQUEST[$value_col]);
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
	
	// Служебные поля для формы поиска
 	function form_sfileds($fid)
	{
		?>
		<input class="fw_ff_h" type="hidden" name="ctr" value="<?=$this->ctr?>">
		<input class="fw_ff_h" type="hidden" name="act" value="ajax_data">
		<input class="fw_ff_h" type="hidden" name="formid" value="<?=$this->formid($fid)?>">
		<input type="hidden" id="order_filed" name="order_filed" value="<?=$this->get_form_sval( $this->formid($fid)  ,'order_filed','request')?>">		
		<input type="hidden" id="order_asc" name="order_asc" value="<?=$this->get_form_sval( $this->formid($fid)  ,'order_asc','request')?>">	
		<?
	}
	

	
	// Контент раскрывающейся строки
	function display_hr_content($v)
	{
		//print '<div style="width:100%; max-width:100vw; text-align:center; padding:10px; " class="loader"  ><img src="loader.gif" height=50/></div>';
		$this->tpl([],'core','tableedit_exrow');
	}
	
	
	
	function act__ajax_data()
	{
		$data=$this->data;
		// Заголовки 
		$titles=$this->ajcrud_table_titles;
 
		// Не переносить по словам
		$nowrap=$this->ajcrud_table_nowrap;
		 
		// print '<pre>';
		// print_r($data[0]);
		// print '</pre>';
	
		if($data)
		{
			$this ->display_tablex_body( $data , $titles ,$nowrap,$this->display_table_exrow);
		}
		else
		{
			// print '<tr><td colspan="10" style="text-align:center"> - нет данных -</td></tr>';
			$this->tpl([],'core','tableedit_nulldata');
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	######################################################################### ЗАпоминание заполнения форм 
	function session_form_save($formid='')
	{
		if(!$formid){ $formid = $_REQUEST['formid']; }
		
		 // Если передан ид формы пишем значения в сессию
		 if($formid)
		 {		  
			 if(!$r){ if( $_POST ){ $r='post'; } else{ $r='get'; }	}
			 unset($_SESSION['sforms'][$r][$formid]);
			 
			 if(!$_SESSION['sforms'][$r][$formid]['generation_time']){ $_SESSION['sforms'][$r][$formid]['generation_time'] = time();  } // Время генерации формы
			 
			 foreach($_GET as $k=>$v )
			 {
				// $_SESSION['sforms']['get'][$formid][$k]=$v;
			 }
			 foreach($_POST as $k=>$v )
			 {
				 if($v )
				 { 
					$_SESSION['sforms']['post'][$formid][$k]=$v;
				 }
				//  else{unset($_SESSION['sforms']['post'][$formid][$k]);}
				//$_SESSION['sforms']['post'][$formid]['dir']='2';
				
				
			 }
			 foreach($_REQUEST as $k=>$v )
			 {
				 if($v)
				 { 
					$_SESSION['sforms']['request'][$formid][$k]=$v;
				 }
			 }
			 
		 }
		 
	}
	// Генерируем ид формы
	function formid($id)
	{
		// Директория Ctr и act остальные гет переменые убрать
		// $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
		return   md5($_GET['ctr'].'-'.$_GET['act']  .'-'.$id);
	}
	
	// ПИшем значение формы в сесссию
	function form_sval($form_id,$name,$value,$r='')
	{
	}
	
	// Получаем значение поля формы из сессии
	function get_form_sval( $form_id  ,$name  , $r='' )
	{	
		if(!$r)	{ if( $_POST ){ $r='post'; } else{ $r='get'; }	}
 		return $_SESSION['sforms'][$r][$form_id][$name];  
	}
	
	
	// чекбукс
	function get_form_check( $form_id  ,$name  )
	{	
		if( $this->get_form_sval( $form_id  ,$name,'post' ) ){ return ' checked="checked" '; }
		else{print $form_id.'-'.$name;}
	}
	
	// select option
	function aj_select( $name, $val  )
	{	
	    $form_id = $_POST['formid'];
		if($val == $this->get_form_sval( $form_id  ,$name ) )
		{
			// print   $form_id.'-'. $this->get_form_sval( $form_id  ,$name );
			return ' selected="selected" ';
		}  
	}
	###########################################################################
	
	
	
}