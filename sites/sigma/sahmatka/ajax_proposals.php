<?php
/**
 * Файл: sites/xdemo2/sahmatka/ajax_proposals.php
 * Обработка AJAX-запросов для функционала "Избранное"/"Предложения"
 */

// Включаем отображение ошибок для отладки (в формате JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);

function handleFatalError() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'PHP Fatal Error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
}
register_shutdown_function('handleFatalError');

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/inc/ProposalManager.php';

    header('Content-Type: application/json');

    if (isset($_POST['action'])) {
        $response = ['success' => false];

        switch ($_POST['action']) {
            case 'add_to_proposal':
                if (isset($_POST['object_id']) && isset($_POST['object_type'])) {
                    if (ProposalManager::addObject($_POST['object_type'], $_POST['object_id'])) {
                        $response = [
                            'success' => true,
                            'count' => ProposalManager::getCount(),
                            'message' => 'Объект добавлен'
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Уже добавлено'];
                    }
                }
                break;

            case 'remove_from_proposal':
                $id = $_POST['object_id'] ?? $_POST['id'] ?? null;
                if ($id) {
                    if (ProposalManager::removeObject($id)) {
                        $response = [
                            'success' => true,
                            'count' => ProposalManager::getCount(),
                            'message' => 'Объект удален'
                        ];
                    }
                }
                break;

            case 'clear_proposal':
                ProposalManager::clear();
                $response = ['success' => true, 'count' => 0];
                break;

            case 'get_proposal_count':
                $response = [
                    'success' => true,
                    'count' => ProposalManager::getCount()
                ];
                break;

            case 'update_proposal_note':
                $id = $_POST['object_id'] ?? $_POST['id'] ?? null;
                $note = $_POST['note'] ?? '';
                if ($id) {
                    if (ProposalManager::addNote($id, $note)) {
                        $response = ['success' => true];
                    }
                }
                break;

            case 'rename_proposal':
                $name = trim($_POST['name'] ?? '');
                if ($name) {
                    $_SESSION['proposal_name'] = $name;
                    $response = ['success' => true];
                }
                break;

            case 'generate_permalink':
                $proposal_name = $_SESSION['proposal_name'] ?? 'Ваше предложение';
                $manager_name = $_POST['manager_name'] ?? $_SESSION['user_full_name'] ?? '';
                
                $hash = ProposalManager::savePermanentProposal($proposal_name, $manager_name);
                if ($hash) {
                    $permalink = 'https://' . $_SERVER['HTTP_HOST'] . '/sahmatka/personaloffer/index.php?file=' . $hash;
                    $response = [
                        'success' => true,
                        'permalink' => $permalink,
                        'hash' => $hash
                    ];
                } else {
                    $response = ['success' => false, 'message' => 'Ошибка при сохранении предложения (возможно, список пуст)'];
                }
                break;
        }

        echo json_encode($response);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
