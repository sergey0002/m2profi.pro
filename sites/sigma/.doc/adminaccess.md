# План: вынос проверки доступа admin из захардкоженного логина

**Дата:** 2026-05-26  
**Область:** только `sites/sigma` + `core` (без `core/etalon_site`, без других поддоменов)  
**Цель:** убрать сравнение `$_SESSION['sh_login'] == 'admin'`; список админ-логинов — в конфиге sigma; в core — единая функция с fallback на старое поведение (`admin`), если конфиг на домене не задан.

**Статус:** план (код не менялся).

---

## 1. Проблема

Логин главного администратора зашит в десятках мест как строка `'admin'`. Смена логина или добавление второго супер-админа требует правок по всему коду.

Дополнительно в `sites/sigma/sahmatka/config.php` уже есть заготовка `fw_check_access()` (стр. 59–92), но она **не используется** и проверяет `sh_id`, а не логин — к задаче не относится (не переименовывать в `check_access`).

---

## 2. Целевая архитектура

```
┌─────────────────────────────────────────────────────────────┐
│ sites/sigma/sahmatka/config.php                             │
│   $GLOBALS['config']['admin_logins'] = ['admin', ...];     │
│   (подключается раньше/вместе с core)                       │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│ core/functions.php                                            │
│   function check_access($role = 'admin', $login = null)       │
│   • role 'admin' → in_array(login, admin_logins)              │
│   • если admin_logins нет → ['admin'] (обратная совместимость)│
│   • if (!function_exists(...)) — не ломать переопределение    │
└──────────────────────────┬──────────────────────────────────┘
                           │
         Все проверки: check_access('admin')
         вместо: $_SESSION['sh_login'] == 'admin'
```

**Порядок загрузки sigma (важно):** в `config.php` строка 674: `include('../../../core/functions.php');` — функция в `core/functions.php` будет доступна **после** блока с `$GLOBALS['config']['admin_logins']`, если массив объявить **выше** include (рекомендуется сразу после DB-настроек, ~стр. 695–696).

---

## 3. Реализация `check_access` (спецификация, без правок)

### 3.1. Файл: `core/functions.php`

**Куда:** в начало файла, после существующих утилит (`t`, `tpl_add`, …), до тяжёлой логики шахматки (~стр. 40–50).

**Что добавить (логика):**

```php
if (!function_exists('check_access')) {
    /**
     * @param string $role  Сейчас только 'admin'
     * @param string|null $login  null = $_SESSION['sh_login']
     */
    function check_access($role = 'admin', $login = null)
    {
        if ($login === null) {
            $login = isset($_SESSION['sh_login']) ? (string) $_SESSION['sh_login'] : '';
        }
        if ($role !== 'admin') {
            return false;
        }
        $list = ['admin']; // fallback для доменов без конфига
        if (!empty($GLOBALS['config']['admin_logins']) && is_array($GLOBALS['config']['admin_logins'])) {
            $list = $GLOBALS['config']['admin_logins'];
        }
        return in_array($login, $list, true);
    }
}
```

**Поведение:**

| Условие | Результат |
|--------|-----------|
| sigma, `admin_logins = ['admin','sigma_root']` | оба логина — admin |
| другой сайт, конфиг без `admin_logins` | только `admin` |
| пустой `$_SESSION['sh_login']` | `false` |
| cron/CLI без сессии | перед вызовом `check_access` нужна сессия или явный `$login` (см. §8) |

**Не делать в core на этом этапе:** роли `demo_admin`, `fd`, `op15`, `agency_id == 92` — отдельная задача.

---

### 3.2. Файл: `sites/sigma/sahmatka/config.php`

**Куда:** после `$GLOBALS['config']['site_subdomain']` / DB (≈ стр. 695–697), **до** `include('../../../core/functions.php');`.

**Что добавить:**

```php
$GLOBALS['config']['admin_logins'] = [
    'admin',
    // при необходимости: 'sigma_super', 'backup_admin',
];
```

**Опционально (позже):** вынести в `sites/sigma/sahmatka/config.local.php` и подключать через `file_exists`, чтобы не коммитить лишние логины.

**Не трогать в этом PR:** `fw_check_access()` — оставить или пометить `@deprecated` в отдельном тикете.

