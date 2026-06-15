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

<? 

if( $_SESSION['sh_login']=='adm3in' || $_GET['mmm']==1 )
{
	
	?>
	 
	<script>
	 
  
  
  
        // Утилита для работы с куками
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
        }

        function getCookie(name) {
            const cookies = document.cookie.split('; ');
            for (const cookie of cookies) {
                const [key, value] = cookie.split('=');
                if (key === name) return value;
            }
            return null;
        }

        // Проверяем, установлен ли куки
        function checkAgreementCookie() {
            return !!getCookie('contract_selected');
        }

        $(document).ready(function () {
			      // Если куки нет, открываем Magnific Popup
           // if (!checkAgreementCookie()) {
                $.magnificPopup.open({
                    items: {
                      src: '#popup-agree',
                        type: 'inline'
                    },
                    closeOnBgClick: false,
                    enableEscapeKey: false,
                    showCloseBtn: false,
                    mainClass: 'mfp-fade',
                });

                const selectButton = document.getElementById('select-button');
                const radioButtons = document.querySelectorAll('input[name="contract"]');

                // Активируем кнопку выбора, если выбрано радио
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', function () {
                        selectButton.disabled = false;
                    });
                });

                // Обработчик кнопки "Выбрать договор"
                selectButton.addEventListener('click', function () {
                    const selectedContract = document.querySelector('input[name="contract"]:checked');
                    if (selectedContract) {
                        setCookie('contract_selected', selectedContract.value, 365); // Устанавливаем куки на 365 дней
						  $.magnificPopup.close(); // Закрываем окно Magnific Popup
 

                        window.location.href = selectedContract.value; // Перенаправляем на выбранный договор
                    }
                });
          //  }
        });
    </script> 
	
	
	<?
	if( $_COOKIE['contract_selected'] )
	{
	 
		print 123;
	}
	
	print 345;
}
?>