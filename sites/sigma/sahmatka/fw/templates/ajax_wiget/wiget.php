<?php 
$v = $data;  

$form = '#rentsearch';
$searchResult ='#rent_search_result';
$progressBar = '#progressbar';
$mapElements= '#rentobjects, #maprentobjects, #showmap, #hidemap';

if($_GET['search_result'] ){ $searchResult = $_GET['search_result'] ; }
if($_GET['search_form'] ){ $form = $_GET['search_form'] ; }
?>//<script>



// === Кэширование DOM-элементов ===
let $form = $('<?=$form?>');
const $searchResult = $('<?=$searchResult?>');
const $progressBar = $('<?=$progressBar?>');
const $mapElements = $('<?=$mapElements?>');



// === Константы ===
const DEBOUNCE_DELAY = 500; // Задержка для debounce обработки формы
const CACHE_EXPIRATION = 0; // 10 минут
const basePath = window.location.pathname;

// === Переменные состояния ===
let formChangeTimeout;
let ajaxCache = {};
let isProgrammaticChange = false;
 

// === Переменные состояния ===
let streetLists = {
  commercial: [], // Для аренды помещений
  parking: [],    // Для парковок
  main: []        // Основной список улиц
};

// === Инициализация при загрузке ===
$(document).ready(() => {
  const path = window.location.pathname;
  if (path.includes('/rent-commercial/')) {
    $('body').addClass('rent-commercial');
  } else if (path.includes('/parking/')) {
    $('body').addClass('parking');
  }
  initialize(); // Запуск основной инициализации
});

// === Функция обновления стилей ===
function updateSelectNavStyle() {
  const path = window.location.pathname;
  $('.select_nav').removeClass('selected-red');
  
  if (path.includes('/rent-commercial/')) {
    $('.select_nav:first').addClass('selected-red');
  } else if (path.includes('/parking/')) {
    $('.select_nav:last').addClass('selected-red');
  }
  
  // Пересоздаем niceSelect для применения стилей
  $('#select_r, #select_p').niceSelect('update');
}

// === Главная инициализация ===
async function initialize() {
  try {
    // Загрузка данных для основного селекта #street
    await loadMainStreets();
    
    // Загрузка данных для аренды помещений (select_r)
    await loadCommercialStreets();
    
    // Загрузка данных для парковок (select_p)
    await loadParkingStreets();
    
    populateFormFromURL();
    setupEventHandlers();
    initPlugins();
    loadData();
  } catch (error) {
    console.error('Ошибка при инициализации:', error);
  }
}

// === Загрузка улиц для парковок ===
function loadParkingStreets() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php',
      type: 'POST',
      data: { 
        ctr: 'parking_floors', 
        act: 'jsoon_street' 
      },
      dataType: 'json',
      success: (response) => {
        if (Array.isArray(response)) {
          streetLists.parking = response;
          populateStreetSelect('select_p', response);
          
          // Если текущая страница парковок, заполняем street_pp
          if ($('body').hasClass('parking')) {
            populateStreetSelect('street_pp', response);
          }
          resolve();
        } else {
          reject('Неверный ответ для парковок');
        }
      },
      error: () => reject('Ошибка загрузки парковок')
    });
  });
}

// === Загрузка улиц для аренды помещений ===
function loadCommercialStreets() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php',
      type: 'POST',
      data: { 
        ctr: 'rentobjects',
        act: 'jsoon_street' 
      },
      dataType: 'json',
      success: (response) => {
        if (Array.isArray(response)) {
          streetLists.commercial = response;
          populateStreetSelect('select_r', response);
          
          // Если текущая страница аренды, заполняем street
          if ($('body').hasClass('rent-commercial')) {
            populateStreetSelect('street', response);
          }
          resolve();
        } else {
          reject('Неверный ответ для коммерческих улиц');
        }
      },
      error: () => reject('Ошибка загрузки коммерческих улиц')
    });
  });
}

// === Загрузка улиц для основного селекта #street ===
function loadMainStreets() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php',
      type: 'POST',
      data: { 
        ctr: 'rentobjects',
        act: 'jsoon_street' 
      },
      dataType: 'json',
      success: (response) => {
        if (Array.isArray(response)) {
          streetLists.main = response;
          populateStreetSelect('street', response);
          resolve();
        } else {
          reject('Неверный ответ для основных улиц');
        }
      },
      error: () => reject('Ошибка загрузки основных улиц')
    });
  });
}

// === Заполнение select'а улицами ===
function populateStreetSelect(selectId, list) {
  const $select = $(`#${selectId}`);
  if (!$select.length) return;

  $select.empty();
  list.forEach(street => {
    $select.append(new Option(street.name, street.id));
  });
  $select.niceSelect('update');
}

