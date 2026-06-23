<?php include __DIR__ . '/_layout_assets.php'; ?>
<div class="cp-edit cp-index-page container mobc">
    <?php if (empty($data['list'])): ?>
        <div class="cp-empty cp-empty--index">
            <h2 class="cp-empty__title">Пока нет предложений для клиентов</h2>
            <p class="cp-empty__lead">
                <?= unit_template('compred_index_intro') ?>
            </p>
            <ol class="cp-empty__steps">
                <li><?= unit_template('compred_index_step_open') ?></li>
                <li><?= unit_template('compred_index_step_card') ?></li>
                <li><?= unit_template('compred_index_step_more') ?></li>
                <li>Нажмите «Поделиться предложением» и отправьте ссылку клиенту.</li>
            </ol>
            <p class="cp-empty__note">
                Клиент увидит аккуратную страницу с планировками, ценами и вашими пояснениями — без доступа в кабинет.
            </p>
            <a href="/sahmatka/user.php?action=objects" class="cp-btn">Перейти к объектам</a>
        </div>
    <?php else: ?>
        <p class="cp-index-page__intro">
            <?= unit_phrase('compred_index_list_hint') ?>
        </p>
        <div class="cp-index-list">
            <?php foreach ($data['list'] as $item):
                $cid = (int)$item['compred_id'];
                $caption = (string)($item['caption'] ?? '');
                $updated = (string)($item['updated_at'] ?? '');
                if ($updated !== '') {
                    $updated = date('d.m.Y H:i', strtotime($updated));
                }
            ?>
            <div class="cp-index-item">
                <div class="cp-index-item__body">
                    <a href="/sahmatka/ctrind.php?ctr=compred&act=edit&id=<?= $cid ?>" class="cp-index-item__title">
                        <?= htmlspecialchars($caption) ?>
                    </a>
                    <div class="cp-index-item__meta">
                        <?= unit_phrase('compred_index_count_label') ?> <?= (int)($item['obj_count'] ?? 0) ?>
                        <?php if ($updated !== ''): ?> · обновлено <?= htmlspecialchars($updated) ?><?php endif; ?>
                    </div>
                </div>
                <div class="cp-index-item__actions">
                    <a href="/sahmatka/ctrind.php?ctr=compred&act=edit&id=<?= $cid ?>" class="cp-btn">Открыть</a>
                    <button type="button" class="cp-btn cp-btn--danger cp-index-delete"
                            data-compred-id="<?= $cid ?>"
                            data-compred-caption="<?= htmlspecialchars($caption) ?>">Удалить</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script src="/sahmatka/template/default/js/compred.js?v=9"></script>
<script>jQuery(function () { cpInitIndex(); });</script>
