<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKILLBOOTS - Listado de Cursos</title>
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
    <link href="../assets/css/ver-cursos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<?php
require_once '../auth/auth.php';
require_once '../conexion/db.php';

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
} elseif ($pageData['userRole'] === 'profesor'){
    include '../includes/navbar-pro.php';
}else{
    include '../includes/navbar.php';
}
?>

<!-- Cabecera -->
<div class="container-fluid bg-primary py-5 mb-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 text-white font-weight-bold">Explora Nuestros Cursos</h1>
                <p class="lead text-white mb-4">Descubre un mundo de conocimiento con nuestra amplia selección de cursos diseñados para ayudarte a crecer profesionalmente.</p>
                <a href="#cursos" class="btn btn-light py-3 px-5 mt-2">Ver Todos los Cursos</a>
            </div>
            <div class="col-lg-5">
                <img class="img-fluid rounded" src="../assets/img/courses-header.jpg" alt="Cursos Online">
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <h2 class="text-primary mb-4">Cursos Disponibles</h2>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" class="form-control" id="searchCourse" placeholder="Buscar cursos...">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" id="btnSearch">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex flex-wrap">
                <div class="mr-3 mb-2">
                    <select class="form-control" id="filterCategory">
                        <option value="">Todas las categorías</option>
                        <?php
                        // Obtener categorías únicas
                        $sql = "SELECT DISTINCT categoria FROM cursos WHERE estado = 'publicado' AND estate = 'activo'";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        foreach ($categorias as $categoria) {
                            echo "<option value=\"" . htmlspecialchars($categoria) . "\">" . htmlspecialchars($categoria) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-2">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-filter="todos">Todos</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="nuevo">Nuevos</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="popular">Populares</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Cursos -->
<div class="container py-5" id="cursos">
    <div class="row" id="courseContainer">
        <?php
        // Consulta para obtener los cursos publicados
        $sql = "SELECT 
                    c.id, 
                    c.nombre, 
                    c.descripcion, 
                    c.categoria, 
                    c.imagen, 
                    c.total_lecciones, 
                    c.horas_totales,
                    c.fecha_creacion,
                    u.name as instructor_nombre,
                    (SELECT COUNT(*) FROM usuarios_cursos WHERE curso_id = c.id) as total_estudiantes,
                    (SELECT AVG(rating) FROM course_ratings WHERE curso_id = c.id) as promedio_calificacion
                FROM 
                    cursos c
                LEFT JOIN 
                    usuarios u ON c.instructor_id = u.id
                WHERE 
                    c.estado = 'publicado' 
                    AND c.estate = 'activo'
                ORDER BY 
                    c.fecha_creacion DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($cursos) > 0) {
            foreach ($cursos as $curso) {
                // Formatear la calificación
                $rating = isset($curso['promedio_calificacion']) ? number_format($curso['promedio_calificacion'], 1) : 0;
                $rating_stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= round($rating)) {
                        $rating_stars .= '<small class="fa fa-star text-warning mr-1"></small>';
                    } else {
                        $rating_stars .= '<small class="fa fa-star text-muted mr-1"></small>';
                    }
                }
                
                // Verificar si el curso es nuevo (menos de 30 días)
                $fecha_creacion = new DateTime($curso['fecha_creacion']);
                $hoy = new DateTime();
                $intervalo = $fecha_creacion->diff($hoy);
                $es_nuevo = $intervalo->days < 30;
                
                echo '
                <div class="col-lg-4 col-md-6 mb-4 curso-item" data-category="' . htmlspecialchars($curso['categoria']) . '" data-nuevo="' . ($es_nuevo ? 'true' : 'false') . '" data-popular="' . ($curso['total_estudiantes'] > 10 ? 'true' : 'false') . '">
                    <div class="card border-0 shadow-sm rounded">
                        <div class="position-relative">
                            <img class="card-img-top" src="' . htmlspecialchars($curso['imagen']) . '" alt="' . htmlspecialchars($curso['nombre']) . '" style="height: 200px; object-fit: cover;">
                            ' . ($es_nuevo ? '<div class="badge badge-primary badge-pill position-absolute" style="top: 10px; right: 10px;">NUEVO</div>' : '') . '
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <small class="bg-light px-2 py-1 rounded text-primary"><i class="fa fa-tag mr-2"></i>' . htmlspecialchars($curso['categoria']) . '</small>
                                <small class="bg-light px-2 py-1 rounded text-primary"><i class="fa fa-users mr-2"></i>' . $curso['total_estudiantes'] . ' estudiantes</small>
                            </div>
                            <h5 class="card-title">' . htmlspecialchars($curso['nombre']) . '</h5>
                            <p class="card-text text-muted mb-3">' . (strlen($curso['descripcion']) > 120 ? substr(htmlspecialchars($curso['descripcion']), 0, 120) . '...' : htmlspecialchars($curso['descripcion'])) . '</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    ' . $rating_stars . '
                                    <small class="ml-1">(' . $rating . ')</small>
                                </div>
                                <small class="text-muted"><i class="fa fa-clock mr-1"></i>' . $curso['horas_totales'] . ' horas</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between">
                            <small class="text-muted">Por: ' . htmlspecialchars($curso['instructor_nombre']) . '</small>
                            <a href="course_details.php?id=' . $curso['id'] . '" class="btn btn-sm btn-primary">Ver Detalles</a>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="col-12">
                    <div class="alert alert-info">
                        <p class="mb-0 text-center">No hay cursos disponibles en este momento.</p>
                    </div>
                  </div>';
        }
        ?>
    </div>
    
    <!-- Paginación -->
    <div class="row mt-5">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Categorías Populares -->
