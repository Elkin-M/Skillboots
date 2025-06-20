<?php 
session_start();
// Capturar mensajes de error/éxito de la sesión
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$error_type = isset($_SESSION['error_type']) ? $_SESSION['error_type'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Limpiar mensajes después de capturarlos para evitar que se muestren de nuevo
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
if (isset($_SESSION['error_type'])) unset($_SESSION['error_type']);
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
require_once './auth/auth.php';

// Optimizar las llamadas a `Auth::isAuthenticated()`
$isLoggedIn = Auth::isAuthenticated();
$userRole = $isLoggedIn ? Auth::getUserRole() : 'visitante';
$userName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Definir los datos de la página
$pageData = [
    'isLoggedIn' => $isLoggedIn,
    'userRole' => $userRole,
    'userName' => $userName
];


if ($pageData['isLoggedIn'] && $pageData['userRole'] === 'profesor') {
   echo "<script>window.location.href='./templates/holaaaa.php';</script>";
   exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SKILLBOOTS - Online Courses</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="./lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="./assets/css/style.css" rel="stylesheet">
    <style>
   body {
    zoom: 0.9;
}
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

.alert-custom {
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
    border: 1px solid;
}
    </style>
</head>

<body>



<!-- Carousel Start -->
<?php
// Incluir la navbar según el rol del usuario
if ($isLoggedIn && $userRole === 'estudiante') {
    include 'includes/navbar-estu.php'; // Navbar para estudiantes
} elseif ($pageData['userRole'] === 'profesor'){
    include 'includes/navbar-estu.php';
}else{
    include 'includes/navbar.php';
}
// Definir imágenes y textos por defecto
$carouselData = [
    [
        "image" => "./assets/img/carousel-1.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "La Mejor Educación Desde Casa"
    ],
    [
        "image" => "./assets/img/carousel-2.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "La Mejor Plataforma De Aprendizaje En Línea"
    ],
    [
        "image" => "./assets/img/carousel-3.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "Nueva Forma De Aprender Desde Casa"
    ]
];

// Verificar el rol del usuario y asignar nuevas imágenes y textos
if ($pageData['isLoggedIn']) {
    if ($pageData['userRole'] === 'estudiante') {
        $carouselData = [
            [
                "image" => "./assets/img/student-dashboard-1.jpg",
                "title" => "¡Bienvenido de nuevo!",
                "subtitle" => "Continúa tu aprendizaje"
            ],
            [
                "image" => "./assets/img/student-dashboard-2.jpg",
                "title" => "Explora Nuevos Conocimientos",
                "subtitle" => "Cursos Recomendados Para Ti"
            ],
            [
                "image" => "./assets/img/student-dashboard-3.jpg",
                "title" => "Logros y Certificaciones",
                "subtitle" => "Sigue Tu Progreso"
            ]
        ];
    } elseif ($pageData['userRole'] === 'profesor') {
        $carouselData = [
            [
                "image" => "./assets/img/carousel-profesor-1.jpg",
                "title" => "Bienvenido Profesor",
                "subtitle" => "Guía y Motiva a Tus Estudiantes"
            ],
            [
                "image" => "./assets/img/carousel-profesor-2.jpg",
                "title" => "Plataforma de Enseñanza",
                "subtitle" => "Comparte Conocimiento y Mejora la Educación"
            ],
            [
                "image" => "./assets/img/carousel-profesor-3.jpg",
                "title" => "Inspira a la Próxima Generación",
                "subtitle" => "Enseñar es Dejar una Huella en la Vida"
            ]
        ];
    }
}
?>

<!-- Carousel Start -->
<div class="container-fluid p-0 pb-3 mb-5">
    <div id="header-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#header-carousel" data-slide-to="0" class="active"></li>
            <li data-target="#header-carousel" data-slide-to="1"></li>
            <li data-target="#header-carousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <?php foreach ($carouselData as $index => $slide): ?>
                <div style="height: auto;" class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> " style="min-height: 300px; <?php echo $isLoggedIn ? 'height: 300px;' : ''; ?>">
                    
                <img class="position-relative w-100" src="<?php echo $slide['image']; ?>" style="z-index:-10;  min-height: 300px; object-fit: fill;">
                <div class="carousel-caption d-flex align-items-center justify-content-center">
                        <div class="p-3" style="width: 100%; max-width: 900px;">
                            <h5 class="text-white text-uppercase mb-md-3"><?php echo $slide['title']; ?></h5>
                            <h1 class="display-3 text-white mb-md-4"><?php echo $slide['subtitle']; ?></h1>
                            <a href="" class="btn btn-primary py-md-2 px-md-4 font-weight-semi-bold mt-2">Leer Más</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Carousel End -->

<?php
if ($isLoggedIn && $userRole === 'estudiante') {
    include './templates/pruebaa.php'; // Navbar para estudiantes
}
?>

<?php if ($isLoggedIn && ($userRole === 'estudiante' || $userRole === 'profesor')) : ?>
<div class="container-fluid py-5 bg-light">
    <div class="container pt-5 pb-3">
        <div class="text-center mb-5">
            <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Tu Progreso</h5>
            <h1>Cursos Recientes</h1>
        </div>
        
        <!-- Contenedor de cursos -->
        <div class="row" id="courses-container">
            <!-- Los cursos se cargarán aquí dinámicamente -->
        </div>
        
        <!-- Botón para ver todos los cursos -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="#" class="btn btn-outline-primary px-4 py-2">Ver todos mis cursos</a>
            </div>
        </div>
    </div>
</div>

<!-- Modales para mostrar detalles del curso -->
<div class="modal fade" id="courseDetailModal" tabindex="-1" aria-labelledby="courseDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseDetailModalLabel">Detalles del Curso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#courseDetailModal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="courseDetailContent">
                <!-- Contenido dinámico del curso -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#courseDetailModal').modal('hide');">Cerrar</button>
                <a href="ver-cursos.php?id=<?php echo $curso['id']; ?>" type="button" class="btn btn-primary" id="continueCourseBtn">Continuar curso</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php
if (isset($pageData['isLoggedIn']) && $pageData['isLoggedIn']) {
    if (isset($pageData['userRole'])) {
        if ($pageData['userRole'] === 'estudiante') {
            include './modules/programs/progreso-estudiantes.php';
        } elseif ($pageData['userRole'] === 'profesor') {
            include './modules/programs/progreso-profesor.php';
        }
    }
}
?>

<?php endif; ?>

    <!-- About Start -->
<?php if (!$pageData['isLoggedIn']) : ?>
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <img class="img-fluid rounded mb-4 mb-lg-0" src="./assets/img/about.jpg" alt="">
                </div>
                <div class="col-lg-7">
                    <div class="text-left mb-4">
                        <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Sobre Nosotros</h5>
                        <h1>Impulsamos tu Futuro Profesional</h1>
                    </div>
                        <p>En SkillBoots, creemos en el poder del aprendizaje práctico y accesible. Nuestra plataforma ofrece formación moderna diseñada para el mundo real, con cursos dinámicos, retos interactivos y apoyo continuo. Ya sea que busques avanzar en tu carrera, aprender una nueva habilidad o emprender tu camino, te brindamos las herramientas y el conocimiento para lograrlo. Únete a miles de estudiantes que confían en SkillBoots para transformar su aprendizaje en resultados reales y medibles.</p>
                        <a href="" class="btn btn-primary py-md-2 px-md-4 font-weight-semi-bold mt-2">Leer Más</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
    <!-- About End -->


    <!-- Category Start -->
    <div class="container-fluid py-5">
        <div class="container pt-5 pb-3">
            <div class="text-center mb-5">
                <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Temas</h5>
                <h1>Explora Temas Mas Vistos</h1>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-1.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Web Design</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-2.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Development</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-3.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Game Design</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-4.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Apps Design</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-5.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Marketing</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-6.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Research</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-7.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">Content Writing</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="./assets/img/cat-8.jpg" alt="">
                        <a class="cat-overlay text-white text-decoration-none" href="">
                            <h4 class="text-white font-weight-medium">SEO</h4>
                            <span>100 Courses</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Category Start -->


    <!-- Courses Start -->
    <div class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Cursos</h5>
            <h1>Nuestros Cursos Populares</h1>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="rounded overflow-hidden mb-2">
                    <img class="img-fluid" src="./assets/img/course-1.jpg" alt="">
                    <div class="bg-secondary p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>25 Estudiantes</small>
                            <small class="m-0"><i class="far fa-clock text-primary mr-2"></i>1h 30min</small>
                        </div>
                        <a class="h5" href="">Diseño y desarrollo web para principiantes</a>
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex justify-content-between">
                                <h6 class="m-0"><i class="fa fa-star text-primary mr-2"></i>4.5 <small>(250)</small></h6>
                                <h5 class="m-0">$99</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="rounded overflow-hidden mb-2">
                    <img class="img-fluid" src="./assets/img/course-2.jpg" alt="">
                    <div class="bg-secondary p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>30 Estudiantes</small>
                            <small class="m-0"><i class="far fa-clock text-primary mr-2"></i>2h 00min</small>
                        </div>
                        <a class="h5" href="">Introducción al marketing digital</a>
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex justify-content-between">
                                <h6 class="m-0"><i class="fa fa-star text-primary mr-2"></i>4.7 <small>(180)</small></h6>
                                <h5 class="m-0">$89</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="rounded overflow-hidden mb-2">
                    <img class="img-fluid" src="./assets/img/course-3.jpg" alt="">
                    <div class="bg-secondary p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>20 Estudiantes</small>
                            <small class="m-0"><i class="far fa-clock text-primary mr-2"></i>1h 45min</small>
                        </div>
                        <a class="h5" href="">Fotografía profesional con cámara y móvil</a>
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex justify-content-between">
                                <h6 class="m-0"><i class="fa fa-star text-primary mr-2"></i>4.6 <small>(210)</small></h6>
                                <h5 class="m-0">$75</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="rounded overflow-hidden mb-2">
                    <img class="img-fluid" src="./assets/img/course-4.jpg" alt="">
                    <div class="bg-secondary p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>35 Estudiantes</small>
                            <small class="m-0"><i class="far fa-clock text-primary mr-2"></i>2h 30min</small>
                        </div>
                        <a class="h5" href="">Programación en Python desde cero</a>
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex justify-content-between">
                                <h6 class="m-0"><i class="fa fa-star text-primary mr-2"></i>4.8 <small>(300)</small></h6>
                                <h5 class="m-0">$120</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="rounded overflow-hidden mb-2">
                    <img class="img-fluid" src="./assets/img/course-5.jpg" alt="">
                    <div class="bg-secondary p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>28 Estudiantes</small>
                            <small class="m-0"><i class="far fa-clock text-primary mr-2"></i>2h 15min</small>
                        </div>
                        <a class="h5" href="">Diseño gráfico con herramientas gratuitas</a>
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex justify-content-between">
                                <h6 class="m-0"><i class="fa fa-star text-primary mr-2"></i>4.4 <small>(190)</small></h6>
                                <h5 class="m-0">$79</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="rounded overflow-hidden mb-2">
                    <img class="img-fluid" src="./assets/img/course-6.jpg" alt="">
                    <div class="bg-secondary p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>22 Estudiantes</small>
                            <small class="m-0"><i class="far fa-clock text-primary mr-2"></i>1h 20min</small>
                        </div>
                        <a class="h5" href="">Excel para negocios y análisis de datos</a>
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex justify-content-between">
                                <h6 class="m-0"><i class="fa fa-star text-primary mr-2"></i>4.3 <small>(170)</small></h6>
                                <h5 class="m-0">$65</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin de Cursos -->


<?php if (!$pageData['isLoggedIn']) : ?>
<!-- Inicio de Registro -->
<div class="container-fluid bg-registration py-5" style="margin: 90px 0;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-5 mb-lg-0">
                <div class="mb-4">
                    <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">¿Buscas un curso?</h5>
                    <h1 class="text-white">30% de descuento para nuevos estudiantes</h1>
                </div>
                <p class="text-white">
                    ¡Aprovecha esta oportunidad única para iniciar tu aprendizaje! Regístrate y accede a nuestros cursos con un 30% de descuento exclusivo para nuevos estudiantes. Aprende a tu ritmo, con los mejores instructores.
                </p>
                <ul class="list-inline text-white m-0">
                    <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Cursos actualizados y prácticos</li>
                    <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Acceso desde cualquier dispositivo</li>
                    <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Certificación al finalizar</li>
                </ul>
            </div>
            <div class="col-lg-5">
                <div class="card border-0">
                    <div class="card-header bg-light text-center p-4">
                        <h1 class="m-0">Regístrate Ahora</h1>
                    </div>
                    <div class="card-body rounded-bottom bg-primary p-5">
                        <form>
                            <div class="form-group">
                                <input type="text" class="form-control border-0 p-4" placeholder="Tu nombre" required="required" />
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control border-0 p-4" placeholder="Tu correo electrónico" required="required" />
                            </div>
                            <div class="form-group">
                                <select class="custom-select border-0 px-4" style="height: 47px;">
                                    <option selected>Selecciona un curso</option>
                                    <option value="1">Programación en Python</option>
                                    <option value="2">Marketing Digital</option>
                                    <option value="3">Diseño Web Básico</option>
                                </select>
                            </div>
                            <div>
                                <button class="btn btn-dark btn-block border-0 py-3" type="submit">Registrarme</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin de Registro -->

    <!-- Registration End -->
<?php endif; ?>

<!-- Inicio del Equipo Docente -->
<div class="container-fluid py-5">
    <div class="container pt-5 pb-3">
        <div class="text-center mb-5">
            <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Docentes</h5>
            <h1>Conoce a Nuestro Equipo de Profesores</h1>
        </div>
        <div class="row">
            <div class="col-md-6 col-lg-3 text-center team mb-4">
                <div class="team-item rounded overflow-hidden mb-2">
                    <div class="team-img position-relative">
                        <img class="img-fluid" src="./assets/img/team-1.jpg" alt="María Rodríguez - Profesora de Diseño Web">
                        <div class="team-social">
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="bg-secondary p-4">
                        <h5>María Rodríguez</h5>
                        <p class="m-0">Diseño Web</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center team mb-4">
                <div class="team-item rounded overflow-hidden mb-2">
                    <div class="team-img position-relative">
                        <img class="img-fluid" src="./assets/img/team-2.jpg" alt="Carlos Pérez - Profesor de Desarrollo Backend">
                        <div class="team-social">
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="bg-secondary p-4">
                        <h5>Carlos Pérez</h5>
                        <p class="m-0">Desarrollo Backend</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center team mb-4">
                <div class="team-item rounded overflow-hidden mb-2">
                    <div class="team-img position-relative">
                        <img class="img-fluid" src="./assets/img/team-3.jpg" alt="Laura Martínez - Profesora de Marketing Digital">
                        <div class="team-social">
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="bg-secondary p-4">
                        <h5>Laura Martínez</h5>
                        <p class="m-0">Marketing Digital</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center team mb-4">
                <div class="team-item rounded overflow-hidden mb-2">
                    <div class="team-img position-relative">
                        <img class="img-fluid" src="./assets/img/team-4.jpg" alt="Andrés Gómez - Profesor de Ciencia de Datos">
                        <div class="team-social">
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="bg-secondary p-4">
                        <h5>Andrés Gómez</h5>
                        <p class="m-0">Ciencia de Datos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin del Equipo Docente -->



    <!-- Inicio Testimonios -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Testimonios</h5>
            <h1>Lo que dicen nuestros estudiantes</h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="owl-carousel testimonial-carousel">
                    <div class="text-center">
                        <i class="fa fa-3x fa-quote-left text-primary mb-4"></i>
                        <h4 class="font-weight-normal mb-4">
                            Gracias a esta plataforma logré certificarme como desarrollador web. Los contenidos son claros, actualizados y los profesores siempre están dispuestos a ayudar.
                        </h4>
                        <img class="img-fluid mx-auto mb-3" src="./assets/img/testimonial-1.jpg" alt="Estudiante Testimonio 1">
                        <h5 class="m-0">Daniela Torres</h5>
                        <span>Estudiante de Desarrollo Web</span>
                    </div>
                    <div class="text-center">
                        <i class="fa fa-3x fa-quote-left text-primary mb-4"></i>
                        <h4 class="font-weight-normal mb-4">
                            Lo que más me gustó fue la flexibilidad para aprender a mi ritmo. Pude completar los cursos sin afectar mi trabajo diario. ¡Muy recomendado!
                        </h4>
                        <img class="img-fluid mx-auto mb-3" src="./assets/img/testimonial-2.jpg" alt="Estudiante Testimonio 2">
                        <h5 class="m-0">Luis Fernández</h5>
                        <span>Diseñador Gráfico Freelance</span>
                    </div>
                    <div class="text-center">
                        <i class="fa fa-3x fa-quote-left text-primary mb-4"></i>
                        <h4 class="font-weight-normal mb-4">
                            Me encantaron los recursos interactivos y la forma en que explican temas complejos. Ahora me siento lista para emprender mi propio negocio digital.
                        </h4>
                        <img class="img-fluid mx-auto mb-3" src="./assets/img/testimonial-3.jpg" alt="Estudiante Testimonio 3">
                        <h5 class="m-0">Carla Mendoza</h5>
                        <span>Emprendedora</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin Testimonios -->
<!-- Inicio Blog -->
<div class="container-fluid py-5">
    <div class="container pt-5 pb-3">
        <div class="text-center mb-5">
            <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Nuestro Blog</h5>
            <h1>Últimas Publicaciones</h1>
        </div>
        <div class="row pb-3">
            <div class="col-lg-4 mb-4">
                <div class="blog-item position-relative overflow-hidden rounded mb-2">
                    <img class="img-fluid" src="./assets/img/blog-1.jpg" alt="Imagen Blog 1">
                    <a class="blog-overlay text-decoration-none" href="#">
                        <h5 class="text-white mb-3">5 consejos para aprender en línea de forma efectiva</h5>
                        <p class="text-primary m-0">27 May, 2025</p>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="blog-item position-relative overflow-hidden rounded mb-2">
                    <img class="img-fluid" src="./assets/img/blog-2.jpg" alt="Imagen Blog 2">
                    <a class="blog-overlay text-decoration-none" href="#">
                        <h5 class="text-white mb-3">Tendencias en educación digital para el 2025</h5>
                        <p class="text-primary m-0">20 May, 2025</p>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="blog-item position-relative overflow-hidden rounded mb-2">
                    <img class="img-fluid" src="./assets/img/blog-3.jpg" alt="Imagen Blog 3">
                    <a class="blog-overlay text-decoration-none" href="#">
                        <h5 class="text-white mb-3">Cómo mantener la motivación durante un curso en línea</h5>
                        <p class="text-primary m-0">13 May, 2025</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin Blog -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-12">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Contáctanos</h5>
                        <p><i class="fa fa-map-marker-alt mr-2"></i>Av. Innovación 1234, Ciudad Tech, España</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>+34 912 345 678</p>
                        <p><i class="fa fa-envelope mr-2"></i>info@skillboots.com</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-outline-light btn-square" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Categorías Populares</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Desarrollo Web</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Ciencia de Datos</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Marketing Digital</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Diseño UX/UI</a>
                            <a class="text-white" href="#"><i class="fa fa-angle-right mr-2"></i>Desarrollo Móvil</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Newsletter</h5>
                <p>Suscríbete a nuestro boletín para recibir las últimas novedades en cursos, descuentos especiales y recursos gratuitos para impulsar tu carrera.</p>
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Tu correo electrónico">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4">Suscribirme</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; <a href="#">SkillBoots</a>. Todos los derechos reservados. Diseñado con <i class="fa fa-heart text-primary"></i> por el equipo de SkillBoots.
                </p>
            </div>
            <div class="col-lg-6 text-center text-md-right">
                <ul class="nav d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Privacidad</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Términos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Preguntas Frecuentes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Ayuda</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Footer End -->
    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="./lib/easing/easing.min.js"></script>
    <script src="./lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="./mail/jqBootstrapValidation.min.js"></script>
    <script src="./mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="./assets/js/main.js"></script>
    <div class="chatbot">
<script type="text/javascript">
  (function(d, t) {
      var v = d.createElement(t), s = d.getElementsByTagName(t)[0];
      v.onload = function() {
        window.voiceflow.chat.load({
          verify: { projectID: '68099233346844a0cbf6dd37' },
          url: 'https://general-runtime.voiceflow.com',
          versionID: 'production',
          voice: {
            url: "https://runtime-api.voiceflow.com"
          }
        });
      }
      v.src = "https://cdn.voiceflow.com/widget-next/bundle.mjs"; v.type = "text/javascript"; s.parentNode.insertBefore(v, s);
  })(document, 'script');
          
          
          
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert-custom';
        
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
                <button type="button" onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: ${textColor}; opacity: 0.7;">&times;</button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
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

    <?php if (!empty($error_message)): ?>
        showAlert('<?php echo addslashes($error_message); ?>', '<?php echo $error_type; ?>');
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        showAlert('<?php echo addslashes($success_message); ?>', 'success');
    <?php endif; ?>
});


</script>

</body>

</html>