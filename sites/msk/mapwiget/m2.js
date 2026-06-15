
// Подключение CSS
function loadCSS(filename) {
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = filename;
    document.getElementsByTagName("head")[0].appendChild(link);
}
// Подключение JS
function loadJS(filename) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = filename;
    document.getElementsByTagName("head")[0].appendChild(script);
}







// Пример использования:
$('#printButton').on('click', function() {
    //printUrlContent('https://msk.m2profi.pro/mapwiget/mapprint.php');
  //  $(document).printSpecificElement('#map__'+mapid);
});



/*
<!-- Стили нумерации участков -->
<style>
 .label-text {
	font-size: 9px;
	font-weight: bold;
	fill: #FFF; 
	transform: translate(0, 3px);
	font-family: 'Halvar', Arial, sans-serif;
}
</style>
	
<script>
    var mapid="58"; // id карты
</script>
<script type="text/javascript" src="https://msk.m2profi.pro/mapwiget/m.js"></script> 
*/


////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////


 




// закрыть все подсказки
function closett() {
    var instances = $.tooltipster.instances();
    $.each(instances, function(i, instance) {
        instance.close();
    });
}


// Печать произвольного элемента на странице!!!!!!!!!!
(function($) {
    $.fn.printSpecificElement = function(selector) {
        // Сохраняем все элементы
        var $elements = $('body').children().not(selector);
        var $targetElement = $(selector);

        // Сохраняем оригинальные стили и родительский элемент
        var originalStyles = $targetElement.attr('style') || '';
        var originalParent = $targetElement.parent();
        var originalNextSibling = $targetElement.next();

        // Перемещаем целевой элемент к корню документа
        $targetElement.appendTo('body');

        // Скрываем все элементы, кроме указанного селектора
        $elements.hide();

        // Применяем стили к указанному селектору
        $targetElement.css({
            'width': '100vw',
            'display': 'block',
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'z-index': '10000',
            'height': '100vh',
            'background': '#FFF'
        });

        // Открываем окно печати
        window.print();

        // Возвращаем целевой элемент на место
        if (originalNextSibling.length) {
            $targetElement.insertBefore(originalNextSibling);
        } else {
            $targetElement.appendTo(originalParent);
        }

        // Возвращаем все обратно
        $elements.show();
        $targetElement.attr('style', originalStyles);

        return this;
    };
}(jQuery));


// Пример использования:
// $(document).ready(function() {
//     $('#printButton').click(function() {
//         $(document).printSpecificElement('#targetElement');
//     });
// });








