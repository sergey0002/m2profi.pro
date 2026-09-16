<?php

/**
 * Импорт квартир TSV для homeseditor (#21).
 * Логика нумерации — копия disp_home (без правок core/classes/classes.php).
 */
class homes_import
{
    var $mysql;

    function __construct($mysql)
    {
        $this->mysql = $mysql;
    }

    function parse($raw_text)
    {
        $raw_text = $this->trim_bom((string) $raw_text);
        $lines = preg_split('/\r\n|\r|\n/', $raw_text);
        $rows = array();
        $line_no = 0;

        foreach ($lines as $line) {
            $line_no++;
            if ($this->clean_cell($line) === '') {
                continue;
            }
            if (strpos($line, "\t") === false) {
                $rows[] = array(
                    'line' => $line_no,
                    'cells' => null,
                    'errors' => array('Ожидался разделитель таб'),
                );
                continue;
            }
            $cells = explode("\t", $line);
            foreach ($cells as $k => $v) {
                $cells[$k] = $this->clean_cell($v);
            }
            if (count($cells) !== 7) {
                $rows[] = array(
                    'line' => $line_no,
                    'cells' => $cells,
                    'errors' => array('Нужно 7 колонок, получено ' . count($cells)),
                );
                continue;
            }
            $rows[] = array(
                'line' => $line_no,
                'cells' => $cells,
                'errors' => array(),
            );
        }

        return $rows;
    }

    function validate_rows($parsed_rows, $home_id_business, $existing_nums = array())
    {
        $seen = array();
        $existing = array();
        foreach ($existing_nums as $n) {
            $existing[(int) $n] = true;
        }

        foreach ($parsed_rows as &$item) {
            $item['ok'] = false;
            $item['normalized'] = null;
            $item['errors'] = isset($item['errors']) ? $item['errors'] : array();

            if ($item['cells'] === null || count($item['errors'])) {
                continue;
            }

            $norm = $this->normalize_row($item['cells']);
            $errors = array();

            if ($norm['floor'] === null || $norm['floor'] <= 0) {
                $errors[] = 'Некорректный этаж';
            }
            if ($norm['section_id'] === null || $norm['section_id'] <= 0) {
                $errors[] = 'Некорректная секция';
            }
            if ($norm['apartment_num'] === null || $norm['apartment_num'] <= 0) {
                $errors[] = 'Некорректный № квартиры';
            }
            if ($norm['area'] === null || (float) $norm['area'] <= 0) {
                $errors[] = 'Некорректная площадь';
            }
            if ($norm['area_small'] === null || (float) $norm['area_small'] < 0) {
                $errors[] = 'Некорректная площадь по договору';
            }
            if ($norm['price'] === null || $norm['price'] < 0) {
                $errors[] = 'Некорректная цена';
            }
            if ($norm['rooms'] === '') {
                $errors[] = 'Пустое поле комнат';
            } elseif (mb_strlen($norm['rooms']) > 20) {
                $errors[] = 'Слишком длинное поле комнат';
            }

            if (!$errors && isset($seen[$norm['apartment_num']])) {
                $errors[] = 'Дубль № квартиры во вставке';
            }
            if (!$errors && isset($existing[$norm['apartment_num']])) {
                $errors[] = 'Уже есть в доме';
            }

            if (!$errors) {
                $seen[$norm['apartment_num']] = true;
                $item['ok'] = true;
                $item['normalized'] = $norm;
            } else {
                $item['errors'] = array_merge($item['errors'], $errors);
            }
        }
        unset($item);

        return $parsed_rows;
    }

    function preview_columns()
    {
        return array(
            'floor' => 'Этаж',
            'rooms' => 'Комнаты',
            'section_id' => 'Секция',
            'apartment_num' => '№ квартиры',
            'area' => 'Площадь',
            'area_small' => 'Площадь по договору',
            'price' => 'Цена',
        );
    }

