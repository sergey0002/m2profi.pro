<?php
class ctr__auth_secure extends ctr__
{
    private $login_attempts_table = 'login_attempts';
    private $max_attempts = 5;
    private $lockout_time = 900; // 15 минут

    /**
     * Отображает форму входа
     */
    public function act__login_form()
    {
        // Проверяем, заблокирован ли IP-адрес
        if ($this->is_ip_blocked($_SERVER['REMOTE_ADDR'])) {
            die('Слишком много неудачных попыток входа. Повторите попытку позже.');
        }
        
        // Просто отображаем шаблон, не оборачивая его в основной layout сайта
        $this->tpl([], 'auth', 'login_form');
    }

    /**
     * Обрабатывает отправленные данные формы входа
     */
    public function act__login()
    {
        global $connection; // Используем глобальное соединение, установленное в config.php

        // Проверяем, заблокирован ли IP-адрес
        if ($this->is_ip_blocked($_SERVER['REMOTE_ADDR'])) {
            die('Слишком много неудачных попыток входа. Повторите попытку позже.');
        }

        if (isset($_POST['submit'])) {
            if (empty($_POST['login'])) {
                echo '<script>alert("Поле логин не заполнено");</script>';
                $this->act__login_form(); // Повторно отобразить форму с ошибкой
                return;
            } elseif (empty($_POST['password'])) {
                echo '<script>alert("Поле пароль не заполнено");</script>';
                $this->act__login_form(); // Повторно отобразить форму с ошибкой
                return;
            } else {
                $login = trim($_POST['login']);
                $password = trim($_POST['password']);

                // Проверяем количество попыток входа для этого логина
                if ($this->is_login_blocked($login)) {
                    echo '<script>alert("Слишком много неудачных попыток входа для этого логина. Повторите попытку позже.");</script>';
                    $this->act__login_form();
                    return;
                }

                // Используем подготовленные выражения для безопасности
                $stmt = $connection->prepare("SELECT users.*, agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption FROM `users` left join agency on agency.admin_user_id = users.id left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = ?");
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    // Записываем неудачную попытку входа
                    $this->record_failed_attempt($_SERVER['REMOTE_ADDR'], $login);
                    
                    echo '<script>alert("Неверный логин или пароль!");</script>';
                    $this->act__login_form(); // Повторно отобразить форму с ошибкой
                    return;
                } else {
                    $user_data = $result->fetch_assoc();
                    
                    // Проверяем пароль с использованием хеширования
                    if (!password_verify($password, $user_data['password'])) {
                        // Записываем неудачную попытку входа
                        $this->record_failed_attempt($_SERVER['REMOTE_ADDR'], $login);
                        
                        echo '<script>alert("Неверный логин или пароль!");</script>';
                        $this->act__login_form(); // Повторно отобразить форму с ошибкой
                        return;
                    }

                    // Успешная аутентификация - удаляем записи о неудачных попытках
                    $this->clear_failed_attempts($_SERVER['REMOTE_ADDR'], $login);

                    // Устанавливаем сессионные переменные (без пароля)
                    $_SESSION['agency_id'] = $user_data['agency_id'];
                    $_SESSION['ucaption'] = $user_data['ucaption'];
                    $_SESSION['adm_caption'] = $user_data['adm_caption'];
                    $_SESSION['sh_login'] = $login;
                    $_SESSION['sh_id'] = $user_data['id'];
                    $_SESSION['sh_name'] = $user_data['name'];
                    $_SESSION['agency_adm_id'] = $user_data['agency_adm_id'];
                    $_SESSION['users_group_id'] = $user_data['users_group_id'];
                    $_SESSION['gl_user_id'] = $user_data['gl_user_id'];
                    // Создаем уникальный токен для сессии для дополнительной безопасности
                    $_SESSION['auth_token'] = bin2hex(random_bytes(32));

                    echo '<div align="center">Вы успешно вошли в систему: ' . $_SESSION['sh_login'] . '</div>';
                    add_log('Выполнен вход в систему');

                    // Редирект на главную страницу или предыдущую
                    header("Location: /sahmatka/ctrind.php?ctr=doc&act=index");
                    exit();
                }
            }
        } else {
            // Если POST['submit'] не установлен, просто отображаем форму
            $this->act__login_form();
        }
    }

    /**
     * Выполняет выход из системы
     */
    public function act__logout()
    {
        //add_log('Выполнен выход из системы');

        // Очищаем сессионные переменные
        unset($_SESSION['auth_token']);
        unset($_SESSION['sh_login']);
        unset($_SESSION['agency_id']);
        unset($_SESSION['sh_name']);
        unset($_SESSION['sh_id']);
        unset($_SESSION['adm_caption']);
        unset($_SESSION['gl_user_id']);
        unset($_SESSION['ucaption']);
        unset($_SESSION['agency_adm_id']);
        unset($_SESSION['users_group_id']);

        // Редирект на страницу входа
        header("Location: /sahmatka/ctrind.php?ctr=auth&act=login_form");
        exit();
    }

    /**
     * Проверяет, авторизован ли пользователь
     * @return bool
     */
    public function is_logged_in()
    {
        return isset($_SESSION['sh_login']) && !empty($_SESSION['sh_login']) && isset($_SESSION['auth_token']);
    }

    /**
     * Проверяет сессию и обновляет данные пользователя, если она активна
     */
    public function check_and_refresh_session()
    {
        global $connection; // Используем глобальное соединение, установленное в config.php

        if ($this->is_logged_in()) {
            $login = $_SESSION['sh_login'];

            $stmt = $connection->prepare("SELECT users.*, agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption FROM `users` left join agency on agency.admin_user_id = users.id left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = ?");
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                // Пользователь в сессии не найден в БД, сессия недействительна
                $this->handle_invalid_session();
            } else {
                // Пользователь найден, обновляем сессию
                $user_data = $result->fetch_assoc();

                $_SESSION['agency_id'] = $user_data['agency_id'];
                $_SESSION['ucaption'] = $user_data['ucaption'];
                $_SESSION['adm_caption'] = $user_data['adm_caption'];
                $_SESSION['sh_login'] = $login;
                $_SESSION['sh_id'] = $user_data['id'];
                $_SESSION['sh_name'] = $user_data['name'];
                $_SESSION['agency_adm_id'] = $user_data['agency_adm_id'];
                $_SESSION['gl_user_id'] = $user_data['gl_user_id'];
            }
        }
    }

    /**
     * Обрабатывает недействительную сессию (очищает и редиректит на логин)
     */
    private function handle_invalid_session()
    {
        add_log('Выполнен выход из системы (сессия недействительна)');
        $this->act__logout(); // Вызываем logout для очистки и редиректа
    }

    /**
     * Проверяет, заблокирован ли IP-адрес из-за частых неудачных попыток
     */
    private function is_ip_blocked($ip_address) {
        global $connection;
        
        $stmt = $connection->prepare("SELECT COUNT(*) as attempts FROM {$this->login_attempts_table} WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL {$this->lockout_time} SECOND)");
        $stmt->bind_param("s", $ip_address);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['attempts'] >= $this->max_attempts;
    }

    /**
     * Проверяет, заблокирован ли логин из-за частых неудачных попыток
     */
    private function is_login_blocked($login) {
        global $connection;
        
        $stmt = $connection->prepare("SELECT COUNT(*) as attempts FROM {$this->login_attempts_table} WHERE login = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL {$this->lockout_time} SECOND)");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['attempts'] >= $this->max_attempts;
    }

    /**
     * Записывает неудачную попытку входа
     */
    private function record_failed_attempt($ip_address, $login) {
        global $connection;
        
        $stmt = $connection->prepare("INSERT INTO {$this->login_attempts_table} (ip_address, login, attempt_time) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $ip_address, $login);
        $stmt->execute();
    }

    /**
     * Очищает записи о неудачных попытках после успешного входа
     */
    private function clear_failed_attempts($ip_address, $login) {
        global $connection;
        
        $stmt = $connection->prepare("DELETE FROM {$this->login_attempts_table} WHERE ip_address = ? AND login = ?");
        $stmt->bind_param("ss", $ip_address, $login);
        $stmt->execute();
    }
}