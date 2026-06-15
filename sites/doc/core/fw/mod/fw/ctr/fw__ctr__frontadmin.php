<?
class ctr__frontadmin extends ctr__
{
	var $ctr = 'frontadmin';
	
	
	function __construct()
	{
		$this->getnav();
		fw::add_mod_css('fw','/css/frontadminpanel.css'); // Стили добавляем в шаблон
		fw::add_mod_js('fw','/js/frontadminpanel.js'); // Скрипты добавляем в шаблон
		
		
		$md['fw']['caption'] = 'Текущая страница';
		$md['fw']['dir']['ftree']['caption'] = 'Файлы';
		$md['fw']['dir']['tplfragments']['caption'] = 'Фрагменты шаблона';
		
		
		$md['seo']['caption'] = 'SEO';
		$md['seo']['dir']['main']['caption'] = 'Раздел 1';
		$md['seo']['dir']['main1']['caption'] = 'Раздел 2';
		 
		$this->mod_description = $md;
	}
	// инициализация меню
	function getnav()
	{
		 
		$this->adm_add_nav('right','fw','ftree','Текст ссылки','xxx2','','<a href="">123123</a> <a href="">123123</a>');
		$this->adm_add_nav('right','fw','tplfragments','Текст ссылки','xxx2','','<a href="">123123</a> <a href="">123123</a>');
		
		
		
		$this->adm_add_nav('right','seo','main','Текст ссылки','xxx2','','<a href="">123123</a> <a href="">123123</a> <a href="">123123</a>');
		$this->adm_add_nav('right','seo','main','Текст ссылки','xxx2','','<a href="">123123</a> <a href="">123123</a> <a href="">123123</a>');
		$this->adm_add_nav('right','seo','main1','Текст ссылки','xxx2','','<a href="">123123</a> <a href="">123123</a> <a href="">123123</a>');
	}
	

	/*
	 в папке модуля
	.admin.php
	в нем класс с именем модуль__admin extends mod_admin_com{

	 
 
	$fw_adm_nav
	nav[left|tight|top|bottom][mod]
	[icon]
	[action]
	[anchor]
	[text]
 

*/


	// ДОбавление элемента навигации
	//add_nav(куда,анкор,action,иконка); добавить кнопку навигации
	function adm_add_nav($target,$mod,$dir='main',$anchor,$act,$icon='',$content='')
	{
		global $fw_adm_nav; // 
		
		$elm = array();
		$mod = $mod;
		$elm['anchor']=$anchor;
		$elm['link']=$this->adm_link_action($act);
		$elm['ctr']=$this->ctr;
		$elm['act']=$act;
		$elm['anchor']=$anchor;
		$elm['icon']=$icon;
		$elm['content']=$content;
 
		$fw_adm_nav[$target][$mod][$dir][] = $elm;
	}
	
	// ссылка на экшен
	function adm_link_action($act)
	{
		return '/ajax_router.php?ctr='.$this->ctr.'&act='.$act;
	}
	
	
	
	 
	
