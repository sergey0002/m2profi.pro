<?php
require_once 'config.php';

header('Content-Type: application/json');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из тела запроса
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Проверяем наличие необходимых данных
    if (isset($data['id']) && isset($data['note'])) {
        $id = $data['id'];
        $note = $data['note'];

        try {
            // Подготавливаем SQL-запрос для обновления примечания
            $stmt = $pdo->prepare("UPDATE caller SET note = :note WHERE id = :id");
            $stmt->bindParam(':note', $note, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Примечание успешно сохранено.']);
        } catch (PDOException $e) {
            error_log("Ошибка при сохранении примечания: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных при сохранении примечания.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Недостаточно данных для сохранения примечания.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный метод запроса.']);
}
?>