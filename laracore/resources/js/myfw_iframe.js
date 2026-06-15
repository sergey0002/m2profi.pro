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





 




// $FILED->IMAGE!

 $(document).ready(function() {
	
	
 
		
		
	
 
 $(document).on('change', '.fw_file', function () {
 
	if (window.FormData === undefined)
	{
		alert('В вашем браузере FormData не поддерживается')
	} 
	else 
	{
		// Находим родительский DIV .fw_fileupload data-result_src_id filename
		fw_fileupload = $(this).closest('.fw_fileupload');
		 
		//alert( $(fw_fileupload).attr('val') );
		var formData = new FormData();

		formData.append('tdir', $(fw_fileupload).attr('data-tdir') );
		formData.append('many', $(fw_fileupload).attr('data-many') );
		formData.append('skinresult', $(fw_fileupload).attr('data-skinresult') );
		formData.append('filename', $(fw_fileupload).attr('data-filename') );
	 
		$.each($(".fw_file",fw_fileupload)[0].files,function(key, input){
			formData.append('file[]', input);
		});
		 
		$.ajax({
			 xhr: function() {
				var xhr = new window.XMLHttpRequest();

				// Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						// console.log(percentComplete*100+'%');
						progress_id = $(fw_fileupload).attr('data-progress_id')
						if( progress_id )
						{
							$('#'+progress_id).show();
							$('.'+progress_id).show();

							//$('#'+progress_id).fladein(0);
							//$('.'+progress_id).fladein(0);
						
							progress = Math.ceil(percentComplete*100);
						 
							$('#'+progress_id+'_line').css('width',progress+'%');

							$('#'+progress_id).text(progress+'%');
							
							if(progress==100){$('#'+progress_id).fadeOut(100);}
							if(progress==100){$('.'+progress_id).fadeOut(100);}
							
							if(progress==100){$('#'+progress_id).hide(100);}
							if(progress==100){$('.'+progress_id).hide(100);} 
							
							
						}
						
					}
			   }, false); //message
			   
			   // Download progress
			   xhr.addEventListener("progress", function(evt){
				   if (evt.lengthComputable) {
					   var percentComplete = evt.loaded / evt.total;
					   // Do something with download progress
						progress_id = $(fw_fileupload).attr('data-progress_id')
						if( progress_id )
						{
							$('#'+progress_id).show();
							
							progress = Math.ceil(percentComplete*100);
							$('#'+progress_id).text(progress+'%');
							if(progress==100){$('#'+progress_id).hide(100);}
						}
				   }
			   }, false);
			   
			   return xhr;
			},
			type: "POST",
			url: '/sahmatka/upload.php',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			dataType : 'json',
 
			beforeSend: function(){
					result_val_id = $(fw_fileupload).attr('data-result_val_id')
					if( result_val_id )
					{
						$('#'+result_val_id).attr('value','');
						
						submit_elm = $('#'+result_val_id).closest('form').find(':submit');
						submit_elm.attr('value','Загрузка');
						submit_elm.prop('disabled', true);
						submit_elm.css('opacity', 0.5);
					}
					$('.fw_fileupload_result',fw_fileupload).fadeOut();
			},
			success: function(data){
				data.forEach(function(msg) {
					$('.fw_fileupload_result',fw_fileupload).html(msg['message']); // .append
		  
					$('.fw_fileupload_result',fw_fileupload).fadeIn(100);
				 
					
					if(!msg['error'])
					{
						 
						
						result_val_id = $(fw_fileupload).attr('data-result_val_id')
						if( result_val_id )
						{
							$('#'+result_val_id).val(msg['link']);
						}
						
						
						result_file_id = $(fw_fileupload).attr('data-result_file_id')
						if( result_file_id )
						{
							$('#'+result_file_id).html(msg['file']);
						}
						
						result_link_id = $(fw_fileupload).attr('data-result_link_id')
						if( result_link_id )
						{
							$('#'+result_link_id).attr('href',msg['link']);
						}
						
						submit_elm = $('#'+result_val_id).closest('form').find(':submit');
						submit_elm.attr('value','Сохранить');
						submit_elm.prop('disabled', false);
						submit_elm.css('opacity', 1);
						 //submit_elm = $('#'+result_val_id).closest('form').submit();
						
					}
					
				});
			}
		});
		 
	}
});




