<html><head><meta charset="utf-8" />
<style>body {font-family:arial;}body{background-color:#333; color:#EEE; font-size:10px;}h1{font-size:14px;}h1{font-size:12px;}h1,h2{ display: inline; line-height: 1.5em;}


    .faq1 {
      color: #42FF00;
      font-size: 64px;
      weight: 500;
      display: block;
    }
    
    .faq2 {
      color: #0D3F05;
      font-size: 32px;
      weight: 500;
      display: block;
      line-height: 1em;
    }
    
    .faq_s {
      border: solid 2px #EEE;
 
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .faq_s_title {
		color: #EEE;
		font-size: 20px;
		position: relative;
		cursor: pointer;
    }
    
    .faq_s_plus {
		display: inline-block;
		background-color: #EEE;
		border-radius: 2px;
		width: 25px;
		height: 25px;
		text-align: center;
		line-height: 25px;
		text-decoration: none;
		color: #000;
		position: absolute;
		right: 0;
    }
    
    .faq_s_text {
      display: none;
      padding: 20px;
    }
	
	</style></head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>




<body>
 
<br/>
<?
$file = file('orderlog.csv');
$file = array_reverse($file);
 

foreach($file as $k=>$v)
{
	$str = explode(' ; ',$v);
	
	$buf = explode(' - ',$str[0]);
	if($buf[1])
	{
		$str['date'] = $buf[0];
		$str['time'] = $buf[1];
		$timestamp_date =  strtotime($str['date'].' '.$str['time']);
		$yearmounth = date('m.Y',$timestamp_date);
		if($yearmounth)
		{
			$ym_arr_c[$yearmounth]++;
			$ym_arr[$yearmounth][] = $str;
		}
	}
}
foreach($ym_arr_c as $k=>$v)
{
	?>
	<div class="faq_s">
	<div class="faq_s_title"><?=$k?> - <?=$v?> заявок <a class="faq_s_plus">+</a></div>
	<div class="faq_s_text"> 
		<table width="100%" border="1">
		 
		
		   <?
		   $i=0;
		   foreach($ym_arr[$k] as $k1=>$v1)
		   {
			   if($i==0)
			   {
					?><tr>
					<td>#</td>
					<td>1</td>
					<td>1</td>
					<td></td>
					</tr>
					<?
					
					 
			   } 
		
			   
			   $i++;
			   ?>
				<tr>
					<td><?=$i?></td>
					<td><?=$v1[0]?></td>
					<td><?=$v1[3]?></td>
					<td>
					<?
					unset($v1[1]);
					foreach($v1 as $k2=>$v2)
					{
					 
						
						 
						print $v2.'<br/>';
			 
			
					}
					?>
					</td>
				</tr>
				<?
			   
			  
		   }
		   ?>
		</table>
		</div>
	 </div>
	<?
	
}
//print_r($str);
 
	 
	 
print '</pre>';


?>






<script>


 




$('.faq_s_title').on('click', function(t) {	  
	$(this).next('.faq_s_text').slideToggle(200)
});
</script>
</body>