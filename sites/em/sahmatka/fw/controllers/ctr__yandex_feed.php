<?php
/**
 * Превью фида Яндекс.Недвижимость из БД (тот же em_yandex_feed, что XML).
 * URL: ctrind.php?ctr=yandex_feed&act=index
 * Задача #23.
 */
require_once dirname(__DIR__, 2) . '/inc/feed_preview.php';
require_once dirname(__DIR__, 2) . '/inc/yandex_feed.php';

class ctr__yandex_feed extends ctr__
{
	var $ctr = 'yandex_feed';
	var $title = 'YandexFeed';
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
		$t['h1'] = 'YandexFeed';
		@set_time_limit(120);

		$homeId = (int)($_GET['building'] ?? 0);
		$onlyInvalid = !empty($_GET['only_invalid']);

		$feed = new em_yandex_feed($mysql, dirname(__DIR__, 2));
		$options = $feed->homes_options();
		$pack = $feed->collect($homeId);
		$items = $pack['items'];

		$filterNote = '';
		if ($homeId > 0) {
			foreach ($options as $opt) {
				if ((int)$opt['value'] === $homeId) {
					$filterNote = 'фильтр: ' . $opt['label'];
					break;
				}
			}
		}

		$forStats = $items;
		$stats = feed_preview_stats_yandex($forStats, feed_preview_origin() . '/sahmatka/yandex_feedx.php', $pack['date']);

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
			'feed_url' => feed_preview_origin() . '/sahmatka/yandex_feedx.php',
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
			'field_meta' => feed_fields_index(feed_fields_yandex_new()),
		], 'yandex_feed', 'index');
	}
}
