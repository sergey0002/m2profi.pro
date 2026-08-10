<?php
$data = $data ?? [];
$apartment = $data['apartment'];
$stat = $data['stat'];
$home_id = $data['home_id'];
$apartment_num = $data['apartment_num'];
$apartments = $data['apartments'] ?? (int)($_GET['apartments'] ?? 0);

// Актуальный статус для сводной/шахматки — status2; legacy status только fallback
$curr_apart_status = (int)($apartment['status2'] ?? $apartment['status'] ?? 0);
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
    <div class="row apartment-order-row">
        <div class="col-md-5 col-xs-12 apartment-info-col" style="text-align:left;">
            <div class="apartment-info-stats">
                Количество комнат — <b><?=$apartment['rooms'];?></b><br>
                Площадь — <b><?=$apartment['area'];?></b> м<sup>2</sup><br>
                Цена — <b><?= number_format((int)($apartment['price'] ?? 0), 0, '.', ' ') ?> руб.</b>
            </div>
            <form class="apartment-info-form" action="?ctr=apartments&act=order&home_id=<?=$home_id?>&apartment_num=<?=$apartment_num?>&apartments=<?=$apartments?>" method="post" enctype="multipart/form-data">
                <div class="apartment-info-field">
                    <label class="apartment-info-field__label" for="apt-status">Статус</label>
                    <select id="apt-status" name="status">
                        <option value="0"<?=($curr_apart_status==0?' selected':'')?>>Не задан</option>
                        <option value="2"<?=($curr_apart_status==2?' selected':'')?>>Свободна</option>
                        <option value="4"<?=($curr_apart_status==4?' selected':'')?>>Забронирована</option>
                        <option value="3"<?=($curr_apart_status==3?' selected':'')?>>Продана</option>
                        <option value="5"<?=($curr_apart_status==5?' selected':'')?>>Забронирована застройщиком</option>
                        <option value="6"<?=($curr_apart_status==6?' selected':'')?>>Квартира подрядчика</option>
                    </select>
                </div>
                <div class="apartment-info-field">
                    <label class="apartment-info-field__label" for="apt-orient-1">Ориентация окон</label>
                    <select id="apt-orient-1" name="window_orient_1">
                        <option value="0">Не задано</option>
                        <?php foreach ($window_orient as $code => $label): ?>
                        <option value="<?= (int)$code ?>"<?=($o1 === (int)$code ? ' selected' : '')?>><?= (int)$code ?>. <?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php /* Направление 2 — отключено */ ?>
                <button type="submit" class="apartment-info-form__submit">Сохранить</button>
            </form>

            <?php
            if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
                include __DIR__ . '/compred_block.php';
            }
            ?>
        </div>
        <div class="col-md-7 col-xs-12 apartment-plan-col">
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
