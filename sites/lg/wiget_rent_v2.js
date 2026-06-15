   
 
if(!window.$)
{
	var script = document.createElement('script');  
	script.src = "https://em.m2profi.pro.test/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js";  
	document.head.appendChild(script);
}




$( document ).ready(function() {
 
/*
resultto - ид тега для загрузки результата
formid - ид формы 
url - 
append - добавлять к содержимому ajax
*/
function sendAjaxForm(resultto, formid, url,append=1,progressid='progressbar',preload='',postload='') {
  
if(!append){$('#'+resultto).html('');	}
$('#'+progressid).show();
$('#'+progressid).removeClass('hide');
$("#"+resultto).fadeOut(100);


// ПЕРЕНЕСТИ ЭТОТ ФУНКЦИОНАЛ В ВИДЖЕТ ПОСЛЕ ЗАГРУЗКИ ДАННЫХ ВЫДЕЛЯТЬ ГЕТ 
// Получаем гет переменные 
function gget(qs) {
    qs = qs.split("+").join(" ");
    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;
	var query ='';
	
    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])]= decodeURIComponent(tokens[2]); // массив 
		query = query + '&' + decodeURIComponent(tokens[1]) + '=' + decodeURIComponent(tokens[2]); // гет строка
    }

    return query;
}
var url_query = gget(document.location.search);

  
  
  
  

			
    $.ajax({
        url:     url + url_query, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: $("#"+formid).serialize(),  // Сеарилизуем объект
        success: function(response) { //Данные отправлены успешно
		 
        	 
			if(preload)	{	preload();	}
			
			
			$("#"+resultto).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка    
			
			
			/////////////  ЗАТЫЧКА ДЛЯ СЕЛЕКТОВ!!!
			$('#'+resultto).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
			$('#'+resultto).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены
			
			
			if(append)
			{
				$('#'+resultto).append(response);
			}
			else
			{
				$('#'+resultto).html(response);
			}
			$("#"+resultto).prop('disabled', ''); // Блокируем селекты в которые предстоит загрузка    
			$('#'+progressid).hide();

			if(postload){	postload(response);	}
			  //$('select').multipleSelect('refresh');
			 // alert($('#'+resultto).prop('outerHTML') );




 


$("#"+resultto).fadeIn(600);


// Затычка преопределение Fancybox			  
   
     $("a.iframe").fancybox({
            maxWidth    : 600,
            maxHeight   : 12600,
            
            width       : '1000px',
            height      : '10000px',
            closeClick  : true,
        }); 
		
		 
		
		  $('select').niceSelect();
		
		
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
  
  
  
// СНятие чекбокса дома
$('#h_adress').on('change', function() {
	$('#rent_home_id').attr('value',''); // Очистка дома
    sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=rentobjects&act=display_v2',0); // Грузим содержимое селек
});
		
 




    	},
    	error: function(response) { // Данные не отправлены
            // $('#'+resultto).html('Ошибка. Данные не отправлены.');
			//alert('ajax error');
			//$('#'+progressid).hide();
			//$('#'+progressid).prop("display", 'none !important;');
			$('#'+progressid).addClass('hide');




    	}
 	});
}
	
	
/*
select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 


 

АНОНИМНАЯ ФУНКЦИЯ АРГУМЕНТ В СЛУЧАЕ УСПЕШНОЙ ЗАГРУЗКИ АЯКС
- Выбирать элементы

*/
function relate_ajax_select(th,select_id_list,formid='filtrform')
{
	// Получаем массив селектов которые затрагивает данный 
	var array = select_id_list.split(",");
	var controller= $('#'+formid).attr('data-controller');
	
	if(array)
	{
		// Цикл по массиву селектов ПРЕДВАРИТЕЛЬНО БЛОКИРУЕМ ВСЕ 
		array.forEach(function(item, i, arr) {
			
			
			if(item  && $("#"+item).prop('id') != $(th).prop('id') )
			{
				// alert( i + ": " + item + " (массив:" + arr + ")" );
				 // 
			}
		});
		//
 
		// Цикл по массиву селектов
		array.forEach(function(item, i, arr) {		 
		if(item) 
		{
			oldval = $("#"+item).val(); // Старое значение селекта сохраняем!
			
			  
			
			
			var preload = function(item)
			{
				//alert($('#'+item).html());
				
				//$('#'+item).find('optgroup').remove(); // Удаляем все НЕПУСТЫЕ опшены
				//$('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
				
			}			
			 
			
			//ЦИКЛИЧЕСКИЙ РЕФРЕШ ПОЛУЧАЕТСЯ ТАК КАК ЗАДАЧА МАЛ ВЫЗЫВАЕ АЯКС !)
			sendAjaxForm( item , formid , 'https://em.m2profi.pro.test/sahmatka/ajax_actions.php?load='+item+'&controller='+ controller,1,'progressbar',preload(item)); // Грузим содержимое селек
		  
		 
			// РАЗБлокируем селекты в которые предстоит загрузка   
			$("#"+item).prop('disabled', '');
			 
		}
	});
	//
	
	}
 

}



 
 
 
// Начальная загрузка данных 
//relate_ajax_select('','sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max');
sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=rentobjects&act=display_v2',0); // Грузим содержимое селек



