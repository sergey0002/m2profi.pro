<?php
// Фильтр с первого числа месяца до сегодня
$firstDayOfMonth = date('Y-m-01');
$yastoday = date('Y-m-d');
if (!$_GET['date_limit']) {
    $_GET['date_limit'] = $firstDayOfMonth . ' : ' . $yastoday;
}

/**
 * Утилиты
 */
function norm_rooms($r){
    if ($r === null) return '0';
    $n = intval($r);
    return (string)$n;
}

function money_str($amount) {
    // возвращает строку вида " (12 345 ₽)" или пустую строку если 0
    $amount = floatval($amount);
    if ($amount == 0) return '';
    return ' (' . number_format($amount, 0, ',', ' ') . ' ₽)';
}

function money_span($amount) {
    // возвращает HTML-спан с суммой или пустую строку если 0
    $s = money_str($amount);
    if ($s === '') return '';
    return '<span style="color:#666; font-size: 12px;">' . $s . '</span>';
}

// Функции из первого контроллера (минимально изменены)
function get_kvartals() {
    $sql = "
        SELECT 
            kv.homes_kvartal_id,
            kv.title,
            COUNT(CASE WHEN h.show > 0 THEN h.home_id END) as total_homes,
            SUM(CASE WHEN h.show > 0 AND h.complite = 1 THEN 1 ELSE 0 END) as completed_homes,
            SUM(CASE WHEN h.show > 0 AND h.complite = 0 THEN 1 ELSE 0 END) as under_construction_homes
        FROM homes_kvartal kv
        LEFT JOIN homes h ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
        WHERE kv.`show` = 1 AND kv.`del` = 0
        GROUP BY kv.homes_kvartal_id, kv.title
        ORDER BY kv.`order`
    ";

    $result = mysqli_query($GLOBALS['connection'], $sql);
    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['total_homes'] = (int)$row['total_homes'];
        $row['completed_homes'] = (int)$row['completed_homes'];
        $row['under_construction_homes'] = (int)$row['under_construction_homes'];
        $list[] = $row;
    }
    return $list;
}

function kvartal_menu() {
    $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
    $selected_kvartal = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
    $action = $_GET['action'] ?? 'stat_sale';

    $kvartals = get_kvartals();
    ?>
    <div style="width:100%; margin-bottom:10px; margin-top:15px;">
        <a href="user.php?action=<?=$action?>&sdan=<?=$sdan?>&kvartal=0" 
           class="mdef <?=($selected_kvartal == 0) ? 'mdefth' : ''?>" 
           style="display:inline-block; padding-left: 10px; font-weight:bold;">
            Все кварталы
        </a>
        <?php foreach ($kvartals as $k): 
            if ($sdan == 0 && $k['under_construction_homes'] == 0) continue;
            if ($sdan == 1 && $k['completed_homes'] == 0) continue;
        ?>
            <a href="user.php?action=<?=$action?>&sdan=<?=$sdan?>&kvartal=<?=$k['homes_kvartal_id']?>&home=" 
               class="mdef <?=($selected_kvartal == $k['homes_kvartal_id']) ? 'mdefth' : ''?>" 
               style="display:inline-block; padding-left: 10px; font-weight:bold;">
                <?=htmlspecialchars($k['title'])?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}

function object_menux_s($action='objects') {
    $sa = $GLOBALS['sa'];
    $h = $sa->get_homes_arr();
    $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
    $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

    // Фильтруем дома по sdan и kvartal на лету, если нужно
    if ($kvartal_id > 0 || in_array($sdan, [0,1])) {
        $filtered_h = [];
        foreach ($h as $v) {
            if ($sdan == 0 && $v['complite'] != "0") continue;
            if ($sdan == 1 && $v['complite'] != "1") continue;
            if ($kvartal_id > 0 && intval($v['kvartal']) != $kvartal_id) continue;
            $filtered_h[] = $v;
        }
        $h = $filtered_h;
    }

    foreach($h as $k => $v) {
        if( isset($_GET['home']) && intval($_GET['home']) == $v['home_id'] ) {
            $class = ' class="mdef mdefth " ';
        } else { 
            $class = ' class="mdef" ';
            if($v['show'] == 2) { $class = ' class="mdef mdefa" '; }
            elseif($v['show'] == 3) { $class = ' class="mdef mdefaop" '; }
        }
        ?>
        <li style="padding:0;">
            <a href="user.php?action=<?=$action?>&home=<?=$v['home_id']?>&sdan=<?=$sdan?>&kvartal=<?=$_GET['kvartal']??0?>" <?=$class?> >
                <?=$v['title']?>
            </a>
        </li>
        <?php
    }
}

