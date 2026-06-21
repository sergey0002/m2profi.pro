<?php
// Кастомный контроллер для 

class ctr__homes__ extends ctr__
{
    /**
     * НОВЫЙ МЕТОД: Рендерит фильтр по периоду анализа
     */
    function _render_period_filter() {
        // Получаем все текущие GET-параметры, чтобы не потерять их при переключении периода
        $queryParams = $_GET;
        $current_period = $queryParams['period'] ?? 'all';
        
        // Удаляем сам параметр 'period', чтобы он не дублировался в ссылке
        unset($queryParams['period']);
        
        // Собираем URL с сохранением всех остальных фильтров
        $base_url = "ctrind.php?" . http_build_query($queryParams);
        ?>
        <div class="noprint" style="width:100%; margin-bottom:10px; margin-top:15px;">
            <a href="<?= $base_url ?>&period=all" class="mdef <?= $current_period == 'all' ? 'mdefth' : '' ?>">Весь период</a>
            <a href="<?= $base_url ?>&period=year" class="mdef <?= $current_period == 'year' ? 'mdefth' : '' ?>">За год</a>
            <a href="<?= $base_url ?>&period=quarter" class="mdef <?= $current_period == 'quarter' ? 'mdefth' : '' ?>">За квартал</a>
            <a href="<?= $base_url ?>&period=month" class="mdef <?= $current_period == 'month' ? 'mdefth' : '' ?>">За месяц</a>
        </div>
        <?php
    }

