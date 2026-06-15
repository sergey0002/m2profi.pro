<?

$result = $data;

 
if(!$result['pom']){$pom='<span style="color:green;">нет</span>';}
else{$pom='<span style="color:red;">да</span>';}
		


if(!$result['dkp']){$dkp='<span style="color:green;">нет</span>';}
else{$dkp='<span style="color:red;">да</span>';}


		
			
			 echo    '<tr'; if($result['del']){ print ' class="del" ' ;} print '>';
			 echo '
					  <td><span>'.$result['zapis_id'].'</span></td>'.
					 '<td style="white-space: nowrap;"><span>'
					 
					 .date('d.m.Y',strtotime( $result['date']));
					 
					 echo '</span>';
					 if($result['del'] && $result['z2date'] ){ print '<br/><span style="color:#EEE; text-decoration:none;">'. date('d.m.Y',strtotime( $result['z2date'])).'</span>'  ; }
					 echo '</td>';
					 
					 echo '<td><span>'.$result['time'];
					  echo '</span>';
					 if($result['del'] &&  $result['z2time'] ){ print '<br/><span style="color:#EEE; text-decoration:none;">'.  $result['z2time'].'</span>'; }
					 
					 echo ' </td>'.
					 '<td><span>'.$result['long_title'].'</span></td>'.
					 '<td><span>'.$result['section'].'</span></td>'.
					 '<td><span>№'.$result['apartment_num'].' ('.$result['floor'].'эт, '.$result['rooms'].'к, '.$result['area'].'м<sup>2</sup>)</span></td>' .
					 '<td><span>'.$result['phone'].'</span></td>' .
					  '<td><span>'.$result['email'].'</span></td>' .
					 '<td><span>'.$pom.'</span></td>' .
					  '<td><span>'.$dkp.'</span></td>' .
					 '<td><span>'.$result['fio'].'</span></td>' ;
					
					print '<td style="wordwrap:nowrap;">';
					 
					   print '<a href="iframe_router.php?ctr=zapiskeys&act=card&id='.$result['zapis_id'].'" style="color:#0000ff; font-size: 18px; " class="iframe_rajax ">i</a>&nbsp;&nbsp;';
					  
					if($_SESSION['sh_login']=='admin' || $_SESSION['sh_login']=='op15')
					{
						 print '<a href="iframe_router.php?ctr=zapiskeys&act=edit&id='.$result['zapis_id'].'" style="color:green; " class="iframe_rajax table-edit"></a>&nbsp;&nbsp;';	
						// print '<a href="user.php?action=zapis&del_id='.$result['zapis_id'].'" style="color:red; font-size: 18px; " onclick="return confirm(\'Вы действительно хотите удалить запись.\');">X</a> ';	
						 
						print '<a href="ctrind.php?ctr=zapiskeys&act=del&id='.$result['zapis_id'].'" style="color:red; font-size: 18px; " onclick="return confirm(\'Вы действительно хотите удалить запись.\');">X</a> ';	
						 
						
					}
					print '</td>';