
<script type="text/javascript">
 
		console.log('map Начало');
		// закрыть все подсказки
		function closett()
		{
			var instances = $.tooltipster.instances();
			$.each(instances, function(i, instance){
				instance.close();
			});
		}


 		function zoominit(imageElement) {
			
			map_id =  $(imageElement).closest(".myContent").attr('id') ; // Получаем первый родительский див с классом myContent и его id  к которому будем применять зум
		   // width="1397" height="1276"/
			var wzoom = WZoom.create('#'+map_id, {
				type: 'html',
				maxScale:1.2,
				width: imageElement.naturalWidth,
				height: imageElement.naturalHeight,
				zoomOnClick:false,
				disableWheelZoom:true, // Отключить скролл зум
				dragScrollableOptions: {
					onGrab: function () {
						//document.getElementById('myViewport').style.cursor = 'grabbing';
						closett();
					},
					onDrop: function () {
						//document.getElementById('myViewport').style.cursor = 'grab';
						closett();
					}
				} 
			});
			
			// wzoom.maxZoomUp();
			 
			// Кнопки масштаба
			document.querySelector('[data-zoom-up]').addEventListener('click', function () {
				wzoom.zoomUp();
			});

			document.querySelector('[data-zoom-down]').addEventListener('click', function () {
				wzoom.zoomDown();
			});
			
			window.addEventListener('resize', function () {
				wzoom.prepare();
			});

		}
		
		
		
 


 

	document.addEventListener('DOMContentLoaded', function () {
	console.log('map DOMContentLoaded');
		//var imageElement = document.getElementById('mapbg') ;
		
		var imageElements = document.getElementsByClassName('mapbg');
		for (var i = 0; i < imageElements.length; i++) {
			imageElement = imageElements.item(i);
			
			//alert(imageElement);
			if (imageElement.complete)
			{
				console.log('Загружена картинка');
				zoominit(imageElement);
			} 
			else 
			{
				console.log('нет - Загружена картинка');

				imageElement.onload = function() {
				  console.log('Картинка наконец загружена');
				  zoominit(imageElement);
				};
			}
		}
	});
 
 
   
 
	// Открытие карты 
	$( "#open_modal" ).click(function(){  
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
	$( "#close_modal" ).click(function(){  
 
	    var modal = $('#psevsdomodal');
		  // Окно закрыто обрабатываем клик
		  if(modal.hasClass('psevsdomodal_open'))
		  {
			   $( "#map" ).fadeOut(0); // Скрываем карту
			 //$('#zoomcontainerx').smartZoom();
			 
			 
				  
			   $('#zoomcontainerx').css("transform",''); //Сброс зума
				$( "#map" ).fadeIn(600); // Плавно показываем
			   modal.removeClass('psevsdomodal_open');
			   $( "#close_modal" ).hide(300);
			    $( "#open_modal" ).show(300);
				
				$('html').css({
					//overflow: 'scroll',
				//	height: '100%'
				});
			//	 umapx();
			   return false;
		  };
		  
		  
	});
 
 
 

		
		
		
 
 // Чек лендплот check=1 отметить check=2 убрать отметку 
 function check_lp( num , change_check , check = "0" )
 {
	  
	 
	 /*
	 При клике 
	 */
	pol = $( 'path[data-num="'+num+'"]' ); // Полигон участка 
	 
	 console.log(num);
	  
	// console.log(pol);
	if (check)
	{
		console.log('check');
		if(change_check)
		{
			$('#lp_check'+num).attr('checked', false );
		}
		pol.css('opacity','0.5');  
		pol.css('stroke','#FFF'); 
		pol.css('stroke-width','1'); 	
		//pol.attr('data-check','false'); 	
	} 
	else 
	{
		console.log('NOT check');
		if(change_check) // при обратной обработке чекбокса задается false
		{
			$('#lp_check'+num).attr('checked',  true);
		}
		
		pol.css('opacity','0.8');  
		pol.css('stroke','#000000');
		pol.css('stroke-width','2'); 			
		
		//pol.attr('data-check','true'); 	
		$('#map_landplots_check_num').append(num+','); // Чекбукс участка				
	}
 }
 
 
 
 
 
 
 
// ОБРАБОТКА КЛИКОВ  и толтипсы =  включаем после загрузки данных только в методе загрузки
function umapx(map_id,numbers=false)
{ 
	// return false; ///  ВЫРУБАЕМ КАРТУ (КЛИКИ ПО КАРТЕ)
		
		var dragging = false;
		$("#map__" +map_id +" scheme path").on("mousedown touchstart", function(e) {
		console.log('mowsedown');
		 if(( e.which ==1 || e.which ===0 )) // только левая
		 {	  
		  var x = e.screenX;
		  var y = e.screenY;
 
		  dragging = false;
 
		  $("#map__" +map_id +" .scheme path").on("mousemove touchmove", function(e) {
			   console.log('move');
			if (Math.abs(x - e.screenX) > 2 || Math.abs(y - e.screenY) > 2) {
			  dragging = true;
			   console.log(1);
			}
		  });
		 }
		 else
		 {
			 //dragging = false;
			  console.log(2);
		 }
		});

			// Перетаскивание на тач устройствах
			$("#map__" +map_id +' .scheme path').on('touchmove', function() {
				 dragging = true;
			});
 
  


		// КЛики по полигонам ОТПУСКАНИЕ КНОПКИ МЫШИ
		$("#map__" +map_id +" .scheme path").on("mouseup touchend", function(e) 
		{

		if( $("#map_editmode").prop('checked') ) // Включен режим редактирования 
		{
			num = $(this).attr('data-num');
			 
			check_lp( num , true);
 
			return false;
		}
		
		 // if( !$(this).hasClass("insale") ){ return; }  // только в продаже
		
		//if(!$(this).attr('data-num')){return;} //нет номера участка 
		//if($(this).attr('data-status')!="2" && $(this).attr('data-status')!="0"){return;} //Статус
		
		 if(e.which == 3){return;} // не правая кнопка
		//alert(1);
		  $("#map__" +map_id +" .scheme path").off("mousemove");
 
		if(false===dragging) // Если не перетаскивание  (перетаскивание меньше 10 писселей при клике)
		{ 
			closett(); // Закрыть подсказки
			// ФОрма брони / редактирования
			$.magnificPopup.open({
			  items: {
				src: 'https://msk.m2profi.pro/sahmatka/iframe_router.php?ctr=landplots&act=order&polygon_id='+$(this).attr('data-id')+'&map_id='+map_id+'&lp_id='+$(this).attr('data-lp_id')+'&a=/sites/gl/sahmatka/ctrind.php'
			  },
			  
		 
			   fixedContentPos: false,
			   removalDelay: 300,
			    mainClass: 'mfp-width-zoom',
			   type: 'iframe',
			   callbacks: {
					close: function(){
						updatejsoon(map_id,numbers); // Обновлять инфу при закрытии окна
					},
					beforeAppend:function() 
					{
						 //!!! $('[relmap~=tooltip]').tooltipster('close');
					 
					}
			  }
			}, 0);
			
			$("#map__" +map_id +' .scheme-popup').hide();
			$("#map__" +map_id +' .scheme-item[data-id=' + $(this).data('id') + ']').trigger('click');
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
			 
			 
}
	 


 

 
 
//label1 = document.querySelector("#label1");
//addLabelText(label1, "Something");
function addLabelText(bgPath, labelText)
{
	if(!bgPath){return;}
   let bbox = bgPath.getBBox();
   let x = bbox.x + bbox.width / 2;
   let y = bbox.y + bbox.height / 2;
   
   // Create a <text> element
   let textElem = document.createElementNS(bgPath.namespaceURI, "text");
   textElem.setAttribute("x", x);
   textElem.setAttribute("y", y);
   // Centre text horizontally at x,y
   textElem.setAttribute("text-anchor", "middle");
   // Give it a class that will determine the text size, colour, etc
   textElem.classList.add("label-text");
   // Set the text
   textElem.textContent = labelText;
   // Add this text element directly after the label background path
   bgPath.before(textElem);
}

// 
/*
Класс цены добавляем дата атрибут

Режимы редактирования чекбукс если стоит то клики по другому обрабатываются (Отмечаются чеки )
*/



// Уникальные значения массива
function unique(arr) {
  let result = [];

  for (let str of arr) {
    if (!result.includes(str)) {
      result.push(str);
    }
  }
  return result;
}




// Загружаем данные jsoon в DOM карты
function updatejsoon(map_id,numbers=false)
{	

// Очистка полей (чекбуксов итп динамически добавляемых)
$('#map_landplots_check').html('');
$('#map_actions').html('');



var uprice_area = new Array(); // массив уникальных цен за сотку



  $('#map__'+map_id+' .scheme text').hide();
	$.ajax({
		url: 'https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jsoondata&map_id='+map_id,
		method: 'get',
		dataType: 'json',
		success: function(data)
		{
			console.log(data);
			$.each(data, function(key, val) 
			{
				if(val.num) // Только если в JSOON номер участка
				{
					//var polygon = $('#polygon'+key);
					var polygon = $('#map__'+map_id+' path[data-id="'+key+'"]');
					
					///$('#polygon'+key).css('opacity','0.5'); // Задаем прозрачность блоков ид которых есть в базе
					polygon.css('fill',val.status_color); // Задаем прозрачность блоков ид которых есть в базе
					polygon.css('cursor','pointer'); // Задаем прозрачность блоков ид которых есть в базе
					
					if(numbers)
					{
						addLabelText(polygon[0],val.num); // Вставляем номер участка
						polygon.css('opacity','0.5'); //НЕ прозрачный так как номера
					}
					 
					polygon.addClass(val.class);
					polygon.attr('data-num',val.num); // добавляем номер цчастка в атрибут 
					polygon.attr('data-status',val.status); // добавляем номер цчастка в атрибут 
					polygon.attr('data-price_area',val.price_area); // добавляем цену сотки в атрибут 
					 
					uprice_area.push(val.price_area); // добавляем цену за сотку в массив
 
					polygon.attr('data-map_id',val.map_id); // добавляем номер цчастка в атрибут
					polygon.attr('data-lp_id',val.lp_id); // добавляем номер цчастка в атрибут
					polygon.attr('title',val.tooltip);
					polygon.attr('relmap','tooltip');
				  
					$('#map_landplots_check').append(' <input type="checkbox" id="lp_check'+val.num+'" class="lp_check" name="check_landplot" value="'+val.num+'"/> '); // Чекбукс участка
					 
				}
			});
				console.log('загрузка данных окончена');
		},
		complete:function(data){
			 
			 
		/////////////////////////////////////////  НАВИГАЦИЯ И МАССОВАЯ ПРАВКА
		uprice_area = unique(uprice_area);	 // только уникальные цены
		
		uprice_area = uprice_area.sort(); // Сортировака массива по возростанию
		
		
		 // Обработка смены чекбокса
		  $('#map_actions').on('change', 'input[type="checkbox"]', function() {
 
			// alert($(this).val());
			if (event.target.checked) 
			{
			   // помечаем классом активносьти 
			   $( "path[data-price_area='"+$(this).val()+"']" ).css('opacity','0.7');  
			   $( "path[data-price_area='"+$(this).val()+"']" ).css('stroke','purple');
			   $( "path[data-price_area='"+$(this).val()+"']" ).css('stroke-width','2'); 			   
			} 
			else 
			{
			   // Снимаем класс активности 
			    $( "path[data-price_area='"+$(this).val()+"']" ).css('opacity','0.5');  
				$( "path[data-price_area='"+$(this).val()+"']" ).css('stroke','#FFF'); 
				$( "path[data-price_area='"+$(this).val()+"']" ).css('stroke-width','1'); 			   
			   
			}
		});


		for (let item of uprice_area) {
			  //map_actions
			  if(item =="0"){caption='Не задана';}
			  else{
				  caption=item;
			  }
			  if(item!=null && item>"0")
			  {
				$('#map_actions').append('<span><input type="checkbox" class="filtr_price_area" name="filtr_price_area" value="'+item+'" id="fpa'+item+'"/> <label for="fpa'+item+'">'+caption+'</label></span>&nbsp;&nbsp;&nbsp; ');
			  }
			console.log(item);
		}
		///////////////////////////////////////////


			 
		 // Обработка отметки чекбокса (обратная )
		 $('#map_landplots_check').on('change', 'input[type="checkbox"]', function() {
	 
			num = $(this).attr('value');
		 
 			if (event.target.checked)  // постановка чека
			{
				check_lp( num , false,false );
			}
			else //снятие  чека
			{
				check_lp( num ,false,true);
			}
		});
		  
		  
  
			 
		umapx(map_id,numbers);
			console.log('Выполение upmax');
			//return false; ///  ВЫРУБАЕМ ВСПЛЫВАЮЩИЕ 
			
			console.log('Вызов изменения размеров окна - перезагрузка zoom');
			// Событие изменение размера окна вызываем (чтобы перестроить зум)
			var evt = window.document.createEvent('UIEvents'); 
			evt.initUIEvent('resize', true, false, window, 0); 
			window.dispatchEvent(evt);
			$(window).trigger('resize');
		}
	});
	
	
	console.log('Вызов изменения размеров окна - перезагрузка zoom 2');
	// Событие изменение размера окна вызываем (чтобы перестроить зум)
	var evt = window.document.createEvent('UIEvents'); 
    evt.initUIEvent('resize', true, false, window, 0); 
    window.dispatchEvent(evt);
	$(window).trigger('resize');
	
	
 
	return true;
} 
 
 
 
 </script>