<?php

function compred_kvartal_meta(array $a): array
{
    return [
        'id'          => (int)($a['kvartal_id'] ?? 0),
        'title'       => trim((string)($a['kvartal_title'] ?? '')),
        'description' => trim(strip_tags((string)($a['kvartal_description'] ?? ''))),
    ];
}

function compred_home_meta(array $a): array
{
    $home_name = trim((string)($a['long_title'] ?? ''));
    if ($home_name === '') {
        $home_name = trim((string)($a['home_title'] ?? ''));
    }

    $details = [];
    if ($addr = trim((string)($a['home_adress'] ?? ''))) {
        $details[] = ['Адрес', $addr];
    }
    if (!empty($a['home_floors'])) {
        $details[] = ['Этажность дома', (int)$a['home_floors'] . ' эт.'];
    }
    if ($wall = trim((string)($a['wallmaterial'] ?? ''))) {
        $details[] = ['Тип дома', $wall];
    }
    if ($renov = trim((string)($a['renovation'] ?? ''))) {
        $details[] = ['Отделка', $renov];
    }

    $delivery = trim((string)($a['complite_text'] ?? ''));
    if ($delivery === '' && !empty($a['delivery_date']) && $a['delivery_date'] !== '0000-00-00') {
        $delivery = $a['delivery_date'];
    }
    if ($delivery === '' && !empty($a['built_year'])) {
        $delivery = (string)$a['built_year'];
        if (!empty($a['ready_quarter'])) {
            $delivery .= ', ' . (int)$a['ready_quarter'] . ' кв.';
        }
    }
    if ($delivery !== '') {
        $details[] = ['Срок сдачи', $delivery];
    } elseif (!empty($a['complite'])) {
        $details[] = ['Статус', 'Дом сдан'];
    }

    return [
        'home_id'          => (int)($a['home_id'] ?? 0),
        'home_name'        => $home_name,
        'home_short'       => trim((string)($a['home_title'] ?? '')),
        'home_details'     => $details,
        'home_description' => trim(strip_tags((string)($a['home_description'] ?? ''))),
    ];
}

function compred_apartment_line(array $a): string
{
    $section_label = trim((string)($a['section_caption'] ?? ''));
    if ($section_label === '') {
        $section_label = 'Секция ' . (int)($a['section_id'] ?? 0);
    }

    return $section_label . ', этаж ' . ($a['floor'] ?? '') . ', '
        . (function_exists('unit_label_cap') ? unit_label_cap('nom') : '')
        . ' №' . ($a['apartment_num'] ?? '');
}

function compred_apartment_card_vars(array $obj): array
{
    $a = $obj['apartment'] ?? [];
    $k = compred_kvartal_meta($a);
    $h = compred_home_meta($a);
    $apt_line = compred_apartment_line($a);

    $img_alt = ($k['title'] !== '' ? $k['title'] . ' — ' : '')
        . ($h['home_name'] !== '' ? $h['home_name'] . ' — ' : '')
        . $apt_line;

    return array_merge($h, $k, [
        'apt_line' => $apt_line,
        'img_alt'  => $img_alt,
        'price'    => number_format((float)($a['price'] ?? 0), 0, '.', ' ') . ' ₽',
        'specs'    => ($a['area'] ?? '') . ' м² | ' . ($a['rooms'] ?? ''),
        'img'      => !empty($a['image_pb']) ? $a['image_pb'] : '/sahmatka/template/default/images/no-photo.jpg',
        'co_id'    => (int)($obj['compred_obj_id'] ?? 0),
        'comment'  => (string)($obj['comment'] ?? ''),
    ]);
}

function compred_clone_object(array $obj): array
{
    $apt = $obj['apartment'] ?? [];

    return [
        'compred_obj_id' => (int)($obj['compred_obj_id'] ?? 0),
        'compred_id'     => (int)($obj['compred_id'] ?? 0),
        'obj_type'       => (string)($obj['obj_type'] ?? 'apartment'),
        'obj_id'         => (int)($obj['obj_id'] ?? 0),
        'comment'        => (string)($obj['comment'] ?? ''),
        'sort_order'     => (int)($obj['sort_order'] ?? 0),
        'created_at'     => $obj['created_at'] ?? '',
        'apartment'      => array_merge([], $apt),
    ];
}

