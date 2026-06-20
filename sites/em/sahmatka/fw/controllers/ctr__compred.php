<?
require_once dirname(__DIR__, 2) . '/inc/compred_helpers.php';

class ctr__compred extends ctr__
{
    private function assert_auth($html = false)
    {
        if (empty($_SESSION['sh_id'])) {
            if ($html) {
                echo '<h2>Требуется авторизация</h2>';
                exit;
            }
            $this->json_response(['ok' => 0, 'error' => 'Требуется авторизация'], 403);
            exit;
        }
    }

    private function json_response($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function redirect_with_error($return_url, $msg)
    {
        $sep = (strpos($return_url, '?') !== false) ? '&' : '?';
        header('Location: ' . $return_url . $sep . 'compred_err=' . urlencode($msg));
        exit;
    }

    private function load_compred($compred_id)
    {
        global $mysql;
        $compred_id = (int)$compred_id;
        if ($compred_id <= 0) {
            return null;
        }
        return $mysql->get_arr(
            'SELECT * FROM compred WHERE compred_id = ' . $compred_id . ' AND del = 0',
            1
        );
    }

    private function load_compred_assert_owner($compred_id)
    {
        $this->assert_auth(true);
        $compred = $this->load_compred($compred_id);
        if (!$compred) {
            echo '<h2>Предложение не найдено</h2>';
            exit;
        }
        if ((int)$compred['user_id'] !== (int)$_SESSION['sh_id']) {
            echo '<h2>Доступ запрещён</h2>';
            exit;
        }
        return $compred;
    }

    private function resolve_apartment($apartament_id)
    {
        global $mysql;
        $apartament_id = (int)$apartament_id;
        if ($apartament_id <= 0) {
            return null;
        }
        $row = $mysql->get_arr(
            'SELECT
                a.apartament_id,
                a.apartment_num,
                a.section_id,
                a.floor,
                a.rooms,
                a.area,
                a.price,
                a.image_pb,
                a.home_id,
                h.title AS home_title,
                h.long_title,
                h.adress AS home_adress,
                h.description AS home_description,
                h.wallmaterial,
                h.floor AS home_floors,
                h.complite,
                h.complite_text,
                h.delivery_date,
                h.renovation,
                h.built_year,
                h.ready_quarter,
                hs.caption AS section_caption,
                hk.title AS kvartal_title,
                hk.description AS kvartal_description,
                hk.homes_kvartal_id AS kvartal_id
            FROM apartaments a
            LEFT JOIN homes h ON h.home_id = a.home_id
            LEFT JOIN homes_sections hs ON hs.homes_sections_id = a.section_id
            LEFT JOIN homes_kvartal hk ON hk.homes_kvartal_id = IF(h.kvartal > 0, h.kvartal, h.homes_kvartal_id)
            WHERE a.apartament_id = ' . $apartament_id,
            1
        );
        return $row ?: null;
    }

    private function load_objects_resolved($compred_id)
    {
        global $mysql;
        $compred_id = (int)$compred_id;
        $rows = $mysql->get_arr(
            'SELECT
                co.compred_obj_id,
                co.compred_id,
                co.obj_type,
                co.obj_id,
                co.comment,
                co.sort_order,
                co.created_at,
                a.apartament_id,
                a.apartment_num,
                a.section_id,
                a.floor,
                a.rooms,
                a.area,
                a.price,
                a.image_pb,
                a.home_id,
                h.title AS home_title,
                h.long_title,
                h.adress AS home_adress,
                h.description AS home_description,
                h.wallmaterial,
                h.floor AS home_floors,
                h.complite,
                h.complite_text,
                h.delivery_date,
                h.renovation,
                h.built_year,
                h.ready_quarter,
                hs.caption AS section_caption,
                hk.title AS kvartal_title,
                hk.description AS kvartal_description,
                hk.homes_kvartal_id AS kvartal_id
            FROM compred_obj co
            INNER JOIN apartaments a ON co.obj_type = "apartment" AND a.apartament_id = co.obj_id
            LEFT JOIN homes h ON h.home_id = a.home_id
            LEFT JOIN homes_sections hs ON hs.homes_sections_id = a.section_id
            LEFT JOIN homes_kvartal hk ON hk.homes_kvartal_id = IF(h.kvartal > 0, h.kvartal, h.homes_kvartal_id)
            WHERE co.compred_id = ' . $compred_id . '
            ORDER BY co.sort_order ASC, co.compred_obj_id ASC'
        );
        if (!$rows) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $out[] = compred_row_to_object($row);
        }
        return $out;
    }

