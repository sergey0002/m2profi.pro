<?php
class ctr__exsources extends ctr__
{  
    var $table = 'form_submissions';
    var $key_filed = 'id';
    var $ctr = 'exsources';
    var $title = 'Внешние источники';
    var $allowed_sources = ['em' => 'em-nsk.ru', 'noff' => 'нофф.рф'];

    // 🔹 СЛОВАРИ ПОЛЕЙ: [источник][ключ_из_json] = 'Заголовок для отображения'
    var $field_labels = [
        'em' => [
            'name'           => 'Имя',
            'phone'          => 'Телефон',
            'email'          => 'Email',
            'user_id'        => 'ID пользователя',
            'agency_id'      => 'ID агентства',
            'nps_score'      => 'Оценка',
            'likes'          => 'Что нравится',
            'improvements'   => 'Что улучшить',
            'missing_features' => 'Не хватает функций',
            'short_desc'     => 'Одним словом',
            'utm_source'     => 'Источник перехода',
            'utm_medium'     => 'Канал',
            'utm_campaign'   => 'Кампания',
            'product_type'   => 'Тип продукта',
            'message'        => 'Сообщение',
        ],
        'noff' => [
            'name'           => 'ФИО',
            'phone'          => 'Телефон',
            'email'          => 'E-mail',
            'user_id'        => 'Пользователь',
            'agency_id'      => 'Агентство',
            'nps_score'      => 'Оценка',
            'company'        => 'Компания',
            'position'       => 'Должность',
            'budget'         => 'Бюджет',
            'deadline'       => 'Сроки',
            'comment'        => 'Комментарий',
            'source_page'    => 'Страница',
        ]
    ];

    function __construct()
    {
        if ($_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'goodzem') { 
            die('Доступ запрещен'); 
        }
        
        $this->current_source = $_GET['source'] ?? '';
        if (!$this->current_source || !isset($this->allowed_sources[$this->current_source])) {
            die('Неверный источник. Доступные: ' . implode(', ', array_keys($this->allowed_sources)));
        }
        
        $this->data = $this->getfiltr([]);
    }

    // 🔥 Заглушка для роутера
    function act__export_csv() {
        header("Location: ?ctr=" . $this->ctr . "&source=" . $this->current_source);
        exit;
    }

    function get_base_sql($where = '')
    {
        $source = $this->current_source;
        return "
            SELECT id, source, source_url, name, phone, email, nps_score,
                   fields, files, created_at
            FROM `{$this->table}` 
            WHERE source = '{$source}'
            ORDER BY created_at DESC
            LIMIT 0, 1000
        ";
    }

    function act__index()
    {
        global $t;
        $t['h1'] = ' ' . $this->allowed_sources[$this->current_source];
        
        $data = $this->data ?: [];
        $labels = $this->field_labels[$this->current_source] ?? [];
        ?>
        <style>
            .ex-table { width: 100%; border-collapse: collapse; font-size: 14px; background: #fff; }
            .ex-table th, .ex-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; }
            .ex-table th { background: #f8fafc; font-weight: 600; color: #475569; }
            .ex-toggle { cursor: pointer; color: #3b82f6; font-weight: bold; font-size: 16px; user-select: none; display: inline-block; width: 20px; text-align: center; }
            .ex-toggle:hover { color: #1d4ed8; }
            .ex-row-detail { display: none; background: #f1f5f9; }
            .ex-row-detail td { padding: 0; border-bottom: 2px solid #cbd5e1; }
            .ex-detail-box { padding: 15px; }
            .ex-detail-tbl { width: 100%; border-collapse: collapse; }
            .ex-detail-tbl td { padding: 4px 8px; border: none; vertical-align: top; }
            .ex-label { width: 160px; font-weight: 500; color: #64748b; }
            .ex-link { color: #0ea5e9; text-decoration: none; }
            .ex-link:hover { text-decoration: underline; }
            .ex-nps { display: inline-block; padding: 2px 6px; border-radius: 4px; color: #fff; font-weight: 600; font-size: 12px; }
        </style>

        <table class="ex-table">
            <thead>
                <tr>
                    <th width="30"></th>
                    <th>ID</th>
                    <th>Дата и время</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Оценка</th>
                    <th>URL</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $row): 
                $nps = $row['nps_score'];
                $nps_html = ($nps !== null && $nps !== '') 
                    ? "<span class='ex-nps' style='background:".($nps <= 6 ? '#ef4444' : ($nps <= 8 ? '#f59e0b' : '#22c55e'))."'>{$nps}</span>" 
                    : '<span style="color:#94a3b8">–</span>';
            ?>
                <tr>
                    <td><span class="ex-toggle" onclick="toggleExRow(this)">+</span></td>
                    <td><?= $row['id'] ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></td>
                    <td><?= htmlspecialchars($row['name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                    <td><?= $nps_html ?></td>
                    <td><a href="<?= htmlspecialchars($row['source_url'] ?? '#') ?>" target="_blank" class="ex-link">Открыть</a></td>
                </tr>
                <tr class="ex-row-detail" id="detail_<?= $row['id'] ?>">
                    <td colspan="8">
                        <div class="ex-detail-box">
                            <table class="ex-detail-tbl">
                                <tr><td class="ex-label">Источник:</td><td><?= htmlspecialchars($row['source']) ?> <small style="color:#64748b">(<?= htmlspecialchars($row['source_url']) ?>)</small></td></tr>
                                <?php
                                $fields = json_decode($row['fields'] ?? '{}', true) ?: [];
                                $files  = json_decode($row['files'] ?? '[]', true) ?: [];
                                
                                // 🔹 Выводим ВСЕ поля из JSON (ничего не пропускаем)
                                foreach ($fields as $k => $v) {
                                    // Если поле есть в словаре — берём красивый заголовок, иначе — оригинальный ключ
                                    $label = $labels[$k] ?? $k;
                                    $v_str = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
                                    echo "<tr><td class='ex-label'>".htmlspecialchars($label).":</td><td style='white-space:pre-wrap'>".htmlspecialchars($v_str)."</td></tr>";
                                }
                                
                                // Файлы
                                if (!empty($files)) {
                                    echo "<tr><td colspan='2' style='padding-top:10px; font-weight:600; color:#3b82f6'>📎 Файлы:</td></tr>";
                                    foreach ($files as $f) {
                                        $fn = basename(parse_url($f, PHP_URL_PATH) ?: $f);
                                        echo "<tr><td class='ex-label'></td><td><a href='".htmlspecialchars($f)."' target='_blank' class='ex-link'>🔗 ".htmlspecialchars($fn)."</a></td></tr>";
                                    }
                                }
                                ?>
                                <tr><td colspan="2" style="padding-top:12px; font-size:11px; color:#94a3b8; border-top:1px solid #e2e8f0">
                                    Hash: <?= htmlspecialchars(substr($row['submission_hash'] ?? '', 0, 16)) ?>...
                                </td></tr>
                            </table>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <script>
        function toggleExRow(btn) {
            var mainRow = btn.closest('tr');
            var detailRow = mainRow.nextElementSibling;
            if (detailRow && detailRow.classList.contains('ex-row-detail')) {
                var isHidden = detailRow.style.display === 'none' || detailRow.style.display === '';
                detailRow.style.display = isHidden ? 'table-row' : 'none';
                btn.textContent = isHidden ? '−' : '+';
            }
        }
        </script>
        <?php
    }
}