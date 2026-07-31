<?

class gantt2
{
	
	function __construct()
	{
		if( !$_GET['month'] )
		{
			$_GET['month'] = date('m');
		}
		
		
		$this->t_start = time(); // Стартовое время отрисовки
		
		
		if( $_GET['datediap'] ) 
		{
			$buf = explode( ' - ' , $_GET['datediap'] );
			$d_start = strtotime( $buf[0] );
			$d_stop = strtotime( $buf[1] );
			
			if( $d_start && $d_stop )
			{
				$this->t_start = $d_start - (86400*90); // 10 прошлых дней
				$this->t_end = $d_stop + (86400*90); // КОнечное +30 дней
			}
		}
		else
		{
			if($_GET['month'])
			{
				
				if($_GET['year']){$y=$_GET['year'];}
				else{$y = date('Y');}
				
				if(strlen($_GET['month'])<2){$_GET['month'] = '0'.$_GET['month'];}
				$start_date = '01.'.$_GET['month'].'.'.$y;
				$stop_date = date("t.m.Y", strtotime($start_date));
				$_GET['datediap'] = $start_date.' - '.$stop_date;
				
				$this->t_start = strtotime( $start_date ); 
				$this->t_end = strtotime( $stop_date );
			}
			else
			{
				$this->t_start = $this->t_start - (86400*90); // 10 прошлых дней
				$this->t_end = $this->t_start+(86400*180); // КОнечное +30 дней
			}
			
		}
		 
		
		$this->w[1]='ПН';
		$this->w[2]='ВТ';
		$this->w[3]='СР';
		$this->w[4]='ЧТ';
		$this->w[5]='ПТ';
		$this->w[6]='СБ';
		$this->w[0]='ВС';
		
		$this->fc_caption='';
		$this->key_filed = 'id'; // В дата ид номера (строки)
		
		$this -> step_size_sec = 86400;
		
		
		
		
		$this->time_intervals=array();
		$this->time_intervals['09:00']='1';
		$this->time_intervals['10:00']='1';
		$this->time_intervals['11:00']='1';
		$this->time_intervals['13:00']='1';
		$this->time_intervals['14:00']='1';
		$this->time_intervals['15:00']='1';
		$this->time_intervals['16:00']='1';
		$this->time_intervals['17:00']='1';
		
		
		// $this->time_intervals_titles['09:00']='09:00';
		 
		
		
		
		$this->time_interval_width=30;
		$this->time_intervals_c = count( $this->time_intervals );
		$this->day_width = ($this->time_intervals_c*$this->time_interval_width);
	}
	
	
	
	
	
	
	
	
	
	
	// Первый столбец гантта
	function firstcol($v)
	{
		?>
		 <?=$v['caption']?>
		<?
		$this->fcols[]=$v;
	}
	
	
	
 
	
	
	
	
	// СТРОКА ЗАГАЛОВОК ( ДАТЫ ) 
	function tr_head()
	{
		?>
			<tr>
			<th style="min-height:40px; height:40px; max-height:40px; "><?=$this->fc_caption?></th>
			<?
			for( $i = $this->t_start; $i <= $this->t_end; $i = $i + $this -> step_size_sec ) // Цикл по датам рисуем календарь!
			{
				$dn = date('w', $i); //Номер дня недели
				if($dn == 0 || $dn == 6 ) // выходные
				{
					//min-width:'.$this->day_width.'px;
					$dst ='color:red; font-size: 12px; padding:3px; background-color:#ffeded;   ';
				}
				else // будни
				{
					//min-width:'.$this->day_width.'px;
					$dst ='color:#000; font-size: 12px; padding:3px;   ';
				}
				
				
				if($this->head_intervals){$colsp = ' colspan="'.count($this->time_intervals).'" ';}
				else{$colsp  = '';}
				
					?><th style="text-align:center; <?=$dst?>" <?=$colsp?> ><?
					print $idate= date('d.m', $i);
					print '<br/>';
					print $this->w[$dn];
					?>
					</th><?
				}
				?>
			</tr>
			
			
			<?
			if($this->head_intervals)
			{
				?>
				<tr>
				<th></th>
				<?
				for( $i = $this->t_start; $i <= $this->t_end; $i = $i + $this -> step_size_sec ) // Цикл по датам рисуем колендарь!
				{
					foreach($this->time_intervals as $k=>$v)
					{
						// Заголовок Интервала
						if($this->time_intervals_titles[$k]){$ititle=$this->time_intervals_titles[$k];}
						else{$ititle = $k;}
						
						print '<th style="padding:3px; text-align:center; min-width:'.$this->time_interval_width.'px;">'.$ititle.'</th>';
					}
				}
				?>
				</tr>
				<? 
			}
	}
	
	
	// отображение одной брони в диаграмме ганта
	function show_broni_col($brow)
	{
		print 1;
	}
	
	
	
	
	
	
	
