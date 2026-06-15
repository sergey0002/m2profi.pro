<?

/*
+ РАЗМЕЩЕНИЕ НА САЙТАХ - просто указываем ссылку на скачивание ! при этом чтобы она работала документ должен быть закреплен (заблокирован от удаления скрытия итп + в служебном комментарии а каких сайтах он доступен )!
+Счетчик скачиваний + лог скачиваний 
ip+user дата время итп РЕФЕРЕР! откуда качается



1. После добавления файла получать заново заполнять форму редирект на гет!!!!


предотврещение повторной обработки формы на СЕССИЯХ + jS

При генерации формы пишем скрытое поле id формы



== Функционал
+ !Нормальная история
+ !Формирование архивов веток дерева с сохранением структуры PATH каждой ветки 
+ Отображение на сайте ссылок на файлы из папки циклом PHP? jsoon 
и яваскрипт для встраивания этого счастья в сайт 


+ Роли права на каждую ветку каждой роли 
+ дата изменения именно файла!!!! отдельно фиксировать 
+ Шаринг папок и файлов архив+история по одному
+ БОЛЬШИЕ КОММЕНТАРИИ В ВИДЕ ТЕКСТА ! (документы прямо в структуре)



== Интерфейс
+ КОментарии в виде title подсказок
+ массовое добавление файлов перетаскиванием или плюсиком под веткой
+ сворачивание разворачивание веток с запоминанием
+ Перетаскивание файлов между ветками скрытие отображение
+ ХРАНЕНИЕ ДОСТУПОВ (не документ а другая сущность?) ? логин пароль произвольные набор полей? 
нода документ содержит поля эти, а ноды другого типа имеют свои формы и контроллеры редактирования 
поля джоинами

+ ПОИСК AJAX по имени файла 




*/
// Класс поля
class fw_filed
{
	function error( $err ) { $this->error[] = $err; }
}

// Класс обекта единицы
class fw_model{
	private  $fileds_obj; // Свойства текущего обекта - классы типа проя массив
	private  $fileds_arr;
	var $id; // ИД текущего обекта
	private $declare_fileds;
	
	// Получить массив ствойств
	function get_fileds_arr()
	{
		return get_object_vars($this);
	}
	
	// Получить массив ствойств
	function set_filed($var,$value)
	{
		$this->$var=$value;
	}
	
	
	// Получить стоблцы таблицы
	function get_table_fileds($table='',$bd='')
	{
		global $mysql;
   
		if(!$table){$table=$this->table;}
		if(!$bd){$bd='m2profi_doc_m2profi';}
		
		$data = $mysql->get_arr('SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA` = "'.$bd.'" AND `TABLE_NAME` = "'.$table.'" ;' );
		foreach( $data as $k=>$v )
		{
			$fileds[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
		}
		return $fileds;
	}
	
	// Обявляем свойство 
	function declare_filed($name)
	{
		$this->declare_fileds[$name] =$name; 
	}
	
	//// Сохранение одной таблицы!
	function save()
	{
		
		print '<pre>';
		
		//print_r($this->get_table_fileds());
		//print_r($this->get_fileds_arr());
		print '</pre>';
		
		global $mysql;
		 
		if( $this->id && $this->get_fileds_arr() ) // существующий элемент
 		{
 			return $mysql->update_for_key( $this->table , $this->key_filed , $this->id, $this->fileds_arr );
 		}
 		elseif( $this->fileds ) // Новый элемент
 		{
 			return $mysql->insert( $this->table , $this->fileds_arr );
 		}
 		else
 		{
 			$this->error('НЕТ ОБЕКТА ДЛЯ СОХРАНЕНИЯ');
 		}
	}
	
	
	function get($id)
	{
		global $mysql;
		if($id)
		{
			$data = $mysql -> get_for_key( $this->table , $this->key_filed , $id , 1 );
		}
		foreach($data as $k=>$v)
		{
			$this->$k=$v;
		}
		return $data;
	}	
	
	
	
	function clean()
	{
		$this->id = false;
		$this->fileds = array();
	}
		
	 
	// лог ошибок
	function error( $err ) { $this->error[] = $err; }
}



/*

ларавель модели грузим добавляем туда ноду и ... и на ней делаем файлы итп

*/




// Класс обекта множества
class fw_model_s
{
	var $models; // Свойства текущего обекта - массив обектов  
	function get()
	{
		global $mysql;
		// $this->
	}
	function error( $err ) { $this->error[] = $err; }
}
 

### #####################
class fw_node extends fw_model
{
	var $table = 'fw_nodes';
	var $key_filed = 'fw_node_id';
}




class fw_node_file extends fw_model
{
	
}
#############################################

