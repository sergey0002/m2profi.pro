<?php
// 1. ОПРЕДЕЛЕНИЕ ТЕКУЩИХ ПАРАМЕТРОВ
$region = isset($_GET['region']) ? $_GET['region'] : 'mo'; // mo, altai, nsk
$seller = isset($_GET['seller']) ? $_GET['seller'] : null;
$base_url = '/sahmatka/user.php?action=pos';
$domain = "https://zemexx.ru";

// 2. ДАННЫЕ
require_once __DIR__ . '/../inc/objects_data.php';
require_once __DIR__ . '/../inc/proposal_manager.php';

// Вспомогательные функции
function getRegionClass($target, $current) { return ($current === $target) ? 'mdef mdefth' : 'mdef'; }
function getRegionStyle($target, $current) {
    $color = ($current === $target) ? '#FFF' : '#2F4049';
    return "display:inline-block; padding-left:12px; font-weight:bold; color:$color !important;";
}

// Логика выбора отображаемых элементов
$display_items = [];
$active_seller = $seller ? $seller : 'Земельный экспресс';
if ($region === 'mo' && isset($settlements_data['mo'])) {
    $display_items = isset($settlements_data['mo'][$active_seller]) ? $settlements_data['mo'][$active_seller] : [];
}
?>

<section class="section-objects">
    <div class="container mobc">
        
        <style>
            @media (max-width: 767px) {.show-mobile{display:auto;} .hide-mobile{display:none;}  }
            @media (min-width: 768px) {.show-mobile{display:none;} .hide-mobile{display:auto;} }
        </style>
        
        <div class="page-header" style="margin-bottom:0;">
            <div class="page-header__logo"><img src="template/default/images/logo.svg" alt=""></div>
            <div class="page-header__title" style="font-size: 41px;">Коттеджные поселки</div>
             
            <div style="width:100%; text-align:right; padding-top:30px; cursor:pointer;" class="open_xxpanel hide-mobile">
                <div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
                    <div style="display:table-cell; text-align:left; vertical-align: top;">
                        <span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br>
                        <span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку в телефон</span>
                    </div>
                    <div style="display:table-cell; text-align:left; vertical-align: baseline;">
                        <span style="font-size:14px;">И заходите в М2 PROFI как в приложение.</span>
                    </div>
                    <div style="display:table-cell; ">
                    <img src="/l.png">
                    </div>
                </div>
            </div>
        </div>

        <div id="proposal-block" class="proposal-block" style="margin-bottom: 20px;">
            <button id="create-proposal-btn" class="btn btn-primary">
                Сформировать предложение
                <span id="proposal-counter" class="badge badge-light" style="display: none;">0</span>
            </button>
        </div>
        
        <div style="width:100%;  padding-top:30px; padding-bottom:30px; cursor:pointer;" class="open_xxpanel show-mobile">
                <div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
                    <div style="display:table-cell; text-align:left; vertical-align: top; width: 100%;">
                        <span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br>
                        <span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку <br>в телефон</span><br><br>
                        <span style="font-size:14px;">И заходите в М2 PROFI<br> как в приложение.</span>
                    </div>
                    <div style="display:table-cell; text-align:right; ">
                    <img src="/l2.png" width="100">
                    </div>
                </div>
            </div>
            
        <style>
        @media screen and (min-width: 1000px) {
          .mmenu{ display:block;    padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: flex-start;      width: 100%;}
          .mmenu li { margin-right: 20px; }
          .mobilenav{display:none;}
        }
        @media screen and (max-width: 1000px) {
          .mmenu{   display:none;       }
          .mobilenav{display:block; width:100%;}
          .nomobile{display:none;}
        }
        </style>
        <script src="fancybox-3.0/dist/jquery.fancybox.min.js"></script>
        <link rel="stylesheet" type="text/css" href="fancybox-3.0/dist/jquery.fancybox.min.css">

        <script type="text/javascript">
         if( window.innerWidth >= 1000 ){
             $(document).ready(function() {
             $("a.iframe").fancybox({
                    maxWidth    : 600,
                    maxHeight   : 600,
                    width       : '1000px',
                    height      : '70%',
                    closeClick  : true,
                    'scrolling' : 'yes',
                    type : 'iframe',
                    fitToView    : true,
                    autoSize: true,
                    margin      : [10, 10, 10, 10],
                    padding:    [39, 10, 10, 10]
                });
             });
         }
        </script>

        <!-- ПЕРВЫЙ УРОВЕНЬ МЕНЮ: РЕГИОНЫ -->
        <div style="width:100%; margin-bottom:10px;">
            <a href="<?= $base_url ?>&region=mo" class="<?= getRegionClass('mo', $region) ?>" style="<?= getRegionStyle('mo', $region) ?>">МОСКОВСКАЯ ОБЛАСТЬ</a> 
            <a href="<?= $base_url ?>&region=altai" class="<?= getRegionClass('altai', $region) ?>" style="<?= getRegionStyle('altai', $region) ?>">АЛТАЙСКИЙ КРАЙ</a>
            <a href="<?= $base_url ?>&region=nsk" class="<?= getRegionClass('nsk', $region) ?>" style="<?= getRegionStyle('nsk', $region) ?>">НОВОСИБИРСКАЯ ОБЛАСТЬ</a>
        </div>
             
        <!-- ВТОРОЙ УРОВЕНЬ МЕНЮ -->
        <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
            <br>
 

            <!-- МОБИЛЬНОЕ МЕНЮ (Select) -->
            <form id="obj_nav_form" method="GET" action="/sahmatka/user.php" class="mobilenav" name="autosubmit_select">
                <input type="hidden" name="action" value="pos">
                <input type="hidden" name="region" value="<?= $region ?>">
                <div class="objects-head-nav__select">
                    <select name="seller" onchange="document.autosubmit_select.submit();" style="width:100%; text-align: left; border-radius:0; ">
                        <?php if ($region === 'mo' && isset($settlements_data['mo'])): ?>
                            <?php foreach ($settlements_data['mo'] as $seller_name => $items): ?>
                                <option value="<?= htmlspecialchars($seller_name) ?>" <?= ($seller === $seller_name) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($seller_name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option>тест</option>
                        <?php endif; ?>
                    </select>
                </div>
            </form>
        </div>          
        
        <hr style="margin-top: 12px; " class="nomobile">
    
        <!-- КОНТЕНТ (КАРТОЧКИ) -->
        <div class="objects">
            <div class="row">
                <?php if ($region === 'mo' && !empty($display_items)): ?>
                    <?php foreach ($display_items as $index => $item): ?>
                        <?php 
                        // Генерация уникального ID для поселка
                        $unique_id = 'settlement_' . md5($region . '_' . $active_seller . '_' . $item['name'] . '_' . $index);
                        ?>
                        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                            <div class="object">
                                <?php 
                                $is_added = ProposalManager::isAdded($unique_id);
                                $active_class = $is_added ? 'active' : '';
                                $icon = $is_added ? '✓' : '+';
                                ?>
                                <div class="add-to-proposal <?= $active_class ?>" data-object-type="settlement" data-object-id="<?=$unique_id?>">
                                    <i class="icon-add-to-proposal"><?= $icon ?></i>
                                    <span class="added-message" style="display:none;">Добавлено в предложение</span>
                                </div>
                                <div class="object__title"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="object__pict">
                                    <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <div class="object__info">
                                        <div class="object__status object__status_sale">от <?= number_format($item['price'], 0, '.', ' ') ?> ₽/сот</div>
                                        <div style="font-size:12px; color:#666; margin-top:5px;">
                                            <?= htmlspecialchars($item['highway']) ?> · <?= $item['mkad'] ?> км от МКАД · Готовность <?= $item['ready'] ?>%
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= $base_url ?>&region=<?= $region ?>&seller=<?= urlencode($active_seller) ?>&pos=<?= urlencode($item['name']) ?>" class="object__btn btn btn_arrow">Подробнее<i></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p style="padding: 20px;"><?= ($region !== 'mo') ? 'тест' : 'Поселки не найдены'; ?></p></div>
                <?php endif; ?>
            </div>
            
            <?php if ($region === 'mo'): ?>
                <a href="/sahmatka/yandex_feedx.php?action=pos&region=<?= $region ?>">XML Фид в формате Yandex</a>
            <?php endif; ?>
        </div>
    </div>
</section>