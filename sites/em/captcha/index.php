<?php
require_once 'FormProtect.php'; // Класс с методом hiddenFields (название с большой буквы, как в последних примерах)
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пример формы с защитой</title>
   
    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        form {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            max-width: 350px;
            padding: 20px 25px 18px 25px;
            margin-bottom: 36px;
            position: relative;
        }
        input[type="text"], input[type="email"], textarea {
            width: 100%;
            padding: 9px 11px;
            margin-bottom: 9px;
            border: 1px solid #d4d8e2;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.2s;
        }
		
		
		
	
         
    </style>
	  
	 <link rel="stylesheet" href="/captcha/style.css" />
	
	
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Обязательно вставьте свои ключи Google/Yandex в formprotect.js -->
    <script src="formprotect.js"></script>
</head>
<body>

<h2>Форма 1 (простая капча)</h2>
 

 
 







<form class="formprotect  " method="post" action="simple_backend.php" data-fp-id="form3"  enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Имя">
    <input type="email" name="email" placeholder="Email">
    <input type="file" name="photos[]" multiple accept="image/jpeg,image/png">
    <?= FormProtect::hiddenFields('form3') ?>
   
   
   
   
   
   
   	<!-- Контейнер для CAPTCHA (не обязательно добавлять вручную) -->
		<!-- Если добавить, используйте класс fp-captcha-box -->
		<div class="fp-captcha-box"></div>

		<!-- Контейнер для общих ошибок (не обязательно добавлять вручную) -->
		<!-- Если добавить, используйте класс fp-general-errors -->
		<div class="fp-general-errors"></div>

		<!-- Контейнер для сообщения об успехе (не обязательно добавлять вручную) -->
		<!-- Если добавить, используйте класс fp-success-message -->
		<div class="fp-success-message" style="display:none;"></div>
		
		
    <button type="submit">Send 2</button>
</form>



</body>
</html>
 