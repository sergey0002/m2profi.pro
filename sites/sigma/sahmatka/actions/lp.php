<?php
// 1. ОПРЕДЕЛЕНИЕ ПАРАМЕТРОВ ИЗ URL
$region = isset($_GET['region']) ? $_GET['region'] : 'mo'; // mo, altai, nsk
$current_pos = isset($_GET['pos']) ? $_GET['pos'] : null;

$base_url = '/sahmatka/user.php?action=lp'; 
$domain = "https://zemexx.ru";

// 2. ДАННЫЕ
require_once __DIR__ . '/../inc/objects_data.php';
require_once __DIR__ . '/../inc/ProposalManager.php';

// Автоматический выбор поселка, если он не задан
if (!$current_pos || !isset($plots_data[$region][$current_pos])) {
    $keys = array_keys($plots_data[$region]);
    $current_pos = $keys[0];
}

$display_plots = $plots_data[$region][$current_pos];

// Вспомогательные функции
function getRegionClass($target, $current) { return ($current === $target) ? 'mdef mdefth' : 'mdef'; }
function getRegionStyle($target, $current) {
    $color = ($current === $target) ? '#FFF' : '#2F4049';
    return "display:inline-block; padding-left:12px; font-weight:bold; color:$color !important;";
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
            <div class="page-header__title" style="font-size: 41px;">Земельные участки</div>
             
            <div style="width:100%; text-align:right; padding-top:30px; cursor:pointer;" class="open_xxpanel hide-mobile">
                <div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
                    <div style="display:table-cell; text-align:left; vertical-align: top;">
                        <span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br>
                        <span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку в телефон</span>
                    </div>
                    <div style="display:table-cell; text-align:left; vertical-align: baseline;">
                        <span style="font-size:14px;">И заходите в М2 PROFI как в приложение.</span>
                    </div>
                    <div style="display:table-cell; "><img src="/l.png"></div>
                </div>
            </div>
        </div>
        
        <div id="proposal-block" class="proposal-block" style="margin-bottom: 20px;">
            <button id="create-proposal-btn" class="btn btn-primary">
                Сформировать предложение
                <span id="proposal-counter" class="badge badge-light" style="display: none;">0</span>
            </button>
        </div>
        
        <div style="width:100%; padding-top:30px; padding-bottom:30px; cursor:pointer;" class="open_xxpanel show-mobile">
            <div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
                <div style="display:table-cell; text-align:left; vertical-align: top; width: 100%;">
                    <span style="text-transform: uppercase; color: #FFF; font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br>
                    <span style="color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку <br>в телефон</span><br><br>
                    <span style="font-size:14px;">И заходите в М2 PROFI<br> как в приложение.</span>
                </div>
                <div style="display:table-cell; text-align:right; "><img src="/l2.png" width="100"></div>
            </div>
        </div>

        <style>
            @media screen and (min-width: 1000px) {
              .mmenu{ display:block; padding-right:0; margin-top:15px; display: flex; flex-direction: row; justify-content: flex-start; width: 100%;}
              .mmenu li { margin-right: 20px; }
              .mobilenav{display:none;}
            }
            @media screen and (max-width: 1000px) {
              .mmenu{ display:none; }
              .mobilenav{display:block; width:100%;}
              .nomobile{display:none;}
            }
        </style>

        <!-- УРОВЕНЬ 1: РЕГИОНЫ -->
        <div style="width:100%; margin-bottom:10px;">
            <a href="<?= $base_url ?>&region=mo" class="<?= getRegionClass('mo', $region) ?>" style="<?= getRegionStyle('mo', $region) ?>">МОСКОВСКАЯ ОБЛАСТЬ</a> 
            <a href="<?= $base_url ?>&region=altai" class="<?= getRegionClass('altai', $region) ?>" style="<?= getRegionStyle('altai', $region) ?>">АЛТАЙСКИЙ КРАЙ</a>
            <a href="<?= $base_url ?>&region=nsk" class="<?= getRegionClass('nsk', $region) ?>" style="<?= getRegionStyle('nsk', $region) ?>">НОВОСИБИРСКАЯ ОБЛАСТЬ</a>
        </div>
             
        <!-- УРОВЕНЬ 2: ПОСЕЛКИ -->
        <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
            <br>
            <ul class="mmenu">
                <?php foreach ($plots_data[$region] as $pos_name => $plots): ?>
                    <li style="padding:0;">
                        <a href="<?= $base_url ?>&region=<?= $region ?>&pos=<?= urlencode($pos_name) ?>" 
                           class="mdef <?= ($current_pos === $pos_name) ? 'mdefth' : '' ?>">
                           <?= htmlspecialchars($pos_name) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- МОБИЛЬНОЕ МЕНЮ -->
            <form id="obj_nav_form" method="GET" action="/sahmatka/user.php" class="mobilenav" name="autosubmit_select">
                <input type="hidden" name="action" value="plots">
                <input type="hidden" name="region" value="<?= $region ?>">
                <div class="objects-head-nav__select">
                    <select name="pos" onchange="document.autosubmit_select.submit();" style="width:100%; text-align: left; border-radius:0; ">
                        <?php foreach ($plots_data[$region] as $pos_name => $plots): ?>
                            <option value="<?= htmlspecialchars($pos_name) ?>" <?= ($current_pos === $pos_name) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pos_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>          
        
        <hr style="margin-top: 12px; " class="nomobile">
    
        <!-- СЕТКА УЧАСТКОВ -->
        <div class="objects">
            <div class="row">
                <?php if (!empty($display_plots)): ?>
                    <?php foreach ($display_plots as $index => $plot): ?>
                        <?php 
                        // Генерация уникального ID для участка
                        $unique_id = 'land_' . md5($region . '_' . $current_pos . '_' . $plot['id'] . '_' . $index);
                        ?>
                        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                            <div class="object">
                                <?php 
                                $is_added = ProposalManager::isAdded($unique_id);
                                $active_class = $is_added ? 'active' : '';
                                $icon = $is_added ? '✓' : '+';
                                ?>
                                <div class="add-to-proposal <?= $active_class ?>" data-object-type="land" data-object-id="<?=$unique_id?>">
                                    <i class="icon-add-to-proposal"><?= $icon ?></i>
                                    <span class="added-message" style="display:none;">Добавлено в предложение</span>
                                </div>
                                <div class="object__title"><?= htmlspecialchars($plot['id']) ?></div>
                                <div class="object__pict">
                                    <img src="<?= htmlspecialchars($plot['img']) ?>" alt="<?= htmlspecialchars($plot['id']) ?>">
                                    <div class="object__info">
                                        <div class="object__status object__status_sale">
                                            <?= number_format($plot['price'], 0, '.', ' ') ?> ₽
                                        </div>
                                        <div style="font-size:12px; color:#666; margin-top:5px;">
                                            Поселок: <?= htmlspecialchars($current_pos) ?><br>
                                            Площадь: <strong><?= $plot['area'] ?> сот.</strong>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="object__btn btn btn_arrow">Забронировать<i></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p style="padding: 40px; text-align:center;">Участки не найдены</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>