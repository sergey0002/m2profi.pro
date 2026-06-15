<?php
// Настройки для HTML кнопок воспроизведения, паузы и перезапуска
$playButtonHtml = '<button class="play-pause-button">▶️</button>';
$pauseButtonHtml = '<button class="play-pause-button">⏸️</button>';
$restartButtonHtml = '<button class="restart-button">🔄</button>';

// Конфигурация для фоновых цветов и изображений
$bg = [
    1 => '#4CAF50',  // Пример: Слайд 1 - зеленый фон
    2 => '#FF5722',  // Пример: Слайд 2 - оранжевый фон
    // Добавьте остальные цвета здесь
];

$bg_img = [
    1 => 'https://example.com/slide1.jpg',  // Пример: Слайд 1 - ссылка на изображение
    2 => 'https://example.com/slide2.jpg',  // Пример: Слайд 2 - ссылка на изображение
    // Добавьте остальные изображения здесь
];

$bg_img_opt = [
    1 => 0.5,  // Пример: Слайд 1 - 50% прозрачности
    2 => 0.7,  // Пример: Слайд 2 - 70% прозрачности
    // Добавьте остальные проценты прозрачности здесь
];

// Параметры по умолчанию
$default_bg_color = '#2E3B2E'; // Цвет фона по умолчанию
$default_bg_img = ''; // Фоновое изображение по умолчанию (пусто, если не задано)
$default_bg_img_opt = 1; // Прозрачность фонового изображения по умолчанию

// Конфигурация функций
$autoplaySlides = true; // Включить/выключить воспроизведение слайдов при перелистывании
$autoAdvance = true; // Включить/выключить автоматическое перелистывание слайдов
$autoStartPlayback = true; // Включить/выключить автоматическое воспроизведение при открытии страницы

// Функция для извлечения числа в начале имени файла
function getNumberFromFileName($fileName) {
    // Проверка, начинается ли имя файла с числа
    if (preg_match('/^(\d+)\./', $fileName, $matches)) {
        return (int)$matches[1]; // Возвращаем число как целое число
    }
    return null; // Если числа нет, возвращаем null
}

// Функция для сортировки файлов
function sortFiles($a, $b) {
    // Извлекаем числа из имен файлов
    $numA = getNumberFromFileName($a);
    $numB = getNumberFromFileName($b);
    
    // Если оба файла содержат числа, сортируем по числам
    if ($numA !== null && $numB !== null) {
        return $numA - $numB;
    } elseif ($numA !== null) {
        return -1; // Файлы с числом идут первыми
    } elseif ($numB !== null) {
        return 1; // Файлы с числом идут первыми
    } else {
        return strcmp($a, $b); // Если числа нет, сортируем по имени файла
    }
}

// Открываем текущую директорию
$dir = opendir(__DIR__);

// Создаем массив для хранения mp3 файлов
$mp3Files = [];

// Сканируем директорию
while (($file = readdir($dir)) !== false) {
    // Проверяем, является ли файл mp3
    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
        $mp3Files[] = $file; // Добавляем файл в массив
    }
}

// Закрываем директорию
closedir($dir);

// Сортируем файлы
usort($mp3Files, 'sortFiles');

// Функция для получения текста описания
function getDescription($fileName) {
    // Извлекаем число из имени файла
    $number = getNumberFromFileName($fileName);
    if ($number !== null) {
        // Формируем путь к файлу с описанием
        $descrFilePath = __DIR__ . '/descr/' . $number . '.htm';
        // Проверяем, существует ли файл с описанием
        if (file_exists($descrFilePath)) {
            return file_get_contents($descrFilePath); // Возвращаем содержимое файла
        }
    }
    return ''; // Если файла нет, возвращаем пустую строку
}

