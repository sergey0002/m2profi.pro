# Аудит Legacy Bridge (Механизмы Входа и Выхода)

**Дата аудита:** 2026-02-08
**Объект анализа:** Интеграция Laravel 12 и Legacy PHP (core/classes/users.php)

---

## 1. Архитектура моста сессий

Система использует **параллельное существование двух механизмов сессий**:
1.  **Laravel Session:** Хранится в БД (таблица `sessions`), управляется встроенным драйвером Laravel. Использует куку `m2_profi_main_dashboard_session`.
2.  **Legacy PHP Session:** Хранится в файловой системе (native PHP), управляется через `session_start()`. Использует куку `PHPSESSID`.

Связующим звеном выступает Middleware `App\Http\Middleware\LegacySessionBridge`.

---

## 2. Детальный анализ механизмов

### 2.1 Вход (Login Flow)

#### Через Laravel (`Ctr__Auth@act__login`):
1.  **Валидация:** Проверяет логин/пароль. Поддерживает Bcrypt и Plaintext.
2.  **Auth::login:** Авторизует пользователя в контексте Laravel.
3.  **Legacy Sync:**
    - Записывает данные в `$_SESSION` (sh_login, sh_password, sh_id, sh_name и др.).
    - **Важно:** В `sh_password` записывается именно то значение, которое лежит в БД (если там хеш, пишется хеш).
4.  **Результат:** Пользователь авторизован в Laravel-контроллерах и в Legacy-скриптах (так как они увидят заполненный `$_SESSION`).

#### Через Legacy (`core/classes/users.php`):
1.  **SQL Check:** Выполняет `SELECT ... WHERE login = '$login' AND password = '$password'`.
2.  **Session Fill:** Заполняет `$_SESSION` теми же ключами.
3.  **Bridge Detection:** На следующем запросе к Laravel-части срабатывает `LegacySessionBridge`, видит `sh_login` в `$_SESSION` и "подтягивает" пользователя в Laravel через `Auth::login()`.

### 2.2 Выход (Logout Flow)

#### Через Laravel (`Ctr__Auth@act__logout`):
1.  **Laravel Logout:** `Auth::logout()`.
2.  **Legacy Cleanup:** Явный `unset` всех ключей в `$_SESSION`.
3.  **Session Nuke:** `invalidate()` и `regenerateToken()` для Laravel сессии.

#### Через Legacy (`users::out` или `?exit=1`):
1.  **Legacy Cleanup:** `unset` всех ключей `$_SESSION`.
2.  **Detection:** При следующем заходе в Laravel-зону `/la/*`, `LegacySessionBridge` замечает отсутствие `sh_login` при наличии активной Auth-сессии Laravel и принудительно делает `Auth::logout()`.

---

## 3. Матрица совместимости паролей

| Пароль в БД | Метод входа | Работает в Laravel? | Работает в Legacy? |
| :--- | :--- | :--- | :--- |
| Plaintext | Laravel Form | Да (через direct match) | Да (в сессию лег plaintext) |
| Plaintext | Legacy Form | Да (через Bridge) | Да (direct match в SQL) |
| Bcrypt | Laravel Form | Да (через Hash::check) | Да (в сессию лег хеш, SQL legacy совпал) |
| Bcrypt | Legacy Form | **НЕТ** (SQL legacy не умеет Bcrypt) | **НЕТ** |

**Вывод:** Миграция на Bcrypt требует использования только формы входа Laravel. Это безопасный и правильный путь.

---

## 4. Критические замечания и Риски

### ⚠️ Риск №1: Хранение паролей в сессии
Legacy-код требует наличия пароля в `$_SESSION['sh_password']` для проверки при каждом обращении к БД.
*   **Опасность:** При краже `PHPSESSID` или доступе к файлам сессий на сервере, пароли (или их хеши) становятся доступными.
*   **Статус:** Принято как ограничение legacy-архитектуры.

### ⚠️ Риск №2: Блокировка (Session Locking)
Вызов `session_start()` в Middleware `LegacySessionBridge.php` (строка 25) блокирует файл сессии до конца выполнения скрипта Laravel.
*   **Проблема:** Если Laravel-запрос долгий, параллельные AJAX-запросы к Legacy-части (или наоборот) будут висеть в состоянии "Pending".
*   **Рекомендация:** Добавить `session_write_close()` после инициализации Auth в Middleware.

### ⚠️ Риск №3: Нарушение стандартов Laravel
`LegacySessionBridge.php` (строка 19) использует `env()` напрямую.
*   **Рекомендация:** Использовать `config('app.platform_domain')`.

---

## 5. Резюме аудита
Механизм моста реализован **грамотно и симметрично**. Он обеспечивает бесшовный пользовательский опыт при переходе между legacy и Laravel частями. Переход на Bcrypt-хеширование не "ломает" legacy-часть благодаря тому, что `sh_password` в сессии синхронизируется с БД, что позволяет legacy SQL-запросам находить соответствие по строке хеша.
