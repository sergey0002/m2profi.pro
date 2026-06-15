<?php
// Тестовая страница для проверки маршрутизации
echo "<h1>Sahmatka Legacy Version</h1>";
echo "<p>Текущий путь: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Домен: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Директория: " . __DIR__ . "</p>";
echo "<hr>";
echo "<p>Если вы видите это сообщение, значит legacy-версия sahmatka работает корректно!</p>";