// Функция для получения пути к слайду
function getSlide($fileName) {
    // Извлекаем число из имени файла
    $number = getNumberFromFileName($fileName);
    if ($number !== null) {
        // Формируем путь к файлу с изображением слайда
        $slideFilePath = __DIR__ . '/slides/' . $number . '.jpg';
        // Проверяем, существует ли файл с изображением слайда
        if (file_exists($slideFilePath)) {
            return str_replace(__DIR__, 'https://msk.m2profi.pro/audiogid/player4', $slideFilePath); // Возвращаем путь к изображению
        }
    }
    return ''; // Если файла нет, возвращаем пустую строку
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MP3 Player</title>
    <style>
        /* Основные стили для страницы */
        body {
            background-color: #2E3B2E; /* Фон страницы */
            color: #E3E4E2; /* Цвет текста */
            font-family: Arial, sans-serif; /* Шрифт текста */
            margin: 0;
            padding: 0;
            overflow: hidden; /* Отключаем прокрутку */
        }
        /* Стили для контейнера слайдов */
        .slider {
            height: 100vh; /* Высота контейнера равна высоте окна браузера */
            overflow-y: scroll; /* Вертикальная прокрутка */
            scroll-snap-type: y mandatory; /* Прокрутка с защелкой */
        }
        /* Стили для каждого слайда */
        .player {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Высота слайда равна высоте окна браузера */
            width: 100%;
            box-sizing: border-box;
            padding: 20px;
            border-bottom: 1px solid #4CAF50; /* Нижняя граница */
            scroll-snap-align: start; /* Начало защелки */
            position: relative;
            transition: background-color 0.3s ease; /* Плавный переход для фона */
        }
        /* Стили для полосы прогресса */
        .progress {
            display: inline-block;
            width: 80%; /* Ширина полосы прогресса */
            height: 10px; /* Высота полосы прогресса */
            background-color: #1C1C1C; /* Фон полосы прогресса */
            border: 1px solid #4CAF50; /* Граница полосы прогресса */
            border-radius: 5px; /* Закругленные углы */
            margin: 5px 0;
            cursor: pointer;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            background-color: #4CAF50; /* Цвет полосы прогресса */
            width: 0;
            border-radius: 5px;
            position: relative;
        }
        .progress-bar-handle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            background-color: white;
            border-radius: 50%;
            cursor: pointer;
            margin-left: -7.5px; /* Половина ширины для центровки */
        }
        /* Стили для блока управления */
        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .play-pause-button, .restart-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #4CAF50; /* Цвет кнопок */
            font-size: 50px;
            margin: 0 10px;
        }
        .play-pause-button:hover, .restart-button:hover {
            color: #76C7A4; /* Цвет кнопок при наведении */
        }
        /* Стили для ссылок-якорей */
        .anchor-link {
            color: #4CAF50;
            text-decoration: none;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .nav {
            position: absolute;
            width: 100%;
            text-align: center;
        }
        .nav.up {
            top: 10px;
        }
        .nav.down {
            bottom: 10px;
        }
        .nav a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 20px;
        }
        .nav a:hover {
            color: #76C7A4; /* Цвет ссылок при наведении */
        }
        /* Стили для описания */
        .description {
            margin-top: 20px;
            font-size: 18px;
            text-align: center;
            color: #E3E4E2;
        }
        /* Стили для номера слайда */
        .slide-number {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #E3E4E2;
            font-size: 20px;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
            border-radius: 5px;
        }
        /* Стили для фонового изображения */
        .slide-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-size: cover;
            background-position: center;
            transition: opacity 0.3s ease; /* Плавный переход для прозрачности */
        }
        /* Стили для панели текущего воспроизведения */
        .current-track-panel {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            color: #E3E4E2;
        }
        .current-track-panel .controls {
            margin: 0 10px 0 0;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="slider">
<?php 
foreach ($mp3Files as $index => $file): 
    $previousSlideNumber = $index;
    $nextSlideNumber = $index + 2;
    $currentSlideNumber = $index + 1;
    $number = getNumberFromFileName($file);
    $slideImage = getSlide($file); // Получаем изображение из папки /slides/
    $description = getDescription($file);
    $bgColor = isset($bg[$number]) ? $bg[$number] : $default_bg_color;
    $bgImage = $slideImage ? $slideImage : (isset($bg_img[$number]) ? $bg_img[$number] : $default_bg_img);
    $bgOpacity = isset($bg_img_opt[$number]) ? $bg_img_opt[$number] : $default_bg_img_opt;
    ?>
    <div class="player" id="slide-<?php echo $currentSlideNumber; ?>" data-bg-color="<?php echo $bgColor; ?>" data-bg-image="<?php echo $bgImage; ?>" data-bg-opacity="<?php echo $bgOpacity; ?>" style="background-color: <?php echo $bgColor; ?>;">
        <?php if ($bgImage): ?>
            <div class="slide-bg" style="background-image: url('<?php echo $bgImage; ?>'); opacity: <?php echo $bgOpacity; ?>;"></div>
        <?php endif; ?>
        <div class="slide-number"><?php echo $currentSlideNumber; ?>/<?php echo count($mp3Files); ?></div>
        <?php if ($index > 0): ?>
            <div class="nav up">
                <a href="#slide-<?php echo $previousSlideNumber; ?>" onclick="updateBackground(<?php echo $previousSlideNumber; ?>)">⬆️ Слайд <?php echo $previousSlideNumber; ?></a>
            </div>
        <?php endif; ?>
        <div style="width:80%; text-align:center;">
            <a class="anchor-link" href="#slide-<?php echo $currentSlideNumber; ?>" onclick="updateBackground(<?php echo $currentSlideNumber; ?>)">
                <?php echo preg_replace('/^\d+\./', '', pathinfo($file, PATHINFO_FILENAME)); ?>
            </a>
            <audio id="audio-<?php echo $file; ?>" src="<?php echo $file; ?>" data-slide-index="<?php echo $currentSlideNumber; ?>"></audio>
            <div class="controls">
                <div onclick="togglePlayPause('<?php echo $file; ?>')" id="play-pause-<?php echo $file; ?>">
                    <?php echo $playButtonHtml; ?>
                </div>
                <div onclick="restartAudio('<?php echo $file; ?>')" id="restart-<?php echo $file; ?>">
                    <?php echo $restartButtonHtml; ?>
                </div>
            </div>
            <div class="progress" onclick="setProgress(event, '<?php echo $file; ?>')">
                <div id="progress-bar-<?php echo $file; ?>" class="progress-bar">
                    <div class="progress-bar-handle" draggable="true" ondragstart="startDrag(event)" ondrag="drag(event, '<?php echo $file; ?>')" ondragend="endDrag(event, '<?php echo $file; ?>')"></div>
                </div>
            </div>
            <span id="percentage-<?php echo $file; ?>">0%</span>
            <?php if ($description): ?>
                <div class="description"><?php echo $description; ?></div>
            <?php endif; ?>
        </div>
        <?php if ($index < count($mp3Files) - 1): ?>
            <div class="nav down">
                <a href="#slide-<?php echo $nextSlideNumber; ?>" onclick="updateBackground(<?php echo $nextSlideNumber; ?>)">⬇️ Слайд <?php echo $nextSlideNumber; ?></a>
            </div>
        <?php endif; ?>
        <div class="current-track-panel" id="current-track-<?php echo $currentSlideNumber; ?>" style="display: none;">
            <div class="track-info">
                <span id="current-track-title-<?php echo $currentSlideNumber; ?>"></span>
            </div>
            <div class="controls" id="current-play-pause-<?php echo $currentSlideNumber; ?>" onclick="toggleCurrentPlayPause()">
                <?php echo $playButtonHtml; ?>
            </div>
            <div class="progress">
                <div id="current-progress-bar-<?php echo $currentSlideNumber; ?>" class="progress-bar">
                    <div class="progress-bar-handle" draggable="true" ondragstart="startDrag(event)" ondrag="drag(event, 'current')" ondragend="endDrag(event, 'current')"></div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
// Переменные для отслеживания текущего слайда и трека
var currentTrack = null;
var currentTrackIndex = null;
var autoplaySlides = <?php echo json_encode($autoplaySlides); ?>;
var autoAdvance = <?php echo json_encode($autoAdvance); ?>;
var autoStartPlayback = <?php echo json_encode($autoStartPlayback); ?>;

// Функция для воспроизведения аудио
function playAudio(file) {
    // Останавливаем все другие аудио элементы
    var audioElements = document.getElementsByTagName('audio');
    for (var i = 0; i < audioElements.length; i++) {
        var otherFile = audioElements[i].id.split('-')[1];
        if (otherFile !== file) {
            audioElements[i].pause();
            var playPauseButton = document.getElementById('play-pause-' + otherFile);
            if (playPauseButton) {
                playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
            }
        }
    }
    // Воспроизводим выбранный аудио элемент
    var audio = document.getElementById('audio-' + file);
    audio.play();
    updateProgress(file);
    var playPauseButton = document.getElementById('play-pause-' + file);
    if (playPauseButton) {
        playPauseButton.innerHTML = '<?php echo addslashes($pauseButtonHtml); ?>';
    }
    // Обновляем текущий трек
    currentTrack = file;
    currentTrackIndex = getSlideIndexFromFile(file);
    updateCurrentTrackPanel();

    // Если включено автоматическое перелистывание
    if (autoAdvance) {
        audio.onended = function() {
            var nextSlideIndex = currentTrackIndex + 1;
            if (nextSlideIndex <= <?php echo count($mp3Files); ?>) {
                var nextFile = getFileFromSlideIndex(nextSlideIndex);
                playAudio(nextFile);
                document.getElementById('slide-' + nextSlideIndex).scrollIntoView({ behavior: 'smooth' });
            }
        };
    }
}

// Функция для постановки на паузу аудио
function pauseAudio(file) {
    var audio = document.getElementById('audio-' + file);
    audio.pause();
    var playPauseButton = document.getElementById('play-pause-' + file);
    if (playPauseButton) {
        playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
    }
}

// Функция для перезапуска аудио
function restartAudio(file) {
    // Останавливаем все другие аудио элементы
    var audioElements = document.getElementsByTagName('audio');
    for (var i = 0; i < audioElements.length; i++) {
        var otherFile = audioElements[i].id.split('-')[1];
        if (otherFile !== file) {
            audioElements[i].pause();
            var playPauseButton = document.getElementById('play-pause-' + otherFile);
            if (playPauseButton) {
                playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
            }
        }
    }
    // Воспроизводим выбранный аудио элемент с начала
    var audio = document.getElementById('audio-' + file);
    audio.currentTime = 0;
    audio.play();
    updateProgress(file); // Обновляем прогресс
    var playPauseButton = document.getElementById('play-pause-' + file);
    if (playPauseButton) {
        playPauseButton.innerHTML = '<?php echo addslashes($pauseButtonHtml); ?>';
    }
    // Обновляем текущий трек
    currentTrack = file;
    currentTrackIndex = getSlideIndexFromFile(file);
    updateCurrentTrackPanel();
}

// Функция для переключения между воспроизведением и паузой
function togglePlayPause(file) {
    var audio = document.getElementById('audio-' + file);
    if (audio.paused) {
        playAudio(file);
    } else {
        pauseAudio(file);
    }
}

// Функция для обновления прогресса воспроизведения
function updateProgress(file) {
    var audio = document.getElementById('audio-' + file);
    var progressBar = document.getElementById('progress-bar-' + file);
    var percentageLabel = document.getElementById('percentage-' + file);
    var handle = progressBar.querySelector('.progress-bar-handle');
    audio.ontimeupdate = function() {
        var percentage = (audio.currentTime / audio.duration) * 100;
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            handle.style.left = percentage + '%';
        }
        if (percentageLabel) {
            percentageLabel.innerText = Math.floor(percentage) + '%';
        }
    };
}