// Прямое редактирование ссылки на файл
 
// ЗАменяем картинки в случае неудачной загрузки
$('.fw_fileupload_img').on('error', function(){ //срабатывает, если картинка загружена
	$(this).attr('src','/admin/nofoto.png');
});
					
$(".fw_imagelink").change(function() {
	fw_fileupload = $( this ).parent('.fw_fileupload');
	$('.fw_fileupload_img',fw_fileupload).attr('src',$( this ).val() ); 
});
		
		 
		
		
	

















$(".fw_files").change(function(){
	if (window.FormData === undefined)
	{
		alert('В вашем браузере FormData не поддерживается')
	} 
	else 
	{
		// Находим родительский DIV .fw_fileupload
		fw_fileupload = $( this ).parent('.fw_fileupload');
		//alert( $(fw_fileupload).attr('val') );
		var formData = new FormData();

		formData.append('tdir', $(fw_fileupload).attr('data-tdir') );
		formData.append('many', $(fw_fileupload).attr('data-many') );
		formData.append('skinresult', $(fw_fileupload).attr('data-skinresult') );
		formData.append('filename', $(fw_fileupload).attr('data-filename') );
		formData.append('node_id', $(fw_fileupload).attr('data-nodeid') );
	 
		$.each($(".fw_files",fw_fileupload)[0].files,function(key, input){
			formData.append('file[]', input);
		});
		 
		$.ajax({
			 xhr: function() {
				var xhr = new window.XMLHttpRequest();

				// Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						// console.log(percentComplete*100+'%');
						progress_id = $(fw_fileupload).attr('data-progress_id')
						if( progress_id )
						{
							$('#'+progress_id).show();
							$('.'+progress_id).show();

							//$('#'+progress_id).fladein(0);
							//$('.'+progress_id).fladein(0);
						
							progress = Math.ceil(percentComplete*100);
						 
							$('#'+progress_id+'_line').css('width',progress+'%');

							$('#'+progress_id).text(progress+'%');
							
							if(progress==100){$('#'+progress_id).fadeOut(1000);}
							if(progress==100){$('.'+progress_id).fadeOut(1000);}
							
							if(progress==100){$('#'+progress_id).hide(1400);}
							if(progress==100){$('.'+progress_id).hide(1400);} 
							
							
						}
						
					}
			   }, false);
			   
			   // Download progress
			   xhr.addEventListener("progress", function(evt){
				   if (evt.lengthComputable) {
					   var percentComplete = evt.loaded / evt.total;
					   // Do something with download progress
						progress_id = $(fw_fileupload).attr('data-progress_id')
						if( progress_id )
						{
							$('#'+progress_id).show();
							
							progress = Math.ceil(percentComplete*100);
							$('#'+progress_id).text(progress+'%');
							if(progress==100){$('#'+progress_id).hide(300);}
						}
				   }
			   }, false);
			   
			   return xhr;
			},
			type: "POST",
			url: '/sahmatka/upload.php',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			dataType : 'json',
 
			beforeSend: function(){
					result_src_id = $(fw_fileupload).attr('data-result_src_id');
					if( result_src_id )
					{
						// $('#'+result_src_id).fadeOut();
						// $('#'+result_src_id).attr('src','');
					}
					result_val_id = $(fw_fileupload).attr('data-result_val_id');
					 
					$('.fw_fileupload_result',fw_fileupload).fadeOut();
			},
			success: function(data){
				
				var lastfnum = $('.fiprew').length;
				 
				
				data.forEach(function(msg) {
					$('.fw_fileupload_result',fw_fileupload).append(msg['message']); // ДОБАВЛЕНИЕ В ЛОГ 
					$('.fw_fileupload_result',fw_fileupload).fadeIn(300);
				//	$('#'+result_src_id).fadeIn(3000);
					
					if(!msg['error'])
					{
						// msg['idx'] - ид файла
						// msg['link'] - ссылка на файл 
						// msg['message'] - Сообщение
						console.log(msg);
						if(lastfnum == 1) // Первая картинка 
						{
							
						}
						lastfnum = lastfnum+1;
						iprew_name = $(fw_fileupload).attr('data-name');
						iprew = ' <div class="fiprew ui-sortable-handle"><img src="'+msg['icon']+'" /><input type="hidden" name="'+iprew_name+'['+lastfnum+']'+'"  value="'+msg['link']+'" /><br/>'+msg['file']+' </div>';
						 
						
						$('.fw_fileupload_prew',fw_fileupload).append(iprew); // ДОБАВЛЕНИЕ ПРЕВЮ (полюбому добавляется)
						
						// alert(msg['link']);
						
						result_src_id = $(fw_fileupload).attr('data-result_src_id')
						if( result_src_id )
						{
							$('#'+result_src_id).attr('src',msg['link']+'?x='+Math.random());
						}
						
						result_val_id = $(fw_fileupload).attr('data-result_val_id');
						if( result_val_id )
						{
							$('#'+result_val_id).val(msg['link']);
						}
						
					}
					
				});
			}
		});
		 
	}
});




