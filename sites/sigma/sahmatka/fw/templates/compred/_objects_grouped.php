<?php
/**
 * Список объектов предложения (без группировки по микрорайону/дому).
 * $groups — из compred_group_objects() или $objects — плоский массив.
 * $mode — public | edit | view
 */
$groups = $groups ?? ($data['groups'] ?? []);
$objects = $objects ?? ($data['objects'] ?? []);
$mode = $mode ?? ($data['mode'] ?? 'public');
?>
<?php if (!empty($objects)): ?>
    <?php foreach ($objects as $compred_obj): ?>
        <?php include __DIR__ . '/_card_apartment.php'; ?>
    <?php endforeach; ?>
<?php else: ?>
    <?php foreach ($groups as $kg): ?>
        <?php foreach ($kg['homes'] as $hg): ?>
            <?php foreach ($hg['apartments'] as $compred_obj): ?>
                <?php include __DIR__ . '/_card_apartment.php'; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>
