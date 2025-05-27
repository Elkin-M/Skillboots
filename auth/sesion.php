<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/favicon/favicon.ico" type="image/x-icon">
    <title>SkillBoots - Inicio de Sesión</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: white;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
            border: 1px solid #e0e0e0;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            top: 40px !important;
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.active .sign-in {
            transform: translateX(100%);
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .form-container form {
            background: white;
            /* display: flex; */
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
            text-align: center;
        }

        .form-container h1 {
            font-size: 1.8rem;
            margin-top: 30px;
            color: #333;
            font-weight: 600;
        }

        .form-container input,
        .form-container select {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-container input:focus,
        .form-container select:focus {
            background: white;
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .form-container button {
            background: #007bff;
            color: white;
            font-size: 12px;
            padding: 12px 45px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .form-container button:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .form-container button:active {
            transform: translateY(0);
        }

        .form-container a {
            color: #666;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
            transition: color 0.3s ease;
        }

        .form-container a:hover {
            color: #007bff;
            text-decoration: underline;
        }

        .espacio {
            font-size: 12px;
            color: #666;
            margin: 15px 0;
        }

        .social-icons {
            margin: 15px 0;
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 0 30px 30px 0;
            z-index: 1000;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 30px 0 0 30px;
        }

        .toggle {
            background: #ff7d26;
            height: 100%;
            color: white;
            position: relative;
            left: -100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }

        .toggle-panel h1 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .toggle-panel p {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .toggle-panel button {
            background: transparent;
            border: 2px solid white;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 12px 35px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-panel button:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .brand-text {
            color: #44425A !important;
            font-weight: 700;
            font-size: 1.8rem;
        }

        .brand-text span {
            color: #ffff;
        }

        /* Responsive Design */
        .mobile-nav {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                min-height: 600px;
                border-radius: 20px;
                margin: 10px;
            }

            .form-container {
                width: 100% !important;
                position: relative;
            }

            .sign-in,
            .sign-up {
                position: relative;
                left: 0 !important;
                width: 100% !important;
                transform: none !important;
                opacity: 1 !important;
                z-index: 1;
            }

            .sign-up {
                display: none;
            }

            .container.active .sign-in {
                display: none;
            }

            .container.active .sign-up {
                display: flex;
            }

            .toggle-container {
                display: none;
            }

            .mobile-nav {
                display: block;
                background: #f8f9fa;
                border-bottom: 1px solid #e0e0e0;
            }

            .mobile-nav-tabs {
                display: flex;
                width: 100%;
            }

            .nav-tab {
                flex: 1;
                padding: 15px;
                text-align: center;
                background: #f8f9fa;
                color: #666;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.3s ease;
                border-bottom: 3px solid transparent;
            }

            .nav-tab.active {
                background: white;
                color: #007bff;
                border-bottom-color: #007bff;
            }

            
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            
            .form-container h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container" id="container">
        <!-- Navegación móvil -->
        <div class="mobile-nav">
            <div class="mobile-nav-tabs">
                <div class="nav-tab login-tab active" id="login-tab">Iniciar Sesión</div>
                <div class="nav-tab register-tab" id="register-tab">Registrarse</div>
            </div>
        </div>

        <!-- Formulario de registro -->
        <div class="form-container sign-up">
            <form action="./prueba.php" method="POST" id="register-form">
                <h1>Crear Cuenta</h1>
                <input type="text" placeholder="Nombre" name="nombre" required>
                <input type="text" placeholder="Apellido" name="apellido" required>
                <input type="email" placeholder="Correo Electrónico" name="email" required>
                <input type="password" placeholder="Contraseña" name="contraseña" required>
                <select name="rol" required>
                    <option value="">Seleccione un rol</option>
                    <option value="admin">Administrador</option>
                    <option value="profesor">Profesor</option>
                    <option value="estudiante">Estudiante</option>
                </select>
            <div style="display:inline-grid !important;">
                <button type="submit" style="background-color:#ff7d26;">Registrarse</button>
                <div class="social-icons">
                    <!-- Íconos sociales aquí -->
                </div>
            </div>
            </form>
        </div>

        <!-- Formulario de inicio de sesión -->
        <div class="form-container sign-in">
            <form action="./login.php" method="POST" id="login-form">
                <h1>Iniciar Sesión</h1>
                <input type="email" placeholder="Correo Electrónico" name="email" required>
                <input type="password" placeholder="Contraseña" name="contraseña" required>
            <div style="display: inline-grid !important;">
                <a href="#">¿Olvidaste tu contraseña?</a>
                <button type="submit" style="background-color:#ff7d26;">Iniciar Sesión</button>
                <div class="social-icons">
                    <!-- Íconos sociales aquí -->
                </div>
            </div>
            </form>
        </div>

        <!-- Panel de alternancia -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>¡Bienvenido de Vuelta!</h1>
                    <p>Ingresa tus datos para utilizar todas nuestras funciones</p>
                    <button class="hidden" id="login">Iniciar Sesión</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <div class="brand-text">
                        <span>SKILL</span>BOOTS
                    </div>
                    <p>Tu Portafolio de Cursos Online.<br>
                    ¡Avanzamos constantemente para ofrecerte más servicios!</p>
                    <button class="hidden" id="register" >Registrarse</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Obtener elementos del DOM
            const container = document.getElementById('container');
            const registerBtn = document.getElementById('register');
            const loginBtn = document.getElementById('login');
            const registerForm = document.getElementById('register-form');
            const loginForm = document.getElementById('login-form');
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');

            // Verificar que todos los elementos existen
            if (!container) {
                console.error('Elemento container no encontrado');
                return;
            }

            // Funciones para alternar entre formularios
            function showLoginForm() {
                container.classList.remove('active');
                if (loginTab && registerTab) {
                    loginTab.classList.add('active');
                    registerTab.classList.remove('active');
                }
            }

            function showRegisterForm() {
                container.classList.add('active');
                if (loginTab && registerTab) {
                    loginTab.classList.remove('active');
                    registerTab.classList.add('active');
                }
            }

            // Event listeners para botones de escritorio
            if (registerBtn) {
                registerBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showRegisterForm();
                });
            }

            if (loginBtn) {
                loginBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showLoginForm();
                });
            }

            // Event listeners para pestañas móviles
            if (loginTab) {
                loginTab.addEventListener('click', function(e) {
                    e.preventDefault();
                    showLoginForm();
                });
            }

            if (registerTab) {
                registerTab.addEventListener('click', function(e) {
                    e.preventDefault();
                    showRegisterForm();
                });
            }

            // Validación de formularios
            function validateForm(form) {
                const inputs = form.querySelectorAll('input[required], select[required]');
                let isValid = true;
                let firstInvalidField = null;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = '#dc3545';
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    } else {
                        input.style.borderColor = '#ddd';
                    }
                });

                if (!isValid && firstInvalidField) {
                    firstInvalidField.focus();
                    alert('Por favor, completa todos los campos requeridos.');
                }

                return isValid;
            }

            // Validación del formulario de registro
            if (registerForm) {
                registerForm.addEventListener('submit', function (event) {
                    if (!validateForm(this)) {
                        event.preventDefault();
                    }
                });
            }

            // Validación del formulario de login
            if (loginForm) {
                loginForm.addEventListener('submit', function (event) {
                    if (!validateForm(this)) {
                        event.preventDefault();
                    }
                });
            }

            // Limpiar estilos de error al escribir
            document.querySelectorAll('input, select').forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.style.borderColor = '#ddd';
                    }
                });
            });

            console.log('Script inicializado correctamente');
        });

        
    </script>
     <?php
    
    
    // Capturar mensajes de error/éxito
    $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
    $error_type = isset($_SESSION['error_type']) ? $_SESSION['error_type'] : '';
    $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
    $show_login = isset($_SESSION['show_login']) ? $_SESSION['show_login'] : false;
    
    // Limpiar mensajes después de capturarlos para evitar que se muestren de nuevo
    if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
    if (isset($_SESSION['error_type'])) unset($_SESSION['error_type']);
    if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
    if (isset($_SESSION['show_login'])) unset($_SESSION['show_login']);
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Función para mostrar alertas (debe definirse primero)
            function showAlert(message, type) {
                // Crear el elemento de alerta
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 400px;
                    min-width: 300px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    border-radius: 8px;
                    animation: slideInRight 0.5s ease-out;
                    padding: 15px;
                    font-size: 14px;
                `;
                
                // Definir colores según el tipo
                let bgColor, borderColor, textColor, iconClass;
                switch(type) {
                    case 'error':
                        bgColor = '#f8d7da';
                        borderColor = '#f5c6cb';
                        textColor = '#721c24';
                        iconClass = 'bi-exclamation-triangle-fill';
                        break;
                    case 'warning':
                        bgColor = '#fff3cd';
                        borderColor = '#ffecb5';
                        textColor = '#856404';
                        iconClass = 'bi-exclamation-triangle';
                        break;
                    case 'success':
                        bgColor = '#d1e7dd';
                        borderColor = '#badbcc';
                        textColor = '#0f5132';
                        iconClass = 'bi-check-circle-fill';
                        break;
                    default:
                        bgColor = '#d1ecf1';
                        borderColor = '#bee5eb';
                        textColor = '#0c5460';
                        iconClass = 'bi-info-circle';
                }
                
                alertDiv.style.backgroundColor = bgColor;
                alertDiv.style.borderColor = borderColor;
                alertDiv.style.color = textColor;
                alertDiv.style.border = `1px solid ${borderColor}`;
                
                alertDiv.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi ${iconClass}" style="font-size: 1.2em;"></i>
                        <span style="flex: 1;">${message}</span>
                        <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()" 
                                style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: ${textColor}; opacity: 0.7;">&times;</button>
                    </div>
                `;
                
                // Agregar estilos de animación si no existen
                if (!document.querySelector('#alert-styles')) {
                    const style = document.createElement('style');
                    style.id = 'alert-styles';
                    style.textContent = `
                        @keyframes slideInRight {
                            from {
                                transform: translateX(100%);
                                opacity: 0;
                            }
                            to {
                                transform: translateX(0);
                                opacity: 1;
                            }
                        }
                        @keyframes slideOutRight {
                            from {
                                transform: translateX(0);
                                opacity: 1;
                            }
                            to {
                                transform: translateX(100%);
                                opacity: 0;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                // Agregar al DOM
                document.body.appendChild(alertDiv);
                
                // Auto-remover después de 5 segundos
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.style.animation = 'slideOutRight 0.5s ease-in';
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.remove();
                            }
                        }, 500);
                    }
                }, 5000);
            }

            // Mostrar alertas si existen
            <?php if (!empty($error_message)): ?>
                console.log('Mostrando error: <?php echo addslashes($error_message); ?>');
                showAlert('<?php echo addslashes($error_message); ?>', '<?php echo $error_type; ?>');
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                console.log('Mostrando éxito: <?php echo addslashes($success_message); ?>');
                showAlert('<?php echo addslashes($success_message); ?>', 'success');
                <?php if ($show_login): ?>
                    // Mostrar formulario de login después de registro exitoso
                    setTimeout(() => {
                        showLoginForm();
                    }, 1000);
                <?php endif; ?>
            <?php endif; ?>

            // Obtener elementos del DOM
            const container = document.getElementById('container');
            const registerBtn = document.getElementById('register');
            const loginBtn = document.getElementById('login');
            const registerForm = document.getElementById('register-form');
            const loginForm = document.getElementById('login-form');
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');

            // Verificar que todos los elementos existen
            if (!container) {
                console.error('Elemento container no encontrado');
                return;
            }

            // Funciones para alternar entre formularios
            function showLoginForm() {
                container.classList.remove('active');
                if (loginTab && registerTab) {
                    loginTab.classList.add('active');
                    registerTab.classList.remove('active');
                }
            }

            function showRegisterForm() {
                container.classList.add('active');
                if (loginTab && registerTab) {
                    loginTab.classList.remove('active');
                    registerTab.classList.add('active');
                }
            }

            // Event listeners para botones de escritorio
            if (registerBtn) {
                registerBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showRegisterForm();
                });
            }

            if (loginBtn) {
                loginBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showLoginForm();
                });
            }

            // Event listeners para pestañas móviles
            if (loginTab) {
                loginTab.addEventListener('click', function(e) {
                    e.preventDefault();
                    showLoginForm();
                });
            }

            if (registerTab) {
                registerTab.addEventListener('click', function(e) {
                    e.preventDefault();
                    showRegisterForm();
                });
            }

            // Validación de formularios
            function validateForm(form) {
                const inputs = form.querySelectorAll('input[required], select[required]');
                let isValid = true;
                let firstInvalidField = null;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = '#dc3545';
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    } else {
                        input.style.borderColor = '#ddd';
                    }
                });

                if (!isValid && firstInvalidField) {
                    firstInvalidField.focus();
                    alert('Por favor, completa todos los campos requeridos.');
                }

                return isValid;
            }

            // Validación del formulario de registro
            if (registerForm) {
                registerForm.addEventListener('submit', function (event) {
                    if (!validateForm(this)) {
                        event.preventDefault();
                    }
                });
            }

            // Validación del formulario de login
            if (loginForm) {
                loginForm.addEventListener('submit', function (event) {
                    if (!validateForm(this)) {
                        event.preventDefault();
                    }
                });
            }

            // Limpiar estilos de error al escribir
            document.querySelectorAll('input, select').forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.style.borderColor = '#ddd';
                    }
                });
            });

            console.log('Script inicializado correctamente');
        });

        // Función para mostrar alertas
        function showAlert(message, type) {
            // Crear el elemento de alerta
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                min-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 8px;
                animation: slideInRight 0.5s ease-out;
            `;
            
            // Definir colores según el tipo
            let bgColor, borderColor, textColor, iconClass;
            switch(type) {
                case 'error':
                    bgColor = '#f8d7da';
                    borderColor = '#f5c6cb';
                    textColor = '#721c24';
                    iconClass = 'bi-exclamation-triangle-fill';
                    break;
                case 'warning':
                    bgColor = '#fff3cd';
                    borderColor = '#ffecb5';
                    textColor = '#856404';
                    iconClass = 'bi-exclamation-triangle';
                    break;
                case 'success':
                    bgColor = '#d1e7dd';
                    borderColor = '#badbcc';
                    textColor = '#0f5132';
                    iconClass = 'bi-check-circle-fill';
                    break;
                default:
                    bgColor = '#d1ecf1';
                    borderColor = '#bee5eb';
                    textColor = '#0c5460';
                    iconClass = 'bi-info-circle';
            }
            
            alertDiv.style.backgroundColor = bgColor;
            alertDiv.style.borderColor = borderColor;
            alertDiv.style.color = textColor;
            alertDiv.style.border = `1px solid ${borderColor}`;
            
            alertDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi ${iconClass}" style="font-size: 1.2em;"></i>
                    <span style="flex: 1;">${message}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: ${textColor}; opacity: 0.7;">&times;</button>
                </div>
            `;
            
            // Agregar estilos de animación
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            if (!document.querySelector('#alert-styles')) {
                style.id = 'alert-styles';
                document.head.appendChild(style);
            }
            
            // Agregar al DOM
            document.body.appendChild(alertDiv);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.animation = 'slideOutRight 0.5s ease-in';
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 500);
                }
            }, 5000);
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
</body>

</html>