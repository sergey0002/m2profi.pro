<?php
/**
 * Отчёт: Статистика свободных квартир.
 * URL: ctrind.php?ctr=stat_free_apartments&act=index
 * Задача 8.
 */
require_once dirname(__DIR__, 2) . '/inc/stat_free_apartments_helpers.php';

class ctr__stat_free_apartments extends ctr__
{
	var $ctr = 'stat_free_apartments';
	var $title = 'Свободные квартиры';

	private function assert_access()
	{
		if (!stat_free_can_access()) {
			echo '<h2>Доступ запрещён</h2>';
			return false;
		}
		return true;
	}

	function act__index()
	{
		global $t;
		if (!$this->assert_access()) {
			return;
		}
		$t['h1'] = 'Свободные квартиры';

		$filters = stat_free_filters_from_request();
		$report = stat_free_load_report($filters);

		$this->tpl([
			'filters' => $filters,
			'homes' => $report['homes'],
			'summary' => $report['summary'],
		], 'stat_free_apartments', 'index');
	}
}
