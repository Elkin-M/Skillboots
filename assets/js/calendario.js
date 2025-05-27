// Código mejorado para el calendario y usuarios en línea

function initCalendar() {
    console.log('initCalendar: Inicializando calendario');
    const calendarContainer = document.getElementById('calendar-container');
    if (!calendarContainer) {
        console.log('initCalendar: No se encontró #calendar-container');
        return;
    }    
    
    // Obtener fecha actual
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();
    console.log(`initCalendar: Mes actual: ${currentMonth}, Año actual: ${currentYear}`);

    // Renderizar calendario inicial
    renderCalendar(currentMonth, currentYear);
    
    // Cargar eventos del calendario con retry
    loadCalendarEventsWithRetry(currentMonth, currentYear);
}
// Función para marcar contenido como visto
// Función para marcar contenido como visto - versión actualizada
function markContentAsViewed(contentId, courseId = null) {
    // Verificar que tenemos un ID de contenido válido
    if (!contentId || contentId <= 0) {
        console.error('ID de contenido inválido:', contentId);
        return Promise.reject('ID de contenido inválido');
    }

    // Obtener CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     window.csrfToken || 
                     '';

    if (!csrfToken) {
        console.error('CSRF token no encontrado');
        return Promise.reject('CSRF token no encontrado');
    }

    // Preparar datos para enviar
    const formData = new FormData();
    formData.append('content_id', contentId);
    formData.append('csrf_token', csrfToken);
    
    if (courseId) {
        formData.append('course_id', courseId);
    }

    // Determinar la ruta correcta basada en la ubicación actual
    let apiPath = '';
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/student/') || currentPath.includes('/cursos/')) {
        apiPath = '/skillboots/modules/progress/mark_content_viewed.php';
    } else if (currentPath.includes('/modules/')) {
        apiPath = '/skillboots/modules/progress/mark_content_viewed.php';
    } else {
        apiPath = '/skillboots/modules/progress/mark_content_viewed.php';
    }

    // Realizar petición AJAX
    return fetch(apiPath, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error(`Archivo no encontrado: ${apiPath}. Verifica que el archivo existe en la ruta correcta.`);
            }
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        
        // Verificar que la respuesta sea JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Respuesta no es JSON:', text.substring(0, 500));
                throw new Error('Respuesta del servidor no es JSON válido');
            });
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Contenido marcado como visto:', contentId);
            
            // Actualizar UI si es necesario
            updateProgressUI(data.data);
            
            // Mostrar notificación si el curso se completó
            if (data.data && data.data.course_completed) {
                showCourseCompletedNotification();
            }
            
            return data;
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error marcando contenido como visto:', error.message);
        
        // Mostrar notificación de error al usuario
        showErrorNotification('No se pudo marcar el contenido como visto: ' + error.message);
        
        throw error;
    });
}

// Función auxiliar para actualizar la UI de progreso
function updateProgressUI(data) {
    try {
        if (!data) return;
        
        // Actualizar barra de progreso si existe
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar && data.progress !== undefined) {
            progressBar.style.width = data.progress + '%';
            progressBar.setAttribute('aria-valuenow', data.progress);
            
            // Actualizar texto del porcentaje
            const progressText = progressBar.querySelector('.progress-text');
            if (progressText) {
                progressText.textContent = data.progress + '%';
            }
        }

        // Actualizar texto de progreso
        const progressText = document.querySelector('.progress-text, .lessons-progress');
        if (progressText && data.completed_lessons !== undefined && data.total_lessons !== undefined) {
            progressText.textContent = `${data.completed_lessons}/${data.total_lessons} lecciones completadas (${data.progress}%)`;
        }

        // Marcar contenido como completado en la lista
        if (data.content_id) {
            const contentElement = document.querySelector(`[data-content-id="${data.content_id}"]`);
            if (contentElement) {
                contentElement.classList.add('completed');
                const icon = contentElement.querySelector('.completion-icon');
                if (icon) {
                    icon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                }
            }
        }

        // Actualizar contador de progreso en el sidebar si existe
        const sidebarProgress = document.querySelector('.sidebar-progress');
        if (sidebarProgress && data.progress !== undefined) {
            sidebarProgress.innerHTML = `
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: ${data.progress}%" 
                         aria-valuenow="${data.progress}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        ${data.progress}%
                    </div>
                </div>
                <small class="text-muted">${data.completed_lessons}/${data.total_lessons} completadas</small>
            `;
        }
        
    } catch (error) {
        console.error('Error actualizando UI de progreso:', error);
    }
}

