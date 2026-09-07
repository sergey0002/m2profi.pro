<?
include('config.php');
include('incudes_/header.php');

// /sahmatka/ → тот же кабинет, что ctrind с default_controller/action из config
if (!empty($_SESSION['sh_login'])) {
	$home_ctr = !empty($r->default_controller) ? $r->default_controller : 'doc';
	$home_act = !empty($r->default_action) ? $r->default_action : 'index';
	header('Location: ' . $GLOBALS['config']['base_url'] . '/sahmatka/ctrind.php?ctr=' . rawurlencode($home_ctr) . '&act=' . rawurlencode($home_act));
	exit();
}

include('incudes_/foother.php');
