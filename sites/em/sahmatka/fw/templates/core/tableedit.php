<?
$v=$data;
?>
		<style>
		.dtable_del_class{text-decoration:line-through; color:#CCC;}
		
		/* Раскрывающиеся строки */
		.fw_hiderow{display:none; border:solid 1px #EEE; background-color:#fcfcfc;}
		.dtable_ch:hover > td { background:#F0F0F0; cursor:pointer; } 
		.fw_selrow{ background:#F0F0F0;}
		
		#fw_ajaxdata table tr td{padding:10px 10px;}
			.table table tr td {
			padding: 12px 12px;
			line-height: 1;
		}
		.fw_hiderow table tr td{font-size:12px;}
		</style>
		
					<div class="stat">
						<div class="stat-top">
							<?=$v['searchform']?>	
						</div>
						<div class="stat-table stat-table-user stat-table_notpd table">

							<table class="dtable" id="fwcrudtable" >
							<thead>
							<tr class="dtable">
						<?=$v['tablehead']?>
							</tr>
							</thead>
							<tbody id="fw_ajaxdata">
							</tbody>
							</table>
	 
							
							<div style="width:100%; max-width:100vw; text-align:center; padding:50px; " id="progressbar"  >
								 <img src="loader.gif" />
							</div>
						</div>
					</div>			
 
		<div id="fw_data_tbody2"></div>