  
 $(document).ready(function() {
 

     $("form").submit(function() {
 
         // Подклеиваем значения Button 
         var formdata = $(this).serialize();

       
         var act = $(this).attr('action');
		 
		 if(!act){act='ajax_form.php';}
		 
         $.ajax({
             url: act,
             method: 'post',
             dataType: 'html',
             data: formdata,
             success: function(data) {

				$.fancybox.close();
				 
                 // $.magnificPopup.close();
                 //$.fancybox.close();
                 //alert("Заявка принята");
                 //$(this).text("Заявка принята");
                 //$(this).css('opacity', '0.3')
 
             },
             complete: function(data) {


                $.fancybox.open({
					src: '#callback_ok',
					type: 'inline'
				});
				
             }

         });

         return false;
         // }

     });
	 
	 /*
     $('.submitform').on('click', function(t) {
         var form = $(this).closest("form");
         $(form).submit();
         return false;
     });
	*/



 });
