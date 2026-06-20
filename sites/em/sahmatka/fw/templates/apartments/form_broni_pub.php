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

if($_GET[dev1])
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
    <div class="row">
        <div class="col-md-6 col-xs-12 apartment-plan-col" style="text-align:center;">
            <?php if ($html_compass = render_window_compass_images($o1, $o2, 110)): ?>
            <div class="mdl-compas apartment-compas">
                <?= $html_compass ?>
            </div>
            <?php endif; ?>
            <img src="<?=$apartment['image_pb'];?>?x=178" style="max-height:400px; max-width:100%">
            <?php if ($o1 || $o2): ?>
            <div class="window-compass__label apartment-compas-caption">
                Окна: <?= htmlspecialchars(window_orient_labels($o1, $o2)) ?>
                <?php if ($codes = window_orient_codes($o1, $o2)): ?>(<?= htmlspecialchars($codes) ?>)<?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 col-xs-12 xxx" style="text-align:left; font-size:16px;">
		
		Количество комнат -  <b><?=$apartment['rooms'];?></b><br/>
		Площадь - <b><?=$apartment['area'];?></b> м<sup>2</sup><br/>
		Цена - <b ><?=number_format($data['apartment']['price'], 0, '.', ' ')?> руб.</b>  <br/> <br/>
            <?php if ($curr_apart_status=="2" || !$curr_apart_status) { ?>
                <form action="?ctr=apartments&act=order&home_id=<?=$home_id?>&apartment_num=<?=$apartment_num?>&apartments=<?=$apartments?>" method="post" enctype="multipart/form-data">
                    <h2 style="font-size:16px;">Данные покупателя</h2>
                    Скан паспорта страницы с фото: <input type="file" name="passport_scan" accept="image/*;capture=camera"><br/><br/>
                    Скан паспорта страницы с пропиской: <input type="file" name="passport_scan2" accept="image/*;capture=camera"><br/>
                    Форма №2 бронь: <input type="file" name="anket" accept="image/*;capture=camera"><br/> 
                    <span style="font-size:12px; color:#ff0000;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</span><br/><br/>
                    <input type="checkbox" id="checkbox" name="checkbox" style="width:auto;" onchange="document.getElementById('submit').disabled = !this.checked;">
                    <span style="font-size:12px;">Подтверждаю согласие с <a target="_blank" style="font-size:12px;" href="http://em-nsk.ru/sahmatka/reglament.php">регламентом</a></span><br/><br/>
                    <input type="submit" id="submit" disabled="disabled" value="ЗАБРОНИРОВАТЬ" class="stat-top-btn btn btn_arrow-long" style="margin-left:0;">
                </form>
            <?php } ?>

            <?php
            if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
                include __DIR__ . '/compred_block.php';
            }
            ?>
        </div>
    </div>
</div>
