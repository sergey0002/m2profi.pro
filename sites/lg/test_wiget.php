<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пример страницы</title>
 
    <link rel="stylesheet" href="https://new.em-nsk.ru/template/assets/css_n/style.css">
 

</head>
<body>

<?
$_GET['home']=55;
?> 

<!-- Основное содержимое -->
<div class="container">
    <h1>Демонстрация подключения виджета отображения дома</h1>
<br/><br/>








<script type="text/javascript" src="https://lg.m2profi.pro/wiget_home.js"></script>

<div class="house-sale">
<div class="row"> 


<div id="new_id_box"></div>
 
<script type="text/javascript">

wrgsv.init('https://lg.m2profi.pro/sahmatka/display_home_public.php?home=<?=$_GET['home']?>&new=1','new_id_box'); 
 
</script>
 
 </div>
 </div>
  
  
  
  
  
  
  
  
  
  
  
</body>
</html>   
</html>    