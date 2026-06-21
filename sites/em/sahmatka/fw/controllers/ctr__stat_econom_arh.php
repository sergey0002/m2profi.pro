<?php

/**
 * Контроллер для вывода статистики по архивным домам.
 * 
 * Отображает экономику проекта для домов, которые были удалены 
 * или скрыты с основного сайта (show = 0).
 */
class ctr__stat_econom_arh extends ctr__homes__
{
    /**
     * @var string Название контроллера.
     */
    var $ctr = 'stat_econom_arh';

    /**
     * @var string Заголовок страницы.
     */
    var $title = 'Экономика проекта (Архив)';

    /**
     * @var bool Устанавливаем флаг для выборки только архивных/скрытых домов.
     */
    protected $show_arch = true;
    
    /**
     * Возвращает условие для фильтрации по `show` с указанием алиаса таблицы `h`.
     * Переопределяет родительский метод для избежания неоднозначности в SQL-запросах.
     */
    function _get_show_condition() {
        // Для данного класса всегда выбираются архивные дома (`show` = 0) из таблицы homes (h)
        return "h.`show` = 0";
    }


    /**
     * Основной метод, формирующий и отображающий страницу.
     */
    function act__index()
    {
        global $t;
        $t['h1'] = 'Сводная статистика (Архив)';

        ?>
        <style>
            /* Стили для меню и отчетов */
            .mdef { padding: 5px 13px; font-weight: bold; font-size: 18px; }
            .objmenua .mdef { color: #000; }
            .mdefa { color: #FFA500; } /* Только админам */
            .mdefaop { color: #999999; } /* Админам и отделу продаж */
            .mdefth { color: #FFF; background-color: #00CDAD; }
            .mdef:hover { color: #FFF; background-color: #00CDAD; }

            .table table tr td { padding: 7px; }

            @media screen and (min-width: 1000px) {
                .mmenu { display: flex; flex-direction: row; flex-wrap: wrap; justify-content: flex-start; width: 100%; padding-right: 0; margin-top: 15px; }
                .mobilenav { display: none; }
            }
            @media screen and (max-width: 1000px) {
                .mmenu { display: none; }
                .mobilenav { display: block; width: 100%; }
                .nomobile { display: none; }
            }

            .table-title { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
            .financial-summary { padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
            .plan-value { text-align: center; font-size: 14px; color: #555; margin-bottom: 5px; font-weight: bold; }
            .plan-value span { font-size: 18px; color: #000; }
            .revenue-bar { display: flex; width: 100%; height: 60px; border-radius: 5px; overflow: hidden; border: 1px solid #aaa; font-size: 14px; font-weight: bold; color: white; }
            .revenue-bar-sold { width: 100%; background-color: rgba(40, 167, 69, 0.7); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 5px; box-sizing: border-box; }
            .bar-label { font-size: 12px; opacity: 0.9; }
            .bar-value { font-size: 16px; }
            .result-value { text-align: center; font-size: 18px; margin-top: 10px; font-weight: bold; }
            .stat-table { padding: 10px 0 0; }
			.mmenu li {
				display: inline;
				padding: 10px;
				margin: 5px;
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
                <a href="JavaScript:window.print();" class="stat-top__print"></a>
            </div>
            
            <?php
            // 1. Выводим блок с фильтрами
            $this->_render_filters();
            
            // 2. Генерируем данные для отчетов
            $financial_summary_html = $this->_generate_financial_summary();
            // В архивной версии все квартиры считаются проданными. Статусы [0, 2, 3, 4, 5, 6] - все возможные.
            $table_sold_html = $this->_generate_stats_table("Проданные квартиры", [0, 2, 3, 4, 5, 6]);
			
			    $booking_dates_report_html = $this->_generate_booking_dates_report();
				$monthly_area_report_html = $this->_generate_universal_sales_summary_report();
    

            ?>

            <!-- 3. Выводим сгенерированные отчеты -->
            <div class="stat-table stat-table-agency table">
                <?= $financial_summary_html ?>
            </div>
			
			
			
            <div class="stat-table stat-table-agency table">
                <?= $table_sold_html ?>
            </div>
			
			
			 <div class="stat-table stat-table-agency table">
                <?= $booking_dates_report_html ?>
            </div>
			
			  
        </div>
        <?php
    }

    /**
     * Переопределенный метод для рендеринга фильтров.
     */
 
	function _render_filters() {
		global $mysql;
		
		$kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

		$show_condition = "`show` = 0";

		// Данные для мобильного меню домов
		$sql_mobile_homes = "SELECT home_id, long_title FROM homes WHERE {$show_condition}";
		if ($kvartal_id > 0) {
			$sql_mobile_homes .= " AND CAST(kvartal AS UNSIGNED) = " . $kvartal_id;
		} elseif ($kvartal_id == -1) {
			$sql_mobile_homes .= " AND (kvartal IS NULL OR kvartal = '0' OR kvartal = '')";
		}
		// ИЗМЕНЕНО: Сортировка по названию дома для мобильного меню
		$sql_mobile_homes .= " ORDER BY `long_title` ASC"; 
		
		$mobile_homes_arr = $mysql->get_arr($sql_mobile_homes);
		?>
		<div class="noprint">
			
			<?php $this->_kvartal_menu(); ?>

			<!-- Десктопное меню домов (ul) -->
			<ul class="mmenu">
				<?php $this->_object_menux_s(); ?>
			</ul>

			<!-- Мобильное меню домов (select) -->
			<form id="obj_nav_form" method="GET" action="ctrind.php" class="mobilenav">
				<input type="hidden" name="ctr" value="<?=$this->ctr?>" />
				<input type="hidden" name="kvartal" value="<?=$kvartal_id?>" />
				<div class="objects-head-nav__select">
					<select name="home" onChange="this.form.submit();" style="width:100%; text-align: left; border-radius:0;">
						<option value="">Выбрать дом</option>
						<?php foreach($mobile_homes_arr as $v): ?>
							<option value="<?=$v['home_id']?>" <?=($v['home_id']==($_GET['home']??'')) ? 'selected' : ''?>>
								<?=htmlspecialchars($v['long_title'])?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
		</div>

		<?php 
		$this->_render_selection_title(); 
		?>
		
		<?php
	}

    /**
     * Выводит меню домов для десктопа с добавленной кнопкой "Все дома".
     */
function _object_menux_s() {
    global $mysql;

    $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
    $current_home_id = isset($_GET['home']) ? intval($_GET['home']) : 0;
    
    $show_condition = "`show` = 0";

    $sql = "SELECT home_id, title, `show` FROM homes 
            WHERE {$show_condition}";

    if ($kvartal_id > 0) {
        $sql .= " AND CAST(kvartal AS UNSIGNED) = " . $kvartal_id;
    } elseif ($kvartal_id == -1) {
        $sql .= " AND (kvartal IS NULL OR kvartal = '0' OR kvartal = '')";
    }

    // ИЗМЕНЕНО: Сортировка по названию дома
    $sql .= " ORDER BY `title` ASC";

    $homes = $mysql->get_arr($sql);
    
    $all_link_params = ['ctr' => $this->ctr, 'kvartal' => $kvartal_id];
    $all_link_href = 'ctrind.php?' . http_build_query($all_link_params);
    $all_class = !$current_home_id ? 'mdef mdefth' : 'mdef';
    ?>
    <li style="padding:0;">
        <a href="<?= htmlspecialchars($all_link_href) ?>" class="<?= $all_class ?>">Все дома</a>
    </li>
    <?php

    foreach ($homes as $v) {
        $class = 'mdef';
        if ($current_home_id == $v['home_id']) {
            $class .= ' mdefth';
        }
        ?>
        <li style="padding:0;">
            <a href="ctrind.php?ctr=<?=$this->ctr?>&home=<?=$v['home_id']?>&kvartal=<?=$kvartal_id?>" class="<?=$class?>">
                <?=htmlspecialchars($v['title'])?>
            </a>
        </li>
        <?php
    }
}

    /**
     * Выводит меню кварталов с пунктом "Дома без кварталов".
     */
    function _kvartal_menu() {
        global $mysql;
        $selected_kvartal = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
        
        $kvartals = parent::_get_kvartals(); 

        $show_condition = "`show` = 0";
        $sql_no_kvartal = "SELECT COUNT(home_id) as cnt FROM homes WHERE {$show_condition} AND (kvartal IS NULL OR kvartal = '0' OR kvartal = '')";
        
        $result_arr = $mysql->get_arr($sql_no_kvartal);
        $no_kvartal_count = isset($result_arr[0]['cnt']) ? $result_arr[0]['cnt'] : 0;

        ?>
        <div style="width:100%; margin-bottom:10px; margin-top:15px;">
            <a href="ctrind.php?ctr=<?=$this->ctr?>&kvartal=0" class="mdef <?=($selected_kvartal == 0) ? 'mdefth' : ''?>">Все кварталы</a>
            <?php foreach ($kvartals as $k): ?>
                <a href="ctrind.php?ctr=<?=$this->ctr?>&kvartal=<?=$k['homes_kvartal_id']?>" class="mdef <?=($selected_kvartal == $k['homes_kvartal_id']) ? 'mdefth' : ''?>"><?=htmlspecialchars($k['title'])?></a>
            <?php endforeach; ?>
            <?php if ($no_kvartal_count > 0): ?>
                 <a href="ctrind.php?ctr=<?=$this->ctr?>&kvartal=-1" class="mdef <?=($selected_kvartal == -1) ? 'mdefth' : ''?>">Другое</a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Генерирует HTML-код таблицы статистики по квартирам.
     */
  private function _generate_stats_table($title, $statuses) {
    global $mysql;
    ob_start();

    $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
    $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;
    $status_list_for_sql = implode(',', $statuses);
    
    $show_condition = $this->_get_show_condition();

    $sql = "
        SELECT REGEXP_SUBSTR(a.rooms, '[0-9]+') as roomsx,
               COUNT(*) as c, SUM(a.area) as summ_area, SUM(a.price) as summ_price,
               SUM(a.price / NULLIF(a.area, 0)) as sum_price_per_m2
        FROM apartaments a  
        LEFT JOIN homes h ON h.home_id = a.home_id
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE a.status2 IN ({$status_list_for_sql}) AND a.rooms > 0 AND {$show_condition} AND a.area > 0 ";

    if ($kvartal_id > 0) {
        $sql .= " AND kv.homes_kvartal_id = " . $kvartal_id;
    } elseif ($kvartal_id == -1) {
        $sql .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
    }
    if ($home_id) $sql .= " AND a.home_id = " . $home_id;
    $sql .= " GROUP BY roomsx ";

    $query_result = $mysql->get_arr($sql);
    ?>
    <div class="table-title"><?= htmlspecialchars($title) ?></div>
    <table width="100%">
        <tr><th>к</th><th>Количество</th><th>Площадь</th><th>Стоимость</th><th>Сред. ст. м<sup>2</sup></th></tr>
        <?php
        $summ_arr = ['c' => 0, 'area' => 0, 'price' => 0];
        foreach ($query_result as $r) {
            if (empty($r['roomsx'])) continue;
            // Средняя цена для каждой группы считается по-старому (цена группы / площадь группы)
            $avg_price_per_m2_group = ($r['summ_area'] ?? 0) > 0 ? ($r['summ_price'] ?? 0) / $r['summ_area'] : 0;
            $summ_arr['c'] += $r['c'];
            $summ_arr['area'] += $r['summ_area'];
            $summ_arr['price'] += $r['summ_price'];
            ?>
            <tr>
                <td><?=$r['roomsx']?></td><td><?=$r['c']?></td><td><?=number_format($r['summ_area'], 2, ',', ' ') ?></td>
                <td><?=number_format($r['summ_price'], 0, ',', ' ')?></td><td><?=number_format($avg_price_per_m2_group, 0, ',', ' ')?></td>
            </tr>
        <?php } 
        
        // --- ИСПРАВЛЕНИЕ ЗДЕСЬ ---
        // Старый неверный расчет: $avg_metr_ = count($avg_metr_list) > 0 ? array_sum($avg_metr_list) / count($avg_metr_list) : 0;
        // Новый верный расчет: общая стоимость / общая площадь
        $avg_metr_ = ($summ_arr['area'] > 0) ? $summ_arr['price'] / $summ_arr['area'] : 0;
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





/**
 * Генерирует УНИВЕРСАЛЬНЫЙ сводный отчет по продажам.
 *
 * Логика "Продано":
 * - Для домов с `show = 0` (архив): все квартиры считаются проданными.
 * - Для домов с `show > 0` (активные): только квартиры с `status2 = 3` считаются проданными.
 *
 * Отчет включает строку "Продано без даты" для корректного итогового подсчета.
 */
/**
 * Генерирует УНИВЕРСАЛЬНЫЙ сводный отчет по продажам, включая ЛИНЕЙНЫЙ ГРАФИК.
 *
 * Логика "Продано":
 * - Для домов с `show = 0` (архив): все квартиры считаются проданными.
 * - Для домов с `show > 0` (активные): только квартиры с `status2 = 3` считаются проданными.
 */
 
private function _generate_universal_sales_summary_report() {
    global $mysql;
    ob_start();

    // Фильтры
    $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
    $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;
    $sold_condition = "((h.show = 0) OR (h.show > 0 AND a.status2 = 3))";
    
    // --- ЗАПРОС 1: Данные для ТАБЛИЦЫ (остается без изменений) ---
    $sql_for_table = "
        SELECT
            YEAR(dates.last_sale_date) as sales_year, MONTH(dates.last_sale_date) as sales_month,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 1 THEN 1 END) as count_1k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 1 THEN a.area END) as area_1k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 1 THEN a.price END) as price_1k,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 2 THEN 1 END) as count_2k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 2 THEN a.area END) as area_2k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 2 THEN a.price END) as price_2k,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 3 THEN 1 END) as count_3k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 3 THEN a.area END) as area_3k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 3 THEN a.price END) as price_3k,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') >= 4 THEN 1 END) as count_4k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') >= 4 THEN a.area END) as area_4k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') >= 4 THEN a.price END) as price_4k
        FROM apartaments a
        JOIN homes h ON a.home_id = h.home_id
        JOIN (
            SELECT apartament_id, MAX(date) as last_sale_date
            FROM broni WHERE date IS NOT NULL AND date != '0000-00-00 00:00:00' GROUP BY apartament_id
        ) AS dates ON a.apartament_id = dates.apartament_id
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE {$sold_condition}";
    if ($kvartal_id > 0) $sql_for_table .= " AND kv.homes_kvartal_id = " . $kvartal_id; elseif ($kvartal_id == -1) $sql_for_table .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
    if ($home_id) $sql_for_table .= " AND h.home_id = " . $home_id;
    $sql_for_table .= " GROUP BY sales_year, sales_month ORDER BY sales_year ASC, sales_month ASC";

    // --- ЗАПРОС 2: Продажи БЕЗ ДАТЫ (остается без изменений) ---
    $sql_without_date = "
        SELECT
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 1 THEN 1 END) as count_1k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 1 THEN a.price END) as price_1k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 1 THEN a.area END) as area_1k,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 2 THEN 1 END) as count_2k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 2 THEN a.price END) as price_2k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 2 THEN a.area END) as area_2k,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 3 THEN 1 END) as count_3k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 3 THEN a.price END) as price_3k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') = 3 THEN a.area END) as area_3k,
            COUNT(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') >= 4 THEN 1 END) as count_4k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') >= 4 THEN a.price END) as price_4k, SUM(CASE WHEN REGEXP_SUBSTR(a.rooms, '[0-9]+') >= 4 THEN a.area END) as area_4k
        FROM apartaments a
        JOIN homes h ON a.home_id = h.home_id
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE {$sold_condition} AND a.apartament_id NOT IN (SELECT DISTINCT apartament_id FROM broni)";
    if ($kvartal_id > 0) $sql_without_date .= " AND kv.homes_kvartal_id = " . $kvartal_id; elseif ($kvartal_id == -1) $sql_without_date .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
    if ($home_id) $sql_without_date .= " AND h.home_id = " . $home_id;

    $results_with_date = $mysql->get_arr($sql_for_table);
    $results_without_date_arr = $mysql->get_arr($sql_without_date);
    $dateless_sales = $results_without_date_arr[0] ?? null;

    $sales_data = [];
    if (!empty($results_with_date)) {
        foreach($results_with_date as $row) { $sales_data[$row['sales_year']][$row['sales_month']] = $row; }
    }
    
    // ... (весь блок отрисовки таблицы остается без изменений) ...
    function format_cell($count, $area, $price) {
        if ($count == 0 && $area == 0 && $price == 0) return '';
        return sprintf("%s шт<br>%s м²<br><b>%s т.р.</b>", number_format($count), number_format($area, 2, ',', ' '), number_format($price / 1000, 0, ',', ' '));
    }
    $grand_totals = array_fill_keys(['count_1k', 'area_1k', 'price_1k', 'count_2k', 'area_2k', 'price_2k', 'count_3k', 'area_3k', 'price_3k', 'count_4k', 'area_4k', 'price_4k'], 0);
    $months_names = [1=>'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    ?>
    <div class="table-title">Месячная динамика продаж по типам квартир</div>
    <table width="100%" style="text-align:center;">
        <tr style="vertical-align:top;"><th>Период</th><th>1-комнатные</th><th>2-комнатные</th><th>3-комнатные</th><th>4-комнатные и более</th><th>Итого за период</th></tr>
        <?php
        if (empty($sales_data) && empty($dateless_sales['count_1k'])) {
            echo '<tr><td colspan="6">Нет данных о продажах для выбранных объектов.</td></tr>';
        } else {
            if(!empty($sales_data)) {
                $start_year = key($sales_data); $end_year = key(array_slice($sales_data, -1, 1, true));
                for ($year = $start_year; $year <= $end_year; $year++) {
                    $year_totals = array_fill_keys(array_keys($grand_totals), 0); $year_has_data = false;
                    for ($month = 1; $month <= 12; $month++) {
                        if (isset($sales_data[$year][$month])) {
                            $year_has_data = true; $row = $sales_data[$year][$month];
                            foreach (array_keys($grand_totals) as $key) { $year_totals[$key] += ($row[$key] ?? 0); $grand_totals[$key] += ($row[$key] ?? 0); }
                            $month_count = ($row['count_1k'] ?? 0) + ($row['count_2k'] ?? 0) + ($row['count_3k'] ?? 0) + ($row['count_4k'] ?? 0);
                            $month_area = ($row['area_1k'] ?? 0) + ($row['area_2k'] ?? 0) + ($row['area_3k'] ?? 0) + ($row['area_4k'] ?? 0);
                            $month_price = ($row['price_1k'] ?? 0) + ($row['price_2k'] ?? 0) + ($row['price_3k'] ?? 0) + ($row['price_4k'] ?? 0);
                            echo "<tr><td><b>{$year} {$months_names[$month]}</b></td><td>" . format_cell($row['count_1k'] ?? 0, $row['area_1k'] ?? 0, $row['price_1k'] ?? 0) . "</td><td>" . format_cell($row['count_2k'] ?? 0, $row['area_2k'] ?? 0, $row['price_2k'] ?? 0) . "</td><td>" . format_cell($row['count_3k'] ?? 0, $row['area_3k'] ?? 0, $row['price_3k'] ?? 0) . "</td><td>" . format_cell($row['count_4k'] ?? 0, $row['area_4k'] ?? 0, $row['price_4k'] ?? 0) . "</td><td><b>" . format_cell($month_count, $month_area, $month_price) . "</b></td></tr>";
                        }
                    }
                    if($year_has_data){
                        $total_year_count = $year_totals['count_1k'] + $year_totals['count_2k'] + $year_totals['count_3k'] + $year_totals['count_4k'];
                        $total_year_area = $year_totals['area_1k'] + $year_totals['area_2k'] + $year_totals['area_3k'] + $year_totals['area_4k'];
                        $total_year_price = $year_totals['price_1k'] + $year_totals['price_2k'] + $year_totals['price_3k'] + $year_totals['price_4k'];
                        echo "<tr style='font-weight:bold; background-color:#e9e9e9; border-top: 2px solid #aaa; border-bottom: 2px solid #aaa;'><td>Итого за {$year} год</td><td>" . format_cell($year_totals['count_1k'], $year_totals['area_1k'], $year_totals['price_1k']) . "</td><td>" . format_cell($year_totals['count_2k'], $year_totals['area_2k'], $year_totals['price_2k']) . "</td><td>" . format_cell($year_totals['count_3k'], $year_totals['area_3k'], $year_totals['price_3k']) . "</td><td>" . format_cell($year_totals['count_4k'], $year_totals['area_4k'], $year_totals['price_4k']) . "</td><td>" . format_cell($total_year_count, $total_year_area, $total_year_price) . "</td></tr>";
                    }
                }
            }
            if(!empty($dateless_sales) && $dateless_sales['count_1k'] + $dateless_sales['count_2k'] + $dateless_sales['count_3k'] + $dateless_sales['count_4k'] > 0) {
                foreach (array_keys($grand_totals) as $key) { if(isset($dateless_sales[$key])) $grand_totals[$key] += $dateless_sales[$key]; }
                $dateless_count = $dateless_sales['count_1k'] + $dateless_sales['count_2k'] + $dateless_sales['count_3k'] + $dateless_sales['count_4k'];
                $dateless_area = $dateless_sales['area_1k'] + $dateless_sales['area_2k'] + $dateless_sales['area_3k'] + $dateless_sales['area_4k'];
                $dateless_price = $dateless_sales['price_1k'] + $dateless_sales['price_2k'] + $dateless_sales['price_3k'] + $dateless_sales['price_4k'];
                echo "<tr style='background-color:#f5f5f5;'><td style='font-weight:bold;'>Продано без даты</td><td>".format_cell($dateless_sales['count_1k'], $dateless_sales['area_1k'], $dateless_sales['price_1k'])."</td><td>".format_cell($dateless_sales['count_2k'], $dateless_sales['area_2k'], $dateless_sales['price_2k'])."</td><td>".format_cell($dateless_sales['count_3k'], $dateless_sales['area_3k'], $dateless_sales['price_3k'])."</td><td>".format_cell($dateless_sales['count_4k'], $dateless_sales['area_4k'], $dateless_sales['price_4k'])."</td><td style='font-weight:bold;'>".format_cell($dateless_count, $dateless_area, $dateless_price)."</td></tr>";
            }
            $total_grand_count = $grand_totals['count_1k'] + $grand_totals['count_2k'] + $grand_totals['count_3k'] + $grand_totals['count_4k'];
            $total_grand_area = $grand_totals['area_1k'] + $grand_totals['area_2k'] + $grand_totals['area_3k'] + $grand_totals['area_4k'];
            $total_grand_price = $grand_totals['price_1k'] + $grand_totals['price_2k'] + $grand_totals['price_3k'] + $grand_totals['price_4k'];
            echo "<tr style='font-weight:bold; background-color:#d0d0d0; font-size:1.1em;'><td>ВСЕГО</td><td>" . format_cell($grand_totals['count_1k'], $grand_totals['area_1k'], $grand_totals['price_1k']) . "</td><td>" . format_cell($grand_totals['count_2k'], $grand_totals['area_2k'], $grand_totals['price_2k']) . "</td><td>" . format_cell($grand_totals['count_3k'], $grand_totals['area_3k'], $grand_totals['price_3k']) . "</td><td>" . format_cell($grand_totals['count_4k'], $grand_totals['area_4k'], $grand_totals['price_4k']) . "</td><td>" . format_cell($total_grand_count, $total_grand_area, $total_grand_price) . "</td></tr>";
        }
        ?>
    </table>

    <?php
    // --- НАЧАЛО БЛОКА ГРАФИКА (С ИСПРАВЛЕНИЯМИ) ---
    $chart_labels = []; $chart_data_1k = []; $chart_data_2k = []; $chart_data_3k = []; $chart_data_4k = []; $chart_data_total = [];
    $months_short = [1=>'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

    if (!empty($sales_data)) {
        $start_year = key($sales_data);
        $end_year = key(array_slice($sales_data, -1, 1, true));
        $current_month_date = new DateTime("$start_year-".key($sales_data[$start_year])."-01");
        $end_month_date = new DateTime("$end_year-".key(array_slice($sales_data[$end_year], -1, 1, true))."-01");
        
        while($current_month_date <= $end_month_date) {
            $y = $current_month_date->format('Y');
            $m = (int)$current_month_date->format('n');
            $chart_labels[] = $months_short[$m] . ' ' . $y;
            
            $price_1k = $sales_data[$y][$m]['price_1k'] ?? 0;
            $price_2k = $sales_data[$y][$m]['price_2k'] ?? 0;
            $price_3k = $sales_data[$y][$m]['price_3k'] ?? 0;
            $price_4k = $sales_data[$y][$m]['price_4k'] ?? 0;
            
            $chart_data_1k[] = $price_1k;
            $chart_data_2k[] = $price_2k;
            $chart_data_3k[] = $price_3k;
            $chart_data_4k[] = $price_4k;
            $chart_data_total[] = $price_1k + $price_2k + $price_3k + $price_4k;
            
            $current_month_date->modify('+1 month');
        }
    }
    ?>
    <div class="table-title" style="margin-top: 30px;">График динамики продаж по месяцам</div>
    <div style="width: 100%; max-width: 1200px; margin: auto; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <canvas id="salesChart"></canvas>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [
                        {
                            label: 'Итого',
                            data: <?= json_encode($chart_data_total) ?>,
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.1
                        },
                        {
                            label: '1-комнатные',
                            data: <?= json_encode($chart_data_1k) ?>,
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 2
                        },
                        {
                            label: '2-комнатные',
                            data: <?= json_encode($chart_data_2k) ?>,
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2
                        },
                        {
                            label: '3-комнатные',
                            data: <?= json_encode($chart_data_3k) ?>,
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 2
                        },
                        {
                            label: '4-комнатные и более',
                            data: <?= json_encode($chart_data_4k) ?>,
                            borderColor: 'rgba(108, 117, 125, 1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top', labels: { font: { size: 14 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return new Intl.NumberFormat('ru-RU', { notation: 'compact' }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
	
	 
/**
 * Генерирует HTML-отчет по датам бронирования, скорости и стоимости продаж.
 *
 * ВАЖНО: Суммарная стоимость и средняя цена м² рассчитываются на основе
 * стабильных цен из таблицы `apartaments.price`.
 * ИСПРАВЛЕНО: Добавлена финальная группировка для устранения дублирования строк.
 *
 * Отсортирован по дате первой брони.
 */
private function _generate_booking_dates_report() {
    global $mysql;
    ob_start();

    // Получаем текущие фильтры из запроса
    $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
    $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
    $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;

    // Определяем, работаем с архивом или с активными домами
    $show_condition = $this->show_arch ? "h.`show` = 0" : "h.`show` > 0";

    // Обновленный SQL-запрос с финальной группировкой
    $sql = "
        SELECT
            h.title,
            dates.first_booking_date,
            dates.last_booking_date,
            apt_stats.total_apartment_count,
            apt_stats.total_price,
            apt_stats.total_area
        FROM homes h
        -- Подзапрос 1: Находим первую и последнюю дату брони
        LEFT JOIN (
            SELECT
                home_id,
                MIN(date) as first_booking_date,
                MAX(date) as last_booking_date
            FROM broni
            WHERE date IS NOT NULL AND date != '0000-00-00 00:00:00'
            GROUP BY home_id
        ) AS dates ON h.home_id = dates.home_id
        -- Подзапрос 2: Считаем все данные по квартирам (включая цену) из `apartaments`
        LEFT JOIN (
            SELECT
                home_id,
                COUNT(apartament_id) as total_apartment_count,
                SUM(price) as total_price,
                SUM(area) as total_area
            FROM apartaments
            GROUP BY home_id
        ) AS apt_stats ON h.home_id = apt_stats.home_id
        
        LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE 
            {$show_condition}
            AND dates.first_booking_date IS NOT NULL -- Показываем только дома, где были брони
    ";

    // Добавляем условия фильтрации
    if ($sdan == 0) $sql .= " AND h.complite = 0 ";
    elseif ($sdan == 1) $sql .= " AND h.complite = 1 ";
    
    if ($kvartal_id > 0) {
        $sql .= " AND kv.homes_kvartal_id = " . $kvartal_id;
    } elseif ($kvartal_id == -1) {
        $sql .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
    }

    if ($home_id) $sql .= " AND h.home_id = " . $home_id;

    // --- ИСПРАВЛЕНИЕ ЗДЕСЬ: Добавляем финальную группировку ---
    $sql .= " GROUP BY h.home_id, h.title, dates.first_booking_date, dates.last_booking_date, apt_stats.total_apartment_count, apt_stats.total_price, apt_stats.total_area";
    
    $sql .= " ORDER BY dates.first_booking_date ASC";

    $results = $mysql->get_arr($sql);
    ?>
    <div class="table-title">Сроки, скорость и стоимость продаж</div>
    <table width="100%">
        <tr>
            <th>Дом</th>
            <th>Первая бронь</th>
            <th>Последняя бронь</th>
            <th>Всего мес</th>
            <th>Кол-во квартир</th>
            <th>Скорость продажи (кв/мес)</th>
            <th>Суммарная стоимость</th>
            <th>Сред. ст. м²</th>
        </tr>
        <?php
        if (empty($results)) {
            echo '<tr><td colspan="8" style="text-align:center;">Нет данных по бронированию для выбранных объектов.</td></tr>';
        } else {
            foreach ($results as $row) {
                $first_date_str = $row['first_booking_date'];
                $last_date_str = $row['last_booking_date'];
                
                $apartment_count = (int)($row['total_apartment_count'] ?? 0);
                $total_price = (float)($row['total_price'] ?? 0);
                $total_area = (float)($row['total_area'] ?? 0);

                $months_diff_display = 'н/д';
                $sales_velocity_display = 'н/д';
                $total_price_display = 'н/д';
                $avg_price_display = 'н/д';

                if (!empty($first_date_str) && !empty($last_date_str)) {
                    try {
                        $date_first = new DateTime($first_date_str);
                        $date_last = new DateTime($last_date_str);
                        $interval = $date_first->diff($date_last);
                        $days_diff = $interval->days;

                        if ($days_diff >= 0) {
                            $months = $days_diff / 30.44; 
                            $months_diff_display = number_format($months, 2, ',', ' ');

                            if ($apartment_count > 0 && $months > 0) {
                                $velocity = $apartment_count / $months;
                                $sales_velocity_display = number_format($velocity, 2, ',', ' ');
                            } else if ($days_diff == 0 && $apartment_count > 0) {
                                $sales_velocity_display = '∞';
                            }
                        }

                    } catch (Exception $e) {
                        $months_diff_display = 'ошибка';
                    }
                }
                
                if ($total_price > 0) {
                    $total_price_display = number_format($total_price, 0, ',', ' ');
                }
                if ($total_area > 0) {
                    $avg_price = $total_price / $total_area;
                    $avg_price_display = number_format($avg_price, 0, ',', ' ');
                }

                $first_date_display = $first_date_str ? date('d.m.Y H:i', strtotime($first_date_str)) : 'нет';
                $last_date_display = $last_date_str ? date('d.m.Y H:i', strtotime($last_date_str)) : 'нет';

                ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $first_date_display ?></td>
                    <td><?= $last_date_display ?></td>
                    <td><?= $months_diff_display ?></td>
                    <td><?= $apartment_count ?></td>
                    <td><?= $sales_velocity_display ?></td>
                    <td><?= $total_price_display ?></td>
                    <td><?= $avg_price_display ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <?php
    return ob_get_clean();
}


    /**
     * Генерирует HTML-код для финансовой сводки с проверкой на наличие project_price.
     */
    private function _generate_financial_summary() {
        global $mysql;
        ob_start();
        $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
        $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;

        $show_condition = $this->_get_show_condition();

        // Запрос для расчета целевой цены и количества домов
        $sql_target = "
            SELECT 
                SUM(h.project_price) as target_price,
                COUNT(h.home_id) as total_homes_in_selection,
                COUNT(CASE WHEN h.project_price > 0 THEN h.home_id END) as homes_with_price
            FROM homes h
            LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
            WHERE {$show_condition} ";

        if ($kvartal_id > 0) {
            $sql_target .= " AND kv.homes_kvartal_id = " . $kvartal_id;
        } elseif ($kvartal_id == -1) {
             $sql_target .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
        }
        if ($home_id) $sql_target .= " AND h.home_id = " . $home_id;
        
        $target_result_arr = $mysql->get_arr($sql_target);
        $summary_data = $target_result_arr[0] ?? null;

        $target_price = !empty($summary_data['target_price']) ? (float)$summary_data['target_price'] : 0;
        $total_homes_in_selection = (int)($summary_data['total_homes_in_selection'] ?? 0);
        $homes_with_price = (int)($summary_data['homes_with_price'] ?? 0);
        
        $is_calculation_valid = ($total_homes_in_selection > 0 && $total_homes_in_selection == $homes_with_price);
        
        // Запрос для данных по квартирам.
        $sql_apartments = "
            SELECT 
                SUM(a.price) as total_sold_price,
                COUNT(a.apartament_id) as total_sold_count
            FROM apartaments a
            LEFT JOIN homes h ON h.home_id = a.home_id
            LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
            WHERE {$show_condition} AND a.area > 0 ";
        
        if ($kvartal_id > 0) {
            $sql_apartments .= " AND kv.homes_kvartal_id = " . $kvartal_id;
        } elseif ($kvartal_id == -1) {
             $sql_apartments .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
        }
        if ($home_id) $sql_apartments .= " AND a.home_id = " . $home_id;
        
        $apartments_result_arr = $mysql->get_arr($sql_apartments);
        $apartments_data = $apartments_result_arr[0] ?? [
            'total_sold_price' => 0,
            'total_sold_count' => 0
        ];
        
        $total_sold_price = $apartments_data['total_sold_price'];
        $total_sold_count = (int)$apartments_data['total_sold_count'];
        $total_potential_revenue = $total_sold_price;
        ?>

        <div class="financial-summary">
            <!-- Блок с баром, всегда виден -->
            <div class="revenue-bar">
                <div class="revenue-bar-sold">
                    <div class="bar-label">Продано <?= $total_sold_count ?> кв.</div>
                    <div class="bar-value">на <?= number_format($total_sold_price, 0, ',', ' ') ?></div>
                </div>
            </div>

            <?php 
            // --- НОВОЕ: Отображаем блок сравнения, только если есть плановая цена ---
            if ($target_price > 0): 
            ?>
                <!-- Заявленная сумма по финмодели -->
                <div class="plan-value" style="margin-top: 15px;">
                    Заявлено по финмодели: <span><?= number_format($target_price, 0, ',', ' ') ?></span>
                </div>

                <!-- Фактическая сумма по дому -->
                <div class="plan-value" style="margin-top: 5px;">
                    Фактическая сумма по дому: <span><?= number_format($total_potential_revenue, 0, ',', ' ') ?></span>
                </div>

                <?php 
                if ($is_calculation_valid): 
                    $projected_result = $total_potential_revenue - $target_price;
                    $projected_margin_percent = $target_price > 0 ? ($projected_result / $target_price) * 100 : 0;
                    $result_text = $projected_result >= 0 ? 'Выполнение финмодели: +' : 'Выполнение финмодели: -';
                    $sign = $projected_result >= 0 ? ' +' : ' -';
                ?>
                    <div class="result-value" style="color: <?= $projected_result >= 0 ? '#28a745' : '#dc3545' ?>;">
                        <?= $result_text . ' ' . number_format(abs($projected_result), 0, ',', ' ') . " (" . $sign . number_format(abs($projected_margin_percent), 2, ',', ' ') . "%)" ?>
                    </div>
                <?php elseif ($total_homes_in_selection > 0): ?>
                    <p style="display:none; text-align:center; margin-top:15px; color:#777; font-style: italic;">
                        Расчет рентабельности недоступен, так как суммы заявленные по финмодели не заданы для всех объектов в выборке.
                    </p>
                <?php endif; ?>
            
            <?php 
            endif; // --- КОНЕЦ НОВОГО УСЛОВНОГО БЛОКА ---
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}