---

## 4. Правила замены (чем на что)

### 4.1. Заменять (входит в задачу)

| Было | Стало |
|------|--------|
| `$_SESSION['sh_login'] == 'admin'` | `check_access('admin')` |
| `$_SESSION['sh_login']=='admin'` | `check_access('admin')` |
| `$_SESSION['sh_login'] != 'admin'` (проверка доступа) | `!check_access('admin')` |
| `$_SESSION['sh_login'] != 'admin'` в `ctr__agency.php` / `ctr__users.php` (die) | `!check_access('admin')` |

**Составные условия** — менять **только** часть про `admin`:

| Было | Стало |
|------|--------|
| `... == 'admin' \|\| ... == 'demo_admin'` | `check_access('admin') \|\| $_SESSION['sh_login'] == 'demo_admin'` |
| `... == 'admin' \|\| ... == 'fd'` | `check_access('admin') \|\| $_SESSION['sh_login'] == 'fd'` |
| `agency_id == "92" \|\| sh_login == 'admin'` | `$_SESSION['agency_id'] == "92" \|\| check_access('admin')` |

### 4.2. Не заменять (ложные срабатывания grep)

| Место | Причина |
|-------|---------|
| `$result['login'] != 'admin'` в `admin_users.php`, `agadmin_users.php`, `agadmin_users_t.php` | Исключение пользователя `admin` из отчёта, не проверка прав |
| `'admin@m2profi.pro'` в `ctr__zapisx.php` | Email отправителя |
| `$_GET['admin']`, `admin_mode`, `admin_user_id` | URL/SQL/схема БД |
| `require ... adminer.php` | сторонний файл |
| `$_SESSION['sh_login'] != 'admin_demo'` в `broni.php` | другой логин (`admin_demo`, не `admin`) |
| `$_SESSION['sh_login'] != 'admin_demo'` в `broni_history.php` | опечатка/legacy: `admin_demo` ≠ `demo_admin` — **не трогать** без отдельного разбора |

### 4.3. Локальная переменная `$is_admin` в core

`core/classes/classes.php`, метод `up_broni()` (~стр. 251):

```php
$is_admin = ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'em_nsv');
```

**Замена:**

```php
$is_admin = (check_access('admin') || $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'em_nsv');
```

(`demo_admin` / `em_nsv` — вне scope «только admin», оставляем как есть.)

---

## 5. Полный реестр файлов sigma (замены admin)

Всего **~25 файлов**, **~80+ вхождений** `sh_login` + `'admin'` (без etalon).

### 5.1. Конфиг и точка входа

| Файл | Строки (ориентир) | Действие |
|------|-------------------|----------|
| `sites/sigma/sahmatka/config.php` | новый блок ~696 | добавить `admin_logins` |
| `sites/sigma/sahmatka/config.php` | 59–92 | **не удалять** `fw_check_access` в этом этапе |

### 5.2. Меню / UI

| Файл | Кол-во admin | Примеры строк |
|------|--------------|---------------|
| `sites/sigma/sahmatka/template/default/in_head.php` | 18 | 85, 107, 127, 188, 217, 242, 251, 270, 296, 313, 333, 384, 415 |

### 5.3. Actions

| Файл | Строки | Комментарий |
|------|--------|-------------|
| `actions/admin_object.php` | 3, 19, 33, 49, 63, 79, 93, 266, 371 | массовое редактирование только admin |
| `actions/admin_agency.php` | 194, 243 | |
| `actions/show_broni.php` | 234, 521, 575 | часто вместе с `demo_admin` |
| `actions/stat_zapis.php` | 62, 167 | admin + `op15` |
| `actions/broni.php` | 147 | `== 'admin'` в elseif |
| `actions/broni_history.php` | 3 | см. §4.2 — **не admin**, `admin_demo` |
| `actions/admin_users.php` | 478 | **не заменять** (`$result['login']`) |
| `actions/agadmin_users.php` | 148 | **не заменять** |
| `actions/agadmin_users_t.php` | 149 | **не заменять** |

### 5.4. Контроллеры fw