(function($) {
    $.fn.zoomable = function(options) {
        // Настройки по умолчанию, которые могут быть переопределены при инициализации плагина
        var settings = $.extend({
            zoomEnabled: true, // Включение/выключение зума
            minScale: 1, // Минимальный масштаб
            maxScale: 3, // Максимальный масштаб
            touchpadEnabled: true, // Включение/выключение обработки тачпада
            scrollEnabled: false // Включение/выключение обработки скролла
        }, options);

        var methods = {
            zoomIn: function() {
                this.each(function() {
                    var $this = $(this);
                    var scale = $this.data('zoom-scale') || 1;
                    scale = Math.min(scale + 0.05, settings.maxScale);
                    $this.data('zoom-scale', scale);
                    $this.css('transform', 'scale(' + scale + ')');
                    updateData.call($this);
                });
            },
            zoomOut: function() {
                this.each(function() {
                    var $this = $(this);
                    var scale = $this.data('zoom-scale') || 1;
                    scale = Math.max(scale - 0.05, settings.minScale);
                    $this.data('zoom-scale', scale);
                    $this.css('transform', 'scale(' + scale + ')');
                    updateData.call($this);
                });
            },
            enableTouchpad: function() {
                settings.touchpadEnabled = true;
            },
            disableTouchpad: function() {
                settings.touchpadEnabled = false;
            },
            enableScroll: function() {
                settings.scrollEnabled = true;
            },
            disableScroll: function() {
                settings.scrollEnabled = false;
            }
        };

        if (methods[options]) {
            return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
        }

        return this.each(function() {
            var $this = $(this); // Текущий элемент
            var scale = 1; // Текущий масштаб
            var startDistance = 0; // Начальное расстояние между касаниями для зума
            var isDragging = false; // Флаг для отслеживания состояния перетаскивания
            var lastMouseX, lastMouseY; // Координаты последней позиции мыши

            // Глобальные свойства класса
            var dataTop, dataBottom, dataLeft, dataRight, dataWidth, dataHeight, dataParentWidth, dataParentHeight;

            // Функция для установки трансформации масштаба элемента
            function setTransform() {
                $this.css('transform', 'scale(' + scale + ')');
                updateData(); // Обновляем данные элемента после изменения масштаба
            }

            // Обновляем данные элемента (top, bottom, left, right, width, height, parent-width, parent-height)
            function updateData() {
                var originalWidth = $this[0].getBoundingClientRect().width / scale; // Оригинальная ширина элемента
                var originalHeight = $this[0].getBoundingClientRect().height / scale; // Оригинальная высота элемента
                var scaledWidth = originalWidth * scale; // Ширина элемента с учетом масштаба
                var scaledHeight = originalHeight * scale; // Высота элемента с учетом масштаба

                var left = parseFloat($this.css('left')) || 0; // Текущая позиция left элемента
                var top = parseFloat($this.css('top')) || 0; // Текущая позиция top элемента
                var parentOffset = $this.parent().offset(); // Смещение родительского контейнера
                var elementOffset = $this.offset(); // Смещение элемента

                var parentWidth = $this.parent().width(); // Ширина контейнера
                var parentHeight = $this.parent().height(); // Высота контейнера

                var right = (parentOffset.left + parentWidth) - (elementOffset.left + scaledWidth); // Расстояние от правого края контейнера до правого края элемента
                var bottom = (parentOffset.top + parentHeight) - (elementOffset.top + scaledHeight); // Расстояние от нижнего края контейнера до нижнего края элемента

                // Обновляем глобальные свойства класса с учетом масштаба и размеров родительского контейнера
                dataTop = elementOffset.top - parentOffset.top;
                dataBottom = bottom;
                dataLeft = elementOffset.left - parentOffset.left;
                dataRight = right;
                dataWidth = scaledWidth;
                dataHeight = scaledHeight;
                dataParentWidth = parentWidth;
                dataParentHeight = parentHeight;

                // Обновляем атрибуты данных элемента
                $this.attr('data-top', dataTop);
                $this.attr('data-bottom', dataBottom);
                $this.attr('data-left', dataLeft);
                $this.attr('data-right', dataRight);
                $this.attr('data-width', dataWidth);
                $this.attr('data-height', dataHeight);
                $this.attr('data-parent-width', dataParentWidth);
                $this.attr('data-parent-height', dataParentHeight);
            }

            // Обработчик события прокрутки колесика мыши для зума
            function onWheel(event) {
                if (!settings.zoomEnabled || !settings.scrollEnabled) return; // Если зум или скролл отключены, ничего не делаем

                event.preventDefault(); // Предотвращаем дефолтное поведение прокрутки
                var delta = event.originalEvent.deltaY; // Получаем направление прокрутки
                if (delta < 0) {
                    scale += 0.1; // Увеличиваем масштаб
                } else {
                    scale -= 0.1; // Уменьшаем масштаб
                }
                // Ограничиваем масштаб минимальным и максимальным значениями
                scale = Math.min(Math.max(scale, settings.minScale), settings.maxScale);
                setTransform(); // Применяем новый масштаб
            }

            // Функция для получения расстояния между двумя касаниями
            function getDistance(touches) {
                var dx = touches[0].pageX - touches[1].pageX;
                var dy = touches[0].pageY - touches[1].pageY;
                return Math.sqrt(dx * dx + dy * dy);
            }

            // Обработчик события начала касания
            function onTouchStart(event) {
                if (!settings.touchpadEnabled) return; // Если обработка тачпада отключена, ничего не делаем

                if (event.touches.length === 2) {
                    // Если два касания, начинаем отслеживать расстояние для зума
                    startDistance = getDistance(event.touches);
                } else if (event.touches.length === 1) {
                    // Если одно касание, начинаем отслеживать перетаскивание
                    lastMouseX = event.touches[0].pageX;
                    lastMouseY = event.touches[0].pageY;
                    isDragging = true;
                }
            }

            // Обработчик события перемещения касания
            function onTouchMove(event) {
                if (!settings.zoomEnabled || !settings.touchpadEnabled || !isDragging) return; // Если зум или перетаскивание или тачпад отключены, ничего не делаем

                if (event.touches.length === 2) {
                    // Если два касания, выполняем зум
                    event.preventDefault();
                    var currentDistance = getDistance(event.touches);
                    var diff = currentDistance - startDistance;
                    scale += diff / 200; // Регулируем чувствительность зума
                    // Ограничиваем масштаб минимальным и максимальным значениями
                    scale = Math.min(Math.max(scale, settings.minScale), settings.maxScale);
                    startDistance = currentDistance;
                    setTransform(); // Применяем новый масштаб
                } else if (event.touches.length === 1 && isDragging) {
                    // Если одно касание и идет перетаскивание, перемещаем элемент
                    event.preventDefault();
                    var dx = event.touches[0].pageX - lastMouseX;
                    var dy = event.touches[0].pageY - lastMouseY;
                    lastMouseX = event.touches[0].pageX;
                    lastMouseY = event.touches[0].pageY;
                    var newLeft = (parseFloat($this.css('left')) || 0) + dx;
                    var newTop = (parseFloat($this.css('top')) || 0) + dy;

                    // Ограничиваем перемещение, чтобы элемент не выходил за границы родительского контейнера
                    if (dataWidth > dataParentWidth) {
                        // Элемент шире, чем окно
                        if (newLeft > 0) {
                            newLeft = 0; // предотвратить отрыв левой грани
                        }
                        if (newLeft + dataWidth < dataParentWidth) {
                            newLeft = dataParentWidth - dataWidth; // предотвратить отрыв правой грани
                        }
                    } else {
                        // Элемент уже, чем окно
                        if (newLeft < 0) {
                            newLeft = 0; // предотвратить отрыв левой грани
                        }
                        if (newLeft + dataWidth > dataParentWidth) {
                            newLeft = dataParentWidth - dataWidth; // предотвратить отрыв правой грани
                        }
                    }

                    if (dataHeight > dataParentHeight) {
                        // Элемент выше, чем окно
                        if (newTop > 0) {
                            newTop = 0; // предотвратить отрыв верхней грани
                        }
                        if (newTop + dataHeight < dataParentHeight) {
                            newTop = dataParentHeight - dataHeight; // предотвратить отрыв нижней грани
                        }
                    } else {
                        // Элемент ниже, чем окно
                        if (newTop < 0) {
                            newTop = 0; // предотвратить отрыв верхней грани
                        }
                        if (newTop + dataHeight > dataParentHeight) {
                            newTop = dataParentHeight - dataHeight; // предотвратить отрыв нижней грани
                        }
                    }

                    $this.css({
                        left: newLeft + 'px',
                        top: newTop + 'px',
                        position: 'absolute'
                    });
                    updateData(); // Обновляем данные элемента
                }
            }

            // Обработчик события окончания касания
            function onTouchEnd(event) {
                isDragging = false; // Прекращаем перетаскивание
            }

            // Обработчик события нажатия мыши
            function onMouseDown(event) {
                event.preventDefault(); // Предотвращаем дефолтное поведение
                lastMouseX = event.pageX;
                lastMouseY = event.pageY;
                isDragging = true; // Начинаем перетаскивание
            }

            // Обработчик события перемещения мыши
            function onMouseMove(event) {
                if (isDragging) {
                    event.preventDefault(); // Предотвращаем дефолтное поведение
                    var dx = event.pageX - lastMouseX;
                    var dy = event.pageY - lastMouseY;
                    lastMouseX = event.pageX;
                    lastMouseY = event.pageY;
                    var newLeft = (parseFloat($this.css('left')) || 0) + dx;
                    var newTop = (parseFloat($this.css('top')) || 0) + dy;

                    // Ограничиваем перемещение, чтобы элемент не выходил за границы родительского контейнера
                    if (dataWidth > dataParentWidth) {
                        // Элемент шире, чем окно
                        if (newLeft > 0) {
                            newLeft = 0; // предотвратить отрыв левой грани
                        }
                        if (newLeft + dataWidth < dataParentWidth) {
                            newLeft = dataParentWidth - dataWidth; // предотвратить отрыв правой грани
                        }
                    } else {
                        // Элемент уже, чем окно
                        if (newLeft < 0) {
                            newLeft = 0; // предотвратить отрыв левой грани
                        }
                        if (newLeft + dataWidth > dataParentWidth) {
                            newLeft = dataParentWidth - dataWidth; // предотвратить отрыв правой грани
                        }
                    }

                    if (dataHeight > dataParentHeight) {
                        // Элемент выше, чем окно
                        if (newTop > 0) {
                            newTop = 0; // предотвратить отрыв верхней грани
                        }
                        if (newTop + dataHeight < dataParentHeight) {
                            newTop = dataParentHeight - dataHeight; // предотвратить отрыв нижней грани
                        }
                    } else {
                        // Элемент ниже, чем окно
                        if (newTop < 0) {
                            newTop = 0; // предотвратить отрыв верхней грани
                        }
                        if (newTop + dataHeight > dataParentHeight) {
                            newTop = dataParentHeight - dataHeight; // предотвратить отрыв нижней грани
                        }
                    }

                    $this.css({
                        left: newLeft + 'px',
                        top: newTop + 'px',
                        position: 'absolute'
                    });
                    updateData(); // Обновляем данные элемента
                }
            }

            // Обработчик события отпускания мыши
            function onMouseUp(event) {
                isDragging = false; // Прекращаем перетаскивание
            }

            // Обработчик события изменения размера окна
            $(window).on('resize', function() {
                setTransform();
            });

            // Методы для включения и отключения обработки тачпада и скролла
            this.enableTouchpad = function() {
                settings.touchpadEnabled = true;
            };

            this.disableTouchpad = function() {
                settings.touchpadEnabled = false;
            };

            this.enableScroll = function() {
                settings.scrollEnabled = true;
            };

            this.disableScroll = function() {
                settings.scrollEnabled = false;
            };

            // Привязка обработчиков событий к элементу и документу
            $this.on('wheel', onWheel); // Привязываем обработчик события колесика мыши
            $this.on('touchstart', onTouchStart); // Привязываем обработчик события начала касания
            $this.on('touchmove', onTouchMove); // Привязываем обработчик события перемещения касания
            $this.on('touchend', onTouchEnd); // Привязываем обработчик события окончания касания
            $this.on('mousedown', onMouseDown); // Привязываем обработчик события нажатия мыши
            $(document).on('mousemove', onMouseMove); // Привязываем обработчик события перемещения мыши
            $(document).on('mouseup', onMouseUp); // Привязываем обработчик события отпускания мыши

            // Первоначальная установка масштаба и обновление данных элемента
            setTransform(); // Первоначальная установка масштаба

            // Возвращаем объект для использования методов вне плагина
            return this;
        });
    };
}(jQuery));






