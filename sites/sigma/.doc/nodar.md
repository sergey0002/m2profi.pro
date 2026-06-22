# План (альтернативный): добавить логин `nodar` как второго админа

**Дата:** 2026-05-26  
**Область:** только `sites/sigma` + `core` (без `core/etalon_site`, без других поддоменов)  
**Цель:** к каждой существующей проверке `sh_login == 'admin'` добавить `|| sh_login == 'nodar'`.  
**Альтернатива:** упрощённый вариант без конфига и без `check_access()` (см. `adminaccess.md`).

**Статус:** план (код не менялся).

---

## 1. Идея

Не вводить функцию и конфиг — везде, где захардкожен `'admin'`, через `||` добавить второй логин `'nodar'`. Минимум вмешательства, максимум совместимости.

```php
// Было
if ($_SESSION['sh_login'] == 'admin') { ... }

// Станет
if ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'nodar') { ... }
```

---

## 2. Плюсы/минусы относительно `adminaccess.md`

| Критерий | `check_access` + конфиг | `nodar` через `\|\|` |
|---|---|---|
| Объём правок | 1 helper + конфиг + замены | только замены |
| Риск регрессии | средний (новая функция) | минимальный |
| Время | ~2–3 ч | ~30–60 мин |
| Расширяемость (третий логин) | редактировать конфиг | редактировать ~25 файлов снова |
| Чистота кода | высокая | низкая (дублирование) |
| Поведение в других sites | нужен fallback | не затрагивает |

**Вывод:** оптимально, если на горизонте только один новый логин.

---

## 3. Шаблон замены

### 3.1. Одиночная проверка

```php
// Было
$_SESSION['sh_login'] == 'admin'

// Стало
($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'nodar')
```

**Важно:** оборачивать в скобки, если выражение часть составного условия с `&&`, иначе изменится приоритет операторов.

### 3.2. Уже составное условие с другими логинами

```php
// Было
$_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin'

// Стало
$_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'nodar'
```

### 3.3. Отрицание (запрет доступа)

```php
// Было
if ($_SESSION['sh_login'] != 'admin') { die('Доступ запрещен'); }

// Стало
if ($_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'nodar') { die('Доступ запрещен'); }
```

Применяется в:
- `sites/sigma/sahmatka/fw/controllers/ctr__users.php:12`
- `sites/sigma/sahmatka/fw/controllers/ctr__agency.php:151` (там уже `&& != 'demo_admin'`, добавить ещё `&& != 'nodar'`)

### 3.4. Локальная переменная

`core/classes/classes.php:251`:

```php
// Было
$is_admin = ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'em_nsv');

// Стало
$is_admin = ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'demo_admin' || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'nodar');
```

---

## 4. Что НЕ менять (исключения)

Те же, что в `adminaccess.md` §4.2:

| Место | Причина |
|-------|---------|
| `$result['login'] != 'admin'` в `admin_users.php:478`, `agadmin_users.php:148`, `agadmin_users_t.php:149` | Исключение строки в отчёте, не проверка сессии — **не добавлять `nodar`** |
| `'admin@m2profi.pro'` в `ctr__zapisx.php` | Email отправителя |
| `$_GET['admin']`, `admin_mode`, `admin_user_id` | URL / SQL / схема БД |
| `require ... adminer.php` | сторонний файл |
| `$_SESSION['sh_login'] != 'admin_demo'` в `actions/broni.php:147`, `actions/broni_history.php:3` | Это другой логин (`admin_demo`, не `admin`) |
| `$GLOBALS['config']['site_subdomain'] = 'sigma';` и т.п. | не имеет отношения к admin-логину |
| `fw_check_access()` в `config.php:59–92` | мёртвый stub, не относится |

**Дубли строки `admin_user_id = ...` (значение 1, 92, …)** — это `id` админа агентства в таблице `agency`, не сессия. Не трогать.

---

## 5. Реестр файлов sigma (с номерами строк)

Всего ~25 файлов, ~80+ вхождений.

