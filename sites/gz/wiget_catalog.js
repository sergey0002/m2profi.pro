   if (!window.$) {
       var script = document.createElement('script');
       script.src = "https://em.m2profi.pro.test/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js";
       document.head.appendChild(script);
   }
   
   
    


 
  
   $(document).ready(function() {
 
       /*
       resultto - ид тега для загрузки результата
       formid - ид формы 
       url - 
       append - добавлять к содержимому ajax
       */
       function sendAjaxForm(resultto, formid, url, append = 1, progressid = 'progressbar', preload = '', postload = '') {
           if (!append) {
               // $('#' + resultto).html('');
           }
      
           // Получаем гет переменные 
           function gget(qs) {
               qs = qs.split("+").join(" ");
               var params = {},
                   tokens,
                   re = /[?&]?([^=]+)=([^&]*)/g;
               var query = '';
               while (tokens = re.exec(qs)) {
                   params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]); // массив 
                   query = query + '&' + decodeURIComponent(tokens[1]) + '=' + decodeURIComponent(tokens[2]); // гет строка
               }
               return query;
           }
           var url_query = gget(document.location.search);
		   
           $.ajax({
			   url: url + url_query, //url страницы (action_ajax_form.php)
			   async: true,
			   type: "GET", //метод отправки
			   dataType: "html", //формат данных
			   cache: false,
			   
			   
			   xhr: function () {
					var xhr = new window.XMLHttpRequest();
					//Download progress
					xhr.addEventListener("progress", function (evt) {
						
						if (evt.lengthComputable) { 
							console.log(percentComplete);
							var percentComplete = evt.loaded / evt.total;
							progressElem.html(Math.round(percentComplete * 100) + "%");
						}
					}, false);
					return xhr;
				},
                data: $("#" + formid).serialize(), // Сеарилизуем объект
			    beforeSend: function () {
				     if (preload) {preload();}
				},
				complete: function () { 
					 if (postload) { postload(); }
				},
			    error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.responseText);
					alert(thrownError);
				},

	
               success: function(response) { //Данные отправлены успешно
                   $("#" + resultto).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка    
                   /////////////  ЗАТЫЧКА ДЛЯ СЕЛЕКТОВ!!!
                   $('#' + resultto).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
                   $('#' + resultto).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены
                   if (append) {
                       $('#' + resultto).append(response);
                   } else {
					   
					     $('#' + resultto).html(response);  
						
                       
                   }
                   $("#" + resultto).prop('disabled', ''); // Блокируем селекты в которые предстоит загрузка    
                  
                 ;
                   // Затычка преопределение Fancybox			  
                   if (window.innerWidth >= 1000) {
                       $("a.iframe").fancybox({
                           maxWidth: 600,
                           maxHeight: 600,
                           width: '1000px',
                           height: '70%',
                           closeClick: true,
                           'scrolling': 'yes',
                       });
                   } else {
                       $('a.iframe').magnificPopup({type:'iframe',
						  mainClass: 'my-mfp-zoom-in',
						   tLoading: 'Загрузка #%curr%...',
							callbacks: {
							open: function() {},
							close: function() {},
							open: function() { location.href = location.href.split('#')[0] + "#pop"; },
							beforeOpen: function() {
							var $triggerEl = $(this.st.el),
								newClass = $triggerEl.data("modal-class");
							if (newClass) {
								this.st.mainClass = this.st.mainClass + ' ' + newClass;
							}
						}
						  }
					  });
                   }
				   
				     
				   
				   
				   
 

  
  
  
               },
               error: function(response) { // Данные не отправлены
                   // $('#'+resultto).html('Ошибка. Данные не отправлены.');
                   //alert('ajax error');
                   //$('#'+progressid).hide();
                   //$('#'+progressid).prop("display", 'none !important;');
                   //$('#' + progressid).addClass('hide');
               }
           });
       }
	   
	   
	   
	   
	   
	    // Метод загрузки данных!
	   function loaddata()
	   {
		   console.log('------------ЗАГРУЗКА ДАННЫХ');
		   preload = function ()
		   {
				$('#progressbar' ).addClass('active');
				$('#progressbar' ).show();
				$('#zapisdata').removeClass('active');
		   }
		   postload  = function ()
		   {
			 
				setTimeout(function(){ 
					$('#progressbar' ).removeClass('active');
					$('#progressbar' ).hide();
					$('#zapisdata').addClass('active');
				}, 500);
		   }
		   sendAjaxForm('zapisdata', 'filtrform', 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=apartments&act=data', 0, 'progressbar', preload , postload ); 

		  
		   //  sendAjaxForm('zapisdata', 'filtrform', 'https://em.m2profi.pro.test/sahmatka/ajax_actions.php?load=data&controller=catalog', 0, 'progressbar', preload , postload ); 
	   }
	   
	   
	   
       /*
       select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 
       АНОНИМНАЯ ФУНКЦИЯ АРГУМЕНТ В СЛУЧАЕ УСПЕШНОЙ ЗАГРУЗКИ АЯКС
       - Выбирать элементы

       */
       function relate_ajax_select(th, select_id_list, formid = 'filtrform') {
           // Получаем массив селектов которые затрагивает данный 
           var array = select_id_list.split(",");
           var controller = $('#' + formid).attr('data-controller');
           if (array) {
               // Цикл по массиву селектов ПРЕДВАРИТЕЛЬНО БЛОКИРУЕМ ВСЕ 
               array.forEach(function(item, i, arr) {
                   if (item && $("#" + item).prop('id') != $(th).prop('id')) {
                       // alert( i + ": " + item + " (массив:" + arr + ")" );
                       // 
                   }
               });
               //
               // Цикл по массиву селектов
               array.forEach(function(item, i, arr) {
                   if (item) {
                       oldval = $("#" + item).val(); // Старое значение селекта сохраняем!
                       var preload = function(item) {
                               //alert($('#'+item).html());
                               //$('#'+item).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены
                               //$('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
                           }
                           //ЦИКЛИЧЕСКИЙ РЕФРЕШ ПОЛУЧАЕТСЯ ТАК КАК ЗАДАЧА МАЛ ВЫЗЫВАЕ АЯКС !)
                           //  sendAjaxForm(item, formid, 'https://em.m2profi.pro.test/sahmatka/ajax_actions.php?load=' + item + '&controller=' + controller, 1, 'progressbar', preload()); // Грузим содержимое селек
					   
					     sendAjaxForm(item, formid, 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=apartments&act=' + item, 1, 'progressbar', preload()); // Грузим содержимое селек
                       // РАЗБлокируем селекты в которые предстоит загрузка   
                       $("#" + item).prop('disabled', '');
					  
					  // Последний элемент массива (тут можно грузить данные)
					   if(i==array.length-1){
						  // console.log('Последний select обработан ');
						  loaddata();
						}
                   }
               });
               //
           }
       }
	   
	   

       // Начальная загрузка данных 
       relate_ajax_select('', 'sel_home,sel_rooms_min,sel_rooms_max');
	   
	   //sel_rooms_k,sel_min_price,sel_max_price,sel_floor
       $('#sel_sdan').on('change', function() {
			relate_ajax_select(this, 'sel_home,sel_rooms_max,sel_rooms_min,sel_sdan');
       });
    
       $('#sel_rooms_min').on('change', function() {
           relate_ajax_select(this, 'sel_home,sel_rooms_max,sel_rooms_min,sel_sdan'); //
       });
       $('#sel_rooms_max').on('change', function() {
           relate_ajax_select(this, 'sel_home,sel_rooms_max,sel_rooms_min,sel_sdan'); //
       });
       //sel_rooms_k,sel_min_price,sel_max_price,sel_floor
       $('#sel_home').on('change', function() {
          relate_ajax_select(this, 'sel_home,sel_rooms_max,sel_rooms_min,sel_sdan');
       });
    
	
	   // ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
       $("#filtrform input,#filtrform select").change(function() {
		//	loaddata();
       });
	   
	   
   });
