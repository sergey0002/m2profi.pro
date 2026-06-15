	<script  src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
	<script src="/sahmatka/js/jquery.ui.rotatable.js"></script>
	<link rel="stylesheet" href="/sahmatka/js/jquery.ui.rotatable.css">
		
	<style type="text/css">
	.car {
		padding: 0;
		padding-top: 20px;
		position: absolute;
		left: 0px;
		top: 0px;
		margin: 0;
		width:32px;
	    height:85px;
		text-align: center;  
		background-size: contain; 
	    background-repeat: no-repeat;
	    font-family: "Exo2", sans-serif;
		cursor:all-scroll;
		user-select:none;
      }
	  .car img {width:100%;}
	  .pk_num
	  {
		width: 100%;
		text-align: center;
		display: block;
		font-size: 14px;
		font-weight: bold;
	  }
	  .pk_price
	  {
		width: 100%;
		text-align: center;
		display: block;
		font-size: 7px;
		 
	  }
	  .pk_area
	  {
		width: 100%;
		text-align: center;
		display: block;
		font-size: 8px;
	  }

	  .ui-rotatable-handle{ }
	  .car_g{background-image:url('/sahmatka/parking/car_g.png'); }
	  .car_r{background-image:url('/sahmatka/parking/car_r.png'); }
	  .car_y{background-image:url('/sahmatka/parking/car_y.png'); }
	  .car_f{background-image:url('https://em.m2profi.pro/sahmatka/parking/car_f.png'); }
	  .car_b{background-image:url('https://em.m2profi.pro/sahmatka/parking/car_b.png'); }
	  
 
	  .ui-rotatable-handle{position:absolute; width:5px; height:5px;     bottom: -7px;     left: 0;}
	  
	  .input_edit {width:100%; max-width:100%;}
	  
	  .deplan_zoom{display:inline-block; padding:2px; border:solid #000 1px; cursor:pointer; user-select:none; background-color:#3d535f; color:#EEE; font-size:10px;    border-radius: 2px; }
	  .deplan_zoom:hover{color:#FFF;}
		  
		  
		 #tmouse{position:absolute;
		 font-size:10px;
		 background-color:#FFF;
		 opacity:0.9;
		 border:solid 1px #333; padding:3px;
		 }
		 input[type="checkbox"] {
			  .position: relative;
			  top: 2px;
		 }
		 .selplaces_b{cursor:pointer; display:inline-block; padding:10px; font-size:10px; font-weight:bold;}
    </style>
	
<script type="text/javascript">
$(function() {
	$(document).ready(function() {
		// Инфа о машине при наведении
		$(".car").hover(function() {
			var deg = $(this).attr('data-deg');
			deg = parseFloat(deg);
			var left = $(this).css('left');
			var top = $(this).css('top');
			var left = parseFloat(left);
			var top = parseFloat(top);
			// $('#save_text').text(' Rad : '+deg.toFixed(2)+' |  Left : '+left+'  |  Top : '+top);
			$('#tmouse').show();
			$('#tmouse').css('top', top + 100);
			$('#tmouse').css('left', left);
			$('#tmouse').text(' Rad : ' + deg.toFixed(2) + ' |  Left : ' + left + '  |  Top : ' + top);
		}, function() {
			$('#tmouse').hide();
		});
		var params_rotate = {
			degrees: true,
			// Callback fired on rotation end.
			wheelRotate: false, // вращение скролом
			rotate: function(event, ui) {
				var degress = ui.angle.current;
				degress = parseFloat(degress);
				// Всплывающая табличка
				$('#tmouse').text(degress.toFixed(2));
				$('#tmouse').show();
			},
			start: function(event, ui) {
				var degress = ui.angle.current;
				degress = parseFloat(degress);
				// $('#save_text').text(degress.toFixed(2));
			},
			stop: function(event, ui) {
				var dataid = $(this).attr('data-id');
				var degress = ui.angle.stop;
				//var degress = $(this).css('rotate');
				//alert(degress);
				$('#tmouse').hide();
				if (Number.parseFloat(degress)) {} else {
					$(this).attr('data-deg', 0);
				}
				$(this).attr('data-deg', degress);
				var save_text = 'Сохранение - deg save: id-' + dataid + 'degress: ' + degress;
				console.log(save_text);
				$('#save_text').text(save_text);
				$.ajax({
					type: "POST",
					url: "ajax_router.php?ctr=parking_floors&act=save_rotate",
					data: {
						'id': dataid,
						'degress': degress
					}
				}).done(function(msg) {
					$('#save_text').text(msg);
				});
			},
			step: 1, // Шаг угла в градусах
			snap: true, // угоол с шагом
		};
		var params_drag = {
			drag: function(event, ui) {
				var pos_x = parseFloat(ui.position.left);
				var pos_y = parseFloat(ui.position.top);
				// Всплывающая табличка
				$('#tmouse').text(' Left : ' + pos_x.toFixed() + 'px  |  Top : ' + pos_y.toFixed() + 'px ');
				$('#tmouse').show();
			},
			// Callback fired on rotation start.
			start: function(event, ui) {},
			containment: '.de_plan', // Родительский контейнер
			grid: [1, 1], // Сетка  
			// Callback fired on rotation end.
			//
			stop: function(event, ui) {
				///// Сохранение при движении каждого элемента
				var pos_x = ui.position.left;
				var pos_y = ui.position.top;
				var dataid = $(this).attr('data-id');
				var save_text = 'Сохранение - drag save: id-' + dataid + 'x:' + pos_x + ' y:' + pos_y;
				console.log(save_text);
				$('#save_text').text(save_text);
				//alert( dataid );
				$.ajax({
					type: "POST",
					url: "ajax_router.php?ctr=parking_floors&act=save_drag",
					data: {
						'id': dataid,
						'x': pos_x,
						'y': pos_y
					}
				}).done(function(msg) {
					$('#save_text').text(msg);
				});
				//////////////////////////////////////////////
			},
		};
		$('.car').rotatable(params_rotate);
		$('.car').draggable(params_drag);
		$('.car').each(function(i, elem) {
			//$(elem).data('uiRotatable').angle($(elem).attr('data-deg'));
			var degress = Number.parseFloat($(elem).attr('data-deg'));
			if (degress) {
				$(elem).data('uiRotatable').angle(degress);
			} else {
				$(elem).data('uiRotatable').angle(0);
			}
		});
		$(".droppable").droppable({
			drop: function(event, ui) {
				$(ui.helper).hide();
				$.ajax({
					type: "POST",
					url: "ajax_router.php?ctr=parking_floors&act=deltedrag",
					data: {
						'id': $(ui.helper).attr('data-id')
					}
				}).done(function(msg) {
					$('#save_text').text(msg);
				});
			}
		});
		// $('.car').resizable(params);
		$(".deplan_plus").click(function() {
			var st_scale = $('.de_plan').css('zoom');
			if (!st_scale || st_scale == 'none' || st_scale == 'NaN') {
				st_scale = 1;
			}
			st_scale = parseFloat(st_scale) + 0.1;
			$('.de_plan').css('zoom', st_scale);
			$('.deplan_text').text('zoom: ' + st_scale.toFixed(2));
		});
		$(".deplan_minus").click(function() {
			var st_scale = $('.de_plan').css('zoom');
			if (!st_scale || st_scale == 'none' || st_scale == 'NaN') {
				st_scale = 1;
			}
			st_scale = parseFloat(st_scale) - 0.1;
			$('.de_plan').css('zoom', st_scale);
			$('.deplan_text').text('zoom: ' + st_scale.toFixed(2));
		});
		$(".deplan_0").click(function() {
			$('.de_plan').css('zoom', 1);
			$('.deplan_text').text('zoom:1');
		});
		// РЕЖИМЫ
		$('#disable_drag').change(function() {
			if (!$(this).is(':checked')) {
				$('.car').draggable('enable');
			} else {
				$('.car').draggable('disable');
			}
		});
		$('#disable_rotate').change(function() {
			if (!$(this).is(':checked')) {
				$('.car').rotatable('enable');
			} else {
				$('.car').rotatable('disable');
			}
		});
		
		
		// Масовое редактирование
		$('#select_mode').change(function() {
			if (!$(this).is(':checked')) { // ОТКЛЮЧЕНО
				$('#disable_drag').prop('checked', false);
				$('#disable_rotate').prop('checked', false);
				$("#disable_rotate").attr("disabled", false);
		 
				$("#disable_rotate").attr("disabled", false);
				$("#disable_drag").attr("disabled", false);
				$("#border_mode").attr("disabled", false);
				
				$('#disable_rotate').change();
				$('#disable_drag').change();
				//$('.car_massform').hide();
				$('.car').css('cursor','all-scroll');
				$('.car').css('border','none');
				 
				// Добавить выделить все и снять выделение
				$( ".car_massform" ).slideUp(); // Скрыть форму
				
				$('.place_ch').prop('checked', false); // Снимаем чекбуксы

			} else {// ВКЛЮЧЕНО
				$('#disable_drag').prop('checked', true);
				$('#disable_rotate').prop('checked', true); 
				$('#border_mode').prop('checked', false);
				
				$("#disable_rotate").attr("disabled", true)
				$("#border_mode").attr("disabled", true);
				$("#disable_drag").attr("disabled", true);
				
				$('#disable_rotate').change();
				$('#disable_drag').change();
				$('#border_mode').change();
				$('.car').css('cursor','pointer');

				// Показать форму
				$( ".car_massform" ).slideDown();

				//$('.car_massform').show();
				
			}
		});
		
		
		 
		
		// ВЫДЕЛЕНИЕ 
		$(".car").click(function(el) {
			
			if ($('#select_mode').is(':checked'))  
			{
				var id = $(this).attr('data-for');
				if (!$('#' + id).is(':checked')) {
					$('#'+id).prop('checked', true);
					$(this).css('border','solid 2px #0000FF');
				} else {
					$('#'+id).prop('checked', false);
					$(this).css('border','none');
				}
			}
		});
		
		// РЕЖИМ ГРАНИЦ
		$('#border_mode').change(function() {
			if (!$(this).is(':checked')) {
				$('.car').css('border', '');
			} else {
				$('.car').css('border', 'solid 1px #000');
			}
		});
		//
		
		
		//Выделить все объекты
		$("#select_all_places").click(function(el) {
			$('.car').css('border','solid 2px #0000FF');
			$('.place_ch').prop('checked', true);
		});
		// Снять выделение со всех
		$("#unselect_all_places").click(function(el) {
			$('.car').css('border','none');
			$('.place_ch').prop('checked', false);
		});
		
		// Очистка массовой формы
		$("#clean_form_places").click(function(el) {
			$('[name="group_edit[price]"]').val('');
			$('[name="group_edit[area]"]').val('');
			$('[name="group_edit[size]"]').val('');
			$('[name="group_edit[x]"]').val('');
			$('[name="group_edit[y]"]').val('');
			$('[name="group_edit[rotate]"]').val('');
			$('[name="group_edit[status]"]').val(0);
			 
		});
		
		
		 
		
		// $('[data-id="2"]').css('border','solid 2px #0000FF');
		$('#select_mode').change();
		
		
		
		// ЦИкл по чекбуксам - выделяем машины
		$('.place_ch').each(function(i, elem) {
			 var dataid = $(elem).attr('value');
			
			 if($(elem).is(':checked'))
			 {
				 $('[data-id="'+dataid+'"]').css('border','solid 2px #0000FF');
			 }
			 else{
				  $('[data-id="'+dataid+'"]').css('border','none');
			 }
		});
		
		
		$('#disable_drag').prop('checked', true); // Отключить по умолчанию перемещение
		$('#disable_rotate').prop('checked', true);// Отключить по умолчанию Вращение
		$('#disable_rotate').change();
		$('#disable_drag').change();
				
				
		//$('.place_ch').show(); // чекбуксы
		
		// Другой бордер у измененных
		$('.input_edit').on('change', function(el) {
		  $(this).css('border','solid #005ead 2px');
		});
		 
		
		
	});
	
	
	
		
		
		
});
// Скрол перетаскиванием 
window.onload = function() {
	 $(".de_planf").on("contextmenu", false); // Отключаем контекстное меню
	var scr = $(".de_planf");
	scr.mousedown(function(ev) {
		// 2 правая кнопка 1 и 0 левая
		if (event.button == 2) {
			var startX = this.scrollLeft + event.pageX;
			var startY = this.scrollTop + event.pageY;
			scr.mousemove(function() {
				this.scrollLeft = startX - event.pageX;
				this.scrollTop = startY - event.pageY;
				return false;
			});
		}
	});
	$(window).mouseup(function() {
		scr.off("mousemove");
	});
}
</script> 