// Función para mostrar notificación de curso completado
function showCourseCompletedNotification() {
    // Usar SweetAlert si está disponible
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Felicidades!',
            text: '¡Has completado este curso exitosamente!',
            icon: 'success',
            confirmButtonText: 'Continuar'
        });
    } else {
        // Fallback a alert nativo
        alert('¡Felicidades! ¡Has completado este curso exitosamente!');
    }
}

// Función para mostrar notificaciones de error
function showErrorNotification(message) {
    // Usar SweetAlert si está disponible
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    } else {
        // Fallback a console.error
        console.error('Error:', message);
    }
}
// Función auxiliar para actualizar la UI de progreso
function updateProgressUI(data) {
    try {
        // Actualizar barra de progreso si existe
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar && data.progress !== undefined) {
            progressBar.style.width = data.progress + '%';
            progressBar.setAttribute('aria-valuenow', data.progress);
        }

        // Actualizar texto de progreso
        const progressText = document.querySelector('.progress-text');
        if (progressText && data.completed_lessons !== undefined && data.total_lessons !== undefined) {
            progressText.textContent = `${data.completed_lessons}/${data.total_lessons} lecciones completadas`;
        }

        // Marcar contenido como completado en la lista
        if (data.content_id) {
            const contentElement = document.querySelector(`[data-content-id="${data.content_id}"]`);
            if (contentElement) {
                contentElement.classList.add('completed');
                const icon = contentElement.querySelector('.completion-icon');
                if (icon) {
                    icon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                }
            }
        }
    } catch (error) {
        console.error('Error actualizando UI de progreso:', error);
    }
}

