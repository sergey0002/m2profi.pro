# План переноса compred: EM → sigma

**Задача:** перенести реализацию «Коммерческих предложений» с `sites/em` на `sites/sigma` **в том же виде**, как сделано на EM.  
**Дата:** 21.06.2025  
**Статус:** план (код не менялся)  
**Эталон:** `sites/em/sahmatka/` + `sites/em/compred/`  
**Документ задачи:** [doc.md](./doc.md)

---

## 0. Результат аудита (EM vs sigma)

### 0.1. Поиск `compred` в sigma

```
sites/sigma — совпадений: 0
```

Функционал **полностью отсутствует**.

### 0.2. Матрица файлов

| # | Путь (от `sites/{tenant}/`) | EM | Sigma | Действие |
|---|----------------------------|:--:|:-----:|----------|
| 1 | `compred/.htaccess` | ✓ | ✗ | **COPY** |
| 2 | `sahmatka/migrations/001_compred.sql` | ✓ | ✗ | **COPY** + создать `migrations/` |
| 3 | `sahmatka/migrations/002_compred_intro.sql` | ✓ | ✗ | **COPY** |
| 4 | `sahmatka/inc/compred_config.php` | ✓ | ✗ | **COPY** |
| 5 | `sahmatka/inc/compred_helpers.php` | ✓ | ✗ | **COPY** |
| 6 | `sahmatka/fw/controllers/ctr__compred.php` | ✓ | ✗ | **COPY** |
| 7 | `sahmatka/fw/templates/apartments/compred_block.php` | ✓ | ✗ | **COPY** |
| 8–17 | `sahmatka/fw/templates/compred/*` (10 файлов) | ✓ | ✗ | **COPY** |
| 18 | `sahmatka/template/default/css/compred.css` | ✓ | ✗ | **COPY** |
| 19 | `sahmatka/template/default/js/compred.js` | ✓ | ✗ | **COPY** |
| 20 | `sahmatka/template/default/images/menu-icon-8.svg` | ✓ | ✗ | **COPY** |
| 21 | `sahmatka/fw/controllers/ctr__apartments.php` | compred | нет | **PATCH** |
| 22 | `sahmatka/fw/templates/apartments/form_broni_pub.php` | include | нет | **PATCH** |
| 23 | `sahmatka/fw/templates/apartments/form_broni_ag.php` | include | нет | **PATCH** |
| 24 | `sahmatka/iframe_router.php` | OG meta | нет | **PATCH** |
| 25 | `sahmatka/template/default/in_head.php` | меню | нет | **PATCH** |
| 26 | `sahmatka/config.php` | include config | сервер | **PATCH** (вне git) |

---

## 1. Этапы переноса (рекомендуемый порядок)

| Этап | Содержание | Зависимости |
|------|------------|-------------|
| **1** | Копирование 19+1 файлов | — |
| **2** | Миграция БД sigma | этап 1 (SQL в репо) |
| **3** | `config.php` на сервере | этап 1 |
| **4** | Патчи интеграции (5 файлов в git) | этап 1 |
| **5** | Apache: `compred/.htaccess` | деплой |
| **6** | QA по чеклисту §8 | всё выше |

**Оценка:** ~0.5–1 д (копирование + патчи + миграция + smoke-test), без доработок под терминологию «апартаменты».

---

## 2. Этап 1 — копирование файлов

### 2.1. Команды (из корня репозитория)

