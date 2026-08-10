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
     * Live card fields from homes (same rules as home_autofill).
     * @return array{statusText:?string,statusTone:?string,metaDelivery:?string,metaAddress:?string,homeTitle:string}
     */
    function live_home_card_fields($home)
    {
        if (!$home) {
            return [
                'statusText' => null,
                'statusTone' => null,
                'metaDelivery' => null,
                'metaAddress' => null,
                'homeTitle' => '',
            ];
        }
        $homeTitle = trim((string) ($home['title'] ?? ''));
        if ($homeTitle === '' && !empty($home['long_title'])) {
            $homeTitle = trim((string) $home['long_title']);
        }

        $complite = (int) ($home['complite'] ?? 0);
        if ($complite === 1) {
            $statusText = 'Дом сдан';
            $statusTone = 'ok';
        } else {
            $statusText = 'Строится';
            $statusTone = 'warn';
        }

        $metaDelivery = null;
        if (!function_exists('stat_free_format_delivery_quarter')) {
            $helper = dirname(__DIR__, 2) . '/inc/stat_free_apartments_helpers.php';
            if (is_file($helper)) {
                include_once $helper;
            }
        }
        if (function_exists('stat_free_format_delivery_quarter')) {
            $label = stat_free_format_delivery_quarter(
                $home['delivery_date'] ?? null,
                $home['ready_quarter'] ?? null,
                $home['built_year'] ?? null
            );
            if ($label && $label !== '—') {
                $metaDelivery = 'Сдан: ' . $label;
            }
        }

        $addr = trim((string) ($home['adress'] ?? ''));
        $metaAddress = $addr !== '' ? ('Адрес: ' . $addr) : null;

        return [
            'statusText' => $statusText,
            'statusTone' => $statusTone,
            'metaDelivery' => $metaDelivery,
            'metaAddress' => $metaAddress,
            'homeTitle' => $homeTitle,
        ];
    }

    function polygon_public_payload($row)
    {
        $points = json_decode($row['points'] ?? '', true);
        if (!is_array($points) || count($points) < 3) {
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

        $statusText = null;
        $statusTone = null;
        $metaDelivery = null;
        $metaAddress = null;

        if ($homeId) {
            $home = $this->get_home_row($homeId);
            $kvartalId = (int) $row['kvartal_id'];
            if ($home && $this->home_belongs_to_kvartal($homeId, $kvartalId)) {
                $live = $this->live_home_card_fields($home);
                $statusText = $live['statusText'];
                $statusTone = $live['statusTone'];
                $metaDelivery = $live['metaDelivery'];
                $metaAddress = $live['metaAddress'];
                if ($titleText === '' && $live['homeTitle'] !== '') {
                    $titleText = $live['homeTitle'];
                    $titleHtml = htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        if ($titleText === '' && !$homeId) {
            // invalid public object without title
            return null;
        }

        $labelX = isset($row['label_x']) && $row['label_x'] !== null && $row['label_x'] !== ''
            ? (int) $row['label_x']
            : null;
        $labelY = isset($row['label_y']) && $row['label_y'] !== null && $row['label_y'] !== ''
            ? (int) $row['label_y']
            : null;

        return [
            'id' => (int) $row['genplan_polygon_id'],
            'homeId' => $homeId,
            'titleHtml' => $titleHtml,
            'titleText' => $titleText,
            'statusText' => $statusText,
            'statusTone' => $statusTone,
            'metaDelivery' => $metaDelivery,
            'metaAddress' => $metaAddress,
            'linkUrl' => trim((string) ($row['link_url'] ?? '')),
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
                    continue;
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
                    'linkUrl' => (string) ($v['link_url'] ?? ''),
                    'labelX' => $labelX,
                    'labelY' => $labelY,
                    'points' => $points,
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
        $home_id_raw = $_POST['home_id'] ?? '';
        $home_id = ($home_id_raw === '' || $home_id_raw === null) ? 0 : (int) $home_id_raw;

        if (!$kvartal_id || !is_array($points) || count($points) < 3) {
            echo json_encode(['success' => false, 'message' => 'Некорректные данные полигона']);
            return;
        }
        if (!$this->get_kvartal($kvartal_id)) {
            echo json_encode(['success' => false, 'message' => 'ЖК не найден']);
            return;
        }

        $points = $this->normalize_points($points);
        if (count($points) < 3) {
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

        $data = [
            'kvartal_id' => $kvartal_id,
            'title' => $title,
            'link_url' => trim((string) ($_POST['link_url'] ?? '')),
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
            $mysql->update_for_key('genplan_polygons', 'genplan_polygon_id', $id, $data);
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
        $live = $this->live_home_card_fields($home);
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
        $objects = [];
        if ($rows) {
            foreach ($rows as $row) {
                $payload = $this->polygon_public_payload($row);
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
