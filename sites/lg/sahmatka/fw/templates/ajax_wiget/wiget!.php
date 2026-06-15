<?
$v = $data;
?>

// <script> 






	//Подключаем jquery если нет
	if(!window.$)
	{
		//var script = document.createElement('script');  
		//script.src = "https://{$GLOBALS['config']['domain']}/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js";  
		//document.head.appendChild(script);
	}
 

	 
	/*
	resultto - ид тега для загрузки результата
	formid - ид формы 
	url - 
	append - добавлять к содержимому ajax
	*/
	function sendAjaxForm(resultto, formid, url,append=1,progressid='progressbar',preload='',postload='')
	{
	  
		if(!append){$('#'+resultto).html('');	}
		$('#'+progressid).show();
		$('#'+progressid).removeClass('hide');
		$("#"+resultto).fadeOut(100);


		// ПЕРЕНЕСТИ ЭТОТ ФУНКЦИОНАЛ В ВИДЖЕТ ПОСЛЕ ЗАГРУЗКИ ДАННЫХ ВЫДЕЛЯТЬ ГЕТ 
		// Получаем гет переменные 
		function gget(qs) {
			qs = qs.split("+").join(" ");
			var params = {},
				tokens,
				re = /[?&]?([^=]+)=([^&]*)/g;
			var query ='';
			
			while (tokens = re.exec(qs)) {
				params[decodeURIComponent(tokens[1])]= decodeURIComponent(tokens[2]); // массив 
				query = query + '&' + decodeURIComponent(tokens[1]) + '=' + decodeURIComponent(tokens[2]); // гет строка
			}

			return query;
		}
		var url_query = gget(document.location.search);

		  
	  
	  
	  

				
		$.ajax({
			url:     url + url_query, //url страницы (action_ajax_form.php)
			type:     "POST", //метод отправки
			dataType: "html", //формат данных
			data: $("#"+formid).serialize(),  // Сеарилизуем объект
			success: function(response) { //Данные отправлены успешно
			 
				 
				if(preload)	{	preload();	}
				 
				$("#"+resultto).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка    
				 
				/////////////  ЗАТЫЧКА ДЛЯ СЕЛЕКТОВ!!!
				$('#'+resultto).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
				$('#'+resultto).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены
				
				 
				if(append)
				{
					$('#'+resultto).append(response);
				}
				else
				{
					$('#'+resultto).html(response);
				}
				$("#"+resultto).prop('disabled', ''); // Блокируем селекты в которые предстоит загрузка    
				$('#'+progressid).hide();
				
				$("#"+resultto).fadeIn(600);
				 
				if (typeof postload !== 'undefined' && typeof postload === 'function') { postload(response); }
				if (typeof in_postload !== 'undefined' && typeof in_postload === 'function') { in_postload(response); }
			 
				  //$('select').multipleSelect('refresh');
				 // alert($('#'+resultto).prop('outerHTML') );
			},
			error: function(response) { // Данные не отправлены
				// $('#'+resultto).html('Ошибка. Данные не отправлены.');
				//alert('ajax error');
				//$('#'+progressid).hide();
				//$('#'+progressid).prop("display", 'none !important;');
				$('#'+progressid).addClass('hide');




			}
		});
	}
		
		
	/*
	select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 
	АНОНИМНАЯ ФУНКЦИЯ АРГУМЕНТ В СЛУЧАЕ УСПЕШНОЙ ЗАГРУЗКИ АЯКС
	- Выбирать элементы

	*/
	function relate_ajax_select(th,select_id_list,formid='filtrform')
	{
		// Получаем массив селектов которые затрагивает данный 
		var array = select_id_list.split(",");
		var controller= $('#'+formid).attr('data-controller');
		
		if(array)
		{
			// Цикл по массиву селектов ПРЕДВАРИТЕЛЬНО БЛОКИРУЕМ ВСЕ 
			array.forEach(function(item, i, arr) {
				
				
				if(item  && $("#"+item).prop('id') != $(th).prop('id') )
				{
					// alert( i + ": " + item + " (массив:" + arr + ")" );
					 // 
				}
			});
			//
	 
			// Цикл по массиву селектов
			array.forEach(function(item, i, arr) {		 
			if(item) 
			{
				oldval = $("#"+item).val(); // Старое значение селекта сохраняем!
				 
				var preload = function(item)
				{
					//alert($('#'+item).html());
					
					//$('#'+item).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены
					//$('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
					
				}			
				  
				//ЦИКЛИЧЕСКИЙ РЕФРЕШ ПОЛУЧАЕТСЯ ТАК КАК ЗАДАЧА МАЛ ВЫЗЫВАЕ АЯКС !)
				sendAjaxForm( item , formid , 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/ajax_actions.php?load='+item+'&controller='+ controller,1,'progressbar',preload(item)); // Грузим содержимое селек
				// РАЗБлокируем селекты в которые предстоит загрузка   
				$("#"+item).prop('disabled', '');	 
			}
		});
		//
		}
	}



	 
	// Начальная загрузка данных 
	 sendAjaxForm( 'street' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/ajax_router.php?ctr=<?=$v['ctr']?>&act=sel_street',0); // Грузим содержимое селек

	 
	 
	// Начальная загрузка данных 
 	sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/ajax_router.php?ctr=<?=$v['ctr']?>&act=<?=$v['act']?>',0); // Грузим содержимое селек

  
 
 
 
 
 
 
 
 
 
 
 



	// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
	$( "#rentsearch input,#rentsearch select" ).change(function() {
		console.log($(this).attr('name'));
		var this_url = $(location).attr('href');
		console.log('!!!-'+this_url);
 
		var form = $('#rentsearch');
		var action =  $(form).attr('action');
		
		if(!action){ action = this_url;} // По умолчанию текущий URL
		 

		// GET переменные ткуцщей страницы
		urlParams = new URLSearchParams(window.location.search);
		this_url_params = {};
		urlParams.forEach((p, key) => {this_url_params[key] = p;});
		 
		
		
		if (history.pushState != undefined)// нормальный браузер
		{

			$('.fw_ff_h',form).attr("disabled", true); // Выкллючаем некоторые поля			
			
			var formParams={};
			$.each($(form).serializeArray(), function() {
				formParams[this.name] = this.value;
			});
			
			$('.fw_ff_h',form).attr("disabled", false);// Вкллючаем обратно поля
			// вначале приписываем существующие переменные только если их нет в форме? если есть игнорим существующие 
			 
			
			
			var new_url_params = {};
			
			
			// Цикл по параметрам текущего URL
			for(k in this_url_params) 
			{
				 					
			  	if (formParams.hasOwnProperty(k) && formParams[k]) 
				{
					new_url_params[k] = formParams[k];
				} 
				else
				{
					if(this_url_params[k])
					{
						new_url_params[k] = this_url_params[k];
					}
				}
			}
			for(k in formParams)  
			{
			  	if (!this_url_params.hasOwnProperty(k) && formParams[k]) 
				{
					new_url_params[k] = formParams[k];
				}  
			}
			
			////////////// 
			var newurl = '';
			var i = 0;
			for(k in new_url_params) 
			{
				i++;
			  	if (!this_url_params.hasOwnProperty(k) && formParams[k]) 
				{
					if( i<1 ) { newurl = newurl + '?'; } else { newurl = newurl + '&'; }		
					newurl = newurl + k +'='+new_url_params[k]; 
				} 
			}
  
			//Собираем URL без гет переменных 
			var url_obj = new URL(this_url);
			var url_path = url_obj.origin + url_obj.pathname;
			
			//state = { url: url_path+newurl, title: "Home", decription: "Home Page" };
			//history.pushState(state, state.title, state.url);    // с url
			//history.pushState({}, '', url_path+newurl);
			console.log(url_path+newurl);
		}
 
	    sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/ajax_router.php?ctr=<?=$v['ctr']?>&act=<?=$v['act']?>',0); // Грузим содержимое селек
	   // $('#maprentobjects').hide();
	});
	
	
	
	
	
	
	
	
	
	 
/*
	// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
	$( "#rent_search_submit" ).click(function() {
		$('#rent_home_id').attr('value',''); // Очистка дома
		sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/ajax_router.php?ctr=<?=$v['ctr']?>&act=<?=$v['act']?>',0); // Грузим содержимое селек
		//  $('#maprentobjects').hide();
	   
	   return false;
	});
*/




	$('body').on('click', '#showmap', function(){
	  $('#rentobjects').fadeOut(100);
	  $('#showmap').fadeOut(100);
	  $('#maprentobjects').fadeIn(600);
	  $('#hidemap').fadeIn(300);
	   return false;
	});

	$('body').on('click', '#hidemap', function(){
	  $('#rentobjects').fadeIn(600);
	  $('#showmap').fadeIn(300);
	  $('#maprentobjects').fadeOut(100);
	  $('#hidemap').fadeOut(100);
	   return false;
	});





	 $('#sel_rooms_min').on('change', function() {
	 // --relate_ajax_select(this,'sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max'); //
	});

 





 