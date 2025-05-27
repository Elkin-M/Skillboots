function loadNotifications() {
    console.log("📥 Iniciando carga de notificaciones...");

    fetch('/skillboots/notificaciones/get_notifications.php')
        .then(response => {
            console.log("✅ Respuesta recibida del servidor:", response);
            return response.json();
        })
        .then(data => {
            console.log("📦 Datos de notificaciones recibidos:", data);

            const notificationContainer = document.getElementById('notificationDropdown');
            const notificationBadge = document.getElementById('notificationBadge');

            if (!notificationContainer || !notificationBadge) {
                console.error("❌ Elementos #notificationDropdown o #notificationBadge no encontrados en el DOM.");
                return;
            }

            // Actualizar contador de notificaciones no leídas
            if (data.unread > 0) {
                notificationBadge.textContent = data.unread;
                notificationBadge.style.display = 'inline-block';
                console.log(`🔔 ${data.unread} notificaciones no leídas.`);
            } else {
                notificationBadge.style.display = 'none';
                console.log("ℹ️ No hay notificaciones no leídas.");
            }

            // Limpiar contenedor
            notificationContainer.innerHTML = '';
            console.log("🧹 Contenedor de notificaciones limpiado.");

            // Añadir notificaciones
            if (data.notifications.length > 0) {
                console.log(`📨 Se encontraron ${data.notifications.length} notificaciones.`);

                data.notifications.forEach(notification => {
                    console.log("➡️ Procesando notificación:", notification);

                    const notificationItem = document.createElement('a');
                    notificationItem.href = notification.link || '#';
                    notificationItem.className = 'dropdown-item';
                    if (!notification.read) {
                        notificationItem.classList.add('unread');
                    }

                    notificationItem.innerHTML = `
                        <div class="notification-icon">
                            <i class="fas ${notification.icon}"></i>
                        </div>
                        <div class="notification-content">
                            <p>${notification.message}</p>
                            <small>${notification.time_ago}</small>
                        </div>
                    `;

                    notificationContainer.appendChild(notificationItem);

                    // Marcar como leída al hacer clic
                    notificationItem.addEventListener('click', () => {
                        console.log(`🖱️ Clic en notificación ID: ${notification.id}`);
                        markAsRead(notification.id);
                    });
                });

                // Añadir enlace "Ver todas"
                const viewAllLink = document.createElement('div');
                viewAllLink.className = 'dropdown-item text-center';
                viewAllLink.innerHTML = '<a href="notifications.php" class="text-primary">Ver todas las notificaciones</a>';
                notificationContainer.appendChild(viewAllLink);
                console.log("🔗 Enlace 'Ver todas' agregado.");
            } else {
                console.log("🛑 No hay notificaciones para mostrar.");
                const emptyItem = document.createElement('div');
                emptyItem.className = 'dropdown-item text-center';
                emptyItem.textContent = 'No tienes notificaciones';
                notificationContainer.appendChild(emptyItem);
            }
        })
        .catch(error => {
            console.error('🚨 Error al cargar notificaciones:', error);
        });
}


// Función para marcar notificación como leída
function markAsRead(notificationId) {
    if (!notificationId) {
        console.error("Error: notificationId no está definido");
        return;
    }

    fetch('/skillboots/notificaciones/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: notificationId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            } else {
                console.error("Error al marcar como leída:", data.error);
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
        });
}

// Cargar notificaciones al iniciar y cada 60 segundos
document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
    setInterval(loadNotifications, 60000);
});