    function save_section_cl($homes_sections_id, $cl)
    {
        $homes_sections_id = (int) $homes_sections_id;
        $this->mysql->sql('DELETE FROM homes_sections_cl WHERE homes_sections_id="' . $homes_sections_id . '"');
        $count = 0;

        if (!is_array($cl)) {
            return 0;
        }

        foreach ($cl as $floor => $cells) {
            if (!is_array($cells)) {
                continue;
            }
            foreach ($cells as $k => $v) {
                if (!$v) {
                    continue;
                }
                $this->mysql->insert('homes_sections_cl', array(
                    'homes_sections_id' => $homes_sections_id,
                    'floor' => (int) $floor,
                    'appart' => (int) $k,
                ), 1);
                $count++;
            }
        }

        return $count;
    }

    function approve($homes_id_pk, $home_id_business, $green_rows)
    {
        $report = array(
            'apartments' => 0,
            'sections' => 0,
            'holes' => 0,
            'floor_updated' => false,
            'errors' => array(),
            'warnings' => array(),
        );

        if (!$green_rows) {
            return $report;
        }

        if (!$this->has_area_small()) {
            $report['errors'][] = 'В таблице apartaments нет колонки area_small — импорт остановлен.';
            return $report;
        }

        $this->sync_home_floor($homes_id_pk, $green_rows, $report);

        $section_stats = $this->build_section_stats($green_rows);
        $by_section_floor = $this->group_by_section_floor($green_rows);

        foreach ($section_stats as $section_id => $stats) {
            $homes_sections_id = $this->upsert_section($homes_id_pk, $section_id, $stats, $report);

            $start_num = $this->sync_section_start_num($homes_id_pk, $section_id, $homes_sections_id);

            $section_row = $this->mysql->get_for_key('homes_sections', 'homes_sections_id', $homes_sections_id);
            $floor_max = (int) $section_row['floor'];
            $apartments = (int) $section_row['apartments'];

            $import_by_floor = isset($by_section_floor[$section_id]) ? $by_section_floor[$section_id] : array();
            $existing_cl = $this->load_section_cl($homes_sections_id);
            $infer = $this->infer_cl($floor_max, $apartments, $start_num, $import_by_floor, $existing_cl);
            $report['holes'] += $this->save_section_cl($homes_sections_id, $infer['cl']);
            $gap_warnings = $this->import_gap_warnings($section_id, $import_by_floor);
            if ($gap_warnings) {
                $report['warnings'] = array_merge($report['warnings'], $gap_warnings);
            }
        }

        $existing = $this->mysql->get_arr(
            'SELECT apartment_num FROM apartaments WHERE home_id="' . (int) $home_id_business . '"'
        );
        $existing_nums = array();
        if (is_array($existing)) {
            foreach ($existing as $r) {
                $existing_nums[(int) $r['apartment_num']] = true;
            }
        }

        foreach ($green_rows as $row) {
            $num = (int) $row['apartment_num'];
            if (isset($existing_nums[$num])) {
                $report['errors'][] = 'Пропуск №' . $num . ': уже есть в доме';
                continue;
            }

            $insert = array(
                'home_id' => (int) $home_id_business,
                'section_id' => (int) $row['section_id'],
                'apartment_num' => $num,
                'floor' => (int) $row['floor'],
                'price' => (int) $row['price'],
                'area' => $row['area'],
                'area_small' => $row['area_small'],
                'rooms' => $row['rooms'],
                'kitchen_area' => '0',
                'text' => '',
                'house_adress' => '',
                'adress' => '',
                'status' => '2',
                'status2' => '2',
                'image_pb' => $this->image_pb(
                    $home_id_business,
                    $row['section_id'],
                    $row['floor'],
                    $row['area']
                ),
            );

            if ($this->mysql->insert('apartaments', $insert)) {
                $report['apartments']++;
                $existing_nums[$num] = true;
            } else {
                $report['errors'][] = 'Не удалось вставить №' . $num;
            }
        }

        return $report;
    }

