<?php include __DIR__ . '/_layout_assets.php'; ?>
<?php
$compred = $data['compred'] ?? [];
$objects = $data['objects'] ?? [];
$intro_text = trim((string)($compred['intro_text'] ?? ''));
?>
<div class="cp-edit container mobc">
    <div class="cp-edit-header">
        <h1 class="cp-edit-header__title"><?= htmlspecialchars($compred['caption'] ?? '') ?></h1>
        <?php if ($intro_text !== ''): ?>
        <div class="cp-public__intro cp-public__intro--view"><?= nl2br(htmlspecialchars($intro_text)) ?></div>
        <?php endif; ?>
        <p class="cp-edit-header__meta">Всего объектов: <?= count($objects) ?></p>
    </div>
    <?php if (empty($objects)): ?>
        <div class="cp-empty">В этом предложении пока нет объектов.</div>
    <?php else: ?>
        <div class="cp-public__list">
            <?php include __DIR__ . '/_objects_grouped.php'; ?>
        </div>
    <?php endif; ?>
</div>
