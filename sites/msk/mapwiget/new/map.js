var original$ = window.$;
    var originalJQuery = window.jQuery;
	var panzoomInstance;
	
    // Функция инициализации виджета
    function initializeWidget(widgetElement) {
        // Получаем атрибуты из элемента виджета
        const mapId = widgetElement.getAttribute('data-map_id');
        const areaId = widgetElement.getAttribute('data-area-id');
        const mapw = widgetElement.getAttribute('data-mapw') === "1";

        // Создаем уникальные идентификаторы для элементов внутри виджета
        const widgetId = `plot-widget-${mapId}`;
        const containerId = `container-${mapId}`;
        const expandButtonId = `expand-button-${mapId}`;
        const searchContainerId = `search-container-${mapId}`;
		
		
        const mapContainerId = `map__id_${mapId}`;

        // Создаем корневой элемент для виджета
        const widgetRoot = document.createElement('div');
        widgetRoot.classList.add('plot-widget');
        widgetRoot.id = widgetId;
        widgetElement.appendChild(widgetRoot);
		const panzoomContainerId = `panzoom-container-${mapId}`; // Уникальный идентификатор
		const plotpanelid = `plotpanelid-${mapId}`;
   
	  
	  // Глобальная переменная для отслеживания текущего выделенного участка
let currentHighlightedPlot = null;
	  
    widgetRoot.innerHTML = `
		
        <div class="widget-container" id="${containerId}">
            <div class="loading-message" id="loading-message-${mapId}" style="text-align:center;">
				<div class="loading-content">
                <img src="https://msk.m2profi.pro/sahmatka/loader.gif" /><br/><span class="progresstext">Загрузка данных...</span>
				</div>
            </div>
            <div id="${mapContainerId}" class="scheme map-container">
                <div id="${panzoomContainerId}" style="position: relative; width: 100%; height: 100%;">
                    <div class="background-image" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url(https://gl.m2profi.pro/maps/${mapId}/map.png); background-size: contain; background-repeat: no-repeat; background-position: center;">
                    <svg id="svg-layer" class="map-svg"></svg>
					 
					</div>
				</div>
				<div class="click-overlay hidden"></div>
			</div>
        <div class="search-container hidden" id="${searchContainerId}" styel="display:none;">
            <input type="text" class="search-input" placeholder="Введите номер участка">
        </div>
		 
		<div class="plotpanel hidden" id="${plotpanelid}">
            Участок № <span class="num plotpanel_val"></span><br/>
			Статус: <span class="status-text plotpanel_val"></span><br/>
			Площадь: <span class="area plotpanel_val"></span><br/>
				<span class="prices">
				Стоимость сотки: <span class="price_area plotpanel_val"></span><br/>
				Цена: <span class="price plotpanel_val"></span><br/>
				</span>
			<span style = "font-size: 10px;">Кадастровый номер:<br/> <span class="kadastrnum plotpanel_val"></span></span><br/>
			<div class="book-button_w"><button class="book-button hide">Забронировать</button></div>
        </div>
        <div class="expand-button hidden" id="${expandButtonId}">&times;</div>
		
	
		 
		</div> 
    `;
	
 
	const containerElement = document.querySelector(`#${containerId}`);
	const panzoomElement = document.getElementById(panzoomContainerId);
	
 
 
	let svgCache = null; // Кеш свг с сервера (чтобы не грузить при каждом перестроении)

	const zoom_object = startzoom(panzoomElement);
 
 
 
 // Для тильды и возможно сео переносит один обект в другой (по умолчанию в начало body) action=1 перенести action =2 вернуть
function moveElement(element, action, targetContainer = document.body) {
    if (action === 1) {
        // Сохраняем родительский элемент и следующий братский элемент для восстановления
        element._originalParent = element.parentNode;
        element._originalNextSibling = element.nextSibling;

        // Перемещаем элемент в указанный контейнер (по умолчанию в начало body)
        targetContainer.insertBefore(element, targetContainer.firstChild);

    } else if (action === 2) {
        // Восстанавливаем элемент на исходное место
        if (element._originalParent) {
            var parent = element._originalParent;
            var nextSibling = element._originalNextSibling;

            if (nextSibling) {
                parent.insertBefore(element, nextSibling);
            } else {
                parent.appendChild(element);
            }

            // Удаляем временные данные
            delete element._originalParent;
            delete element._originalNextSibling;
        }
    } else {
        console.error('Invalid action. Use 1 to move element or 2 to return it to original position.');
    }
}







 
 // развернуть обект на всю высоту!!!!!!!! делаем
 function resizeToFullHeight() {
   const mapContainer = document.getElementById(mapContainerId);
	const containerHeight = mapContainer.clientHeight;
	const contentHeight = mapElement.clientHeight;
	const scale = containerHeight / contentHeight; // Вычисляем коэффициент увеличения
	zoom_object.zoom(scale, { animate: true }); // Применяем масштаб с анимацией

	// Центрируем изображение после увеличения
	zoom_object.pan(0, 0, { relative: false });
}




	// Тач устройство (мобильный планшет )
	function isTouchDevice() {
		return 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
	}
 
	function startzoom(panzoomElement)
	{
	 
		panzoomInstance = new Panzoom(panzoomElement, {
			      contain: 'inside', // Ограничение перемещения и масштабирования внутри контейнера
        panOnlyWhenZoomed: true, // Панорамирование возможно только при увеличении
        maxScale: 6, // Максимальное масштабирование
        minScale: 1, // Минимальное масштабирование
		click:"false",
		 friction: 0.05
		});
		
		 
 
		 
		return panzoomInstance;
	}	 
			
function showProgressBar(text) {
     const progressBar = document.getElementById(`loading-message-${mapId}`);
    const progressText = progressBar.querySelector('.progresstext');

    // Устанавливаем новый текст
    progressText.textContent = text;

    // Показываем прогресс-бар
    progressBar.classList.remove('hidden');
	
}

function hideProgressBar() {
    const progressBar = document.getElementById(`loading-message-${mapId}`);
    progressBar.classList.add('hidden');
}
		
		////////////////////////////// СВОРАЧИВАНИЕ РАЗВОРАЧИВАНИЕ КРАТЫ
		
		
        // Переменные для хранения состояния
        let isExpanded = false;
       
		// Разворачивание
function expandMap() {
    isExpanded = true;
    widgetRoot.querySelector(`#${expandButtonId}`).classList.remove('hidden');
    widgetRoot.querySelector(`#${expandButtonId}`).style.display = 'block';

    widgetRoot.querySelector(`#${searchContainerId}`).classList.remove('hidden');
    widgetRoot.querySelector(`#${searchContainerId}`).style.display = 'block';

    const container = widgetRoot.querySelector(`#${containerId}`);
    container.style.transition = "all 0.5s ease";
    container.style.position = "fixed";
    container.style.width = window.innerWidth + 'px';
    container.style.height = window.innerHeight + 'px';
    container.style.zIndex = '1000';
    container.style.top = 0;
    container.style.left = 0;

    widgetRoot.querySelector('.click-overlay').style.display = 'none';




	moveElement(document.getElementById(`w-map-${mapId}`),1); // Переносим контейнер в начало боди!))
	
    zoom_object.toggleFS(); // Переключаем карту в полноэкранный режим

    // Масштабируем объект, чтобы он занял всю высоту контейнера
    setTimeout(() => {
        const containerHeight = container.clientHeight;
        const contentHeight = zoom_object.contentRect.fullHeight;
        const scale = containerHeight / contentHeight; // Рассчитываем масштаб

        // Применяем масштабирование
        zoom_object.zoomTo(scale, {
            animate: true,
            friction: 0.05,
            originX: 0,
            originY: 0
        });
    }, 350); // Небольшая задержка для корректного применения стилей и анимации
}


		
		
		
		
		// Сворачивание
		function collapseMap() {
		isExpanded = false;
		
		moveElement(document.getElementById(`w-map-${mapId}`),2); // ) // Возвращаем контейнер из начала боди
		
		
			const container = widgetRoot.querySelector(`#${containerId}`);
			container.style.transition = "all 0.3s ease";
	 
			container.style.position = "relative";
			container.style.zIndex = '1';
			 
			
			
			
			if (zoom_object) {
				zoom_object.reset();
			}

			widgetRoot.querySelector(`#${expandButtonId}`).style.display = 'none';
			widgetRoot.querySelector(`#${searchContainerId}`).style.display = 'none';
			widgetRoot.querySelector(`#${plotpanelid}`).style.display = 'none';
			widgetRoot.querySelector(`#${plotpanelid}`).classList.add('hidden');
			widgetRoot.querySelector('.click-overlay').style.display = 'block';

		 
			setContainerSize(); // Корректный вызов функции после сворачивания
		 
		}

		////////////////////////////////////////////////////////////////////////////////////////



        
 

// Функция форматирования валюты
function formatCurrency(value) {
    if (isNaN(value) || value === 'Не указано') {
        return 'Не указано';
    }
    return parseFloat(value).toLocaleString('ru-RU', { style: 'currency', currency: 'RUB' });
}





// Функция для выделения участка
function highlightPlot(plot) {
    // Снимаем выделение с предыдущего участка, если он есть
    if (currentHighlightedPlot) {
        currentHighlightedPlot.style.strokeWidth  = '1px'; // Убираем рамку
        currentHighlightedPlot.style.stroke = '#FFF'; // Убираем тень, если есть
    }
 
    // Добавляем стиль рамки к текущему участку
    plot.style.strokeWidth  = '3px ';
    plot.style.stroke = 'red'; // Необязательно, для дополнительного эффекта

    // Сохраняем ссылку на текущий выделенный участок
    currentHighlightedPlot = plot;
}



// закрыть все подсказки
function closett() {
    var instances = $.tooltipster.instances();
    $.each(instances, function(i, instance) {
        instance.close();
    });
}





// Форма брони принимает обект path 
function form( plot )
{

    showProgressBar('');

	map_id = mapId;
     // if( !$(plot).hasClass("insale") ){ return; }  // только в продаже
  
	closett(); // Закрыть подсказки
	// ФОрма брони / редактирования

	$.getJSON('https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jsoon_landplot&polygon_id=' + $(plot).attr('data-id') + '&map_id=' + map_id)
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
		url: 'https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=public_form&polygon_id=' + $(plot).attr('data-id') + '&map_id=' + map_id,
		data: {
			action: 'ajax_action',
			postid: $(plot).data('id'),
			//$(plot).data() works because it's a standard AJAX call
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
		},
        complete: function() {
            // Скрываем прогресс-бар после завершения всех загрузок
            hideProgressBar();
        },
        error: function() {
            console.log('Ошибка загрузки формы');
            hideProgressBar(); // Скрываем прогресс-бар даже в случае ошибки
        }
		
		
		
	});

	$(document).on("submit", '#lpform', function(event) {
 showProgressBar('Отправляем форму');
		$.ajax({
			url: $(this).attr('action'),
			method: 'post',
			dataType: 'html',
			data: $(this).serialize(),
			success: function(data) {
				//$('#message').html(data);
				$('#lpform').hide(200);
				$('#lpform_ok').show(200);
			},
			complete: function() {
				// Скрываем прогресс-бар после завершения всех загрузок
				hideProgressBar();
			},
			error: function() {
				console.log('Ошибка загрузки формы');
				hideProgressBar(); // Скрываем прогресс-бар даже в случае ошибки
			}
		});
		return false;
	});

	$("#map__" + map_id + ' .scheme-popup').hide();
	$("#map__" + map_id + ' .scheme-item[data-id=' + $(plot).data('id') + ']').trigger('click');  
}






