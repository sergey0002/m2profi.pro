
		// Перед аякс запросом
		var predcallback = function (item){}
		
		// Перед загрузкой результата запроса в тег результата
		var predcallback2 = function (item)	{}
		
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
 
			// Раскрытие строк таблицы
			$('tr.akk').click(function() 
			{
				$('.akk_slide').hide(1); 
				$(this).next('.akk_slide').toggle(300);
			});
			
			
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
		
		 
		// Стартовая загрузка ajax - нельзя ставить в   $(document).ready иначе зациклится
		$(window).load(function() {
		  // Селект разделов - стартовая загрузка
		  sendAjaxForm( 'sel_dir' , 'filtrform' , '/admin/ajax_router.php?ctr=news&act=sel_dir',1,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
		  
		  // Контент - стартовая загрузка
		    sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback); // Грузим содержимое селек
		});


		  $(document).ready(function() {
			$('#sel_dir').on('change', function() {
				 // relate_ajax_select(this,'');
			});
			
			// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
			$( "#filtrform input,#filtrform select" ).change(function() {
			  sendAjaxForm( 'fw_ajaxdata' , 'filtrform','',0,'',predcallback,predcallback2,postcallback); //  
			});
 	  
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