	function act__xxx()
	{
		print '123123213';
	}
	
	
	// ВЫВОД ПАНЕЛИ НАВИГАЦИИ 
	function get_panel_dir($target,$mod='fw',$dir='main' )
	{
		global $fw_adm_nav;
		?>
			<ul class="ul_fw_frontadmin_panel_<?=$target?> ulfwfaf__<?=$target?>__<?=$mod?>__<?=$dir?>">
			<?
			foreach( $fw_adm_nav[$target][$mod][$dir] as $k=>$v )
			{
				//print_r($v);
				if($v['icon']){$img='<img src="'.$v['icon'].'" />';}
				?>
				<li>
					<a href="<?=$v['link']?>">
						<?=$img?>
						<span><?=$v['anchor']?></span>
					</a>
					<div class="fw_adm_item_content">
						<?=$v['content']?>
					<div>
				</li>
				<?
			}
			?>
			</ul>
		  
		<?
	}
	
	
	

	
	function get_panel_target($target='bottom')
	{
		global $fw_adm_nav;
		 
		$dir='main';
		?>
		<div class="fw_frontadmin_panel fw_frontadmin_panel_<?=$target?>">
		<div class="fw_panel_label fw_panel_label_<?=$target?>">ADMIN</div>
		<div class="fw_panel_content fw_panel_ndisp">
		<?
		foreach($fw_adm_nav[$target] as $k=>$v) // Цикл по модулям панели
		{
			?>
			<div class="fwfafcontent_mod fwfafcontent_mod_active fwfaf__<?=$target?>__<?=$k?>">
			<div class="fwt"><?=$this->mod_description[$k]['caption']?></div>
			<?
			foreach($v as $k1=>$v1) // Цикл по разделам модуля
			{
				?><div class="fwfafcontent_mod_dir  fwfaf__<?=$target?>__<?=$mod?>__<?=$dir?>">
				
				<div class="fwt"><?=$this->mod_description[$k]['dir'][$k1]['caption']?></div>
				<?
				$this->get_panel_dir($target,$k ,$k1);
				?></div><?
			}
			?>
			</div>
			<?
		}
			
		?>
		</div>
		</div>
		<?
	}
	
	
	
	
	 
	 
	
	/*
	puth /панель/модуль/далее разделы
	*/
	static function add_panel_item($puth='/',$caption,$content,$ctr='',$act='')
	{
		global $fw_frontap;
 
		$puth_arr = explode( '/' , $puth );
		//print_r($puth_arr);
		// Массив не пустых значений
		foreach( $puth_arr as $k => $v ){	if( $v ){ $puth_arr_val[] = $v; } }
		$puth = implode('/',$puth_arr_val);
		$level = count($puth_arr_val);
		
		// РОдительский puth
		$puth_arr_parant = $puth_arr_val;
		unset($puth_arr_parant[count($puth_arr_parant)-1]);
		$parent = implode('/',$puth_arr_parant);
		 
		/* 
		print '<h2>'.$puth.'</h2>';
		print $parent;
		print '<pre>';
		print_r($puth_arr_parant );
		print '</pre>';
		*/
		$item = array();
		$item['target'] = $puth_arr_val[0];
		$item['mod'] = $puth_arr_val[1];
		$item['this'] = $puth_arr_val[count($puth_arr_val)-1];
		$item['this_puth'] = $puth;
		$item['parent'] = $parent;
		$item['level']=$level;
		$item['caption'] = $caption;
		$item['content'] = $content;
		  
		$fw_frontap[ $parent ][] = $item; 
	}
	
	// Рекурсивный метод вывода дерева
	static function get_panel_fragment($puth) 
	{
		global $fw_frontap;
		$first = current($fw_frontap[$puth]);
		 
		 $level = $first['level']-1;
		print '<ul class="frontap_ul frontap_ul_level_'.$level.'">';
		
		if($level=='3'){$class='frontap_title_tab';}
		else{$class='frontap_title';}
		
		$i=0;
		foreach( $fw_frontap[$puth] as $k => $v )
		{
			if($i==0){$active = 'frontap_active';}
			else{$active = 'frontap_noactive';}
			print '<li class="frontap_li">';
			print '<div class="'.$class.'">'.$v['caption'].'</div>';
			print '<div class="frontap_content '.$active.'" class="fw_ajc" data-ctr="xxx" data-act="index"">';
				print $v['this_puth'];
				if( $fw_frontap[$v['this_puth']] )
				{
					self::get_panel_fragment( $v['this_puth'] );
				}
			print '<div class="frontap_tabcontent"></div>';
			print '</div>';
			print '</li>';
			
			$i++;
		}
		print '</ul>';
	}
	
	
	
