<script type="text/javascript">
function blocked_child_select(indexselect)
{
	if (!indexselect && indexselect !== 0) { indexselect = 1; }
	$('#zapisform_add select[data-bl]').each(function () {
		var ind = Number($(this).attr('data-bl'));
		if (ind > indexselect) {
			$(this).fwreset_select_options();
		}
	});
}

(function ($) {
	$.fn.fwloadx = function ($url, $calback) {
		var $form = $(this).closest('form');
		var obj = this;

		$(obj).prop('disabled', 'disabled');
		$.ajax({
			type: 'POST',
			url: $url,
			async: true,
			cache: false,
			data: $form.serialize(),
			success: function (response) {
				$(obj).html(response);
				blocked_child_select(Number($(obj).attr('data-bl')));
			},
			error: function () {
				blocked_child_select(Number($(obj).attr('data-bl')));
			}
		}).done(function () {
			$(obj).removeAttr('disabled');
			if ($calback) {
				$calback();
			}
			$(obj).trigger('change');
			$('#form_progressbar').removeClass('is-active').hide();
		});
		return this;
	};

	$.fn.fwreset_select_options = function () {
		var sel = this;
		if (!sel.is('select')) {
			return this;
		}
		sel.prop('selected', false);
		$('option[value=""]', sel).prop('selected', true);
		var option = $('option[value!=""]', sel);
		if (option.length) { option.remove(); }
		$(sel).prop('disabled', 'disabled');
	};
})(jQuery);

$(document).ready(function () {
	var AJAX = <?= json_encode($ajax_base) ?>;

	$.ajaxSetup({
		cache: false
	});

	function reloadGraficDependent() {
		if ($('#apartament_num').val()) {
			$('#date').fwloadx(AJAX + '&act=sel_date_add');
		} else if ($('#section_id').val()) {
			$('#section_id').fwloadx(AJAX + '&act=sel_section_add');
		} else if ($('#home_id').val()) {
			$('#home_id').fwloadx(AJAX + '&act=sel_home_add');
		}
	}

	$('#home_id').fwloadx(AJAX + '&act=sel_home_add');

	$('#zapis_add_vne').on('change', function () {
		blocked_child_select(-1);
		$('#home_id').fwloadx(AJAX + '&act=sel_home_add');
	});

	$('#zapis_add_pom, input[name="dkp"]').on('change', function () {
		reloadGraficDependent();
	});

	$('#home_id').change(function () {
		if ($(this).val()) {
			$('#section_id').fwloadx(AJAX + '&act=sel_section_add');
		} else {
			blocked_child_select(Number($(this).attr('data-bl')));
		}
	});

	$('#section_id').change(function () {
		if ($(this).val()) {
			$('#apartament_num').fwloadx(AJAX + '&act=sel_apartament_add');
		} else {
			blocked_child_select(Number($(this).attr('data-bl')));
		}
	});

	$('#apartament_num').change(function () {
		if ($(this).val()) {
			$('#date').fwloadx(AJAX + '&act=sel_date_add');
		} else {
			blocked_child_select(Number($(this).attr('data-bl')));
		}
	});

	$('#date').change(function () {
		if ($(this).val()) {
			$('#time').fwloadx(AJAX + '&act=sel_time_add');
		} else {
			blocked_child_select(Number($(this).attr('data-bl')));
		}
	});
});
</script>
