<?php
session_start();
require_once __DIR__.'/control/operations.php';
opsInit('panel');

// Обработка выхода
if (isset($_GET['logout'])) {
    opsWrite('logout','ok');
    session_destroy();
    header("Location: index.php");
    exit;
}

$globalEnvFile = __DIR__ . '/.env';
$usersDict = [];

// Парсим глобальных пользователей
if (file_exists($globalEnvFile)) {
    $lines = file($globalEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'USERS=') === 0) {
            $usersStr = substr(trim($line), 6);
            $pairs = explode(',', $usersStr);
            foreach ($pairs as $pair) {
                if (strpos($pair, ':') !== false) {
                    list($u, $p) = explode(':', $pair, 2);
                    $usersDict[trim($u)] = trim($p);
                }
            }
        }
    }
}

// Обработка логина
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'], $_POST['password'])) {
        $login = trim($_POST['login']);
        $pass = trim($_POST['password']);

        if (isset($usersDict[$login]) && $usersDict[$login] === $pass) {
            $_SESSION['user'] = $login;
            opsWrite('login','ok');
            header("Location: index.php");
            exit;
        } else {
            opsWrite('login','error',['attempted_login'=>$login,'reason'=>'Неверные учётные данные']);
            $errorMsg = 'Неверный логин или пароль!';
        }
    }
}

// Проверка сессии, если нет - выводим форму логина
if (empty($_SESSION['user'])) {
    if ($_SERVER['REQUEST_METHOD']==='GET') opsWrite('open_login','ok');
    ?>
    <!DOCTYPE html>
    <html lang="ru">

    <head>
        <meta charset="UTF-8">
        <title>Вход - Xuyak2production</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white text-center fs-5">Авторизация</div>
                        <div class="card-body">
                            <?php if ($errorMsg): ?>
                                <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($errorMsg) ?></div>
                            <?php endif; ?>

                            <form method="post" action="">
                                <div class="mb-3">
                                    <label class="form-label">Логин</label>
                                    <input type="text" name="login" class="form-control" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Пароль</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">ВОЙТИ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
    <?php
    exit;
}

$currentUser = $_SESSION['user'];

// Сканируем папку проектов
$projectsDir = __DIR__ . '/projects/';
$availableProjects = [];

if (is_dir($projectsDir)) {
    $files = scandir($projectsDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'env') {
            $projName = pathinfo($file, PATHINFO_FILENAME);
            $envPath = $projectsDir . $file;

            // Парсим ALLOWED_USERS чтобы проверить доступ
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $allowedStr = '';
            foreach ($lines as $line) {
                if (strpos(trim($line), 'ALLOWED_USERS=') === 0) {
                    $allowedStr = trim(substr(trim($line), 14));
                    break;
                }
            }

            $hasAccess = false;
            if (empty($allowedStr)) {
                $hasAccess = true; // Если пусто - доступен всем
            } else {
                $allowedArr = array_map('trim', explode(',', $allowedStr));
                if (in_array($currentUser, $allowedArr)) {
                    $hasAccess = true;
                }
            }

            if ($hasAccess) {
                $availableProjects[$projName] = $envPath;
            }
        }
    }
}

if (empty($availableProjects)) {
    opsWrite('open_project','error',['reason'=>'Нет доступных проектов']);
    die("У вас нет доступа ни к одному из проектов.");
}

// Определяем текущий проект
$currentProject = $_GET['project'] ?? array_key_first($availableProjects);
if (!isset($availableProjects[$currentProject])) {
    $currentProject = array_key_first($availableProjects);
}

// Чтение конфигурации выбранного проекта для формирования радио-кнопок
$envFile = $availableProjects[$currentProject];
$dirs = [];
$prodAllowedUsersStr = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'PROD_ALLOWED_USERS=') === 0) {
            $prodAllowedUsersStr = trim(substr($line, 19));
        }
        if (strpos($line, 'DIRS=') === 0) {
            $dirsStr = substr($line, 5);
            foreach (explode(',', $dirsStr) as $dirItem) {
                if (!trim($dirItem))
                    continue;
                @list($name, $rest) = explode(':', $dirItem, 2);
                @list($path, $type) = explode('|', $rest, 2);
                if ($name && $type) {
                    $dirs[] = ['name' => trim($name), 'type' => trim($type)];
                }
            }
        }
    }
}

// Фильтрация папок: если тип prod, проверять права
$prodAllowedArr = array_map('trim', explode(',', $prodAllowedUsersStr));
$filteredDirs = [];
foreach ($dirs as $d) {
    if ($d['type'] === 'prod') {
        if (in_array($currentUser, $prodAllowedArr)) {
            $filteredDirs[] = $d;
        }
    } else {
        $filteredDirs[] = $d;
    }
}
$dirs = $filteredDirs;
require_once __DIR__.'/control/api.php';
$fcEnv=[];foreach ($lines as $line) { $pair=explode('=',trim($line),2);if(count($pair)===2) $fcEnv[trim($pair[0])]=trim($pair[1]); }
try {$fc=fcConfig($fcEnv);}catch(Throwable $e){opsWrite('open_project','error',['project'=>$currentProject,'reason'=>$e->getMessage()]);die(htmlspecialchars($e->getMessage()));}
opsWrite('open_project','ok',['project'=>$currentProject]);
function panelAsset($path) {
    $file=__DIR__.'/'.$path;
    return htmlspecialchars($path.'?v='.(is_file($file) ? substr(hash_file('sha256',$file),0,12) : 'missing'), ENT_QUOTES, 'UTF-8');
}
if (empty($_SESSION['fc_csrf'])) $_SESSION['fc_csrf']=bin2hex(random_bytes(24));

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Xuyak2production - <?= htmlspecialchars($currentProject) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= panelAsset('accet/css/style.css') ?>" rel="stylesheet">
    <script>
        // Передаем токен проекта в JS
        const fcCsrf = <?= json_encode($_SESSION['fc_csrf']) ?>;
        const currentProject = <?= json_encode($currentProject) ?>;
    </script>
