let logoutTiempo = 60 * 60 * 1000; // 1 hora
let inactivoTiempo = 5 * 60 * 1000; // 5 minutos

let logoutTimer;
let inactivoTimer;

// Llama al backend para eliminar usuario de `usuarios_online`
function marcarUsuarioInactivo() {
    fetch('/skillboots/api/init/marcar_inactivo.php', { method: 'POST' });
}

// Llama al backend para reactivar usuario en `usuarios_online`
function marcarUsuarioActivo() {
    fetch('/skillboots/api/init/marcar_activo.php', { method: 'POST' });
}

// Cierra sesión automáticamente
function cerrarSesion() {
    fetch('/skillboots/auth/logout.php').then(() => {
        window.location.href = '/index.php';
    });
}

// Reinicia ambos temporizadores
function reiniciarTemporizadores() {
    clearTimeout(logoutTimer);
    clearTimeout(inactivoTimer);

    logoutTimer = setTimeout(cerrarSesion, logoutTiempo);
    inactivoTimer = setTimeout(marcarUsuarioInactivo, inactivoTiempo);

    // Reactivar usuario si estaba marcado como inactivo
    marcarUsuarioActivo();
}

// Detectar cualquier actividad del usuario
['mousemove', 'keydown', 'scroll', 'click'].forEach(evento => {
    window.addEventListener(evento, reiniciarTemporizadores);
});

// Iniciar temporizadores al cargar la página
reiniciarTemporizadores();
