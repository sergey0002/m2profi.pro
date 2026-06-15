<script>



 // Карта без зума и открытия
if(window.screen.width>100)
{
	// $( "#open_modal" ).hide();
	 umapx();
}


 
$.when( updatejsoon() ).done(function() {
		// $('#zoomcontainerx').smartZoom();
			//alert(2);
	
});
$( document ).ready(function() {
	
	
	
	
	 umapx();
	
	
});

   
  
		
 

		
 

  
	// Открытие карты 
	$( "#open_modal" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
	    var modal = $('#psevsdomodal');
		  // Окно закрыто обрабатываем клик
		  if(!modal.hasClass('psevsdomodal_open'))
		  {
			   $( "#map" ).fadeOut(0); // Скрываем карту
			   modal.addClass('psevsdomodal_open'); // Помещаем ее в экран по высоте
			    $( "#open_modal" ).hide(300);
			     $( "#map" ).fadeIn(600); // Плавно показываем
			     $("#close_modal").show(300);
				$('html').css({
					//overflow: 'hidden',
					//height: '100%'
				});
  
			 $('#zoomcontainerx').smartZoom();
			 umapx();
			 return false;
		  };
	});
	
	// Закрытие карты
	$( "#close_modal" ).click(function(){ // задаем функцию при нажатиии на элемент <button>

	    var modal = $('#psevsdomodal');
		  // Окно закрыто обрабатываем клик
		  if(modal.hasClass('psevsdomodal_open'))
		  {
			   $( "#map" ).fadeOut(0); // Скрываем карту
			   $('#zoomcontainerx').smartZoom("destroy"); // Откл плагин
			   $('#zoomcontainerx').css("transform",''); //Сброс зума
				$( "#map" ).fadeIn(600); // Плавно показываем
			   modal.removeClass('psevsdomodal_open');
			   $( "#close_modal" ).hide(300);
			    $( "#open_modal" ).show(300);
				
				$('html').css({
					//overflow: 'scroll',
				//	height: '100%'
				});
				
			   return false;
		  };
		  
		  
	});





   
 

  
function umapx()
{ 
	// return false; ///  ВЫРУБАЕМ КАРТУ (КЛИКИ ПО КАРТЕ)
		 
 

  

 

		var dragging = false;
		(".scheme polygon").on("mousedown touchstart", function(e) {
		 
		 if(( e.which ==1 || e.which ===0 ) && $(this).hasClass("insale") ) // только левая
		 {	  
		  var x = e.screenX;
		  var y = e.screenY;
  
		  dragging = false;
  
		  (".scheme polygon").on("mousemove touchmove", function(e) {
  
			 // console.log('123');
			if (Math.abs(x - e.screenX) > 10 || Math.abs(y - e.screenY) > 10) {
			  dragging = true;
			 // console.log(1);
			}
		  });
		 }
		 else
		 {
			 dragging = false;
			// console.log(2);
		 }
		});

			// Перетаскивание на тач устройствах
			('.scheme polygon').on('touchmove', function() {
				 dragging = true;
			});
  

		// КЛики по полигонам
		(".scheme polygon").on("mouseup touchend", function(e) 
		{ 
		  (".scheme polygon").off("mousemove");
		   
		  if(false==dragging) // Если не перетаскивание (перетаскивание меньше 10 писселей при клике)
		  {
			  // ФОрма брони / редактирования
				$.magnificPopup.open({
				 closeMarkup:"<button title='%title%' style='top:-60px;' type='button' class='mfp-close myDisplayOverride'><img src='/sahmatka/images/x.png'></button>",
				  items: {
					  src: '<?=$GLOBALS['config']['domains']['gl']?>/sahmatka/iframe_router.php?ctr=landplots&act=order&polygon_id='+$(this).attr('data-id')+'&a=<?=$_SERVER['SCRIPT_NAME']?>'
					 
				  },
				   fixedContentPos: true,
				  type: 'iframe',
				   callbacks: {
						close: function(){
							updatejsoon();
						},
						beforeAppend:function() 
						{
							 
							this.content.find('iframe').on('load', function() {
							 //var h = this.contentWindow.document.body.offsetHeight + 'px';
							// alert(h);
							});
							//$('iframe').load(function() {
								//alert(1);
								//this.style.height =
								//
							//});
						}
				  }
				}, 0);
				
				
				
				('body').on("click",'.mfp-close',function(){
					$.magnificPopup.close();
				})
				('.scheme-popup').hide();
				('.scheme-item[data-id=' + $(this).data('id') + ']').trigger('click');
		  }
		});
	
	
	
	
	

		//if(window.screen.width>100)
		//{
		 
			$('[relmap~=tooltip]').tooltipster({
			theme: 'Borderless',
			'maxWidth': 300, // set max width of tooltip box
			contentAsHTML: true, // set title content to html
			trigger: 'custom', // add custom trigger
			triggerOpen: { // open tooltip when element is clicked, tapped (mobile) or hovered
					   click: true,
					   tap: true,
					   mouseenter: true
				   },
				   triggerClose: { // close tooltip when element is clicked again, tapped or when the mouse leaves it
					   click: true,
					   scroll: false, // ensuring that scrolling mobile is not tapping!
					   tap: false,
					   mouseleave: true
				   }
			});
   
		//}
   
   
   
		//var scroll_zoom = new ScrollZoom($('#zoomcontainer'),2,0.03)
}
	 
 

 

 

 

 

function updatejsoon()
{	




	$.ajax({
	url: '<?=$GLOBALS['config']['domains']['gl']?>/sahmatka/ajax_router.php?ctr=landplots&act=jsoondata',
		method: 'get',
		dataType: 'json',
		success: function(data){
				$.each(data, function(key, val) {
				
				///$('#polygon'+key).css('opacity','0.5'); // Задаем прозрачность блоков ид которых есть в базе
				$('#polygon'+key).css('fill',val.status_color); // Задаем прозрачность блоков ид которых есть в базе
				$('#polygon'+key).css('cursor','pointer'); // Задаем прозрачность блоков ид которых есть в базе
				
				$('#polygon'+key).addClass(val.class);
				$('#polygon'+key).attr('title',val.tooltip);
				$('#polygon'+key).attr('relmap','tooltip');
			
			});
		},
		complete:
		function(data){
			 //return false; ///  ВЫРУБАЕМ ВСПЛЫВАЮЩИЕ ОКНА
			if(window.screen.width>1000)
			{
				$('[relmap~=tooltip]').tooltipster({
				theme: 'Borderless',
				'maxWidth': 270, // set max width of tooltip box
				'minWidth': 270, // set max width of tooltip box
				contentAsHTML: true, // set title content to html
				trigger: 'custom', // add custom trigger
				triggerOpen: { // open tooltip when element is clicked, tapped (mobile) or hovered
					   click: true,
					   tap: true,
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
		}
	});
	return true;
} 
</script>