<?
// Класс полей формы
class filed
{
	
	#Текстовое поле
	function hidden($name,$caption,$value='')
	{
		if(!$value){$value='';} // Скрываем нули
		?>
		<input type="hidden" name="<?=$name?>" value="<?=$value?>" data-caption="<?=$caption?>" />
		<?
	}
	function perrors($name)
	{
		global $filed_errors;
		if($filed_errors[$name])
		{
			foreach($filed_errors[$name] as $k=>$v)
			{
				$ret .= '<span class="input_error">'.$v.'</span>';
			}
		}
		return $ret;
	}
	
	function error_filed_class($name)
	{
		global $filed_errors;
		if($filed_errors[$name])
		{
			return ' error_input ';
		}
	}
	
	
    // Чекаем по именам полей плечхудер функциями
    

    // ПИшем ошибки в $this->errors[name] и выводим в полях
    

    #### Вывод в шаблонах
    function print_timestamp($x)
    {
        return date("d.m.Y / H:i", $x);
    }

    function print_status($x)
    {
        if ($x == 0)
        {
            return '';
        }
        elseif ($x == 1)
        {
            return 'Бронь отменена ';
        }
        elseif ($x == 2)
        {
            return 'НЕ подтверждено';
        }
        elseif ($x == 3)
        {
            return 'Бронь - без оплаты';
        }
        elseif ($x == 4)
        {
            return 'Бронь - оплачено';
        }
        elseif ($x == 5)
        {
            return 'Выезд';
        }
        elseif ($x == 6)
        {
            return 'Незаезд';
        }
        elseif ($x == 7)
        {
            return 'Заехали, оплачено';
        }
        else
        {
            return $x;
        }
    }
    function status_color($x)
    {
        /*
        Зелёный - заехали, оплачено. Красный - на брони, не заехали, не оплачено. Желтый - не заехали, но внесли предоплату.
        */
        if ($x == 0)
        {
            return '';
        }
        elseif ($x == 1)
        {
            return '#CCCCCC';
        }
        //elseif($x==2){return '#7FFFD4';} //
        elseif ($x == 3)
        {
            return '#FF8A90';
        } // К
        elseif ($x == 4)
        {
            return '#F0E68C';
        } //
        elseif ($x == 5)
        {
            return '#F0F0FF';
        } //
        elseif ($x == 6)
        {
            return '#696969';
        }
        elseif ($x == 7)
        {
            return '#8DFFA9';
        }
        else
        {
            return $x;
        }
    }

    function status_color_text($x)
    {
        /*
        Зелёный - заехали, оплачено. Красный - на брони, не заехали, не оплачено. Желтый - не заехали, но внесли предоплату.
        */
        if ($x == 0)
        {
            return '';
        }
        elseif ($x == 1)
        {
            return '#000';
        }
        //elseif($x==2){return '#FFF';} //
        elseif ($x == 3)
        {
            return '#000';
        } // К
        elseif ($x == 4)
        {
            return '#000';
        } //
        elseif ($x == 5)
        {
            return '#FFF';
        } //
        elseif ($x == 6)
        {
            return '#FFF';
        }
        elseif ($x == 7)
        {
            return '#000';
        }
        else
        {
            return $x;
        }
    }

    function status_arr()
    {
        //$arr=array();
        $arr[7] = 'Заехали, оплачено';
        $arr[3] = 'Бронь - без оплаты';
        $arr[4] = 'Бронь - оплачено';
        return $arr;
    }

    function phone_int($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return $phone;
    }

    # Время
    function time($name, $caption, $value = '')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        $r = rand(0, 10000);
?>
		<span class="input_title"><?=$caption
?></span>
		<input id="<?=$name
?><? $r
?>"  list="<?=$name
?><? $r
?>"  type="time" list="time-list" name="<?=$name
?>" value="<?=$value
?>"   class="input_edit"  autocomplete="off" />
		<?
    }

    # Диапазон дат
    function date_diap($name, $caption, $value = '', $tagattr = '')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        
?>
		<span class="input_title"><?=$caption
?></span>
		<input type="text" name="<?=$name
?>" value="<?=$value
?>"   class="input_edit date_diap" <?=$tagattr ?>  autocomplete="off" />
		<?
    }

    # Диапазон дат
    function date_birth($name, $caption, $value = '')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        else
        {
            // в читаемый вид из mysql date
            $value = date("d.m.Y", strtotime($value));
        }
?>
		<span class="input_title"><?=$caption
?></span>
		<input type="text" name="<?=$name
?>" value="<?=$value
?>"   class="input_edit"  autocomplete="off" />
		<?
    }

    # textarea
    function textarea($name, $caption, $value = '', $tagatr = 'rows="10"')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        
