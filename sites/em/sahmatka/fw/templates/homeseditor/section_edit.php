<?php
global $filed;

$back_url = $data['back_url'];
$form_url = $data['form_url'];
$section = $data['section'];
$homes_sections_id = (int) ($data['homes_sections_id'] ?? 0);
$floor_max = (int) ($data['floor_max'] ?? 0);
$apartments = (int) ($data['apartments'] ?? 0);
$clean = $data['clean_apartments'] ?? [];
$cell_nums = $data['cell_nums'] ?? [];
$message = $data['message'] ?? '';
$is_new = !empty($data['is_new']);
$show_grid = ($homes_sections_id && $floor_max > 0 && $apartments > 0);
?>
<style>
.he-cl-table { border-collapse: collapse; margin-top: 16px; width: auto; }
.he-cl-table td, .he-cl-table th { border: 1px solid #ccc; padding: 6px 10px; text-align: center; min-width: 48px; }
.he-cl-table td:first-child { font-weight: bold; background: #f5f7f8; }
.he-cl-empty { background: #ffebee; }
.he-cl-num { display: block; font-weight: bold; font-size: 14px; margin-bottom: 4px; }
.he-cl-empty .he-cl-num { color: #999; text-decoration: line-through; }
</style>

<form action="<?= htmlspecialchars($form_url) ?>" method="POST" id="editform">
    <input type="hidden" name="save_section" value="1">
    <?php if ($show_grid): ?>
        <input type="hidden" name="has_cl_grid" value="1">
    <?php endif; ?>

    <?php
    $this->forminform = $message;
    $this->formpanel($back_url);
    ?>

    <div class="row">
        <div class="col-md-6">
            <?= $filed->text('section_id', '№ секции', $section['section_id'] ?? '', $is_new ? '' : 'readonly') ?><br/>
            <?= $filed->text('caption', 'Подпись', $section['caption'] ?? '') ?><br/>
            <?= $filed->text('floor', 'Этажей в секции', $section['floor'] ?? '') ?><br/>
        </div>
        <div class="col-md-6">
            <?= $filed->text('apartments', 'Квартир на этаже', $section['apartments'] ?? '') ?><br/>
            <?= $filed->text('start_num', 'Стартовый № (start_num)', $section['start_num'] ?? '0') ?><br/>
            <span style="font-size:12px;color:#666;">Смещение нумерации: для 1-й секции обычно 0, для следующей — конец предыдущей (заполняется автоматически).</span>
        </div>
    </div>

    <?php if ($show_grid): ?>
        <h2>Пустые клетки (отсутствующие квартиры)</h2>
        <p>Отметьте клетки без квартир. Номера — как на шахматке (после сохранения галок нумерация пересчитается).</p>
        <table class="objects-table he-cl-table">
            <thead>
            <tr>
                <td>Этаж</td>
                <?php for ($k = 1; $k <= $apartments; $k++): ?>
                    <td><?= $k ?></td>
                <?php endfor; ?>
            </tr>
            </thead>
            <tbody>
            <?php for ($i = $floor_max; $i >= 1; $i--): ?>
                <tr>
                    <td><?= $i ?></td>
                    <?php for ($k = 1; $k <= $apartments; $k++):
                        $checked = !empty($clean[$i][$k]);
                        $num = isset($cell_nums[$i][$k]) ? (int) $cell_nums[$i][$k] : null;
                        ?>
                        <td class="<?= $checked ? 'he-cl-empty' : '' ?>">
                            <?php if ($num !== null): ?>
                                <span class="he-cl-num">№<?= $num ?></span>
                            <?php elseif ($checked): ?>
                                <span class="he-cl-num">—</span>
                            <?php endif; ?>
                            <label>
                                <input type="checkbox" name="cl[<?= $i ?>][<?= $k ?>]" value="1" <?= $checked ? 'checked="checked"' : '' ?>>
                                пусто
                            </label>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    <?php elseif (!$is_new): ?>
        <p style="color:#666;">Сохраните параметры секции — затем появится сетка отсутствующих квартир.</p>
    <?php endif; ?>
</form>
