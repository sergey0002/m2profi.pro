<?php
// Исправленный запрос: считаем только нужные статусы и корректно группируем
$sql = "
    SELECT 
        a.home_id,
        h.show,
        h.title,
        
        -- Свободные: status = 0, 2 или NULL
        COUNT(
            CASE 
                WHEN a.status IN (0, 2) OR a.status IS NULL THEN 1 
            END
        ) AS free_count,

        -- Остальные статусы
        COUNT(CASE WHEN a.status = 1 THEN 1 END) AS no_data,
        COUNT(CASE WHEN a.status = 3 THEN 1 END) AS sold,
        COUNT(CASE WHEN a.status = 4 THEN 1 END) AS agent_reserved,
        COUNT(CASE WHEN a.status = 5 THEN 1 END) AS builder_reserved,
        COUNT(CASE WHEN a.status = 6 THEN 1 END) AS contractor_reserved,

        -- Общее количество (для справки)
        COUNT(*) AS total_all

    FROM apartaments a
    LEFT JOIN homes h ON h.home_id = a.home_id AND h.show = 1

    WHERE 
        a.home_id NOT IN (18, 19)
        AND h.show = 1

    GROUP BY a.home_id
    ORDER BY h.title
";

$query = mysqli_query($connection, $sql);

// Инициализируем массивы
$new_arr = [
    'free' => ['all' => 0],
    'no_data' => ['all' => 0],
    'sold' => ['all' => 0],
    'agent' => ['all' => 0],
    'builder' => ['all' => 0],
    'contractor' => ['all' => 0],
    'total' => ['all' => 0]
];

$homesn = [];

while ($result = mysqli_fetch_array($query)) {
    $home_id = (int)$result['home_id'];
    $title = $result['title'];

    $homesn[$home_id]['caption'] = $title;

    // Заполняем данные по дому
    $new_arr['free'][$home_id] = (int)$result['free_count'];
    $new_arr['no_data'][$home_id] = (int)$result['no_data'];
    $new_arr['sold'][$home_id] = (int)$result['sold'];
    $new_arr['agent'][$home_id] = (int)$result['agent_reserved'];
    $new_arr['builder'][$home_id] = (int)$result['builder_reserved'];
    $new_arr['contractor'][$home_id] = (int)$result['contractor_reserved'];
    $new_arr['total'][$home_id] = (int)$result['total_all'];

    // Суммы по всем домам
    $new_arr['free']['all'] += $result['free_count'];
    $new_arr['no_data']['all'] += $result['no_data'];
    $new_arr['sold']['all'] += $result['sold'];
    $new_arr['agent']['all'] += $result['agent_reserved'];
    $new_arr['builder']['all'] += $result['builder_reserved'];
    $new_arr['contractor']['all'] += $result['contractor_reserved'];
    $new_arr['total']['all'] += $result['total_all'];
}

$homes = $homesn;

// Подготавливаем данные для старого стиля (для детальной таблицы), если нужно
$arr = [];
while (mysqli_data_seek($query, 0) || true) break; // сброс курсора не поддерживается, перезапросим
$query2 = mysqli_query($connection, $sql);
while ($result = mysqli_fetch_array($query2)) {
    $row = [
        'home_id' => (int)$result['home_id'],
        'show' => (int)$result['show'],
        'c' => (int)$result['total_all'],
        1 => (int)$result['no_data'],
        3 => (int)$result['sold'],
        4 => (int)$result['agent_reserved'],
        5 => (int)$result['builder_reserved'],
        6 => (int)$result['contractor_reserved'],
        // status 2 и 0 не сохраняем отдельно — они в "свободных"
    ];
    $arr[] = $row;
}
?>

