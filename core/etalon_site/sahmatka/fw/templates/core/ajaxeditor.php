<script>


 $(document).ready(function(){
		//  $('#progressbar').hide(); // Скрываем прогрессбар
    
		// Перед аякс запросом
		var predcallback = function (item){
			// Делаем полупрозрачным Элемент результата + помещаем сверху прогрессбар , после загрузки убираем полупрозрачность
		}
		
		// Перед загрузкой результата запроса в тег результата
		var predcallback2 = function (item)	{
			
			
		}
		
		// Перед загрузкой результата запроса в тег результата
		var postcallback = function (item)
		{  			

		 
		
			// ajax действия кнопки внутри контейнера 
			$('.fw_ajaxlink').click(function() 
			{
				var confirm = $(this).attr('data-confirm');
				var datacontainer = $(this).parents('tr:first');
				var url = $(this).attr('href');
				var data_id =$(this).attr('data-id') ;
				
				 // alert(data_id);
				// #ajaxitem_43
				
				
				if(confirm)
				{
					if (window.confirm(confirm)) 
					{
						
						$.ajax({  
						   type: "POST",  
						    dataType:"html", //формат данных
						    url: url,
						    success: function(response){  
							
								/* Скрытие контейнера если указано */
								if($(this).attr('data-actionhide'))
								{
									$(datacontainer).hide(500);
								}
								// alert(response);
								//Обновляем данные
								if( $(this).attr('data-reloadall') )
								{
									sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
								}
								else
								{
									sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
								}
								
								$('#ajaxitem_'+data_id).css('border-right','solid 5px #3C96E1');
						   }  
						 });  
					}
				}
				else
				{
					$.ajax({  
						  type: "POST",  
						  dataType:"html", //формат данных
						  url: url,
						  success: function(response){  
						  
							/* Скрытие контейнера если указано */
							if($(this).attr('data-actionhide'))
							{
								$(datacontainer).hide(500);
							}
						
							//alert(response);
							//Обновляем данные
							if( $(this).attr('data-reloadall') )
							{
								sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
							}
							else
							{
								sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
							}
							//alert('#ajaxitem_'+data_id);
							$('#ajaxitem_'+data_id).css('border-right','solid 5px #3C96E1');
						}  
					});  
				}
				
				
				return false;
			});
 
 
 
/* ПЛЮСИКИ РАЗВОРАЧИВАНИЯ ФОРМ */
// Наведение на плюсик
$('.aj_crud_rowplus').mouseover(function(e) 
{
	var tr = $(this).parents('tr:first');
	tr.addClass('fw_selrow');
});

// СНятие курсора с плюсика
$('.aj_crud_rowplus').mouseout(function(e) 
{
	var tr = $(this).parents('tr:first');
	tr.removeClass('fw_selrow');
});


// Клик по плюсику
$('.aj_crud_rowplus').click(function(e) 
{
	$('.fw_selrow').removeClass('fw_selrow');
	$('.fw_selrow2').removeClass('fw_selrow2');
	var tr = $(this).parents('tr:first');
	
	$(tr).addClass('fw_selrow2');
	$('.fw_hiderow').hide(); 	
	var hr = $(tr).next('.fw_hiderow');
	 
	if( hr.is(":hidden") )
	{
		hr.slideToggle(300);
		if(hr.attr('data-ajax'))
		{
			//alert(hr.attr('data-ajax'));
			$.ajax({
				  beforeSend: function() {
					$('.loader').show(1);
					 
				},
				complete: function() {
					$('.loader').hide(1);
					
					
				$('.fw_iframeajax').magnificPopup({type:'iframe',
				  removalDelay: 100,
				  fixedContentPos: true, 
				  disableOn:1,
				   tLoading: 'Загрузка #%curr%...',
					callbacks: {
					open: function() {
					  // Will fire when this exact popup is opened
					  // this - is Magnific Popup object
					},
					close: function() {
						// Перезагрузить отображение!
						sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек	 
						// ПЕРЕЗАГРУЗИТЬ ТОЛЬКО ИЗМЕНЕННЫЙ ЭЛЕМЕНТ!!! тут это развернутая строка!
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					// e.t.c.
				  }
				  });
					 
				},
				type: "GET",
				url: hr.attr('data-ajax'),
				success: function(data){
					hr.html('<td colspan="100">'+data+'</td>');
				}
			});
		}
	}
	else
	{
		//  hr.slideToggle(300);
	}
					
					
	 return false;
})

	
	
	
	/*

			// Раскрытие строк таблицы
			$('tr.dtable_ch').click(function(e) 
			{
					$('.fw_selrow').removeClass('fw_selrow');
					$(this).addClass('fw_selrow');
					
					$('.fw_hiderow').hide(); 
					
					var hr = $(this).next('.fw_hiderow');
					if( hr.is(":hidden") )
					{
						hr.slideToggle(300);
						if(hr.attr('data-ajax'))
						{
							//alert(hr.attr('data-ajax'));
							$.ajax({
								  beforeSend: function() {
									//$('.loader').show(1);
									 
								},
								complete: function() {
									//$('.loader').hide(1);
									 
								},
								type: "GET",
								url: hr.attr('data-ajax'),
								success: function(data){
									hr.html('<td colspan="100">'+data+'</td>');
								}
							});
						}
					
					}
					else
					{
						// hr.slideToggle(300);
					}
				
				
			});
			
		*/	
			 // Модальные окна редактирвоания
			   $('.fw_iframeajax').magnificPopup({type:'iframe',
				  removalDelay: 100,
				  fixedContentPos: true, 
				  disableOn:1,
				   tLoading: 'Загрузка #%curr%...',
					callbacks: {
					open: function() {
					  // Will fire when this exact popup is opened
					  // this - is Magnific Popup object
					},
					close: function() {
						// Перезагрузить отображение!
						sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек	 
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					// e.t.c.
				  }
				  });
				  
		}
		
		
 
  
  


  
  
 
		 
			$('#sel_dir').on('change', function() {
				 // relate_ajax_select(this,'');
			});
			
			// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
			$( "#filtrform input,#filtrform select" ).change(function() {
				// Меняем URL //////////////////////////////////////////////////
				var form = $('#filtrform');
				var action =  $(form).attr('action');
				
				if(!action){ action = 'ctrind.php';}

				// GET переменные
				urlParams = new URLSearchParams(window.location.search);
				params = {};
				urlParams.forEach((p, key) => {params[key] = p;});
				// params.ctr 
				// params.act
				if (history.pushState != undefined)// нормальный браузер
				{
 
					$('.fw_ff_h',form).attr("disabled", true); // Выкллючаем некоторые поля
					var form_arr = $(form).serializeArray();
					$('.fw_ff_h',form).attr("disabled", false);// Вкллючаем обратно поля
					
					var formdata = '';
					form_arr.forEach(function(item, i, arr) 
					{
						//  alert( i + ": " + item + " (массив:" + arr + ")" );
						if(item.value && item.value!=0)
						{
							// console.log(item.name);
							formdata = formdata + '&'+item.name+'='+item.value;
						}
						if(!params.act){params.act='index';}
					});
					history.pushState({}, '', action+'?ctr='+params.ctr+'&act='+params.act+'&'+formdata);
					// console.log(action+'?ctr='+params.ctr+'&act='+params.act+'&'+formdata);
				}
				////////////////////////////////////////////////////////////
				sendAjaxForm( 'fw_ajaxdata' , 'filtrform','',0,'',predcallback,predcallback2,postcallback); // 
			});
			
			
			
			// Текстовый поиск с задержкой 1  сек при вводе 
			 $('#search').on('keyup', function(){
					var $this = $(this);
					var $delay = 1000;
					clearTimeout($this.data('timer'));
					$this.data('timer', setTimeout(function(){
							$this.removeData('timer');
							// обновляем данные
							sendAjaxForm( 'fw_ajaxdata' , 'filtrform','',0,'',predcallback,predcallback2,postcallback); //
					}, $delay));
			});
					
		
		
		

		// СОртировка 
		$('.stat-table th a').on('click', function (e) {
			form = $(this).parents('form:first');
			form_id = $(form).attr('id');
			
			if ($(this).hasClass('top-active')) { 
			 
				$(this).removeClass('top-active');
				
				$('#ajaxcontent a').removeClass('top-active');
				$('#ajaxcontent a').removeClass('bottom-active');
			 
				 $('#order_filed').val( $(this).attr('data-filed') );
				 $('#order_asc').val(1);
				 $('#order_asc').change();
				 
				$(this).addClass('bottom-active');
		 
			} else {
				 $(this).removeClass('bottom-active');  
				
				$('#ajaxcontent a').removeClass('top-active');
				$('#ajaxcontent a').removeClass('bottom-active');
			 
			 
				$('#order_filed').val( $(this).attr('data-filed') );
				$('#order_asc').val(0);
				
				
				$('#order_asc').change();
				$(this).addClass('top-active');
				
			}
			 e.preventDefault();
		});
		
		
		
		
		
		 
	// Селект разделов - стартовая загрузка
	//  sendAjaxForm( 'sel_dir' , 'filtrform' , '/sahmatka/ajax_router.php?ctr=<?=$this->ctr?>&act=sel_dir',1,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
		  
	// Контент - стартовая загрузка
    sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
	
	
		// Запрещаем отправку формы поиска по интер (так как там брад)
		$('#filtrform').submit(function(event) {
			//event.preventDefault();
			return false;
			//window.history.back();
		});
		    
     })
</script>