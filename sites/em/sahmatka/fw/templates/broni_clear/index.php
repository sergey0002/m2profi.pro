<?php
$filters = $data['filters'] ?? [];
$options = $data['options'] ?? [];
$rows = $data['rows'] ?? [];
$flash = $data['flash'] ?? '';
$truncated = !empty($data['truncated']);
$limit = (int)($data['limit'] ?? 500);
$guardMode = $filters['guard_mode'] ?? 'all';
?>
<style>
.broni-clear-wrap { margin: 16px 0 40px; }
.broni-clear-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:16px; }
.broni-clear-filters label { display:block; font-size:12px; font-weight:600; margin-bottom:4px; }
.broni-clear-filters select { min-width:160px; padding:6px 8px; }
.broni-clear-table { width:100%; border-collapse:collapse; font-size:14px; }
.broni-clear-table th, .broni-clear-table td { border:1px solid #ddd; padding:8px 10px; text-align:left; }
.broni-clear-table th { background:#f5f5f5; }
.broni-clear-table tr:hover { background:#f8fbfd; }
.badge-manual { color:#b33a3a; font-weight:700; }
.badge-auto { color:#666; }
.broni-clear-actions { margin:14px 0; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.broni-clear-flash { background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:10px 12px; border-radius:6px; margin-bottom:14px; }
.broni-clear-warn { background:#fff3cd; border:1px solid #ffc107; color:#856404; padding:10px 12px; border-radius:6px; margin-bottom:14px; }
</style>

<div class="broni-clear-wrap">
    <h1>Снятие броней</h1>

    <?php if ($flash): ?>
        <div class="broni-clear-flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($truncated): ?>
        <div class="broni-clear-warn">Показаны первые <?= (int)$limit ?>; уточните фильтр.</div>
    <?php endif; ?>

    <form method="get" action="/sahmatka/ctrind.php" class="broni-clear-filters">
        <input type="hidden" name="ctr" value="broni_clear">
        <input type="hidden" name="act" value="index">

        <div>
            <label>Дом</label>
            <select name="home_id">
                <option value="">Все</option>
                <?php foreach (($options['homes'] ?? []) as $h): ?>
                    <option value="<?= (int)$h['home_id'] ?>" <?= ((int)($filters['home_id'] ?? 0) === (int)$h['home_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$h['home_title'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Квартира</label>
            <select name="apartment_num">
                <option value="">Все</option>
                <?php foreach (($options['apartments'] ?? []) as $a): ?>
                    <?php
                    $optVal = (string)$a['apartment_num'];
                    $label = $a['home_title'] . ' / ' . $optVal;
                    ?>
                    <option value="<?= htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') ?>" <?= ((string)($filters['apartment_num'] ?? '') === $optVal) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Комнаты</label>
            <select name="rooms">
                <option value="">Все</option>
                <?php foreach (($options['rooms'] ?? []) as $r): ?>
                    <?php $rv = (string)$r['rooms']; ?>
                    <option value="<?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?>" <?= ((string)($filters['rooms'] ?? '') === $rv) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Пользователь</label>
            <select name="user_id">
                <option value="">Все</option>
                <?php foreach (($options['users'] ?? []) as $u): ?>
                    <?php
                    $uid = (int)$u['user_id'];
                    $ulabel = trim(($u['login'] ?? '') . ' / ' . ($u['name'] ?? ''), ' /');
                    ?>
                    <option value="<?= $uid ?>" <?= ((int)($filters['user_id'] ?? 0) === $uid) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ulabel, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Режим (тип квартир)</label>
            <select name="guard_mode">
                <option value="all" <?= $guardMode === 'all' ? 'selected' : '' ?>>Все</option>
                <option value="auto" <?= $guardMode === 'auto' ? 'selected' : '' ?>>Автоматическое</option>
                <option value="manual" <?= $guardMode === 'manual' ? 'selected' : '' ?>>Ручное</option>
            </select>
        </div>

        <div>
            <button type="submit" class="filter-btn" style="padding:8px 16px; background:#00CDAD; color:#fff; border:none; border-radius:5px; font-weight:700; cursor:pointer;">Фильтр</button>
        </div>
    </form>

    <form method="post" action="/sahmatka/ctrind.php?ctr=broni_clear&act=clear" id="broni-clear-form"
          onsubmit="return confirmClearBroni(this);">
        <input type="hidden" name="home_id" value="<?= (int)($filters['home_id'] ?? 0) ?>">
        <input type="hidden" name="apartment_num" value="<?= htmlspecialchars((string)($filters['apartment_num'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="rooms" value="<?= htmlspecialchars((string)($filters['rooms'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="user_id" value="<?= (int)($filters['user_id'] ?? 0) ?>">
        <input type="hidden" name="guard_mode" value="<?= htmlspecialchars((string)$guardMode, ENT_QUOTES, 'UTF-8') ?>">

        <div class="broni-clear-actions">
            <label><input type="checkbox" id="broni-clear-all"> Выбрать все</label>
            <button type="submit" id="broni-clear-submit" disabled style="padding:8px 16px; background:#dc3545; color:#fff; border:none; border-radius:5px; font-weight:700; cursor:pointer;">Снять выбранные брони</button>
            <span id="broni-clear-count" style="color:#666;">Выбрано: 0</span>
        </div>

        <table class="broni-clear-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Дом</th>
                    <th>Кв.</th>
                    <th>Комнаты</th>
                    <th>Режим</th>
                    <th>Пользователь</th>
                    <th>Дата</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="8">Нет актуальных броней по фильтру</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $isManual = !empty($row['is_manual_mode']);
                    $userLabel = trim(($row['login'] ?? '') . ' / ' . ($row['user_name'] ?? ''), ' /');
                    if (!empty($row['agency_caption'])) {
                        $userLabel .= ' (' . $row['agency_caption'] . ')';
                    }
                    $orderUrl = '/sahmatka/iframe_router.php?ctr=apartments&act=order&home_id=' . (int)$row['home_id']
                        . '&apartment_num=' . urlencode((string)$row['apartment_num']);
                    ?>
                    <tr>
                        <td><input type="checkbox" class="broni-clear-cb" name="broni_ids[]" value="<?= (int)$row['broni_id'] ?>"></td>
                        <td><?= htmlspecialchars((string)$row['home_title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$row['apartment_num'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$row['rooms'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="<?= $isManual ? 'badge-manual' : 'badge-auto' ?>"><?= $isManual ? 'ручное' : 'авто' ?></td>
                        <td><?= htmlspecialchars($userLabel, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$row['broni_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><a href="<?= htmlspecialchars($orderUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">открыть</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
(function() {
    var form = document.getElementById('broni-clear-form');
    if (!form) return;
    var all = document.getElementById('broni-clear-all');
    var submit = document.getElementById('broni-clear-submit');
    var countEl = document.getElementById('broni-clear-count');
    function boxes() { return Array.prototype.slice.call(form.querySelectorAll('.broni-clear-cb')); }
    function refresh() {
        var checked = boxes().filter(function(cb) { return cb.checked; });
        submit.disabled = checked.length === 0;
        countEl.textContent = 'Выбрано: ' + checked.length;
        if (all) {
            var total = boxes().length;
            all.checked = total > 0 && checked.length === total;
            all.indeterminate = checked.length > 0 && checked.length < total;
        }
    }
    if (all) {
        all.addEventListener('change', function() {
            boxes().forEach(function(cb) { cb.checked = all.checked; });
            refresh();
        });
    }
    form.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('broni-clear-cb')) refresh();
    });
    window.confirmClearBroni = function() {
        var n = boxes().filter(function(cb) { return cb.checked; }).length;
        if (!n) return false;
        return confirm('Снять выбранные брони (' + n + ' шт.)?\nКвартиры снова станут свободными.');
    };
    refresh();
})();
</script>
