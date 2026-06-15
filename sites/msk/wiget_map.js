
 
 // Карта без зума и открытия
 if(window.screen.width>1000)
 {
	  // $( "#open_modal" ).hide();
	  // umapx();
 }
 
 
  
 $.when( updatejsoon() ).done(function() {
	// $('#zoomcontainerx').smartZoom();
	// alert(2);
});

$( document ).ready(function() {
	
	
	
	// umapx();
	
		
});
  
 
		
 
	 $( "#mapplus" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
	   $('#zoomcontainerx').smartZoom("zoom", 0.5);
		   
	});
	
	
	 $( "#mapminus" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
	    $('#zoomcontainerx').smartZoom("zoom", -0.5);
		   
	});
	
 
	// Открытие карты 
	$( "#open_modal" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
	    var modal = $('#psevsdomodal');
		  // Окно закрыто обрабатываем клик
		  if(!modal.hasClass('psevsdomodal_open'))
		  {
			  // $( "#map" ).fadeOut(0); // Скрываем карту
			   modal.addClass('psevsdomodal_open'); // Помещаем ее в экран по высоте
			     $( "#open_modal" ).hide(300);
			   //  $( "#map" ).fadeIn(600); // Плавно показываем
			     $("#close_modal").show(300);
				$('html').css({
					//overflow: 'hidden',
					//height: '100%'
				});
			
			
			
			
				  
			  
			 return false;
		  };
		   
	});
	
	// Закрытие карты
	$( "#close_modal" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
 
	    var modal = $('#psevsdomodal');
		  // Окно закрыто обрабатываем клик
		  if(modal.hasClass('psevsdomodal_open'))
		  {
			   $( "#map" ).fadeOut(0); // Скрываем карту
			 //$('#zoomcontainerx').smartZoom();
			 
			 if($('#zoomcontainerx').smartZoom('isPluginActive'))
				  {
						  $('#zoomcontainerx').smartZoom('destroy'); 
				  }
				  
				  
			   $('#zoomcontainerx').css("transform",''); //Сброс зума
				$( "#map" ).fadeIn(600); // Плавно показываем
			   modal.removeClass('psevsdomodal_open');
			   $( "#close_modal" ).hide(300);
			    $( "#open_modal" ).show(300);
				
				$('html').css({
					//overflow: 'scroll',
				//	height: '100%'
				});
				 umapx();
			   return false;
		  };
		  
		  
	});
 
 
 
 
 
  

 // ОБРАБОТКА КЛИКОВ  и толтипсы =  включаем после загрузки данных только в методе загрузки
