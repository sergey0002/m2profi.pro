<?php include __DIR__ . '/_layout_assets.php'; ?>
<?php
$compred = $data['compred'] ?? [];
$objects = $data['objects'] ?? [];
$cid = (int)($compred['compred_id'] ?? 0);
$intro_text = (string)($compred['intro_text'] ?? '');
$public_url = (string)($data['public_url'] ?? '');
$share_links = $data['share_links'] ?? compred_share_links($public_url, (string)($compred['caption'] ?? ''));
$share_caption = (string)($compred['caption'] ?? '');
?>
<div class="cp-edit cp-edit-page container mobc" id="cp-edit-page" data-compred-id="<?= $cid ?>">
    <p class="cp-edit-page__back">
        <a href="/sahmatka/ctrind.php?ctr=compred&act=index">← Все предложения</a>
    </p>

    <div class="cp-edit-header">
        <div class="cp-edit-header__row">
            <div class="cp-edit-header__main">
                <h1 class="cp-edit-header__title" id="cp-main-title"><?= htmlspecialchars($compred['caption'] ?? '') ?></h1>
                <p class="cp-edit-header__meta">
                    Квартир в подборке: <span id="cp-objects-count" style="font-weight:700;color:var(--cp-accent);"><?= count($objects) ?></span>
                </p>
            </div>
            <div class="cp-edit-actions">
                <span class="cp-details-edit__status" id="cp-details-status" style="display:none;">Сохранено</span>
                <button type="button" class="cp-btn" id="cp-save-details" data-compred-id="<?= $cid ?>">Сохранить</button>
                <button type="button" class="cp-btn cp-btn--danger" id="cp-delete-proposal" data-compred-id="<?= $cid ?>">Удалить предложение</button>
            </div>
        </div>
        <?php include __DIR__ . '/_share_panel.php'; ?>
        <div class="cp-details-edit">
            <div class="cp-details-field">
                <label for="cp-edit-caption" class="cp-details-edit__label">Название предложения:</label>
                <input type="text" class="form-control cp-details-edit__input" id="cp-edit-caption" value="<?= htmlspecialchars($compred['caption'] ?? '') ?>"
                       placeholder="Например: Подборка для семьи Ивановых">
            </div>
            <div class="cp-intro-edit">
                <label for="cp-edit-intro" class="cp-intro-edit__label">Вводный текст для клиента</label>
                <p class="cp-intro-edit__hint">Появится под названием на публичной странице. Можно написать приветствие, условия или краткое описание подборки.</p>
                <textarea id="cp-edit-intro" class="cp-intro-edit__input" rows="4"
                          placeholder="Например: Добрый день! Подготовили для вас подборку квартир с учётом ваших пожеланий…"><?= htmlspecialchars($intro_text) ?></textarea>
            </div>
        </div>
    </div>

    <?php if (!empty($objects)): ?>
    <div class="cp-public__list cp-edit-page__list">
        <?php include __DIR__ . '/_objects_grouped.php'; ?>
    </div>
    <?php else: ?>
    <div class="cp-empty">
        <h2 class="cp-empty__title">В этом предложении пока нет квартир</h2>
        <p class="cp-empty__lead">
            Откройте квартиру на шахматке и нажмите «Добавить к предложению», выбрав эту подборку в списке.
        </p>
        <a href="/sahmatka/user.php?action=objects" class="cp-btn">Перейти к объектам</a>
    </div>
    <?php endif; ?>
</div>
<script src="/sahmatka/template/default/js/compred.js?v=8"></script>
<script>jQuery(function () { cpInitEdit(<?= $cid ?>); });</script>
