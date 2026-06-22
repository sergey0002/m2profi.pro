<?php
/**
 * Блок ссылки и кнопок «Поделиться».
 * Ожидает: $public_url, $share_links, опционально $share_caption, $share_panel_mode.
 */
$public_url = (string)($public_url ?? '');
$share_links = $share_links ?? compred_share_links($public_url, (string)($share_caption ?? ''));
$share_caption = (string)($share_caption ?? '');
$share_panel_mode = (string)($share_panel_mode ?? 'edit');
?>
<?php if ($share_panel_mode === 'edit'): ?>
<div class="cp-rename cp-share-link-row">
    <span class="cp-share-link-row__label">Ссылка для клиента:</span>
    <input type="text" id="cp-permalink-result" class="form-control cp-share-link-row__input" readonly
           value="<?= htmlspecialchars($public_url) ?>">
    <div class="cp-share-link-row__actions">
        <button type="button" class="cp-btn" id="cp-copy-link">Копировать</button>
        <a href="<?= htmlspecialchars($public_url) ?>" class="cp-btn cp-btn--outline" id="cp-view-link" target="_blank" rel="noopener noreferrer">Смотреть</a>
        <button type="button" class="cp-btn cp-btn--outline" id="cp-share-toggle" aria-expanded="false" aria-controls="cp-share-menu">Поделиться</button>
    </div>
    <div class="cp-share-menu" id="cp-share-menu" hidden>
        <a href="<?= htmlspecialchars($share_links['telegram']) ?>" class="cp-share-btn cp-share-btn--tg" target="_blank" rel="noopener noreferrer">Telegram</a>
        <a href="<?= htmlspecialchars($share_links['whatsapp']) ?>" class="cp-share-btn cp-share-btn--wa" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        <a href="<?= htmlspecialchars($share_links['max']) ?>" class="cp-share-btn cp-share-btn--max" target="_blank" rel="noopener noreferrer">MAX</a>
        <a href="<?= htmlspecialchars($share_links['vk']) ?>" class="cp-share-btn cp-share-btn--vk" target="_blank" rel="noopener noreferrer">ВКонтакте</a>
        <a href="<?= htmlspecialchars($share_links['ok']) ?>" class="cp-share-btn cp-share-btn--ok" target="_blank" rel="noopener noreferrer">Одноклассники</a>
    </div>
</div>
<?php else: ?>
<div class="cp-share-panel cp-share-panel--public" id="cp-share-panel">
    <p class="cp-share-panel__lead">Поделитесь этой подборкой в мессенджерах или соцсетях.</p>
    <div class="cp-share-panel__link-row">
        <input type="text" id="cp-permalink-result" class="form-control cp-share-panel__input" readonly
               value="<?= htmlspecialchars($public_url) ?>">
        <button type="button" class="cp-btn" id="cp-copy-link">Копировать</button>
        <a href="<?= htmlspecialchars($public_url) ?>" class="cp-btn cp-btn--outline" target="_blank" rel="noopener noreferrer">Смотреть</a>
    </div>
    <div class="cp-share-panel__social">
        <span class="cp-share-panel__social-label">Поделиться:</span>
        <div class="cp-share-panel__buttons">
            <a href="<?= htmlspecialchars($share_links['telegram']) ?>" class="cp-share-btn cp-share-btn--tg" target="_blank" rel="noopener noreferrer">Telegram</a>
            <a href="<?= htmlspecialchars($share_links['whatsapp']) ?>" class="cp-share-btn cp-share-btn--wa" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            <a href="<?= htmlspecialchars($share_links['max']) ?>" class="cp-share-btn cp-share-btn--max" target="_blank" rel="noopener noreferrer">MAX</a>
            <a href="<?= htmlspecialchars($share_links['vk']) ?>" class="cp-share-btn cp-share-btn--vk" target="_blank" rel="noopener noreferrer">ВКонтакте</a>
            <a href="<?= htmlspecialchars($share_links['ok']) ?>" class="cp-share-btn cp-share-btn--ok" target="_blank" rel="noopener noreferrer">Одноклассники</a>
        </div>
    </div>
</div>
<?php endif; ?>
