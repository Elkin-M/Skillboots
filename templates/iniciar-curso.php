<?php
session_start();
require_once 'conexion/db.php';

// Agregar protección CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirigir al login si no está autenticado
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Mejorada la validación de entrada
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "ID de curso inválido";
    exit;
}

$curso_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Verificar si el usuario está inscrito en el curso con consulta mejorada
    $sql = "SELECT uc.*, c.nombre as curso_nombre
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.curso_id = :curso_id AND uc.usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':user_id' => $user_id
    ]);

    if ($stmt->rowCount() === 0) {
        // Si no está inscrito, redirigir a la página del curso
        $_SESSION['info_message'] = "Debes inscribirte en este curso primero";
        header('Location: curso.php?id=' . $curso_id);
        exit;
    }

    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al verificar la inscripción del usuario: " . $e->getMessage());
    throw $e;
}

try {
    // Obtener información del curso
    $sql = "SELECT c.*, u.name as instructor_nombre, u.lastname as instructor_apellido
            FROM cursos c
            LEFT JOIN usuarios u ON c.instructor_id = u.id
            WHERE c.id = :curso_id AND c.estado = 'publicado' AND c.estate = 'activo'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);

    if ($stmt->rowCount() === 0) {
        echo "Curso no encontrado o no está disponible";
        exit;
    }

    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener información del curso: " . $e->getMessage());
    throw $e;
}

try {
    // Obtener módulos y su progreso
    $sql = "SELECT m.*,
           (SELECT COUNT(*) FROM contenido_visto cv
            JOIN contenido_modular cm ON cv.contenido_id = cm.id
            WHERE cm.modulo_id = m.id AND cv.usuario_id = :user_id) as contenidos_vistos,
           (SELECT COUNT(*) FROM actividades_completadas ac
            JOIN actividades a ON ac.actividad_id = a.id
            WHERE a.unidad_id = m.id AND ac.usuario_id = :aser_id) as actividades_completadas,
           (SELECT COUNT(*) FROM contenido_modular WHERE modulo_id = m.id) as total_contenidos,
           (SELECT COUNT(*) FROM actividades WHERE unidad_id = m.id) as total_actividades
           FROM modulos m
           WHERE m.curso_id = :curso_id
           ORDER BY m.orden ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':user_id' => $user_id,
        ':aser_id' => $user_id
    ]);

    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener módulos y su progreso: " . $e->getMessage());
    throw $e;
}

// Calcular el progreso de cada módulo
foreach ($modulos as &$modulo) {
    $total_items = $modulo['total_contenidos'] + $modulo['total_actividades'];
    $items_completados = $modulo['contenidos_vistos'] + $modulo['actividades_completadas'];
    $modulo['progreso'] = $total_items > 0 ? round(($items_completados / $total_items) * 100) : 0;

    // Obtener recursos
    try {
        $sql = "SELECT * FROM recursos WHERE unidad_id = :modulo_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $modulo['recursos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener recursos: " . $e->getMessage());
        throw $e;
    }

// Obtener actividades
try {
    $sql = "SELECT a.*
            FROM actividades a
            WHERE a.unidad_id = :modulo_id
            ORDER BY a.orden ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':modulo_id' => $modulo['id']]);
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener actividades: " . $e->getMessage());
    throw $e;
}

// Obtener estado de completado para cada actividad
foreach ($actividades as &$actividad) {
    try {
        $sql = "SELECT COUNT(*) > 0 as completada
                FROM actividades_completadas
                WHERE actividad_id = :actividad_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':actividad_id' => $actividad['id'],
            ':user_id' => $user_id
        ]);
        $actividad['completada'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al obtener estado de completado para actividad ID " . $actividad['id'] . ": " . $e->getMessage());
        throw $e;
    }

    try {
        $sql = "SELECT calificacion
                FROM actividades_completadas
                WHERE actividad_id = :actividad_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':actividad_id' => $actividad['id'],
            ':user_id' => $user_id
        ]);
        $actividad['calificacion'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al obtener calificación para actividad ID " . $actividad['id'] . ": " . $e->getMessage());
        throw $e;
    }

    try {
        $sql = "SELECT fecha_completado
                FROM actividades_completadas
                WHERE actividad_id = :actividad_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':actividad_id' => $actividad['id'],
            ':user_id' => $user_id
        ]);
        $actividad['fecha_completado'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al obtener fecha de completado para actividad ID " . $actividad['id'] . ": " . $e->getMessage());
        throw $e;
    }
}

