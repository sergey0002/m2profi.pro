<?
include_once('sahmatka/config.php');
$dir= '../../core/etalon_site';

$file = $dir.$_SERVER['REQUEST_URI'];

if(file_exists($file))
{
	include_once($file);
}
else
{
 
	print 'index error ';
	print $file;
}
 