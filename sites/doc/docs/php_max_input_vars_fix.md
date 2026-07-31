# Увеличение лимита max_input_vars в PHP

## Проблема
При сохранении большого количества прав возникает предупреждение:
```
Warning: PHP Request Startup: Input variables exceeded 1000. To increase the limit change max_input_vars in php.ini.
```

## Решение

### Вариант 1: Через php.ini (рекомендуется)

1. Найдите файл `php.ini` в Laragon:
   - Обычно находится в `C:\laragon\bin\php\php-8.x.x-Win32-vs16-x64\php.ini`

2. Откройте файл и найдите строку:
   ```ini
   max_input_vars = 1000
   ```

3. Измените значение на:
   ```ini
   max_input_vars = 5000
   ```

4. Перезапустите Apache/Nginx в Laragon

### Вариант 2: Через .htaccess (если не работает вариант 1)

Создайте файл `.htaccess` в корне проекта:
```apache
php_value max_input_vars 5000
```

### Вариант 3: Через .user.ini

Создайте файл `.user.ini` в корне проекта:
```ini
max_input_vars = 5000
```

## Проверка

После изменений проверьте текущее значение:
```php
<?php
echo ini_get('max_input_vars');
?>
```