    function trim_bom($text)
    {
        if (strncmp($text, "\xEF\xBB\xBF", 3) === 0) {
            return substr($text, 3);
        }
        return $text;
    }

    function clean_cell($value)
    {
        $value = str_replace(array('"', "\t"), '', (string) $value);
        $value = preg_replace('/\x{00A0}/u', ' ', $value);
        return trim($value);
    }

    function normalize_int($value)
    {
        $value = $this->clean_cell($value);
        $value = preg_replace('/\s+/', '', $value);
        $value = ltrim($value, '0');
        if ($value === '') {
            $value = '0';
        }
        if (!preg_match('/^\d+$/', $value)) {
            return null;
        }
        return (int) $value;
    }

    function normalize_float($value)
    {
        $value = $this->clean_cell($value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/\s+/', '', $value);
        if (!preg_match('/^\d+(\.\d+)?$/', $value)) {
            return null;
        }
        return $value;
    }

    function normalize_row($cells)
    {
        return array(
            'floor' => $this->normalize_int($cells[0]),
            'rooms' => $this->clean_cell($cells[1]),
            'section_id' => $this->normalize_int($cells[2]),
            'apartment_num' => $this->normalize_int($cells[3]),
            'area' => $this->normalize_float($cells[4]),
            'area_small' => $this->normalize_float($cells[5]),
            'price' => $this->normalize_int($cells[6]),
        );
    }

    function simulate_numbers($floor_max, $apartments, $start_num, $clean_apartments)
    {
        $ckv = 0;
        for ($i = 1; $i <= $floor_max; $i++) {
            for ($k = 1; $k <= $apartments; $k++) {
                if (!empty($clean_apartments[$i][$k])) {
                    $ckv++;
                }
            }
        }

        $endnum = ($floor_max * $apartments) + (int) $start_num - $ckv;
        $by_floor = array();

        for ($i = $floor_max; $i >= 1; $i--) {
            $nezk = 0;
            if (!empty($clean_apartments[$i]) && is_array($clean_apartments[$i])) {
                $nezk = count($clean_apartments[$i]);
            }
            $end_etza_num = $endnum - $apartments - 1 + $nezk;

            for ($k = 0; $k <= $apartments; $k++) {
                // Как disp_home: для k=0 и жилых k — end_etza_num++, для «пусто» — нет
                if (empty($clean_apartments[$i][$k])) {
                    $end_etza_num++;
                }

                if ($k === 0) {
                    continue;
                }

                if (!empty($clean_apartments[$i][$k])) {
                    continue;
                }

                $by_floor[$i][$k] = $end_etza_num;
                $endnum--;
            }
        }

        return $by_floor;
    }

    function load_section_cl($homes_sections_id)
    {
        $cl = array();
        $rows = $this->mysql->get_arr(
            'SELECT floor, appart FROM homes_sections_cl WHERE homes_sections_id="' . (int) $homes_sections_id . '"'
        );
        if (!is_array($rows)) {
            return $cl;
        }
        foreach ($rows as $row) {
            $cl[(int) $row['floor']][(int) $row['appart']] = 1;
        }
        return $cl;
    }

    function infer_cl($floor_max, $apartments, $start_num, $import_by_floor, $existing_cl = array())
    {
        $cl = is_array($existing_cl) ? $existing_cl : array();

        for ($i = $floor_max; $i >= 1; $i--) {
            if (empty($import_by_floor[$i])) {
                continue;
            }

            $want = array_unique(array_map('intval', $import_by_floor[$i]));
            sort($want);

            $best_mask = null;
            $best_holes = -1;
            $best_first_living = $apartments + 1;
            $best_max_hole_k = 0;

            $max_mask = 1 << $apartments;
            for ($mask = 0; $mask < $max_mask; $mask++) {
                $trial = $cl;
                for ($k = 1; $k <= $apartments; $k++) {
                    unset($trial[$i][$k]);
                }
                for ($k = 1; $k <= $apartments; $k++) {
                    if ($mask & (1 << ($k - 1))) {
                        $trial[$i][$k] = 1;
                    }
                }

                $sim = $this->simulate_numbers($floor_max, $apartments, $start_num, $trial);
                $got = isset($sim[$i]) ? array_values($sim[$i]) : array();
                sort($got);

                if ($got === $want) {
                    $holes = substr_count(decbin($mask), '1');
                    $first_living = $apartments + 1;
                    $max_hole_k = 0;
                    for ($k = 1; $k <= $apartments; $k++) {
                        if ($mask & (1 << ($k - 1))) {
                            $max_hole_k = max($max_hole_k, $k);
                        } elseif ($first_living > $apartments) {
                            $first_living = $k;
                        }
                    }
                    if ($best_mask === null
                        || $holes < $best_holes
                        || ($holes === $best_holes && $first_living < $best_first_living)
                        || ($holes === $best_holes && $first_living === $best_first_living && $max_hole_k > $best_max_hole_k)) {
                        $best_mask = $mask;
                        $best_holes = $holes;
                        $best_first_living = $first_living;
                        $best_max_hole_k = $max_hole_k;
                    }
                }
            }

            if ($best_mask !== null) {
                for ($k = 1; $k <= $apartments; $k++) {
                    unset($cl[$i][$k]);
                }
                for ($k = 1; $k <= $apartments; $k++) {
                    if ($best_mask & (1 << ($k - 1))) {
                        $cl[$i][$k] = 1;
                    }
                }
            }
        }

        return array(
            'cl' => $cl,
        );
    }

    /**
     * Пропуски номеров квартир на одном этаже в данных импорта (не сообщения про сетку «дыр»).
     */
    function import_gap_warnings($section_id, $import_by_floor)
    {
        $warnings = array();
        $section_id = (int) $section_id;

        foreach ($import_by_floor as $floor => $nums) {
            $floor = (int) $floor;
            $nums = array_values(array_unique(array_map('intval', $nums)));
            sort($nums);
            if (count($nums) < 2) {
                continue;
            }

            $missing = array();
            for ($n = $nums[0]; $n <= $nums[count($nums) - 1]; $n++) {
                if (!in_array($n, $nums, true)) {
                    $missing[] = $n;
                }
            }

            if (!$missing) {
                continue;
            }

            $cnt = count($missing);
            $warnings[] = 'Секция ' . $section_id . ', этаж ' . $floor
                . ': отсутствуют ' . $cnt . ' '
                . ($cnt === 1 ? 'квартира' : 'квартиры')
                . ' (№' . implode(', №', $missing) . ')';
        }

        return $warnings;
    }

    function build_section_stats($green_rows)
    {
        $stats = array();
        foreach ($green_rows as $row) {
            $sid = (int) $row['section_id'];
            $fl = (int) $row['floor'];
            if (!isset($stats[$sid])) {
                $stats[$sid] = array(
                    'max_floor' => 0,
                    'per_floor' => array(),
                );
            }
            $stats[$sid]['max_floor'] = max($stats[$sid]['max_floor'], $fl);
            if (!isset($stats[$sid]['per_floor'][$fl])) {
                $stats[$sid]['per_floor'][$fl] = 0;
            }
            $stats[$sid]['per_floor'][$fl]++;
        }
        foreach ($stats as $sid => $s) {
            $stats[$sid]['apartments'] = max($s['per_floor']);
        }
        return $stats;
    }

    function group_by_section_floor($green_rows)
    {
        $by_section = array();
        foreach ($green_rows as $row) {
            $sid = (int) $row['section_id'];
            $fl = (int) $row['floor'];
            if (!isset($by_section[$sid])) {
                $by_section[$sid] = array();
            }
            if (!isset($by_section[$sid][$fl])) {
                $by_section[$sid][$fl] = array();
            }
            $by_section[$sid][$fl][] = (int) $row['apartment_num'];
        }
        return $by_section;
    }

    function has_area_small()
    {
        $row = $this->mysql->get_arr("SHOW COLUMNS FROM apartaments LIKE 'area_small'", 1);
        return !empty($row);
    }

    function image_pb($home_id, $section_id, $floor, $area)
    {
        $base = function_exists('get_app_url') ? rtrim(get_app_url(), '/') : '';
        if ($base === '') {
            $base = 'https://em.m2profi.pro';
        }
        return $base . '/sahmatka/pbplans/' . (int) $home_id . '/' . (int) $section_id . '/' . (int) $floor . '/' . $area . '.svg';
    }

    function upsert_section($homes_id_pk, $section_id, $stats, &$report)
    {
        $homes_id_pk = (int) $homes_id_pk;
        $section_id = (int) $section_id;

        $existing = $this->mysql->get_arr(
            'SELECT * FROM homes_sections WHERE homes_id="' . $homes_id_pk . '" AND section_id="' . $section_id . '" LIMIT 1',
            1
        );

        $data = array(
            'homes_id' => $homes_id_pk,
            'section_id' => $section_id,
            'floor' => (int) $stats['max_floor'],
            'apartments' => (int) $stats['apartments'],
        );

        if ($existing) {
            if (trim((string) ($existing['caption'] ?? '')) === '') {
                $data['caption'] = 'Секция №' . $section_id;
            }
            $this->mysql->update_for_key('homes_sections', 'homes_sections_id', $existing['homes_sections_id'], $data);
            return (int) $existing['homes_sections_id'];
        }

        $data['caption'] = 'Секция №' . $section_id;
        $data['start_num'] = $this->compute_start_num($homes_id_pk, $section_id);
        $report['sections']++;
        return (int) $this->mysql->insert('homes_sections', $data);
    }

    /**
     * Пересчитать start_num секции по предыдущим секциям (после infer cl у них) и сохранить в БД.
     */
    function sync_section_start_num($homes_id_pk, $section_id, $homes_sections_id)
    {
        $section_id = (int) $section_id;
        $homes_sections_id = (int) $homes_sections_id;
        $start_num = $this->compute_start_num($homes_id_pk, $section_id);
        $this->mysql->update_for_key('homes_sections', 'homes_sections_id', $homes_sections_id, array(
            'start_num' => $start_num,
        ));

        return $start_num;
    }

    function compute_start_num($homes_id_pk, $section_id)
    {
        $homes_id_pk = (int) $homes_id_pk;
        $section_id = (int) $section_id;
        if ($section_id <= 1) {
            return 0;
        }

        $prev = $this->mysql->get_arr(
            'SELECT * FROM homes_sections WHERE homes_id="' . $homes_id_pk . '" AND section_id<"'.$section_id.'" ORDER BY section_id'
        );
        if (!$prev) {
            return 0;
        }

        $start = 0;
        foreach ($prev as $sec) {
            $sid = (int) $sec['homes_sections_id'];
            $holes_row = $this->mysql->get_arr(
                'SELECT COUNT(*) AS c FROM homes_sections_cl WHERE homes_sections_id="' . $sid . '"',
                1
            );
            $holes = (int) ($holes_row['c'] ?? 0);
            $start = ((int) $sec['floor'] * (int) $sec['apartments']) + (int) $sec['start_num'] - $holes;
        }
        return max(0, $start);
    }

    function sync_home_floor($homes_id_pk, $green_rows, &$report)
    {
        $home = $this->mysql->get_for_key('homes', 'homes_id', (int) $homes_id_pk);
        if (!$home) {
            return;
        }

        $current = trim((string) ($home['floor'] ?? ''));
        if ($current !== '' && $current !== '0') {
            return;
        }

        $max_floor = 0;
        foreach ($green_rows as $row) {
            $max_floor = max($max_floor, (int) $row['floor']);
        }
        if ($max_floor <= 0) {
            return;
        }

        $this->mysql->update_for_key('homes', 'homes_id', (int) $homes_id_pk, array(
            'floor' => (string) $max_floor,
        ));
        $report['floor_updated'] = true;
    }
}
