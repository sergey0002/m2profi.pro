</div>
 

<!--[if lt IE 9]>
	<script src="libs/html5shiv/es5-shim.min.js"></script>
	<script src="libs/html5shiv/html5shiv.min.js"></script>
	<script src="libs/html5shiv/html5shiv-printshiv.min.js"></script>
	<script src="libs/respond/respond.min.js"></script>
	<![endif]-->
	 
 
 <!--[if lt IE 9]>
	<script src="js/selectivizr.js"></script>
	<script src="js/html5.js"></script>
	<script src="js/ie9.js"></script>
	<![endif]-->
 

 

	 
	 
<script src="/sahmatka/template/default/libs/jquery.lazy.min.js"></script>
<script src="/sahmatka/template/default/libs/air-datepicker/js/datepicker.min.js"></script>
<script src="/sahmatka/template/default/libs/chartjs/chart.min.js"></script>
<script src="/sahmatka/template/default/libs/slick/slick.min.js"></script>

<script src="/sahmatka/template/default/libs/aos/aos.js"></script>

<script src="/sahmatka/template/default/libs/inputMask/jquery.inputmask.bundle.min.js"></script>

<script src="/sahmatka/template/default/js/jquery.mask.js"></script>
<script>
$('.money').mask('00 000 000 ', {reverse: true});
</script>

<script src="/sahmatka/template/default/js/scripts.js?x=44123123357678"></script>

<? if($_GET['home']==17) 
{
	// 704 скрол на последний подезд
	?> 
	<script>

	$(document).ready(function () {

	$('.objects-cl-nav').slick('slickGoTo', 4,  true);
	$('.objects-cl').slick('slickGoTo', 4,  true);
	});
	</script>
	<?
}
?>



<? if($_GET['home']==12 || $_GET['home']==39  ) 
{
	// 704 скрол на последний подезд
	?> 
	<script>

	$(document).ready(function () {

	$('.objects-cl-nav').slick('slickGoTo', 6,  true);
	$('.objects-cl').slick('slickGoTo', 6,  true);
	});
	</script>
	<?
}
?>
 

<div style="display:none;">
<?
t('Страница готова');
print '<pre>';
print_r($tlog);
 
print '</pre>';
?>
</div>




 
 

</body>

</html>