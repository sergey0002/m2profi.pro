<?php

/**
 * ======================================================================
 * НОВЫЙ БАЗОВЫЙ КЛАСС ДЛЯ НАВИГАЦИИ
 * ======================================================================
 * Гибкий, с поддержкой двух режимов: "только актуальные" и "включая скрытые".
 */
class ctr__homes__ extends ctr__
{
    /**
     * @var bool true — показывать только архив (`show` = 0)
     */
    protected $show_arch = false;

    /**
     * @var bool true — включать в выборку скрытые/архивные объекты (`show` != 1)
     */
    protected $include_hidden = false;

    /**
     * Формирует и выводит заголовок на основе активных фильтров.
     */
    function _render_selection_title() {
        global $mysql;
        $title = '';
        $home_id = isset($_GET['home']) ? intval($_GET['home']) : 0;
        $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
        if ($home_id > 0) {
            $home = $mysql->get_arr("SELECT title FROM homes WHERE home_id = {$home_id} LIMIT 1");
            if (isset($home[0]['title'])) { $title = 'Дом: ' . htmlspecialchars($home[0]['title']); }
        } elseif ($kvartal_id > 0) {
            $kvartal = $mysql->get_arr("SELECT title FROM homes_kvartal WHERE homes_kvartal_id = {$kvartal_id} LIMIT 1");
            if (isset($kvartal[0]['title'])) { $title = ' ' . htmlspecialchars($kvartal[0]['title']); }
        }
        if (!empty($title)) { echo '<div class="selection-title" style="font-size: 24px; font-weight: bold; margin-top: 25px; margin-bottom: 15px; text-align: center;">' . $title . '</div>'; }
    }

    /**
     * Возвращает SQL-условие для фильтрации по `show` в зависимости от свойств класса.
     */
    function _get_show_condition($alias = 'homes') {
        if ($this->include_hidden) {
            return "1"; // Показываем все
        }
        if ($this->show_arch) {
            return "{$alias}.`show` = 0"; // Только архивные
        }
        return "{$alias}.`show` > 0"; // Только активные (поведение по умолчанию)
    }

