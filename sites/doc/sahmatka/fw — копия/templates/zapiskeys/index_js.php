<script>

$( document ).ready(function() {
	
	
/*
resultto - ид тега для загрузки результата
formid - ид формы 
url - 
append - добавлять к содержимому ajax
*/
function sendAjaxForm(resultto, formid, url,append=1,progressid='progressbar') {
 
 
 if(!append){$('#'+resultto).html('');	}
 $('#'+progressid).show();
 	
			
    $.ajax({
        url:     url, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: $("#"+formid).serialize(),  // Сеарилизуем объект
        success: function(response) { //Данные отправлены успешно
		 
        	// result = $.parseJSON(response);
        	//$('#'+resultto).html('Имя: '+result.name+'<br>Телефон: '+result.phonenumber);
			if(append)
			{
				$('#'+resultto).append(response);
			}
			else
			{
				$('#'+resultto).html(response);
			}
			$('#'+progressid).hide();
			
				// Перезагржаем фенси 
				 $('.iframe_rajax').magnificPopup({type:'iframe',
				  removalDelay: 100,
				  fixedContentPos: true, 
				  disableOn:1,
				   tLoading: 'Загрузка #%curr%...',
					callbacks: {
					open: function() {
					  // Will fire when this exact popup is opened
					  // this - is Magnific Popup object
					},
					close: function() {
						// Перезагрузить отображение!
						sendAjaxForm( 'zapisdata' , 'filtrform' , '/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					 
					// e.t.c.
				  }
				   
				  });
				//////////////////////////////////
  
  
  
    	},
    	error: function(response) { // Данные не отправлены
            // $('#'+resultto).html('Ошибка. Данные не отправлены.');
			 alert('ajax error');
			$('#'+progressid).hide();
    	}
 	});
 
 
}
	
	
/*
select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 
*/
function relate_ajax_select(th,select_id_list,formid='filtrform')
{
	// Получаем массив селектов которые затрагивает данный 
	var array = select_id_list.split(",");
 
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) {
		// alert( i + ": " + item + " (массив:" + arr + ")" );
		 $("#"+item).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка  
		 $('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
	});
	//
	var controller= $('#'+formid).attr('data-controller');
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) {		 
		// alert(item);
		 sendAjaxForm( item , formid , '/sahmatka/ajax_actions.php?load='+item+'&controller='+ controller); // Грузим содержимое селек
	// РАЗБлокируем селекты в которые предстоит загрузка   
		  $("#"+item).prop('disabled', '');
	});
	//
}




$('#submitform').hide();


// Начальная загрузка данных 
relate_ajax_select('','sel_home,sel_section,sel_apartment_num,sel_date');
sendAjaxForm( 'zapisdata' , 'filtrform' , '/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек


$('#arhiv').change(function() {
 relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});


$('#pom').change(function() {
 relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});


$('#sel_home').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_section,sel_apartment_num,sel_date');
});


$('#sel_section').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_apartment_num,sel_date');
});

 
 
$('#sel_apartment_num').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_date');
});

 
 


 
 

// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#filtrform input,#filtrform select" ).change(function() {
  sendAjaxForm( 'zapisdata' , 'filtrform' , '<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек
});


});
</script>