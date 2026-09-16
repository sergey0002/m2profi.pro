<?php
header('Content-type: application/xml; charset=utf-8');
include('config.php');
require_once __DIR__ . '/inc/avito_feed.php';

$home_id = isset($_GET['home_id']) ? (int)$_GET['home_id'] : 0;
$feed = new em_avito_feed($mysql, __DIR__);
$feed->output_xml($home_id);
