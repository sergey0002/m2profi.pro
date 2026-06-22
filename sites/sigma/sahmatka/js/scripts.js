var $ = jQuery;

$(document).ready(function() {

	var lazyLoadInstance = new LazyLoad({
		elements_selector: ".lazy"
		// ... more custom settings?
	});

	//FormStyler

	 // $('input, select').styler({		 selectSmartPositioning: false	  });


	// for link:
	// 	data-effect="mfp-zoom-in"
	// for modal window:
	// class="mfp-with-anim"

	$('.popup').magnificPopup({
		removalDelay: 500,
		midClick: true,
		mainClass: 'mfp-zoom-in',
		closeBtnInside: true
	});

	$('.image-popup').magnificPopup({
		type: 'image',
		closeOnContentClick: true,
		mainClass: 'mfp-with-zoom mfp-img-mobile',
		image: {
			verticalFit: true
		},
		zoom: {
			enabled: true,
			duration: 300, // don't foget to change the duration also in CSS
			opener: function(element) {
				return element.find('img');
			}
		}
	});

	// $('.gallery-popup').magnificPopup({
	// 	delegate: 'a',
	// 	type: 'image',
	// 	closeOnContentClick: false,
	// 	showCloseBtn: false,
	// 	mainClass: 'mfp-with-zoom mfp-img-mobile',
	// 	image: {
	// 		verticalFit: true
	// 	},
	// 	gallery: {
	// 		enabled: true
	// 	},
	// 	zoom: {
	// 		enabled: true,
	// 		duration: 300, // don't foget to change the duration also in CSS
	// 		opener: function(element) {
	// 			return element.find('img');
	// 		}
	// 	}

	// });

	$(document).ready(function() {

		//Маска ввода
		$(".phone-in").inputmask("+7 (999) 999-99-99",{ showMaskOnHover: false });
		$('.mail-in').inputmask({
			mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,10}][.*{1,3}]",
		    greedy: false,
		    onBeforePaste: function (pastedValue, opts) {
		      pastedValue = pastedValue.toLowerCase();
		      return pastedValue.replace("mailto:", "");
		    },
		    definitions: {
		      '*': {
		        validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~\-]",
		        casing: "lower"
		      }
		    }
		});

		//моб. меню
		$('#btn-mob').on('click', function() {
			$(this).toggleClass('active');
			$('.header-nav').toggleClass('open');
		});
		$('body').click(function(e) {
		    if ($(e.target).closest("#btn-mob, .header-nav").length) return;
		    $('#btn-mob').removeClass('active');
		    $('.header-nav').removeClass('open');
		});


		//Табы
		$(".house-plan-tabs__item").click(function() {
		    $('.house-plan-tabs__item').removeClass("active");
		    $(this).addClass("active");
		    $(".house-plan-body").removeClass('open').hide();
		    var activeTab = $(this).attr("href");
		    $(activeTab).addClass('open').fadeIn(700);
		    return false;
		});

		$(".room-tabnav li a").click(function() {
		    $('.room-tabnav li a').removeClass("active");
		    $(this).addClass("active");
		    $(".room-tabbody").removeClass('open').hide();
		    var activeTab = $(this).attr("href");
		    $(activeTab).addClass('open').fadeIn(700);
		    return false;
		});

		//Карусель
		$('.stepform-cl').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  slidesToShow: 2,
		  slideToScroll: 1,
		  rows: 0,
		  dots: false,
		  arrows: false,
		  infinite: false,
		  asNavFor: '.stepform-nav',
		  responsive: [
		    {
		      breakpoint: 992,
		      settings: {
		        slidesToShow: 2
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2
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
		$('.stepform-nav').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  slidesToShow: 5,
		  slideToScroll: 1,
		  rows: 0,
		  dots: false,
		  arrows: false,
		  infinite: false,
		  // centerMode: true,
		  focusOnSelect: true,
		  asNavFor: '.stepform-cl'
		});
		$('.stepform-item__link').on('click',function(){
			$('.stepform-cl').slick('slickNext');
		});
		//Номер слайда
		$('.stepform-cl .slick-slide').each(function(i){
			$(".stepform-item-count__current", this).text('0' + (i + 1));
		});
		//Всего слайдов
		var slider3 = $('.stepform-cl');
		$('.stepform-item-count__total').text( '0' + slider3.slick("getSlick").slideCount);

		$('.house-gallery__cl').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  slidesToShow: 3,
		  slideToScroll: 1,
		  dots: false,
		  arrows: true,
		  infinite: true,
		  centerMode: true,
		  appendArrows: '.house-gallery__nav .container',
		  responsive: [
		    {
		      breakpoint: 992,
		      settings: {
		        slidesToShow: 2
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2
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
		//Скрыть слайдер до инициализации
		$('.house-gallery__cl').on('init', function(slick){
		  $('.house-gallery__cl').show();
		});

		//progressbar
		$(window).on('load', function() {
			$(".about-item__progressbar").each( function() {
				var widthBar   = $(this).attr('data-ready');
					cssValues  = {
						"width": widthBar,
						"opacity": "1"
					}
				$(this).css(cssValues);
			});
		});

		//Слайдер
		$('#sl-child').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  dots: true,
		  arrows: true,
		  infinite: true
		});

		$('#sl-old').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  dots: true,
		  arrows: true,
		  infinite: true
		});
		//Скрыть слайдер до инициализации
		$('#sl-child, #sl-old').on('init', function(slick){
		  $('#sl-child, #sl-old').show();
		});

		//Фильтр
		$(window).on('load', function() {
			$(".house-table td a").filter( function() {
				var status = $(this).attr('data-status');
					free   = {'background': '#89FFA4'};
					sale   = {'background': '#FF8A90'};
				if ( status == 'free') {
					$(this).css(free);
				} else if ( status == 'sale') {
					$(this).css(sale);
				}
			});
			$('.house-filter-main input').filter( function() {
				var th        = $(this);
					isChecked = $( '.house-filter-main input:checked' );
					inCheck   = isChecked.val();
					td        = $('.house-table td a');
					selector  = $('.house-table td a[data-room="' + inCheck +'"]');

				if ( th.is(':checked') ) {
					td.removeClass('stop');
					selector.addClass('active');
					td.not('.active').addClass('stop');
				}

				if ( inCheck == 'all' ) {
					td.not('.active').removeClass('stop');
				}

				// if ( th.is(':checked') ) {
				// 	selector.addClass('active');
				// 	td.not('.active').addClass('stop');
				// } else if ( inCheck == 'all' ) {
				// 	td.removeClass('stop').addClass('active');
				// }
			});
		});
		$('.house-filter-main input').change(function() {
			var th        = $(this);
				isChecked = $( '.house-filter-main input:checked' ).val();
				inCheck   = th.val();
				td        = $('.house-table td a');
				selector  = $('.house-table td a[data-room="' + inCheck +'"]');

			if ( th.prop("checked") ) {
				td.removeClass('stop');
				selector.addClass('active');
				td.not('.active').addClass('stop');
			} else {
				selector.removeClass('active');
				td.not('.active').addClass('stop');
			}
			if ( isChecked == 'all' ) {
				td.not('.active').removeClass('stop');
			}
		});

		//Изменение цен
		var checked = false;
		$('.tdcheck-head input').change(function() {
			var th      = $(this);
				tdcol   = th.attr('data-col');
				tdbody  = th.closest('.objects-table').find(tdcol);

			if ($(this).is(':checked')) {
				th.prop('checked', true).trigger('refresh');
		        tdbody.prop('checked', true).trigger('refresh');
		        tdbody.prop('checked', true).parents().addClass('checked');
		        checked = true;
		    } else {
		    	th.prop('checked', false).trigger('refresh');
		        tdbody.prop('checked', false).trigger('refresh');
		        tdbody.prop('checked', true).parents().removeClass('checked');
		        checked = false;
		    }
		});

		$('.tdcheck-body input').change(function() {
			if ($(this).prop('checked', true)) {
		    	$(this).closest('td').addClass('checked');
		    }
	    });

		$('.objects-bottom__btn').click(function() {
			var th         = $(this);
				priceIn    = $('.objects-bottom__in input').val();
				tdCheck    = $('td.checked');
				tablePrice = $('.tdprice');
				tdCheck.find('.tdprice').text(priceIn);
		});

		$('.objects-cl').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  slidesToShow: 4,
		  slideToScroll: 1,
		  dots: false,
		  arrows: false,
		  infinite: false,
		  asNavFor: '.objects-cl-nav',
		  responsive: [
		  	{
		      breakpoint: 1200,
		      settings: {
		        slidesToShow: 3
		      }
		    },
		    {
		      breakpoint: 992,
		      settings: {
		        slidesToShow: 2
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2
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
		$('.objects-cl-nav').slick({
		  // autoplay: true,
		  // autoplaySpeed: 3000,
		  slidesToShow: 4,
		  slideToScroll: 1,
		  dots: false,
		  arrows: true,
		  infinite: false,
		  asNavFor: '.objects-cl',
		  responsive: [
			{
		      breakpoint: 1200,
		      settings: {
		        slidesToShow: 3
		      }
		    },
		    {
		      breakpoint: 992,
		      settings: {
		        slidesToShow: 2
		      }
		    },
		    {
		      breakpoint: 767,
		      settings: {
		        slidesToShow: 2
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
		// $('.cl-next').on('click', function() {
		//   $('.objects-cl').slick('slickNext');
		// });
		$('.cl-next').slick('slickNext');
		//Скрыть слайдер до инициализации
		$('.objects-cl').on('init', function(slick){
		  $('.objects-cl').show();
		});
		$('.objects-cl-nav').on('init', function(slick){
		  $('.objects-cl-nav').show();
		});














 
			
			 
	
	
	
		$(window).on('resize load', function() {
            var windowWidth = $(window).width();
            if (windowWidth <= 992) {
                $('.sidenav-menu > li').unbind('click mouseleave');
                $('.overlay-page').addClass('overlay-page-mobile');
            } else {
                $('.sidenav-menu > li').bind('click mouseleave');
                $('.overlay-page').removeClass('overlay-page-mobile');
            }
		});











		$('.sidenav-menu > li > span').click(function() {
			$(this).toggleClass('active');
			$(this).closest('.sidenav-menu > li').toggleClass('active');
			$(this).closest('.sidenav-menu > li').find(' > ul').toggleClass('open').stop(true,true).fadeToggle();
	    });

		$('#btn-lk').on('click', function() {
			$(this).toggleClass('active');
			$('.sidenav').toggleClass('open');
			$('.overlay-page-mobile').fadeToggle();
		});
		$('body').click(function(e) {
		    if ($(e.target).closest("#btn-lk, .sidenav").length) return;
		    $('#btn-lk').removeClass('active');
		    $('.sidenav').removeClass('open');
		    $('.overlay-page-mobile').fadeOut();
		});

		// //прокрутка по якорям
	   $('.btn-add').bind("click", function(e) {
	        var anchor = $(this);
	        $('html, body').stop().animate({
	            scrollTop: $(anchor.attr('href')).offset().top - 0
	        }, 1000);
	        e.preventDefault();
	    });

	   //Дата
	   $('.datepicker-here').datepicker();

	   //Раскрытие статистики
	   $('.sales-link').click(function(e) {
	   	var th = $(this);
		   $(this).toggleClass('active').text(th.is('.active') ? 'Краткая статистика' : 'Детальная статистика');
		   $('.sales-item__info').stop().slideToggle('fast');
		   return false;
	   });
	   $('.sales-inlink').click(function(e) {
	   	var th = $(this);
		   $(this).toggleClass('active').text(th.is('.active') ? 'Краткая статистика' : 'Детальная статистика');
		   th.parent().find('.sales-item__info').stop().slideToggle('fast');
		   return false;
	   });

	});

	//chartjs
	var ctx = document.getElementById('myChart').getContext('2d');
	var myChart = new Chart(ctx, {
	    type: 'line',
	    data: {
	        labels: ['1.2019', ' ', '3.2019', ' ', '5.2019', ' ', '7.2019', ' ', '9.2019', ' ', '11.2019'],
	        datasets: [{
	        	label: '1',
	            data: [60, 95, 230, 145, 92, 96, 112, 100, 93, 122, 110, 62],
	            borderColor: [
	                'rgba(31, 119, 180, 1)'
	            ],
	            pointBackgroundColor: 'rgba(31, 119, 180, 1)',
		        fill: false,
		        lineTension: 0,
		        borderWidth: 1
	         	}, {
         		label: '2',
         		data: [45, 50, 185, 95, 62, 80, 98, 105, 152, 100, 105, 65],
	            borderColor: [
	                'rgba(255, 127, 14, 1)'
	            ],
	            pointBackgroundColor: 'rgba(255, 127, 14, 1)',
		        fill: false,
		        lineTension: 0,
		        borderWidth: 1
	           	}, {
	           	label: '3',
           		data: [29, 22, 105, 38, 18, 16, 20, 22, 20, 53, 50, 22],
	            borderColor: [
	                'rgba(44, 160, 44, 1)'
	            ],
	            pointBackgroundColor: 'rgba(44, 160, 44, 1)',
		        fill: false,
		        lineTension: 0,
		        borderWidth: 1
		        }, {
		        label: '4',
		        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
	            borderColor: [
	                'rgba(214, 39, 40, 1)'
	            ],
	            pointBackgroundColor: 'rgba(214, 39, 40, 1)',
		        fill: false,
		        lineTension: 0,
		        borderWidth: 1
	        }]
	    },
	    options: {
	    	responsive: true,
	        legend: {
	            display: true,
	            position: 'bottom',

	            labels: {
	                fontColor: '#333',
	                boxWidth: 12
	            }
	        },
	        scales: {
		        xAxes: [{
					gridLines: {
						display: true,
						drawOnChartArea: false,
						color: '#333'
					}
				}],
		        yAxes: [{
		        	ticks: {
		        		min: -20,
		        		stepSize: 20
		        	},
					gridLines: {
						display: true,
						drawOnChartArea: false,
						color: '#333'
					}
				}]
			}
	    }
	});

});