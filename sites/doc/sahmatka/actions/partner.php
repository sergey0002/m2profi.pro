 
			<div style="background-color:#F2F2F2; width:100%;">
				<center>
				 <ul class="mmenu">

			 
					<li><a href="user.php?action=partner&home=pr2" style="color:#000; text-decoration:underline;">Приозерный №2</a> </li> 
				 
					<li><a href="user.php?action=partner&home=452" style="color:#000; text-decoration:underline;">452</a> </li>
								 
				</ul>
				</center>
			</div>			
		<?

	 if(!$_GET['home']){$_GET['home']='451';}
	if($_GET['home'])
	{
	    	 $file = 'h_'.$_GET[home].'.php';
		 if(  file_exists(  $file))
		 {
			 include( $file);
		 }
	}