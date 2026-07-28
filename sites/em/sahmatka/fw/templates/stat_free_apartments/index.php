<?php
$filters = $data['filters'] ?? ['sdan' => 'all', 'sort' => 'delivery_asc'];
$homes = $data['homes'] ?? [];
$roomColumns = $data['room_columns'] ?? [];
$summary = $data['summary'] ?? ['homes' => 0, 'apartments' => 0, 'sold' => 0, 'free' => 0];
$sdan = $filters['sdan'] ?? 'all';
$sort = $filters['sort'] ?? 'delivery_asc';
$fmtPct = static function ($n) {
	$n = (float)$n;
	$s = number_format($n, 1, ',', '');
	return rtrim(rtrim($s, '0'), ',');
};
?>
<style>
.stat-free-wrap { margin: 8px 0 48px; }
.stat-free-top {
	display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between;
	gap: 16px; margin-bottom: 18px;
}
.stat-free-filters {
	display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
}
.stat-free-filters label {
	display: block; font-size: 12px; font-weight: 700; color: #2F4049; margin-bottom: 4px;
}
.stat-free-filters select,
.stat-free-btn {
	height: 40px; box-sizing: border-box; padding: 0 14px; font-size: 14px;
	border: 2px solid #00CDAD; border-radius: 5px; line-height: 1;
}
.stat-free-filters select {
	min-width: 160px; background: #fff; color: #2F4049;
}
.stat-free-btn {
	background: #00CDAD; color: #fff; cursor: pointer; font-weight: 700;
}
.stat-free-btn:hover { background: #00b89c; }

.stat-free-legend {
	display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
	font-size: 12px; font-weight: 700; color: #5a6a74; padding: 8px 0;
}
.stat-free-legend-item {
	display: flex; align-items: center; gap: 6px;
}
.stat-free-legend-swatch {
	width: 36px; height: 12px; border-radius: 999px;
}

.stat-free-summary {
	display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.stat-free-summary-item {
	flex: 1 1 140px; background: #fff; border: 1px solid #dfe6ea; border-radius: 10px;
	padding: 14px 16px; text-align: center;
}
.stat-free-summary-item .v { font-size: 26px; font-weight: 800; color: #2F4049; line-height: 1.1; }
.stat-free-summary-item .l { font-size: 12px; color: #6a7a84; margin-top: 4px; font-weight: 600; }
.stat-free-summary-item.is-total .v { color: #607080; }
.stat-free-summary-item.is-sold .v { color: #b71c1c; }
.stat-free-summary-item.is-free .v { color: #00a88e; }

.stat-free-scroll {
	width: 100%; overflow-x: auto;
}
.stat-free-table {
	width: 100%; min-width: 720px; border-collapse: collapse; font-size: 13px;
	background: #fff; border-radius: 12px; table-layout: fixed;
}
.stat-free-table th, .stat-free-table td {
	border: none !important;
	padding: 10px 6px;
	text-align: left;
	vertical-align: top;
}
.stat-free-table th.col-home,
.stat-free-table td.col-home {
	width: 48px; padding-left: 8px; padding-right: 4px;
	position: sticky; left: 0; z-index: 2; background: #fff;
	border-right: 1px solid #b0bec5 !important;
}
.stat-free-table thead th.col-home { background: #f4f7f8; z-index: 3; }
.stat-free-table th.col-date,
.stat-free-table td.col-date {
	width: 58px; padding-left: 2px; padding-right: 6px;
	position: sticky; left: 48px; z-index: 2; background: #fff;
	box-shadow: 4px 0 6px -4px rgba(47, 64, 73, .12);
	border-right: 1px solid #b0bec5 !important;
	text-align: center;
}
.stat-free-table thead th.col-date { background: #f4f7f8; z-index: 3; }
.stat-free-table th.col-room,
.stat-free-table td.col-room {
	min-width: 70px;
	border-left: 1px solid #b0bec5 !important;
}
.stat-free-table thead th {
	font-size: 11px; text-transform: uppercase; letter-spacing: .04em;
	color: #6a7a84; font-weight: 700; background: #f4f7f8;
	border-bottom: 1px solid #000 !important;
	text-align: center;
}
.stat-free-table thead th.col-home { text-align: left; }
.stat-free-table thead th.col-date { text-align: center; }
.stat-free-table tbody tr {
	border-bottom: 1px solid #000;
	background: #fff;
}
.stat-free-table tbody tr:nth-child(even) {
	background: #f3f7f8;
}
.stat-free-table tbody tr:nth-child(even) td.col-home,
.stat-free-table tbody tr:nth-child(even) td.col-date {
	background: #f3f7f8;
}
.stat-free-table tbody tr:hover {
	background: #e8f7f3;
}
.stat-free-table tbody tr:hover td.col-home,
.stat-free-table tbody tr:hover td.col-date {
	background: #e8f7f3;
}
.stat-free-home {
	font-weight: 700; font-size: 13px; color: #2F4049; line-height: 1.2;
	white-space: nowrap;
}
.stat-free-date {
	font-weight: 700; color: #2F4049; font-size: 11px; line-height: 1.2;
	white-space: nowrap;
}
.stat-free-date.is-empty { color: #adb5bd; font-weight: 600; }

.stat-free-plot {
	display: flex; flex-direction: column; gap: 2px;
	min-width: 50px; max-width: 100%; box-sizing: border-box;
}
.stat-free-type-caption {
	display: block; font-size: 9px; font-weight: 700; white-space: nowrap;
	line-height: 1.15; color: #2F4049;
}
.stat-free-bar {
	display: block; height: 14px; border-radius: 999px; overflow: hidden;
	width: 100%; min-width: 0; background: #d5dde3;
}
.stat-free-bar-fill {
	display: block; height: 100%; border-radius: 999px;
	min-width: 0; box-sizing: border-box;
}
.stat-free-bar-fill.is-zero { width: 0 !important; }
.stat-free-cell-empty {
	display: block; min-height: 20px; color: #c5ced6; font-size: 12px;
}

.stat-free-empty {
	padding: 28px; text-align: center; color: #6a7a84; background: #f8fafb;
	border-radius: 10px; font-weight: 600;
}

@media print {
	.stat-free-filters, .stat-free-legend, .stat-top__print { display: none !important; }
	.stat-free-table tbody tr { break-inside: avoid; }
	.stat-free-scroll { overflow: visible; }
}
</style>

<div id="ajaxcontent" class="stat">
	<div class="stat-top">
		<div class="stat-top-filter"></div>
		<a href="JavaScript:window.print();" class="stat-top__print"></a>
	</div>

	<div class="stat-free-wrap">
		<div class="stat-free-top">
			<form method="get" action="/sahmatka/ctrind.php" class="stat-free-filters">
				<input type="hidden" name="ctr" value="stat_free_apartments">
				<input type="hidden" name="act" value="index">
				<div>
					<label>Дома</label>
					<select name="sdan">
						<option value="all" <?= $sdan === 'all' ? 'selected' : '' ?>>Все</option>
						<option value="0" <?= $sdan === '0' ? 'selected' : '' ?>>Строящиеся</option>
						<option value="1" <?= $sdan === '1' ? 'selected' : '' ?>>Сданные</option>
					</select>
				</div>
				<div>
					<label>Сортировка</label>
					<select name="sort">
						<option value="delivery_asc" <?= $sort === 'delivery_asc' ? 'selected' : '' ?>>Срок сдачи ↑</option>
						<option value="delivery_desc" <?= $sort === 'delivery_desc' ? 'selected' : '' ?>>Срок сдачи ↓</option>
					</select>
				</div>
				<div>
					<button type="submit" class="stat-free-btn">Показать</button>
				</div>
			</form>

			<div class="stat-free-legend" title="Цвет = тип квартир; темнее = больше свободных">
				<?php foreach ($roomColumns as $rk): ?>
					<?php
					$legStart = stat_free_type_shade_rgb($rk, 0);
					$legMid = stat_free_type_shade_rgb($rk, 50);
					$legEnd = stat_free_type_shade_rgb($rk, 100);
					$legGrad = 'linear-gradient(90deg, ' . $legStart['css'] . ' 0%, ' . $legMid['css'] . ' 50%, ' . $legEnd['css'] . ' 100%)';
					?>
					<span class="stat-free-legend-item">
						<span class="stat-free-legend-swatch" style="background:<?= htmlspecialchars($legGrad, ENT_QUOTES, 'UTF-8') ?>;"></span>
						<?= htmlspecialchars((string)$rk, ENT_QUOTES, 'UTF-8') ?>
					</span>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="stat-free-summary">
			<div class="stat-free-summary-item">
				<div class="v"><?= (int)$summary['homes'] ?></div>
				<div class="l">домов</div>
			</div>
			<div class="stat-free-summary-item is-total">
				<div class="v"><?= (int)$summary['apartments'] ?></div>
				<div class="l">квартир</div>
			</div>
			<div class="stat-free-summary-item is-free">
				<div class="v"><?= (int)$summary['free'] ?></div>
				<div class="l">свободно</div>
			</div>
			<div class="stat-free-summary-item is-sold">
				<div class="v"><?= (int)$summary['sold'] ?></div>
				<div class="l">продано</div>
			</div>
		</div>

		<?php if (!$homes): ?>
			<div class="stat-free-empty">Нет домов по выбранному фильтру</div>
		<?php else: ?>
			<div class="stat-free-scroll">
			<table class="stat-free-table" id="stat-free-table">
				<thead>
					<tr>
						<th class="col-home">Дом</th>
						<th class="col-date">Сдача</th>
						<?php foreach ($roomColumns as $rk): ?>
							<th class="col-room"><?= htmlspecialchars((string)$rk, ENT_QUOTES, 'UTF-8') ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($homes as $home): ?>
					<?php
					$label = (string)($home['delivery_label'] ?? '—');
					$isEmptyDate = ($label === '—');
					$byType = $home['rooms_by_type'] ?? [];
					?>
					<tr>
						<td class="col-home">
							<div class="stat-free-home">
								<?= htmlspecialchars((string)$home['caption'], ENT_QUOTES, 'UTF-8') ?>
							</div>
						</td>
						<td class="col-date">
							<span class="stat-free-date<?= $isEmptyDate ? ' is-empty' : '' ?>">
								<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
							</span>
						</td>
						<?php foreach ($roomColumns as $rk): ?>
							<?php $room = $byType[$rk] ?? null; ?>
							<td class="col-room">
								<?php if (!$room): ?>
									<span class="stat-free-cell-empty">—</span>
								<?php else:
									$barScale = (float)($room['bar_scale_pct'] ?? 0);
									$free = (int)$room['free'];
									$total = (int)$room['total'];
									$freePct = (float)($room['label_free_pct'] ?? 0);
									$freeFill = (float)($room['free_fill_pct'] ?? $freePct);
									$grad = stat_free_type_gradient_css($rk, $freePct);
								?>
									<div class="stat-free-plot" style="width:<?= number_format(max($barScale, 0.5), 2, '.', '') ?>%;"
										 title="<?= htmlspecialchars(
											 $rk . ': всего ' . $total
											 . ', свободно ' . $free . ' (' . $fmtPct($freePct) . '%)',
											 ENT_QUOTES,
											 'UTF-8'
										 ) ?>">
										<span class="stat-free-type-caption">
											<?= $free ?> / <?= $fmtPct($freePct) ?>%
										</span>
										<div class="stat-free-bar" title="всего <?= (int)$total ?>">
											<span class="stat-free-bar-fill<?= $free <= 0 ? ' is-zero' : '' ?>"
												style="width:<?= number_format(max($freeFill, 0), 2, '.', '') ?>%;background:<?= htmlspecialchars($grad, ENT_QUOTES, 'UTF-8') ?>;"></span>
										</div>
									</div>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
	</div>
</div>
