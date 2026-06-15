<?php

return [
    // Активная тема по умолчанию
    'default' => env('THEME_DEFAULT', 'default'),
    
    // Путь к темам в ядре
    'core_path' => resource_path('themes'),
    
    // Путь к публичным ресурсам тем
    'public_path' => public_path('themes'),
    
    // Разрешить переопределение тем на уровне сайта
    'allow_site_override' => true,
    
    // Путь к темам сайта (относительно корня проекта)
    'site_themes_path' => '../sites/{subdomain}/la/themes',
    'site_public_path' => '../sites/{subdomain}/public/themes',
];
