<?
/*
SELECT 
IFNULL( (select status  from broni as b where b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num ORDER by b.date DESC LIMIT 1 ) ,  2)  as status2x,
IFNULL( MONTH( broni.date ),  1000) as MONTH,
count(*) as c,  
 
homes.home_id,
apartaments.apartment_num,
IFNULL(broni.status, 2) AS statusx 
 
FROM `apartaments` 
LEFT JOIN broni on broni.apartments_num = apartaments.apartment_num AND broni.home_id = apartaments.home_id
LEFT JOIN homes on homes.home_id = apartaments.home_id 
WHERE 1=1 
AND homes.home_id > 0  
AND homes.title> 0 
 AND homes.show=1
AND homes.home_id=40
AND 
(
 broni.broni_id = (select broni_id from broni as b where b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num ORDER by b.date DESC LIMIT 1 )
OR
 broni.broni_id IS NULL
)

GROUP BY   status2x  , MONTH ORDER BY  status2x  , MONTH 


*/
function bytes2readable(int $divisible, int $divisor = 1024, int $precision = 2): string {
    if ($divisible < 0) {
        return (string) $divisible;
    }

    $units = [ 'Б', 'КБ', 'МБ', 'ГБ', 'ТБ', 'ПБ', 'ЭБ' ];

    $iteration = 0;
    if ($divisible >= $divisor) {
        do {
            $divisible  /= $divisor;
            $iteration++;
        } while (floor($divisible) >= $divisor);
    }

    return sprintf('%s %s', round($divisible, $precision), $units[ $iteration ]);
}


function mem_usage()
{
print 'Выделенно памяти '.ini_get('memory_limit');
print '<br/>';
print 'Пиковое использование '.bytes2readable(memory_get_peak_usage(true));
print '<br/>';
	
print 'Текущее состояние '.bytes2readable(memory_get_usage());
print '<br/>';
}
		
		

class ctr__metrika
{
	