```powershell
$src = "sites/em"
$dst = "sites/sigma"

# Корень ЧПУ
New-Item -ItemType Directory -Force -Path "$dst/compred"
Copy-Item "$src/compred/.htaccess" "$dst/compred/.htaccess"

# Миграции
New-Item -ItemType Directory -Force -Path "$dst/sahmatka/migrations"
Copy-Item "$src/sahmatka/migrations/001_compred.sql" "$dst/sahmatka/migrations/"
Copy-Item "$src/sahmatka/migrations/002_compred_intro.sql" "$dst/sahmatka/migrations/"

# inc
New-Item -ItemType Directory -Force -Path "$dst/sahmatka/inc"
Copy-Item "$src/sahmatka/inc/compred_config.php" "$dst/sahmatka/inc/"
Copy-Item "$src/sahmatka/inc/compred_helpers.php" "$dst/sahmatka/inc/"

# Контроллер
Copy-Item "$src/sahmatka/fw/controllers/ctr__compred.php" "$dst/sahmatka/fw/controllers/"

# Шаблоны compred
New-Item -ItemType Directory -Force -Path "$dst/sahmatka/fw/templates/compred"
Copy-Item "$src/sahmatka/fw/templates/compred/*" "$dst/sahmatka/fw/templates/compred/"

# Блок карточки
Copy-Item "$src/sahmatka/fw/templates/apartments/compred_block.php" "$dst/sahmatka/fw/templates/apartments/"

# CSS/JS
Copy-Item "$src/sahmatka/template/default/css/compred.css" "$dst/sahmatka/template/default/css/"
Copy-Item "$src/sahmatka/template/default/js/compred.js" "$dst/sahmatka/template/default/js/"

# Иконка меню
Copy-Item "$src/sahmatka/template/default/images/menu-icon-8.svg" "$dst/sahmatka/template/default/images/"
```

### 2.2. Правки в скопированных файлах

**MVP — не менять.** Копировать байт-в-байт с EM.

Опционально (backlog, не блокирует MVP):

- в `001_compred.sql` комментарий `-- БД: m2profi_em` → `-- БД: <имя БД sigma>`
- тексты «квартира» → «апартамент» в шаблонах/helpers (sigma использует `unit_*` в карточках)

---

## 3. Этап 2 — база данных

### 3.1. Определить имя БД

`config.php` sigma **не в git** (`.gitignore`). На сервере найти:

```php
$mysql = new mysql(..., 'ИМЯ_БД', ...);
```

или аналог в `config.php.example` / документации деплоя.

### 3.2. Выполнить миграции

```bash
mysql -u USER -p ИМЯ_БД_SIGMA < sites/sigma/sahmatka/migrations/001_compred.sql
mysql -u USER -p ИМЯ_БД_SIGMA < sites/sigma/sahmatka/migrations/002_compred_intro.sql
```

### 3.3. Проверка

```sql
SHOW TABLES LIKE 'compred%';
DESCRIBE compred;
DESCRIBE compred_obj;
```

Ожидание: 2 таблицы, в `compred` есть колонка `intro_text`.

---

## 4. Этап 3 — config.php (сервер)

В `sites/sigma/sahmatka/config.php` (на сервере):

```php
include(__DIR__ . '/inc/compred_config.php');
```

Разместить рядом с другими `include` из `inc/` (по аналогии с EM).

**APP_URL** — обязательно домен sigma:

```php
// Пример (как в config.php.example EM):
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'sigma.m2profi.pro';
putenv("APP_URL=$protocol$host");
```

На sigma в `in_head.php` уже есть `$m2SiteBaseUrl = 'https://' . $subdomain . '.m2profi.pro'` — `APP_URL` должен совпадать с этим доменом для корректных ссылок `/compred/{token}`.

---

## 5. Этап 4 — патчи интеграции

### 5.1. `ctr__apartments.php` — метод `act__order`

**Файл:** `sites/sigma/sahmatka/fw/controllers/ctr__apartments.php`  
**Якорь:** после блока:

```php
    if (!$data) {
        echo '<h2>' . unit_phrase('not_found') . '</h2>';
        return;
    }
```

**Вставить** (идентично EM, строки 707–730):

```php
    // --- compred: блок «Добавить к предложению» ---
    $compred_list = [];
    $compred_msg = '';
    $compred_err = '';
    $compred_selected_id = 0;
    if (!empty($_SESSION['sh_id'])) {
        if (!empty($_GET['compred_ok'])) {
            $compred_msg = 'Квартира добавлена в предложение';
        }
        $compred_selected_id = (int)($_GET['compred_id'] ?? 0);
        if (!empty($_GET['compred_err'])) {
            $compred_err = urldecode((string)$_GET['compred_err']);
        }
        $compred_list = $mysql->get_arr(
            'SELECT compred_id, caption FROM compred
             WHERE user_id = ' . (int)$_SESSION['sh_id'] . ' AND del = 0
             ORDER BY updated_at DESC LIMIT 100'
        );
    }
    $compred_apartament_id = (int)($data['apartament_id'] ?? 0);
    $compred_return_url = 'iframe_router.php?ctr=apartments&act=order'
        . '&home_id=' . (int)$home_id
        . '&apartment_num=' . (int)$apartment_num
        . '&apartments=' . (int)($_GET['apartments'] ?? 0);
```