    private function compred_public_url($token)
    {
        return compred_build_public_url((string)$token);
    }

    private function ensure_share_token(array $compred)
    {
        global $mysql;
        $compred_id = (int)($compred['compred_id'] ?? 0);
        $token = trim((string)($compred['share_token'] ?? ''));
        if ($token === '' && $compred_id > 0) {
            $token = bin2hex(random_bytes(16));
            $mysql->update_for_key('compred', 'compred_id', $compred_id, [
                'share_token' => $token,
            ]);
            $compred['share_token'] = $token;
        }
        return $compred;
    }

    function act__index()
    {
        global $t, $mysql;
        $this->assert_auth(true);
        $t['h1'] = 'Мои предложения';

        $list = $mysql->get_arr(
            'SELECT c.*,
                (SELECT COUNT(*) FROM compred_obj co WHERE co.compred_id = c.compred_id) AS obj_count
             FROM compred c
             WHERE c.user_id = ' . (int)$_SESSION['sh_id'] . ' AND c.del = 0
             ORDER BY c.updated_at DESC
             LIMIT 200'
        );

        $this->tpl([
            'list' => $list ?: [],
        ], 'compred', 'index');
    }

    function act__edit()
    {
        global $t;
        compred_ensure_intro_column();
        $t['h1'] = 'Мои предложения';
        $compred = $this->load_compred_assert_owner((int)($_GET['id'] ?? 0));
        $compred = $this->ensure_share_token($compred);
        $objects = $this->load_objects_resolved($compred['compred_id']);
        $public_url = $this->compred_public_url($compred['share_token']);

        $this->tpl([
            'compred'     => $compred,
            'objects'     => $objects,
            'groups'      => compred_group_objects($objects),
            'mode'        => 'edit',
            'public_url'  => $public_url,
            'share_links' => compred_share_links($public_url, (string)($compred['caption'] ?? '')),
        ], 'compred', 'edit');
    }

    function act__view()
    {
        global $t, $mysql;
        $this->assert_auth(true);
        $t['h1'] = 'Мои предложения';

        $compred_id = (int)($_GET['id'] ?? 0);
        $compred = $this->load_compred($compred_id);
        if (!$compred) {
            echo '<h2>Предложение не найдено</h2>';
            return;
        }

        $objects = $this->load_objects_resolved($compred_id);
        $mode = 'view';

        $this->tpl([
            'compred' => $compred,
            'objects' => $objects,
            'groups'  => compred_group_objects($objects),
            'mode'    => $mode,
        ], 'compred', 'view');
    }

    function act__public()
    {
        global $mysql;
        $token = preg_replace('/[^a-f0-9]/', '', strtolower($_GET['token'] ?? ''));
        if (strlen($token) !== 32) {
            echo '<p>Ссылка недействительна</p>';
            return;
        }

        $compred = $mysql->get_arr(
            "SELECT * FROM compred WHERE share_token = '" . mysqli_real_escape_string($mysql->c, $token) . "' AND del = 0",
            1
        );
        if (!$compred) {
            echo '<p>Предложение не найдено</p>';
            return;
        }

        $objects = $this->load_objects_resolved($compred['compred_id']);
        $mode = 'public';

        $this->tpl([
            'compred' => $compred,
            'objects' => $objects,
            'groups'  => compred_group_objects($objects),
            'mode'    => $mode,
        ], 'compred', 'public');
    }

