<?

class ctr__ajax_wiget extends ctr__
{  


	function act__wiget()
	{
		
		$data['ctr'] = $_GET['c'];
		$data['act'] = $_GET['a'];
		
	  	$this->tpl($data,'ajax_wiget','wiget');


	
	}
}

?>