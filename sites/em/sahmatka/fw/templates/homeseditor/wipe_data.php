<?php
$edit_url = $data['edit_url'];
$home = $data['home'];
$before = $data['before'];
$report = $data['report'] ?? null;
$form_url = $data['form_url'];
$home_title = htmlspecialchars((string) ($home['title'] ?: $home['long_title']));
$empty = ((int) $before['apartaments'] + (int) $before['sections'] + (int) $before['sections_cl'] + (int) $before['broni']) < 1;
?>

<?php if ($report): ?>
    <form action="<?= htmlspecialchars($edit_url) ?>" method="GET" id="editform">
        <?php
        $this->forminform = 'Данные дома удалены';
        $this->formpanel($edit_url);
        ?>
    </form>
    <p>Удалено из БД для дома <strong><?= $home_title ?></strong>
        (homes_id=<?= (int) $report['homes_id'] ?>, home_id=<?= (int) $report['home_id'] ?>):</p>
    <ul>
        <li>Квартир: <?= (int) $report['apartaments'] ?></li>
        <li>Секций: <?= (int) $report['sections'] ?></li>
        <li>Пустых клеток: <?= (int) $report['sections_cl'] ?></li>
        <li>Броней: <?= (int) $report['broni'] ?></li>
    </ul>
<?php else: ?>
    <form method="POST" action="<?= htmlspecialchars($form_url) ?>" id="editform"
          onsubmit="return confirm('Точно удалить все данные дома?');">
        <input type="hidden" name="confirm_wipe" value="1">
        <?php
        $this->forminform = $home_title;
        $this->formpanel($edit_url);
        ?>
        <p>Будут удалены записи:</p>
        <ul>
            <li>Квартир сейчас: <b><?= (int) $before['apartaments'] ?></b></li>
            <li>Секций сейчас: <b><?= (int) $before['sections'] ?></b></li>
            <li>Пустых клеток сейчас: <b><?= (int) $before['sections_cl'] ?></b></li>
            <li>Броней сейчас: <b><?= (int) $before['broni'] ?></b></li>
        </ul>
        <p>Карточка дома <strong>не</strong> удаляется.</p>
        <?php if ($empty): ?>
            <p style="color:#c45c00;">Сейчас по этому дому в этих таблицах уже пусто — удалять нечего.</p>
        <?php endif; ?>
    </form>
<?php endif; ?>