 /*
$x = new fw_node();
$x->get(198);
print $x->title;
$x->title=123123;
 $x->save();
 
 */
class ctr__doc extends ctr__
{  
 

	var $table = 'agency'; //Главная таблица
	var $key_filed = 'agency_id'; // Ключевое поле главной таблицы
	var $ctr = 'agency';
    var $title = 'Агентства';
   
	function __construct()
	{
		 
	}
	
	
	function get_file_url( $data )
	{
		return $data['link'];
	}
 
 
 ##########################
 
	// 
	function add_node($date)
	{
		global $mysql;
		if($date)
		{
			return $mysql->insert('node',$date);
		}
		else
		{
			return false;
		}
	}
	
	// 
	function get_node($id)
	{
		global $mysql;
		
		$v = $mysql->get_arr('SELECT * FROM fw_node WHERE fw_node_id="'.$id.'"',1);
		return $v;
	}
	
	
	
	function get_node_files($node_id)
	 {
		global $mysql;
		$v = $mysql->get_arr('SELECT * FROM files2node WHERE node_id="'.$id.'" AND del="0" ');
		return $v;
	}
	function add_node_file($node_id,$file_data,$file_id='')
	{
		global $mysql;
		
		$v = $mysql->get_arr('SELECT * FROM fw_node WHERE fw_node_id="'.$id.'"',1);
		return $v;
	}
	 
 
	function update_node_file($node_id,$file_data)
	{
		global $mysql;
		
		$v = $mysql->get_arr('SELECT * FROM fw_node WHERE fw_node_id="'.$id.'"',1);
		return $v;
	}
 
	 
	 function act__delete()
	 {
		 
		$post_data['del']=1;
		if($_GET['id'])
		{
			$mysql->update_for_key('files2node','files2node_id',$_GET['id'],$post_data);
		}
		 
	 }
	 
 
 
 ##########################
 
	 function act__edit()
	 {
	 
		if($_SESSION['users_group_id']!="3" && $_SESSION['users_group_id']!="1")
		{
			 return;
		}
		 
		 // Пред сохранение перед добавлением ! файлв с активностью =0! статус черновик!
		 global $filed;
		 global $mysql;
		 $id = $_GET['id'];
		 
		 
		 if($_POST)
		 {
			// print 'обработка формы';
			// print_r($_POST);
			 
			 $post_data=array();
			 
			 $post_data['node_type']='doc_dir';
			 
			 
			 if(!$_POST['file_caption']){$_POST['file_caption'] = basename($_POST['filex']);}
			 $post_data['name']=$_POST['file_caption'];
			 $post_data['caption']=$_POST['file_caption'];
			 
			 $post_data['link'] = $GLOBALS['config']['domains']['doc'].'/'.$_POST['filex'];
			 $post_data['puth'] = str_replace('/sahmatka/upload/', '', $_POST['filex']);
			 $post_data['uptime'] = time();
			 $post_data['comment'] = $_POST['comment'];
			 
			if($_POST['docdate']){ $post_data['docdate'] = $_POST['docdate']; }
 
			 
			 if($_POST['del'])
			 {
				 $post_data['del'] = $_POST['del'];
			 }
			 // $post_data['size'] = filesize( $_POST['filex'] );
		     
			 
			 // ИД ПАПКИ В КОТОРУЮ ДОБАВЛЯЕТСЯ ФАЙЛ
			 if( $_GET['dir_id'] ) { $post_data['node_id'] =  $_GET['dir_id']; }
			 
			 // $post_data['user'] = $_SESSION;
			   
			 if($_GET['id'])
			 {
				 $mysql->update_for_key('files2node','files2node_id',$_GET['id'],$post_data);
			 }
			 else
			 {
				 $mysql->insert('files2node',$post_data);
			 }
			   
		 }
		 
		 if($id)
		 {
			$v = $mysql->get_arr('SELECT * FROM files2node WHERE files2node_id="'.$id.'"',1);
			// print_r($data);
			 
			$file_caption = $v['caption'];
			if(!$file_caption){$file_caption = $v['name'];}
		 }
		 else // добавляем черновик!
		 {
			 
			 //Добавляем ноду с черновиком!
			  
			 
			 $data =array();
			 $data['node_type'] = 'file';
			 $data['node_id'] = 'doc_dir';
			 
			 $data['dir_id'] = (int) $_GET['dir_id'];
			  
			  
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 $data['node_type'] = '';
			 
			// $mysql->
			 // редирект на черновик!
		 }
		 
		 
		$data['fid'] = rand(0,100000); //СЛУЧАЙНОЕ ЧИСЛО - ДИРЕКТОРИЯ СОХРАНЕНИЯ ФАЙЛА (против дублирования имен)
		$data['v'] = $v;
		$data['file_caption'] = $file_caption;
		$data['filed'] = $filed;

		$this->tpl($data, 'doc', 'edit_form');
		
		//print_r($_GET);		
	 }
	 
	 
	 
	 
	 
	 
	 
	 
	 // 
	 
	 
	 
