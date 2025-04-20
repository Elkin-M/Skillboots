<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKILLBOOTS - Dashboard Profesor</title>
    <link href="./css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<?php
require_once './roles/auth.php';
require_once './conexion/db.php';

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
if ($isLoggedIn) {
    include 'navbar-pro.php';
} else {
    include 'navbar.php';
}
?>

<!-- Carousel Start -->
<?php
// Definir imágenes y textos por defecto
$carouselData = [
    [
        "image" => "img/carousel-1.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "La Mejor Educación Desde Casa"
    ],
    [
        "image" => "img/carousel-2.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "La Mejor Plataforma De Aprendizaje En Línea"
    ],
    [
        "image" => "img/carousel-3.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "Nueva Forma De Aprender Desde Casa"
    ]
];

// Verificar el rol del usuario y asignar nuevas imágenes y textos
if ($pageData['isLoggedIn']) {
    if ($pageData['userRole'] === 'profesor') {
        $carouselData = [
            [
                'image' => './img/student-dashboard-1.jpg',
                'title' => 'Bienvenido a su Panel Docente',
                'subtitle' => 'Gestione sus cursos de manera eficiente'
            ],
            [
                'image' => './img/student-dashboard-2.jpg',
                'title' => 'Seguimiento de Estudiantes',
                'subtitle' => 'Consulte el progreso y asistencia en tiempo real'
            ],
            [
                'image' => './img/student-dashboard-3.jpg',
                'title' => 'Material Didáctico',
                'subtitle' => 'Comparta recursos y actividades interactivas'
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
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" style="min-height: 300px;">
                    <img class="position-relative w-100" src="<?php echo $slide['image']; ?>" style="min-height: 300px; object-fit: cover;">
                    <div class="carousel-caption d-flex align-items-center justify-content-center">
                        <div class="p-4" style="width: 100%; max-width: 900px;  border-radius: 15px;">
                            <h5 class="text-white text-uppercase mb-md-3" style="letter-spacing: 2px; font-weight: 600; text-align:center;"><?php echo $slide['title']; ?></h5>
                            <div class="d-flex flex-column flex-md-row align-items-center justify-content-center">
                                <h1 class="display-4 text-white mb-3 mb-md-0 mr-md-4" style="font-weight: 700;"><?php echo $slide['subtitle']; ?></h1>
                                <a href="#" class="btn py-md-3 px-md-5 font-weight-bold" style="background: linear-gradient(to right, #e67e22, #d35400); color: white; border: none; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: all 0.3s;"
                                    onclick="window.location.href='./crear_curso.php'; return false;">
                                    Crear Nuevo Curso <i class="fas fa-plus ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Carousel End -->

<!-- Panel de estadísticas -->
<?php
$instructor_id = $_SESSION['user_id'];

// Consulta para obtener estadísticas
$stats = [
    'total_students' => 0,
    'avg_rating' => 0,
    'active_courses' => 0,
    'upcoming_classes' => 0
];

// Total de estudiantes
$sql = "SELECT
            COUNT(DISTINCT uc.usuario_id) AS total_students
        FROM
            usuarios_cursos uc
        JOIN
            cursos c ON uc.curso_id = c.id
        WHERE
            c.instructor_id = :instructor_id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $stats['total_students'] = $row['total_students'];
}

// Promedio de calificaciones
$sql = "SELECT
            AVG(r.rating) as avg_rating
        FROM
            course_ratings r
        JOIN
            cursos c ON r.curso_id = c.id
        WHERE
            c.instructor_id = :instructor_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $stats['avg_rating'] = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
}

// Cursos activos
$sql = "SELECT
            COUNT(*) as total
        FROM
            cursos
        WHERE
            instructor_id = :instructor_id AND estado = 'publicado'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $stats['active_courses'] = $row['total'];
}

// Próximas clases
$sql = "SELECT
            COUNT(*) as total
        FROM
            class_sessions
        WHERE
            instructor_id = :instructor_id AND start_time > NOW()
        ORDER BY
            start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $stats['upcoming_classes'] = $row['total'];
}

