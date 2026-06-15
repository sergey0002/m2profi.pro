<header class="header-lk">
	<div class="container">
		<div class="header-lk-main">
			<div id="btn-lk" class="menu-lk">
				<span></span>
				<span></span>
				<span></span>
			</div>
			<div class="header-account">
				<a href="#" class="header-account__profile"><?=$_SESSION['sh_login']?></a>
				<a href="/sahmatka/user.php?exit" class="header-account__logout"></a><br/>
				
			</div>
		</div>
	</div>
</header>


<div class="circle-blur circle-blur_inner-top-left" data-aos="fade-left" data-aos-delay="100"></div>
<div class="circle-blur circle-blur_inner-top-right" data-aos="fade-right" data-aos-delay="100" data-aos-offset="100">
</div>
<div class="circle-blur circle-blur_inner-center-right" data-aos="fade-left" data-aos-delay="100"></div>
 

<div class="overlay-page"></div>
<div class="sidenav">
	<div class="sidenav__close"></div>
	<div class="sidenav-wrap">
	
		<a href="http://em-nsk.ru" class="sidenav__backlink">Вернуться на сайт</a>
		<?
		
		//fw_check_access(1,2);
		?>
		<div class="sidenav-nav">
			<a href="http://em-nsk.ru" class="sidenav__backlink sidenav__backlink_mob">Вернуться на сайт</a>
			<div style="font-size:7px;"><?=$_SERVER['SERVER_ADDR'];?></div>
			<ul class="sidenav-menu">
				<li><a href="user.php?action=objects" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Квартиры</a></li>
				 
				 
				<?
				if( fw_check_access('parking') || 1==1 )
				{  
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='parking' || $_GET['ctr']=='parking_buildings' || $_GET['ctr']=='parking_areas'  || $_GET['ctr']=='parking_floors'  || $_GET['ctr']=='parking_spaces'  || $_GET['ctr']=='parking_broni' )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/carp.png" style="bottom: 7px;" alt=""></i>Парковки</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="ctrind.php?ctr=parking_floors&act=catalog">Каталог</a></li>
						
						<?
						if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin'   )
						{
							
						?>
						<li><a href="ctrind.php?ctr=parking_broni&act=index">Брони парковок</a></li>
						<li><a href="ctrind.php?ctr=parking_buildings">Здания</a></li>
						<li><a href="ctrind.php?ctr=parking_floors">Поэтажные планы</a></li>
						<li><a href="ctrind.php?ctr=parking_spaces">Парковочные места</a></li>
						<?
						}
						?>
					</ul>
				</li>
				<?
				}	
				?>	
				
				
				
				
					
				<?
				if( $_SESSION['sh_login'] == 'admin'   || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  )
				{ 
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='rentobjects' || $_GET['ctr']=='renthomes' || $_GET['ctr']=='rentbroni'   )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Коммерческие помещения  </a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
					
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag">Аренда</a></li>	
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag&sale=1"  > Продажа</a></li> 				
				<?
				if( $_SESSION['sh_login'] == 'admin'   || $_SESSION['sh_login'] == 'demo_admin'   )
				{ 
				?>
					<li><a href="ctrind.php?ctr=renthomes">Здания</a></li>
					<li><a href="ctrind.php?ctr=rentobjects">Помещения</a></li>
					<li><a href="ctrind.php?ctr=rentbroni&act=index">Брони</a></li>
				<?
				}
				?>
								
					</ul>
				</li>
				<?
				}
 
				else
				{
					?>
					<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='rentobjects' || $_GET['ctr']=='renthomes' || $_GET['ctr']=='rentbroni'   )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Коммерческие помещения  </a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
					
				 
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag">Аренда</a></li>	
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag&sale=1"  > Продажа</a></li> 		
				 
					 				
					</ul>
				</li>
				
				<?
				
					
				}
				?>			
				
				
				
				
				
				
				
				<?
				if($_SESSION['sh_login'] != 'keys1'   &&  $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' && $_SESSION['sh_login'] != 'director' ) // Администратор !!!!проверять отдел администраторы!
				{
					?>
					<li><a href="user.php?action=show_broni"><i><img src="template/default/images/menu-icon-2.svg" alt=""></i>Брони</a></li>
					<?
				}
				?>
				
				<?
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'director' ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					
					<?
					// РАскрытие меню
					if( $_GET['action']=='stat_salen' || $_GET['action']=='stat_sale' || $_GET['action']=='agency_stat' || $_GET['action']=='object_stat' || $_GET['action']=='stat_salen2'   ||  $_GET['ctr']=='parking_stat'  
					||  $_GET['ctr']=='stat_econom' ||  $_GET['ctr']=='stat_sales_dynamic' )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-3.svg" alt=""></i>Статистика</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="user.php?action=stat_salen">Подписанные договоры</a></li>
						<li><a href="user.php?action=stat_sale">Статистика продаж</a></li>
						<li><a href="user.php?action=agency_stat">Статистика агентств</a></li>
						<li><a href="user.php?action=object_stat">Статистика квартир</a></li>
						<li><a href="/sahmatka/ctrind.php?ctr=parking_stat">Статистика парковок</a></li>

						<li><a href="/sahmatka/ctrind.php?ctr=stat_econom">Сводная статистика</a></li>
						<li><a href="/sahmatka/ctrind.php?ctr=stat_sales_dynamic">Статистика продаж (NEW)</a></li>
					<li><a href="ctrind.php?ctr=op_broni_actual">Анализ броней</a></li>
						
						
						<?
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'director' ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  ) // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="/sahmatka/ctrind.php?ctr=econom" style=" display:none; font-size: 10px; color: #FFF;">Калькулятор наценки</a></li>
					<li><a href="/sahmatka/ctrind.php?ctr=metrika" style="display:none; font-size: 10px; color: #FFF;">Метрика</a></li>
				<?
				}
				?>
					</ul>
				</li>
				<? 
				}
				?> 
				 
				
				<?
				if( $_SESSION['sh_login'] != 'admin' &&  $_SESSION['adm_caption'] && $_SESSION['sh_login'] != 'demo_admin'  )
				{
					?>
					<li><a href="user.php?action=users"><i><img src="template/default/images/menu-icon-3.svg" alt=""></i>Пользователи</a></li>
					<?
				}
				?>
  
				<?
				if($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin' ) // Администратор !!!!проверять отдел администраторы!
				{
					?>
					<li><a href="/sahmatka/ctrind.php?ctr=agency"><i><img src="template/default/images/menu-icon-5.svg" alt=""></i>Агентства</a></li>
					<?
				}
				?>
				 
				<?
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'op15' || $_SESSION['sh_login'] == 'fd' ||   $_SESSION['sh_login'] == 'demo_admin')
				{
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['action']=='exc_zapis' || $_GET['action']=='zapis_editor'  )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Экскурсии</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="user.php?action=exc_zapis">Запись на экскурсии</a></li>
						
						
						<?
							if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'op15' ||   $_SESSION['sh_login'] == 'demo_admin')
							{
							?>
							<li><a href="user.php?action=zapis_editor">Редактор расписания</a></li>
							<?
							}
							?>
					</ul>
				</li>
				<?
				}
				elseif( $_SESSION['agency_id'] == "92" && $_SESSION['sh_login'] != 'keys1' &&  $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' ) //  
				{
					?>
					<li><a href="user.php?action=exc_zapis"><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Экскурсии</a></li>
					
					<?
				}
				
					 
					 
					 
					 
					 
					 
					 
				if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'op15'  || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'keys1' ||  $_SESSION['sh_login'] == 'keys2' || $_SESSION['sh_login'] == 'em_nsv' ||   $_SESSION['sh_login'] == 'demo_admin')
				{ 
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['action']=='zapis' || $_GET['action']=='zapis_editor_keys' || $_GET['ctr']=='zapiskeys' || $_GET['ctr']=='zapisx2' || $_GET['ctr']=='zapis_stat' )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Выдача <br>ключей</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
					<li><a href="ctrind.php?ctr=zapiskeys">Запись на выдачу</a></li>
					<?			
					if( $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'op15'   ||   $_SESSION['sh_login'] == 'demo_admin' )
					{ 
					//user.php?action=zapis_editor_keys
					?>
					<li><a href="ctrind.php?ctr=zapisx2">Редактор расписания</a></li>
					<?
					}
					?>		
					<li><a href="ctrind.php?ctr=zapis_stat">Статистика</a></li>
					</ul>
				</li>
				<?
				}				
				elseif( $_SESSION['agency_id'] == "92") //  
				{
					?>
					<li><a href="ctrind.php?ctr=zapiskeys"><i><img src="template/default/images/menu-icon-7.svg" alt=""></i>Выдача <br>ключей</a></li>
					<?
				}
				
				if($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'fd' ||   $_SESSION['sh_login'] == 'demo_admin') //  
				{
					?>
					<li><a href="user.php?action=messages"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Заявки <br>с сайта</a></li>				
					<?
				}
				?>
				
				<?
				if($_SESSION['sh_login'] != 'admin'  ||   $_SESSION['sh_login'] == 'demo_admin') // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="user.php?action=showroom"><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Шоурум</a></li>
 
					<li><a href="user.php?action=contact"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Контакты</a></li>
				 
				<?
				}
				
			//	print_r($_SESSION );
				?>
				
				
				
			
				
				
				
				
				
				
				
				
				
				
				
				
				
				
						
						
				
				
				
				
				
				
				
				
				
				<?
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'demo_admin' )
				{
				?>
				<li class="sidenav-dropmenu">
					<span></span>
					<?
					// РАскрытие меню
					if( $_GET['ctr']=='homeseditor' || $_GET['ctr']=='homes_kvartal'  )
					{
						$statm=1;
					}
					else{$statm=0;}
					?>
					<a href="#" <? if($statm){?>class="active"<?} ?>><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Настройки</a>
					<ul class="sidenav-submenu <? if($statm){?>active<?} ?>" <? if($statm){?>style="display: block;"<?}	?>>
						<li><a href="ctrind.php?ctr=homeseditor">Настройки объектов</a></li>
						 <li><a href="ctrind.php?ctr=homes_kvartal">Настройки ЖК</a></li>
					</ul>
				</li>
				
				
				 
				
				
				<?
				}
				
				
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'docm'    )
				{
					?>
					<li  ><a href="ctrind.php?ctr=agfiles&act=index"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы агентств</a></li>
					<?
				}
				
				
				if( $_SESSION['sh_login'] == 'admin'    )
				{
					?>
						<li><a href="user.php?action=docs"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы</a></li>
					<?
				}
				else
				{
					?> 
						<li style="display:non!e;"><a href="user.php?action=docs"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Документы</a></li>
					<?
				}
				
				
				if( $_SESSION['sh_login'] == 'admin' ||   $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'docm'    )
				{
					?>
					<li  ><a href="ctrind.php?ctr=stat_econom_arh"><i><img src="template/default/images/menu-icon-9.svg" alt=""></i>Архив статистики</a></li>
					<?
				}
				 
				
				?>
				
				
				
				
				
				 
			
				
				
			 
				
				
				
			</ul>
			
			
 
  
  
  
		</div>
	</div>
	
	
	

