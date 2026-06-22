<?php
$compred_list = $data['compred_list'] ?? [];
$apartament_id = (int)($data['apartament_id'] ?? 0);
$return_url = $data['return_url'] ?? '';
$compred_msg = $data['compred_msg'] ?? '';
$compred_err = $data['compred_err'] ?? '';
$compred_selected_id = (int)($data['compred_selected_id'] ?? 0);
if (!$apartament_id) {
    return;
}
?>
<link rel="stylesheet" href="/sahmatka/template/default/css/compred.css?v=3">
<div class="cp-block" id="cp-block">
    <?php if ($compred_msg): ?><div class="alert alert-success"><?= htmlspecialchars($compred_msg) ?></div><?php endif; ?>
    <?php if ($compred_err): ?><div class="alert alert-danger"><?= htmlspecialchars($compred_err) ?></div><?php endif; ?>

    <div class="cp-block__title">Добавить к предложению</div>

    <form method="post" action="/sahmatka/ajax_router.php?ctr=compred&act=add_item" id="cp-add-form">
        <input type="hidden" name="obj_type" value="apartment">
        <input type="hidden" name="obj_id" value="<?= $apartament_id ?>">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">

        <label for="cp-select-proposal">Предложение</label>
        <select name="compred_id" id="cp-select-proposal" class="cp-block__select">
            <option value="">— Создать новое —</option>
            <?php foreach ($compred_list as $c):
                $cid = (int)$c['compred_id'];
                $sel = ($compred_selected_id === $cid) ? ' selected' : '';
            ?>
            <option value="<?= $cid ?>"<?= $sel ?>><?= htmlspecialchars($c['caption']) ?></option>
            <?php endforeach; ?>
        </select>

        <div id="cp-new-caption-wrap" class="cp-block__new-caption">
            <label for="cp-caption-new">Название нового предложения</label>
            <input type="text" name="caption_new" id="cp-caption-new" maxlength="255"
                   placeholder="Например: Подборка для Ивановых" autocomplete="off">
        </div>

        <label for="cp-comment">Примечание (необязательно)</label>
        <textarea name="comment" id="cp-comment" rows="2" placeholder="Примечание для клиента"></textarea>

        <div class="cp-block__actions">
            <button type="submit" class="cp-btn" id="cp-submit-btn">Добавить к предложению</button>
            <a href="/sahmatka/ctrind.php?ctr=compred&act=edit&id="
               target="_blank" rel="noopener" id="cp-view-link" class="cp-btn cp-block__view-link">
                Смотреть предложение
            </a>
        </div>
    </form>
</div>
<script src="/sahmatka/template/default/js/compred.js?v=9"></script>
<script>cpInitBlock();</script>
