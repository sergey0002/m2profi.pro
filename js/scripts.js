var $ = jQuery;

$(document).ready(function () {

	$('.lazy').Lazy({
		effect: 'fadeIn',
		visibleOnly: true
	});

	AOS.init({
		offset: 50,
		once: false,
		duration: 200,
		mirror: false
	});

	//Маска ввода
	$(".phone-in").inputmask("+7 (999) 999-99-99", { showMaskOnHover: false });
	$('.mail-in').inputmask({
		mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,10}][.*{1,3}]",
		greedy: false,
		showMaskOnHover: false,
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

	// //прокрутка по якорям
	$('.header-menu a, .footer-menu a, .mobile-nav-menu a').bind("click", function (e) {
		var anchor = $(this);
		$('html, body').stop().animate({
			scrollTop: $(anchor.attr('href')).offset().top - 50
		}, 1000);
		e.preventDefault();
	});

	//прокрутка по якорям
	// $(window).on('load resize', function(){
	//    	$('.header-menu a').bind("click", function(e) {
	//        var anchor = $(this);
	// 		windowWidth = $(window).width();
	// 	if(windowWidth <= 992) {
	// 		$('html, body').stop().animate({
	//             scrollTop: $(anchor.attr('href')).offset().top - 0
	//         }, 1000);
	// 	} else {
	// 		$('html, body').stop().animate({
	//             scrollTop: $(anchor.attr('href')).offset().top - (-100)
	//         }, 1000);
	// 	}
	// 	e.preventDefault();
	// 	});
	// });

	//кнопка наверх
	//   $(".scroll-top").hide().removeAttr("href");
	// $(window).scroll(function(){
	// 	if ($(window).scrollTop() <= "1000") {
	// 		$(".scroll-top").removeClass('active').stop().fadeOut("slow");
	// 	} else {
	// 		$(".scroll-top").addClass('active').stop().fadeIn("slow");
	// 	}
	// });
	// $(".scroll-top").click(function(){
	// 	$("html, body").animate({scrollTop:0},"slow");
	// });

});
