<?php
header('Content-type: application/xml');
include('config.php');
require_once __DIR__ . '/inc/yandex_feed.php';

$home_id = isset($_GET['home_id']) ? (int)$_GET['home_id'] : 0;
$feed = new em_yandex_feed($mysql, __DIR__);
$feed->output_xml($home_id);
