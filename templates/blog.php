<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>SKILLBOOTS - Plantilla HTML de Cursos en Línea</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Cursos en Línea, Educación Digital, Aprendizaje" name="keywords">
    <meta content="Plataforma de cursos en línea para desarrollar tus habilidades profesionales" name="description">

    <!-- Favicon -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
<?php 
session_start();
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
    include 'navbar-estu.php'; // Navbar para estudiantes
} else {
    include '../includes/navbar.php'; // Navbar para visitantes
}
?>


    <!-- Encabezado Inicio -->
    <div class="container-fluid page-header" style="margin-bottom: 90px;">
        <div class="container">
            <div class="d-flex flex-column justify-content-center" style="min-height: 300px">
                <h3 class="display-4 text-white text-uppercase">Blog</h3>
                <div class="d-inline-flex text-white">
                    <p class="m-0 text-uppercase"><a class="text-white" href="">Inicio</a></p>
                    <i class="fa fa-angle-double-right pt-1 px-3"></i>
                    <p class="m-0 text-uppercase">Blog</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Encabezado Fin -->


    <!-- Blog Inicio -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row pb-3">
                        <div class="col-lg-6 mb-4">
                            <div class="blog-item position-relative overflow-hidden rounded mb-2">
                                <img class="img-fluid" src="../assets/img/blog-1.jpg" alt="">
                                <a class="blog-overlay text-decoration-none" href="">
                                    <h5 class="text-white mb-3">Cómo dominar las habilidades digitales en el mercado laboral actual</h5>
                                    <p class="text-primary m-0">15 Ene, 2025</p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="blog-item position-relative overflow-hidden rounded mb-2">
                                <img class="img-fluid" src="../assets/img/blog-2.jpg" alt="">
                                <a class="blog-overlay text-decoration-none" href="">
                                    <h5 class="text-white mb-3">Las mejores prácticas para crear un portafolio profesional impactante</h5>
                                    <p class="text-primary m-0">12 Ene, 2025</p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="blog-item position-relative overflow-hidden rounded mb-2">
                                <img class="img-fluid" src="../assets/img/blog-3.jpg" alt="">
                                <a class="blog-overlay text-decoration-none" href="">
                                    <h5 class="text-white mb-3">Estrategias efectivas de marketing digital para pequeñas empresas</h5>
                                    <p class="text-primary m-0">10 Ene, 2025</p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="blog-item position-relative overflow-hidden rounded mb-2">
                                <img class="img-fluid" src="../assets/img/blog-1.jpg" alt="">
                                <a class="blog-overlay text-decoration-none" href="">
                                    <h5 class="text-white mb-3">Guía completa para principiantes en desarrollo web frontend moderno</h5>
                                    <p class="text-primary m-0">08 Ene, 2025</p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="blog-item position-relative overflow-hidden rounded mb-2">
                                <img class="img-fluid" src="../assets/img/blog-2.jpg" alt="">
                                <a class="blog-overlay text-decoration-none" href="">
                                    <h5 class="text-white mb-3">Tendencias tecnológicas que transformarán la educación en línea</h5>
                                    <p class="text-primary m-0">05 Ene, 2025</p>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="blog-item position-relative overflow-hidden rounded mb-2">
                                <img class="img-fluid" src="../assets/img/blog-3.jpg" alt="">
                                <a class="blog-overlay text-decoration-none" href="">
                                    <h5 class="text-white mb-3">Cómo optimizar tu tiempo de estudio para maximizar el aprendizaje</h5>
                                    <p class="text-primary m-0">03 Ene, 2025</p>
                                </a>
                            </div>
                        </div>
                        <div class="col-12">
                            <nav aria-label="Navegación de páginas">
                                <ul class="pagination pagination-lg justify-content-center mb-0">
                                  <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Anterior">
                                      <span aria-hidden="true">&laquo;</span>
                                      <span class="sr-only">Anterior</span>
                                    </a>
                                  </li>
                                  <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                                  <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Siguiente">
                                      <span aria-hidden="true">&raquo;</span>
                                      <span class="sr-only">Siguiente</span>
                                    </a>
                                  </li>
                                </ul>
                              </nav>
                        </div>
                    </div>
                </div>
    
                <div class="col-lg-4 mt-5 mt-lg-0">
                    <!-- Biografía del Autor -->
                    <div class="d-flex flex-column text-center bg-dark rounded mb-5 py-5 px-4">
                        <img src="../assets/img/user.jpg" class="img-fluid rounded-circle mx-auto mb-3" style="width: 100px;">
                        <h3 class="text-primary mb-3">María González</h3>
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Etiquetas</h3>
                        <p class="text-white m-0">Experta en educación digital y desarrollo profesional con más de 10 años de experiencia ayudando a estudiantes a alcanzar sus metas</p>
                    </div>
    
                    <!-- Formulario de Búsqueda -->
                    <div class="mb-5">
                        <form action="">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" placeholder="Buscar artículos...">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-transparent text-primary"><i
                                            class="fa fa-search"></i></span>
                                </div>
                            </div>
                        </form>
                    </div>
    
                    <!-- Lista de Categorías -->
                    <div class="mb-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Categorías</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="" class="text-decoration-none h6 m-0">Diseño Web</a>
                                <span class="badge badge-primary badge-pill">150</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="" class="text-decoration-none h6 m-0">Desarrollo Web</a>
                                <span class="badge badge-primary badge-pill">131</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="" class="text-decoration-none h6 m-0">Marketing Digital</a>
                                <span class="badge badge-primary badge-pill">78</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="" class="text-decoration-none h6 m-0">Investigación de Palabras Clave</a>
                                <span class="badge badge-primary badge-pill">56</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="" class="text-decoration-none h6 m-0">Email Marketing</a>
                                <span class="badge badge-primary badge-pill">98</span>
                            </li>
                        </ul>
                    </div>
    
                    <!-- Publicaciones Recientes -->
                    <div class="mb-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Publicaciones Recientes</h3>
                        <a class="d-flex align-items-center text-decoration-none mb-3" href="">
                            <img class="img-fluid rounded" src="../assets/img/blog-80x80.jpg" alt="">
                            <div class="pl-3">
                                <h6 class="m-1">Herramientas esenciales para desarrolladores web modernos</h6>
                                <small>28 Ene, 2025</small>
                            </div>
                        </a>
                        <a class="d-flex align-items-center text-decoration-none mb-3" href="">
                            <img class="img-fluid rounded" src="../assets/img/blog-80x80.jpg" alt="">
                            <div class="pl-3">
                                <h6 class="m-1">Cómo crear contenido viral en redes sociales efectivamente</h6>
                                <small>26 Ene, 2025</small>
                            </div>
                        </a>
                        <a class="d-flex align-items-center text-decoration-none mb-3" href="">
                            <img class="img-fluid rounded" src="../assets/img/blog-80x80.jpg" alt="">
                            <div class="pl-3">
                                <h6 class="m-1">Metodologías ágiles aplicadas al aprendizaje en línea</h6>
                                <small>24 Ene, 2025</small>
                            </div>
                        </a>
                    </div>
    
                    <!-- Nube de Etiquetas -->
                    <div class="mb-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Nube de Etiquetas</h3>
                        <div class="d-flex flex-wrap m-n1">
                            <a href="" class="btn btn-outline-primary m-1">Diseño</a>
                            <a href="" class="btn btn-outline-primary m-1">Desarrollo</a>
                            <a href="" class="btn btn-outline-primary m-1">Marketing</a>
                            <a href="" class="btn btn-outline-primary m-1">SEO</a>
                            <a href="" class="btn btn-outline-primary m-1">Redacción</a>
                            <a href="" class="btn btn-outline-primary m-1">Consultoría</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog Fin -->


    <!-- Pie de Página Inicio -->
    <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-12">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Contáctanos</h5>
                        <p><i class="fa fa-map-marker-alt mr-2"></i>Calle 123, Bogotá, Colombia</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>+57 1 234 5678</p>
                        <p><i class="fa fa-envelope mr-2"></i>info@skillboots.com</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-outline-light btn-square" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Nuestros Cursos</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Diseño Web</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Diseño de Apps</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Marketing</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Investigación</a>
                            <a class="text-white" href="#"><i class="fa fa-angle-right mr-2"></i>SEO</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Boletín Informativo</h5>
                <p>Mantente actualizado con las últimas tendencias en educación digital y desarrollo profesional. Recibe consejos exclusivos y recursos gratuitos directamente en tu correo electrónico</p>
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Tu Correo Electrónico">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4">Suscribirse</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; <a href="#">SKILLBOOTS</a>. Todos los Derechos Reservados. Diseñado por <a href="https://htmlcodex.com">HTML Codex</a>
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
    <!-- Pie de Página Fin -->


    <!-- Volver Arriba -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>


    <!-- Librerías JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Archivo JavaScript de Contacto -->
    <script src="../mail/jqBootstrapValidation.min.js"></script>
    <script src="../mail/contact.js"></script>

    <!-- JavaScript de la Plantilla -->
    <script src="../assets/js/main.js"></script>
</body>

</html>