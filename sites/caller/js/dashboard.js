// --- AJAX Queue and Progress Bar ---
const ajaxQueue = [];
let isProcessingQueue = false;
const progressBar = document.getElementById('global-progress-bar');

function showProgressBar() {
    if (progressBar) progressBar.classList.add('active');
}

function hideProgressBar() {
    if (progressBar) progressBar.classList.remove('active');
}

async function processQueue() {
    if (isProcessingQueue || ajaxQueue.length === 0) {
        if (ajaxQueue.length === 0) {
            hideProgressBar();
        }
        return;
    }

    isProcessingQueue = true;
    showProgressBar();

    const request = ajaxQueue.shift();
    
    try {
        const response = await fetch(request.url, request.options);
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.statusText}`);
        }
        const data = await response.json();
        if (data.success) {
            request.resolve(data.data);
        } else {
            throw new Error(data.error || 'Unknown server error');
        }
    } catch (error) {
        request.reject(error);
    } finally {
        isProcessingQueue = false;
        // Process next item in the queue
        setTimeout(processQueue, 0);
    }
}

function enqueueRequest(url, options = {}) {
    return new Promise((resolve, reject) => {
        ajaxQueue.push({ url, options, resolve, reject });
        if (!isProcessingQueue) {
            processQueue();
        }
    });
}

function showNotification(text, type = 'success') {
    const backgrounds = {
        success: "linear-gradient(to right, #00b09b, #96c93d)",
        error: "linear-gradient(to right, #ff5f6d, #ffc371)",
        info: "linear-gradient(to right, #00d2ff, #3a7bd5)"
    };

    Toastify({
        text: text,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        style: {
            background: backgrounds[type] || backgrounds.info,
        },
        stopOnFocus: true,
    }).showToast();
}

// Универсальная функция форматирования событий
function renderEvents(events) {
    if (!events || !events.length) return '<div class="event-item">Нет событий</div>';
    // сообщения должны идти от старых к новым (новые снизу)
    return events.slice().reverse().map(event => {
        return `<div class="event-item">${event.datetime} | ${event.user || 'Система'} | ${event.event_type_text || ''} | ${event.comment}</div>`;
    }).join('');
}

// Функция для обновления контента области событий
function reloadEvents(id, rowElement, forceScroll = false) {
    // Сохраняем scrollTop контейнера перед обновлением
    return new Promise((resolve, reject) => {
        const expandedRow = rowElement.nextElementSibling;
        if (!expandedRow) return reject('Expanded row not found');

        const eventContainer = expandedRow.querySelector('.event-container');
        if (!eventContainer) return reject('Event container not found');

        const shouldScrollToBottom = forceScroll || !eventContainer || eventContainer.scrollHeight - eventContainer.scrollTop === eventContainer.clientHeight;
        
        // Show spinner in event container
        eventContainer.innerHTML = '<div class="loading-indicator show"><div class="loading-spinner"></div></div>';

        Promise.all([
            enqueueRequest(`ajax_handler.php?action=get_phones&id=${id}`),
            enqueueRequest(`ajax_handler.php?action=get_events&id=${id}`)
        ])
        .then(([phones, events]) => {
            // Update phones
            const phoneNumbers = expandedRow.querySelector('.phone-numbers');
            if (phoneNumbers) {
                phoneNumbers.innerHTML = `
                    <div><strong>Рабочий телефон:</strong> ${phones.work_phone || "Нет данных"}</div>
                    <div><strong>Мобильный телефон:</strong> ${phones.mobile_phone || "Нет данных"}</div>
                `;
            }

            // Update events with animation
            if (events.events) {
                eventContainer.style.opacity = '0';
                setTimeout(() => {
                    eventContainer.innerHTML = renderEvents(events.events);
                    eventContainer.style.opacity = '1';
                    if (shouldScrollToBottom) {
                        eventContainer.scrollTop = eventContainer.scrollHeight;
                    }
                    resolve(); // Resolve promise after animation
                }, 250); // Short delay for fade-in effect
            } else {
                resolve(); // Resolve promise even if there are no events
            }
        })
        .catch(error => {
            console.error('Ошибка при обновлении данных:', error);
            eventContainer.innerHTML = '<div style="padding: 10px; color: red;">Не удалось загрузить события.</div>';
            reject(error);
        });
    });
}

// --- Logic for unsent comments ---
function getUnsentComments() {
    return JSON.parse(localStorage.getItem('unsentComments')) || {};
}

function saveUnsentComment(id, comment, color) {
    const unsent = getUnsentComments();
    if (!unsent[id]) {
        unsent[id] = [];
    }
    unsent[id].push({ comment, color, timestamp: new Date().getTime() });
    localStorage.setItem('unsentComments', JSON.stringify(unsent));
}

function removeUnsentComments(id) {
    const unsent = getUnsentComments();
    delete unsent[id];
    localStorage.setItem('unsentComments', JSON.stringify(unsent));
}

function sendUnsentComments() {
    const unsent = getUnsentComments();
    for (const id in unsent) {
        unsent[id].forEach(data => {
            // Попытка отправить каждый неотправленный комментарий
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add_comment', id, comment: data.comment, color: data.color })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Если успешно, удаляем из очереди
                    const currentUnsent = getUnsentComments();
                    if (currentUnsent[id]) {
                        currentUnsent[id] = currentUnsent[id].filter(item => item.timestamp !== data.timestamp);
                        if (currentUnsent[id].length === 0) {
                            delete currentUnsent[id];
                        }
                        localStorage.setItem('unsentComments', JSON.stringify(currentUnsent));
                    }
                }
            })
            .catch(err => console.error(`Failed to send unsent comment for id ${id}:`, err));
        });
    }
}

// --- End of unsent comments logic ---

// Функция для сохранения комментария
function saveComment(id) {
    const commentEl = document.getElementById(`new-comment-${id}`);
    const colorEl = document.getElementById(`row-color-${id}`);
    if (!commentEl || !colorEl) return;

    const comment = commentEl.value;
    const color = colorEl.value;
    if (!comment.trim()) return;

    const requestOptions = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add_comment', id, comment, color })
    };

    enqueueRequest('ajax_handler.php', requestOptions)
        .then(() => {
            commentEl.value = '';
            removeUnsentComments(id); // Очищаем очередь для этого ID
            const row = table.getRow(id);
            if (row) {
                const rowElement = row.getElement();
                if (rowElement) {
                    rowElement.style.backgroundColor = rowcolors[color];
                    reloadEvents(id, rowElement, true).then(() => { // Force scroll to bottom
                        showNotification('Комментарий успешно сохранен');
                        row.update({ last_comment: comment }); // Обновляем поле "Примечание"
                    });
                }
            }
        })
        .catch(error => {
            console.error('Ошибка при сохранении, сохраняю в localStorage:', error);
            saveUnsentComment(id, comment, color);
            showNotification('Ошибка сохранения. Комментарий будет отправлен позже.', 'error');
        });
}

// Функция для обновления цвета строки
function updateRowColor(id, color) {
    const requestOptions = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_color', id, color })
    };

    enqueueRequest('ajax_handler.php', requestOptions)
        .then(() => {
            if (window.table) {
                const row = window.table.getRow(id);
                if (row) {
                    row.getElement().style.backgroundColor = rowcolors[color];
                    reloadEvents(id, row.getElement());
                    showNotification('Цвет успешно обновлен');
                }
            }
        })
        .catch(error => {
            console.error('Ошибка при обновлении цвета:', error);
            showNotification('Не удалось обновить цвет', 'error');
        });
}

document.addEventListener("DOMContentLoaded", function() {
    // Попытка отправить неотправленные комментарии при загрузке страницы
    sendUnsentComments();
    // и периодически, например, каждые 30 секунд
    setInterval(sendUnsentComments, 30000);

    // Динамическая генерация колонок теперь происходит в dashboard.php
    // Здесь мы ожидаем, что `dynamic_columns` будет доступна как глобальная переменная
    const final_columns = [
        {title: "ID", field: "id", width: 60, hozAlign: "center", resizable: false, sorter: "number"},
        ...dynamic_columns,
        {
            title: "Телефон",
            field: "phone_search_dummy", // Dummy field for the filter
            hozAlign: "center",
            width: 180,
            headerFilter: "input",
            headerFilterPlaceholder: "Поиск по телефону...",
            headerFilterLiveFilterDelay: 100, // Add a small delay
            resizable: true,
            headerSort: false,
            headerFilterFunc: function(headerValue, rowValue, rowData, filterParams) {
                const normalizedHeader = (headerValue || "").replace(/\D/g, '');
                if (normalizedHeader === '') return true;

                const workPhone = (rowData.work_phone_contact || '').toString().replace(/\D/g, '');
                const mobilePhone = (rowData.mobile_phone_contact || '').toString().replace(/\D/g, '');

                if (workPhone.includes(normalizedHeader)) return true;
                if (mobilePhone.includes(normalizedHeader)) return true;

                return false;
            },
            formatter: function(cell, formatterParams, onRendered) {
                return `<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">Смотреть телефон</button>`;
            },
            cellClick: function(e, cell) {
                let row = cell.getRow();
                let rowElement = row.getElement();
                const rowId = row.getData().id;

                // Check if the current row is already expanded
                if (rowElement.classList.contains("expanded")) {
                    const expandedContent = rowElement.nextElementSibling;
                    if (expandedContent && expandedContent.classList.contains('tabulator-row-expanded')) {
                        expandedContent.classList.remove('show');
                        rowElement.classList.remove('bordered-row');
                        expandedContent.classList.remove('bordered-expanded-content');
                        setTimeout(() => {
                            rowElement.classList.remove("expanded");
                            expandedContent.remove();
                        }, 300);
                    }
                    return;
                }

                // Collapse all other expanded rows immediately
                document.querySelectorAll(".tabulator-row.expanded").forEach(expandedRowElement => {
                    const existingExpandedContent = expandedRowElement.nextElementSibling;
                    if (existingExpandedContent && existingExpandedContent.classList.contains('tabulator-row-expanded')) {
                        existingExpandedContent.classList.remove('show');
                        expandedRowElement.classList.remove("expanded");
                        existingExpandedContent.remove();
                        expandedRowElement.classList.remove('bordered-row'); // Удаляем рамку с основной строки
                        existingExpandedContent.classList.remove('bordered-expanded-content'); // Удаляем рамку с развернутого блока
                    }
                });

                // Log phone view
                enqueueRequest('ajax_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'log_phone_view', id: rowId })
                }).catch(err => console.error('Failed to log phone view:', err));

                let loadingRow = document.createElement("div");
                loadingRow.className = "tabulator-row tabulator-row-expanded bordered-expanded-content"; // Add border class here
                loadingRow.innerHTML = `<div class='tabulator-cell' colspan='100'><div class="loading-indicator show"><div class="loading-spinner"></div></div></div>`;
                rowElement.after(loadingRow);
                rowElement.classList.add("expanded", "bordered-row"); // Add expanded and bordered-row classes

                // Make the loading row appear semi-transparently
                setTimeout(() => {
                    loadingRow.classList.add('show');
                }, 10);

                Promise.all([
                    enqueueRequest(`ajax_handler.php?action=get_phones&id=${rowId}`),
                    enqueueRequest(`ajax_handler.php?action=get_events&id=${rowId}`)
                ])
                .then(([phones, events]) => {
                    const rowData = row.getData();
                    const contentCell = loadingRow.querySelector('.tabulator-cell');
                    
                    // Prepare the final content
                    contentCell.innerHTML = `
                        <div style="padding: 15px;">
                            <div style="margin-bottom: 15px;">
                                <div class="phone-numbers" style="display: flex; gap: 20px; margin-bottom: 15px;">
                                    <div><strong>Рабочий телефон:</strong> ${phones.work_phone || "Нет данных"}</div>
                                    <div><strong>Мобильный телефон:</strong> ${phones.mobile_phone || "Нет данных"}</div>
                                </div>
                                <select id="row-color-${rowData.id}" class="color-select" onchange="updateRowColor('${rowData.id}', this.value)">
                                    ${Object.entries(rowcolors).map(([key, color]) =>
                                        `<option value="${key}" style="background-color: ${color}" ${(rowData.color == key ? 'selected' : '')}>${color}</option>`
                                    ).join('')}
                                </select>
                            </div>
                            <div class="event-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 0; margin-bottom: 15px; font-size: 12px;">
                                ${renderEvents(events.events)}
                            </div>
                            <input type="text" id="new-comment-${row.getData().id}" placeholder="Введите комментарий" style="display: block; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; font-size: 12px;" />
                            <div style="text-align: left;">
                                <button id="save-comment-btn-${row.getData().id}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" style="display: inline-block;">Отправить</button>
                            </div>
                        </div>`;
                    
                    if (typeof rowData.color !== 'undefined' && rowcolors[rowData.color]) {
                        rowElement.style.backgroundColor = rowcolors[rowData.color];
                    }
                    
                    setTimeout(() => {
                        const eventContainer = loadingRow.querySelector('.event-container');
                        if (eventContainer) {
                            eventContainer.scrollTop = eventContainer.scrollHeight;
                        }

                        // Attach event listener for the save comment button
                        const saveCommentBtn = document.getElementById(`save-comment-btn-${row.getData().id}`);
                        if (saveCommentBtn) {
                            saveCommentBtn.addEventListener('click', () => saveComment(row.getData().id));
                        }
                    }, 0);
                })
                .catch(error => {
                    console.error("Ошибка при загрузке событий:", error);
                    loadingRow.querySelector('.tabulator-cell').innerHTML = '<div style="padding: 20px; text-align: center; color: red;">Ошибка при загрузке данных.</div>';
                    loadingRow.classList.add('show');
                });
            }
        },
        {title: "Примечание", field: "last_comment", editor: "textarea", headerFilter: "input", resizable: true, minWidth: 200,
            cellEdited: function(cell) {
                const row = cell.getRow();
                const id = row.getData().id;
                const comment = cell.getValue();
                const color = row.getData().color; // Get current color
                const rowElement = row.getElement();

                const requestOptions = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_comment', id, comment, color })
                };

                enqueueRequest('ajax_handler.php', requestOptions)
                    .then(() => {
                        showNotification('Комментарий успешно сохранен');
                        // Only reload events if the row is actually expanded
                        if (rowElement && rowElement.classList.contains('expanded')) {
                            reloadEvents(id, rowElement, true);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при сохранении, сохраняю в localStorage:', error);
                        saveUnsentComment(id, comment, color);
                        showNotification('Ошибка сохранения. Комментарий будет отправлен позже.', 'error');
                    });
            }
        }
    ];

    window.table = new Tabulator("#caller-table", {
        rowFormatter: function(row) {
            var data = row.getData();
            if (data.color && rowcolors[data.color]) {
                row.getElement().style.backgroundColor = rowcolors[data.color];
            }
        },
        height: "calc(100vh - 150px)",
        layout: "fitColumns",
        placeholder: "Нет данных для отображения",
        ajaxURL: "ajax_handler.php?action=get_caller_data",
        ajaxResponse: function(url, params, response) {
            return response.data; // Return the data property of the response
        },
        columns: final_columns
    });

    // Добавляем слушатели на поля ввода фильтров для принудительного закрытия строк
    function setupFilterListeners() {
        const filters = document.querySelectorAll('.tabulator-header-filter input');
        filters.forEach(filterInput => {
            filterInput.addEventListener('input', () => {
                const expandedContents = document.querySelectorAll(".tabulator-row-expanded");
                expandedContents.forEach(content => {
                    const parentRow = content.previousElementSibling;
                    if (parentRow && parentRow.classList.contains('tabulator-row')) {
                        parentRow.classList.remove('expanded', 'bordered-row');
                    }
                    content.remove();
                });
            });
        });
    }

    // Tabulator может создавать заголовки с задержкой, поэтому мы ждем их появления
    const observer = new MutationObserver((mutations, obs) => {
        const header = document.querySelector('.tabulator-header');
        if (header) {
            setupFilterListeners();
            obs.disconnect(); // отключаем наблюдатель, как только заголовки найдены
        }
    });

    observer.observe(document.getElementById('caller-table'), {
        childList: true,
        subtree: true
    });
});
