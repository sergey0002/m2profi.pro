<?
session_start();
error_reporting(1);


$file = file('orderlogx.csv');
$file = array_reverse($file);


$display_table=0;

$fileds['datetime'] = 'Дата заявки';
$fileds['fw_thisurl'] = 'Страница';
$fileds['fw_ym'] = 'Цель метрики';
$fileds['name'] = 'Имя';
$fileds['phone'] = 'Телефон';
$fileds['time'] = 'Удобное время звонка';
$fileds['email'] = 'E-Mail';
 



		//	email	time	Вы_хотите	Кол-во_спален	Кол-во_этажей	Конструктив	Вариант_отделки
/*
$fileds['datetime'] = 'Дата заявки';
$fileds['form'] = 'Форма';
 $fileds['date'] = 'Даты проживания';


$fileds['guests'] = 'гостей';
$fileds['pol1'] = 'Пол';
$fileds['guests_vz'] = 'Взрослых';
$fileds['guests_ch'] = 'Детей';
$fileds['numbers'] = 'Номеров';
$fileds['ch_age'] = 'Возраст детей';
		  
$fileds_variant['kvartal']['1'] ='Green Lounge Dacha';
$fileds_variant['kvartal']['2'] ='GL Residence';
*/	  
		  
		  
		  
if( $_GET['out'] )
{
	 unset( $_SESSION['zfw_login'] );
}

if( $_POST['login'] && $_POST['password'] )
{
	if( $_POST['login']=='admin' && $_POST['password'] == 'zdctvjue' )
	{
		$_SESSION['zfw_login'] = 1;
	}
	else{
		print 'Не верный пароль';
	}
}
?>


<html><head><meta charset="utf-8" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