function plot_panel(plot) 
{


    // Получаем ID панели из глобальной переменной или параметра
    const plotPanel = document.getElementById(plotpanelid);
 
				
    // Проверяем, существует ли элемент панель
    if (!plotPanel) {
        console.error(`Не удалось найти элемент с ID ${plotpanelid}`);
        return;
    }




    // Проверяем, что элемент plot имеет атрибут data-num
    if (plot && plot.hasAttribute('data-num')) {
	 
		highlightPlot(plot);
	 
		// если не мобильное устройство выводим форму 
		
		if(!isTouchDevice())
		{
			widgetRoot.querySelector(`#${plotpanelid}`).style.display = 'hidden';
			document.getElementById(plotpanelid).classList.add('hidden');
			console.log('Не тач девайс');
			form(plot);
		}
		else //мобильное устройство выводим панельку 
		{
		
		widgetRoot.querySelector(`#${plotpanelid}`).style.display = 'block';
		document.getElementById(plotpanelid).classList.remove('hidden');
	 
	 
			console.log('  тач девайс');
			// Получаем данные из атрибутов элемента plot
			const num = plot.getAttribute('data-num');
			const statustext = plot.getAttribute('data-status_text') || 'Не указано';
			const status = plot.getAttribute('data-status') || '0';
			  var statusColor = plot.getAttribute('data-status_color') || 'Не указано';
			
			if(status==7) // Скоро в продаже
			{
				 statusColor = '#A9A9A9';
			}
			if(status==6) // подрядч
			{
				 statusColor = '#B22222';
			}
			if(status==5) // Бронь Усадьбы
			{
				 statusColor = '#B22222';
			}
			if(status==4) // Забронирован
			{
				 statusColor = '#FFD700';
			}
			if(status==3) // подрядч
			{
				 statusColor = '#B22222';
			}
			if(status==2) // свободен
			{
				 statusColor = '#32CD32';
			}
			 
			
			
			const area = plot.getAttribute('data-area') || 'Не указано';
			const kadastrNum = plot.getAttribute('data-kadastrnum') || 'Не указано';
			const priceArea = plot.getAttribute('data-price_area') || 'Не указано';
			const price = plot.getAttribute('data-price') || 'Не указано';
			

			// Обновляем содержимое элемента панель
			plotPanel.querySelector('.num').textContent = num;
			plotPanel.querySelector('.status-text').textContent = statustext;
			plotPanel.querySelector('.status-text').style.color = statusColor;
			plotPanel.querySelector('.area').innerHTML  = area+' м<sup>2</sup>';
			plotPanel.querySelector('.kadastrnum').textContent = kadastrNum;

			// Форматируем цены в денежном формате
			plotPanel.querySelector('.price_area').textContent = formatCurrency(priceArea);
			plotPanel.querySelector('.price').textContent = formatCurrency(price);

 


			// Обрабатываем отображение кнопки "Забронировать"
			const bookButton = plotPanel.querySelector('.book-button');
			const prices = plotPanel.querySelector('.prices');
			if (status === '2') {
				 bookButton.classList.remove('hidden'); // добавляем класс для стиля
				 prices.classList.remove('hidden'); // добавляем класс для стиля
			} else {
				 bookButton.classList.add('hidden'); // добавляем класс для стиля
				 prices.classList.add('hidden'); // добавляем класс для стиля
			}

			// Показываем элемент панель
			plotPanel.classList.remove('hidden');
			
			
			
			// Клик по забронировать в панельке - выделенный участок
			bookButton.addEventListener('click', function(event) {
				form(currentHighlightedPlot); 
			});
			
			 
	 
		} // Мобила  
	} 
	else 
	{
        console.error('Элемент plot не имеет атрибута data-num');
    }
}



		

		
		