?>
		<span class="input_title"><?=$caption
?></span>
		<textarea name="<?=$name
?>" class="input_edit" <?=$tagatr ?>><?=$value ?></textarea>
		<?
    }

    # Диапазон дат в два таймштампа
    function datediap_data($text)
    {
        global $mysql;
        $hotel_info = $mysql->get_for_key('tra_hotels', 'hotel_id', 1);

        //[check_in_time] => 14:00:00 [check_out_time] => 12:00:00
        $h_check_in_time = explode(':', $hotel_info['check_in_time']); // массив часы, минуты
        $h_check_out_time = explode(':', $hotel_info['check_out_time']); // массив часы, минуты
        

        // Преобразуем диаппазон дат
        $d_arr = explode(' - ', $text);

        $date1 = new DateTime($d_arr[0]);
        $date1->setTime($h_check_in_time[0], $h_check_in_time[1], $h_check_in_time[2]);

        $date2 = new DateTime($d_arr[1]);
        $date2->setTime($h_check_out_time[0], $h_check_out_time[1], $h_check_out_time[2]);
        #############
        

        $data['start'] = $d_arr[0];
        $data['stop'] = $d_arr[1];
        $data['timestamp_start'] = $date1->getTimestamp();
        $data['timestamp_end'] = $date2->getTimestamp();
        return $data;
    }

    # Диапазон дат (КОТОРЫЕ ПОКАЗЫВАЕМ В ФИЛЬТРАХ - год месяц квартал)
    function date_diap_big($name, $caption, $value = '', $tagattr = '')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        
?>
		<span class="input_title"><?=$caption
?></span>
		<input type="text" name="<?=$name
?>" value="<?=$value
?>"   class="input_edit date_diap" <?=$tagattr ?>  autocomplete="off" />
		<?
    }

















    # Диапазон дат
    function date($name, $caption, $value = '',$attr='')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
       // print $value;
?>
		<span class="input_title"><?=$caption
?></span>
		<input type="date" <?=$attr?> name="<?=$name
