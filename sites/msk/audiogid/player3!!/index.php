<?php
// Функция для получения числа в начале имени файла
function getNumberFromFileName($fileName) {
    if (preg_match('/^(\d+)\./', $fileName, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

// Функция для сортировки файлов
function sortFiles($a, $b) {
    $numA = getNumberFromFileName($a);
    $numB = getNumberFromFileName($b);
    
    if ($numA !== null && $numB !== null) {
        return $numA - $numB;
    } elseif ($numA !== null) {
        return -1;
    } elseif ($numB !== null) {
        return 1;
    } else {
        return strcmp($a, $b);
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
        $mp3Files[] = $file;
    }
}

// Закрываем директорию
closedir($dir);

// Сортируем файлы
usort($mp3Files, 'sortFiles');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MP3 Player</title>
    <style>
        body {
            background-color: #2E3B2E;
            color: #E3E4E2;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .slider {
            height: 100vh;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
        }
        .player {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            box-sizing: border-box;
            padding: 20px;
            background-color: #364D36;
            border-bottom: 1px solid #4CAF50;
            scroll-snap-align: start;
            position: relative;
        }
        .progress {
            width: 80%;
            height: 10px;
            background-color: #1C1C1C;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            margin: 5px 0;
            cursor: pointer;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            background-color: #4CAF50;
            width: 0;
            border-radius: 5px;
        }
        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .play-pause-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #4CAF50;
            font-size: 50px;
        }
        .play-pause-button:hover {
            color: #76C7A4;
        }
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
            color: #76C7A4;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="slider">
<?php foreach ($mp3Files as $index => $file): 
    $previousFile = $index > 0 ? $mp3Files[$index - 1] : null;
    $nextFile = $index < count($mp3Files) - 1 ? $mp3Files[$index + 1] : null;
    ?>
    <div class="player" id="file-<?php echo pathinfo($file, PATHINFO_FILENAME); ?>">
        <?php if ($previousFile): ?>
            <div class="nav up">
                <a href="#file-<?php echo pathinfo($previousFile, PATHINFO_FILENAME); ?>">⬆️ <?php echo preg_replace('/^\d+\./', '', pathinfo($previousFile, PATHINFO_FILENAME)); ?></a>
            </div>
        <?php endif; ?>
        <a class="anchor-link" href="#file-<?php echo pathinfo($file, PATHINFO_FILENAME); ?>">
            <?php echo preg_replace('/^\d+\./', '', pathinfo($file, PATHINFO_FILENAME)); ?>
        </a>
        <audio id="audio-<?php echo $file; ?>" src="<?php echo $file; ?>"></audio>
        <div class="controls">
            <button class="play-pause-button" onclick="togglePlayPause('<?php echo $file; ?>')" id="play-pause-<?php echo $file; ?>">▶️</button>
        </div>
        <div class="progress" onclick="setProgress(event, '<?php echo $file; ?>')">
            <div id="progress-bar-<?php echo $file; ?>" class="progress-bar"></div>
        </div>
        <span id="percentage-<?php echo $file; ?>">0%</span>
        <?php if ($nextFile): ?>
            <div class="nav down">
                <a href="#file-<?php echo pathinfo($nextFile, PATHINFO_FILENAME); ?>">⬇️ <?php echo preg_replace('/^\d+\./', '', pathinfo($nextFile, PATHINFO_FILENAME)); ?></a>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<script>
function playAudio(file) {
    var audioElements = document.getElementsByTagName('audio');
    for (var i = 0; i < audioElements.length; i++) {
        var otherFile = audioElements[i].id.split('-')[1];
        if (otherFile !== file) {
            audioElements[i].pause();
            var playPauseButton = document.getElementById('play-pause-' + otherFile);
            if (playPauseButton) {
                playPauseButton.innerText = '▶️';
            }
        }
    }

    var audio = document.getElementById('audio-' + file);
    audio.play();
    updateProgress(file);
    var playPauseButton = document.getElementById('play-pause-' + file);
    if (playPauseButton) {
        playPauseButton.innerText = '⏸️';
    }
}

function pauseAudio(file) {
    var audio = document.getElementById('audio-' + file);
    audio.pause();
    var playPauseButton = document.getElementById('play-pause-' + file);
    if (playPauseButton) {
        playPauseButton.innerText = '▶️';
    }
}

function togglePlayPause(file) {
    var audio = document.getElementById('audio-' + file);
    if (audio.paused) {
        playAudio(file);
    } else {
        pauseAudio(file);
    }
}

function updateProgress(file) {
    var audio = document.getElementById('audio-' + file);
    var progressBar = document.getElementById('progress-bar-' + file);
    var percentageLabel = document.getElementById('percentage-' + file);
    
    audio.ontimeupdate = function() {
        var percentage = (audio.currentTime / audio.duration) * 100;
        if (progressBar) {
            progressBar.style.width = percentage + '%';
        }
        if (percentageLabel) {
            percentageLabel.innerText = Math.floor(percentage) + '%';
        }
    };
}

function setProgress(event, file) {
    var audio = document.getElementById('audio-' + file);
    var progressContainer = event.currentTarget;
    var rect = progressContainer.getBoundingClientRect();
    var offsetX = event.clientX - rect.left;
    var totalWidth = progressContainer.offsetWidth;
    var percentage = (offsetX / totalWidth);
    audio.currentTime = percentage * audio.duration;
}

$(document).ready(function(){
    $(".slider").on("scroll", function(){
        $(".player").each(function(){
            if ($(this).position().top <= $(window).height() / 2 && $(this).position().top >= -($(window).height() / 2)) {
                $(this).css("background-color", "#364D36");
            } else {
                $(this).css("background-color", "#2E3B2E");
            }
        });
    });

    $(window).on("resize", function(){
        $(".slider").scrollTop(0);
    });
});
</script>

</body>
</html>