function umapx()
{ 
	// return false; ///  ВЫРУБАЕМ КАРТУ (КЛИКИ ПО КАРТЕ)
		  
		var dragging = false;
		$(".scheme polygon").on("mousedown touchstart", function(e) {
		console.log('click');
		 if(( e.which ==1 || e.which ===0 ) && $(this).hasClass("insale") ) // только левая
		 {	  
		  var x = e.screenX;
		  var y = e.screenY;
 
		  dragging = false;
 
		  $(".scheme polygon").on("mousemove touchmove", function(e) {
 
			 // console.log('123');
			if (Math.abs(x - e.screenX) > 10 || Math.abs(y - e.screenY) > 10) {
			  dragging = true;
			 // console.log(1);
			}
		  });
		 }
		 else
		 {
			 dragging = false;
			// console.log(2);
		 }
		});

			// Перетаскивание на тач устройствах
			$('.scheme polygon').on('touchmove', function() {
				 dragging = true;
			});
 

		// КЛики по полигонам ОТПУСКАНИЕ КНОПКИ МЫШИ
		$(".scheme polygon").on("mouseup touchend", function(e) 
		{  
		 
		if(!$(this).attr('data-num')){return;} //нет номера участка 
			if($(this).attr('data-status')!="2" && $(this).attr('data-status')!="0"){return;} //Статус
		if(e.which == 3){return;} // не правая кнопка
		//alert(1);
		  $(".scheme polygon").off("mousemove");
		  
		if(false==dragging) // Если не перетаскивание  (перетаскивание меньше 10 писселей при клике)
		{
			// ФОрма брони / редактирования
			$.magnificPopup.open({
			  items: {
				src: 'https://msk.m2profi.pro/sahmatka/iframe_router.php?ctr=landplots&act=order&polygon_id='+$(this).attr('data-id')+'&a=1'
			  },
			   fixedContentPos: true,
			   type: 'iframe',
			   callbacks: {
					close: function(){
						//updatejsoon(); // Обновлять инфу при закрытии окна
					},
					beforeAppend:function() 
					{
						  $('[relmap~=tooltip]').tooltipster('close');
						//this.content.find('iframe').on('load', function() {
						 //var h = this.contentWindow.document.body.offsetHeight + 'px';
						// alert(h);
						//});
						//$('iframe').load(function() {
							//alert(1);
							//this.style.height =
							//
						//});
					}
			  }
			}, 0);
			
			$('.scheme-popup').hide();
			$('.scheme-item[data-id=' + $(this).data('id') + ']').trigger('click');
		}
		
		});
	
	
	
		//if(window.screen.width>1000)
		//{
		 
		// $('[relmap~=tooltip]').tooltipster('destroy');
		 
		 	$('[relmap~=tooltip]').tooltipster({
				debug: false,
				theme: 'Borderless',
				'maxWidth': 270, // set max width of tooltip box
				'minWidth': 270, // set max width of tooltip box
				contentAsHTML: true, // set title content to html
				trigger: 'custom', // add custom trigger
				triggerOpen: { // open tooltip when element is clicked, tapped (mobile) or hovered
					   click: true,
					   tap: false,
					   mouseenter: true
					   },
					   triggerClose: { // close tooltip when element is clicked again, tapped or when the mouse leaves it
						   click: true,
						   scroll: false, // ensuring that scrolling mobile is not tapping!
						   tap: true,
						   mouseleave: true
					   }
				});
			 
			 /*
			$('[relmap~=tooltip]').tooltipster({
				debug: false,
			theme: 'Borderless',
			'maxWidth': 300, // set max width of tooltip box
			contentAsHTML: true, // set title content to html
			trigger: 'custom', // add custom trigger
			triggerOpen: { // open tooltip when element is clicked, tapped (mobile) or hovered
					   click: true,
					   tap: true,
					   mouseenter: true
				   },
				   triggerClose: { // close tooltip when element is clicked again, tapped or when the mouse leaves it
					   click: true,
					   scroll: false, // ensuring that scrolling mobile is not tapping!
					   tap: false,
					   mouseleave: true
				   }
			});
			 */
		//}
  
  
  
		//var scroll_zoom = new ScrollZoom($('#zoomcontainer'),2,0.03)
}
	 

 




// Загружаем данные jsoon в DOM карты
function updatejsoon()
{	
	$.ajax({
		url: 'https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jsoondata&map_id=1',
		method: 'get',
		dataType: 'json',
		success: function(data)
		{
			console.log(data);
			$.each(data, function(key, val) 
			{
				if(val.num) // Только если в JSOON номер участка
				{
					var polygon = $('#polygon'+key);
					///$('#polygon'+key).css('opacity','0.5'); // Задаем прозрачность блоков ид которых есть в базе
					polygon.css('fill',val.status_color); // Задаем прозрачность блоков ид которых есть в базе
					polygon.css('cursor','pointer'); // Задаем прозрачность блоков ид которых есть в базе
					
					polygon.addClass(val.class);
					
					polygon.attr('data-num',val.num); // добавляем номер цчастка в атрибут 
					polygon.attr('data-status',val.status); // добавляем номер цчастка в атрибут 
					
					polygon.attr('title',val.tooltip);
					polygon.attr('relmap','tooltip');
				}
			});
				console.log('загрузка данных окончена');
		},
		complete:function(data){
			console.log('Выполение upmax');
			//return false; ///  ВЫРУБАЕМ ВСПЛЫВАЮЩИЕ 
			
			
			 if($('#zoomcontainerx').smartZoom('isPluginActive'))
				  {
						  $('#zoomcontainerx').smartZoom('destroy'); 
				  }
				 else
				 {
					 $.when($('#zoomcontainerx').smartZoom({'maxScale':6, scrollEnabled:false,mouseEnabled : true}) ).done(function() {
						 umapx();
					});  
				 }
				 
				  
			
			 
			
		}
	});
	return true;
} 
 
