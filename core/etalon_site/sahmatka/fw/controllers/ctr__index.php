<?

class ctr__index
{
	
	
	
	function act__index()
	{
		
		print '<h2>Метод индекс</h2>';
	}
	
	
	function act__x()
	{
		global $mysql;
		global $tpl;
		
		print '<h2>Метод x</h2>';
		?>
		<style>
		.pages li{display:inline-block; padding:5px; margin:2px; border:solid 1px #000;}
		.pages li a{text-decoration:none; color:#000;}
	    .pages{ padding-left: 0; }
		
		</style>
		<?
		
		$q = 'select* from users ';
		$arr = $mysql->get_arr($q);
		$c = count($arr);
		$limit = $mysql->pages_limits($c,50,$_GET['page']);
		$arr = $mysql->get_arr($q.$limit);
 
 
		 
		
		// Показать таблицу
		$titles['login']='Логин';
		$mysql->display_table($arr,$titles,'1');
	 
		
		$mysql->pages_menu($c,50,$_GET['page']); 
		
		
		// Показать шаблон 
		//$mysql->display_tpl($arr,'index','tablerow');
	}
	
	
}