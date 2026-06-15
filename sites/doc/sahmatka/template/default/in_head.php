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
				<a href="?exit=1" class="header-account__logout"></a><br/>
				
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
	
	 
		<div class="sidenav-nav">
			 
			 
			<ul class="sidenav-menu">
				<li><a href="ctrind.php?ctr=doc&act=index" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Документы</a></li>
			</ul>
			 
			 <ul class="sidenav-menu">
				<?
				if(  $_SESSION['sh_login'] == 'admin' ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="ctrind.php?ctr=dir" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Разделы документов</a></li>
			 
					<li><a href="ctrind.php?ctr=users" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Пользователи</a></li>
				    <li><a href="ctrind.php?ctr=users_group" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Группы</a></li>
		
					<li><a href="ctrind.php?ctr=permissions" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Права</a></li>
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