    /**
     * Рендерит весь блок с фильтрами: Статус, Кварталы, Дома.
     */
    function _render_filters() {
        global $mysql;
        $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
        $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
        
        // Данные для мобильного меню домов
        $sql_mobile_homes = "SELECT home_id, long_title FROM homes WHERE `show` > 0 ";
        if ($sdan == 0) $sql_mobile_homes .= " AND complite = 0 ";
        elseif ($sdan == 1) $sql_mobile_homes .= " AND complite = 1 ";
        if ($kvartal_id > 0) $sql_mobile_homes .= " AND CAST(kvartal AS UNSIGNED) = " . intval($kvartal_id);
        $sql_mobile_homes .= " ORDER BY `order`";
        $mobile_homes_arr = $mysql->get_arr($sql_mobile_homes);
        ?>
        <div class="noprint">
            <!-- Фильтр Статуса (Сдан/Строится) -->
            <div style="width:100%; margin-bottom:10px;">
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=3&kvartal=<?=$kvartal_id?>" class="mdef <?=$sdan==3 ? 'mdefth' : ''?>">ВСЕ</a>
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=0&kvartal=<?=$kvartal_id?>" class="mdef <?=$sdan==0 ? 'mdefth' : ''?>">СТРОЯЩИЕСЯ</a> 
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=1&kvartal=<?=$kvartal_id?>" class="mdef <?=$sdan==1 ? 'mdefth' : ''?>">СДАННЫЕ</a>
            </div>

            <?php $this->_kvartal_menu(); // Вывод меню кварталов ?>

            <!-- Десктопное меню домов (ul) -->
            <ul class="mmenu">
                <?php $this->_object_menux_s(); // ВЫВОД МЕНЮ ДОМОВ ЧЕРЕЗ ГЛОБАЛЬНЫЙ ОБЪЕКТ $sa ?>
            </ul>

            <!-- Мобильное меню домов (select) -->
            <form id="obj_nav_form" method="GET" action="ctrind.php" class="mobilenav">
                <input type="hidden" name="ctr" value="<?=$this->ctr?>" />
                <input type="hidden" name="sdan" value="<?=$sdan?>" />
                <input type="hidden" name="kvartal" value="<?=$kvartal_id?>" />
                <div class="objects-head-nav__select">
                    <select name="home" onChange="this.form.submit();" style="width:100%; text-align: left; border-radius:0;">
                        <option value="">Выбрать дом</option>
                        <?php foreach($mobile_homes_arr as $v): ?>
                            <option value="<?=$v['home_id']?>" <?=($v['home_id']==($_GET['home']??'')) ? 'selected' : ''?>>
                                <?=htmlspecialchars($v['long_title'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <hr style="margin-top: 12px;" class="nomobile"/>
        <?php
    }

    /**
     * ВЫВОДИТ МЕНЮ ДОМОВ ДЛЯ ДЕСКТОПА, ИСПОЛЬЗУЯ ГЛОБАЛЬНЫЙ ОБЪЕКТ $sa
     */
    function _object_menux_s() {
        global $sa; // Получаем доступ к глобальному объекту
        
        $h = $sa->get_homes_arr(); // Вызываем метод для получения всех домов
        
        $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
        $kvartal_id = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;

        // Фильтруем массив домов на лету
        $filtered_h = [];
        foreach ($h as $v) {
            if ($sdan == 0 && $v['complite'] != "0") continue;
            if ($sdan == 1 && $v['complite'] != "1") continue;
            if ($kvartal_id > 0 && intval($v['kvartal']) != $kvartal_id) continue;
            $filtered_h[] = $v;
        }

        // Выводим отфильтрованный список
        foreach($filtered_h as $v) {
            $class = 'mdef';
            if ($_GET['home'] == $v['home_id']) { $class .= ' mdefth'; } 
            elseif ($v['show'] == 2) { $class .= ' mdefa'; } 
            elseif ($v['show'] == 3) { $class .= ' mdefaop'; }
            ?>
            <li style="padding:0;"><a href="ctrind.php?ctr=<?=$this->ctr?>&home=<?=$v['home_id']?>&sdan=<?=$sdan?>&kvartal=<?=$kvartal_id?>" class="<?=$class?>"><?=htmlspecialchars($v['title'])?></a></li>
            <?php
        }
    }

    /**
     * Выводит меню кварталов.
     */
    function _kvartal_menu() {
        $sdan = isset($_GET['sdan']) ? intval($_GET['sdan']) : 3;
        $selected_kvartal = isset($_GET['kvartal']) ? intval($_GET['kvartal']) : 0;
        
        $kvartals = $this->_get_kvartals();
        ?>
        <div style="width:100%; margin-bottom:10px; margin-top:15px;">
            <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=<?=$sdan?>&kvartal=0" class="mdef <?=($selected_kvartal == 0) ? 'mdefth' : ''?>">Все кварталы</a>
            <?php foreach ($kvartals as $k): 
                if ($sdan == 0 && $k['under_construction_homes'] == 0) continue;
                if ($sdan == 1 && $k['completed_homes'] == 0) continue;
            ?>
                <a href="ctrind.php?ctr=<?=$this->ctr?>&sdan=<?=$sdan?>&kvartal=<?=$k['homes_kvartal_id']?>" class="mdef <?=($selected_kvartal == $k['homes_kvartal_id']) ? 'mdefth' : ''?>"><?=htmlspecialchars($k['title'])?></a>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Получает данные по кварталам из БД.
     */
    function _get_kvartals() {
		   global $mysql;
        $sql = "
            SELECT 
                kv.homes_kvartal_id, kv.title,
                SUM(CASE WHEN h.show > 0 AND h.complite = 1 THEN 1 ELSE 0 END) as completed_homes,
                SUM(CASE WHEN h.show > 0 AND h.complite = 0 THEN 1 ELSE 0 END) as under_construction_homes
            FROM homes_kvartal kv
            LEFT JOIN homes h ON CAST(h.kvartal AS UNSIGNED) = kv.homes_kvartal_id
            WHERE kv.`show` = 1 AND kv.`del` = 0
            GROUP BY kv.homes_kvartal_id, kv.title ORDER BY kv.`order`";
        
        $result = $mysql->get_arr($sql);
        foreach ($result as &$row) {
            $row['completed_homes'] = (int)($row['completed_homes'] ?? 0);
            $row['under_construction_homes'] = (int)($row['under_construction_homes'] ?? 0);
        }
        return $result;
    }
}