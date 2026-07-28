<?php
/**
 * Админ-страница массового снятия броней.
 * URL: ctrind.php?ctr=broni_clear&act=index
 * Задача 7.
 */
require_once dirname(__DIR__, 2) . '/inc/booking_guard_helpers.php';

class ctr__broni_clear extends ctr__
{
    var $ctr = 'broni_clear';
    var $title = 'Снятие броней';
    private $limit = 500;
    /** Skip full-home sync (already done in act__clear). */
    private $skip_guard_sync = false;

    private function assert_access()
    {
        if (!booking_guard_can_clear_broni()) {
            echo '<h2>Доступ запрещён. Страница доступна только администратору.</h2>';
            return false;
        }
        return true;
    }

    private function filters_from_request()
    {
        return [
            'home_id' => (int)($_GET['home_id'] ?? $_POST['home_id'] ?? 0),
            'apartment_num' => trim((string)($_GET['apartment_num'] ?? $_POST['apartment_num'] ?? '')),
            'rooms' => trim((string)($_GET['rooms'] ?? $_POST['rooms'] ?? '')),
            'user_id' => (int)($_GET['user_id'] ?? $_POST['user_id'] ?? 0),
            'guard_mode' => (string)($_GET['guard_mode'] ?? $_POST['guard_mode'] ?? 'all'),
        ];
    }

    private function base_where(array $f, $forOptions = false)
    {
        $w = [
            'a.status2 = 4',
            'a.status_broni_id > 0',
            'b.status = 4',
        ];
        if (!$forOptions) {
            if ($f['home_id'] > 0) {
                $w[] = 'a.home_id = ' . (int)$f['home_id'];
            }
            if ($f['apartment_num'] !== '') {
                $w[] = "a.apartment_num = '" . booking_guard_escape($f['apartment_num']) . "'";
            }
            if ($f['rooms'] !== '') {
                $w[] = "TRIM(a.rooms) = '" . booking_guard_escape($f['rooms']) . "'";
            }
            if ($f['user_id'] > 0) {
                $w[] = 'b.user_id = ' . (int)$f['user_id'];
            }
            if ($f['guard_mode'] === 'manual') {
                $w[] = 'COALESCE(bgr.is_manual_mode, 0) = 1';
            } elseif ($f['guard_mode'] === 'auto') {
                $w[] = 'COALESCE(bgr.is_manual_mode, 0) = 0';
            }
        }
        return implode(' AND ', $w);
    }

    private function join_sql()
    {
        return "
            FROM apartaments a
            INNER JOIN broni b ON b.broni_id = a.status_broni_id
            INNER JOIN homes h ON h.home_id = a.home_id
            LEFT JOIN users u ON u.id = b.user_id
            LEFT JOIN agency ag ON ag.agency_id = u.agency_id
            LEFT JOIN booking_guard_room bgr
              ON bgr.home_id = a.home_id AND bgr.rooms = TRIM(a.rooms)
        ";
    }

