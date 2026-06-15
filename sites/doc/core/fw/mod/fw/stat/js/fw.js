 // Авто выравниание по высоте атрибут data-equal="селектор дочернего" $('.yourelements').equalHeights(); https://github.com/mattbanks/jQuery.equalHeights/blob/master/example/example.html
  (function($) {

    $.fn.equalHeights = function() {
        var maxHeight = 0,
            $this = $(this);

        $this.each( function() {
            var height = $(this).innerHeight();

            if ( height > maxHeight ) { maxHeight = height; }
        });

        return $this.css('height', maxHeight);
    };

    // auto-initialize plugin
    $('[data-equal]').each(function(){
        var $this = $(this),
            target = $this.data('equal');
        $this.find(target).equalHeights();
    });

})(jQuery);




    $(document).ready(function() {
   
 
 
 $('.popupform').magnificPopup({
  type:'inline',
   fixedContentPos: true  
			 
 });

   
	
	 $("form").submit(function () {
		 
		 $.magnificPopup.open({  items: {	src: '#pupup_ok'  },  type: 'inline' }, 0);
		 
	
		 // Подклеиваем значения Button 
		 var formdata = $(this).serialize();
		 
		// alert(formdata); 
 
		 // if( $(this).valid() )
		 // {
			//	var formdata = $(this).serialize();
				var act = $(this).attr('action');
				$.ajax({
				url: act,
				method: 'post',
				dataType: 'html',
				data: formdata,
				success: function(data){
				 
					// $.magnificPopup.close();
					$.fancybox.close();
					//alert("Заявка принята");
					//$(this).text("Заявка принята");
					//$(this).css('opacity', '0.3')

					 
				},
				complete: function(data){
				 
					 
					$.magnificPopup.open({  items: {	src: '#pupup_ok'  },  type: 'inline' }, 0);
				}
				  
				});
			   
			   return false;
		 // }
 
     }); 
	 $('.submitform').on('click', function(t) {
        var form = $(this).closest("form");
		$( form ).submit();
		return false;
     });
 
  
	   
			
    });
	
