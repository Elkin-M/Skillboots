<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/sesion.html");
    exit();
}
$nombre = $_SESSION['user_name'];
$rol = $_SESSION['user_rol'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Profesores</title>
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/notificacion.css" rel="stylesheet">
    <link href="../assets/css/navbar-pro.css" rel="stylesheet">
    
    <style>
        /* Base navbar styles */
        
    </style>
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid d-none d-lg-block">
        <div class="nav-bar row align-items-center py-3 px-xl-5">
            <!-- Logo -->
            <div class="col-lg-3 logo-container">
                <a href="" class="text-decoration-none">
                    <h1 class="m-0"><span class="text-primary">SKILL</span>BOOTS</h1>
                </a>
            </div>

            <!-- Navigation Menu -->
            <div class="derecho">
                <a href="dashboard-profesor.php" class="nav-item nav-link active">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a href="crear_curso.php#crearCurso" class="nav-item nav-link">
                    <i class="fas fa-book"></i>Mis Cursos
                </a>
                <a href="calificaciones.php" class="nav-item nav-link position-relative">
                    <i class="fas fa-tasks"></i>Calificaciones
                    <span class="badge-new">5</span>
                </a>
                
                <!-- Notifications dropdown -->
                <div class="dropdown">
                    <a href="#" class="nav-link position-relative" id="notificationDropdownToggle">
                        <i class="fas fa-bell"></i>
                        <span id="notificationBadge" class="badge badge-pill badge-danger">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" id="notificationDropdown" aria-labelledby="notificationDropdownToggle">
                        <!-- Notifications will be inserted here dynamically -->
                        <div class="dropdown-item">
                            <i class="fas fa-upload text-primary"></i>
                            <div>
                                <strong>Nueva entrega</strong>
                                <p class="small text-muted mb-0">Juan Pérez ha subido su tarea de Matemáticas</p>
                                <small class="text-muted">Hace 10 minutos</small>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-item">
                            <i class="fas fa-question-circle text-warning"></i>
                            <div>
                                <strong>Nueva pregunta</strong>
                                <p class="small text-muted mb-0">María González tiene una duda en el tema 3</p>
                                <small class="text-muted">Hace 30 minutos</small>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-item">
                            <i class="fas fa-calendar-alt text-success"></i>
                            <div>
                                <strong>Recordatorio</strong>
                                <p class="small text-muted mb-0">Clase de Programación a las 15:00</p>
                                <small class="text-muted">Hoy</small>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="todas-notificaciones.php" class="dropdown-item text-center text-primary">
                            Ver todas las notificaciones
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Section: Search + User Info -->
            <div class="right-section">
                <!-- User dropdown menu -->
                <div class="user-info dropdown">
                    <span><?php echo htmlspecialchars($nombre); ?></span>
                    <span class="profesor-badge">Profesor</span>
                    <div class="user-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                    </div>
                    <div class="dropdown-menu">
                        <a href="perfil-profesor.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i> Mi Perfil
                        </a>
                        <a href="configuracion.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <a href="horario-clases.php" class="dropdown-item">
                            <i class="fas fa-calendar-alt"></i> Horario de Clases
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="soporte.php" class="dropdown-item">
                            <i class="fas fa-question-circle"></i> Soporte
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="./conexion/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Mobile Navbar Start -->
    <div class="container-fluid d-lg-none">
        <div class="row border-top px-3">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0">
                    <!-- Logo (mobile only) -->
                    <a href="" class="text-decoration-none d-block d-lg-none">
                        <h1 class="m-0"><span class="text-primary">SKILL</span>BOOTS</h1>
                    </a>

                    <!-- Hamburger button -->
                    <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Mobile menu -->
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <!-- Search bar in mobile view -->
                        
                        <div class="navbar-nav py-0">
                            <!-- User info in mobile menu -->
                            <div class="user-info" style="justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                                <span><?php echo htmlspecialchars($nombre); ?></span>
                                <span class="profesor-badge">Profesor</span>
                            </div>
                            
                            <!-- Mobile navigation links -->
                            <a href="dashboard-profesor.php" class="nav-item nav-link active">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a href="mis-cursos.php" class="nav-item nav-link">
                                <i class="fas fa-book me-2"></i>Mis Cursos
                            </a>
                            <a href="calificaciones.php" class="nav-item nav-link">
                                <i class="fas fa-tasks me-2"></i>Calificaciones
                                <span class="badge bg-danger rounded-pill ms-1">5</span>
                            </a>
                            <a href="horario-clases.php" class="nav-item nav-link">
                                <i class="fas fa-calendar-alt me-2"></i>Horario
                            </a>
                            <a href="todas-notificaciones.php" class="nav-item nav-link">
                                <i class="fas fa-bell me-2"></i>Notificaciones
                                <span class="badge bg-danger rounded-pill ms-1">3</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="perfil-profesor.php" class="nav-item nav-link">
                                <i class="fas fa-user-cog me-2"></i>Mi Perfil
                            </a>
                            <a href="configuracion.php" class="nav-item nav-link">
                                <i class="fas fa-cog me-2"></i>Configuración
                            </a>
                            <a href="soporte.php" class="nav-item nav-link">
                                <i class="fas fa-question-circle me-2"></i>Soporte
                            </a>
                            <a href="./conexion/logout.php" class="nav-item nav-link">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Mobile Navbar End -->

    <!-- JavaScript Libraries -->
    <script src="../assets/js/notificaciones.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // JavaScript can be added here for enhanced functionality
            
            // Toggle notifications dropdown on click (for mobile/touch devices)
            const notificationToggle = document.getElementById('notificationDropdownToggle');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            if (notificationToggle && notificationDropdown) {
                notificationToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('show');
                });
            }
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.parentNode.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });
        });
    </script>
</body>
</html>