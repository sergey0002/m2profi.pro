<?
extract($data);
?>
<style>
.doc-card {
    padding: 20px;
}
.doc-card .filelink {
    border: solid 2px #3d535f;
    display: inline-block;
    padding: 10px;
    color: #3d535f;
}
.doc-card .filelink:hover {
    color: #000;
    border: solid 2px #000;
}
.doc-card td {
    border: solid 1px #000;
}
</style>
<div class="doc-card">

<?
foreach($card_data as $k=>$v)
{
	$editdate = $date = date('d.m.Y H:i:s', $v['uptime'] );
	$file_caption = $v['caption'];
	if(!$file_caption){$file_caption = $v['name'];}
	
	if(!$v['actual'])
	{
		$dl = '/sahmatka/ajax_router.php?ctr=doc&act=download&id='.(int)$v['files2node_id'];
		?> 

		<div class="actfile" style="padding: 20px;text-align: center;padding-top: 30px; font-size: 20px;  font-weight: bold;">
		
		<a href="<?=$dl?>" class="filelink doc-card-download" target="_blank" rel="noopener">
		 
		Файл:<br/><?=$file_caption?><br/><br/>
		<img src="/sahmatka/template/download.png" width="50"><br/><br/>
		<?=$editdate?><br/>
		<?=$user?>
		</a>
		<br/>
		
		<?
		if($_SESSION['users_group_id']=="3" || $_SESSION['users_group_id']=="1")
		{
			?>
			<a href="iframe_router.php?ctr=doc&act=edit&id=<?=$v['files2node_id']?>" class="doc-modal-edit-link" data-file-id="<?=$v['files2node_id']?>" style="font-size:12px; color:red">Редактировать</a>
			<?
		}
		?>
		
		</div>
 
		<?
		break;
	}
}
?>
<br/><br/>
История:
<table width="100%;">
<?
foreach($card_data as $k=>$v)
{
	$editdate = $date = date('d.m.Y H:i:s', $v['uptime'] );
	$file_caption = $v['caption'];
	if(!$file_caption){$file_caption = $v['name'];}
	
	if(!$v['actual'])
	{
		$dl = '/sahmatka/ajax_router.php?ctr=doc&act=download&id='.(int)$v['files2node_id'];
		?> 
		 <tr>
			 <td>
				<?=$editdate?>
			 </td>
			 <td>
				<a href="<?=$dl?>" class="doc-card-download" target="_blank" rel="noopener"><?=$file_caption?></a>
			 </td>
			 <td>
				<?=$user?>
			 </td>
			 
		 </tr>
		<?
		break;
	}
}
?>
</table>
</div>