</div>

<style>
.mmenu li{display:inline; padding:10px;}
.iframe_r{position:static; z-index:100;  }
section{min-height:100vh;}
</style>





















<?
// ПОПАП УВЕДОМЛЕНИЕ 1 раз 
if($GLOBALS['inlogin'])
{
	?>
	
	    <style>
        /* Стили popup */
        .custom-popup-content {
            background: #FF0000;
            border-radius: 12px;
            padding: 30px 40px;
            max-width: 600px;
            width: 100%;
            position: relative;
            z-index: 9999;
            color: white;
            font-family: Arial, sans-serif;
        }

        .popup-title {
            font-size: 36px;
            line-height: 42px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .popup-message {
            font-size: 16px;
            line-height: 24px;
            margin-bottom: 30px;
        }

        .popup-button {
            background: #FFF01A;
            color: #000000;
            font-size: 18px;
            padding: 12px 40px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .popup-button:hover {
            background: #D9C200;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .popup-button:active {
            background: #B5A700;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }

        .arrow-hover {
            display: inline-block;
            vertical-align: middle;
            margin-left: 15px;
            position: relative;
            width: 20px;
            height: 12px;
        }

        .arrow-hover div {
            position: relative;
            top: 3px;
            height: 2px;
            background-color: #000000;
        }

        .arrow-hover div::after,
        .arrow-hover div::before {
            content: '';
            position: absolute;
            width: 10px;
            height: 2px;
            background-color: #000000;
        }

        .arrow-hover div::after {
            top: -3px;
            right: -2px;
            transform: rotate(45deg);
        }

        .arrow-hover div::before {
            top: 3px;
            right: -2px;
            transform: rotate(-45deg);
        }

        .popup-button:hover .arrow-hover {
            animation: arrow-hover 1s linear infinite;
        }

        @keyframes arrow-hover {
            0% { transform: translateX(0); }
            50% { transform: translateX(10px); }
            100% { transform: translateX(0); }
        }

        .warning-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 180px;
            height: 180px;
            opacity: 0.8;
        }

        .warning-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @media (max-width: 768px) {
            .custom-popup-content {
                padding: 20px;
            }

            .popup-title {
                font-size: 28px;
                line-height: 34px;
            }

            .popup-message {
                font-size: 14px;
                line-height: 20px;
            }

            .popup-button {
                width: 100%;
                padding: 10px 20px;
            }

            .warning-icon {
                position: static;
                margin-top: 20px;
                width: 100px;
                height: 100px;
            }
        }

        /* Zoom In (при открытии) */
        .mfp-zoom-in .mfp-figure {
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease-out;
        }

        /* Активируем анимацию при открытии */
        .mfp-ready.mfp-zoom-in .mfp-figure {
            opacity: 1;
            transform: scale(1);
        }

        /* Zoom Out (при закрытии) */
        .mfp-zoom-out .mfp-figure {
            animation: zoomOutCustom 0.3s forwards;
        }

        @keyframes zoomOutCustom {
            to {
                opacity: 0;
                transform: scale(0.8);
            }
        }

        /* Фон затемнения */
        .mfp-bg {
            background-color: rgba(0, 0, 0, 0.7);
            
        }

        .mfp-ready .mfp-bg {
            opacity: 1;
        }

        /* Для inline-контента */
        .mfp-inline-holder .mfp-content, .mfp-ajax-holder .mfp-content {
            width: auto;
            cursor: auto;
        }
    </style>
 

<!-- Модальное окно -->
<div class="mfp-hide white-popup" id="popup-agree2">
    <figure class="mfp-figure"> <!-- Важная обёртка для анимации -->
        <div class="custom-popup-content">
            <div class="popup-container">
                <div class="popup-header">
                    <h2 class="popup-title">Внимание!!!</h2>
                </div>
                <div class="popup-body">
                    <p class="popup-message">
                        Уважаемые Партнеры!<br>
                        Просим Вас обратить внимание на изменение пункта 4.1.16 и пункта 6.1 Субагентского договора (публичная оферта)!<br><br>
                        В случае нарушения сроков предоставления Субагентом закрывающих документов по Субагентскому договору (публичная оферта), документы к учету не принимаются, а услуги Субагенту не оплачиваются.
                    </p>
                    <button class="popup-button" id="continue-button">
                        Продолжить
                        <span class="arrow-hover">
                            <div></div>
                        </span>
                    </button>
                </div>
                <div class="warning-icon">
                    <img src="https://em.m2profi.pro/w.svg"  alt="Warning">
                </div>
            </div>
        </div>
    </figure>
</div>




<div class="mfp-hide white-popup" id="popup-agree">
    <figure class="mfp-figure"> <!-- Важная обёртка для анимации -->
        <div class="custom-popup-content">
            <div class="popup-container">
                <div class="popup-header">
                    <h2 class="popup-title">Внимание!!!</h2>
                </div>
                <div class="popup-body">
                    <p class="popup-message">
					
					       <b>Уважаемые партнеры Группы компании «Сталкер»!</b><br>
					
					В связи с участившимся случаями нарушений Субагентского договора (публичной оферты), размещённого в телекоммуникационной сети Интернет по адресу: https://em-nsk.ru/reg/, напоминаем Вам, о том, что в указанном Договоре содержатся положения Параграфа 12 (Особые условия), которые носят обязательный характер для каждого субагента, вне зависимости от того, чьими действиями были нарушены условия указанного положения Договора (сотрудниками субагента / субагентом лично).<br/>
	Положения Параграфа 12 содержат пункты, предусматривающие ответственность субагента за обращения / раскрытие условий сотрудничества перед третьими лицами, а также перед Принципалом.<br/>
	Согласно абзацу 2 пункта 2.10. Субагентского Договора, субагентам категорически запрещено напрямую обращаться к Принципалу (Энергомонтаж) по любым вопросам взаимодействия Агента и Субагента, вытекающим из условий заключенного Договора.<br/>
	 В случае нарушения условий Договора к лицу, допустившему такое нарушение со стороны Агента в лице Общества с ограниченной ответственностью «Сталкер» и/или Общества с ограниченной ответственностью «М2», могут быть применены меры ответственности, заключающиеся в ограничении доступа (блокировки) личного кабинета «Субагента», а также штрафные санкции, предусмотренные в актуальной редакции Субагентского Договора –<b style="color: #000;"> штраф в размере 200 000 (двести тысяч) рублей</b>, за каждый факт такого рода обращения.<br/>
	Кроме того, настоятельно просим Вас обратить своё внимание на положения пункта 6.1. Договора, регламентирующего порядок сдачи Отчетов об исполнении поручения. Напоминаем Вам, что в случае нарушения указанного порядка, со стороны Агента, может последовать задержка / отказ в выплате субагентского вознаграждения, в связи с неисполнением условий Договора. <br/>
	 
Для дальнейшего плодотворного сотрудничества, просим Вас дополнительно внимательно ознакомиться с условиями заключенного Договора и четко следовать его положениям!

                    </p>
                    <button class="popup-button" id="continue-button">
                        Продолжить
                        <span class="arrow-hover">
                            <div></div>
                        </span>
                    </button>
                </div>
                <div class="warning-icon">
                    <img src="https://em.m2profi.pro/w.svg"  alt="Warning">
                </div>
            </div>
        </div>
    </figure>
</div>



<script>
    // Работа с куками
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }

    function getCookie(name) {
        const cookies = document.cookie.split('; ');
        for (const cookie of cookies) {
            const [key, value] = cookie.split('=');
            if (key === name) return value;
        }
        return null;
    }

    function checkAgreementCookie() {
		return false;
        return !!getCookie('contract_selected');
    }


/*
    $(document).ready(function () {
        if (!checkAgreementCookie()) {
            $.magnificPopup.open({
                items: {
                    src: '#popup-agree',
                    type: 'inline'
                },
                closeOnBgClick: false,
                enableEscapeKey: false,
                showCloseBtn: true,
                mainClass: 'mfp-zoom-in', // Эффект появления — zoom in
                removalDelay: 300 // Задержка перед удалением DOM для анимации закрытия
            });

            $('#continue-button').on('click', function () {
                setCookie('contract_selected', 'agreed', 365);

                // Добавляем класс zoom-out перед закрытием
                $.magnificPopup.instance.wrap.addClass('mfp-zoom-out');

                // Ждём завершения анимации и закрываем popup
                setTimeout(function () {
                    $.magnificPopup.close();
                }, 300); // Время должно совпадать с длительностью анимации
            });
        }
    });
	
	*/
</script>
  
	
	
	<?
	
	
	
	
	
	
}

?>


<?
########################################## ФОРМА ОПРОСА
?>

<?php if (isset($_SESSION['show_survey_modal']) && $_SESSION['show_survey_modal'] === true): ?>
<?php unset($_SESSION['show_survey_modal']); ?>
<style>
.survey_panel_bg {
    background-color:#000;  
    position:fixed; top:0; 
    width:100%; 
    height:100%;
    opacity: 0.3;
    z-index:99998;
    display:none;
}
@media (min-width: 768px) {
    .survey_panel {
        padding: 30px;
        background:#FFF;
        height:100vh;
        position: fixed;
        top:0px;
        right: 0;
        width: 550px;
        max-width: 90%;
        z-index:99999;
        display:none;
        overflow-y: auto;
    }
}
@media (max-width: 767px) {
    .survey_panel {
        padding: 20px;
        background: #FFF;
        height: auto;
        position: fixed;
        top: 60px;
        right: 5%;
        left: 0;
        width: 90%;
        max-width: 90%;
        z-index: 99999;
        border-bottom-right-radius: 25px;
        border-top-right-radius: 25px;
        display:none;
        overflow-y: auto;
        max-height: 85vh;
    }
}
.survey-popup-title {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #000;
}
.survey-popup-subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 25px;
    line-height: 1.4;
}
.survey-form-group {
    margin-bottom: 25px;
}
.survey-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 15px;
}
.survey-form-group textarea,
.survey-form-group input[type="text"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}
.survey-form-group textarea:focus,
.survey-form-group input[type="text"]:focus {
    border-color: #00CDAD;
    outline: none;
}
.nps-scale {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9f9f9;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
}
.nps-scale label {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    margin-bottom: 0;
    font-weight: normal;
    font-size: 14px;
}
.nps-scale input[type="radio"] {
    margin-bottom: 5px;
    transform: scale(1.2);
}
.survey-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
}
.survey-btn-submit {
    background: #00CDAD;
    color: #fff;
    border: none;
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s;
}
.survey-btn-submit:hover {
    background: #00b095;
}
.survey-btn-close {
    background: transparent;
    color: #999;
    border: none;
    text-decoration: underline;
    cursor: pointer;
    font-size: 14px;
}
.survey-btn-close:hover {
    color: #666;
}
@media (max-width: 768px) {
    .nps-scale {
        flex-wrap: wrap;
        gap: 5px;
    }
    .nps-scale label {
        flex: 1 0 10%;
    }
    .survey-actions {
        flex-direction: column-reverse;
        gap: 15px;
    }
    .survey-btn-submit {
        width: 100%;
    }
}
</style>