// Функция для установки прогресса воспроизведения
function setProgress(event, file) {
    var audio = document.getElementById('audio-' + file);
    var progressContainer = event.currentTarget;
    var rect = progressContainer.getBoundingClientRect();
    var offsetX = event.clientX - rect.left;
    var totalWidth = progressContainer.offsetWidth;
    var percentage = (offsetX / totalWidth);
    audio.currentTime = percentage * audio.duration;
    updateProgress(file); // Обновляем прогресс
}

// Функция для начала перетаскивания
function startDrag(event) {
    event.dataTransfer.setDragImage(new Image(), 0, 0);
}

// Функция для перетаскивания
function drag(event, file) {
    var progressContainer = event.target.parentElement;
    var rect = progressContainer.getBoundingClientRect();
    var offsetX = event.clientX - rect.left;
    var totalWidth = progressContainer.offsetWidth;
    var percentage = (offsetX / totalWidth) * 100;
    if (percentage >= 0 && percentage <= 100) {
        event.target.style.left = percentage + '%';
    }
}

// Функция для окончания перетаскивания
function endDrag(event, file) {
    var audio = document.getElementById('audio-' + file);
    var progressContainer = event.target.parentElement;
    var rect = progressContainer.getBoundingClientRect();
    var offsetX = event.clientX - rect.left;
    var totalWidth = progressContainer.offsetWidth;
    var percentage = (offsetX / totalWidth);
    if (percentage >= 0 && percentage <= 1) {
        audio.currentTime = percentage * audio.duration;
        updateProgress(file); // Обновляем прогресс
    }
}

