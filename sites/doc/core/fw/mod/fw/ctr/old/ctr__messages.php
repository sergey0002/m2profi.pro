<?

class ctr__messages extends ctr__
{
	
	
	
	function act__index()
	{
		global $t;
		$t['h1'] = 'Заявки  с сайта';
		
		$sql = 'SELECT * FROM messages
		order by messages_id desc  
		';
		$arr = $this->mysql->get_arr($sql);
		
		$titles['messages_id'] = 'id';
 
		$titles['date'] = 'Дата';
		$titles['to'] = 'Получатель';
		$titles['subject'] = 'Тема';
		$titles['text'] = 'Текст заявки';
		 
		?>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
				 
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-user table">
			<?			
				$this->mysql->display_table($arr,$titles);
			?>
			</div>
		</div>			
		<?
	}
	
	
}