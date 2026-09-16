<?php
$error = (string)($data['error'] ?? '');
$stats = $data['stats'] ?? [];
$cards = $data['cards'] ?? [];
$buildings = $data['buildings'] ?? [];
$filters = $data['filters'] ?? [];
$truncated = !empty($data['truncated']);
$limit = (int)($data['limit'] ?? 500);
$feedUrl = (string)($data['feed_url'] ?? ($stats['url'] ?? ''));
$filterNote = (string)($data['filter_note'] ?? '');
$building = (string)($filters['building'] ?? '');
$onlyInvalid = !empty($filters['only_invalid']);
$fieldMeta = $data['field_meta'] ?? [];
?>
<style>
.feed-wrap { margin: 16px 0 40px; }
.feed-summary {
	background: #f5f7f8;
	border: 1px solid #d6dde2;
	border-radius: 8px;
	padding: 16px 18px 18px;
	margin-bottom: 16px;
	box-sizing: border-box;
}
.feed-summary__grid {
	display: flex;
	flex-wrap: wrap;
	gap: 10px 24px;
	font-size: 14px;
	line-height: 1.5;
}
.feed-summary__grid span { color: #666; }
.feed-summary__grid b { color: #01112B; }
.feed-summary__link { margin-top: 10px; font-size: 13px; }
.feed-summary__rooms { margin-top: 10px; font-size: 13px; color: #444; }
.feed-error {
	background: #fde8e8;
	border: 1px solid #e74c3c;
	color: #922;
	padding: 12px 14px;
	border-radius: 6px;
	margin-bottom: 14px;
}
.feed-warn {
	background: #fff3cd;
	border: 1px solid #ffc107;
	color: #856404;
	padding: 10px 12px;
	border-radius: 6px;
	margin-bottom: 14px;
}
.feed-filters {
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
	align-items: flex-end;
	margin-bottom: 16px;
}
.feed-filters label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
.feed-filters select { min-width: 200px; padding: 6px 8px; }
.feed-filters .feed-check { display: flex; align-items: center; gap: 6px; padding-bottom: 6px; }
.feed-card {
	display: flex;
	flex-wrap: wrap;
	gap: 16px;
	width: 100%;
	background: #f5f7f8;
	border: 1px solid #d6dde2;
	border-radius: 8px;
	padding: 14px 16px;
	margin-bottom: 12px;
	box-sizing: border-box;
}
.feed-card.is-invalid {
	background: #fde8e8;
	border-color: #e74c3c;
}
.feed-card__img {
	flex: 0 0 200px;
	max-width: 200px;
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 4px;
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 120px;
}
.feed-card__img img {
	max-height: 180px;
	max-width: 100%;
	object-fit: contain;
	display: block;
}
.feed-card__img .ph { color: #999; font-size: 13px; padding: 12px; text-align: center; }
.feed-card__body { flex: 1 1 280px; min-width: 0; }
.feed-card__title {
	margin: 0 0 10px;
	font-size: 16px;
	font-weight: 600;
	color: #01112B;
	line-height: 1.35;
}
.feed-card__fields {
	width: 100%;
	border-collapse: collapse;
	font-size: 13px;
}
.feed-card__fields th,
.feed-card__fields td {
	border: 1px solid #e0e6ea;
	padding: 4px 8px;
	text-align: left;
	vertical-align: top;
}
.feed-card__fields th {
	width: 32%;
	background: rgba(255,255,255,.5);
	font-weight: 600;
	color: #555;
	white-space: nowrap;
	cursor: help;
}
.feed-card__fields td { cursor: help; word-break: break-word; }
.feed-card__fields td.is-empty { color: #999; font-style: italic; }
.feed-card.is-invalid .feed-card__fields th { background: rgba(255,255,255,.35); }
.feed-card__reasons {
	margin: 10px 0 0;
	padding-left: 18px;
	color: #b33a3a;
	font-size: 13px;
	font-weight: 600;
}
.feed-card__reasons li { margin-bottom: 2px; }
</style>

<div class="feed-wrap">
	<?php if ($error !== ''): ?>
		<div class="feed-error"><?= feed_preview_h($error) ?></div>
	<?php endif; ?>

	<?php if (!$error): ?>
		<div class="feed-summary">
			<?php if ($filterNote !== ''): ?>
				<div style="margin-bottom:8px;font-size:13px;color:#666;"><?= feed_preview_h($filterNote) ?></div>
			<?php endif; ?>
			<div class="feed-summary__grid">
				<div><span>Объявлений всего:</span> <b><?= (int)($stats['total'] ?? 0) ?></b></div>
				<div><span>В фид XML:</span> <b><?= (int)($stats['in_feed'] ?? $stats['valid'] ?? 0) ?></b></div>
				<div><span>Не попадут в фид:</span> <b><?= (int)($stats['skipped'] ?? 0) ?></b></div>
				<div><span>Валидных:</span> <b><?= (int)($stats['valid'] ?? 0) ?></b></div>
				<div><span>Невалидных:</span> <b><?= (int)($stats['invalid'] ?? 0) ?></b></div>
				<div><span>Уник. NewDevelopmentId:</span> <b><?= (int)($stats['developments'] ?? 0) ?></b></div>
				<div><span>Уник. HouseType:</span> <b><?= (int)($stats['house_types'] ?? 0) ?></b></div>
				<div><span>NewBuilding=yes:</span> <b><?= (int)($stats['new_building_yes'] ?? 0) ?></b></div>
				<div><span>NewBuilding=no:</span> <b><?= (int)($stats['new_building_no'] ?? 0) ?></b></div>
				<div><span>Без картинки:</span> <b><?= (int)($stats['no_image'] ?? 0) ?></b></div>
				<?php if (!empty($stats['format_version'])): ?>
					<div><span>formatVersion:</span> <b><?= feed_preview_h($stats['format_version']) ?></b></div>
				<?php endif; ?>
				<?php if (!empty($stats['target'])): ?>
					<div><span>target:</span> <b><?= feed_preview_h($stats['target']) ?></b></div>
				<?php endif; ?>
			</div>
			<?php if (!empty($stats['rooms']) && is_array($stats['rooms'])): ?>
				<div class="feed-summary__rooms">
					Rooms:
					<?php
					$parts = [];
					foreach ($stats['rooms'] as $rk => $rc) {
						$parts[] = feed_preview_h((string)$rk) . '&nbsp;—&nbsp;' . (int)$rc;
					}
					echo implode('; ', $parts);
					?>
				</div>
			<?php endif; ?>
			<?php if ($feedUrl !== ''): ?>
				<div class="feed-summary__link">
					Фид: <a href="<?= feed_preview_h($feedUrl) ?>" target="_blank" rel="noopener"><?= feed_preview_h($feedUrl) ?></a>
				</div>
			<?php endif; ?>
		</div>

		<?php if ($truncated): ?>
			<div class="feed-warn">Показаны первые <?= (int)$limit ?>; уточните фильтр.</div>
		<?php endif; ?>

		<form method="get" action="/sahmatka/ctrind.php" class="feed-filters">
			<input type="hidden" name="ctr" value="avito_feed">
			<input type="hidden" name="act" value="index">
			<div>
				<label>Дом</label>
				<select name="building">
					<option value="">Все</option>
					<?php foreach ($buildings as $b): ?>
						<?php
						$val = is_array($b) ? (string)($b['value'] ?? '') : (string)$b;
						$lab = is_array($b) ? (string)($b['label'] ?? $val) : (string)$b;
						?>
						<option value="<?= feed_preview_h($val) ?>" <?= $building === $val ? 'selected' : '' ?>>
							<?= feed_preview_h($lab) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="feed-check">
				<label style="margin:0;font-weight:500;">
					<input type="checkbox" name="only_invalid" value="1" <?= $onlyInvalid ? 'checked' : '' ?>>
					Только невалидные
				</label>
			</div>
			<div>
				<button type="submit" class="filter-btn" style="padding:8px 16px; background:#00CDAD; color:#fff; border:none; border-radius:5px; font-weight:700; cursor:pointer;">Фильтр</button>
				<a href="/sahmatka/ctrind.php?ctr=avito_feed&act=index" style="margin-left:10px;font-size:13px;">Все</a>
			</div>
		</form>

		<?php foreach ($cards as $card): ?>
			<?php
			$invalid = empty($card['ok']);
			$img = (string)($card['image'] ?? '');
			$fields = $card['fields'] ?? [];
			$reasons = $card['reasons'] ?? [];
			?>
			<div class="feed-card<?= $invalid ? ' is-invalid' : '' ?>">
				<div class="feed-card__img">
					<?php if ($img !== ''): ?>
						<img src="<?= feed_preview_h($img) ?>" alt="" loading="lazy">
					<?php else: ?>
						<div class="ph">нет картинки</div>
					<?php endif; ?>
				</div>
				<div class="feed-card__body">
					<div class="feed-card__title"><?= feed_preview_h($card['title'] ?? '') ?></div>
					<?php if ($fields): ?>
						<table class="feed-card__fields">
							<tbody>
							<?php foreach ($fields as $fk => $fv): ?>
								<?php
								$tip = (string)(($fieldMeta[$fk]['tooltip'] ?? ''));
								$empty = trim((string)$fv) === '';
								?>
								<tr>
									<th title="<?= feed_preview_h($tip) ?>"><?= feed_preview_h($fk) ?></th>
									<td class="<?= $empty ? 'is-empty' : '' ?>" title="<?= feed_preview_h($tip !== '' ? (($empty ? 'пусто. ' : 'значение: ' . (string)$fv . '. ') . $tip) : '') ?>">
										<?= $empty ? '—' : feed_preview_h($fv) ?>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
					<?php if ($invalid && $reasons): ?>
						<ul class="feed-card__reasons">
							<?php foreach ($reasons as $r): ?>
								<li><?= feed_preview_h($r) ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<?php if (!$cards): ?>
			<p style="color:#666;">Нет объявлений по текущему фильтру.</p>
		<?php endif; ?>
	<?php endif; ?>
</div>
