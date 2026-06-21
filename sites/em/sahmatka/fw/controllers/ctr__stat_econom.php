<?php
$GLOBALS['t']['title']='Экономика проекта';

class ctr__stat_econom extends ctr__homes__
{ 
    var $ctr = 'stat_econom'; // Название нашего контроллера
    var $title = 'Экономика проекта';

    /**
     * Основной метод (action), который вызываетcя по умолчанию.
     * Он выводит меню и все отчеты.
     */
    function act__index()
    {
        global $t;
        $t['h1'] = 'Сводная статистика'; 

        ?>
        <style>
            /* Ваши стили для меню */
            .mdef{ padding:5px; padding-left:13px; padding-right:13px; font-weight:bold; font-size:18px; }	
            .objmenua .mdef{color:#000;}
            .mdefa{color:#FFA500;} /* ТОлько админам */
            .mdefaop{color:#999999;} /*  Админам и отделу продаж */
            .mdefth{color:#FFF; background-color:#00CDAD;}			 
            .mdef:hover{color:#FFF; background-color:#00CDAD;}					
            
            /* Ваши стили для отчета */
            .table table tr td { padding: 7px; }
            @media screen and (min-width: 1000px) { .mmenu{ display:block; padding-right:0; margin-top:15px; display: flex; flex-direction: row; justify-content: space-between; width: 100%;} .mobilenav{display:none;} }
            @media screen and (max-width: 1000px) { .mmenu{ display:none; } .mobilenav{display:block; width:100%;} .nomobile{display:none;} }
            .table-title { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
            .financial-summary { padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
            .plan-value { text-align: center; font-size: 14px; color: #555; margin-bottom: 5px; font-weight: bold; }
            .plan-value span { font-size: 18px; color: #000; }
            .revenue-bar { display: flex; width: 100%; height: 60px; border-radius: 5px; overflow: hidden; border: 1px solid #aaa; font-size: 14px; font-weight: bold; color: white; }
            .revenue-bar-sold {min-width:150px; background-color:GREY;  display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 5px; box-sizing: border-box; border-right: 1px solid #aaa; } 
			
			 
			
			
            .revenue-bar-unsold {min-width:150px; background-color: rgba(40, 167, 69, 0.7); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 5px; box-sizing: border-box; }
            .bar-label { font-size: 12px; opacity: 0.9; }
            .bar-value { font-size: 16px; }
            .result-value { text-align: center; font-size: 18px; margin-top: 10px; font-weight: bold; }
			
			.stat-table {
				padding: 10px  0 0;
			}
			    .mmenu {
        display: block;
        padding-right: 0;
        margin-top: 15px;
        display: flex
;
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
        max-width: 100%;
        display: inline;
    }
			.mmenu li {
				display: inline;
				padding: 10px;
				margin: 5px;
			}
			.mdef {
    padding: 5px 3px;
    font-weight: bold;
    font-size: 18px;
    line-height: 2em;
}
        </style>
		
		<div id="ajaxcontent" class="stat">
        	<div class="stat-top">
				
				<div class="stat-top-filter">
					<a href="#" class="stat-top-btn btn btn_arrow-long" style="display:none;">ДЕТАЛЬНАЯ СТАТИСТИКА<i></i></a>
				</div>
				<!-- Панель поиска -->
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
				</div>
        <?php 
        // 1. Выводим весь блок с меню и фильтрами
        $this->_render_filters(); 
        
        // 2. Генерируем данные для ВСЕХ таблиц из исходного отчета
        $financial_summary_html = $this->_generate_financial_summary();
        $table_sold_html = $this->_generate_stats_table("Проданные квартиры", [3]);
        $table_unsold_html = $this->_generate_stats_table("Непроданные квартиры (свободные + бронь агентств, застройщика, подрядчиков)", [0, 2, 4, 5, 6]);
        $table_booked_html = $this->_generate_stats_table("Забронированные квартиры (бронь агентств и ОП)", [4]);
        $table_remaining_html = $this->_generate_stats_table("Остатки (свободные, бронь застройщика, подрядчиков)", [0, 2, 5, 6]);
        ?>
        
        <!-- 3. Выводим таблицы с результатами -->
        <div class="stat-table stat-table-agency table">
            <?= $financial_summary_html ?>
        </div>
        <div class="stat-table stat-table-agency table">
            <?= $table_sold_html ?>
        </div>
        <div class="stat-table stat-table-agency table">
            <?= $table_unsold_html ?>
        </div>
        <div class="stat-table stat-table-agency table">
            <?= $table_booked_html ?>
        </div>
        <div class="stat-table stat-table-agency table">
            <?= $table_remaining_html ?>
        </div>
		</div>
        <?php
    }

   

    /**
     * Генерирует HTML-код таблицы статистики по квартирам.
     */
    private function _generate_stats_table($title, $statuses) {
		global $t, $mysql;
        ob_start();

        $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
        $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
        $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;
        $status_list_for_sql = implode(',', $statuses);

        $sql = "
            SELECT REGEXP_SUBSTR(apartaments.rooms, '[0-9]+') as roomsx,
                   COUNT(*) as c, SUM(apartaments.area_small) as summ_area, SUM(apartaments.price) as summ_price,
                   SUM(apartaments.price / NULLIF(apartaments.area_small, 0)) as sum_price_per_m2
            FROM apartaments  
            LEFT JOIN homes ON homes.home_id = apartaments.home_id
            LEFT JOIN homes_kvartal kv ON CAST(homes.kvartal AS UNSIGNED) = kv.homes_kvartal_id
            WHERE apartaments.status2 IN ({$status_list_for_sql}) AND apartaments.rooms > 0 AND homes.`show` > 0 AND apartaments.area_small > 0 ";

        if ($sdan == 0) $sql .= " AND homes.complite = 0 ";
        elseif ($sdan == 1) $sql .= " AND homes.complite = 1 ";
        if ($kvartal_id > 0) $sql .= " AND kv.homes_kvartal_id = " . $kvartal_id;
        if ($home_id) $sql .= " AND apartaments.home_id = " . $home_id;
        $sql .= " GROUP BY roomsx ";

        $query_result = $mysql->get_arr($sql);
        ?>
        <div class="table-title"><?= htmlspecialchars($title) ?></div>
        <table width="100%">
            <tr><th>к</th><th>Количество</th><th>Площадь</th><th>Стоимость</th><th>Сред. ст. м<sup>2</sup></th></tr>
            <?php
            $summ_arr = ['c' => 0, 'area' => 0, 'price' => 0];
            $avg_metr_list = [];
            foreach ($query_result as $r) {
                if (empty($r['roomsx'])) continue;
                $avg_price_per_m2 = ($r['c'] ?? 0) > 0 ? ($r['sum_price_per_m2'] ?? 0) / $r['c'] : 0;
                $avg_metr_list[] = $avg_price_per_m2;
                $summ_arr['c'] += $r['c'];
                $summ_arr['area'] += $r['summ_area'];
                $summ_arr['price'] += $r['summ_price'];
                ?>
                <tr>
                    <td><?=$r['roomsx']?></td><td><?=$r['c']?></td><td><?=number_format($r['summ_area'], 2, ',', ' ') ?></td>
                    <td><?=number_format($r['summ_price'], 0, ',', ' ')?></td><td><?=number_format($avg_price_per_m2, 0, ',', ' ')?></td>
                </tr>
            <?php } 
            $avg_metr_ = count($avg_metr_list) > 0 ? array_sum($avg_metr_list) / count($avg_metr_list) : 0;
            ?>
            <tr>
                <td><b>Итого</b></td><td><b><?=number_format($summ_arr['c'], 0, ',', ' ') ?></b></td>
                <td><b><?=number_format($summ_arr['area'], 2, ',', ' ') ?> м<sup>2</sup></b></td>
                <td><b><?=number_format($summ_arr['price'], 0, ',', ' ') ?></b></td>
                <td><b><?=number_format($avg_metr_, 0, ',', ' ') ?></b></td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }
    
    private function _generate_financial_summary() {
    global $t, $mysql;
    ob_start();
    $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
    $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
    $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;

    // Запрос для расчета целевой цены и количества домов
    $sql_target = "
        SELECT 
            SUM(h.project_price) as target_price,
            COUNT(h.home_id) as total_homes_in_selection,
            COUNT(CASE WHEN h.project_price > 0 THEN h.home_id END) as homes_with_price
        FROM homes h
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE h.`show` > 0 ";
    if ($sdan == 0) $sql_target .= " AND h.complite = 0 ";
    elseif ($sdan == 1) $sql_target .= " AND h.complite = 1 ";
    if ($kvartal_id > 0) $sql_target .= " AND kv.homes_kvartal_id = " . $kvartal_id;
    if ($home_id) $sql_target .= " AND h.home_id = " . $home_id;
    
    $target_result_arr = $mysql->get_arr($sql_target);
    $summary_data = $target_result_arr[0] ?? null;

    $target_price = $summary_data['target_price'] ?? 0;
    $total_homes_in_selection = (int)($summary_data['total_homes_in_selection'] ?? 0);
    $homes_with_price = (int)($summary_data['homes_with_price'] ?? 0);
    
    // Проверяем, полные ли у нас данные для расчета
    $is_calculation_valid = ($total_homes_in_selection > 0 && $total_homes_in_selection == $homes_with_price);
    
    // Запрос для данных по квартирам
    $sql_apartments = "
        SELECT 
            SUM(CASE WHEN a.status2 = 3 THEN a.price ELSE 0 END) as total_sold_price,
            SUM(CASE WHEN a.status2 IN (0, 2, 4, 5, 6) THEN a.price ELSE 0 END) as potential_unsold_price,
            COUNT(CASE WHEN a.status2 = 3 THEN a.apartament_id END) as total_sold_count,
            COUNT(CASE WHEN a.status2 IN (0, 2, 4, 5, 6) THEN a.apartament_id END) as potential_unsold_count,
            SUM(a.area) as total_area,
            SUM(a.area_small) as total_area_small
        FROM apartaments a
        LEFT JOIN homes h ON h.home_id = a.home_id
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE h.`show` > 0 AND a.area_small > 0 ";
    if ($sdan == 0) $sql_apartments .= " AND h.complite = 0 ";
    elseif ($sdan == 1) $sql_apartments .= " AND h.complite = 1 ";
    if ($kvartal_id > 0) $sql_apartments .= " AND kv.homes_kvartal_id = " . $kvartal_id;
    if ($home_id) $sql_apartments .= " AND a.home_id = " . $home_id;
    
    $apartments_result_arr = $mysql->get_arr($sql_apartments);
    $apartments_data = $apartments_result_arr[0] ?? [
        'total_sold_price' => 0,
        'potential_unsold_price' => 0,
        'total_sold_count' => 0,
        'potential_unsold_count' => 0,
        'total_area' => 0,
        'total_area_small' => 0
    ];
    
    $total_sold_price = $apartments_data['total_sold_price'];
    $potential_unsold_price = $apartments_data['potential_unsold_price'];
    $total_potential_revenue = $total_sold_price + $potential_unsold_price;
    $total_sold_count = (int)$apartments_data['total_sold_count'];
    $potential_unsold_count = (int)$apartments_data['potential_unsold_count'];
    $total_area = $apartments_data['total_area'];
    $total_area_small = $apartments_data['total_area_small'];
    ?>

    <div class="financial-summary">
        <?php
        // Рассчитываем ширину полосок
        $sold_width_percent = $total_potential_revenue > 0 ? ($total_sold_price / $total_potential_revenue) * 100 : 0;
        $unsold_width_percent = 100 - $sold_width_percent;
        ?>

        <!-- Блок с барами -->
        <div class="revenue-bar">
            <div class="revenue-bar-sold"  style="     background-color: #888; width: <?= $sold_width_percent ?>%;">
                <div class="bar-label">Продано <?= $total_sold_count ?> кв.</div>
                <div class="bar-value">на <?= number_format($total_sold_price, 0, ',', ' ') ?>  </div>
            </div>
            <div class="revenue-bar-unsold" style="width: <?= $unsold_width_percent ?>%;">
                <div class="bar-label">Осталось <?= $potential_unsold_count ?> кв.</div>
                <div class="bar-value">на <?= number_format($potential_unsold_price, 0, ',', ' ') ?>  </div>
            </div>
        </div>

        <!-- Заявленная сумма по финмодели -->
        <div class="plan-value" style="margin-top: 15px;">
            Заявлено по финмодели: <span><?= number_format($target_price, 0, ',', ' ') ?></span>
        </div>

        <!-- Прогнозируемая сумма по дому -->
        <div class="plan-value" style="margin-top: 5px;">
            Прогнозируемая сумма по дому: <span><?= number_format($total_potential_revenue, 0, ',', ' ') ?></span>
        </div>

        <?php // Условный блок: показываем расчеты по прибыли только если данные полные ?>
        <?php if ($is_calculation_valid): 
            $projected_result = $total_potential_revenue - $target_price;
            $projected_margin_percent = $target_price > 0 ? ($projected_result / $target_price) * 100 : 0;
            $result_text = $projected_result >= 0 ? 'Выполнение финмодели: +' : 'Выполнение финмодели: ';
            $sign = $projected_result >= 0 ? ' ' : '  ';
        ?>
            <div class="result-value" style="color: <?= $projected_result >= 0 ? '#28a745' : '#dc3545' ?>;">
                <?= $result_text . ' ' . number_format($projected_result, 0, ',', ' ') . " (" . $sign . number_format($projected_margin_percent, 2, ',', ' ') . "%)" ?>
            </div>
        <?php elseif ($total_homes_in_selection > 0): // Если дома есть, но данные неполные ?>
            <p style="display:none; text-align:center; margin-top:15px; color:#777; font-style: italic;">
                Расчет рентабельности недоступен, так как суммы заявленоые по финмодели не задана не для всех объектов в выборке.
            </p>
        <?php else: // Если вообще нет домов в выборке или у единственного дома не задана цена ?>
            <p style="display:none; text-align:center; margin-top:15px; color:#777; font-style: italic;">
                Плановая себестоимость не задана для выбранных объектов.
            </p>
        <?php endif; ?>

        <!-- Общая площадь -->
        <div class="plan-value" style="margin-top: 15px;">
            Общая площадь: <span><?= number_format($total_area, 2, ',', ' ') ?> м<sup>2</sup></span>
        </div>

        <!-- Приведенная площадь -->
        <div class="plan-value" style="margin-top: 5px;">
            Приведенная площадь: <span><?= number_format($total_area_small, 2, ',', ' ') ?> м<sup>2</sup></span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
}