**В массив `$tpl_data`** (перед `if ($show_done_template)`), добавить ключи:

```php
        'compred_list' => $compred_list,
        'compred_msg' => $compred_msg,
        'compred_err' => $compred_err,
        'compred_selected_id' => $compred_selected_id,
        'apartament_id' => $compred_apartament_id,
        'return_url' => $compred_return_url,
```

**Не делать:** дублировать в ветке `form_broni_done` (early return) — на EM тоже не обязательно для MVP.

---

### 5.2. `form_broni_pub.php`

**Файл:** `sites/sigma/sahmatka/fw/templates/apartments/form_broni_pub.php`  
**Текущее окончание правой колонки (sigma):**

```php
            <?php } ?>
        </div>
    </div>
</div>
```

**Заменить на** (как EM):

```php
            <?php } ?>

            <?php
            if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
                include __DIR__ . '/compred_block.php';
            }
            ?>
        </div>
    </div>
</div>
```

**Важно:** include **вне** `<form>` бронирования.

---

### 5.3. `form_broni_ag.php`

**Файл:** `sites/sigma/sahmatka/fw/templates/apartments/form_broni_ag.php`  
**После** `</form>` (~стр. 52):

```php
            <?php
            if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
                include __DIR__ . '/compred_block.php';
            }
            ?>
```

---

### 5.4. `iframe_router.php`

**Полный diff** (EM минус sigma):

```diff
 include('config.php');
+require_once __DIR__ . '/inc/compred_helpers.php';
 $_GET=$_REQUEST;
 
+$compred_page_meta = null;
+if (($_GET['ctr'] ?? '') === 'compred' && ($_GET['act'] ?? '') === 'public') {
+    $compred_page_meta = compred_bootstrap_public_meta((string)($_GET['token'] ?? ''));
+}
+
 if( $_SESSION['sh_login'] || 1==1 )
 {
@@
   <head>
     <meta charset="utf-8">
+    <?php if ($compred_page_meta): ?>
+    <?php include __DIR__ . '/fw/templates/compred/_public_meta.php'; ?>
+    <?php else: ?>
     <meta name="robots" content="noindex, nofollow" />
     ...
     <meta name="description" content="">
+    <?php endif; ?>
```

---

### 5.5. `in_head.php` — пункт меню

**Файл:** `sites/sigma/sahmatka/template/default/in_head.php`  
**Якорь:** после строки с «Апартаменты» (~стр. 63):

```php
<li><a href="user.php?action=objects&home=60&sdan=0" class="active">…Апартаменты</a></li>
```

**Вставить сразу после `</li>` этого пункта:**

```php
				<?
				if ($_SESSION['sh_login'] != 'keys1' && $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' && $_SESSION['sh_login'] != 'director') {
				?>
				<li><a href="/sahmatka/ctrind.php?ctr=compred&act=index"<?= (($_GET['ctr'] ?? '') === 'compred') ? ' class="active"' : '' ?>><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Мои предложения</a></li>
				<?
				}
				?>
```

**Позиция:** под «Апартаменты», **до** «Парковки» — аналог EM (там под «Квартиры»).

---

## 6. Этап 5 — Apache и деплой

### 6.1. ЧПУ

Убедиться, что на хосте sigma обрабатывается:

```
sites/sigma/compred/.htaccess
```

URL вида: `https://sigma.m2profi.pro/compred/a1b2c3…` (32 hex).

### 6.2. Структура на сервере

Типичный layout m2profi multi-tenant:

```
public_html/          ← корень sigma на сервере
  compred/.htaccess
  sahmatka/...
```

Путь `RewriteRule` в `.htaccess` ведёт на `/sahmatka/iframe_router.php` — **от корня домена**, как на EM.

### 6.3. Deploy

После коммита файлов sigma — выкладка через существующий механизм `sites/dep/` (при наличии проекта sigma) или ручной `git pull` на сервере тенанта.

**Файлы деплоя compred (ожидаемый набор):**

- 19 новых + 1 svg
- 5 изменённых PHP в `sahmatka/`
- `config.php` — ручная правка на сервере
- SQL — один раз на БД

