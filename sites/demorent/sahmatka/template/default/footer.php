</div>
 

<!--[if lt IE 9]>
	<script src="libs/html5shiv/es5-shim.min.js"></script>
	<script src="libs/html5shiv/html5shiv.min.js"></script>
	<script src="libs/html5shiv/html5shiv-printshiv.min.js"></script>
	<script src="libs/respond/respond.min.js"></script>
	<![endif]-->
	 
 
 <!--[if lt IE 9]>
	<script src="js/selectivizr.js"></script>
	<script src="js/html5.js"></script>
	<script src="js/ie9.js"></script>
	<![endif]-->
 

 

	 
	 
<script src="/sahmatka/template/default/libs/jquery.lazy.min.js"></script>
<script src="/sahmatka/template/default/libs/air-datepicker/js/datepicker.min.js"></script>
<script src="/sahmatka/template/default/libs/chartjs/chart.min.js"></script>
<script src="/sahmatka/template/default/libs/slick/slick.min.js"></script>

<script src="/sahmatka/template/default/libs/aos/aos.js"></script>

<script src="/sahmatka/template/default/libs/inputMask/jquery.inputmask.bundle.min.js"></script>

<script src="/sahmatka/template/default/js/jquery.mask.js"></script>
<script>
$('.money').mask('00 000 000 ', {reverse: true});
</script>

<script src="/sahmatka/template/default/js/scripts.js?x=44123123357678"></script>

<? if($_GET['home']==17) 
{
	// 704 скрол на последний подезд
	?> 
	<script>

	$(document).ready(function () {

	$('.objects-cl-nav').slick('slickGoTo', 4,  true);
	$('.objects-cl').slick('slickGoTo', 4,  true);
	});
	</script>
	<?
}
?>



<? if($_GET['home']==12 || $_GET['home']==39  ) 
{
	// 704 скрол на последний подезд
	?> 
	<script>

	$(document).ready(function () {

	$('.objects-cl-nav').slick('slickGoTo', 6,  true);
	$('.objects-cl').slick('slickGoTo', 6,  true);
	});
	</script>
	<?
}
?>
 

<div style="display:none;">
<?
t('Страница готова');
print '<pre>';
print_r($tlog);
 
print '</pre>';
?>
</div>




 
 
