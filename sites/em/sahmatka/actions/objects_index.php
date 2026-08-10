<?
// ВЫвод обектов если не выбран обьект

$sa = new sahmatka( $_SESSION , $connection );
$h = $sa->get_homes_arr();
// print_r($h);

require_once __DIR__ . '/../inc/booking_guard_helpers.php';
$bgCfg = booking_guard_config();
$bgThreshold = (float)$bgCfg['free_percent_threshold'];
$showAdminServicePanel = booking_guard_can_manage_panel();
$homeServiceStats = [];

$visibleHomeIds = [];
foreach ((array)$h as $homeRow) {
	$hid = (int)($homeRow['home_id'] ?? 0);
	if ($hid > 0) { $visibleHomeIds[] = $hid; }
}
$visibleHomeIds = array_values(array_unique($visibleHomeIds));
if ($visibleHomeIds) {
	$homeServiceStats = booking_guard_calc_room_stats($visibleHomeIds);
	booking_guard_sync_auto($homeServiceStats);
	$modes = booking_guard_load_modes($visibleHomeIds);
	booking_guard_apply_modes_to_stats($homeServiceStats, $modes);
}


	
?>
 
 
 
 <section class="section-objects">
	<div class="container mobc">
 
		
<style>
		@media (max-width: 767px) {.show-mobile{display:auto;} .hide-mobile{display:none;}  }
		@media (min-width: 768px) {.show-mobile{display:none;} .hide-mobile{display:auto;} }
</style>
		
 <div class="page-header" style="margin-bottom:0;">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Объекты</div>
			 
			<div style="width:100%; text-align:right; padding-top:30px; cursor:pointer;" class="open_xxpanel hide-mobile">
				<div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
					<div style="display:table-cell; text-align:left; vertical-align: top;">
						<span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br/>
						<span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку в телефон</span>
					</div>
					<div style="display:table-cell; text-align:left; vertical-align: baseline;">
						<span style="font-size:14px;">И заходите в М2 PROFI как в приложение.</span>
					</div>
					<div style="display:table-cell; ">
					<img src="/l.png" />
					</div>
				</div>
			</div>
			 
		</div>
		
		
		
		<div style="width:100%;  padding-top:30px; padding-bottom:30px; cursor:pointer;" class="open_xxpanel show-mobile">
				<div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
					<div style="display:table-cell; text-align:left; vertical-align: top; width: 100%;">
						<span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br/>
						<span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку <br/>в телефон</span><br/><br/>
						<span style="font-size:14px;">И заходите в М2 PROFI<br/> как в приложение.</span>
					</div>
					<div style="display:table-cell; text-align:right; ">
					<img src="/l2.png" width="100" />
					</div>
				</div>
			</div>
			
			
<?
		object_menu();		
