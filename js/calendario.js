// Código para el calendario y usuarios en línea


function initCalendar() {
    const calendarContainer = document.getElementById('calendar-container');
    if (!calendarContainer) return;
    
    // Obtener fecha actual
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();
    
    // Renderizar calendario inicial
    renderCalendar(currentMonth, currentYear);
    
    // Cargar eventos del calendario
    loadCalendarEvents(currentMonth, currentYear);
}

function renderCalendar(month, year) {
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
                dateString = `${year}-${month+1}-${day}`;
                
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

function loadCalendarEvents(month, year) {
    // Petición AJAX para cargar eventos del calendario
    fetch(`api/calendar_events.php?month=${month+1}&year=${year}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
        },
    })
    .then(response => response.json())
    .then(data => {
        markCalendarEvents(data.events);
    })
    .catch(error => {
        console.error('Error cargando eventos del calendario:', error);
    });
}

function markCalendarEvents(events) {
    // Marcar días que tienen eventos
    events.forEach(event => {
        const eventDate = new Date(event.fecha);
        const day = eventDate.getDate();
        const month = eventDate.getMonth();
        const year = eventDate.getFullYear();
        
        const cell = document.querySelector(`td[data-date="${year}-${month+1}-${day}"]`);
        if (cell) {
            cell.classList.add('calendar-event');
            // Agregar tooltip con información del evento
            cell.title = event.titulo;
            // Agregar evento clic
            cell.onclick = function() {
                showEventDetails(event);
            };
        }
    });
}

function showEventDetails(event) {
    // Mostrar detalles del evento en un modal o tooltip
    // Implementación personalizada según necesidades
    alert(`Evento: ${event.titulo}\nFecha: ${new Date(event.fecha).toLocaleDateString()}\nDetalles: ${event.descripcion || 'No hay detalles disponibles'}`);
}

function loadOnlineUsers() {
    // Petición AJAX para cargar usuarios en línea
    fetch('api/online_users.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
        },
    })
    .then(response => response.json())
    .then(data => {
        updateOnlineUsersList(data.users, data.total);
    })
    .catch(error => {
        console.error('Error cargando usuarios en línea:', error);
    });
}

function updateOnlineUsersList(users, total) {
    const usersList = document.getElementById('lista-usuarios-online');
    const totalCount = document.getElementById('total-usuarios');
    
    if (!usersList || !totalCount) return;
    
    // Actualizar contador
    totalCount.textContent = total;
    
    // Limpiar lista actual
    usersList.innerHTML = '';
    
    // Agregar usuarios a la lista
    users.forEach(user => {
        const userItem = document.createElement('li');
        userItem.className = 'list-group-item user-online-item';
        
        // Obtener iniciales para el avatar
        const initials = user.name.charAt(0).toUpperCase() + (user.lastname ? user.lastname.charAt(0).toUpperCase() : '');
        
        userItem.innerHTML = `
            <div class="user-status-indicator"></div>
            <div class="user-online-avatar">${initials}</div>
            <div class="user-online-name">${user.name} ${user.lastname}</div>
        `;
        
        usersList.appendChild(userItem);
    });
}

function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}
// Función para controlar la visibilidad de la sidebar
document.addEventListener('DOMContentLoaded', function() {
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
    const toggleButton = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.right-sidebar'); // Ajusta este selector según tu estructura
    const mainContent = document.querySelector('.main-content'); // Ajusta este selector según tu estructura
    
    // Verificar si existe botón y sidebar
    if (!toggleButton || !sidebar) return;
    
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
    });
}
// Agregar detector de cambio de tamaño de ventana
window.addEventListener('resize', debounce(function() {
    // Obtener mes y año actuales
    const calendarContainer = document.getElementById('calendar-container');
    const month = parseInt(calendarContainer.dataset.currentMonth);
    const year = parseInt(calendarContainer.dataset.currentYear);
    
    // Volver a renderizar el calendario
    renderCalendar(month, year);
    // Volver a cargar los eventos
    loadCalendarEvents(month, year);
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