$modulo['actividades'] = $actividades;


   // Obtener contenido modular
try {
    $sql = "SELECT cm.*
            FROM contenido_modular cm
            WHERE cm.modulo_id = :modulo_id
            ORDER BY cm.orden ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':modulo_id' => $modulo['id']]);
    $contenido_modular = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener contenido modular: " . $e->getMessage());
    throw $e;
}

// Obtener estado de visto y fecha de visto para cada contenido modular
foreach ($contenido_modular as &$contenido) {
    try {
        $sql = "SELECT COUNT(*) > 0 as visto
                FROM contenido_visto
                WHERE contenido_id = :contenido_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':contenido_id' => $contenido['id'],
            ':user_id' => $user_id
        ]);
        $contenido['visto'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al obtener estado de visto para contenido ID " . $contenido['id'] . ": " . $e->getMessage());
        throw $e;
    }

    try {
        $sql = "SELECT fecha_visto
                FROM contenido_visto
                WHERE contenido_id = :contenido_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':contenido_id' => $contenido['id'],
            ':user_id' => $user_id
        ]);
        $contenido['fecha_visto'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al obtener fecha de visto para contenido ID " . $contenido['id'] . ": " . $e->getMessage());
        throw $e;
    }
}

$modulo['contenido_modular'] = $contenido_modular;

}

// Encontrar el último contenido visto o la próxima lección por ver
$ultimo_contenido_id = null;
$proximo_contenido_id = null;

foreach ($modulos as $modulo) {
    foreach ($modulo['contenido_modular'] as $contenido) {
        if ($contenido['visto']) {
            $ultimo_contenido_id = $contenido['id'];
        } elseif ($proximo_contenido_id === null) {
            $proximo_contenido_id = $contenido['id'];
        }
    }
}

// Si no hay próximo contenido, usar el último visto
if ($proximo_contenido_id === null && $ultimo_contenido_id !== null) {
    $proximo_contenido_id = $ultimo_contenido_id;
}

// Si no hay contenido visto ni próximo, usar el primer contenido del primer módulo
if ($proximo_contenido_id === null && !empty($modulos) && !empty($modulos[0]['contenido_modular'])) {
    $proximo_contenido_id = $modulos[0]['contenido_modular'][0]['id'];
}

try {
    // Obtener el total de contenidos vistos
    $sql = "SELECT COUNT(*) as contenidos_vistos
            FROM contenido_visto cv
            JOIN contenido_modular cm ON cv.contenido_id = cm.id
            JOIN modulos m ON cm.modulo_id = m.id
            WHERE m.curso_id = :curso_id AND cv.usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':user_id' => $user_id
    ]);
    $contenidos_vistos = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error al obtener el total de contenidos vistos: " . $e->getMessage());
    throw $e;
}

try {
    // Obtener el total de actividades completadas
    $sql = "SELECT COUNT(*) as actividades_completadas
            FROM actividades_completadas ac
            JOIN actividades a ON ac.actividad_id = a.id
            JOIN modulos m ON a.unidad_id = m.id
            WHERE m.curso_id = :curso_id AND ac.usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':user_id' => $user_id
    ]);
    $actividades_completadas = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error al obtener el total de actividades completadas: " . $e->getMessage());
    throw $e;
}

try {
    // Obtener el total de contenidos
    $sql = "SELECT COUNT(*) as total_contenidos
            FROM contenido_modular cm
            JOIN modulos m ON cm.modulo_id = m.id
            WHERE m.curso_id = :curso_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    $total_contenidos = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error al obtener el total de contenidos: " . $e->getMessage());
    throw $e;
}

