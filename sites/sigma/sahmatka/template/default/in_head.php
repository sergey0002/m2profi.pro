<?php
$m2SiteSubdomain = trim((string)$GLOBALS['config']['site_subdomain']);
$m2SiteBaseUrl = 'https://' . $m2SiteSubdomain . '.m2profi.pro';
$m2SahmatkaUrl = $m2SiteBaseUrl . '/sahmatka';
$m2AjaxRouterUrl = $m2SahmatkaUrl . '/ajax_router.php';
$m2IframeRouterUrl = $m2SahmatkaUrl . '/iframe_router.php';
$m2AjaxActionsUrl = $m2SahmatkaUrl . '/ajax_actions.php';
$m2ClientSiteUrl = htmlspecialchars((string)$GLOBALS['config']['client_site_url'], ENT_QUOTES, 'UTF-8');
$m2WarningIconUrl = htmlspecialchars($m2SiteBaseUrl . '/w.svg', ENT_QUOTES, 'UTF-8');
$m2PublicConfig = json_encode(array(
	'siteSubdomain' => $m2SiteSubdomain,
	'baseUrl' => $m2SiteBaseUrl,
	'sahmatkaUrl' => $m2SahmatkaUrl,
	'ajaxRouter' => $m2AjaxRouterUrl,
	'iframeRouter' => $m2IframeRouterUrl,
	'ajaxActions' => $m2AjaxActionsUrl,
	'assetsUrl' => $m2SiteBaseUrl,
	'clientSiteUrl' => $GLOBALS['config']['client_site_url'],
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<script>
window.M2PROFI_CONFIG = <?=$m2PublicConfig?>;
window.M2WidgetConfig = window.M2WidgetConfig || window.M2PROFI_CONFIG;
</script>
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
	
		<a href="<?=$m2ClientSiteUrl?>" class="sidenav__backlink">Вернуться на сайт</a>
		<?
		
		//fw_check_access(1,2);
		?>
				<div class="sidenav-nav">
			<a href="<?=$m2ClientSiteUrl?>" class="sidenav__backlink sidenav__backlink_mob">Вернуться на сайт</a>
			<div style="font-size:7px;"><?=$_SERVER[SERVER_ADDR];?></div>
			<ul class="sidenav-menu">
				<li><a href="user.php?action=objects&home=60&sdan=0" class="active"><i><img src="template/default/images/menu-icon-1.svg" alt=""></i>Апартаменты</a></li>
				 
				 
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
						if( check_access('admin') || $_SESSION['sh_login'] == 'demo_admin'   )
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
				if( check_access('admin')   || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  )
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
					
					
					<li><a href="ctrind.php?ctr=rentobjects&act=index_ag&sale=1"  > Продажа</a></li> 		
<li><a href="ctrind.php?ctr=rentobjects&act=index_ag">Аренда</a></li>						
				<?
				if( check_access('admin')   || $_SESSION['sh_login'] == 'demo_admin'   )
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
				if( check_access('admin') ||   $_SESSION['sh_login'] == 'director' ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  ) // Администратор !!!!проверять отдел администраторы!
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
						<li><a href="user.php?action=object_stat"><?= unit_phrase('stats_title') ?></a></li>
						<li><a href="/sahmatka/ctrind.php?ctr=parking_stat">Статистика парковок</a></li>

						<li><a href="/sahmatka/ctrind.php?ctr=stat_econom">Сводная статистика</a></li>
						<li><a href="/sahmatka/ctrind.php?ctr=stat_sales_dynamic">Статистика продаж (NEW)</a></li>
					<li style="display:none;"><a href="ctrind.php?ctr=op_broni_actual">Анализ броней</a></li>
						
						
						<?
				if( check_access('admin') ||   $_SESSION['sh_login'] == 'director' ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin'  ) // Администратор !!!!проверять отдел администраторы!
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
				if( !check_access('admin') &&  $_SESSION['adm_caption'] && $_SESSION['sh_login'] != 'demo_admin'  )
				{
					?>
					<li><a href="user.php?action=users"><i><img src="template/default/images/menu-icon-3.svg" alt=""></i>Пользователи</a></li>
					<?
				}
				?>
  
				<?
				if(check_access('admin')  || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin' ) // Администратор !!!!проверять отдел администраторы!
				{
					?>
					<li><a href="/sahmatka/ctrind.php?ctr=agency"><i><img src="template/default/images/menu-icon-5.svg" alt=""></i>Агентства</a></li>
					<?
				}
				?>
				 
				<?
				if( check_access('admin') || $_SESSION['sh_login'] == 'op15' || $_SESSION['sh_login'] == 'fd' ||   $_SESSION['sh_login'] == 'demo_admin')
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
							if( check_access('admin') || $_SESSION['sh_login'] == 'op15' ||   $_SESSION['sh_login'] == 'demo_admin')
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
				
					 
					 
					 
					 
					 
					 
					 
				if( check_access('admin') || $_SESSION['sh_login'] == 'op15'  || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'keys1' ||  $_SESSION['sh_login'] == 'keys2' || $_SESSION['sh_login'] == 'em_nsv' ||   $_SESSION['sh_login'] == 'demo_admin')
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
					if( check_access('admin') || $_SESSION['sh_login'] == 'op15'   ||   $_SESSION['sh_login'] == 'demo_admin' )
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
				
				if(check_access('admin')  || $_SESSION['sh_login'] == 'fd' ||   $_SESSION['sh_login'] == 'demo_admin') //  
				{
					?>
					<li><a href="user.php?action=messages"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Заявки <br>с сайта</a></li>				
					<?
				}
				?>
				
				<?
				if(!check_access('admin')  ||   $_SESSION['sh_login'] == 'demo_admin') // Администратор !!!!проверять отдел администраторы!
				{
				?>
					<li><a href="user.php?action=showroom"><i><img src="template/default/images/menu-icon-6.svg" alt=""></i>Шоурум</a></li>
 
					<li><a href="user.php?action=contact"><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Контакты</a></li>
				 
				<?
				}
				
			//	print_r($_SESSION );
				?>
				
				
				
			
				
				
				
				
				
				
				
				
				
				
				
				
				
				
						
						
				
				
				
				
				
				
				
				
				
				<?
				if( check_access('admin') ||   $_SESSION['sh_login'] == 'demo_admin' )
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
				
				 
				 
				
				
				if( check_access('admin') ||   $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'docm'    )
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
                    <img src="<?=$m2WarningIconUrl?>"  alt="Warning">
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
					
					        
                    </p>
                    <button class="popup-button" id="continue-button">
                        Продолжить
                        <span class="arrow-hover">
                            <div></div>
                        </span>
                    </button>
                </div>
                <div class="warning-icon">
                    <img src="<?=$m2WarningIconUrl?>"  alt="Warning">
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
