<?php
$data = $data ?? [];
$apartment = $data['apartment'];
$stat = $data['stat'];
$home_id = $data['home_id'];
$apartment_num = $data['apartment_num'];
$apartments = $data['apartments'] ?? (int)($_GET['apartments'] ?? 0);

$curr_apart_status = isset($apartment['status']) ? (int)$apartment['status'] : 0;
$o1 = (int)($apartment['window_orient_1'] ?? 0);
$o2 = (int)($apartment['window_orient_2'] ?? 0);
$window_orient = $GLOBALS['window_orient'] ?? [];
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-xs-12" style="text-align:center;">
            <h1 style="font-size:34px;"><b><?=$GLOBALS['homes'][$home_id]['caption'];?></b></h1>
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
        <div class="col-md-6 col-xs-12 apartment-plan-col">
            <?php if ($html_compass = render_window_compass_images($o1, $o2, 110)): ?>
            <div class="mdl-compas apartment-compas">
                <?= $html_compass ?>
            </div>
            <?php endif; ?>
            <img src="<?=$apartment['image_pb'];?>?x=178" style="max-height:400px; max-width:100%" alt="">
            <?php if ($o1 || $o2): ?>
            <div class="window-compass__label apartment-compas-caption">
                Окна: <?= htmlspecialchars(window_orient_labels($o1, $o2)) ?>
                <?php if ($codes = window_orient_codes($o1, $o2)): ?>(<?= htmlspecialchars($codes) ?>)<?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 col-xs-12" style="text-align:left; font-size:18px;">
            Количество комнат — <b><?=$apartment['rooms'];?></b><br>
            Площадь — <b><?=$apartment['area'];?></b> м<sup>2</sup><br>
            Цена — <b><?= number_format((int)($apartment['price'] ?? 0), 0, '.', ' ') ?> руб.</b><br><br>
            <form action="?ctr=apartments&act=order&home_id=<?=$home_id?>&apartment_num=<?=$apartment_num?>&apartments=<?=$apartments?>" method="post" enctype="multipart/form-data">
                Статус —
                <select name="status" style="font-size:16px;">
                    <option value="0"<?=($curr_apart_status==0?' selected':'')?>>Не задан</option>
                    <option value="2"<?=($curr_apart_status==2?' selected':'')?>>Свободна</option>
                    <option value="4"<?=($curr_apart_status==4?' selected':'')?>>Забронирована</option>
                    <option value="3"<?=($curr_apart_status==3?' selected':'')?>>Продана</option>
                    <option value="5"<?=($curr_apart_status==5?' selected':'')?>>Забронирована застройщиком</option>
                    <option value="6"<?=($curr_apart_status==6?' selected':'')?>>Квартира подрядчика</option>
                </select>
                <br><br>
                Ориентация окон<br>
                Направление 1
                <select name="window_orient_1" style="font-size:16px; width:95%;">
                    <option value="0">Не задано</option>
                    <?php foreach ($window_orient as $code => $label): ?>
                    <option value="<?= (int)$code ?>"<?=($o1 === (int)$code ? ' selected' : '')?>><?= (int)$code ?>. <?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <br>
                <?php /* Направление 2 — отключено
                Направление 2
                <select name="window_orient_2" style="font-size:16px; width:95%;">
                    <option value="0">Не задано</option>
                    <?php foreach ($window_orient as $code => $label): ?>
                    <option value="<?= (int)$code ?>"<?=($o2 === (int)$code ? ' selected' : '')?>><?= (int)$code ?>. <?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                */ ?>
                <br><br>
                <input type="submit" value="Сохранить" style="background-color:#00CDAD; color:#FFF; padding:10px 40px; font-size:16px; font-weight:bold; border-radius:7px;">
            </form>
        </div>
    </div>
</div>
