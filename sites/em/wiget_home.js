



var wrgsv = { 
     idBox: 'wrgsv', 
     url_wiget: 'https://em.m2profi.pro/sahmatka/display_home_public.php', 
     init: function(url,id) { 
        if (!id) { id = this.idBox; } 
		if (!url) { url = this.url_wiget; } 
        if (document.getElementById(id)) { 
            this.addStyle(); 
            try { 
                // для кросс-доменного запроса создаем один из ниже указанных объектов 
                var XHR = ("onload" in new XMLHttpRequest())?XMLHttpRequest:XDomainRequest; 
                // создаем экземпляр объекта 
                var xhr = new XHR(); 
                // запрос на другой домен (асинхронный) 
                xhr.open('GET', url, true); 
                // событие отслеживает, что запрос был успешно завершён 
                xhr.onload = function() { 
                    // если существует ответ 
					
					
					
if (window.jQuery) {
    window.jQuery.holdReady( false );
} 	
                    if (this.response) { 
                        // добавляем полученный ответ в HTML элемент 
                        document.getElementById(id).innerHTML = this.response; 
 

 
                    } 
					else // нет результата (нет данных)
					{
						 $('.hide_no_m2').hide();	 // Скрытие элементов с классом   .hide_no_m2
						 $('.house').hide(); // Скрытие элементов с классом  
					}
                } 
                xhr.onerror = function() { console.log('onerror '+this.status); } 
                // отсылаем запрос 
                xhr.send(); 
            } catch(e) {} 
        } 
        // если на странице не существует HTML элемента с указаным идентификатором 
        // выводим сообщение: блок с идентификатором id="id" отсутствует 
        else { console.log('The specified block id="'+id+'" is missing'); } 
    }
, 
    // метод подключения стилей виджета 
    addStyle: function() { 
        style = document.createElement('link'); 
        style.rel = 'stylesheet'; 
        style.type = 'text/css'; 
        style.href = 'https://em.m2profi.pro/wiget_home.css'; 
        document.head.appendChild(style); 
    }, 

}; 


