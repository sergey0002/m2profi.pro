<?php
/**
 * Список объектов, сгруппированный: микрорайон → дом → квартиры.
 * $groups — из compred_group_objects()
 * $mode — public | edit | view
 */
$groups = $groups ?? ($data['groups'] ?? []);
$mode = $mode ?? ($data['mode'] ?? 'public');
?>
<?php foreach ($groups as $kg): ?>
<section class="cp-group cp-group--kvartal">
    <?php if (!empty($kg['kvartal_title'])): ?>
    <header class="cp-group__header cp-group__header--kvartal">
        <div class="cp-group__label">Микрорайон</div>
        <h2 class="cp-group__title"><?= htmlspecialchars($kg['kvartal_title']) ?></h2>
        <?php if (!empty($kg['kvartal_description'])): ?>
        <div class="cp-group__desc"><?= nl2br(htmlspecialchars($kg['kvartal_description'])) ?></div>
        <?php endif; ?>
    </header>
    <?php endif; ?>

    <?php foreach ($kg['homes'] as $hg):
        $home_id = (int)($hg['home_id'] ?? 0);
        $home_url = compred_build_home_url($home_id);
    ?>
    <div class="cp-home-block">
        <header class="cp-group__header cp-group__header--home">
            <div class="cp-group__label">Дом</div>
            <h3 class="cp-group__title cp-group__title--home">
                <?php if ($mode !== 'public' && $home_url !== ''): ?>
                <a href="<?= htmlspecialchars($home_url) ?>" class="cp-home-link" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($hg['home_name']) ?></a>
                <?php else: ?>
                <?= htmlspecialchars($hg['home_name']) ?>
                <?php endif; ?>
            </h3>
            <?php if ($hg['home_short'] !== '' && $hg['home_short'] !== $hg['home_name']): ?>
            <div class="cp-group__detail">
                <span class="cp-group__detail-label">Корпус:</span>
                <?= htmlspecialchars($hg['home_short']) ?>
            </div>
            <?php endif; ?>
            <?php foreach ($hg['home_details'] as $detail): ?>
            <div class="cp-group__detail">
                <span class="cp-group__detail-label"><?= htmlspecialchars($detail[0]) ?>:</span>
                <?= htmlspecialchars($detail[1]) ?>
            </div>
            <?php endforeach; ?>
            <?php if ($hg['home_description'] !== ''): ?>
            <div class="cp-group__desc"><?= nl2br(htmlspecialchars($hg['home_description'])) ?></div>
            <?php endif; ?>
        </header>

        <div class="cp-group__apartments">
            <?php foreach ($hg['apartments'] as $compred_obj): ?>
                <?php include __DIR__ . '/_card_apartment.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>
<?php endforeach; ?>
