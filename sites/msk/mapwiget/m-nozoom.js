  
// закрыть все подсказки
function closett() {
    var instances = $.tooltipster.instances();
    $.each(instances, function(i, instance) {
        instance.close();
    });
}
 

 




function zoominit(imageElement) 
{ 
return;
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
    document.querySelector('[data-zoom-up]').addEventListener('click', function() {
       // zoomableElement.zoomable('zoomIn');
    });

    document.querySelector('[data-zoom-down]').addEventListener('click', function() {
      //  zoomableElement.zoomable('zoomOut');
    });

    window.addEventListener('resize', function() {
        //wzoom.prepare();
    });
}








document.addEventListener('DOMContentLoaded', function() {
    //var imageElement = document.getElementById('mapbg') ;
    //$('.mapbg').css('width','100%');
    var imageElements = document.getElementsByClassName('mapbg');
    for (var i = 0; i < imageElements.length; i++) {
        imageElement = imageElements.item(i);

        console.log(imageElement);
        if (imageElement.complete) {
            zoominit(imageElement);
            console.log('Загружена картинка');
        } else {
            console.log('нет - Загружена картинка');

            imageElement.onload = function() {
                console.log('Картинка наконец загружена');
                zoominit(imageElement);
            };
        }

    }


});










// ОБРАБОТКА КЛИКОВ  и толтипсы =  включаем после загрузки данных только в методе загрузки
function umapx(map_id, numbers = false) {
    // return false; ///  ВЫРУБАЕМ КАРТУ (КЛИКИ ПО КАРТЕ)

    var dragging = false;
    $("#map__" + map_id + " .scheme path").on("mousedown touchstart", function(e) {
        console.log('mowsedown');
        if ((e.which == 1 || e.which === 0)) // только левая
        {
            var x = e.screenX;
            var y = e.screenY;

            dragging = false;

            $("#map__" + map_id + " .scheme path").on("mousemove touchmove", function(e) {
                console.log('move');
                if (Math.abs(x - e.screenX) > 2 || Math.abs(y - e.screenY) > 2) {
                    dragging = true;
                    console.log(1);
                }
            });
        } else {
            //dragging = false;
            console.log(2);
        }
    });

    // Перетаскивание на тач устройствах
    $("#map__" + map_id + ' .scheme path').on('touchmove', function() {
        dragging = true;
    });




    // КЛики по полигонам ОТПУСКАНИЕ КНОПКИ МЫШИ
    $("#map__" + map_id + " .scheme path").on("mouseup touchend", function(e) {

        // if( !$(this).hasClass("insale") ){ return; }  // только в продаже

        //if(!$(this).attr('data-num')){return;} //нет номера участка 
        //if($(this).attr('data-status')!="2" && $(this).attr('data-status')!="0"){return;} //Статус

        if (e.which == 3) {
            return;
        } // не правая кнопка
        //alert(1);
        $("#map__" + map_id + " .scheme path").off("mousemove");

        if (false === dragging) // Если не перетаскивание  (перетаскивание меньше 10 писселей при клике)
        {
            closett(); // Закрыть подсказки
            // ФОрма брони / редактирования

            //1 Грузим аяксом данные в див с формой
            //2 Открываем форму
            //3 добавить обработку формы!

            $.getJSON('https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jsoon_landplot&polygon_id=' + $(this).attr('data-id') + '&map_id=' + map_id)
                .then(function(data) {
                        $.each(data, function(index, value) {
                            // 
                            var obj = $('.fwajaxdata[data_id="' + index + '"]');
                            if (obj.length) {
                                console.log('ЗАПОЛНЕН ЭЛЕМЕНТ ШАБЛОНА Индекс: ' + index + '; Значение: ' + value);
                                obj.html(value);
                            } else {
                                console.log('НЕТ ЭЛЕМЕНТА ШАБЛОНА Индекс: ' + index + '; Значение: ' + value);
                            }
                        });
                    },
                    function() {
                        console.log('Ошибка загрузки данных'); // вывод сообщения об ошибке в консоль в случае ошибки
                    });


            $.ajax({
                type: "POST",
                url: 'https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=public_form&polygon_id=' + $(this).attr('data-id') + '&map_id=' + map_id,
                data: {
                    action: 'ajax_action',
                    postid: $(this).data('id'),
                    //$(this).data() works because it's a standard AJAX call
                },
                success: function(data) {

                    //alert(data);
                    $.magnificPopup.open({

                        items: {
                            src: ' ' + data + ' ',
                            type: 'inline',
                            modal: true,


                        }
                    })
                }
            });



            $(document).on("submit", '#lpform', function(event) {

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'post',
                    dataType: 'html',
                    data: $(this).serialize(),
                    success: function(data) {
                        //$('#message').html(data);
                        $('#lpform').hide(200);
                        $('#lpform_ok').show(200);
                    }
                });





                //	alert("Ваше обращение отправлено!");


                //$.magnificPopup.close();
                return false;
            });



            /*
            
            		$.magnificPopup.open({
            		  items: {
            			src: 'https://msk.m2profi.pro/sahmatka/iframe_router.php?ctr=landplots&act=order&polygon_id='+$(this).attr('data-id')+'&map_id='+map_id
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
            		
            		
            		*/
            $("#map__" + map_id + ' .scheme-popup').hide();
            $("#map__" + map_id + ' .scheme-item[data-id=' + $(this).data('id') + ']').trigger('click');
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

 const event = new Event("mapload"); // определяем объект события
    document.dispatchEvent(event); // генерируем событие для всего документа
}







//label1 = document.querySelector("#label1");
//addLabelText(label1, "Something");
function addLabelText(bgPath, labelText) {
    if (!bgPath) {
        return;
    }
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


// Загружаем данные jsoon в DOM карты
function updatejsoon(map_id, numbers = false) {
    $('#map__' + map_id + ' .scheme text').hide();
    $.ajax({
        url: 'https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jsoondata&map_id=' + map_id,
        method: 'get',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            $.each(data, function(key, val) {
                if (val.num) // Только если в JSOON номер участка
                {
                    //var polygon = $('#polygon'+key);
                    var polygon = $('#map__' + map_id + ' path[data-id="' + key + '"]');

                    ///$('#polygon'+key).css('opacity','0.5'); // Задаем прозрачность блоков ид которых есть в базе
                    polygon.css('fill', val.status_color); // Задаем прозрачность блоков ид которых есть в базе
                    polygon.css('cursor', 'pointer'); // Задаем прозрачность блоков ид которых есть в базе

                    if (numbers) {
                        addLabelText(polygon[0], val.num); // Вставляем номер участка
                        polygon.css('opacity', '0,6'); //НЕ прозрачный так как номера
                    }


                    polygon.addClass(val.class);

                    polygon.attr('data-num', val.num); // добавляем номер цчастка в атрибут 
                    polygon.attr('data-status', val.status); // добавляем номер цчастка в атрибут 
                    polygon.attr('data-map_id', val.map_id); // добавляем номер цчастка в атрибут
                    polygon.attr('title', val.tooltip);
                    polygon.attr('relmap', 'tooltip');
                }
            });
            console.log('загрузка данных окончена');
        },
        complete: function(data) {

            umapx(map_id, numbers);
            console.log('Выполение upmax');
            //return false; ///  ВЫРУБАЕМ ВСПЛЫВАЮЩИЕ 

            // Событие изменение размера окна вызываем (чтобы перестроить зум)
            var evt = window.document.createEvent('UIEvents');
            evt.initUIEvent('resize', true, false, window, 0);
            window.dispatchEvent(evt);
            $(window).trigger('resize');

        }
    });


    // Событие изменение размера окна вызываем (чтобы перестроить зум)
    var evt = window.document.createEvent('UIEvents');
    evt.initUIEvent('resize', true, false, window, 0);
    window.dispatchEvent(evt);
    $(window).trigger('resize');

    return true;


}













 