<script>
/*
(function () {
    let highlightMode = false;
    let multiSelectMode = false;
    let selectedElements = new Set();
    let cssHashCache = new Map();

    const style = document.createElement('style');
    style.innerHTML = `
        #infoPanel {
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            overflow-y: auto;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
            z-index: 9999;
            padding: 10px;
            display: none;
            font-family: Arial, sans-serif;
            font-size: 14px;
            resize: horizontal;
            overflow: auto;
        }
        #infoPanel h2 {
            margin-top: 0;
            font-size: 18px;
        }
        #toggleInfoPanel, #toggleHighlightMode, #toggleMultiSelectMode {
            position: fixed;
            top: 10px;
            z-index: 10000;
            background-color: #333;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 10px;
        }
        #toggleInfoPanel {
            right: 250px;
        }
        #toggleHighlightMode {
            right: 150px;
        }
        #toggleMultiSelectMode {
            right: 10px;
        }
        .highlight {
            outline: 2px solid red;
        }
        .matched {
            outline: 2px solid green;
        }
        ul.css-properties {
            list-style-type: none;
            padding-left: 15px;
            font-family: monospace;
            margin-bottom: 20px;
        }
        #resizeHandle {
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            cursor: ew-resize;
            background-color: transparent;
        }
    `;
    document.head.appendChild(style);

    function hashString(str) {
        let hash = 0, i, chr;
        for (i = 0; i < str.length; i++) {
            chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0; // Преобразование в 32-битное целое число
        }
        return hash;
    }

    function getAllStyles(element) {
        const computedStyle = window.getComputedStyle(element);
        let allStyles = {};

        for (let i = 0; i < computedStyle.length; i++) {
            let prop = computedStyle[i];
            allStyles[prop] = computedStyle.getPropertyValue(prop);
        }

        return allStyles;
    }

    function generateCSSHash(allStyles) {
        const sortedProps = Object.keys(allStyles).sort();
        let concatenatedStyles = sortedProps.map(prop => `${prop}:${allStyles[prop]}`).join(';');
        return hashString(concatenatedStyles);
    }

    function generateXPath(element) {
        if (element.id !== "") {
            return `//*[@id="${element.id}"]`;
        }
        if (element === document.body) {
            return '/html/body';
        }

        const ix = Array.from(element.parentNode.children).indexOf(element) + 1;
        const tagName = element.tagName.toLowerCase();
        return `${generateXPath(element.parentNode)}/${tagName}[${ix}]`;
    }

    function createCSSHashCache() {
        document.querySelectorAll('*').forEach(element => {
            const xpath = generateXPath(element);
            const allStyles = getAllStyles(element);
            const cssHash = generateCSSHash(allStyles);
            cssHashCache.set(xpath, cssHash);
        });
    }

    function showElementInfo(element) {
        const infoPanel = document.getElementById('infoPanel');
        infoPanel.innerHTML = '';

        const xpath = generateXPath(element);
        const allStyles = getAllStyles(element);
        const cssHash = generateCSSHash(allStyles);

        const fragment = document.createDocumentFragment();

        const xpathHeader = document.createElement('h2');
        xpathHeader.textContent = `XPath: ${xpath}`;
        fragment.appendChild(xpathHeader);

        const hashHeader = document.createElement('h3');
        hashHeader.textContent = `CSS Hash: ${cssHash}`;
        fragment.appendChild(hashHeader);

        const matchingElementsCount = Array.from(cssHashCache.values()).filter(hash => hash === cssHash).length - 1;

        const countHeader = document.createElement('p');
        countHeader.textContent = `Number of elements with the same CSS hash: ${matchingElementsCount}`;
        fragment.appendChild(countHeader);

        const allStylesHeader = document.createElement('h3');
        allStylesHeader.textContent = 'All CSS Properties:';
        fragment.appendChild(allStylesHeader);

        const allStylesList = document.createElement('ul');
        allStylesList.classList.add('css-properties');
        for (let prop in allStyles) {
            let li = document.createElement('li');
            li.textContent = `${prop}: ${allStyles[prop]}`;
            allStylesList.appendChild(li);
        }
        fragment.appendChild(allStylesList);

        infoPanel.appendChild(fragment);
        infoPanel.style.display = 'block';

        highlightMatchingElements(cssHash);
    }

    function highlightMatchingElements(commonStyles) {
        // Снимаем предыдущие подсветки
        document.querySelectorAll('.matched').forEach(el => el.classList.remove('matched'));

        document.querySelectorAll('*').forEach(otherElement => {
            const otherStyles = getAllStyles(otherElement);
            let isMatch = true;

            for (let prop in commonStyles) {
                if (commonStyles[prop] !== otherStyles[prop]) {
                    isMatch = false;
                    break;
                }
            }

            if (isMatch) {
                otherElement.classList.add('matched');
            }
        });
    }

    function showCommonStyles() {
        if (selectedElements.size === 0) return;

        const infoPanel = document.getElementById('infoPanel');
        infoPanel.innerHTML = '';

        const fragment = document.createDocumentFragment();

        const commonStylesHeader = document.createElement('h3');
        commonStylesHeader.textContent = 'Common CSS Properties:';
        fragment.appendChild(commonStylesHeader);

        const commonStylesList = document.createElement('ul');
        commonStylesList.classList.add('css-properties');

        const firstElement = Array.from(selectedElements)[0];
        let commonStyles = getAllStyles(firstElement);

        selectedElements.forEach(element => {
            const elementStyles = getAllStyles(element);
            for (let prop in commonStyles) {
                if (elementStyles[prop] !== commonStyles[prop]) {
                    delete commonStyles[prop];
                }
            }
        });

        for (let prop in commonStyles) {
            let li = document.createElement('li');
            li.textContent = `${prop}: ${commonStyles[prop]}`;
            commonStylesList.appendChild(li);
        }
        fragment.appendChild(commonStylesList);

        infoPanel.appendChild(fragment);
        infoPanel.style.display = 'block';

        // Подсветить элементы с такими же общими стилями
        highlightMatchingElements(commonStyles);
    }

    function init() {
        let infoPanel = document.createElement('div');
        infoPanel.id = 'infoPanel';

        let resizeHandle = document.createElement('div');
        resizeHandle.id = 'resizeHandle';
        infoPanel.appendChild(resizeHandle);

        document.body.appendChild(infoPanel);

        let startX, startWidth;

        resizeHandle.addEventListener('mousedown', function (e) {
            startX = e.clientX;
            startWidth = parseInt(document.defaultView.getComputedStyle(infoPanel).width, 10);
            document.documentElement.addEventListener('mousemove', doDrag, false);
            document.documentElement.addEventListener('mouseup', stopDrag, false);
        });

        function doDrag(e) {
            infoPanel.style.width = (startWidth - e.clientX + startX) + 'px';
        }

        function stopDrag() {
            document.documentElement.removeEventListener('mousemove', doDrag, false);
            document.documentElement.removeEventListener('mouseup', stopDrag, false);
        }

        // Создаем кеш хешей CSS при инициализации
        createCSSHashCache();
    }

    function createButtons() {
        let toggleInfoButton = document.createElement('button');
        toggleInfoButton.id = 'toggleInfoPanel';
        toggleInfoButton.textContent = 'Show Info Panel';

        toggleInfoButton.addEventListener('click', () => {
            let infoPanel = document.getElementById('infoPanel');
            if (infoPanel.style.display === 'none') {
                infoPanel.style.display = 'block';
                toggleInfoButton.textContent = 'Hide Info Panel';
            } else {
                infoPanel.style.display = 'none';
                toggleInfoButton.textContent = 'Show Info Panel';
            }
        });

        let toggleHighlightButton = document.createElement('button');
        toggleHighlightButton.id = 'toggleHighlightMode';
        toggleHighlightButton.textContent = 'Enable Click-to-Highlight Mode';

        toggleHighlightButton.addEventListener('click', (e) => {
            highlightMode = !highlightMode;
            toggleHighlightButton.textContent = highlightMode ? 'Disable Click-to-Highlight Mode' : 'Enable Click-to-Highlight Mode';
            multiSelectMode = false;
            document.getElementById('toggleMultiSelectMode').textContent = 'Enable Multi-Select Mode';
            selectedElements.clear();
            document.querySelectorAll('.highlight').forEach(el => el.classList.remove('highlight'));
            showCommonStyles(); // Clear the info panel
            e.stopPropagation();
        });

        let toggleMultiSelectButton = document.createElement('button');
        toggleMultiSelectButton.id = 'toggleMultiSelectMode';
        toggleMultiSelectButton.textContent = 'Enable Multi-Select Mode';

        toggleMultiSelectButton.addEventListener('click', (e) => {
            multiSelectMode = !multiSelectMode;
            toggleMultiSelectButton.textContent = multiSelectMode ? 'Disable Multi-Select Mode' : 'Enable Multi-Select Mode';
            highlightMode = false;
            document.getElementById('toggleHighlightMode').textContent = 'Enable Click-to-Highlight Mode';
            selectedElements.clear();
            document.querySelectorAll('.highlight').forEach(el => el.classList.remove('highlight'));
            showCommonStyles(); // Clear the info panel
            e.stopPropagation();
        });

        document.body.appendChild(toggleInfoButton);
        document.body.appendChild(toggleHighlightButton);
        document.body.appendChild(toggleMultiSelectButton);
    }

    function handleDocumentClick(event) {
        if (!highlightMode && !multiSelectMode) return;

        const infoPanel = document.getElementById('infoPanel');
        const toggleHighlightButton = document.getElementById('toggleHighlightMode');
        const toggleMultiSelectButton = document.getElementById('toggleMultiSelectMode');
        const toggleInfoButton = document.getElementById('toggleInfoPanel');

        if (infoPanel.contains(event.target) || event.target === toggleHighlightButton || event.target === toggleMultiSelectButton || event.target === toggleInfoButton) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const element = event.target;

        if (highlightMode) {
            document.querySelectorAll('.highlight').forEach(el => el.classList.remove('highlight'));
            element.classList.add('highlight');
            selectedElements.clear();
            selectedElements.add(element);
            showElementInfo(element);
        } else if (multiSelectMode) {
            if (selectedElements.has(element)) {
                element.classList.remove('highlight');
                selectedElements.delete(element);
            } else {
                element.classList.add('highlight');
                selectedElements.add(element);
            }
            showCommonStyles();
        }
    }

    window.addEventListener('load', () => {
        createButtons();
        init();

        document.addEventListener('click', handleDocumentClick, true);
    });
})();

*/


// набор свойств как селектор
(function($) {
    $.fn.cssSelector = function(conditions) {
        // Кэшируем условия как объект {свойство: значение}
        var conditionPairs = {};
        conditions.split(';').forEach(function(condition) {
            var parts = condition.split('=');
            if (parts.length === 2) {
                conditionPairs[parts[0].trim()] = parts[1].trim();
            }
        });

        // Фильтруем элементы, кэшируя вызовы $(this) и css()
        return this.filter(function() {
            var element = $(this);
            return Object.keys(conditionPairs).every(function(property) {
                // Кэшируем значение CSS свойства
                var cssValue = element.css(property);
                return cssValue === conditionPairs[property];
            });
        });
    };
})(jQuery);
/*
// Выбираем все элементы с шириной 100px и красным фоном
var selectedElements = $('.my-class').cssSelector('width=100px; background-color=rgb(255, 0, 0)');

// Работаем с выбранными элементами
selectedElements.css('border', '2px solid green');
*/



</script>
</body>

</html>