// Начальная загрузка данных 
sendAjaxForm( 'street' , 'rentsearch' , 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=rentobjects&act=sel_street',0); // Грузим содержимое селек





 



// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#rentsearch input,#rentsearch select" ).change(function() {
	   $('#rent_home_id').attr('value',''); // Очистка дома
       sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=rentobjects&act=display_v2',0,'progressbar','',function postload()
 {
	 /*
	 $(document).snowfall({
            flakeCount: 1,
            image :"/images/snowfall/7.png", 
            minSize: 5, 
            maxSize:20,
            round: true,
            shadow: false,
			maxSpeed: 3,	
        });
		*/
 }); // Грузим содержимое селек

   // $('#maprentobjects').hide();
});

// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#rent_search_submit" ).click(function() {
	$('#rent_home_id').attr('value',''); // Очистка дома
    sendAjaxForm( 'rent_search_result' , 'rentsearch' , 'https://em.m2profi.pro.test/sahmatka/ajax_router.php?ctr=rentobjects&act=display_v2',0); // Грузим содержимое селек
    //  $('#maprentobjects').hide();
   
   return false;
});





$('body').on('click', '#showmap', function(){
  $('#rentobjects').fadeOut(100);
  $('#showmap').fadeOut(100);
  $('#maprentobjects').fadeIn(600);
  $('#hidemap').fadeIn(300);
   return false;
});

$('body').on('click', '#hidemap', function(){
  $('#rentobjects').fadeIn(600);
  $('#showmap').fadeIn(300);
  $('#maprentobjects').fadeOut(100);
  $('#hidemap').fadeOut(100);
   return false;
});





 $('#sel_rooms_min').on('change', function() {
 // --relate_ajax_select(this,'sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max'); //
});

$('#sel_rooms_max').on('change', function() {
 //--relate_ajax_select(this,'sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max'); //
});



/*
$('#sel_rooms_k').on('change', function() {
 relate_ajax_select(this,'sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max'); //
});


$('#sel_min_price').on('change', function() {
 relate_ajax_select(this,'sel_min_price,sel_rooms_k,sel_max_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max'); //
});


$('#sel_max_price').on('change', function() {
 relate_ajax_select(this,'sel_max_price,sel_rooms_k,sel_min_price,sel_floor,sel_home,sel_rooms_min,sel_rooms_max');
});


$('#sel_floor').on('change', function() {
 relate_ajax_select(this,'sel_floor,sel_rooms_k,sel_min_price,sel_max_price,sel_home,sel_rooms_min,sel_rooms_max');
});
*/

//sel_rooms_k,sel_min_price,sel_max_price,sel_floor
$('#sel_home').on('change', function() {
 //--relate_ajax_select(this,'sel_home,sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_rooms_min,sel_rooms_max');
});

 
//sel_rooms_k,sel_min_price,sel_max_price,sel_floor
$('#sel_sdan').on('change', function() {
 //--relate_ajax_select(this,'sel_home,sel_rooms_k,sel_min_price,sel_max_price,sel_floor,sel_rooms_min,sel_rooms_max');
});





// Плавная прокрутка
$(".scrollto").on("click", function(e){
    e.preventDefault();
    var anchor = $(this).attr('href');
 
    $('html, body').stop().animate({
        scrollTop: $(anchor).offset().top - 100
    }, 300);
	return false;
});





});


 
