<?
class ctr__facades extends ctr__
{
    var $table = 'facade_polygons';
    var $key_filed = 'facade_polygon_id';
    var $ctr = 'facades';
    var $title = 'Разметка фасадов';

    const FACADE_IMAGE_EXT_PRIORITY = ['jpg', 'jpeg', 'png', 'webp'];
    const MAX_UPLOAD_BYTES = 20 * 1024 * 1024;

    function require_admin()
    {
        if (!check_access('admin')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Ошибка доступа']);
            exit;
        }
    }

    /** @return string абсолютный путь к каталогу fasades/ */
    function fasades_dir()
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'fasades';
    }

    /**
     * Найти файл фасада среди поддерживаемых расширений.
     * @return array{ext:string, path:string}|null
     */
    function facade_image_resolve($home_id)
    {
        $home_id = (int) $home_id;
        $dir = $this->fasades_dir();
        foreach (self::FACADE_IMAGE_EXT_PRIORITY as $ext) {
            $path = $dir . DIRECTORY_SEPARATOR . $home_id . '.' . $ext;
            if (is_file($path)) {
                return ['ext' => $ext, 'path' => $path];
            }
        }
        return null;
    }

    /** @return string абсолютный путь к файлу фасада (или ожидаемый .jpg, если файла нет) */
    function facade_image_fs_path($home_id)
    {
        $found = $this->facade_image_resolve($home_id);
        if ($found) {
            return $found['path'];
        }
        return $this->fasades_dir() . DIRECTORY_SEPARATOR . (int) $home_id . '.jpg';
    }

    function facade_image_target_path($home_id, $ext)
    {
        return $this->fasades_dir() . DIRECTORY_SEPARATOR . (int) $home_id . '.' . $ext;
    }

    function facade_image_url($home_id)
    {
        $found = $this->facade_image_resolve($home_id);
        $ext = $found ? $found['ext'] : 'jpg';
        return '/fasades/' . (int) $home_id . '.' . $ext;
    }

    /**
     * Абсолютный URL картинки фасада для публичного виджета (embed на чужих доменах).
     * Root-relative facade_image_url() на стороннем сайте резолвится неверно.
     */
    function facade_image_absolute_url($home_id)
    {
        $home_id = (int) $home_id;
        $found = $this->facade_image_resolve($home_id);
        $rel = $this->facade_image_url($home_id);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $scheme . '://' . $host . $rel;
        if ($found) {
            $url .= '?v=' . (int) @filemtime($found['path']);
        }
        return $url;
    }

    /** @return string абсолютный путь к каталогу fasades/sections/ */
    function fasades_sections_dir()
    {
        return $this->fasades_dir() . DIRECTORY_SEPARATOR . 'sections';
    }

    /**
     * Найти файл визуала секции шахматки: fasades/sections/{section_id}.jpg (также png/webp).
     * @return array{ext:string, path:string}|null
     */
    function section_image_resolve($section_id)
    {
        $section_id = (int) $section_id;
        if ($section_id < 1) {
            return null;
        }
        $dir = $this->fasades_sections_dir();
        foreach (self::FACADE_IMAGE_EXT_PRIORITY as $ext) {
            $path = $dir . DIRECTORY_SEPARATOR . $section_id . '.' . $ext;
            if (is_file($path)) {
                return ['ext' => $ext, 'path' => $path];
            }
        }
        return null;
    }

    function section_image_url($section_id)
    {
        $found = $this->section_image_resolve($section_id);
        $ext = $found ? $found['ext'] : 'jpg';
        return '/fasades/sections/' . (int) $section_id . '.' . $ext;
    }

    /** Абсолютный URL визуала секции для публичного виджета. */
    function section_image_absolute_url($section_id)
    {
        $section_id = (int) $section_id;
        $found = $this->section_image_resolve($section_id);
        if (!$found) {
            return '';
        }
        $rel = $this->section_image_url($section_id);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $rel . '?v=' . (int) @filemtime($found['path']);
    }

    function chessboard_visual_url($home_id, $section_id)
    {
        $url = $this->section_image_absolute_url($section_id);
        if ($url !== '') {
            return $url;
        }
        return $this->facade_image_absolute_url($home_id);
    }

    function assert_within_fasades($target_path)
    {
        $base = realpath($this->fasades_dir());
        $real = realpath(dirname($target_path));
        if ($base === false || $real === false || strpos($real, $base) !== 0) {
            throw new RuntimeException('Path containment check failed for ' . $target_path);
        }
    }

    function raster_ext_for_imagetype($imagetype)
    {
        switch ($imagetype) {
            case IMAGETYPE_JPEG:
                return 'jpg';
            case IMAGETYPE_PNG:
                return 'png';
            case IMAGETYPE_WEBP:
                return 'webp';
        }
        return null;
    }

    function upload_error_message($code)
    {
        switch ((int) $code) {
            case UPLOAD_ERR_OK:
                return null;
            case UPLOAD_ERR_INI_SIZE:
                return 'Файл больше лимита сервера (upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Файл больше лимита формы';
            case UPLOAD_ERR_PARTIAL:
                return 'Файл загружен частично — попробуйте ещё раз';
            case UPLOAD_ERR_NO_FILE:
                return 'Файл не выбран';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Ошибка сервера: нет временной директории для загрузки';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Ошибка сервера: не удалось записать файл на диск';
            case UPLOAD_ERR_EXTENSION:
                return 'Загрузка остановлена расширением PHP на сервере';
            default:
                return 'Неизвестная ошибка загрузки файла';
        }
    }

    function widget_json_error($message)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Публичные данные для встраиваемого виджета (без admin).
     * GET home_id → JSON { success, homeId, title, imageUrl, imageWidth, imageHeight, sections, polygons }
     */
    function act__widget_data()
    {
        global $mysql;

        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        if (!$home_id) {
            $this->widget_json_error('Не указан home_id');
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            $this->widget_json_error('Дом не найден');
            return;
        }

        $path = $this->facade_image_fs_path($home_id);
        if (!is_file($path)) {
            $this->widget_json_error('Файл фасада не найден');
            return;
        }

        $size = @getimagesize($path);
        if (!$size) {
            $this->widget_json_error('Не удалось прочитать изображение');
            return;
        }

        $sections_raw = $this->get_home_sections($home);
        $sections = [];
        foreach ($sections_raw as $sec) {
            $sections[] = [
                'id'      => (int) $sec['id'],
                'caption' => (string) $sec['caption'],
            ];
        }

        $rows = $mysql->get_arr(
            'SELECT * FROM facade_polygons WHERE home_id="' . $home_id . '" AND del="0" ORDER BY section, floor, sort_order, facade_polygon_id'
        );
        $polygons = [];
        if ($rows) {
            foreach ($rows as $v) {
                $points = json_decode($v['points'], true);
                if (!is_array($points) || count($points) < 3) {
                    continue;
                }
                $polygons[] = [
                    'id'      => (int) $v['facade_polygon_id'],
                    'section' => (int) ($v['section'] ?? 1),
                    'floor'   => (int) $v['floor'],
                    'label'   => (string) ($v['label'] ?? ''),
                    'points'  => $points,
                    'color'   => ($v['color'] ?: '#3388ff'),
                ];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'     => true,
            'homeId'      => $home_id,
            'title'       => (string) ($home['title'] ?? ''),
            'imageUrl'    => $this->facade_image_absolute_url($home_id),
            'imageWidth'  => (int) $size[0],
            'imageHeight' => (int) $size[1],
            'sections'    => $sections,
            'polygons'    => $polygons,
            'unitLabels'  => $this->widget_unit_labels(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /** Подписи типа объекта из config object_unit (квартира / резиденция / …). */
    function widget_unit_labels()
    {
        if (!function_exists('unit_label')) {
            return ['nom' => 'квартира', 'nomCap' => 'Квартира', 'abbrev' => 'кв.'];
        }
        return [
            'nom'    => unit_label('nom'),
            'nomCap' => unit_label_cap('nom'),
            'abbrev' => function_exists('unit_abbrev') ? unit_abbrev() : 'кв.',
        ];
    }

    /**
     * Публичные данные плана этажа для встраиваемого виджета (без admin).
     * Виджет всегда ходит на ctr=facades (detectApiBase), поэтому act здесь,
     * а не в ctr__floor_plans — но SQL/логика не дублируются: делегируем в
     * ctr__floor_plans::floor_plan_public_payload() (аудит C2).
     * GET home_id, section, floor → JSON.
     */
    function act__floor_plan_data()
    {
        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        $section = (int) ($_REQUEST['section'] ?? 1);
        $floor   = (int) ($_REQUEST['floor'] ?? 0);

        if (!class_exists('ctr__floor_plans')) {
            $file = __DIR__ . '/ctr__floor_plans.php';
            if (is_file($file)) {
                include_once $file;
            }
        }
        if (!class_exists('ctr__floor_plans')) {
            $this->widget_json_error('Модуль планов этажей не найден');
            return;
        }

        $floor_plans = new ctr__floor_plans();
        $payload = $floor_plans->floor_plan_public_payload($home_id, $section, $floor);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Публичные данные шахматки для виджета (Stage 5).
     * GET home_id, section (логический section_id).
     * Нумерация/дыры — как disp_home_p_n (get_sec_arr + get_data_appart_arr).
     */
    function act__chessboard_data()
    {
        global $mysql, $connection;

        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        $section = (int) ($_REQUEST['section'] ?? 0);

        if (!$home_id) {
            $this->widget_json_error('Не указан home_id');
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            $this->widget_json_error('Дом не найден');
            return;
        }

        if (!class_exists('sahmatka')) {
            $this->widget_json_error('Модуль шахматки не найден');
            return;
        }

        $sections_raw = $this->get_home_sections($home);
        $sections = [];
        foreach ($sections_raw as $sec) {
            $sid = (int) $sec['id'];
            $sections[] = [
                'id'        => $sid,
                'caption'   => (string) $sec['caption'],
                'visualUrl' => $this->chessboard_visual_url($home_id, $sid),
            ];
        }

        if (!$sections) {
            $this->widget_json_error('Секции не найдены');
            return;
        }

        if ($section < 1) {
            $section = (int) $sections[0]['id'];
        }

        $section_ok = false;
        $section_caption = '';
        foreach ($sections as $sec) {
            if ((int) $sec['id'] === $section) {
                $section_ok = true;
                $section_caption = (string) $sec['caption'];
                break;
            }
        }
        if (!$section_ok) {
            $this->widget_json_error('Секция не найдена');
            return;
        }

        $sa = new sahmatka($_SESSION, $connection);
        $conf = $sa->get_sec_arr($home_id, $section);
        if (!$conf || empty($conf['floor']) || empty($conf['apartments'])) {
            $this->widget_json_error('Конфиг секции не найден');
            return;
        }

        $data = $sa->get_data_appart_arr($home_id, $section);
        if (!is_array($data)) {
            $data = [];
        }

        $clean = (isset($conf['clean_apartments']) && is_array($conf['clean_apartments']))
            ? $conf['clean_apartments']
            : [];

        $ckv = 0;
        foreach ($clean as $floor_holes) {
            if (is_array($floor_holes)) {
                $ckv += count($floor_holes);
            }
        }

        $floors = (int) $conf['floor'];
        $cols = (int) $conf['apartments'];
        $start_num = (int) $conf['start_num'];
        $endnum = ($floors * $cols) + $start_num - $ckv;

        $rows = [];
        for ($i = $floors; $i > 0; $i--) {
            $nezk = count((array) ($clean[$i] ?? []));
            // Как в disp_home_p_n: стартовый счётчик + инкремент на «нулевой» столбец этажа.
            $end_etza_num = $endnum - $cols - 1 + $nezk;
            if (empty($clean[$i][0])) {
                $end_etza_num++;
            }
            $cells = [];

            for ($k = 1; $k <= $cols; $k++) {
                if (empty($clean[$i][$k])) {
                    $end_etza_num++;
                }

                if (!empty($clean[$i][$k])) {
                    $cells[] = [
                        'empty' => true,
                        'col'   => $k,
                    ];
                    continue;
                }

                $apart = $data[$home_id][$end_etza_num] ?? null;
                $status_raw = $apart['status'] ?? null;
                if ($status_raw === null || $status_raw === '') {
                    $status_raw = '2';
                }
                $status = (int) $status_raw;

                if ($status === 0 || $status === 1 || $status === 2 || !$apart) {
                    $status_key = 'free';
                    $status_label = 'Свободна';
                } elseif ($status === 4) {
                    $status_key = 'reserved';
                    $status_label = 'Бронь';
                } else {
                    $status_key = 'sold';
                    $status_label = 'Продана';
                }

                $rooms = (int) ($apart['rooms'] ?? 0);
                $apt_num = (int) ($apart['apartment_num'] ?? $end_etza_num);
                $image = $apart['image_pb'] ?? '';

                $cells[] = [
                    'empty'         => false,
                    'col'           => $k,
                    'apartmentNum'  => $apt_num,
                    'apartamentId'  => (int) ($apart['apartament_id'] ?? 0),
                    'rooms'         => $rooms,
                    'label'         => $rooms > 0 ? ($rooms . 'K') : '',
                    'area'          => isset($apart['area']) ? (float) $apart['area'] : null,
                    'status'        => $status,
                    'statusKey'     => $status_key,
                    'statusLabel'   => $status_label,
                    'imageUrl'      => $this->widget_absolute_url($image),
                ];

                $endnum--;
            }

            $rows[] = [
                'floor' => $i,
                'cells' => $cells,
            ];
        }

        $status_colors = [
            'free'        => '#7095A3',
            'reserved'    => '#DAA152',
            'sold'        => '#D3D3D3',
            'soldTip'     => '#A39C9D',
            'filteredOut' => '#E6E6E6',
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'      => true,
            'homeId'       => $home_id,
            'section'      => [
                'id'      => $section,
                'caption' => $section_caption !== '' ? $section_caption : (string) ($conf['caption'] ?? ('Секция ' . $section)),
            ],
            'sections'     => $sections,
            'floors'       => $floors,
            'columns'      => $cols,
            'statusColors' => $status_colors,
            'visualUrl'    => $this->chessboard_visual_url($home_id, $section),
            'rows'         => $rows,
            'unitLabels'   => $this->widget_unit_labels(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /** Абсолютный URL для embed (root-relative пути картинок квартир). */
    function widget_absolute_url($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $scheme . '://' . $host . $path;
    }

    /** Логический номер секции (facade_polygons.section) по PK apartaments.section_id. */
    function resolve_apartment_section_id($home, $section_pk)
    {
        global $mysql;

        $section_pk = (int) $section_pk;
        if (!$section_pk) {
            return 1;
        }

        $homes_id = (int) ($home['homes_id'] ?? 0);
        if ($homes_id) {
            $row = $mysql->get_arr(
                'SELECT section_id FROM homes_sections WHERE homes_id="' . $homes_id . '" AND homes_sections_id="' . $section_pk . '" LIMIT 1',
                1
            );
            if ($row && (int) ($row['section_id'] ?? 0) > 0) {
                return (int) $row['section_id'];
            }
        }

        return $section_pk;
    }

    /**
     * Публичные данные карточки квартиры для виджета (Stage 3).
     * GET home_id, apartment_num
     */
    function act__apartment_card_data()
    {
        global $mysql;

        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        $apartment_num = (int) ($_REQUEST['apartment_num'] ?? 0);
        $apartament_id = (int) ($_REQUEST['apartament_id'] ?? 0);

        if (!$home_id) {
            $this->widget_json_error('Не указан home_id');
            return;
        }

        if (!class_exists('ctr__apartments')) {
            $file = __DIR__ . '/ctr__apartments.php';
            if (is_file($file)) {
                include_once $file;
            }
        }
        if (!class_exists('ctr__apartments')) {
            $this->widget_json_error('Модуль квартир не найден');
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            $this->widget_json_error('Дом не найден');
            return;
        }

        // Номер для карточки — apartaments.apartment_num (не displayCode на плане).
        if ($apartament_id && $apartment_num < 1) {
            $apt_row = $mysql->get_arr(
                'SELECT apartment_num FROM apartaments WHERE apartament_id="' . $apartament_id . '" AND home_id="' . $home_id . '" LIMIT 1',
                1
            );
            if ($apt_row) {
                $apartment_num = (int) ($apt_row['apartment_num'] ?? 0);
            }
        }

        if ($apartment_num < 1) {
            $labels = $this->widget_unit_labels();
            $this->widget_json_error('Не указан apartment_num для ' . ($labels['nom'] ?? 'объекта'));
            return;
        }

        $apartments = new ctr__apartments();
        $data = $apartments->get_apartment($home_id, $apartment_num);
        if (!$data && $apartament_id) {
            $by_id = $apartments->get_apartment_by_id($apartament_id);
            if ($by_id && (int) ($by_id['home_id'] ?? $by_id['apartment_home_id'] ?? 0) === $home_id) {
                $apartment_num = (int) ($by_id['apartment_num'] ?? 0);
                if ($apartment_num > 0) {
                    $data = $apartments->get_apartment($home_id, $apartment_num);
                }
            }
        }
        if (!$data) {
            $labels = $this->widget_unit_labels();
            $this->widget_json_error(($labels['nomCap'] ?? 'Квартира') . ' не найдена');
            return;
        }

        $apartment_num = (int) ($data['apartment_num'] ?? $apartment_num);
        $apartament_id = (int) ($data['apartament_id'] ?? $apartament_id);

        $status = $data['status2'] ?? $data['apartment_status'] ?? $data['status'] ?? '';
        if ($status === '5' || $status === '6') {
            $status = '4';
        }
        if ($status === '' || $status === null) {
            $status = '2';
        }
        $status = (string) $status;

        global $status_arr, $status_color_arr;

        $section = $this->resolve_apartment_section_id($home, (int) ($data['section_id'] ?? 0));
        $sections = $this->get_home_sections($home);
        $floors_total = (int) ($home['floor'] ?? 0);
        foreach ($sections as $sec) {
            if ((int) $sec['id'] === $section) {
                if ((int) $sec['maxFloor'] > 0) {
                    $floors_total = (int) $sec['maxFloor'];
                }
                break;
            }
        }
        if ($floors_total < 1) {
            $floors_total = 1;
        }

        $price = (float) ($data['price'] ?? 0);
        $show_form = (!$status || $status === '2');

        $form_id = 'facade_widget_card';
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['_csrf'][$form_id])) {
            $_SESSION['_csrf'][$form_id] = bin2hex(random_bytes(16));
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $booking_url = $scheme . '://' . $host . '/sahmatka/ajax_router.php?ctr=facades&act=widget_booking_submit';

        $rooms = (string) ($data['rooms'] ?? '');
        $area = (string) ($data['area'] ?? '');
        $unit_labels = $this->widget_unit_labels();

        $sectionCaption = trim((string) ($data['section_caption'] ?? ''));
        if ($sectionCaption === '') {
            $sectionCaption = 'Секция ' . $section;
        }
        $homeTitle = trim((string) ($data['title'] ?? ''));
        if ($homeTitle === '') {
            $homeTitle = trim((string) ($home['title'] ?? ''));
        }
        if (mb_strlen($homeTitle) < 3) {
            $homeTitle = 'Дом ' . $home_id;
        }
        // ЖК: prefer homes_kvartal.title, иначе название дома (часто ЖК = title дома).
        $kvartalTitle = trim((string) ($data['kvartal_title'] ?? ''));
        if ($kvartalTitle === '') {
            $kvartalTitle = $homeTitle;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'          => true,
            'homeId'           => $home_id,
            'apartmentNum'     => $apartment_num,
            'apartamentId'     => $apartament_id,
            'rooms'            => $rooms,
            'area'             => $area,
            'floor'            => (int) ($data['floor'] ?? 0),
            'floorsTotal'      => $floors_total,
            'section'          => $section,
            'sectionCaption'   => $sectionCaption,
            'kvartalTitle'     => $kvartalTitle,
            'homeTitle'        => $homeTitle,
            'addressLabel'     => (string) ($data['adress'] ?? ''),
            'price'            => $price,
            'priceFormatted'   => $price > 0 ? number_format($price, 0, '.', ' ') : '',
            'status'           => $status,
            'statusLabel'      => (string) ($status_arr[$status] ?? $status),
            'statusColor'      => (string) ($status_color_arr[$status] ?? '#ccc'),
            'imageLayoutUrl'   => $this->widget_absolute_url($data['image_pb'] ?? ''),
            'imageFloorPlanUrl'=> $this->widget_absolute_url($data['image_pb_plan'] ?? ''),
            'showBookingForm'  => $show_form,
            'unitLabelNomCap'  => $unit_labels['nomCap'],
            'unitLabelNom'     => $unit_labels['nom'],
            'booking'          => [
                'actionUrl'    => $booking_url,
                'fpId'         => $form_id,
                'hiddenFields' => [
                    '_fp_form' => $form_id,
                    '_csrf'    => $_SESSION['_csrf'][$form_id],
                    '_fp_hp'   => '',
                    '_fp_js'   => '1',
                    'home'     => $homeTitle,
                    'section_caption' => $sectionCaption,
                    'apartment_num'   => (string) $apartment_num,
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Отправка заявки из виджета (Stage 3) — тот же flow, что act__card_ajaxform.
     */
    function act__widget_booking_submit()
    {
        global $fw_mailer;

        $formProtectPath = dirname(__DIR__, 3) . '/captcha/FormProtect.php';
        if (!is_file($formProtectPath)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'FormProtect не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }
        require_once $formProtectPath;

        header('Content-Type: application/json; charset=utf-8');
        try {
            $formProtect = new FormProtect();
            $rules = [
                'home'             => 'required|string|min:3|max:64',
                'section_caption'  => 'required|string|min:3|max:64',
                'apartment_num'    => 'required|string|min:1|max:4',
                'fio'              => 'required|string|min:3|max:64',
                'phone'            => 'required|validPhone',
                'message'          => 'string|max:300|noHtml|noLinks',
            ];
            // Капча в legacy фактически отключена (CaptchaValidator); для embed — без капчи.
            $data = $formProtect->validateForm($rules, false);

            $titles = [
                'home'            => 'Дом',
                'section_caption' => 'Секция',
                'apartment_num'   => unit_label_cap('nom'),
                'fio'             => 'ФИО',
                'phone'           => 'Телефон',
                'message'         => 'Сообщение',
            ];

            $message = fw_messages::build_message($data, $titles);
            $recipients = '89236470002@mail.ru,op@em-nsk.group';

            if (!$fw_mailer->send($recipients, 'Заявка EM-NSK.RU - виджет фасада ' . unit_label('gen') . ' ', $message)) {
                $formProtect->fail('Не удалось отправить заявку. Попробуйте позднее.');
                exit;
            }

            $formProtect->ok('Ваша заявка успешно отправлена');
        } catch (Throwable $e) {
            error_log('[Widget booking error] ' . $e->getMessage());
            if (isset($formProtect)) {
                $formProtect->fail('Ошибка сервера. Попробуйте позднее.');
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка сервера. Попробуйте позднее.'], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    /**
     * Секции дома из homes_sections (номер section_id + подпись + этажность секции).
     * @return array<int, array{id:int,caption:string,maxFloor:int}>
     */
    function get_home_sections($home)
    {
        global $mysql;

        $homes_id = (int) ($home['homes_id'] ?? 0);
        $fallback_max = (int) ($home['floor'] ?? 0);
        if ($fallback_max < 1) {
            $fallback_max = 30;
        }

        $sections = [];
        if ($homes_id) {
            $rows = $mysql->get_arr(
                'SELECT * FROM homes_sections WHERE homes_id="' . $homes_id . '" ORDER BY section_id, homes_sections_id'
            );
            if ($rows) {
                foreach ($rows as $row) {
                    $sid = (int) ($row['section_id'] ?? 0);
                    if ($sid < 1) {
                        $sid = (int) ($row['homes_sections_id'] ?? 0);
                    }
                    if ($sid < 1) {
                        continue;
                    }
                    $max = (int) ($row['floor'] ?? 0);
                    if ($max < 1) {
                        $max = $fallback_max;
                    }
                    $caption = trim((string) ($row['caption'] ?? ''));
                    if ($caption === '') {
                        $caption = 'Секция ' . $sid;
                    }
                    $sections[] = [
                        'id'       => $sid,
                        'caption'  => $caption,
                        'maxFloor' => $max,
                    ];
                }
            }
        }

        if (!$sections) {
            $sections[] = [
                'id'       => 1,
                'caption'  => 'Секция 1',
                'maxFloor' => $fallback_max,
            ];
        }

        return $sections;
    }

    function act__index()
    {
        if (!check_access('admin')) {
            die('Ошибка доступа');
        }
        global $mysql, $t, $r;
        $t['h1'] = 'Разметка фасадов';

        $homes = $mysql->get_arr('SELECT home_id, title, `floor` FROM homes WHERE 1=1 ORDER BY `order`, title');
        ?>
        <p>Файл фасада: загрузите в редакторе или положите вручную в <code>sites/sigma/fasades/{home_id}.jpg</code> (также png/webp).</p>
        <p>Фото корпуса в шахматке: <code>sites/sigma/fasades/sections/{section_id}.jpg</code> (по одному файлу на секцию; при переключении «Корпус №1 / №2» подставляется свой).</p>
        <table border="0" class="dtable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дом</th>
                <th>Файл фасада</th>
                <th>Разметка</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($homes as $h):
                $hid = (int) $h['home_id'];
                $has_file = $this->facade_image_resolve($hid) !== null;
                $cnt = $mysql->get_arr('SELECT COUNT(*) AS c FROM facade_polygons WHERE home_id="' . $hid . '" AND del="0"', 1);
                $poly_count = (int) ($cnt['c'] ?? 0);
                ?>
                <tr>
                    <td><?= $hid ?></td>
                    <td><?= htmlspecialchars($h['title']) ?></td>
                    <td><?= $has_file ? '<span style="color:green">есть</span>' : '<span style="color:#c45c00">нет</span>' ?></td>
                    <td><?= $poly_count ? $poly_count . ' полиг.' : '—' ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($r->acturl('facades', 'editor', 'ctrind.php') . '&home_id=' . $hid) ?>">Редактор</a>
                        &nbsp;|&nbsp;
                        <a href="<?= htmlspecialchars($r->acturl('facades', 'editor', 'iframe_router.php') . '&home_id=' . $hid) ?>" class="iframe_r">iframe</a>
                        <?php if ($has_file): ?>
                            &nbsp;|&nbsp;
                            <a href="<?= htmlspecialchars($r->acturl('facades', 'widget_demo', 'ctrind.php') . '&home_id=' . $hid) ?>">Виджет</a>
                        <?php endif; ?>
                        &nbsp;|&nbsp;
                        <a href="<?= htmlspecialchars($r->acturl('floor_plans', 'editor', 'ctrind.php') . '&home_id=' . $hid) ?>">Планы этажей</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    function act__editor()
    {
        if (!check_access('admin')) {
            die('Ошибка доступа');
        }
        global $mysql, $t, $r;

        $home_id = (int) $_REQUEST['home_id'];
        if (!$home_id) {
            print 'Не указан home_id';
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            print 'Дом не найден';
            return;
        }

        $found = $this->facade_image_resolve($home_id);
        $image_url = '';
        $image_w = 0;
        $image_h = 0;
        if ($found) {
            $size = @getimagesize($found['path']);
            if ($size) {
                $image_url = $this->facade_image_url($home_id) . '?v=' . (int) @filemtime($found['path']);
                $image_w = (int) $size[0];
                $image_h = (int) $size[1];
            }
        }

        $sections = $this->get_home_sections($home);
        $max_floor = 1;
        foreach ($sections as $sec) {
            if ($sec['maxFloor'] > $max_floor) {
                $max_floor = $sec['maxFloor'];
            }
        }

        $t['h1'] = 'Разметка фасада';

        $tpl = [
            'home_id'          => $home_id,
            'home_title'       => $home['title'],
            'image_url'        => $image_url,
            'image_w'          => $image_w,
            'image_h'          => $image_h,
            'max_floor'        => $max_floor,
            'sections'         => $sections,
            'ajax_base'        => '/sahmatka/ajax_router.php?ctr=facades',
            'max_upload_bytes' => self::MAX_UPLOAD_BYTES,
            'widget_demo_url'  => $r->acturl('facades', 'widget_demo', 'iframe_router.php') . '&home_id=' . $home_id,
        ];
        $this->tpl($tpl, 'facades', 'editor');
    }

    function act__widget_demo()
    {
        if (!check_access('admin')) {
            die('Ошибка доступа');
        }
        global $t;

        $home_id = (int) ($_REQUEST['home_id'] ?? 60);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $origin = $scheme . '://' . $host;

        $t['h1'] = 'Демо виджета фасада';

        $tpl = [
            'home_id'    => $home_id ?: 60,
            'api_base'   => $origin . '/sahmatka/ajax_router.php?ctr=facades',
            'script_src' => $origin . '/sahmatka/template/default/js/facade_widget.js',
        ];
        $this->tpl($tpl, 'facades', 'widget_demo');
    }

    function act__get_polygons()
    {
        $this->require_admin();
        global $mysql;

        $home_id = (int) $_REQUEST['home_id'];
        $rows = $mysql->get_arr(
            'SELECT * FROM facade_polygons WHERE home_id="' . $home_id . '" AND del="0" ORDER BY section, floor, sort_order, facade_polygon_id'
        );

        $result = [];
        if ($rows) {
            foreach ($rows as $v) {
                $points = json_decode($v['points'], true);
                if (!is_array($points)) {
                    continue;
                }
                $result[] = [
                    'id'      => (int) $v['facade_polygon_id'],
                    'section' => (int) ($v['section'] ?? 1),
                    'floor'   => (int) $v['floor'],
                    'label'   => $v['label'],
                    'points'  => $points,
                    'color'   => $v['color'] ?: '#3388ff',
                ];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    function act__save_polygon()
    {
        $this->require_admin();
        global $mysql;

        $home_id = (int) $_POST['home_id'];
        $section = (int) ($_POST['section'] ?? 1);
        $floor   = (int) $_POST['floor'];
        $id      = (int) $_POST['facade_polygon_id'];
        $points  = json_decode($_POST['points'] ?? '', true);

        if ($section < 1) {
            $section = 1;
        }

        if (!$home_id || !$floor || !is_array($points) || count($points) < 3) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Некорректные данные полигона']);
            return;
        }

        $points = $this->normalize_points($points);
        if (count($points) < 3) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Полигон должен иметь минимум 3 точки']);
            return;
        }

        $data = [
            'home_id' => $home_id,
            'section' => $section,
            'floor'   => $floor,
            'label'   => trim((string) ($_POST['label'] ?? '')),
            'points'  => json_encode($points),
            'color'   => trim((string) ($_POST['color'] ?? '#3388ff')),
        ];

        if ($id) {
            // Проверяем принадлежность существующего полигона этому дому —
            // иначе id чужого полигона можно было бы "перевесить" на другой home_id.
            $existing = $mysql->get_for_key('facade_polygons', 'facade_polygon_id', $id);
            if (!$existing || (int) $existing['home_id'] !== $home_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Полигон не найден для этого дома']);
                return;
            }
            $mysql->update_for_key('facade_polygons', 'facade_polygon_id', $id, $data);
        } else {
            $id = $mysql->insert('facade_polygons', $data);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'id' => (int) $id]);
    }

    function act__delete_polygon()
    {
        $this->require_admin();
        global $mysql;

        $id      = (int) ($_POST['facade_polygon_id'] ?? 0);
        $home_id = (int) ($_POST['home_id'] ?? 0);
        if (!$id) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Не указан id']);
            return;
        }

        $existing = $mysql->get_for_key('facade_polygons', 'facade_polygon_id', $id);
        if (!$existing || ($home_id && (int) $existing['home_id'] !== $home_id)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Полигон не найден для этого дома']);
            return;
        }

        $mysql->sql('UPDATE facade_polygons SET del="1" WHERE facade_polygon_id="' . $id . '"');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'id' => $id]);
    }

    function normalize_points($points)
    {
        $out = [];
        foreach ($points as $p) {
            if (!is_array($p) || count($p) < 2) {
                continue;
            }
            $out[] = [(float) $p[0], (float) $p[1]];
        }
        return $out;
    }

    function act__upload_facade_image()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $home_id = (int) ($_POST['home_id'] ?? 0);
        if (!$home_id) {
            echo json_encode(['success' => false, 'message' => 'Не указан home_id']);
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            echo json_encode(['success' => false, 'message' => 'Дом не найден']);
            return;
        }

        if (!isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'Файл не передан']);
            return;
        }

        $file = $_FILES['file'];
        $errMsg = $this->upload_error_message($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errMsg !== null) {
            echo json_encode(['success' => false, 'message' => $errMsg]);
            return;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Некорректная загрузка файла']);
            return;
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > self::MAX_UPLOAD_BYTES) {
            echo json_encode(['success' => false, 'message' => 'Размер файла должен быть до ' . (int) (self::MAX_UPLOAD_BYTES / 1024 / 1024) . ' МБ']);
            return;
        }

        $imgInfo = @getimagesize($file['tmp_name']);
        $ext = null;
        $dims = null;
        if ($imgInfo && isset($imgInfo[2])) {
            $rasterExt = $this->raster_ext_for_imagetype($imgInfo[2]);
            if ($rasterExt) {
                $ext = $rasterExt;
                $dims = ['width' => (int) $imgInfo[0], 'height' => (int) $imgInfo[1]];
            }
        }

        if (!$ext || !$dims) {
            echo json_encode(['success' => false, 'message' => 'Неподдерживаемый или повреждённый файл. Разрешены: JPG, PNG, WEBP']);
            return;
        }

        $dir = $this->fasades_dir();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось создать каталог на сервере']);
            return;
        }

        $existing = $this->facade_image_resolve($home_id);
        $replaced = $existing !== null;

        $target = $this->facade_image_target_path($home_id, $ext);
        try {
            $this->assert_within_fasades($target);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка проверки пути сохранения']);
            return;
        }

        if (!@move_uploaded_file($file['tmp_name'], $target)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл на сервере']);
            return;
        }
        @chmod($target, 0644);

        foreach (self::FACADE_IMAGE_EXT_PRIORITY as $otherExt) {
            if ($otherExt === $ext) {
                continue;
            }
            $siblingPath = $this->facade_image_target_path($home_id, $otherExt);
            if (is_file($siblingPath)) {
                @unlink($siblingPath);
            }
        }

        echo json_encode([
            'success'     => true,
            'imageUrl'    => $this->facade_image_url($home_id) . '?v=' . (int) @filemtime($target),
            'imageWidth'  => $dims['width'],
            'imageHeight' => $dims['height'],
            'ext'         => $ext,
            'replaced'    => $replaced,
        ], JSON_UNESCAPED_UNICODE);
    }

    /** Очистить разметку выбранного этажа секции (файл фасада не трогает). */
    function act__clear_floor_polygons()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $home_id = (int) ($_POST['home_id'] ?? 0);
        $section = (int) ($_POST['section'] ?? 1);
        $floor   = (int) ($_POST['floor'] ?? 0);
        if ($section < 1) {
            $section = 1;
        }

        if (!$home_id || !$floor) {
            echo json_encode(['success' => false, 'message' => 'Не указаны home_id/floor']);
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            echo json_encode(['success' => false, 'message' => 'Дом не найден']);
            return;
        }

        $mysql->sql(
            'UPDATE facade_polygons SET del="1"
             WHERE home_id="' . $home_id . '" AND section="' . $section . '" AND floor="' . $floor . '" AND del="0"'
        );
        $cleared = (int) $mysql->count;

        echo json_encode(['success' => true, 'cleared' => $cleared]);
    }

    /** Очистить весь фасад: все полигоны дома + удаление файла изображения. */
    function act__clear_facade()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $home_id = (int) ($_POST['home_id'] ?? 0);
        if (!$home_id) {
            echo json_encode(['success' => false, 'message' => 'Не указан home_id']);
            return;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            echo json_encode(['success' => false, 'message' => 'Дом не найден']);
            return;
        }

        $mysql->sql(
            'UPDATE facade_polygons SET del="1" WHERE home_id="' . $home_id . '" AND del="0"'
        );
        $cleared = (int) $mysql->count;

        $removedFiles = 0;
        foreach (self::FACADE_IMAGE_EXT_PRIORITY as $ext) {
            $path = $this->facade_image_target_path($home_id, $ext);
            try {
                $this->assert_within_fasades($path);
            } catch (Throwable $e) {
                continue;
            }
            if (is_file($path) && @unlink($path)) {
                $removedFiles++;
            }
        }

        echo json_encode([
            'success'      => true,
            'cleared'      => $cleared,
            'removedFiles' => $removedFiles,
        ]);
    }
}