// УДАЛЕНИЕ КАРТИНКИ
$(".fiprew_links_del").click(function()
{
	fw_fileupload = $( this ).parent('.fiprew'); // РОдительский блок с превю
	$(fw_fileupload).fadeOut(300);
	
	var id = $('input',fw_fileupload).attr('value');
	var files2node_id = $('.files2node_id',fw_fileupload).attr('value');
	
	$('input',fw_fileupload).attr('value','');
	
	$.ajax({
		url: '/sahmatka/ajax_router.php?ctr=nodes&act=ajax_delimg',
		method: 'post',
		dataType: 'html',
		data: {'id': id, 'files2node_id':files2node_id},
		success: function(data){
		//	alert(data);
		}
	});

	return false;
});
	

// ДЕЛАЕМ КАРТИНКУ ГЛАВНОЙ
$(".fiprew_links_main").click(function()
{
	fw_fileupload = $( this ).parent('.fiprew'); // РОдительский блок с превю
	//$(fw_fileupload).fadeOut(300);
	
	
	var id = $('input',fw_fileupload).attr('value');
	var files2node_id = $('.files2node_id',fw_fileupload).attr('value');
	
	$('input',fw_fileupload).attr('value','');
	
	$.ajax({
		url: '/admin/ajax_router.php?ctr=nodes&act=ajax_mainimg',
		method: 'post',
		dataType: 'html',
		data: {'id': id, 'files2node_id':files2node_id},
		success: function(data){
			//alert(data);
		}
	});

	return false;
	
	
	return false;
});












	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	