### 5.1. Меню / UI

| Файл | Строки |
|------|--------|
| `sites/sigma/sahmatka/template/default/in_head.php` | 85, 107, 127, 188, 217, 242, 251, 270, 296, 313, 333, 384, 415 |

### 5.2. Actions

| Файл | Строки |
|------|--------|
| `actions/admin_object.php` | 3, 19, 33, 49, 63, 79, 93, 266, 371 |
| `actions/admin_agency.php` | 194, 243 |
| `actions/show_broni.php` | 234, 521, 575 |
| `actions/stat_zapis.php` | 62, 167 |
| `actions/broni.php` | 147 *(см. §4 — не трогать `admin_demo`)* |

### 5.3. Контроллеры fw

| Файл | Строки |
|------|--------|
| `fw/controllers/ctr__objects.php` | 187, 224, 486, 502, 516, 530, 546, 560 |
| `fw/controllers/ctr__apartments.php` | 895 |
| `fw/controllers/ctr__parking_floors.php` | 118, 134, 395, 401, 427, 430, 434 |
| `fw/controllers/ctr__parking_spaces.php` | 360, 404 |
| `fw/controllers/ctr__agency.php` | 151 *(см. §3.3 — отрицание)* |
| `fw/controllers/ctr__users.php` | 12 *(см. §3.3 — отрицание)* |

### 5.4. Шаблоны fw

| Файл | Строки |
|------|--------|
| `fw/templates/zapiskeys/index_ajaxrow.php` | 46 |
| `fw/templates/rentobjects/ag_one_item.php` | 63, 66 |
| `fw/templates/rentobjects/display_ag_form.php` | 314 |
| `fw/templates/parking_spaces/form_broni_ag.php` | 6, 45, 67, 86, 102, 123, 145 |
| `fw/templates/parking_floors/status_legend.php` | 6 |

### 5.5. Прочие sigma

| Файл | Строки |
|------|--------|
| `form_exc_backoffice.php` | 9 |
| `ajax_actions.php` | 179 |
| `iframe_apart.php` | 151, 377 (стр. 500 — мёртвый `1==2`, пропустить) |
| `user.php` | 228, 268, 330 |
| `cron_clear_expired_broni.php` | 27 *(см. §7)* |

---

## 6. Реестр файлов core

**Не трогать:** `core/etalon_site/**`.

### 6.1. `core/functions.php`

Строки (по grep): **200, 470, 1114, 1160, 1249, 1283, 1311, 1319, 1341, 1378, 1430, 1474**

Везде шаблон §3.1.

### 6.2. `core/classes/classes.php`

| Блок | Строки |
|------|--------|
| `up_broni()` — `$is_admin` | 251 *(см. §3.4)* |
| Видимость домов | 506, 533 |
| Шахматка / брони / статусы | 811, 938, 974, 1000, 1011, 1032, 1071, 1128, 1180 |
| Права на объекты, 1-й блок | 1421, 1492, 1506, 1544, 1550, 1587, 1603, 1630, 1648 |
| Дублированный блок | 1858, 1929, 1943, 1981, 1997, 2034, 2050, 2077, 2095 |
| Агентства / каталог | 2351, 2372, 2385, 2780, 2795 |

### 6.3. Прочие core (без правок)

- `core/classes/mysql.php` — только `demo_admin`, не трогать.
- `core/classes/users.php`, `controller.php`, `filed.php`, `core/fw/**` — без вхождений `sh_login == 'admin'`.

---

## 7. Особый случай: cron

`sites/sigma/sahmatka/cron_clear_expired_broni.php:27`:

```php
$_SESSION = [
    'sh_login' => 'admin',
    ...
];
```

**Не трогать.** Логин `admin` будет работать как и раньше — мы только добавляем `nodar`, ничего не отбираем. Если потребуется, чтобы cron шёл от `nodar` — поправить отдельно.

---

## 8. Порядок внедрения