<div class="container-fluid py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Categorías Populares</h2>
            <p>Explora nuestras categorías más populares y encuentra el curso perfecto para ti</p>
        </div>
        <div class="row">
            <?php
            // Obtener las categorías con más cursos
            $sql = "SELECT 
                        categoria, 
                        COUNT(*) as total_cursos,
                        (SELECT imagen FROM cursos WHERE categoria = c.categoria LIMIT 1) as imagen_categoria
                    FROM 
                        cursos c
                    WHERE 
                        estado = 'publicado' 
                        AND estate = 'activo'
                    GROUP BY 
                        categoria
                    ORDER BY 
                        total_cursos DESC
                    LIMIT 6";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($categorias as $categoria) {
                echo '
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="cat-item position-relative overflow-hidden rounded mb-2">
                        <img class="img-fluid" src="' . ($categoria['imagen_categoria'] ? htmlspecialchars($categoria['imagen_categoria']) : 'img/cat-default.jpg') . '" alt="' . htmlspecialchars($categoria['categoria']) . '" style="height: 200px; width: 100%; object-fit: cover;">
                        <a class="cat-overlay text-white text-decoration-none" href="lista_cursos.php?categoria=' . urlencode($categoria['categoria']) . '">
                            <h4 class="text-white font-weight-medium">' . htmlspecialchars($categoria['categoria']) . '</h4>
                            <span>' . $categoria['total_cursos'] . ' Cursos</span>
                        </a>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Instructores Destacados -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="text-primary text-uppercase mb-3" style="letter-spacing: 5px;">Instructores Destacados</h2>
            <p>Aprende de los mejores profesionales en su campo</p>
        </div>
        <div class="row">
            <?php
            // Obtener los instructores con más cursos
            $sql = "SELECT 
                        u.id,
                        u.name,
                        u.email,
                        u.foto_perfil,
                        COUNT(c.id) as total_cursos,
                        (SELECT COUNT(*) FROM usuarios_cursos uc JOIN cursos c2 ON uc.curso_id = c2.id WHERE c2.instructor_id = u.id) as total_estudiantes
                    FROM 
                        usuarios u
                    JOIN 
                        cursos c ON u.id = c.instructor_id
                    WHERE 
                        u.rol = 'profesor'
                        AND c.estado = 'publicado'
                        AND c.estate = 'activo'
                    GROUP BY 
                        u.id
                    ORDER BY 
                        total_cursos DESC, total_estudiantes DESC
                    LIMIT 4";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($instructores as $instructor) {
                echo '
                <div class="col-md-6 col-lg-3 text-center team mb-4">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="' . (empty($instructor['foto_perfil']) ? 'img/profile-default.jpg' : htmlspecialchars($instructor['foto_perfil'])) . '" alt="' . htmlspecialchars($instructor['name']) . '" style="height: 200px; width: 100%; object-fit: cover;">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="bg-secondary p-4">
                            <h5>' . htmlspecialchars($instructor['name']) . '</h5>
                            <p class="m-0">' . $instructor['total_cursos'] . ' cursos · ' . $instructor['total_estudiantes'] . ' estudiantes</p>
                        </div>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Footer Start -->