// obtener la próxima clase dinámicamente
$sql = "SELECT
            DATE_FORMAT(start_time, '%d %b, %H:%i') as formatted_time
        FROM
            class_sessions
        WHERE
            instructor_id = :instructor_id
            AND start_time > NOW()
        ORDER BY
            start_time ASC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$next_class = $stmt->fetch(PDO::FETCH_ASSOC);
$next_class_time = $next_class ? $next_class['formatted_time'] : 'No hay clases programadas';
?>

<!-- Panel de estadísticas -->
<div class="container mb-5">
    <div class="row stats-panel" style="margin: 0 -15px; display: flex; align-items: center; justify-content:space-around; overflow-x: auto; padding-bottom: 15px;">
        <div class="col-card" style="flex: 0 0 250px; padding: 0 10px; margin-bottom: 15px;">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 25px 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.15), 0 5px 8px rgba(0,0,0,0.12); height: 100%; transition: all 0.3s; border: 1px solid rgba(230, 126, 34, 0.1);">
                <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Estudiantes Totales</p>
                <h2 style="font-size: 40px; font-weight: 700; color: #e67e22; margin-bottom: 10px;"><?php echo $stats['total_students']; ?></h2>
                <div style="width: 50px; height: 50px; background: rgba(230, 126, 34, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-users" style="color: #e67e22; font-size: 22px;"></i>
                </div>
            </div>
        </div>
        <div class="col-card" style="flex: 0 0 250px; padding: 0 10px; margin-bottom: 15px;">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 25px 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.15), 0 5px 8px rgba(0,0,0,0.12); height: 100%; transition: all 0.3s; border: 1px solid rgba(230, 126, 34, 0.1);">
            <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Promedio Calificaciones</p>
            <h2 style="font-size: 40px; font-weight: 700; color: #e67e22; margin-bottom: 10px;"><?php echo $stats['avg_rating']; ?></h2>
            <div style="width: 50px; height: 50px; background: rgba(230, 126, 34, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-chart-bar" style="color: #e67e22; font-size: 22px;"></i>
                </div>
            </div>
        </div>
        <div class="col-card" style="flex: 0 0 250px; padding: 0 10px; margin-bottom: 15px;">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 25px 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.15), 0 5px 8px rgba(0,0,0,0.12); height: 100%; transition: all 0.3s; border: 1px solid rgba(230, 126, 34, 0.1);">
            <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Cursos Activos</p>
            <h2 style="font-size: 40px; font-weight: 700; color: #e67e22; margin-bottom: 10px;"><?php echo $stats['active_courses']; ?></h2>
            <div style="width: 50px; height: 50px; background: rgba(230, 126, 34, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-book" style="color: #e67e22; font-size: 22px;"></i>
                </div>
            </div>
        </div>
        <div class="col-card" style="flex: 0 0 250px; padding: 0 10px; margin-bottom: 15px;">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 25px 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.15), 0 5px 8px rgba(0,0,0,0.12); height: 100%; transition: all 0.3s; border: 1px solid rgba(230, 126, 34, 0.1);">
                <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Próximas Clases</p>
                <h2 style="font-size: 40px; font-weight: 700; color: #e67e22; margin-bottom: 10px;"><?php echo $stats['upcoming_classes']; ?></h2>
            <div style="width: 50px; height: 50px; background: rgba(230, 126, 34, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-calendar-alt" style="color: #e67e22; font-size: 22px;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- sesion de acciones rapidas -->
