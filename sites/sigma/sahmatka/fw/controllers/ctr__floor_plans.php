<?
/**
 * Редактор поэтажных планов (Stage 1, задача 4).
 * Разметка квартир на JPG плана этажа: sahmatka/pbplans/{home_id}/floor/{section}/{floor}.jpg
 * Аналог ctr__facades, но единица разметки — квартира (1 активный полигон на квартиру),
 * а не этаж (на фасаде у этажа может быть несколько полигонов).
 * Публичный JSON для виджета — в ctr__facades::act__floor_plan_data (аудит C2),
 * он дёргает floor_plan_public_payload() этого класса, чтобы не дублировать SQL.
 */
class ctr__floor_plans extends ctr__
{
    var $table = 'floor_plan_polygons';
    var $key_filed = 'floor_plan_polygon_id';
    var $ctr = 'floor_plans';
    var $title = 'Разметка планов этажей';

    /** Stage 2 (аудит C3/H5): порядок поиска расширений фона этажа — один файл на этаж. */
    const FLOOR_IMAGE_EXT_PRIORITY = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    /** Единый верхний предел этажа — синхронно с act__floor_jpg_map (аудит M6). */
    const MAX_FLOOR = 200;
    /** Лимит размера загружаемого фона (аудит M1). */
    const MAX_UPLOAD_BYTES = 20 * 1024 * 1024;
    /** Allow-list тегов SVG после санитизации (аудит C1). */
    const SVG_ALLOWED_TAGS = [
        'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'defs', 'lineargradient', 'radialgradient', 'stop', 'clippath',
        'use', 'style', 'title', 'desc',
    ];

    function require_admin()
    {
        if (!check_access('admin')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Ошибка доступа']);
            exit;
        }
    }