<div class="survey_panel_bg"></div>
<div class="survey_panel" id="survey-sliding-panel">
    <div style="text-align:right;">
        <a href="#" class="close_survey_panel" style="color: #333; font-size: 30px; text-decoration: none; cursor:pointer; line-height: 1;">✕</a>
    </div>

    <div class="survey-popup-title">Опрос пользователей</div>
    <div class="survey-popup-subtitle">Помогите нам стать лучше! Ответьте на несколько вопросов о нашей системе.</div>
    
    <form id="feedbackSurveyForm">
        <div class="survey-form-group">
            <label>1. С какой вероятностью вы порекомендуете наш программный продукт коллегам или друзьям по шкале от 0 до 10?</label>
            <div class="nps-scale">
                <?php for($i=0; $i<=10; $i++): ?>
                <label>
                    <input type="radio" name="nps_score" value="<?= $i ?>" required>
                    <?= $i ?>
                </label>
                <?php endfor; ?>
            </div>
            <div style="display:flex; justify-content: space-between; font-size:12px; color:#999; margin-top:5px;">
                <span>0 — Ни за что</span>
                <span>10 — Обязательно</span>
            </div>
        </div>

        <div class="survey-form-group">
            <label>2. Что именно вам больше всего нравится в нашем продукте?</label>
            <textarea name="likes" rows="3" placeholder="Ваш ответ..."></textarea>
        </div>

        <div class="survey-form-group">
            <label>3. Что, по вашему мнению, можно было бы улучшить в продукте?</label>
            <textarea name="improvements" rows="3" placeholder="Ваш ответ..."></textarea>
        </div>

        <div class="survey-form-group">
            <label>4. Есть ли функции или возможности, которых вам не хватает?</label>
            <textarea name="missing_features" rows="3" placeholder="Ваш ответ..."></textarea>
        </div>

        <div class="survey-form-group">
            <label>5. Как бы вы описали наш продукт одним словом или короткой фразой?</label>
            <input type="text" name="short_desc" placeholder="Ваш ответ...">
        </div>

        <div class="survey-actions" style="padding-bottom: 20px;">
            <button type="button" class="survey-btn-close close_survey_panel">Закрыть, не отвечать</button>
            <button type="submit" class="survey-btn-submit">Отправить ответы</button>
        </div>
    </form>
    <div id="survey-thankyou-msg" style="display:none; text-align:center; padding: 40px 20px; padding-bottom: 60px;">
        <h3 style="color:#00CDAD; margin-bottom:15px; font-size:24px;">Спасибо большое!</h3>
        <p style="font-size: 16px; color:#666;">Ваши ответы успешно отправлены.</p>
    </div>
