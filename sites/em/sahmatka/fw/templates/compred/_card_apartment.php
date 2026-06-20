<?php
/**
 * Partial: карточка квартиры в предложении (только данные квартиры).
 */
$compred_obj = $compred_obj ?? ($obj ?? []);
$mode = $mode ?? ($data['mode'] ?? 'public');

if (empty($compred_obj['apartment'])) {
    return;
}

$card = compred_apartment_card_vars($compred_obj);

if ($mode === 'edit'):
?>
<div class="cp-card cp-card--edit" data-compred-obj-id="<?= (int)$card['co_id'] ?>">
    <div class="cp-card__inner">
        <div class="cp-card__row">
            <div class="cp-card__media">
                <img src="<?= htmlspecialchars($card['img']) ?>" alt="<?= htmlspecialchars($card['img_alt']) ?>">
            </div>
            <div class="cp-card__content">
                <?php include __DIR__ . '/_card_apartment_body.php'; ?>
                <div class="cp-card__edit">
                    <label>Примечание:</label>
                    <textarea class="cp-note-input" data-compred-obj-id="<?= (int)$card['co_id'] ?>"
                              placeholder="Напишите здесь детали для клиента..."><?= htmlspecialchars($card['comment']) ?></textarea>
                    <div class="cp-note-status" style="display:none;">Сохранено</div>
                    <button type="button" class="cp-btn-remove cp-remove-object" data-compred-obj-id="<?= (int)$card['co_id'] ?>">
                        Удалить из списка
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="cp-card cp-card--public">
    <div class="cp-card__inner">
        <div class="cp-card__row">
            <div class="cp-card__media">
                <img src="<?= htmlspecialchars($card['img']) ?>" alt="<?= htmlspecialchars($card['img_alt']) ?>">
            </div>
            <div class="cp-card__content">
                <?php include __DIR__ . '/_card_apartment_body.php'; ?>
                <?php if ($card['comment'] !== ''): ?>
                <div class="cp-card__comment">
                    <div class="cp-note-box">
                        <strong>Примечание:</strong><br>
                        <?= nl2br(htmlspecialchars($card['comment'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
