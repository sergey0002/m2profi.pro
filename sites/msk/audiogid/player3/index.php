<?php
error_reporting(0);

$playButtonHtml = '<button class="play-pause-button"><img src="https://msk.m2profi.pro/audiogid/play.png" style="max-width:30vw; width: 200px;" /></button>';
$pauseButtonHtml = '<button class="play-pause-button"><img src="https://msk.m2profi.pro/audiogid/pause.png"  style="max-width:30vw; width: 200px;" /></button>';
$restartButtonHtml = '<button class="restart-button"><img src="https://msk.m2profi.pro/audiogid/rew.png"  style="max-width:30vw; width: 60px;" /></button>';

$bg = [
    1 => '#5cb44e',
    2 => '#6ebb62',
    3 => '#5cb44e',
    4 => '#6ebb62',
    5 => '#5cb44e',
    6 => '#6ebb62',
    7 => '#5cb44e',
    8 => '#6ebb62',
    14 => '#6ebb62',
];

$bg_img = [
    1 => 'https://example.com/slide1.jpg',
    2 => 'https://example.com/slide2.jpg',
];

$bg_img_opt = [
    1 => 1,
    2 => 1,
];

$default_bg_color = '#5cb04e';
$default_bg_img = '';
$default_bg_img_opt = 1;

$autoplaySlides = false;
$autoAdvance = false;
$autoStartPlayback = false;
$rewindTime = 5;
$scrollAnimationSpeed = 0;

$dir = opendir(__DIR__);

$slidesWithNumbers = [];
$slidesWithoutNumbers = [];

