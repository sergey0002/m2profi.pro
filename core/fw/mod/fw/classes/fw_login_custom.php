<?
class fw_login extends fw_login__
{
 	function get_arr($login='',$password='')
	{
		# $query = mysqli_query($connection, "SELECT users.*,	agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption  FROM `users` left join agency on agency.admin_user_id = users.id  left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = '$login' AND `password` = '$password'"); // Формируем переменную с запросом к базе данных с проверкой пользователя
		# $result = mysqli_fetch_array($query); // Формируем переменную с исполнением запроса к БД 
		return $result;
	}	
}