	function __construct()
	{
		global $mysql;
		
		$graph_colors=array();
		$graph_colors[]='#fc4526';
		$graph_colors[]='#9b40ab';
		$graph_colors[]='#62ace6';
		$graph_colors[]='#fdcc35';
		$graph_colors[]='#8bc554';
		$graph_colors[]='rgb(47, 85, 172)';
		$graph_colors[]='rgb(215, 145, 89)';
		$graph_colors[]='rgb(252, 69, 38)';
		
		$graph_colors[]='#990000';
		$graph_colors[]='#990066';
		$graph_colors[]='#993399';
		$graph_colors[]='#006699';
		$graph_colors[]='#6633FF';
		$graph_colors[]='#3399CC';
		$graph_colors[]='#00CCCC';
		$graph_colors[]='#336666';
		$graph_colors[]='#006633';
		$graph_colors[]='#00CC33';
		
		$graph_colors[]='#9933CC';
		$graph_colors[]='#FF0099';
		$graph_colors[]='#FF6633';
		$graph_colors[]='#FF6600';
		$graph_colors[]='#996666';
	 
		
		
		
		$this->graph_colors = $graph_colors;
		
		
		/*
		$ind=0;
		$this->fi[$ind]['title'] = 'Год';
		$this->fi[$ind]['filed'] = 'YEAR(broni.date) as year';
		$this->fi[$ind]['filed_g'] = 'year';
		$this->fi[$ind]['filed_w'] = 'YEAR(broni.date)';
		$this->fi[$ind]['filed_v'] = 'year';
		$this->fi[$ind]['group'] = 1;
		
		
		*/
		
		
		/*
		Получать бронь на дату! как в статистике
		+ НУЛЕВЫЕ СТАТУСЫ - ПОЧИНИТЬ В БАЗЕ UPDATE `apartaments` SET `status` = '2' WHERE `status` = '0' or `status` IS NULL
		 
		+ итоговая строка верхняя сумма цифр корневого раздела от этого итога берется процент?
		+ ПОЛУЧИТЬ status_broni_id в Аппартаментс (последнюю бронь!) если этого поля нету!  !!!!!!!!!!!!!!!! Получим сезонность продаж!!!!
		
		+ вычислять среднее значение по графику - сколько в среднем продаж в месяц по годам?
		+ за какое время в среднем продается квартира? нужен момент публикации!(первая бронь по дому - момент публикации дома дополнить)
		+ Время продажи дома 
		
		+ график свободных квартир в доме количества не по актуальным а по броням!!!!!!!!
		+ В таблице также по броням?
		
		по этажу? есть ли зависимость от этажа на скорость продажи?
		
		по площади есть ли зависимость от скорости продажи ?
		по материалу? стен 
		сравнить сорость продажи 
		
		
		График перехват level + значение!
		аякс
		по ним берем временной интервал группировки (месяц/день/неделя)
		Получаем поле и значение + родительские тоже! 
		по ним формируем селеки серч к броням и выводим по датам!
		
		
		+ ФИЛЬТРЫ СДЕЛАТЬ! 
		- СТАТУС
		- ДОМА
		- Агентство+ ПОльзователь (Связные списки)
		+ диаппазон дат
		
		
		*/
		 
		$ind=0;
		$this->fi[$ind]['title'] = 'Статус';
		$this->fi[$ind]['filed'] = '(select status  from broni as b where b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num ORDER by b.date DESC LIMIT 1 ) as status'; 
		$this->fi[$ind]['filed_g'] = 'status';
		$this->fi[$ind]['filed_w'] = '(select status  from broni as b where b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num ORDER by b.date DESC LIMIT 1 )';
		$this->fi[$ind]['filed_v'] = 'status';
		$this->fi[$ind]['group'] = 1;
		 
		/*

		

		
		
		
			$ind=2;
		$this->fi[$ind]['title'] = 'Месяц';
		$this->fi[$ind]['filed'] = 'MONTH(broni.date) as month';
		$this->fi[$ind]['filed_g'] = 'month';
		$this->fi[$ind]['filed_w'] = 'MONTH(broni.date)';
		$this->fi[$ind]['filed_v'] = 'month';
		$this->fi[$ind]['group'] = 1;
		*/
		$ind=1;
		$this->fi[$ind]['title'] = 'Год';
		$this->fi[$ind]['filed'] = 'YEAR(broni.date) as year';
		$this->fi[$ind]['filed_g'] = 'year';
		$this->fi[$ind]['filed_w'] = 'YEAR(broni.date)';
		$this->fi[$ind]['filed_v'] = 'year';
		$this->fi[$ind]['group'] = 1;
		
	
		
		$ind=2;
		$this->fi[$ind]['title'] = 'Комнат';
		$this->fi[$ind]['filed'] = 'CAST(apartaments.rooms AS UNSIGNED) as rooms_int'; 
		$this->fi[$ind]['filed_g'] = 'rooms_int';
		$this->fi[$ind]['filed_w'] = 'CAST(apartaments.rooms AS UNSIGNED)';
		$this->fi[$ind]['filed_v'] = 'rooms_int';
		$this->fi[$ind]['group'] = 1;
		 
		
				$ind=3;
		$this->fi[$ind]['title'] = 'Объект';
		$this->fi[$ind]['filed'] = 'apartaments.home_id';
		$this->fi[$ind]['filed_g'] = 'apartaments.home_id';
		$this->fi[$ind]['filed_w'] = 'apartaments.home_id';
		$this->fi[$ind]['filed_v'] = 'home_id';
		$this->fi[$ind]['group'] =1;
		
		$this->fic[0]='ac';
		
		$this-> homes_arr = $mysql -> get_arr(' SELECT * FROM `homes` ORDER BY `title` ',1,'home_id');
		 
		 
	}
	// Вывод дочерних строк родителя
	function disp_tabletree_row($data_row,$parent='',$header=false,$dop='')
	{
		$id_attr='';
		$parent_attr='';
		// Есть ид записи (не заголовок и не первая строка)
		if( $data_row['i'] && $data_row['i']!=0 )
		{
			$id_attr = ' treegrid-'.$data_row['i'].' ';
			if($parent || $parent=='0'){$parent_attr = ' treegrid-parent-'.$parent.'" ';}
		}
		elseif($data_row['i']=='0') // Первая строка 
		{
			$id_attr = ' treegrid-'.$data_row['i'].' ';
		}
		
		 
		?><tr class="<?=$id_attr?> <?=$parent_attr?>"><?
		foreach($data_row as $kk=>$vv)
		{
			?><td><?
			print $dop;
			print ' - ';
			print $vv;
			?></td><?
		}
		?></tr><? 
		 
	}
	 
	 
	
	 function act__xxx()
	 {
		global $mysql;
		print  $sql = $this->sql_query(1);
		
		$data=$mysql->get_arr($sql);
		print '<pre>';
		print_r($data);
		print '</pre>';
	 }
	
	
 

	
	
