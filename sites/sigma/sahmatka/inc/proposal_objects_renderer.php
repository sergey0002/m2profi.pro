<?php
/**
 * Файл: sites/xdemo2/sahmatka/inc/proposal_objects_renderer.php
 * Единый компонент для отрисовки объектов в предложении
 */

require_once __DIR__ . '/ProposalManager.php';

class ProposalObjectsRenderer {
    
    /**
     * Отрисовка одного объекта
     * 
     * @param array $obj Данные объекта из сессии или JSON
     * @param bool $isEditable Флаг режима редактирования (показывать инпуты для заметок и кнопки удаления)
     */
    public static function renderObject($obj, $isEditable = false) {
        $objectData = (isset($obj['name']) || isset($obj['art'])) ? $obj : ProposalManager::getFullObjectData($obj['id'], $obj['type']);
        
        if (!$objectData) {
            return '<!-- Объект не найден: ' . (isset($obj['id']) ? $obj['id'] : 'unknown') . ' -->';
        }

        $typeLabel = $objectData['type_label'] ?? ($obj['type'] ?? '');
        $title = !empty($objectData['name']) ? $objectData['name'] : (!empty($objectData['art']) ? $objectData['art'] : 'Без названия');
        $img = $objectData['img'] ?? 'template/default/images/no-photo.jpg';
        $price = isset($objectData['price']) ? number_format((float)$objectData['price'], 0, '.', ' ') . ' ₽' : 'Цена по запросу';
        
        $extraInfo = '';
        if ($obj['type'] === 'house') {
            $extraInfo = $objectData['descr'] ?? '';
        } elseif ($obj['type'] === 'project') {
            $extraInfo = ($objectData['area'] ?? '?') . ' м² | ' . ($objectData['floors'] ?? '?') . ' эт. | ' . ($objectData['bedrooms'] ?? '?') . ' спальни';
        } elseif ($obj['type'] === 'settlement') {
            $extraInfo = ($objectData['highway'] ?? '') . ', ' . ($objectData['mkad'] ?? '?') . ' км от МКАД';
        } elseif ($obj['type'] === 'land') {
            $extraInfo = 'Площадь: ' . ($objectData['area'] ?? '?') . ' сот. | ' . ($objectData['settlement'] ?? '');
        }

        ob_start();
        ?>
        <div class="proposal-card <?= $isEditable ? 'editable' : 'public' ?>" data-object-id="<?= $obj['id'] ?>" data-object-type="<?= $obj['type'] ?>">
            <div class="proposal-card__inner">
                <?php if ($isEditable): ?>
                    <!-- Режим редактирования: 2 столбца -->
                    <div class="proposal-card__row">
                        <!-- Левая колонка: Инфо из базы -->
                        <div class="proposal-card__col-db">
                            <div class="proposal-card__image">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>">
                                <div class="proposal-card__type"><?= htmlspecialchars($typeLabel) ?></div>
                            </div>
                            <div class="proposal-card__content">
                                <h3 class="proposal-card__title"><?= htmlspecialchars($title) ?></h3>
                                <div class="proposal-card__price"><?= $price ?></div>
                                <div class="proposal-card__extra"><?= htmlspecialchars($extraInfo) ?></div>
                            </div>
                        </div>
                        
                        <!-- Правая колонка: Заметки и Удаление -->
                        <div class="proposal-card__col-edit">
                            <div class="proposal-card__note">
                                <label>Ваш комментарий:</label>
                                <textarea class="note-input" placeholder="Напишите здесь детали для клиента..."><?= htmlspecialchars($obj['note'] ?? '') ?></textarea>
                                <div class="note-status" style="display:none; font-size:10px; color: #00CDAE; text-align: right; margin-top:5px;">Сохранено</div>
                            </div>
                            <div class="proposal-card__danger-zone">
                                <button class="remove-object-btn btn btn-outline-danger btn-sm" style="width: 100%; border-radius: 8px;">Удалить из списка</button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Публичный режим: Сверху инфо, снизу заметка -->
                    <div class="proposal-card__top">
                        <div class="proposal-card__image">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>">
                            <div class="proposal-card__type"><?= htmlspecialchars($typeLabel) ?></div>
                        </div>
                        <div class="proposal-card__content">
                            <h3 class="proposal-card__title"><?= htmlspecialchars($title) ?></h3>
                            <div class="proposal-card__price"><?= $price ?></div>
                            <div class="proposal-card__extra"><?= htmlspecialchars($extraInfo) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($obj['note'])): ?>
                        <div class="proposal-card__footer">
                            <div class="note-box">
                                <strong>Комментарий:</strong><br>
                                <?= nl2br(htmlspecialchars($obj['note'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Отрисовка всего списка объектов
     */
    public static function renderList($objects, $isEditable = false) {
        if (empty($objects)) {
            return '<div class="proposal-empty">Ваше предложение пока пусто. Добавьте объекты из каталога!</div>';
        }

        $html = '<div class="proposal-grid">';
        foreach ($objects as $obj) {
            $html .= self::renderObject($obj, $isEditable);
        }
        $html .= '</div>';
        
        return $html;
    }
}