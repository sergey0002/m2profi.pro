<?
class ctr__dir extends ctr__
{ 

	var $table = 'dir'; //Главная таблица
	var $key_filed = 'dir_id'; // Ключевое поле главной таблицы
	var $ctr = 'dir';
  
   
  
	function __construct()
	{
		$this->title='Рубрики';
		$GLOBALS['t']['title']=$this->title;
		
		global $mysql;
		// $this->session_form_save();

		// Получаем данные 
		$this->data_arr = $mysql->get_arr($this->get_base_sql());
 
	}
	
	function translit($text, $translit = 'ru_en') 
	{ 
		$RU['ru'] = array( 
			'Ё', 'Ж', 'Ц', 'Ч', 'Щ', 'Ш', 'Ы',  
			'Э', 'Ю', 'Я', 'ё', 'ж', 'ц', 'ч',  
			'ш', 'щ', 'ы', 'э', 'ю', 'я', 'А',  
			'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И',  
			'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',  
			'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ъ',  
			'Ь', 'а', 'б', 'в', 'г', 'д', 'е',  
			'з', 'и', 'й', 'к', 'л', 'м', 'н',  
			'о', 'п', 'р', 'с', 'т', 'у', 'ф',  
			'х', 'ъ', 'ь', '/'
			); 

		$EN['en'] = array( 
			"Yo", "Zh",  "Cz", "Ch", "Shh","Sh", "Y'",  
			"E'", "Yu",  "Ya", "yo", "zh", "cz", "ch",  
			"sh", "shh", "y'", "e'", "yu", "ya", "A",  
			"B" , "V" ,  "G",  "D",  "E",  "Z",  "I",  
			"J",  "K",   "L",  "M",  "N",  "O",  "P",  
			"R",  "S",   "T",  "U",  "F",  "Kh",  "''", 
			"'",  "a",   "b",  "v",  "g",  "d",  "e",  
			"z",  "i",   "j",  "k",  "l",  "m",  "n",   
			"o",  "p",   "r",  "s",  "t",  "u",  "f",   
			"h",  "''",  "'",  "-"
			); 
		if($translit == 'en_ru') { 
			$t = str_replace($EN['en'], $RU['ru'], $text);         
			$t = preg_replace('/(?<=[а-яё])Ь/u', 'ь', $t); 
			$t = preg_replace('/(?<=[а-яё])Ъ/u', 'ъ', $t); 
			} 
		else {
			$t = str_replace($RU['ru'], $EN['en'], $text);
			$t = preg_replace("/[\s]+/u", "_", $t); 
			$t = preg_replace("/[^a-z0-9_\-]/iu", "", $t); 
			$t = strtolower($t);
			}
		return $t; 
	}	




	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		global $mysql;
		$q = 'SELECT '.$this->table.'.* ';

		//if( $_REQUEST['dir'] ) { $q.= ', count(dir) as c ';}

        $q.=' FROM `'.$this->table.'` WHERE  `'.$this->table.'`.`del` != "2" ';
			
		// Фильтрация раздела
		if( $_REQUEST['dir']){ $q.=' AND dir.dir_id = "'. $_REQUEST['dir'].'" ';}
		if( $_REQUEST['id']){ $q.=' AND '.$this->table.'.'.$this->key_filed.' = "'. $_REQUEST['id'].'" ';}
		
		// Удаленные
		if( !$_REQUEST['show_dell']){ $q.=' AND '.$this->table.'.del = "0" '; }
		else{ }
		 
		if( $_REQUEST['act']!='sel_dir' || 1==1 )
		{
			$search_arr = array();
			$search_arr[]=''.$this->table.'.dir_title';
			if( $_REQUEST['search']){ $q.=$mysql->search($search_arr,$_REQUEST['search']); }
		}
		
		if($where){$q.=' '.$where.' ';}
	 
		// Группировка (для вывода селектов)
		//if( $_GET['act']=='sel_dir' ){ $q.=' GROUP BY dir_id ORDER BY dir';}
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
			
						print '<td class="dtable" style="white-space:nowrap;">';
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
		global $filed;
			$desc_cut = mb_substr($result['descr'], 0, 50);
			if(mb_strlen($desc_cut)<mb_strlen($result['descr'])){$desc_cut.='...';}
			$desc_cut = $result['descr'];
			
			if($result['del']){$del_class = 'dtable_del_class';}
			else{ $del_class = ''; }
			
			
			
			if($result['logo'])	{	$logo = $result['logo']; 	}
			else{	$logo = '/admin/nofoto.png'; 	}
			
