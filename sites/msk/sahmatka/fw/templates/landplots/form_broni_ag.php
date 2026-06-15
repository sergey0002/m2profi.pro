<?
// print_r($data);
global $filed;


// print_r($data);

if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'goodzem')
{
	$broni_status_arr[0]='Не задан';
	$broni_status_arr[2]='Свободен';
	$broni_status_arr[3]='Продан';
	$broni_status_arr[4]='Забронирован';
	$broni_status_arr[5]='Бронь Усадьбы';
	$broni_status_arr[6]='Участок подрядчика';		

	$broni_status_arr[7]='Скоро в продаже';		
}
elseif(  $_SESSION['sh_login'] == 'em_nsv' )
{
	$broni_status_arr[4]='Забронирован';
	$broni_status_arr[6]='Участок подрядчика';
	$broni_status_arr[5]='Забронирован застройщиком';				  
}



if(!$data['street']){$data['street']='Звездная';} 
if(!$data['htype']){$data['htype']='Family 180g';} 
 


//$buf_area100 = $data['area']/100;
//$calc_price_area = $buf_area100*600000;
//$data['price'] = $calc_price_area ;

//if(!$data['price']){$data['price'] = '16000000';} 
  
					
$price =  number_format($data['price'], 0, '.', ' ');
					
$stat = $data['status'];
?>

<style>
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
<br/><br/>

