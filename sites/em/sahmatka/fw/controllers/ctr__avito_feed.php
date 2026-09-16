<?php
/**
 * Превью фида Avito из БД (тот же em_avito_feed, что XML).
 * URL: ctrind.php?ctr=avito_feed&act=index
 * Задача #23.
 */
require_once dirname(__DIR__, 2) . '/inc/feed_preview.php';
require_once dirname(__DIR__, 2) . '/inc/avito_feed.php';

class ctr__avito_feed extends ctr__
{
	var $ctr = 'avito_feed';
	var $title = 'AvitoFeed';
	private $limit = 500;

	private function assert_access()
	{
		if (!feed_preview_can_access()) {
			echo '<h2>Доступ запрещён. Страница доступна только администратору.</h2>';
			return false;
		}
		return true;
	}

	function act__index()
	{
		global $t, $mysql;
		if (!$this->assert_access()) {
			return;
		}
		$t['h1'] = 'AvitoFeed';
		@set_time_limit(120);

		$homeId = (int)($_GET['building'] ?? 0);
		$onlyInvalid = !empty($_GET['only_invalid']);

		$feed = new em_avito_feed($mysql, dirname(__DIR__, 2));
		$options = $feed->homes_options();
		$items = $feed->collect($homeId, true);

		$filterNote = '';
		if ($homeId > 0) {
			foreach ($options as $opt) {
				if ((int)$opt['value'] === $homeId) {
					$filterNote = 'фильтр: ' . $opt['label'];
					break;
				}
			}
		}

		$stats = feed_preview_stats_avito(
			$items,
			feed_preview_origin() . '/sahmatka/avito_feedx.php',
			'3',
			'Avito.ru'
		);
		$inFeed = 0;
		$skipped = 0;
		foreach ($items as $it) {
			if (!empty($it['in_feed'])) {
				$inFeed++;
			} else {
				$skipped++;
			}
		}
		$stats['in_feed'] = $inFeed;
		$stats['skipped'] = $skipped;

		$filtered = $items;
		if ($onlyInvalid) {
			$filtered = [];
			foreach ($items as $it) {
				if (empty($it['ok'])) {
					$filtered[] = $it;
				}
			}
		}
		$truncated = count($filtered) > $this->limit;
		if ($truncated) {
			$filtered = array_slice($filtered, 0, $this->limit);
		}

		$this->tpl([
			'error' => '',
			'feed_url' => feed_preview_origin() . '/sahmatka/avito_feedx.php',
			'stats' => $stats,
			'cards' => $filtered,
			'buildings' => $options,
			'filters' => [
				'building' => $homeId > 0 ? (string)$homeId : '',
				'only_invalid' => $onlyInvalid ? 1 : 0,
			],
			'truncated' => $truncated,
			'limit' => $this->limit,
			'filter_note' => $filterNote,
			'field_meta' => $feed->fields_spec()->index(),
		], 'avito_feed', 'index');
	}
}
