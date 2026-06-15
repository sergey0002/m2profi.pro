<?php
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
        }
        .player {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #364D36;
            border: 1px solid #4CAF50;
            border-radius: 10px;
        }
        .progress {
            width: 100%;
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
        .controls button {
            background: none;
            border: none;
            cursor: pointer;
            color: #4CAF50;
            font-size: 20px;
            margin: 0 5px;
        }
        .controls button:hover {
            color: #76C7A4;
        }
    </style>
</head>
<body>

<?php foreach ($mp3Files as $file): ?>
    <div class="player">
        <p><?php echo pathinfo($file, PATHINFO_FILENAME); ?></p>
        <audio id="audio-<?php echo $file; ?>" src="<?php echo $file; ?>"></audio>
        <div class="controls">
            <button onclick="playAudio('<?php echo $file; ?>')">▶️</button>
            <button onclick="pauseAudio('<?php echo $file; ?>')">⏸️</button>
            <button onclick="stopAudio('<?php echo $file; ?>')">⏹️</button>
        </div>
        <div class="progress" onclick="setProgress(event, '<?php echo $file; ?>')">
            <div id="progress-bar-<?php echo $file; ?>" class="progress-bar"></div>
        </div>
        <span id="percentage-<?php echo $file; ?>">0%</span>
    </div>
<?php endforeach; ?>

<script>
function playAudio(file) {
    var audio = document.getElementById('audio-' + file);
    audio.play();
    updateProgress(file);
}

function pauseAudio(file) {
    var audio = document.getElementById('audio-' + file);
    audio.pause();
}

function stopAudio(file) {
    var audio = document.getElementById('audio-' + file);
    audio.pause();
    audio.currentTime = 0;
    document.getElementById('progress-bar-' + file).style.width = '0%';
    document.getElementById('percentage-' + file).innerText = '0%';
}

function updateProgress(file) {
    var audio = document.getElementById('audio-' + file);
    var progressBar = document.getElementById('progress-bar-' + file);
    var percentageLabel = document.getElementById('percentage-' + file);
    
    audio.ontimeupdate = function() {
        var percentage = (audio.currentTime / audio.duration) * 100;
        progressBar.style.width = percentage + '%';
        percentageLabel.innerText = Math.floor(percentage) + '%';
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
</script>

</body>
</html>