$(".fw_files2").change(function(){
	if (window.FormData === undefined)
	{
		alert('В вашем браузере FormData не поддерживается')
	} 
	else 
	{
		// Находим родительский DIV .fw_fileupload
		fw_fileupload = $( this ).parent('.fw_fileupload');
		//alert( $(fw_fileupload).attr('val') );
		var formData = new FormData();

		formData.append('tdir', $(fw_fileupload).attr('data-tdir') );
		formData.append('node_name', $(fw_fileupload).attr('data-name') );
		formData.append('file_type', $(fw_fileupload).attr('data-file_type') );
 
		formData.append('many', $(fw_fileupload).attr('data-many') );
		formData.append('skinresult', $(fw_fileupload).attr('data-skinresult') );
		formData.append('filename', $(fw_fileupload).attr('data-filename') );
		formData.append('node_id', $(fw_fileupload).attr('data-nodeid') );
	 
		$.each($(".fw_files2",fw_fileupload)[0].files,function(key, input){
			formData.append('file[]', input);
		});
		 
		$.ajax({
			 xhr: function() {
				var xhr = new window.XMLHttpRequest();

				// Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						// console.log(percentComplete*100+'%');
						progress_id = $(fw_fileupload).attr('data-progress_id')
						if( progress_id )
						{
							$('#'+progress_id).show();
							$('.'+progress_id).show();

							//$('#'+progress_id).fladein(0);
							//$('.'+progress_id).fladein(0);
						
							progress = Math.ceil(percentComplete*100);
						 
							$('#'+progress_id+'_line').css('width',progress+'%');

							$('#'+progress_id).text(progress+'%');
							
							if(progress==100){$('#'+progress_id).fadeOut(1000);}
							if(progress==100){$('.'+progress_id).fadeOut(1000);}
							
							if(progress==100){$('#'+progress_id).hide(1400);}
							if(progress==100){$('.'+progress_id).hide(1400);} 
							
							
						}
						
					}
			   }, false);
			   
			   // Download progress
			   xhr.addEventListener("progress", function(evt){
				   if (evt.lengthComputable) {
					   var percentComplete = evt.loaded / evt.total;
					   // Do something with download progress
						progress_id = $(fw_fileupload).attr('data-progress_id')
						if( progress_id )
						{
							$('#'+progress_id).show();
							
							progress = Math.ceil(percentComplete*100);
							$('#'+progress_id).text(progress+'%');
							if(progress==100){$('#'+progress_id).hide(300);}
						}
				   }
			   }, false);
			   
			   return xhr;
			},
			type: "POST",
			url: '/sahmatka/upload.php',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			dataType : 'json',
 
			beforeSend: function(){
					result_src_id = $(fw_fileupload).attr('data-result_src_id');
					if( result_src_id )
					{
						// $('#'+result_src_id).fadeOut();
						// $('#'+result_src_id).attr('src','');
					}
					result_val_id = $(fw_fileupload).attr('data-result_val_id');
					 
					$('.fw_fileupload_result',fw_fileupload).fadeOut();
			},
			success: function(data){
				
				var lastfnum = $('.fiprew').length;
				 
				
				data.forEach(function(msg) {
					$('.fw_fileupload_result',fw_fileupload).append(msg['message']); // ДОБАВЛЕНИЕ В ЛОГ 
					$('.fw_fileupload_result',fw_fileupload).fadeIn(300);
				//	$('#'+result_src_id).fadeIn(3000);
					
					if(!msg['error'])
					{
						// msg['idx'] - ид файла
						// msg['link'] - ссылка на файл 
						// msg['message'] - Сообщение
						console.log(msg);
						if(lastfnum == 1) // Первая картинка 
						{
							
						}
						lastfnum = lastfnum+1;
						iprew_name = $(fw_fileupload).attr('data-name');
						//iprew = ' <tr class="fiprew ui-sortable-handle"> <td>1</td> <td>1</td> <td><img src="'+msg['icon']+'" /><input type="hidden" name="'+iprew_name+'['+lastfnum+']'+'"  value="'+msg['link']+'" /> '+msg['file']+' </td><td></td></tr>';
						 
						iprew = msg['new'];
						$('.filestable',fw_fileupload).append(iprew); // ДОБАВЛЕНИЕ ПРЕВЮ (полюбому добавляется)
						
						// alert(msg['link']);
						
						result_src_id = $(fw_fileupload).attr('data-result_src_id')
						if( result_src_id )
						{
							$('#'+result_src_id).attr('src',msg['link']+'?x='+Math.random());
						}
						
						result_val_id = $(fw_fileupload).attr('data-result_val_id');
						if( result_val_id )
						{
							$('#'+result_val_id).val(msg['link']);
						}
						
					}
					
				});
			}
		});
		 
	}
});










});
	

window.sendAjaxForm = sendAjaxForm;
window.relate_ajax_select = relate_ajax_select;
