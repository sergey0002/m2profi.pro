<?php
/**
 * Файл: sites/xdemo2/sahmatka/inc/proposal_block.php
 * Блок формирования предложения для добавления в HTML-файлы
 */

// Функция для вывода блока предложения
function renderProposalBlock() {
    $count = ProposalManager::getCount();
    
    ob_start();
    ?>
    <div id="proposal-block" class="proposal-block" style="<?php echo ($count === 0) ? 'display: none;' : ''; ?>">
        <button id="create-proposal-btn" class="btn btn-primary">
            Сформировать предложение
            <span id="proposal-counter" class="badge badge-light" style="<?php echo ($count === 0) ? 'display: none;' : ''; ?>"><?php echo $count; ?></span>
        </button>
    </div>
    
    <!-- Модальное окно для ввода названия (если нужно) -->
    <div id="proposal-modal" class="proposal-modal mfp-hide">
        <div class="proposal-modal-content">
            <h3>Название предложения</h3>
            <input type="text" id="proposal-name" class="form-control" placeholder="Введите название предложения">
            <div id="proposal-actions" style="margin-top:20px; text-align:right;">
                <button id="save-proposal-btn" class="btn btn-success">Сохранить</button>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}
?>