	function sql_query( $max_group='' , $search_arr='' ,$nogroup=false)
	{
		// Получаем данные для таблицы (последние столбы с максимальной группировкой)
		$grc = 0;
		foreach($this->fi as $k=>$v) // Считаем поля группировки 
		{
			if($v['group'] && $v['filed_g'])
			{
				$grc++;
			}
		}
		$this->grc=$grc;
		
		if($max_group<$grc){$grc = $max_group;} 
		
		$sql = 'SELECT ';

		if(!$nogroup)
		{
			foreach($this->fi as $k=>$v)
			{
				$sql.=$v['filed'].', ';
			}
			$sql.= ' count(*) as c ';
			$sql2.=' count(apartaments.apartament_id) as ac, ('. $this->sql_query($max_group,$search_arr ,'c') .') as c /* Всего общего */
			(count(apartaments.apartament_id)*100/('. $this->sql_query($max_group,$search_arr ,'c') .')) as acper /*Процент от общего */
			';
		}
		elseif($nogroup=='c')
		{
			$sql.= ' count(*) as c ';
		}
		$sql.=' 
		FROM `broni` 
		LEFT JOIN apartaments on apartaments.status_broni_id = broni.broni_id  
		LEFT JOIN homes on homes.home_id = apartaments.home_id 
		
		WHERE 1=1 
		AND homes.home_id>0
		AND homes.title>0
		AND broni.date = (select max(date) from broni as b where b.home_id = broni.home_id  AND b.apartments_num = broni.apartments_num) 	
	 
		';
//	
		foreach($search_arr as $k=>$v)
		{ 
			$sql.=' AND  '.$k.' ="'.$v.'" '; 
		}
		
		
		
		if(!$nogroup)
		{
		$sql.='
		GROUP BY 
		';
		
		// Группировка
		$i=0;
		foreach($this->fi as $k=>$v)
		{
			if($v['filed_g'] && $v['group'])
			{
			if($max_group && $i>=$max_group){break;}
			$i++;
			$sql.=$v['filed_g'];
			if($i<$grc ){$sql.=',';}
			}
		}

		$sql.=' ORDER BY count(apartaments.apartament_id) DESC';
		}
		/*
		$sql.=' ORDER BY ';
		$i=0;
		foreach($this->fi as $k=>$v)
		{
			if($v['filed_g'] && $v['group'] )
			{
			if($max_group && $i>=$max_group){break;}
			$i++;
			$sql.=$v['filed_g'].' DESC ';
			if($i<$grc ){$sql.=' ,';}
			}
		}
		*/
		return $sql;
	}
	
	 
	 