?>

 
			
			
			
			
		<div class="objects">
			<div class="row">
				<?
				foreach($h as $k=>$v)
				{
					
					if(isset($_GET['sdan']))
					{
						if($_GET['sdan']){if($v['complite']=="0"){continue;}}
						else{if($v['complite']=="1"){continue;}}
					}
				?>
				
				<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
					<div class="object">
						<div class="object__title"><?=$v['long_title']?></div>
						<div class="object__pict">
							<img src="render/<?=$v['home_id']?>.jpg" alt="">
							<div class="object__info">
							<?
							// Не выводим адрес!
							if($v['adress333'])
							{
								?>
								<div class="object__location"><?=$v['adress']?></div>
								<?
							}
							?>
							<div class="object__status object__status_sale"><?=$v['complite_text']?></div>
							</div>
						</div>
						<?
						if ($showAdminServicePanel) {
							$homeId = (int)$v['home_id'];
							$service = $homeServiceStats[$homeId] ?? null;
							if ($service && !empty($service['rooms'])) {
								$homeFreePercent = (float)$service['percent'];
								$homeBookedPercent = (float)$service['booked_percent'];
								$homeSoldPercent = (float)$service['sold_percent'];
								$homeProgressWidth = max(0, min(100, $homeSoldPercent));
								$homeProgressColor = 'rgba(231, 167, 167, 0.18)';
								?>
								<div class="admin-home-stats" style="background:#fff; border:1px solid #dfe6ea; border-radius:10px; margin-top:10px; padding:0 0 10px 0; font-size:9px; line-height:2em;">
									<div style="position:relative; margin-bottom:6px; border:1px solid #dfe6ea; border-radius:8px; padding:2px 6px; overflow:hidden; background:#fcfaf4;">
										<div style="position:absolute; left:0; top:0; bottom:0; width:<?= number_format($homeProgressWidth, 2, '.', '') ?>%; background:<?= $homeProgressColor ?>;"></div>
										<div style="position:relative; z-index:1; font-weight:600; <?= ($homeFreePercent < $bgThreshold) ? 'color:#b33a3a;' : '' ?>">
											<b><?= (int)$service['total'] ?></b> / Свободно: <b><?= (int)$service['free'] ?></b><? if ($homeFreePercent > 0): ?> (<b><?= number_format($homeFreePercent, 2, ',', '') ?>%</b>)<? endif; ?> / Бронь: <b><?= (int)$service['booked'] ?></b><? if ($homeBookedPercent > 0): ?> (<b><?= number_format($homeBookedPercent, 2, ',', '') ?>%</b>)<? endif; ?>
										</div>
									</div>
									<div style="padding:0 8px;" class="admin-home-rooms">
										<? foreach ($service['rooms'] as $rooms => $roomStat) { ?>
											<? $roomFreePercent = (float)$roomStat['percent']; ?>
											<? $roomBookedPercent = (float)$roomStat['booked_percent']; ?>
											<? $isZeroFree = ((int)$roomStat['free'] === 0); ?>
											<? $isZeroBooked = ((int)$roomStat['booked'] === 0); ?>
											<? $isManualRoom = !empty($roomStat['is_manual_mode']); ?>
											<?
											$manualTooltip = 'Осталось менее '
												. rtrim(rtrim(number_format($bgThreshold, 2, '.', ''), '0'), '.')
												. '% свободных квартир данного типа в доме';
											?>
											<div class="admin-room-row<?= $isManualRoom ? ' admin-room-row--manual' : '' ?>"
												style="<?= $isManualRoom ? 'background:#f8d7da; border-radius:4px; padding:1px 4px; margin:1px 0;' : '' ?>">
												<b><?= htmlspecialchars((string)$rooms, ENT_QUOTES, 'UTF-8') ?></b>
												<? if ($isManualRoom): ?>
													<span style="color:#b33a3a; font-weight:700; cursor:help;" title="<?= htmlspecialchars($manualTooltip, ENT_QUOTES, 'UTF-8') ?>">ручное</span>
												<? else: ?>
													<span style="color:#888;">авто</span>
												<? endif; ?>: - <b><?= (int)$roomStat['total'] ?></b> /
												<span style="<?= $isZeroFree ? 'color:#CCC;' : '' ?>">Свободно: <b><?= (int)$roomStat['free'] ?></b><? if ($roomFreePercent > 0): ?> (<b><?= number_format($roomFreePercent, 2, ',', '') ?>%</b>)<? endif; ?></span> /
												<span style="<?= $isZeroBooked ? 'color:#CCC;' : '' ?>">Бронь: <b><?= (int)$roomStat['booked'] ?></b><? if ($roomBookedPercent > 0): ?> (<b><?= number_format($roomBookedPercent, 2, ',', '') ?>%</b>)<? endif; ?></span>
											</div>
										<? } ?>
									</div>
								</div>
								<?
							}
						}
						?>
						<a href="user.php?action=objects&home=<?=$v['home_id']?>&sdan=<?=$v['complite']?>" class="object__btn btn btn_arrow">К объекту<i></i></a>
					</div>
				</div>
					 <?
				}
				
				
				if($_GET['sdan'])
				{
					foreach($custom_apparts_all as $k=>$v)
					{
					?>
					<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
						<div class="object">
						<div class="object__title"><?=$v['homecaption']?></div>
						<div class="object__pict" style="border: solid 1px #EEE; text-align:center;">
							<img src="<?=$v['image_pb']?>" alt="" style="width:auto; display:inline-block;">
							<div class="object__info">
								<div class="object__status object__status_sale">сдан</div>
							</div>
						</div>
						<a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="iframe object__btn btn btn_arrow">К объекту<i></i></a>
						</div>
					</div>
					<?
					}
				}
					
					
					
				?> 

				
			</div>
		<a href="/sahmatka/yandex_feedx.php">XML Фид в формате Yandex</a>
		</div>
	</div>
</section>

<script>
(function() {
	function syncAdminStatsHeight() {
		var blocks = document.querySelectorAll('.admin-home-stats');
		if (!blocks.length) return;
		var maxH = 0;
		blocks.forEach(function(el) {
			el.style.minHeight = '';
			if (el.offsetHeight > maxH) maxH = el.offsetHeight;
		});
		blocks.forEach(function(el) { el.style.minHeight = maxH + 'px'; });
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', syncAdminStatsHeight);
	} else {
		syncAdminStatsHeight();
	}
	window.addEventListener('resize', syncAdminStatsHeight);
})();
</script>




		
				 
