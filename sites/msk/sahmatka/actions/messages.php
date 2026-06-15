	 
	  <?
 
   $message = new messages();
 
	?>
	 
	 

<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Заявки с сайта</div>
		</div>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_order">
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-in stat-top-in_search">
						<input type="search" placeholder="Найти">
					</div>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-order table">
			 <?
	$message-> display_messages();
?> 
			</div>
		</div>
	</div>
</section>



 


	  