<?php
 
/**
 * Контроллер для вывода универсального отчета по динамике продаж.
 * 
 * Наследует всю навигацию от `ctr__homes__` и содержит только свой
 * уникальный метод для генерации таблицы и графика.
 */
class ctr__stat_sales_dynamic extends ctr__homes__
{
    /**
     * @var string Название контроллера.
     */
    var $ctr = 'stat_sales_dynamic';

    /**
     * @var string Заголовок страницы.
     */
    var $title = 'Динамика продаж';

    /**
     * @var bool Включаем режим "показывать все", включая скрытые и архивные.
     * Эта строка активирует всю новую логику навигации из родительского класса.
     */
    protected $include_hidden = true;

    /**
     * Основной метод, формирующий и отображающий страницу.
     */
    function act__index()
    {
        global $t;
        $t['h1'] = 'Статистика продаж';

        ?>
     

     	<div id="ajaxcontent" class="stat">
        	<div class="stat-top">
				
				<div class="stat-top-filter">
					<a href="#" class="stat-top-btn btn btn_arrow-long" style="display:none;">ДЕТАЛЬНАЯ СТАТИСТИКА<i></i></a>
				</div>
				<!-- Панель поиска -->
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
				</div>
            
            <?php
            // Вызываем родительский метод, который отрисует всю навигацию
           // parent::_render_filters();
            
            // Генерируем и выводим наш уникальный отчет
            $sales_report_html = $this->_generate_universal_sales_summary_report();
            ?>

            <div class="stat-table stat-table-agency table">
                <?= $sales_report_html ?>
            </div>
        </div>
        <?php
    }

    /**
     * Вспомогательная функция для форматирования ячеек таблицы.
     */
    private function _format_cell_dynamics($count, $area, $price) {
        if ($count == 0 && $area == 0 && $price == 0) return '';
        return sprintf("%s %s / %s м²<br><b>%s т.р.</b>", number_format($count), unit_abbrev(), number_format($area, 2, ',', ' '), number_format($price / 1000, 0, ',', ' '));
    }

