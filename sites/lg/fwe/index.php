<?php
// index.php — основной вход для FWE Online Editor
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==== CONFIG ====
define('FWE_ROOT', __DIR__); // Корень редактора
define('FWE_DEBUG', true);  // Для дебага временно true

// ==== AUTOLOAD ====
// Здесь можно подключить свои классы через autoload, если потребуется
require_once FWE_ROOT . '/fwe.php'; // Весь серверный код редактора
 

// ==== MAIN ROUTING ====
// Простейший роутинг — всё, что относится к AJAX, отдаётся JSON и завершается
if (isset($_GET['ajax']) && $_GET['ajax']) {
    // Все ajax-запросы обрабатывает router_fwe
    $r = new router_fwe();
    $r->action_content();
    exit;
}

// ==== Готовим шаблонные переменные ====
// Пример: можно наполнять $fw_tplx перед выводом шаблона
global $fw_tplx;
if (!isset($fw_tplx) || !is_array($fw_tplx)) $fw_tplx = array();
$fw_tplx['leftpanel'] = '<b style="font-size:12px; color:#eee;">/'.htmlspecialchars('/').'</b>';

// ==== Рендерим основную страницу ====
require_once FWE_ROOT . '/tpl.php';

// ==== ФУНКЦИИ ДЛЯ DEBUG ====
if (FWE_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL | E_NOTICE | E_STRICT);
}
?>
