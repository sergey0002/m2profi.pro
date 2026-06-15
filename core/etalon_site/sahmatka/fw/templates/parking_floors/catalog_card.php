<?
$v=$data;
?>
<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
  <div class="object" style="<?=$v['opt']?>">
    <div class="object__title">
      <?=$v['adress_disp']?>
    </div>
    <div class="object__pict">
      <img src="/sahmatka/parking/render/<?=$v['parking_building_id']?>.jpg" alt="">
      <div class="object__info">
        <div class="object__status object__status_sale">
          <?=$v['complite_text']?>
        </div>
      </div>
    </div>
    <a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" class="object__btn btn btn_arrow">К объекту<i></i></a>
  </div>
</div>