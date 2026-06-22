<?php
// Фильтр с первого числа месяца до сегодня
$firstDayOfMonth = date('Y-m-01');
$yastoday = date('Y-m-d');
if (!$_GET['date_limit']) {
    $_GET['date_limit'] = $firstDayOfMonth . ' : ' . $yastoday;
}

 
function get_kvartals() {
    // ... (Ваша функция без изменений) ...
    $sql = "
        SELECT 
            kv.homes_kvartal_id,
            kv.title,
            COUNT(CASE WHEN h.show > 0 THEN h.home_id END) as total_homes,
            SUM(CASE WHEN h.show > 0 AND h.complite = 1 THEN 1 ELSE 0 END) as completed_homes,
            SUM(CASE WHEN h.show > 0 AND h.complite = 0 THEN 1 ELSE 0 END) as under_construction_homes
        FROM homes_kvartal kv
        LEFT JOIN homes h ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE kv.`show` = 1 AND kv.`del` = 0
        GROUP BY kv.homes_kvartal_id, kv.title
        ORDER BY kv.`order`
    ";

    $result = mysqli_query($GLOBALS['connection'], $sql);
    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['total_homes'] = (int)$row['total_homes'];
        $row['completed_homes'] = (int)$row['completed_homes'];
        $row['under_construction_homes'] = (int)$row['under_construction_homes'];
        $list[] = $row;
    }
    return $list;
}

function kvartal_menu() {
    // ... (Ваша функция без изменений) ...
    $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
    $selected_kvartal = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
    $action = $_GET['action'] ?? 'stat_salen2';

    $kvartals = get_kvartals();
    ?>
    <div style="width:100%; margin-bottom:10px; margin-top:15px;">
        <a href="user.php?action=<?=$action?>&sdan=<?=$sdan?>&kvartal=0" 
           class="mdef <?=($selected_kvartal == 0) ? 'mdefth' : ''?>" 
           style="display:inline-block; padding-left: 10px; font-weight:bold;">
            Все кварталы
        </a>
        <?php foreach ($kvartals as $k): 
            if ($sdan == 0 && $k['under_construction_homes'] == 0) continue;
            if ($sdan == 1 && $k['completed_homes'] == 0) continue;
        ?>
            <a href="user.php?action=<?=$action?>&sdan=<?=$sdan?>&kvartal=<?=$k['homes_kvartal_id']?>&home=" 
               class="mdef <?=($selected_kvartal == $k['homes_kvartal_id']) ? 'mdefth' : ''?>" 
               style="display:inline-block; padding-left: 10px; font-weight:bold;">
                <?=htmlspecialchars($k['title'])?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}

function object_menux_s($action='objects') {
    // ... (Ваша функция без изменений) ...
    $sa = $GLOBALS['sa'];
    $h = $sa->get_homes_arr();
    $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
    $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

    // Фильтруем дома по sdan и kvartal на лету, если нужно
    if ($kvartal_id > 0 || in_array($sdan, [0,1])) {
        $filtered_h = [];
        foreach ($h as $v) {
            if ($sdan == 0 && $v['complite'] != "0") continue;
            if ($sdan == 1 && $v['complite'] != "1") continue;
            if ($kvartal_id > 0 && intval($v['kvartal']) != $kvartal_id) continue;
            $filtered_h[] = $v;
        }
        $h = $filtered_h;
    }

    foreach($h as $k => $v) {
        if( $_GET['home'] == $v['home_id'] ) {
            $class = ' class="mdef mdefth " ';
        } else { 
            $class = ' class="mdef" ';
            if($v['show'] == 2) { $class = ' class="mdef mdefa" '; }
            elseif($v['show'] == 3) { $class = ' class="mdef mdefaop" '; }
        }
        ?>
        <li style="padding:0;">
            <a href="user.php?action=<?=$action?>&home=<?=$v['home_id']?>&sdan=<?=$sdan?>&kvartal=<?=$_GET['kvartal']??0?>" <?=$class?> >
                <?=$v['title']?>
            </a>
        </li>
        <?php
    }
}
 
