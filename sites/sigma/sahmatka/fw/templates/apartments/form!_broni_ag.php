<?php
$data = $data ?? [];
$apartment = $data['apartment'];
$stat = $data['stat'];
$home_id = $data['home_id'];
$apartment_num = $data['apartment_num'];
$apartments = $data['apartments'];
?>
<div class="container-fluid">111111111111
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
            <form action="?ctr=apartments&act=order&home_id=<?=$home_id?>&apartment_num=<?=$apartment_num?>&apartments=<?=$apartments?>&dev=1 " method="post" enctype="multipart/form-data">
                Статус - 
                <select name="status">
                    <option value="0"<?=($stat==0?' selected':'')?>>Не задан</option>
                    <option value="2"<?=($stat==2?' selected':'')?>>Свободна</option>
                    <option value="4"<?=($stat==4?' selected':'')?>>Забронирована</option>
                    <option value="3"<?=($stat==3?' selected':'')?>>Продана</option>
                    <option value="5"<?=($stat==5?' selected':'')?>>Забронирована застройщиком</option>
                    <option value="6"<?=($stat==6?' selected':'')?>><?= unit_phrase('contractor') ?></option>
                </select>
                <br><br>
                <input type="submit" value="Сохранить" style="background-color:#00CDAD; color:#FFF; padding:10px 40px; font-size:16px; font-weight:bold; border-radius:7px;">
            </form>
        </div>
    </div>
</div>