<?


class fw_sitedirtree
{
	
	
	static function scan_callback($path,$name,$is_dir,$cnf,$level) 
	{
		
		$r_dir = '/'.str_replace($GLOBALS['basedir'],'',$path).''; // Относительная диреткория
		
		$fw_files = $cnf['fw_files'];
		$fw_ext =   $cnf['fw_ext'];
		
		if( !$is_dir )
		{
			$info = pathinfo($path);
		 //print_r($info);
			if( $fw_files[$name] ) // СПЕЦИАЛЬНЫЙ ТИП ФАЙЛА
			{
				$color = $fw_files[$name]['color'];
				$text = $fw_files[$name]['text'];
			}
			else
			{
				if($fw_ext[$info['extension']]) // СПЕЦИАЛЬНОЕ РАСШИРЕНИЕ ФАЙЛА
				{
					$color = $fw_ext[$info['extension']]['color'];
					$text = $fw_ext[$info['extension']]['text'];
				}
				else // Не известное расширение (по умолчанию)
				{
					$color = 'red';
					$text = 'НЕ ИЗВЕСТНЫЙ ФАЙЛ';
				}
			}
			
			if($info['extension'] == 'php' && !$fw_files[$name] )
			{
				//формируем ссылку на файл
				$link_dir = str_replace($GLOBALS['basedir'].'site','',$info['dirname']);
				if($name =='index.php'){$link_file = '';}
				else{$link_file = $info['filename'];}
				   $link = 'http://'.$_SERVER['HTTP_HOST'].'/'.$link_dir.$link_file;
				   $link_t = ' (<a title="Перейти на страницу" href="'.$link.'">'.$link.'</a>)';
				   $link_e = ' (<a title="Редактирвоать файл" href="'.$r_dir.'">'.$r_dir.'</a>)';
				   echo ' <a title="'.$text.'" href="" style="color:'.$color.'">', htmlspecialchars($name), '</a> - </span> '.$link_e.$link_t ;
			}
			else
			{
				echo ' <span title="'.$text.'"  style="color:'.$color.'">'.htmlspecialchars($name). '</span>   ';
			}
			
		}
		else
		{
			echo ' <span style="color:#000; font-weight:bold;">', htmlspecialchars($name), '</span>';
		}
	}
	 
 
	static function showdir($dir, $callback='',$cnf='', $level = 0) 
	{
 if($level==0)
 {
		//https://xhtml.ru/2022/html/tree-views/
		?>
		<style>
		
		.tree{
  --spacing : 1.5rem;
  --radius  : 10px;
}

.tree li{
  display      : block;
  position     : relative;
  padding-left : calc(2 * var(--spacing) - var(--radius) - 2px);
}

.tree ul{
  margin-left  : calc(var(--radius) - var(--spacing));
  padding-left : 0;
}

.tree ul li{
  border-left : 2px solid #ddd;
}

.tree ul li:last-child{
  border-color : transparent;
}

.tree ul li::before{
  content      : '';
  display      : block;
  position     : absolute;
  top          : calc(var(--spacing) / -2);
  left         : -2px;
  width        : calc(var(--spacing) + 2px);
  height       : calc(var(--spacing) + 1px);
  border       : solid #ddd;
  border-width : 0 0 2px 2px;
}

.tree summary{
  display : block;
  cursor  : pointer;
}

.tree summary::marker,
.tree summary::-webkit-details-marker{
  display : none;
}

.tree summary:focus{
  outline : none;
}

.tree summary:focus-visible{
  outline : 1px dotted #000;
}

.tree li::after,
.tree summary::before{
  content       : '';
  display       : block;
  position      : absolute;
  top           : calc(var(--spacing) / 2 - var(--radius));
  left          : calc(var(--spacing) - var(--radius) - 1px);
  width         : calc(1.7 * var(--radius));
  height        : calc(1.7 * var(--radius));
  border-radius : 50%;
  background    : #ddd;
}

.tree summary::before{
  content     : '+';
  z-index     : 1;
  background  : #696;
  color       : #fff;
  line-height : calc(2 * var(--radius) - 2px);
  text-align  : center;
}

.tree details[open] > summary::before{
  content : '−';
}
		</style>
		
		<?
		if($level==0){$class='class="tree"';}
 }
 
	 	if ($level > 10) {debug_print_backtrace() and die();}
		$list = scandir($dir);
		if (is_array($list)) 
		{
			$list = array_diff($list, array('.', '..'));
			if ($list) 
			{
				
				echo '<ul '.$class.'>';
				foreach($list as $k=>$v)
				{
					echo '<li>';
					$name = $v;
					$path = $dir . '/' . $name;
					$is_dir = is_dir($path);
					
					$callback($path,$name,$is_dir,$cnf,$level);
					
					if($is_dir)
					{
						  fw_sitedirtree::showdir($path, $callback,$cnf, $level + 1);
					}
					echo '</li>';
				}
				echo '</ul>';				
			}
		}
		else 
		{
			echo '<i>не могу прочитать</i>';
		}
	}
	 
 
  
}

 