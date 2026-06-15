<?




// $zapis_filed_valuses[имя] = 'значение'; // имя переменной
    add_css(' 
	input[type=checkbox]:checked + label {color:green; font-weight:bold; } 
	input[type=checkbox] + label {color:#EEE; font-weight:bold; }
 
    .tree span 
	{
      cursor: pointer;
    }
 
	.tree ul li{border-left: solid 1px #000; margin 10px; padding:10px;  border-bottom: solid 1px #000; margin 10px; padding:10px;}
	
	.setup_b-body{width:100%;}
	');
 
	ob_start();
	?>
		$(document).ready(function(){
 
		$('.addtime').click(function() {
			 var dnum = $(this).attr("dnum");
			$x =   $('<div><input type="time" class="field" name="tm_arr_act['+dnum+'][]" value="" /></div>').fadeIn('slow');
			//.appendTo('.time2s');
			$(this).parent().find('.times').append($x);
   
   
   
		 /*
		   var num = $("#last_fields").attr("num");
			$("#last_fields").before('<input type="text" name=name['+(parseInt(num)+1)+'] value="">Возможно описание здесь.');
			$("#last_fields").attr("num", (parseInt(num)+1))
		 */
 
		});

		$('.setup_b-body').css({'display':'none'});
		/* Споллеры настроек */
		$('.setup_b').click(function(){
		  $(this).next('.setup_b-body').slideToggle(200)
		});
	 
 
	});
	 
	<?
	$js = ob_get_clean();
	add_js($js);
	?>
	











<?
class zapis 
{
	 
	
	 

	### Генерация полей методы ##################
	
	function filed__home($g='')
	{	
		global $mysql;
		$q = 'SELECT  * FROM homes  WHERE homes.show="1" ';
		
		$arrw = $mysql->get_arr($q,1,'homes_id' );
		foreach($arrw as $k=>$v)
		{
			 $arr[$k] = $v['title'];
		}
		return $this->select_options($arr);	
	}
	
	
	function filed__section($g='')
	{	
		global $mysql;
		$q = 'SELECT * FROM apartaments  WHERE home_id="'.$g['home_id'].'" group by section_id ';
		
		$arrw = $mysql->get_arr($q,1,'section_id' );
		foreach($arrw as $k=>$v)
		{
			 $arr[$k] = $v['section_id'];
		}
		return $this->select_options($arr);	
	}
	
	
	function filed__appartament($g='')
	{	
		global $mysql;
		$q = 'SELECT * FROM apartaments  WHERE home_id="'.$g['home_id'].'" AND section_id = "'.$g['section_id'].'" ';
		
		$arrw = $mysql->get_arr($q,1,'apartment_num' );
		foreach($arrw as $k=>$v)
		{
			 $arr[$k] = $v['apartment_num'];
		}
		return $this->select_options($arr);	
	}
	
	
	
	
	
	function filed__date($g='')
	{	
	
	
	
	/*
	вывод даты
0. Сетка времени по таблице zapis_time
+полуаем все правила этой формы для filed_id  + filed_value (для номера квартиры к примеру или номера дома ) отсортированные по датам старта и датам конца

+ циклом проходим 
= формируем перечень дат от старта до конца + дня недели(если указан), если не указан для всех дней
= 
 

	1. выбираем количество записей на дату и значение переменных 
	select from zapis 
	
	+ на каждую переменную отдельный left_join zapis_fileds_values 

	*/
		 
	}
	
	function filed__time($g='')
	{	
		 
	}
	
	################################################
	
	
	
	
	 



	// Поля Option для ajax select полей формы
	function select_options($arr,$selected='')
	{
		foreach($arr as $k=>$v)
		{
		?>
		<option value="<?=$k?>"><?=$v?></option>
		<?
		}
	}
 
		
	
//получение массива иерархии полей
function get_zapis_fileds_arr()
{
		global $mysql;
		$q = 'SELECT  * FROM zapis_fileds order by level asc';
		$arrw = $mysql->get_arr($q,1,'filed_id' );
		return $arrw;	
}

//получение массива иерархии полей
function get_zapis_date_interval_arr()
{
		global $mysql;
		$q = 'SELECT  * FROM zapis_dinterval  ';
		$arrw = $mysql->get_arr($q,1,'zapis_dinterval_id' );
		return $arrw;	
}





//Вывод интервала редатора....
function day_time_filed($zapis_dinterval_id)
{
	
	
	$dn_arr[1]="ПН";
	$dn_arr[2]="ВТ";
	$dn_arr[3]="СР";
	$dn_arr[4]="ЧТ";
	$dn_arr[5]="ПТ";
	$dn_arr[6]="СБ";
	$dn_arr[7]="ВС";
 
	
	// Доступное время (из бавзы) на дни [день][номер] = время
	$tm_arrd[1][1]="10:00";
	$tm_arrd[1][2]="11:00";
	$tm_arrd[1][3]="12:00";
	$tm_arrd[1][4]="13:00";
	$tm_arrd[3][5]="14:00";
	$tm_arrd[2][6]="15:00";
	$tm_arrd[1][7]="16:00";
 
	// Автивное время (из бавзы) на дни [день][номер] = время
	$tm_arr_act[1][1]="10:00";
	$tm_arr_act[1][2]="11:00";
	$tm_arr_act[1][3]="12:00";
	$tm_arr_act[1][4]="13:00";
	$tm_arr_act[1][5]="14:00";
	$tm_arr_act[1][6]="15:00";
	$tm_arr_act[1][7]="16:00";
	
	
	?>
	 
	<span class="caret"> 
	
	<span>Даты: c <input name="interval[<?=$zapis_dinterval_id?>][date_start]" type="date" />   по  <input name="interval[<?=$zapis_dinterval_id?>][date_stop]" type="date" /></span></span> <span class="setup_b">Время</span><?
	
	?>
	<span class="setup_b-body" style="display:block;">
	<br/>
	<form method="post">
	<table border=1>
	 
	<tr>
	<?
	// шапка таблицы (дни недели все)
	foreach($dn_arr as $dn_arr_k=>$dn_arr_v)
	{
		print '<th width="14%">'.$dn_arr_v.'</th>';
	}
	?>
	</tr>
	<?
		
	 $zapis_dinterval_id=rand(0,1000);
			//  дни недели все 
			foreach($dn_arr as $dn_arr_k=>$dn_arr_v)
			{
				print '<td>';
				print '<div class="times">';
				// Выводим доступное время для этого дня
				if(isset($tm_arrd[$dn_arr_k]) && is_array($tm_arrd[$dn_arr_k]) )
				{
					foreach($tm_arrd[$dn_arr_k] as $tm_arrd_k=>$tm_arrd_v)
					{
						print ' <input name="interval['.$zapis_dinterval_id.']['.$dn_arr_k.'][]" type="checkbox" value="'.$tm_arrd_v.'" id="interval['.$zapis_dinterval_id.']['.$dn_arr_k.']['.$tm_arrd_v.']" ';
						if( is_array( $tm_arr_act[$dn_arr_k] ) && in_array($tm_arrd_v,$tm_arr_act[$dn_arr_k]) ){ print ' checked="checked" ';}
						
						print ' />';
						print ' <label for="interval['.$zapis_dinterval_id.']['.$dn_arr_k.']['.$tm_arrd_v.']">'.$tm_arrd_v.'</label><br/>';
					}
				}
				print '</div>';
				print '<span class="addtime" dnum="'.$dn_arr_k.'"> + </span>';
				print '</td>';
			}
		
	 
	?>
	</table>
	<?
	print '<pre>';
	//print_r($_POST);
	print '</pre>';
	?>
	<input type="submit" /> <input tupe="clean" value="Выходной день" />
	</form>
	</span>
 
	<?
	//$('#check1').prop('checked', false);
}




// вывод интервалов для переменных
function trree_level_print_values($arr,$v)
{
	/*
	=zapis_datetime
	zapis_time_id	int(11) Автоматическое приращение	
	acc_id	int(11)	аккаунт при многопользовательском
	filed_id	int(11)	ид поля 
	filed_value	varchar(255)	значение поля
	date_start	date	начальная дата
	date_end	date NULL	конечная (необяз)
	time	varchar(255) NULL	время (необяз)
	day_num	tinyint(4) NULL	день недели (необяз)
	zcount_dtime	tinyint(4) NULL	максимальное количество записей на это время дату
	interval_houers	tinyint(4) NULL	интервал до записи маинимальный
	*/
}

// Вывод уровней переменных
function tree_level_print($arr,$parent=0)
{
	?>
	<ul <? if ( $parent == 0 ){ print 'class="tree" id="tree" '; }else { print ' class="nested" ';}?> >
	<?

	foreach($arr as $k=>$v)
	{
		if( $v['parent_filed_id']==$parent )
		{
			?>
			<li> <span class="caret"><?=$v['filed_caption']?></span>  
			<?=$this->tree_level_print($arr,$v['filed_id'])?>
			</li>
			<?
		}
	}
	?></ul><?
	
}

	### Редактор выводим 
	function  datetime_form()
	{
		
		
		$arr_z = $this->get_zapis_date_interval_arr();
		print_R($arr_z);
		
		$arr = $this->get_zapis_fileds_arr();
		$this->tree_level_print( $arr );
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 
		 /*
		 
		 
		 $zapis_dinterval_id - ид интервала для уникализации форм
		 
		 
		 */
		?>
		
		
	<ul class="tree">
 
        <li> <span class="caret">Дом №451</span> 
          <ul class="nested">
            <li>  <?  $this->day_time_filed( $zapis_dinterval_id ); ?>	

				 <ul class="nested">
						<li><span class="caret">Секция №1</span>
							  <ul>
								<li>  <?  $this->day_time_filed( $zapis_dinterval_id ); ?>	</li> 	 
							  </ul>
						</li>
						<li><span class="caret">Секция №2</span>
							  <ul>
								<li>  <?  $this->day_time_filed( $zapis_dinterval_id ); ?>	</li>
								<li>  <?  $this->day_time_filed( $zapis_dinterval_id ); ?>	 
								
								
								
								<ul class="nested">
										<li><span class="caret">Квартира 200</span>
											  <ul>
												<li>  <?  $this->day_time_filed( $zapis_dinterval_id ); ?>	</li> 	 
											  </ul>
										</li>
										<li><span class="caret">Квартира 300</span>
											  <ul>
												<li>  <?  $this->day_time_filed( $zapis_dinterval_id); ?>	</li>
												<li>  <?  $this->day_time_filed( $zapis_dinterval_id ); ?>	 </li>
											  </ul>
										</li>
								  </ul>
				  
				  
				  </li>
							  </ul>
						</li>
				  </ul>
	  

			</li>
          </ul>
        </li>
 
      </ul>
     
  
 
 
 
 <style>
 /* Удалить пули по умолчанию */
ul  {
  list-style-type: none;
}

 

/* Стиль курсора/стрелки */
.caret {
  cursor: pointer;
  user-select: none; /* Запретить выделение текста */
  
	display: inline-block;
 
    background: #333;
    color: #FFF;
	padding:5px;
}

/* Создайте курсор/стрелку с юникодом, и стиль его */
.caret::before {
  content: "\25B6";
  color: #FFF;
  display: inline-block;
  margin-right: 6px;
}

/* Поверните значок курсора/стрелки при нажатии (с помощью JavaScript) */
.caret-down::before {
  transform: rotate(90deg);
}

/* Скрыть вложенный список */
.nested {
  display: none;
}

/* Показать вложенный список, когда пользователь нажимает на курсор стрелку (с JavaScript) */
.active {
  display: block;
}
 </style>
  
 
 
 <script>
 var toggler = document.getElementsByClassName("caret");
var i;

for (i = 0; i < toggler.length; i++) {
  toggler[i].addEventListener("click", function() {
    this.parentElement.querySelector(".nested").classList.toggle("active");
    this.classList.toggle("caret-down");
  });
}
 </script>
	 
		<?
		
		
	}
		
		
		
		
		
		
		
	/*
	1. Редактор форм  + обработка
	
	add__forms
	edit_forms
	get_forms
	*/
 
	/*
	1. Редактор переменных форма + обработка
	add_fileds
	edit_fileds
	get_fileds
	*/
	
	
	
	/*
	
	1. Редактор редактор форм записи десктоп (табы)
	add_fileds
	edit_fileds
	get_fileds
	*/
	
	
	/*
	вывод записей! 
	 - списком 
	 - календарем 
	*/
	
	
	/*
	универсальные вложенные аякс списки неограниченной вложенности ! 
	
	*/
	
 
	
	
	
}
?>
  