</head>

<body>
    <!-- Верхнее меню -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Система Деплоя</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <?php foreach ($availableProjects as $pName => $pPath): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $pName === $currentProject ? 'active text-warning fw-bold' : '' ?>"
                                href="?project=<?= urlencode($pName) ?>">
                                <?= htmlspecialchars($pName) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex text-white align-items-center">
                    <span class="me-3">Пользователь: <strong><?= htmlspecialchars($currentUser) ?></strong></span>
                    <a href="?logout=1" class="btn btn-sm btn-outline-light">Выход</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 text-primary">Управление: <?= htmlspecialchars($currentProject) ?></h3>
            <div id="global_status_wrapper" style="display: none; width: 300px;">
                <div class="progress shadow-sm" style="height: 10px;">
                    <div id="global_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <div id="global_status_text" class="text-end text-muted fw-bold mt-1" style="font-size: 0.8rem;">Готово</div>
            </div>
        </div>
        <div class="row">
            <!-- Левая часть -->
            <div class="col-md-6 mb-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">Выбор ветки из репозитория</div>
                    <div class="card-body">
                        <select id="branch_select" class="form-select" size="12" disabled>
                            <option value="">Загрузка веток...</option>
                        </select>
                    </div>
                </div>

                <fieldset class="destinations">
                    <legend class="dest-title">Целевая директория</legend>
                    <div class="dest-meta"><span>Выберите каталог для выкладки</span><button type="button" id="dest_refresh">Обновить ветки</button></div>
                    <?php if (!$dirs): ?><p class="text-muted">Нет доступных директорий.</p><?php endif; ?>
                    <?php foreach ($dirs as $idx => $d): $prod=$d['type']==='prod'; ?>
                    <label class="dest-row">
                        <input class="target-radio" type="radio" name="deploy_target" id="target_<?= $idx ?>" data-guard="<?= isset($fc[$d['name']]) ? 1 : 0 ?>" value="<?= htmlspecialchars($d['name']) ?>" <?= $idx===0?'checked':'' ?>>
                        <span class="dest-card">
                            <span class="dest-folder" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 7V5a1 1 0 0 1 1-1h5l2 3h9a1 1 0 0 1 1 1v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></span>
                            <span class="dest-body"><strong><?= htmlspecialchars($d['name']) ?></strong><small>Контроль FTP: <?= isset($fc[$d['name']]) ? 'включён' : 'выключен' ?></small><small>Ветка: <span class="dest-branch" aria-live="polite">не проверена</span></small></span>
                            <span class="dest-env <?= $prod?'is-prod':'' ?>"><?= $prod?'PROD':'TEST' ?></span><span class="dest-dot" aria-hidden="true"></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </fieldset>

                <div id="fc_box" <?= isset($dirs[0]) && isset($fc[$dirs[0]['name']]) ? '' : 'hidden' ?> class="mt-3 border rounded p-3">
                    <button type="button" id="fc_bind" class="btn btn-outline-secondary" disabled>Привязать к Git</button>
                    <button type="button" id="fc_check" class="btn btn-outline-primary" disabled>Контроль</button>
                    <button type="button" id="fc_sync" class="btn btn-outline-primary" disabled>Синхронизация</button>
                    <small class="d-block mt-2" id="fc_info" role="status"></small>
                </div>
                <button id="btn_deploy" class="btn btn-success btn-lg w-100 mt-4 py-3 fw-bold shadow" disabled>ЗАПУСТИТЬ
                    ДЕПЛОЙ</button>
            </div>

            <!-- Правая часть -->
            <div class="col-md-6 mb-4">
                <div class="card mb-4 shadow-sm h-100">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Лог выполнения терминала</span>
                        <span id="status_text" class="badge bg-secondary">Ожидание</span>
                    </div>
                    <div class="card-body p-0 d-flex flex-column">
                        <pre id="log_box" class="bg-dark text-success p-3 m-0 flex-grow-1" style="height: 525px; overflow-y: auto; font-size: 0.85rem; border-radius: 0 0 calc(0.375rem - 1px) calc(0.375rem - 1px);">Здесь будут отображаться этапы выполнения...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="asset_warning" role="alert" hidden class="container alert alert-warning">Не загрузились скрипты панели. Проверьте файлы в accet/js/ и обновите страницу.</div>
    <noscript><p class="container">Для проверки веток включите JavaScript.</p></noscript>
    <script src="<?= panelAsset('accet/js/jquery-3.6.0.min.js') ?>" onerror="document.getElementById('asset_warning').hidden=false"></script>
    <script src="<?= panelAsset('accet/js/app.js') ?>" onerror="document.getElementById('asset_warning').hidden=false"></script>
</body>

</html>