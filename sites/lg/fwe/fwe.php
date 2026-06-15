<?php
// fwe.php — серверная логика FWE-редактора (v2.0, с исправлениями)

// Не забудьте вызвать session_start() в index.php перед включением этого файла.

class FWE
{
    protected $top_dir;
    protected $top_dir_editor;
    protected $fpath;

    public function __construct()
    {
        $this->top_dir        = realpath($_SERVER['DOCUMENT_ROOT']);
        $this->top_dir_editor = ''; // По желанию укажите подкаталог внутри DOCUMENT_ROOT
        $this->fpath          = isset($_REQUEST['f'])
                               ? $this->sanitizePath($_REQUEST['f'])
                               : '';

        // Авторизация
        if (!$this->isLoggedIn()) {
            $this->handleAuth();
            exit;
        }
    }

    /* ========== Авторизация ========== */

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['fwe_loggedin']) && $_SESSION['fwe_loggedin'] === true;
    }

    protected function handleAuth(): void
    {
        // Если POST — пробуем залогиниться
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['pass'])) {
            $login = trim($_POST['login']);
            $pass  = trim($_POST['pass']);
            // Пароль «zdctvjue», захэшируйте и храните хеш в конфиге!
            $hash  = password_hash('zdctvjue', PASSWORD_DEFAULT);
            if ($login === 'admin' && password_verify($pass, $hash)) {
                $_SESSION['fwe_loggedin'] = true;
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $this->printLoginForm('Неверные логин или пароль');
            }
        }
        // По умолчанию показываем форму
        $this->printLoginForm();
        exit;
    }

    protected function printLoginForm(string $error = ''): void
    {
        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Вход в FWE</title>
            <style>
                body{background:#222;color:#eee;font-family:Arial,sans-serif;
                      display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
                .fwe-login{background:#2c2c2c;padding:30px;border-radius:8px;box-shadow:0 2px 10px #000;}
                .fwe-login input{width:100%;margin:8px 0;padding:8px;border-radius:4px;
                                  border:1px solid #444;background:#191919;color:#eee;}
                .fwe-login button{width:100%;padding:10px;border:none;background:#2196f3;
                                  color:#fff;border-radius:4px;font-size:16px;cursor:pointer;}
                .fwe-login .err{color:#ff7373;text-align:center;margin-bottom:8px;}
            </style>
        </head><body>
            <form class="fwe-login" method="post">
                <h3 style="margin:0 0 10px">FWE Авторизация</h3>';
        if ($error) {
            echo '<div class="err">' . htmlspecialchars($error) . '</div>';
        }
        echo '
                <input type="text"    name="login" placeholder="Логин" required autofocus>
                <input type="password" name="pass"  placeholder="Пароль" required>
                <button type="submit">Войти</button>
            </form>
        </body></html>';
    }

    /* ========== Безопасность путей ========== */

    protected function sanitizePath(string $path): string
    {
        // Убираем «..», «./» и обратные слэши
        $p = str_replace(['..', './', '\\'], '', $path);
        return ltrim($p, '/');
    }

    protected function realFilePath(string $relative)
    {
        $abs = realpath($this->top_dir . '/' . $relative);
        // Доступ только внутри DOCUMENT_ROOT
        if (!$abs || strpos($abs, $this->top_dir) !== 0) {
            return false;
        }
        return $abs;
    }

    /* ========== AJAX API для jsTree и редактора ========== */

    // Возвращает JSON для jsTree
public function act__jsoonajaxftree(): void
{
    // если запрошен root — вернуть виртуальный корень
    if (!isset($_GET['id']) || $_GET['id'] === '#' || $_GET['id'] === '') {
        $rootName = '/'; // или другое имя
        $rootId = '/'; // идентификатор корня
        $out = [[
            'id'     => $rootId,
            'parent' => '#',
            'text'   => '<span class="fwe_jstree_fn">' . htmlspecialchars($rootName) . '</span>',
            'a_attr' => [
                'dpath' => $rootId,
                'dtype' => 'dir'
            ],
            'type'   => 'dir',
            'icon'   => 'folder',
            'children' => true
        ]];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        return;
    }

    // иначе — обычная выдача потомков для узла
    $dir = ltrim($_GET['id'], '/');
    $fs_path = $this->realFilePath($dir);
    if (!$fs_path || !is_dir($fs_path)) {
        header('Content-Type: application/json; charset=utf-8');
        echo '[]';
        return;
    }

    $out = [];
    foreach (scandir($fs_path) as $file) {
        if ($file === '.' || $file === '..') continue;
        $rel = ltrim(($dir ? $dir . '/' : '') . $file, '/');
        $abs = $this->realFilePath($rel);
        if (!$abs) continue;
        $isDir = is_dir($abs);

        $out[] = [
            'id'     => '/' . $rel,              // id с ведущим / (или без — но должен совпадать с parent)
            'parent' => $_GET['id'],             // parent — id родителя, то есть "/" для корня
            'text'   => '<span class="fwe_jstree_fn">' . htmlspecialchars($file) . '</span>',
            'a_attr' => [
                'dpath' => '/' . $rel,
                'dtype' => $isDir ? 'dir' : 'file'
            ],
            'type'   => $isDir ? 'dir' : 'file',
            'icon'   => $isDir ? 'folder' : 'file file-' . pathinfo($file, PATHINFO_EXTENSION),
            'children' => $isDir
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
}


    // Загружает содержимое файла
    public function act__loadfile(): void
    {
        $file = isset($_GET['f']) ? $this->sanitizePath($_GET['f']) : '';
        $abs  = $this->realFilePath($file);
        if (!$abs || !is_file($abs)) {
            $this->sendError('Файл не найден');
        }

        $mime = mime_content_type($abs);
        $ext  = pathinfo($abs, PATHINFO_EXTENSION);
        $data = '';

        if (strpos($mime, 'text/') === 0
            || in_array($mime, ['application/octet-stream','application/x-empty'], true)
        ) {
            $data = file_get_contents($abs);
        } else {
            $data = "Тип не поддерживается ($mime)";
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'mime' => $mime,
            'size' => filesize($abs),
            'ext'  => $ext,
            'data' => $data
        ]);
    }
	
	
	
	
	
	
	
	// Загружает содержимое файла
	public function act__listdir(): void
	{
		// Универсально поддерживаем ?f= и ?d=
		$file = '';
		if (isset($_GET['f']))      $file = $this->sanitizePath($_GET['f']);
		elseif (isset($_GET['d']))  $file = $this->sanitizePath($_GET['d']);

		$abs  = $this->realFilePath($file);
		if (!$abs || !is_dir($abs)) {
			$this->sendError('Директория не найдена');
		}

		$out = [];
		foreach (scandir($abs) as $item) {
			if ($item === '.' || $item === '..') continue;
			$rel    = ltrim(($file ? $file . '/' : '') . $item, '/');
			$isDir  = is_dir($abs . '/' . $item);
			$out[] = [
				'name' => $item,
				'path' => $rel,
				'type' => $isDir ? 'dir' : 'file',
			];
		}

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['files' => $out]);
	}

	
	
	
	
	
	# СКАЧИВАНИЕ ДИРЕКТОРИИ
	public function act__downloaddir(): void
	{
		$dir = isset($_GET['d']) ? $this->sanitizePath($_GET['d']) : '';
		$abs = $this->realFilePath($dir);
		if (!$abs || !is_dir($abs)) $this->sendError('Директория не найдена');
		$zipFile = tempnam(sys_get_temp_dir(), 'fwezip');
		$zip = new ZipArchive();
		$zip->open($zipFile, ZipArchive::OVERWRITE);
		$baseLen = strlen($abs) + 1;
		$this->addDirToZip($abs, $zip, $baseLen);
		$zip->close();
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="'.basename($abs).'.zip"');
		header('Content-Length: ' . filesize($zipFile));
		readfile($zipFile);
		unlink($zipFile);
		exit;
	}
	private function addDirToZip($dir, $zip, $baseLen) {
		foreach (scandir($dir) as $file) {
			if ($file == '.' || $file == '..') continue;
			$fullPath = $dir . DIRECTORY_SEPARATOR . $file;
			$localPath = substr($fullPath, $baseLen);
			if (is_dir($fullPath)) {
				$zip->addEmptyDir($localPath);
				$this->addDirToZip($fullPath, $zip, $baseLen);
			} else {
				$zip->addFile($fullPath, $localPath);
			}
		}
	}
		
	
	
	# СКАЧИВАНИЕ ФАЙЛА ИЗ ТОЛБАРА
	public function act__downloadfile(): void
	{
		$file = isset($_GET['f']) ? $this->sanitizePath($_GET['f']) : '';
		$abs  = $this->realFilePath($file);

		if (!$abs || !is_file($abs)) {
			$this->sendError('Файл не найден');
		}

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($abs).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($abs));
		readfile($abs);
		exit;
	}
	
	
	
	# УДАЛЕНИЕ ИЗ ТОЛБАРА!!!!!!!!!!!!! 
	public function act__delete(): void
	{
		// Получаем параметры из POST в первую очередь, а если их нет — из GET
		$isDir = isset($_POST['d']) || isset($_GET['d']);
		$path = null;
		if ($isDir) {
			$path = $this->sanitizePath($_POST['d'] ?? $_GET['d'] ?? '');
		} else {
			$path = $this->sanitizePath($_POST['f'] ?? $_GET['f'] ?? '');
		}

		if (!$path) $this->sendError('Путь для удаления не указан!');

		$abs  = $this->realFilePath($path);
		if (!$abs || !file_exists($abs)) $this->sendError('Файл/папка не найдены');

		if ($isDir) {
			$this->deleteDir($abs);
		} else {
			if (!unlink($abs)) $this->sendError('Ошибка удаления');
		}
		echo json_encode(['ok' => true]);
	}
	private function deleteDir($dir) {
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') continue;
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (is_dir($path)) $this->deleteDir($path);
			else unlink($path);
		}
		rmdir($dir);
	}


	# АПЛОАД И ОБНОВЛЕНИЕ ФАЙЛА 
// АПЛОАД И ОБНОВЛЕНИЕ ФАЙЛА
public function act__upload(): void
{
    // Для файлов: ?f=...
    // Для директорий: ?d=...
    if (!isset($_FILES['file'])) {
        $this->sendError('Файл не получен');
    }
    $isDir = isset($_GET['d']);
    $target = $this->sanitizePath($isDir ? $_GET['d'] : $_GET['f'] ?? '');
    $abs = $this->realFilePath($target);

    // Доп. защита: файл действительно загружен?
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errmap = [
            UPLOAD_ERR_INI_SIZE   => 'Размер файла превышает upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE  => 'Размер файла превышает MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL    => 'Файл загружен не полностью',
            UPLOAD_ERR_NO_FILE    => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Нет временной директории',
            UPLOAD_ERR_CANT_WRITE => 'Ошибка записи на диск',
            UPLOAD_ERR_EXTENSION  => 'PHP-расширение остановило загрузку',
        ];
        $err = $errmap[$_FILES['file']['error']] ?? 'Ошибка загрузки файла';
        $this->sendError($err);
    }

    if ($isDir) {
        if (!$abs || !is_dir($abs)) $this->sendError('Папка не найдена');
        if (!is_writable($abs)) $this->sendError('Нет прав на запись в папку');
        $dest = $abs . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
    } else {
        if (!$abs || !is_file($abs)) $this->sendError('Файл не найден');
        if (!is_writable($abs)) $this->sendError('Нет прав на запись в файл');
        $dest = $abs;
    }

    // Пробуем загрузить файл
    if (!@move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
        // Доп. проверка: почему не удалось
        if (!is_writable(dirname($dest))) {
            $this->sendError('Нет прав на запись в папку назначения');
        }
        if (file_exists($dest) && !is_writable($dest)) {
            $this->sendError('Нет прав на перезапись файла');
        }
        $this->sendError('Ошибка загрузки. Возможно, нехватка прав или место на диске.');
    }

    echo json_encode(['ok' => true]);
}

public function act__rename() {
    // Для POST всегда!
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->sendJson([
            'ok' => false,
            'error' => 'Неверный метод. Используйте POST.',
            'debug' => ['method' => $_SERVER['REQUEST_METHOD']]
        ]);
    }

    $rel    = $this->sanitizePath($_POST['f'] ?? $_POST['d'] ?? '');
    $newName = trim($_POST['newName'] ?? '');
    $isFile = isset($_POST['f']);

    // 1. Входные проверки
    if (!$rel) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Не указан путь к файлу или директории!',
            'debug' => ['rel' => $rel, 'POST' => $_POST]
        ]);
    }
    if (!$newName) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Не указано новое имя!',
            'debug' => ['newName' => $newName, 'POST' => $_POST]
        ]);
    }
    if (preg_match('#[\/\\\\]#', $newName)) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Имя не должно содержать / или \\',
            'debug' => ['newName' => $newName]
        ]);
    }

    // 2. Проверяем исходный путь
    $oldAbs = $this->realFilePath($rel);
    if (!$oldAbs || !file_exists($oldAbs)) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Исходный файл или папка не найдены!',
            'debug' => ['rel' => $rel, 'oldAbs' => $oldAbs]
        ]);
    }
    // 3. Не меняем имя на то же самое
    $base = dirname($oldAbs);
    $newAbs = $base . DIRECTORY_SEPARATOR . $newName;

    if ($oldAbs === $newAbs) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Имя не изменилось!',
            'debug' => ['oldAbs' => $oldAbs, 'newAbs' => $newAbs]
        ]);
    }

    // 4. Проверка на существование нового файла/папки
    if (file_exists($newAbs)) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Такой файл или папка уже существует!',
            'debug' => ['newAbs' => $newAbs]
        ]);
    }

    // 5. Проверка на выход за пределы DOCUMENT_ROOT
    $newRealBase = realpath($base);
    if (!$newRealBase) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Ошибка получения пути к базе для нового файла!',
            'debug' => ['base' => $base]
        ]);
    }
    $newRealAbs = $newRealBase . DIRECTORY_SEPARATOR . $newName;
    if (strpos($newRealAbs, $this->top_dir) !== 0) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Новый путь выходит за пределы разрешённой директории!',
            'debug' => ['newRealAbs' => $newRealAbs, 'top_dir' => $this->top_dir]
        ]);
    }

    // 6. Проверка прав на запись в директории
    if (!is_writable($base)) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Нет прав на запись в директории!',
            'debug' => ['base' => $base]
        ]);
    }
    if (!is_writable($oldAbs)) {
        $this->sendJson([
            'ok' => false,
            'error' => 'Нет прав на изменение файла/папки!',
            'debug' => ['oldAbs' => $oldAbs]
        ]);
    }

    // 7. Переименование
    if (@rename($oldAbs, $newAbs)) {
        $relPath = ltrim(str_replace($this->top_dir, '', $newAbs), '/\\');
        $this->sendJson(['ok' => true, 'newPath' => $relPath]);
    } else {
        $errorMsg = error_get_last();
        $this->sendJson([
            'ok' => false,
            'error' => 'Ошибка при переименовании!',
            'debug' => [
                'oldAbs' => $oldAbs,
                'newAbs' => $newAbs,
                'error_get_last' => $errorMsg
            ]
        ]);
    }
}


	protected function sendJson($arr) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($arr, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	
	
	
	
	public function act__fileinfo(): void
	{
		$file = isset($_GET['f']) ? $this->sanitizePath($_GET['f']) : '';
		$abs  = $this->realFilePath($file);

		if (!$abs || !is_file($abs)) {
			$this->sendError('Файл не найден');
		}

		$size = filesize($abs);
		$mtime = filemtime($abs);

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'size' => $this->formatBytes($size),
			'mtime' => date('Y-m-d H:i:s', $mtime),
			// Дополнительно можно вернуть имя, расширение и др.
			'name' => basename($abs),
			'ext'  => pathinfo($abs, PATHINFO_EXTENSION),
			'path' => $file,
		]);
	}
	protected function formatBytes($bytes, $precision = 2)
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}






	# МЕТАДАННЫЕ ДИРЕКТОРИИ толбар
	public function act__dirinfo(): void
	{
		// Универсально поддерживаем ?f= и ?d=
		$file = '';
		if (isset($_GET['f']))      $file = $this->sanitizePath($_GET['f']);
		elseif (isset($_GET['d']))  $file = $this->sanitizePath($_GET['d']);

		$abs  = $this->realFilePath($file);
		if (!$abs || !is_dir($abs)) {
			$this->sendError('Директория не найдена');
		}

		$out = [];
		foreach (scandir($abs) as $item) {
			if ($item === '.' || $item === '..') continue;
			$rel    = ltrim(($file ? $file . '/' : '') . $item, '/');
			$isDir  = is_dir($abs . '/' . $item);
			$out[] = [
				'name' => $item,
				'path' => $rel,
				'type' => $isDir ? 'dir' : 'file',
			];
		}

		$totalSize = $this->formatBytes($this->dirSize($abs));

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['files' => $out, 'totalSize' => $totalSize]);
	}
	private function dirSize($dir) {
		$size = 0;
		foreach (scandir($dir) as $file) {
			if ($file === '.' || $file === '..') continue;
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if (is_dir($path)) $size += $this->dirSize($path);
			else $size += filesize($path);
		}
		return $size;
	}







	
    // Сохраняет файл (POST)
	// Сохраняет файл (POST)
	public function act__savefile(): void
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->sendError('Неверный метод');
		}
		$file = isset($_GET['f']) ? $this->sanitizePath($_GET['f']) : '';
		$abs  = $this->realFilePath($file);
		if (!$abs || !is_file($abs)) {
			$this->sendError('Файл не найден');
		}
		if (!is_writable($abs)) {
			$this->sendError('Нет прав на запись в файл');
		}
		// Проверим директорию файла на доступность
		$dir = dirname($abs);
		if (!is_writable($dir)) {
			$this->sendError('Нет прав на запись в папку ' . $dir);
		}

		$content = $_POST['finput'] ?? '';
		$this->save_backupfile($file);

		$result = @file_put_contents($abs, $content);
		if ($result === false) {
			if (disk_free_space($dir) < strlen($content)) {
				$this->sendError('Недостаточно места на диске для сохранения файла');
			}
			$this->sendError('Ошибка записи файла (возможно, нехватка прав или места)');
		}
		$this->sendSuccess('ОК');
	}

    // Делает бэкап перед сохранением
    protected function save_backupfile(string $orig): void
    {
        $rel = $this->sanitizePath($orig);
        $abs = $this->realFilePath($rel);
        if (!$abs || !is_file($abs)) return;

        $dir  = __DIR__ . '/backup/' . dirname($rel);
        $this->create_path($dir);
        $new  = date('Ymd_His') . '__' . basename($rel);
        @copy($abs, $dir . '/' . $new);
    }

    protected function create_path(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    protected function sendError(string $msg): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $msg]);
        exit;
    }

    protected function sendSuccess(string $msg): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'msg' => $msg]);
        exit;
    }

    /* ========== HTML-дерево для левой панели ========== */

    public function print_tree(): string
    {
        return '<ul id="browser">' . $this->get_ftree_html() . '</ul>';
    }

    protected function get_ftree_html(string $dir = '', int $level = 0): string
    {
        $fs = $this->realFilePath($dir ?: $this->top_dir_editor);
        if (!$fs || !is_dir($fs)) return '';

        $html = '';
        foreach (scandir($fs) as $file) {
            if ($file === '.' || $file === '..') continue;
            $rel  = ltrim(($dir ? $dir . '/' : '') . $file, '/');
            $abs  = $this->realFilePath($rel);
            if (!$abs) continue;
            $isDir = is_dir($abs);

            if ($isDir) {
                $html .= '<li class="tree_dir">'
                      .  '<a href="?ajax=1&f=' . urlencode($rel) . '" class="tree_dira">'
                      .  htmlspecialchars($file)
                      .  '</a><ul>'
                      .  $this->get_ftree_html($rel, $level + 1)
                      .  '</ul></li>';
            } else {
                $html .= '<li class="tree_file">'
                      .  '<a href="index.php?ajax=1&act=loadfile&f=' . urlencode($rel) . '" '
                      .  'class="tree_filea" data-path="' . htmlspecialchars($rel) . '">'
                      .  htmlspecialchars($file)
                      .  '</a></li>';
            }
        }
        return $html;
    }

    // Главная точка входа для непро AJAX
    public function act__index(): void
    {
        global $fw_tplx;
        $fw_tplx['leftpanel'] = $this->print_tree();
    }
}
















/* ========== Шаблонизатор ========== */
class fw_tpl
{
    public function attr(string $name): string
    {
        global $fw_tplx;
        return $fw_tplx[$name] ?? 'НЕТ ПЕРЕМЕННОЙ "' . htmlspecialchars($name) . '"';
    }
}

/* ========== Роутер ========== */
class router_fwe
{
    public $ctr_get = 'ctr';
    public $act_get = 'act';

    public function action_content(string $action = ''): bool
    {
        if (!$action && isset($_GET[$this->act_get])) {
            $action = $_GET[$this->act_get];
        }
        if (!$action) {
            $action = 'index';
        }
        $method = 'act__' . $action;
        if (method_exists('FWE', $method)) {
            $obj = new FWE();
            $obj->$method();
        } else {
            echo 'no method';
        }
        return true;
    }
}

// Экземпляр шаблонизатора (для tpl.php)
$fw_tpl = new fw_tpl();
