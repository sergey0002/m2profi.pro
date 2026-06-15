<?php
/**
 * Контроллер: Аналитика броней
 * Отчёт с группировкой: Агентство → Пользователь → Статус → Квартира → История
 */
class ctr__op_broni_actual extends ctr__homes__
{
    var $ctr = 'op_broni_actual';
    var $title = 'Аналитика броней';
    protected $include_hidden = true;
    private $broni_days = 14;

    function act__index()
    {
        global $t;
        $t['h1'] = 'Аналитика броней';
        ?>
        <style>
            .tree-container { margin-top: 20px; }
            .tree-node { margin: 2px 0; }
            .collapsed { display: none !important; }
            
            /* Агентство */
            .node-agency {
                background: #00CDAD;
                color: white;
                padding: 12px 15px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                margin-top: 10px;
            }
            .node-agency:hover { opacity: 0.95; }
            .node-agency .expand-icon { margin-right: 10px; font-weight: bold; }
            
            /* Пользователь */
            .node-user {
                background: #e8f4fd;
                padding: 10px 15px 10px 30px;
                cursor: pointer;
                border-left: 3px solid #00CDAD;
                margin-left: 15px;
            }
            .node-user:hover { background: #dceefb; }
            .node-user .expand-icon { margin-right: 8px; color: #00CDAD; font-weight: bold; }
            
            /* Статус */
            .node-status {
                padding: 8px 15px 8px 45px;
                cursor: pointer;
                border-left: 3px solid #ccc;
                margin-left: 30px;
                font-weight: bold;
            }
            .node-status:hover { background: #f5f5f5; }
            .node-status .expand-icon { margin-right: 8px; }
            
            .status-broni { background: #fff9e6; border-left-color: #ffc107; }
            .status-broni .status-label { color: #856404; }
            .status-sold { background: #e8f5e9; border-left-color: #28a745; }
            .status-sold .status-label { color: #155724; }
            .status-work { background: #fff3e0; border-left-color: #fd7e14; }
            .status-work .status-label { color: #7c3a00; }
            
            /* Квартиры */
            .node-apartments {
                margin-left: 45px;
                overflow-x: auto;
            }
            .apartments-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }
            .apartments-table th, .apartments-table td {
                padding: 8px 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .apartments-table th { background: #f5f5f5; font-weight: bold; }
            .apartments-table tr.apartment-row { cursor: pointer; }
            .apartments-table tr.apartment-row:hover { background: #e8f4fd; }
            
            /* История */
            .history-row { background: #fafafa; }
            .history-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
                background: #fff;
            }
            .history-table th, .history-table td {
                padding: 6px 10px;
                border: 1px solid #e0e0e0;
                text-align: left;
            }
            .history-table th { background: #f0f0f0; }
            
            .apartment-link { color: #0066cc; text-decoration: none; }
            .apartment-link:hover { text-decoration: underline; }
            .price-diff { color: #dc3545; font-weight: bold; font-size: 12px; }
            .days-warning { color: #dc3545; font-weight: bold; }
            .days-normal { color: #28a745; }
            .result-actual { color: #28a745; font-weight: bold; }
            .result-sold { color: #155724; font-weight: bold; }
            .result-expired { color: #dc3545; font-weight: bold; }
            .comment-cell { font-size: 12px; color: #666; max-width: 150px; }
            .other-user { color: #999; font-style: italic; }
            
            /* Фильтры */
            .filter-row { display: flex; align-items: center; gap: 15px; margin: 15px 0; flex-wrap: wrap; }
            .filter-input { padding: 8px 12px; font-size: 14px; border: 2px solid #00CDAD; border-radius: 5px; }
            .filter-label { font-weight: bold; font-size: 14px; }
            .filter-btn { padding: 8px 20px; background: #00CDAD; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
            .filter-btn:hover { background: #00b89c; }
            
            /* Сводка */
            .summary-block {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 20px;
                display: flex;
                justify-content: space-around;
                flex-wrap: wrap;
            }
            .summary-item { text-align: center; margin: 10px; }
            .summary-value { font-size: 28px; font-weight: bold; }
            .summary-label { font-size: 12px; opacity: 0.9; }
        </style>

        <div id="ajaxcontent" class="stat">
            <div class="stat-top"><div class="stat-top-filter"></div>
                <a href="JavaScript:window.print();" class="stat-top__print"></a>
            </div>
            <?php
            parent::_render_filters();
            $this->_render_filters();
            echo $this->_generate_report();
            ?>
        </div>
        
        <script>
        function treeToggle(btn, event) {
            if (event) event.stopPropagation();
            var nodeId = btn.getAttribute('data-node');
            var node = document.getElementById(nodeId);
            var icon = btn.querySelector('.expand-icon');
            
            if (node) {
                if (node.classList.contains('collapsed')) {
                    node.classList.remove('collapsed');
                    if (icon) icon.textContent = '−';
                } else {
                    node.classList.add('collapsed');
                    if (icon) icon.textContent = '+';
                }
            }
        }
        
        function toggleHistory(historyId, event) {
            if (event.target.tagName === 'A') return;
            var row = document.getElementById('history_' + historyId);
            if (row) {
                if (row.style.display === 'none' || row.style.display === '') {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        </script>
        <?php
    }

    public function _render_filters()
    {
        global $mysql;
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-t');
        
        // Только агентства с актуальными бронями (по квартирам со status=4)
        $agencies = $mysql->get_arr("
            SELECT DISTINCT ag.agency_id, ag.caption 
            FROM agency ag 
            INNER JOIN users u ON u.agency_id = ag.agency_id 
            INNER JOIN apartaments a ON a.status = 4 AND a.status_broni_id > 0
            INNER JOIN broni b ON b.broni_id = a.status_broni_id AND b.user_id = u.id
            INNER JOIN homes h ON h.home_id = a.home_id AND h.show = 1
            WHERE a.home_id NOT IN (18, 19)
            ORDER BY ag.caption
        ");
        $selected = intval($_GET['agency_id'] ?? 92);
        ?>
        <div class="noprint filter-row">
            <form method="GET" action="ctrind.php" style="display: contents;">
                <input type="hidden" name="ctr" value="<?= htmlspecialchars($this->ctr) ?>">
                <input type="hidden" name="sdan" value="<?= htmlspecialchars($_GET['sdan'] ?? 3) ?>">
                <input type="hidden" name="kvartal" value="<?= htmlspecialchars($_GET['kvartal'] ?? 0) ?>">
                <input type="hidden" name="home" value="<?= htmlspecialchars($_GET['home'] ?? 0) ?>">
                
                <span class="filter-label">Период:</span>
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="filter-input">
                <span>—</span>
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="filter-input">
                
                <span class="filter-label">Агентство:</span>
                <select name="agency_id" class="filter-input">
                    <option value="0">-- Все --</option>
                    <?php foreach ($agencies as $ag): ?>
                        <option value="<?= $ag['agency_id'] ?>" <?= $selected == $ag['agency_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag['caption']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="filter-btn">Применить</button>
            </form>
        </div>
        <?php
    }

    private function _generate_report()
    {
        global $mysql;
        ob_start();

        $home_id = intval($_GET['home'] ?? 0);
        $kvartal_id = intval($_GET['kvartal'] ?? 0);
        $sdan = intval($_GET['sdan'] ?? 3);
        $agency_filter = intval($_GET['agency_id'] ?? 92);
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-t');

        $home_conds = ["h.show = 1", "a.home_id NOT IN (18, 19)"];
        if ($sdan == 0) $home_conds[] = "h.complite = 0";
        elseif ($sdan == 1) $home_conds[] = "h.complite = 1";
        if ($home_id > 0) $home_conds[] = "h.home_id = " . $home_id;
        elseif ($kvartal_id > 0) $home_conds[] = "CAST(h.kvartal AS UNSIGNED) = " . $kvartal_id;
        elseif ($kvartal_id == -1) $home_conds[] = "(h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
        $home_where = implode(' AND ', $home_conds);
        $agency_where = $agency_filter > 0 ? " AND ag.agency_id = " . $agency_filter : "";

        // ========== 1. АКТУАЛЬНЫЕ БРОНИ (группировка по квартирам) ==========
        $sql_broni = "
            SELECT 'broni' as deal_type,
                a.apartament_id,
                a.home_id, a.apartment_num as apartments_num,
                a.price as current_price, a.area, a.rooms, a.floor,
                h.title as home_title, u.id as user_id, u.login, u.name as user_name, 
                ag.agency_id, ag.caption as agency_caption,
                b.broni_id, b.price as broni_price, b.date as broni_date, b.comment,
                b.date as last_broni_date,
                ({$this->broni_days} - DATEDIFF(NOW(), b.date)) as days_remaining,
                'Актуальна' as deal_result
            FROM apartaments a
            INNER JOIN broni b ON a.status_broni_id = b.broni_id
            INNER JOIN homes h ON h.home_id = a.home_id
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN agency ag ON ag.agency_id = u.agency_id
            WHERE a.status = 4 AND a.status_broni_id > 0
              AND {$home_where} {$agency_where}";

        // ========== 2. ПРОДАНО (за период) ==========
        $sql_sold = "
            SELECT 'sold' as deal_type,
                b.broni_id, b.home_id, b.apartments_num, b.price as broni_price, 
                b.date as broni_date, b.comment,
                a.apartament_id, a.price as current_price, a.area, a.rooms, a.floor,
                h.title as home_title, u.id as user_id, u.login, u.name as user_name, 
                ag.agency_id, ag.caption as agency_caption,
                'Продано' as deal_result
            FROM broni b
            INNER JOIN homes h ON h.home_id = b.home_id
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN agency ag ON ag.agency_id = u.agency_id
            INNER JOIN apartaments a ON a.apartament_id = b.apartament_id
            INNER JOIN broni sale ON sale.apartament_id = b.apartament_id AND sale.status = 3
            WHERE b.status = 4
              AND b.broni_id = (
                  SELECT b2.broni_id FROM broni b2 
                  WHERE b2.apartament_id = b.apartament_id 
                    AND b2.status = 4
                    AND b2.date < sale.date
                  ORDER BY b2.date DESC LIMIT 1
              )
              AND sale.date >= '{$date_from} 00:00:00'
              AND sale.date <= '{$date_to} 23:59:59'
              AND {$home_where} {$agency_where}";

        // ========== 3. АКТИВНОСТЬ ЗА ПЕРИОД ==========
        $sql_work = "
            SELECT 'work' as deal_type,
                a.apartament_id,
                a.home_id, a.apartment_num as apartments_num,
                a.price as current_price, a.area, a.rooms, a.floor,
                h.title as home_title, u.id as user_id, u.login, u.name as user_name, 
                ag.agency_id, ag.caption as agency_caption,
                MAX(b.date) as last_broni_date,
                (SELECT b2.price FROM broni b2 
                 WHERE b2.apartament_id = a.apartament_id AND b2.status = 4
                 ORDER BY b2.date DESC LIMIT 1) as last_broni_price,
                COUNT(DISTINCT b.broni_id) as broni_count,
                CASE a.status
                    WHEN 0 THEN 'Свободна'
                    WHEN 2 THEN 'Свободна'
                    WHEN 3 THEN 'Продано'
                    WHEN 4 THEN 'Забронирована'
                    WHEN 5 THEN 'Бронь застройщика'
                    WHEN 6 THEN 'Бронь подрядчика'
                    ELSE CONCAT('Статус: ', a.status)
                END as deal_result
            FROM apartaments a
            INNER JOIN broni b ON b.apartament_id = a.apartament_id AND b.status = 4
                AND b.date >= '{$date_from} 00:00:00'
                AND b.date <= '{$date_to} 23:59:59'
            INNER JOIN homes h ON h.home_id = a.home_id
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN agency ag ON ag.agency_id = u.agency_id
            WHERE {$home_where} {$agency_where}
            GROUP BY a.apartament_id, a.home_id, a.apartment_num, a.price, a.area, a.rooms, a.floor,
                     h.title, u.id, u.login, u.name, ag.agency_id, ag.caption, a.status";

        $results_broni = $mysql->get_arr($sql_broni);
        $results_sold = $mysql->get_arr($sql_sold);
        $results_work = $mysql->get_arr($sql_work);

        if (empty($results_broni) && empty($results_sold) && empty($results_work)) {
            return '<div style="text-align:center;padding:40px;color:#666;"><h3>Нет данных</h3></div>';
        }

        // === РАСЧЁТ ДАТЫ НАЧАЛА ЦЕПОЧКИ БРОНЕЙ ===
        // Собираем все apartament_id + user_id пары
        $allAptUser = [];
        foreach ($results_broni as $r) $allAptUser[$r['apartament_id'].'_'.$r['user_id']] = [$r['apartament_id'], $r['user_id']];
        foreach ($results_sold as $r) $allAptUser[$r['apartament_id'].'_'.$r['user_id']] = [$r['apartament_id'], $r['user_id']];
        foreach ($results_work as $r) $allAptUser[$r['apartament_id'].'_'.$r['user_id']] = [$r['apartament_id'], $r['user_id']];
        
        // Загружаем все брони для этих квартир одним запросом
        $chainDates = [];
        if (!empty($allAptUser)) {
            $aptList = implode(',', array_unique(array_column($allAptUser, 0)));
            $allBroniHistory = $mysql->get_arr("
                SELECT b.apartament_id, b.date, b.status, b.user_id, u.agency_id
                FROM broni b
                LEFT JOIN users u ON u.id = b.user_id
                WHERE b.apartament_id IN ({$aptList})
                ORDER BY b.apartament_id, b.date ASC
            ");
            
            // Группируем по квартирам
            $byApartment = [];
            foreach ($allBroniHistory as $b) {
                $byApartment[$b['apartament_id']][] = $b;
            }
            
            // Вычисляем chain_start_date для каждой квартиры+пользователь
            foreach ($allAptUser as $key => $pair) {
                $aptId = $pair[0];
                $userId = $pair[1];
                
                if (!isset($byApartment[$aptId])) continue;
                
                $history = $byApartment[$aptId];
                $chainStart = null;
                
                // Идём с конца к началу
                for ($i = count($history) - 1; $i >= 0; $i--) {
                    $broni = $history[$i];
                    $isCurrentUser = ($broni['user_id'] == $userId);
                    $isAdmin = ($broni['agency_id'] == 1);
                    $isOtherUser = !$isAdmin && !$isCurrentUser && $broni['user_id'] > 0;
                    
                    if ($isCurrentUser && $broni['status'] == 4) {
                        $chainStart = $broni['date'];
                    } elseif ($isOtherUser && $broni['status'] == 4) {
                        // Бронь другого пользователя прерывает цепочку
                        break;
                    }
                }
                
                $chainDates[$aptId][$userId] = $chainStart;
            }
        }

        // Обновляем days_active в результатах
        $updateDays = function(&$results) use ($chainDates) {
            foreach ($results as &$r) {
                $aptId = $r['apartament_id'];
                $uId = $r['user_id'];
                if (isset($chainDates[$aptId][$uId]) && $chainDates[$aptId][$uId]) {
                    $r['chain_start_date'] = $chainDates[$aptId][$uId];
                    $r['days_active'] = floor((time() - strtotime($chainDates[$aptId][$uId])) / 86400);
                } else {
                    $r['chain_start_date'] = $r['broni_date'] ?? $r['last_broni_date'] ?? null;
                    $r['days_active'] = 0;
                }
            }
        };
        $updateDays($results_broni);
        $updateDays($results_sold);
        $updateDays($results_work);

        // Группировка
        $tree = [];
        $total = ['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0];

        // Актуальные брони
        foreach ($results_broni as $r) {
            $ag = $r['agency_id'];
            $u = $r['user_id'];
            if (!isset($tree[$ag])) {
                $tree[$ag] = ['caption'=>$r['agency_caption'],'users'=>[],'s'=>['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0]];
            }
            if (!isset($tree[$ag]['users'][$u])) {
                $tree[$ag]['users'][$u] = ['name'=>$r['user_name'],'login'=>$r['login'],'deals'=>['broni'=>[],'sold'=>[],'work'=>[]],'s'=>['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0]];
            }
            $tree[$ag]['users'][$u]['deals']['broni'][] = $r;
            $tree[$ag]['users'][$u]['s']['broni']++;
            $tree[$ag]['users'][$u]['s']['broni_sum'] += $r['broni_price'];
            $tree[$ag]['s']['broni']++;
            $tree[$ag]['s']['broni_sum'] += $r['broni_price'];
            $total['broni']++;
            $total['broni_sum'] += $r['broni_price'];
        }

        // Продано
        foreach ($results_sold as $r) {
            $ag = $r['agency_id'];
            $u = $r['user_id'];
            if (!isset($tree[$ag])) {
                $tree[$ag] = ['caption'=>$r['agency_caption'],'users'=>[],'s'=>['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0]];
            }
            if (!isset($tree[$ag]['users'][$u])) {
                $tree[$ag]['users'][$u] = ['name'=>$r['user_name'],'login'=>$r['login'],'deals'=>['broni'=>[],'sold'=>[],'work'=>[]],'s'=>['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0]];
            }
            $tree[$ag]['users'][$u]['deals']['sold'][] = $r;
            $tree[$ag]['users'][$u]['s']['sold']++;
            $tree[$ag]['users'][$u]['s']['sold_sum'] += $r['broni_price'];
            $tree[$ag]['s']['sold']++;
            $tree[$ag]['s']['sold_sum'] += $r['broni_price'];
            $total['sold']++;
            $total['sold_sum'] += $r['broni_price'];
        }

        // Активность (квартиры)
        foreach ($results_work as $r) {
            $ag = $r['agency_id'];
            $u = $r['user_id'];
            if (!isset($tree[$ag])) {
                $tree[$ag] = ['caption'=>$r['agency_caption'],'users'=>[],'s'=>['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0]];
            }
            if (!isset($tree[$ag]['users'][$u])) {
                $tree[$ag]['users'][$u] = ['name'=>$r['user_name'],'login'=>$r['login'],'deals'=>['broni'=>[],'sold'=>[],'work'=>[]],'s'=>['broni'=>0,'sold'=>0,'work'=>0,'broni_sum'=>0,'sold_sum'=>0,'work_sum'=>0]];
            }
            $tree[$ag]['users'][$u]['deals']['work'][] = $r;
            $tree[$ag]['users'][$u]['s']['work']++;
            $tree[$ag]['users'][$u]['s']['work_sum'] += $r['last_broni_price'] ?? 0;
            $tree[$ag]['s']['work']++;
            $tree[$ag]['s']['work_sum'] += $r['last_broni_price'] ?? 0;
            $total['work']++;
            $total['work_sum'] += $r['last_broni_price'] ?? 0;
        }

        // Сортировка агентств по названию
        uasort($tree, fn($a, $b) => strcmp($a['caption'], $b['caption']));
        // Сортировка пользователей внутри агентства по сумме
        foreach ($tree as &$ag) {
            uasort($ag['users'], fn($a,$b)=>($b['s']['broni_sum']+$b['s']['sold_sum'])-($a['s']['broni_sum']+$a['s']['sold_sum']));
        }

        // === СВОДКА ===
        ?>
        <div class="summary-block noprint">
            <div class="summary-item">
                <div class="summary-value"><?= $total['broni'] ?></div>
                <div class="summary-label">Актуальных броней</div>
            </div>
            <div class="summary-item">
                <div class="summary-value"><?= number_format($total['broni_sum'],0,',',' ') ?></div>
                <div class="summary-label">Сумма броней</div>
            </div>
            <div class="summary-item">
                <div class="summary-value"><?= $total['sold'] ?></div>
                <div class="summary-label">Продано</div>
            </div>
            <div class="summary-item">
                <div class="summary-value"><?= number_format($total['sold_sum'],0,',',' ') ?></div>
                <div class="summary-label">Сумма продаж</div>
            </div>
            <div class="summary-item">
                <div class="summary-value"><?= $total['work'] ?></div>
                <div class="summary-label">Квартир с активностью</div>
            </div>
        </div>

        <div class="tree-container">
        <?php
        $agI = 0;
        foreach ($tree as $agId => $ag):
            $agI++;
            $agNode = "ag{$agI}";
            $s = $ag['s'];
        ?>
            <!-- Агентство -->
            <div class="tree-node node-agency" onclick="treeToggle(this, event)" data-node="<?= $agNode ?>">
                <span class="expand-icon">+</span>
                <span><?= htmlspecialchars($ag['caption']) ?></span>
                <span style="margin-left:15px;opacity:0.9">
                    Бронь: <b><?= $s['broni'] ?></b> (<?= number_format($s['broni_sum'],0,',',' ') ?>) |
                    Продано: <b><?= $s['sold'] ?></b> (<?= number_format($s['sold_sum'],0,',',' ') ?>) |
                    Активность: <b><?= $s['work'] ?></b> (<?= number_format($s['work_sum'],0,',',' ') ?>)
                </span>
            </div>
            
            <div id="<?= $agNode ?>" class="collapsed">
            <?php
            $uI = 0;
            foreach ($ag['users'] as $uId => $u):
                $uI++;
                $uNode = "{$agNode}_u{$uI}";
                $us = $u['s'];
            ?>
                <!-- Пользователь -->
                <div class="tree-node node-user" onclick="treeToggle(this, event)" data-node="<?= $uNode ?>">
                    <span class="expand-icon">+</span>
                    <b><?= htmlspecialchars($u['name'] ?: $u['login']) ?></b>
                    <span style="margin-left:15px;opacity:0.9">
                        Бронь: <b><?= $us['broni'] ?></b> (<?= number_format($us['broni_sum'],0,',',' ') ?>) |
                        Продано: <b><?= $us['sold'] ?></b> (<?= number_format($us['sold_sum'],0,',',' ') ?>) |
                        Активность: <b><?= $us['work'] ?></b> (<?= number_format($us['work_sum'],0,',',' ') ?>)
                    </span>
                </div>
                
                <div id="<?= $uNode ?>" class="collapsed">
                <?php
                $sections = [
                    'broni'=>['title'=>'Актуальные брони','class'=>'status-broni'],
                    'sold'=>['title'=>'Продано','class'=>'status-sold'],
                    'work'=>['title'=>'Активность за период','class'=>'status-work']
                ];
                $stI = 0;
                foreach ($sections as $stType => $st):
                    if (empty($u['deals'][$stType])) continue;
                    $stI++;
                    $stNode = "{$uNode}_s{$stI}";
                ?>
                    <!-- Статус -->
                    <div class="tree-node node-status <?= $st['class'] ?>" onclick="treeToggle(this, event)" data-node="<?= $stNode ?>">
                        <span class="expand-icon">+</span>
                        <span class="status-label"><?= $st['title'] ?>:</span>
                        <?= count($u['deals'][$stType]) ?> шт.
                    </div>
                    
                    <div id="<?= $stNode ?>" class="collapsed">
                        <div class="node-apartments">
                            <table class="apartments-table">
                                <tr>
                                    <th>Дом</th><th>Кв.</th><th>Комн.</th><th>Этаж</th><th>Площадь</th>
                                    <th>Цена брони</th><th>Текущая цена</th><th>Дата брони</th>
                                    <th>С первой брони</th>
                                    <?php if ($stType != 'broni'): ?>
                                    <th>Итог</th><th>Коммент.</th>
                                    <?php else: ?>
                                    <th>До снятия</th>
                                    <?php endif; ?>
                                </tr>
                                
                                <?php 
                                foreach ($u['deals'][$stType] as $a): 
                                    $broniPrice = $a['broni_price'] ?? $a['last_broni_price'] ?? 0;
                                    $diff = $a['current_price'] - $broniPrice;
                                    $daysCls = 'days-normal';
                                    if (isset($a['days_remaining']) && $a['days_remaining'] <= 3) $daysCls = 'days-warning';
                                    
                                    $resCls = 'result-actual';
                                    $dealRes = $a['deal_result'];
                                    if ($dealRes == 'Продано' || strpos($dealRes, 'Продано') !== false) {
                                        $resCls = 'result-sold';
                                    } elseif (strpos($dealRes, 'Снят') !== false || strpos($dealRes, 'Свободна') !== false) {
                                        $resCls = 'result-expired';
                                    }
                                    
                                    $broniDate = $a['broni_date'] ?? $a['last_broni_date'] ?? '';
                                    $comment = str_replace('Бронь через iframe apart', 'Бронь через кабинет', $a['comment'] ?? '');
                                    $daysActive = $a['days_active'] ?? 0;
                                ?>
                                <tr class="apartment-row" onclick="toggleHistory('<?= $stType ?>_<?= $a['apartament_id'] ?>_<?= $a['user_id'] ?>', event)">
                                    <td><?= htmlspecialchars($a['home_title']) ?></td>
                                    <td><a href="https://em.m2profi.pro/sahmatka/iframe_router.php?ctr=apartments&act=order&home_id=<?= $a['home_id'] ?>&apartment_num=<?= $a['apartments_num'] ?>&apartments=1" class="apartment-link" target="_blank"><?= $a['apartments_num'] ?></a></td>
                                    <td><?= htmlspecialchars($a['rooms']) ?></td>
                                    <td><?= $a['floor'] ?></td>
                                    <td><?= number_format($a['area'],2,',',' ') ?> м²</td>
                                    <td><?= number_format($broniPrice,0,',',' ') ?></td>
                                    <td>
                                        <?= number_format($a['current_price'],0,',',' ') ?>
                                        <?php if ($diff != 0): ?><br><span class="price-diff"><?= $diff>0?'+':'' ?><?= number_format($diff,0,',',' ') ?></span><?php endif; ?>
                                    </td>
                                    <td><?= $broniDate ? date('d.m.Y', strtotime($broniDate)) : '-' ?></td>
                                    <td><?= $daysActive ?> дн.</td>
                                    <?php if ($stType == 'broni'): ?>
                                    <td class="<?= $daysCls ?>">
                                        <?= $a['days_remaining'] < 0 ? '<span style="color:#dc3545">Просрочена!</span>' : $a['days_remaining'].' дн.' ?>
                                    </td>
                                    <?php else: ?>
                                    <td class="<?= $resCls ?>"><?= $dealRes ?></td>
                                    <td class="comment-cell"><?= htmlspecialchars($comment) ?></td>
                                    <?php endif; ?>
                                </tr>
                                <?php 
                                    // Загружаем ВСЮ историю броней для этой квартиры (включая других пользователей)
                                    $history = $mysql->get_arr("
                                        SELECT b.broni_id, b.date, b.price, b.comment, b.status,
                                               u.name as user_name, u.login, ag.caption as agency_caption,
                                               CASE b.status
                                                   WHEN 0 THEN 'Свободна'
                                                   WHEN 2 THEN 'Снята'
                                                   WHEN 3 THEN 'Продано'
                                                   WHEN 4 THEN 'Бронь'
                                                   WHEN 5 THEN 'Застройщик'
                                                   WHEN 6 THEN 'Подрядчик'
                                                   ELSE CONCAT('Статус: ', b.status)
                                               END as status_text
                                        FROM broni b
                                        LEFT JOIN users u ON u.id = b.user_id
                                        LEFT JOIN agency ag ON ag.agency_id = u.agency_id
                                        WHERE b.apartament_id = " . intval($a['apartament_id']) . "
                                        ORDER BY b.date DESC
                                    ");
                                    if (!empty($history)):
                                ?>
                                <tr id="history_<?= $stType ?>_<?= $a['apartament_id'] ?>_<?= $a['user_id'] ?>" class="history-row" style="display:none;">
                                    <td colspan="<?= $stType == 'broni' ? 10 : 11 ?>" style="padding:0;">
                                        <table class="history-table">
                                            <tr>
                                                <th>Дата</th><th>Статус</th><th>Цена</th><th>Пользователь</th><th>Агентство</th><th>Комментарий</th>
                                            </tr>
                                            <?php foreach ($history as $h): 
                                                $isOtherUser = ($h['login'] && $h['login'] != $u['login']);
                                            ?>
                                            <tr<?= $isOtherUser ? ' class="other-user"' : '' ?>>
                                                <td><?= date('d.m.Y H:i', strtotime($h['date'])) ?></td>
                                                <td><?= $h['status_text'] ?></td>
                                                <td><?= number_format($h['price'],0,',',' ') ?></td>
                                                <td><?= htmlspecialchars($h['user_name'] ?: $h['login'] ?: '—') ?></td>
                                                <td><?= htmlspecialchars($h['agency_caption'] ?: '—') ?></td>
                                                <td><?= htmlspecialchars(str_replace('Бронь через iframe apart', 'Бронь через кабинет', $h['comment'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}