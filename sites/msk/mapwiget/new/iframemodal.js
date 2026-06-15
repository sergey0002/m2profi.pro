class MapWidget {
    constructor() {
        // Инициализация основных свойств класса
        this.wrapper = null;  // Основной контейнер для модального окна
        this.overlay = null;  // Элемент наложения (оверлей)
        this.window = null;   // Контейнер для iframe
        this.iframe = null;   // iframe для отображения внешнего контента
        this.closeBtn = null; // Кнопка закрытия модального окна
        this.originalParent = null; // Исходный родительский элемент wrapper
        this.originalNextSibling = null; // Исходный следующий sibling для wrapper
        this.inPlaceParent = null; // Элемент, в который перемещается wrapper в методе showInPlace
        this.originalStyles = {}; // Объект для хранения исходных стилей wrapper
        this.animationDuration = 500; // Длительность анимации в миллисекундах
        this.body = document.body; // Кэширование ссылки на document.body для оптимизации
		this.originalIframeHeight = null; // Для хранения исходной высоты iframe

    }

    // Метод для инициализации и создания необходимых элементов
    load(url, wrapperStyles = {}) {
        this.createWrapper(wrapperStyles); // Создаем контейнер wrapper
        this.createOverlay(); // Создаем элемент overlay
        this.createWindow(); // Создаем контейнер для iframe (window)
        this.createIframe(url); // Создаем iframe с указанным URL
        this.createCloseButton(); // Создаем кнопку закрытия модального окна

        // Вкладываем элементы друг в друга
        this.window.appendChild(this.iframe); // Вставляем iframe в контейнер window
        this.wrapper.appendChild(this.overlay); // Вставляем overlay в wrapper
        this.wrapper.appendChild(this.window); // Вставляем window в wrapper

        // Добавляем wrapper в начало body
        this.body.insertBefore(this.wrapper, this.body.firstChild);
    }

    // Метод для создания основного контейнера wrapper
    createWrapper(wrapperStyles) {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'm2wiget_wrapper';
        // Применяем базовые стили к wrapper и объединяем их с дополнительными стилями, переданными в аргументах
        Object.assign(this.wrapper.style, {
            display: 'none', // Скрываем элемент по умолчанию
            position: 'relative',
            width: '1000px',
            height: '500px',
            overflow: 'hidden',
            transition: `opacity ${this.animationDuration}ms ease`, // Анимация изменения прозрачности
            opacity: '0' // Изначальная прозрачность
        }, wrapperStyles);
    }

    // Метод для создания оверлея
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'm2wiget_wrapper_overlay';
        // Применяем стили к overlay
        Object.assign(this.overlay.style, {
            position: 'absolute',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
          //  backgroundColor: 'rgba(0, 0, 0, 0.5)', // Затемняем фон для видимости оверлея
            zIndex: '10',
            display: 'none' // Изначально скрываем оверлей
        });

        // Добавляем обработчик клика на оверлей, чтобы при клике открывалось модальное окно
        this.overlay.addEventListener('click', () => this.show());
    }

    // Метод для создания контейнера window
    createWindow() {
        this.window = document.createElement('div');
        this.window.className = 'm2wiget_wrapper_window';
        // Применяем стили к контейнеру window
        Object.assign(this.window.style, {
            position: 'absolute',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%'
        });
    }

    // Метод для создания iframe
    createIframe(url) {
        this.iframe = document.createElement('iframe');
        this.iframe.src = url; // Устанавливаем URL для iframe
        // Применяем стили к iframe, чтобы он занимал всю площадь контейнера
        Object.assign(this.iframe.style, {
            border: 'none',
            width: '100%',
            height: '100%',
			overflow: 'auto'  // Включаем скроллинг по высоте и ширине
        });
		console.log('Создание iframe');
		// Добавляем событие для подстройки высоты по содержимому
		this.iframe.addEventListener('load', () => {
			this.adjustIframeHeight();
		});
	
		//this.iframe.setAttribute('scrolling', 'auto'); // Устанавливаем атрибут для прокрутки
    }
	
	
	
	
	
	
	
	// Метод для подстройки высоты iframe по содержимому
	adjustIframeHeight() {
		try {
			const iframeContent = this.iframe.contentWindow.document.documentElement;
			const newHeight = iframeContent.scrollHeight;
			console.log(newHeight);
			this.iframe.style.height = `${newHeight}px`;
			
			
			// Устанавливаем высоту m2wiget_wrapper по высоте контента iframe
			this.wrapper.style.height = `${newHeight}px`;
		
		} 
		catch (error) 
		{
			console.error("Ошибка при попытке подстроить высоту iframe:", error);
		}
	}
	
	
	

    // Метод для создания и стилизации кнопки закрытия
    createCloseButton() {
        if (!this.closeBtn) {
            this.closeBtn = document.createElement('button');
            this.closeBtn.innerHTML = '&times;'; // Устанавливаем символ закрытия
            // Применение стилей к кнопке закрытия
            Object.assign(this.closeBtn.style, {
                position: 'absolute',
                top: '10px',
                right: '10px',
                background: 'none',
                border: 'solid',
                borderRadius: '30px',
                fontSize: '30px',
                lineHeight: '28px',
                color: '#000',
				background: '#FFF',
                cursor: 'pointer',
                zIndex: '100',
                display: 'none' // Изначально скрываем кнопку закрытия
            });
            // Добавляем обработчик события клика для закрытия модального окна
            this.closeBtn.addEventListener('click', () => this.close());
            // Вставляем кнопку закрытия в контейнер window
            this.window.appendChild(this.closeBtn);
        }
    }

    // Метод для открытия модального окна
    show() {
		
		// Сохраняем исходную высоту iframe, если она еще не сохранена
		if (!this.originalIframeHeight) {
			this.originalIframeHeight = this.iframe.style.height || 'auto';
		}
		
		// Устанавливаем высоту iframe в 100vh при разворачивании
		this.iframe.style.height = '100vh';
 
        this.saveOriginalStyles(); // Сохраняем исходные стили wrapper
        this.moveToBody(); // Перемещаем wrapper в начало body, если это необходимо
        this.applyModalStyles(); // Применяем стили для отображения как модального окна
        this.toggleElement(this.closeBtn, true); // Показываем кнопку закрытия
        this.toggleOverlay(false); // Скрываем overlay, чтобы он не перекрывал контент
        this.animateOpen(); // Запускаем анимацию открытия (fade-in)
        this.body.style.overflow = 'hidden'; // Отключаем скроллинг страницы
		
		
		
    }

    // Метод для закрытия модального окна
    close() {
        this.animateClose(() => {
			// Восстанавливаем исходную высоту iframe при сворачивании
			if (this.originalIframeHeight) {
				this.iframe.style.height = this.originalIframeHeight;
			}
		
            this.wrapper.style.display = 'none'; // Скрываем wrapper после завершения анимации
            this.restoreOriginalStyles(); // Восстанавливаем исходные стили wrapper
            this.toggleElement(this.closeBtn, false); // Скрываем кнопку закрытия
            this.toggleOverlay(true); // Восстанавливаем видимость overlay
            this.moveToOriginalPosition(); // Перемещаем wrapper обратно на исходное место
            this.body.style.overflow = ''; // Включаем скроллинг страницы
        });
    }

    // Метод для перемещения wrapper в указанное место без модального окна
    showInPlace(targetElement, iframestyle = {}, showoverlay = false) {
        this.toggleElement(this.closeBtn, false); // Скрываем кнопку закрытия, так как окно не модальное
        this.applyStyles({
            display: 'block',
            position: 'relative',
            backgroundColor: 'transparent',
            zIndex: 'auto',
            opacity: '1' // Восстановление полной непрозрачности
        });
        Object.assign(this.wrapper.style, iframestyle); // Применяем дополнительные стили
        this.inPlaceParent = targetElement; // Сохраняем элемент, в который перемещаем wrapper
        this.moveWrapper(targetElement); // Перемещаем wrapper в указанный элемент targetElement
        this.toggleOverlay(showoverlay); // Отображаем или скрываем overlay в зависимости от переданного параметра
    }

    // Вспомогательные методы

    // Сохранение исходных стилей wrapper
    saveOriginalStyles() {
        const computedStyles = window.getComputedStyle(this.wrapper);
        this.originalStyles = {
            display: this.wrapper.style.display || computedStyles.display,
            justifyContent: this.wrapper.style.justifyContent || computedStyles.justifyContent,
            alignItems: this.wrapper.style.alignItems || computedStyles.alignItems,
            position: this.wrapper.style.position || computedStyles.position,
            top: this.wrapper.style.top || computedStyles.top,
            left: this.wrapper.style.left || computedStyles.left,
            width: this.wrapper.style.width || computedStyles.width,
            height: this.wrapper.style.height || computedStyles.height,
            backgroundColor: this.wrapper.style.backgroundColor || computedStyles.backgroundColor,
            zIndex: this.wrapper.style.zIndex || computedStyles.zIndex,
            opacity: this.wrapper.style.opacity || computedStyles.opacity,
        };
    }

    // Восстановление исходных стилей wrapper
    restoreOriginalStyles() {
        Object.assign(this.wrapper.style, this.originalStyles);
    }

    // Перемещение wrapper в начало body, если он там не находится
    moveToBody() {
        if (this.wrapper.parentNode !== this.body) {
            this.originalParent = this.wrapper.parentNode; // Сохраняем текущий родительский элемент
            this.originalNextSibling = this.wrapper.nextSibling; // Сохраняем следующий sibling элемент
            this.body.insertBefore(this.wrapper, this.body.firstChild); // Перемещаем wrapper в начало body
        }
    }

    // Перемещение wrapper обратно на исходное место
    moveToOriginalPosition() {
        if (this.inPlaceParent) {
            this.moveWrapper(this.inPlaceParent); // Если было вызвано showInPlace, возвращаем в inPlaceParent
        } else if (this.originalParent) {
            this.moveWrapper(this.originalParent, this.originalNextSibling); // Иначе возвращаем на исходное место
        }
    }

    // Перемещение wrapper в указанный элемент parent
    moveWrapper(parent, nextSibling = null) {
        if (nextSibling) {
            parent.insertBefore(this.wrapper, nextSibling); // Вставляем перед указанным sibling элементом
        } else {
            parent.appendChild(this.wrapper); // Добавляем в конец parent
        }
    }

    // Применение стилей для модального окна
    applyModalStyles() {
        this.applyStyles({
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            position: 'fixed',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            //backgroundColor: 'rgba(0, 0, 0, 0.8)',
            zIndex: '9999'
        });
    }

    // Применение переданных стилей к wrapper
    applyStyles(styles) {
        Object.assign(this.wrapper.style, styles);
    }

    // Анимация открытия (fade-in)
    animateOpen() {
        setTimeout(() => {
            this.wrapper.style.opacity = '1'; // Устанавливаем полную непрозрачность
        }, 0);
    }

    // Анимация закрытия (fade-out) с выполнением callback по завершению
    animateClose(callback) {
        this.wrapper.style.opacity = '0'; // Устанавливаем прозрачность
        setTimeout(callback, this.animationDuration); // Вызываем callback после завершения анимации
    }

    // Переключение отображения overlay
    toggleOverlay(show) {
        this.toggleElement(this.overlay, show); // Используем универсальный метод для отображения/скрытия overlay
    }

    // Универсальный метод для переключения видимости элемента
    toggleElement(element, show) {
        element.style.display = show ? 'block' : 'none'; // Показываем или скрываем элемент
    }
}