	// Диаграма ганта
	function show($data_broni,$data_str) 
	{ 
	
	
	

		$this->head();
		
		global $filed;
		global $t;
		global $r;
		global $mysql;
 
		$key_filed = $this->key_filed ; 
		?>
		
<div style="width:100%; position:relative; " id="gantt">
<div style="overflow: auto;" id="ganttс">
		<table class="calendar-table " id="calendar"  style="  position: relative;
  border-collapse: collapse;">
		<thead >
		<?
		$step_size_sec = $this->step_size_sec;
		
		$n=0;
		foreach($data_str as $k => $v ) // ЦИКЛ ПО НОМЕРАМ СТРОКИ
		{
			if($n==0) // Строка заголовок
			{
				$this->tr_head(); // ШАПКА ТАБЛИЦЫ (ИНТЕРВАЛЫ)
			}
			$n++;
			?>
			</thead>
			<tr>
			<td>
				<?=$this->firstcol($v);?>  
			</td>
			<?
		 
			for( $i = $this->t_start; $i <= $this->t_end; $i = $i + $step_size_sec ) // Цикл по временным отрезкам -  рисуем календарь!
			{
				$idate= date('d.m.Y', $i); //ДАТА ТЕКУЩИХ РАССМАТРИВАЕМЫХ СУТОК
				
		        $interval_c=0;
				
				
				print '<td style="padding:0; min-width:90px; min-height:215px;">';
				// print '<a href="iframe_router.php?ctr=zapiskeys&act=dayedit&place">Редактировать</a>';
				
				
				
				// Цикл по интервалам в течении суток!
				foreach($this->time_intervals as $ki=>$vi)
				{
					$interval_c++;
					
					// Бордюр интервала 
					if($interval_c!=count($this->time_intervals)){$tdst_dop=' border-right:1px solid #EEE; ';}
					else{$tdst_dop='';}
					
					// Получаем информацию о данном интервале (все брони и прочая информация)
					$brow = $data_broni[$k][$idate][$ki];  
						
					if( $brow  ) // есть информация для данного интервала 
					{
						print '<div style="background:#00CDAD; padding:3px; bottom-border:solid 1px #FFF;" class="gant_actday">';
						print '<div style="width:50%; float:left;">'.$ki .'</div>';
					
						print '
						<div style="width:50%;">
						<input name="сapacity['.$k.']['.$idate.']['.$ki.']" type="number" min="0" value="'.$brow['сapacity'].'" style="width: 40px;" class="gant_xxx">
						</div>
						';
						print '</div>';
					}
					else 
					{ 
						print '<div style="background:#EEE; padding:3px; bottom-border:solid 1px #FFF;" class="gant_actday">';
						print '<div style="width:50%; float:left;">'.$ki .'</div>';
					
						print '
						<div style="width:50%;">
						<input name="сapacity['.$k.']['.$idate.']['.$ki.']" type="number" min="0" value="'.$brow['сapacity'].'" style="width: 40px;" class="gant_xxx">
						</div>
						';
						print '</div>';;
					}
				}
				
				print '</td>';
				
				
			 
			}
			?></tr><?
		}
		?>
	   

		</table>
	 </div>
	 
	 
	 
<div style=" position: absolute; top:40px;  width:auto; left: 0;    z-index:800; background-color:#FFF;  ">
	<table class="calendar-table "   >
	<tbody>
	<?
	foreach($this->fcols as $k=>$v)
	{
		?><tr><td style="text-align:center; font-weight:bold;"><?=$this->firstcol($v);?></td></tr><?
	}
	?>
	 
	</tbody>
	</table>
</div>



		</div>
		<?
 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	 
	
	function head()
	{
		 
		?>
		<style>
		
		 .calendar-table thead th { position: sticky; top: 0; }
		
		.calendar-table thead tr:first-child th { position: sticky; top: 0; }
		
		
 .calendar-table td { padding: 0; height: 190px;  width: auto; max-width: 100%; position: relative; white-space: nowrap; border:solid 1px #333; padding-left:10px;}
 
 
 
.calendar-table { margin-bottom: 5px; overflow: hidden; width: 100%;}
.calendar-table th { padding: 3px 3px; background: none; white-space: nowrap;   width: auto; border:solid 1px #333; font-size:11px; }

.calendar-table tr:hover{background-color:#dff5df;} /* Подсветка строк */

.calendar-table .selected { background: #47ad85 !important; }

.calendar-table .dl_col{text-align:left; font-size: 12px; padding: 5px;}/* Ячейка номера */
.calendar-table .dateline { z-index:2; overflow:hidden; height:17px; left:51%; top:4px; display:block; position:absolute; border:solid 1px #000; } /* Строка брони номера */

#new-booking {z-index: 1001; display: none; position: fixed; top: 0; left: 51%; margin-left: -200px; width: 410px; border: 2px solid #bdd6f2; border-top: none; background: #eeeeee; max-height: 100%; overflow: auto;   text-align: center; }
#new-booking .wrap { margin: 5px 10px 0; overflow: hidden;}
#new-booking .form-row { text-align: left; }
#new-booking .title { font-weight: bold; margin-bottom: 5px; }
#new-booking .col1, #new-booking .col2 { display: inline-block; float: none; text-align: left;}
#new-booking .col1 { margin-right: 10px; }
#new-booking .col2 { margin-right: 0; }
#new-booking label { text-align: left; }
#new-booking .reserv label { display: block; }
#new-booking .reserv .col1, #new-booking .reserv .col2 { width: 50%; margin: 0; float: left; }
#new-booking .reserv .col1 input, #new-booking .reserv .col2 input { width: 100%; }
#new-booking .reserv .col1 .form-row { padding-right: 10px; }

		</style>
		
		<style>
		 .a_day_br {  display:block; width:100%; height:20px;   }
		 .a_day_br:hover{    background:#CCC;  }
		</style>
		
		
		<script>
		
		$( document ).ready(function() {
			$('.calendar-table td').height('200');
		});
		</script>
		

<?
		
	}
}