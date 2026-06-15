var $ = jQuery;

$(document).ready(function () {

	//$('.lazy').Lazy({
	//	effect: 'fadeIn',
	//	visibleOnly: true
	//});

	//FormStyler
	// $('input, select').styler({
	// 	selectSmartPositioning: false
	// });

	AOS.init({
		offset: 0,
		once: false,
		duration: 1000,
		mirror: false
	});

	//Дата
	var today = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
	var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();
	var today = dd + '.' + mm + '.' + yyyy;
	$('.datepicker-here').datepicker({
		multipleDatesSeparator: " | "
	});
	// .data('datepicker').selectDate(new Date())

	$('.datepicker-here_today').attr('placeholder', today);

	//Маска ввода
	$(".phone-in").inputmask("+7 (999) 999-99-99", { showMaskOnHover: false });
	
	 

	
	$(".timepicker-here").inputmask("99:99", { showMaskOnHover: false });

	// Fixed menu
	var header = $('.header');
	$(window).scroll(function () {
		if ($(window).scrollTop() > 100) {
			header.addClass('fixed');
			$('.circle-blur_top-right').addClass('back');
		} else {
			header.removeClass('fixed');
			$('.circle-blur_top-right').removeClass('back');
		}
	});

	//моб. меню
	$('#btn-mob').on('click', function () {
		$(this).toggleClass('active');
		$('.overlay').stop().fadeToggle();
		$('.mobile-nav').stop().fadeToggle('fast').toggleClass('open');
	});
	$('body').click(function (e) {
		if ($(e.target).closest(".menu-mob, .mobile-nav").length) return;
		$('.menu-mob').removeClass('active');
		$('.mobile-nav').stop().fadeOut('fast').removeClass('open');
		$('.overlay').stop().fadeOut();
	});
	$('.mobile-nav__close, .mobile-nav-menu li a').click(function (e) {
		$('.menu-mob').removeClass('active');
		$('.mobile-nav').stop().fadeOut('fast').removeClass('open');
		$('.overlay').stop().fadeOut();
	});

	$('#btn-lk').on('click', function () {
		$(this).toggleClass('active');
		$('.sidenav').toggleClass('open');
		$('.overlay-page').stop().fadeToggle();
	});
	$('body').click(function (e) {
		if ($(e.target).closest("#btn-lk, .sidenav").length) return;
		$('#btn-lk').removeClass('active');
		$('.sidenav').removeClass('open');
		$('.overlay-page').stop().fadeOut();
	});
	$('.sidenav__close').click(function (e) {
		$('#btn-lk').removeClass('active');
		$('.sidenav').removeClass('open');
		$('.overlay-page').stop().fadeOut();
	});

	// $(".sidenav-dropmenu").mouseenter(
	// 	function () {
	// 		$(this).find('ul').stop(true, true).fadeIn("fast");
	// 		$(this).children().toggleClass('active');
	// 	});
	// $(".sidenav-dropmenu").mouseleave(function () {
	// 	$(this).find('ul').stop(true, true).fadeOut("fast");
	// 	$(this).children().toggleClass('active');
	// }
	// );
	$(".sidenav-dropmenu > a").on('click', function () {
		$(this).closest('li').find('ul').stop(true, true).fadeToggle("fast");
		$(this).toggleClass('active');
		$(this).prev().toggleClass('active');
		return false;
	});
	// $(window).on('load', function () {
	// 	var windowWidth = $(window).width();
	// 	if (windowWidth <= 992) {
	// 		$('.sidenav-menu > li').unbind('mouseenter mouseleave');
	// 	} else {
	// 		$('.sidenav-menu > li').bind('mouseenter mouseleave');
	// 	}
	// });
	// $(window).on('resize', function () {
	// 	var windowWidth = $(window).width();
	// 	if (windowWidth <= 992) {
	// 		$('.sidenav-menu > li').unbind('mouseenter mouseleave');
	// 	} else {
	// 		$('.sidenav-menu > li').bind('mouseenter mouseleave');
	// 	}
	// });

	// $('.sidenav-dropmenu > span').click(function () {
	// 	$(this).toggleClass('active');
	// 	$(this).next().toggleClass('active');
	// 	$(this).parents('li').find(' > ul').toggleClass('open').stop(true, true).fadeToggle();
	// });

	// //прокрутка по якорям
	$('.header-menu a, .footer-menu a, .mobile-nav-menu a').bind("click", function (e) {
		var anchor = $(this);
		$('html, body').stop().animate({
			scrollTop: $(anchor.attr('href')).offset().top - 50
		}, 1000);
		e.preventDefault();
	});

	$('.login-modal-form__password .jq-checkbox').on('click', function () {
		if ($(this).hasClass('checked')) {
			$('#password-input').attr('type', 'text');
		} else {
			$('#password-input').attr('type', 'password');
		}
	});

	//Фильтр
	// $(window).on('load', function () {
	// 	$(".house-table td a").filter(function () {
	// 		var status = $(this).attr('data-status');
	// 		free = { 'background': '#89FFA4' };
	// 		sale = { 'background': '#FF8A90' };
	// 		if (status == 'free') {
	// 			$(this).css(free);
	// 		} else if (status == 'sale') {
	// 			$(this).css(sale);
	// 		}
	// 	});
	// 	$('.house-filter-main input').filter(function () {
	// 		var th = $(this);
	// 		isChecked = $('.house-filter-main input:checked');
	// 		inCheck = isChecked.val();
	// 		td = $('.house-table td a');
	// 		selector = $('.house-table td a[data-room="' + inCheck + '"]');

	// 		if (th.is(':checked')) {
	// 			td.removeClass('stop');
	// 			selector.addClass('active');
	// 			td.not('.active').addClass('stop');
	// 		}

	// 		if (inCheck == 'all') {
	// 			td.not('.active').removeClass('stop');
	// 		}

	// 		// if ( th.is(':checked') ) {
	// 		// 	selector.addClass('active');
	// 		// 	td.not('.active').addClass('stop');
	// 		// } else if ( inCheck == 'all' ) {
	// 		// 	td.removeClass('stop').addClass('active');
	// 		// }
	// 	});
	// });
	// $('.house-filter-main input').change(function () {
	// 	var th = $(this);
	// 	isChecked = $('.house-filter-main input:checked').val();
	// 	inCheck = th.val();
	// 	td = $('.house-table td a');
	// 	selector = $('.house-table td a[data-room="' + inCheck + '"]');

	// 	if (th.prop("checked")) {
	// 		td.removeClass('stop');
	// 		selector.addClass('active');
	// 		td.not('.active').addClass('stop');
	// 	} else {
	// 		selector.removeClass('active');
	// 		td.not('.active').addClass('stop');
	// 	}
	// 	if (isChecked == 'all') {
	// 		td.not('.active').removeClass('stop');
	// 	}
	// });

	var checked = false;
	$('.tdcheck-head input').change(function () {
		var th = $(this);
		tdcol = th.data('col');
		tdbody = th.closest('.objects-table').find(tdcol);

		if ($(this).is(':checked')) {
			th.prop('checked', true).trigger('refresh');
			tdbody.prop('checked', true).trigger('refresh');
			tdbody.prop('checked', true).parents().addClass('checked');
			checked = true;
		} else {
			th.prop('checked', false).trigger('refresh');
			tdbody.prop('checked', false).trigger('refresh');
			tdbody.prop('checked', s).parents().removeClass('checked');
			checked = false;
		}
	});

	$('.tdcheck-body input').change(function () {
		if ($(this).is(':checked')) {
			$(this).closest('td').addClass('checked');
		} else {
			$(this).closest('td').removeClass('checked');
		}
	});

	//Изменение цен
	$('.objects-bottom__btn').click(function () {
		var th = $(this);
		priceIn = $('.objects-bottom__in .jq-number__field input').val();
		tdCheck = $('td.checked');
		tablePrice = $('.tdprice');
		tdCheck.find('.tdprice').text(priceIn);
	});

	$('.objects-cl-nav').on('init afterChange', function (e, slick) {
		let currentSlick = $('.slick-active:last', slick.$slideTrack);
		let nextName = currentSlick.next().find('.objects-cl-nav__title').text();

		slick.$nextArrow.html('<span>' + nextName + '</span>');
	});

	$('.objects-cl-nav').slick({
		// autoplay: true,
		// autoplaySpeed: 3000,
		focusOnSelect: true,
		slidesToShow: 2,
		slideToScroll: 1,

		rows: 0,
		dots: false,
		arrows: true,
		infinite: false,
		prevArrow: '<button type="button" class="slick-prev"></button >',
		nextArrow: '<button type="button" class="slick-next"></button>',
		asNavFor: '.objects-cl',
		responsive: [
			{
				breakpoint: 1400,
				settings: {
					slideToScroll:1,
					"centerMode": false
				}
			},
			{
				breakpoint: 1200,
				settings: {
					slidesToShow: 1,
					"centerMode": false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 1,
					"centerMode": true
				}
			},
			{
				breakpoint: 767,
				settings: {
					slidesToShow: 1
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 1
				}
			}
		]
	});
	
	
	$('.objects-cl').slick({
  		slidesToShow: 1,
		slideToScroll: 1,
		rows: 1,
		dots: false,
		arrows: false,
		infinite: false,
		asNavFor: '.objects-cl-nav',
 		responsive: [
			{
				breakpoint: 1400,
				settings: {
					slideToScroll: 1,
					"centerMode": false
				}
			},
			
			{
				breakpoint: 1200,
				settings: {
					slideToScroll: 1,
					"centerMode": false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 1,
					"centerMode": true
				}
			},
			{
				breakpoint: 767,
				settings: {
					slidesToShow: 1
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 1
				}
			}
		]
	});

	//Дата
	$('.datepicker-here').datepicker();

	//chartjs
	if ($('#myChart').length) {
		const ctx = document.getElementById('myChart').getContext('2d');
		const fz = function () {
			if ($(window).width() <= 768) {
				return 8;
			} else {
				return 12;
			}
		}
		const pd = function () {
			if ($(window).width() <= 768) {
				return 10;
			} else {
				return 20;
			}
		}
		var myChart = new Chart(ctx, {

			type: 'line',
			options: {
				responsive: true,
				plugins: {
					legend: {
						display: true,
						position: 'bottom',
						usePointStyle: true,
						labels: {
							usePointStyle: true,
							pointRadius: 5,
							boxWidth: 23,
							boxHeight: 23,
							padding: pd,
							font: {
								family: 'Montserrat',
								color: '#01112B',
								size: 12,
								lineHeight: 0
							},
						}
					}
				},
				scales: {
					x: {
						offset: true,
						ticks: {
							padding: pd,
							font: {
								family: 'Montserrat',
								color: '#01112B',
								size: fz,
								lineHeight: 0
							}
						},
						grid: {
							display: true,
							drawBorder: true,
							drawOnChartArea: false,
							drawTicks: true,
							borderWidth: 2,
							borderColor: '#C8C8C8',
							tickWidth: 2,
							tickLength: 9,
							tickColor: '#C8C8C8'
						},
					},
					y: {
						offset: false,
						beginAtZero: false,
						min: 0,
						max: 13,
						stepSize: 2,
						ticks: {
							padding: 10,
							font: {
								family: 'Montserrat',
								color: '#01112B',
								size: fz,
								lineHeight: 0
							},
							callback: function (value, index) {
								if (value !== 0 && value !== 13) return value;
							}
						},
						grid: {
							display: true,
							drawBorder: false,
							drawOnChartArea: true,
							drawTicks: false,
							color: '#C8C8C8'
						}
					}
				}
			},
			data: {
				labels: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
				datasets: [{
					label: '1к',
					data: [2, 6, 4.3, 6, 6.8, 7, 8.4, 8.8, 10, 8.4, 12.5],
					pointBackgroundColor: '#00CDAD',
					radius: 0,
					fill: false,
					lineTension: 0,
					borderWidth: 1,
					borderColor: '#00CDAD'
				},
				{
					label: '2к',
					data: [4, 1.5, 3.1, 3.8, 3.6, 4.9, 5.2, 5.2, 7, 6.1, 6.4, 8.2],
					pointBackgroundColor: '#2F4049',
					radius: 0,
					fill: false,
					lineTension: 0,
					borderWidth: 1,
					borderColor: '#2F4049'
				},
				{
					label: '3к',
					data: [5.4, 3.5, 5.8, 5.2, 6.5, 6.1, 7.5, 7, 8.5, 9.5, 11.5],
					pointBackgroundColor: '#F8932B',
					radius: 0,
					fill: false,
					lineTension: 0,
					borderWidth: 1,
					borderColor: '#F8932B'
				},
				{
					label: '4к',
					data: [2, 4, 3.5, 4.5, 5, 5.5, 6.8, 6.5, 7.5, 9.5, 7.5, 9.5],
					pointBackgroundColor: '#5A6D9D',
					radius: 0,
					fill: false,
					lineTension: 0,
					borderWidth: 1,
					borderColor: '#5A6D9D'
				}]
			}
		});
	}

	$('.stat-title').on('click', function (e) {
		var th = $(this);
		var statParent = th.closest('.stat-content');
		var statBody = statParent.find('.stat-body');

		statBody.stop().slideToggle('fast').toggleClass('open');

		$(this).toggleClass('active');

		e.preventDefault();
	});

/*
	$('.stat-table th a').on('click', function (e) {
		if ($(this).hasClass('top-active')) {
			$(this).removeClass('top-active');
			$(this).addClass('bottom-active');
		} else {
			$(this).removeClass('bottom-active');
			$(this).addClass('top-active');
		}
		e.preventDefault();
	});
*/
	$(".stat-top__print").click(function () {
		//window.print();
		//return false;
	});
























  if( window.innerWidth >= 1000 ){
     
	 
	 
//FormStyler
	// $('input:text').styler();
//$('input:text, select').styler({ selectSmartPositioning: false	});

 //$('input:checkbox').styler('destroy'); // отключаем стилизацию чекбоксов

 
 
 } else {
      //не выполнять
 } 










 

 
 
 
 if( window.innerWidth >= 1000 ){
	 // Подсказки для десктопа
var options = {
 attach: '[rel~=tooltip]',
 responsiveWidth:true,
 responsiveHeight:true,
 animation:'zoomIn',
 theme:'TooltipDark',
 addClass:'ttopt',
 maxWidth:180,
 width:180
}; 
	 
 }
 else
 {
	 // Подсказки для мобильных
 var options = {
 attach: '[rel~=tooltip]',
 responsiveWidth:true,
 responsiveHeight:true,
 animation:'zoomIn',
 theme:'TooltipDark',
 addClass:'ttopt',
 closeOnMouseleave:true,
 maxWidth:150,
 width:150
};
	 
 }
 new jBox('Tooltip', options); 
 

 /*
    var targets = $( '[rel~=tooltip]' ),
        target  = false,
        tooltip = false,
        title   = false;
  
    targets.bind( 'mouseenter', function()
    {
		 
        target  = $( this );
        tip     = target.attr( 'title' );
        tooltip = $( '<div id="tooltip"></div>' );

        if( !tip || tip == '' )
            return false;
  
        target.removeAttr( 'title' );
        tooltip.css( 'opacity', 0 )
               .html( tip )
               .appendTo( 'body' );
  
        var init_tooltip = function()
        {
            if( $( window ).width() < tooltip.outerWidth() * 1.5 )
			{
                tooltip.css( 'max-width', $( window ).width() / 2 );
				 
			}
            else
			{
                tooltip.css( 'max-width', 340 );
			}
				var pos_left = target.offset().left + ( target.outerWidth() / 2 ) - ( tooltip.outerWidth() / 2 ),
                pos_top  = target.offset().top - tooltip.outerHeight() - 20;
  
            if( pos_left < 0 )
            {
                pos_left = target.offset().left + target.outerWidth() / 2 - 20;
                tooltip.addClass( 'left' );
            }
            else
			{
                tooltip.removeClass( 'left' );
			}
            if( pos_left + tooltip.outerWidth() > $( window ).width() )
            {
                pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
                tooltip.addClass( 'right' );
            }
            else
			{
                tooltip.removeClass( 'right' );
			}
            if( pos_top < 50 ) // внизу
            {
                var pos_top  = target.offset().top + target.outerHeight();
                tooltip.addClass( 'top' );
            }
            else
			{
                tooltip.removeClass( 'top' );
			}			
			tooltip.css( { left: pos_left, top: pos_top } ).animate( { top: '+=1', opacity: 1 }, 10 );
        };
  
        init_tooltip();
        $( window ).resize( init_tooltip );
  
      var remove_tooltip = function()
        {
            tooltip.animate( { top: '-=10', opacity: 0 }, 0, function()
            {
                $( this ).remove();
            });
 
            target.attr( 'title', tip );
        };
  
 
        target.bind( 'mouseleave', remove_tooltip );
        tooltip.bind( 'click', remove_tooltip );
		$('window').bind( 'scroll', remove_tooltip );
		
		
    });
	*/
	
	
	
	
	 /*
$('[rel~=tooltip]').mouseenter(function() {
  alert( "!!!" );
});

$('.tdsquare').click(function() {
  alert( "!!2!" );
});

*/



























  
  
  
  
     
	
	
	
	/*

    $(".various").fancybox({
            maxWidth    : 800,
            maxHeight   : 600,
            fitToView   : false,
            width       : '70%',
            height      : '70%',
            autoSize    : false,
            closeClick  : false,
            openEffect  : 'none',
            closeEffect : 'none'
        });
*/
 
 
 
 
  $('.iframe_r').magnificPopup({type:'iframe',
  removalDelay: 100,
  fixedContentPos: true, 
  disableOn:1,
 
 mainClass: 'my-mfp-zoom-in',

   tLoading: 'Загрузка #%curr%...',
    callbacks: {
    open: function() {
      // Will fire when this exact popup is opened
      // this - is Magnific Popup object
    },
    close: function() {
         parent.location.reload(true);  
    },
	open: function() {
          location.href = location.href.split('#')[0] + "#pop";
        } 
	 
    // e.t.c.
  }
   
  });
  
  
  
  
  if( window.innerWidth >= 1000 ){
     
	 
	 
	 /*
     $("a.iframe").fancybox({
            maxWidth    : 600,
            maxHeight   : 600,
            
            width       : '1000px',
            height      : '70%',
            closeClick  : true,
 	 
 	'scrolling' : 'yes',

 afterClose: function () { // USE THIS IT IS YOUR ANSWER THE KEY WORD IS "afterClose"
        // parent.location.reload(true);
    }
        });
 
 */
 
 
 
 
 
  
  
  
  $(window).on('hashchange',function() {

       if(location.href.indexOf("#pop")<0) {

         $.magnificPopup.close(); 
       }
  });
  
  
  
 
 
 
 
 
 } else {
      //не выполнять
	  
	  // игнорировть клики на квартирах в мобильной
	$('.iframe_r').click(function(e) {
    e.preventDefault();
	});
 } 
 
 
 
 
   if( window.innerWidth >= 1000 ){
	   
	   
	 /*
     $("a.iframe2_r").fancybox({
            width       : '1000px',
            closeClick  : true,
			'scrolling' : 'yes',
			afterClose: function () { // USE THIS IT IS YOUR ANSWER THE KEY WORD IS "afterClose"
			parent.location.reload(true);
    },
	onComplete: function() {
         $('.fancybox-content').css({
            height: '1000px'
         });
      }
	});
		
		
		
$("a.iframe_f").fancybox({
    width       : '1000px',   
    closeClick  : true,
 	'scrolling' : 'yes',
	onComplete: function() {
         $('.fancybox-content').css({
            height: '1000px'
         });
      }
 });
		
 */
 
 
 
 
 






 
 } else {
      //не выполнять
 } 
 
 
 
	


});