function object_menu_s($action='stat_salen2') {
    // ... (Ваша функция без изменений) ...
    $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
    $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

    // Получаем список домов с фильтрацией по sdan и kvartal
    $sql_homes = "
        SELECT home_id, title, long_title, complite, show, kvartal
        FROM homes 
        WHERE show > 0 
    ";
    if ($sdan == 0) $sql_homes .= " AND complite = 0 ";
    elseif ($sdan == 1) $sql_homes .= " AND complite = 1 ";
    if ($kvartal_id > 0) $sql_homes .= " AND CAST(kvartal AS UNSIGNED) = " . $kvartal_id;
    $sql_homes .= " ORDER BY `order`";

    $result = mysqli_query($GLOBALS['connection'], $sql_homes);
    $h_arr = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $h_arr[] = $row;
    }

    ?>
    <style>
    @media screen and (min-width: 1000px) {
      .mmenu{ display:block; padding-right:0; margin-top:15px; display: flex; flex-direction: row; justify-content: space-between; width: 100%;}
      .mobilenav{display:none;}
    }
    @media screen and (max-width: 1000px) {
      .mmenu{ display:none; }
      .mobilenav{display:block; width:100%;}
      .nomobile{display:none;}
    }
    .table-title {
        font-size: 18px;
        font-weight: bold;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .progress-bar-container {
        width: 100%;
        background-color: #f3f3f3;
        border-radius: 5px;
        border: 1px solid #ccc;
        height: 25px;
        position: relative;
    }
    .progress-bar {
        width: 0%;
        height: 100%;
        background-color: #4CAF50;
        border-radius: 5px;
        text-align: center;
        line-height: 25px;
        color: white;
        font-weight: bold;
    }
    .progress-bar-text {
        position: absolute;
        width: 100%;
        text-align: center;
        line-height: 25px;
        font-weight: bold;
        color: #333;
    }
	
	
	
	/* 👇 НОВЫЕ СТИЛИ ДЛЯ ГРАФИКА */
.financial-summary {
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
}
.plan-value {
    text-align: center;
    font-size: 14px;
    color: #555;
    margin-bottom: 5px;
    font-weight: bold;
}
.plan-value span {
    font-size: 18px;
    color: #000;
}
.revenue-bar {
    display: flex;
    width: 100%;
    height: 60px;
    border-radius: 5px;
    overflow: hidden;
    border: 1px solid #aaa;
    font-size: 14px;
    font-weight: bold;
    color: white;
}
.revenue-bar-sold {
    background-color: rgba(220, 53, 69, 0.7); /* Красный с прозрачностью */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 5px;
    box-sizing: border-box;
    border-right: 1px solid #aaa;
}
.revenue-bar-unsold {
    background-color: rgba(40, 167, 69, 0.7); /* Зеленый с прозрачностью */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 5px;
    box-sizing: border-box;
}
.bar-label {
    font-size: 12px;
    opacity: 0.9;
}
.bar-value {
    font-size: 16px;
}
.result-value {
    text-align: center;
    font-size: 18px;
    margin-top: 10px;
    font-weight: bold;
}


    </style>
     

    <div style="width:100%; margin-bottom:10px;">
        <a href="user.php?action=<?=$action?>&sdan=3&kvartal=<?=$kvartal_id?>" class="mdef <?=$_GET['sdan']==3 ? 'mdefth' : ''?>" style="display:inline-block; padding-left: 10px; font-weight:bold;">ВСЕ</a>
        <a href="user.php?action=<?=$action?>&sdan=0&kvartal=<?=$kvartal_id?>" class="mdef <?=$_GET['sdan']==0 ? 'mdefth' : ''?>" style="display:inline-block; padding-left: 12px; font-weight:bold;">СТРОЯЩИЕСЯ</a> 
        <a href="user.php?action=<?=$action?>&sdan=1&kvartal=<?=$kvartal_id?>" class="mdef <?=$_GET['sdan']==1 ? 'mdefth' : ''?>" style="display:inline-block; padding-left: 10px; font-weight:bold;">СДАННЫЕ</a>
    </div>

    <?php kvartal_menu(); ?>

    <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
        <br/>
        <ul class="mmenu">
            <?php object_menux_s($action); ?>
        </ul>

        <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select">
            <input type="hidden" name="action" value="<?=$action?>" />
            <input type="hidden" name="sdan" value="<?=$sdan?>" />
            <input type="hidden" name="kvartal" value="<?=$kvartal_id?>" />
            <div class="objects-head-nav__select">
                <select name="home" onChange="this.form.submit();" style="width:100%; text-align: left; border-radius:0;">
                    <option value="">Выбрать дом</option>
                    <?php foreach($h_arr as $v): ?>
                        <option value="<?=$v['home_id']?>" <?=($v['home_id']==($_GET['home']??'')) ? 'selected' : ''?>>
                            <?=$v['long_title']?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <hr style="margin-top: 12px;" class="nomobile"/>
    <?php
}