function object_menu_s($action='stat_sale') {
    $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
    $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

    // Получаем список домов с фильтрацией по sdan и kvartal
    $sql_homes = "
        SELECT home_id, title, long_title, complite, show, kvartal
        FROM homes 
        WHERE show > 0 
    ";
    if ($sdan == 0) $sql_homes .= " AND complite = 0 ";
    elseif ($sdan == 1) $sql_homes .= " AND complite = 1 ";
    if ($kvartal_id > 0) $sql_homes .= " AND CAST(kvartal AS UNSIGNED) = " . $kvartal_id;
    $sql_homes .= " ORDER BY `order`";

    $result = mysqli_query($GLOBALS['connection'], $sql_homes);
    $h_arr = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $h_arr[] = $row;
    }

    ?>
    <style>
    @media screen and (min-width: 1000px) {
      .mmenu{ display:block; padding-right:0; margin-top:15px; display: flex; flex-direction: row; justify-content: space-between; width: 100%;}
      .mobilenav{display:none;}
    }
    @media screen and (max-width: 1000px) {
      .mmenu{ display:none; }
      .mobilenav{display:block; width:100%;}
      .nomobile{display:none;}
    }
    </style>
    <script src="fancybox-3.0/dist/jquery.fancybox.min.js"></script>
    <link rel="stylesheet" type="text/css" href="fancybox-3.0/dist/jquery.fancybox.min.css">

    <script type="text/javascript">
    if( window.innerWidth >= 1000 ){
        $(document).ready(function() {
            $("a.iframe").fancybox({
                maxWidth    : 600,
                maxHeight   : 600,
                width       : '1000px',
                height      : '70%',
                closeClick  : true,
                'scrolling' : 'yes',
                afterClose: function () {},
                beforeLoad: function() {   
                    if (this.width = $(this.element).attr('width')) {this.maxWidth = $(this.element).attr('width');} else {this.width = '800';}
                    if (this.height = $(this.element).attr('height')) {this.maxHeight = $(this.element).attr('height');} else {this.height = '100%';}
                },
                type : 'iframe',
                openEffect : 'elastic',
                closeEffect : 'elastic',
                arrows : false,
                closeClick : false,
                scrolling: 'auto',
                fitToView    : true,
                autoSize: true,
                margin      : [10, 10, 10, 10],
                padding:    [39, 10, 10, 10],
                helpers : {
                    overlay : {closeClick : false},
                    title    : {type : 'inside_top' },
                }
            });
        });
    }
    </script>

    <div style="width:100%; margin-bottom:10px;">
        <a href="user.php?action=<?=$action?>&sdan=3&kvartal=<?=$kvartal_id?>" class="mdef <?=$_GET['sdan']==3 ? 'mdefth' : ''?>" style="display:inline-block; padding-left: 10px; font-weight:bold;">ВСЕ</a>
        <a href="user.php?action=<?=$action?>&sdan=0&kvartal=<?=$kvartal_id?>" class="mdef <?=$_GET['sdan']==0 ? 'mdefth' : ''?>" style="display:inline-block; padding-left: 12px; font-weight:bold;">СТРОЯЩИЕСЯ</a> 
        <a href="user.php?action=<?=$action?>&sdan=1&kvartal=<?=$kvartal_id?>" class="mdef <?=$_GET['sdan']==1 ? 'mdefth' : ''?>" style="display:inline-block; padding-left: 10px; font-weight:bold;">СДАННЫЕ</a>
    </div>

    <?php kwartal_menu: // старый стиль, функция выше ?> 

    <?php kvartal_menu(); ?>

    <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
        <br/>
        <ul class="mmenu">
            <?php object_menux_s($action); ?>
        </ul>

        <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select">
            <input type="hidden" name="action" value="<?=$action?>" />
            <input type="hidden" name="sdan" value="<?=$sdan?>" />
            <input type="hidden" name="kvartal" value="<?=$kvartal_id?>" />
            <div class="objects-head-nav__select">
                <select name="home" onChange="this.form.submit();" style="width:100%; text-align: left; border-radius:0;">
                    <option value="">Выбрать дом</option>
                    <?php foreach($h_arr as $v): ?>
                        <option value="<?=$v['home_id']?>" <?=($v['home_id']==($_GET['home']??'')) ? 'selected' : ''?>>
                            <?=$v['long_title']?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <hr style="margin-top: 12px;" class="nomobile"/>
    <?php
}

// Суперглобальный массив статусов
$s_arr = [
    0 => 'Не задан',
    2 => 'Свободна',
    4 => 'Забронирована',
    5 => 'Забронирована застройщиком',
    6 => 'Квартира подрядчика'
];

// Получаем параметры
$sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
$kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
$home_id = isset($_GET['home']) ? intval($_GET['home']) : 0;

// Получаем дома (для использования в object_menux_s и homesx)
$sa = $GLOBALS['sa'];
$h = $sa->get_homes_arr();

// Фильтруем дома для использования в логике
$actual_homes = [];
$homesx = [];

foreach($h as $k => $v) {
    if($sdan) {
        if($sdan == 1 && $v['complite'] == "1") {
            $actual_homes[] = $v['home_id'];
        } elseif($sdan == 0 && $v['complite'] == "0") {
            $actual_homes[] = $v['home_id'];
        }
    } else {
        $actual_homes[] = $v['home_id'];
    }
    $homesx[$v['home_id']] = $v;
    $homesx[$v['home_id']]['caption'] = $v['title'];
}