function renderCalendar(month, year) {
    console.log(`renderCalendar: Renderizando calendario para ${month + 1}/${year}`);
    const calendarContainer = document.getElementById('calendar-container');
    const today = new Date();
    
    // Nombres de los meses en español
    const monthNames = ["enero", "febrero", "marzo", "abril", "mayo", "junio",
                       "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    
    // Nombres cortos de los días de la semana para pantallas pequeñas
    const dayNamesShort = ["D", "L", "M", "X", "J", "V", "S"];
    // Nombres normales de los días de la semana
    const dayNames = ["Do", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
    
    // Determinar qué nombres de días usar según el ancho de la pantalla
    const useShortNames = window.innerWidth < 576;
    const displayDayNames = useShortNames ? dayNamesShort : dayNames;
    
    // Crear estructura del calendario
    let calendarHTML = `
        <div class="calendar-header">
            <span class="calendar-nav" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></span>
            <h6 class="calendar-month">${monthNames[month]} ${year}</h6>
            <span class="calendar-nav" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></span>
        </div>
        <div class="table-responsive">
            <table class="calendar-table">
                <thead>
                    <tr>
    `;
    
    // Agregar encabezados de días
    for(let i = 0; i < displayDayNames.length; i++) {
        calendarHTML += `<th>${displayDayNames[i]}</th>`;
    }
    
    calendarHTML += `</tr></thead><tbody>`;
    
    // Obtener el primer día del mes
    const firstDay = new Date(year, month, 1);
    const startingDay = firstDay.getDay(); // 0 = domingo, 1 = lunes, etc.
    
    // Obtener el número de días en el mes
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Variables para el calendario
    let day = 1;
    let dateString = '';
    
    // Crear filas del calendario
    for (let i = 0; i < 6; i++) {
        // Si ya pasamos todos los días del mes, salimos del bucle
        if (day > daysInMonth) break;
        
        calendarHTML += '<tr>';
        
        // Crear celdas para cada día de la semana
        for (let j = 0; j < 7; j++) {
            // Agregar celdas vacías para los días anteriores al inicio del mes
            if (i === 0 && j < startingDay) {
                calendarHTML += '<td class="empty-day"></td>';
            } 
            // Agregar celdas para los días del mes
            else if (day <= daysInMonth) {
                dateString = `${year}-${String(month+1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                
                // Verificar si es hoy
                const isToday = day === today.getDate() && 
                               month === today.getMonth() && 
                               year === today.getFullYear();
                
                calendarHTML += `<td data-date="${dateString}" class="${isToday ? 'today' : ''}">${day}</td>`;
                day++;
            } 
            // Agregar celdas vacías para los días después del fin del mes
            else {
                calendarHTML += '<td class="empty-day"></td>';
            }
        }
        
        calendarHTML += '</tr>';
    }
    
    calendarHTML += `
            </tbody>
        </table>
        </div>
    `;
    
    calendarContainer.innerHTML = calendarHTML;
    
    // Guardar mes y año actuales como atributos de datos
    calendarContainer.dataset.currentMonth = month;
    calendarContainer.dataset.currentYear = year;
}

// Función con retry para cargar eventos del calendario
function loadCalendarEventsWithRetry(month, year, attempt = 1, maxAttempts = 3) {
    console.log(`loadCalendarEventsWithRetry: Intento ${attempt} para ${month + 1}/${year}`);
    
    loadCalendarEvents(month, year)
        .catch(error => {
            console.error(`Error en intento ${attempt}:`, error);
            if (attempt < maxAttempts) {
                console.log(`Reintentando en 2 segundos... (${attempt + 1}/${maxAttempts})`);
                setTimeout(() => {
                    loadCalendarEventsWithRetry(month, year, attempt + 1, maxAttempts);
                }, 2000);
            } else {
                console.error('Máximo número de intentos alcanzado para cargar eventos del calendario');
                // Mostrar calendario sin eventos
                markCalendarEvents([]);
            }
        });
}

// Función mejorada para cargar eventos del calendario
function loadCalendarEvents(month, year) {
    console.log(`loadCalendarEvents: Cargando eventos para ${month + 1}/${year}`);
    
    return new Promise((resolve, reject) => {
        // Obtener múltiples opciones de token CSRF
        const csrfData = getCSRFTokenAndMethod();
        if (!csrfData.token) {
            console.error('loadCalendarEvents: No se pudo obtener el token CSRF');
            reject(new Error('No CSRF token available'));
            return;
        }
        
        // Construir headers dinámicamente
        const headers = {
            'Content-Type': 'application/json',
        };
        
        // Agregar token CSRF según el método disponible
        if (csrfData.method === 'header') {
            headers['X-CSRF-Token'] = csrfData.token;
        }
        
        // Construir URL con parámetros
        let url = `/skillboots/modules/calendar/calendar_events.php?month=${month+1}&year=${year}`;
        
        // Si el token va por URL, agregarlo
        if (csrfData.method === 'param') {
            url += `&csrf_token=${encodeURIComponent(csrfData.token)}`;
        }
        
        console.log('loadCalendarEvents: URL:', url);
        console.log('loadCalendarEvents: Headers:', headers);
        
        // Petición AJAX
        fetch(url, {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin' // Importante para las cookies de sesión
        })
        .then(response => {
            console.log('loadCalendarEvents: Respuesta recibida', response.status);
            
            // Verificar el content-type de la respuesta
            const contentType = response.headers.get('content-type');
            console.log('loadCalendarEvents: Content-Type:', contentType);
            
            if (!response.ok) {
                // Intentar leer el cuerpo de la respuesta para más información
                return response.text().then(text => {
                    console.error('loadCalendarEvents: Error response body:', text);
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 200)}`);
                });
            }
            
            // Verificar si la respuesta es JSON
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Si no es JSON, leer como texto para debugging
                return response.text().then(text => {
                    console.error('loadCalendarEvents: Respuesta no es JSON:', text.substring(0, 200));
                    throw new Error('Response is not JSON: ' + text.substring(0, 100));
                });
            }
        })
        .then(data => {
            console.log('loadCalendarEvents: Datos recibidos:', data);
            
            if (data.success && data.events) {
                markCalendarEvents(data.events);
                resolve(data);
            } else if (data.error) {
                console.error('loadCalendarEvents: Error del servidor:', data.error, data.debug);
                reject(new Error(`Server error: ${data.error}`));
            } else {
                console.error('loadCalendarEvents: Formato de datos inesperado:', data);
                markCalendarEvents([]);
                resolve(data);
            }
        })
        .catch(error => {
            console.error('Error cargando eventos del calendario:', error);
            reject(error);
        });
    });
}