// Функция для обновления фона слайда
function updateBackground(slideNumber) {
    var player = document.getElementById('slide-' + slideNumber);
    var bgColor = player.getAttribute('data-bg-color');
    var bgImage = player.getAttribute('data-bg-image');
    var bgOpacity = player.getAttribute('data-bg-opacity');

    player.style.backgroundColor = bgColor;

    var slideBg = player.querySelector('.slide-bg');
    if (slideBg) {
        slideBg.style.backgroundImage = 'url(' + bgImage + ')';
        slideBg.style.opacity = bgOpacity;
    } else {
        var newBg = document.createElement('div');
        newBg.className = 'slide-bg';
        newBg.style.backgroundImage = 'url(' + bgImage + ')';
        newBg.style.opacity = bgOpacity;
        player.appendChild(newBg);
    }
}

// Функция для получения индекса слайда по имени файла
function getSlideIndexFromFile(file) {
    return parseInt(document.querySelector(`#audio-${file}`).dataset.slideIndex);
}

// Функция для обновления панели текущего трека
function updateCurrentTrackPanel() {
    if (currentTrack !== null) {
        var panel = document.querySelector(`#current-track-${currentTrackIndex}`);
        var audio = document.getElementById('audio-' + currentTrack);
        if (panel && audio.paused === false) {
            document.querySelectorAll('.current-track-panel').forEach(el => el.style.display = 'none');
            panel.style.display = 'flex';
            document.getElementById(`current-track-title-${currentTrackIndex}`).innerText = `Слайд ${currentTrackIndex}: ${audio.src.split('/').pop().replace(/^\d+\./, '')}`;
            updateCurrentTrackProgress();
        }
    }
}

