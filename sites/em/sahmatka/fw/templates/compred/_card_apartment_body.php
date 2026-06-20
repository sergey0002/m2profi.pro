<?php
/**
 * Текстовый блок карточки — только параметры квартиры.
 * Ожидает $card из _card_apartment.php.
 */
?>
<div class="cp-card__body">
    <div class="cp-card__block cp-card__block--apt">
        <div class="cp-card__apt-line"><?= htmlspecialchars($card['apt_line']) ?></div>
        <div class="cp-card__price"><?= $card['price'] ?></div>
        <div class="cp-card__specs"><?= htmlspecialchars($card['specs']) ?></div>
    </div>
</div>
