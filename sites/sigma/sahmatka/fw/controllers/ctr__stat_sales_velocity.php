<?php
$GLOBALS['t']['title']='Жизненный цикл продаж';

class ctr__stat_lifecycle extends ctr__homes__
{ 
    var $ctr = 'stat_lifecycle';
    var $title = 'Анализ жизненного цикла продаж';

    function act__index()
    {
        global $t, $mysql;
        $t['h1'] = 'Анализ жизненного цикла продаж по домам';

        // 1. ПОЛУЧЕНИЕ ФИЛЬТРОВ
        $sdan = isset($_REQUEST['sdan']) ? intval($_REQUEST['sdan']) : 3;
        $kvartal_id = isset($_REQUEST['kvartal']) ? intval($_REQUEST['kvartal']) : 0;
        $home_id = isset($_REQUEST['home']) ? intval($_REQUEST['home']) : 0;
        $rooms = isset($_REQUEST['rooms']) ? intval($_REQUEST['rooms']) : 0;
        
        // 2. ПОДГОТОВКА ДАННЫХ
        $lifecycle_data = $this->_get_lifecycle_data($sdan, $kvartal_id, $home_id, $rooms);
        
        // 3. ОБРАБОТКА ДАННЫХ ДЛЯ ГРАФИКОВ
        $charts_data = $this->_prepare_charts_data_by_30_day_periods($lifecycle_data);

        // 4. ВЫВОД HTML
        ?>
        <style>
            .mdef{ padding:5px; padding-left:13px; padding-right:13px; font-weight:bold; font-size:18px; }	
            .mdefth{color:#FFF; background-color:#00CDAD;} .mdef:hover{color:#FFF; background-color:#00CDAD;}
            .charts-container { display: grid; grid-template-columns: 1fr; gap: 30px; margin-bottom: 30px; }
            .charts-container-2-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
            .chart-box { padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            @media screen and (min-width: 1000px) { .mmenu{ display:block; padding-right:0; margin-top:15px; display: flex; flex-direction: row; justify-content: space-between; width: 100%;} .mobilenav{display:none;} }
            @media screen and (max-width: 1000px) { .mmenu{ display:none; } .mobilenav{display:block; width:100%;} .nomobile{display:none;} .charts-container-2-cols { grid-template-columns: 1fr; } }
            @media print { .noprint { display: none; } }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <?php 
        parent::_render_filters(); 
        parent::_render_rooms_filter();
        ?>

        <div class="charts-container">
            <div class="chart-box"><canvas id="percentSalesChart"></canvas></div>
            <div class="chart-box"><canvas id="percentBookingsChart"></canvas></div>
            <div class="chart-box"><canvas id="absoluteSalesChart"></canvas></div>
            <div class="chart-box"><canvas id="absoluteBookingsChart"></canvas></div>
        </div>
        <div class="charts-container-2-cols">
             <div class="chart-box"><canvas id="avgTimeToSellChart"></canvas></div>
            <div class="chart-box"><canvas id="avgBookingsBeforeSaleChart"></canvas></div>
        </div>

        <script>
            const createLineChart = (canvasId, title, labels, datasets, isPercent = false) => {
                const options = {
                    responsive: true, interaction: { mode: 'index', intersect: false },
                    plugins: { title: { display: true, text: title, font: { size: 16 } } },
                    scales: {
                        x: { title: { display: true, text: 'Период (30 дней от старта)' } },
                        y: { beginAtZero: true }
                    }
                };
                if (isPercent) {
                    options.scales.y.max = 100;
                    options.scales.y.ticks = { callback: function(value) { return value + '%' } };
                }
                new Chart(document.getElementById(canvasId), { type: 'line', data: { labels: labels, datasets: datasets }, options: options });
            };
            
            const createBarChart = (canvasId, title, labels, datasets) => {
                 new Chart(document.getElementById(canvasId), {
                    type: 'bar', data: { labels: labels, datasets: datasets },
                    options: { indexAxis: 'y', responsive: true, plugins: { title: { display: true, text: title, font: { size: 16 } } } }
                });
            };

            createLineChart('percentSalesChart', '<?= unit_phrase('sales_pct') ?>', 
                <?= json_encode($charts_data['labels']) ?>, <?= json_encode($charts_data['percent_sales_datasets']) ?>, true);
            createLineChart('percentBookingsChart', '<?= unit_phrase('bookings_pct') ?>', 
                <?= json_encode($charts_data['labels']) ?>, <?= json_encode($charts_data['percent_bookings_datasets']) ?>, true);
            createLineChart('absoluteSalesChart', 'Продажи по 30-дневным периодам (шт/период)', 
                <?= json_encode($charts_data['labels']) ?>, <?= json_encode($charts_data['absolute_sales_datasets']) ?>);
            createLineChart('absoluteBookingsChart', 'Брони по 30-дневным периодам (шт/период)', 
                <?= json_encode($charts_data['labels']) ?>, <?= json_encode($charts_data['absolute_bookings_datasets']) ?>);
            
            createBarChart('avgTimeToSellChart', 'Среднее время до продажи (дней)', <?= json_encode($charts_data['summary_labels']) ?>, <?= json_encode($charts_data['avg_days_sell_datasets']) ?>);
            createBarChart('avgBookingsBeforeSaleChart', 'Среднее кол-во броней до продажи', <?= json_encode($charts_data['summary_labels']) ?>, <?= json_encode($charts_data['avg_bookings_datasets']) ?>);
        </script>
        <?php
    }

    private function _get_lifecycle_data($sdan, $kvartal_id, $home_id, $rooms) {
        global $mysql;
        $rooms_filter_sql = '';
        if ($rooms > 0) {
            $rooms_filter_sql = ($rooms < 4) ? "AND REGEXP_SUBSTR(a_inner.rooms, '^[0-9]+') = $rooms" : "AND REGEXP_SUBSTR(a_inner.rooms, '^[0-9]+') >= 4";
        }
        $sql = "
            SELECT
                h.home_id, h.title AS home_title,
                (SELECT COUNT(a_inner.apartament_id) FROM apartaments a_inner WHERE a_inner.home_id = h.home_id {$rooms_filter_sql}) as total_apartments,
                b.date AS event_date, b.status AS event_status, a.apartament_id,
                ps.project_start_date,
                DATEDIFF(b.date, ps.project_start_date) as days_from_start,
                bc.booking_count
            FROM broni b
            JOIN homes h ON b.home_id = h.home_id
            JOIN apartaments a ON b.apartament_id = a.apartament_id
            LEFT JOIN homes_kvartal kv ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
            LEFT JOIN (SELECT home_id, MIN(date_first) as project_start_date FROM broni WHERE date_first >= '2022-01-01' GROUP BY home_id) AS ps ON h.home_id = ps.home_id
            LEFT JOIN (SELECT apartament_id, COUNT(broni_id) AS booking_count FROM broni WHERE date >= '2022-01-01' GROUP BY apartament_id) AS bc ON a.apartament_id = bc.apartament_id
            WHERE ps.project_start_date IS NOT NULL AND b.date >= '2022-01-01' AND DATEDIFF(b.date, ps.project_start_date) >= 0
        ";
        if ($sdan == 0) $sql .= " AND h.complite = 0 "; elseif ($sdan == 1) $sql .= " AND h.complite = 1 ";
        if ($kvartal_id > 0) $sql .= " AND kv.homes_kvartal_id = " . $kvartal_id;
        if ($home_id > 0) $sql .= " AND h.home_id = " . $home_id;
        if ($rooms > 0) {
            if ($rooms < 4) { $sql .= " AND REGEXP_SUBSTR(a.rooms, '^[0-9]+') = " . $rooms; } 
            else { $sql .= " AND REGEXP_SUBSTR(a.rooms, '^[0-9]+') >= 4"; }
        }
        $sql .= " ORDER BY b.date ASC";
        return $mysql->get_arr($sql);
    }

    private function _prepare_charts_data_by_30_day_periods($data) {
        if (empty($data)) {
            return [ /* Пустые данные */ ];
        }

        $events_by_period = [];
        $homes = [];
        $max_period = 0;

        foreach ($data as $row) {
            $period = floor($row['days_from_start'] / 30);
            $home_id = $row['home_id'];
            if (!isset($homes[$home_id])) {
                $homes[$home_id] = ['title' => $row['home_title'], 'total_apartments' => (int)$row['total_apartments']];
            }
            $events_by_period[$period][] = $row;
            if ($period > $max_period) $max_period = $period;
        }
        
        $chart_labels = range(0, $max_period);
        $home_stats_timeline = [];
        $colors = ['#00CDAD', '#FF8A90', '#5b9bd5', '#ffc000', '#70ad47', '#4472c4', '#ed7d31'];
        $i = 0;

        foreach ($homes as $home_id => $home_data) {
            $home_stats_timeline[$home_id] = [
                'title' => $home_data['title'], 'color' => $colors[$i++ % count($colors)],
                'total_apartments' => $home_data['total_apartments'],
                'percent_sales' => [], 'percent_bookings' => [], 'absolute_sales' => [], 'absolute_bookings' => [],
                'cumulative_sales' => 0, 'cumulative_bookings' => 0, 'sold_apartments' => [],
                'summary_total_days_to_sell' => 0, 'summary_total_bookings_before_sale' => 0,
            ];
        }

        for ($p = 0; $p <= $max_period; $p++) {
            $period_events = $events_by_period[$p] ?? [];
            $period_home_data = [];

            foreach ($period_events as $event) {
                $home_id = $event['home_id'];
                if (!isset($period_home_data[$home_id])) {
                    $period_home_data[$home_id] = ['sales_in_period' => 0, 'bookings_in_period' => 0];
                }
                $period_home_data[$home_id]['bookings_in_period']++;
                if ($event['event_status'] == 3 && !isset($home_stats_timeline[$home_id]['sold_apartments'][$event['apartament_id']])) {
                    $home_stats_timeline[$home_id]['sold_apartments'][$event['apartament_id']] = true;
                    $period_home_data[$home_id]['sales_in_period']++;
                    $home_stats_timeline[$home_id]['summary_total_days_to_sell'] += $event['days_from_start'];
                    $home_stats_timeline[$home_id]['summary_total_bookings_before_sale'] += $event['booking_count'];
                }
            }

            foreach ($homes as $home_id => $home_data) {
                $period_data = $period_home_data[$home_id] ?? null;
                $total_apartments = $home_stats_timeline[$home_id]['total_apartments'];
                $sales_in_period = $period_data['sales_in_period'] ?? 0;
                $bookings_in_period = $period_data['bookings_in_period'] ?? 0;
                $home_stats_timeline[$home_id]['absolute_sales'][] = $sales_in_period;
                $home_stats_timeline[$home_id]['absolute_bookings'][] = $bookings_in_period;
                $home_stats_timeline[$home_id]['cumulative_sales'] += $sales_in_period;
                $home_stats_timeline[$home_id]['cumulative_bookings'] += $bookings_in_period;
                $home_stats_timeline[$home_id]['percent_sales'][] = ($total_apartments > 0) ? round(($home_stats_timeline[$home_id]['cumulative_sales'] / $total_apartments) * 100, 1) : 0;
                $home_stats_timeline[$home_id]['percent_bookings'][] = ($total_apartments > 0) ? round(($home_stats_timeline[$home_id]['cumulative_bookings'] / $total_apartments) * 100, 1) : 0;
            }
        }
        
        $datasets = [];
        $summary_labels = []; $summary_avg_sell_data = []; $summary_avg_booking_data = [];

        foreach ($home_stats_timeline as $stats) {
            $datasets['percent_sales'][] = ['label' => $stats['title'], 'data' => $stats['percent_sales'], 'borderColor' => $stats['color'], 'tension' => 0.1, 'fill' => false];
            $datasets['percent_bookings'][] = ['label' => $stats['title'], 'data' => $stats['percent_bookings'], 'borderColor' => $stats['color'], 'tension' => 0.1, 'fill' => false];
            $datasets['absolute_sales'][] = ['label' => $stats['title'], 'data' => $stats['absolute_sales'], 'borderColor' => $stats['color'], 'tension' => 0.1, 'fill' => false];
            $datasets['absolute_bookings'][] = ['label' => $stats['title'], 'data' => $stats['absolute_bookings'], 'borderColor' => $stats['color'], 'tension' => 0.1, 'fill' => false];
            
            $sales_count = count($stats['sold_apartments']);
            $summary_labels[] = $stats['title'];
            $summary_avg_sell_data[] = ($sales_count > 0) ? round($stats['summary_total_days_to_sell'] / $sales_count) : 0;
            $summary_avg_booking_data[] = ($sales_count > 0) ? round($stats['summary_total_bookings_before_sale'] / $sales_count, 1) : 0;
        }

        return [
            'labels' => $chart_labels,
            'percent_sales_datasets' => $datasets['percent_sales'], 'percent_bookings_datasets' => $datasets['percent_bookings'],
            'absolute_sales_datasets' => $datasets['absolute_sales'], 'absolute_bookings_datasets' => $datasets['absolute_bookings'],
            'summary_labels' => $summary_labels,
            'avg_days_sell_datasets' => [['label' => 'Среднее кол-во дней', 'data' => $summary_avg_sell_data, 'backgroundColor' => 'rgba(0, 205, 173, 0.6)']],
            'avg_bookings_datasets' => [['label' => 'Среднее кол-во броней', 'data' => $summary_avg_booking_data, 'backgroundColor' => 'rgba(255, 159, 64, 0.6)']],
        ];
    }
}