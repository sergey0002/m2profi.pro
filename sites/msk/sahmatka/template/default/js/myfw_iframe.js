// ТРЕБУЕТ JQ 

 
/*
resultto - ид тега для загрузки результата
formid - ид формы 
url - 
append - добавлять к содержимому ajax
*/ 
        
function sendAjaxForm(resultto,formid,url,append=1,progressid='progressbar',predcallback,predcallback2,postcallback) 
{ 

	if(predcallback){predcallback(resultto);}

	 if(!append){$('#'+resultto).html('');	}
	
	// Прогрессбар по умолчанию иначе при вызове '' ошибка
	if(!progressid){  progressid = 'progressbar';}
	  	
	// $('#'+resultto).hide(0);
	// $('#'+resultto).css('opacity','0.3');
	$('#'+progressid).show();
 
	
  
	if( url==''){ url = $('#'+formid).attr('data-ajaxurl' ); }
	
    $.ajax({
        url:     url, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: $("#"+formid).serialize(),  // Сеарилизуем данные формы
		async:true,
        success: function(response) //Данные отправлены успешно
		{ 
			if(predcallback2){predcallback2(resultto);	}
			if(append)
			{
				$('#'+resultto).append(response);
			}
			else
			{
				 $('#'+resultto).html(response);
			}
    	},
		complete: function(response) 
		{
			// При загрузке dom
			$('#'+resultto).ready(function() {
				$('#'+progressid).hide();
				// $('#'+resultto).css('opacity','1');
				$('#'+resultto).show(500);
			}); 
	 	
			if(postcallback){ postcallback(resultto); }
		},
    	error: function(response) { // Данные не отправлены
			alert('ajax error');
			$('#'+progressid).hide();
    	},
		 
 
		 
		 
 	});

}
	
	
/*
select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 
*/
function relate_ajax_select(th,select_id_list,formid='ajaxform')
{ 
	console.log('relate_ajax_select : '+select_id_list);
 
	// Получаем массив селектов которые затрагивает данный 
	var array = select_id_list.split(",");
 
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) 
	{
		// alert( i + ": " + item + " (массив:" + arr + ")" );
		console.log('load select - ' + item);
		
		var controller = $('#'+formid).attr('data-controller'); // Контроллер для получения селект данных (action = ид селекта)
		  
		// Перед аякс запросом
		var predcallback = function (item)
		{
		   lastval = $("#"+item+'__original').val();
		}
		
		// Перед загрузкой результата запроса в тег результата
		var predcallback2 = function (item)
		{  
		   $("#"+item).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка  
		   $('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
		}
		
		// После загрузки результата
		var postcallback = function (item){
			 //alert(lastval);
			  $("#"+item).prop('disabled', '');
			  
			  // Если есть такой option value выбираем его 
			  if ( $("#"+item+' > option[value="'+lastval+'"]').length  ) 
			  {
				  if( !$("#"+item+' > option[value="'+lastval+'"]').attr('disabled') )
				  {
				  $("#"+item).val(lastval);
				  }
			  }
		};
		sendAjaxForm( item , formid , '/sahmatka/ajax_router.php?ctr='+controller+'&act='+item , 1 , 'progressbar', predcallback , predcallback2 , postcallback ); // Грузим содержимое селек
	});
	//
}





 

// Начальная загрузка данных 
//relate_ajax_select('','sel_home,sel_section,sel_apartment_num,sel_date');
// sendAjaxForm( 'zapisdata' , 'filtrform' , 'https://em.m2profi.pro.test/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек
/*
$('#ch_arhiv').change(function() {
 relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});
 
// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#filtrform input,#filtrform select" ).change(function() {
  sendAjaxForm( 'zapisdata' , 'filtrform' , 'https://em.m2profi.pro.test/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек
});

*/





 

