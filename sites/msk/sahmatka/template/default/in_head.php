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
	
		<a href="https://xn--80aci4cj6cf.xn--p1ai/" class="sidenav__backlink">Вернуться на сайт</a>
		
		<div class="sidenav-nav">
			<a href="https://xn--80aci4cj6cf.xn--p1ai/" class="sidenav__backlink sidenav__backlink_mob">Вернуться на сайт</a>
			<div style="font-size:7px;"><?=$_SERVER[SERVER_ADDR];?></div>
			<ul class="sidenav-menu">
			
			 
			
				<li><a href="ctrind.php?ctr=landplots&act=area" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Участки</a></li>
			
			 
				<?
				if( ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'admin_demo')  ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="ctrind.php?ctr=landplots&act=price" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Участки (прайс)</a></li>
				<?
				}
				?>
			 
				
				
				
				
				
				
				
				
				
				
				<?
				if($_SESSION['sh_login'] != 'keys1' && $_SESSION['sh_login'] != 'fd' &&  $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' && $_SESSION['sh_login'] != 'director' ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
				<li><a href="ctrind.php?ctr=landplots_broni"><i><img src="template/default/images/menu-icon-2.svg" alt=""></i>Брони</a></li>
				<?
				}
				?>
				
				<?
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'admin_demo' ||  $_SESSION['sh_login'] == 'director' ||  $_SESSION['sh_login'] == 'fd' ) // Администратор !!!!проверять отдел администраторы!
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
					 
						<li style="display:none;"><a href="user.php?action=stat_sale">Статистика продаж</a></li>
						<li style="display:none;"><a href="user.php?action=agency_stat">Статистика агентств</a></li>
						 
						
						<li><a href="/sahmatka/ctrind.php?ctr=landplots_stat">Статистика участков</a></li>

						<li style="display:none;"><a href="user.php?action=stat_salen2">Средняя стоимость м<sup>2</sup></a></li>
					</ul>
				</li>
				<? 
				}
				?> 
				 
				
				<?
				if( $_SESSION['sh_login'] != 'admin' && $_SESSION['adm_caption'] )
				{
					?>
					<li><a href="user.php?action=users"><i><img src="template/default/images/menu-icon-3.svg" alt=""></i>Пользователи</a></li>
					<?
				}
				?>
  
				<?
				if($_SESSION['sh_login'] == 'admin'  ||   $_SESSION['sh_login'] == 'admin_demo' || $_SESSION['sh_login'] == 'fd' ) // Администратор !!!!проверять отдел администраторы!
				{
					?>
					<li><a href="/sahmatka/ctrind.php?ctr=agency"><i><img src="template/default/images/menu-icon-5.svg" alt=""></i>Агентства</a></li>
					<?
				}
				?>
				 
				<?
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'admin_demo' || $_SESSION['sh_login'] == 'op15' || $_SESSION['sh_login'] == 'fd')
				{
				?>
				<li class="sidenav-dropmenu" style="display:none;">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['action']=='exc_zapis' || $_GET['action']=='zapis_editor'  )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Экскурсии</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="user.php?action=exc_zapis">Запись на экскурсии</a></li>
						
						
						<?
							if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'admin_demo' || $_SESSION['sh_login'] == 'op15' )
							{
							?>
							<li><a href="user.php?action=zapis_editor">Редактор расписания</a></li>
							<?
							}
							?>
					</ul>
				</li>
				<?
				}
				elseif( $_SESSION['agency_id'] == "92" && $_SESSION['sh_login'] != 'keys1' &&  $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' ) //  
				{
					?>
					<li style="display:none;"><a href="user.php?action=exc_zapis"><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Экскурсии</a></li>
					
					<?
				}
				
					 
					 
					 
					 
					 
					 
					 
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'op15'  || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'keys1' ||  $_SESSION['sh_login'] == 'keys2' || $_SESSION['sh_login'] == 'em_nsv')
				{ 
				?>
				<li class="sidenav-dropmenu" style="display:none;">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['action']=='zapis' || $_GET['action']=='zapis_editor_keys' || $_GET['ctr']=='zapiskeys' || $_GET['ctr']=='zapisx' || $_GET['ctr']=='zapis_stat' )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Выдача <br>ключей</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
					<li><a href="ctrind.php?ctr=zapiskeys">Запись на выдачу</a></li>
					<?			
					if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'op15'    )
					{ 
					//user.php?action=zapis_editor_keys
					?>
					<li><a href="ctrind.php?ctr=zapisx">Редактор расписания</a></li>
					<?
					}
					?>		
					<li><a href="ctrind.php?ctr=zapis_stat">Статистика</a></li>
					</ul>
				</li>
				<?
				}				
				elseif( $_SESSION['agency_id'] == "92") //  
				{
					?>
					<li style="display:none;"><a href="ctrind.php?ctr=zapiskeys"><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Выдача <br>ключей</a></li>
					<?
				}
				
				if($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'fd' ) //  
				{
					?>
					<li><a href="user.php?action=messages"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Заявки <br>с сайта</a></li>				
					<?
				}
				?>
				
				<?
				if($_SESSION['sh_login'] != 'admin'  && 1==2 ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="user.php?action=showroom"><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Шоурум</a></li>
					<li><a href="user.php?action=novoselam"><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Новоселам</a></li>
					<li><a href="user.php?action=contact"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Контакты</a></li>
				 
				<?
				}
				
			//	print_r($_SESSION );
				?>
				
				
				
			 
				
				
				
				
				<?
				if( $_SESSION['sh_login'] == 'admin'  )
				{
				?>
				<li class="sidenav-dropmenu" style="display:none;">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='homeseditor' || $_GET['ctr']=='landplots'  )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Настройки</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="ctrind.php?ctr=landplots&act=editor">Редактор карты</a></li>
						 
					</ul>
				</li>
				<?
				}
				?>
				
				
				
				
				
				 
				<li ><a href="user.php?action=docs" ><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы</a></li>
			</ul>
			
			
			
 <script>
 
 <?
 $time = time();
 if( ($_SESSION['formshow']-$time) > 86400 ){ $_SESSION['formshow']=0; }
 
 
 if($_SESSION['formshow']==0)
 {
 $_SESSION['formshow'] = time();
 ?>
$( document ).ready(function() 
{
	/*
	  		 $.magnificPopup.open({
			   closeMarkup:"<button title='%title%' type='button' class='mfp-close myDisplayOverride'>&#215;</button>",
			   mainClass: 'my-mfp-zoom-in',
			   fixedContentPos: true, 
			   disableOn:1,
			   modal: false,
			   removalDelay: 100,
			   items: {
				 src: 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/popup.php',
			   },
			   type: 'iframe', 
			   callbacks: {
					open: function(item) {
						$(this.container).find('.mfp-content').css('height', '200px');
					}
			   }
			});
	*/
	 
});
<?
 }
?>

 </script>
  
  
  
		</div>
	</div>
	
	
	

</div>

<style>
.mmenu li{display:inline; padding:10px;}
.iframe_r{position:static; z-index:100;  }
section{min-height:100vh;}
</style>


