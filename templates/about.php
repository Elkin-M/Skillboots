<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>SKILLBOOTS - Tu plataforma de cursos en línea</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="cursos online, educación en línea, skillboots, aprendizaje digital, desarrollo profesional" name="keywords">
    <meta content="SkillBoots es tu plataforma de aprendizaje en línea que te conecta con los mejores cursos y profesionales para impulsar tu carrera" name="description">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #5e72e4;
            --secondary: #8392ab;
        }
        
        .bg-primary {
            background-color: var(--primary) !important;
        }
        
        .text-primary {
            color: var(--primary) !important;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #4a5acf;
            border-color: #4a5acf;
        }
        
        .bg-registration {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url(/assets/img/registration-bg.jpg);
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        .page-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url(/assets/img/page-header.jpg);
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        .testimonial-carousel .owl-dots {
            margin-top: 25px;
            text-align: center;
        }
        
        .testimonial-carousel .owl-dot {
            display: inline-block;
            margin: 0 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #dddddd;
        }
        
        .testimonial-carousel .owl-dot.active {
            background-color: var(--primary);
        }
        
        .category-card {
            transition: all 0.3s;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stats-counter {
            font-size: 3rem;
            font-weight: 700;
            color: white;
        }
        
        .course-badge {
            position: absolute;
            right: 10px;
            top: 10px;
            z-index: 10;
        }
        .stats-container {
    padding: 3rem 0;
    background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('../assets/img/stats-bg.jpg');
    background-attachment: fixed;
    background-size: cover;
    color: white;
    margin: 5rem 0;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    text-align: center;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item:last-child {
    border-right: none;
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--primary);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-text {
    font-size: 1rem;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .stat-item {
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem 1rem;
    }

    .stat-item:last-child {
        border-bottom: none;
    }
}

    </style>
</head>

<body>
<?php 
require_once '../auth/auth.php';

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

// Incluir la navbar según el rol del usuario
if ($isLoggedIn && $userRole === 'estudiante') {
    include '../includes/navbar-estu.php'; // Navbar para estudiantes
} else {
    include '../includes/navbar.php'; // Navbar para visitantes
}
?>

    <!-- Header Start -->
    <div class="container-fluid page-header" style="margin-bottom: 90px;">
        <div class="container">
            <div class="d-flex flex-column justify-content-center" style="min-height: 300px">
                <h3 class="display-4 text-white text-uppercase">Sobre Nosotros</h3>
                <div class="d-inline-flex text-white">
                    <p class="m-0 text-uppercase"><a class="text-white" href="/index.php">Inicio</a></p>
                    <i class="fa fa-angle-double-right pt-1 px-3"></i>
                    <p class="m-0 text-uppercase">Sobre Nosotros</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- About Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <img class="img-fluid rounded mb-4 mb-lg-0" src="../assets/img/about.jpg" alt="Acerca de SkillBoots">
                </div>
                <div class="col-lg-7">
                    <div class="text-left mb-4">
                        <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Nuestra Historia</h5>
                        <h1>Impulsando el Talento Digital desde 2018</h1>
                    </div>
                    <p>En SkillBoots creemos que la educación de calidad debe ser accesible para todos. Nacimos en 2018 con la misión de democratizar el conocimiento y conectar a estudiantes con los mejores profesionales en distintas áreas del conocimiento digital.</p>
                    <p>Con más de 500 instructores certificados y una comunidad de más de 120,000 estudiantes en toda Latinoamérica, nos hemos convertido en una de las plataformas educativas de mayor crecimiento en el mercado hispanohablante.</p>
                    <p>Nuestra metodología combina la teoría con proyectos prácticos que te permiten construir un portafolio profesional mientras aprendes, asegurando que no solo adquieras conocimientos, sino que puedas aplicarlos inmediatamente en el mundo real.</p>
                    <a href="/templates/course.php" class="btn btn-primary py-md-2 px-md-4 font-weight-semi-bold mt-2">Explorar Cursos</a>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Counter Start -->
    <div class="container-fluid stats-container">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-number" data-count="120000">0</div>
                        <div class="stat-text">Estudiantes</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-number" data-count="450">0</div>
                        <div class="stat-text">Cursos</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-number" data-count="600">0</div>
                        <div class="stat-text">Instructores</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="stat-number" data-count="25">20</div>
                        <div class="stat-text">Paises</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Counter End -->

    <!-- Categories Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Categorías</h5>
                <h1>Explora Nuestras Áreas de Conocimiento</h1>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="rounded overflow-hidden mb-2 category-card">
                        <img class="img-fluid" src="../assets/img/cat-1.jpg" alt="Desarrollo Web">
                        <div class="bg-white p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>25,349 Estudiantes</small>
                                <small class="m-0"><i class="fa fa-book-open text-primary mr-2"></i>85 Cursos</small>
                            </div>
                            <a class="h5" href="../templates/course.php">Desarrollo Web</a>
                            <p class="m-0 pt-2">Aprende HTML, CSS, JavaScript, React, Angular, Node.js y más para convertirte en un desarrollador web completo.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="rounded overflow-hidden mb-2 category-card">
                        <img class="img-fluid" src="../assets/img/cat-2.jpg" alt="Ciencia de Datos">
                        <div class="bg-white p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>20,128 Estudiantes</small>
                                <small class="m-0"><i class="fa fa-book-open text-primary mr-2"></i>68 Cursos</small>
                            </div>
                            <a class="h5" href="../templates/course.php">Ciencia de Datos</a>
                            <p class="m-0 pt-2">Domina Python, R, Machine Learning, Estadística y Visualización de datos para impulsar tu carrera en el análisis de datos.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="rounded overflow-hidden mb-2 category-card">
                        <img class="img-fluid" src="../assets/img/cat-3.jpg" alt="Marketing Digital">
                        <div class="bg-white p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <small class="m-0"><i class="fa fa-users text-primary mr-2"></i>18,752 Estudiantes</small>
                                <small class="m-0"><i class="fa fa-book-open text-primary mr-2"></i>72 Cursos</small>
                            </div>
                            <a class="h5" href="../templates/course.php">Marketing Digital</a>
                            <p class="m-0 pt-2">Aprende SEO, SEM, redes sociales, email marketing y estrategias de contenido para destacar en el entorno digital.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Categories End -->

    <!-- Registration Start -->
    <div class="container-fluid bg-registration py-5" style="margin: 90px 0;">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-5 mb-lg-0">
                    <div class="mb-4">
                        <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Oferta Especial</h5>
                        <h1 class="text-white">30% de Descuento para Nuevos Estudiantes</h1>
                    </div>
                    <p class="text-white">Únete hoy a nuestra comunidad de aprendizaje y obtén un 30% de descuento en tu primer curso. Comienza tu viaje de transformación profesional con acceso a contenido premium, proyectos prácticos y mentorías con expertos de la industria.</p>
                    <ul class="list-inline text-white m-0">
                        <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Acceso ilimitado a todos los materiales del curso</li>
                        <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Certificado de finalización verificado</li>
                        <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Soporte técnico y académico personalizado</li>
                        <li class="py-2"><i class="fa fa-check text-primary mr-3"></i>Comunidad exclusiva de estudiantes y profesionales</li>
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
                                    <input type="text" class="form-control border-0 p-4" placeholder="Nombre completo" required="required" />
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control border-0 p-4" placeholder="Correo electrónico" required="required" />
                                </div>
                                <div class="form-group">
                                    <select class="custom-select border-0 px-4" style="height: 47px;">
                                        <option selected>Selecciona una categoría</option>
                                        <option value="1">Desarrollo Web</option>
                                        <option value="2">Ciencia de Datos</option>
                                        <option value="3">Marketing Digital</option>
                                        <option value="4">Diseño UX/UI</option>
                                        <option value="5">Desarrollo Móvil</option>
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-dark btn-block border-0 py-3" type="submit">Comenzar Ahora</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Registration End -->

    <!-- Team Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Nuestro Equipo</h5>
                <h1>Conoce a Nuestros Líderes</h1>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="../assets/img/team-1.jpg" alt="CEO">
                        <div class="cat-overlay p-4">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <h4 class="text-white font-weight-bold mb-3">Carlos Méndez</h4>
                                <p class="text-white mb-3">CEO & Fundador</p>
                                <div class="d-flex">
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-twitter"></i></a>
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-primary btn-circle" href="#"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="../assets/img/team-2.jpg" alt="CTO">
                        <div class="cat-overlay p-4">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <h4 class="text-white font-weight-bold mb-3">Laura Sánchez</h4>
                                <p class="text-white mb-3">Directora de Tecnología</p>
                                <div class="d-flex">
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-twitter"></i></a>
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-primary btn-circle" href="#"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="../assets/img/team-3.jpg" alt="Director Académico">
                        <div class="cat-overlay p-4">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <h4 class="text-white font-weight-bold mb-3">Roberto Gómez</h4>
                                <p class="text-white mb-3">Director Académico</p>
                                <div class="d-flex">
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-twitter"></i></a>
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-primary btn-circle" href="#"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="../assets/img/team-4.jpg" alt="Directora de Marketing">
                        <div class="cat-overlay p-4">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <h4 class="text-white font-weight-bold mb-3">Ana Martínez</h4>
                                <p class="text-white mb-3">Directora de Marketing</p>
                                <div class="d-flex">
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-twitter"></i></a>
                                    <a class="btn btn-primary btn-circle mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-primary btn-circle" href="#"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

    <!-- Testimonial Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h5 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Testimonios</h5>
                <h1>Lo Que Dicen Nuestros Estudiantes</h1>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="owl-carousel testimonial-carousel">
                        <div class="text-center">
                            <i class="fa fa-3x fa-quote-left text-primary mb-4"></i>
                            <h4 class="font-weight-normal mb-4">Los cursos de SkillBoots transformaron mi carrera profesional. En menos de 6 meses, pasé de no tener experiencia en programación a conseguir mi primer trabajo como desarrollador frontend. La calidad del contenido y el apoyo de los instructores hace toda la diferencia.</h4>
                            <img class="img-fluid mx-auto mb-3" src="../assets/img/testimonial-1.jpg" alt="Testimonio de estudiante">
                            <h5 class="m-0">Miguel Rodríguez</h5>
                            <span>Desarrollador Frontend</span>
                        </div>
                        <div class="text-center">
                            <i class="fa fa-3x fa-quote-left text-primary mb-4"></i>
                            <h4 class="font-weight-normal mb-4">Como profesional de marketing tradicional, necesitaba actualizar mis habilidades digitales. Los cursos de Marketing Digital de SkillBoots me permitieron hacer la transición al entorno digital sin problemas. Ahora manejo campañas con resultados increíbles.</h4>
                            <img class="img-fluid mx-auto mb-3" src="../assets/img/testimonial-2.jpg" alt="Testimonio de estudiante">
                            <h5 class="m-0">Carmen Fuentes</h5>
                            <span>Especialista en Marketing Digital</span>
                        </div>
                        <div class="text-center">
                            <i class="fa fa-3x fa-quote-left text-primary mb-4"></i>
                            <h4 class="font-weight-normal mb-4">La combinación de teoría y práctica es perfecta. Los proyectos reales que desarrollé durante mi curso de Ciencia de Datos no solo me ayudaron a aprender, sino que enriquecieron mi portafolio. Tres meses después de terminar, conseguí mi trabajo soñado en una startup.</h4>
                            <img class="img-fluid mx-auto mb-3" src="../assets/img/testimonial-3.jpg" alt="Testimonio de estudiante">
                            <h5 class="m-0">Javier Morales</h5>
                            <span>Data Scientist</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->

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
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="../mail/jqBootstrapValidation.min.js"></script>
    <script src="../mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="../assets/js/main.js"></script>
    <script>
        // Counter animation
        $('.stat-number').each(function() {
                var $this = $(this);
                var countTo = $this.attr('data-count');
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'linear',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
    </script>
</body>

</html>