function initializePanzoom( ) {
    
    // Переменные для отслеживания перемещений
    let startX, startY, startTime;
    let isDragging = false;
 
    function handleMouseDown(event) {
        startX = event.clientX;
        startY = event.clientY;
        startTime = Date.now();
        isDragging = false;
    }

    function handleMouseMove(event) {
        if (Math.abs(event.clientX - startX) > 10 || Math.abs(event.clientY - startY) > 10) {
            isDragging = true;
        }
    }

    function handleMouseUp(event) {
        if (!isDragging) {
           
            plot_panel(event.target.closest('polygon, path'))
        }
    }

    function handleTouchStart(event) {
        startX = event.touches[0].clientX;
        startY = event.touches[0].clientY;
        startTime = Date.now();
        isDragging = false;
    }

    function handleTouchMove(event) {
        if (Math.abs(event.touches[0].clientX - startX) > 10 || Math.abs(event.touches[0].clientY - startY) > 10) {
            isDragging = true;
        }
    }

    function handleTouchEnd(event) {
        if (!isDragging) {
           plot_panel(event.target.closest('polygon, path'))
        }
    }

    // Обработка кликов и перетаскиваний на десктопе
    panzoomElement.addEventListener('mousedown', handleMouseDown);
    panzoomElement.addEventListener('mousemove', handleMouseMove);
    panzoomElement.addEventListener('mouseup', handleMouseUp);

    // Поддержка тач-событий для мобильных устройств
    panzoomElement.addEventListener('touchstart', handleTouchStart, { passive: true });
    panzoomElement.addEventListener('touchmove', handleTouchMove, { passive: true });
    panzoomElement.addEventListener('touchend', handleTouchEnd, { passive: true });






    // Настройка событий для оверлея и кнопки расширения карты
    if (mapw) {
        const overlay = widgetRoot.querySelector('.click-overlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.addEventListener('click', expandMap);
        } else {
            console.error('Overlay not found');
        }

        const expandButton = widgetRoot.querySelector(`#${expandButtonId}`);
        if (expandButton) {
            expandButton.addEventListener('click', collapseMap);
        } else {
            console.error('Expand button not found:', expandButtonId);
        }
    }
}





	
	
	
	
	
		function debounce(func, wait) {
			let timeout;
			return function() {
				clearTimeout(timeout);
				timeout = setTimeout(() => func.apply(this, arguments), wait);
			};
		}
		 
		function setContainerSize() {
    requestAnimationFrame(() => {
        const container = document.getElementById(containerId);

        if (isExpanded) {
            container.style.width = `${window.innerWidth}px`;
            container.style.height = `${window.innerHeight}px`;
        } else {
            const img = container.querySelector('.sizeimage_xxx');
            if (img && img.complete) {
                const widgetWidth = widgetElement.offsetWidth;
                const aspectRatio = img.height / img.width;
                
                // Устанавливаем ширину и высоту контейнера на основе фактической ширины виджета и соотношения сторон изображения
                container.style.width = `${widgetWidth}px`;
                container.style.height = `${widgetWidth * aspectRatio}px`;
            }
        }
    });
}
						
		
		
	 
	
	
	
	

		 
 

        // Функция для отображения карты после загрузки
        function displayMap() {
            document.getElementById(`loading-message-${mapId}`).classList.add('hidden');
            document.getElementById(containerId).classList.remove('hidden');
            document.getElementById(searchContainerId).classList.remove('hidden');
			//document.getElementById(plotpanelid).classList.remove('hidden');
 
			 $('[relmap~=tooltip]').tooltipster({
					debug: true,
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

        
	 
		 
		 
		 
		 
        // Функция для загрузки SVG-контента карты		
		function loadSVG() {
		
			if (svgCache) {
				document.getElementById(panzoomContainerId).querySelector('.map-svg').innerHTML = svgCache;
				return Promise.resolve();
			}
			else
			{
			showProgressBar('Загрузка участков');
			const url = `https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jqsvg&map_id=${mapId}`;
			return fetch(url)
				.then(response => {
					if (!response.ok) throw new Error(`Ошибка загрузки SVG: ${response.statusText}`);
					return response.text();
				})
				.then(svgContent => {
					svgCache = svgContent;
					document.getElementById(panzoomContainerId).querySelector('.map-svg').innerHTML = svgCache;
				}).finally(() => {
					hideProgressBar(); // Прячем прогресс-бар после завершения
				});
			}
		}
				
		
		
		
		
		
		
		
		

        // Функция для загрузки данных SVG
        function loadSVGData() {
		showProgressBar('Загрузка лучших цен');
            const url = `https://msk.m2profi.pro/sahmatka/ajax_router.php?ctr=landplots&act=jsoondata&map_id=${mapId}`;
            return fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`Ошибка загрузки данных SVG: ${response.statusText}`);
                    return response.json();
                })
                .then(data => {
                    modifySVG(data);
                }).finally(() => {
					hideProgressBar(); // Прячем прогресс-бар после завершения
				});
        }
		
		// Функция для добавления текстовых меток на SVG
        function addLabelText(bgPath, labelText) {
            if (!bgPath) return;
            let bbox = bgPath.getBBox();
            let x = bbox.x + bbox.width / 2;
            let y = bbox.y + bbox.height / 2;

            let textElem = document.createElementNS(bgPath.namespaceURI, "text");
            textElem.setAttribute("x", x);
            textElem.setAttribute("y", y);
            textElem.setAttribute("text-anchor", "middle");
            textElem.classList.add("label-text");
            textElem.textContent = labelText;
            bgPath.before(textElem);
        } 
		
		

        // Функция для модификации SVG-контента на основе загруженных данных (цены итп)
        function modifySVG(data) { 
            const mapElement = document.getElementById(mapContainerId);
            for (let key in data) {
                if (data[key].num) {
                    let polygon = mapElement.querySelector(`path[data-id="${key}"]`);
                    if (polygon) {
                        polygon.style.fill = data[key].status_color;
                        polygon.style.cursor = 'pointer';
                        polygon.classList.add(data[key].class);
						
						
						 
						
						 polygon.setAttribute('relmap', 'tooltip');
						
                        polygon.setAttribute('data-num', data[key].num);
                        polygon.setAttribute('data-status', data[key].status);
                        polygon.setAttribute('data-map_id', data[key].map_id);
						 
						 polygon.setAttribute('data-area', data[key].area);
						 polygon.setAttribute('data-kadastrnum', data[key].kadastrnum);
						 polygon.setAttribute('data-price', data[key].price);
						  
						polygon.setAttribute('data-price_area', data[key].price_area);
						polygon.setAttribute('data-status_text', data[key].status_text);
						polygon.setAttribute('data-status_color', data[key].status_color);
						   
                        polygon.setAttribute('title', data[key].tooltip);
                        if (widgetElement.getAttribute('data-numbers')) {
                            addLabelText(polygon, data[key].num);
                            polygon.style.opacity = '0.6';
                        }
                    }
                }
            }
        }

        
		
		// запрещаем зум на мобильных во всем документе 
		function setOrUpdateViewportMeta() {
			let viewportMeta = document.querySelector('meta[name="viewport"]');

			if (!viewportMeta) {
				// Если мета-тег отсутствует, создаем его
				viewportMeta = document.createElement('meta');
				viewportMeta.name = "viewport";
				document.head.appendChild(viewportMeta);
			}

			// Устанавливаем необходимые атрибуты content
			viewportMeta.setAttribute('content', 'initial-scale=1, maximum-scale=1, user-scalable=no');
		}

		 
		 
		 
	 
		 loadSVG()
        .then(loadSVGData)
        .then(() => initializePanzoom(panzoomContainerId,plotpanelid)) // Передаем ID
        .then(displayMap)
		.then(setOrUpdateViewportMeta)
 
        .catch(error => {
            console.error('Ошибка в процессе загрузки и инициализации:', error);
        });
		 
        
		
		
		
		const img = new Image();
		img.src = `https://gl.m2profi.pro/maps/${mapId}/map.png`;
		img.style.visibility = `hidden`;
		img.classList.add('sizeimage_xxx');
		img.style.width = '100%';
		img.style.display = 'block';
		panzoomElement.appendChild(img);
		img.onload = function() 
		{
			// Устанавливаем высоту контейнера на основе высоты загруженного изображения
			const container = document.getElementById(containerId);
			//	container.style.height = img.height + 'px';
		 
			// высота всего виджета задаем как картинки
			const widgetParent = document.querySelector('.m2-map-ladplots-widget');
			widgetParent.style.height = img.height + 'px';
			
	
			 // Добавляем изображение в контейнер
			
		
			setContainerSize();
		};
		
		

        // Добавляем обработчик изменения размера окна
       window.addEventListener('resize', debounce(setContainerSize, 100));
    }

 






