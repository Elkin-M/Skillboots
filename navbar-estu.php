<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: sesion.html");
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
    <title>Document</title>
        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="./css/style.css" rel="stylesheet">
    <link href="./css/notificacion.css" rel="stylesheet">
</head>


    <style>
        .nav-bar{
            display: flex;
            justify-content: space-between;
            flex-direction: row;
        }

        .derecho{
            display: flex;
            justify-content: space-between;
            flex-direction: row;

        }
        .derecho a{
            padding: 20px 15px;
            color: #44425A;
            font-size: 18px;
            font-weight: 500;
            outline: none;
        }

        .nav-bar input{
            border: 3px solid #bebebe;
            border-radius: 10px;
        }
        input{
            outline: none;
        }
        .lupa{
            background-color: #ff6600;
            color: white;
            border-radius: 10px;
            padding: 3px 5px;
            text-align: center;
            justify-content: center;
            align-items: center;
            margin:auto;
        }
        .user-info {
        display: flex;
        align-items: center;
        gap: 5px;
        position: relative;
    }
    
    .user-info span {
        margin-right: 5px;
    }
    
    .user-avatar {
        cursor: pointer;
    }
    
    .dropdown {
        position: relative;
    }
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        width: 200px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: none;
        z-index: 1000;
        padding: 8px 0;
        margin-top: 5px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .dropdown:hover .dropdown-menu {
        display: block;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        text-decoration: none;
        color: #44425A;
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
        background-color: rgba(255, 102, 0, 0.1);
        color: #ff6600;
    }
    
    .dropdown-item i {
        margin-right: 10px;
        width: 16px;
        text-align: center;
    }
    
    .dropdown-divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 8px 0;
    }
    
    /* Efecto de triángulo en la parte superior del menú desplegable */
    .dropdown-menu::before {
        content: '';
        position: absolute;
        top: -8px;
        right: 20px;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 8px solid white;
    }
    .profesor-badge {
    background-color:rgb(76, 137, 175);
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

    </style>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid d-none d-lg-block">
        <div class="nav-bar row align-items-center py-4 px-xl-5" style="flex-wrap: nowrap;">
            <div class="col-lg-3">
                <a href="" class="text-decoration-none">
                    <h1 class="m-0"><span class="text-primary">SKILL</span>BOOTS</h1>
                </a>
            </div>

            <div class="derecho">
                            <a href="index.php" class="nav-item nav-link active" style="color:#ff6600; ">Inicio</a>
                            <a href="course.php" class="nav-item nav-link">Cursos</a>
                            <a href="teacher.php" class="nav-item nav-link">Profesores</a>
                            <a href="contact.php" class="nav-item nav-link">Contactanos</a>
                <!-- icono de notificaciones -->
                <div class="dropdown">
    <a href="#" class="nav-link dropdown-toggle" id="notificationDropdownToggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span id="notificationBadge" class="badge badge-pill badge-danger" style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-right" id="notificationDropdown" aria-labelledby="notificationDropdownToggle" style="max-width: 300px; overflow-x: hidden; overflow-y: auto; max-height: 400px;">
        <!-- Las notificaciones se insertarán aquí dinámicamente -->
        <div class="dropdown-item text-center">Cargando notificaciones...</div>
    </div>
</div>
            </div>
            <!-- barra de busqueda -->

            <!-- <div class="search-container">
                <form action="buscar-falta.php" method="GET"> 
                    <input type="search" id="search" name="q" placeholder="Buscar">
                    <button type="submit" class="btn btn-primary lupa">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                    <path d="M21 21l-6 -6" />
            </svg>
        </button>
    </form>
</div> -->


 <!-- menu desplegable -->
<div class="user-info dropdown">
    <span><?php echo htmlspecialchars($nombre); ?></span>
    <span class="profesor-badge">Estudiante</span>

    <div class="user-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
    </div>
    <div class="dropdown-menu">
        <a href="perfil.php" class="dropdown-item">
            <i class="fas fa-user-cog"></i> Mi Perfil
        </a>
        <a href="dashboard.php" class="dropdown-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <div class="dropdown-divider"></div>
        <a href="./conexion/logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</div>
            
        </div>
    </div>
    <!-- Topbar End -->

<!-- Navbar Start -->
<div class="container-fluid d-lg-none"> <!-- Añadí d-lg-none aquí para ocultarlo en pantallas grandes -->
    <div class="row border-top px-xl-5">
        <div class="col-lg-9">
            <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0">
                <!-- Logo (visible solo en móviles) -->
                <a href="" class="text-decoration-none d-block d-lg-none">
                    <h1 class="m-0"><span class="text-primary">SKILL</span>BOOTS</h1>
                </a>

                <!-- Botón hamburguesa (visible solo en móviles) -->
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menú de navegación (solo visible en móviles) -->
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav py-0">
                    <div class="user-info justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                            <span><?php echo htmlspecialchars($nombre); ?></span>

                    </div>
                        <a href="index.php" class="nav-item nav-link">Inicio</a>
                        <a href="about.php" class="nav-item nav-link">Acerca De</a>
                        <a href="course.php" class="nav-item nav-link">Cursos</a>
                        <a href="teacher.php" class="nav-item nav-link">Profesores</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Blog</a>
                            <div class="dropdown-menu rounded-0 m-0">
                                <a href="blog.php" class="dropdown-item">Lista De Blog</a>
                                <a href="single.php" class="dropdown-item">Cuadros De Blog</a>
                            </div>
                        </div>
                        <a href="contact.php" class="nav-item nav-link">Contáctanos</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>
    <!-- Navbar End -->
     <!-- JavaScript Libraries -->
<script src="./js/notificaciones.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Esta función no es necesaria si usamos :hover en CSS, pero la añado
    // por si prefieres un control más preciso con JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Si prefieres controlar el menú con clics en lugar de hover
        // Descomenta este código:
        
        /*
        const userAvatar = document.querySelector('.user-avatar');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        userAvatar.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Cerrar el menú cuando se hace clic en cualquier otro lugar
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                dropdownMenu.classList.remove('show');
            }
        });
        */
    });
</script>
</body>
</html>