</div>

<script>
$(document).ready(function() {
    // Автоматическое открытие панели
    setTimeout(function() {
        $('.survey_panel').show(300);
        $('.survey_panel_bg').show();
    }, 500); // Небольшая задержка для плавности появления после исчезновения прелоадера

    // Обработка закрытия панели
    $('.close_survey_panel, .survey_panel_bg').click(function(e) {
        e.preventDefault();
        $('.survey_panel').hide(300); 
        $('.survey_panel_bg').hide();
    });

    // Закрытие по клику вне области (на фоне)
    $(document).mouseup( function(e){
        var div = $(".survey_panel");
        if (!div.is(e.target) && div.has(e.target).length === 0) {
            $('.survey_panel').hide(300); 
            $('.survey_panel_bg').hide();
        }
    });

    // Отправка формы аяксом
    $('#feedbackSurveyForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        // Disable submit button
        $(this).find('.survey-btn-submit').prop('disabled', true).text('Отправка...');
        
        $.ajax({
            url: '/sahmatka/ajax_survey.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#feedbackSurveyForm').hide();
                $('#survey-thankyou-msg').fadeIn();
                setTimeout(function() {
                    $('.survey_panel').hide(300); 
                    $('.survey_panel_bg').hide();
                }, 2500);
            },
            error: function() {
                alert('Произошла ошибка при отправке анкеты. Попробуйте еще раз.');
                $('#feedbackSurveyForm').find('.survey-btn-submit').prop('disabled', false).text('Отправить ответы');
            }
        });
    });
});
</script>
<?php endif; ?>
 
<?
########################################## ФОРМА ОПРОСА
?>

