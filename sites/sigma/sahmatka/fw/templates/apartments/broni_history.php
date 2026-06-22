<?php
$rows = $data['history'];
$apt = $data['apt'];
$status_map = $data['status_map'];

if (!$apt) {
    echo '<div style="padding:10px;"><b>' . unit_phrase('not_found') . '</b></div>';
    return;
}

// Коды статусов, при которых показываем подробности о брони и предупреждение о цене
$show_bron_info = in_array($apt['status'], [4, 5, 6]); // 4 — Забронирована, 5 — застройщик, 6 — подрядчик

// Актуальная цена и цена последней брони
$last_bron_price = $rows && isset($rows[0]['price']) ? $rows[0]['price'] : null;
$curr_price = $apt['price'];
$curr_status = (int)$apt['status'];

// Информация о текущей (последней) брони
$last_bron = $rows ? $rows[0] : null;
?>

<style>
.broni-history-table {
    font-size:13px;
    border-collapse:collapse;
    margin:10px 0 18px 0;
    width:100%;
}
.broni-history-table th, .broni-history-table td {
    border:1px solid #d5d5d5;
    padding:5px 8px;
    text-align:left;
}
.broni-history-table th {
    background:#f8f8f8;
    font-weight:600;
}
.broni-history-table tr:nth-child(even) {background:#f7f9fa;}
.broni-history-table tr:hover {background:#e7f1fa;}
.broni-price-alert {
    color:#c70000;
    background: #fff5f2;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 7px;
    border:1px solid #ffe2e2;
}
.broni-price-info {
    color:#207f13;
    background: #f5fff4;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 7px;
    border:1px solid #c1e2b3;
}
.broni-price-diff {
    color: #c70000;
    font-weight: bold;
}
.broni-user-block {
    background: #f4faff;
    padding: 12px 14px;
    border-radius: 6px;
    font-size: 15px;
    margin-bottom: 10px;
    border:1px solid #cbe8f6;
}
</style>

<!-- Актуальные данные о квартире -->
<div style="margin-bottom:10px;">
    <b>Актуальная цена:</b>
    <span class="<?=($last_bron_price!==null && $curr_price != $last_bron_price ? 'broni-price-diff':'')?>"><?=number_format($curr_price, 0, '.', ' ')?> руб.</span><br>
    <b>Актуальный статус:</b> <?=$status_map[$apt['status']] ?? $apt['status']?>
</div>

<?php
// Блок с информацией по активной брони (Пользователь, агентство, телефон, e-mail, дата, окончание брони)
if ($show_bron_info && $last_bron) {
    $date_start = strtotime($last_bron['fdate']);
    $date_end = $date_start + 14*24*3600;
    $days_left = ceil(($date_end - time()) / (24*3600));
    ?>
    <div class="broni-user-block">
        <b>Текущий статус:</b> <?=($status_map[$curr_status] ?? $curr_status)?>, по цене: <b><?=number_format($last_bron['price'], 0, '.', ' ')?> руб.</b> <br>
        Пользователь: <b><?=htmlspecialchars($last_bron['login'])?></b><BR/>
		
        <?php if ($last_bron['name']) { ?>(<?=htmlspecialchars($last_bron['name'])?>)<?php } ?><br>
        Email: <a href="mailto:<?=htmlspecialchars($last_bron['e_mail'])?>"><?=htmlspecialchars($last_bron['e_mail'])?></a><br>
        Телефон: <?=htmlspecialchars($last_bron['phone'])?><br>
        Агентство: <?=htmlspecialchars($last_bron['caption'])?><br>
        Дата бронирования: <?=date('d.m.Y H:i:s', strtotime($last_bron['fdate']))?><br>
        <?php if ($curr_status == 4) { ?>
            Бронь истекает через <b><?=$days_left?></b> <?=($days_left==1?'день':'дней')?> — до <b><?=date('d.m.Y', $date_end)?></b>
        <?php } else { ?>
            Срок действия: <b>Бессрочно</b>
        <?php } ?>
    </div>
<?php
}

// Выводим инфоблок о цене только при статусах 4, 5, 6
if ($show_bron_info && $last_bron_price !== null && $curr_price != $last_bron_price) { ?>
    <div class="broni-price-alert">
        <b>Внимание!</b> <?= unit_phrase('price_changed') ?>.<br>
        После окончания текущей брони новая бронь будет возможна только по актуальной цене <b><?=number_format($curr_price, 0, '.', ' ')?> руб.</b>
    </div>
<?php } elseif ($show_bron_info && $last_bron_price !== null && $curr_price == $last_bron_price) { ?>
    <div class="broni-price-info">
        <span>При повторном бронировании будет действовать та же цена.</span>
    </div>
<?php } ?>

<h3 style="margin:10px 0 8px 0;">История бронирования</h3>

<?php if (!$rows) { ?>
    <div style="padding:10px;"><b>История бронирования отсутствует</b></div>
    <?php return;
} ?>

<table class="broni-history-table">
    <tr>
        <th>ID</th>
        <th>Дата</th>
        <th>Агентство</th>
        <th>Пользователь</th>
        <th>Статус брони</th>
        <th>Цена на момент бронирования</th>
    </tr>
<?php foreach($rows as $row) { ?>
    <tr>
        <td><?=$row['broni_id']?></td>
        <td><?=date('d.m.Y H:i:s', strtotime($row['fdate']))?></td>
        <td><?=htmlspecialchars($row['caption'])?></td>
        <td><b><?=htmlspecialchars($row['login'])?></b> (<?=htmlspecialchars($row['name'])?>)</td>
        <td><?=$status_map[$row['bstatus']] ?? $row['bstatus']?></td>
        <td><?=number_format($row['price'], 0, '.', ' ')?> руб.</td>
    </tr>
<?php } ?>
</table>
