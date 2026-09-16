<?php
$back_url = $data['back_url'];
$parse_url = $data['parse_url'];
$approve_url = $data['approve_url'];
$home_title = $data['home_title'];
$raw_text = $data['raw_text'];
$preview = $data['preview'];
$columns = $data['columns'];
$token = $data['token'];
$green_count = (int) $data['green_count'];
$message = $data['message'];
$report = $data['report'];

$this->forminform = htmlspecialchars((string) $home_title) . ' (home_id=' . (int) $data['home_id'] . ')';
if ($message) {
    $this->forminform = htmlspecialchars($message);
}
?>
<style>
.hi-preview-table tr.hi-ok { background: #e8f5e9; }
.hi-preview-table tr.hi-err { background: #ffebee; }
.hi-raw { width: 100%; min-height: 220px; font-family: monospace; font-size: 12px; }
.hi-report { margin: 12px 0; }
.hi-report-errors { color: #b00020; }
.hi-report-warn { color: #c45c00; }
</style>

<form action="<?= htmlspecialchars($parse_url) ?>" method="POST" id="editform">
    <input type="hidden" name="do" value="parse">
    <?php $this->formpanel($back_url); ?>

    <p>Формат: 7 колонок через таб — этаж, комнаты, секция, № квартиры, площадь, площадь по договору, цена.</p>

    <?php if ($report): ?>
        <div class="hi-report">
            <p><strong>Импорт выполнен</strong></p>
            <ul>
                <li>Квартир добавлено: <?= (int) $report['apartments'] ?></li>
                <li>Новых секций: <?= (int) $report['sections'] ?></li>
                <?php if (!empty($report['holes'])): ?>
                    <li>Пустых клеток на шахматке: <?= (int) $report['holes'] ?></li>
                <?php endif; ?>
                <li>Этажей дома обновлено: <?= !empty($report['floor_updated']) ? 'да' : 'нет' ?></li>
            </ul>
            <?php if (!empty($report['errors'])): ?>
                <p class="hi-report-errors"><?= htmlspecialchars(implode('; ', $report['errors'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($report['warnings'])): ?>
                <p class="hi-report-warn"><?= htmlspecialchars(implode('; ', $report['warnings'])) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <label for="raw_text"><strong>Данные TSV</strong></label><br/>
    <textarea name="raw_text" id="raw_text" class="hi-raw input_edit"><?= htmlspecialchars($raw_text) ?></textarea>
</form>

<?php if (is_array($preview) && $preview): ?>
    <div class="stat">
        <div class="stat-table stat-table-user table">
            <table class="dtable hi-preview-table">
                <thead>
                <tr class="dtable">
                    <th class="dtable">Строка</th>
                    <?php foreach ($columns as $label): ?>
                        <th class="dtable"><?= htmlspecialchars($label) ?></th>
                    <?php endforeach; ?>
                    <th class="dtable">Ошибки</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($preview as $row): ?>
                    <tr class="<?= !empty($row['ok']) ? 'hi-ok' : 'hi-err' ?>">
                        <td><?= (int) $row['line'] ?></td>
                        <?php if (!empty($row['normalized'])): ?>
                            <?php $n = $row['normalized']; ?>
                            <td><?= (int) $n['floor'] ?></td>
                            <td><?= htmlspecialchars($n['rooms']) ?></td>
                            <td><?= (int) $n['section_id'] ?></td>
                            <td><?= (int) $n['apartment_num'] ?></td>
                            <td><?= htmlspecialchars($n['area']) ?></td>
                            <td><?= htmlspecialchars($n['area_small']) ?></td>
                            <td><?= (int) $n['price'] ?></td>
                        <?php elseif (!empty($row['cells'])): ?>
                            <?php foreach ($row['cells'] as $cell): ?>
                                <td><?= htmlspecialchars($cell) ?></td>
                            <?php endforeach; ?>
                            <?php for ($i = count($row['cells']); $i < 7; $i++): ?>
                                <td></td>
                            <?php endfor; ?>
                        <?php else: ?>
                            <?php for ($i = 0; $i < 7; $i++): ?>
                                <td></td>
                            <?php endfor; ?>
                        <?php endif; ?>
                        <td><?= htmlspecialchars(implode('; ', (array) ($row['errors'] ?? []))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($green_count > 0): ?>
        <form action="<?= htmlspecialchars($approve_url) ?>" method="POST" id="approveform">
            <input type="hidden" name="do" value="approve">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <p class="forminform">Зелёных строк: <strong><?= $green_count ?></strong></p>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6"></div>
                <div class="col-md-3" style="text-align:right;">
                    <a href="#" onclick="document.getElementById('approveform').submit(); return false;"
                       class="forminformpanel_link forminformpanel_link_save" style="text-align:right;">Сохранить</a>
                </div>
            </div>
            <br/>
            <hr/>
        </form>
    <?php endif; ?>
<?php endif; ?>
