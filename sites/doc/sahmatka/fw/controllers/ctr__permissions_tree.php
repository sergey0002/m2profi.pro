<?
class ctr__permissions_tree extends ctr__
{
    var $table = 'users_group_permissions'; 
    var $ctr = 'permissions_tree';
    var $title = 'Права доступа (Дерево)';

    function __construct()
    {
        // Only admin access
        if ($_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'goodzem') {
            die('Доступ запрещен');
        }
    }

    function act__index()
    {
        global $mysql;
        
        ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/themes/default/style.min.css" />
        <style>
            .permissions-tree-container {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .tree-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #e0e0e0;
            }
            .tree-actions {
                margin-top: 20px;
                padding-top: 15px;
                border-top: 2px solid #e0e0e0;
            }
            /* Custom colors for tree nodes */
            .jstree-default .jstree-node-group > .jstree-icon {
                background-color: #3498db !important;
                border-radius: 3px;
            }
            .jstree-default .jstree-node-controller > .jstree-icon {
                background-color: #2ecc71 !important;
                border-radius: 3px;
            }
            .jstree-default .jstree-node-action > .jstree-icon {
                background-color: #f39c12 !important;
                border-radius: 3px;
            }
            /* Inherited permissions styling */
            .jstree-default .inherited > .jstree-checkbox {
                opacity: 0.5;
                cursor: not-allowed;
            }
            .jstree-default .inherited > a {
                color: #999 !important;
                font-style: italic;
            }
            .jstree-default .inherited.jstree-clicked > .jstree-checkbox {
                background-color: #b3d9ff !important;
            }
            /* Unique (directly assigned) permissions styling */
            .jstree-default .jstree-node-action:not(.inherited).jstree-clicked > .jstree-checkbox {
                background-color: #28a745 !important;
                border-color: #28a745 !important;
            }
            #permissions-tree {
                margin-top: 20px;
                max-height: 600px;
                overflow-y: auto;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 4px;
            }
            .legend {
                display: flex;
                gap: 20px;
                margin-bottom: 15px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 4px;
                flex-wrap: wrap;
            }
            .legend-item {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .legend-color {
                width: 20px;
                height: 20px;
                border-radius: 3px;
            }
            .legend-checkbox {
                width: 16px;
                height: 16px;
                border: 2px solid #ccc;
                border-radius: 3px;
            }
        </style>

        <div class="permissions-tree-container">
            <div class="tree-header">
                <h2>Управление правами доступа</h2>
                <p class="text-muted">
                    Используйте дерево для управления правами групп пользователей. 
                    <strong>Дочерние группы автоматически наследуют права от родительских групп.</strong>
                    Вы можете переопределить унаследованные права, задав их явно для дочерней группы.
                </p>
                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #3498db;"></div>
                        <span>Группы пользователей</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #2ecc71;"></div>
                        <span>Контроллеры</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #f39c12;"></div>
                        <span>Действия</span>
                    </div>
                    <div class="legend-item" style="border-left: 2px solid #ddd; padding-left: 20px; margin-left: 10px;">
                        <div class="legend-checkbox" style="background: #28a745; border-color: #28a745;"></div>
                        <span><strong>Уникальное право</strong> (задано явно)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-checkbox" style="background: #b3d9ff; border-color: #b3d9ff; opacity: 0.5;"></div>
                        <span style="color: #999; font-style: italic;"><strong>Унаследованное</strong> (от родительской группы)</span>
                    </div>
                </div>
            </div>

            <div id="permissions-tree"></div>

            <div class="tree-actions">
                <button type="button" class="btn btn-success btn-lg" id="save-permissions">
                    <i class="fa fa-save"></i> Сохранить изменения
                </button>
                <button type="button" class="btn btn-secondary" id="expand-all">
                    <i class="fa fa-plus-square"></i> Развернуть все
                </button>
                <button type="button" class="btn btn-secondary" id="collapse-all">
                    <i class="fa fa-minus-square"></i> Свернуть все
                </button>
                <a href="?ctr=permissions&act=index" class="btn btn-outline-secondary">
                    <i class="fa fa-list"></i> Старый интерфейс
                </a>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/jstree.min.js"></script>
        <script>
        $(document).ready(function() {
            // Load tree data
            $.ajax({
                url: 'ajax_router.php?ctr=permissions_tree&act=get_tree_data',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    initTree(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading tree data:', error);
                    alert('Ошибка загрузки данных дерева');
                }
            });

            function initTree(treeData) {
                $('#permissions-tree').jstree({
                    'core': {
                        'data': treeData,
                        'themes': {
                            'name': 'default',
                            'responsive': true,
                            'dots': true,
                            'icons': true
                        }
                    },
                    'checkbox': {
                        'keep_selected_style': false,
                        'three_state': true,
                        'cascade': 'down+undetermined'
                    },
                    'plugins': ['checkbox', 'search']
                });
            }

            // Expand all
            $('#expand-all').click(function() {
                $('#permissions-tree').jstree('open_all');
            });

            // Collapse all
            $('#collapse-all').click(function() {
                $('#permissions-tree').jstree('close_all');
            });

            // Save permissions
            $('#save-permissions').click(function() {
                var tree = $('#permissions-tree').jstree(true);
                var selected = tree.get_checked();
                
                // Show loading indicator
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Сохранение...');
                
                $.ajax({
                    url: 'ajax_router.php?ctr=permissions_tree&act=save',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ selected_nodes: selected }),
                    dataType: 'json',
                    success: function(response) {
                        alert('Права успешно сохранены!');
                        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Сохранить изменения');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error saving permissions:', error);
                        alert('Ошибка при сохранении прав');
                        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Сохранить изменения');
                    }
                });
            });
        });
        </script>
        <?
    }

    function act__get_tree_data()
    {
        global $mysql;
        
        $tree_data = [];
        
        // 1. Get all groups with hierarchy
        $groups = $mysql->get_arr("SELECT * FROM users_group WHERE del=0 ORDER BY users_group_id");
        
        // Build group hierarchy map
        $group_map = [];
        $group_children = [];
        foreach ($groups as $group) {
            $group_map[$group['users_group_id']] = $group;
            $parent_id = $group['parent_users_group_id'] ?: 0;
            if (!isset($group_children[$parent_id])) {
                $group_children[$parent_id] = [];
            }
            $group_children[$parent_id][] = $group['users_group_id'];
        }
        
        // 2. Scan controllers
        $controllers = [];
        $files = glob(__DIR__ . '/ctr__*.php');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $ctr_name = str_replace('.php', '', basename($file));
            
            preg_match_all('/function\s+act__([a-zA-Z0-9_]+)/', $content, $matches);
            if (!empty($matches[1])) {
                $controllers[$ctr_name] = $matches[1];
            }
        }
        
        // 3. Get existing permissions for ALL groups
        $permissions = [];
        $rows = $mysql->get_arr("SELECT users_group_id, rule, access FROM users_group_rules WHERE access=1");
        foreach ($rows as $row) {
            if (!isset($permissions[$row['users_group_id']])) {
                $permissions[$row['users_group_id']] = [];
            }
            $permissions[$row['users_group_id']][] = $row['rule'];
        }
        
        // Helper function to get inherited permissions
        $get_inherited_permissions = function($group_id) use (&$permissions, &$group_map, &$get_inherited_permissions) {
            $inherited = [];
            $current_id = $group_id;
            $checked = [];
            
            while ($current_id && !in_array($current_id, $checked)) {
                $checked[] = $current_id;
                if (isset($permissions[$current_id])) {
                    $inherited = array_merge($inherited, $permissions[$current_id]);
                }
                $current_id = isset($group_map[$current_id]) ? ($group_map[$current_id]['parent_users_group_id'] ?: null) : null;
                if ($current_id == 0) $current_id = null;
            }
            
            return array_unique($inherited);
        };
        
        // 4. Build tree structure recursively
        $build_group_tree = function($parent_id = 0) use (&$group_children, &$group_map, &$controllers, &$permissions, &$get_inherited_permissions, &$build_group_tree) {
            $nodes = [];
            
            if (!isset($group_children[$parent_id])) {
                return $nodes;
            }
            
            foreach ($group_children[$parent_id] as $group_id) {
                $group = $group_map[$group_id];
                $group_permissions = isset($permissions[$group_id]) ? $permissions[$group_id] : [];
                $inherited_permissions = $get_inherited_permissions($group_id);
                
                $group_node = [
                    'id' => 'group_' . $group_id,
                    'text' => $group['caption'] . ' (' . $group['group_name'] . ')',
                    'icon' => 'fa fa-users',
                    'li_attr' => ['class' => 'jstree-node-group'],
                    'state' => ['opened' => $parent_id == 0],
                    'children' => []
                ];
                
                // Add controllers as children
                foreach ($controllers as $ctr_name => $actions) {
                    $ctr_clean = str_replace('ctr__', '', $ctr_name);
                    
                    $controller_node = [
                        'id' => 'group_' . $group_id . '_ctr_' . $ctr_clean,
                        'text' => $ctr_name,
                        'icon' => 'fa fa-folder',
                        'li_attr' => ['class' => 'jstree-node-controller'],
                        'state' => ['opened' => false],
                        'children' => []
                    ];
                    
                    // Add actions as children
                    foreach ($actions as $action) {
                        $rule = $ctr_clean . '/' . $action;
                        $is_directly_allowed = in_array($rule, $group_permissions);
                        $is_inherited = !$is_directly_allowed && in_array($rule, $inherited_permissions);
                        
                        $text = $action;
                        if ($is_inherited) {
                            $text .= ' <span style="color: #999; font-style: italic;">(унаследовано)</span>';
                        }
                        
                        $action_node = [
                            'id' => 'group_' . $group_id . '_rule_' . $rule,
                            'text' => $text,
                            'icon' => 'fa fa-file-code-o',
                            'li_attr' => ['class' => 'jstree-node-action' . ($is_inherited ? ' inherited' : '')],
                            'state' => [
                                'selected' => $is_directly_allowed,
                                'disabled' => $is_inherited
                            ],
                            'data' => [
                                'group_id' => $group_id,
                                'rule' => $rule,
                                'inherited' => $is_inherited
                            ]
                        ];
                        
                        $controller_node['children'][] = $action_node;
                    }
                    
                    if (!empty($controller_node['children'])) {
                        $group_node['children'][] = $controller_node;
                    }
                }
                
                // Add child groups recursively
                $child_groups = $build_group_tree($group_id);
                if (!empty($child_groups)) {
                    $group_node['children'] = array_merge($group_node['children'], $child_groups);
                }
                
                $nodes[] = $group_node;
            }
            
            return $nodes;
        };
        
        $tree_data = $build_group_tree(0);
        
        header('Content-Type: application/json');
        echo json_encode($tree_data);
    }

    function act__save()
    {
        global $mysql, $connection;
        
        // Read JSON input from request body
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);
        
        if (!isset($data['selected_nodes']) || !is_array($data['selected_nodes'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }
        
        $selected_nodes = $data['selected_nodes'];
        
        // Parse selected nodes and group by group_id
        $permissions_by_group = [];
        
        foreach ($selected_nodes as $node_id) {
            // Format: group_{group_id}_rule_{rule}
            if (preg_match('/^group_(\d+)_rule_(.+)$/', $node_id, $matches)) {
                $group_id = (int)$matches[1];
                $rule = $matches[2];
                
                if (!isset($permissions_by_group[$group_id])) {
                    $permissions_by_group[$group_id] = [];
                }
                $permissions_by_group[$group_id][] = $rule;
            }
        }
        
        // Clear all existing permissions
        $connection->query("DELETE FROM users_group_rules");
        
        // Insert new permissions
        foreach ($permissions_by_group as $group_id => $rules) {
            foreach ($rules as $rule) {
                // Parse rule to extract ctr and act
                $ctr_clean = null;
                $act_clean = null;
                
                if (strpos($rule, '/') !== false) {
                    list($ctr_clean, $act_clean) = explode('/', $rule, 2);
                }
                
                $ctr_sql = $ctr_clean ? "'" . $connection->real_escape_string($ctr_clean) . "'" : 'NULL';
                $act_sql = $act_clean ? "'" . $connection->real_escape_string($act_clean) . "'" : 'NULL';
                $rule_escaped = $connection->real_escape_string($rule);
                
                $connection->query("INSERT INTO users_group_rules (users_group_id, ctr, act, rule, access) VALUES ($group_id, $ctr_sql, $act_sql, '$rule_escaped', 1)");
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Права успешно сохранены']);
    }
}
?>