########## ГРАФИК (последние 12 месяцев) — СУММАРНАЯ СТОИМОСТЬ
 
#### ГОТОВИМ ДАННЫЕ — ВСЕ КВАРТИРЫ (с ценой)
$sql = '
    SELECT 
        apartaments.home_id, 
        REGEXP_SUBSTR(apartaments.rooms, "[0-9]+") as roomsx, 
        COUNT(*) as count,
        SUM(apartaments.price) as sum_price
    FROM apartaments 
    LEFT JOIN homes ON homes.home_id = apartaments.home_id
    WHERE CAST(REGEXP_SUBSTR(apartaments.rooms, "[0-9]+") AS UNSIGNED) > 0
      AND homes.show > 0
';

if ($sdan == 0) $sql .= " AND homes.complite = 0 ";
elseif ($sdan == 1) $sql .= " AND homes.complite = 1 ";
if ($kvartal_id > 0) $sql .= " AND CAST(homes.kvartal AS UNSIGNED) = " . $kvartal_id;
if ($home_id) $sql .= " AND apartaments.home_id = " . $home_id;

$sql .= " GROUP BY apartaments.home_id, roomsx ";

$query = mysqli_query($GLOBALS['connection'], $sql);
$all_arr = [];

while($result = mysqli_fetch_assoc($query)) {
    $hid = $result['home_id'];
    $rooms = norm_rooms($result['roomsx']);
    $all_arr[$hid][$rooms]['count'] = $result['count'];
    $all_arr[$hid][$rooms]['sum_price'] = $result['sum_price'];
    $all_arr['all'][$rooms]['count'] = ($all_arr['all'][$rooms]['count'] ?? 0) + $result['count'];
    $all_arr['all'][$rooms]['sum_price'] = ($all_arr['all'][$rooms]['sum_price'] ?? 0) + $result['sum_price'];
}

#### ГОТОВИМ ДАННЫЕ — ПРОДАННЫЕ И ЗАБРОНИРОВАННЫЕ (с ценой)
$sql = '
    SELECT 
        broni.status,
        broni.home_id,
        REGEXP_SUBSTR(apartaments.rooms, "[0-9]+") as roomsx,
        COUNT(*) as count,
        SUM(apartaments.price) as sum_price,
        YEAR(broni.date) as year,
        MONTH(broni.date) as month
    FROM broni 
    LEFT JOIN apartaments ON apartaments.home_id = broni.home_id AND apartaments.apartment_num = broni.apartments_num
    LEFT JOIN homes ON homes.home_id = broni.home_id
    WHERE broni.status IN ("3","4","5","6")
      AND CAST(REGEXP_SUBSTR(apartaments.rooms, "[0-9]+") AS UNSIGNED) > 0
      AND homes.show > 0
';

if ($sdan == 0) $sql .= " AND homes.complite = 0 ";
elseif ($sdan == 1) $sql .= " AND homes.complite = 1 ";
if ($kvartal_id > 0) $sql .= " AND CAST(homes.kvartal AS UNSIGNED) = " . $kvartal_id;
if ($home_id) $sql .= " AND broni.home_id = " . $home_id;

$sql .= " 
    AND broni.date = (
        SELECT MAX(b.date) 
        FROM broni b 
        WHERE b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num
    )
    GROUP BY broni.status, broni.home_id, roomsx, year, month
";

$query = mysqli_query($GLOBALS['connection'], $sql);

$sale_arr = $sale_arr4 = $sale_arr5 = $sale_arr6 = [];
$sale_arr_m = $sale_arr_m4 = $sale_arr_m5 = $sale_arr_m6 = [];
$sale_arr2 = $sale_arr2_m = $sale_arr3 = [];
$rooms_arr = [];