try {
    // Obtener el total de actividades
    $sql = "SELECT COUNT(*) as total_actividades
            FROM actividades a
            JOIN modulos m ON a.unidad_id = m.id
            WHERE m.curso_id = :curso_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    $total_actividades = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error al obtener el total de actividades: " . $e->getMessage());
    throw $e;
}

// Calcular el progreso general del curso
$total_items_curso = $total_contenidos + $total_actividades;
$items_completados_curso = $contenidos_vistos + $actividades_completadas;
$progreso_curso = $total_items_curso > 0 ? round(($items_completados_curso / $total_items_curso) * 100) : 0;


// Usar transacción para actualizar el progreso
$conn->beginTransaction();
// Actualizar el progreso en la tabla usuarios_cursos
try {
    $sql = "UPDATE usuarios_cursos SET
            progreso = :progreso,
            ultimo_acceso = NOW()
            WHERE curso_id = :curso_id AND usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':progreso' => $progreso_curso,
        ':curso_id' => $curso_id,
        ':user_id' => $user_id
    ]);
} catch (PDOException $e) {
    error_log("Error al actualizar el progreso del curso: " . $e->getMessage());
    $conn->rollBack();
    throw $e;
}

// Verificar si el curso está completado y actualizar si es necesario
try {
    $curso_completado = $progreso_curso >= 100;

    if ($curso_completado && $inscripcion['completado'] == 0) {
        $sql = "UPDATE usuarios_cursos SET
                completado = 1,
                fecha_completado = NOW()
                WHERE curso_id = :curso_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':curso_id' => $curso_id,
            ':user_id' => $user_id
        ]);
    }

    $conn->commit();
} catch (PDOException $e) {
    error_log("Error al actualizar el estado de completado del curso: " . $e->getMessage());
    $conn->rollBack();
    throw $e;
}


// Construir las rutas base para enlaces
$base_path = dirname($_SERVER['PHP_SELF']);
$base_path = rtrim($base_path, '/') . '/';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title><?php echo htmlspecialchars($curso['nombre']); ?> - SkillBoots</title>
    <!-- Incluir CSS y JS necesarios -->
    <link href="../assets/css/curso.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
   body {
    zoom: 0.8;
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
    include 'navbar-estu.php'; // Navbar para estudiantes
} elseif ($pageData['userRole'] === 'profesor'){
    include 'navbar-pro.php';
} else {
    include '../includes/navbar.php';
}

