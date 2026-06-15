<!-- Карточка объекта -->
<?
	$v = $data;
	
	if($v['status']==0){$v['status']=2;}
	
	
	if($v['show_b']){$show_b_panel = '<div class="rentobj_stp rentobj_stp1">помещение с комиссией</div>';}
	else{$show_b_panel = '<div class="rentobj_stp rentobj_stp2">помещение без комиссии</div>';}
	
	 
	//print_r($_SESSION);
?>

<div class="row rentcard" >
	<div class="col-md-12">
		<div class="grey_shadow">
			<div class="row" style="padding: 13px 0px 13px 0px;">
				<div class="col-lg-4 p10" style="text-align:center; position:relative;">
				
				<?=$show_b_panel?>
				
				 <img src="<?=$v['plan']?>" alt="" class="rent_img" style="max-height:250px;">

				</div>
				<div class="col-lg-5 p10">
					<p class="rent_h3">
						<?=$v['area_n']?>
					</p>
					<p>
						<?=$v['h_adress']?>
						
					</p>
					 <p><?=$v['adress']?></p>
					<br/>
				 	<p>
						<?=$v['comment']?>
					</p>
					<br/>
					
					
					 <?
						  if($v['appointment']!=4  )
						  {
						  ?> 
							<ul class="rent_list">
							<?=$v['params_n']?>
							</ul>
					 
							<?
						  }
						?>
					
				
					<p><a href="iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>" class="rent_a iframe_r" style="text-decoration: underline;">Подробнее о помещении</a></p>
					
				</div>
				<div class="col-lg-3 p10" style="text-align:center;">
					 
					 
					 
				<?
				if( check_access('admin')  ||  $_SESSION['agency_id'] == 92 )
				{ 
		 
					if( check_access('admin')   )
					{ 
						?>
						<div style="padding:2px; text-align:center;">
						<a href="ctrind.php?ctr=rentobjects&act=edit&id=<?=$v['rent_objects_id']?>" class="rent_a iframe_r" style="text-decoration: underline;">Редактировать</a>
						</div>
						<?
					}
					if($data['status_broni_id'] && $data['status'] && $data['status']!=2)
					{
						if($data['login']=='admin'){  $data['caption']='';}
						$br_info=' '.fromsql_date($data['date']).'<br/>'.$data['login'].' - '.$data['name'].' <br> <b>'.$data['caption'].'</b><br/>';
					}
					
				}
				else
				{
					$br_info=' ';
				}
				//else
				//{
					?>
					
					
					
					 
										
										
					<div style="padding:20px; text-align:center;">
						<div style="font-size: 24px; line-height:1.2em; margin:10px; background:<?=$GLOBALS['broni_colors'][$v['status']]?>"><?=$GLOBALS['broni_status'][$v['status']]?></div>
						<?
						print $br_info;
						?>
					</div>
					
					
					 <?
						  if( ( $v['appointment']==4 || $v['sale'] > 0 ) && $v['sale_price'])
						  {
								$v['sale_price'] = str_replace(' ','',$v['sale_price']);
								$v['sale_price']  = preg_replace('/\D/', '',  $v['sale_price'] );
								$v['sale_price']  = (int)  $v['sale_price'];
 
						  ?><br/><br/>
							 <b style="font-size:28px;">  <?= number_format($v['sale_price'], 0, ' ', ' ')  ?> руб.</b>
							<br/><br/>
							<?
						  }
						?>
						
						
					<?
					if($v['status'] == 2 || !$v['status'] )
					{
						?>	 
						<a class="iframe_r" href="iframe_router.php?ctr=rentobjects&act=broniform&id=<?=$v['rent_objects_id']?>">
						  <button class="btn_bg_border">
							<div class="btn_bg_text p20"> ЗАБРОНИРОВАТЬ <i class="btn_arrowx"></i> </div>
						  </button>
						</a> 
						<?
					}
					
					
					
				//}
				?>
				
				
				
			
				
				</div>
			</div>
		</div>
	</div>
</div>
<!--  -->