///////////////////////////////////////////////// загрузка скриптов и стилей


// Функция загрузки произвольного CSS файла
function loadCSS(url) {
    return new Promise((resolve, reject) => {
        //showProgressBar('Загрузка стилей...');
        
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        link.onload = () => {
            //hideProgressBar();
            resolve();
        };
        link.onerror = () => {
            // hideProgressBar();
            reject(new Error(`Ошибка загрузки CSS: ${url}`));
        };
        
        document.head.appendChild(link);
    });
}


// Функция загрузки произвольного JS файла с передачей jQuery в качестве аргумента
function loadJS(url, jQuery=null) {
    return new Promise((resolve, reject) => {
        //showProgressBar('Загрузка скриптов...');
        
        const script = document.createElement('script');
        script.src = url;
        script.onload = () => {
           // hideProgressBar();
            // Убедитесь, что jQuery передается в глобальную область
            if (jQuery) {
                window.$ = jQuery;
                window.jQuery = jQuery;
            }
            resolve();
        };
        script.onerror = () => {
           // hideProgressBar();
            reject(new Error(`Ошибка загрузки JS: ${url}`));
        };
        
        document.head.appendChild(script);
    });
}


 
 
	
	
	
// Функция для загрузки jQuery и передачи его в другие скрипты
function loadJQueryAndScripts() {
    return new Promise((resolve, reject) => {
        if (typeof jQuery === 'undefined') {
            // Если jQuery не загружен, загружаем его
            loadJS('https://code.jquery.com/jquery-3.7.1.min.js')
                .then(() => {
                    // Включаем режим без конфликтов
                    const jQueryNoConflict = jQuery.noConflict(true);
                    resolve(jQueryNoConflict);
                })
                .catch(error => {
                    reject(new Error('Ошибка загрузки jQuery: ' + error.message));
                });  
        } else {
            // Если jQuery уже загружен, передаем его напрямую
            resolve(jQuery.noConflict(true));
        }
    });
}
	
	
	