    /** @return string абсолютный путь к каталогу pbplans/{home_id} */
    function pbplans_dir($home_id)
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'pbplans' . DIRECTORY_SEPARATOR . (int) $home_id;
    }

    /** @return string абсолютный путь к каталогу секции: pbplans/{home_id}/floor/{section} */
    function floor_section_dir($home_id, $section)
    {
        return $this->pbplans_dir($home_id)
            . DIRECTORY_SEPARATOR . 'floor'
            . DIRECTORY_SEPARATOR . (int) $section;
    }

    /**
     * Найти существующий файл фона этажа среди поддерживаемых расширений (Stage 2, аудит H5).
     * @return array{ext:string, path:string}|null
     */
    function floor_image_resolve($home_id, $section, $floor)
    {
        $dir = $this->floor_section_dir($home_id, $section);
        foreach (self::FLOOR_IMAGE_EXT_PRIORITY as $ext) {
            $path = $dir . DIRECTORY_SEPARATOR . (int) $floor . '.' . $ext;
            if (is_file($path)) {
                return ['ext' => $ext, 'path' => $path];
            }
        }
        return null;
    }

    /** @return string абсолютный путь к JPG плана этажа (обратная совместимость: только резолв, без построения нового пути) */
    function floor_image_fs_path($home_id, $section, $floor)
    {
        $found = $this->floor_image_resolve($home_id, $section, $floor);
        return $found ? $found['path'] : null;
    }

    /** @return string целевой путь для сохранения фона конкретного расширения (upload) */
    function floor_image_target_path($home_id, $section, $floor, $ext)
    {
        return $this->floor_section_dir($home_id, $section)
            . DIRECTORY_SEPARATOR . (int) $floor . '.' . $ext;
    }

    /** @return string root-relative URL плана этажа; $ext — если известен (иначе резолвится) */
    function floor_image_url($home_id, $section, $floor, $ext = null)
    {
        if ($ext === null) {
            $found = $this->floor_image_resolve($home_id, $section, $floor);
            $ext = $found ? $found['ext'] : 'jpg';
        }
        return '/sahmatka/pbplans/' . (int) $home_id . '/floor/' . (int) $section . '/' . (int) $floor . '.' . $ext;
    }

    /**
     * Абсолютный URL плана для публичного виджета (embed на чужих доменах) —
     * симметрично facade_image_absolute_url в ctr__facades.
     */
    function floor_image_absolute_url($home_id, $section, $floor)
    {
        $found = $this->floor_image_resolve($home_id, $section, $floor);
        $rel = $this->floor_image_url($home_id, $section, $floor, $found ? $found['ext'] : null);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $scheme . '://' . $host . $rel;
        if ($found) {
            $url .= '?v=' . (int) @filemtime($found['path']);
        }
        return $url;
    }

    /**
     * Размеры фона независимо от формата (аудит §3.3): растровые — getimagesize,
     * SVG — viewBox/width/height из XML (getimagesize не читает SVG надёжно).
     * @return array{width:int, height:int}|null
     */
    function floor_image_dimensions($path, $ext)
    {
        if ($ext === 'svg') {
            $raw = @file_get_contents($path);
            if ($raw === false) {
                return null;
            }
            $prevErrors = libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $ok = @$doc->loadXML($raw, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($prevErrors);
            if (!$ok) {
                return null;
            }
            $dims = $this->svg_root_dimensions($doc);
            return $dims ? ['width' => $dims[0], 'height' => $dims[1]] : null;
        }

        $size = @getimagesize($path);
        if (!$size) {
            return null;
        }
        return ['width' => (int) $size[0], 'height' => (int) $size[1]];
    }

    /** @return array{0:int,1:int}|null [width, height] из корневого <svg viewBox|width|height> */
    function svg_root_dimensions(DOMDocument $doc)
    {
        $root = $doc->documentElement;
        if (!$root) {
            return null;
        }

        $viewBox = $root->getAttribute('viewBox');
        if ($viewBox) {
            $parts = preg_split('/[\s,]+/', trim($viewBox));
            if (count($parts) === 4) {
                $w = (float) $parts[2];
                $h = (float) $parts[3];
                if ($w > 0 && $h > 0) {
                    return [(int) round($w), (int) round($h)];
                }
            }
        }

        $w = (float) preg_replace('/[^0-9.]/', '', (string) $root->getAttribute('width'));
        $h = (float) preg_replace('/[^0-9.]/', '', (string) $root->getAttribute('height'));
        if ($w > 0 && $h > 0) {
            return [(int) round($w), (int) round($h)];
        }
        return null;
    }

    /**
     * Containment-проверка (аудит H4): целевой путь должен физически лежать
     * внутри pbplans/{home_id}/ — защита от регрессии в резолвере путей.
     * @throws RuntimeException если путь вышел за пределы каталога дома
     */
    function assert_within_pbplans($target_path, $home_id)
    {
        $base = realpath($this->pbplans_dir($home_id));
        $real = realpath(dirname($target_path));
        if ($base === false || $real === false || strpos($real, $base) !== 0) {
            throw new RuntimeException('Path containment check failed for ' . $target_path);
        }
    }

    /** Маппинг getimagesize()[2] (IMAGETYPE_*) на расширение файла на диске. */
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

    /** Человекочитаемое сообщение по коду $_FILES[...]['error'] (аудит M1). */
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

    /**
     * Allow-list санитизация SVG (аудит C1/C2/C3): парсинг без внешних entity/DTD,
     * удаление всех тегов и атрибутов вне whitelist. Возвращает очищенный XML
     * или null, если файл не является валидным/безопасным SVG.
     * @return string|null
     */
    function sanitize_svg_content($raw)
    {
        $raw = is_string($raw) ? trim($raw) : '';
        if ($raw === '' || stripos($raw, '<svg') === false) {
            return null;
        }

        $prevEntityLoader = null;
        if (function_exists('libxml_disable_entity_loader')) {
            // @ — устарело/no-op в PHP 8, но безопасно вызвать; поведение по умолчанию уже "выключено".
            $prevEntityLoader = @libxml_disable_entity_loader(true);
        }
        $prevErrors = libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        // Только LIBXML_NONET: без LIBXML_NOENT (не раскрываем entity) и без DTDLOAD/DTDVALID (анти-XXE, аудит C2).
        $ok = @$doc->loadXML($raw, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);
        if ($prevEntityLoader !== null) {
            @libxml_disable_entity_loader($prevEntityLoader);
        }

        if (!$ok || $doc->doctype) {
            // DOCTYPE запрещаем даже без раскрытия entity — лишняя поверхность атаки не нужна.
            return null;
        }

        $root = $doc->documentElement;
        if (!$root || strtolower($root->localName) !== 'svg') {
            return null;
        }

        $this->sanitize_svg_attributes($root);
        $this->sanitize_svg_children($root);

        $result = $doc->saveXML($root);
        return $result ?: null;
    }

    /** Рекурсивно вычищает дерево SVG до allow-list тегов (аудит C1). */
    function sanitize_svg_children(DOMElement $node)
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->localName);
                if (!in_array($tag, self::SVG_ALLOWED_TAGS, true)) {
                    $node->removeChild($child);
                    continue;
                }
                $this->sanitize_svg_attributes($child);
                $this->sanitize_svg_children($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
            }
        }
    }

    /**
     * Вычищает атрибуты одного узла: on*, href/xlink:href кроме "#…", а также любой
     * url(...)/expression()/@import в ЛЮБОМ атрибуте (не только style) — например
     * fill="url(javascript:...)" / filter="url(...)" на обычных презентационных
     * атрибутах (rect, path, use...) — тот же вектор, что через style, но без style
     * (доп. фикс к аудиту C1, найден собственным smoke-тестом sanitize_svg_content).
     * Текст <style> фильтруется тем же паттерном (аудит M3 — CSS заливки/градиенты
     * по #id остаются, режем только внешние/скриптовые схемы).
     */
    function sanitize_svg_attributes(DOMElement $el)
    {
        $tag = strtolower($el->localName);
        $attrs = [];
        foreach ($el->attributes as $attr) {
            $attrs[] = $attr;
        }
        foreach ($attrs as $attr) {
            $name = strtolower($attr->nodeName);
            $value = (string) $attr->nodeValue;

            if (strpos($name, 'on') === 0) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }
            if ($name === 'href' || $name === 'xlink:href') {
                if (strpos(trim($value), '#') !== 0) {
                    $el->removeAttribute($attr->nodeName);
                }
                continue;
            }
            $clean = $this->sanitize_attribute_value($value);
            if ($clean !== $value) {
                $el->setAttribute($attr->nodeName, $clean);
            }
        }

        if ($tag === 'style') {
            $clean = $this->sanitize_attribute_value($el->textContent);
            while ($el->firstChild) {
                $el->removeChild($el->firstChild);
            }
            $el->appendChild($el->ownerDocument->createTextNode($clean));
        }
    }

    /**
     * @return string значение атрибута/CSS без expression()/@import и без url(...) с
     * внешними/скриптовыми схемами; url(#internal) — на defs/gradient — сохраняется.
     */
    function sanitize_attribute_value($value)
    {
        $value = (string) preg_replace('/expression\s*\(/i', '', (string) $value);
        $value = (string) preg_replace('/@import/i', '', $value);
        // (?:[^()]|\([^()]*\))* — допускает один уровень вложенных скобок внутри url(...),
        // иначе url(javascript:alert(1)) обрезался бы по первой ")" и оставлял мусорный хвост.
        $value = (string) preg_replace_callback('/url\s*\(\s*([\'"]?)((?:[^()]|\([^()]*\))*)\1\s*\)/i', function ($m) {
            $inner = trim($m[2]);
            return (strpos($inner, '#') === 0) ? ('url(' . $inner . ')') : '';
        }, $value);
        return $value;
    }

    /**
     * Секции дома — код синхронизирован с ctr__facades::get_home_sections()
     * (тот же смысл: логический section_id из homes_sections, не PK).
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

    /**
     * Как в шахматках (sahmatka::get_data_appart_arr / disp_home_p):
     * фильтр только по бизнес-полю apartaments.home_id (= homes.home_id из URL),
     * НЕ по homes.homes_id (PK). Для Сигма Плаза: home_id=60, homes_id=53 —
     * в apartaments лежит 60; PK 53 принадлежит чужим/старым рядам и нельзя подмешивать.
     *
     * Секция: apartaments.section_id = логический номер (1, 2…), тот же что
     * homes_sections.section_id и что передаёт display_home в disp_home_p.
     * (Редко бывает FK на homes_sections_id — OR на этот случай.)
     */
    function apartment_section_sql($alias, $section_logical)
    {
        $section_logical = (int) $section_logical;
        $a = $alias;
        return '(
            ' . $a . '.section_id = "' . $section_logical . '"
            OR EXISTS (
                SELECT 1 FROM homes_sections hs
                WHERE hs.homes_sections_id = ' . $a . '.section_id
                  AND hs.section_id = "' . $section_logical . '"
            )
        )';
    }

    /**
     * Квартиры этажа с флагом разметки — тот же ключ, что у шахматки:
     * home_id + section_id(логический) + floor.
     */
    function list_apartments_with_marks($home_id, $section_logical, $floor)
    {
        global $mysql;
        $home_id = (int) $home_id;
        $section_logical = (int) $section_logical;
        $floor = (int) $floor;

        $sql = '
            SELECT a.apartament_id, a.apartment_num, a.rooms, a.area, a.status, a.status2,
                   fpp.floor_plan_polygon_id AS marked_id
            FROM apartaments a
            LEFT JOIN floor_plan_polygons fpp
                   ON fpp.apartament_id = a.apartament_id AND fpp.del = "0"
            WHERE a.home_id = "' . $home_id . '"
              AND a.floor = "' . $floor . '"
              AND ' . $this->apartment_section_sql('a', $section_logical) . '
            ORDER BY a.apartment_num ASC
        ';
        $rows = $mysql->get_arr($sql);
        $result = [];
        if ($rows) {
            foreach ($rows as $row) {
                $result[] = [
                    'apartamentId' => (int) $row['apartament_id'],
                    'apartmentNum' => (int) $row['apartment_num'],
                    'rooms'        => (string) ($row['rooms'] ?? ''),
                    'area'         => (string) ($row['area'] ?? ''),
                    'status'       => $row['status2'] ?? $row['status'] ?? null,
                    'marked'       => !empty($row['marked_id']),
                ];
            }
        }
        return $result;
    }

    /**
     * Одна квартира по apartament_id, с проверкой принадлежности (home_id, секция, этаж).
     */
    function find_apartment($home_id, $section_logical, $floor, $apartament_id)
    {
        global $mysql;
        $sql = '
            SELECT a.apartament_id, a.apartment_num
            FROM apartaments a
            WHERE a.home_id = "' . (int) $home_id . '"
              AND a.floor = "' . (int) $floor . '"
              AND a.apartament_id = "' . (int) $apartament_id . '"
              AND ' . $this->apartment_section_sql('a', (int) $section_logical) . '
            LIMIT 1
        ';
        $row = $mysql->get_arr($sql, 1);
        return $row ?: null;
    }

    function act__index()
    {
        if (!check_access('admin')) {
            die('Ошибка доступа');
        }
        global $mysql, $t, $r;
        $t['h1'] = 'Разметка планов этажей';

        $homes = $mysql->get_arr('SELECT home_id, title FROM homes WHERE 1=1 ORDER BY `order`, title');
        ?>
        <p>Планы этажей — PNG/JPG/WEBP/SVG в <code>sites/sigma/sahmatka/pbplans/{home_id}/floor/{section}/{floor}.{ext}</code> (загружаются кнопкой «Загрузить фон» в редакторе этажа).</p>
        <table border="0" class="dtable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дом</th>
                <th>Планов (любой формат)</th>
                <th>Размечено квартир</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($homes as $h):
                $hid = (int) $h['home_id'];
                $jpg_count = 0;
                $dir = $this->pbplans_dir($hid) . DIRECTORY_SEPARATOR . 'floor';
                if (is_dir($dir)) {
                    // Stage 2 (аудит H5): счётчик планов — любой поддерживаемый формат, не только jpg.
                    foreach (self::FLOOR_IMAGE_EXT_PRIORITY as $ext) {
                        foreach (glob($dir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.' . $ext) as $f) {
                            $jpg_count++;
                        }
                    }
                }
                $cnt = $mysql->get_arr('SELECT COUNT(*) AS c FROM floor_plan_polygons WHERE home_id="' . $hid . '" AND del="0"', 1);
                $poly_count = (int) ($cnt['c'] ?? 0);
                ?>
                <tr>
                    <td><?= $hid ?></td>
                    <td><?= htmlspecialchars($h['title']) ?></td>
                    <td><?= $jpg_count ? $jpg_count . ' файл(ов)' : '<span style="color:#c45c00">нет</span>' ?></td>
                    <td><?= $poly_count ? $poly_count . ' кв.' : '—' ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($r->acturl('floor_plans', 'editor', 'ctrind.php') . '&home_id=' . $hid) ?>">Редактор</a>
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
        global $mysql, $t;

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

        $sections = $this->get_home_sections($home);

        $t['h1'] = 'Разметка планов этажей — ' . $home['title'];

        $tpl = [
            'home_id'          => $home_id,
            'home_title'       => $home['title'],
            'sections'         => $sections,
            'ajax_base'        => '/sahmatka/ajax_router.php?ctr=floor_plans',
            'max_upload_bytes' => self::MAX_UPLOAD_BYTES,
        ];
        $this->tpl($tpl, 'floor_plans', 'editor');
    }

    /**
     * Метаданные этажа для редактора: размер JPG (или success:false, если файла нет)
     * + список квартир этажа с флагом разметки (§2.3).
     */
    function act__get_floor_meta()
    {
        $this->require_admin();
        global $mysql;

        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        $section = (int) ($_REQUEST['section'] ?? 1);
        $floor   = (int) ($_REQUEST['floor'] ?? 0);

        header('Content-Type: application/json; charset=utf-8');

        if (!$home_id || !$floor) {
            echo json_encode(['success' => false, 'message' => 'Не указаны home_id/floor']);
            return;
        }

        $apartments = $this->list_apartments_with_marks($home_id, $section, $floor);

        // Stage 2 (аудит H5): резолвим любой поддерживаемый формат, не только jpg.
        $found = $this->floor_image_resolve($home_id, $section, $floor);
        if (!$found) {
            echo json_encode([
                'success'    => false,
                'message'    => 'План этажа не найден: ' . $this->floor_image_url($home_id, $section, $floor, 'jpg'),
                'apartments' => $apartments,
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $dims = $this->floor_image_dimensions($found['path'], $found['ext']);
        if (!$dims) {
            echo json_encode(['success' => false, 'message' => 'Не удалось прочитать изображение плана']);
            return;
        }

        echo json_encode([
            'success'     => true,
            'imageUrl'    => $this->floor_image_url($home_id, $section, $floor, $found['ext']) . '?v=' . (int) @filemtime($found['path']),
            'imageWidth'  => $dims['width'],
            'imageHeight' => $dims['height'],
            'hasImage'    => true,
            'imageExt'    => $found['ext'],
            'apartments'  => $apartments,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Карта «этаж → есть ли JPG / сколько квартир» для секции.
     * Счётчик квартир — как в шахматках: home_id + section_id(логический).
     */
    function act__floor_jpg_map()
    {
        $this->require_admin();
        global $mysql;

        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        $section = (int) ($_REQUEST['section'] ?? 1);
        $max_floor = (int) ($_REQUEST['max_floor'] ?? 30);
        if ($max_floor < 1) {
            $max_floor = 30;
        }
        if ($max_floor > self::MAX_FLOOR) {
            $max_floor = self::MAX_FLOOR;
        }

        $aptCounts = [];
        $rows = $mysql->get_arr(
            'SELECT a.floor, COUNT(*) AS c
             FROM apartaments a
             WHERE a.home_id = "' . $home_id . '"
               AND ' . $this->apartment_section_sql('a', $section) . '
             GROUP BY a.floor'
        );
        if ($rows) {
            foreach ($rows as $row) {
                $aptCounts[(int) $row['floor']] = (int) $row['c'];
            }
        }

        $floors = [];
        $apartments = [];
        for ($f = 1; $f <= $max_floor; $f++) {
            // Stage 2 (аудит H5): «есть файл» — любой поддерживаемый формат, не только jpg.
            $floors[$f] = $this->floor_image_resolve($home_id, $section, $f) !== null;
            $apartments[$f] = isset($aptCounts[$f]) ? $aptCounts[$f] : 0;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'     => true,
            'floors'      => $floors,
            'apartments'  => $apartments,
        ]);
    }

    function act__get_polygons()
    {
        $this->require_admin();
        global $mysql;

        $home_id = (int) ($_REQUEST['home_id'] ?? 0);
        $section = (int) ($_REQUEST['section'] ?? 1);
        $floor   = (int) ($_REQUEST['floor'] ?? 0);

        $rows = $mysql->get_arr(
            'SELECT * FROM floor_plan_polygons
             WHERE home_id="' . $home_id . '" AND section="' . $section . '" AND floor="' . $floor . '" AND del="0"
             ORDER BY sort_order, floor_plan_polygon_id'
        );

        $result = [];
        if ($rows) {
            foreach ($rows as $v) {
                $points = json_decode($v['points'], true);
                if (!is_array($points)) {
                    continue;
                }
                $result[] = [
                    'id'            => (int) $v['floor_plan_polygon_id'],
                    'apartamentId'  => (int) $v['apartament_id'],
                    'apartmentNum'  => (int) $v['apartment_num'],
                    'label'         => $v['label'],
                    'points'        => $points,
                    'color'         => $v['color'] ?: '#4da3ff',
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

        $home_id       = (int) ($_POST['home_id'] ?? 0);
        $section       = (int) ($_POST['section'] ?? 1);
        $floor         = (int) ($_POST['floor'] ?? 0);
        $id            = (int) ($_POST['floor_plan_polygon_id'] ?? 0);
        $apartament_id = (int) ($_POST['apartament_id'] ?? 0);
        $points        = json_decode($_POST['points'] ?? '', true);

        if ($section < 1) {
            $section = 1;
        }

        if (!$home_id || !$floor || !$apartament_id || !is_array($points) || count($points) < 3) {
            echo json_encode(['success' => false, 'message' => 'Некорректные данные полигона']);
            return;
        }

        $apartment = $this->find_apartment($home_id, $section, $floor, $apartament_id);
        if (!$apartment) {
            echo json_encode(['success' => false, 'message' => unit_label_cap('nom') . ' не найдена для этого дома/секции/этажа']);
            return;
        }

        $points = $this->normalize_points($points);
        if (count($points) < 3) {
            echo json_encode(['success' => false, 'message' => 'Полигон должен иметь минимум 3 точки']);
            return;
        }

        // Уникальность: не больше 1 активного полигона на квартиру (аудит §2.1 / M6 — del=0).
        $dup = $mysql->get_arr(
            'SELECT floor_plan_polygon_id FROM floor_plan_polygons
             WHERE apartament_id="' . $apartament_id . '" AND del="0"'
            . ($id ? ' AND floor_plan_polygon_id != "' . $id . '"' : ''),
            1
        );
        if ($dup) {
            echo json_encode(['success' => false, 'message' => 'У этой квартиры уже есть активный полигон (id ' . $dup['floor_plan_polygon_id'] . ')']);
            return;
        }

        $overlap = $this->find_overlapping_polygon($home_id, $section, $floor, $points, $id);
        if ($overlap) {
            echo json_encode([
                'success'    => false,
                'message'    => 'Полигон перекрывает существующий (квартира №' . (int) $overlap['apartment_num'] . '). Отредактируйте существующий.',
                'overlap_id' => (int) $overlap['floor_plan_polygon_id'],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = [
            'home_id'       => $home_id,
            'section'       => $section,
            'floor'         => $floor,
            'apartament_id' => $apartament_id,
            'apartment_num' => (int) $apartment['apartment_num'],
            'label'         => trim((string) ($_POST['label'] ?? '')),
            'points'        => json_encode($points),
            'color'         => trim((string) ($_POST['color'] ?? '#4da3ff')),
        ];

        if ($id) {
            $existing = $mysql->get_for_key('floor_plan_polygons', 'floor_plan_polygon_id', $id);
            if (!$existing || (int) $existing['home_id'] !== $home_id) {
                echo json_encode(['success' => false, 'message' => 'Полигон не найден для этого дома']);
                return;
            }
            $mysql->update_for_key('floor_plan_polygons', 'floor_plan_polygon_id', $id, $data);
        } else {
            $id = $mysql->insert('floor_plan_polygons', $data);
        }

        echo json_encode(['success' => true, 'id' => (int) $id]);
    }

    function act__delete_polygon()
    {
        $this->require_admin();
        global $mysql;

        header('Content-Type: application/json; charset=utf-8');

        $id      = (int) ($_POST['floor_plan_polygon_id'] ?? 0);
        $home_id = (int) ($_POST['home_id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Не указан id']);
            return;
        }

        $existing = $mysql->get_for_key('floor_plan_polygons', 'floor_plan_polygon_id', $id);
        if (!$existing || ($home_id && (int) $existing['home_id'] !== $home_id)) {
            echo json_encode(['success' => false, 'message' => 'Полигон не найден для этого дома']);
            return;
        }

        $mysql->sql('UPDATE floor_plan_polygons SET del="1" WHERE floor_plan_polygon_id="' . $id . '"');

        echo json_encode(['success' => true, 'id' => $id]);
    }

    /**
     * Stage 2: аплоад фона плана этажа (png/jpg/webp/svg), перезаписывает существующий
     * файл этого этажа (полигоны не трогает), удаляет siblings другого расширения.
     * Тип файла определяется по содержимому, а не по имени/MIME от клиента (аудит C3).
     */
    function act__upload_floor_image()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $home_id = (int) ($_POST['home_id'] ?? 0);
        $section = (int) ($_POST['section'] ?? 0);
        $floor   = (int) ($_POST['floor'] ?? 0);
        if ($section < 1) {
            $section = 1;
        }

        if (!$home_id || $floor < 1 || $floor > self::MAX_FLOOR) {
            echo json_encode(['success' => false, 'message' => 'Некорректные home_id/section/floor']);
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

        // Определяем тип по фактическому содержимому (аудит C3), не по имени файла клиента.
        $ext = null;
        $dims = null;
        $svgContent = null;

        $imgInfo = @getimagesize($file['tmp_name']);
        if ($imgInfo && isset($imgInfo[2])) {
            $rasterExt = $this->raster_ext_for_imagetype($imgInfo[2]);
            if ($rasterExt) {
                $ext = $rasterExt;
                $dims = ['width' => (int) $imgInfo[0], 'height' => (int) $imgInfo[1]];
            }
        }

        if (!$ext) {
            $raw = @file_get_contents($file['tmp_name']);
            $sanitized = $raw !== false ? $this->sanitize_svg_content($raw) : null;
            if ($sanitized !== null) {
                $prevErrors = libxml_use_internal_errors(true);
                $svgDoc = new DOMDocument();
                $svgOk = @$svgDoc->loadXML($sanitized, LIBXML_NONET);
                libxml_clear_errors();
                libxml_use_internal_errors($prevErrors);
                $svgDims = $svgOk ? $this->svg_root_dimensions($svgDoc) : null;
                if ($svgDims) {
                    $ext = 'svg';
                    $svgContent = $sanitized;
                    $dims = ['width' => $svgDims[0], 'height' => $svgDims[1]];
                }
            }
        }

        if (!$ext || !$dims) {
            echo json_encode(['success' => false, 'message' => 'Неподдерживаемый или повреждённый файл. Разрешены: JPG, PNG, WEBP, SVG']);
            return;
        }

        $dir = $this->floor_section_dir($home_id, $section);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось создать каталог на сервере']);
            return;
        }

        $existing = $this->floor_image_resolve($home_id, $section, $floor);
        $replaced = $existing !== null;

        $target = $this->floor_image_target_path($home_id, $section, $floor, $ext);
        try {
            $this->assert_within_pbplans($target, $home_id);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка проверки пути сохранения']);
            return;
        }

        if ($ext === 'svg') {
            $ok = @file_put_contents($target, $svgContent) !== false;
        } else {
            $ok = @move_uploaded_file($file['tmp_name'], $target);
        }
        if (!$ok) {
            echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл на сервере']);
            return;
        }
        @chmod($target, 0644);

        // Один фон на этаж: чистим файлы того же этажа с другим расширением (аудит §3.2).
        foreach (self::FLOOR_IMAGE_EXT_PRIORITY as $otherExt) {
            if ($otherExt === $ext) {
                continue;
            }
            $siblingPath = $this->floor_image_target_path($home_id, $section, $floor, $otherExt);
            if (is_file($siblingPath)) {
                @unlink($siblingPath);
            }
        }

        echo json_encode([
            'success'     => true,
            'imageUrl'    => $this->floor_image_url($home_id, $section, $floor, $ext) . '?v=' . (int) @filemtime($target),
            'imageWidth'  => $dims['width'],
            'imageHeight' => $dims['height'],
            'ext'         => $ext,
            'replaced'    => $replaced,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Stage 2: очистить разметку этажа (soft-delete всех активных полигонов), фон не трогает.
     */
    function act__clear_polygons()
    {
        $this->require_admin();
        global $mysql;
        header('Content-Type: application/json; charset=utf-8');

        $home_id = (int) ($_POST['home_id'] ?? 0);
        $section = (int) ($_POST['section'] ?? 1);
        $floor   = (int) ($_POST['floor'] ?? 0);

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
            'UPDATE floor_plan_polygons SET del="1"
             WHERE home_id="' . $home_id . '" AND section="' . $section . '" AND floor="' . $floor . '" AND del="0"'
        );
        $cleared = (int) $mysql->count;

        echo json_encode(['success' => true, 'cleared' => $cleared]);
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

    function bbox($points)
    {
        $minX = $minY = PHP_FLOAT_MAX;
        $maxX = $maxY = -PHP_FLOAT_MAX;
        foreach ($points as $p) {
            $x = (float) $p[0];
            $y = (float) $p[1];
            if ($x < $minX) { $minX = $x; }
            if ($y < $minY) { $minY = $y; }
            if ($x > $maxX) { $maxX = $x; }
            if ($y > $maxY) { $maxY = $y; }
        }
        return [$minX, $minY, $maxX, $maxY];
    }

    function bbox_intersects($a, $b)
    {
        return !($a[2] < $b[0] || $b[2] < $a[0] || $a[3] < $b[1] || $b[3] < $a[1]);
    }

    function bbox_overlap_ratio($a, $b)
    {
        if (!$this->bbox_intersects($a, $b)) {
            return 0;
        }
        $ix = max(0, min($a[2], $b[2]) - max($a[0], $b[0]));
        $iy = max(0, min($a[3], $b[3]) - max($a[1], $b[1]));
        $inter = $ix * $iy;
        $areaA = max(1, ($a[2] - $a[0]) * ($a[3] - $a[1]));
        $areaB = max(1, ($b[2] - $b[0]) * ($b[3] - $b[1]));
        return $inter / min($areaA, $areaB);
    }

    /**
     * Соседние квартиры на плане делят стены: тонкая полоска bbox-пересечения
     * не должна блокировать сохранение. 0.85 — только почти-дубликаты
     * (синхронно с ctr__facades::overlap_conflict_threshold).
     */
    function overlap_conflict_threshold()
    {
        return 0.85;
    }

    function find_overlapping_polygon($home_id, $section, $floor, $points, $exclude_id = 0)
    {
        global $mysql;
        $q = 'SELECT * FROM floor_plan_polygons
              WHERE home_id="' . (int) $home_id . '" AND section="' . (int) $section . '" AND floor="' . (int) $floor . '" AND del="0"';
        if ($exclude_id) {
            $q .= ' AND floor_plan_polygon_id != "' . (int) $exclude_id . '"';
        }
        $existing = $mysql->get_arr($q);
        if (!$existing) {
            return null;
        }

        $newBbox = $this->bbox($points);
        $threshold = $this->overlap_conflict_threshold();
        foreach ($existing as $row) {
            $existingPoints = json_decode($row['points'], true);
            if (!is_array($existingPoints)) {
                continue;
            }
            $existingBbox = $this->bbox($existingPoints);
            if (!$this->bbox_intersects($newBbox, $existingBbox)) {
                continue;
            }
            if ($this->bbox_overlap_ratio($newBbox, $existingBbox) > $threshold) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Публичный payload плана этажа (без прав admin) — вызывается из
     * ctr__facades::act__floor_plan_data (аудит C2: виджет ходит только на ctr=facades).
     * Запрещённые поля (аудит M4): price, price_m, контакты, брони — не отдаём.
     * @return array {success:bool, message?:string, ...}
     */
    function floor_plan_public_payload($home_id, $section, $floor)
    {
        global $mysql;

        $home_id = (int) $home_id;
        $section = (int) $section;
        $floor   = (int) $floor;

        if (!$home_id || !$floor) {
            return ['success' => false, 'message' => 'Не указаны home_id/floor'];
        }
        if ($section < 1) {
            $section = 1;
        }

        $home = $mysql->get_for_key('homes', 'home_id', $home_id);
        if (!$home) {
            return ['success' => false, 'message' => 'Дом не найден'];
        }

        // Stage 2 (аудит H5): виджет должен видеть png/webp/svg, не только jpg.
        $found = $this->floor_image_resolve($home_id, $section, $floor);
        if (!$found) {
            return ['success' => false, 'message' => 'План этажа не найден'];
        }
        $dims = $this->floor_image_dimensions($found['path'], $found['ext']);
        if (!$dims) {
            return ['success' => false, 'message' => 'Не удалось прочитать изображение плана'];
        }

        $sections = $this->get_home_sections($home);
        $caption = 'Секция ' . $section;
        foreach ($sections as $sec) {
            if ((int) $sec['id'] === $section) {
                $caption = $sec['caption'];
                break;
            }
        }

        $rows = $mysql->get_arr(
            'SELECT fpp.*, a.apartment_num AS apt_apartment_num, a.rooms, a.area, a.status2, a.status
             FROM floor_plan_polygons fpp
             LEFT JOIN apartaments a ON a.apartament_id = fpp.apartament_id
             WHERE fpp.home_id="' . $home_id . '" AND fpp.section="' . $section . '" AND fpp.floor="' . $floor . '" AND fpp.del="0"
             ORDER BY fpp.sort_order, fpp.floor_plan_polygon_id'
        );

        $apartments = [];
        $index = 0;
        if ($rows) {
            foreach ($rows as $v) {
                $points = json_decode($v['points'], true);
                if (!is_array($points) || count($points) < 3) {
                    continue;
                }
                $index++;
                $aptNum = (int) ($v['apt_apartment_num'] ?? 0);
                if ($aptNum < 1) {
                    $aptNum = (int) ($v['apartment_num'] ?? 0);
                }
                $apartments[] = [
                    'polygonId'    => (int) $v['floor_plan_polygon_id'],
                    'apartamentId' => (int) $v['apartament_id'],
                    'apartmentNum' => $aptNum,
                    'displayCode'  => $section . '.' . $floor . '.' . $index,
                    'rooms'        => (string) ($v['rooms'] ?? ''),
                    'area'         => (string) ($v['area'] ?? ''),
                    'status'       => $v['status2'] ?? $v['status'] ?? null,
                    'points'       => $points,
                    'color'        => $v['color'] ?: '#4da3ff',
                ];
            }
        }

        return [
            'success'       => true,
            'homeId'        => $home_id,
            'section'       => $section,
            'floor'         => $floor,
            'sectionCaption' => $caption,
            'title'         => (string) ($home['title'] ?? ''),
            'imageUrl'      => $this->floor_image_absolute_url($home_id, $section, $floor),
            'imageWidth'    => $dims['width'],
            'imageHeight'   => $dims['height'],
            'apartments'    => $apartments,
        ];
    }
}
