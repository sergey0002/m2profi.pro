<?
/*
Методы для загрузки ajax 
	- дом + подезд квартира методы для публичных форм и форм запси 
	
	+ ИСПОЛЬЗУЕТСЯ ДЛЯ ЗАПИСИ И ДЛЯ БРОНИРОВАНИЯ ДЛЯ ВСЕГО!!!
*/

class ctr__uniajax extends ctr__
{ 

 
	var $table = 'homes'; //Главная таблица
	var $key_filed = 'home_id'; // Ключевое поле главной таблицы

	function __construct()
	{
		$this->mysql=$GLOBALS['mysql'];
	}
	
 
	
 
	
	// view-source:https://xdemo.m2profi.pro/sahmatka/ajax_router.php?ctr=uniajax&act=selecthomes
	// аякс селект ДОМА
	function act__selecthomes( $data = ''  )
	{
		if(!$data){ $data = array(); }
		
		global $mysql;
		global $filed;;
		 
		?><option value="">Дом</option><? 
		$arr = $mysql->get_arr(' SELECT * FROM `homes` WHERE 1=1 AND (`show`="1") ');
		//  print_r($arr);
		
		foreach($arr as $k=>$v)
		{
			//if($_GET['home_id'] == $v['home_id'] ){ $sel=' selected="selected" '; }
			//else{ $sel=''; }
			print '<option value="'.$v['home_id'].'" '.$sel.'>'.$v['title'].'</option>';
		} 
		
	}
	
		
	
		
	// аякс селект ДОМА
	function act__selectsection( $data = ''  )
	{
		if(!$data){$data=array();}
		global $mysql;
		global $filed;;
		 
		?><option value="">Подъезд</option><? 
		$arr = $mysql->get_arr('SELECT * FROM homes, homes_sections WHERE `homes`.`homes_id` = `homes_sections`.`homes_id` AND `homes`.`home_id` = "'.$_REQUEST['home_id'].'" ');
		//  print_r($arr);
		foreach($arr as $k=>$v)
		{
			//if($_GET['home_id'] == $v['home_id'] ){ $sel=' selected="selected" '; }
			//else{ $sel=''; }
			print '<option value="'.$v['section_id'].'" '.$sel.'>Подъезд №'.$v['section_id'].'</option>';
		} 
	}
	
	
	
	
	
	
	//https://xdemo.m2profi.pro/sahmatka/ajax_router.php?ctr=uniajax&act=selectappat&home=28&section=1
	// аякс apppart Квартиры	
	function act__selectappat( $data = ''  )
	{
		if(!$data){$data=array();}
		global $mysql;
		global $filed;;
		 		
		#### ПОЛУЧАЕМ СПИСОК КВАРТИР В ДОМАХ НА КОТОРЫЕ ЕЩЕ НЕТ БРОНИ?!
		
		 // print $sql;
		$query = mysqli_query($connection, $sql); 

		$sql = 'SELECT * FROM `apartaments` WHERE home_id="'.$_REQUEST['home'].'" ';
		if($section)
		{
			$sql.=' AND `section_id`="'.$_REQUEST['section'].'" order by apartment_num';
		}
		
		?><option value=""><?= unit_label_cap('nom') ?></option><?
		$arr = $mysql->get_arr($sql);
		// print_r($arr);
		foreach($arr as $k=>$v)
		{
			//if($_GET['home_id'] == $v['home_id'] ){ $sel=' selected="selected" '; }
			//else{ $sel=''; }
			print '<option value="'.$v['apartment_num'].'">'.$v['apartment_num'].'</option>';
		} 
		
	}
	
	

	
	
	
	
	
	
	
	
	//https://xdemo.m2profi.pro/sahmatka/ajax_router.php?ctr=uniajax&act=managerop
	
	// Менеджеры отдела продаж	
	function act__managerop( $data = ''  )
	{
		if(!$data){$data=array();}
		global $mysql;
		global $filed;;
		
		#### ПОЛУЧАЕМ СПИСОК КВАРТИР В ДОМАХ НА КОТОРЫЕ ЕЩЕ НЕТ БРОНИ?!
		
		 // print $sql;
		$query = mysqli_query($connection, $sql); 

		$sql = ' SELECT * FROM `users` WHERE `agency_id` = "92" AND user_group = "agent" AND `password` NOT LIKE "%!%" ';
		if($section)
		{
			$sql.=' AND `section_id`="'.$_REQUEST['section'].'" order by apartment_num';
		}
		
		?><option value="">Менеджер</option><?
		$arr = $mysql->get_arr($sql);
		// print_r($arr);
		foreach($arr as $k=>$v)
		{
			print '<option value="'.$v['name'].'">'.$v['name'].'</option>';
		} 
		
	}
	
	
	
	
	
	
	
	
	
}