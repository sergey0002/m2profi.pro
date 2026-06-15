<?php $v = $data; ?>
//<script>
// Константы для настройки скрипта
const DEBOUNCE_DELAY = 500;
const CACHE_EXPIRATION = 10 * 60 * 1000; // 10 минут
let formChangeTimeout;
let ajaxCache = {};
let isProgrammaticChange = false;
let streetList = []; // Для старого списка улиц
let streetListP = []; // Для street_p
let streetListR = []; // Для select_r

// Кэширование DOM-элементов
const $form = $('#rentsearch');
const $searchResult = $('#rent_search_result');
const $progressBar = $('#progressbar');
const $mapElements = $('#rentobjects, #maprentobjects, #showmap, #hidemap');
const basePath = window.location.pathname;

async function initialize() {
    try {
        await Promise.all([loadStreets('street'), loadStreets('street_p'), loadStreets('select_r')]);
        populateFormFromURL();
        setupEventHandlers();
        initPlugins();
        loadData();
    } catch (error) {
        console.error('Ошибка при инициализации:', error);
    }
}

function getDefaultValue($element) {
    if ($element.is('select')) return $element.find('option[selected]').val() || '';
    if ($element.is(':checkbox')) return $element.prop('defaultChecked') ? 'on' : '';
    return $element.val() || '';
}

function loadStreets(type) {
    return new Promise((resolve, reject) => {
        let action;
        let targetList;
        switch (type) {
            case 'street':
                action = 'jsoon_street';
                targetList = streetList;
                break;
            case 'street_p':
                action = 'jsoon_street';
                targetList = streetListP;
                break;
            case 'select_r':
                action = 'jsoon_street';
                targetList = streetListR;
                break;
            default:
                reject('Неизвестный тип улиц');
                return;
        }

        $.ajax({
            url: 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php',
            type: 'POST',
            data: { ctr: '<?=$v['ctr']?>', act: action },
            dataType: 'json',
            success: function(response) {
                if (Array.isArray(response)) {
                    switch (type) {
                        case 'street':
                            streetList = response;
                            break;
                        case 'street_p':
                            streetListP = response;
                            break;
                        case 'select_r':
                            streetListR = response;
                            break;
                    }
                    populateStreetSelect(type, response);
                    resolve();
                } else {
                    reject(`Ошибка загрузки улиц для ${type}`);
                }
            },
            error: function() {
                reject(`Ошибка при загрузке улиц для ${type}`);
            }
        });
    });
}

function populateStreetSelect(type, streetList) {
    let $streetSelect;
    switch (type) {
        case 'street':
            $streetSelect = $('#street');
            break;
        case 'street_p':
            $streetSelect = $('#street_p');
            break;
        case 'select_r':
            $streetSelect = $('#select_r');
            break;
        default:
            return;
    }

    if ($streetSelect.length) {
        $streetSelect.empty();
        streetList.forEach(street => $streetSelect.append(new Option(street.name, street.id)));
        // Инициализация nice-select после заполнения селекта
        $streetSelect.niceSelect('update');
    }
}

function populateFormFromURL() {
    const params = new URLSearchParams(window.location.search);
    $('#rentsearch').find('input, select').each(function () {
        const $element = $(this);
        const name = $element.attr('name');
        if (name && params.has(name)) {
            $element.val(params.get(name)).trigger('change');
        }
    });
}

function setupEventHandlers() {
    $form.on('change', handleFormChange);
    $(window).on('popstate', () => {
        populateFormFromURL();
        loadData();
    });
    $mapElements.on('click', '#showmap, #hidemap', toggleMap);
}

function handleFormChange() {
    if (isProgrammaticChange) return;
    clearTimeout(formChangeTimeout);
    formChangeTimeout = setTimeout(() => {
        updateURL();
        loadData();
    }, DEBOUNCE_DELAY);
}

function updateURL() {
    const params = new URLSearchParams(new FormData($form[0]));
    history.replaceState(null, '', `${basePath}?${params.toString()}`);
}

function loadData() {
    sendAjaxForm({
        resultto: 'rent_search_result',
        formid: 'rentsearch',
        url: 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php',
        data: { ctr: '<?=$v['ctr']?>', act: '<?=$v['act']?>' }
    });
}

function sendAjaxForm({ resultto, formid, url, data = {} }) {
    const cacheKey = `${url}?${$.param(data)}`;
    if (ajaxCache[cacheKey] && Date.now() - ajaxCache[cacheKey].timestamp < CACHE_EXPIRATION) {
        handleAjaxResponse(resultto, ajaxCache[cacheKey].data);
        return;
    }
    $progressBar.show();
    $.ajax({ url, type: 'POST', dataType: 'html', data: { ...data, ...$(`#${formid}`).serializeArray().reduce((acc, { name, value }) => (acc[name] = value, acc), {}) } })
    .done(response => {
        if (response) {
            ajaxCache[cacheKey] = { data: response, timestamp: Date.now() };
            handleAjaxResponse(resultto, response);
        }
    })
    .fail(() => {
        $(`#${resultto}`).html('<div class="error">Ошибка загрузки. <button class="retry-btn">Повторить</button></div>').fadeIn(600);
    })
    .always(() => $progressBar.hide());
}

function handleAjaxResponse(resultto, response) {
    $(`#${resultto}`).html(response).fadeIn(600);
}

function toggleMap(e) {
    e.preventDefault();
    $mapElements.fadeToggle(300);
}

function initPlugins() {
    $('.iframe_r').magnificPopup({ type: 'iframe', mainClass: 'mfp-no-margins' });
    // Инициализация nice-select для всех селектов после загрузки данных
    $('select').niceSelect();
}

$(document).ready(initialize);

 
 
 
 
 
 
 
 
 
 
function in_postload( )
{ 

// Переопределение слайдера для загруженных данных
   var swiper = new Swiper(".mySwiper", {
	  spaceBetween: 30,
	  navigation: {
		nextEl: ".swiper-button-next",
		prevEl: ".swiper-button-prev",
	  },
	  pagination: {
		el: ".swiper-pagination",
	  },
	  mousewheel: true,
	  keyboard: true,
	});
	
 
 
 $("a.iframe").fancybox({
		maxWidth    : 600,
		maxHeight   : 12600,
		
		width       : '1000px',
		height      : '10000px',
		closeClick  : true,
	}); 
	//$('select').niceSelect('destroy');
 // $('select').niceSelect();
	
  $('.iframerent').magnificPopup({type:'iframe',
 // removalDelay: 100,
 // fixedContentPos: true, 
  //disableOn:1,
  mainClass: 'mfp-fade',
	mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
	  removalDelay: 300,
   tLoading: 'Загрузка #%curr%...',
	callbacks: {
	open: function() {
	  // Will fire when this exact popup is opened
	  // this - is Magnific Popup object
	},
	close: function() {
		// parent.location.reload(true);  
	},
	open: function() {
		  location.href = location.href.split('#')[0] + "#pop";
		} 
	 
	// e.t.c.
  }
   
  });

}



