while($result = mysqli_fetch_assoc($query)) {
    $hid = $result['home_id'];
    $rooms = norm_rooms($result['roomsx']);
    $status = $result['status'];
    $count = $result['count'];
    $sum_price = $result['sum_price'];
    
    $rooms_arr[$rooms] = 1;

    if ($status == '3') {
        $sale_arr[$hid][$rooms]['count'] = ($sale_arr[$hid][$rooms]['count'] ?? 0) + $count;
        $sale_arr[$hid][$rooms]['sum_price'] = ($sale_arr[$hid][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr['all'][$rooms]['count'] = ($sale_arr['all'][$rooms]['count'] ?? 0) + $count;
        $sale_arr['all'][$rooms]['sum_price'] = ($sale_arr['all'][$rooms]['sum_price'] ?? 0) + $sum_price;
        if (!empty($result['year']) && !empty($result['month'])) {
            $y = intval($result['year']);
            $m = intval($result['month']);
            $sale_arr_m[$hid][$y][$m][$rooms]['count'] = ($sale_arr_m[$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
            $sale_arr_m[$hid][$y][$m][$rooms]['sum_price'] = ($sale_arr_m[$hid][$y][$m][$rooms]['sum_price'] ?? 0) + $sum_price;
            $sale_arr_m['all'][$y][$m][$rooms]['count'] = ($sale_arr_m['all'][$y][$m][$rooms]['count'] ?? 0) + $count;
            $sale_arr_m['all'][$y][$m][$rooms]['sum_price'] = ($sale_arr_m['all'][$y][$m][$rooms]['sum_price'] ?? 0) + $sum_price;
        }
    } elseif ($status == '4') {
        $sale_arr4[$hid][$rooms]['count'] = ($sale_arr4[$hid][$rooms]['count'] ?? 0) + $count;
        $sale_arr4[$hid][$rooms]['sum_price'] = ($sale_arr4[$hid][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr4['all'][$rooms]['count'] = ($sale_arr4['all'][$rooms]['count'] ?? 0) + $count;
        $sale_arr4['all'][$rooms]['sum_price'] = ($sale_arr4['all'][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr2[4][$hid][$rooms]['count'] = ($sale_arr2[4][$hid][$rooms]['count'] ?? 0) + $count;
        if (!empty($result['year']) && !empty($result['month'])) {
            $y = intval($result['year']);
            $m = intval($result['month']);
            $sale_arr_m4[$hid][$y][$m][$rooms]['count'] = ($sale_arr_m4[$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
            $sale_arr_m4[$hid][$y][$m][$rooms]['sum_price'] = ($sale_arr_m4[$hid][$y][$m][$rooms]['sum_price'] ?? 0) + $sum_price;
            $sale_arr2_m[4][$hid][$y][$m][$rooms]['count'] = ($sale_arr2_m[4][$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
        }
    } elseif ($status == '5') {
        $sale_arr5[$hid][$rooms]['count'] = ($sale_arr5[$hid][$rooms]['count'] ?? 0) + $count;
        $sale_arr5[$hid][$rooms]['sum_price'] = ($sale_arr5[$hid][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr5['all'][$rooms]['count'] = ($sale_arr5['all'][$rooms]['count'] ?? 0) + $count;
        $sale_arr5['all'][$rooms]['sum_price'] = ($sale_arr5['all'][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr2[5][$hid][$rooms]['count'] = ($sale_arr2[5][$hid][$rooms]['count'] ?? 0) + $count;
        if (!empty($result['year']) && !empty($result['month'])) {
            $y = intval($result['year']);
            $m = intval($result['month']);
            $sale_arr_m5[$hid][$y][$m][$rooms]['count'] = ($sale_arr_m5[$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
            $sale_arr_m5[$hid][$y][$m][$rooms]['sum_price'] = ($sale_arr_m5[$hid][$y][$m][$rooms]['sum_price'] ?? 0) + $sum_price;
            $sale_arr2_m[5][$hid][$y][$m][$rooms]['count'] = ($sale_arr2_m[5][$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
        }
    } elseif ($status == '6') {
        $sale_arr6[$hid][$rooms]['count'] = ($sale_arr6[$hid][$rooms]['count'] ?? 0) + $count;
        $sale_arr6[$hid][$rooms]['sum_price'] = ($sale_arr6[$hid][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr6['all'][$rooms]['count'] = ($sale_arr6['all'][$rooms]['count'] ?? 0) + $count;
        $sale_arr6['all'][$rooms]['sum_price'] = ($sale_arr6['all'][$rooms]['sum_price'] ?? 0) + $sum_price;
        $sale_arr2[6][$hid][$rooms]['count'] = ($sale_arr2[6][$hid][$rooms]['count'] ?? 0) + $count;
        if (!empty($result['year']) && !empty($result['month'])) {
            $y = intval($result['year']);
            $m = intval($result['month']);
            $sale_arr_m6[$hid][$y][$m][$rooms]['count'] = ($sale_arr_m6[$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
            $sale_arr_m6[$hid][$y][$m][$rooms]['sum_price'] = ($sale_arr_m6[$hid][$y][$m][$rooms]['sum_price'] ?? 0) + $sum_price;
            $sale_arr2_m[6][$hid][$y][$m][$rooms]['count'] = ($sale_arr2_m[6][$hid][$y][$m][$rooms]['count'] ?? 0) + $count;
        }
    }
}

// ТАБЛИЦА СВОБОДНЫХ КВАРТИР — КОЛИЧЕСТВО + СТОИМОСТЬ
ob_start();
ksort($rooms_arr);

if (!$home_id && !empty($all_arr)) {
    ?>
    <table class="table table-bordered">
    <thead>
    <tr>
        <th>Дом</th>
        <?php foreach($rooms_arr as $rk => $rv): ?>
            <th><b><?=$rk?>к</b></th>
        <?php endforeach; ?>
        <th><b>Итого</b></th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Инициализируем суммы для итоговой строки
    $total_free_house = 0;
    $total_free_price = 0;
    $itogo_free_arr = [];
    $itogo_free_price = [];

    foreach($all_arr as $hid => $room_data) {
        if ($hid === 'all') continue;
        if (empty($homesx[$hid])) continue;
        
        ?>
        <tr>
        <td><?=$homesx[$hid]['caption']?> <?php if($homesx[$hid]['complite']==1){echo ' <b>сдан</b> ';} ?></td>
        <?php
        // Сбрасываем локальные значения для каждой строки
        $row_free_count = 0;
        $row_free_price = 0;

        foreach($rooms_arr as $rk => $rv) {
            $total_price = $all_arr[$hid][$rk]['sum_price'] ?? 0;
            $sold_price = $sale_arr[$hid][$rk]['sum_price'] ?? 0;
            $reserved_price = ($sale_arr4[$hid][$rk]['sum_price'] ?? 0) + ($sale_arr5[$hid][$rk]['sum_price'] ?? 0) + ($sale_arr6[$hid][$rk]['sum_price'] ?? 0);
            $free_price = max(0, $total_price - $sold_price - $reserved_price);
            
            $total_count = $all_arr[$hid][$rk]['count'] ?? 0;
            $sold_count = $sale_arr[$hid][$rk]['count'] ?? 0;
            $reserved_count = ($sale_arr4[$hid][$rk]['count'] ?? 0) + ($sale_arr5[$hid][$rk]['count'] ?? 0) + ($sale_arr6[$hid][$rk]['count'] ?? 0);
            $free_count = max(0, $total_count - $sold_count - $reserved_count);
            
            // Накапливаем по строке
            $row_free_count += $free_count;
            $row_free_price += $free_price;
            
            // Накапливаем по итогам
            $itogo_free_arr[$rk] = ($itogo_free_arr[$rk] ?? 0) + $free_count;
            $itogo_free_price[$rk] = ($itogo_free_price[$rk] ?? 0) + $free_price;
            
            ?>
            <td>
                <?=number_format($free_count, 0, ',', ' ')?> 
                <?= $free_price > 0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($free_price,0,',',' ') . ' ₽)</span>' : '' ?>
                <sup>
                    <?php if(isset($sale_arr2[4][$hid][$rk]['count']) && $sale_arr2[4][$hid][$rk]['count']): ?>
                        / <span style="background:#FFFF00; padding:1px 3px; border-radius:2px;" title="Бронь"><?=$sale_arr2[4][$hid][$rk]['count']?></span>
                    <?php endif; ?>
                    <?php if(isset($sale_arr2[5][$hid][$rk]['count']) && $sale_arr2[5][$hid][$rk]['count']): ?>
                        / <span style="background:#D4E6FF; padding:1px 3px; border-radius:2px;" title="Застройщика"><?=$sale_arr2[5][$hid][$rk]['count']?></span>
                    <?php endif; ?>
                    <?php if(isset($sale_arr2[6][$hid][$rk]['count']) && $sale_arr2[6][$hid][$rk]['count']): ?>
                        / <span style="background:#9933ff; color:#FFF; padding:1px 3px; border-radius:2px;" title="Подрядчика"><?=$sale_arr2[6][$hid][$rk]['count']?></span>
                    <?php endif; ?>
                </sup>
            </td>
            <?php
        }
        // ✅ ВЫВОД ИТОГО ПО СТРОКЕ — НЕ ПОСЛЕДНЕЙ КОМНАТНОСТИ, А СУММА ПО ВСЕМ
        ?>
        <td><?=number_format($row_free_count, 0, ',', ' ')?> 
            <?= $row_free_price > 0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($row_free_price,0,',',' ') . ' ₽)</span>' : '' ?>
        </td>
        </tr>
        <?php
        
        // Накапливаем общую сумму по всем домам
        $total_free_house += $row_free_count;
        $total_free_price += $row_free_price;
    }
    ?>
    <tr style="font-weight: bold;">
    <td>Итого</td>
    <?php
    foreach($rooms_arr as $rk => $rv) {
        $free = $itogo_free_arr[$rk] ?? 0;
        $price = $itogo_free_price[$rk] ?? 0;
        echo "<td>" . number_format($free, 0, ',', ' ') . " " . ($price>0 ? "<span style='color:#666; font-size: 12px;'>(<span style='color:#666;'>" . number_format($price, 0, ',', ' ') . " ₽</span>)</span>" : "") . "</td>";
    }
    ?>
    <td><?=number_format($total_free_house, 0, ',', ' ')?> 
        <?= $total_free_price > 0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($total_free_price,0,',',' ') . ' ₽)</span>' : '' ?>
    </td>
    </tr>
    </tbody>
    </table>
    <?php
}
$free_table = ob_get_clean();

// СВОДНАЯ СТАТИСТИКА — КОЛИЧЕСТВО + СТОИМОСТЬ
ob_start();
?>
<table class="table table-bordered">
<tr>
    <th>Комнат</th>
    <th>Всего</th>
    <th>Продано</th>
    <th>Продано %</th>
    <th>Свободно</th>
    <th>Свободно %</th>
    <th>Забронировано</th>
    <th>Забронировано %</th>
 </tr>
<?php
$itogo = [
    'total' => 0,
    'sold' => 0,
    'free' => 0,
    'reserved' => 0,
    'sum_price' => 0,
    'sold_price' => 0,
    'free_price' => 0,
    'reserved_price' => 0
];

$target_home = $home_id ?: 'all';

if (empty($all_arr[$target_home])) {
    echo "<tr><td colspan='9'>Нет данных</td></tr>";
} else {
    foreach($all_arr[$target_home] as $rooms => $data) {
        $total_price = $data['sum_price'] ?? 0;
        $sold_price = $sale_arr[$target_home][$rooms]['sum_price'] ?? 0;
        $reserved_price = ($sale_arr4[$target_home][$rooms]['sum_price'] ?? 0) + ($sale_arr5[$target_home][$rooms]['sum_price'] ?? 0) + ($sale_arr6[$target_home][$rooms]['sum_price'] ?? 0);
        $free_price = max(0, $total_price - $sold_price - $reserved_price);
        
        $total_count = $data['count'] ?? 0;
        $sold_count = $sale_arr[$target_home][$rooms]['count'] ?? 0;
        $reserved_count = ($sale_arr4[$target_home][$rooms]['count'] ?? 0) + ($sale_arr5[$target_home][$rooms]['count'] ?? 0) + ($sale_arr6[$target_home][$rooms]['count'] ?? 0);
        $free_count = max(0, $total_count - $sold_count - $reserved_count);
        
        $itogo['total'] += $total_count;
        $itogo['sold'] += $sold_count;
        $itogo['free'] += $free_count;
        $itogo['reserved'] += $reserved_count;
        $itogo['sum_price'] += $total_price;
        $itogo['sold_price'] += $sold_price;
        $itogo['free_price'] += $free_price;
        $itogo['reserved_price'] += $reserved_price;
        
        $sold_percent = $total_count > 0 ? round($sold_count / $total_count * 100, 2) : 0;
        $free_percent = $total_count > 0 ? round($free_count / $total_count * 100, 2) : 0;
        $reserved_percent = $total_count > 0 ? round($reserved_count / $total_count * 100, 2) : 0;
        ?>
        <tr>
        <td><?=$rooms?>к</td>
        <td><?=number_format($total_count, 0, ',', ' ')?> <?= $total_price>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($total_price,0,',',' ') . ' ₽)</span>' : '' ?></td>
        <td><?=number_format($sold_count, 0, ',', ' ')?> <?= $sold_price>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($sold_price,0,',',' ') . ' ₽)</span>' : '' ?></td>
        <td><?=$sold_percent?>%</td>
        <td><?=number_format($free_count, 0, ',', ' ')?> <?= $free_price>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($free_price,0,',',' ') . ' ₽)</span>' : '' ?></td>
        <td><?=$free_percent?>%</td>
        <td><?=number_format($reserved_count, 0, ',', ' ')?> <?= $reserved_price>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($reserved_price,0,',',' ') . ' ₽)</span>' : '' ?></td>
        <td><?=$reserved_percent?>%</td>
     
        </tr>
        <?php
    }
    
    $itogo_sold_percent = $itogo['total'] > 0 ? round($itogo['sold'] / $itogo['total'] * 100, 2) : 0;
    $itogo_free_percent = $itogo['total'] > 0 ? round($itogo['free'] / $itogo['total'] * 100, 2) : 0;
    $itogo_reserved_percent = $itogo['total'] > 0 ? round($itogo['reserved'] / $itogo['total'] * 100, 2) : 0;
    ?>
    <tr style="font-weight: bold;">
    <td>Итого</td>
    <td><?=number_format($itogo['total'], 0, ',', ' ')?> <?= $itogo['sum_price']>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($itogo['sum_price'],0,',',' ') . ' ₽)</span>' : '' ?></td>
    <td><?=number_format($itogo['sold'], 0, ',', ' ')?> <?= $itogo['sold_price']>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($itogo['sold_price'],0,',',' ') . ' ₽)</span>' : '' ?></td>
    <td><?=$itogo_sold_percent?>%</td>
    <td><?=number_format($itogo['free'], 0, ',', ' ')?> <?= $itogo['free_price']>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($itogo['free_price'],0,',',' ') . ' ₽)</span>' : '' ?></td>
    <td><?=$itogo_free_percent?>%</td>
    <td><?=number_format($itogo['reserved'], 0, ',', ' ')?> <?= $itogo['reserved_price']>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($itogo['reserved_price'],0,',',' ') . ' ₽)</span>' : '' ?></td>
    <td><?=$itogo_reserved_percent?>%</td>
  
    </tr>
    <?php
}
?>
</table>
<?php
$svodnaya = ob_get_clean();

// СТАТИСТИКА ПО МЕСЯЦАМ — КОЛИЧЕСТВО + СТОИМОСТЬ
ob_start();
$month_names = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
    5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
    9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
];

$target_home = $home_id ?: 'all';
if (!empty($sale_arr_m[$target_home])) {
    foreach($sale_arr_m[$target_home] as $year => $months) {
        // сортируем месяцы по номеру (1..12) чтобы накопительный процент шёл хронологически
        ksort($months);

        echo "<h4>$year год</h4>";
        $total_apartments = array_sum(array_column($all_arr[$target_home] ?? [], 'sum_price'));
        ?>
        <table class="table table-bordered">
        <tr>
            <th>Месяц</th>
            <?php foreach($rooms_arr as $rk => $rv): ?>
                <th><?=$rk?>к</th>
            <?php endforeach; ?>
            <th>Итого</th>
            <th>% от общего</th>
        </tr>
        <?php
        $cumulative_percent = 0;
        foreach($months as $month_num => $room_data) {
            $month_total_price = 0;
            $month_total_count = 0;
            foreach($rooms_arr as $rk => $rv) {
                $month_total_price += ($room_data[$rk]['sum_price'] ?? 0);
                $month_total_count += ($room_data[$rk]['count'] ?? 0);
            }
            $month_percent = $total_apartments > 0 ? round($month_total_price / $total_apartments * 100, 2) : 0;
            $cumulative_percent += $month_percent;
            ?>
            <tr>
            <td><?=$month_names[$month_num]?></td>
            <?php foreach($rooms_arr as $rk => $rv): ?>
                <td>
                    <?=number_format($room_data[$rk]['count'] ?? 0, 0, ',', ' ')?> 
                    <?= ($room_data[$rk]['sum_price'] ?? 0) > 0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($room_data[$rk]['sum_price'], 0, ',', ' ') . ' ₽)</span>' : '' ?>
                    <?php if (isset($sale_arr2_m[4][$target_home][$year][$month_num][$rk]['count']) && $sale_arr2_m[4][$target_home][$year][$month_num][$rk]['count']): ?>
                        <sup style="background:#FFFF00; padding:1px 3px; border-radius:2px;" title="Бронь"><?=$sale_arr2_m[4][$target_home][$year][$month_num][$rk]['count']?></sup>
                    <?php endif; ?>
                    <?php if (isset($sale_arr2_m[5][$target_home][$year][$month_num][$rk]['count']) && $sale_arr2_m[5][$target_home][$year][$month_num][$rk]['count']): ?>
                        <sup style="background:#D4E6FF; padding:1px 3px; border-radius:2px;" title="Застройщика"><?=$sale_arr2_m[5][$target_home][$year][$month_num][$rk]['count']?></sup>
                    <?php endif; ?>
                    <?php if (isset($sale_arr2_m[6][$target_home][$year][$month_num][$rk]['count']) && $sale_arr2_m[6][$target_home][$year][$month_num][$rk]['count']): ?>
                        <sup style="background:#9933ff; color:#FFF; padding:1px 3px; border-radius:2px;" title="Подрядчика"><?=$sale_arr2_m[6][$target_home][$year][$month_num][$rk]['count']?></sup>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            <td><?=number_format($month_total_count, 0, ',', ' ')?> 
                <?= $month_total_price>0 ? '<span style="color:#666; font-size: 12px;">(' . number_format($month_total_price,0,',',' ') . ' ₽)</span>' : '' ?>
            </td>
            <td><?=round($cumulative_percent, 2)?>%</td>
            </tr>
            <?php
        }
        echo "</table><br>";
    }
} else {
    echo "<p>Нет данных по продажам по месяцам</p>";
}
$stat_month = ob_get_clean();





















 
 
// ---------- ФРАГМЕНТ: ГРАФИК (последние 12 месяцев, деньги по комнатам) ----------
// Ожидается, что $sale_arr_m и $target_home уже формированы в основном коде.
// Структура: $sale_arr_m[<home|'all'>][<year>][<month>][<rooms>]['count'/'sum_price']

ob_start();

// месячные названия (короткие)
$monthShort = [1=>'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];

// целевой набор (по умолчанию 'all')
if (!isset($target_home)) $target_home = $home_id ?? 'all';

// сформируем список последних 12 месяцев (year, month)
$months_list = [];
$now = new DateTimeImmutable('now');
for ($i = 11; $i >= 0; $i--) {
    $dt = $now->modify("-{$i} months");
    $y = (int)$dt->format('Y');
    $m = (int)$dt->format('n'); // 1..12
    $months_list[] = ['year'=>$y, 'month'=>$m, 'label'=>$monthShort[$m] . ' ' . $y];
}

// комнаты, которые хотим отобразить (1..4)
$room_types = [1,2,3,4];

// подготовка массивов данных: для каждой комнаты — массив 12 значений (sum_price)
// и массив для суммарных денег по месяцам
$series_money_by_room = [];
foreach ($room_types as $r) $series_money_by_room[$r] = array_fill(0, count($months_list), 0.0);

$series_money_total = array_fill(0, count($months_list), 0.0);

// заполним данные, если $sale_arr_m имеет данные
if (!empty($sale_arr_m[$target_home]) && is_array($sale_arr_m[$target_home])) {
    $data_src = $sale_arr_m[$target_home];
    foreach ($months_list as $idx => $mitem) {
        $y = $mitem['year'];
        $m = $mitem['month'];
        if (isset($data_src[$y]) && isset($data_src[$y][$m]) && is_array($data_src[$y][$m])) {
            foreach ($room_types as $r) {
                // берем сумму по комнатам (sum_price) — если отсутствует, 0
                $sum = $data_src[$y][$m][$r]['sum_price'] ?? 0;
                $series_money_by_room[$r][$idx] = (float)$sum;
                $series_money_total[$idx] += (float)$sum;
            }
        } else {
            // оставляем нули
            foreach ($room_types as $r) $series_money_by_room[$r][$idx] = 0.0;
            $series_money_total[$idx] = 0.0;
        }
    }
}

// подписи месяцев
$labels = array_map(function($m){ return $m['label']; }, $months_list);

// цвета для линий
$colors = [
    1 => 'rgba(75,192,192,1)',   // 1к
    2 => 'rgba(54,162,235,1)',   // 2к
    3 => 'rgba(255,159,64,1)',   // 3к
    4 => 'rgba(153,102,255,1)',  // 4к
    'total' => 'rgba(220,20,60,1)' // сумма — красная
];

// Подготовка JSON для JS
$js_labels = json_encode($labels, JSON_UNESCAPED_UNICODE);

// Формируем datasets: 4 линии по комнатам (денежные значения) + линия "Итого"
$js_datasets = [];

foreach ($room_types as $r) {
    $js_datasets[] = [
        'label' => $r . "к (₽)",
        // приведение к float/int - чтобы JSON содержал числа
        'data' => array_map(function($v){ return round((float)$v, 0); }, $series_money_by_room[$r]),
        'fill' => false,
        'borderColor' => $colors[$r],
        'backgroundColor' => $colors[$r],
        'tension' => 0.3,
        'pointRadius' => 3,
    ];
}

// Линия "Итого" (толще, пунктирная)
$js_datasets[] = [
    'label' => 'Итого (₽)',
    'data' => array_map(function($v){ return round((float)$v, 0); }, $series_money_total),
    'fill' => false,
    'borderColor' => $colors['total'],
    'backgroundColor' => $colors['total'],
    'tension' => 0.25,
    'pointRadius' => 4,
    'borderWidth' => 2,
    'borderDash' => [6,3],
];

// JSON-кодирование
$js_datasets_json = json_encode($js_datasets, JSON_UNESCAPED_UNICODE);

?>
<div class="chart-block" style="width:100%; max-width:100%; height:360px; margin: 10px 0;">
    <canvas id="grafMoneyChart"></canvas>
</div>

<!-- Подключение Chart.js (CDN). При необходимости замените на локальную копию -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function(){
    const ctx = document.getElementById('grafMoneyChart').getContext('2d');
    const labels = <?php echo $js_labels; ?>;
    const datasets = <?php echo $js_datasets_json; ?>;

    // Конфигурация графика — деньги на единственной оси Y
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const seriesLabel = context.dataset.label || '';
                            const value = context.parsed.y ?? context.raw ?? 0;
                            // форматируем число с разделителем тысяч
                            const formatted = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
                            return seriesLabel + ': ' + formatted + ' ₽';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Месяц'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Сумма продаж, ₽'
                    },
                    ticks: {
                        callback: function(value, index, ticks) {
                            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' ₽';
                        }
                    }
                }
            }
        }
    });
})();
</script>

<?php
$graf = ob_get_clean();
// ---------- /ФРАГМЕНТ ----------
 





?>
<style>
th{font-weight:bold;}
</style>
<section class="section-stat">
    <div class="container">
        <div class="page-header">
            <div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
            <div class="page-header__title">СТАТИСТИКА <span>продаж</span></div>
        </div>
        
    

        <div class="stat">
            <div class="stat-top">
                <div class="stat-top-filter">
                    <div class="stat-top-items">
                        <?php object_menu_s('stat_sale'); ?>
                    </div>
                    <div class="stat-top-btns">
                        <a href="JavaScript:window.print();" class="stat-top__print"></a>
                    </div>
                </div>
            </div>
            
            <div class="chart-wrap">
                <h3>График продаж за последние 12 месяцев</h3>
                <?=$graf?>
            </div>

            <div class="stat-wrap">
                <?php if(!$home_id): ?>
                <div class="room-table stat-content">
                    <div class="second-title stat-title active">Свободные квартиры</div>
                    <div class="table-block stat-body open">
                        <?=$free_table?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="total-stat stat-content">
                    <div class="second-title stat-title">Сводная статистика</div>
                    <div class="total total-stat__body stat-body">
                        <div class="table-block">
                            <?=$svodnaya?>
                        </div>
                    </div>
                </div>
                
                <div class="month-stat stat-content">
                    <div class="second-title stat-title">Статистика продаж по месяцам</div>
                    <div class="month-stat__body stat-body">
                        <div class="table-block">
                            <?=$stat_month?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
