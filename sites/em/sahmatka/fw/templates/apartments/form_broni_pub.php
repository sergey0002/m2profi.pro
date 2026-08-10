<?php
$data = $data ?? [];
$apartment = $data['apartment'];
$stat = $data['stat']; // ЗАчем то статус последней брони 
$home_id = $data['home_id'];
$apartment_num = $data['apartment_num'];
$apartments = $data['apartments'];


$curr_apart_status = isset($apartment['status2']) ? (int)$apartment['status2'] : 0;  // АКТУАЛЬНЫЙ СТАТУС КВАРТИРЫ 
$o1 = (int)($apartment['window_orient_1'] ?? 0);
$o2 = (int)($apartment['window_orient_2'] ?? 0);

if (!empty($_GET['dev1']))
{
	print '<pre>';
	print_r($data);
	print '</pre>';
}
// АГЕНТАМ И ПУБЛИКЕ
?>
<style>
.xxx *{font-size:16px;     line-height: 1.6em;}
input, select {
    border: 1px solid #000;
    border-radius: 5px;
    padding: 4px;
    font-size: 16px;
    margin: 6px;
    width: 95%;
    font-size: 14px;
}
</style> 
<div class="container-fluid"> <br/><br/>
<h1 style="text-align:center; font-size:32px;"><?=$data['data']['title'];?>   </h1>
    <div class="row">
        <div class="col-md-12 col-xs-12" style="text-align:center;">       
            <h1 style="font-size:34px;"><b><?=$GLOBALS['homes'][$data['apartment']['home_id']]['caption'];?></b></h1>   
        </div>
        <div class="col-md-12 col-xs-12" style="text-align:center; font-size:22px;">
            Секция - <span style="color:#00CDAD; font-weight:bold;"><?=$apartment['section_id'];?></span>
            Этаж - <span style="color:#00CDAD; font-weight:bold;"><?=$apartment['floor'];?></span>
            Квартира - <span style="color:#00CDAD; font-weight:bold;"><?=$apartment['apartment_num'];?></span>
            <hr/>
        </div>
    </div>
    <?php if ($data['success']) { ?>
        <div class="alert alert-success"><?=$data['success']?></div>
    <?php } elseif ($data['err_m']) { ?>
        <div class="alert alert-danger"><?=implode('<br>', $data['err_m'])?></div>
    <?php } ?>
    <div class="row apartment-order-row">
        <div class="col-md-5 col-xs-12 apartment-info-col xxx" style="text-align:left;">
            <div class="apartment-info-stats">
                Количество комнат — <b><?=$apartment['rooms'];?></b><br>
                Площадь — <b><?=$apartment['area'];?></b> м<sup>2</sup><br>
                Цена — <b><?=number_format($data['apartment']['price'], 0, '.', ' ')?> руб.</b>
            </div>
            <?php if (!empty($data['is_manual_mode'])) { ?>
                <div class="alert alert-warning" style="text-align:left; margin:10px 0; padding:12px; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; color:#856404; font-size:15px; line-height:1.5;">
                    <?= $data['manual_message_html'] ?? nl2br(htmlspecialchars($data['manual_message'] ?? 'Обратитесь в отдел продаж.', ENT_QUOTES, 'UTF-8')) ?>
                </div>
            <?php } elseif (($curr_apart_status=="2" || !$curr_apart_status) && ($data['show_form'] ?? true)) { ?>
                <form class="apartment-info-form" action="?ctr=apartments&act=order&home_id=<?=$home_id?>&apartment_num=<?=$apartment_num?>&apartments=<?=$apartments?>" method="post" enctype="multipart/form-data">
                    <div class="apartment-info-field">
                        <label class="apartment-info-field__label">Скан паспорта (фото)</label>
                        <input type="file" name="passport_scan" accept="image/*;capture=camera">
                    </div>
                    <div class="apartment-info-field">
                        <label class="apartment-info-field__label">Скан паспорта (прописка)</label>
                        <input type="file" name="passport_scan2" accept="image/*;capture=camera">
                    </div>
                    <div class="apartment-info-field">
                        <label class="apartment-info-field__label">Форма №2 бронь</label>
                        <input type="file" name="anket" accept="image/*;capture=camera">
                    </div>
                    <span style="font-size:12px; color:#ff0000;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</span>
                    <div style="margin-top:4px;">
                        <input type="checkbox" id="checkbox" name="checkbox" style="width:auto;" onchange="document.getElementById('submit').disabled = !this.checked;">
                        <span style="font-size:12px;">Подтверждаю согласие с <a target="_blank" style="font-size:12px;" href="http://em-nsk.ru/sahmatka/reglament.php">регламентом</a></span>
                    </div>
                    <input type="submit" id="submit" disabled="disabled" value="ЗАБРОНИРОВАТЬ" class="apartment-info-form__submit" style="margin-left:0;">
                </form>
            <?php } ?>

            <?php
            if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
                include __DIR__ . '/compred_block.php';
            }
            ?>
        </div>
        <div class="col-md-7 col-xs-12 apartment-plan-col" style="text-align:center;">
            <?php if ($html_compass = render_window_compass_images($o1, $o2, 110)): ?>
            <div class="mdl-compas apartment-compas">
                <?= $html_compass ?>
            </div>
            <?php endif; ?>
            <?= render_plan_with_sun($apartment['image_pb'] ?? '', $o1, $o2) ?>
            <?php if ($o1 || $o2): ?>
            <div class="window-compass__label apartment-compas-caption">
                Окна: <?= htmlspecialchars(window_orient_labels($o1, $o2)) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
