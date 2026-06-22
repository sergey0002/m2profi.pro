<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
	<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободна</li>
	<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирована</li>
	<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продана</li>
	<?
	if(check_access('admin') ||  $_SESSION['agency_id'] == 92)
	{
		?>
		<li class="objects-head-status__grey" style="display:inline-block; margin-right: 20px;">Забронирована  застройщиком</li>
		<li class="objects-head-status__blue" style="display:inline-block; margin-right: 20px;">Забронирована  подрядчиком</li>
		<?
	}
	?>
</ul>