function getNumberFromFileName($fileName) {
    if (preg_match('/^(\d+)\./', $fileName, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

while (($file = readdir($dir)) !== false) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
        $title = pathinfo($file, PATHINFO_FILENAME);
        $path = './' . $file;
        $number = getNumberFromFileName($file);

        if ($number !== null) {
            $slidesWithNumbers[] = [
                'number' => $number,
                'caption' => $title,
                'audio' => $path,
                'bgColor' => isset($bg[$number]) ? $bg[$number] : $default_bg_color,
                'bgImage' => isset($bg_img[$number]) ? $bg_img[$number] : $default_bg_img,
                'bgOpacity' => isset($bg_img_opt[$number]) ? $bg_img_opt[$number] : $default_bg_img_opt,
                'description' => ''
            ];
        } else {
            $slidesWithoutNumbers[] = [
                'number' => $number,
                'caption' => $title,
                'audio' => $path,
                'bgColor' => $default_bg_color,
                'bgImage' => $default_bg_img,
                'bgOpacity' => $default_bg_img_opt,
                'description' => ''
            ];
        }
    }
}

closedir($dir);

usort($slidesWithNumbers, function($a, $b) {
    return $a['number'] - $b['number'];
});

usort($slidesWithoutNumbers, function($a, $b) {
    return strcmp($a['caption'], $b['caption']);
});

$slides = array_merge($slidesWithNumbers, $slidesWithoutNumbers);

$slidex['number'] = 14;
$slidex['caption'] = 'Контакты';
$slidex['audio'] = false;
$slidex['bgColor'] = '#5cb44e';
$slidex['bgImage'] = false;
$slidex['bgOpacity'] = false;
$slidex['description'] = '
БЦ «W plaza 2»<br/>
117 105, г. Москва, Варшавское шоссе, д. 1,<br/>
строение 17, офис Б105−2<br/>
<br/><br/>

<a href="https://xn--80aci4cj6cf.xn--p1ai/" target="_blank" style="color:#FFF; display:block; padding:10px; font-size:22px;">Усадьбы.РФ</a>
<br/>

<a href="tel:+74993905000" target="_blank" style="color:#FFF; display:block; padding:10px; font-size:22px;">+7 499 390-50-00</a>
<br/>
<a href="mailto:info@m2profi.pro" target="_blank" style="color:#FFF; display:block; padding:10px; font-size:22px;">info@m2profi.pro</a>
<br/>






<a class="sni" target="_blank" href="https://t.me/+Vkwo4M8RQF1lZGQ6"><img src="/audiogid/sn/sn_telegram.svg"></a>

<a class="sni" target="_blank" href="https://vk.com/usadbi_development"><img src="/audiogid/sn/sn_vkontakte.svg"></a>

<a class="sni" target="_blank" href="https://api.whatsapp.com/send/?phone=79831725000&text&type=phone_number&app_absent=0"><img src="/audiogid/sn/sn_whatsapp.svg"></a>

<a class="sni" target="_blank" href="https://www.youtube.com/@usadbi"><img src="/audiogid/sn/sn_youtube.svg"></a>

<a class="sni" target="_blank" href="https://rutube.ru/channel/36762440/"><img width="50" src="/audiogid/sn/sn_rt.svg"></a>
';
$slides[] = $slidex;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pagePiling.js/1.5.6/jquery.pagepiling.css" integrity="sha512-xbp9DExL/1FLDKhQIJNwoCaBjPytQcPMg82UsbBq02kckLcVzQms0+Ot54jXwuBjR6M91vaYHSmqrZlQ/nOEAQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Аудиогид - Усадьбы.РФ</title>
    <style>
	  #pp-nav { display: none; } /* Скрыть навигационные буллеты */
	.sni{display:inline-block; width:50px; margin:5px;}
              *{font-family:Roboto,Arial}
        /* Основные стили для страницы */
        body {
            background-color: #2E3B2E; /* Цвет фона страницы */
            color: #E3E4E2; /* Цвет текста на странице */
            font-family: Arial, sans-serif; /* Шрифт для всей страницы */
            margin: 0;
            padding: 0;
             
        }
 
        /* Стили для полосы прогресса */
        .progress {
            display: inline-block;
            width: 80%;
            height: 2px;
            background-color: #FFF;
            margin: 5px 0;
            cursor: pointer;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            background-color: #7c3a44;
            width: 0;
            position: relative;
        }
        .progress-circle {
            background: #FFF;
            border-radius: 20px;
            width: 20px;
            height: 20px;
            position: absolute;
            right: -2px;
            top: -8px;
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
            color: #4CAF50;
            font-size: 50px;
            margin: 0 10px;
        }
        .play-pause-button:hover, .restart-button:hover {
            color: #76C7A4;
        }
        /* Стили для ссылок-якорей */
        .anchor-link {
            color: #FFF;
            text-decoration: none;
            font-size: 30px;
            margin-bottom: 5vh;
            display: block;
        }
        .nav {
            position: absolute;
            width: 100%;
            text-align: center;
        }
        .nav.up {
            padding: 20px;
            top: 0;
        }
        .restart-button {
            margin-top: 5vh;
        }
        .play-pause-button {
            margin-bottom: 5vh;
        }
        .slide-foter {
            width: 100%;
            position: absolute;
            bottom: 0;
        }
        .footerimg {padding: 10px; text-align: right;}
        .nav.down {
            position: relative;
            padding: 20px;
        }
        .nav a {
            color: #FFF;
            text-decoration: none;
            font-size: 12px;
            opacity: 0.4;
        }
        .nav a:hover {
            color: #FFF;
        }
        .nav span {
            font-size: 30px;
        }
        /* Стили для описания */
        .description {
            margin-top: 20px;
            font-size: 18px;
            text-align: center;
            color: #FFF;
        }
        /* Стили для номера слайда */
        .slide-number {
            position: absolute;
            top: 10px;
            opacity: 0.6;
            left: 10px;
            color: #000;
            font-size: 10px;
            background-color: #FFF;
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
            transition: opacity 0.3s ease;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pagePiling.js/1.5.6/jquery.pagepiling.min.js" integrity="sha512-FcXc9c211aHVJEHxoj2fNFeT8+wUESf/4mUDIR7c31ccLF3Y6m+n+Wsoq4dp2sCnEEDVmjhuXX6TfYNJO6AG6A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <img src="https://msk.m2profi.pro/audiogid/play.png" style="display:none;" /> 
    <img src="https://msk.m2profi.pro/audiogid/pause.png" style="display:none;" /> 
    <img src="https://msk.m2profi.pro/audiogid/rew.png" style="display:none;" /> 
</head>
<body>
<div id="pagepiling">
<?php 
 
foreach ($slides as $index => $slide): 
    $previousSlideNumber = $indexс;
    $nextSlideNumber = $index + 1;
    $currentSlideNumber = $index;
    $bgColor = isset($slide['bgColor']) ? $slide['bgColor'] : $default_bg_color;
    $bgImage = isset($slide['bgImage']) ? $slide['bgImage'] : $default_bg_img;
    $bgOpacity = isset($slide['bgOpacity']) ? $slide['bgOpacity'] : $default_bg_img_opt;

    $pre_slide_caption = isset($slides[$index - 1]) ? preg_replace('/^\d+\./', '', $slides[$index - 1]['caption']) : ''; 
    $post_slide_caption = isset($slides[$index + 1]) ? preg_replace('/^\d+\./', '', $slides[$index + 1]['caption']) : ''; 
    $caption = preg_replace('/^\d+\./', '', $slide['caption']);       
    ?>
    <div class="section" id="slide-<?php echo $currentSlideNumber; ?>" data-bg-color="<?php echo $bgColor; ?>" data-bg-image="<?php echo $bgImage; ?>" data-bg-opacity="<?php echo $bgOpacity; ?>" style="background-color: <?php echo $bgColor; ?>;">
        <?php if ($bgImage): ?>
            <div class="slide-bg" style="background-image: url('<?php echo $bgImage; ?>'); opacity: <?php echo $bgOpacity; ?>;"></div>
        <?php endif; ?>
        <div class="slide-number"><?php echo $currentSlideNumber+1; ?>/<?php echo count($slides); ?></div>
        <?php if ($index > 0): ?>
            <div class="nav up">
                <a href="javascript:void(0);" onclick="$.fn.pagepiling.moveSectionUp()"><span>&#8593;</span> <?php echo $pre_slide_caption; ?></a>
            </div>
        <?php endif; ?>
        <div style="width:100%; text-align:center;">
            <a class="anchor-link" href="javascript:void(0);">
                <?php echo $caption ?>
            </a>
            <?php if (!empty($slide['audio'])): ?>
                <audio id="audio-<?php echo $currentSlideNumber; ?>" src="<?php echo $slide['audio']; ?>" data-slide-index="<?php echo $currentSlideNumber; ?>"></audio>
                <div class="controls">
                    <div onclick="togglePlayPause('<?php echo $currentSlideNumber; ?>')" id="play-pause-<?php echo $currentSlideNumber; ?>">
                        <?php echo $playButtonHtml; ?>
                    </div>
                </div>
                <div class="progress" onclick="setProgress(event, '<?php echo $currentSlideNumber; ?>')">
                    <div id="progress-bar-<?php echo $currentSlideNumber; ?>" class="progress-bar"><div class="progress-circle"></div></div>
                </div>
                <div onclick="restartAudio('<?php echo $currentSlideNumber; ?>')" id="restart-<?php echo $currentSlideNumber; ?>">
                    <?php echo $restartButtonHtml; ?>
                </div>
                <span id="percentage-<?php echo $currentSlideNumber; ?>">0%</span>
            <?php endif; ?>
            <div class="description"><?php echo $slide['description']; ?></div>
        </div>
        <div class="slide-foter">
            <div class="footerimg"><img src="https://msk.m2profi.pro/audiogid/logo.png" style="max-width: 40vw;" /></div>
            <div class="nav down" style="background-color:#139b46">
                <?php if ($index < count($slides) - 1): ?>
                    <a href="javascript:void(0);" onclick="$.fn.pagepiling.moveSectionDown()"><span>&#8595;</span> <?php echo $post_slide_caption; ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<script>
var currentTrack = null;
var currentTrackIndex = null;
var autoplaySlides = <?php echo json_encode($autoplaySlides); ?>;
var autoAdvance = <?php echo json_encode($autoAdvance); ?>;
var autoStartPlayback = <?php echo json_encode($autoStartPlayback); ?>;
var rewindTime = <?php echo json_encode($rewindTime); ?>;
var scrollAnimationSpeed = <?php echo json_encode($scrollAnimationSpeed); ?>;
var userInteracted = false;
var userPaused = false;

document.addEventListener('click', function() {
    userInteracted = true;
});

function playAudio(slideIndex) {
    if (!userInteracted) {
        console.log('User interaction required to play audio.');
        return;
    }

    var audio = document.querySelector(`#audio-${slideIndex}`);
    if (!audio) {
        console.error('Audio element not found for slide: ' + slideIndex);
        return;
    }

    document.querySelectorAll('audio').forEach(function(audioElement) {
        if (audioElement !== audio) {
            audioElement.pause();
            var playPauseButton = document.getElementById('play-pause-' + audioElement.dataset.slideIndex);
            if (playPauseButton) {
                playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
            }
        }
    });

    audio.play();
    updateProgress(slideIndex);
    var playPauseButton = document.getElementById('play-pause-' + slideIndex);
    if (playPauseButton) {
        playPauseButton.innerHTML = '<?php echo addslashes($pauseButtonHtml); ?>';
    }

    currentTrack = slideIndex;
    currentTrackIndex = slideIndex;
    userPaused = false;

    if (autoAdvance) {
        audio.onended = function() {
            var nextSlideIndex = slideIndex + 1;
            if (nextSlideIndex <= <?php echo count($slides); ?>) {
                playAudio(nextSlideIndex);
                $.fn.pagepiling.moveSectionDown();
            }
        };
    }
}

function pauseAudio(slideIndex) {
    var audio = document.querySelector(`#audio-${slideIndex}`);
    if (!audio) {
        console.error('Audio element not found for slide: ' + slideIndex);
        return;
    }
    audio.pause();
    var playPauseButton = document.getElementById('play-pause-' + slideIndex);
    if (playPauseButton) {
        playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
    }
    userPaused = true;
}

function restartAudio(slideIndex) {
    var audio = document.querySelector(`#audio-${slideIndex}`);
    if (!audio) {
        console.error('Audio element not found for slide: ' + slideIndex);
        return;
    }

    document.querySelectorAll('audio').forEach(function(audioElement) {
        if (audioElement !== audio) {
            audioElement.pause();
            var playPauseButton = document.getElementById('play-pause-' + audioElement.dataset.slideIndex);
            if (playPauseButton) {
                playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
            }
        }
    });

    audio.currentTime = 0;
    audio.play();
    updateProgress(slideIndex);
    var playPauseButton = document.getElementById('play-pause-' + slideIndex);
    if (playPauseButton) {
        playPauseButton.innerHTML = '<?php echo addslashes($pauseButtonHtml); ?>';
    }

    currentTrack = slideIndex;
    currentTrackIndex = slideIndex;
    userPaused = false;
}

function togglePlayPause(slideIndex) {
    var audio = document.querySelector(`#audio-${slideIndex}`);
    if (!audio) {
        console.error('Audio element not found for slide: ' + slideIndex);
        return;
    }
    if (audio.paused) {
        playAudio(slideIndex);
    } else {
        pauseAudio(slideIndex);
    }
}

function updateProgress(slideIndex) {
    var audio = document.querySelector(`#audio-${slideIndex}`);
    if (!audio) {
        console.error('Audio element not found for slide: ' + slideIndex);
        return;
    }
    var progressBar = document.getElementById('progress-bar-' + slideIndex);
    var percentageLabel = document.getElementById('percentage-' + slideIndex);
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

function setProgress(event, slideIndex) {
    var audio = document.querySelector(`#audio-${slideIndex}`);
    if (!audio) {
        console.error('Audio element not found for slide: ' + slideIndex);
        return;
    }
    var progressContainer = event.currentTarget;
    var rect = progressContainer.getBoundingClientRect();
    var offsetX = event.clientX - rect.left;
    var totalWidth = progressContainer.offsetWidth;
    var percentage = (offsetX / totalWidth);
    audio.currentTime = percentage * audio.duration;
    updateProgress(slideIndex);
}

// Добавьте следующий код в $(document).ready(function() { ... });
$(document).ready(function() {
    $('#pagepiling').pagepiling({
        menu: null,
        direction: 'vertical',
        verticalCentered: true,
        sectionsColor: [],
        anchors: <?php echo json_encode(array_map(function($slide) { return 'slide-' . $slide['number']; }, $slides)); ?>,
        scrollingSpeed: 300,
        easing: 'easeInQuart',
        loopBottom: false,
        loopTop: false,
        css3: true,
        navigation: {
            'textColor': '#000',
            'bulletsColor': '#000',
            'position': 'right',
            'tooltips': <?php echo json_encode(array_column($slides, 'caption')); ?>
        },
        normalScrollElements: null,
        normalScrollElementTouchThreshold: 20,
        touchSensitivity: 50,
        keyboardScrolling: true,
        sectionSelector: '.section',
        animateAnchor: false,
        onLeave: function(index, nextIndex, direction) {
            if (currentTrack !== null) {
                var audio = document.querySelector(`#audio-${currentTrack}`);
                if (audio) {
                    audio.pause();
                    var playPauseButton = document.getElementById('play-pause-' + currentTrack);
                    if (playPauseButton) {
                        playPauseButton.innerHTML = '<?php echo addslashes($playButtonHtml); ?>';
                    }
                    audio.currentTime = Math.max(0, audio.currentTime - rewindTime);
                }
            }

            if (!userPaused && nextIndex - 1 !== currentTrackIndex) {
                playAudio(nextIndex - 1);
            }
        }
    });

    if (autoStartPlayback) {
        playAudio(0); // Автоматически запускаем воспроизведение первого слайда при загрузке страницы
    }

    // Переход к слайду по якорю при загрузке страницы
    var hash = window.location.hash.substring(1);
    if (hash) {
		 
        var slideIndex = $('#pagepiling .section[id="slide-' + hash + '"]').index() + 1;
        if (slideIndex > 0) {
            $.fn.pagepiling.moveTo(slideIndex);
        }
    }
});
</script>
</body>
</html>
