# План реализации системы ролей (RBAC)

## Цель
Создать гибкую систему управления правами доступа для групп пользователей. Система должна позволять настраивать доступ к контроллерам и действиям (action), а также поддерживать произвольные именованные правила (например, `delete_docs`).

**Основные принципы:**
*   **Без наследования**: Права каждой группы настраиваются индивидуально.
*   **Без логов**: Не усложняем систему лишним логированием.
*   **jQuery UI**: Интерфейс управления правами строится на чистом jQuery.
*   **Проверка контента**: Функция `get_rule` используется для скрытия/показа элементов интерфейса (кнопок).

---

## 1. Структура Базы Данных

Используется таблица `users_group_rules` (уже создана).

```sql
CREATE TABLE `users_group_rules` (
  `users_group_rules_id` int NOT NULL AUTO_INCREMENT,
  `users_group_id` int NOT NULL,
  `ctr` int DEFAULT NULL,
  `act` int DEFAULT NULL,
  `rule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Имя правила (ctr/act) или wildcard',
  `access` tinyint NOT NULL COMMENT '1 - разрешено, 0 - запрещено',
  PRIMARY KEY (`users_group_rules_id`),
  KEY `users_group_id` (`users_group_id`),
  UNIQUE KEY `group_rule_unique` (`users_group_id`, `rule`),
  CONSTRAINT `users_group_rules_ibfk_1` FOREIGN KEY (`users_group_id`) REFERENCES `users_group` (`users_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 2. Глобальная функция `get_rule`

Файл: `sahmatka/fw/functions/permissions.php`

### Логика работы:
1.  **Кэширование (Runtime)**: При первом вызове загружает **все** правила для текущей группы пользователя в статическую переменную. Это минимизирует нагрузку на БД (1 запрос на страницу).
2.  **Супер-Админ**: Если `$_SESSION['users_group_id'] == 1` (или логин 'admin'), возвращает `true` всегда.
3.  **Проверка**:
    *   Ищет точное совпадение переданного правила в загруженном массиве.
    *   Если точного нет, проверяет Wildcard (например, для `ctr__users/act__edit` ищет `ctr__users/*`).
    *   Возвращает `true` (если access=1) или `false`.

### Пример использования в шаблонах (Проверка контента):
```php
<!-- Кнопка редактирования показывается только если есть право -->
<? if (get_rule('ctr__users/act__edit')): ?>
    <a href="?ctr=users&act=edit&id=<?=$row['id']?>" class="btn btn-edit">Редактировать</a>
<? endif; ?>

<!-- Кнопка удаления -->
<? if (get_rule('delete_docs')): ?>
    <button class="btn btn-danger">Удалить документ</button>
<? endif; ?>
```

---

## 3. Контроллер управления правами (`ctr__permissions.php`)

Файл: `sahmatka/fw/controllers/ctr__permissions.php`

### Методы:

#### `act__index()`
*   Список групп пользователей.
*   Кнопка "Настроить права" (`?ctr=permissions&act=edit&id=...`).

#### `act__edit()`
*   **Backend**:
    *   Сканирует папку `controllers/` на наличие файлов `ctr__*.php`.
    *   Парсит файлы (regex) для поиска методов `act__*`.
    *   Загружает текущие права группы из БД.
    *   Передает в шаблон массив контроллеров, действий и текущих прав.
*   **Frontend (jQuery)**:
    *   Отрисовывает таблицу/список прав.
    *   **Группировка**: Каждому контроллеру соответствует блок (div/fieldset).
    *   **Wildcard Checkbox**: В заголовке блока чекбокс "Все действия (*)". При клике на него:
        *   Автоматически отмечает/снимает все чекбоксы действий внутри блока.
        *   Визуально блокирует (disabled) вложенные чекбоксы, показывая, что действует глобальное правило.
    *   **Фильтрация**: Поле поиска "Найти правило". jQuery скрывает строки, не совпадающие с вводом.
    *   **Кастомные правила**:
        *   Список существующих кастомных правил.
        *   Форма добавления: Input Text + Button "Add".
        *   При нажатии "Add" jQuery добавляет новую строку в таблицу (без перезагрузки, или с перезагрузкой - для простоты можно с перезагрузкой через `act__add_rule`).

#### `act__save()`
*   Принимает массив `permissions` из формы.
*   Очищает старые права группы (или обновляет).
*   Сохраняет новые значения.
*   Если выбран Wildcard (`ctr/*`), сохраняет только его (для оптимизации) или все явно.

---

## 4. План реализации (Пошаговый)

### Шаг 1: Подготовка
1.  Создать файл `sahmatka/fw/functions/permissions.php`.
2.  Реализовать функцию `get_rule` с кэшированием.
3.  Подключить файл в `sahmatka/config.php`.

### Шаг 2: Контроллер `ctr__permissions.php`
1.  Создать файл контроллера.
2.  Реализовать метод `scan_controllers()` (glob + regex).
3.  Реализовать `act__index` (список групп).
4.  Реализовать `act__edit` (вывод формы).
5.  Реализовать `act__save` (сохранение в БД).

### Шаг 3: Интерфейс (jQuery)
1.  В шаблоне `act__edit` добавить скрипт:
    *   Обработка клика по "Все действия (*)".
    *   Обработка поиска по правилам.
    *   (Опционально) AJAX сохранение без перезагрузки.

### Шаг 4: Внедрение
1.  В `ctr__users_group.php` добавить ссылку на настройку прав.
2.  В `ctr__users_group.php` (для теста) обернуть кнопку "Удалить" в проверку `if (get_rule('...'))`.

---

## 5. Пример кода (jQuery для `act__edit`)

```javascript
$(document).ready(function() {
    // Wildcard toggle
    $('.ctr-wildcard').change(function() {
        var ctrName = $(this).data('ctr');
        var isChecked = $(this).is(':checked');
        // Найти все чекбоксы действий этого контроллера
        $('.act-checkbox[data-ctr="' + ctrName + '"]').prop('checked', isChecked).prop('disabled', isChecked);
    });

    // Инициализация состояния (если wildcard уже выбран)
    $('.ctr-wildcard:checked').each(function() {
        var ctrName = $(this).data('ctr');
        $('.act-checkbox[data-ctr="' + ctrName + '"]').prop('disabled', true);
    });
});
```