<!-- Секция "sales" (скрыта, но оставляем как есть) -->
<div class="sales" style="display:none;">
    <div class="sales-wrap">
        <div class="sales-item">
            <div class="sales-item__num"><?= $new_arr['free']['all'] ?></div>
            <div class="sales-item__status sales-item__status_green">Свободно</div>
            <div class="sales-item__info">
                <ul class="sales-item-list">
                    <?php foreach ($new_arr['free'] as $k => $v): ?>
                        <?php if ($k !== 'all' && !empty($homes[$k]['caption'])): ?>
                            <li><?= $v ?> - <?= htmlspecialchars($homes[$k]['caption']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="#" style="display:none;" class="sales-inlink">Детальная статистика</a>
        </div>
        <div class="sales-item">
            <div class="sales-item__num"><?= $new_arr['agent']['all'] ?></div>
            <div class="sales-item__status sales-item__status_yellow">Забронировано</div>
            <div class="sales-item__info">
                <ul class="sales-item-list">
                    <?php foreach ($new_arr['agent'] as $k => $v): ?>
                        <?php if ($k !== 'all' && !empty($homes[$k]['caption'])): ?>
                            <li><?= $v ?> - <?= htmlspecialchars($homes[$k]['caption']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="#" class="sales-inlink" style="display:none;">Детальная статистика</a>
        </div>
        <div class="sales-item">
            <div class="sales-item__num"><?= $new_arr['sold']['all'] ?></div>
            <div class="sales-item__status sales-item__status_red">Продано</div>
            <div class="sales-item__info">
                <ul class="sales-item-list">
                    <?php foreach ($new_arr['sold'] as $k => $v): ?>
                        <?php if ($k !== 'all' && !empty($homes[$k]['caption'])): ?>
                            <li><?= $v ?> - <?= htmlspecialchars($homes[$k]['caption']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="#" class="sales-inlink" style="display:none;">Детальная статистика</a>
        </div>
        <div class="sales-item">
            <div class="sales-item__num"><?= $new_arr['builder']['all'] ?></div>
            <div class="sales-item__status sales-item__status_grey">Бронь <br>застройщика</div>
            <div class="sales-item__info">
                <ul class="sales-item-list">
                    <?php foreach ($new_arr['builder'] as $k => $v): ?>
                        <?php if ($k !== 'all' && !empty($homes[$k]['caption'])): ?>
                            <li><?= $v ?> - <?= htmlspecialchars($homes[$k]['caption']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="#" class="sales-inlink">Детальная статистика</a>
        </div>
        <div class="sales-item">
            <div class="sales-item__num"><?= $new_arr['contractor']['all'] ?></div>
            <div class="sales-item__status sales-item__status_blue">Бронь <br>подрядчика</div>
            <div class="sales-item__info">
                <ul class="sales-item-list">
                    <?php foreach ($new_arr['contractor'] as $k => $v): ?>
                        <?php if ($k !== 'all' && !empty($homes[$k]['caption'])): ?>
                            <li><?= $v ?> - <?= htmlspecialchars($homes[$k]['caption']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="#" class="sales-inlink">Детальная статистика</a>
        </div>
    </div>
    <a href="#" class="sales-link">Детальная статистика</a>
</div>

<?php
// Генерация детальной таблицы по домам
ob_start();
foreach ($arr as $v) {
    $home_id = $v['home_id'];
    if (!$home_id || empty($homes[$home_id]['caption'])) continue;

    $cll = ($v['show']) ? '' : '2';
    $free_actual = $new_arr['free'][$home_id] ?? 0;
    $not_sold = $v['c'] - $v[3]; // всего минус продано
    ?>
    <div class="stat-rooms-item<?= $cll ?>">
        <div class="stat-rooms-item__title<?= $cll ?>"><?= htmlspecialchars($homes[$home_id]['caption']) ?></div>
        <ul class="stat-rooms-item-list<?= $cll ?>">
            <li><span>Всего квартир:</span> <span><?= $v['c'] ?></span></li>
            <li><span>Продано</span> <span><?= $v[3] ?></span></li>
            <li><span>Бронь агента:</span> <span><?= $v[4] ?></span></li>
            <li><span>Бронь застройщика:</span> <span><?= $v[5] ?></span></li>
            <li><span>Бронь подрядчика:</span> <span><?= $v[6] ?></span></li>
            <li><span>Свободно для бронирования агентами:</span> <span><?= $free_actual ?></span></li>
            <li><span>Квартир не продано:</span> <span><?= $not_sold ?></span></li>
        </ul>
    </div>
    <?php
}
$obj_stat = ob_get_clean();
?>

<section class="section-stat">
    <div class="container">
        <div class="page-header">
            <div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
            <div class="page-header__title">СТАТИСТИКА <span>квартир</span></div>
        </div>
        <div class="stat">
            <div class="stat-top stat-top_lp stat-top_room">
                <div class="stat-top-filter">
                    <a href="#" class="stat-top-btn btn btn_arrow-long" style="display:none;">ДЕТАЛЬНАЯ СТАТИСТИКА<i></i></a>
                </div>
                <a href="JavaScript:window.print();" class="stat-top__print"></a>
            </div>
            <div class="stat-total">
                <div class="stat-total-item">
                    <div class="stat-total-item__num"><?= $new_arr['free']['all'] ?></div>
                    <div class="stat-total-item__btn" style="background-color: #8DFFA9; color:#000;">Свободно</div>
                </div>
                <div class="stat-total-item">
                    <div class="stat-total-item__num"><?= $new_arr['agent']['all'] ?></div>
                    <div class="stat-total-item__btn" style="background-color: #FEFF52; color:#000;">ЗАБРОНИРОВАНО</div>
                </div>
                <div class="stat-total-item">
                    <div class="stat-total-item__num"><?= $new_arr['sold']['all'] ?></div>
                    <div class="stat-total-item__btn" style="background-color: #FF8A90; color:#000;">ПРОДАНО</div>
                </div>
                <div class="stat-total-item">
                    <div class="stat-total-item__num"><?= $new_arr['builder']['all'] ?></div>
                    <div class="stat-total-item__btn" style="background-color: #D5E6FE; color:#000;">БРОНЬ ЗАСТРОЙЩИКА</div>
                </div>
                <div class="stat-total-item">
                    <div class="stat-total-item__num"><?= $new_arr['contractor']['all'] ?></div>
                    <div class="stat-total-item__btn" style="background-color: #991DFB;">БРОНЬ ПОДРЯДЧИКА</div>
                </div>
            </div>
            <div class="stat-rooms">
                <?= $obj_stat ?>
            </div>
        </div>
    </div>
</section>