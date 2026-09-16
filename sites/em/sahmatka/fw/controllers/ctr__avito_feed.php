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
	private $limit = 10000;

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
		@set_time_limit(180);

		$filters = feed_preview_read_filters();
		$homeId = (int)$filters['building'];
		$onlyInvalid = !empty($filters['only_invalid']);

		$feed = new em_avito_feed($mysql, dirname(__DIR__, 2));
		$options = $feed->homes_options();
		$kvartals = feed_preview_kvartal_options($mysql);
		$items = $feed->collect($homeId, true, $filters);

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
		if (count($filtered) > $this->limit) {
			$filtered = array_slice($filtered, 0, $this->limit);
		}

		$this->tpl([
			'error' => '',
			'feed_url' => feed_preview_origin() . '/sahmatka/avito_feedx.php',
			'stats' => $stats,
			'cards' => $filtered,
			'buildings' => $options,
			'kvartals' => $kvartals,
			'rooms_options' => feed_preview_rooms_select_options(),
			'area_options' => feed_preview_area_select_options(),
			'filters' => [
				'building' => $homeId > 0 ? (string)$homeId : '',
				'kvartal' => !empty($filters['kvartal']) ? (string)(int)$filters['kvartal'] : '',
				'rooms_from' => $filters['rooms_from'] === null ? '' : (string)(int)$filters['rooms_from'],
				'rooms_to' => $filters['rooms_to'] === null ? '' : (string)(int)$filters['rooms_to'],
				'area_from' => $filters['area_from'] === null ? '' : (string)(int)$filters['area_from'],
				'area_to' => $filters['area_to'] === null ? '' : (string)(int)$filters['area_to'],
				'only_invalid' => $onlyInvalid ? 1 : 0,
			],
			'field_meta' => $feed->fields_spec()->index(),
		], 'avito_feed', 'index');
	}
}