---

## 7. Сверка с реализацией EM (полный функционал)

После переноса на sigma должны работать те же экшены `ctr__compred`:

| Action | Entry | Auth |
|--------|-------|------|
| `index` | ctrind | login |
| `edit` | ctrind | owner |
| `view` | ctrind | login |
| `public` | iframe_router / ЧПУ | token, без login |
| `add_item` | ajax_router POST | login |
| `save_details` | ajax_router | owner |
| `save_comment` | ajax_router | owner |
| `remove_item` | ajax_router | owner |
| `generate_link` | ajax_router | owner |
| `del` | ajax_router | owner |

UI-фичи EM (должны сохраниться при копировании шаблонов/CSS):

- группировка микрорайон → дом → квартиры (`_objects_grouped.php`)
- `intro_text` + единая кнопка «Сохранить» (`save_details`)
- share panel + мессенджеры (`_share_panel.php`, `compred_share_links()`)
- OG-meta для public (`_public_meta.php`)
- accent `#00CDAD`, cache bust CSS v=22 / JS v=3–8

Подробное описание — [EM doc.md](../../../em/.doc/tasks/1/doc.md).

---

## 8. Тест-план (sigma)

### 8.1. База и конфиг

- [ ] Таблицы `compred`, `compred_obj` созданы
- [ ] `compred_config.php` подключён в config.php
- [ ] `APP_URL` = домен sigma

### 8.2. Кабинет

- [ ] Логин риэлтора → «Мои предложения» в меню (не keys1/keys2/em_nsv/director)
- [ ] Пустой index → инструкция + CTA
- [ ] Карточка апартамента (iframe order) → блок compred
- [ ] add_item → redirect с `compred_ok=1`

### 8.3. Edit / share

- [ ] Сохранение caption + intro (`save_details`)
- [ ] Autosave примечания к объекту
- [ ] Копирование ссылки `/compred/{token}`
- [ ] Удаление объекта / предложения

### 8.4. Public

- [ ] `/compred/{token}` без cookie сессии
- [ ] intro_text, группировка, планировки без обрезки
- [ ] OG tags в исходнике страницы

### 8.5. Регрессия sigma

- [ ] Бронирование (form_broni_pub) не сломано
- [ ] Смена статуса (form_broni_ag)
- [ ] Парковки / аренда в меню без изменений

---

## 9. Чеклист PR / коммита

```
A  sites/sigma/compred/.htaccess
A  sites/sigma/sahmatka/migrations/001_compred.sql
A  sites/sigma/sahmatka/migrations/002_compred_intro.sql
A  sites/sigma/sahmatka/inc/compred_config.php
A  sites/sigma/sahmatka/inc/compred_helpers.php
A  sites/sigma/sahmatka/fw/controllers/ctr__compred.php
A  sites/sigma/sahmatka/fw/templates/compred/  (10 files)
A  sites/sigma/sahmatka/fw/templates/apartments/compred_block.php
A  sites/sigma/sahmatka/template/default/css/compred.css
A  sites/sigma/sahmatka/template/default/js/compred.js
A  sites/sigma/sahmatka/template/default/images/menu-icon-8.svg
M  sites/sigma/sahmatka/fw/controllers/ctr__apartments.php
M  sites/sigma/sahmatka/fw/templates/apartments/form_broni_pub.php
M  sites/sigma/sahmatka/fw/templates/apartments/form_broni_ag.php
M  sites/sigma/sahmatka/iframe_router.php
M  sites/sigma/sahmatka/template/default/in_head.php
A  sites/sigma/.doc/tasks/1/doc.md
A  sites/sigma/.doc/tasks/1/plan.md
```

**Вне git:** `sites/sigma/sahmatka/config.php` — include `compred_config.php`.

---

## 10. Backlog (после MVP на sigma)

- Терминология «апартамент» вместо «квартира» в UI compred
- Отдельный deploy-проект `sites/dep/projects/sigma.env`
- Перенос compred на другие тенанты по тому же шаблону
- См. [EM plan §12](../../../em/.doc/tasks/1/plan.md) — parking/rent, drag-sort, PDF

---

*План составлен по diff репозитория EM/sigma, 21.06.2025.*