function markCalendarEvents(events) {
    console.log(`markCalendarEvents: Marcando ${events ? events.length : 0} eventos`);
    
    // Verificar que events existe y es un array
    if (!events || !Array.isArray(events)) {
        console.warn('markCalendarEvents: No hay eventos válidos para marcar');
        return;
    }
    
    // Marcar días que tienen eventos
    events.forEach(event => {
        if (!event.fecha) {
            console.warn('markCalendarEvents: Evento sin fecha', event);
            return;
        }
        
        const eventDate = new Date(event.fecha);
        const day = eventDate.getDate();
        const month = eventDate.getMonth();
        const year = eventDate.getFullYear();
        
        // Formato de fecha consistente con el formato usado en renderCalendar
        const formattedDate = `${year}-${String(month+1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        const cell = document.querySelector(`td[data-date="${formattedDate}"]`);
        if (cell) {
            cell.classList.add('calendar-event');
            // Agregar tooltip con información del evento
            cell.title = event.titulo;
            // Agregar evento clic
            cell.onclick = function() {
                showEventDetails(event);
            };
        } else {
            console.warn(`markCalendarEvents: No se encontró celda para la fecha ${formattedDate}`);
        }
    });
}

function showEventDetails(event) {
    console.log('showEventDetails: Mostrando detalles del evento', event);
    // Mostrar detalles del evento en un modal o tooltip
    let eventDetails = `Evento: ${event.titulo}\nFecha: ${new Date(event.fecha).toLocaleDateString()}`;
    
    if (event.hora_inicio) {
        eventDetails += `\nHora: ${event.hora_inicio}`;
        if (event.hora_fin) {
            eventDetails += ` - ${event.hora_fin}`;
        }
    }
    
    if (event.descripcion) {
        eventDetails += `\nDetalles: ${event.descripcion}`;
    }
    
    alert(eventDetails);
}

// Función mejorada para cargar usuarios en línea
function loadOnlineUsers() {
    console.log('loadOnlineUsers: Cargando usuarios en línea');
    
    // Obtener token CSRF con múltiples métodos
    const csrfData = getCSRFTokenAndMethod();
    if (!csrfData.token) {
        console.error('loadOnlineUsers: No se pudo obtener el token CSRF');
        return;
    }
    
    // Construir headers
    const headers = {
        'Content-Type': 'application/json',
    };
    
    if (csrfData.method === 'header') {
        headers['X-CSRF-Token'] = csrfData.token;
    }
    
    // Construir URL
    let url = '/skillboots/modules/users/online_users.php';
    if (csrfData.method === 'param') {
        url += `?csrf_token=${encodeURIComponent(csrfData.token)}`;
    }
    
    // Petición AJAX
    fetch(url, {
        method: 'GET',
        headers: headers,
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('loadOnlineUsers: Respuesta recibida', response.status);
        
        // Verificar content-type
        const contentType = response.headers.get('content-type');
        console.log('loadOnlineUsers: Content-Type:', contentType);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('loadOnlineUsers: Error response:', text.substring(0, 200));
                throw new Error(`HTTP error! status: ${response.status}`);
            });
        }
        
        // Verificar if es JSON
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Si no es JSON, probablemente hay errores PHP
            return response.text().then(text => {
                console.error('loadOnlineUsers: Respuesta no es JSON (probablemente errores PHP):', text.substring(0, 200));
                // Intentar extraer JSON del texto si está mezclado con HTML
                const jsonMatch = text.match(/\{.*\}/);
                if (jsonMatch) {
                    try {
                        return JSON.parse(jsonMatch[0]);
                    } catch (e) {
                        throw new Error('Mixed HTML/JSON response, could not parse JSON part');
                    }
                } else {
                    throw new Error('Response is not JSON: ' + text.substring(0, 100));
                }
            });
        }
    })
    .then(data => {
        console.log('loadOnlineUsers: Usuarios recibidos', data);
        if (data.users) {
            updateOnlineUsersList(data.users, data.total || data.users.length);
        } else if (data.error) {
            console.error('loadOnlineUsers: Error del servidor:', data.error);
        } else {
            console.error('loadOnlineUsers: Formato de datos inesperado:', data);
        }
    })
    .catch(error => {
        console.error('Error cargando usuarios en línea:', error);
        // Mostrar lista vacía en caso de error
        updateOnlineUsersList([], 0);
    });
}

function updateOnlineUsersList(users, total) {
    console.log(`updateOnlineUsersList: Actualizando lista con ${users ? users.length : 0} usuarios`);
    const usersList = document.getElementById('lista-usuarios-online');
    const totalCount = document.getElementById('total-usuarios');
    
    if (!usersList || !totalCount) {
        console.warn('updateOnlineUsersList: Elementos DOM no encontrados');
        return;
    }    
    
    // Actualizar contador
    totalCount.textContent = total || 0;
    
    // Limpiar lista actual
    usersList.innerHTML = '';
    
    // Verificar que users existe y es un array
    if (!users || !Array.isArray(users)) {
        console.warn('updateOnlineUsersList: No hay usuarios válidos');
        return;
    }
    
    // Agregar usuarios a la lista
    users.forEach(user => {
        const userItem = document.createElement('li');
        userItem.className = 'list-group-item user-online-item';
        
        // Obtener iniciales para el avatar
        const name = user.name || user.nombre || '';
        const lastname = user.lastname || user.apellido || '';
        const initials = name.charAt(0).toUpperCase() + (lastname ? lastname.charAt(0).toUpperCase() : '');
        
        userItem.innerHTML = `
            <div class="user-status-indicator"></div>
            <div class="user-online-avatar">${initials}</div>
            <div class="user-online-name">${name} ${lastname}</div>
        `;
        
        usersList.appendChild(userItem);
    });
}

// Función mejorada para obtener token CSRF con múltiples métodos
function getCSRFTokenAndMethod() {
    let token = '';
    let method = '';
    
    // 1. Desde meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        token = metaToken.getAttribute('content');
        method = 'header';
    }
    
    // 2. Desde input hidden (si existe)
    if (!token) {
        const inputToken = document.querySelector('input[name="csrf_token"]');
        if (inputToken) {
            token = inputToken.value;
            method = 'param';
        }
    }
    
    // 3. Desde variable global (si existe)
    if (!token && typeof window.csrfToken !== 'undefined') {
        token = window.csrfToken;
        method = 'header';
    }
    
    // 4. Intentar obtener desde cookie
    if (!token) {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'csrf_token' || name === 'XSRF-TOKEN') {
                token = decodeURIComponent(value);
                method = 'header';
                break;
            }
        }
    }
    
    console.log('getCSRFTokenAndMethod: Token CSRF obtenido', token ? `OK (${method})` : 'NO ENCONTRADO');
    console.log('getCSRFTokenAndMethod: Token preview:', token ? token.substring(0, 10) + '...' : 'none');
    
    return { token, method };
}

// Función para obtener token CSRF (mantenida para compatibilidad)
function getCSRFToken() {
    return getCSRFTokenAndMethod().token;
}

// Función para controlar la visibilidad de la sidebar
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded: Página cargada, inicializando funciones');

    // Inicializar calendario
    initCalendar();
    
    // Inicializar lista de usuarios en línea
    loadOnlineUsers();
    
    // Refrescar usuarios en línea cada 40 segundos
    setInterval(loadOnlineUsers, 40000);
    
    // Configurar el toggle de la sidebar
    setupSidebarToggle();
});

function setupSidebarToggle() {
    console.log('setupSidebarToggle: Configurando sidebar');
    const toggleButton = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.right-sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Verificar si existe botón y sidebar
    if (!toggleButton || !sidebar) {
        console.warn('setupSidebarToggle: Botón o sidebar no encontrados');
        return;
    }
    
    // Restaurar estado guardado de la sidebar
    const sidebarVisible = localStorage.getItem('sidebarVisible') !== 'false';
    if (!sidebarVisible) {
        sidebar.classList.add('sidebar-hidden');
        if (mainContent) mainContent.classList.add('main-content-expanded');
    }
    
    toggleButton.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-hidden');
        if (mainContent) mainContent.classList.toggle('main-content-expanded');
        
        // Guardar preferencia del usuario
        localStorage.setItem('sidebarVisible', !sidebar.classList.contains('sidebar-hidden'));
        console.log('setupSidebarToggle: Sidebar toggled, guardado en localStorage');
    });
}

// Función para cambiar mes
function changeMonth(direction) {
    console.log(`changeMonth: Cambiando mes en dirección ${direction}`);
    const calendarContainer = document.getElementById('calendar-container');
    if (!calendarContainer) return;
    
    let month = parseInt(calendarContainer.dataset.currentMonth);
    let year = parseInt(calendarContainer.dataset.currentYear);
    
    month += direction;
    
    if (month > 11) {
        month = 0;
        year++;
    } else if (month < 0) {
        month = 11;
        year--;
    }
    
    console.log(`changeMonth: Nuevo mes ${month + 1}/${year}`);
    renderCalendar(month, year);
    loadCalendarEventsWithRetry(month, year);
}

// Agregar detector de cambio de tamaño de ventana
window.addEventListener('resize', debounce(function() {
    console.log('resize: Detectado cambio de tamaño de ventana');
    const calendarContainer = document.getElementById('calendar-container');
    if (!calendarContainer) return;
    
    const month = parseInt(calendarContainer.dataset.currentMonth);
    const year = parseInt(calendarContainer.dataset.currentYear);
    
    if (isNaN(month) || isNaN(year)) {
        console.warn('resize: Mes o año no válidos');
        return;
    }
    
    console.log(`resize: Re-renderizando calendario para ${month + 1}/${year}`);
    
    // Volver a renderizar el calendario
    renderCalendar(month, year);
    // Volver a cargar los eventos con retry
    loadCalendarEventsWithRetry(month, year);
}, 250));

// Función para limitar la frecuencia de ejecución (debounce)
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}