	 function act__zipdir()
	 {
		global $mysql;
		$node_id = $_GET['node_id'];
		
		if(!$node_id){return;}
		
		$data = $mysql->get_arr('SELECT * FROM files2node WHERE files2node.node_type="doc_dir" AND node_id="'.$node_id.'" AND `del`="0" AND `show`="1" ');
 
		if(!$data){return;}
		
		 
		 
		$archive_dir = $GLOBALS['config']['upload_dir'];
		$archive_file = 'archive.zip';
		$archive_path = $archive_dir.$archive_file;
		 
		$zip = new ZipArchive(); //Создаём объект для работы с ZIP-архивами
		$zip->open($archive_path, ZIPARCHIVE::CREATE); //Открываем (создаём) архив archive.zip
		
		foreach($data as $k=>$v)
		{
			$path = str_replace('/sahmatka/upload/', '', $v['puth']);
			$path = str_replace('/sahmatka/', '', $path);
			$path = ltrim($path, '/');
			
			$full_path = $GLOBALS['config']['upload_dir'] . $path;
			$name = basename($path);
			
			if($name && $path && file_exists($full_path) )
			{
				 $zip->addFile($full_path,$name); //Добавляем в архив 
			}
		}
		
		 
		$zip->close(); //Завершаем работу с архивом
		 
		// Отправляем на скачивание
		 $this->path_download($archive_path);
 
	 }
 
	 
	 
	 
	 
	 // Передает на загрузку файл по пути
	 function path_download($file='')
	 {
		if(file_exists($file))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			exit;
		}
		else
		{
			print 'файл не найден';
	 
			print $file;
		}
	 }
	 
	 
	 
 
 
	 function act__download($id='') // ПЕРЕДАЛАТЬ НА ПАТЧ
	 {
		 //a href="ajax_router.php?ctr=doc&act=download&id= $v['files2node_id']
		if(!$id){$id=$_GET['id'];}
		 
		global $mysql;
		$data = $mysql->get_arr('SELECT * FROM files2node WHERE files2node_id="'.$id.'"',1);
		$path = str_replace('/sahmatka/upload/', '', $data['puth']);
		$path = str_replace('/sahmatka/', '', $path);
		$path = ltrim($path, '/');
		$file = $GLOBALS['config']['upload_dir'].$path;
		$this->path_download($file);
	 }
	 
	 
 
