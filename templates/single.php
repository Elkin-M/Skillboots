<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>SKILLBOOTS - Plantilla HTML de Cursos en Línea</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Plantillas HTML Gratuitas" name="keywords">
    <meta content="Plantillas HTML Gratuitas" name="description">

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

    <!-- Header Start -->
    <div class="container-fluid page-header" style="margin-bottom: 90px;">
        <div class="container">
            <div class="d-flex flex-column justify-content-center" style="min-height: 300px">
                <h3 class="display-4 text-white text-uppercase">Artículo</h3>
                <div class="d-inline-flex text-white">
                    <p class="m-0 text-uppercase"><a class="text-white" href="">Inicio</a></p>
                    <i class="fa fa-angle-double-right pt-1 px-3"></i>
                    <p class="m-0 text-uppercase">Artículo</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->


    <!-- Detail Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <h6 class="text-primary mb-3">01 Ene, 2050</h6>
                        <h1 class="mb-5">Aprende desarrollo web moderno con las mejores prácticas y tecnologías actuales</h1>
                        <img class="img-fluid rounded w-100 mb-4" src="../assets/img/carousel-1.jpg" alt="Image">
                        <p>El desarrollo web moderno requiere un enfoque integral que combine tanto habilidades técnicas como conocimiento de las últimas tendencias. En esta guía completa exploraremos las herramientas esenciales, frameworks populares y metodologías que todo desarrollador web debe dominar para crear aplicaciones exitosas y eficientes.</p>
                        <p>Desde HTML5 y CSS3 hasta JavaScript avanzado y frameworks como React, Vue.js y Angular, cubriremos todos los aspectos fundamentales. También abordaremos temas cruciales como la optimización de rendimiento, accesibilidad web, diseño responsivo y las mejores prácticas de SEO para garantizar que tus proyectos destaquen en el competitivo mundo digital actual.</p>
                        <h2 class="mb-4">Tecnologías fundamentales para dominar</h2>
                        <img class="img-fluid rounded w-50 float-left mr-4 mb-3" src="../assets/img/blog-1.jpg" alt="Image">
                        <p>Para convertirse en un desarrollador web competente es esencial dominar las tecnologías base como HTML, CSS y JavaScript. Estas son las piedras angulares sobre las cuales se construyen todas las aplicaciones web modernas. HTML proporciona la estructura semántica, CSS se encarga del diseño visual y la presentación, mientras que JavaScript añade interactividad y funcionalidad dinámica. Además de estos fundamentos, es crucial familiarizarse con herramientas de desarrollo como Git para control de versiones, editores de código avanzados como VS Code, y entender conceptos de diseño responsivo para crear experiencias optimizadas en todos los dispositivos. El ecosistema web actual también demanda conocimiento en APIs REST, bases de datos, y principios de seguridad web para desarrollar aplicaciones robustas y escalables que satisfagan las necesidades del mercado tecnológico contemporáneo.</p>
                        <p>El panorama del desarrollo web evoluciona constantemente, por lo que mantenerse actualizado con las últimas tendencias y herramientas es fundamental para el éxito profesional. Los frameworks modernos como React, Vue.js y Angular han revolucionado la forma en que construimos interfaces de usuario, ofreciendo componentes reutilizables y arquitecturas escalables que facilitan el mantenimiento del código a largo plazo.</p>
                    </div>

                    <!-- Comment List -->
                    <div class="mb-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">3 Comentarios</h3>
                        <div class="media mb-4">
                            <img src="../assets/img/user.jpg" alt="Image" class="img-fluid rounded-circle mr-3 mt-1"
                                style="width: 45px;">
                            <div class="media-body">
                                <h6>Juan Pérez <small><i>01 Ene 2045 a las 12:00pm</i></small></h6>
                                <p>Excelente artículo sobre desarrollo web. Me ha resultado muy útil especialmente la sección sobre frameworks modernos. Las explicaciones son claras y los ejemplos prácticos realmente ayudan a entender los conceptos. Definitivamente pondré en práctica estas recomendaciones en mis próximos proyectos.</p>
                                <button class="btn btn-sm btn-secondary">Responder</button>
                            </div>
                        </div>
                        <div class="media mb-4">
                            <img src="../assets/img/user.jpg" alt="Image" class="img-fluid rounded-circle mr-3 mt-1"
                                style="width: 45px;">
                            <div class="media-body">
                                <h6>María García <small><i>01 Ene 2045 a las 12:00pm</i></small></h6>
                                <p>Como desarrolladora junior, este contenido me ha proporcionado una perspectiva muy valiosa sobre las tecnologías que debo priorizar en mi aprendizaje. La información sobre las mejores prácticas es especialmente útil para alguien que está empezando en este campo.</p>
                                <button class="btn btn-sm btn-secondary">Responder</button>
                                <div class="media mt-4">
                                    <img src="../assets/img/user.jpg" alt="Image" class="img-fluid rounded-circle mr-3 mt-1"
                                        style="width: 45px;">
                                    <div class="media-body">
                                        <h6>Carlos López <small><i>01 Ene 2045 a las 12:00pm</i></small></h6>
                                        <p>Totalmente de acuerdo María. Yo también estoy empezando y estos recursos han sido fundamentales para estructurar mi ruta de aprendizaje. Especialmente útiles son los consejos sobre herramientas de desarrollo y control de versiones con Git.</p>
                                        <button class="btn btn-sm btn-secondary">Responder</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comment Form -->
                    <div class="bg-secondary rounded p-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Deja un comentario</h3>
                        <form>
                            <div class="form-group">
                                <label for="name">Nombre *</label>
                                <input type="text" class="form-control border-0" id="name">
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control border-0" id="email">
                            </div>
                            <div class="form-group">
                                <label for="website">Sitio Web</label>
                                <input type="url" class="form-control border-0" id="website">
                            </div>

                            <div class="form-group">
                                <label for="message">Mensaje *</label>
                                <textarea id="message" cols="30" rows="5" class="form-control border-0"></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <input type="submit" value="Enviar Comentario" class="btn btn-primary py-md-2 px-md-4 font-weight-semi-bold">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4 mt-5 mt-lg-0">
                    <!-- Author Bio -->
                    <div class="d-flex flex-column text-center bg-dark rounded mb-5 py-5 px-4">
                        <img src="../assets/img/user.jpg" class="img-fluid rounded-circle mx-auto mb-3" style="width: 100px;">
                        <h3 class="text-primary mb-3">Ana Rodríguez</h3>
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Sobre la Autora</h3>
                        <p class="text-white m-0">Desarrolladora Full Stack con más de 8 años de experiencia en tecnologías web modernas, especializada en React, Node.js y arquitectura de aplicaciones escalables.</p>
                    </div>

                    <!-- Search Form -->
                    <div class="mb-5">
                        <form action="">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" placeholder="Buscar...">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-transparent text-primary"><i
                                            class="fa fa-search"></i></span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Category List -->
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

                    <!-- Recent Post -->
                    <div class="mb-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Artículos Recientes</h3>
                        <a class="d-flex align-items-center text-decoration-none mb-3" href="">
                            <img class="img-fluid rounded" src="../assets/img/blog-80x80.jpg" alt="">
                            <div class="pl-3">
                                <h6 class="m-1">Guía completa de JavaScript moderno para principiantes</h6>
                                <small>01 Ene, 2050</small>
                            </div>
                        </a>
                        <a class="d-flex align-items-center text-decoration-none mb-3" href="">
                            <img class="img-fluid rounded" src="../assets/img/blog-80x80.jpg" alt="">
                            <div class="pl-3">
                                <h6 class="m-1">Cómo optimizar el rendimiento de tu sitio web</h6>
                                <small>01 Ene, 2050</small>
                            </div>
                        </a>
                        <a class="d-flex align-items-center text-decoration-none mb-3" href="">
                            <img class="img-fluid rounded" src="../assets/img/blog-80x80.jpg" alt="">
                            <div class="pl-3">
                                <h6 class="m-1">Tendencias en diseño web para el próximo año</h6>
                                <small>01 Ene, 2050</small>
                            </div>
                        </a>
                    </div>

                    <!-- Tag Cloud -->
                    <div class="mb-5">
                        <h3 class="text-uppercase mb-4" style="letter-spacing: 5px;">Etiquetas Populares</h3>
                        <div class="d-flex flex-wrap m-n1">
                            <a href="" class="btn btn-outline-primary m-1">Diseño</a>
                            <a href="" class="btn btn-outline-primary m-1">Desarrollo</a>
                            <a href="" class="btn btn-outline-primary m-1">Marketing</a>
                            <a href="" class="btn btn-outline-primary m-1">SEO</a>
                            <a href="" class="btn btn-outline-primary m-1">Contenido</a>
                            <a href="" class="btn btn-outline-primary m-1">Consultoría</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Detail End -->


    <!-- Footer Start -->
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
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Desarrollo de Apps</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Marketing Digital</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Investigación</a>
                            <a class="text-white" href="#"><i class="fa fa-angle-right mr-2"></i>SEO</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Boletín Informativo</h5>
                <p>Mantente actualizado con las últimas tendencias en desarrollo web, tips de programación y recursos educativos. Suscríbete a nuestro boletín semanal y recibe contenido exclusivo directamente en tu bandeja de entrada.</p>
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Tu dirección de email">
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
                        <a class="nav-link text-white py-0" href="#">FAQ</a>
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
</body>

</html>