function zoominit(imageElement) {




    map_id = $(imageElement).closest(".myContent").attr('id');
    var zoomableElement = $('#' + map_id).zoomable({
        zoomEnabled: true, // Включение/отключение зума
        minScale: 1, // Минимальный масштаб
        maxScale: 3, // Максимальный масштаб
        touchpadEnabled: true, // Включение/отключение обработки тачпада
        scrollEnabled: true // Включение/отключение обработки скролла
    });


    /*
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
			 
			
			
			*/
    // Кнопки масштаба
    document.querySelector('[data-zoom-up]').addEventListener('click', function() {
        zoomableElement.zoomable('zoomIn');
    });

    document.querySelector('[data-zoom-down]').addEventListener('click', function() {
        zoomableElement.zoomable('zoomOut');
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




// Открытие карты 
$("#open_modal").click(function() {
    var modal = $('#psevsdomodal');
    // Окно закрыто обрабатываем клик
    if (!modal.hasClass('psevsdomodal_open')) {
        // $( "#map" ).fadeOut(0); // Скрываем карту
        modal.addClass('psevsdomodal_open'); // Помещаем ее в экран по высоте
        $("#open_modal").hide(300);
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
$("#close_modal").click(function() {

    var modal = $('#psevsdomodal');
    // Окно закрыто обрабатываем клик
    if (modal.hasClass('psevsdomodal_open')) {
        $("#map").fadeOut(0); // Скрываем карту
        //$('#zoomcontainerx').smartZoom();



        $('#zoomcontainerx').css("transform", ''); //Сброс зума
        $("#map").fadeIn(600); // Плавно показываем
        modal.removeClass('psevsdomodal_open');
        $("#close_modal").hide(300);
        $("#open_modal").show(300);

        $('html').css({
            //overflow: 'scroll',
            //	height: '100%'
        });
        //	 umapx();
        return false;
    };


});








// ОБРАБОТКА КЛИКОВ  и толтипсы =  включаем после загрузки данных только в методе загрузки
function umapx(map_id, numbers = false) {

    const event = new Event("mapload"); // определяем объект события
    document.dispatchEvent(event); // генерируем событие для всего документа

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













document.addEventListener('DOMContentLoaded', function() {
    // Масштабирование карты
    loadJS("https://msk.m2profi.pro/maps/frontend/wheel-zoom.min.js");

    // Подсказки при наведении
    loadCSS("https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.min.css");
    loadCSS("https://msk.m2profi.pro/sahmatka/tooltip/tooltipster-sideTip-punk.min.css");
    loadCSS("https://msk.m2profi.pro/sahmatka/tooltip/style.css");
    loadJS("https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.js");

    // Попап окна
    loadCSS("https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/magnific-popup.css");
    loadJS("https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/jquery.magnific-popup.js");

    // Стиль для интерактивной карты
    loadCSS("https://msk.m2profi.pro/mapwiget/m.css");

    // Интерактивность участков карты
    loadJS("https://msk.m2profi.pro/mapwiget/m.js");
	
	
	
	$(document).ready(function() {
		$(".scheme").load("https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jqsvg&map_id=" + mapid,
			function(response, status, xhr) {
				if (status == "error") {
					$("#content").html("An error occured: " + xhr.status + " " + xhr.statusText);
				} else {
					updatejsoon(mapid, 1); // id карты, нумерация
				}
		 });
	});




	document.write('<div id="map__' + mapid + '" class="noselect" style="position:relative">');
	document.write('<div style="position: absolute; left: 0; top: 30px; z-index: 3;">');
	document.write('<div>');

	if (window.innerWidth >= 768) {
		document.write('<button class="zmb" data-zoom-down  style="width: auto;">-</button> ');
		document.write('<button class="zmb" data-zoom-up  style="width: auto;" >+</button>');
	}

	document.write('<button class="zmb" id="printButton" style="width: auto;" >  <img src="https://msk.m2profi.pro/mapwiget/print.png" style="width:30px;" />  </button>');
	document.write('</div>');
	document.write('</div>');
	document.write('<div class="ratio ratio-4x3 " style="overflow:hidden">');
	document.write('<div id="myViewport" class="myViewport"> ');
	document.write('<div class="myContent" id="mapcontent__' + mapid + '">');
	document.write('<div class="scheme" style="position:absolute;  width:100%; left:0; "></div> ');
	document.write('<img id="map" class="mapbg" src="https://gl.m2profi.pro/maps/' + mapid + '/map.png" alt="">');
	document.write('</div>');
	document.write('</div>');
	document.write('</div>');
	document.write('</div>');

});