	 function tcol__status($data,$nohtml=false)
	 {
		 global $status_arr;
		 global $status_color_arr;
		 //return '<b style="background-color:'.$status_color_arr[$data['status']].'">'.$status_arr[$data['status']].'</b>';
		 if(!$nohtml)
		 {
			return '<b style="background-color:'.$status_color_arr[$data].'">'.$status_arr[$data].'</b>';
		 }
		 else
		 {
			 return $status_arr[$data];
		 }
	 }
	 
	 
	 function tcol__home_id($data,$nohtml=false)
	 {
		// return '<b>'.$this-> homes_arr[$data['home_id']]['title'].'</b>';
		
		
		 if(!$nohtml)
		 {
			return '<b>'.$this-> homes_arr[$data]['title'].'</b>';
		 }
		 else
		 {
			 return $this-> homes_arr[$data]['title'];
		 }
		 
	 }
	 
 
	 
	 
	 
	 
	 // получение графика по патчу
	function act__ajax__chartdata()
	{
		global $mysql;
		  
		$puth_ = urldecode(trim($_GET['id']));
		if(!$puth_ || $puth_ == '#')
		{
		$puth_ = '/';
		}
		// Парсинг  puth в search array
		$search_array = array();
		// print $puth_;
		$buf = explode('/',$puth_);
		foreach($buf as $k=>$v)
		{
			if($v)
			{
			// print_r($this->fi[$buf2[0]]); // массив параметров поля lavel
			 $buf2 = explode(':',$v);
			 $search_array[$this->fi[$buf2[0]]['filed_w']] =  $buf2[1];
			 $value = '';
			 
			$method = 'tcol__'.$this->fi[$buf2[0]]['filed_v'];
			if(method_exists($this,$method))
			{
				$value = $this->$method($buf2[1],true);
			}
			else{$value = $buf2[1];}
			
			
			 $label .= $this->fi[$buf2[0]]['title'].':'.$value .' / ';
			}
		} 
		// search_array
		// print '<pre>';
		//  print_r( $search_array);
		//  print_r($this->fi[$buf2[0]]);
		// print '</pre>';
		$level = count($search_array)+1;
		  
		  
		$sql=' SELECT  MONTH(broni.date) as month , YEAR(broni.date) as YEAR,
		apartaments.status, 
		apartaments.home_id, 
		CAST(apartaments.rooms AS UNSIGNED) as rooms_int, 
		count(apartaments.apartament_id) as ac 
		FROM `apartaments` 
		LEFT JOIN homes on homes.home_id = apartaments.home_id 
		LEFT JOIN broni on apartaments.status_broni_id= broni.broni_id

		WHERE 1=1 
		AND apartaments.status_broni_id >0
		  ';
		
		foreach($search_array as $k=>$v)
		{ 
			$sql.=' AND '.$k.' ="'.$v.'" '; 
		}
		
		$sql.='
		GROUP BY
		MONTH(broni.date)
		order by  MONTH(broni.date)';
		
		$data = $mysql->get_arr($sql);
	
		$gradation_filed = 'month';
		
		
		/*
		{
			label: "Vendas",
			backgroundColor: 'rgba(99, 255, 132, 0.2)',
			borderColor: 'rgba(99, 255, 132, 1)',
			borderWidth: 2,
			data: [10, 20, 30, 40, 50, 60, 70],
		}
		*/
				
		$js_data= array();
		$js_data['label'] = $label ;
		$color = $this->graph_colors[rand(0,count($this->graph_colors))]; // случайный цвет из ассортимента
		
		$js_data['backgroundColor'] = $color;
		$js_data['borderColor'] = $color;
		$js_data['borderWidth'] = 2;
		   
		//  $js_data['fill'] = true; // Заливка
		    
		$graph_points = array();
		foreach($data as $k=>$v)
		{
			$graph_points[]=$v['ac'];
		}
	 
		$js_data['data'] = $graph_points;
		 
		//print '<pre>';
		print json_encode($js_data);
		//print '</pre>'; 
	}
	
	
	
	
	
	
	
	
	
	
	function act__jsoonajaxftree()
	{
		$puth_ = urldecode(trim($_GET['id']));
		if(!$puth_ || $puth_ == '#')
		{
		$puth_ = '/';
		}
		//print $puth_;
		global $mysql;	
	  
		// Парсинг  puth в search array
		$search_array = array();
		 
		$buf = explode('/',$puth_);
		foreach($buf as $k=>$v)
		{
			if($v)
			{
			 $buf2 = explode(':',$v);
			 $search_array[$this->fi[$buf2[0]]['filed_w']] =  $buf2[1];
			}
		} 
		// search_array
		// print '<pre>';
		 // print_r( $search_array);
		// print '</pre>';
		$level = count($search_array)+1;
		  
		$sql = $this->sql_query($level,$search_array);
		 
		$data = $mysql->get_arr($sql); 
		 
		foreach( $data as $k1=>$v1)
		{
			$puth='';
			$parent='';
			
			$cl = 0;
			foreach($this->fi as $k=>$v) 
			{
				if( $v['group'] && $v['filed_g']  )
				{
					$cl++;
					
					 if($k <= $level-2){
						$parent.='/'.$k.':'. $v1[$v['filed_v']] ;
					}
					
					if($k <= $level-1)
					{
						$puth.='/'.$k.':'. $v1[$v['filed_v']] ;
					}
					
					 			
				}
				 
			}
			
			
			// Методы обработки  значений столбцов 
			$value = $this->fi[$level-1]['title'].' '.$v1[$this->fi[$level-1]['filed_v']];
			$method = 'tcol__'.$this->fi[$level-1]['filed_v'];
			if(method_exists($this,$method))
			{
				$value = $this->$method($v1[$this->fi[$level-1]['filed_v']]);
			}
			///
			
			$content = $level.':'.$cl.' - '.$this->fi[$level-1]['title'].' '.$v1[$this->fi[$level-1]['filed_v']].' <span class="fwcoltree"><span>'.$v1['ac'].'</span>';
			$content =  $value;
			
			
			// данные таблицы
			$datar = array();
			$datar['acper']=round($v1['acper'], 2); 
			$datar['c']=$v1['c'];
			$datar['ac']=$v1['ac'];
			
			$datar['bcpercent']=$v1['ac']; // Получить значение bc для верхней категории
			
			
			
			$v1 = array();
			$v1['id'] = $puth;
			if(!$parent){$parent='#';}
			$v1['parent'] = $parent;
			$v1['text'] = '<span class="fwe_jstree_fn">'.$content.'</span>';
			
			$v1['type'] = 'file';
			$v1['icon'] = 'file file-htaccess';
	 
			 
			 if($level!=$cl)
			 {
				  $v1['children'] = true;
			 }
			 
			$v1['data'] = $datar;
			
  
			$a_attr=array();
			$a_attr['dpath'] = $puth;
			$a_attr['dtype'] = 'file';
			$v1['a_attr'] = $a_attr;
			 
			$state=array();
			$state['selected'] = false;
			$state['opened'] = false;
			$v1['state'] = $state;
		  
			$data[$k1]=$v1;
		}
	  
			//print '<pre>';
			print json_encode($data);
			//print '</pre>';
	}
	
	
	
	
	
	
	
	
	
	
	function act__index()
	{
		
		global $mysql;	
		global $t;
		t('Старт индекс');
		 ?>
		  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/themes/default-dark/style.min.css" />
		  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
		  <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/jstree.min.js"></script>
 
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.2.1/chart.min.js"></script>
			 
			<script src="/sahmatka/js/jstreetable.js"></script>
 <style>
.jstree-anchor{display:inline-block; position:relative;
     }
	
	.fwcoltree{width: 100%;
    display: inline-block;
    text-align: right;
	position:absolute: right:0;
	}
	.fwcoltree span{display:inline-block; width:100px; position:absolute: right:0; }
</style>
		  <?
 
		$t['h1'] = 'Статистика';
  
	 
		?>
		
		
		
 <div>
  <canvas id="myChartx" height="50px" aria-label="Hello ARIA World" role="img"></canvas>
</div>


		<a href="#" id="unselecttreeall">Снять выделение</a>
		
		<div id="filed_group_tree"   style=" width:100%;"></div>
	 
		<script>
		$(document).ready(function() {
			
			
		$('#filed_group_tree').jstree({
			'core': {
			  'data': {
				"url": "<?=$GLOBALS['config']['domains']['em']?>/sites/em/sahmatka/ajax_router.php?ctr=metrika&act=jsoonajaxftree",
				'data': function(node) {
				  return {
					'id': node.id,
					 
				  }; 
				},
				"check_callback": true,
				"dataType": "json" // needed only if you do not supply JSON headers
			}
			}, 
				             
			table: {
				stateful:true,
					 fixedHeader:true,
					 columnWidth:300 ,
					 resizable:true,
					 contextmenu:false,
					 headerContextMenu:false,
					columns: [
						{ header: "Группировка данных" , width: 350,headerClass: "jtreeHeader",wideCellClass: "jtreeCell",},
						{width: 100, value: "ac", header: "Количество"},
						{width: 100, value: "acper", header: "%"},
					 
					],
					
				},
				
				"checkbox": {
				three_state : false, // to avoid that fact that checking a node also check others
				whole_node : false,  // to avoid checking the box just clicking the node 
				tie_selection : false // for checking without selecting and selecting without checking
           },
		   
				 
			  "plugins" : [
			    "state", "table","checkbox", "wholerow"
			  ]
			}) ;
			
	 
			// Перехват события отметки чекбокса с поддержкой STATE!		
			$('#filed_group_tree').on("check_node.jstree uncheck_node.jstree", function(e, data) 
			{
				 if(data.node.state.checked){add_chartline(data.node.id);}
				 else{dell_chartline(data.node.id )}
			});
			
			
			var datasets_index  = []; // ИНдекс графиков
			//datasets_index.push(1);datasets_index.push(1); // ЗАТЫЧКА НАЧАЛЬНОЕ КОЛИЧЕСТВО ГРАФИКОВ ОПРЕДЕЛЯТЬ В  add_chartline и добавлять если нет индекса
			// Добавить линию в график по path
			function add_chartline(puth)
			{
				 //alert('Добавить линию'+puth);
				/*
				Получить все нажатые чекбоксы?
				*/
				 var newDataset = {
					label: "Vendas",
					backgroundColor: 'rgba(99, 255, 132, 0.2)',
					borderColor: 'rgba(99, 255, 132, 1)',
					borderWidth: 2,
					data: [10, 20, 30, 40, 50, 60, 70],
				};
				
				//<?=$GLOBALS['config']['domains']['em']?>/sites/em/sahmatka/ajax_router.php?ctr=metrika&act=ajax__chartdata
				$.ajax({
					 method: 'get', 
					dataType: "json",
					url: '<?=$GLOBALS['config']['domains']['em']?>/sites/em/sahmatka/ajax_router.php?ctr=metrika&act=ajax__chartdata',
					data: {id: puth},
					success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
						//alert(data);  
						chart.data.datasets.push(data); 
						// Добавляем в индекс существующие графики с значением puth = 10000 (чтобы удаление работало корректно)
						datasets_index.push(puth); // Добавляем гарфик в индекс
						//console.log(datasets_index);
						chart.update();
					}
				});
				
				
			}
			// Удалить линию в графике по puth?
			function dell_chartline(puth)
			{
				// Индекс графика находим
				var gri = datasets_index.indexOf(puth);
				//alert(gri);

				//console.log(  chart.data.datasets);
				datasets_index.splice(gri,1);// Удаляем индекс
				chart.data.datasets.splice(gri,1); // Удаляем график
				
				
				
				chart.update();
				
				 //alert('Удалить линию'+puth);
			}


			
			
			/*
			// Получить все чекбоксы для сохранения?
			$('.btnGetCheckedItem').click(function(){
				var checked_ids = []; 
				var selectedNodes = $('#filed_group_tree').jstree("get_selected", true);
				$.each(selectedNodes, function() {
					checked_ids.push(this.id);
				});
				// You can assign checked_ids to a hidden field of a form before submitting to the server
				$('#idshow').text(checked_ids);
			});
			*/
			
			
			// Снять выделение и свернуть все ветки
			$('#unselecttreeall').click(function(){
				$("#filed_group_tree").jstree(true).uncheck_all(); 
				$('#filed_group_tree').jstree(true).deselect_all();
				datasets_index.splice(0);// Удаляем индекс
					chart.data.datasets.splice(0); // Удаляем график
					chart.update();
				 return false;
			});
			
			
			
			
	//*
//Убрать из scripts.js график	?
			
  //https://www.chartjs.org/docs/latest/samples/scales/time-combo.html
			
 
			
  const ctx = document.getElementById('myChartx');
 
	var chartconfig = {
    type: 'line',  
    data: {
      labels: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Окрябрь','Ноябрь','Декабрь'],
      datasets: [],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      },
	  /* Подсказки для всех линий*/
	   interaction: 
	   {
      intersect: false,
      mode: 'index',
    },
	
 
	 hover: {
      mode: 'nearest',
      intersect: true,
    },
	   plugins: {
            legend: {
                display: true,
				position:'bottom',
                labels: {
                },
				colors: {
				  enabled: false
				},
				
			 
            }
        }
    }
  };
  
  var chart =  window.myChart = new Chart(ctx, chartconfig);
   
    
chart.hoveringLegendIndex = -1
chart.canvas.addEventListener('mousemove', function(e) {
  if (chart.hoveringLegendIndex >= 0) {
    if (e.layerX < chart.legend.left || chart.legend.right < e.layerX
      || e.layerY < chart.legend.top || chart.legend.bottom < e.layerY
    ) {
      chart.hoveringLegendIndex = -1
      for (var i = 0; i < chart.data.datasets.length; i++) {
        var dataset = chart.data.datasets[i]
        dataset.borderColor = dataset.accentColor
      }
      chart.update()
    }
  }
})
	 
 
  //*/
  
  
  
  /*
  
  function addData(chart, label, data) {
    chart.data.labels.push(label);
    chart.data.datasets.forEach((dataset) => {
        dataset.data.push(data);
    });
    chart.update();
}

function removeData(chart) {
    chart.data.labels.pop();
    chart.data.datasets.forEach((dataset) => {
        dataset.data.pop();
    });
    chart.update();
}


 
btnNextStep.onclick = () => {
  console.log(1);
  chart.data.datasets[0].data.push(40);
  chart.data.labels.push(60);
  chart.update();
};
*/





		});
		</script>
		
		<?
		
		print_r($GLOBALS['mysql_log']);
	}
	
	 
 
	
	
}