<link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
<style>
body {font-family:Roboto;}body{background-color:#333; color:#EEE; font-size:10px;}h1{font-size:14px;}h1{font-size:12px;}h1,h2{ display: inline; line-height: 1.5em;}
 
    
    .faq_s {
      border: solid 2px #1e1e1e;
      border-bottom:solid 1px #666;
    }
    
    .faq_s_title {
		padding:5px;
		color: #EEE;
		font-size: 20px;
		position: relative;
		cursor: pointer;
		background-color:#1e1e1e;
    }
    
    .faq_s_plus {
		display: inline-block;
		background-color: #EEE;
		border-radius: 2px;
		width: 25px;
		height: 25px;
		text-align: center;
		line-height: 25px;
		text-decoration: none;
		color: #000;
		position: absolute;
		right: 5px;
    }
    
    .faq_s_text {
		display: none;
		max-width: 100%;
		overflow: scroll;
    }
	
	.fw_table td{   padding: 6px; background-color:#FFF; color:#000;   border: solid 1px #EEE;  border-bottom:none;   font-size:12px;}
	.fw_table th{ border:solid 1px #555; padding: 6px; font-size:12px;}
	
	.topmenu{ border-bottom: 2px solid #414c57; background-color:#1e1e1e;}
	
	
	.topmenu div{
		color:#e1e1e1;  font-size:16px;  
	}
	
	.topmenu a,.topmenu span{
		color:#666; display:inline-block;  margin:20px; text-align: right; text-decoration:none;
	}
	</style>
	
	



</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  
 
<body>
<?

if( !$_SESSION['zfw_login'] )
{
	?>
	<div class="container" style="text-align:center; margin-top:30px;">
	<div style="display:inline-block; max-width:500px;">
	<form method="post" action="">
	  <div class="mb-3">
		<label for="username" class="form-label">Логин</label>
		<input type="text" class="form-control" id="login" name="login" required>
	  </div>
	  <div class="mb-3">
		<label for="password" class="form-label">Пароль</label>
		<input type="password" class="form-control" id="password" name="password" required>
	  </div>
	  <button type="submit" class="btn btn-primary" style="background-color: #111; border: solid 1px #666;">Войти</button>
	</form>
	</div>
</body>
</html>
	<?
	exit();
}

?>
<div class="container-fluid topmenu" >
<div class="row">
	<div class="col-md-6" >
		<span>Заявки с сайта</span>
	</div>
	<div class="col-md-6" style="text-align:right;">
		<a href="?out=1">Выйти</a>
	</div>
</div>
</div>

 



<br/>
<?


foreach($file as $k=>$v)
{
	$str = unserialize(base64_decode($v));
 
 // Добавляем столбы которых нет с оригинальными именами перменных
 foreach($str as $kk=>$vv)
 {
	 if(!isset($fileds[$kk]))
	 {
		 $fileds[$kk] = $kk;
	 }
 }


	if($str['timestamp'])
	{
		$timestamp_date = $str['timestamp'];
		$yearmounth = date('m.Y',$timestamp_date);
		if($yearmounth)
		{
			$ym_arr_c[$yearmounth]++;
			$ym_arr[$yearmounth][] = $str;
		}
	}
}

unset($fileds['timestamp']);
unset($fileds['fw_fnamex']);
 

foreach($ym_arr_c as $k=>$v)
{
	?>
	<div class="container">
	<div class="faq_s">
	<div class="faq_s_title"><?=$k?> / Заявок  - <?=$v?> <a class="faq_s_plus">+</a></div>
	<div class="faq_s_text"> 
	
	
	
	<?
	if($display_table)
	{
	?>
		<table width="100%" border="1" class="fw_table">
		<tr>
			<th>№</th>
			<?
			foreach($fileds as $fk=>$fv)
			{
				print '<th>'.$fv.'</th>';
			}
			?>
		</tr>
		   <?
		   $i=0;
		   foreach($ym_arr[$k] as $k1=>$v1)
		   {
			   $i++;
			   ?>
				<tr>
					<td><?=$i?></td>
					 
					<?
					foreach($fileds as $fk=>$fv)
					{
						if( isset($fileds_variant[$fk][$v1][$fk]) )
						{
							print '<td>'.$fileds_variant[$fk][$v1][$fk].'</td>'; 
						}
						elseif( is_array($v1[$fk]) )
						{
							print '<td>';
							foreach($v1[$fk] as $fv_k => $fv_v)
							{
								print $fv_k.' - '.$fv_v.',';
							}
							print '</td>';
						}
						else
						{
							print '<td>'.$v1[$fk].'</td>';
						}
					}
					?>
					 
				</tr>
				<?
		   }
		   ?>
		</table>
		
		<?
		}
		else //НЕ ТАБЛИЧНЫЙ ВЫВОД
		{
			?>
			<table width="100%" border="1" class="fw_table">
			<tr>
			<th>№</th>
			</tr>
			
			<?
			   $i=0;
			   foreach($ym_arr[$k] as $k1=>$v1)
			   {
				   $i++;
				   ?>
					<tr>
					<td><?=count($ym_arr[$k])-($i-1)?></td>
					<td>
						<?
						foreach($fileds as $fk=>$fv)
						{
							if(!$v1[$fk]){continue;}
							print '<b>'.$fv.'</b>'.' - ';
							
							
							if( isset($fileds_variant[$fk][$v1][$fk]) )
							{
								print $fileds_variant[$fk][$v1][$fk]; 
							}
							elseif( is_array($v1[$fk]) )
							{
								print '';
								foreach($v1[$fk] as $fv_k => $fv_v)
								{
									print $fv_k.' - '.$fv_v.',';
								}
								print '';
							}
							else
							{
								print ''.$v1[$fk].'';
							}
							print '<br/>';
						}
						?>
					</td>
					</tr>
					<?
			   }
			   ?>
			</table>
			<?
			
		}
		?>
		
		
		</div>
	 </div>
	 </div>
	<?
	
}
?>

<script>


 




$('.faq_s_title').on('click', function(t) {	  
	$(this).next('.faq_s_text').slideToggle(200)
});
</script>
</body>