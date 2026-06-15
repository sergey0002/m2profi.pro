<?  
include('config.php');
 
 
 
 
if( $_GET['url'] )
{ 
	$_GET['url'] = 'Location: '.$GLOBALS['config']['domains']['doc'].'/'.$_GET['url'];
	header($_GET['url']);
	exit();
}


include('incudes_/header.php');



?>
<script>
$(document).ready(function(){
    window.location.href = '<?=$GLOBALS['config']['domains']['doc']?>/sahmatka/ctrind.php';
});
</script>

<?
header("Location: ".$GLOBALS['config']['domains']['doc']."/sahmatka/ctrind.php");
exit( );


 
 
 if(!$_GET['action']){$_GET['action']='index';}
 
 ob_start();
?>
<div class="container-fluid" >
<div class="row">
<div class="col-md-12"> 




<?
/*
при создании агенства поля для создания администратора!!!!! ИМЯ пароль E-mail логин + проверка занятых логинов
проверка корректности email и номеров телефонов
*/ 
	
	function object_menux($action='objects')
	{
		$sa = $GLOBALS['sa'];
		$h = $sa->get_homes_arr();
		
		//print_r($h);
		 ?>
	
				<?
				foreach($h as $k=>$v)
				{
					if(isset($_GET['sdan']))
					{
						if($_GET['sdan']){if($v['complite']=="0"){continue;}}
						else{if($v['complite']=="1"){continue;}}
					}
					
					if( $_GET['home'] == $v['home_id'] )
					{
						$class='  class="mdef mdefth " ';  
					}
					else
					{ 
						$class='  class="mdef mdef" '; 
						if($v['show']==2){ $class='   class="mdef mdefa"     ';  }// ТОлько админам
						elseif($v['show']==3){ $class='   class="mdef mdefaop"     ';  } // Админам и отделу продаж
			 
						else{$class='  class="mdef"   '; }
						//$class.='" ';
					}
					
					
					?>
					
					<li style="padding:0;"><a href="user.php?action=<?=$action?>&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <?=$class?> ><?=$v['title']?></a> </li>
					<?
				}
				?>  
				
				
			<?
		  
		 
	}
	
	
	
	
	
	function object_menux2($action='objects')
	{
		$sa = $GLOBALS['sa'];
		$h = $sa->get_homes_arr();
 
		//print_r($h);
		 ?>
	
				<?
				foreach($h as $k=>$v)
				{
					if( $_GET['home'] == $v['home_id'] )
					{
						$style=' style=" color:#ff0000; font-size: 18px;  font-weight:bold;  " '; 
					}
					else
					{ 
						$style=' style= " color:#000;    font-size: 18px;  font-weight:bold;   '; 
						if($v['show']==2){ $style.='   color:#FFA500;  ';  }// ТОлько админам
						elseif($v['show']==3){ $style.='   color:#999999;  ';  } // Админам и отделу продаж
						else{$style.='  color:#000;  '; }
						$style.='" ';
					}
					?>
					  <a href="user.php?action=<?=$action?>&home=<?=$v['home_id']?>" <?=$style?> class="stat-top-items__item"> <?=$v['title']?> </a>   
					<?
				}
				?>  
				
				
			<?
		  
		 
	}
	
	
	
	
	
	function object_menu($action='objects')
	{
			$h_arr = $GLOBALS['sa']->get_homes_arr();
		?>
<style>
@media screen and (min-width: 1000px) {
  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: 1flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
  .mobilenav{display:none;}
}
@media screen and (max-width: 1000px) {
  .mmenu{	display:none;		}
  .mobilenav{display:block; width:100%;}
  .nomobile{display:none;}
}
</style>
<script src="fancybox-3.0/dist/jquery.fancybox.min.js"></script>
<link rel="stylesheet" type="text/css" href="fancybox-3.0/dist/jquery.fancybox.min.css">

	 <script type="text/javascript">
	 
	 
	 
	 
	 
 
 
 
 
 if( window.innerWidth >= 1000 ){
     
	 
	 
	 $(document).ready(function() {
     $("a.iframe").fancybox({
            maxWidth    : 600,
            maxHeight   : 600,
            
            width       : '1000px',
            height      : '70%',
            closeClick  : true,
 	 
 	'scrolling' : 'yes',

 afterClose: function () { // USE THIS IT IS YOUR ANSWER THE KEY WORD IS "afterClose"
       // parent.location.reload(true);
    },
	beforeLoad: function() {   
            if (this.width = $(this.element).attr('width')) {this.maxWidth = $(this.element).attr('width');} else {this.width = '800';}
            if (this.height = $(this.element).attr('height')) {this.maxHeight = $(this.element).attr('height');}    else {this.height = '100%';}
            },
            type : 'iframe',
            openEffect : 'elastic',
            closeEffect : 'elastic',
            arrows : false,
            closeClick : false,
            scrolling: 'auto',
            fitToView    : true,
            autoSize: true,
            //width: 300, // Вот отсюда я вытащил размеры
            //height: 200, //

            margin      : [10, 10, 10, 10],
            padding:    [39, 10, 10, 10],
            helpers : {
                overlay : {closeClick : false},
                title    : {type : 'inside_top' },
            }
			
        });
 });
 
 
 
 } else {
      //не выполнять
 } 
 </script>
			 <div style="width:100%; margin-bottom:10px;">
				<a href="user.php?action=<?=$action?>&sdan=0" class="mdef <? if(!$_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 12px; font-weight:bold; ">СТРОЯЩИЕСЯ</a> 
				<a href="user.php?action=<?=$action?>&sdan=1" class="mdef <? if($_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">СДАННЫЕ</a>
			 </div>
			 
			 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header"    >
			
			 <br/>
			 <ul class="mmenu">
		<?
		
		object_menux($action);
		$class=' class="mdef" ';
		if( $_GET['action'] == 'objects2' )
		{
			$class='  class="mdef mdefth " ';  
		}
		?>
		
		<?
		if(($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'fd'  ) ||1==1)
		{
			if($_GET['sdan'])
			{
				
				foreach($GLOBALS['custom_apparts_all'] as $k=>$v)
				{
					?>
					<li style="padding:0; "><a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="mdef m2catalog_item_order iframe"><?=$v['homecaption']?></a></li>
					<?
				}
				 
			?>
			 <li style="display:none; padding:0; "><a href="user.php?action=objects2&sdan=<?=$_GET['sdan']?>" <?=$class?>>Другие</a></li>
			
			<?
			}
		}
		?>
			</ul>
		 
 
		 
		 	          <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select"  >
						<div class="objects-head-nav__select"  >
						 
							<select  name="url" onChange="document.autosubmit_select.submit();" style="width:100%;  text-align: left; border-radius:0; ">
							<?
								?><option>Выбрать дом</option><?
							foreach($h_arr as $k=>$v)
							{
								if(isset($_GET['sdan']))
								{
									if($_GET['sdan']){if($v['complite']=="0"){continue;}}
									else{if($v['complite']=="1"){continue;}}
								}
								?><option value="/sahmatka/user.php?action=objects&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <? if($v['home_id']==$_GET['home']){ print ' selected="selected" ';}?>><?=$v['long_title']?></option><?
							}
							?>
							<?
							if($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' )
							{
								if(isset($_GET['sdan']))
								{
								?>
							 
								<option value="/sahmatka/form_order_custom.php?custom_home_id=101&custom_appart_id=1">Свечникова, 4/1</option>
								<?
								}
							}
							?>
							</select>
							
						</div>
					 	</form>
 		
		</div>			
		
		<hr style="margin-top: 12px; " class="nomobile"/>
		
	
						
						
						
		<?
		
	}
		
		
		
?>


 




<?
#### ИНтерфейс подрядчиков (Цены на дома)
if($_SESSION['sh_login'] == 'uservip' || $_SESSION['sh_login'] == 'partner')
{
	

 if(!$_GET['action']){$_GET['action']='objectsp';}

	//object_menu();
	if($_GET['action']=='objectsp')
	{
 		 
		 if(!$_GET['home']){$_GET['home']='451';}
		 
	    	 $file = 'h_'.$_GET[home].'.php';
		 if(  file_exists(  $file))
		 {
			 include( $file);
		 }
	}
}

#### ИНТЕРФЕЙС АДМИНИСТРАТОРА
elseif($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'fd')
{
 
 
if($_GET['action']=='users')
{
	include('actions/admin_users.php');
}// пользователи
  
elseif($_GET['action']=='object_stat') // 
{
include('actions/obj_stat.php');
}
elseif($_GET['action']=='stat_sale')  
{
	include('actions/stat_sale.php');
}
elseif($_GET['action']=='stat_sale2')  
{
	include('actions/stat_sale2.php');
}
elseif($_GET['action']=='stat_salen') // 
{
	include('actions/stat_salen.php');
}
elseif($_GET['action']=='stat_salen2') // 
{
	include('actions/stat_salen2.php');
}
elseif($_GET['action']=='stat_salen4') // 
{
	include('actions/stat_salen4.php');
}

elseif($_GET['action']=='stat_salen3') // 
{
	include('actions/stat_salen3.php');
}

elseif($_GET['action']=='zapis') // 
{
	include('actions/stat_zapis.php');
}

elseif($_GET['action']=='catalog') // 
{
	include('actions/catalog.php');
}
elseif($_GET['action']=='exc_zapis') //  
{
	include('actions/stat_exc_zapis.php');
}
elseif($_GET['action']=='zapis_editor') // 
{
	include('actions/stat_zapis_editor.php');
}
elseif($_GET['action']=='zapis_editor_keys') // 
{
	include('actions/stat_zapis_editor_keys.php');
}
elseif($_GET['action']=='objects2') // 
{
	
	
	if($_SESSION['sh_login']=='fd')
	{
		?>
 
 
	<section class="section-stat">
	
	<div class="container">
	
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Сданные  </span></div>
			
		</div>
			 <?=object_menu();?>
			 <br/><br/>
		<div class="stat">

			<div class="stat-table stat-table-user stat-table_notpd table" style="padding-top:0;">
		 
		 
		 <?
		 include('actions/sdan.txt');
		 ?>
		 
		 
			</div>
		</div>
	</div>
</section>



	<?
	}
	else
		
		{
				?>
	
		
	<section class="section-stat">
	
	<div class="container">
	
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Сданные  </span></div>
			
		</div>
			 <?=object_menu();?>
			 <br/><br/>
		<div class="stat">

			<div class="stat-table stat-table-user stat-table_notpd table">
		 
		 
		 <?
		 include('actions/objects2_editor.php');
		 ?>
		 
		 
			</div>
		</div>
	</div>
</section>

<?
		}
	
	

	
	
}
elseif($_GET['action']=='objects3') //  
{
	include('actions/sdan.txt');
}
  
elseif($_GET['action']=='objects4') //  
{	
//object_menu();
	include('actions/objects3_editor.php');
}
  
elseif($_GET['action']=='broni') //  
{	 
	include('actions/broni.php');
}
elseif($_GET['action']=='show_broni') //  
{	 
	include('actions/show_broni.php');
}
elseif($_GET['action']=='broni_history') //  
{	 
	include('actions/broni_history.php');
}
 elseif($_GET['action']=='agency_stat') // 
 {
  include('actions/agency_stat.php');	 
 }
 
 elseif($_GET['action']=='partner') // 
 {
	 include('actions/partner.php');
 }
 elseif($_GET['action']=='agency') // 
 {
	 include('actions/admin_agency.php');
 }
 elseif($_GET['action']=='objects') // 
 {
	?>
	<script>window.location.href = "<?=$GLOBALS['config']['domains']['doc']?>/sahmatka/ctrind.php"</script>
	<?
 }
 elseif($_GET['action']=='docs') {	 include('actions/docs.php'); }
 elseif($_GET['action']=='messages') {	 include('actions/messages.php'); }
 
 
 if($_GET['action']=='showroom')  
{
	include('actions/showroom.php');
}
elseif($_GET['action']=='novoselam')  
{
	include('actions/novoselam.php');
}
elseif($_GET['action']=='contact') 
{
	include('actions/contact.php');
}

}




















 
#### ИНТЕРФЕЙС АДМИНИСТРАТОРА - АГЕНТСТВА!!!
if($_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'fd' && $_SESSION['adm_caption'])
{
  
  
if($_GET['action']=='showroom')  
{
	include('actions/showroom.php');
}
elseif($_GET['action']=='novoselam')  
{
	include('actions/novoselam.php');
}
elseif($_GET['action']=='contact') 
{
	include('actions/contact.php');
}




 
 if($_GET['action']=='objects2') // Агентства (администратор)
{
	
	
	?>
	<br><br>
	<style>
	/*
	.table table{border:1px solid;}
	.table table tr{border:1px solid;}
	.table table tr td{border:1px solid;}
	*/
	</style>
	<center>
	
	</center>
	
	
		
	<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Сданные  </span></div>
		</div>
		<div class="stat">
	 
			<div class="stat-table stat-table-user stat-table_notpd table">
	<?
	include('actions/sdan.txt');
	?>
			</div>
		</div>
	</div>
</section>


	<?
}
 
 
 
 if($_GET['action']=='users')
 {
	include('actions/agadmin_users.php');
	//include('actions/admin_users.php');

 }// пользователи
 elseif($_SESSION['sh_login'] != 'admin'  && $_SESSION['sh_login'] != 'fd' && $_GET['action']=='objects') // Обьекты (НЕ администратор)
 {
	?> <script>window.location.href = "<?=$GLOBALS['config']['domains']['doc']?>/sahmatka/ctrind.php"</script><?
 }
elseif($_GET['action']=='broni') // Агентства (администратор)
{
	 
	include('actions/broni.php');
}
elseif($_GET['action']=='show_broni') // Агентства (администратор)
{
	include('actions/show_broni.php');
}
elseif($_GET['action']=='docs') {	 include('actions/docs.php'); }
}
 
 
 
 
 
 
 
 
 
 
 
 

### ИНТЕРФЕЙС ПОЛЬЗОВАТЕЛЯ
elseif($_SESSION['sh_login'] && !$_SESSION['adm_caption'] && $_SESSION['sh_login']!='admin' && $_SESSION['sh_login'] != 'fd' &&  $_SESSION['sh_login']!='uservip' &&  $_SESSION['sh_login']!='partner' )
{
 
 
 
 
 
 
 
 
if($_GET['action']=='showroom')  
{
	include('actions/showroom.php');
}
elseif($_GET['action']=='novoselam')  
{
	include('actions/novoselam.php');
}
elseif($_GET['action']=='contact') 
{
	include('actions/contact.php');
}










// СТАТИСТИКА КВАРТИР ДСТУПНА ОТДЕЛУ ПРОДАЖ
if($_SESSION['agency_id'] == "92" && $_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'fd'  && $_SESSION['sh_login'] != 'director' )
{
	if($_GET['action']=='object_stat'  ) // Агентства (администратор)
	{
		include('actions/obj_stat.php');
	}
	if($_GET['action']=='exc_zapis'  ) // Агентства (администратор)
	{
		include('actions/stat_exc_zapis.php');
	}
	
	if($_GET['action']=='messages'  ) // Агентства (администратор)
	{
		include('actions/messages.php');
	}
	elseif($_GET['action']=='zapis') // 
	{
		include('actions/stat_zapis.php');
	}
	elseif($_GET['action']=='zapis_editor_keys' &&   $_SESSION['sh_login'] == 'op15') // 
	{
		include('actions/stat_zapis_editor_keys.php');
	}
		elseif($_GET['action']=='zapis_editor'  &&   $_SESSION['sh_login'] == 'op15') // 
	{
		include('actions/stat_zapis_editor.php');
	}
 
}
 

 
if($_GET['action']=='broni') // Агентства (администратор)
{
	include('actions/broni.php');
}
elseif($_GET['action']=='show_broni') // 
{
		include('actions/show_broni.php');
}

elseif($_GET['action']=='objects') // Агентства (администратор)
{
 ?>
	<script>window.location.href = "<?=$GLOBALS['config']['domains']['doc']?>/sahmatka/ctrind.php"</script>
	<?
}
 elseif($_GET['action']=='docs') {	 include('actions/docs.php'); }

 
}
?>
 
 
 
</div></div></div>


<style>
.mdef{ padding:5px; padding-left:13px; padding-right:13px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	


.objmenua .mdef{color:#000;  }
  .mdefa{color:#FFA500;} /* ТОлько админам */
.mdefaop{color:#999999;} /*  Админам и отделу продаж */


.mdefth{color:#FFF; background-color:#00CDAD;  }			 
.mdef:hover{color:#FFF; background-color:#00CDAD;}					
						
</style>
<?
 
include('incudes_/foother.php');
print ob_get_clean();
 
?>