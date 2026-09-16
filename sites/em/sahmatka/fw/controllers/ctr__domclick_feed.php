<?php
/**
 * Превью фида Домклик из БД (логика как domclick_feedx.php).
 * URL: ctrind.php?ctr=domclick_feed&act=index
 * Публичный XML: /sahmatka/domclick-{home_id}.xml
 * Задача #23.
 */
require_once dirname(__DIR__, 2) . '/inc/feed_preview.php';
require_once dirname(__DIR__, 2) . '/inc/domclick_feed.php';

class ctr__domclick_feed extends ctr__
{
	var $ctr = 'domclick_feed';
	var $title = 'DomclickFeed';
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
		$t['h1'] = 'DomclickFeed';
		@set_time_limit(180);

		$filters = feed_preview_read_filters();
		$homeId = (int)$filters['building'];
		$onlyInvalid = !empty($filters['only_invalid']);

		$feed = new em_domclick_feed($mysql, dirname(__DIR__, 2));
		$options = $feed->homes_options();
		$kvartals = feed_preview_kvartal_options($mysql);
		$items = $feed->collect($homeId, true, $filters);

		$feedUrlPattern = feed_preview_origin() . '/sahmatka/domclick-{home_id}.xml';
		$feedUrl = $homeId > 0
			? feed_preview_origin() . '/sahmatka/domclick-' . $homeId . '.xml'
			: $feedUrlPattern;

		$stats = feed_preview_stats_domclick($items, $feedUrl);
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
			'feed_url' => $feedUrl,
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
		], 'domclick_feed', 'index');
	}
}