function compred_row_to_object(array $row): array
{
    $apartment = [
        'apartament_id'       => (int)($row['apartament_id'] ?? 0),
        'apartment_num'       => $row['apartment_num'] ?? '',
        'section_id'          => $row['section_id'] ?? '',
        'floor'               => $row['floor'] ?? '',
        'rooms'               => $row['rooms'] ?? '',
        'area'                => $row['area'] ?? '',
        'price'               => $row['price'] ?? 0,
        'image_pb'            => $row['image_pb'] ?? '',
        'home_id'             => (int)($row['home_id'] ?? 0),
        'home_title'          => $row['home_title'] ?? '',
        'long_title'          => $row['long_title'] ?? '',
        'home_adress'         => $row['home_adress'] ?? '',
        'home_description'    => $row['home_description'] ?? '',
        'wallmaterial'        => $row['wallmaterial'] ?? '',
        'home_floors'         => $row['home_floors'] ?? '',
        'complite'            => $row['complite'] ?? 0,
        'complite_text'       => $row['complite_text'] ?? '',
        'delivery_date'       => $row['delivery_date'] ?? '',
        'renovation'          => $row['renovation'] ?? '',
        'built_year'          => $row['built_year'] ?? '',
        'ready_quarter'       => $row['ready_quarter'] ?? '',
        'section_caption'     => $row['section_caption'] ?? '',
        'kvartal_title'       => $row['kvartal_title'] ?? '',
        'kvartal_description' => $row['kvartal_description'] ?? '',
        'kvartal_id'          => (int)($row['kvartal_id'] ?? 0),
    ];

    return compred_clone_object([
        'compred_obj_id' => $row['compred_obj_id'] ?? 0,
        'compred_id'     => $row['compred_id'] ?? 0,
        'obj_type'       => $row['obj_type'] ?? 'apartment',
        'obj_id'         => $row['obj_id'] ?? 0,
        'comment'        => $row['comment'] ?? '',
        'sort_order'     => $row['sort_order'] ?? 0,
        'created_at'     => $row['created_at'] ?? '',
        'apartment'      => $apartment,
    ]);
}

function compred_group_objects(array $objects): array
{
    $groups = [];
    $kvartal_index = [];

    foreach ($objects as $obj) {
        $a = $obj['apartment'] ?? null;
        if (!$a) {
            continue;
        }

        $k = compred_kvartal_meta($a);
        $k_key = $k['id'] > 0 ? 'k' . $k['id'] : 'k0';

        if (!isset($kvartal_index[$k_key])) {
            $kvartal_index[$k_key] = count($groups);
            $groups[] = [
                'kvartal_id'          => $k['id'],
                'kvartal_title'       => $k['title'],
                'kvartal_description' => $k['description'],
                'homes'               => [],
                'home_index'          => [],
            ];
        }

        $ki = $kvartal_index[$k_key];
        $h = compred_home_meta($a);
        $h_key = $h['home_id'] > 0 ? $h['home_id'] : 'h0';

        if (!isset($groups[$ki]['home_index'][$h_key])) {
            $groups[$ki]['home_index'][$h_key] = count($groups[$ki]['homes']);
            $groups[$ki]['homes'][] = array_merge($h, ['apartments' => []]);
        }

        $hi = $groups[$ki]['home_index'][$h_key];
        $groups[$ki]['homes'][$hi]['apartments'][] = compred_clone_object($obj);
    }

    foreach ($groups as &$group) {
        unset($group['home_index']);
    }
    unset($group);

    return $groups;
}

function compred_site_base_url(): string
{
    $base = getenv('APP_URL') ?: '';
    if ($base === '' && !empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . $_SERVER['HTTP_HOST'];
    }
    return rtrim($base, '/');
}

function compred_build_home_url(int $home_id): string
{
    if ($home_id <= 0) {
        return '';
    }
    return '/sahmatka/user.php?action=objects&home=' . $home_id;
}

function compred_build_public_url(string $token): string
{
    $token = preg_replace('/[^a-f0-9]/', '', strtolower($token));
    return compred_site_base_url() . '/compred/' . $token;
}

function compred_absolute_url(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    if ($path === '') {
        return compred_site_base_url();
    }
    return compred_site_base_url() . '/' . ltrim($path, '/');
}

function compred_apartments_count_label(int $count): string
{
    return function_exists('unit_count_label') ? unit_count_label($count) : '';
}

function compred_share_links(string $url, string $title): array
{
    $text = trim($title);
    if ($text === '') {
        $text = function_exists('unit_phrase') ? unit_phrase('compred_collection') : '';
    }
    $encodedUrl = rawurlencode($url);
    $encodedText = rawurlencode($text);

    return [
        'telegram'  => 'https://t.me/share/url?url=' . $encodedUrl . '&text=' . $encodedText,
        'whatsapp'  => 'https://api.whatsapp.com/send?text=' . rawurlencode($text . ' ' . $url),
        'max'       => 'https://max.ru/:share?text=' . rawurlencode($text . ' ' . $url),
        'vk'        => 'https://vk.com/share.php?url=' . $encodedUrl . '&title=' . $encodedText,
        'ok'        => 'https://connect.ok.ru/offer?url=' . $encodedUrl . '&title=' . $encodedText,
    ];
}

