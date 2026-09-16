<?php
$back_url = $data['back_url'];
$add_url = $data['add_url'];
$sections = $data['sections'];
$home_title = (string) ($data['home_title'] ?? '');
?>
<div class="stat">
    <div class="stat-top">
        <form method="GET" action="" id="filtrform" style="width:100%;">
            <style>.admfiltr *{display:inline-block;}</style>
            <div>
                <div class="admfiltr" style="display:table-cell; width: 100%;">
                    <a href="<?= htmlspecialchars($back_url) ?>">Назад</a>
                    <?php if ($home_title !== ''): ?>
                        <span style="margin-left:12px;"><?= htmlspecialchars($home_title) ?></span>
                    <?php endif; ?>
                </div>
                <div style="display:table-cell; vertical-align:top;">
                    <div class="filter-item filter-item_print">
                        <a href="<?= htmlspecialchars($add_url) ?>" class="filter-item-icon" title="Добавить секцию">
                            <img src="/sahmatka/template/default/images/add.svg" width="32" alt="Добавить"/>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="stat-table stat-table-user stat-table_notpd table">
        <?php if (!$sections): ?>
            <p>Секций пока нет.</p>
        <?php else: ?>
            <table class="dtable" id="fwcrudtable">
                <thead>
                <tr class="dtable">
                    <th class="dtable">№ секции</th>
                    <th class="dtable">Подпись</th>
                    <th class="dtable">Этажей</th>
                    <th class="dtable">Кв/этаж</th>
                    <th class="dtable">start_num</th>
                    <th class="dtable">Редактирование</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sections as $sec): ?>
                    <tr class="dtable_ch">
                        <td><?= (int) $sec['section_id'] ?></td>
                        <td><?= htmlspecialchars($sec['caption'] ?? '') ?></td>
                        <td><?= (int) ($sec['floor'] ?? 0) ?></td>
                        <td><?= (int) ($sec['apartments'] ?? 0) ?></td>
                        <td><?= (int) ($sec['start_num'] ?? 0) ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($sec['edit_url']) ?>" class="table-edit" title="Редактировать"></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
