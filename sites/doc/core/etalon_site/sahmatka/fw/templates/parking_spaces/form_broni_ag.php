<?
// print_r($data);
global $filed;


if( $_SESSION['sh_login'] == 'admin' )
{
	$broni_status_arr[0]='Не задан';
	$broni_status_arr[2]='Свободна';
	$broni_status_arr[3]='Продана';
	$broni_status_arr[4]='Забронирована';
	$broni_status_arr[5]='Забронирована застройщиком';
	$broni_status_arr[6]='Парковка подрядчика';					
}
elseif(  $_SESSION['sh_login'] == 'em_nsv' )
{
	$broni_status_arr[4]='Забронирована';
	$broni_status_arr[6]='Парковка подрядчика';
	$broni_status_arr[5]='Забронирована застройщиком';				  
}

$stat = $data['status'];
?>
<br/><br/>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12 col-xs-12" style="text-align:center;">
      <h1 style="font-size:34px; text-align:center;"><b><?=$data['adress_disp'];?></b></h1>
    </div>
    <div class="col-md-12 col-xs-12" style="text-align:center; font-size:22px;">
      Этаж - <span style="color:#00CDAD; font-weight:bold;"><?=$data['floor'];?></span>   
	  Место - <span style="color:#00CDAD; font-weight:bold;"><?=$data['num'];?></span>
      <hr/>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 col-xs-12" style="text-align:center;">
      <img src="https://{$GLOBALS['config']['domain']}/sahmatka//images/parkingcar.png" style="max-height:600px; max-width:100%">
	  <div style="position:absolute; top:240px; text-align:center; width:100%; font-size:54px; font-weight:bold; right:5px;"> <?=$data['num']?></div>
    </div>
    <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;">
    
	<?
	if( ($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'em_nsv')   )
	{
	?>
	<form action="iframe_router.php?ctr=parking_spaces&act=order&id=<?=$data['parking_space_id'];?>" method="post" enctype="multipart/form-data" method="post">
		<div class="row">
			 <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;"> 
			 <?
				//Статус
			 ?>
			 </div>
			 
			 <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;">	
			 <?
				//Цена
			 ?>	
			 </div>
		</div>
		
		<div class="row">
			 <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;"> 
			 <?
				// Цена
				if( $_SESSION['sh_login'] == 'admin' )
				{
					$filed->text_num('price','Цена',$data['price'],'0','','1','width:200px; display:inline-block;');
				}
				else
				{
					// Для не админа цена только для свободных
					if(!$data['status'] || $data['status'] == 2)
					{
						?> Цена - <b><?=$data['price'];?></b> <?
					}
				}
				
			 ?>
			 </div>
			 
			 <div class="col-md-12 col-xs-12" style="text-align:left; font-size:24px;">	
			 <?
				// Площадь
				if( $_SESSION['sh_login'] == 'admin' )
				{
					$filed->text_float('area','Площадь',$data['area'],'','','0.01','width:200px; display:inline-block;');
				}
				else
				{
					?>Площадь - <b><?=$data['area']?></b> м<sup>2</sup><?
				}
			 ?>	
			 </div>
		</div>
		
		<div class="row">
			 <div class="col-md-12 col-xs-12" style="text-align:left; font-size:24px;"> 
			 <?
			 
				if( $_SESSION['sh_login'] == 'admin' || ( $_SESSION['sh_login'] == 'em_nsv' && $data['status'] == 2 || $data['status']==5 || $data['status']==6 || !$data['status'] ) )
				{
				 
						
					$filed->select( 'status' , 'Статус' , $broni_status_arr , $data['status'] );
					
					 
					// $filed->checkbox('del','Удалить',$data['del']); print '<br/>';
				}
				else
				{
					?>Статус - <b><?=$broni_status_arr[ $data['status'] ]?></b><?
				}
				
			 ?>
			 </div>
			 
			 <div class="col-md-12 col-xs-12" style="text-align:left; font-size:24px;">	
			 <?
			 
			 //Размер
				if( $_SESSION['sh_login'] == 'admin' )
				{
					$filed->text('size','Размер',$data['size']);
				}
				else
				{
					if($data['size']){ ?>Размер - <b><?=$data['size']?></b><? }
				}
				
			 ?>	
			 </div>
			 
			
			 
			 
		</div>
	 
	 
	<div class="row">
		<div class="col-md-12 col-xs-12" style="text-align:left; font-size:24px;">	
			 <?
			 //Размер
				if( $_SESSION['sh_login'] == 'admin' )
				{
					$filed->text('num','Номер',$data['num']);
				}
				else
				{
					?>Номер - <b><?=$data['num']?></b><?
				}
			 ?>	
		</div>	 
	</div>
	 
	 <div class="row">
	 
		<div class="col-md-12 col-xs-12" style="text-align:left; font-size:24px; text-algin:center;"> 
		<br/> 
		<input type="submit" value="Сохранить" style="border: solid 1px #FFF; width:95%; background-color:#00CDAD;  color:#FFF; padding:10px;  padding-right:40px; padding-left:40px; font-size:16px; font-weight:bold;   border-radius:7px;  ">
		
		</div>
	 </div>
	 
	 
	 
	</form>
               
    <?
	}
	elseif($_SESSION['sh_login'] != 'admin' &&  $_SESSION['sh_login'] !='em_nsv'  ) // Админ агентства или агент
	{
		?>
        <div style="font-size:14px;">
        <? 
$_GET['home_id'] = trim( $_GET['home_id'] );
if( $stat=="2" || !$stat   ) 
{
	?>
                      <form action="iframe_router.php?ctr=parking_spaces&act=order&id=<?=$data['parking_space_id'];?>" method="post" enctype="multipart/form-data" method="post">
                        <h2 style="font-size:16px;">Данные покупателя</h2> Скан паспорта страницы с фото:
                        <input type="file" name="passport_scan" accept="image/*;capture=camera">
                        </br/>
                        <br/> Скан паспорта страницы с пропиской:
                        <input type="file" name="passport_scan2" accept="image/*;capture=camera">
                        <br/> Форма №2 бронь:
                        <input type="file" name="anket" accept="image/*;capture=camera">
                        <br/>
                        <br/>
                        <b style="font-size:20px; color:#ff0000;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</b>
                        <br/>
                        <br/>

                        <input type="checkbox" id="checkbox" name="checkbox" style="width:auto;" onchange="document.getElementById('submit').disabled = !this.checked;">
                        <span style="font-size:20px;">Подтверждаю согласие с <a target="_blanc"  style="font-size:20px;" href="http://em-nsk.ru/sahmatka/reglament.php">регламентом </a></span>
                        <br/>
                        <br/>

                        <input type="submit" id="submit" disabled="disabled" value="ЗАБРОНИРОВАТЬ" class="stat-top-btn btn btn_arrow-long" style="  margin-left:0;" />
                      </form>
                      <?
}
		?>
		</div>
        <?
	}
	?>

    </div>

  </div>
</div>