?>" value="<?=$value
?>"  class="input_edit"  autocomplete="off" />
		<?
    }

    #Текстовое поле
    function text_num($name, $caption, $value = '', $min = '0', $max = '', $step = '1', $style = 'width:80px; display:inline-block;')
    {
        if (!$value)
        {
            $value = '';
        }
?>
		<span class="input_title"><?=$caption
?></span>
		<input type="number" step="<?=$step
?>" name="<?=$name
?>" value="<?=$value
?>"   class="input_edit" style="<?=$style
?>" min="<?=$min
?>" max="<?=$max
?>" autocomplete="off" />
		<?
    }

    #Текстовое поле
    function text_float($name, $caption, $value = '', $min = '0', $max = '', $step = '0.1', $style = 'width:80px; display:inline-block;')
    {
        if (!$value)
        {
            $value = '';
        }
		?><span class="input_title"><?=$caption?></span><input type="number" step="<?=$step?>" name="<?=$name?>" value="<?=$value?>"   class="input_edit" style="<?=$style?>" min="<?=$min?>" max="<?=$max?>" autocomplete="off" />	<?
    }

    function div($c, $attr = '')
    {
        print '<div style="' . $style . '">' . $c . '</div>';
    }

    #Текстовое поле
    function text($name, $caption, $value = '', $tagatr = '')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        
		?><span class="input_title"><?=$caption?></span>
		<input type="text" name="<?=$name?>" value="<?=$value?>" class="input_edit <?=$this->error_filed_class($name);?>" <?=$tagatr ?> autocomplete="off" /> 
		<span class="input_error"><?=$this->perrors($name);?></span>
		<?
    }

    # textarea
    function textarea_html($name, $caption, $value = '', $tagatr = 'rows="10"')
    {
        if (!$value)
        {
            $value = '';
        } // Скрываем нули
        
?>
		<script src="https://cdn.ckeditor.com/ckeditor5/12.0.0/classic/ckeditor.js"></script>
		<script src="https://cdn.ckeditor.com/ckeditor5/12.0.0/classic/translations/ru.js"></script>
		<style>
		.ck-editor__editable {min-height:200px; border: solid #3C96E1 2px}
		</style>

		<span class="input_title"><?=$caption
?></span>
		<textarea name="<?=$name
?>" class="editor input_edit" <?=$tagatr ?>><?=$value ?></textarea>
			<script>
				$(document).ready(function() {
				var myEditor;
					ClassicEditor.create( document.querySelector( '.editor' ),
					{
						language: 'ru',
						resize: {
							minHeight: 100,
							maxHeight: 100
						}
					})
					.then( editor => {
					console.log( 'Editor was initialized', editor );
					myEditor = editor;
					} 
					)
					.catch( error => {
						console.error( error );
					} );
				 });
			</script>
		<?
    }

    function map($name_perf = 'map', $values = '')
    {
        $idx = 'ym_' . $name_perf;
?>
		<div style="width:100%;" class="input_edit">
		Поместите метку на объект, для получения координат и адреса 
		<div id="<?=$idx
?>" style="width: 100%; height: 400px;"></div> 
		  
		<span class="input_title">Адрес</span>
		<input type="text" id="<?=$idx
?>yam_adress" name="<?=$name_perf
?>_adress" value = "<?=$values['adress'] ?>"  style="width:100%" class="input_edit" />
 	 
		<span class="input_title">Координаты</span>
		<input type="text" id="<?=$idx ?>yam_point_lat" name="<?=$name_perf ?>_lat" value = "<?=$values['lat'] ?>"  style="width:100%" class="input_edit" />
		<input type="text" id="<?=$idx ?>yam_point_lon" name="<?=$name_perf ?>_lon" value = "<?=$values['lon'] ?>"  style="width:100%" class="input_edit" />
		 
		<script src="https://yandex.st/jquery/2.1.1/jquery.min.js"></script>
		<script src="https://api-maps.yandex.ru/2.1/?apikey=9998badd-d4f7-462f-b4a5-9c3aa51768c0&lang=ru_RU"></script>

		<script type="text/javascript">
		ymaps.ready(init<?=$idx ?>);        
		function init<?=$idx ?>() {
			var myPlacemark,  coord, 
			myMap = new ymaps.Map("<?=$idx ?>", {
				center: [55.76, 37.64],
				zoom: 12
			}, {
				// searchControlProvider: 'yandex#search'
			});
			
			/*  адрес по умолчанию на карте */
			var address = 'Россия, Новосибирск, Тюленина, д. 26';
			 
			
			// координаты из текстового поля
			if( $('#<?=$idx ?>yam_point_lat').attr('value') && $('#<?=$idx ?>yam_point_lon').attr('value')  )
			{
					coord = [$('#<?=$idx ?>yam_point_lat').val(), $('#<?=$idx ?>yam_point_lon').val()]; 
			}
			else
			{
				if( $('#<?=$idx ?>yam_adress').attr('value') )
				{
					address =  $('#<?=$idx ?>yam_adress').val();
				}
			} 
			
			
			ymaps.geocode(address).then(function(res) {
			
			// Нет координат получаем по адресу
			if(!coord)
			{
				  coord = res.geoObjects.get(0).geometry.getCoordinates();
			}	
			else // Есть координаты получаем по ним адрес?! 
			{
			
				// нет координат из текстовых полей
				//  alert('1');
				// adress2 = getAddress(coord);
			//	alert(adress2);
			}
			
			var myPlacemark = new ymaps.Placemark(coord, null, {
					preset: 'islands#blueDotIcon',
					draggable: true
			});
			
				
				// Слушаем клик на карте.
					myMap.events.add('click', function (e) {
						
						 
						var coords = e.get('coords');

						// Если метка уже создана – просто передвигаем ее.
						if (myPlacemark) {
							myPlacemark.geometry.setCoordinates(coords);
						}
						// Если нет – создаем.
						else {
							myPlacemark = createPlacemark(coords);
							myMap.geoObjects.add(myPlacemark);
							// Слушаем событие окончания перетаскивания на метке.
							myPlacemark.events.add('dragend', function () {
								getAddress(myPlacemark.geometry.getCoordinates());
							});
						}
						getAddress(coords);
					});
					
					
					
					// Создание метки.
					function createPlacemark(coords) {
						return new ymaps.Placemark(coords, {
							iconCaption: 'поиск...'
						}, {
							preset: 'islands#violetDotIconWithCaption',
							draggable: true
						});
					}
					
					
					// Определяем адрес по координатам (обратное геокодирование).
					function getAddress(coords) {
						myPlacemark.properties.set('iconCaption', 'поиск...');
						ymaps.geocode(coords).then(function (res) {
							var firstGeoObject = res.geoObjects.get(0);	 
							
							myPlacemark.properties
								.set({
									// Формируем строку с данными об объекте.
									iconCaption: [
										// Название населенного пункта или вышестоящее административно-территориальное образование.
										firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
										// Получаем путь до топонима, если метод вернул null, запрашиваем наименование здания.
										firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
									].filter(Boolean).join(', '),
									// В качестве контента балуна задаем строку с адресом объекта.
									balloonContent: firstGeoObject.getAddressLine()
								});
								
								$('#<?=$idx ?>yam_point_lat').val(coords[0]);
								$('#<?=$idx ?>yam_point_lon').val(coords[1]);
								
								$('#<?=$idx ?>yam_adress').val( firstGeoObject.getAddressLine() );
								 
						});
					}
		 
				/* Событие dragend - получение нового адреса */
				myPlacemark.events.add('dragend', function(e){
					var cord = e.get('target').geometry.getCoordinates();
		 
					$('#<?=$idx ?>yam_point_lat').val(cord[0]);
					$('#<?=$idx ?>yam_point_lon').val(cord[1]);
								
					ymaps.geocode(cord).then(function(res) {
						var data = res.geoObjects.get(0).properties.getAll();
						$('#<?=$idx ?>yam_adress').val(data.text);
					});
				});
				
				myMap.geoObjects.add(myPlacemark);	
				myMap.setCenter(coord, 17);
			 	//myMap.setBounds(myMap.geoObjects.getBounds(),{checkZoomRange:true, zoomMargin:7}); // Авто масштаб и центрироание 

			});
		}
		</script>

		</div>
		<?
    }

    # select
    function select($name, $caption, $data, $value = '', $style = 'text-transform:none; height:auto;',$attr='')
    {
		?><span class="input_title"><?=$caption?></span>
		<select name="<?=$name?>" class="input_edit" style="<?=$style?>" <?=$attr?>>
		<?
        foreach ($data as $k => $v)
        {
			?>
			<option value="<?=$k?>" 
			<? 
			if ($value == $k){ print 'selected="selected"';} ?> ><?=$v?></option><?
        }
			?>
		</select>
		<?
    }
	
	
	
	
	
	
	
	
	
	

    # select
    function select_status($name, $caption, $data, $value = '')
    {
?>
		<span class="input_title"><?=$caption
?></span>
		<select name="<?=$name
?>" class="input_edit" style="text-transform:none; height:auto; ">
		<?
        foreach ($data as $k => $v)
        {
            $bgc = $this->status_color($k);
            $color = $this->status_color_text($k);
?>
				<option style="background-color:<?=$bgc
?>; color:<?=$color
?>" value="<?=$k
?>" <? if ($value == $k)
            {
                print 'selected="selected"';
            } ?> ><?=$v ?> </option>
			<?
        }
?>
		</select>
		<?
    }

    # checkbox
    function checkbox($name, $caption, $checked,$attr='',$id='',$default_value='1')
    {
         if(!$id){ $id= $name.rand(0, 10000);}
		 ?>
		 <label class=" " for="<?=$id?>" style="cursor: pointer;  display: inline-block;   border: solid 1px #EEE;   padding: 5px;">
		 <input id="<?=$id?>" type="checkbox" <?=$attr?> style="width:auto;" name="<?=$name?>"  value="<?=$default_value?>"  
		 <? if ($checked){print 'checked ';} ?> />
		 <?=$caption?>
		 </label> 
		 
		 
		 <?
    }

    function image($name, $caption = '', $value = '', $fid)
    {

        if (!$value)
        {
            $value = '/admin/nofoto.png';
        }
?>
		
		<div class="fw_fileupload" data-skinresult="default" data-tdir="<?=$fid; ?>" data-filename="" data-many="1"  data-result_src_id="<?=$fid; ?>_filedsrc" data-result_val_id="<?=$fid; ?>_filed" data-progress_id="<?=$fid ?>_progress"  >
			<span class="input_title"><?=$caption ?></span>
		    <input type="text"   name="<?=$name ?>" value="<?=$value ?>" class="input_edit fw_imagelink" id="<?=$fid; ?>_filed" />
			
			<input type="file" class="fw_file" name="ajaxuserfiles[]" id="<?=$fid; ?>_ajaxuserfiles" style="display:none;">
			<label   for="<?=$fid; ?>_ajaxuserfiles" class="forminformpanel_link" style="text-align:center; cursor:pointer; width:100%;">Загрузить файл</label>						 
 
			<div style="width:100%; background-color:#EEE; margin-bottom:2px; display:none;" class="<?=$fid; ?>_progress">
				<span id="<?=$fid; ?>_progress" style="display: block; width: 100%; text-align: center;"></span>
				<div style="background-color:#000; height:1px; width:0" id="<?=$fid; ?>_progress_line"></div>
			</div>
			
			<div style="padding:3px; border:solid 1px #3C96E1;  text-align: center; background-color:#fafafa;">
				<img id="<?=$fid ?>_filedsrc" src="<?=$value ?>" class="fw_fileupload_img" style="max-width:100%; max-height:200px; " />
			</div> 
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
		</div>
		<?
    }

    function images($name, $caption = '', $value = '', $fid)
    {
        global $mysql;

        if (!$value)
        {
            $value = '/admin/nofoto.png';
        }
?>
		<style>
		.fiprew{
			display: inline-block;
			padding: 3px;
			border:solid 1px #EEE;
			position:relative;
			margin-top:5px;
		}
		.fiprew img {height:110px; max-width:110px;}
		
		.fiprew_links {
			padding:5px;
			color:#000;
			position:absolute;
			display:none;
			background-color:#FFF;
		}
		.fiprew_links a:hover{font-weight:bold;}
		.fiprew:hover .fiprew_links  { display:block;}
		
		.fiprew_links_main{left:0;}
		.fiprew_links_del{right:0; }
		.fiprew_links_del a{color:red; }
		</style>
		<div class="fw_fileupload" data-name="<?=$name
?>" data-nodeid="<?=$fid
?>" data-skinresult="default" data-tdir="<?=$fid; ?>" data-filename="" data-many="1"  data-result_src_id="<?=$fid; ?>_filedsrc" data-result_val_id="<?=$fid; ?>_filed" data-progress_id="<?=$fid ?>_progress"  >
			<span class="input_title"><?=$caption ?></span>
		   
			
			<input type="file" class="fw_files" name="ajaxuserfiles2[]" id="<?=$fid; ?>_ajaxuserfiles" multiple >
			<label   for="<?=$fid; ?>_ajaxuserfiles" class="forminformpanel_link" style="text-align:center; cursor:pointer; width:100%;">Загрузить файл</label>						 
 
			<div style="width:100%; background-color:#EEE; margin-bottom:2px; display:none;" class="<?=$fid; ?>_progress">
				<span id="<?=$fid; ?>_progress" style="display: block; width: 100%; text-align: center;"></span>
				<div style="background-color:#000; height:1px; width:0" id="<?=$fid; ?>_progress_line"></div>
			</div>
			<?
        $data_arr = $mysql->get_arr('SELECT * FROM files2node WHERE node_id = "' . $fid . '" ');
?>
			<div style="padding:3px; border:solid 1px #3C96E1;  text-align: center; background-color:#fafafa; display:block;" class="fiprew">
				<img id="<?=$fid
?>_filedsrc" src="<?=$value
?>" class="fw_fileupload_img" style="max-width:100%; max-height:200px; min-width:200px; height:auto;" />
				<input type="text"  name="<?=$name
?>[0]" value="<?=$value
?>" class="input_edit fw_imagelink" id="<?=$fid; ?>_filed" />
			</div> 
			
			<div class="fw_fileupload_prew">
			<!-- Превюхи -->
			<?

        foreach ($data_arr as $k => $v)
        {
            //print_r($v);
            
?>
				<div class="fiprew">
				<div class="fiprew_links fiprew_links_main"  > 	<a href="" title="Главная">m</a> </div>
				<div class="fiprew_links fiprew_links_del" > <a href="" title="Удалить">x</a> </div>
				
				
				<img src="<?=$v['link'] ?>" /><br/>
				<input type="hidden" name="<?=$name ?>[<?=$v['files2node_id'] ?>]"  value="<?=$v['link'] ?>" /></div>
				<?
        }
?>
			</div>
			
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
			
			
		</div>
		<?
    }








	
	function file_doc($name,$caption='',$value='',$fid,$add=true, $edit = false) 
	{
		$fid = $name.'__'.$fid;
	 
		 // Для массивов (множественных здачений)
		 $fid = str_replace('[','_',$fid);
		 $fid = str_replace(']','_',$fid);
		?>
 		 <style>
		.fiprew{
			display: inline-block;
			padding: 3px;
			border:solid 1px #EEE;
			position:relative;
			margin-top:5px;
			max-width:200px;
			min-height:150px;
			background: #FFF;
			text-align: center;
			vertical-align: top;
		}
		.fiprew img {height:110px; max-width:190px;}
		
		.fiprew_links {
			padding:5px;
			color:#000;
			position:absolute;
			display:none;
			background-color:#FFF;
		}
		.fiprew_links a:hover{font-weight:bold;}
		.fiprew:hover .fiprew_links  { display:block;}
		
		.fiprew_links_main{left:0;}
		.fiprew_links_del{right:0; }
		.fiprew_links_del a{color:red; }
		</style>
		
		
		<div class="fw_fileupload" data-skinresult="default" data-tdir="<?=$fid;?>" data-filename="" data-many="1"  data-result_src_id="<?=$fid;?>_filedsrc" data-result_val_id="<?=$fid;?>_filed" data-progress_id="<?=$fid?>_progress"  >
			<div class="fw_input_wrapper">
			<span class="input_title"><?=$caption?></span>
		       <input type="hidden" name="<?=$name?>" value="<?=$value?>"  id="<?=$fid;?>_filed" />
			<?
			  if(!$value){$value = '/sahmatka/noimg.png';}
			?>
			  
					 
			<div style="width:100%; background-color:#EEE; margin-bottom:2px; display:none;" class="<?=$fid;?>_progress">
				<span id="<?=$fid;?>_progress" style="display: block; width: 100%; text-align: center;"></span>
				<div style="background-color:#000; height:1px; width:0" id="<?=$fid;?>_progress_line"></div>
			</div>
			
			<div style="padding:3px; border:solid 1px #3C96E1;  text-align: center; background-color:#fafafa; width:100%; min-height:200px;">
				<br/>
				 <a href="<?=$value?>" target="_blank" style="display:block; width:100%; font-size: 12px;">
				<?
				$file_name = basename($value);
				$file_ext = end(explode(".", basename($v['link'])));
				
				if($file_ext =='jpg' || $file_ext =='jpeg' || $file_ext =='png' || $file_ext =='svg' )
				{
					$file_icon = $v['link'];
				}
				else
				{
					$file_icon = '/sahmatka/template/file_icon.png';
				}
				?>
				<img src="<?=$file_icon?>" /><br/>
				<?=$file_name ?>
				</a>
			</div> 
			
				<br/>
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
			<br/>
			 
			<?
			if(($add && !$value ) || $edit )
			{
			?>
			<input type="file" class="fw_file" name="ajaxuserfiles[]" id="<?=$fid;?>_ajaxuserfiles" style="display:none;">
			<label   for="<?=$fid;?>_ajaxuserfiles" class="forminformpanel_link" style="text-align:center; cursor:pointer; width:100%; margin-top:5px;">Загрузить файл </label>						 
			<?
			}
			?>
		 
			<span class="fw_input_error"></span>
			</div>
		</div>
 
		<?
	}








	
	function file($name,$caption='',$value='',$fid,$add=true, $edit = false) 
	{
		$fid = $name.'__'.$fid;
	 
		 // Для массивов (множественных здачений)
		 $fid = str_replace('[','_',$fid);
		 $fid = str_replace(']','_',$fid);
		?>
 		 <style>
		.fiprew{
			display: inline-block;
			padding: 3px;
			border:solid 1px #EEE;
			position:relative;
			margin-top:5px;
			max-width:200px;
			min-height:150px;
			background: #FFF;
			text-align: center;
			vertical-align: top;
		}
		.fiprew img {height:110px; max-width:190px;}
		
		.fiprew_links {
			padding:5px;
			color:#000;
			position:absolute;
			display:none;
			background-color:#FFF;
		}
		.fiprew_links a:hover{font-weight:bold;}
		.fiprew:hover .fiprew_links  { display:block;}
		
		.fiprew_links_main{left:0;}
		.fiprew_links_del{right:0; }
		.fiprew_links_del a{color:red; }
		</style>
		
		
		<div class="fw_fileupload" data-skinresult="default" data-tdir="<?=$fid;?>" data-filename="" data-many="1"  data-result_src_id="<?=$fid;?>_filedsrc" data-result_val_id="<?=$fid;?>_filed" data-progress_id="<?=$fid?>_progress"  >
			<div class="fw_input_wrapper">
			<span class="input_title"><?=$caption?></span>
		       <input type="hid2den" name="<?=$name?>" value="<?=$value?>"  id="<?=$fid;?>_filed" />
			<?
			  if(!$value){$value = '/sahmatka/noimg.png';}
			?>
			  
					 
			<div style="width:100%; background-color:#EEE; margin-bottom:2px; display:none;" class="<?=$fid;?>_progress">
				<span id="<?=$fid;?>_progress" style="display: block; width: 100%; text-align: center;"></span>
				<div style="background-color:#000; height:1px; width:0" id="<?=$fid;?>_progress_line"></div>
			</div>
			
			<div style="padding:3px; border:solid 1px #3C96E1;  text-align: center; background-color:#fafafa; width:100%; min-height:200px;">
				<br/>
				 <a href="<?=$value?>" target="_blank" style="display:block; width:100%; font-size: 12px;">
				<?
				$file_name = basename($value);
				$file_ext = end(explode(".", basename($v['link'])));
				
				if($file_ext =='jpg' || $file_ext =='jpeg' || $file_ext =='png' || $file_ext =='svg' )
				{
					$file_icon = $v['link'];
				}
				else
				{
					$file_icon = '/sahmatka/template/file_icon.png';
				}
				?>
				<img src="<?=$file_icon?>" /><br/>
				<?=$file_name ?>
				</a>
			</div> 
			
				<br/>
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
			<br/>
			 
			<?
			if(($add && !$value ) || $edit )
			{
			?>
			<input type="file" class="fw_file" name="ajaxuserfiles[]" id="<?=$fid;?>_ajaxuserfiles" style="display:none;">
			<label   for="<?=$fid;?>_ajaxuserfiles" class="forminformpanel_link" style="text-align:center; cursor:pointer; width:100%; margin-top:5px;">Загрузить файл </label>						 
			<?
			}
			?>
		 
			<span class="fw_input_error"></span>
			</div>
		</div>
 
		<?
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Загрузка файла 
 
	function file2($name,$caption='',$value='',$fid,$add=true, $edit = false, $req = false ) 
	{
		$fid = $name.'__'.$fid;
	 
		 // Для массивов (множественных здачений)
		 $fid = str_replace('[','_',$fid);
		 $fid = str_replace(']','_',$fid);
		?>
 		 <style>
		.fiprew{
			display: inline-block;
			padding: 3px;
			border:solid 1px #EEE;
			position:relative;
			margin-top:5px;
			max-width:200px;
			min-height:150px;
			background: #FFF;
			text-align: center;
			vertical-align: top;
		}
		.fiprew img {height:110px; max-width:190px;}
		
		.fiprew_links {
			padding:5px;
			color:#000;
			position:absolute;
			display:none;
			background-color:#FFF;
		}
		.fiprew_links a:hover{font-weight:bold;}
		.fiprew:hover .fiprew_links  { display:block;}
		
		.fiprew_links_main{left:0;}
		.fiprew_links_del{right:0; }
		.fiprew_links_del a{color:red; }
		</style>
		
		
		<div class="fw_fileupload" data-skinresult="default" data-tdir="<?=$fid;?>" data-filename="" data-many="0"  data-result_src_id="<?=$fid;?>_filedsrc" data-result_val_id="<?=$fid;?>_filed" data-result_file_id="<?=$fid;?>_file" 
		data-result_link_id="<?=$fid;?>_link" data-progress_id="<?=$fid?>_progress" style="width: 470px; max-width: 90%;" >
			<div class="fw_input_wrapper">
			<span class="input_title"><?=$caption?></span>
			
			<?
			if($req){$req = 'required';}
			else{$req ='';}
			?>
		       <input type="text" style="opacity:0; height:1px;" <?=$req?> name="<?=$name?>" value="<?=$value?>"  id="<?=$fid;?>_filed" />
			<?
			  $file_name = basename($value);
			  $file_icon = '/sahmatka/template/file_icon.png';		
			  if(!$value){$value = '/sahmatka/noimg.png';}
			?>
			   	 
			<div style="width:100%; background-color:#EEE; margin-bottom:2px; display:none;" class="<?=$fid;?>_progress">
				<span id="<?=$fid;?>_progress" style="display: block; width: 100%; text-align: center;"></span>
				<div style="background-color:#000; height:1px; width:0" id="<?=$fid;?>_progress_line"></div>
			</div>
			
			<div style="padding:3px; border:solid 1px #3C96E1;  text-align: center; background-color:#fafafa; width:100%; min-height:50px;">
				 <table>
				 <tr>
					<td>
						 
						<img src="<?=$file_icon?>" style="width:30px;" />
						 
					</td>
					<td>
					<?
					if($file_name)
					{
						?>
						<a href="<?=$value?>" target="_blank" style="display:block; width:100%; font-size: 12px;" id="<?=$fid;?>_link">
						<span id="<?=$fid;?>_file"><?=$file_name ?></span>
						</a>
						<?
					}
					else
					{
						?>
						<span id="<?=$fid;?>_file">нет файла</span>
						<?
					}
					?>
		
						
					</td>
				 </tr>
				 </table>
				 
				
				 
				
				 
			</div> 
			
			 
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
			 
			 
			<?
			if(($add && !$value ) || $edit )
			{
			?>
			<input type="file" class="fw_file" name="ajaxuserfiles[]" id="<?=$fid;?>_ajaxuserfiles" style="display:none;">
			<label   for="<?=$fid;?>_ajaxuserfiles" class="forminformpanel_link" style="text-align:center; cursor:pointer; width:100%; margin-top:5px;">Загрузить файл </label>						 
			<?
			}
			?>
		 
			<span class="fw_input_error"></span>
			</div>
		</div>
 
		<?
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// ФАЙЛЫ В ВИДЕ ТАБЛИЦЫ С ДУБЛИРОВАНИЕМ В MYSQL
	
	function files2( $name, $caption='', $data_arr ='' , $id ,$file_type='', $add=true, $edit = false , $editurl = '' )
	{
		
		$fid = $name.'__'.$id;
		global $mysql;
		
		if( !$fid )
		{
			print 'Для того, чтобы добавить файлы - сохраните документ';
			return;
		}
	 		
	 
		?>
		<style>
		.fiprew{ 
			padding: 3px;
			border:solid 1px #EEE;
			margin-top:5px; 
		}
		.fiprew img {height:20px; max-width:20px;}
		
		.fiprew_links {
			padding:5px;
			color:#000;
			position:absolute;
			display:none;
			background-color:#FFF;
		}
		.fiprew_links a:hover{font-weight:bold;}
		.fiprew:hover .fiprew_links  { display:block;}
		
		.fiprew_links_main{left:0;}
		.fiprew_links_del{right:0; }
		.fiprew_links_del a{color:red; }
		
		
		.filestable tr td{ padding:5px; font-size:16px; border:solid 1px #EEE;}
		</style>
		
		
		<?
		if( $edit )
		{
		 ?>
		 <script>
		 // Сотировка ajax
		 $( function() {
			$( ".fw_fileupload_prew" ).sortable();
			//$( ".fw_fileupload_prew" ).disableSelection();
		  } );
		 </script> 
		<?
		}
		?>
		
		<div class="fw_fileupload" style="background-color:#FCFCFC;" data-file_type="<?=$file_type?>" data-name="<?=$name?>" data-nodeid="<?=$id?>" data-skinresult="default" data-tdir="<?=$fid;?>" data-filename="" data-many="1"  data-result_src_id="<?=$fid;?>_filedsrc" data-result_val_id="<?=$fid;?>_filed" data-progress_id="<?=$fid?>_progress"  >
			<span class="input_title" style="    background-color: #EEE; padding: 7px; margin: 0"><?=$caption?></span>

			
			<div style="width:100%; background-color:#EEE; margin-bottom:2px; display:none;" class="<?=$fid;?>_progress">
				<span id="<?=$fid;?>_progress" style="display: block; width: 100%; text-align: center;"></span>
				<div style="background-color:#000; height:1px; width:0" id="<?=$fid;?>_progress_line"></div>
			</div>
			
			<?
		
			// Дублируем из папки (или берем только из папки)
			?>
		 
			<div class="fw_fileupload_prew" style="border: none;  ">
			<!-- Превюхи -->
			
			<table class="filestable" width="100%">
			<?
			foreach($data_arr as $k=>$v)
			{
				 
				$file_name = basename($v['link']);
				$file_ext = end(explode(".", basename($v['link'])));
				
				if($file_ext =='jpg' || $file_ext =='jpeg' || $file_ext =='png' || $file_ext =='svg' )
				{
					$file_icon = $v['link'];
				}
				else
				{
					$file_icon = '/sahmatka/template/file_icon.png';
				}
				
				//print '<pre>';
				// print_r($data_arr);
				//print '</pre>';
				
				
					
			$file_type_display['11']='Договор М2';
			$file_type_display['12']='Акт М2';
			$file_type_display['21']='Договор Сталкер';
			$file_type_display['22']='Акт Сталкер';
			
				?>
				<tr class="fiprew">
					<td width="200"><?=date('d.m.Y H:m:s', $v['uptime']); ?></td>
					<td width="200"><?=$file_type_display[$v['file_type']] ?></td>
					<td width="200"><?=$v['login'] ?></td>
					<td>
						<a href="<?=$v['link']?>" target="_blank" style="display:block; width:100%; font-size: 15px;">
						<img src="<?=$file_icon?>" width="20" /> 
						<?=$file_name ?>
						</a>
					</td>
					
					 
					<?
						if($edit)
						{
							?>
							<div class="fiprew_links fiprew_links_del" > <a href="#" title="Удалить">x</a> </div>
							<?
						}
						?>
					 
						<input type="hidden" class="file_type"  value="<?=$v['file_type']?>" />
						<input type="hidden" class="files2node_id"  value="<?=$v['files2node_id']?>" />
						<input type="hidden" name="<?=$name?>[<?=$v['files2node_id']?>]"  value="<?=$v['link']?>" />
						<input type="hidden" name="<?=$name?>__sort[]" value="<?=$v['files2node_id']?>">
			 
					 
				 
				</tr>
			 
				<?
			} 
			?>
			</table>
			</div>
			
			<?
			if($add)
			{
			?>
			<input type="file" style="display:none;" class="fw_files2" name="ajaxuserfiles2[]" id="<?=$fid;?>_ajaxuserfiles" multiple >
			<label   for="<?=$fid;?>_ajaxuserfiles" class="forminformpanel_link" style="text-align:center; cursor:pointer; width:100%; margin-top:5px;">Загрузить файлы</label>						 
			<?
			}
			?>
			
			<div class="fw_fileupload_result"><!-- Результат из upload.php --></div>
			
			
		</div>
		<?
	}
	



 
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    function submit($caption = 'Сохранить', $tagatr = ' class="input_button" ')
    {
		?>
		<input style ="width: 470px;  max-width: 90%;" type="submit" value="<?=$caption?>" <?=$tagatr ?> >
		<?
    }

}
