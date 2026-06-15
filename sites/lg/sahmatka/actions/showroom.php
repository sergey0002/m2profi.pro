<style>
.novos *{font-size:14px;}
.novos h2{font-size:24px;}
.novos div {line-height: 1.8em;}
</style>

<div class="container mobc">
<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt=""></div>
			<div class="page-header__title">Шоу-рум</div>
		</div>
</div>



<div class="container novos mobc">
	  
  

 
 
<link rel="stylesheet" type="text/css" href="/sahmatka/template/default/libs/slick/slick-theme.css"/>
 
  <style type="text/css">
   

    
    .slick-slide {
       
	  height:auto;
	 
	  width:auto;
    }

    .slick-slide img {
      width: 100%;
    }

    .slick-prev:before,
    .slick-next:before {
      color: black;
    }


    .slick-slide {
      
      opacity: .2;
    }
    
    .slick-active {
      opacity: .5;
    }

    .slick-current {
      opacity: 1;
	  
	  
    }
	
 
	.slick-next {
        right: 25px;
}

.slick-prev {
    left: 25px;
	z-index:10000;
 }

 



.slider-controll {
  display: block;
  width:100%;
}

.slider-item {
  display: inline-block;
   
  cursor:pointer;
}

.slider-item img{vertical-align:baseline;}

 
 .slide_this
 {
	 opacity:0.5;
 }

 
  </style>		
 
<?

function gallery_dir_arr_( $dir )
{
if($sd){$sd++;}
	$files = scandir($dir);
	//sort($files);
	
	
	foreach($files as $file)
	{
		if(is_file($dir.$file))
		{		
			$t = basename($dir.$file);
 
			$arr_files[$t]=$dir.$file;	
		}
	}
	sort($arr_files);
 
	return $arr_files;
}
?>

<div class="container mobc">
 
<div  class="stat">

<?
    //include('sahmatka/form_exc_carant2.php');
    //include('sahmatka2/form_exc.php'); # Актуальная форма ! 10.06.2021
?>



<div style="padding:30px; font-size:21px; text-align:center;  "> 

Запись на экскурсии осуществляется по телефону:<br/>
<a href="phone:+73833474700"><b>+7 (383) 347-47-00</b></a>

</div>



</div>
<br>
</div>

<div class="container">
<div class="row">
<div  class="col-md-12 col-xs-12" style="text-align:center; font-family: Exo 2;
font-style: normal;
font-weight: bold;
line-height: 29px;
font-size: 30px;
text-align: center;
letter-spacing: 0.02em;">
 <br/>
    Фото шоу-рум
<br/> <br/>
 





<center>

<div id="slider" style="display:none; max-width:900px;">


	<div class="slider-for  " style="margin-bottom: 5px;">
	<?
	$arr= gallery_dir_arr_('sr/');
	foreach($arr as $k=>$v)
	{
		?>
		<div><a data-fancybox="gallery" href="<?=$v?>" style="cursor: zoom-in;" ><img src="<?=$v?>" width=100%></a> </div>
		<?
	}
	?>
	</div>


	<div class="slider-controll" style="text-align:center;">
	<?
	$arr= gallery_dir_arr_('sr/');
	$i=0;
	foreach($arr as $k=>$v)
	{
		?>
		<div class="slider-item" data-item="<?=$i?>"><img src="<?=$v?>" width="80" height="80"> </div>
		<?
		$i++;
	}
	?>
	</div>
	 

</div>


</div>
</div>
</div>
			
 <script>
 
 $(function() {
   $('#slider').show(); // Скрыт слайдер чтобы не мельтешили картинки пока не загружен скрипт 
   
  var $Slider = $('.slider-for').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
 
  dots: false,
  infinite: false,
  focusOnSelect: true,
  fade: true,
    speed:100,

});


 $('.slider-for').on('afterChange', function (event, slick, currentSlide) {
    // если то же самое нужно не до смены слайда, а после... но тут нет параметра nextSlide, здесь мы видим только текущий слайд currentSlide
	//alert(currentSlide);
	$('.slider-item').removeClass('slide_this'); // Сбрасываем класс аетивности
	$('div[data-item="'+currentSlide+'"]').addClass('slide_this'); // добавляем к активнму
  })

$('.slider-item').on('click', function(e) {
  var slideIndex = $(this).data('item');
  $(this).addClass('slide_this').siblings().removeClass('slide_this');
  $Slider.slick( 'slickGoTo', parseInt( slideIndex )  );
  e.preventDefault();
});
 
 

});


</script>












 </div>