// Основная функция для загрузки ресурсов
function initializeCustomAssets() {
    // Сохраняем оригинальные значения $ и jQuery, если они определены
    var original$ = window.$;
    var originalJQuery = window.jQuery;

    // Загружаем jQuery и устанавливаем его для использования в вашем скрипте
    loadJQueryAndScripts()
        .then(($) => {
            (function($) {
                // Здесь начинается ваш код, использующий jQuery
                return loadJS('https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/panzoom/panzoom.umd.js', $)
                    .then(() => { return loadCSS('https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.min.css'); })
                    .then(() => { return loadCSS('https://msk.m2profi.pro/sahmatka/tooltip/tooltipster-sideTip-punk.min.css'); })
                    .then(() => { return loadCSS('https://msk.m2profi.pro/sahmatka/tooltip/style.css'); })
                    .then(() => { return loadCSS('https://msk.m2profi.pro/mapwiget/new/tt.css'); })
                    .then(() => { return loadCSS('https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/magnific-popup.css'); })
                    .then(() => { return loadJS('https://msk.m2profi.pro/sahmatka/tooltip/tooltipster.bundle.js', $); })
                    .then(() => { return loadJS('https://msk.m2profi.pro/sahmatka/template/default/libs/mpop/jquery.magnific-popup.js', $); })
					.then(() => { return loadCSS('https://msk.m2profi.pro/mapwiget/new/map.css');})
                    .then(() => {
                        // ИСПОЛНЯЕМ ВИДЖЕТ
                        document.querySelectorAll('.m2-map-ladplots-widget').forEach(widgetElement => {
                            initializeWidget(widgetElement);
                        });
                    });
                // Здесь заканчивается ваш код, использующий jQuery
            })($); // Передаем jQuery в анонимную функцию
        })
        .then(() => {
            // Восстанавливаем оригинальные значения $ и jQuery
            if (original$) {
                window.$ = original$;
            } else {
                delete window.$;
            }

            if (originalJQuery) {
                window.jQuery = originalJQuery;
            } else {
                delete window.jQuery;
            }
            console.log('Все файлы загружены и готовы к использованию.');
        })
        .catch(error => {
            console.error(error.message);
        });
}
initializeCustomAssets();