    /**
     * Генерирует УНИВЕРСАЛЬНЫЙ сводный отчет по продажам.
     */
    private function _generate_universal_sales_summary_report() {
        global $mysql;
        ob_start();

        // Фильтры
        $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
        $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;
        $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
        $sold_condition = "((h.show = 0) OR (h.show > 0 AND a.status2 = 3))";
        
        // ЗАПРОС 1: Продажи С ДАТОЙ
        $sql_with_date = "
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
        if ($sdan == 0) $sql_with_date .= " AND h.complite = 0 "; elseif ($sdan == 1) $sql_with_date .= " AND h.complite = 1 ";
        if ($kvartal_id > 0) $sql_with_date .= " AND kv.homes_kvartal_id = " . $kvartal_id; elseif ($kvartal_id == -1) $sql_with_date .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
        if ($home_id) $sql_with_date .= " AND h.home_id = " . $home_id;
        $sql_with_date .= " GROUP BY sales_year, sales_month ORDER BY sales_year ASC, sales_month ASC";

        // ЗАПРОС 2: Продажи БЕЗ ДАТЫ
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
        if ($sdan == 0) $sql_without_date .= " AND h.complite = 0 "; elseif ($sdan == 1) $sql_without_date .= " AND h.complite = 1 ";
        if ($kvartal_id > 0) $sql_without_date .= " AND kv.homes_kvartal_id = " . $kvartal_id; elseif ($kvartal_id == -1) $sql_without_date .= " AND (h.kvartal IS NULL OR h.kvartal = '0' OR h.kvartal = '')";
        if ($home_id) $sql_without_date .= " AND h.home_id = " . $home_id;

        $results_with_date = $mysql->get_arr($sql_with_date);
        $results_without_date_arr = $mysql->get_arr($sql_without_date);
        $dateless_sales = $results_without_date_arr[0] ?? null;
        $sales_data = [];
        if (!empty($results_with_date)) {
            foreach($results_with_date as $row) { $sales_data[$row['sales_year']][$row['sales_month']] = $row; }
        }
        
        $grand_totals = array_fill_keys(['count_1k', 'area_1k', 'price_1k', 'count_2k', 'area_2k', 'price_2k', 'count_3k', 'area_3k', 'price_3k', 'count_4k', 'area_4k', 'price_4k'], 0);
        $months_names = [1=>'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        ?>
		
		
		
		  <?php
        $chart_labels = []; $chart_data_1k = []; $chart_data_2k = []; $chart_data_3k = []; $chart_data_4k = []; $chart_data_total = [];
        $months_short = [1=>'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
        if (!empty($sales_data)) {
            $start_year = key($sales_data); $end_year = key(array_slice($sales_data, -1, 1, true));
            $current_month_date = new DateTime("$start_year-".key($sales_data[$start_year])."-01");
            $end_month_date = new DateTime("$end_year-".key(array_slice($sales_data[$end_year], -1, 1, true))."-01");
            while($current_month_date <= $end_month_date) {
                $y = $current_month_date->format('Y'); $m = (int)$current_month_date->format('n');
                $chart_labels[] = $months_short[$m] . ' ' . $y;
                $price_1k = $sales_data[$y][$m]['price_1k'] ?? 0; $price_2k = $sales_data[$y][$m]['price_2k'] ?? 0;
                $price_3k = $sales_data[$y][$m]['price_3k'] ?? 0; $price_4k = $sales_data[$y][$m]['price_4k'] ?? 0;
                $chart_data_1k[] = $price_1k; $chart_data_2k[] = $price_2k; $chart_data_3k[] = $price_3k; $chart_data_4k[] = $price_4k;
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
                if (typeof Chart !== 'undefined' && document.getElementById('salesChart')) {
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?= json_encode($chart_labels) ?>,
                            datasets: [
                                { label: 'Итого', data: <?= json_encode($chart_data_total) ?>, borderColor: 'rgba(220, 53, 69, 1)', backgroundColor: 'rgba(220, 53, 69, 0.1)', borderWidth: 3, fill: true, tension: 0.1 },
                                { label: '1-комнатные', data: <?= json_encode($chart_data_1k) ?>, borderColor: 'rgba(0, 123, 255, 1)', borderWidth: 2 },
                                { label: '2-комнатные', data: <?= json_encode($chart_data_2k) ?>, borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 2 },
                                { label: '3-комнатные', data: <?= json_encode($chart_data_3k) ?>, borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 2 },
                                { label: '4-комнатные и более', data: <?= json_encode($chart_data_4k) ?>, borderColor: 'rgba(108, 117, 125, 1)', borderWidth: 2 }
                            ]
                        },
                        options: { responsive: true, plugins: { legend: { position: 'top', labels: { font: { size: 14 } } }, tooltip: { callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.y !== null) { label += new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(context.parsed.y); } return label; } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(value, index, values) { return new Intl.NumberFormat('ru-RU', { notation: 'compact' }).format(value); } } } } }
                    });
                }
            });
        </script>
		
		
		
        <div class="table-title"><?= unit_phrase('monthly_sales') ?></div>
        <table width="100%" style="text-align:center;">
            <tr style="vertical-align:top;"><th>Период</th><th>1-комнатные</th><th>2-комнатные</th><th>3-комнатные</th><th>4-комнатные и более</th><th>Итого за период</th></tr>
            <?php
            if (empty($sales_data) && (empty($dateless_sales) || $dateless_sales['count_1k'] + $dateless_sales['count_2k'] + $dateless_sales['count_3k'] + $dateless_sales['count_4k'] == 0)) {
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
                                echo "<tr><td><b>{$year} {$months_names[$month]}</b></td><td>" . $this->_format_cell_dynamics($row['count_1k'] ?? 0, $row['area_1k'] ?? 0, $row['price_1k'] ?? 0) . "</td><td>" . $this->_format_cell_dynamics($row['count_2k'] ?? 0, $row['area_2k'] ?? 0, $row['price_2k'] ?? 0) . "</td><td>" . $this->_format_cell_dynamics($row['count_3k'] ?? 0, $row['area_3k'] ?? 0, $row['price_3k'] ?? 0) . "</td><td>" . $this->_format_cell_dynamics($row['count_4k'] ?? 0, $row['area_4k'] ?? 0, $row['price_4k'] ?? 0) . "</td><td><b>" . $this->_format_cell_dynamics($month_count, $month_area, $month_price) . "</b></td></tr>";
                            }
                        }
                        if($year_has_data){
                            $total_year_count = $year_totals['count_1k'] + $year_totals['count_2k'] + $year_totals['count_3k'] + $year_totals['count_4k'];
                            $total_year_area = $year_totals['area_1k'] + $year_totals['area_2k'] + $year_totals['area_3k'] + $year_totals['area_4k'];
                            $total_year_price = $year_totals['price_1k'] + $year_totals['price_2k'] + $year_totals['price_3k'] + $year_totals['price_4k'];
                            echo "<tr style='font-weight:bold; background-color:#e9e9e9; border-top: 2px solid #aaa; border-bottom: 2px solid #aaa;'><td>Итого за {$year} год</td><td>" . $this->_format_cell_dynamics($year_totals['count_1k'], $year_totals['area_1k'], $year_totals['price_1k']) . "</td><td>" . $this->_format_cell_dynamics($year_totals['count_2k'], $year_totals['area_2k'], $year_totals['price_2k']) . "</td><td>" . $this->_format_cell_dynamics($year_totals['count_3k'], $year_totals['area_3k'], $year_totals['price_3k']) . "</td><td>" . $this->_format_cell_dynamics($year_totals['count_4k'], $year_totals['area_4k'], $year_totals['price_4k']) . "</td><td>" . $this->_format_cell_dynamics($total_year_count, $total_year_area, $total_year_price) . "</td></tr>";
                        }
                    }
                }
                if(!empty($dateless_sales) && $dateless_sales['count_1k'] + $dateless_sales['count_2k'] + $dateless_sales['count_3k'] + $dateless_sales['count_4k'] > 0) {
                    foreach (array_keys($grand_totals) as $key) { if(isset($dateless_sales[$key])) $grand_totals[$key] += $dateless_sales[$key]; }
                    $dateless_count = $dateless_sales['count_1k'] + $dateless_sales['count_2k'] + $dateless_sales['count_3k'] + $dateless_sales['count_4k'];
                    $dateless_area = $dateless_sales['area_1k'] + $dateless_sales['area_2k'] + $dateless_sales['area_3k'] + $dateless_sales['area_4k'];
                    $dateless_price = $dateless_sales['price_1k'] + $dateless_sales['price_2k'] + $dateless_sales['price_3k'] + $dateless_sales['price_4k'];
                    echo "<tr style='background-color:#f5f5f5;'><td style='font-weight:bold;'>Продано без даты</td><td>".$this->_format_cell_dynamics($dateless_sales['count_1k'], $dateless_sales['area_1k'], $dateless_sales['price_1k'])."</td><td>".$this->_format_cell_dynamics($dateless_sales['count_2k'], $dateless_sales['area_2k'], $dateless_sales['price_2k'])."</td><td>".$this->_format_cell_dynamics($dateless_sales['count_3k'], $dateless_sales['area_3k'], $dateless_sales['price_3k'])."</td><td>".$this->_format_cell_dynamics($dateless_sales['count_4k'], $dateless_sales['area_4k'], $dateless_sales['price_4k'])."</td><td style='font-weight:bold;'>".$this->_format_cell_dynamics($dateless_count, $dateless_area, $dateless_price)."</td></tr>";
                }
                $total_grand_count = $grand_totals['count_1k'] + $grand_totals['count_2k'] + $grand_totals['count_3k'] + $grand_totals['count_4k'];
                $total_grand_area = $grand_totals['area_1k'] + $grand_totals['area_2k'] + $grand_totals['area_3k'] + $grand_totals['area_4k'];
                $total_grand_price = $grand_totals['price_1k'] + $grand_totals['price_2k'] + $grand_totals['price_3k'] + $grand_totals['price_4k'];
                echo "<tr style='font-weight:bold; background-color:#d0d0d0; font-size:1.1em;'><td>ВСЕГО</td><td>" . $this->_format_cell_dynamics($grand_totals['count_1k'], $grand_totals['area_1k'], $grand_totals['price_1k']) . "</td><td>" . $this->_format_cell_dynamics($grand_totals['count_2k'], $grand_totals['area_2k'], $grand_totals['price_2k']) . "</td><td>" . $this->_format_cell_dynamics($grand_totals['count_3k'], $grand_totals['area_3k'], $grand_totals['price_3k']) . "</td><td>" . $this->_format_cell_dynamics($grand_totals['count_4k'], $grand_totals['area_4k'], $grand_totals['price_4k']) . "</td><td>" . $this->_format_cell_dynamics($total_grand_count, $total_grand_area, $total_grand_price) . "</td></tr>";
            }
            ?>
        </table>
       
      
        <?php
        return ob_get_clean();
    }
}