<div class="container-fluid py-5" style="padding-top: 0% !important;">
    <div class="container py-5" style="padding-top: 0% !important;">
        <div class="row">
            <!-- Panel de acciones rápidas -->
            <div class="col-lg-4 mb-4">
                <div class="bg-secondary rounded p-4 h-100">
                    <h4 class="text-primary text-uppercase mb-4" style="letter-spacing: 3px;">Acciones Rápidas</h4>

                    <div class="mb-4">
                        <a href="#" class="btn btn-primary btn-block py-3 mb-3" data-accion="revisarTareas">
                            <i class="fa fa-pencil-alt mr-2"></i> Revisar Tareas (12)
                        </a>
                        <a href="#" class="btn btn-primary btn-block py-3 mb-3" data-accion="mensajes">
                            <i class="fa fa-comments mr-2"></i> Mensajes (3)
                        </a>
                        <a href="#" class="btn btn-primary btn-block py-3 mb-3" data-accion="programarSesion">
                            <i class="fa fa-calendar mr-2"></i> Programar Sesión
                        </a>
                        <a href="#" class="btn btn-primary btn-block py-3 mb-3" data-accion="subirMaterial">
                            <i class="fa fa-upload mr-2"></i> Subir Material
                        </a>

                    </div>

                    <!-- Mini calendario -->
                    <div class="border-top pt-4">
                        <h5 class="text-primary mb-3"><?php echo strtoupper(date('F Y')); ?></h5>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span><i class="fa fa-clock text-primary mr-2"></i> Próxima clase:</span>
                            <span class="font-weight-bold"><?php echo $next_class_time; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de cursos -->
            <div class="col-lg-8 mb-4">
                <div class="bg-secondary rounded p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-primary text-uppercase" style="letter-spacing: 3px;">Mis Cursos Activos</h4>
                        <div>
                            <button class="btn btn-sm btn-outline-primary px-3 mr-1 rounded-pill filter-btn" data-filter="todos">Todos</button>
                            <button class="btn btn-sm btn-outline-primary px-3 mr-1 rounded-pill filter-btn" data-filter="publicado">Activos</button>
                            <button class="btn btn-sm btn-outline-primary px-3 mr-1 rounded-pill filter-btn" data-filter="en_progreso">En Progreso</button>
                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill filter-btn" data-filter="finalizado">Finalizados</button>
                        </div>
                    </div>

                    <?php
// Consulta para obtener los cursos del instructor
$sql = "SELECT
            c.id,
            c.nombre,
            c.descripcion,
            c.estado,
            COUNT(uc.usuario_id) as student_count
        FROM
            cursos c
        LEFT JOIN
            usuarios_cursos uc ON c.id = uc.curso_id
        WHERE
            c.instructor_id = :instructor_id and c.estate != 'eliminado'
        GROUP BY
            c.id
        ORDER BY
            c.created_at DESC";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay cursos
if (count($result) > 0) {
    // Mostrar los cursos
    foreach ($result as $course) {
        // Determinar el estado del curso con su ícono
        $status_icon = '';
        $status_text = '';

        switch ($course['estado']) {
            case 'publicado':
                $status_icon = '<i class="fa fa-circle text-success mr-1"></i>';
                $status_text = 'Activo';
                break;
            case 'en_progreso':
                $status_icon = '<i class="fa fa-circle text-warning mr-1"></i>';
                $status_text = 'En progreso';
                break;
            case 'finalizado':
                $status_icon = '<i class="fa fa-circle text-secondary mr-1"></i>';
                $status_text = 'Finalizado';
                break;
        }

        echo '
        <div class="bg-light rounded p-4 mb-3 curso-item" data-id="'.$course['id'].'" data-status="'.$course['estado'].'">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h5 class="mb-1">'.htmlspecialchars($course['nombre']).'</h5>
                    <p class="mb-2 text-muted">'.htmlspecialchars($course['descripcion']).'</p>
                    <div class="d-flex">
                        <div class="mr-3">
                            <small>'.$status_icon.$status_text.'</small>
                        </div>
                        <div>
                            <small><i class="fa fa-users text-primary mr-2"></i>'.$course['student_count'].' estudiantes</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <a href="./Acciones/view_courses.php?id='.$course['id'].'" class="btn btn-sm btn-outline-primary mr-1">Ver</a>
                    <a href="./Acciones/edit_course.php?id='.$course['id'].'" class="btn btn-sm btn-outline-primary mr-1">Editar</a>
                    <a href="#" class="btn btn-sm btn-outline-danger delete-course" data-id="'.$course['id'].'" data-nombre="'.htmlspecialchars($course['nombre']).'">Eliminar</a>
                    <a href="#" class="btn btn-sm btn-outline-info" onclick="mostrarPersonasInscritas('.$course['id'].', \''.htmlspecialchars($course['nombre']).'\')">Inscritos</a>
                </div>
            </div>
        </div>';
    }
} else {
    // No hay cursos
    echo '<div class="bg-light rounded p-4 text-center">
    <p class="mb-0">No tienes cursos activos actualmente.</p>
    <button id="crearCursoBtn" class="btn btn-primary mt-3">
        <i class="fas fa-plus mr-2"></i> Crear tu primer curso
    </button>
</div>';
}
?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('crearCursoBtn').addEventListener('click', function() {
        window.location.href = 'crear_curso.php#crearCurso';
    });
});
</script>


                </div>

        <!-- Modal para confirmar eliminación -->
        <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmarEliminarModalLabel">Confirmar eliminación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#confirmarEliminarModal').modal('hide');">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar el curso <strong id="curso-nombre"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"  onclick="$('#confirmarEliminarModal').modal('hide');">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="btn-eliminar">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Inscritos -->
        <div class="modal fade" id="modalInscritos" tabindex="-1" role="dialog" aria-labelledby="modalInscritosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalInscritosLabel">Personas inscritas en <span id="cursoNombre"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalInscritos').modal('hide');">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Fecha de inscripción</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="listaInscritos">
                      <!-- Aquí se cargarán los inscritos dinámicamente -->
                    </tbody>
                  </table>
                </div>
                <div id="sinInscritos" class="alert alert-info text-center d-none">
                  No hay personas inscritas en este curso todavía.
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#modalInscritos').modal('hide');">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
    </div>