			$rubric = 'Рубрики';
			
			 print ' <tr class="dtable akk" style="background-color:#EEE;  border: solid 2px #FFF;"   id="ajaxitem_'.$result[$this->key_filed].'">';
			 print ' <td class="dtable '.$del_class.'">'.$result[$this->key_filed].'</td>'.
					'<td class="dtable '.$del_class.'"><img src="'.$logo.'" style="height:60px; max-width:100px; margin:20px;" /></td>'.
					'<td class="dtable '.$del_class.'" style="white-space: nowrap;"><span>'.$result['title'].'</td>'.
					'<td class="dtable '.$del_class.'">'.$desc_cut.'</td>'.
					'<td class="dtable '.$del_class.'"> '.$rubric.'</td>'.
					'<td class="dtable '.$del_class.'"> '.$result['title'].'</td>' .
					'<td class="dtable '.$del_class.'"> '.$result['title'].'</td>' ;
					print $this->actions_panel($result); // Панель редактирования
			 print '</tr>';	
			 print '<tr class="akk_slide"><td colspan="1000">';
			 ?>
			 <div class="row">
				 <div class="col-md-3"><img src="<?=$result['ima!ge']?>" width="100%" /></div>
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
		<form method="GET" id="filtrform" data-controller="<?=$this->ctr?>" data-router="/sahmatka/ajax_router.php" data-ajaxurl="/sahmatka/ajax_router.php?ctr=<?=$this->ctr?>&act=ajax_data" style="width:100%;" >
		<?=$this->form_sfileds('searchform')?>
							
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
				<input type="text" id="search" name="search" class="filtr_input" value="" /><br/>
		</div> 
		<div class="filter-item"> 
			<input type="checkbox"  id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
		</div>
								