function compred_public_page_meta(array $compred, array $objects, string $public_url): array
{
    $title = trim((string)($compred['caption'] ?? ''));
    if ($title === '') {
        $title = function_exists('unit_phrase') ? unit_phrase('compred_collection') : '';
    }

    $intro = trim(preg_replace('/\s+/u', ' ', strip_tags((string)($compred['intro_text'] ?? ''))));
    $count = count($objects);
    if ($intro !== '') {
        $description = mb_strlen($intro) > 200 ? mb_substr($intro, 0, 197) . '…' : $intro;
    } elseif ($count > 0) {
        $description = function_exists('unit_phrase')
            ? sprintf(unit_phrase('compred_collection_count'), $count, compred_apartments_count_label($count))
            : '';
    } else {
        $description = function_exists('unit_phrase') ? unit_phrase('compred_collection_personal') : '';
    }

    $image = '/sahmatka/template/default/images/home-og.jpg';
    if (!empty($objects[0]['apartment']['image_pb'])) {
        $image = (string)$objects[0]['apartment']['image_pb'];
    }

    $absoluteUrl = compred_absolute_url($public_url);
    $absoluteImage = compred_absolute_url($image);

    return [
        'title'       => $title,
        'description' => $description,
        'url'         => $absoluteUrl,
        'image'       => $absoluteImage,
        'json_ld'     => [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            'name'        => $title,
            'description' => $description,
            'url'         => $absoluteUrl,
            'image'       => $absoluteImage,
            'inLanguage'  => 'ru-RU',
            'publisher'   => [
                '@type' => 'Organization',
                'name'  => 'M2 Profi',
            ],
        ],
    ];
}

function compred_bootstrap_public_meta(string $token): ?array
{
    global $mysql;

    $token = preg_replace('/[^a-f0-9]/', '', strtolower($token));
    if (strlen($token) !== 32) {
        return null;
    }

    $compred = $mysql->get_arr(
        "SELECT * FROM compred WHERE share_token = '" . mysqli_real_escape_string($mysql->c, $token) . "' AND del = 0",
        1
    );
    if (!$compred) {
        return null;
    }

    $objects = [];
    $row = $mysql->get_arr(
        'SELECT
            co.compred_obj_id,
            co.compred_id,
            co.obj_type,
            co.obj_id,
            co.comment,
            co.sort_order,
            co.created_at,
            a.apartament_id,
            a.apartment_num,
            a.section_id,
            a.floor,
            a.rooms,
            a.area,
            a.price,
            a.image_pb,
            a.home_id,
            h.title AS home_title,
            h.long_title,
            h.adress AS home_adress,
            h.description AS home_description,
            h.wallmaterial,
            h.floor AS home_floors,
            h.complite,
            h.complite_text,
            h.delivery_date,
            h.renovation,
            h.built_year,
            h.ready_quarter,
            hs.caption AS section_caption,
            hk.title AS kvartal_title,
            hk.description AS kvartal_description,
            hk.homes_kvartal_id AS kvartal_id
        FROM compred_obj co
        INNER JOIN apartaments a ON co.obj_type = "apartment" AND a.apartament_id = co.obj_id
        LEFT JOIN homes h ON h.home_id = a.home_id
        LEFT JOIN homes_sections hs ON hs.homes_sections_id = a.section_id
        LEFT JOIN homes_kvartal hk ON hk.homes_kvartal_id = IF(h.kvartal > 0, h.kvartal, h.homes_kvartal_id)
        WHERE co.compred_id = ' . (int)$compred['compred_id'] . '
        ORDER BY co.sort_order ASC, co.compred_obj_id ASC
        LIMIT 1',
        1
    );
    if ($row) {
        $objects[] = compred_row_to_object($row);
    }

    $public_url = compred_build_public_url($token);
    return compred_public_page_meta($compred, $objects, $public_url);
}

function compred_ensure_intro_column(): void
{
    global $mysql;
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $row = $mysql->get_arr("SHOW COLUMNS FROM compred LIKE 'intro_text'", 1);
    if (!$row) {
        $mysql->sql('ALTER TABLE `compred` ADD COLUMN `intro_text` TEXT NULL DEFAULT NULL AFTER `caption`');
    }
}
