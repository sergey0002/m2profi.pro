<?php
// fw/templates/apartments/form_broni_done.php
$data = $data ?? [];
$apartment = $data['apartment'] ?? [];
$home = $data['home'] ?? [];
$broni = $data['broni'] ?? [];
$done_message = $data['done_message'] ?? '';
$stat = $data['stat'] ?? '';
$home_id = $data['home_id'] ?? 0;
$apartment_num = $data['apartment_num'] ?? 0;

// --- Извлекаем данные из $data ---
$broni_id = $broni['b_id'] ?? $data['b_id'] ?? 0;
$user_id = $data['data']['user_id']  ;

// --- Цены ---
$old_price = $data['data']['b_price'] ;
$new_price = $data['data']['price'] ?? 0;
  
// --- Форматирование ---
$old_price_fmt = number_format($old_price, 0, '.', ' ');
$new_price_fmt = number_format($new_price, 0, '.', ' ');

// --- Статус брони ---
$booking_status = $broni['b_status'] ?? $data['b_status'] ?? '';


//print '<pre>';
//print_r($data);
//print '</pre>';

$days_left = 14;

if($_SESSION['agency_id']=='958') // ООО НОВЫЕ ТЕХНОЛОГИИ 5 дней на бронь 
{
	$days_left = 5;
}
?>

<style>
.broni-action-btns {
    display: flex;
    justify-content: center;
    gap: 22px;
    margin-top: 38px;
}
.broni-btn {
    padding: 14px 34px;
    border-radius: 9px;
    border: none;
    font-size: 20px;
    font-weight: 600;
    cursor: pointer;
    background: #00cdad;
    color: #fff;
    box-shadow: 0 1.5px 7px rgba(60,74,123,0.04);
    transition: background .18s, color .15s;
    text-decoration: none !important;
}
.broni-btn:hover { background: #019b85; }
.broni-btn-danger { background: #f2675a; }
.broni-btn-danger:hover { background: #d93d2a; }

@media (max-width: 550px) {
    .broni-btn { font-size: 15px; padding: 10px 14px; }
    .broni-action-btns { gap: 10px; }
}
</style>
<h1 style="text-align:center;"><?= $data['data']['title']?></h1>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-xs-12 text-center">
            <h1 style="font-size:34px;">
                <b><?= htmlspecialchars($GLOBALS['homes'][$home_id]['caption'] ?? '—') ?></b>
            </h1>
        </div>
        <div class="col-md-12 col-xs-12 text-center" style="font-size:22px;">
            <p>
                Секция - <span style="color:#00CDAD; font-weight:bold;"><?= htmlspecialchars($apartment['section_id'] ?? '') ?></span><br>
                Этаж - <span style="color:#00CDAD; font-weight:bold;"><?= htmlspecialchars($apartment['floor'] ?? '') ?></span><br>
                Квартира - <span style="color:#00CDAD; font-weight:bold;"><?= htmlspecialchars($apartment['apartment_num'] ?? '') ?></span>
            </p>
            <hr/>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xs-12 text-center">
            <img src="<?= htmlspecialchars($apartment['image_pb'] ?? '') ?>" style="max-height:400px; max-width:100%;">
        </div>

        <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;">
            <div style="color:#00CDAD; font-weight:bold; font-size:21px; margin-top:40px;">
                <?=  $done_message  ?>
            </div>

            <p style="color:#000; margin-top:20px; font-size:14px;">
                <?php if ($broni_id): ?>
                    Бронь №<?= htmlspecialchars($broni_id) ?><br>
                    Дата бронирования: <?= date('d.m.Y', strtotime($broni['b_date'] ?? $data['b_date'] ?? '')) ?><br>
                    Статус: <?= $booking_status == 4 ? 'Забронирована' : 'Неизвестный статус' ?>
                <?php endif; ?>
            </p>

            <div style="margin-top:32px; color:#888; font-size:14px;">
                Срок действия брони — <b><?=$days_left?> календарных дней</b>.<br>
                По прошествии срока бронь будет автоматически аннулирована.
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <?php if ($broni_id && $user_id == $_SESSION['sh_id']): ?>
            <!-- Сообщение о цене -->
            <?php if ($old_price > 0 && $new_price > 0): ?>
                <?php if ($new_price != $old_price): ?>
                    <div class="broni-price-alert" style="color:red; font-weight:bold; text-align:center; width:100%;">
                        <b>Внимание!</b> Актуальная цена квартиры изменилась.<br>
						Квартира забронирована по цене: <b><?=$old_price_fmt?></b><br/>
                        Продление будет возможно только по новой цене: <b><?= $new_price_fmt ?> руб.</b>
                    </div>
                <?php else: ?>
                    <div class="broni-price-info" style="color:red; font-weight:bold; text-align:center;">
                        Продление возможно по прежней цене: <b><?= $old_price_fmt ?> руб.</b>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
 
            <!-- Кнопки действий -->
            <div class="broni-action-btns">
                <a href="iframe_router.php?ctr=apartments&act=order&home_id=<?= intval($home_id) ?>&apartment_num=<?= intval($apartment_num) ?>&subact=upbroni"
                   class="broni-btn" onclick="return confirm('Продлить бронь по цене <?= $new_price_fmt ?> ?');">
                    Продлить бронь
                </a>
                <a href="iframe_router.php?ctr=apartments&act=order&home_id=<?= intval($home_id) ?>&apartment_num=<?= intval($apartment_num) ?>&subact=unsetbroni"
                   class="broni-btn broni-btn-danger"
                   onclick="return confirm('Отменить бронь?');">
                    Отменить бронь
                </a>
            </div>
        <?php else: ?>
            <div class="text-center mt-4">
                <p>На эту квартиру нет активной брони.</p>
            </div>
        <?php endif; ?>
    </div>
</div>