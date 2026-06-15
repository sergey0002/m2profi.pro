 
<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">СТАТИСТИКА <span>агентств</span></div>
		</div>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_dogovor">
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-select stat-top-item_agency">
						<select data-placeholder="Агенства">
							<option></option>
							<option>ООО Фонд недвижимости</option>
							<option>Сибакадемстрой недвижимость</option>
						</select>
					</div>
					<a href="#" class="stat-top-btn btn btn_arrow-long">Выбрать<i></i></a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table-agency table">
			
			
			
			
			<?
		 
		$query = mysqli_query($connection, "SELECT users_stat.users_stat_id, users_stat.date, agency.caption, users.name, users_stat.action, users.id    FROM `users_stat` left join users on users.id = users_stat.users_id left join agency on users.agency_id= agency.agency_id  order by  date desc LIMIT 0,200");  
	 
		 ?>
	 
				 
		<table >
		<thead>
			<tr>
				<th><b>id</b></th>
				<th><a href="#"><b>Дата</b></a></th>
				<th><a href="#"><b>Агенство</b></a></th>
				<th><a href="#"><b>Пользователь</b></a></th>
				<th><a href="#"><b>Действие</b></a></th>
			</tr>
		</thead>
		<tbody>
		<?
 
		
		while ($result = mysqli_fetch_array($query)) 
		{
			echo     '<tr>
					  <td>'.$result['users_stat_id'].'</td>'.
					 '<td>'.$result['date'].'</td>'.
					  '<td>'.$result['caption'].'</td>'.
					 '<td>'.$result['name'].'</td>'.
					 '<td>'.$result['action'].'</td>
					 </tr>' ;
			}
		?>
		</tbody>
		</table>
 
			</div>
		</div>
	</div>
</section>





 