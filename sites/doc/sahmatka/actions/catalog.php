<?	
 error_reporting(E_ALL);
ob_start();
?>
 	 
		
		<div id="zapisdata">
		 <?
		 /* ВЫВОД КАТАЛОГА */
		 ?>
		 </div>
		
		<div style="width:100%; max-width:100vw; text-align:center; padding:50px; display:none;" id="progressbar"  >
		<img src="loader.gif"  />
		</div>
 
		
 
<?
$content = ob_get_clean();
?>


 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css!" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js!"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.css!">

<!-- Latest compiled and minified JavaScript -->
<script src="https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.js!"></script>


<link rel="stylesheet" href="<?=$GLOBALS['config']['domains']['em']?>/wiget_catalog.css">
<script src="<?=$GLOBALS['config']['domains']['em']?>/wiget_catalog.js"></script>



	  <style>
/* Input field */
.select2-selection__rendered {  }
    
/* Around the search field */
.select2-search {  }
    
/* Search field */
.select2-search input {  }
    
/* Each result */
.select2-results {  color:#000;}
    
/* Higlighted (hover) result */
.select2-results__option--highlighted {  }
    
/* Selected option */
.select2-results__option[aria-selected=true] {  }
	  </style>
	  
	  
	  
<script>
$(document).ready(function() {
   // $('select').select2();
   //$('select').multipleSelect();
  
});
</script>



<section class="section-stat">
	<div class="container mobc" style=" min-height: 100vh;">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Каталог квартир</span></div>
		</div>
		  
		
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_key">
			
				<form method="get" action="user.php?action=zapis" id="filtrform" data-controller="catalog">
				<input type="hidden" name = "get_arr" value=" Кодированная гет строка "/>
				<div class="stat-top-filter">
				
					<div class=" stat-top-item  stat-top-select stat-top-item_house" style="display:none;">
						<select name="rooms_k " id="sel_rooms_k">
							<option value="">Комнат</option> 
						</select>
					</div>


					<div class=" stat-top-item  stat-top-select stat-top-item_house">
						<select name="rooms_min" id="sel_rooms_min">
							<option value="">Комнат от</option> 
						</select>
					</div>
					
					
					<div class=" stat-top-item  stat-top-select stat-top-item_house">
						<select name="rooms_max" id="sel_rooms_max">
							<option value="">Комнат до</option> 
						</select>
					</div>
					
					
					
					<div class=" stat-top-item  stat-top-select stat-top-item_house" style="display:none;">
						<select name="min_price" id="sel_min_price">
							<option value="">Цена от</option> 
						</select>
					</div>
					
					
					<div class=" stat-top-item  stat-top-select stat-top-item_house" style="display:none;">
						<select name="max_price" id="sel_max_price">
							<option value="">Цена до</option> 
						</select>
					</div>
				
					
					<div class=" stat-top-item  stat-top-select stat-top-item_house" style="display:none;">
						<select name="floor" id="sel_floor">
							<option value="">Этаж</option> 
						</select>
					</div>
						
					<div class=" stat-top-item  stat-top-select stat-top-item_house">
						<select name="home" id="sel_home">
							<option value="">Объекты</option> 
						</select>
					</div>
					   
				</div>
				
				<input type="hidden" name="action" value="catalog">
  
</form>



			<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table-user stat-table_notpd table">
			
			<?=$content?>
	 
			</div>
		</div>
	</div>
</section>











 



	



<?

/*
https://qna.habr.com/q/414317 - ОТЛИЧНЫЕ СЕЛЕКТЫ КОМБОБУКСЫ
 
 
 
		
		*/
	 