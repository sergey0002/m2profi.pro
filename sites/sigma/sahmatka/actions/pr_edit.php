<?php
/**
 * Редактирование предложения (экшен)
 * Встраивается в user.php
 */

require_once __DIR__ . '/../inc/proposal_manager.php';
require_once __DIR__ . '/../inc/proposal_objects_renderer.php';

// Получаем данные предложения из сессии
$proposal_data = ProposalManager::getProposalData();

// Для переименования берем имя из сессии или дефолт
$proposal_name = $_SESSION['proposal_name'] ?? 'Новое предложение';
$objects_count = count($proposal_data['objects'] ?? []);

// Если объектов нет
if ($objects_count === 0) {
    echo '<div class="container" style="padding: 100px 0; text-align: center; background: #fff; margin-top: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">';
    echo '<h2 style="font-weight: 700; margin-bottom: 20px;">Ваше предложение пока пусто</h2>';
    echo '<p style="color: #64748b; font-size: 18px; margin-bottom: 30px;">Добавьте объекты из каталогов, чтобы сформировать красивый список для клиента.</p>';
    echo '<div style="display: flex; gap: 15px; justify-content: center;">';
    echo '<a href="user.php?action=gh" class="btn btn-primary" style="background: #00CDAE; border: none; padding: 12px 25px;">Перейти в каталог домов</a>';
    echo '<a href="user.php?action=lp" class="btn btn-secondary" style="background: #334155; border: none; padding: 12px 25px;">Каталог участков</a>';
    echo '</div>';
    echo '</div>';
    return;
}
?>

<link rel="stylesheet" href="template/default/css/proposal.css">

<div class="proposal-wrapper container">
    <!-- Заголовок и основные кнопки -->
    <div class="proposal-header">
        <div class="proposal-title-row">
            <div>
                <h1 id="proposal-main-title" style="margin: 0; font-size: 32px; font-weight: 800;"><?=htmlspecialchars($proposal_name)?></h1>
                <p style="color: #64748b; margin: 5px 0 0 0;">Всего объектов: <span id="objects-count-badge" style="font-weight: 700; color: #00CDAE;"><?=$objects_count?></span></p>
            </div>
            <div class="proposal-actions">
                <button id="generate-link-btn" class="btn btn-primary" style="background: #00CDAE; border: none; padding: 10px 20px; font-weight: 600;">Поделиться предложением</button>
                <button id="clear-proposal-btn" class="btn btn-outline-danger" style="margin-left: 10px; padding: 10px 20px;">Удалить предложение</button>
            </div>
        </div>

        <!-- Поле для переименования -->
        <div class="proposal-rename-box">
            <span style="font-weight: 600; min-width: 170px;">Название предложения:</span>
            <input type="text" id="edit-proposal-name" class="form-control" value="<?=htmlspecialchars($proposal_name)?>" placeholder="Например: Подборка домов для семьи Смирновых">
            <button id="save-proposal-name-btn" class="btn btn-success" style="background: #10b981; border: none; padding: 8px 15px; font-weight: 600;">Сохранить название</button>
        </div>
    </div>
    
    <!-- Контейнер для ссылки -->
    <div id="permalink-container" style="display:none; margin-bottom: 30px; background: #ecfdf5; border: 1px solid #10b981; padding: 20px; border-radius: 12px;">
        <div style="display: flex; align-items: flex-start; gap: 15px;">
            <div style="background: #10b981; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">✓</div>
            <div style="flex-grow: 1;">
                <p style="margin: 0 0 10px 0; font-weight: 700; color: #065f46; font-size: 18px;">Персональная ссылка готова!</p>
                <p style="margin: 0 0 15px 0; color: #065f46; font-size: 14px;">Отправьте эту ссылку клиенту. Он увидит все объекты с вашими комментариями.</p>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="permalink-result" class="form-control" readonly style="background: #fff; font-family: monospace;">
                    <button class="btn btn-success" id="copy-link-btn" style="background: #10b981; border: none; font-weight: 600; min-width: 130px;">Копировать</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Сетка объектов -->
    <div class="proposal-objects-container">
        <?php 
        echo ProposalObjectsRenderer::renderList($proposal_data['objects'] ?? [], true);
        ?>
    </div>
</div>