    /**
     * Рендерит весь блок с фильтрами: Статус, Кварталы, Дома.
     */
    function _render_filters() {
        global $mysql;
        $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
        $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

        $show_condition = $this->_get_show_condition();

        $sql_mobile_homes = "SELECT home_id, long_title, `show` FROM homes WHERE {$show_condition}";
        if ($sdan == 0) $sql_mobile_homes .= " AND complite = 0 ";
        elseif ($sdan == 1) $sql_mobile_homes .= " AND complite = 1 ";
        if ($kvartal_id > 0) $sql_mobile_homes .= " AND CAST(kvartal AS UNSIGNED) = " . intval($kvartal_id);
        elseif ($kvartal_id == -1) $sql_mobile_homes .= " AND (kvartal IS NULL OR kvartal = '0' OR kvartal = '')";
        $sql_mobile_homes .= " ORDER BY `title` ASC";
        $mobile_homes_arr = $mysql->get_arr($sql_mobile_homes);
        ?><style>
		
		            /* Стили для меню и отчетов */
            .mdef { padding: 5px 13px; font-weight: bold; font-size: 18px; }
            .objmenua .mdef { color: #000; }
            .mdefa { color: #FFA500; } /* Только админам */
            .mdefaop { color: #999999; } /* Админам и отделу продаж */
            .mdefth { color: #FFF; background-color: #00CDAD; }
            .mdef:hover { color: #FFF; background-color: #00CDAD; }

            .table table tr td { padding: 7px; }

            @media screen and (min-width: 1000px) {
                .mmenu { display: flex; flex-direction: row; flex-wrap: wrap; justify-content: flex-start; width: 100%; padding-right: 0; margin-top: 15px; }
                .mobilenav { display: none; }
            }
            @media screen and (max-width: 1000px) {
                .mmenu { display: none; }
                .mobilenav { display: block; width: 100%; }
                .nomobile { display: none; }
            }

            .table-title { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
            .financial-summary { padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
            .plan-value { text-align: center; font-size: 14px; color: #555; margin-bottom: 5px; font-weight: bold; }
            .plan-value span { font-size: 18px; color: #000; }
            .revenue-bar { display: flex; width: 100%; height: 60px; border-radius: 5px; overflow: hidden; border: 1px solid #aaa; font-size: 14px; font-weight: bold; color: white; }
            .revenue-bar-sold { width: 100%; background-color: rgba(40, 167, 69, 0.7); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 5px; box-sizing: border-box; }
            .bar-label { font-size: 12px; opacity: 0.9; }
            .bar-value { font-size: 16px; }
            .result-value { text-align: center; font-size: 18px; margin-top: 10px; font-weight: bold; }
            .stat-table { padding: 10px 0 0; }
			.mmenu li {
				display: inline;
				padding: 10px;
				margin: 5px;
			}
			    .mmenu {
        display: block;
        padding-right: 0;
        margin-top: 15px;
        display: flex
;
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
        max-width: 100%;
        display: inline;
    }
	.mdef {
    padding: 5px 3px;
    font-weight: bold;
    font-size: 18px;
    line-height: 2em;
}</style>
        <div class="noprint">
            <div style="width:100%; margin-bottom:10px;">
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=3&kvartal=<?=$kvartal_id?>" class="mdef <?=$sdan==3 ? 'mdefth' : ''?>">ВСЕ</a>
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=0&kvartal=<?=$kvartal_id?>" class="mdef <?=$sdan==0 ? 'mdefth' : ''?>">СТРОЯЩИЕСЯ</a> 
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=1&kvartal=<?=$kvartal_id?>" class="mdef <?=$sdan==1 ? 'mdefth' : ''?>">СДАННЫЕ</a>
            </div>

            <?php $this->_kvartal_menu(); ?>

            <ul class="mmenu">
                <?php $this->_object_menux_s(); ?>
            </ul>

            <form id="obj_nav_form" method="GET" action="ctrind.php" class="mobilenav">
                <input type="hidden" name="ctr" value="<?=$this->ctr?>" />
                <input type="hidden" name="sdan" value="<?=$sdan?>" />
                <input type="hidden" name="kvartal" value="<?=$kvartal_id?>" />
                <div class="objects-head-nav__select">
                    <select name="home" onChange="this.form.submit();" style="width:100%; text-align: left; border-radius:0;">
                        <option value="">Выбрать дом</option>
                        <?php foreach($mobile_homes_arr as $v):
                            $title = htmlspecialchars($v['long_title']);
                            if ($this->include_hidden && $v['show'] == 0) {
                                $title .= ' (Архив)';
                            }
                        ?>
                            <option value="<?=$v['home_id']?>" <?=($v['home_id']==($_GET['home']??'')) ? 'selected' : ''?>><?=$title?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <?php 
        $this->_render_selection_title(); 
    }

    /**
     * Выводит меню домов для десктопа.
     */
    function _object_menux_s() {
        global $mysql;
        $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
        $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
        $show_condition = $this->_get_show_condition();

        $sql = "SELECT home_id, title, `show` FROM homes WHERE {$show_condition}";
        if ($sdan == 0) $sql .= " AND complite = 0";
        elseif ($sdan == 1) $sql .= " AND complite = 1";
        if ($kvartal_id > 0) $sql .= " AND CAST(kvartal AS UNSIGNED) = " . intval($kvartal_id);
        elseif ($kvartal_id == -1) $sql .= " AND (kvartal IS NULL OR kvartal = '0' OR kvartal = '')";
        $sql .= " ORDER BY `title` ASC";
        $homes = $mysql->get_arr($sql);

        foreach ($homes as $v) {
            $class = 'mdef';
            if ((isset($_GET['home']) && $_GET['home'] == $v['home_id'])) {
                $class .= ' mdefth';
            }
            if ($this->include_hidden && $v['show'] == 0) {
                 $class .= ' mdefaop';
            }
            ?>
            <li style="padding:0;">
                <a href="ctrind.php?ctr=<?=$this->ctr?>&home=<?=$v['home_id']?>&sdan=<?=$sdan?>&kvartal=<?=$kvartal_id?>" class="<?=$class?>">
                    <?=htmlspecialchars($v['title'])?>
                </a>
            </li>
            <?php
        }
    }

    /**
     * Выводит меню кварталов.
     */
    function _kvartal_menu() {
        global $mysql;
        $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
        $selected_kvartal = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
        $kvartals = $this->_get_kvartals();
        
        $sql_no_kvartal_condition = $this->_get_show_condition();
        $sql_no_kvartal = "SELECT COUNT(home_id) as cnt FROM homes WHERE ({$sql_no_kvartal_condition}) AND (kvartal IS NULL OR kvartal = '0' OR kvartal = '')";
        $result_arr = $mysql->get_arr($sql_no_kvartal);
        $no_kvartal_count = isset($result_arr[0]['cnt']) ? $result_arr[0]['cnt'] : 0;
        ?>
        <div style="width:100%; margin-bottom:10px; margin-top:15px;">
            <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=<?=$sdan?>&kvartal=0" class="mdef <?=($selected_kvartal == 0) ? 'mdefth' : ''?>">Все кварталы</a>
            <?php foreach ($kvartals as $k): 
                if (!$this->include_hidden) {
                    if ($sdan == 0 && ($k['under_construction_homes'] ?? 0) == 0) continue;
                    if ($sdan == 1 && ($k['completed_homes'] ?? 0) == 0) continue;
                }
                $class = 'mdef';
                if ($selected_kvartal == $k['homes_kvartal_id']) {
                    $class .= ' mdefth';
                }
                if ($this->include_hidden && $k['show'] == 0) {
                     $class .= ' mdefaop';
                }
            ?>
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=<?=$sdan?>&kvartal=<?=$k['homes_kvartal_id']?>" class="<?=$class?>"><?=htmlspecialchars($k['title'])?></a>
            <?php endforeach; ?>
            <?php if ($no_kvartal_count > 0): ?>
                 <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=<?=$sdan?>&kvartal=-1" class="mdef <?=($selected_kvartal == -1) ? 'mdefth' : ''?>">Другое</a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Получает данные по кварталам из БД с учётом свойств класса.
     */
    function _get_kvartals() {
        global $mysql;
        $show_condition_homes = $this->_get_show_condition('h');
        
        $sql = "
            SELECT 
                kv.homes_kvartal_id, 
                kv.title,
                kv.show,
                SUM(CASE WHEN h.home_id IS NOT NULL AND {$show_condition_homes} AND h.complite = 1 THEN 1 ELSE 0 END) AS completed_homes,
                SUM(CASE WHEN h.home_id IS NOT NULL AND {$show_condition_homes} AND h.complite = 0 THEN 1 ELSE 0 END) AS under_construction_homes
            FROM homes_kvartal kv
            LEFT JOIN homes h ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
            WHERE kv.`del` = 0 ";
        
        if (!$this->include_hidden) {
            $sql .= " AND kv.show > 0 ";
        }

        $sql .= " GROUP BY kv.homes_kvartal_id, kv.title, kv.show ";

        if (!$this->include_hidden) {
             $sql .= " HAVING completed_homes > 0 OR under_construction_homes > 0 ";
        } else {
             $sql .= " HAVING COUNT(h.home_id) > 0 ";
        }
        
        $sql .= " ORDER BY kv.`order`";

        $result = $mysql->get_arr($sql);
        foreach ($result as &$row) {
            $row['completed_homes'] = (int)($row['completed_homes'] ?? 0);
            $row['under_construction_homes'] = (int)($row['under_construction_homes'] ?? 0);
        }
        return $result;
    }
	
    /**
     * Рендерит фильтр по количеству комнат.
     */
    function _render_rooms_filter() {
        $queryParams = $_GET; unset($queryParams['rooms']);
        $current_rooms = $_GET['rooms'] ?? 0;
        $room_options = [0 => 'Все', 1 => '1', 2 => '2', 3 => '3', 4 => '4+'];
        ?>
        <div class="noprint" style="width:100%; margin-bottom:10px; margin-top:15px;">
            <?php foreach($room_options as $room_num => $room_label): 
                $urlParams = $queryParams; $urlParams['rooms'] = $room_num;
                $url = "ctrind.php?" . http_build_query($urlParams);
            ?>
                <a href="<?= htmlspecialchars($url) ?>" class="mdef <?= $current_rooms == $room_num ? 'mdefth' : '' ?>"><?= $room_label ?></a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