| Файл | Строки |
|------|--------|
| `fw/controllers/ctr__objects.php` | 187, 224, 486, 502, 516, 530, 546, 560 |
| `fw/controllers/ctr__apartments.php` | 895 |
| `fw/controllers/ctr__parking_floors.php` | 118, 134, 395, 401, 427, 430, 434 |
| `fw/controllers/ctr__parking_spaces.php` | 360, 404 |
| `fw/controllers/ctr__agency.php` | 151 (`!= 'admin'` → `!check_access('admin')`) |
| `fw/controllers/ctr__users.php` | 12 (`!= 'admin'`) |

### 5.5. Шаблоны fw

| Файл | Строки |
|------|--------|
| `fw/templates/zapiskeys/index_ajaxrow.php` | 46 |
| `fw/templates/rentobjects/ag_one_item.php` | 63, 66 |
| `fw/templates/rentobjects/display_ag_form.php` | 314 |
| `fw/templates/parking_spaces/form_broni_ag.php` | 6, 45, 67, 86, 102, 123, 145 |
| `fw/templates/parking_floors/status_legend.php` | 6 |

### 5.6. Прочие sigma

| Файл | Строки | Комментарий |
|------|--------|-------------|
| `form_exc_backoffice.php` | 9 | admin + agency 92 |
| `ajax_actions.php` | 179 | admin + op15 |
| `iframe_apart.php` | 151, 377 | admin + em_nsv + demo_admin; стр. 500 — мёртвый `1==2` |
| `user.php` | 228, 268, 330 | |
| `cron_clear_expired_broni.php` | 27 | см. §8 |

### 5.7. Файлы sigma из grep без правки admin-login

| Файл | Причина |
|------|---------|
| `fw/controllers/ctr__zapisx.php` | только `admin@m2profi.pro`, GET admin |
| `restapi/*` | нет `sh_login` + admin |

---

## 6. Полный реестр файлов core (замены admin)

**Не трогать:** `core/etalon_site/**` (вне scope).

### 6.1. `core/functions.php`

| Строки (grep) | Контекст |
|---------------|----------|
| 200, 470, 1114 | UI шахматки: строка этажа для admin |
| 1160, 1249, 1283, 1311, 1319, 1341, 1378, 1430, 1474 | отображение/действия в таблице квартир |

**Замена:** везде `$_SESSION['sh_login'] == 'admin'` → `check_access('admin')`.

### 6.2. `core/classes/classes.php`

**Объём:** основной массив прав (~40+ вхождений admin в сессии).

| Участки | Строки (ориентир) |
|---------|-------------------|
| `up_broni()` | 251 |
| `get_homes` / видимость show 2,3 | 506–507, 533 |
| Отрисовка шахматки, брони, статусы | 811, 938, 974, 1000, 1011, 1032, 1071, 1128, 1180 |
| Права на объекты (дубли блоков) | 1421, 1492, 1506, 1544, 1550, 1587, 1603, 1630, 1648 |
| Второй дублированный блок | 1858, 1929, 1943, 1981, 1997, 2034, 2050, 2077, 2095 |
| Агентства / каталог | 2351, 2372, 2385, 2780, 2795 |

**Составные условия:** как в §4.1 (`demo_admin`, `fd`, `em_nsv`, `agency_id`).

### 6.3. Остальной core

| Файл | admin-login | Действие |
|------|-------------|----------|
| `core/classes/mysql.php` | только `demo_admin` | **не менять** в этой задаче |
| `core/classes/users.php` | — | — |
| `core/classes/controller.php` | — | — |
| `core/classes/filed.php` | — | — |
| `core/fw/**` | нет sh_login admin | — |

---

## 7. Порядок внедрения (этапы)

### Этап 0 — подготовка (этот документ)

- [x] Инвентаризация
- [ ] Согласовать список `admin_logins` для sigma

### Этап 1 — инфраструктура (1 PR)

1. Добавить `check_access()` в `core/functions.php`.
2. Добавить `$GLOBALS['config']['admin_logins']` в `sites/sigma/sahmatka/config.php`.
3. **Smoke:** на sigma под логином из списка — меню «Статистика», `ctr__users`; под обычным агентом — `die` на admin-контроллерах.

### Этап 2 — sigma, критичные точки доступа

1. `fw/controllers/ctr__users.php`, `ctr__agency.php` — жёсткий `die`.
2. `actions/admin_object.php` — POST массового редактирования.
3. `user.php`, `ajax_actions.php`, `iframe_apart.php`.

