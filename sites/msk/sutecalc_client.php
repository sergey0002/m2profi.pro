<html>

<head>
<script
  src="https://code.jquery.com/jquery-3.7.1.js"
  integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
  crossorigin="anonymous"></script>
</head>
<body>








<style>

#calc_proj *{    font-family: 'Halvar', Arial, sans-serif;}
</style>

<form id="calc_proj" action="https://msk.m2profi.pro/sitecalc.php" method="POST">

<select name="project" id="clac_project" style="display:none;">
	<option value="1">Усадьба 102 с сауной</option>
	<option value="2">Усадьба 102</option>
	<option value="3">Усадьба 102 мастер спальня</option>
	<option value="4" selected="selected">Усадьба 92</option>
	<option value="5">Усадьба 126 с антресолью</option>
	<option value="6">Усадьба 114</option>
	<option value="7">Усальба 42</option>
</select>

<br/>
<select name="compl" id="clac_complection">
 
  <option value="1">Холодный контур</option>
  <option value="2">Теплый контур</option>
  <option value="3">С отделкой фасада</option>
</select>
 <br/>
<input name="septic" id="calc_sept" type="checkbox" value="1">  <label for="calc_sept">Септик</label><br/>
<input name="inz" id="calc_vnutr" type="checkbox" value="1">  <label for="calc_vnutr">Внутренняя инженерия</label><br/>
<input name="vnutr" id="calc_ch" type="checkbox" value="1">  <label for="calc_ch">Чистовая отделка</label><br/>
<input name="pokraska" id="calc_pokr" type="checkbox" value="1">  <label for="calc_pokr">Покраска имитации бруса</label><br/>
 
<div id="calc_result" style="font-family: 'Halvar', Arial, sans-serif;"></div>
</form>
 
 
 
<script>
$( document ).ready(function() {
	  //выбор проекта
		$('#calc_proj *').change(function() {
		  
			var $form = $('#calc_proj');
			 
			$.ajax({
			  type: $form.attr('method'),
			  url: $form.attr('action'),
			  data: $form.serialize(),
            beforeSend: function () {
                // Вывод текста в процессе отправки
                $('#calc_result').html('Расчет...</p>');
            },
            success: function (data) {
                // Вывод текста результата отправки
                $('#calc_result').html(data);
            },
            error: function (jqXHR, text, error) {
                // Вывод текста ошибки отправки
                $('#calc_result').html(error);
            }
        });
		
		
		 
 
	});
	
	$("#clac_complection").trigger("change");
});
</script>




</body>
</html>
