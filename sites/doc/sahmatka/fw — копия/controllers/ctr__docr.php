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
			 $post_data['puth'] = $_POST['filex'];
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
		 
		 
		$fid = rand(0,100000); //СЛУЧАЙНОЕ ЧИСЛО - ДИРЕКТОРИЯ СОХРАНЕНИЯ ФАЙЛА (против дублирования имен)
		?>
		<div style="padding:20px">
		<form method="POST" enctype="multipart/form-data" >
		<h2>Загрузка файла</h2><br/>
		<?=$filed->text('file_caption','Заголовок',$file_caption);?> 
		<?=$filed->file2('filex','Файл',$v['puth'],$fid,true,true,false);?>  
		
 
		<?=$filed->date('docdate', 'Дата документа',   $v['docdate'] );?>
		<?=$filed->text('comment', 'Комментарий',   $v['comment'] );?>
		
		
		 
		 
		<?=$filed->checkbox( 'del', 'Удалить документ' , $v['del'] );?>
		
		<?=$filed->submit('Сохранить');?>
		</form>
		</div>
		<?
		
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
		
		 
		 
		$archive_dir = 'upload/';
		$archive_file = 'archive.zip';
		$archive_path = $archive_dir.$archive_file;
		 
		$zip = new ZipArchive(); //Создаём объект для работы с ZIP-архивами
		$zip->open($archive_path, ZIPARCHIVE::CREATE); //Открываем (создаём) архив archive.zip
		
		foreach($data as $k=>$v)
		{
			$path = str_replace('/sahmatka/','',$v['puth']);
			$name = basename($path);
			
			if($name && $path && file_exists($path) )
			{
				 $zip->addFile($path,$name); //Добавляем в архив 
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
		$file =$GLOBALS['config']['keys_base_dir'].$data['puth'];
		$this->path_download($file);
	 }
	 
	 
 
	// ФОрма добавления редактивроания сайта
	function act__card()
	{
		global $mysql;
		$id = $_GET['id'];
		
		$dir_id = $_GET['dir_id'];
		 
		$data = $mysql->get_arr('
		SELECT files2node.* FROM files2node
		LEFT JOIN users ON users.id = files2node.user_id
		WHERE 
			node_type="doc_dir" 
			AND files2node_id="'.$id.'" 
			
		ORDER BY uptime, actual');
		?>
		<style>
		.filelink
		{
			border: solid 2px #3d535f; display: inline-block; padding: 10px;
			color:#3d535f;
		}
		.filelink:hover
		{
			color:#000;
			border: solid 2px #000; 
		}
		td{border:solid 1px #000;}
		</style>
		<div style="padding:20px;">
 
		<?
		foreach($data as $k=>$v)
		{
			// $user = 'Пользователь';
			$editdate = $date = date('d.m.Y H:i:s', $v['uptime'] );
			$file_caption = $v['caption'];
			if(!$file_caption){$file_caption = $v['name'];}
			
			if(!$v['actual']) // Актуальная версия файла
			{
				?> 
	
				<div class="actfile" style="padding: 20px;text-align: center;padding-top: 30px; font-size: 20px;  font-weight: bold;">
				
				<a href="ajax_router.php?ctr=doc&act=download&id=<?=$v['files2node_id']?>" class="filelink" >
				 
				Файл:<br/><?=$file_caption?><br/><br/>
				<img src="/sahmatka/template/download.png" width="50"><br/><br/>
				<?=$editdate?><br/>
				<?=$user?>
				</a>
				<br/>
				
				<?
				if($_SESSION['users_group_id']=="3" || $_SESSION['users_group_id']=="1")
				{
					?>
					<a href="iframe_router.php?ctr=doc&act=edit&id=<?=$v['files2node_id']?>" style="font-size:12px; color:red">Редактировать</a>
					<?
				}
				?>
				
				</div>
		 
				<?
				break;
			}
		}
		 
		 
		 
		?>
		<br/><br/>
		История:
		<table width="100%;">
		<?
		//История файла
		foreach($data as $k=>$v)
		{
			//$user = 'Пользователь';
			$editdate = $date = date('d.m.Y H:i:s', $v['uptime'] );;
			$file_caption = $v['caption'];
			if(!$file_caption){$file_caption = $v['name'];}
			
			if(!$v['actual']) // Актуальная версия файла
			{
				?> 
				 <tr>
					 <td>
						<?=$editdate?>
					 </td>
					 <td>
						<a href="<?=$v['link']?>"><?=$file_caption?></a>
					 </td>
					 <td>
						<?=$user?>
					 </td>
					 
				 </tr>
				<?
				break;
			}
		}
		?>
		</table>
		<?
		
		
		//print '<pre>';
		//print_r($data);
		//print '</pre>';
		?>
		</div>
		<?
	}
 
	//Вывод файлов раздела
	function show_dir_files($dir_id)
	{
		global $mysql;
		$data = $mysql->get_arr('SELECT * FROM files2node WHERE node_type="doc_dir" AND node_id="'.$dir_id.'" AND files2node.del="0" ');
		 
	 
		//print_r($data);
		
		
		$file['caption']='Файл';
		
		foreach( $data as $k => $v )
		{
		//	$user ='Пользователь';
			$editdate =  date('d.m.Y H:i:s', $v['uptime'] );
			
			
 
			
			if($v['docdate']){ $docdate =  fromsql_date($v['docdate'],1) ; }
			
			
			$file_caption = $v['caption'];
			if(!$file_caption){$file_caption = $v['name'];}
			
			$out.='<div class="tfile">
			 
			<table width="100%">
			<tr>
			<td>
			<a href="iframe_router.php?ctr=doc&act=card&&id='.$v['files2node_id'].'"  class="iframe_r file-link"><img src="/sahmatka/template/download.png" width=15 /> &nbsp;'.$file_caption.' </a>
			</td>
			<td style="text-align:right; font-size: 12px;">';
			
			if($v['docdate']){	$out.='   <b>Документ от: '.$docdate.'</b>';}
			
			$out.=' / Обновлен: '.$editdate;
	 
				   
				      
				   // $out.='<a href="?act=del" style="color:red" onClick="return window.confirm("Удалить документ?");"> X </a>';
				 
				   
				   
				  $out.='
			</td>
			</tr>
			</table>
			
			
			
			</div>';
		}
		return $out;
	}
	

	
	
 
 
 
	// Рекурсивный метод вывода дерева
	function build_tree($cats,$parent_id,$only_parent = false,$level=0)
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

		
	
	
	
	// Вывод дерева разделов 
	function show_tree()
	{
		global $mysql;
		
		$data = $mysql->get_arr('SELECT * from dir order by parent_dir_id');  
		foreach($data as $k=>$v)
		{
			$cats[$v['parent_dir_id']][$v['dir_id']] =  $v;
		}		
		?>
		
		<style>
		
		.file-link{
			font-weight: bold;
			font-size: 14px;
			color:#3d535f;
		}
		.file-link:hover{
			color:#000;
		}
		
		
		.dirtree  h2{
			display:block;
			position:relative;
			padding: 5px;
			background: #3d535f;
			margin-left: -20px;
			color:#FFF;
			font-size: 16px;
		}
		.dirtree UL {
		  margin: 0;
		  padding: 0;
		  list-style: none;
		  border-left: 1px solid skyblue;
		}
		 
		.dirtree LI {
		  
		  margin-bottom: .8em;
		  padding-left:20px;
		  }
  
		 .tfile{
			width: 100%;
			border: solid 1px #CCC;
			padding: 3px;
			margin: 3px;
		 }
		 
		 
		 
		 

		 
		 
		 
		 
	 #files {
	margin:2em 0 5em;
	 
	}
	.tree {
	    font-size:1.5em;
	}
	.tree,.tree ul,.tree li {
	    list-style:none;
	    margin:0;
	    padding:0;
	    zoom:1;
	}
	.tree ul {
	    margin-left:8px;
	}
	.tree li a {
	    color:#555;
	    padding:.1em 7px .1em 27px;
	    display:block;
	    text-decoration:none;
	    border:1px dashed #fff;
	    background:url(../images/icon-file.gif) 5px 50% no-repeat;
	}
	.tree li a.tree-parent {
	    background:url(../images/icon-folder-open.gif) 5px 50% no-repeat;
	}
	.tree li a.tree-parent-collapsed {
	    background:url(../images/icon-folder.gif) 5px 50% no-repeat;
	}
	.tree li a:hover,.tree li a.tree-parent:hover,.tree li a:focus,.tree li a.tree-parent:focus,.tree li a.tree-item-active {
	    color:#000;
	    border:1px solid#eee;
	    background-color:#fafafa;
	    -moz-border-radius:4px;
	    -webkit-border-radius:4px;
	    border-radius:4px;
	}
	.tree li a:focus,.tree li a.tree-parent:focus,.tree li a.tree-item-active {
	    border:1px solid #e2f3fb;
	    background-color:#f2fafd;
	}
	.tree ul.tree-group-collapsed {
	    display: none;
	}
	
	
		</style>
		<?		
		print '<div class="dirtree" id="files">';		
		print $this-> build_tree($cats,0);//результат в html
		print '</div>';
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
	
	 
	 
	 
	 global $t;
	 
	 
	global $filed;


?><form><?
 // $name, $caption='', $data_arr ='' , $id ,$file_type='', $add=true, $edit = false , $editurl = '' )
 
 
 //function file($name,$caption='',$value='',$fid,$add=true, $edit = false) 
	//$filed->file('file','файл','','123',true,true);	
	 ?></form><?
 }
 
 
 
	
	
}