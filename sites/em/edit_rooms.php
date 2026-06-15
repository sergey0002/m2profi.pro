<?php
session_start();

// Простая авторизация
$auth_user = 'admin';
$auth_pass = '12345';

// Выход
if (isset($_GET['logout'])) {
    unset($_SESSION['authenticated']);
    session_destroy();
    header('Location: ?');
    exit;
}

// Проверка авторизации
if (isset($_POST['login']) && isset($_POST['password'])) {
    if ($_POST['login'] === $auth_user && $_POST['password'] === $auth_pass) {
        $_SESSION['authenticated'] = true;
        header('Location: ?');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Если не авторизован - показать форму
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Авторизация</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .login-form { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
            .login-form h2 { margin: 0 0 20px; color: #333; text-align: center; }
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; margin-bottom: 5px; color: #555; font-size: 14px; }
            .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
            .btn-login { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
            .btn-login:hover { background: #0056b3; }
            .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <form class="login-form" method="POST">
            <h2>Авторизация</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" name="login" required autofocus>
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Войти</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$connection = mysqli_connect('localhost', 'm2profi_em', 'tI9CBndTum14hShc', 'm2profi_em');
if (!$connection) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}
mysqli_set_charset($connection, 'utf8mb4');

// Получаем текущий выбранный дом
$selected_home_id = isset($_GET['home_id']) ? (int)$_GET['home_id'] : null;

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && $selected_home_id) {
    if (isset($_POST['rooms']) && is_array($_POST['rooms'])) {
        foreach ($_POST['rooms'] as $image_pb => $rooms_value) {
            $rooms_value = mysqli_real_escape_string($connection, $rooms_value);
            $image_pb_escaped = mysqli_real_escape_string($connection, $image_pb);
            mysqli_query($connection, "UPDATE apartaments SET rooms = '{$rooms_value}' WHERE image_pb = '{$image_pb_escaped}' AND home_id = {$selected_home_id}");
        }
        echo '<div class="alert-success">Данные сохранены! Обновлены все квартиры с такими планировками.</div>';
    }
}

// Получаем все дома с show=1
$homes_query = mysqli_query($connection, "SELECT home_id, title FROM homes WHERE `show` = 1 ORDER BY `order`, title");
$homes = [];
while ($row = mysqli_fetch_assoc($homes_query)) {
    $homes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование комнат по планировкам</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px 20px 80px 20px; background: #f5f5f5; }
        
        .nav-menu { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; gap: 10px; }
        .nav-menu a { padding: 8px 16px; background: #e9ecef; color: #333; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .nav-menu a:hover { background: #dee2e6; }
        .nav-menu a.active { background: #007bff; color: #fff; }
        
        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb; }
        
        .home-section { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .home-title { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #007bff; }
        
        .apartment-row { display: flex; align-items: center; padding: 12px; border-bottom: 1px solid #eee; transition: background 0.2s; }
        .apartment-row:hover { background: #f8f9fa; }
        .apartment-row:focus-within { background: #fff8e1; }
        
        .col-image { width: 300px; flex-shrink: 0; text-align: center; padding: 5px; }
        .col-image img { max-width: 285px; max-height: 210px; border: 1px solid #ddd; border-radius: 4px; background: #fafafa; }
        .no-image { display: block; width: 285px; height: 150px; line-height: 150px; text-align: center; background: #f0f0f0; color: #999; font-size: 12px; border-radius: 4px; }
        
        .col-input { width: 140px; flex-shrink: 0; padding: 5px 20px; }
        .col-input input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; text-align: center; }
        .col-input input:focus { border-color: #ffc107; outline: none; box-shadow: 0 0 0 3px rgba(255,193,7,0.4); background: #fffde7; }
        .input-label { font-size: 11px; color: #888; text-align: center; margin-bottom: 5px; }
        
        .col-text { flex: 1; padding: 0 20px; min-width: 0; }
        .plan-title { font-size: 16px; font-weight: bold; color: #007bff; margin-bottom: 5px; }
        .apartment-info { font-size: 14px; color: #555; line-height: 1.6; }
        .plan-code { font-size: 12px; color: #999; margin-top: 5px; }
        
        .col-apartments { width: 250px; flex-shrink: 0; padding: 5px 10px; background: #f8f9fa; border-left: 1px solid #eee; }
        .apartments-title { font-size: 12px; font-weight: bold; color: #666; margin-bottom: 5px; }
        .apartments-numbers { font-size: 13px; color: #333; line-height: 1.5; word-break: break-all; }
        
        .btn-save { position: fixed; bottom: 20px; left: 20px; background: #28a745; color: #fff; padding: 15px 35px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 1000; }
        .btn-save:hover { background: #218838; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state h2 { margin-bottom: 10px; }
        
        .stats { font-size: 13px; color: #888; margin-left: 10px; font-weight: normal; }
        
        .count-badge { background: #17a2b8; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px; }
    </style>
</head>
<body>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h1>Редактирование комнат по планировкам</h1>
        <a href="?logout" style="color:#dc3545;text-decoration:none;font-size:14px;">Выход</a>
    </div>
    
    <div class="nav-menu">
        <?php foreach ($homes as $home): ?>
            <a href="?home_id=<?php echo $home['home_id']; ?>" 
               class="<?php echo $selected_home_id == $home['home_id'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($home['title']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <?php if ($selected_home_id): ?>
        <?php
        $home_query = mysqli_query($connection, "SELECT * FROM homes WHERE home_id = {$selected_home_id}");
        $home = mysqli_fetch_assoc($home_query);
        
        // Группируем квартиры по image_pb
        $plans_query = mysqli_query($connection, "
            SELECT 
                image_pb,
                rooms,
                plan_code,
                area,
                COUNT(*) as apartment_count,
                GROUP_CONCAT(apartment_num ORDER BY apartment_num SEPARATOR ', ') as apartments,
                MIN(price) as min_price,
                MAX(price) as max_price
            FROM apartaments 
            WHERE home_id = {$selected_home_id} 
            GROUP BY image_pb, rooms, plan_code, area
            ORDER BY apartment_num
        ");
        $plans_count = mysqli_num_rows($plans_query);
        ?>
        
        <form method="POST">
            <div class="home-section">
                <div class="home-title">
                    <?php echo htmlspecialchars($home['title']); ?>
                    <span class="stats">Уникальных планировок: <?php echo $plans_count; ?></span>
                </div>
                
                <?php if ($plans_count > 0): ?>
                    <?php $i = 1; while ($plan = mysqli_fetch_assoc($plans_query)): ?>
                        <div class="apartment-row">
                            <div class="col-image">
                                <?php if (!empty($plan['image_pb']) && $plan['image_pb'] !== '0'): ?>
                                    <img src="<?php echo htmlspecialchars($plan['image_pb']); ?>" alt="Планировка">
                                <?php else: ?>
                                    <span class="no-image">Нет фото</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-input">
                                <div class="input-label">Комнаты</div>
                                <input type="text" 
                                       name="rooms[<?php echo htmlspecialchars($plan['image_pb']); ?>]" 
                                       value="<?php echo htmlspecialchars($plan['rooms']); ?>" 
                                       placeholder="0"
                                       tabindex="<?php echo $i; ?>"
                                       class="room-input">
                            </div>
                            <div class="col-text">
                                <div class="plan-title">
                                    Планировка #<?php echo $i; ?>
                                    <span class="count-badge"><?php echo $plan['apartment_count']; ?> кв.</span>
                                </div>
                                <div class="apartment-info">
                                    Площадь: <strong><?php echo $plan['area']; ?> м²</strong> &nbsp;|&nbsp;
                                    Цена: <strong><?php echo number_format($plan['min_price'], 0, '', ' '); ?><?php if ($plan['min_price'] != $plan['max_price']) echo ' - ' . number_format($plan['max_price'], 0, '', ' '); ?> ₽</strong>
                                </div>
                                <?php if (!empty($plan['plan_code']) && $plan['plan_code'] !== '0'): ?>
                                    <div class="plan-code">Код: <?php echo htmlspecialchars($plan['plan_code']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-apartments">
                                <div class="apartments-title">Номера квартир:</div>
                                <div class="apartments-numbers"><?php echo $plan['apartments']; ?></div>
                            </div>
                        </div>
                    <?php $i++; endwhile; ?>
                <?php else: ?>
                    <p>В этом доме нет квартир.</p>
                <?php endif; ?>
            </div>
            
            <?php if ($plans_count > 0): ?>
                <button type="submit" name="save" class="btn-save">Сохранить</button>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <div class="empty-state">
            <h2>Выберите дом</h2>
            <p>Для редактирования планировок выберите дом из меню выше</p>
        </div>
    <?php endif; ?>
    
    <script>
    document.querySelectorAll('.room-input').forEach(function(input) {
        input.addEventListener('focus', function() {
            const row = this.closest('.apartment-row');
            if (row) {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
    </script>
</body>
</html>
<?php mysqli_close($connection); ?>
