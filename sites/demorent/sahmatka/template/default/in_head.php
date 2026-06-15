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
	
		 
		<?
		
		//fw_check_access(1,2);
		?>
		<div class="sidenav-nav">
			 
			<div style="font-size:7px;"><?=$_SERVER[SERVER_ADDR];?></div>
			<ul class="sidenav-menu">
		  
		  
		  
 
		
		  
		  
					
				<?
				if( $_SESSION['sh_login'] == 'admin'   || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  )
				{ 
				?>
				
				
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag"> <i><img src="template/default/images/menu-icon-5.svg" alt=""></i> Аренда</a></li>	
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag&sale=1"  >  <i><img src="template/default/images/menu-icon-5.svg" alt=""></i> Продажа</a></li> 			
					<li><a href="ctrind.php?ctr=renthomes"> <i><img src="template/default/images/menu-icon-5.svg" alt=""></i> Здания</a></li>
					<li><a href="ctrind.php?ctr=rentobjects"><i><img src="template/default/images/menu-icon-5.svg" alt=""></i> Помещения</a></li>
					<li><a href="ctrind.php?ctr=rentbroni&act=index"><i><img src="template/default/images/menu-icon-5.svg" alt=""></i> Брони</a></li>
				
			
 
				<?
				}
 
				else
				{
					?>
					<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='rentobjects' || $_GET['ctr']=='renthomes' || $_GET['ctr']=='rentbroni'   )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Коммерческие помещения  </a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
					
				 
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag">Аренда</a></li>	
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag&sale=1"  > Продажа</a></li> 		
				 
					 				
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
				 
			
					 
					 
					 
					 
					  
			//	print_r($_SESSION );
				?>
				
				
				
			
				
				
				
				
				
				
				
				
				
				
				
				
				
				
						
						
				
				
				
 
				
				  
				
				
				
				 
				<li style="display:nonse;"  ><a href="user.php?action=docs"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы</a></li>
				
				
				
			 
				
				
				
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