1. **Создать пользователя `nodar`** в БД (таблица `users`, с нужным паролем и доступом).
2. **sigma — критичные `die`:**
   - `ctr__users.php:12`
   - `ctr__agency.php:151`
3. **sigma — POST/массовые действия:**
   - `actions/admin_object.php` (9 точек)
   - `iframe_apart.php`, `ajax_actions.php`, `form_exc_backoffice.php`
4. **sigma — UI:** `template/default/in_head.php` (13 точек) + шаблоны `fw/templates/**`.
5. **sigma — actions/контроллеры** (остальное).
6. **core:** `functions.php` (12 точек) + `classes/classes.php` (~40 точек).
7. **Регрессия** по §10.

---

## 9. Команды поиска (для контроля)

Перед началом — полный список:

```bash
rg "sh_login.*['\"]admin['\"]" sites/sigma core --glob '!**/etalon_site/**' -n
```

После внедрения каждый файл должен иметь рядом с каждой строкой и проверку `nodar`:

```bash
# Найти оставшиеся места БЕЗ nodar (грубая эвристика):
rg "sh_login.*['\"]admin['\"]" sites/sigma core --glob '!**/etalon_site/**' -l \
  | xargs -I{} sh -c 'rg -q nodar "{}" || echo "MISS: {}"'
```

Под PowerShell:

```powershell
rg "sh_login.*['""]admin['""]" sites/sigma core --glob '!**/etalon_site/**' -l |
  ForEach-Object {
    if (-not (Select-String -Path $_ -Pattern 'nodar' -Quiet)) { "MISS: $_" }
  }
```

(MISS-список должен пересечься только с разрешёнными исключениями §4.)

---

## 10. Чек-лист регрессии

| # | Сценарий | Ожидание |
|---|----------|----------|
| 1 | Вход `admin` | как раньше, всё доступно |
| 2 | Вход `nodar` | те же права, что у `admin` |
| 3 | Вход `demo_admin` | без изменений |
| 4 | Вход `fd`, `op15`, `em_nsv` | без изменений |
| 5 | Обычный агент | не получает admin-доступ, `die` на admin-контроллерах |
| 6 | `ctrind.php?ctr=users` под `nodar` | доступ открыт |
| 7 | `ctrind.php?ctr=agency` под `nodar` | доступ открыт |
| 8 | POST массового изменения цен под `nodar` | работает |
| 9 | Шахматка/каталог под `nodar` | как у admin |
| 10 | Продление брони `up_broni` под `nodar` | без лимита 15 дней |
| 11 | Cron снятия броней | работает как раньше |

---

## 11. Риски

1. **Опечатка приоритетов:** в составных `A && B || C` без скобок легко сломать логику. Использовать §3.1 шаблон со скобками.
2. **Объём диффа:** ~80+ изменений; высокий риск пропустить точку. Контроль — §9.
3. **Долговая нагрузка:** третий логин потребует прохода по тем же ~25 файлам. Если ожидается, лучше идти по плану `adminaccess.md`.
4. **Безопасность:** `nodar` получит полный доступ — пароль должен быть сильным; учётка не должна быть «гостевой».

---

## 12. Сводка для исполнителя

| Шаг | Файл/группа | Действие |
|-----|-------------|----------|
| 0 | БД `users` | создать `nodar` |
| 1 | `ctr__users.php`, `ctr__agency.php` | отрицание (§3.3) |
| 2 | ~23 файла sigma (§5) | шаблон §3.1/§3.2 |
| 3 | `core/functions.php`, `core/classes/classes.php` | то же |
| 4 | — | регрессия §10 |
| 5 | cron | **не трогать** |

**Один шаблон правки на 95% случаев:**

```php
// найти:   $_SESSION['sh_login'] == 'admin'
// заменить: ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'nodar')
```

(Скобки добавлять только там, где выражение уже было одиночным операндом в составном условии — для безопасности приоритетов.)

---

*Документ подготовлен как упрощённая альтернатива `adminaccess.md`. Код не менялся.*
