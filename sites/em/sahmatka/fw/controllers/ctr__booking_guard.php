<?php
/**
 * AJAX: ручное вкл/выкл режима бронирования по типу квартир.
 * Задача 7 — ctrind/ajax_router?ctr=booking_guard&act=toggle
 */
require_once dirname(__DIR__, 2) . '/inc/booking_guard_helpers.php';

class ctr__booking_guard extends ctr__
{
    var $ctr = 'booking_guard';
    var $title = 'Booking guard';

    private function json_response($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function assert_panel_access()
    {
        if (!booking_guard_can_manage_panel()) {
            $this->json_response(['ok' => 0, 'error' => 'Нет прав'], 403);
        }
    }

    /**
     * POST home_id, rooms, enabled (0|1)
     * Optional: reset_auto=1 → source=auto
     */
    function act__toggle()
    {
        $this->assert_panel_access();

        $homeId = (int)($_POST['home_id'] ?? $_GET['home_id'] ?? 0);
        $rooms = trim((string)($_POST['rooms'] ?? $_GET['rooms'] ?? ''));
        $enabled = (int)($_POST['enabled'] ?? $_GET['enabled'] ?? 0) ? 1 : 0;
        $resetAuto = !empty($_POST['reset_auto']) || !empty($_GET['reset_auto']);

        if ($homeId <= 0 || $rooms === '') {
            $this->json_response(['ok' => 0, 'error' => 'Некорректные параметры'], 400);
        }

        if (!booking_guard_table_exists()) {
            $this->json_response(['ok' => 0, 'error' => 'Таблица booking_guard_room не создана'], 500);
        }

        if ($resetAuto) {
            booking_guard_set_auto($homeId, $rooms);
            $isManual = booking_guard_is_manual_mode($homeId, $rooms);
            $this->json_response([
                'ok' => 1,
                'is_manual_mode' => $isManual ? 1 : 0,
                'source' => 'auto',
            ]);
        }

        booking_guard_set_manual($homeId, $rooms, (bool)$enabled, (int)($_SESSION['sh_id'] ?? 0));
        $this->json_response([
            'ok' => 1,
            'is_manual_mode' => $enabled,
            'source' => 'manual',
        ]);
    }
}
