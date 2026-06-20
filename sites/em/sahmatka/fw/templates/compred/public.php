<?php include __DIR__ . '/_layout_assets.php'; ?>
<?php
$compred = $data['compred'] ?? [];
$intro_text = trim((string)($compred['intro_text'] ?? ''));
?>
<div class="cp-public">
    <h1 class="cp-public__title"><?= htmlspecialchars($compred['caption'] ?? '') ?></h1>
    <?php if ($intro_text !== ''): ?>
    <div class="cp-public__intro"><?= nl2br(htmlspecialchars($intro_text)) ?></div>
    <?php endif; ?>
    <?php if (empty($data['objects'])): ?>
        <div class="cp-empty">В этом предложении пока нет объектов.</div>
    <?php else: ?>
        <div class="cp-public__list">
            <?php include __DIR__ . '/_objects_grouped.php'; ?>
        </div>
    <?php endif; ?>
</div>
