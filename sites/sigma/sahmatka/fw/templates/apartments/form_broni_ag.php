<?php
//print '<pre>';
//print_r($data);
//print_r($_POST);
//print '</pre>';

$data = $data ?? [];
$apartment = $data['apartment'];
$stat = $data['stat']; // ЗАчем то статус последней брони 
$home_id = $data['home_id'];
$apartment_num = $data['apartment_num'];
$apartments = $data['apartments'];

$curr_apart_status = isset($apartment['status']) ? (int)$apartment['status'] : 0;  // АКТУАЛЬНЫЙ СТАТУС КВАРТИРЫ 


?>
<div class="container-fluid">АГ
    <div class="row">
        <div class="col-md-12 col-xs-12" style="text-align:center;">
            <h1 style="font-size:34px;"><b><?=$GLOBALS['homes'][$home_id]['caption'];?></b></h1>   
        </div>
        <div class="col-md-12 col-xs-12" style="text-align:center; font-size:22px;">
            Секция - <span style="color:#00CDAD; font-weight:bold;"><?=$apartment['section_id'];?></span>
            Этаж - <span style="color:#00CDAD; font-weight:bold;"><?=$apartment['floor'];?></span>
            <?= unit_label_cap('nom') ?> - <span style="color:#00CDAD; font-weight:bold;"><?=$apartment['apartment_num'];?></span>
            <hr/>
        </div>
    </div>
    <?php if ($data['success']) { ?>
        <div class="alert alert-success"><?=$data['success']?></div>
    <?php } elseif ($data['err_m']) { ?>
        <div class="alert alert-danger"><?=implode('<br>', $data['err_m'])?></div>
    <?php } ?>
    <div class="row">
        <div class="col-md-6 col-xs-12" style="text-align:center;">
            <img src="<?=$apartment['image_pb'];?>?x=178" style="max-height:400px; max-width:100%">
        </div>
        <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;">
            <form action="?ctr=apartments&act=order&home_id=<?=$home_id?>&apartment_num=<?=$apartment_num?>&apartments=<?=$apartments?> " method="post" enctype="multipart/form-data">
                Статус - 
                <select name="status">
                    <option value="0"<?=($curr_apart_status==0?' selected':'')?>>Не задан</option>
                    <option value="2"<?=($curr_apart_status==2?' selected':'')?>>Свободна</option>
                    <option value="4"<?=($curr_apart_status==4?' selected':'')?>>Забронирована</option>
                    <option value="3"<?=($curr_apart_status==3?' selected':'')?>>Продана</option>
                    <option value="5"<?=($curr_apart_status==5?' selected':'')?>>Забронирована застройщиком</option>
                    <option value="6"<?=($curr_apart_status==6?' selected':'')?>><?= unit_phrase('contractor') ?></option>
                </select>
                <br><br>
                <input type="submit" value="Сохранить" style="background-color:#00CDAD; color:#FFF; padding:10px 40px; font-size:16px; font-weight:bold; border-radius:7px;">
            </form>
        </div>
    </div>
</div>