### Этап 3 — sigma, UI

1. `template/default/in_head.php` (массовая замена, проверить каждый пункт меню).
2. Шаблоны `fw/templates/**`.

### Этап 4 — sigma, остальное

1. Остальные `actions/*`, `fw/controllers/*`.
2. `cron_clear_expired_broni.php` (§8).

### Этап 5 — core

1. `core/functions.php` — все вхождения.
2. `core/classes/classes.php` — пакетная замена с прогоном сценариев: шахматка, бронь, `up_broni`, каталог домов.

### Этап 6 — регрессия

Чек-лист ниже (§9).

---

## 8. Особый случай: cron `cron_clear_expired_broni.php`

**Сейчас (стр. 26–31):**

```php
$_SESSION = [
    'sh_login' => 'admin',
    'sh_id' => 1,
    ...
];
```

**После смены логина в конфиге** cron сломается, если оставить жёстко `'admin'`.

**Рекомендация:**

```php
$cron_admin = !empty($GLOBALS['config']['admin_logins'][0])
    ? $GLOBALS['config']['admin_logins'][0]
    : 'admin';
$_SESSION = [
    'sh_login' => $cron_admin,
    ...
];
```

Либо вынести `$GLOBALS['config']['cron_admin_login']` в конфиг sigma.

---

## 9. Чек-лист регрессии (sigma + core)

| # | Сценарий | Ожидание |
|---|----------|----------|
| 1 | Вход логином из `admin_logins` | полное admin-меню, массовое редактирование |
| 2 | Вход `demo_admin` | как сейчас (не admin, если не в списке) |
| 3 | Вход `fd`, `op15`, `em_nsv` | права без изменений (составные условия) |
| 4 | `ctrind.php?ctr=users` не-admin | «Доступ запрещен» |
| 5 | POST `admin_object.php` не-admin | нет массовых изменений |
| 6 | Шахматка: видимость домов show=2,3 | только admin/fd/agency 92 по старым правилам |
| 7 | Продление брони `up_broni` | admin из списка — без лимита 15 дней |
| 8 | Домен без `admin_logins` (другой сайт) | логин `admin` по-прежнему admin |
| 9 | Cron снятия броней | отрабатывает под системным пользователем |

---

## 10. Риски и ограничения

1. **Дубли кода в `classes.php`** — два почти одинаковых блока; при замене править оба или вынести helper позже.
2. **`broni_history.php` / `broni.php`** — несогласованность `admin_demo` vs `demo_admin`; не смешивать с `check_access`.
3. **Строгое сравнение:** `in_array(..., true)` — регистр логина важен.
4. **Безопасность:** смена логина в конфиге не отменяет знание пароля в БД; нужна смена пароля/логина в `users`.
5. **Scope:** другие поддомены продолжат работать на fallback `'admin'` до добавления своих `admin_logins`.

---

## 11. Что сознательно не входит в этот этап

- Роли: `demo_admin`, `fd`, `director`, `op15`, `keys1/2`, `em_nsv`, `docm`
- Проверка `agency_id == 92` (отдел продаж)
- Рефакторинг `fw_check_access()`
- Правки `core/etalon_site`
- Миграция остальных `sites/*`

---

## 12. Краткая сводка для исполнителя

| Шаг | Файл | Действие |
|-----|------|----------|
| 1 | `core/functions.php` | добавить `check_access()` |
| 2 | `sites/sigma/sahmatka/config.php` | `$GLOBALS['config']['admin_logins']` |
| 3 | ~25 файлов sigma (§5) | `== 'admin'` → `check_access('admin')` |
| 4 | `core/functions.php` + `core/classes/classes.php` | то же |
| 5 | `cron_clear_expired_broni.php` | логин из конфига |
| 6 | — | регрессия §9 |

**Команда поиска остатков после внедрения:**

```bash
rg "sh_login.*['\"]admin['\"]" sites/sigma core --glob '!**/etalon_site/**'
```

Ожидаемый остаток: исключения из §4.2, cron (если оставлен явный fallback), комментарии.

---

*Документ подготовлен для внедрения без изменения кода на момент сохранения.*