</div>

<!-- fin de sesion de acciones rapidas -->
 <!-- Modal  de acciones rapidas-->
<div class="modal fade" id="accionesModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#courseDetailModal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#courseDetailModal').modal('hide');">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del curso -->
<div class="modal fade" id="courseDetailModal" tabindex="-1" aria-labelledby="courseDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="courseDetailModalLabel">Detalles del curso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#courseDetailModal').modal('hide');">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="courseDetailContent">
          <!-- El contenido dinámico se cargará aquí -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#courseDetailModal').modal('hide');">Cerrar</button>
      </div>
    </div>
  </div>
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
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="mail/jqBootstrapValidation.min.js"></script>
    <script src="mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
// Datos de ejemplo - en una aplicación real, esto vendría de la base de datos

document.addEventListener("DOMContentLoaded", function() {
    const acciones = {
        "revisarTareas": {
            titulo: "Revisión de Tareas",
            contenido: "Tienes 12 tareas pendientes para revisar."
        },
        "mensajes": {
            titulo: "Mensajes Recibidos",
            contenido: "Tienes 3 mensajes nuevos en tu bandeja de entrada."
        },
        "programarSesion": {
            titulo: "Programar Sesión",
            contenido: "Seleccione una fecha y hora para programar una nueva sesión."
        },
        "subirMaterial": {
            titulo: "Subir Nuevo Material",
            contenido: "Adjunte los archivos y presione subir para compartirlos."
        }
    };

    document.querySelectorAll(".btn").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();

            let accion = this.getAttribute("data-accion");
            if (!acciones[accion]) return;

            document.getElementById("modalTitle").textContent = acciones[accion].titulo;
            document.getElementById("modalContent").textContent = acciones[accion].contenido;

            let modal = new bootstrap.Modal(document.getElementById("accionesModal"));
            modal.show();
        });
    });
});

