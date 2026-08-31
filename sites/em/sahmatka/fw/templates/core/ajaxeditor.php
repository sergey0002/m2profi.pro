<script>


 $(document).ready(function(){
		var fwLazyIo = null;
		var fwLazyLoading = false;

		function fwCrudLazyOn() {
			return $('#filtrform').attr('data-lazy') !== '0';
		}
		function fwCrudUseButton() {
			return $('#filtrform').attr('data-more-button') === '1';
		}
		function fwCrudPageSize() {
			var n = parseInt($('#fw_crud_page_size').val(), 10);
			if (!n) { n = parseInt($('#filtrform').attr('data-page-size'), 10) || 20; }
			return n;
		}
		function fwCrudResetStart() {
			$('#fw_crud_start').val(0);
		}
		function fwCrudHasMore() {
			var $m = $('#fw_ajaxdata .fw_lazy_mark, #fwcrudtable .fw_lazy_mark').last();
			return $m.length && String($m.attr('data-has-more')) === '1';
		}
		function fwLazyScrollRoot(el) {
			var node = el ? el.parentElement : null;
			while (node && node !== document.body && node !== document.documentElement) {
				var st = window.getComputedStyle(node);
				var oy = st.overflowY || st.overflow;
				if (oy === 'auto' || oy === 'scroll') {
					return node;
				}
				node = node.parentElement;
			}
			return null;
		}
		function fwCrudLastVisible() {
			var el = fwCrudLastDataRow().get(0);
			if (!el) { return false; }
			var r = el.getBoundingClientRect();
			var vh = window.innerHeight || document.documentElement.clientHeight;
			return r.top < vh + 120 && r.bottom > -80;
		}
		function fwCrudLastDataRow() {
			var $root = $('#fw_ajaxdata');
			var $rows;
			if ($root.is('tbody')) {
				$rows = $root.children('tr:not(.fw_hiderow):not(.fw_lazy_mark)');
			} else {
				$rows = $('#fwcrudtable tbody').children('tr:not(.fw_hiderow):not(.fw_lazy_mark)');
			}
			return $rows.last();
		}
		function fwLazyDisconnect() {
			if (fwLazyIo) {
				fwLazyIo.disconnect();
				fwLazyIo = null;
			}
			$(window).off('scroll.fwlazy');
			$('.fw-lazy-scroll').off('scroll.fwlazy').removeClass('fw-lazy-scroll');
		}
		function fwLazyObserve() {
			fwLazyDisconnect();
			if (!fwCrudLazyOn() || !fwCrudHasMore()) {
				$('#fw_lazy_more_wrap').hide();
				return;
			}
			$('#fw_lazy_more_wrap').show();
			if (fwCrudUseButton()) {
				return;
			}
			var last = fwCrudLastDataRow().get(0);
			if (!last) { return; }
			var rootEl = fwLazyScrollRoot(last);
			if (typeof IntersectionObserver !== 'undefined') {
				fwLazyIo = new IntersectionObserver(function (entries) {
					if (fwLazyLoading) { return; }
					if (entries[0] && entries[0].isIntersecting) {
						fwCrudLoadMore();
					}
				}, { root: rootEl, rootMargin: '120px', threshold: 0 });
				fwLazyIo.observe(last);
			}
			var $sc = rootEl ? $(rootEl).addClass('fw-lazy-scroll') : $(window);
			$sc.on('scroll.fwlazy', function () {
				if (fwLazyLoading || !fwCrudHasMore()) { return; }
				if (fwCrudLastVisible()) {
					fwCrudLoadMore();
				}
			});
			setTimeout(function () {
				if (!fwLazyLoading && fwCrudHasMore() && fwCrudLastVisible()) {
					fwCrudLoadMore();
				}
			}, 50);
		}
		function fwCrudReload() {
			fwCrudResetStart();
			fwLazyDisconnect();
			sendAjaxForm( 'fw_ajaxdata' , 'filtrform' , '',0,'',predcallback,predcallback2,postcallback);
		}
		function fwCrudLoadMore() {
			if (!fwCrudLazyOn() || fwLazyLoading || !fwCrudHasMore()) { return; }
			fwLazyLoading = true;
			fwLazyDisconnect();
			var n = fwCrudPageSize();
			var start = parseInt($('#fw_crud_start').val(), 10) || 0;
			$('#fw_crud_start').val(start + n);
			$('#progressbar').show();
			$.ajax({
				url: $('#filtrform').attr('data-ajaxurl'),
				type: 'POST',
				dataType: 'html',
				data: $('#filtrform').serialize(),
				success: function (html) {
					$('.fw_lazy_mark').remove();
					if ($('#fw_ajaxdata').is('tbody')) {
						$('#fw_ajaxdata').append(html);
					} else {
						var $box = $('<div/>').html(html);
						var $table = $box.find('table').first();
						if ($table.length) {
							$('#fwcrudtable tbody').append($table.find('tbody').children());
						} else {
							$('#fwcrudtable tbody').append(html);
						}
					}
				},
				complete: function () {
					fwLazyLoading = false;
					$('#progressbar').hide();
					if (typeof postcallback === 'function') { postcallback(); }
				}
			});
		}

		$('#fw_lazy_more').on('click', function (e) {
			e.preventDefault();
			fwCrudLoadMore();
		});
    
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
			$('.fw_ajaxlink').off('click.fwcrud').on('click.fwcrud', function() 
			{
				var confirm = $(this).attr('data-confirm');
				var datacontainer = $(this).parents('tr:first');
				var url = $(this).attr('href');
				var data_id =$(this).attr('data-id') ;

				if(confirm)
				{
					if (window.confirm(confirm)) 
					{
						$.ajax({  
						   type: "POST",  
						    dataType:"html",
						    url: url,
						    success: function(response){  
								if($(this).attr('data-actionhide'))
								{
									$(datacontainer).hide(500);
								}
								fwCrudReload();
								$('#ajaxitem_'+data_id).css('border-right','solid 5px #3C96E1');
						   }  
						 });  
					}
				}
				else
				{
					$.ajax({  
						  type: "POST",  
						  dataType:"html",
						  url: url,
						  success: function(response){  
							if($(this).attr('data-actionhide'))
							{
								$(datacontainer).hide(500);
							}
							fwCrudReload();
							$('#ajaxitem_'+data_id).css('border-right','solid 5px #3C96E1');
						}  
					});  
				}
				return false;
			});
 
 
 
/* ПЛЮСИКИ РАЗВОРАЧИВАНИЯ ФОРМ */
// Наведение на плюсик
$('.aj_crud_rowplus').off('mouseover.fwcrud').on('mouseover.fwcrud', function(e) 
{
	var tr = $(this).parents('tr:first');
	tr.addClass('fw_selrow');
});

// СНятие курсора с плюсика
$('.aj_crud_rowplus').off('mouseout.fwcrud').on('mouseout.fwcrud', function(e) 
{
	var tr = $(this).parents('tr:first');
	tr.removeClass('fw_selrow');
});


// Клик по плюсику
$('.aj_crud_rowplus').off('click.fwcrud').on('click.fwcrud', function(e) 
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
						fwCrudReload();
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
						fwCrudReload(); 
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					// e.t.c.
				  }
				  });
				fwLazyObserve();
		}
		
		
 
  
  


  
  
 
		 
			$('#sel_dir').on('change', function() {
				 // relate_ajax_select(this,'');
			});
			
			// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
			$( "#filtrform input,#filtrform select" ).change(function() {
				if ($(this).attr('id') === 'fw_crud_start' || $(this).attr('id') === 'fw_crud_page_size') {
					return;
				}
				fwCrudResetStart();
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
				fwCrudReload();
			});
			
			
			
			// Текстовый поиск с задержкой 1  сек при вводе 
			 $('#search').on('keyup', function(){
					var $this = $(this);
					var $delay = 1000;
					clearTimeout($this.data('timer'));
					$this.data('timer', setTimeout(function(){
							$this.removeData('timer');
							// обновляем данные
							fwCrudReload();
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
    fwCrudReload();
	
	
		// Запрещаем отправку формы поиска по интер (так как там брад)
		$('#filtrform').submit(function(event) {
			//event.preventDefault();
			return false;
			//window.history.back();
		});
		    
     })
</script>