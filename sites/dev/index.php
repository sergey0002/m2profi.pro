<?
 
$dir= '../../core/etalon_site';
 
$url_parts = parse_url( $_SERVER['REQUEST_URI'] );
 
$file = $dir.$url_parts['path'];
if(!$file){$file = 'index.php';}

if( file_exists( $file ) )
{
	include_once($file);
}
else
{
	print '<pre>';
	print_r($_SERVER);
	print_r($url_parts);
	print '</pre>';
 
	print 'index error ';
	print $file;
}
 