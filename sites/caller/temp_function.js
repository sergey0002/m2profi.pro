    function reloadEvents(id, rowElement) {
        Promise.all([
            fetch(`get_phones.php?id=${id}`).then(r => r.json()),
            fetch(`get_events.php?id=${id}`).then(r => r.json())
        ])
        .then(([phones, events]) => {
            // Получаем текущий цвет из данных строки
            const currentColor = rowElement.querySelector('select[id^="row-color-"]')?.value || '';
            
            // Обновляем цвет строки
            rowElement.style.backgroundColor = rowcolors[currentColor];
            
            const expandedRow = rowElement.nextElementSibling;
            if (!expandedRow) return;

            // Обновляем select с сохранением выбранного значения
            const colorEl = document.getElementById(`row-color-${id}`);
            if (colorEl) {
                const selectedColor = colorEl.value;
                colorEl.innerHTML = Object.entries(rowcolors).map(([value, color]) => 
                    `<option value="${value}" ${value === selectedColor ? 'selected' : ''}>${color}</option>`
                ).join('');
                
                // Добавляем обработчик изменения цвета если его еще нет
                if (!colorEl.hasChangeListener) {
                    colorEl.hasChangeListener = true;
                    colorEl.addEventListener('change', function() {
                        const newColor = this.value;
                        rowElement.style.backgroundColor = rowcolors[newColor];
                    });
                }
            }

            const container = expandedRow.querySelector('.event-container');
            const phoneNumbers = expandedRow.querySelector('.phone-numbers');
            
            if (phoneNumbers) {
                phoneNumbers.innerHTML = `
                    <div><strong>Рабочий телефон:</strong> ${phones.work_phone || "Нет данных"}</div>
                    <div><strong>Мобильный телефон:</strong> ${phones.mobile_phone || "Нет данных"}</div>
                `;
            }

            if (container && events.events) {
                container.innerHTML = events.events.length ? events.events.map(event => {
                    return `<div class="event-item">
                        <div><strong>${event.datetime}</strong> - <strong>${event.user || 'Система'}</strong></div>
                        <div style="color: #666;">${event.event_type_text || ''}</div>
                        <div style="margin-top: 3px;">${event.comment}</div>
                    </div>`;
                }).join('') : '<div class="event-item">Нет событий</div>';
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(error => console.error('Ошибка при обновлении данных:', error));
    }