</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar filtros de cursos
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Cambiar estado activo de los botones
            filterButtons.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });

            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');

            // Obtener el filtro seleccionado
            const filter = this.getAttribute('data-filter');

            // Filtrar cursos
            const cursoItems = document.querySelectorAll('.curso-item');

            cursoItems.forEach(item => {
                const itemStatus = item.getAttribute('data-status');

                if (filter === 'todos') {
                    item.style.display = 'block';
                } else if (itemStatus === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Establecer "Todos" como filtro predeterminado
    document.querySelector('.filter-btn[data-filter="todos"]').click();
});

// Función para confirmar eliminación
function confirmarEliminar(id, nombre) {
    console.log('Confirmar eliminar:', id, nombre);
    // Establecer el nombre del curso en el modal
    document.getElementById('curso-nombre').textContent = nombre;

    // Configurar el botón de eliminar
    const btnEliminar = document.getElementById('btn-eliminar');
    btnEliminar.onclick = function() {
        eliminarCurso(id);
    };

    // Mostrar el modal
    $('#confirmarEliminarModal').modal('show');
}

// Función para eliminar curso
function eliminarCurso(id) {
    if (!id || isNaN(id)) {
        alert('ID de curso no válido');
        return;
    }
    
    console.log('Eliminar curso ID:', id);
    
    // Crear el objeto FormData para enviar datos
    const formData = new FormData();
    
    // Agregar token CSRF si existe en la página
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    
    // Realizar la solicitud
    fetch('./Acciones/delete_course.php?id=' + id, {
        method: 'GET', // Cambiado a GET para coincidir con el backend
        credentials: 'same-origin' // Incluir cookies en la solicitud
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta:', data);
        
        // Cerrar el modal si existe
        const modal = $('#confirmarEliminarModal');
        if (modal.length) {
            modal.modal('hide');
        }

        if (data.success) {
            // Eliminar el elemento del DOM
            const cursoElement = document.querySelector(`.curso-item[data-id="${id}"]`);
            if (cursoElement) {
                cursoElement.remove();
            } else {
                // Si no se encuentra el elemento, recargar la página
                window.location.reload();
            }

            // Mostrar mensaje de éxito
            alert('Curso marcado para eliminación. Se eliminará permanentemente en 30 días.');
        } else {
            // Mostrar mensaje de error
            alert('Error al eliminar el curso: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al conectar con el servidor: ' + error.message);
        
        // Cerrar el modal en caso de error
        const modal = $('#confirmarEliminarModal');
        if (modal.length) {
            modal.modal('hide');
        }
    });
}

// Función para mostrar personas inscritas
function mostrarPersonasInscritas(cursoId, cursoNombre) {
    console.log('Mostrar personas inscritas:', cursoId, cursoNombre);
    // Actualizar el título del modal con el nombre del curso
    $('#cursoNombre').text(cursoNombre);

    // Mostrar un indicador de carga
    $('#listaInscritos').html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div></td></tr>');

    // Hacer la petición AJAX para obtener los inscritos
    $.ajax({
    url: './Acciones/get_inscritos.php',
    type: 'GET',
    data: { curso_id: cursoId },
    dataType: 'json',
    success: function(response) {
        $('#listaInscritos').empty();

        // Verificar si la respuesta fue exitosa
        if (response.success && response.inscritos.length > 0) {
            $('#sinInscritos').addClass('d-none');

            // Recorrer el array correctamente
            $.each(response.inscritos, function(index, inscrito) {
                $('#listaInscritos').append(`
                    <tr>
                        <td>${inscrito.nombre}</td>
                        <td>${inscrito.email}</td>
                        <td>${inscrito.fecha_inscripcion}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarInscrito(${index}, ${cursoId})">
                                <i class="fas fa-user-minus"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            // Si no hay inscritos, mostrar el mensaje correspondiente
            $('#sinInscritos').removeClass('d-none');
        }
    },
    error: function() {
        $('#listaInscritos').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los inscritos. Intenta nuevamente.</td></tr>');
    }
});


    // Mostrar el modal
    $('#modalInscritos').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-course').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            console.log('Eliminar curso:', id, nombre);
            confirmarEliminar(id, nombre);
        });
    });
});

</script>

<style>
/* Estilos adicionales para el modal de inscritos */
#modalInscritos .modal-header {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border-bottom: 0;
    padding: 1.25rem;
}

#modalInscritos .modal-title {
    font-weight: 600;
    letter-spacing: 0.5px;
}

#modalInscritos .modal-body {
    padding: 1.5rem;
}

#modalInscritos .table th {
    font-weight: 600;
    background-color: #f8f9fc;
}

#modalInscritos .table-hover tbody tr:hover {
    background-color: #f2f7ff;
}

#modalInscritos .close:hover {
    color: #fff;
    opacity: 1;
}

#modalInscritos .btn-outline-danger:hover {
    color: #fff;
}
</style>
</body>
</html>