    private function sync_for_active_homes()
    {
        global $mysql;
        $rows = $mysql->get_arr(
            "SELECT DISTINCT a.home_id
             FROM apartaments a
             WHERE a.status2 = 4 AND a.status_broni_id > 0"
        );
        $homeIds = [];
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $hid = (int)($r['home_id'] ?? 0);
                if ($hid > 0) {
                    $homeIds[] = $hid;
                }
            }
        }
        if ($homeIds) {
            $stats = booking_guard_calc_room_stats($homeIds);
            booking_guard_sync_auto($stats);
        }
    }

    private function load_filter_options()
    {
        global $mysql;
        $join = $this->join_sql();
        $where = $this->base_where([], true);

        $homes = $mysql->get_arr(
            "SELECT DISTINCT a.home_id, h.long_title AS home_title
             {$join}
             WHERE {$where}
             ORDER BY h.long_title"
        );
        $apts = $mysql->get_arr(
            "SELECT DISTINCT a.home_id, a.apartment_num, h.long_title AS home_title
             {$join}
             WHERE {$where}
             ORDER BY h.long_title, a.apartment_num+0, a.apartment_num"
        );
        $rooms = $mysql->get_arr(
            "SELECT DISTINCT TRIM(a.rooms) AS rooms
             {$join}
             WHERE {$where} AND TRIM(a.rooms) <> ''
             ORDER BY rooms"
        );
        $users = $mysql->get_arr(
            "SELECT DISTINCT b.user_id, u.login, u.name
             {$join}
             WHERE {$where} AND b.user_id > 0
             ORDER BY u.login"
        );

        return [
            'homes' => is_array($homes) ? $homes : [],
            'apartments' => is_array($apts) ? $apts : [],
            'rooms' => is_array($rooms) ? $rooms : [],
            'users' => is_array($users) ? $users : [],
        ];
    }

    private function load_rows(array $f)
    {
        global $mysql;
        $join = $this->join_sql();
        $where = $this->base_where($f, false);
        $limit = (int)$this->limit;
        $sql = "
            SELECT
              a.apartament_id,
              a.home_id,
              a.apartment_num,
              TRIM(a.rooms) AS rooms,
              a.status2,
              a.status_broni_id AS broni_id,
              b.date AS broni_date,
              b.user_id,
              b.status AS broni_status,
              u.login,
              u.name AS user_name,
              ag.caption AS agency_caption,
              h.long_title AS home_title,
              COALESCE(bgr.is_manual_mode, 0) AS is_manual_mode,
              COALESCE(bgr.source, 'auto') AS guard_source
            {$join}
            WHERE {$where}
            ORDER BY b.date DESC
            LIMIT {$limit}
        ";
        $rows = $mysql->get_arr($sql);
        return is_array($rows) ? $rows : [];
    }

    function act__index()
    {
        global $t;
        if (!$this->assert_access()) {
            return;
        }
        $t['h1'] = 'Снятие броней';

        $filters = $this->filters_from_request();
        if (!in_array($filters['guard_mode'], ['all', 'auto', 'manual'], true)) {
            $filters['guard_mode'] = 'all';
        }

        // Full sync only when filtering by mode (needs fresh cache).
        // After mass clear we already synced touched homes — skip.
        if (!$this->skip_guard_sync && $filters['guard_mode'] !== 'all') {
            @set_time_limit(120);
            $this->sync_for_active_homes();
        }

        $flash = '';
        if (!empty($_SESSION['broni_clear_flash'])) {
            $flash = (string)$_SESSION['broni_clear_flash'];
            unset($_SESSION['broni_clear_flash']);
        }

        $options = $this->load_filter_options();
        $rows = $this->load_rows($filters);
        $truncated = count($rows) >= $this->limit;

        $this->tpl([
            'filters' => $filters,
            'options' => $options,
            'rows' => $rows,
            'flash' => $flash,
            'truncated' => $truncated,
            'limit' => $this->limit,
        ], 'broni_clear', 'index');
    }

    function act__clear()
    {
        global $sa, $mysql;
        if (!$this->assert_access()) {
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->act__index();
            return;
        }

        @set_time_limit(300);

        $ids = $_POST['broni_ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $cleared = 0;
        $skipped = 0;
        $touchedHomes = [];

        if (!isset($sa) || !is_object($sa)) {
            global $connection;
            $sa = new sahmatka($_SESSION, $connection);
        }

        foreach ($ids as $broniId) {
            $row = $mysql->get_arr(
                "SELECT a.home_id, a.status_broni_id
                 FROM apartaments a
                 WHERE a.status_broni_id = {$broniId} AND a.status2 = 4",
                1
            );
            if (!$row) {
                $skipped++;
                continue;
            }
            $newId = $sa->up_broni(
                $broniId,
                2,
                'Снятие брони в связи с переходом на ручное бронирование'
            );
            if (!$newId) {
                $skipped++;
                continue;
            }
            $cleared++;
            $hid = (int)$row['home_id'];
            if ($hid > 0) {
                $touchedHomes[$hid] = $hid;
            }
        }

        if ($touchedHomes) {
            $stats = booking_guard_calc_room_stats(array_values($touchedHomes));
            booking_guard_sync_auto($stats);
        }

        $_SESSION['broni_clear_flash'] = "Снято: {$cleared}. Пропущено: {$skipped}.";

        // ctrind.php уже вывел шапку — header(Location) не работает и exit даёт белый экран.
        // Рендерим список в том же запросе.
        foreach (['home_id', 'apartment_num', 'rooms', 'user_id', 'guard_mode'] as $k) {
            if (isset($_POST[$k])) {
                $_GET[$k] = $_POST[$k];
            }
        }
        $_GET['ctr'] = 'broni_clear';
        $_GET['act'] = 'index';
        $this->skip_guard_sync = true;
        $this->act__index();
    }
}