// Mostrar mensajes de error o éxito si existen
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['error_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['success_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['success_message']);
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar izquierdo con temario -->
        <div class="col-lg-3 sidebar">
            <div class="sidebar-header">
                <h3><?php echo htmlspecialchars($curso['nombre']); ?></h3>
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                         style="width: <?php echo $progreso_curso; ?>%;"
                         aria-valuenow="<?php echo $progreso_curso; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $progreso_curso; ?>%
                    </div>
                </div>
                <div class="instructor-info">
                    <p class="mb-1"><i class="fas fa-chalkboard-teacher me-2"></i> Instructor:
                    <?php echo htmlspecialchars($curso['instructor_nombre'] . ' ' . $curso['instructor_apellido']); ?></p>
                </div>
            </div>
            <div class="accordion" id="temarioAcordeon">
                <?php foreach ($modulos as $index => $modulo): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#modulo-<?php echo $modulo['id']; ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <span><?php echo $modulo['orden']; ?>. <?php echo htmlspecialchars($modulo['titulo']); ?></span>
                                <div class="progress ms-3" style="width: 60px; height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?php echo $modulo['progreso']; ?>%;"
                                         aria-valuenow="<?php echo $modulo['progreso']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="modulo-<?php echo $modulo['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                         data-bs-parent="#temarioAcordeon">
                        <div class="accordion-body p-0">
                            <ul class="list-group list-group-flush">
                                <!-- Contenido modular -->
                                <?php foreach ($modulo['contenido_modular'] as $contenido): ?>
                                <li class="list-group-item">
                                    <a href="contenido.php?id=<?php echo $contenido['id']; ?>" class="contenido-link d-flex align-items-center
                                        <?php echo $contenido['visto'] ? 'visto' : ''; ?>
                                        <?php echo $contenido['id'] == $proximo_contenido_id ? 'active' : ''; ?>">
                                        <?php
                                        $icono = 'fa-file-alt';
                                        if ($contenido['tipo'] == 'video') $icono = 'fa-video';
                                        elseif ($contenido['tipo'] == 'audio') $icono = 'fa-headphones';
                                        elseif ($contenido['tipo'] == 'imagen') $icono = 'fa-image';
                                        ?>
                                        <i class="fas <?php echo $icono; ?> me-2"></i>
                                        <span><?php echo htmlspecialchars($contenido['titulo']); ?></span>
                                        <?php if ($contenido['visto']): ?>
                                            <i class="fas fa-check-circle ms-auto text-success"></i>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>

                                <!-- Recursos -->
                                <?php foreach ($modulo['recursos'] as $recurso): ?>
                                <li class="list-group-item">
                                    <a href="actividad.php?id=<?php echo $recurso['id']; ?>" class="recurso-link d-flex align-items-center"
                                       target="_blank" rel="noopener">
                                        <i class="fas fa-file-download me-2"></i>
                                        <span><?php echo htmlspecialchars($recurso['titulo']); ?></span>
                                        <?php if ($recurso['obligatorio']): ?>
                                            <span class="badge bg-danger ms-auto">Obligatorio</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>

                                <!-- Actividades -->
                                <?php foreach ($modulo['actividades'] as $actividad): ?>
                                <li class="list-group-item">
                                    <a href="actividad.php?id=<?php echo $actividad['id']; ?>" class="actividad-link d-flex align-items-center
                                        <?php echo $actividad['completada'] ? 'completada' : ''; ?>">
                                        <?php
                                        $icono = 'fa-tasks';
                                        if ($actividad['tipo'] == 'quiz') $icono = 'fa-question-circle';
                                        elseif ($actividad['tipo'] == 'assignment') $icono = 'fa-clipboard-list';
                                        ?>
                                        <i class="fas <?php echo $icono; ?> me-2"></i>
                                        <span><?php echo htmlspecialchars($actividad['titulo']); ?></span>
                                        <?php if ($actividad['completada']): ?>
                                            <span class="badge bg-success ms-auto"><?php echo $actividad['calificacion']; ?>%</span>
                                        <?php elseif ($actividad['obligatorio']): ?>
                                            <span class="badge bg-danger ms-auto">Obligatorio</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark ms-auto"><?php echo $actividad['puntuacion']; ?> pts</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>


        
        <!-- Contenido principal -->
        <div class="col-lg-6 main-content">
            <div id="loading-indicator" class="d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <?php if ($proximo_contenido_id): ?>
                <!-- iframe para cargar el contenido -->
                <iframe id="contenido-frame" src="contenido.php?id=<?php echo $proximo_contenido_id; ?>&embedded=1"
                        frameborder="0" title="Contenido del curso"></iframe>
            <?php else: ?>
                <div class="p-5 text-center empty-state">
                    <i class="fas fa-book-open fa-4x mb-3 text-muted"></i>
                    <h3>Bienvenido/a al curso</h3>
                    <p>Selecciona una lección del temario para comenzar a aprender.</p>
                </div>
            <?php endif; ?>
        </div>
         <!-- Nueva barra lateral derecha -->
        <div class="col-lg-3 right-sidebar">
            <!-- Calendario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Calendario</h5>
                </div>
                <div class="card-body">
                    <div id="calendar-container"></div>
                    <div class="d-flex justify-content-center mt-3">
                        <a href="#" class="btn btn-sm btn-outline-primary" id="calendar-completo">Calendario completo</a>
                        <a href="#" class="btn btn-sm btn-outline-secondary ms-2" id="exportar-calendario">Importar/Exportar Calendarios</a>
                    </div>
                </div>
            </div>
            
            <!-- Usuarios en línea -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Usuarios en línea</h5>
                </div>
                <div class="card-body">
                    <div id="usuarios-online">
                        <div class="online-count"><span id="total-usuarios">0</span> usuarios online (últimos 5 minutos)</div>
                        <ul class="list-group online-users-list" id="lista-usuarios-online">
                            <!-- Los usuarios en línea se cargarán aquí -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Toast para notificaciones -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-bell me-2"></i>
            <strong class="me-auto" id="toast-title">Notificación</strong>
            <small id="toast-time">Ahora</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toast-message">
            Mensaje de notificación
        </div>
    </div>
</div>
<div class="sidebar-toggle" id="sidebar-toggle">
        <i class="fas fa-columns"></i>
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

<!-- JavaScript necesario -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/calendario.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Module globals
    const iframe = document.getElementById('contenido-frame');
    const enlaces = document.querySelectorAll('.contenido-link, .actividad-link');

    // Dynamic iframe height adjustment
    function adjustIframeHeight() {
        if (!iframe) return;

        const windowHeight = window.innerHeight;
        const navbarHeight = document.querySelector('nav')?.offsetHeight || 0;
        const footerHeight = document.querySelector('footer')?.offsetHeight || 0;
        const padding = 30;

        const newHeight = (windowHeight - navbarHeight - footerHeight - padding);
        iframe.style.height = `${newHeight}px`;
    }

  

    // Update module progress percentages
    function updateModuleProgress() {
        // This could be implemented with AJAX instead of page reload
        // for a smoother experience
        setTimeout(() => {
            fetch(`api/progress.php?curso_id=${getCursoId()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCSRFToken()
                },
            })
            .then(response => response.json())
            .then(data => {
                // Update progress bars with new data
                updateProgressBars(data);
            })
            .catch(error => {
                console.error('Error updating progress:', error);
                // Fallback to page reload if API fails
                window.location.reload();
            });
        }, 500);
    }

    // Helper functions
    function getCursoId() {
        // Extract from URL or data attribute
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }

    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function updateProgressBars(data) {
        // Update course progress
        const courseProgress = document.querySelector('.sidebar-header .progress-bar');
        if (courseProgress) {
            courseProgress.style.width = `${data.course_progress}%`;
            courseProgress.textContent = `${data.course_progress}%`;
            courseProgress.setAttribute('aria-valuenow', data.course_progress);
        }

        // Update module progress bars
        data.modules.forEach(module => {
            const moduleProgress = document.querySelector(`#modulo-${module.id} .progress-bar`);
            if (moduleProgress) {
                moduleProgress.style.width = `${module.progress}%`;
                moduleProgress.setAttribute('aria-valuenow', module.progress);
            }
        });
    }

    // Handle iframe messages
    window.addEventListener('message', function(event) {
        // Security check
        if (!isValidMessageOrigin(event.origin)) return;

        switch(event.data.action) {
            case 'contenidoVisto':
                markContentAsViewed(event.data.contenidoId);
                break;

            case 'actividadCompletada':
                updateModuleProgress();
                break;

            case 'frameResize':
                if (event.data.height) {
                    iframe.style.height = `${event.data.height}px`;
                }
                break;
        }
    });

    function isValidMessageOrigin(origin) {
        // Validate message origin to prevent XSS
        const allowedOrigins = [window.location.origin];
        return allowedOrigins.includes(origin);
    }

    // Handle content navigation
    enlaces.forEach(enlace => {
        enlace.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            // Show loading indicator
            if (iframe) {
                iframe.style.opacity = '0.6';
            }

            // Update active state
            enlaces.forEach(el => el.classList.remove('active'));
            this.classList.add('active');

            // Load content
            setTimeout(() => {
                if (iframe) {
                    iframe.src = href + '&embedded=1';
                    iframe.onload = function() {
                        iframe.style.opacity = '1';
                    };
                }
            }, 200);
        });
    });

    // Initialize
    adjustIframeHeight();
    window.addEventListener('resize', adjustIframeHeight);
});
</script>

<style>
/* Global styles */
</style>

</body>
</html>
