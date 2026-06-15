<?
session_start();
header('Access-Control-Allow-Origin: *'); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0", false);
header("Cache-Control: max-age=0", false);
header("Pragma: no-cache");
 
 
 
// Те же контроллеры и экшены без шаблона 
 include('config.php');
 

$_GET=$_REQUEST;

if( $_SESSION['sh_login'] || 1==1)
{ 
	include('router.php');
}
 
// print '<pre>';
// print_r($log);
// print '</pre>';

?>