<div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
    <div class="row pt-5">
        <div class="col-lg-7 col-md-12">
            <div class="row">
                <div class="col-md-6 mb-5">
                    <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Get In Touch</h5>
                    <p><i class="fa fa-map-marker-alt mr-2"></i>123 Street, New York, USA</p>
                    <p><i class="fa fa-phone-alt mr-2"></i>+012 345 67890</p>
                    <p><i class="fa fa-envelope mr-2"></i>info@example.com</p>
                    <div class="d-flex justify-content-start mt-4">
                        <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a class="btn btn-outline-light btn-square" href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-6 mb-5">
                    <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Our Courses</h5>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Web Design</a>
                        <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Apps Design</a>
                        <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Marketing</a>
                        <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Research</a>
                        <a class="text-white" href="#"><i class="fa fa-angle-right mr-2"></i>SEO</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-md-12 mb-5">
            <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Newsletter</h5>
            <p>Rebum labore lorem dolores kasd est, et ipsum amet et at kasd, ipsum sea tempor magna tempor. Accu kasd sed ea duo ipsum. Dolor duo eirmod sea justo no lorem est diam</p>
            <div class="w-100">
                <div class="input-group">
                    <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Your Email Address">
                    <div class="input-group-append">
                        <button class="btn btn-primary px-4">Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
    <div class="row">
        <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
            <p class="m-0 text-white">&copy; <a href="#">Domain Name</a>. All Rights Reserved. Designed by <a href="https://htmlcodex.com">HTML Codex</a>
            </p>
        </div>
        <div class="col-lg-6 text-center text-md-right">
            <ul class="nav d-inline-flex">
                <li class="nav-item">
                    <a class="nav-link text-white py-0" href="#">Privacy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white py-0" href="#">Terms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white py-0" href="#">FAQs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white py-0" href="#">Help</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- Footer End -->

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script src="../lib/easing/easing.min.js"></script>
<script src="../lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="../assets/js/main.js"></script>

<!-- Custom Javascript -->
<script>
    $(document).ready(function() {
        // Funcionalidad de búsqueda
        $("#btnSearch, #searchCourse").on("keyup click", function() {
            const searchValue = $("#searchCourse").val().toLowerCase();
            
            $(".curso-item").each(function() {
                const titulo = $(this).find(".card-title").text().toLowerCase();
                const descripcion = $(this).find(".card-text").text().toLowerCase();
                const categoria = $(this).data("category").toLowerCase();
                
                if (titulo.includes(searchValue) || descripcion.includes(searchValue) || categoria.includes(searchValue)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Filtro por categoría
        $("#filterCategory").change(function() {
            const categoriaSeleccionada = $(this).val().toLowerCase();
            
            if (categoriaSeleccionada === '') {
                $(".curso-item").show();
            } else {
                $(".curso-item").each(function() {
                    const categoriaItem = $(this).data("category").toLowerCase();
                    
                    if (categoriaItem === categoriaSeleccionada) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
        
        // Filtros de botones (Todos, Nuevos, Populares)
        $(".btn-group .btn").click(function() {
            const filtro = $(this).data("filter");
            
            // Activar/desactivar botones
            $(".btn-group .btn").removeClass("active");
            $(this).addClass("active");
            
            if (filtro === "todos") {
                $(".curso-item").show();
            } else if (filtro === "nuevo") {
                $(".curso-item").each(function() {
                    if ($(this).data("nuevo") === "true") {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else if (filtro === "popular") {
                $(".curso-item").each(function() {
                    if ($(this).data("popular") === "true") {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
        
        // Efecto hover en las tarjetas de cursos
        $(".curso-item .card").hover(
            function() {
                $(this).addClass("shadow");
                $(this).css("transform", "translateY(-5px)");
                $(this).css("transition", "all 0.3s ease");
            },
            function() {
                $(this).removeClass("shadow");
                $(this).css("transform", "translateY(0)");
            }
        );
    });
</script>

</body>
</html>