// Функция для обновления прогресса на панели текущего трека
function updateCurrentTrackProgress() {
    if (currentTrack !== null) {
        var audio = document.getElementById('audio-' + currentTrack);
        var progressBar = document.getElementById(`current-progress-bar-${currentTrackIndex}`);
        var handle = progressBar.querySelector('.progress-bar-handle');
        var percentage = (audio.currentTime / audio.duration) * 100;
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            handle.style.left = percentage + '%';
        }
    }
}

// Функция для переключения воспроизведения текущего трека на панели
function toggleCurrentPlayPause() {
    if (currentTrack !== null) {
        togglePlayPause(currentTrack);
        updateCurrentTrackPanel();
    }
}

// Функция для перелистывания слайдов при воспроизведении
function handleSlideChange() {
    var currentSlide = Math.round($('.slider').scrollTop() / $(window).height()) + 1;
    if (currentSlide !== currentTrackIndex) {
        if (currentTrack !== null) {
            pauseAudio(currentTrack);
        }
        var file = getFileFromSlideIndex(currentSlide);
        playAudio(file);
    }
}

// Функция для получения имени файла по индексу слайда
function getFileFromSlideIndex(index) {
    return document.querySelector(`#slide-${index} audio`).id.split('-')[1];
}

// Обработчик прокрутки для слайдера
$(document).ready(function(){
    $(".slider").on("scroll", function(){
        $(".player").each(function(){
            var player = $(this);
            var slideNumber = player.attr('id').split('-')[1];
            updateBackground(slideNumber);
        });
    });

    // Сбрасываем прокрутку при изменении размера окна
    $(window).on("resize", function(){
        $(".slider").scrollTop(0);
    });

    if (autoStartPlayback) {
        playAudio(getFileFromSlideIndex(1));
    }

    if (autoplaySlides) {
        $(".slider").on("scroll", handleSlideChange);
    }
});
</script>

</body>
</html>