		</div>
		</form>
							<?
	}
	
	
	
	
	
	

	############### JS TREE ДЕРЕВО КАТЕГОРИЙ
	############################################################################

	// Отрисовка дерева jsoon
	function act__get_node_jsoon()
	{
		global $mysql;
		header('Content-Type: application/json; charset=utf-8');
		//echo json_encode( $data )

		//$result = $mysql->get_arr('SELECT * FROM dir order  by dir_id');
		$result = $mysql->get_arr('SELECT * FROM dir   WHERE del=0   order by dir_id',1, 'dir_id' );
		
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
		$result2 = array();
		// Формируем массив дочених [родитель][]=дочерний
		foreach( $result as $k=>$v )
		{
		 
			if( !$v['parent_dir_id'] ){$v['parent_dir_id'] = '#';}
			$item['id'] =  $v['dir_id'];
			$item['text'] = $v['dir_title'];
			$item['parent'] = $v['parent_dir_id'];
 			$result2[] = $item;
		}

		 // print_r($result2);
		 print json_encode( $result2 );     
	}
	 
	
	function act__delete_node_jsoon()
	{
		header('Content-Type: application/json; charset=utf-8');

		global $mysql;
		$id = $_GET['id'];
	
		$data = array();
		$data['del'] = 1;
		$mysql->update_for_key('dir','dir_id',$id,$data); //УДаляем ноду
		$mysql->update_for_key('dir','parent_dir_id',$id,$data);// Удаляем все дочерние ноды !!(без рекурсии) нужна рекурсия или работа с патч
		 
	}
	
	# Создание ветки
	function act__create_node_jsoon()
	{
		header('Content-Type: application/json; charset=utf-8');
		global $mysql;
		//&id=2&position=0&text=New%20node
		$id = $_GET['id'];
		$parent_arr = $mysql->get_for_key('dir','dir_id',$id);
		// print_r($parent_arr);
		 
		$data = array();
		$data['parent_dir_id'] = $parent_arr['dir_id'];
		$data['dir_level'] = $parent_arr['dir_level']+1;
		$data['dir_type'] = $parent_arr['dir_type'];
		$data['dir_title'] = 'Новая рубрика';
		$data['dir_name'] = $this->translit($data['dir_title']).'-'.$data['parent_dir_id']; // Нужно проверка уникальности имени ноды
		$data['dir_path'] = $parent_arr['dir_path'].'/'.$data['dir_name'];
		$r['id'] = (string) $mysql->insert('dir',$data);
		
		// {"id":"New node"}
		print json_encode( $r );  
		
	}

	# Переименование ветки
	function act__rename_node_jsoon()
	{
 		header('Content-Type: application/json; charset=utf-8');

		global $mysql;
		$id = $_GET['id'];
		
		$parent_arr = $mysql->get_for_key('dir','dir_id',$id);
		
		$data = array();
		$data['dir_title'] = $_GET['text'];
		$data['dir_name'] = $this->translit($data['dir_title']).'-'.$id; // Нужно проверка уникальности имени ноды
		$data['dir_path'] = $parent_arr['dir_path'].'/'.$data['dir_name'];
		
		$mysql->update_for_key('dir','dir_id',$id,$data);
	}
	
	function act__move_node_jsoon()
	{	
		header('Content-Type: application/json; charset=utf-8');
		print 1;
	}
	
	function act__copy_node_jsoon()
	{		
		header('Content-Type: application/json; charset=utf-8');
		print 1;
	} 
	 
	//Контент ноды при клике
	function act__get_content_node_jsoon()
	{		
		header('Content-Type: application/json; charset=utf-8');
		global $mysql;
		global $filed;
		
		$id = $_GET['id'];
		if($id)
		{
			$data = $mysql->get_for_key('dir','dir_id',$id);
		}
		ob_start();
		print '<h2> Рубрика #'.$id.'</h2>';
		print '<hr/></h2>';
		?>
		<form>
		<?
		$filed->hidden('id','id рубрики', $data['dir_id']);
		
		$filed->text('dir_title','Рубрика', $data['dir_title']);
		$filed->text('dir_name','name', $data['dir_name']);
		$filed->text('dir_path','path', $data['dir_path']);
		
		?>
		
 
		</form>
		<?
		$content['content'] = ob_get_clean();
		print json_encode($content);
	} 
	
	##########################################################################################
	
	
	
	
	
	
	
	function act__index()
	{
		global $r;
		global $mysql;
		?>
		<style>
		.dtable_del_class{text-decoration:line-through; color:#CCC;}
		</style>
		
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/themes/default/style.min.css" />
	 
		<section class="section-stat">
			<div class="container mobc"  >
				<div class="page-header">
					<div class="page-header__logo"><img src="/admin/adm_template/images/logo_green.png" alt="" /></div>
					<div class="page-header__title"><?=$this->title?></span></div>
				</div>
		 
				<div style="text-align:right; width:100%; padding:20px; padding-left:0; padding-right:0;">
				 
				</div>

					<div id="ajaxcontent" class="stat">
						<div class="stat-top" style="display:none;">
							<?=$this->searchform()?>
							<a href="JavaScript:window.print();" class="stat-top__print" ></a>
						</div>
						<div class="stat-table stat-table-user stat-table_notpd table">
						
				  
		<div class="row">
			<div class="col-md-7">
				<h2>Рубрики сайта</h2>
				<hr/><br/>



<div id="tree_menu" style="height:30px; overflow:auto;">
<input type="button" id="add_folder" value="add folder" style="display:block; float:left;">
<input type="button" id="add_default" value="add file" style="display:block; float:left;">
<input type="button" id="rename" value="rename" style="display:block; float:left;">
<input type="button" id="remove" value="remove" style="display:block; float:left;">
<input type="button" id="cut" value="cut" style="display:block; float:left;">
<input type="button" id="copy" value="copy" style="display:block; float:left;">
<input type="button" id="paste" value="paste" style="display:block; float:left;">
<input type="button" id="clear_search" value="clear" style="display:block; float:right;">
<input type="button" id="search" value="search" style="display:block; float:right;">
<input type="text" id="text" value="" style="display:block; float:right;">
</div>




				<div id="tree"></div>
			</div>
			
			<div class="col-md-5">
				<div id="data">
				<div class="content code" style="display:none;"><textarea id="code" readonly="readonly"></textarea></div>
				<div class="content folder" style="display:none;"></div>
				<div class="content image" style="display:none; position:relative;"><img src="" alt="" style="display:block;  max-height:90%; max-width:90%;" /></div>
				<div class="content default" style="text-align:left;">Выберите раздел.</div>
				
				</div>
			</div>
			
			
		</div>
		
		 
			
		<script src="/admin/adm_template/libs/jstree/jstree.min.js"></script>
		<script>
		$(function () {
			$(window).resize(function () {
				var h = Math.max($(window).height() - 0, 420);
				//$('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
			}).resize();


			$('#tree')
				.jstree({
					'core' : {
						'data' : {
							'url' : '/sahmatka/ajax_router.php?ctr=dir&act=get_node_jsoon',
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
					'plugins' : ['state','dnd','wholerow','search', "themes","json_data","ui","crrm","cookies","contextmenu","sort"],
					"search" : {
						// As this has been a common question - async search
						// Same as above - the `ajax` config option is actually jQuery's AJAX object
						"ajax" : {
							"url" : "/static/v.1.0pre/_demo/server.php",
							// You get the search string as a parameter
							"data" : function (str) {
								return { 
									"operation" : "search", 
									"search_str" : str 
								}; 
							}
						}
					}			
				
				})
				.on('delete_node.jstree', function (e, data) {
					$.get('/sahmatka/ajax_router.php?ctr=dir&act=delete_node_jsoon', { 'id' : data.node.id })
						.fail(function () {
							data.instance.refresh();
						});
				})
				.on('create_node.jstree', function (e, data) {
					$.get('/sahmatka/ajax_router.php?ctr=dir&act=create_node_jsoon', { 'id' : data.node.parent, 'position' : data.position, 'text' : data.node.text })
						.done(function (d) {
								 
							data.instance.set_id(data.node, d.id);
						
						})
						.fail(function () {
							data.instance.refresh();
							
						});
				})
				.on('rename_node.jstree', function (e, data) {
					$.get('/sahmatka/ajax_router.php?ctr=dir&act=rename_node_jsoon', { 'id' : data.node.id, 'text' : data.text })
						.fail(function () {
							data.instance.refresh();
						});
				})
				.on('move_node.jstree', function (e, data) {
					$.get('/sahmatka/ajax_router.php?ctr=dir&act=move_node_jsoon', { 'id' : data.node.id, 'parent' : data.parent, 'position' : data.position })
						.fail(function () {
							data.instance.refresh();
						});
				})
				.on('copy_node.jstree', function (e, data) {
					$.get('/sahmatka/ajax_router.php?ctr=dir&act=copy_node_jsoon', { 'id' : data.original.id, 'parent' : data.parent, 'position' : data.position })
						.always(function () {
							data.instance.refresh();
						});
				})
				.on('changed.jstree', function (e, data) {
					if(data && data.selected && data.selected.length) {
 
						$.get('/sahmatka/ajax_router.php?ctr=dir&act=get_content_node_jsoon', { 'id' : data.node.id})
						.done(function (d) {	 
							// data.instance.set_id(data.node, d.id);
							$('#data .default').html(d.content).show();
						})
						.fail(function () {
					
						});
							
					}
					else {
						$('#data .content').hide();
						$('#data .default').html('Выберите раздел.').show();
					}
				});
		});
		
		
		
		
		
		
		
	$(document).ready(function() {
		
		 // $("#tree").jstree("set_theme","apple");
		 
		 
			 $(function () { 
	$("#tree_menu input").click(function () {
	 
		switch(this.id) {
			case "add_default":
			case "add_folder":
				$("#tree").jstree("create", null, "last", { "attr" : { "rel" : this.id.toString().replace("add_", "") } });
				break;
			case "search":
				$("#tree").jstree("search", document.getElementById("text").value);
				break;
			case "text": break;
			default:
				$("#tree").jstree(this.id);
				break;
		}
	});
});
		});	
		
		// Code for the menu buttons




		</script>
		
		
							 
						</div>
					</div>			
					 

			</div>
		</section>
		 

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
		<table width="100%">
			<tr>
				<td width="30%" style="text-align:left;"><a href="<?=$this->backlink?>" class="forminformpanel_link">Назад</a> </td>
				<td width="40%" style="text-align:center;" class="forminform"><?=$this->forminform?> </td>
				<td width="30%" style="text-align:right;"><a href="#" onclick="document.getElementById('editform').submit(); return false;" class="forminformpanel_link" style="text-align:right;">Сохранить</a></td>
			</tr>
		</table>
		<br/>
		<hr/>
		<?
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

		if(!$_POST || 1==1) ############# ФОРМА
		{
		?>
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
		
		<form action="<?=$action?>" method="POST" id="editform">
		<br/><br/>
		<?=$this->formpanel();?>
		<div class="row">
		<div class="col-md-6">
		<?
			$filed->text('title','Название',$data['title']); print '<br/>';
			$sel_arr = $this->dir_date;
			print $filed->select('dir','Раздел',$sel_arr,$data['dir']);
			$filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 228px;" '); print '<br/>';
			$filed->text('order','Порядок',$data['order']); print '<br/>';
		?>
		</div>
		<div class="col-md-6">
		<?
			$filed->text('image','Фото (ссылка на изображение)',$data['image'],' id="image_filed" '); print '<br/>';
			?><div style="padding:3px; border:solid 1px #3C96E1; margin-left:3px; text-align: center;"><img id="image_filedsrc" src="" style="max-width:200px; max-height:200px;" /></div><?
			$filed->text('logo','Лого (ссылка на изображение)',$data['logo'],' id="logo_filed" '); print '<br/>';
			?><div style="padding:3px; border:solid 1px #3C96E1; margin-left:3px; text-align: center;"><img id="logo_filedsrc"  src="" style="max-width:200px; max-height:200px;" /></div><?
		
		?>
		</div>
		</div>
			<br/>
			<br/>
			<?
			$filed->textarea_html('content','Текст',$data['content'],' rows="20" '); print '<br/>';
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

		
		<div class="fw_fileupload">
			<input type="file" class="fw_file" name="userfile[]"  >
			<label class="file" for="file">Прикрепить файл</label>					
			<input class="fw_fileupload_tdir" name="tdir" value="<?=rand(0,10000);?><?=rand(0,10000);?><?=rand(0,10000);?>" type="!hidden" />
			<input class="fw_fileupload_many" name="many" value="1" type="!hidden" />
			<input class="fw_fileupload_filename" name="filename" value="233.jpg" type="!hidden" />
			 
			 
			<span  class="fw_fileupload_files"></span>
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
		</div>



		
		
<script>
$(".fw_file").change(function(){
	if (window.FormData === undefined)
	{
		alert('В вашем браузере FormData не поддерживается')
	} 
	else 
	{
		// Находим родительский DIV .fw_fileupload
		fw_fileupload = $( this ).parent('.fw_fileupload');
		//alert( $(fw_fileupload).attr('val') );
		var formData = new FormData();
		
		dir = $('.fw_fileupload_tdir',fw_fileupload).val();
		formData.append('tdir', dir );
		
		many = $('.fw_fileupload_many',fw_fileupload).val();
		formData.append('many', many );
		
		filename = $('.fw_fileupload_filename',fw_fileupload).val();
		formData.append('filename', filename );
		
		$.each($(".fw_file",fw_fileupload)[0].files,function(key, input){
			formData.append('file[]', input);
		});
		 
		$.ajax({
			type: "POST",
			url: '/admin/upload.php',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			dataType : 'json',
			success: function(data){
				data.forEach(function(msg) {
					$('.fw_fileupload_result',fw_fileupload).append(msg);
				});
			}
		});
		 
	}
});
</script> 




		<script>
		
	

		// Отображение картинок
		$( "#logo_filed" ).change(function() {
			$('#logo_filedsrc').attr('src',$( "#logo_filed" ).val() );
		});
		
		$( "#image_filed" ).change(function() {
			$('#image_filedsrc').attr('src',$( "#image_filed" ).val() );
		});
		
		
		$('#logo_filedsrc').attr('src',$( "#logo_filed" ).val() );
		$('#image_filedsrc').attr('src',$( "#image_filed" ).val() );

		
		$(document).ready(function(){
			// ЗАменяем картинки в случае неудачной загрузки
			$('#image_filedsrc').on('error', function(){ //срабатывает, если картинка загружена
				$('#image_filedsrc').attr('src','/noimg.png');
			});
			$('#logo_filedsrc').on('error', function(){ //срабатывает, если картинка загружена
				$('#logo_filedsrc').attr('src','/noimg.png');
			});
		});
		
		
		</script>
		
		<?
		}
	}  
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	### ОБРАБОТКА ФОРМЫ  
	
	function post__edit()
	{
			global $mysql;
			global $guest;
			global $filed;
			
			
			print '<pre>';
			print_r($_POST);
			print '</pre>';
			
			$data = array();
			$data['title'] = $_POST['title'];
			$data['descr'] = $_POST['descr'];
			$data['image'] = $_POST['image'];
			$data['logo'] = $_POST['logo'];
			
			$data['dir'] = $_POST['dir'];
			$data['order'] = $_POST['order'];
			
			$data['content'] = $_POST['content'];

			//ТИП ПОСТА
			$data['type'] = $this->post_type;
			$data['price'] = $_POST['price'];
							
			$id=$_GET['id'];
			if($id) // Редактирваоние существующей записи
			{
				$this->forminform='Изменения сохранены!';
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
					$this->forminform= 'Добавлена запись '.$newid;
					 
				}
				else
				{
					$this->forminform='Ошибка, попробуйте позже';
				}
			}
			
	}
	
	
	
	
	

	
	
	
	

	
	
	 
	
	
	 
	
	
}