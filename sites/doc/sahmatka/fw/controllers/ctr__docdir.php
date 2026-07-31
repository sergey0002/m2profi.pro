<?php

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
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 

/*
 * ============================================================================
 * Контроллер для управления документами с помощью jsTree
 * Версия: 5.0 (Финальная доработка по макету и исправлению кликов)
 * Полная версия, без сокращений.
 * ============================================================================
 */

class ctr__docdir extends ctr__
{
    /**
     * Основной метод для отображения страницы
     */
    public function act__index()
    {
        global $t;
        $t['h1'] = 'Документы';
        $this->show_tree_view();
    }

    /**
     * Генерирует HTML, CSS и JavaScript для дерева
     */
    public function show_tree_view()
    {
        ?>
        <!-- 1. Подключение библиотеки и стилей -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
        <style>
            /* --- Стилизация, точно повторяющая ваш макет --- */
            
            /* Общий контейнер */
            #fileTreeContainer { border: none; }
            .jstree-default .jstree-container-ul { padding: 0; margin: 0; }
            .jstree-default .jstree-node { margin-left: 20px; background: none !important; }

            /* Убираем стандартный фон jsTree, чтобы управлять им вручную */
            .jstree-wholerow { display: none !important; } 

            /* Базовые стили для всех строк */
            .jstree-anchor {
                font-size: 14px;
                width: 100%;
                display: flex;
                align-items: center; /* Вертикальное выравнивание */
                justify-content: space-between;
                height: 40px;
                padding: 0 10px;
                box-sizing: border-box;
            }

            /* --- СТИЛИ ДЛЯ ПАПОК (темные плашки) --- */
            .jstree-node:not(.jstree-leaf) > .jstree-anchor {
                background: #3d535f;
                color: #FFF;
                font-size: 16px;
                border-radius: 4px;
                margin: 4px 0;
            }
            .jstree-node:not(.jstree-leaf) > .jstree-clicked {
                background: #2d434f;
            }

            /* --- СТИЛИ ДЛЯ ФАЙЛОВ (светлые строки) --- */
            .jstree-node.jstree-leaf > .jstree-anchor {
                color: #3d535f;
                font-weight: bold;
                border-bottom: 1px solid #e0e0e0;
            }
            .jstree-node.jstree-leaf > .jstree-clicked {
                background-color: #e7f4f9 !important;
                border-radius: 4px;
            }
            
            /* --- ВЕРТИКАЛЬНЫЕ ЛИНИИ и СТРЕЛКИ --- */
            .jstree-default .jstree-line { border-color: #add8e6; border-width: 1px; }
            .jstree-ocl {
                width: 24px;
                height: 24px;
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
            }
            .jstree-node { position: relative; } /* Нужно для позиционирования стрелки */
            .jstree-anchor { padding-left: 25px; } /* Делаем отступ для стрелки */
            
            /* --- ИКОНКИ --- */
            .jstree-themeicon { display: none !important; } /* Скрываем стандартную иконку папки */
            .jstree-node.jstree-leaf > .jstree-anchor .jstree-themeicon {
                display: inline-block !important; /* Показываем иконку только для файлов */
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%233d535f"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>') no-repeat center center !important;
                width: 18px; height: 18px;
            }

            /* --- КАСТОМНЫЕ ЭЛЕМЕНТЫ ВНУТРИ ССЫЛКИ --- */
            .node-text { flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .file-meta { color: #888; font-size: 12px; margin-left: auto; padding-left: 15px; white-space: nowrap; }
            .add-file-btn { color: #FFF; background-color: #5a7381; border-radius: 4px; padding: 0px 8px; font-size: 20px; text-decoration: none; margin-left: 15px; }
            .add-file-btn:hover { background-color: #fff; color: #3d535f; }
            .jstree-node.jstree-leaf .add-file-btn { display: none; } /* Не показывать "+" у файлов */
            
            #search-input { width: 100%; box-sizing: border-box; padding: 12px; font-size: 16px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
			
			
			

        </style>

        <!-- 2. HTML-структура -->
        <input type="text" id="search-input" placeholder="Поиск по файлам и папкам...">
        <div id="fileTreeContainer"></div>

        <!-- 3. Подключение скриптов -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

        <!-- 4. JavaScript для инициализации -->
        <script>
        $(function () {
            $('#fileTreeContainer').jstree({
                'core' : {
                    'data' : { 'url' : 'ajax_router.php?ctr=docdir&act=get_tree_data', 'dataType' : 'json' },
                    'check_callback' : true,
                    'themes': { 'stripes' : false, 'dots': false, 'icons': false }
                },
                'plugins' : [ "dnd", "search", "contextmenu", "state", "types" ],
                'types': {
                    "folder": { "icon": false },
                    "file": { "icon": false, "valid_children": [] }
                },
                'state': { 'key': 'doc_tree_state_v3' },
                'search': { 'show_only_matches': true, 'show_only_matches_children': true },
                'contextmenu': {
                    'items': function(node) {
                        var items = {};
                        if (node.type === 'folder') {
                            items.create = {
                                "label": "Создать дочернюю папку",
                                "action": function (data) {
                                    var inst = $.jstree.reference(data.reference);
                                    var p_node = inst.get_node(data.reference);
                                    var name = prompt("Введите название:", "Новая папка");
                                    if (name) {
                                        $.post('ajax_router.php?ctr=docdir&act=create_dir', { parent_id: p_node.id, dir_title: name })
                                        .done(d => { if(d.id) inst.create_node(p_node, { id: d.id, text: d.text, type: 'folder' }); else alert(d.message); })
                                        .fail(() => alert("Ошибка сервера."));
                                    }
                                }
                            };
                        }
                        items.remove = {
                            "label": "Удалить",
                            "action": function (data) {
                                var inst = $.jstree.reference(data.reference);
                                var node_to_del = inst.get_node(data.reference);
                                var nodeText = $('<div>').html(node_to_del.text).find('.node-text').text().trim() || node_to_del.text;
                                if (confirm("Вы уверены, что хотите удалить '" + nodeText + "'?")) {
                                    $.post('ajax_router.php?ctr=docdir&act=delete_node', { node_id: node_to_del.id, node_type: node_to_del.type })
                                    .done(d => { if(d.status === 'success') inst.delete_node(node_to_del); else alert(d.message); })
                                    .fail(() => alert("Ошибка сервера."));
                                }
                            }
                        };
                        return items;
                    }
                }
            })
            .on('move_node.jstree', function (e, data) {
                var p_node = $('#fileTreeContainer').jstree(true).get_node(data.parent);
                $.post('ajax_router.php?ctr=docdir&act=move_node', { 
                    'node_id': data.node.id, 'node_type': data.node.type,
                    'new_parent_id': data.parent, 'children_order': p_node.children 
                }).fail(() => { alert('Ошибка сервера.'); location.reload(); });
            });

            // --- РЕШЕНИЕ ПРОБЛЕМЫ С КЛИКАМИ ---
            $('#fileTreeContainer').on('click', '.jstree-anchor', function(e) {
                var target = $(e.target);
                if (target.hasClass('add-file-btn')) {
                    e.stopPropagation();
                    // Браузер сам перейдет по ссылке
                }
            });

            // Поиск
            var to = false;
            $('#search-input').keyup(function () {
                if(to) { clearTimeout(to); }
                to = setTimeout(() => { $('#fileTreeContainer').jstree(true).search($('#search-input').val()); }, 250);
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX: Формирует и отдает JSON-данные для дерева
     */
    public function act__get_tree_data() {
        global $mysql;
        header('Content-Type: application/json');

        $dirs = $mysql->get_arr('SELECT * FROM dir WHERE del = 0 ORDER BY `order`, dir_title');
        $files = $mysql->get_arr('SELECT * FROM files2node WHERE node_type="doc_dir" AND del = 0 ORDER BY `order`, name');
        
        $nodes = [];

        foreach ($dirs as $dir) {
            $add_btn = '<a class="iframe_r add-file-btn" href="iframe_router.php?ctr=doc&act=edit&dir_id=' . $dir['dir_id'] . '">+</a>';
            $nodes[] = [
                'id'        => 'dir_' . $dir['dir_id'],
                'parent'    => $dir['parent_dir_id'] == 0 ? '#' : 'dir_' . $dir['parent_dir_id'],
                'text'      => '<span class="node-text">' . htmlspecialchars($dir['dir_title']) . '</span>' . $add_btn,
                'type'      => 'folder'
            ];
        }

        foreach ($files as $file) {
            $caption = $file['caption'] ?: $file['name'];
            $meta = '';
            if($file['docdate'] && $file['docdate'] != '0000-00-00') $meta .= 'Документ от: ' . date('d.m.Y', strtotime($file['docdate']));
            if($file['uptime']) $meta .= ($meta ? ' / ' : '') . 'Обновлен: ' . date('d.m.Y H:i:s', $file['uptime']);
            
            $nodes[] = [
                'id'        => 'file_' . $file['files2node_id'],
                'parent'    => 'dir_' . $file['node_id'],
                'text'      => '<span class="node-text">' . htmlspecialchars($caption) . '</span><span class="file-meta">' . $meta . '</span>',
                'type'      => 'file',
                'a_attr'    => [ 'href' => 'iframe_router.php?ctr=doc&act=card&id=' . $file['files2node_id'], 'class' => 'iframe_r' ]
            ];
        }

        echo json_encode($nodes);
        exit;
    }
    
    /**
     * AJAX: Создание новой директории
     */
    public function act__create_dir() {
        global $mysql;
        header('Content-Type: application/json');

        if ($_SESSION['users_group_id'] != "3" && $_SESSION['users_group_id'] != "1") {
            echo json_encode(['status' => 'error', 'message' => 'Недостаточно прав.']);
            exit;
        }

        $parentId = isset($_POST['parent_id']) ? (int)str_replace('dir_', '', $_POST['parent_id']) : 0;
        $dirTitle = isset($_POST['dir_title']) ? trim($_POST['dir_title']) : '';

        if (empty($dirTitle)) {
            echo json_encode(['status' => 'error', 'message' => 'Название папки не может быть пустым.']);
            exit;
        }

        $parentDir = $mysql->get_arr('SELECT * FROM dir WHERE dir_id=' . $parentId, 1);
        $parentLevel = $parentDir ? $parentDir['dir_level'] : -1;
        $parentPath = $parentDir ? $parentDir['dir_path'] : '';
        $newDirName = $this->transliterate($dirTitle) . '-' . time();
        
        $data = [
            'dir_type' => 0,
            'dir_title' => $dirTitle,
            'parent_dir_id' => $parentId,
            'dir_name' => $newDirName,
            'dir_level' => $parentLevel + 1,
            'dir_path' => ($parentPath ? $parentPath . '/' : '') . $newDirName,
            'order' => 999,
            'del' => 0
        ];

        $newId = $mysql->insert('dir', $data);

        if ($newId) {
            $add_btn = '<a class="iframe_r add-file-btn" href="iframe_router.php?ctr=doc&act=edit&dir_id=' . $newId . '">+</a>';
            echo json_encode([
                'status' => 'success', 
                'id' => 'dir_' . $newId,
                'text' => '<span class="node-text">' . htmlspecialchars($dirTitle) . '</span>' . $add_btn
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка БД.']);
        }
        exit;
    }
    
    /**
     * AJAX: Мягкое удаление узла (файла или папки рекурсивно)
     */
    public function act__delete_node() {
        global $mysql;
        header('Content-Type: application/json');

        if ($_SESSION['users_group_id'] != "3" && $_SESSION['users_group_id'] != "1") {
            echo json_encode(['status' => 'error', 'message' => 'Недостаточно прав.']); exit;
        }

        $nodeIdStr = $_POST['node_id'];
        $nodeType = $_POST['node_type']; // 'file' or 'folder'
        $nodeId = (int)str_replace(['file_', 'dir_'], '', $nodeIdStr);

        if ($nodeType === 'file') {
            $mysql->update_for_key('files2node', 'files2node_id', $nodeId, ['del' => 1]);
        } elseif ($nodeType === 'folder') {
            $this->delete_folder_recursive($nodeId);
        }

        echo json_encode(['status' => 'success']);
        exit;
    }

    /**
     * AJAX: Обработка перемещения/сортировки узлов
     */
    public function act__move_node() {
        global $mysql;
        header('Content-Type: application/json');
        
        if ($_SESSION['users_group_id'] != "3" && $_SESSION['users_group_id'] != "1") {
            echo json_encode(['status' => 'error', 'message' => 'Недостаточно прав.']); exit;
        }

        $nodeIdStr = $_POST['node_id'];
        $nodeType = $_POST['node_type']; // 'file' или 'folder'
        $newParentIdStr = $_POST['new_parent_id'];
        $childrenOrder = $_POST['children_order'];
        
        $nodeId = (int)str_replace(['file_', 'dir_'], '', $nodeIdStr);
        $newParentId = ($newParentIdStr === '#') ? 0 : (int)str_replace('dir_', '', $newParentIdStr);

        // Обновляем сортировку
        foreach ($childrenOrder as $order => $childIdStr) {
            $childId = (int)str_replace(['file_', 'dir_'], '', $childIdStr);
            if (strpos($childIdStr, 'file_') === 0) {
                $mysql->update_for_key('files2node', 'files2node_id', $childId, ['order' => $order]);
            } else {
                $mysql->update_for_key('dir', 'dir_id', $childId, ['order' => $order]);
            }
        }
        
        // Обновляем родителя
        if ($nodeType == 'file') {
            $mysql->update_for_key('files2node', 'files2node_id', $nodeId, ['node_id' => $newParentId]);
        } elseif ($nodeType == 'folder') {
            $this->update_folder_parent_and_paths($nodeId, $newParentId);
        }

        echo json_encode(['status' => 'success']);
        exit;
    }

    // --- ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ---
    
    /**
     * Рекурсивно "мягко" удаляет папку и все ее содержимое
     */
    private function delete_folder_recursive($folderId) {
        global $mysql;
        $mysql->update_for_key('dir', 'dir_id', $folderId, ['del' => 1]);
        $mysql->update('files2node', ['node_id' => $folderId], ['del' => 1]);
        $children = $mysql->get_arr('SELECT dir_id FROM dir WHERE parent_dir_id = ' . (int)$folderId);
        if ($children) {
            foreach ($children as $child) {
                $this->delete_folder_recursive($child['dir_id']);
            }
        }
    }
    
    /**
     * Рекурсивно обновляет путь и уровень вложенности для папки и всех ее потомков
     */
    private function update_folder_parent_and_paths($folderId, $newParentId) {
        global $mysql;
        $parentDir = $mysql->get_arr('SELECT * FROM dir WHERE dir_id=' . $newParentId, 1);
        $parentLevel = $parentDir ? $parentDir['dir_level'] : -1;
        $parentPath = $parentDir ? $parentDir['dir_path'] : '';
        $folderToMove = $mysql->get_arr('SELECT * FROM dir WHERE dir_id=' . $folderId, 1);
        if (!$folderToMove) return;

        $newLevel = $parentLevel + 1;
        $newPath = ($parentPath ? $parentPath . '/' : '') . $folderToMove['dir_name'];
        
        $mysql->update_for_key('dir', 'dir_id', $folderId, [
            'parent_dir_id' => $newParentId, 'dir_level' => $newLevel, 'dir_path' => $newPath
        ]);
        
        $children = $mysql->get_arr('SELECT dir_id FROM dir WHERE parent_dir_id = ' . $folderId);
        if ($children) {
            foreach ($children as $child) {
                $this->update_folder_parent_and_paths($child['dir_id'], $folderId);
            }
        }
    }
    
    /**
     * Простая транслитерация для создания системных имен
     */
    private function transliterate($text) {
        $cyr = [' ', 'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'];
        $lat = ['-','a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','shch','','y','','e','yu','ya','A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','Ts','Ch','Sh','Shch','','Y','','E','Yu','Ya'];
        return strtolower(preg_replace('/[^A-Za-z0-9-]/', '', str_replace($cyr, $lat, $text)));
    }
}