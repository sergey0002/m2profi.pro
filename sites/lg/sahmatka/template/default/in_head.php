<header class="header-lk">
	<div class="container">
		<div class="header-lk-main">
			<div id="btn-lk" class="menu-lk">
				<span></span>
				<span></span>
				<span></span>
			</div>
			<div class="header-account">
				<a href="#" class="header-account__profile"><?=$_SESSION['sh_login']?></a>
				<a href="/sahmatka/user.php?exit" class="header-account__logout"></a><br/>
				
			</div>
		</div>
	</div>
</header>


<div class="circle-blur circle-blur_inner-top-left" data-aos="fade-left" data-aos-delay="100"></div>
<div class="circle-blur circle-blur_inner-top-right" data-aos="fade-right" data-aos-delay="100" data-aos-offset="100">
</div>
<div class="circle-blur circle-blur_inner-center-right" data-aos="fade-left" data-aos-delay="100"></div>
 

<div class="overlay-page"></div>
<div class="sidenav">
	<div class="sidenav__close"></div>
	<div class="sidenav-wrap">
	
		<a style="display:none;" href="http://em-nsk.ru" class="sidenav__backlink">Вернуться на сайт</a>
		<?
		
		//fw_check_access(1,2);
		?>
		<div class="sidenav-nav">
			<a href="http://em-nsk.ru" class="sidenav__backlink sidenav__backlink_mob">Вернуться на сайт</a>
			<div style="font-size:7px;"><?=$_SERVER[SERVER_ADDR];?></div>
			<ul class="sidenav-menu">
				<li><a href="user.php?action=objects" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Квартиры</a></li>
				 
				 
				<?
				if( fw_check_access('parking') || 1==2 )
				{  
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='parking' || $_GET['ctr']=='parking_buildings' || $_GET['ctr']=='parking_areas'  || $_GET['ctr']=='parking_floors'  || $_GET['ctr']=='parking_spaces'  || $_GET['ctr']=='parking_broni' )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/carp.png" style="bottom: 7px;" alt=""></i>Парковки</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="ctrind.php?ctr=parking_floors&act=catalog">Каталог</a></li>
						
						<?
						if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin'   )
						{
							
						?>
						<li><a href="ctrind.php?ctr=parking_broni&act=index">Брони парковок</a></li>
						<li><a href="ctrind.php?ctr=parking_buildings">Здания</a></li>
						<li><a href="ctrind.php?ctr=parking_floors">Поэтажные планы</a></li>
						<li><a href="ctrind.php?ctr=parking_spaces">Парковочные места</a></li>
						<?
						}
						?>
					</ul>
				</li>
				<?
				}	
				?>	
				
				
				
				
				 
				
				
				
				
				
				
				<?
				if($_SESSION['sh_login'] != 'keys1' && $_SESSION['sh_login'] != 'fd' &&  $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' && $_SESSION['sh_login'] != 'director' ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
				<li><a href="user.php?action=show_broni"><i><img src="template/default/images/menu-icon-2.svg" alt=""></i>Брони</a></li>
				<?
				}
				?>
				
				<?
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'director' ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					
					<?
					// РАскрытие меню
					if( $_GET['action']=='stat_salen' || $_GET['action']=='stat_sale' || $_GET['action']=='agency_stat' || $_GET['action']=='object_stat' || $_GET['action']=='stat_salen2'   ||  $_GET['ctr']=='parking_stat')
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-3.svg" alt=""></i>Статистика</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="user.php?action=stat_salen">Подписанные договоры</a></li>
						<li><a href="user.php?action=stat_sale">Статистика продаж</a></li>
						<li><a href="user.php?action=agency_stat">Статистика агентств</a></li>
						<li><a href="user.php?action=object_stat">Статистика квартир</a></li>
 
						<li><a href="user.php?action=stat_salen2">Сводная статистика</a></li>
					</ul>
				</li>
				<? 
				}
				?> 
				 
				
				<?
				if( $_SESSION['sh_login'] != 'admin' &&  $_SESSION['adm_caption'] && $_SESSION['sh_login'] != 'demo_admin'  )
				{
					?>
					<li><a href="user.php?action=users"><i><img src="template/default/images/menu-icon-3.svg" alt=""></i>Пользователи</a></li>
					<?
				}
				?>
  
				<?
				if($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin' ) // Администратор !!!!проверять отдел администраторы!
				{
					?>
					<li><a href="/sahmatka/ctrind.php?ctr=agency"><i><img src="template/default/images/menu-icon-5.svg" alt=""></i>Агентства</a></li>
					<?
				}
				?>
				 
	 
				
				<?
				if($_SESSION['sh_login'] != 'admin'  ||   $_SESSION['sh_login'] == 'demo_admin') // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="user.php?action=showroom"><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Шоурум</a></li>
 
					<li><a href="user.php?action=contact"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Контакты</a></li>
				 
				<?
				}
				
			//	print_r($_SESSION );
				?>
				
				
				
			
				
				
				
				
				
				
				
				
				
				
				
				
				
				
						
						
				
				
				
				
				
				
				
				
				
				<?
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'demo_admin' )
				{
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='homeseditor' || $_GET['ctr']=='homes_kvartal'  )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Настройки</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="ctrind.php?ctr=homeseditor">Настройки объектов</a></li>
						 <li><a href="ctrind.php?ctr=homes_kvartal">Настройки ЖК</a></li>
					</ul>
				</li>
				
				
				 
				
				
				<?
				}
				
				
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'docm'    )
				{
					?>
					<li  ><a href="ctrind.php?ctr=agfiles&act=index"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы агентств</a></li>
					<?
				}
				
				
				if( $_SESSION['sh_login'] == 'admin'    )
				{
					?>
						<li><a href="user.php?action=docs"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы</a></li>
					<?
				}
				else
				{
					?> 
						<li style="display:non!e;"><a href="user.php?action=docs"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы</a></li>
					<?
				}
				
				
				?>
				
				
				
				
				
				 
			
				
				
			 
				
				
				
			</ul>
			
			
 
  
  
  
		</div>
	</div>
	
	
	

</div>

<style>
.mmenu li{display:inline; padding:10px;}
.iframe_r{position:static; z-index:100;  }
section{min-height:100vh;}
</style>

















 