	// ФОрма добавления редактивроания сайта
	function act__card()
	{
		global $mysql;
		$id = $_GET['id'];
		
		$dir_id = $_GET['dir_id'];
		 
		$card_data = $mysql->get_arr('
		SELECT files2node.* FROM files2node
		LEFT JOIN users ON users.id = files2node.user_id
		WHERE 
			node_type="doc_dir" 
			AND files2node_id="'.$id.'" 
			
		ORDER BY uptime, actual');

		$data['card_data'] = $card_data;
		$this->tpl($data, 'doc', 'document_card');
	}
 
// Вывод файлов раздела
function show_dir_files($dir_id, $search_query = '')
{
    global $mysql;
    $sql = 'SELECT * FROM files2node WHERE node_type="doc_dir" AND node_id="' . (int)$dir_id . '" AND files2node.del="0" ';
    
    if (!empty($search_query)) {
        $search_query_safe = mysqli_real_escape_string($mysql->c, $search_query);
        $sql .= " AND (caption LIKE '%" . $search_query_safe . "%' OR name LIKE '%" . $search_query_safe . "%')";
    }

    $data = $mysql->get_arr($sql);
    $out = '';

    foreach ($data as $k => $v) {
        $editdate = date('d.m.Y H:i:s', $v['uptime']);

        if ($v['docdate']) {
            $docdate = fromsql_date($v['docdate'], 1);
        }

        $file_caption = $v['caption'];
        if (!$file_caption) {
            $file_caption = $v['name'];
        }

        $out .= '<div class="tfile">
            <table width="100%">
                <tr>
                    <td>
                        <a href="iframe_router.php?ctr=doc&act=card&&id=' . $v['files2node_id'] . '" class="iframe_r file-link">
                            <img src="/sahmatka/template/download.png" width=15 /> &nbsp;' . $file_caption . '
                        </a>
                    </td>
                    <td style="text-align:right; font-size: 12px;">';

        if ($v['docdate']) {
            $out .= '   <b>Документ от: ' . $docdate . '</b>';
        }

        $out .= ' / Обновлен: ' . $editdate;
        $out .= '</td></tr></table></div>';
    }

    return $out;
}
	

	// Рекурсивный метод вывода дерева
	function build_tree2($cats,$parent_id,$only_parent = false,$level=0)
	{	
		$level ++;
		if(is_array($cats) and isset($cats[$parent_id]))
		{
			$tree = '<ul>';
			if($only_parent==false)
			{			 
				foreach($cats[$parent_id] as $cat)
				{
					// $tree .= '<li><h2>'.$cat['dir_title'].'</h2>  ' ;
					if($_SESSION['users_group_id']=="3" || $_SESSION['users_group_id']=="1"){$addlink = '<a class="iframe_r" href="iframe_router.php?ctr=doc&act=edit&dir_id='.$cat['dir_id'].'" style="font-weightbold; color:#FFF;" title="Добавить файл">+</a>';}
					
					 $tree .= '<li><h2><table width=100%><tr><td>'.$cat['dir_title'];
					// $tree.=' <a href="ajax_router.php?ctr=doc&act=zipdir&node_id='.$cat['dir_id'].'">[Скачать архив]</a>';
					 $tree.='</td> <td style="text-align:right;">'.$addlink.'</td></tr></table></h2>  ' ;

					$tree .=  ctr__doc::build_tree($cats,$cat['dir_id'],false,$level);
					$tree .= $this->show_dir_files($cat['dir_id']);
					$tree .= '</li>';
				}
			}
			elseif(is_numeric($only_parent))
			{ 
				$cat = $cats[$parent_id][$only_parent];
				$tree .= '<li>'.$cat['dir_title'].'  ' ;
				$tree .=  ctr__doc::build_tree($cats,$cat['dir_id'],false,$level);
				$tree .= '</li>';
			}
			$tree .= '</ul>';
		}
		else 
		{ 
			return null;
		}
		return $tree;
	}
	
	
	
	
	
	
// Рекурсивный метод вывода дерева
function build_tree($cats, $parent_id, $only_parent = false, $level = 0, $search_query = '')
{
    $level++;
    if (is_array($cats) && isset($cats[$parent_id])) {
        $tree = '<ul class="tree-list">';
        if ($only_parent == false) {
            foreach ($cats[$parent_id] as $cat) {
                $has_children = isset($cats[$cat['dir_id']]) && !empty($cats[$cat['dir_id']]);
                
                // Получаем файлы для текущей директории
                $files_html = $this->show_dir_files($cat['dir_id'], $search_query);

                // Если есть дочерние элементы или найдены файлы, показываем папку
                if ($has_children || !empty($files_html)) {
                    $children_class = $has_children ? 'has-children' : '';
    
                    if ($_SESSION['users_group_id'] == "3" || $_SESSION['users_group_id'] == "1") {
                        $addlink = '<a class="iframe_r" href="iframe_router.php?ctr=doc&act=edit&dir_id=' . $cat['dir_id'] . '" style="font-weight:bold; color:#FFF; float:right;" title="Добавить файл">+</a>';
                    } else {
                        $addlink = '';
                    }
    
                    $tree .= '<li class="' . $children_class . '" data-id="' . $cat['dir_id'] . '" data-level="' . $level . '">';
    
                    $toggle_icon = ($has_children || !empty($files_html)) ? '<span class="toggle-icon">▶</span> ' : '';
                    $tree .= '<h2 class="folder-header">' . $toggle_icon . $cat['dir_title'] . $addlink . '</h2>';
    
                    // Получаем поддерево (рекурсивно)
                    $subtree = $this->build_tree($cats, $cat['dir_id'], false, $level, $search_query);
    
                    // Если есть хоть что-то (подпапки ИЛИ файлы) — оборачиваем в .children
                    if ($subtree || $files_html) {
                        $display_style = !empty($search_query) ? 'display:block;' : 'display:none;';
                        $tree .= '<div class="children" style="' . $display_style . '">';
                        if ($subtree) {
                            $tree .= $subtree;
                        }
                        if ($files_html) {
                            $tree .= $files_html;
                        }
                        $tree .= '</div>';
                    }
    
                    $tree .= '</li>';
                }
            }
        } elseif (is_numeric($only_parent)) {
            $cat = $cats[$parent_id][$only_parent];
            $tree .= '<li>' . $cat['dir_title'] . ' ';
            $tree .= $this->build_tree($cats, $cat['dir_id'], false, $level, $search_query);
            $tree .= '</li>';
        }
        $tree .= '</ul>';
        return $tree;
    }
    return null;
}
// Вывод дерева разделов 
function show_tree()
{
    global $mysql;

    $data_sql = $mysql->get_arr('SELECT * FROM dir WHERE del = 0 ORDER BY parent_dir_id, `order`, dir_id');
    $cats = [];
    foreach ($data_sql as $k => $v) {
        $cats[$v['parent_dir_id']][$v['dir_id']] = $v;
    }

    $data['cats'] = $cats;
    $this->tpl($data, 'doc', 'document_tree');
}
	
	function act__search_tree()
    {
        global $mysql;
        header('Content-Type: application/json');
        $search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

        if (empty($search_query)) {
            // Если запрос пустой, возвращаем пустой массив, jsTree покажет все узлы
            echo json_encode([]);
            exit();
        }

        // 1. Найти файлы, соответствующие запросу
        $search_query_safe = mysqli_real_escape_string($mysql->c, $search_query);
        $files_sql = "SELECT files2node_id FROM files2node WHERE (name LIKE '%{$search_query_safe}%' OR caption LIKE '%{$search_query_safe}%') AND del=0";
        $found_files = $mysql->get_arr($files_sql);

        if (empty($found_files)) {
            // Если ничего не найдено, возвращаем специальное значение, которое jsTree поймет как "ничего не найдено"
            echo json_encode(['#']); 
            exit();
        }

        // 2. Собрать ID найденных файлов для jsTree
        $file_ids = [];
        foreach ($found_files as $file) {
            $file_ids[] = 'file_' . $file['files2node_id'];
        }

        // 3. Отправить массив ID файлов
        echo json_encode($file_ids);
        exit();
    }
	
	
	
	
	
	
	
	
	
	function dir_access()
	{
		
	}
	
	
	function file_access()
	{
		
	}
	
 
 function act__index()
 {
	  global $t;
		$t['h1'] = 'Документы';
	 $this->show_tree();
	 
	 /// ВЫводим разделы ДЕРЕВО + файлы в каждом разделе (ссылки + версии итп)
 }
 
 	function act__get_tree_data()
	{
		global $mysql;

		// Параметр для показа удаленных элементов
		$show_deleted = isset($_GET['show_deleted']) ? (int)$_GET['show_deleted'] : 0;
		$del_condition = $show_deleted ? 'del IN (0, 1)' : 'del = 0';

		// Параметры для фильтрации по дате
		$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
		$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
		
		$date_condition = '';
		if ($date_from && $date_to) {
			// Преобразуем формат даты из dd.mm.yyyy в yyyy-mm-dd
			$date_from_parts = explode('.', $date_from);
			$date_to_parts = explode('.', $date_to);
			
			if (count($date_from_parts) === 3 && count($date_to_parts) === 3) {
				$date_from_sql = $date_from_parts[2] . '-' . $date_from_parts[1] . '-' . $date_from_parts[0];
				$date_to_sql = $date_to_parts[2] . '-' . $date_to_parts[1] . '-' . $date_to_parts[0];
				$date_condition = " AND docdate >= '$date_from_sql' AND docdate <= '$date_to_sql'";
			}
		} elseif ($date_from) {
			$date_from_parts = explode('.', $date_from);
			if (count($date_from_parts) === 3) {
				$date_from_sql = $date_from_parts[2] . '-' . $date_from_parts[1] . '-' . $date_from_parts[0];
				$date_condition = " AND docdate >= '$date_from_sql'";
			}
		} elseif ($date_to) {
			$date_to_parts = explode('.', $date_to);
			if (count($date_to_parts) === 3) {
				$date_to_sql = $date_to_parts[2] . '-' . $date_to_parts[1] . '-' . $date_to_parts[0];
				$date_condition = " AND docdate <= '$date_to_sql'";
			}
		}

		// 1. Получить директории
		$dirs_sql = "SELECT dir_id, parent_dir_id, dir_title, del, `order` FROM dir WHERE $del_condition";
		$dirs = $mysql->get_arr($dirs_sql);

		// 2. Получить файлы
		$files_sql = "SELECT files2node_id as id, node_id, caption as title, name, puth, docdate, uptime, del, `order` FROM files2node WHERE $del_condition AND node_type = \"doc_dir\"$date_condition";
		$files = $mysql->get_arr($files_sql);

		$tree_data = [];

		// 3. Обработать директории
		foreach ($dirs as $dir) {
			$is_deleted = (int)$dir['del'] === 1;
			$tree_data[] = [
				'id' => 'dir_' . $dir['dir_id'],
				'parent' => (!$dir['parent_dir_id']) ? '#' : 'dir_' . $dir['parent_dir_id'],
                'text' => $dir['dir_title'],
				'type' => 'folder',
				'li_attr' => ['class' => 'type-folder' . ($is_deleted ? ' node-deleted' : '')],
				'data' => [
					'deleted' => $is_deleted,
                    'order' => (int)$dir['order'],
                    'sort_date' => 0,
                    'actions_html' => $this->_get_folder_actions_html($dir)
				]
			];
		}

		foreach ($files as $file) {
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
			if(!$ext && $file['puth']) {
				$ext = pathinfo($file['puth'], PATHINFO_EXTENSION);
			}
			
            $docdate = $file['docdate'] ? date('d.m.Y', strtotime($file['docdate'])) : '';
            $uptime = $file['uptime'] ? date('d.m.Y H:i:s', $file['uptime']) : '';
			$is_deleted = (int)$file['del'] === 1;
            $sort_date = $file['docdate'] ? strtotime($file['docdate']) : 0;

			$tree_data[] = [
				'id' => 'file_' . $file['id'],
				'parent' => 'dir_' . $file['node_id'],
				'text' => $file['title'] . ($ext ? '.' . $ext : '') . '<span class="mobile-menu-btn">⋮</span>',
				'type' => 'file',
				'li_attr' => ['class' => 'type-file' . ($is_deleted ? ' node-deleted' : '')],
                'data' => [
                    'docdate' => $docdate,
                    'uptime' => $uptime,
					'deleted' => $is_deleted,
                    'order' => (int)$file['order'],
                    'sort_date' => $sort_date,
                    'actions_html' => $this->_get_file_actions_html($file)
                ]
			];
		}

        // Сортировка: сначала по order (ASC), затем по дате/id (DESC)
        usort($tree_data, function($a, $b) {
            if ($a['data']['order'] != $b['data']['order']) {
                return $a['data']['order'] - $b['data']['order'];
            }
            
             $id_a = (int)filter_var($a['id'], FILTER_SANITIZE_NUMBER_INT);
             $id_b = (int)filter_var($b['id'], FILTER_SANITIZE_NUMBER_INT);
             
             return $id_b - $id_a; // DESC
        });

		// 5. Вывести результат в JSON
		header('Content-Type: application/json');
		echo json_encode($tree_data);
		exit();
	}
	
    function act__move_node()
    {
        global $mysql;
        header('Content-Type: application/json');

        if (!isset($_POST['id']) || !isset($_POST['parent']) || !isset($_POST['position'])) {
            echo json_encode(['status' => 'error', 'message' => 'Отсутствуют необходимые параметры.']);
            exit();
        }

        $id = $_POST['id'];
        $parent = $_POST['parent'];
        $position = (int)$_POST['position'];

        list($type, $numeric_id) = explode('_', $id);
        $numeric_id = (int)$numeric_id;

        if ($parent === '#') {
            $parent_id = 0;
        } else {
            list($parent_type, $parent_id) = explode('_', $parent);
            $parent_id = (int)$parent_id;
        }

        // 1. Обновляем родителя перемещаемого узла
        if ($type === 'file') {
            $mysql->update_for_key('files2node', 'files2node_id', $numeric_id, ['node_id' => $parent_id]);
        } elseif ($type === 'dir') {
            $mysql->update_for_key('dir', 'dir_id', $numeric_id, ['parent_dir_id' => $parent_id]);
        }

        // 2. Получаем всех детей нового родителя (папки и файлы) для сортировки
        
        $dirs_sql = "SELECT dir_id as id, 'dir' as type, `order` FROM dir WHERE parent_dir_id = '$parent_id' AND del = 0 ORDER BY `order` ASC";
        $dirs = $mysql->get_arr($dirs_sql);

        $files_sql = "SELECT files2node_id as id, 'file' as type, `order` FROM files2node WHERE node_id = '$parent_id' AND node_type = 'doc_dir' AND del = 0 ORDER BY `order` ASC";
        $files = $mysql->get_arr($files_sql);
        
        $siblings = [];
        foreach ($dirs as $d) {
            if ($type === 'dir' && (int)$d['id'] === $numeric_id) continue;
            $siblings[] = $d;
        }
        foreach ($files as $f) {
            if ($type === 'file' && (int)$f['id'] === $numeric_id) continue;
            $siblings[] = $f;
        }

        // Сортируем siblings по текущему order (на всякий случай)
        usort($siblings, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        // 3. Вставляем перемещаемый узел в новую позицию
        $moved_node = ['id' => $numeric_id, 'type' => $type];
        
        if ($position >= count($siblings)) {
            $siblings[] = $moved_node;
        } else {
            array_splice($siblings, $position, 0, [$moved_node]);
        }

        // 4. Обновляем order для всех узлов
        foreach ($siblings as $index => $node) {
            $new_order = $index;
            if ($node['type'] === 'dir') {
                $mysql->update_for_key('dir', 'dir_id', $node['id'], ['order' => $new_order]);
            } elseif ($node['type'] === 'file') {
                $mysql->update_for_key('files2node', 'files2node_id', $node['id'], ['order' => $new_order]);
            }
        }

        echo json_encode(['status' => 'success']);
        exit();
    }

    function act__create_folder()
    {
        global $mysql;
        header('Content-Type: application/json');

        $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : 'Новая папка';

        if ($parent_id !== 0) {
             $parent_id = str_replace('dir_', '', $parent_id);
             $parent_id = (int)$parent_id;
        }

        $data = [
            'parent_dir_id' => $parent_id,
            'dir_title' => $title,
            'del' => 0,
            'order' => 0 // Можно добавить логику для порядка
        ];

        $new_id = $mysql->insert('dir', $data);

        if ($new_id) {
            echo json_encode(['status' => 'success', 'id' => 'dir_' . $new_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Не удалось создать папку']);
        }
        exit();
    }

    function act__delete_node()
    {
        global $mysql;
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : '';

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Не указан ID узла']);
            exit();
        }

        list($type, $numeric_id) = explode('_', $id);
        $numeric_id = (int)$numeric_id;

        if ($type === 'dir') {
            // Мягкое удаление папки
            $mysql->update_for_key('dir', 'dir_id', $numeric_id, ['del' => 1]);
            // Также можно скрыть все вложенные файлы и папки, но пока просто скрываем саму папку
        } elseif ($type === 'file') {
            // Мягкое удаление файла
            $mysql->update_for_key('files2node', 'files2node_id', $numeric_id, ['del' => 1]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Неизвестный тип узла']);
             exit();
        }

        echo json_encode(['status' => 'success']);
        exit();
    }

    function act__restore_node()
    {
        global $mysql;
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : '';

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Не указан ID узла']);
            exit();
        }

        list($type, $numeric_id) = explode('_', $id);
        $numeric_id = (int)$numeric_id;

        if ($type === 'dir') {
            $mysql->update_for_key('dir', 'dir_id', $numeric_id, ['del' => 0]);
        } elseif ($type === 'file') {
            $mysql->update_for_key('files2node', 'files2node_id', $numeric_id, ['del' => 0]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Неизвестный тип узла']);
             exit();
        }

        echo json_encode(['status' => 'success']);
        exit();
    }

    function act__rename_node()
    {
        global $mysql;
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';

        if (!$id || !$title) {
            echo json_encode(['status' => 'error', 'message' => 'Не указан ID или новое название']);
            exit();
        }

        list($type, $numeric_id) = explode('_', $id);
        $numeric_id = (int)$numeric_id;

        if ($type === 'dir') {
            $mysql->update_for_key('dir', 'dir_id', $numeric_id, ['dir_title' => $title]);
        } elseif ($type === 'file') {
            // Опционально: переименование файлов (caption)
            $mysql->update_for_key('files2node', 'files2node_id', $numeric_id, ['caption' => $title]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Неизвестный тип узла']);
             exit();
        }

        echo json_encode(['status' => 'success']);
        exit();
    }

    /**
     * Генерирует HTML для меню действий папки.
     * @param array $dir - Массив с данными о папке.
     * @return string - HTML-код меню.
     */
    protected function _get_folder_actions_html($dir)
    {
        // На основе прав доступа будем формировать меню
        $actions = '';
        $actions .= '<a href="#" class="action-btn add-folder-btn" title="Создать папку">📂</a>';
        $actions .= '<a href="#" class="action-btn add-doc-btn" title="Добавить документ">📄</a>';
        $actions .= '<a href="#" class="action-btn rename-btn" title="Переименовать">✏️</a>';

        if (isset($dir['deleted']) && $dir['deleted']) {
            $actions .= '<a href="#" class="action-btn restore-btn" title="Восстановить">♻️</a>';
        } else {
            $actions .= '<a href="#" class="action-btn delete-btn" title="Удалить">🗑</a>';
        }

        return '<span class="tree-actions">' . $actions . '</span>';
    }

    /**
     * Генерирует HTML для меню действий файла.
     * @param array $file - Массив с данными о файле.
     * @return string - HTML-код меню.
     */
    protected function _get_file_actions_html($file)
    {
        // На основе прав доступа будем формировать меню
        $actions = '';
        $actions .= '<a href="#" class="action-btn rename-btn" title="Переименовать">✏️</a>';
        
        if (isset($file['deleted']) && $file['deleted']) {
            $actions .= '<a href="#" class="action-btn restore-btn" title="Восстановить">♻️</a>';
        } else {
            $actions .= '<a href="#" class="action-btn delete-btn" title="Удалить">🗑</a>';
        }

        return '<span class="tree-actions">' . $actions . '</span>';
    }
}