/**
 * Генерирует и возвращает HTML-код таблицы статистики для заданных статусов.
 * @param string $title Заголовок таблицы.
 * @param array $statuses Массив статусов квартир для включения в отчет.
 * @return string HTML-код таблицы.
 */
function generate_stats_table($title, $statuses) {
    // ... (Ваша функция без изменений) ...
    ob_start();

    $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
    $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
    $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;

    // Преобразуем массив статусов в строку для SQL-запроса
    $status_list_for_sql = implode(',', $statuses);

    $sql = '
        SELECT 
            REGEXP_SUBSTR(apartaments.rooms, "[0-9]+") as roomsx,
            COUNT(*) as c,
            SUM(apartaments.area) as summ_area,
            SUM(apartaments.price) as summ_price,
            SUM(apartaments.price / NULLIF(apartaments.area, 0)) as sum_price_per_m2
        FROM apartaments  
        LEFT JOIN homes ON homes.home_id = apartaments.home_id
        LEFT JOIN homes_kvartal kv ON CAST(homes.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE apartaments.status2 IN (' . $status_list_for_sql . ')
          AND apartaments.rooms > 0 
          AND homes.show > 0 
          AND apartaments.area > 0
    ';

    if ($sdan == 0) {
        $sql .= " AND homes.complite = 0 ";
    } elseif ($sdan == 1) {
        $sql .= " AND homes.complite = 1 ";
    }

    if ($kvartal_id > 0) {
        $sql .= " AND kv.homes_kvartal_id = " . $kvartal_id . " ";
    }

    if ($home_id) {
        $sql .= " AND apartaments.home_id = " . $home_id . " ";
    }

    $sql .= " GROUP BY roomsx ";

    $query = mysqli_query($GLOBALS['connection'], $sql);

    if (!$query) {
        // Просто выводим сообщение об ошибке, не прерывая весь скрипт
        return "<p>Ошибка при загрузке данных для таблицы '" . htmlspecialchars($title) . "': " . mysqli_error($GLOBALS['connection']) . "</p>";
    }

    ?>
    <div class="table-title"><?= htmlspecialchars($title) ?></div>
    <table>
        <tr>
            <th>к</th>
            <th><?= unit_phrase('count_col') ?></th>
            <th>Суммарная площадь</th>
            <th>Суммарная стоимость</th>
            <th>Средняя стоимость м<sup>2</sup></th>
        </tr>
        <?php
        $avg_metr_list = []; 
        $summ_arr = ['c' => 0, 'area' => 0, 'price' => 0]; 

        while($r = mysqli_fetch_assoc($query)) {
            if (empty($r['roomsx'])) continue;

            $avg_price_per_m2 = $r['c'] > 0 ? $r['sum_price_per_m2'] / $r['c'] : 0;
            $avg_metr_list[] = $avg_price_per_m2;

            $summ_arr['c'] += $r['c'];
            $summ_arr['area'] += $r['summ_area'];
            $summ_arr['price'] += $r['summ_price'];
            ?>
            <tr>
                <td><?=$r['roomsx']?></td>
                <td><?=$r['c']?></td>
                <td><?=number_format($r['summ_area'], 2, ',', ' ') ?></td>
                <td><?=number_format($r['summ_price'], 2, ',', ' ')?></td>
                <td><?=number_format($avg_price_per_m2, 2, ',', ' ')?></td>
            </tr>
            <?php
        }

        $avg_metr_ = count($avg_metr_list) > 0 ? array_sum($avg_metr_list) / count($avg_metr_list) : 0;
        ?>
        <tr>
            <td><b>Итого</b></td>
            <td><b><?=number_format($summ_arr['c'], 0, ',', ' ') ?></b></td>
            <td><b><?=number_format($summ_arr['area'], 2, ',', ' ') ?> м<sup>2</sup></b></td>
            <td><b><?=number_format($summ_arr['price'], 2, ',', ' ') ?></b></td>
            <td><b><?=number_format($avg_metr_, 2, ',', ' ') ?></b></td>
        </tr>
    </table>
    <?php
    return ob_get_clean();
}



/**
 * 👇 ОБНОВЛЕННАЯ ФУНКЦИЯ, которая генерирует ГРАФИК вместо таблицы
 * Генерирует HTML-код с графической финансовой сводкой по проекту.
 * @return string HTML-код.
 */
function generate_financial_summary() {
    ob_start();

    // --- Блок получения данных (остался без изменений) ---
    $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
    $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
    $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;

    $sql_target = "
        SELECT SUM(h.project_price) as target_price
        FROM homes h
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE h.show > 0 AND h.project_price > 0
    ";
    if ($sdan == 0) $sql_target .= " AND h.complite = 0 ";
    elseif ($sdan == 1) $sql_target .= " AND h.complite = 1 ";
    if ($kvartal_id > 0) $sql_target .= " AND kv.homes_kvartal_id = " . $kvartal_id;
    if ($home_id) $sql_target .= " AND h.home_id = " . $home_id;
    
    $target_result = mysqli_query($GLOBALS['connection'], $sql_target);
    $target_data = mysqli_fetch_assoc($target_result);
    $target_price = $target_data['target_price'] ?? 0;

    if ($target_price <= 0) {
        return '<div class="table-title">Финансовая сводка по проекту</div><p>Плановая себестоимость не задана для выбранных объектов.</p>';
    }

    $sql_apartments = "
        SELECT
            SUM(CASE WHEN a.status2 = 3 THEN a.price ELSE 0 END) as total_sold_price,
            SUM(CASE WHEN a.status2 IN (0, 2, 4, 5, 6) THEN a.price ELSE 0 END) as potential_unsold_price
        FROM apartaments a
        LEFT JOIN homes h ON h.home_id = a.home_id
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE h.show > 0 AND a.area > 0
    ";
    if ($sdan == 0) $sql_apartments .= " AND h.complite = 0 ";
    elseif ($sdan == 1) $sql_apartments .= " AND h.complite = 1 ";
    if ($kvartal_id > 0) $sql_apartments .= " AND kv.homes_kvartal_id = " . $kvartal_id;
    if ($home_id) $sql_apartments .= " AND a.home_id = " . $home_id;
    
    $apartments_result = mysqli_query($GLOBALS['connection'], $sql_apartments);
    $apartments_data = mysqli_fetch_assoc($apartments_result);
    
    $total_sold_price = $apartments_data['total_sold_price'] ?? 0;
    $potential_unsold_price = $apartments_data['potential_unsold_price'] ?? 0;

    // --- Блок вычислений (добавлены расчеты для ширины блоков) ---
    $total_potential_revenue = $total_sold_price + $potential_unsold_price;
    $projected_result = $total_potential_revenue - $target_price;
    $projected_margin_percent = $total_potential_revenue > 0 ? ($projected_result / $target_price) * 100 : 0;
    
    // Вычисляем процентную ширину для каждого блока
    $sold_width_percent = $total_potential_revenue > 0 ? ($total_sold_price / $total_potential_revenue) * 100 : 0;
    $unsold_width_percent = $total_potential_revenue > 0 ? ($potential_unsold_price / $total_potential_revenue) * 100 : 100;

    // --- НОВЫЙ БЛОК ВЫВОДА HTML-ГРАФИКА ---
    ?>
    <div class="table-title">Финансовая сводка по проекту</div>
    <div class="financial-summary">
        
        <!-- Сумма плана (себестоимость) сверху -->
        <div class="plan-value">
            Плановая стоимость: <span><?= number_format($target_price, 0, ',', ' ') ?></span>
        </div>
        
        <!-- Графическая полоса -->
        <div class="revenue-bar">
            <!-- Секция "Продано" -->
            <div class="revenue-bar-sold" style="width: <?= $sold_width_percent ?>%;">
                <div class="bar-label">ПРОДАНО</div>
                <div class="bar-value"><?= number_format($total_sold_price, 0, ',', ' ') ?></div>
            </div>
            <!-- Секция "Непродано" -->
            <div class="revenue-bar-unsold" style="width: <?= $unsold_width_percent ?>%;">
                <div class="bar-label">НЕПРОДАНО</div>
                <div class="bar-value"><?= number_format($potential_unsold_price, 0, ',', ' ') ?></div>
            </div>
        </div>
        
        <!-- Суммарная потенциальная выручка под полосой -->
        <div class="plan-value" style="margin-top: 5px;">
             Потенциальная выручка по текущим ценам: <span><?= number_format($total_potential_revenue, 0, ',', ' ') ?></span>
        </div>
        
        <!-- Итоговый финансовый результат снизу -->
        <div class="result-value" style="color: <?= $projected_result >= 0 ? '#28a745' : '#dc3545' ?>;">
            <?php
            $result_text = $projected_result >= 0 ? 'Прогнозируемая прибыль:' : 'Прогнозируемый убыток:';
            $sign = $projected_result >= 0 ? '+' : '';
            
            echo $result_text . ' ' . number_format($projected_result, 0, ',', ' ');
            echo " (" . $sign . number_format($projected_margin_percent, 2, ',', ' ') . "%)";
            ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}




// Генерируем HTML для каждой таблицы
$financial_summary_table = generate_financial_summary(); // 👈 ВЫЗОВ НОВОЙ ФУНКЦИИ
$table_sold = generate_stats_table(unit_phrase('sold_table'), [3]);
$table_unsold = generate_stats_table(unit_phrase('unsold_table'), [0, 2, 4, 5, 6]);
$table_booked = generate_stats_table(unit_phrase('booked_table'), [4]);
$table_remaining = generate_stats_table("Остатки (свободные, бронь застройщика, подрядчиков)", [0, 2, 5, 6]);

?>
<style>
.table table tr td {
    padding: 7px;
}
</style>
<section class="section-stat">
    <div class="container">
        <div class="page-header">
            <div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
            <div class="page-header__title">Сводная статистика</div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <div class="stat-top-filter">
                    <div class="stat-top-items">
                        <?php object_menu_s('stat_salen2'); ?>
                    </div>
                    <div class="stat-top-btns">
                        <a href="JavaScript:window.print();" class="stat-top__print"></a>
                    </div>
                </div>
            </div>
            
            <!-- 👇 ВЫВОДИМ НОВЫЙ БЛОК ПЕРЕД ОСТАЛЬНЫМИ ТАБЛИЦАМИ -->
            <div class="stat-table stat-table-agency table" style="display:none;">
                <?= $financial_summary_table ?>
            </div>

            <!-- Выводим четыре таблицы последовательно -->
           	<div class="stat-table stat-table-agency table">
                <?= $table_sold ?>
            </div>
            <div class="stat-table stat-table-agency table">
                <?= $table_unsold ?>
            </div>
            <div class="stat-table stat-table-agency table">
                <?= $table_booked ?>
            </div>
            <div class="stat-table stat-table-agency table">
                <?= $table_remaining ?>
            </div>
		
        </div>
    </div>
</section>