    function act__add_item()
    {
        global $mysql;
        $this->assert_auth();

        $compred_id = (int)($_POST['compred_id'] ?? 0);
        $caption_new = trim((string)($_POST['caption_new'] ?? ''));
        $obj_type = (string)($_POST['obj_type'] ?? 'apartment');
        $obj_id = (int)($_POST['obj_id'] ?? 0);
        $comment = trim((string)($_POST['comment'] ?? ''));
        $return_url = trim((string)($_POST['return_url'] ?? ''));

        if ($obj_type !== 'apartment' || $obj_id <= 0) {
            if ($return_url) {
                $this->redirect_with_error($return_url, 'Некорректный объект');
            }
            $this->json_response(['ok' => 0, 'error' => 'Некорректный объект']);
            return;
        }

        if (!$this->resolve_apartment($obj_id)) {
            if ($return_url) {
                $this->redirect_with_error($return_url, 'Квартира не найдена');
            }
            $this->json_response(['ok' => 0, 'error' => 'Квартира не найдена']);
            return;
        }

        if ($compred_id > 0) {
            $compred = $this->load_compred($compred_id);
            if (!$compred || (int)$compred['user_id'] !== (int)$_SESSION['sh_id']) {
                if ($return_url) {
                    $this->redirect_with_error($return_url, 'Предложение не найдено');
                }
                $this->json_response(['ok' => 0, 'error' => 'Предложение не найдено']);
                return;
            }
        } else {
            if ($caption_new === '') {
                if ($return_url) {
                    $this->redirect_with_error($return_url, 'Укажите название предложения');
                }
                $this->json_response(['ok' => 0, 'error' => 'Укажите название предложения']);
                return;
            }
            if (mb_strlen($caption_new) > 255) {
                $caption_new = mb_substr($caption_new, 0, 255);
            }
            $mysql->insert('compred', [
                'caption'     => $caption_new,
                'user_id'     => (int)$_SESSION['sh_id'],
                'share_token' => bin2hex(random_bytes(16)),
            ]);
            $compred_id = (int)$mysql->c->insert_id;
            add_log('Создано коммерческое предложение #' . $compred_id);
        }

        $existing = $mysql->get_arr(
            'SELECT compred_obj_id FROM compred_obj
             WHERE compred_id = ' . $compred_id . '
               AND obj_type = "apartment" AND obj_id = ' . $obj_id,
            1
        );

        if ($existing) {
            $mysql->update_for_key('compred_obj', 'compred_obj_id', (int)$existing['compred_obj_id'], [
                'comment' => $comment,
            ]);
        } else {
            $mysql->insert('compred_obj', [
                'compred_id' => $compred_id,
                'obj_type'   => 'apartment',
                'obj_id'     => $obj_id,
                'comment'    => $comment,
            ]);
        }

        $mysql->update_for_key('compred', 'compred_id', $compred_id, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        add_log('Добавлена квартира в предложение #' . $compred_id);

        if ($return_url !== '') {
            $sep = (strpos($return_url, '?') !== false) ? '&' : '?';
            header('Location: ' . $return_url . $sep . 'compred_ok=1&compred_id=' . $compred_id);
            exit;
        }

        $this->json_response([
            'ok'        => 1,
            'compred_id'=> $compred_id,
            'edit_url'  => '/sahmatka/ctrind.php?ctr=compred&act=edit&id=' . $compred_id,
        ]);
    }

    function act__save_details()
    {
        global $mysql;
        $this->assert_auth();
        compred_ensure_intro_column();
        $compred_id = (int)($_POST['compred_id'] ?? 0);
        $caption = trim((string)($_POST['caption'] ?? ''));
        $intro_text = trim((string)($_POST['intro_text'] ?? ''));
        if ($caption === '') {
            $this->json_response(['ok' => 0, 'error' => 'Пустое название']);
        }
        if (mb_strlen($caption) > 255) {
            $caption = mb_substr($caption, 0, 255);
        }
        if (mb_strlen($intro_text) > 10000) {
            $intro_text = mb_substr($intro_text, 0, 10000);
        }
        $compred = $this->load_compred($compred_id);
        if (!$compred || (int)$compred['user_id'] !== (int)$_SESSION['sh_id']) {
            $this->json_response(['ok' => 0, 'error' => 'Доступ запрещён'], 403);
        }
        $mysql->update_for_key('compred', 'compred_id', $compred_id, [
            'caption'    => $caption,
            'intro_text' => $intro_text === '' ? null : $intro_text,
        ]);
        add_log('Обновлено предложение #' . $compred_id);
        $this->json_response(['ok' => 1, 'caption' => $caption]);
    }

    function act__save_caption()
    {
        $compred_id = (int)($_POST['compred_id'] ?? 0);
        $compred = $this->load_compred($compred_id);
        if ($compred) {
            $_POST['intro_text'] = (string)($compred['intro_text'] ?? '');
        }
        $this->act__save_details();
    }

    function act__save_intro()
    {
        $compred_id = (int)($_POST['compred_id'] ?? 0);
        $compred = $this->load_compred($compred_id);
        if (!$compred) {
            $this->json_response(['ok' => 0, 'error' => 'Предложение не найдено'], 404);
        }
        $_POST['caption'] = trim((string)($compred['caption'] ?? ''));
        $this->act__save_details();
    }

    function act__save_comment()
    {
        global $mysql;
        $this->assert_auth();
        $compred_obj_id = (int)($_POST['compred_obj_id'] ?? 0);
        $comment = trim((string)($_POST['comment'] ?? ''));

        $row = $mysql->get_arr(
            'SELECT co.*, c.user_id FROM compred_obj co
             INNER JOIN compred c ON c.compred_id = co.compred_id
             WHERE co.compred_obj_id = ' . $compred_obj_id . ' AND c.del = 0',
            1
        );
        if (!$row || (int)$row['user_id'] !== (int)$_SESSION['sh_id']) {
            $this->json_response(['ok' => 0, 'error' => 'Доступ запрещён'], 403);
            return;
        }

        $mysql->update_for_key('compred_obj', 'compred_obj_id', $compred_obj_id, [
            'comment' => $comment,
        ]);
        $this->json_response(['ok' => 1]);
    }

    function act__remove_item()
    {
        global $mysql;
        $this->assert_auth();
        $compred_obj_id = (int)($_POST['compred_obj_id'] ?? 0);

        $row = $mysql->get_arr(
            'SELECT co.*, c.user_id, c.compred_id FROM compred_obj co
             INNER JOIN compred c ON c.compred_id = co.compred_id
             WHERE co.compred_obj_id = ' . $compred_obj_id . ' AND c.del = 0',
            1
        );
        if (!$row || (int)$row['user_id'] !== (int)$_SESSION['sh_id']) {
            $this->json_response(['ok' => 0, 'error' => 'Доступ запрещён'], 403);
            return;
        }

        $mysql->sql('DELETE FROM compred_obj WHERE compred_obj_id = ' . $compred_obj_id);
        $mysql->update_for_key('compred', 'compred_id', (int)$row['compred_id'], [
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        add_log('Удалён объект из предложения #' . (int)$row['compred_id']);
        $this->json_response(['ok' => 1]);
    }

    function act__generate_link()
    {
        global $mysql;
        $this->assert_auth();
        $compred_id = (int)($_POST['compred_id'] ?? 0);
        $compred = $this->load_compred($compred_id);
        if (!$compred || (int)$compred['user_id'] !== (int)$_SESSION['sh_id']) {
            $this->json_response(['ok' => 0, 'error' => 'Доступ запрещён'], 403);
            return;
        }

        $compred = $this->ensure_share_token($compred);
        $url = $this->compred_public_url($compred['share_token']);
        $this->json_response(['ok' => 1, 'url' => $url, 'token' => $compred['share_token']]);
    }

    function act__del()
    {
        global $mysql;
        $this->assert_auth();
        $compred_id = (int)($_POST['compred_id'] ?? 0);
        $compred = $this->load_compred($compred_id);
        if (!$compred || (int)$compred['user_id'] !== (int)$_SESSION['sh_id']) {
            $this->json_response(['ok' => 0, 'error' => 'Доступ запрещён'], 403);
            return;
        }
        $mysql->update_for_key('compred', 'compred_id', $compred_id, ['del' => 1]);
        add_log('Удалено предложение #' . $compred_id);
        $this->json_response([
            'ok'       => 1,
            'redirect' => '/sahmatka/ctrind.php?ctr=compred&act=index',
        ]);
    }
}