<link href="https://fonts.cdnfonts.com/css/montserrat" rel="stylesheet">
<style>
@import url('https://fonts.cdnfonts.com/css/montserrat');
</style>
<div class="container-fluid">
 
  <div class="row">
	<div class="col-md-6 col-xs-6" style="text-align:left;  ">	
	
	 
		<img src="/logo.svg" width="100%" style="max-width:100%" />
		<br/><br/><br/> 
		<div style = "line-height: 27px; font-size:16px;  font-weight: 600;  font-family: 'Montserrat', sans-serif;">
		Номер участка: <b><?=$data['num'];?> </b><br/>
		Площадь участка: <b><?=$data['area'];?> м<sup>2</sup> </b><br/>
		
		 Кадастровый номер - <b><?=$data['kadastrnum']?></b><br/>
		 
		 <?
		 
		 /* Стоимость лота: <b><?=$price;?> &#x20bd;</b><br/> */
		 ?>
		 <br/> 
		 <?=$this->act__calcbuilding($data['lp_id']);?>
		 
		 <br/> 
		</div>
		</div>	 
    <div class="col-md-6 col-xs-6" style="text-align:left; font-size:24px;">
    
	<?
	if( ($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'goodzem')   )
	{
	?>
	<form action="iframe_router.php?ctr=landplots&act=order&map_id=<?=$_GET['map_id'];?>&polygon_id=<?=$_GET['polygon_id'];?>&lp_id=<?=$_GET['lp_id'];?>" method="post" enctype="multipart/form-data" method="post">
	
	
		<div class="row">
		 
		
		<div class="col-md-12 col-xs-12" style="text-align:left; font-size:24px;">	
			
			 <?
			 /* 
				if( $_SESSION['sh_login'] == 'admin' )
				{
					$filed->text('street','Улица',$data['street']);
				}
				else
				{
					?>Улица - <b><?=$data['street']?></b><br/><?
				}
				*/
			 ?>	
			 
			  
			  <?
			 /*
				if( $_SESSION['sh_login'] == 'admin' )
				{
				 
					$filed->select('raion', 'Район', $GLOBALS['gl_raion'],   $data['raion'], $style = 'text-transform:none; height:auto;');  
				}
				else
				{
					?>Район - <b><?=$GLOBALS['gl_raion'][$data['raion']]?></b><br/><?
				}
				*/
			 ?>	
			 

			<?
			 // 
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'goodzem'  )
				{
					$filed->text('num','Номер участка',$data['num']);
				}
				else
				{
					?>Номер - <b><?=$data['num']?></b><br/><?
				}
			 ?>	
			 
			 
			 
			 	<?
			 // 
				if( $_SESSION['sh_login'] == 'admin'   )
				{
					$filed->text('kadastrnum','Кадастровый номер участка',$data['kadastrnum']);
				}
				else
				{
					?>Кадастровый номер - <b><?=$data['kadastrnum']?></b><br/><?
				}
			 ?>	
			 
			 
			 <?
				/*
				if( $_SESSION['sh_login'] == 'admin' )
				{
					$filed->select('project_id', 'Тип дома (проект)', $GLOBALS['gl_projects'],   $data['project_id'], $style = 'text-transform:none; height:auto;');  
				}
				else
				{
					?>Тип дома - <b><?=$GLOBALS['gl_projects'][$data['project_id']]?></b> <br/><?
				}
				*/
			 ?>	
			   
			  
			  
			  
			 <?
				// Площадь
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'goodzem')
				{
					$filed->text_float('area','Площадь (м<sup>2</sup>)',$data['area'],'','','0.01','width:200px; display:inline-block;');
				}
				else
				{
					?>Площадь участка - <b><?=$data['area']?></b> м<sup>2</sup><br/><?
				}
			 ?>	
			 
			 
			 
			 
				<? 
				// Стоимость сотки
				if( $_SESSION['sh_login'] == 'admin'   )
				{
					$filed->text('price_area','Стоимость сотки (цена будет пересчитана)',$data['price_area'],'0','','1','width:200px; display:inline-block;');
				}
	 
				
				// Цена
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'goodzem' )
				{
					$filed->text('price','Цена',$data['price'],'0','','1','width:200px; display:inline-block;');
				}
				else
				{
					// Для не админа цена только для свободных
					if(!$data['status'] || $data['status'] == 2)
					{
						?>
 

			<?=$this->act__calcbuilding($data['lp_id']);?>


			Стоимость лота - <b><?=$price;?></b> <br/><?
					}
				}
				
			 ?>
			   
			  <?
			 
				if( $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem' || ( $_SESSION['sh_login'] == 'em_nsv' && $data['status'] == 2 || $data['status']==5 || $data['status']==6 || !$data['status'] ) )
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
	elseif($_SESSION['sh_login'] && $_SESSION['sh_login'] != 'admin' &&    $_SESSION['sh_login'] != 'goodzem' && $_SESSION['sh_login'] !='em_nsv'  ) // Админ агентства или агент
	{
		
		
		//print_r();
		?>
		 
        <div style="font-size:14px;">
        <? 
 
if( $stat=="2" || !$stat   ) 
{
	?>  
	
	<b style="font-size:12px; color:#ff0000;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</b>
	
	<br><br>
	
              
					  <form action="iframe_router.php?ctr=landplots&act=order&map_id=<?=$_GET['map_id'];?>&polygon_id=<?=$_GET['polygon_id'];?>&lp_id=<?=$_GET['lp_id'];?>" method="post" enctype="multipart/form-data" method="post">
	
                        <h2 style="font-size:16px;">Данные покупателя</h2> Скан паспорта страницы с фото:
                        <input type="file" name="passport_scan" accept="image/*;capture=camera">
                        </br/>
                        <br/> Скан паспорта страницы с пропиской:
                        <input type="file" name="passport_scan2" accept="image/*;capture=camera">
                        <br/> Форма №2 бронь:
                        <input type="file" name="anket" accept="image/*;capture=camera">
                        <br/>
                        <br/>
                        

                        <input type="checkbox" id="checkbox" name="checkbox" style="width:auto;" onchange="document.getElementById('submit').disabled = !this.checked;">
                        <span style="font-size:20px;">Подтверждаю согласие с <a target="_blanc"  style="font-size:20px;" href="http://em-nsk.ru/sahmatka/reglament.php">регламентом </a></span>
                        <br/>
                        <br/>

                        <input type="submit" id="submit" disabled="disabled" value="ЗАБРОНИРОВАТЬ" class="stat-top-btn btn " style="width:100%; max-width:100%; font-size:24px;   margin-left:0;" />
                      </form>
                      <?
}
		?>
		</div>
        <?
	}
	
	else // Публичная форма заявки
	{
		?>
        <div style="font-size:14px;">
        <? 
 
			if( $stat=="2" || !$stat   ) 
			{
				?> 
				  <form action="iframe_router.php?ctr=landplots&act=order&polygon_id=<?=$_GET['polygon_id'];?>" method="post" enctype="multipart/form-data" method="post">
	
	
			<div class="row">
			
			
				<div class="col-md-6 col-xs-6" style="text-align:left; font-size:24px; text-align:center;">	
					<img src="/sahmatka/hrender/1.png" width="90%" style="max-width:100%" />
				</div>	 
				
			
				<div class="col-md-6 col-xs-12" style="text-align:left; font-size:18px; line-height: 2em;">	
					 Улица  - <b><?=$data['street']?></b> 
					<br/>Площадь участка - <b><?=$data['area']?></b> м<sup>2</sup>
					<br/>Тип дома - <b><?=$data['htype']?></b> 
					<br/>Цена - <b><?=$data['price']?></b> 
					<br/>Кадастровый номер - <b><?=$data['kadastrnum']?></b>
					<hr/>
					<br/><?=$filed->text('phone','ФИО','');?>
					
					 <?=$filed->text('phone','Телефон','');?>
					 
					<input type="submit" id="submit"   value="Отправить заявку"  style="  margin-left:0;" />
				</div>	
			
			 


			 

				
			</div>

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