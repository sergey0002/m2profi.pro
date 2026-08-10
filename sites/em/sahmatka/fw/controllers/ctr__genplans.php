<?
/**
 * Интерактивный план ЖК (EM task 10).
 * Паттерны path/upload/require_admin — из sites/sigma ctr__facades.
 */
class ctr__genplans extends ctr__
{
    var $table = 'genplan_polygons';
    var $key_filed = 'genplan_polygon_id';
    var $ctr = 'genplans';
    var $title = 'Интерактивный план';

    const IMAGE_EXT_PRIORITY = ['jpg', 'jpeg', 'png', 'webp'];
    const MAX_UPLOAD_BYTES = 20971520; // 20 MB

    function require_admin()
    {
        if (!check_access('admin')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Ошибка доступа']);
            exit;
        }
    }

    function genplans_dir()
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'genplans';
    }

    function genplan_image_resolve($kvartal_id)
    {
        $kvartal_id = (int) $kvartal_id;
        $dir = $this->genplans_dir();
        foreach (self::IMAGE_EXT_PRIORITY as $ext) {
            $path = $dir . DIRECTORY_SEPARATOR . $kvartal_id . '.' . $ext;
            if (is_file($path)) {
                return ['ext' => $ext, 'path' => $path];
            }
        }
        return null;
    }

    function genplan_image_fs_path($kvartal_id)
    {
        $found = $this->genplan_image_resolve($kvartal_id);
        if ($found) {
            return $found['path'];
        }
        return $this->genplans_dir() . DIRECTORY_SEPARATOR . (int) $kvartal_id . '.jpg';
    }

    function genplan_image_target_path($kvartal_id, $ext)
    {
        return $this->genplans_dir() . DIRECTORY_SEPARATOR . (int) $kvartal_id . '.' . $ext;
    }

    function genplan_image_url($kvartal_id)
    {
        $found = $this->genplan_image_resolve($kvartal_id);
        $ext = $found ? $found['ext'] : 'jpg';
        return '/genplans/' . (int) $kvartal_id . '.' . $ext;
    }

    function genplan_image_absolute_url($kvartal_id)
    {
        $kvartal_id = (int) $kvartal_id;
        $found = $this->genplan_image_resolve($kvartal_id);
        $rel = $this->genplan_image_url($kvartal_id);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $scheme . '://' . $host . $rel;
        if ($found) {
            $url .= '?v=' . (int) @filemtime($found['path']);
        }
        return $url;
    }

    function assert_within_genplans($target_path)
    {
        $base = realpath($this->genplans_dir());
        if ($base === false) {
            $dir = $this->genplans_dir();
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $base = realpath($dir);
        }
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

    /** Allowlist HTML for title (plan §3.2). */
    function sanitize_title_html($html)
    {
        $html = (string) $html;
        if ($html === '') {
            return '';
        }
        $allowed = '<b><strong><i><em><br><span><a>';
        $clean = strip_tags($html, $allowed);
        // strip event handlers / javascript: in remaining tags via regex
        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            function ($m) {
                if (!preg_match('/href\s*=\s*("|\')(.*?)\1/i', $m[1], $hm)) {
                    return '<a>';
                }
                $href = trim($hm[2]);
                if (!preg_match('#^(https?:|mailto:)#i', $href)) {
                    return '<a>';
                }
                return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
            },
            $clean
        );
        return $clean;
    }

    function title_plain($html)
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));
    }

    function public_site_url()
    {
        $url = getenv('PUBLIC_URL');
        if ($url && $url !== '') {
            return rtrim($url, '/');
        }
        return 'https://em-nsk.ru';
    }

    function ru_plural_label($n, $one, $few, $many)
    {
        $n = abs((int) $n);
        $mod10 = $n % 10;
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 14) {
            return $n . ' ' . $many;
        }
        if ($mod10 === 1) {
            return $n . ' ' . $one;
        }
        if ($mod10 >= 2 && $mod10 <= 4) {
            return $n . ' ' . $few;
        }
        return $n . ' ' . $many;
    }

    function polygon_kind_from_points($points)
    {
        if (!is_array($points) || count($points) === 0) {
            return 'point';
        }
        return 'polygon';
    }

    function row_flag($row, $key, $default = 1)
    {
        if (!is_array($row) || !array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
            return (int) $default ? 1 : 0;
        }
        return (int) $row[$key] ? 1 : 0;
    }

    function post_flag($key, $default = 1)
    {
        if (!isset($_POST[$key])) {
            return (int) $default ? 1 : 0;
        }
        return (int) $_POST[$key] ? 1 : 0;
    }

    function ensure_stat_helpers()
    {
        if (!function_exists('stat_free_format_delivery_quarter')) {
            $helper = dirname(__DIR__, 2) . '/inc/stat_free_apartments_helpers.php';
            if (is_file($helper)) {
                include_once $helper;
            }
        }
    }

    /**
     * @param int[] $home_ids
     * @return array{aptTotal:array,freeTotal:array,sections:array,aptLinksByHome:array,homes:array}
     */
    function batch_home_widget_stats(array $home_ids)
    {
        global $mysql;
        $home_ids = array_values(array_unique(array_filter(array_map('intval', $home_ids))));
        $result = [
            'aptTotal' => [],
            'freeTotal' => [],
            'sections' => [],
            'aptLinksByHome' => [],
            'homes' => [],
        ];
        if (!$home_ids) {
            return $result;
        }

        $idsSql = implode(',', $home_ids);
        $homeRows = $mysql->get_arr('SELECT * FROM homes WHERE home_id IN (' . $idsSql . ')');
        if ($homeRows) {
            foreach ($homeRows as $h) {
                $result['homes'][(int) $h['home_id']] = $h;
            }
        }

        $aptRows = $mysql->get_arr(
            'SELECT home_id,
                    COUNT(*) AS apt_total,
                    SUM(CASE WHEN status2 IS NULL OR status2 IN (0, 2) THEN 1 ELSE 0 END) AS free_total
             FROM apartaments
             WHERE home_id IN (' . $idsSql . ')
             GROUP BY home_id'
        );
        if ($aptRows) {
            foreach ($aptRows as $r) {
                $hid = (int) $r['home_id'];
                $result['aptTotal'][$hid] = (int) ($r['apt_total'] ?? 0);
                $result['freeTotal'][$hid] = (int) ($r['free_total'] ?? 0);
            }
        }

        $secRows = $mysql->get_arr(
            'SELECT h.home_id, COUNT(s.section_id) AS sections_n
             FROM homes h
             LEFT JOIN homes_sections s ON s.homes_id = h.homes_id
             WHERE h.home_id IN (' . $idsSql . ')
             GROUP BY h.home_id'
        );
        if ($secRows) {
            foreach ($secRows as $r) {
                $result['sections'][(int) $r['home_id']] = (int) ($r['sections_n'] ?? 0);
            }
        }

        $roomRows = $mysql->get_arr(
            'SELECT home_id,
                    CAST(TRIM(rooms) AS UNSIGNED) AS rooms_n,
                    SUM(CASE WHEN status2 IS NULL OR status2 IN (0, 2) THEN 1 ELSE 0 END) AS free_count
             FROM apartaments
             WHERE home_id IN (' . $idsSql . ')
               AND TRIM(rooms) <> ""
               AND CAST(TRIM(rooms) AS UNSIGNED) > 0
             GROUP BY home_id, CAST(TRIM(rooms) AS UNSIGNED)
             HAVING free_count > 0
             ORDER BY home_id, rooms_n'
        );
        if ($roomRows) {
            $base = $this->public_site_url();
            foreach ($roomRows as $r) {
                $hid = (int) $r['home_id'];
                $rooms = (int) $r['rooms_n'];
                $free = (int) $r['free_count'];
                if (!isset($result['aptLinksByHome'][$hid])) {
                    $result['aptLinksByHome'][$hid] = [];
                }
                $result['aptLinksByHome'][$hid][] = [
                    'rooms' => $rooms,
                    'free' => $free,
                    'label' => $rooms . '-комнатные (' . $free . ')',
                    'url' => $base . '/catalog/?rooms_min=' . $rooms . '&rooms_max=' . $rooms . '&home_id=' . $hid . '&start=0&limit=15',
                ];
            }
        }

        return $result;
    }

    /**
     * Срок сдачи для тултипа виджета: «4 квартал 2025» (арабская цифра + слово «квартал»).
     */
    function home_delivery_meta($home)
    {
        if (!$home) {
            return null;
        }
        $q = 0;
        $year = 0;
        $deliveryDate = trim((string) ($home['delivery_date'] ?? ''));
        if ($deliveryDate !== '' && $deliveryDate !== '0000-00-00') {
            $ts = strtotime($deliveryDate);
            if ($ts) {
                $q = (int) ceil(((int) date('n', $ts)) / 3);
                $year = (int) date('Y', $ts);
            }
        }
        if ($q < 1 || $year < 1) {
            $q = (int) ($home['ready_quarter'] ?? 0);
            $year = (int) ($home['built_year'] ?? 0);
        }
        if ($q < 1 || $q > 4 || $year < 1) {
            return null;
        }
        return 'Срок сдачи: ' . $q . ' квартал ' . $year;
    }

    /**
     * @return array{statusTone:string,statusText:string}
     */
    function compute_home_status($home, $aptTotal, $freeTotal)
    {
        if (!$home) {
            return ['statusTone' => 'muted', 'statusText' => ''];
        }

        $show = (int) ($home['show'] ?? 1);
        if ($show === 0) {
            $text = trim((string) ($home['complite_text'] ?? ''));
            if ($text === '') {
                $text = 'Скрыт';
            }
            return ['statusTone' => 'muted', 'statusText' => $text];
        }

        if ($aptTotal <= 0) {
            return ['statusTone' => 'wait', 'statusText' => 'Ждет начала строительства'];
        }

        if ($freeTotal <= 0) {
            return ['statusTone' => 'muted', 'statusText' => 'Квартиры проданы'];
        }

        if ((int) ($home['complite'] ?? 0) === 1) {
            return ['statusTone' => 'ok', 'statusText' => 'Сдан'];
        }

        return ['statusTone' => 'warn', 'statusText' => 'Строится'];
    }

    /**
     * @return array{0:?string,1:?string} ctaLabel, ctaUrl
     */
    function compute_cta_fields($statusTone, $linkUrl, $homeId)
    {
        $linkUrl = trim((string) $linkUrl);
        if ($statusTone === 'muted') {
            return [null, null];
        }
        if ($statusTone === 'wait') {
            if ($linkUrl === '') {
                return [null, null];
            }
            return ['Сообщить о старте продаж', $linkUrl];
        }
        if ($statusTone === 'ok' || $statusTone === 'warn') {
            $url = $linkUrl;
            if ($url === '' && $homeId > 0) {
                $url = $this->public_site_url() . '/catalog/?home_id=' . (int) $homeId . '&start=0&limit=15';
            }
            if ($url === '') {
                return [null, null];
            }
            return ['Выбрать квартиру', $url];
        }
        return [null, null];
    }

    function get_kvartal($kvartal_id)
    {
        global $mysql;
        $kvartal_id = (int) $kvartal_id;
        if ($kvartal_id < 1) {
            return null;
        }
        return $mysql->get_for_key('homes_kvartal', 'homes_kvartal_id', $kvartal_id);
    }

    function home_belongs_to_kvartal($home_id, $kvartal_id)
    {
        global $mysql;
        $home_id = (int) $home_id;
        $kvartal_id = (int) $kvartal_id;
        if ($home_id < 1 || $kvartal_id < 1) {
            return false;
        }
        $row = $mysql->get_arr(
            'SELECT home_id FROM homes
             WHERE home_id="' . $home_id . '"
               AND CAST(kvartal AS UNSIGNED)="' . $kvartal_id . '"
               AND (del=0 OR del IS NULL)
             LIMIT 1',
            1
        );
        return !empty($row['home_id']);
    }

    function get_home_row($home_id)
    {
        global $mysql;
        $home_id = (int) $home_id;
        if ($home_id < 1) {
            return null;
        }
        return $mysql->get_for_key('homes', 'home_id', $home_id);
    }

    /**
     * Live card fields from homes (editor preview).
     * @return array{statusText:?string,statusTone:?string,metaDelivery:?string,metaAddress:?string,homeTitle:string,floors:?int,sections:?int,floorsLabel:?string,sectionsLabel:?string}
     */
    function live_home_card_fields($home, $batch = null)
    {
        if (!$home) {
            return [
                'statusText' => null,
                'statusTone' => null,
                'metaDelivery' => null,
                'metaAddress' => null,
                'homeTitle' => '',
                'floors' => null,
                'sections' => null,
                'floorsLabel' => null,
                'sectionsLabel' => null,
            ];
        }
        $homeId = (int) ($home['home_id'] ?? 0);
        $homeTitle = trim((string) ($home['title'] ?? ''));
        if ($homeTitle === '' && !empty($home['long_title'])) {
            $homeTitle = trim((string) $home['long_title']);
        }

        $aptTotal = 0;
        $freeTotal = 0;
        $sections = 0;
        if (is_array($batch)) {
            $aptTotal = (int) ($batch['aptTotal'][$homeId] ?? 0);
            $freeTotal = (int) ($batch['freeTotal'][$homeId] ?? 0);
            $sections = (int) ($batch['sections'][$homeId] ?? 0);
        }

        $status = $this->compute_home_status($home, $aptTotal, $freeTotal);
        $metaDelivery = $this->home_delivery_meta($home);
        $addr = trim((string) ($home['adress'] ?? ''));
        $metaAddress = $addr !== '' ? ('Адрес: ' . $addr) : null;

        $floors = isset($home['floor']) && $home['floor'] !== '' ? (int) $home['floor'] : null;
        $floorsLabel = ($floors && $floors > 0)
            ? $this->ru_plural_label($floors, 'этаж', 'этажа', 'этажей')
            : null;
        $sectionsLabel = ($sections > 0)
            ? $this->ru_plural_label($sections, 'секция', 'секции', 'секций')
            : null;

        return [
            'statusText' => $status['statusText'],
            'statusTone' => $status['statusTone'],
            'metaDelivery' => $metaDelivery,
            'metaAddress' => $metaAddress,
            'homeTitle' => $homeTitle,
            'floors' => $floors,
            'sections' => $sections > 0 ? $sections : null,
            'floorsLabel' => $floorsLabel,
            'sectionsLabel' => $sectionsLabel,
        ];
    }

    function polygon_public_payload($row, $batch = null)
    {
        $points = json_decode($row['points'] ?? '', true);
        if (!is_array($points)) {
            $points = [];
        }
        $points = $this->normalize_points($points);
        $kind = $this->polygon_kind_from_points($points);
        if ($kind === 'polygon' && count($points) < 3) {
            return null;
        }

        $homeId = isset($row['home_id']) && $row['home_id'] !== null && $row['home_id'] !== ''
            ? (int) $row['home_id']
            : null;
        if ($homeId === 0) {
            $homeId = null;
        }

        $rawTitle = (string) ($row['title'] ?? '');
        $titleHtml = $this->sanitize_title_html($rawTitle);
        $titleText = $this->title_plain($titleHtml);
        $contentHtml = $this->sanitize_title_html((string) ($row['content'] ?? ''));

        $showTitleDesktop = $this->row_flag($row, 'show_title_desktop', 1);
        $showTitleMobile = $this->row_flag($row, 'show_title_mobile', 1);
        $showAptLinks = $this->row_flag($row, 'show_apt_links', 0);
        if (!$homeId) {
            $showAptLinks = 0;
        }

        $statusText = null;
        $statusTone = 'muted';
        $metaDelivery = null;
        $metaAddress = null;
        $floors = null;
        $sections = null;
        $floorsLabel = null;
        $sectionsLabel = null;
        $aptTotal = 0;
        $freeTotal = 0;
        $homeShow = null;
        $aptLinks = [];

        if ($homeId) {
            $home = is_array($batch) && isset($batch['homes'][$homeId])
                ? $batch['homes'][$homeId]
                : $this->get_home_row($homeId);
            $kvartalId = (int) $row['kvartal_id'];
            if ($home && $this->home_belongs_to_kvartal($homeId, $kvartalId)) {
                if (is_array($batch)) {
                    $aptTotal = (int) ($batch['aptTotal'][$homeId] ?? 0);
                    $freeTotal = (int) ($batch['freeTotal'][$homeId] ?? 0);
                }
                $live = $this->live_home_card_fields($home, $batch);
                $statusText = $live['statusText'];
                $statusTone = $live['statusTone'];
                $metaDelivery = $live['metaDelivery'];
                $metaAddress = $live['metaAddress'];
                $floors = $live['floors'];
                $sections = $live['sections'];
                $floorsLabel = $live['floorsLabel'];
                $sectionsLabel = $live['sectionsLabel'];
                $homeShow = isset($home['show']) ? (int) $home['show'] : null;
                if ($titleText === '' && $live['homeTitle'] !== '') {
                    $titleText = $live['homeTitle'];
                    $titleHtml = htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8');
                }
                if ($showAptLinks && is_array($batch) && !empty($batch['aptLinksByHome'][$homeId])) {
                    $aptLinks = $batch['aptLinksByHome'][$homeId];
                }
            }
        }

        if ($titleText === '' && !$homeId) {
            return null;
        }

        $labelX = isset($row['label_x']) && $row['label_x'] !== null && $row['label_x'] !== ''
            ? (int) $row['label_x']
            : null;
        $labelY = isset($row['label_y']) && $row['label_y'] !== null && $row['label_y'] !== ''
            ? (int) $row['label_y']
            : null;

        if ($kind === 'point' && ($labelX === null || $labelY === null)) {
            return null;
        }

        $linkUrl = trim((string) ($row['link_url'] ?? ''));
        list($ctaLabel, $ctaUrl) = $homeId
            ? $this->compute_cta_fields($statusTone, $linkUrl, $homeId)
            : [null, null];

        return [
            'id' => (int) $row['genplan_polygon_id'],
            'kind' => $kind,
            'homeId' => $homeId,
            'titleHtml' => $titleHtml,
            'titleText' => $titleText,
            'contentHtml' => $contentHtml !== '' ? $contentHtml : null,
            'showTitleDesktop' => (bool) $showTitleDesktop,
            'showTitleMobile' => (bool) $showTitleMobile,
            'showAptLinks' => (bool) $showAptLinks,
            'statusText' => $statusText,
            'statusTone' => $statusTone,
            'metaDelivery' => $metaDelivery,
            'metaAddress' => $metaAddress,
            'floors' => $floors,
            'sections' => $sections,
            'floorsLabel' => $floorsLabel,
            'sectionsLabel' => $sectionsLabel,
            'aptTotal' => $aptTotal,
            'freeTotal' => $freeTotal,
            'homeShow' => $homeShow,
            'aptLinks' => $aptLinks,
            'ctaLabel' => $ctaLabel,
            'ctaUrl' => $ctaUrl,
            'linkUrl' => $linkUrl,
            'labelX' => $labelX,
            'labelY' => $labelY,
            'points' => $points,
            'sortOrder' => (int) ($row['sort_order'] ?? 0),
        ];
    }

    // ─── HTML acts ───────────────────────────────────────────

    function act__index()
    {
        if (!check_access('admin')) {
            die('Ошибка доступа');
        }
        global $mysql, $t, $r;
        $t['h1'] = 'Интерактивный план';

        $rows = $mysql->get_arr(
            'SELECT homes_kvartal_id, title FROM homes_kvartal
             WHERE (del=0 OR del IS NULL OR del="")
             ORDER BY `order`, title'
        );
        if (!$rows) {
            $rows = [];
        }
        ?>
        <p>Фон интерактивного плана: загрузите в редакторе в <code>sites/em/genplans/{kvartal_id}.jpg</code> (также png/webp).</p>
        <table border="0" class="dtable">
            <thead>
            <tr>
                <th>ID</th>
                <th>ЖК</th>
                <th>Фон</th>
                <th>Объекты</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $k):
                $kid = (int) $k['homes_kvartal_id'];
                $has_file = $this->genplan_image_resolve($kid) !== null;
                $cnt = $mysql->get_arr(
                    'SELECT COUNT(*) AS c FROM genplan_polygons WHERE kvartal_id="' . $kid . '" AND del="0"',
                    1
                );
                $poly_count = (int) ($cnt['c'] ?? 0);
                ?>
                <tr>
                    <td><?= $kid ?></td>
                    <td><?= htmlspecialchars($k['title'] ?? '') ?></td>
                    <td><?= $has_file ? '<span style="color:green">есть</span>' : '<span style="color:#c45c00">нет</span>' ?></td>
                    <td><?= $poly_count ? $poly_count . ' полиг.' : '—' ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($r->acturl('genplans', 'editor', 'ctrind.php') . '&kvartal_id=' . $kid) ?>">Редактор</a>
                        &nbsp;|&nbsp;
                        <a href="<?= htmlspecialchars($r->acturl('genplans', 'editor', 'iframe_router.php') . '&kvartal_id=' . $kid) ?>" target="_blank" rel="noopener">полный экран</a>
                        <?php if ($has_file): ?>
                            &nbsp;|&nbsp;
                            <a href="<?= htmlspecialchars($r->acturl('genplans', 'widget_demo', 'iframe_router.php') . '&kvartal_id=' . $kid) ?>" target="_blank" rel="noopener">Виджет</a>
                        <?php endif; ?>
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
        global $t, $r;

        $kvartal_id = (int) ($_REQUEST['kvartal_id'] ?? 0);
        if (!$kvartal_id) {
            print 'Не указан kvartal_id';
            return;
        }
        $kvartal = $this->get_kvartal($kvartal_id);
        if (!$kvartal) {
            print 'ЖК не найден';
            return;
        }

        $found = $this->genplan_image_resolve($kvartal_id);
        $image_url = '';
        $image_w = 0;
        $image_h = 0;
        if ($found) {
            $size = @getimagesize($found['path']);
            if ($size) {
                $image_url = $this->genplan_image_url($kvartal_id) . '?v=' . (int) @filemtime($found['path']);
                $image_w = (int) $size[0];
                $image_h = (int) $size[1];
            }
        }

        $t['h1'] = 'Интерактивный план';

        $tpl = [
            'kvartal_id' => $kvartal_id,
            'kvartal_title' => (string) ($kvartal['title'] ?? ''),
            'image_url' => $image_url,
            'image_w' => $image_w,
            'image_h' => $image_h,
            'ajax_base' => '/sahmatka/ajax_router.php?ctr=genplans',
            'max_upload_bytes' => self::MAX_UPLOAD_BYTES,
            'widget_demo_url' => $r->acturl('genplans', 'widget_demo', 'iframe_router.php') . '&kvartal_id=' . $kvartal_id,
        ];
        $this->tpl($tpl, 'genplans', 'editor');
    }

    function act__widget_demo()
    {
        if (!check_access('admin')) {
            die('Ошибка доступа');
        }
        global $t;

        $kvartal_id = (int) ($_REQUEST['kvartal_id'] ?? 0);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $origin = $scheme . '://' . $host;

        $t['h1'] = 'Демо виджета интерактивного плана';

        $tpl = [
            'kvartal_id' => $kvartal_id,
            'api_base' => $origin . '/sahmatka/ajax_router.php?ctr=genplans',
            'script_src' => $origin . '/sahmatka/template/default/js/genplan_widget.js',
        ];
        $this->tpl($tpl, 'genplans', 'widget_demo');
    }

    // ─── Admin AJAX ──────────────────────────────────────────

    function act__get_polygons()
    {
        $this->require_admin();
        global $mysql;

        $kvartal_id = (int) ($_REQUEST['kvartal_id'] ?? 0);
        $rows = $mysql->get_arr(
            'SELECT * FROM genplan_polygons WHERE kvartal_id="' . $kvartal_id . '" AND del="0"
             ORDER BY sort_order, genplan_polygon_id'
        );

        $result = [];
        if ($rows) {
            foreach ($rows as $v) {
                $points = json_decode($v['points'], true);
                if (!is_array($points)) {
                    $points = [];
                }
                $homeId = isset($v['home_id']) && $v['home_id'] !== null && $v['home_id'] !== ''
                    ? (int) $v['home_id']
                    : null;
                if ($homeId === 0) {
                    $homeId = null;
                }
                $labelX = isset($v['label_x']) && $v['label_x'] !== null && $v['label_x'] !== ''
                    ? (int) $v['label_x']
                    : null;
                $labelY = isset($v['label_y']) && $v['label_y'] !== null && $v['label_y'] !== ''
                    ? (int) $v['label_y']
                    : null;
                $result[] = [
                    'id' => (int) $v['genplan_polygon_id'],
                    'homeId' => $homeId,
                    'title' => (string) ($v['title'] ?? ''),
                    'content' => (string) ($v['content'] ?? ''),
                    'showTitleDesktop' => (bool) $this->row_flag($v, 'show_title_desktop', 1),
                    'showTitleMobile' => (bool) $this->row_flag($v, 'show_title_mobile', 1),
                    'showAptLinks' => (bool) ($homeId ? $this->row_flag($v, 'show_apt_links', 0) : 0),
                    'linkUrl' => (string) ($v['link_url'] ?? ''),
                    'labelX' => $labelX,
                    'labelY' => $labelY,
                    'points' => $points,
                    'kind' => $this->polygon_kind_from_points($points),
                    'sortOrder' => (int) ($v['sort_order'] ?? 0),
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
        header('Content-Type: application/json; charset=utf-8');

        $kvartal_id = (int) ($_POST['kvartal_id'] ?? 0);
        $id = (int) ($_POST['genplan_polygon_id'] ?? 0);
        $points = json_decode($_POST['points'] ?? '', true);
        $title = (string) ($_POST['title'] ?? '');
        $content = (string) ($_POST['content'] ?? '');
        $home_id_raw = $_POST['home_id'] ?? '';
        $home_id = ($home_id_raw === '' || $home_id_raw === null) ? 0 : (int) $home_id_raw;

        if (!$kvartal_id || !is_array($points)) {
            echo json_encode(['success' => false, 'message' => 'Некорректные данные полигона']);
            return;
        }
        if (!$this->get_kvartal($kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'ЖК не найден']);
            return;
        }

        $points = $this->normalize_points($points);
        $isPoint = count($points) === 0;
        if (!$isPoint && count($points) < 3) {
            echo json_encode(['success' => false, 'message' => 'Полигон должен иметь минимум 3 точки']);
            return;
        }

        $plain = $this->title_plain($title);
        if ($home_id < 1 && $plain === '') {
            echo json_encode(['success' => false, 'message' => 'Укажите заголовок']);
            return;
        }

        if ($home_id > 0 && !$this->home_belongs_to_kvartal($home_id, $kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'Дом не принадлежит этому ЖК']);
            return;
        }

        $label_x = isset($_POST['label_x']) && $_POST['label_x'] !== '' ? (int) $_POST['label_x'] : null;
        $label_y = isset($_POST['label_y']) && $_POST['label_y'] !== '' ? (int) $_POST['label_y'] : null;

        if ($isPoint && ($label_x === null || $label_y === null)) {
            echo json_encode(['success' => false, 'message' => 'Укажите позицию точки']);
            return;
        }

        $show_title_desktop = $this->post_flag('show_title_desktop', 1);
        $show_title_mobile = $this->post_flag('show_title_mobile', 1);
        $show_apt_links = $home_id > 0 ? $this->post_flag('show_apt_links', 0) : 0;

        $link_url = trim((string) ($_POST['link_url'] ?? ''));
        $data = [
            'kvartal_id' => $kvartal_id,
            'title' => $title,
            'content' => $content,
            'show_title_desktop' => $show_title_desktop,
            'show_title_mobile' => $show_title_mobile,
            'show_apt_links' => $show_apt_links,
            'link_url' => $link_url,
            'points' => json_encode($points),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'del' => 0,
        ];
        // home_id / label_*: update_for_key пишет NULL; insert без ключа = DEFAULT NULL
        if ($home_id > 0) {
            $data['home_id'] = $home_id;
        }
        if ($label_x !== null) {
            $data['label_x'] = $label_x;
        }
        if ($label_y !== null) {
            $data['label_y'] = $label_y;
        }

        if ($id) {
            $existing = $mysql->get_for_key('genplan_polygons', 'genplan_polygon_id', $id);
            if (!$existing || (int) $existing['kvartal_id'] !== $kvartal_id) {
                echo json_encode(['success' => false, 'message' => 'Полигон не найден для этого ЖК']);
                return;
            }
            $data['home_id'] = $home_id > 0 ? $home_id : null;
            $data['label_x'] = $label_x;
            $data['label_y'] = $label_y;
            // update_for_key: '' → NULL; title NOT NULL — пустой title пишем отдельным SQL
            if ($title === '') {
                unset($data['title']);
            }
            $mysql->update_for_key('genplan_polygons', 'genplan_polygon_id', $id, $data);
            if ($title === '') {
                $mysql->sql(
                    'UPDATE genplan_polygons SET `title`="" WHERE `genplan_polygon_id`="' . (int) $id . '"'
                );
            }
        } else {
            $id = $mysql->insert('genplan_polygons', $data);
        }

        echo json_encode(['success' => true, 'id' => (int) $id]);
    }

    function act__delete_polygon()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $id = (int) ($_POST['genplan_polygon_id'] ?? 0);
        $kvartal_id = (int) ($_POST['kvartal_id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Не указан id']);
            return;
        }

        $existing = $mysql->get_for_key('genplan_polygons', 'genplan_polygon_id', $id);
        if (!$existing || ($kvartal_id && (int) $existing['kvartal_id'] !== $kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'Полигон не найден для этого ЖК']);
            return;
        }

        $mysql->sql('UPDATE genplan_polygons SET del="1" WHERE genplan_polygon_id="' . $id . '"');
        echo json_encode(['success' => true, 'id' => $id]);
    }

    function act__upload_image()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $kvartal_id = (int) ($_POST['kvartal_id'] ?? 0);
        if (!$kvartal_id) {
            echo json_encode(['success' => false, 'message' => 'Не указан kvartal_id']);
            return;
        }
        if (!$this->get_kvartal($kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'ЖК не найден']);
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
            echo json_encode([
                'success' => false,
                'message' => 'Размер файла должен быть до ' . (int) (self::MAX_UPLOAD_BYTES / 1024 / 1024) . ' МБ',
            ]);
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

        $dir = $this->genplans_dir();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось создать каталог на сервере']);
            return;
        }

        $existing = $this->genplan_image_resolve($kvartal_id);
        $replaced = $existing !== null;
        $target = $this->genplan_image_target_path($kvartal_id, $ext);
        try {
            $this->assert_within_genplans($target);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка проверки пути сохранения']);
            return;
        }

        if (!@move_uploaded_file($file['tmp_name'], $target)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл на сервере']);
            return;
        }
        @chmod($target, 0644);

        foreach (self::IMAGE_EXT_PRIORITY as $otherExt) {
            if ($otherExt === $ext) {
                continue;
            }
            $siblingPath = $this->genplan_image_target_path($kvartal_id, $otherExt);
            if (is_file($siblingPath)) {
                @unlink($siblingPath);
            }
        }

        echo json_encode([
            'success' => true,
            'imageUrl' => $this->genplan_image_url($kvartal_id) . '?v=' . (int) @filemtime($target),
            'imageWidth' => $dims['width'],
            'imageHeight' => $dims['height'],
            'ext' => $ext,
            'replaced' => $replaced,
        ], JSON_UNESCAPED_UNICODE);
    }

    function act__clear_all()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $kvartal_id = (int) ($_POST['kvartal_id'] ?? 0);
        if (!$kvartal_id) {
            echo json_encode(['success' => false, 'message' => 'Не указан kvartal_id']);
            return;
        }
        if (!$this->get_kvartal($kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'ЖК не найден']);
            return;
        }

        $mysql->sql(
            'UPDATE genplan_polygons SET del="1" WHERE kvartal_id="' . $kvartal_id . '" AND del="0"'
        );
        $cleared = (int) $mysql->count;

        $removedFiles = 0;
        foreach (self::IMAGE_EXT_PRIORITY as $ext) {
            $path = $this->genplan_image_target_path($kvartal_id, $ext);
            try {
                $this->assert_within_genplans($path);
            } catch (Throwable $e) {
                continue;
            }
            if (is_file($path) && @unlink($path)) {
                $removedFiles++;
            }
        }

        echo json_encode([
            'success' => true,
            'cleared' => $cleared,
            'removedFiles' => $removedFiles,
            'fileRemoved' => $removedFiles > 0,
        ]);
    }

    function act__homes_options()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $kvartal_id = (int) ($_REQUEST['kvartal_id'] ?? 0);
        if (!$kvartal_id) {
            echo json_encode(['success' => false, 'message' => 'Не указан kvartal_id']);
            return;
        }

        $rows = $mysql->get_arr(
            'SELECT home_id, title FROM homes
             WHERE CAST(kvartal AS UNSIGNED)="' . $kvartal_id . '"
               AND (del=0 OR del IS NULL)
             ORDER BY `order`, title'
        );
        $homes = [];
        if ($rows) {
            foreach ($rows as $h) {
                $homes[] = [
                    'id' => (int) $h['home_id'],
                    'title' => (string) ($h['title'] ?? ''),
                ];
            }
        }
        echo json_encode(['success' => true, 'homes' => $homes], JSON_UNESCAPED_UNICODE);
    }

    function act__home_autofill()
    {
        $this->require_admin();
        header('Content-Type: application/json; charset=utf-8');

        $kvartal_id = (int) ($_REQUEST['kvartal_id'] ?? 0);
        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        if (!$kvartal_id || !$home_id) {
            echo json_encode(['success' => false, 'message' => 'Не указаны kvartal_id/home_id']);
            return;
        }
        if (!$this->home_belongs_to_kvartal($home_id, $kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'Дом не принадлежит этому ЖК']);
            return;
        }
        $home = $this->get_home_row($home_id);
        if (!$home) {
            echo json_encode(['success' => false, 'message' => 'Дом не найден']);
            return;
        }
        $batch = $this->batch_home_widget_stats([$home_id]);
        $live = $this->live_home_card_fields($home, $batch);
        $suggest = $live['homeTitle'];
        if ($suggest !== '' && mb_stripos($suggest, 'дом') === false) {
            $suggest = 'Дом ' . $suggest;
        }
        echo json_encode([
            'success' => true,
            'titleSuggest' => $suggest,
            'statusText' => $live['statusText'],
            'statusTone' => $live['statusTone'],
            'metaDelivery' => $live['metaDelivery'],
            'metaAddress' => $live['metaAddress'],
            'floorsLabel' => $live['floorsLabel'],
            'sectionsLabel' => $live['sectionsLabel'],
        ], JSON_UNESCAPED_UNICODE);
    }

    // ─── Public ──────────────────────────────────────────────

    function act__widget_data()
    {
        global $mysql;

        $kvartal_id = (int) ($_REQUEST['kvartal_id'] ?? 0);
        if (!$kvartal_id) {
            $this->widget_json_error('Не указан kvartal_id');
            return;
        }
        $kvartal = $this->get_kvartal($kvartal_id);
        if (!$kvartal) {
            $this->widget_json_error('ЖК не найден');
            return;
        }

        $path = $this->genplan_image_fs_path($kvartal_id);
        if (!is_file($path)) {
            $this->widget_json_error('Интерактивный план не найден');
            return;
        }
        $size = @getimagesize($path);
        if (!$size) {
            $this->widget_json_error('Не удалось прочитать изображение');
            return;
        }

        $rows = $mysql->get_arr(
            'SELECT * FROM genplan_polygons WHERE kvartal_id="' . $kvartal_id . '" AND del="0"
             ORDER BY sort_order, genplan_polygon_id'
        );
        $homeIds = [];
        if ($rows) {
            foreach ($rows as $row) {
                $hid = isset($row['home_id']) && $row['home_id'] !== null && $row['home_id'] !== ''
                    ? (int) $row['home_id']
                    : 0;
                if ($hid > 0) {
                    $homeIds[] = $hid;
                }
            }
        }
        $batch = $this->batch_home_widget_stats($homeIds);

        $objects = [];
        if ($rows) {
            foreach ($rows as $row) {
                $payload = $this->polygon_public_payload($row, $batch);
                if ($payload) {
                    $objects[] = $payload;
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'kvartalId' => $kvartal_id,
            'title' => (string) ($kvartal['title'] ?? ''),
            'imageUrl' => $this->genplan_image_absolute_url($kvartal_id),
            'imageWidth' => (int) $size[0],
            'imageHeight' => (int) $size[1],
            'objects' => $objects,
        ], JSON_UNESCAPED_UNICODE);
    }
}
