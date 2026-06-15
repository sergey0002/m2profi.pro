<?php
/**
 * Файл: sites/xdemo2/sahmatka/inc/proposal_manager.php
 * Точка входа для системы предложений. Обеспечивает обратную совместимость.
 */

// Подключаем основной класс (PSR-4 style autoloading не настроен, подключаем вручную)
require_once __DIR__ . '/ProposalManager.php';
require_once __DIR__ . '/proposal_block.php';

// Инициализируем сессию
ProposalManager::init();

/**
 * Прокси-функции для обратной совместимости (если где-то вызываются функции напрямую)
 */

if (!function_exists('renderProposalObjects')) {
    function renderProposalObjects($objects, $edit_mode = false) {
        $renderer_path = __DIR__ . '/proposal_objects_renderer.php';
        if (file_exists($renderer_path)) {
            include $renderer_path;
        } else {
            echo "Ошибка: Рендерер объектов не найден.";
        }
    }
}