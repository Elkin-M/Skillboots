<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sistema de alertas
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$error_type = isset($_SESSION['error_type']) ? $_SESSION['error_type'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Limpiar mensajes
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
if (isset($_SESSION['error_type'])) unset($_SESSION['error_type']);
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<style>
    .schedule-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.schedule-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="1.5" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1" fill="white" opacity="0.1"/></svg>');
    pointer-events: none;
}

.month-header {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.month-title {
    color: white;
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    letter-spacing: 1px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 20px;
    padding: 10px 0;
}

.section-header i {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px;
    border-radius: 10px;
    font-size: 1rem;
}

.classes-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.class-item {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.class-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent 30%, rgba(102, 126, 234, 0.05) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.class-item:hover::before {
    transform: translateX(100%);
}

.class-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    border-left-color: #764ba2;
}

.class-time {
    color: #2d3748;
    font-weight: 700;
    font-size: 1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.class-time i {
    color: #667eea;
    font-size: 0.9rem;
}

.no-classes {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.no-classes-icon {
    font-size: 3rem;
    color: #a0aec0;
    margin-bottom: 15px;
    opacity: 0.7;
}

.no-classes-text {
    color: #4a5568;
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0;
}

.no-classes-subtitle {
    color: #718096;
    font-size: 0.9rem;
    margin-top: 8px;
    margin-bottom: 0;
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.class-item {
    animation: fadeInUp 0.5s ease forwards;
}

.class-item:nth-child(1) { animation-delay: 0.1s; }
.class-item:nth-child(2) { animation-delay: 0.2s; }
.class-item:nth-child(3) { animation-delay: 0.3s; }
.class-item:nth-child(4) { animation-delay: 0.4s; }
.class-item:nth-child(5) { animation-delay: 0.5s; }

/* Responsive */
@media (max-width: 768px) {
    .schedule-container {
        padding: 20px 15px;
        border-radius: 15px;
    }
    
    .month-title {
        font-size: 1.2rem;
    }
    
    .section-header {
        font-size: 1rem;
    }
    
    .class-item {
        padding: 14px 16px;
    }
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
<?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKILLBOOTS - Dashboard Profesor</title>
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
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
if ($isLoggedIn) {
    include '../includes/navbar-pro.php';
} else {
    include '../includes/navbar.php';
}
?>

<!-- Carousel Start -->
<?php
// Definir imágenes y textos por defecto
$carouselData = [
    [
        "image" => "../assets/img/carousel-1.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "La Mejor Educación Desde Casa"
    ],
    [
        "image" => "../assets/img/carousel-2.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "La Mejor Plataforma De Aprendizaje En Línea"
    ],
    [
        "image" => "../assets/img/carousel-3.jpg",
        "title" => "Los Mejores Cursos en Línea",
        "subtitle" => "Nueva Forma De Aprender Desde Casa"
    ]
];

// Verificar el rol del usuario y asignar nuevas imágenes y textos
if ($pageData['isLoggedIn']) {
    if ($pageData['userRole'] === 'profesor') {
        $carouselData = [
            [
                'image' => '../assets/img/student-dashboard-1.jpg',
                'title' => 'Bienvenido a su Panel Docente',
                'subtitle' => 'Gestione sus cursos de manera eficiente'
            ],
            [
                'image' => '../assets/img/student-dashboard-2.jpg',
                'title' => 'Seguimiento de Estudiantes',
                'subtitle' => 'Consulte el progreso y asistencia en tiempo real'
            ],
            [
                'image' => '../assets/img/student-dashboard-3.jpg',
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
                    
                    <img class="position-relative w-100" src="<?php echo $slide['image']; ?>" style="z-index:-10;  min-height: 300px; object-fit: cover;">
                    <div class="carousel-caption d-flex align-items-center justify-content-center">
                        <div class="p-4" style="width: 100%; max-width: 900px;  border-radius: 15px;">
                            <h5 class="text-white text-uppercase mb-md-3" style="letter-spacing: 2px; font-weight: 600; text-align:center;"><?php echo $slide['title']; ?></h5>
                            <div class="d-flex flex-column flex-md-row align-items-center justify-content-center">
                                <h1 class="display-4 text-white mb-3 mb-md-0 mr-md-4" style="font-weight: 700;"><?php echo $slide['subtitle']; ?></h1>
                                <a href="#" class="btn py-md-3 px-md-5 font-weight-bold" style="background: linear-gradient(to right, #e67e22, #d35400); color: white; border: none; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: all 0.3s;"
                                    onclick="window.location.href='../courses/crear_curso.php'; return false;">
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
            instructor_id = :instructor_id 
            AND start_time > NOW()";

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
        LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':instructor_id', $instructor_id, PDO::PARAM_INT);
$stmt->execute();
$next_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <div class="schedule-container">
    <div class="month-header">
        <h5 class="month-title">
            <?php 
            // Formatear el mes en español
            $meses = [
                'January' => 'ENERO', 'February' => 'FEBRERO', 'March' => 'MARZO',
                'April' => 'ABRIL', 'May' => 'MAYO', 'June' => 'JUNIO',
                'July' => 'JULIO', 'August' => 'AGOSTO', 'September' => 'SEPTIEMBRE',
                'October' => 'OCTUBRE', 'November' => 'NOVIEMBRE', 'December' => 'DICIEMBRE'
            ];
            $mes_ingles = date('F');
            $año = date('Y');
            echo $meses[$mes_ingles] . ' ' . $año;
            ?>
        </h5>
    </div>
    
    <div class="section-header">
        <i class="fas fa-calendar-alt"></i>
        <span>Próximas clases</span>
    </div>

    <?php if (count($next_classes) > 0): ?>
        <ul class="classes-list">
            <?php foreach ($next_classes as $index => $class): ?>
                <li class="class-item" style="animation-delay: <?php echo ($index + 1) * 0.1; ?>s;">
                    <p class="class-time">
                        <i class="fas fa-clock"></i>
                        <?php echo $class['formatted_time']; ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="no-classes">
            <div class="no-classes-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <p class="no-classes-text">No hay clases programadas</p>
            <p class="no-classes-subtitle">Disfruta tu tiempo libre</p>
        </div>
    <?php endif; ?>
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
            c.instructor_id = :instructor_id and c.estado != 'eliminado'
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

        // Debug: Verificar que el ID del curso existe
        $course_id = $course['id'];
        echo '
        <div class="bg-light rounded p-4 mb-3 curso-item" data-id="'.$course_id.'" data-status="'.$course['estado'].'">
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
                    <!-- Debug: Ver el HTML generado -->
                    <a href="javascript:void(0)" onclick="window.location.href=\'course_details.php?id='.$course_id.'\'" class="btn btn-sm btn-outline-primary mr-1">Ver</a>
                    <a href="javascript:void(0)" onclick="window.location.href=\'../courses/edit_course.php?id='.$course_id.'\'" class="btn btn-sm btn-outline-primary mr-1">Editar</a>
                    <a href="#" class="btn btn-sm btn-outline-danger delete-course" data-id="'.$course_id.'" data-nombre="'.htmlspecialchars($course['nombre']).'">Eliminar</a>
                    <a href="#" class="btn btn-sm btn-outline-info" onclick="mostrarPersonasInscritas('.$course_id.', \''.htmlspecialchars($course['nombre']).'\')">Inscritos</a>
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

<!-- Script para depurar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que los enlaces se generen correctamente
    const verButtons = document.querySelectorAll('a[href*="course_details.php"]');
    const editButtons = document.querySelectorAll('a[href*="edit_course.php"]');
    
    console.log('Botones Ver encontrados:', verButtons.length);
    console.log('Botones Editar encontrados:', editButtons.length);
    
    verButtons.forEach((btn, index) => {
        console.log(`Botón Ver ${index + 1} - href:`, btn.href);
    });
    
    editButtons.forEach((btn, index) => {
        console.log(`Botón Editar ${index + 1} - href:`, btn.href);
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('crearCursoBtn').addEventListener('click', function() {
        window.location.href = '../courses/crear_curso.php#crearCurso';
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
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="../mail/jqBootstrapValidation.min.js"></script>
    <script src="../mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="../assets/js/main.js"></script>
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
    fetch('../courses/delete_course.php?id=' + id, {
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
    url: '../courses/get_inscritos.php',
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
</script>
<script>
// ============================================================================
// FUNCIONALIDADES DASHBOARD PROFESOR - VERSIÓN CORREGIDA
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Inicializar filtros de cursos
    initializeCourseFilters();
    
    // Inicializar acciones rápidas
    initializeQuickActions();
    
    // Inicializar eliminación de cursos
    initializeCourseActions();
    
    // NUEVO: Configurar eventos de cierre de modal
    initializeModalEvents();
}

// ============================================================================
// CONFIGURACIÓN DE EVENTOS DE MODAL - NUEVO
// ============================================================================

function initializeModalEvents() {
    // Asegurar que el modal se cierre correctamente
    $('#accionesModal').on('hidden.bs.modal', function () {
        // Remover backdrop manualmente si persiste
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        
        // Limpiar contenido del modal
        document.getElementById('modalContent').innerHTML = '';
    });
    
    // Manejar botón de cerrar
    $(document).on('click', '[data-dismiss="modal"]', function() {
        $('#accionesModal').modal('hide');
    });
}

// ============================================================================
// FUNCIÓN AUXILIAR PARA CERRAR MODAL CORRECTAMENTE - NUEVA
// ============================================================================

function cerrarModal() {
    $('#accionesModal').modal('hide');
    
    // Timeout para asegurar que se remueva el backdrop
    setTimeout(function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
    }, 300);
}

// ============================================================================
// FILTROS DE CURSOS (sin cambios)
// ============================================================================

function initializeCourseFilters() {
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
            filterCourses(filter);
        });
    });

    // Establecer "Todos" como filtro predeterminado
    document.querySelector('.filter-btn[data-filter="todos"]')?.click();
}

function filterCourses(filter) {
    const cursoItems = document.querySelectorAll('.curso-item');
    let visibleCount = 0;

    cursoItems.forEach(item => {
        const itemStatus = item.getAttribute('data-status');

        if (filter === 'todos') {
            item.style.display = 'block';
            visibleCount++;
        } else if (itemStatus === filter) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Mostrar mensaje si no hay cursos para el filtro seleccionado
    updateEmptyState(visibleCount, filter);
}

function updateEmptyState(visibleCount, filter) {
    let emptyMessage = document.querySelector('.empty-courses-message');
    
    if (visibleCount === 0) {
        if (!emptyMessage) {
            emptyMessage = document.createElement('div');
            emptyMessage.className = 'bg-light rounded p-4 text-center empty-courses-message';
            document.querySelector('.col-lg-8 .bg-secondary').appendChild(emptyMessage);
        }
        
        const filterMessages = {
            'todos': 'No tienes cursos creados',
            'publicado': 'No tienes cursos activos',
            'en_progreso': 'No tienes cursos en progreso',
            'finalizado': 'No tienes cursos finalizados'
        };
        
        emptyMessage.innerHTML = `
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="mb-3">${filterMessages[filter] || 'No hay cursos para mostrar'}</p>
            <button class="btn btn-primary" onclick="window.location.href='../courses/crear_curso.php'">
                <i class="fas fa-plus mr-2"></i> Crear nuevo curso
            </button>
        `;
        emptyMessage.style.display = 'block';
    } else {
        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    }
}

// ============================================================================
// ACCIONES RÁPIDAS (sin cambios)
// ============================================================================

function initializeQuickActions() {
    // Revisar Tareas
    document.querySelector('[data-accion="revisarTareas"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarRevisarTareas();
    });

    // Mensajes
    document.querySelector('[data-accion="mensajes"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarMensajes();
    });

    // Programar Sesión
    document.querySelector('[data-accion="programarSesion"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarProgramarSesion();
    });

    // Subir Material
    document.querySelector('[data-accion="subirMaterial"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarSubirMaterial();
    });
}

// ============================================================================
// REVISAR TAREAS (sin cambios significativos)
// ============================================================================

function mostrarRevisarTareas() {
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = 'Revisar Tareas Pendientes';
    modalContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    $('#accionesModal').modal('show');
    
    // Cargar tareas pendientes
    fetch('./dashboard/get_pending_tasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPendingTasks(data.tasks);
            } else {
                modalContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No hay tareas pendientes para revisar
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Error al cargar las tareas pendientes
                </div>
            `;
        });
}

function renderPendingTasks(tasks) {
    const modalContent = document.getElementById('modalContent');
    
    if (tasks.length === 0) {
        modalContent.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>
                ¡Excelente! No tienes tareas pendientes por revisar
            </div>
        `;
        return;
    }

    let html = `
        <div class="mb-3">
            <h6>Tienes ${tasks.length} tarea(s) pendiente(s) por revisar:</h6>
        </div>
    `;

    tasks.forEach(task => {
        html += `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title">${task.titulo}</h6>
                            <p class="text-muted mb-1">
                                <i class="fas fa-book mr-1"></i>
                                Curso: ${task.curso_nombre}
                            </p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-user mr-1"></i>
                                Estudiante: ${task.estudiante_nombre}
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                Entregado: ${task.fecha_entrega}
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-primary" onclick="revisarTarea(${task.id})">
                                <i class="fas fa-eye mr-1"></i>
                                Revisar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    modalContent.innerHTML = html;
}

function revisarTarea(taskId) {
    window.open(`../tasks/review_task.php?id=${taskId}`, '_blank');
}

// ============================================================================
// MENSAJES (sin cambios significativos)
// ============================================================================

function mostrarMensajes() {
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = 'Mensajes Recientes';
    modalContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    $('#accionesModal').modal('show');
    
    // Cargar mensajes recientes
    fetch('./dashboard/get_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMessages(data.messages);
            } else {
                modalContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-inbox mr-2"></i>
                        No tienes mensajes nuevos
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Error al cargar los mensajes
                </div>
            `;
        });
}

function renderMessages(messages) {
    const modalContent = document.getElementById('modalContent');
    
    if (messages.length === 0) {
        modalContent.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-inbox mr-2"></i>
                No tienes mensajes nuevos
            </div>
        `;
        return;
    }

    let html = `
        <div class="mb-3">
            <h6>Tienes ${messages.length} mensaje(s) nuevo(s):</h6>
        </div>
    `;

    messages.forEach(message => {
        html += `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="card-title">${message.asunto || 'Sin asunto'}</h6>
                            <p class="text-muted mb-1">
                                <i class="fas fa-user mr-1"></i>
                                De: ${message.remitente_nombre}
                            </p>
                            <p class="card-text">${message.mensaje.substring(0, 100)}${message.mensaje.length > 100 ? '...' : ''}</p>
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                ${message.fecha_envio}
                            </small>
                        </div>
                        <div class="ml-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="leerMensaje(${message.id})">
                                <i class="fas fa-envelope-open mr-1"></i>
                                Leer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += `
        <div class="text-center mt-3">
            <a href="../messages/inbox.php" class="btn btn-primary">
                <i class="fas fa-inbox mr-2"></i>
                Ver todos los mensajes
            </a>
        </div>
    `;

    modalContent.innerHTML = html;
}

function leerMensaje(messageId) {
    window.open(`../messages/view_message.php?id=${messageId}`, '_blank');
}

// ============================================================================
// PROGRAMAR SESIÓN - CORREGIDO
// ============================================================================

function mostrarProgramarSesion() {
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = 'Programar Nueva Sesión';
    
    modalContent.innerHTML = `
        <form id="programarSesionForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tituloSesion">Título de la Sesión</label>
                        <input type="text" class="form-control" id="tituloSesion" name="titulo" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cursoSesion">Curso</label>
                        <select class="form-control" id="cursoSesion" name="curso_id" required>
                            <option value="">Seleccionar curso...</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="descripcionSesion">Descripción</label>
                <textarea class="form-control" id="descripcionSesion" name="descripcion" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fechaSesion">Fecha</label>
                        <input type="date" class="form-control" id="fechaSesion" name="fecha" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="horaSesion">Hora</label>
                        <input type="time" class="form-control" id="horaSesion" name="hora" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="zoomLink">Enlace de Zoom (opcional)</label>
                <input type="url" class="form-control" id="zoomLink" name="zoom_link" placeholder="https://zoom.us/j/...">
            </div>
            
            <div class="form-group text-right">
                <button type="button" class="btn btn-secondary mr-2" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Programar Sesión
                </button>
            </div>
        </form>
    `;
    
    $('#accionesModal').modal('show');
    
    // Cargar cursos del instructor
    cargarCursosInstructor();
    
    // Configurar fecha mínima (hoy)
    document.getElementById('fechaSesion').min = new Date().toISOString().split('T')[0];
    
    // Manejar envío del formulario
    document.getElementById('programarSesionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        programarNuevaSesion(this);
    });
}

function cargarCursosInstructor() {
    fetch('/skillboots/templates/dashboard/get_instructor_courses.php')
    .then(response => response.json())
        .then(data => {
            console.log('Respuesta del servidor:', data);
            const select = document.getElementById('cursoSesion');
            console.log('Select encontrado:', select);
            if (data.success && data.courses.length > 0) {
                data.courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = course.nombre;
                    select.appendChild(option);
                    console.log('Añadiendo curso:', course.nombre);
                });
            } else {
                console.warn('No hay cursos disponibles o success es false');
            }
        })
        .catch(error => {
            console.error('Error al cargar cursos:', error);
        });
}

function programarNuevaSesion(form) {
    const formData = new FormData(form);

    const fecha = formData.get('fecha');
    const hora = formData.get('hora');
    const fechaHora = `${fecha} ${hora}:00`;

    formData.set('start_time', fechaHora);

    console.log('📤 Enviando datos al servidor:');
    for (const [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    fetch('dashboard/schedule_session.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        console.log('📄 Respuesta del servidor (raw):', text);
        try {
            const data = JSON.parse(text);
            console.log('✅ JSON parseado:', data);

            if (data.success) {
                // ✅ Mostrar mensaje de éxito
                alert(data.message || 'Sesión programada con éxito');

                // ✅ Cerrar el modal correctamente
                cerrarModal();

                // ✅ Recargar la página para ver la nueva sesión
                setTimeout(() => location.reload(), 500);
            } else {
                alert('❌ Error: ' + (data.message || 'No se pudo programar la sesión'));
            }

        } catch (e) {
            console.error('❌ JSON inválido:', e);
            alert('Respuesta del servidor inválida. Consulta la consola.');
        }
    })
    .catch(err => {
        console.error('❌ Error en fetch:', err);
        alert('Hubo un error al intentar programar la sesión');
    });
}

// ============================================================================
// SUBIR MATERIAL - CORREGIDO
// ============================================================================

function mostrarSubirMaterial() {
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = 'Subir Nuevo Material';
    
    modalContent.innerHTML = `
        <form id="subirMaterialForm" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cursoMaterial">Curso</label>
                        <select class="form-control" id="cursoMaterial" name="curso_id" required>
                            <option value="">Seleccionar curso...</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="moduloMaterial">Módulo</label>
                        <select class="form-control" id="moduloMaterial" name="modulo_id" required>
                            <option value="">Primero selecciona un curso</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="tituloMaterial">Título del Material</label>
                <input type="text" class="form-control" id="tituloMaterial" name="titulo" required>
            </div>
            
            <div class="form-group">
                <label for="tipoMaterial">Tipo de Material</label>
                <select class="form-control" id="tipoMaterial" name="tipo" required>
                    <option value="">Seleccionar tipo...</option>
                    <option value="pdf">Documento PDF</option>
                    <option value="video">Video</option>
                    <option value="imagen">Imagen</option>
                    <option value="texto">Texto/Contenido</option>
                </select>
            </div>
            
            <div class="form-group" id="archivoGroup">
                <label for="archivoMaterial">Seleccionar Archivo</label>
                <input type="file" class="form-control-file" id="archivoMaterial" name="archivo">
                <small class="form-text text-muted">Tamaño máximo: 50MB</small>
            </div>
            
            <div class="form-group" id="contenidoGroup" style="display: none;">
                <label for="contenidoMaterial">Contenido</label>
                <textarea class="form-control" id="contenidoMaterial" name="contenido" rows="5"></textarea>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="obligatorioMaterial" name="obligatorio" value="1">
                    <label class="form-check-label" for="obligatorioMaterial">
                        Material obligatorio
                    </label>
                </div>
            </div>
            
            <div class="form-group text-right">
                <button type="button" class="btn btn-secondary mr-2" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload mr-2"></i>
                    Subir Material
                </button>
            </div>
        </form>
    `;
    
    $('#accionesModal').modal('show');
    
    // Cargar cursos del instructor
    cargarCursosParaMaterial();
    
    // Manejar cambio de curso para cargar módulos
    document.getElementById('cursoMaterial').addEventListener('change', function() {
        cargarModulosCurso(this.value);
    });
    
    // Manejar cambio de tipo de material
    document.getElementById('tipoMaterial').addEventListener('change', function() {
        toggleMaterialInputs(this.value);
    });
    
    // Manejar envío del formulario
    document.getElementById('subirMaterialForm').addEventListener('submit', function(e) {
        e.preventDefault();
        subirNuevoMaterial(this);
    });
}

function cargarCursosParaMaterial() {
    fetch('../dashboard/get_instructor_courses.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('cursoMaterial');
            if (data.success && data.courses.length > 0) {
                data.courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = course.nombre;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar cursos:', error);
        });
}

function cargarModulosCurso(cursoId) {
    const moduloSelect = document.getElementById('moduloMaterial');
    moduloSelect.innerHTML = '<option value="">Cargando módulos...</option>';
    
    if (!cursoId) {
        moduloSelect.innerHTML = '<option value="">Primero selecciona un curso</option>';
        return;
    }
    
    fetch(`../dashboard/get_course_modules.php?curso_id=${cursoId}`)
        .then(response => response.json())
        .then(data => {
            moduloSelect.innerHTML = '<option value="">Seleccionar módulo...</option>';
            if (data.success && data.modules.length > 0) {
                data.modules.forEach(module => {
                    const option = document.createElement('option');
                    option.value = module.id;
                    option.textContent = module.titulo;
                    moduloSelect.appendChild(option);
                });
            } else {
                moduloSelect.innerHTML = '<option value="">No hay módulos disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error al cargar módulos:', error);
            moduloSelect.innerHTML = '<option value="">Error al cargar módulos</option>';
        });
}

function toggleMaterialInputs(tipo) {
    const archivoGroup = document.getElementById('archivoGroup');
    const contenidoGroup = document.getElementById('contenidoGroup');
    const archivoInput = document.getElementById('archivoMaterial');
    
    if (tipo === 'texto') {
        archivoGroup.style.display = 'none';
        contenidoGroup.style.display = 'block';
        archivoInput.required = false;
    } else {
        archivoGroup.style.display = 'block';
        contenidoGroup.style.display = 'none';
        archivoInput.required = true;
        
        // Configurar tipos de archivo aceptados
        switch(tipo) {
            case 'pdf':
                archivoInput.accept = '.pdf';
                break;
            case 'video':
                archivoInput.accept = '.mp4,.avi,.mov,.wmv';
                break;
            case 'imagen':
                archivoInput.accept = '.jpg,.jpeg,.png,.gif';
                break;
            default:
                archivoInput.accept = '';
        }
    }
}

function subirNuevoMaterial(form) {
    const formData = new FormData(form);
    
    // Mostrar indicador de carga
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subiendo...';
    submitBtn.disabled = true;
    
    fetch('../dashboard/upload_material.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cerrarModal(); // Usar función corregida
            alert('Material subido exitosamente');
        } else {
            alert('Error al subir el material: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al subir el material');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// ============================================================================
// FUNCIONES AUXILIARES (mantener las existentes si las hay)
// ============================================================================

// ============================================================================
// ACCIONES DE CURSOS
// ============================================================================

function initializeCourseActions() {
    // Manejar eliminación de cursos
    document.querySelectorAll('.delete-course').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            confirmarEliminar(id, nombre);
        });
    });
}

// function confirmarEliminar(id, nombre) {
//     document.getElementById('curso-nombre').textContent = nombre;
    
//     const btnEliminar = document.getElementById('btn-eliminar');
//     btnEliminar.onclick = function() {
//         eliminarCurso(id);
//     };
    
//     $('#confirmarEliminarModal').modal('show');
// }

// function eliminarCurso(id) {
//     if (!id || isNaN(id)) {
//         alert('ID de curso no válido');
//         return;
//     }
    
//     fetch(`./Acciones/delete_course.php?id=${id}`, {
//         method: 'GET',
//         credentials: 'same-origin'
//     })
//     .then(response => response.json())
//     .then(data => {
//         $('#confirmarEliminarModal').modal('hide');

//         if (data.success) {
//             const cursoElement = document.querySelector(`.curso-item[data-id="${id}"]`);
//             if (cursoElement) {
//                 cursoElement.remove();
//             }
//             alert('Curso marcado para eliminación. Se eliminará permanentemente en 30 días.');
            
//             // Verificar si quedan cursos visibles después del filtro actual
//             const activeFilter = document.querySelector('.filter-btn.btn-primary')?.getAttribute('data-filter') || 'todos';
//             filterCourses(activeFilter);
//         } else {
//             alert('Error al eliminar el curso: ' + (data.message || 'Error desconocido'));
//         }
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         alert('Error al conectar con el servidor: ' + error.message);
//         $('#confirmarEliminarModal').modal('hide');
//     });
// }

// ============================================================================
// PERSONAS INSCRITAS
// ============================================================================

function mostrarPersonasInscritas(cursoId, cursoNombre) {
    $('#cursoNombre').text(cursoNombre);
    $('#listaInscritos').html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div></td></tr>');

    $.ajax({
        url: '../courses/get_inscritos.php',
        type: 'GET',
        data: { curso_id: cursoId },
        dataType: 'json',
        success: function(response) {
            $('#listaInscritos').empty();

            if (response.success && response.inscritos.length > 0) {
                $('#sinInscritos').addClass('d-none');

                $.each(response.inscritos, function(index, inscrito) {
                    $('#listaInscritos').append(`
                        <tr>
                            <td>${inscrito.nombre}</td>
                            <td>${inscrito.email}</td>
                            <td>${inscrito.fecha_inscripcion}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarInscrito(${inscrito.usuario_id}, ${cursoId}, '${inscrito.nombre}')">
                                    <i class="fas fa-user-minus"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    `);
                });
            } else {
                $('#sinInscritos').removeClass('d-none');
            }
        },
        error: function() {
            $('#listaInscritos').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los inscritos. Intenta nuevamente.</td></tr>');
        }
    });

    $('#modalInscritos').modal('show');
}

function eliminarInscrito(usuarioId, cursoId, nombreEstudiante) {
    if (confirm(`¿Estás seguro de que deseas eliminar a ${nombreEstudiante} del curso?`)) {
        $.ajax({
            url: '../courses/remove_student.php',
            type: 'POST',
            data: {
                usuario_id: usuarioId,
                curso_id: cursoId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Estudiante eliminado del curso exitosamente');
                    // Recargar la lista de inscritos
                    mostrarPersonasInscritas(cursoId, $('#cursoNombre').text());
                } else {
                    alert('Error al eliminar el estudiante: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    }
}

</script>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Función para mostrar alertas
    function showAlert(message, type) {
        // Crear el elemento de alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert-custom';
        
        // Definir colores según el tipo
        let bgColor, borderColor, textColor, iconClass;
        switch(type) {
            case 'error':
                bgColor = '#f8d7da';
                borderColor = '#f5c6cb';
                textColor = '#721c24';
                iconClass = '⚠️'; // Puedes usar iconos de Bootstrap Icons si están disponibles
                break;
            case 'warning':
                bgColor = '#fff3cd';
                borderColor = '#ffecb5';
                textColor = '#856404';
                iconClass = '⚠️';
                break;
            case 'success':
                bgColor = '#d1e7dd';
                borderColor = '#badbcc';
                textColor = '#0f5132';
                iconClass = '✅';
                break;
            default:
                bgColor = '#d1ecf1';
                borderColor = '#bee5eb';
                textColor = '#0c5460';
                iconClass = 'ℹ️';
        }
        
        // Aplicar estilos
        alertDiv.style.backgroundColor = bgColor;
        alertDiv.style.borderColor = borderColor;
        alertDiv.style.color = textColor;
        alertDiv.style.border = `1px solid ${borderColor}`;
        
        // Contenido HTML
        alertDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.2em;">${iconClass}</span>
                <span style="flex: 1;">${message}</span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: ${textColor}; opacity: 0.7;">&times;</button>
            </div>
        `;
        
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
        showAlert('<?php echo addslashes($error_message); ?>', '<?php echo $error_type; ?>');
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        showAlert('<?php echo addslashes($success_message); ?>', 'success');
    <?php endif; ?>
});
</script>

<!-- ====================================
     PARTE 4: VERSIÓN CON BOOTSTRAP ICONS (Opcional)
     ==================================== -->
<!-- Si tienes Bootstrap Icons disponible, usa esta versión mejorada: -->

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


});
</script>

</body>
</html>