// === Заполнение формы из URL ===
// === Заполнение формы из URL ===
function populateFormFromURL() {
  const params = new URLSearchParams(window.location.search);
  
  // Обрабатываем все параметры формы
  const formFields = {
    'p': '#rent_home_id',
    'pp': '#pp',
    'area_min': 'input[name="area_min"]',
    'area_max': 'input[name="area_max"]',
    'separate_entrance': 'select[name="separate_entrance"]',
    'floor': 'select[name="floor"]',
    'form_rect': 'select[name="form_rect"]',
    'street': 'select[name="street"]',
    'order': 'input[name="order"]',
    'nav3': '#select_p',
    'street_pp': 'select[name="street_pp"]'
  };

  // Заполняем все поля формы
  Object.entries(formFields).forEach(([param, selector]) => {
    if (params.has(param)) {
      const value = params.get(param);
      const $field = $(selector);
      
      if ($field.length) {
        isProgrammaticChange = true;
        
        if ($field.is('select')) {
          $field.val(value).niceSelect('update');
        } else if ($field.is('input[type="radio"], input[type="checkbox"]')) {
          $field.filter(`[value="${value}"]`).prop('checked', true);
        } else {
          $field.val(value);
        }
        
        isProgrammaticChange = false;
      }
    }
  });

  // Синхронизация связанных селектов
  if ($('body').hasClass('rent-commercial') && params.has('street')) {
    isProgrammaticChange = true;
    $('#select_r').val(params.get('street')).niceSelect('update');
    isProgrammaticChange = false;
  }

  if ($('body').hasClass('parking') && params.has('nav3')) {
    isProgrammaticChange = true;
    $('select[name="street_pp"]').val(params.get('nav3')).niceSelect('update');
    isProgrammaticChange = false;
  }
}






// === Настройка обработчиков событий ===
function setupEventHandlers() {
  // Синхронизация основного селекта street с select_r (аренда)
  $form.on('change', 'select[name="street"]', function() {
    if ($('body').hasClass('rent-commercial')) {
      const value = $(this).val();
      isProgrammaticChange = true;
      $('#select_r').val(value).niceSelect('update');
      isProgrammaticChange = false;
      updateURL();
      loadData();
    }
  });

  // Синхронизация основного селекта street_pp с select_p (парковки)
  $form.on('change', 'select[name="street_pp"]', function() {
    if ($('body').hasClass('parking')) {
      const value = $(this).val();
      isProgrammaticChange = true;
      $('#select_p').val(value).niceSelect('update');
      isProgrammaticChange = false;
      updateURL();
      loadData();
    }
  });

  // Для аренды помещений
  $('#select_r').on('change', function() {
    if (isProgrammaticChange) return;
    
    const streetValue = $(this).val();
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/rent-commercial/')) {
      // Если уже на странице аренды - обновляем данные
      isProgrammaticChange = true;
      $('select[name="street"]').val(streetValue).niceSelect('update');
      isProgrammaticChange = false;
      updateURL();
      loadData();
    } else {
      // Если на другой странице - переходим
      const newPath = '/rent-commercial/';
      const params = new URLSearchParams({ street: streetValue });
      history.pushState(null, '', `${newPath}?${params}`);
      window.location.href = `${newPath}?${params}`;
    }
  });

  // Для парковок
  $('#select_p').on('change', function() {
    if (isProgrammaticChange) return;
    
    const streetValue = $(this).val();
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/parking/')) {
      // Если уже на странице парковок - обновляем данные
      isProgrammaticChange = true;
      $('select[name="street_pp"]').val(streetValue).niceSelect('update');
      isProgrammaticChange = false;
      updateURL();
      loadData();
    } else {
      // Если на другой странице - переходим
      const newPath = '/parking/';
      const params = new URLSearchParams({ nav3: streetValue });
      history.pushState(null, '', `${newPath}?${params}`);
      window.location.href = `${newPath}?${params}`;
    }
  });

  // Обработка других полей формы
  $form.on('change', 'input, select:not([name="street"], [name="street_pp"])', handleFormChange);
 
  $(window).on('popstate', () => {
    populateFormFromURL();
    loadData();
  }); 

  $('body').on('click', '#showmap, #hidemap', toggleMap);
}

// === Обработка изменения формы с debounce ===
function handleFormChange() {
  if (isProgrammaticChange) return;

  clearTimeout(formChangeTimeout);
  formChangeTimeout = setTimeout(() => {
    updateURL();
    loadData();
  }, DEBOUNCE_DELAY);
}

 
// === Обновление URL без перезагрузки страницы ===
function updateURL() {
  const formData = new FormData($form[0]);
  const params = new URLSearchParams();
  
  // Добавляем только заполненные параметры
  for (const [key, value] of formData.entries()) {
    if (value && value !== '0' && key !== 'rent_home_id') { // Исключаем пустые и rent_home_id
      params.set(key, value);
    }
  }
  
  // Добавляем специальные параметры
  const selectPValue = $('#select_p').val();
  if (selectPValue) params.set('nav3', selectPValue);
  
  const selectRValue = $('#select_r').val();
  if (selectRValue) params.set('street', selectRValue);
  
  // Всегда добавляем параметры пагинации
  params.set('p', $form.find('input[name="p"]').val() || '1');
  params.set('pp', $form.find('input[name="pp"]').val() || '10');
  
  // Удаляем дублирующиеся параметры
  const uniqueParams = new URLSearchParams(params.toString());
  
  // Обновляем URL
  history.replaceState(null, '', `${basePath}?${uniqueParams.toString()}`);
}

