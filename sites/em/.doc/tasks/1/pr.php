<?php
// 1. ОПРЕДЕЛЕНИЕ ПАРАМЕТРОВ
$region = isset($_GET['region']) ? $_GET['region'] : 'mo'; 
$builder = isset($_GET['builder']) ? $_GET['builder'] : null;

$base_url = '/sahmatka/user.php?action=pr'; 

// 2. ДАННЫЕ
require_once __DIR__ . '/../inc/objects_data.php';
require_once __DIR__ . '/../inc/ProposalManager.php';

// Автоматический выбор застройщика
if (!$builder || !isset($projects_data[$region][$builder])) {
    $keys = array_keys($projects_data[$region]);
    $builder = $keys[0];
}

$display_projects = $projects_data[$region][$builder];

// Стили для кнопок меню
function getRegionClass($target, $current) { return ($current === $target) ? 'mdef mdefth' : 'mdef'; }
function getRegionStyle($target, $current) {
    $color = ($current === $target) ? '#FFF' : '#2F4049';
    return "display:inline-block; padding-left:12px; font-weight:bold; color:$color !important;";
}
?>

<section class="section-objects">
    <div class="container mobc">
        
        <div class="page-header" style="margin-bottom:0;">
            <div class="page-header__logo"><img src="template/default/images/logo.svg" alt=""></div>
            <div class="page-header__title" style="font-size: 41px;">Проекты домов</div>
             
            <div style="width:100%; text-align:right; padding-top:30px; cursor:pointer;" class="open_xxpanel hide-mobile">
                <div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
                    <div style="display:table-cell; text-align:left; vertical-align: top;">
                        <span style="text-transform: uppercase; color: #FFF; font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br>
                        <span style="color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку в телефон </span>
                    </div>
                    <div style="display:table-cell; text-align:left; vertical-align: baseline;">
                        <span style="font-size:14px;">Выберите идеальный дом для строительства.</span>
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

        <style>
            @media screen and (min-width: 1000px) {
              .mmenu{ display:block; padding-right:0; margin-top:15px; display: flex; flex-direction: row; justify-content: flex-start; width: 100%;}
              .mmenu li { margin-right: 25px; }
              .mobilenav{display:none;}
            }
            @media screen and (max-width: 1000px) {
              .mmenu{ display:none; }
              .mobilenav{display:block; width:100%;}
              .nomobile{display:none;}
            }
            .project-stat { font-size: 12px; color: #666; margin-bottom: 3px; }
            .project-stat b { color: #333; }
        </style>

        <!-- УРОВЕНЬ 1: РЕГИОНЫ -->
        <div style="width:100%; margin-bottom:10px; margin-top:20px;">
            <a href="<?= $base_url ?>&region=mo" class="<?= getRegionClass('mo', $region) ?>" style="<?= getRegionStyle('mo', $region) ?>">МОСКОВСКАЯ ОБЛАСТЬ</a> 
            <a href="<?= $base_url ?>&region=altai" class="<?= getRegionClass('altai', $region) ?>" style="<?= getRegionStyle('altai', $region) ?>">АЛТАЙСКИЙ КРАЙ</a>
            <a href="<?= $base_url ?>&region=nsk" class="<?= getRegionClass('nsk', $region) ?>" style="<?= getRegionStyle('nsk', $region) ?>">НОВОСИБИРСКАЯ ОБЛАСТЬ</a>
        </div>
             
        <!-- УРОВЕНЬ 2: ЗАСТРОЙЩИКИ -->
        <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
            <br>
            <ul class="mmenu">
                <?php foreach ($projects_data[$region] as $b_name => $projs): ?>
                    <li style="padding:0;">
                        <a href="<?= $base_url ?>&region=<?= $region ?>&builder=<?= urlencode($b_name) ?>" 
                           class="mdef <?= ($builder === $b_name) ? 'mdefth' : '' ?>">
                           <?= htmlspecialchars($b_name) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- МОБИЛЬНОЕ МЕНЮ -->
            <form id="obj_nav_form" method="GET" action="/sahmatka/user.php" class="mobilenav" name="autosubmit_select">
                <input type="hidden" name="action" value="projects">
                <input type="hidden" name="region" value="<?= $region ?>">
                <div class="objects-head-nav__select">
                    <select name="builder" onchange="document.autosubmit_select.submit();" style="width:100%; text-align: left; border-radius:0; ">
                        <?php foreach ($projects_data[$region] as $b_name => $projs): ?>
                            <option value="<?= htmlspecialchars($b_name) ?>" <?= ($builder === $b_name) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>          
        
        <hr style="margin-top: 12px; " class="nomobile">
    
        <!-- СЕТКА ПРОЕКТОВ -->
        <div class="objects">
            <div class="row">
                <?php if (!empty($display_projects)): ?>
                    <?php foreach ($display_projects as $index => $item): ?>
                        <?php 
                        // Генерация уникального ID для проекта
                        $unique_id = 'project_' . md5($region . '_' . $builder . '_' . $item['name'] . '_' . $index);
                        ?>
                        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                            <div class="object">
                                <div class="add-to-proposal" data-object-type="project" data-object-id="<?=$unique_id?>">
    <i class="icon-add-to-proposal">+</i>
    <span class="added-message" style="display:none;">Добавлено в предложение</span>
</div>
<div class="object__title"><?= htmlspecialchars($item['art']) ?></div>
                                <div class="object__pict">
                                    <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="height:180px; object-fit:cover;">
                                    <div class="object__info">
                                        <div class="object__status object__status_sale">
                                            от <?= number_format($item['price'], 0, '.', ' ') ?> ₽
                                        </div>
                                        <div style="margin-top:8px;">
                                            <div class="project-stat">Площадь: <b><?= $item['area'] ?> м²</b></div>
                                            <div class="project-stat">Этажей: <b><?= $item['floors'] ?></b></div>
                                            <div class="project-stat">Спален: <b><?= $item['bedrooms'] ?></b> | С/У: <b><?= $item['bath'] ?></b></div>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="object__btn btn btn_arrow">Смотреть проект<i></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p style="padding: 40px; text-align:center;">Проекты не найдены</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>