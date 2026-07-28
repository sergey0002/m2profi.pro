<?php
$filters = $data['filters'] ?? ['sdan' => 'all', 'sort' => 'delivery_asc'];
$homes = $data['homes'] ?? [];
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
.stat-free-filters select {
	min-width: 160px; padding: 8px 12px; font-size: 14px;
	border: 2px solid #00CDAD; border-radius: 5px; background: #fff; color: #2F4049;
}
.stat-free-btn {
	padding: 8px 20px; background: #00CDAD; color: #fff; border: none; border-radius: 5px;
	cursor: pointer; font-weight: 700; font-size: 14px;
}
.stat-free-btn:hover { background: #00b89c; }
.stat-free-toggle {
	display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px;
	color: #2F4049; user-select: none; padding: 8px 0;
}
.stat-free-toggle input { width: 18px; height: 18px; accent-color: #c62828; }

.stat-free-summary {
	display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.stat-free-summary-item {
	flex: 1 1 140px; background: #fff; border: 1px solid #dfe6ea; border-radius: 10px;
	padding: 14px 16px; text-align: center;
}
.stat-free-summary-item .v { font-size: 26px; font-weight: 800; color: #2F4049; line-height: 1.1; }
.stat-free-summary-item .l { font-size: 12px; color: #6a7a84; margin-top: 4px; font-weight: 600; }
.stat-free-summary-item.is-sold .v { color: #b71c1c; }
.stat-free-summary-item.is-free .v { color: #00a88e; }

.stat-free-table {
	width: 100%; border-collapse: collapse; font-size: 14px;
	background: #fff; border-radius: 12px; overflow: hidden;
}
.stat-free-table th, .stat-free-table td {
	border: none !important;
	padding: 14px 12px;
	text-align: left;
	vertical-align: middle;
}
.stat-free-table thead th {
	font-size: 12px; text-transform: uppercase; letter-spacing: .04em;
	color: #6a7a84; font-weight: 700; background: #f4f7f8;
	border-bottom: 2px solid #e8eef1 !important;
}
.stat-free-table tbody tr { border-bottom: 1px solid #eef3f5; }
.stat-free-table tbody tr:hover { background: #f8fcfb; }
.stat-free-home {
	font-weight: 800; font-size: 16px; color: #2F4049; white-space: nowrap;
}
.stat-free-home small {
	display: block; font-weight: 600; font-size: 11px; color: #8a9aa3; margin-top: 2px;
}
.stat-free-date {
	font-weight: 700; color: #00a88e; white-space: nowrap; font-size: 15px;
	min-width: 90px;
}
.stat-free-date.is-empty { color: #adb5bd; font-weight: 600; }

.stat-free-bars {
	display: flex; width: 100%; align-items: stretch; gap: 6px; min-width: 280px;
	overflow-x: auto; padding-bottom: 2px;
}
.stat-free-type {
	display: flex; flex-direction: column; gap: 4px; min-width: 42px;
	box-sizing: border-box;
}
.stat-free-type-label {
	font-size: 11px; font-weight: 800; color: #2F4049; line-height: 1;
}
.stat-free-bar {
	position: relative; height: 28px; border-radius: 999px; overflow: hidden;
	width: 100%; background: #f0d6d6; box-shadow: inset 0 0 0 1px rgba(183,28,28,.12);
}
.stat-free-bar-base {
	position: absolute; left: 0; top: 0; bottom: 0; right: 0;
	background: linear-gradient(90deg, #ef9a9a 0%, #e57373 100%);
}
.stat-free-bar-sold {
	position: absolute; left: 0; top: 0; bottom: 0; z-index: 1;
	background: linear-gradient(90deg, #c62828 0%, #b71c1c 100%);
	display: flex; align-items: center; justify-content: flex-end;
	padding: 0 8px; box-sizing: border-box; min-width: 0;
}
.bar-sold-label, .stat-free-bar-label {
	position: relative; z-index: 2; font-size: 11px; font-weight: 800;
	white-space: nowrap; line-height: 1; pointer-events: none;
}
.bar-sold-label { color: #fff; text-shadow: 0 1px 1px rgba(0,0,0,.25); }
.stat-free-bar-label {
	position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
	color: #4a1c1c; mix-blend-mode: multiply;
}
.stat-free-hide-sold .bar-sold { display: none !important; }

.stat-free-empty {
	padding: 28px; text-align: center; color: #6a7a84; background: #f8fafb;
	border-radius: 10px; font-weight: 600;
}

@media (max-width: 900px) {
	.stat-free-table thead { display: none; }
	.stat-free-table, .stat-free-table tbody, .stat-free-table tr, .stat-free-table td {
		display: block; width: 100%;
	}
	.stat-free-table tbody tr {
		padding: 12px; margin-bottom: 10px; border: 1px solid #e8eef1; border-radius: 10px;
	}
	.stat-free-table td { padding: 6px 0; }
	.stat-free-home::before { content: 'Дом · '; font-weight: 600; color: #8a9aa3; }
	.stat-free-date::before { content: 'Сдача · '; font-weight: 600; color: #8a9aa3; }
}
@media print {
	.stat-free-filters, .stat-free-toggle, .stat-top__print { display: none !important; }
	.stat-free-table tbody tr { break-inside: avoid; }
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

			<label class="stat-free-toggle">
				<input type="checkbox" id="stat-free-show-sold" checked>
				Показать проданные
			</label>
		</div>

		<div class="stat-free-summary">
			<div class="stat-free-summary-item">
				<div class="v"><?= (int)$summary['homes'] ?></div>
				<div class="l">домов</div>
			</div>
			<div class="stat-free-summary-item">
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
			<table class="stat-free-table">
				<thead>
					<tr>
						<th style="width:140px;">Дом</th>
						<th style="width:110px;">Дата сдачи</th>
						<th>Квартиры</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($homes as $home): ?>
					<?php
					$label = (string)($home['delivery_label'] ?? '—');
					$isEmptyDate = ($label === '—');
					?>
					<tr>
						<td>
							<div class="stat-free-home">
								<?= htmlspecialchars((string)$home['caption'], ENT_QUOTES, 'UTF-8') ?>
								<?php if (!empty($home['complite'])): ?>
									<small>сдан</small>
								<?php else: ?>
									<small>строится</small>
								<?php endif; ?>
							</div>
						</td>
						<td>
							<span class="stat-free-date<?= $isEmptyDate ? ' is-empty' : '' ?>">
								<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
							</span>
						</td>
						<td>
							<?php if (empty($home['rooms'])): ?>
								<span style="color:#adb5bd;">нет данных</span>
							<?php else: ?>
								<div class="stat-free-bars">
									<?php foreach ($home['rooms'] as $room): ?>
										<?php
										$w = (float)$room['width_pct'];
										$soldFill = (float)$room['sold_fill_pct'];
										$sold = (int)$room['sold'];
										$total = (int)$room['total'];
										?>
										<div class="stat-free-type" style="flex:0 0 <?= number_format($w, 2, '.', '') ?>%; max-width:<?= number_format($w, 2, '.', '') ?>%;"
											 title="<?= htmlspecialchars(
												 $room['rooms'] . ': ' . $total . ' (' . $fmtPct($room['label_type_pct']) . '%)'
												 . ($sold > 0 ? ', продано ' . $sold . ' (' . $fmtPct($room['label_sold_pct']) . '%)' : ''),
												 ENT_QUOTES,
												 'UTF-8'
											 ) ?>">
											<span class="stat-free-type-label"><?= htmlspecialchars((string)$room['rooms'], ENT_QUOTES, 'UTF-8') ?></span>
											<div class="stat-free-bar">
												<div class="stat-free-bar-base"></div>
												<?php if ($sold > 0): ?>
													<div class="stat-free-bar-sold bar-sold" style="width:<?= number_format($soldFill, 2, '.', '') ?>%;">
														<?php if ($soldFill >= 28): ?>
															<span class="bar-sold-label"><?= $sold ?> · <?= $fmtPct($room['label_sold_pct']) ?>%</span>
														<?php endif; ?>
													</div>
												<?php endif; ?>
												<span class="stat-free-bar-label"><?= $total ?> · <?= $fmtPct($room['label_type_pct']) ?>%</span>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<script>
(function () {
	var cb = document.getElementById('stat-free-show-sold');
	var table = document.querySelector('.stat-free-table');
	if (!cb || !table) return;
	function apply() {
		table.classList.toggle('stat-free-hide-sold', !cb.checked);
	}
	cb.addEventListener('change', apply);
	apply();
})();
</script>