	static function get_panel($puth='/')
	{
		
		global $fw_frontap;
		?>
		<style>
		
		#fw_frontap_right 
		{
			display:block;
		}
		#fw_frontap_right .frontap_ul{list-style-type: none; margin:0; padding:0; display:block;}
		
	

		#fw_frontap_right .frontap_ul_level_1  /* mod ul */
		{
			display:block;
		}
		
		#fw_frontap_right .frontap_ul_level_1 > .frontap_li > .frontap_title/* Заголовок модуля */
		{
			padding: 13px;
			color: #888;
			font-size: 14px;
			cursor: pointer;
			background: #25252b;
			border-bottom: solid 1px #000;
	
			border-left: solid 5px red;
			padding-left: 8px;
			color: #FFF;
		}
		
		
		
		#fw_frontap_right .frontap_ul_level_2 > .frontap_li > .frontap_title/* Заголовок раздела */
		{
			  
			margin: 3px;
			display: flex;
			padding: 7px;
			background: #000;
			color: #3068df;
			border-radius: 7px;
			font-weight: 100;
			font-size: 14px;
		}		  
		
		#fw_frontap_right .frontap_ul_level_3 
		{
			margin:0;
			padding:0;
		}
		
		
		
		
		.frontap_title_tab{
			display:inline;
			background:#212127;
			padding:3px; 
			color:#999;
			text-decoration:underline;
		}
		
		
		
		#fw_frontap_right .frontap_ul_level_3 > .frontap_li  /* ПОДРАЗДЕЛ */
		{
			 
			display: inline;
			 
			background: #000;
			color: #3068df;
			font-weight: 100;
			font-size: 14px;
		}	
		
		#fw_frontap_right .frontap_ul_level_3 > .frontap_li > .frontap_title /* ПОДРАЗДЕЛ */
		{
		 display:inline;
		}
		#fw_frontap_right .frontap_ul_level_3 > .frontap_li > .frontap_content /* ПОДРАЗДЕЛ */
		{
			display:none;
		}
		
		
		
		

		#fw_frontap_right   .frontap_content /* Контент раздела */
		{
			padding: 7px;
			margin: 3px;
			font-size: 14px;
			border-radius: 7px;
			background: #212127;
			color: #E9E;
			cursor:pointer;
		}	

		.frontap_content{transition: all 0.2s linear;}
		      
	
		/* Скрываем блоки  */
		#fw_frontap_right .frontap_active
		{
			overflow: visible;
			height:auto;
			padding: 3px;
			margin: 7px;
		}
	
		
		#fw_frontap_right .frontap_noactive 
		{ 
			overflow:hidden;
			height:0;
			padding: 0;
			margin: 0
		}
		</style>
		
		<?
		self::add_panel_item('right/fw','Текущая страница',''); // 
 	
	    self::add_panel_item('right/fw/template_fragments','Фрагменты шаблона','');
		self::add_panel_item('right/fw/page_filleds','Переменные страницы','');
		
		
		self::add_panel_item('right/fw/2','Пункт 2','123!!');
		self::add_panel_item('right/fw/2/1','Пункт 2.1','111111111111111111');
		self::add_panel_item('right/fw/2/2','Пункт 2.2','124444443');
		self::add_panel_item('right/fw/tpl_fragment','ФРагменты шаблона','333333');
		self::add_panel_item('right/seo','ФРагменты шаблона','333333');
	
		//print '<pre>';
		//print_r($fw_frontap); 
		//print '</pre>';
		
		$target = 'right';
		?>
		<div class="fw_frontadmin_panel fw_frontadmin_panel_<?=$target?>">
		<div class="fw_panel_label fw_panel_label_<?=$target?>">ADMIN</div>
		<div class="fw_panel_content fw_panel_disp">
		<?
		print '<div id="fw_frontap_right">';
		self::get_panel_fragment('right');
		print '</div>';
		?>
		</div>
		</div>
		<?
	}
	
	
	
}

?>