// === Загрузка данных ===
function loadData() {
  sendAjaxForm({
    resultto: 'rent_search_result',
    formid: 'rentsearch',
    url: 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php',
    data: {
      ctr: '<?=$v['ctr']?>',
      act: '<?=$v['act']?>',
      nav3: $('#select_p').val()
    }
  });
}

// === Универсальная AJAX форма ===
// === Универсальная AJAX форма ===
function sendAjaxForm({ resultto, formid, url, data = {} }) {
  // Сериализация данных формы
  const formData = $(`#${formid}`).serializeArray().reduce((acc, { name, value }) => {
    acc[name] = value;
    return acc;
  }, {});

  // Добавляем значение select_r в formData
  const selectRValue = $('#select_r').val();
  if (selectRValue) {
    formData.street = selectRValue;
  }

  // Формируем ключ кэша
  const cacheKey = `${window.location.pathname}?${$.param({ ...data, ...formData })}`;
  
  // Проверка кэша
  if (ajaxCache[cacheKey] && Date.now() - ajaxCache[cacheKey].timestamp < CACHE_EXPIRATION) {
    handleAjaxResponse(resultto, ajaxCache[cacheKey].data);
    return;
  }

  // Подготовка UI перед загрузкой
  const $resultDiv = $(`#${resultto}`);
  $resultDiv.css('opacity', '0.5'); // Устанавливаем полупрозрачность
  $progressBar.css({
    'display': 'flex',
    'justify-content': 'center',
    'align-items': 'center',
    'position': 'absolute',
    'top': $resultDiv.offset().top,
    'left': $resultDiv.offset().left,
    'width': $resultDiv.outerWidth(),
    'height': $resultDiv.outerHeight(),
    'z-index': 1000,
    'background-color': 'rgba(255, 255, 255, 0.7)'
  }).show();

  // Отправка данных
  $.ajax({
    url,
    type: 'POST',
    dataType: 'html',
    data: { ...data, ...formData }
  })
  .done(response => {
    ajaxCache[cacheKey] = { data: response, timestamp: Date.now() };
    handleAjaxResponse(resultto, response);
  })
  .fail(() => {
    $(`#${resultto}`).html('<div class="error">Ошибка загрузки. <button class="retry-btn">Повторить</button></div>');
  })
  .always(() => {
    $progressBar.hide();
    $resultDiv.css('opacity', '1'); // Возвращаем полную непрозрачность
  });
}


// === Обработка повторной загрузки ===
$(document).on('click', '.retry-btn', loadData);

// === Обновление цвета селекта ===
function updateSelectRColor() {
  const $selectNav = $('.select_nav');
  $selectNav.removeClass('rent-commercial parking');
  
  const path = window.location.pathname;
  if (path.includes('/rent-commercial/')) {
    $selectNav.addClass('rent-commercial');
  } else if (path.includes('/parking/')) {
    $selectNav.addClass('parking');
  }
  
  $('#select_r').niceSelect('update');
}

// === Обработка HTML-ответа ===
function handleAjaxResponse(resultto, response) {
  const $resultDiv = $(`#${resultto}`);
  
  // Вставляем содержимое
  $resultDiv.html(response).fadeIn(600, () => {
    // После завершения анимации
    triggerPostLoad();
    updateSelectNavStyle();
  });
}

// === Переключение карты ===
function toggleMap(e) {
  e.preventDefault();
  const $mapElements = $('#maprentobjects, #showmap, #hidemap');
  if ($mapElements.length) {
    $mapElements.fadeToggle(300);
  }
}

// === Универсальный post-load триггер ===
function triggerPostLoad() {
  initPlugins();
  initSliders();
 // initModals();
}

// === Инициализация плагинов ===
function initPlugins() {
  // Основные селекты
  $('select:not(.nonice)').niceSelect();
  
  // Стилизация select_nav
  const path = window.location.pathname;
  if (path.includes('/rent-commercial/')) {
    $('#select_r_container .nice-select').css('background-color', '#FF4444');
  } else if (path.includes('/parking/')) {
    $('#select_p_container .nice-select').css('background-color', '#44FF44');
  }
}

// === Инициализация слайдеров Swiper ===
function initSliders() {
  if ($('.mySwiper').length) {
    new Swiper(".mySwiper", {
      spaceBetween: 30,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      }, 
      pagination: {
        el: ".swiper-pagination"
      },
      mousewheel: true, 
      keyboard: true
    });
  }
}


 
// === Инициализация модальных окон ===
function initModals() {
  $("a.ifrasme").fancybox({
    maxWidth: 800,
    maxHeight: 12600,
    width: '1000px',
    height: '10000px',
    closeClick: true
  });

  $('.iframe').magnificPopup({
    type: 'iframe',
    removalDelay: 300,
    mainClass: 'mfp-no-margins mfp-with-zoom',
    tLoading: 'Загрузка #%curr%...',
    callbacks: {
      open: function() {
        location.href = location.href.split('#')[0] + "#pop";
      }
    }
  });
}