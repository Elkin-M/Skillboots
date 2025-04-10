<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Cursos - SkillBoots</title>
    <!-- Bootstrap CSS -->
    <!-- Font Awesome para iconos -->
    <link href="css/crear_curso.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>

<?php
session_start();
require_once 'roles/auth.php';

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
}else{
    include 'navbar.php';
}
?>

    <!-- Admin Header -->
    <header class="bg-white shadow-sm">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold text-dark mb-0">Panel de Administración</h1>
                <div class="d-flex align-items-center">
                    <span class="me-3" style="color: var(--primary);">Bienvenido, <?php echo htmlspecialchars($userName); ?></span>
                    <a href="./holaaaa.php" class="hola">Regresar</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Alert Container -->
    <div id="alertContainer" class="container mt-3"></div>

    <!-- Main Content -->
    <div class="container py-4">
        <!-- Course Management Tabs -->
        <div class="mb-4">
            <ul class="nav nav-tabs border-bottom" id="courseManagementTabs">
                <li class="nav-item">
                    <a class="nav-link active px-4" data-bs-toggle="tab" href="#misCursos">Mis Cursos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4" data-bs-toggle="tab" href="#crearCurso">Crear Nuevo Curso</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4" data-bs-toggle="tab" href="#estadisticas">Estadísticas</a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Mis Cursos Tab -->
            <div class="tab-pane fade show active" id="misCursos">
                <div class="row custom-row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">

                <!-- Course Card -->
                <?php
                include './conexion/db.php';  // Este archivo se encarga de la conexión PDO
                $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;
                try {
                    // Asegúrate de que la sesión esté iniciada
                    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;

                    // Consulta SQL corregida - asegurarse de que los parámetros sean correctos
                    $sql = "SELECT
                                c.id,
                                c.nombre AS titulo,
                                c.descripcion,
                                c.imagen,
                                COALESCE(uc.progreso, 0) AS progreso,
                                COALESCE(uc.ultimo_acceso, 'Nunca') AS ultimoAcceso,
                                COALESCE(uc.lecciones_completadas, 0) AS lecciones_completadas,
                                c.total_lecciones,
                                COALESCE(c.horas_totales * (1 - COALESCE(uc.progreso, 0)/100), c.horas_totales) AS horas_restantes,
                                c.estado AS estado,
                                CASE
                                    WHEN COALESCE(uc.progreso, 0) < 30 THEN 'danger'
                                    WHEN COALESCE(uc.progreso, 0) < 70 THEN 'warning'
                                    ELSE 'success'
                                END AS colorProgreso
                            FROM
                                cursos c
                            LEFT JOIN
                                usuarios_cursos uc ON c.id = uc.curso_id AND uc.usuario_id = :usuario_id
                            ORDER BY
                                COALESCE(uc.ultimo_acceso, '1900-01-01') DESC
                            LIMIT 6";

                    // Preparar la consulta
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Comprobar si $cursos está vacío y asignar un array vacío si es así
                    if (empty($cursos)) {
                        $cursos = [];
                    }

                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                    // Inicializar $cursos como un array vacío en caso de error
                    $cursos = [];
                }
                ?>

                </div>
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <!-- Mostrar cursos existentes -->
                    <?php foreach ($cursos as $curso): ?>
                        <div class="col mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="<?= htmlspecialchars($curso['imagen']) ?>" class="card-img-top" alt="Course thumbnail"
                                    style="height: 180px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($curso['titulo']) ?></h5>
                                    <p class="card-text text-muted"><?= htmlspecialchars($curso['descripcion']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Estado:
                                            <span class="badge bg-<?= $curso['estado'] === 'borrador' ? 'warning' : ($curso['estado'] === 'publicado' ? 'success' : 'danger') ?>"><?= htmlspecialchars($curso['estado']) ?></span>
                                        </small>
                                        <button class="btn btn-sm text-orange" onclick="editCourse(<?= $curso['id'] ?>)">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Botón para crear nuevo curso -->
                    <div class="col mb-4">
                        <div class="card h-100 shadow-sm border-dashed" onclick="showCreateCourseTab()">
                            <div class="card-body d-flex align-items-center justify-content-center"
                                style="height: 280px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-plus-circle fa-3x mb-3"></i>
                                    <p>Crear Nuevo Curso</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Crear Curso Tab -->
            <div class="tab-pane fade" id="crearCurso">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Crear Nuevo Curso</h4>
                        <form id="courseForm" action="save_course.php" method="post" enctype="multipart/form-data">
                            <!-- Campo oculto para instructor_id -->
                            <input type="hidden" name="instructor_id" value="1">

                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="courseTitle" class="form-label">Título del Curso</label>
                                    <input type="text" class="form-control" id="courseTitle" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="courseCategory" class="form-label">Categoría</label>
                                    <select class="form-select" id="courseCategory" name="categoria" required>
                                        <option selected>Desarrollo Web</option>
                                        <option>Diseño Gráfico</option>
                                        <option>Marketing Digital</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Course Content -->
                            <div class="mb-4">
                                <label for="courseDescription" class="form-label">Descripción del Curso</label>
                                <textarea class="form-control" id="courseDescription" name="descripcion" rows="4" required></textarea>
                            </div>

                            <!-- Imagen del curso -->
                            <div class="mb-4">
                                <label for="courseImage" class="form-label">Imagen del Curso</label>
                                <input type="file" class="form-control" id="courseImage" name="imagen" accept="image/*">
                                <small class="text-muted">Imagen representativa para el curso (opcional)</small>
                            </div>

                            <!-- NUEVA ESTRUCTURA DEL CURSO (Estilo CTEV) -->
                            <div class="mb-4">
                                <h5 class="mb-3">Estructura del Curso</h5>
                                <div id="courseStructureContainer">
                                    <!-- Sección de actividades de presentación -->
                                    <div class="course-section mb-3">
                                        <div class="course-section-header" onclick="toggleSection('presentacion')">
                                            <span><i class="fas fa-chevron-down me-2"></i>Actividades de presentación</span>
                                            <button type="button" class="btn btn-sm text-orange" onclick="addElement('presentacion', event)">
                                                <i class="fas fa-plus me-1"></i> Añadir elemento
                                            </button>
                                        </div>
                                        <div class="course-section-content" id="presentacion-content">
                                            <!-- Elementos de presentación -->
                                            <div class="activity-item d-flex justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <span class="circle-indicator gray-indicator"></span>
                                                    <input type="text" class="form-control-plaintext" name="presentacion[0][titulo]" value="Foro de presentación" style="display: inline-block; width: auto;">
                                                    <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                        <i class="fas fa-edit"></i> Contenido
                                                    </button>
                                                </div>
                                                <div>
                                                    <select class="form-select form-select-sm" name="presentacion[0][tipo]" style="width: auto; display: inline-block;">
                                                        <option value="foro" selected>Foro</option>
                                                        <option value="cuestionario">Cuestionario</option>
                                                        <option value="enlace">Enlace</option>
                                                    </select>
                                                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="content-details w-100 mt-2" style="display: none;">
                                                    <textarea class="form-control" name="presentacion[0][contenido]" rows="3" placeholder="Descripción y detalles del contenido..."></textarea>
                                                    <div class="mt-2 small text-muted">Ingresa instrucciones, contenido del foro, preguntas, etc.</div>

                                                    <!-- Contenedor para el foro -->
                                                    <div id="foro-container" style="display: none;">
                                                        <label for="foro-descripcion">Descripción del Foro:</label>
                                                        <textarea class="form-control" id="foro-descripcion" name="presentacion[0][foro_descripcion]" rows="3"></textarea>
                                                    </div>

                                                    <!-- Contenedor para el quiz -->
                                                    <div id="quiz-container" style="display: none;">
                                                        <label for="quiz-pregunta">Pregunta del Quiz:</label>
                                                        <input type="text" class="form-control" id="quiz-pregunta" name="presentacion[0][quiz_pregunta]">
                                                        <label for="quiz-respuesta">Respuesta Correcta:</label>
                                                        <input type="text" class="form-control" id="quiz-respuesta" name="presentacion[0][quiz_respuesta]">
                                                    </div>

                                                    <!-- Contenedor para el archivo -->
                                                    <div id="archivo-container" style="display: none;">
                                                        <label for="archivo-upload">Subir Archivo:</label>
                                                        <input type="file" class="form-control" id="archivo-upload" name="presentacion[0][archivo]">
                                                        <label for="archivo-descripcion">Descripción del Archivo:</label>
                                                        <textarea class="form-control" id="archivo-descripcion" name="presentacion[0][archivo_descripcion]" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="activity-item d-flex justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <span class="circle-indicator gray-indicator"></span>
                                                    <input type="text" class="form-control-plaintext" name="presentacion[1][titulo]" value="Enlaces de tutoría" style="display: inline-block; width: auto;">
                                                    <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                        <i class="fas fa-edit"></i> Contenido
                                                    </button>
                                                </div>
                                                <div>
                                                    <select class="form-select form-select-sm" name="presentacion[1][tipo]" style="width: auto; display: inline-block;">
                                                        <option value="foro">Foro</option>
                                                        <option value="cuestionario">Cuestionario</option>
                                                        <option value="enlace" selected>Enlace</option>
                                                    </select>
                                                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="content-details w-100 mt-2" style="display: none;">
                                                    <textarea class="form-control" name="presentacion[1][contenido]" rows="3" placeholder="Enlaces y recursos para tutorías..."></textarea>
                                                    <div class="mt-2 small text-muted">Agrega enlaces a horarios de tutoría, contacto del tutor, etc.</div>

                                                    <!-- Contenedor para el foro -->
                                                    <div id="foro-container" style="display: none;">
                                                        <label for="foro-descripcion">Descripción del Foro:</label>
                                                        <textarea class="form-control" id="foro-descripcion" name="presentacion[1][foro_descripcion]" rows="3"></textarea>
                                                    </div>

                                                    <!-- Contenedor para el quiz -->
                                                    <div id="quiz-container" style="display: none;">
                                                        <label for="quiz-pregunta">Pregunta del Quiz:</label>
                                                        <input type="text" class="form-control" id="quiz-pregunta" name="presentacion[1][quiz_pregunta]">
                                                        <label for="quiz-respuesta">Respuesta Correcta:</label>
                                                        <input type="text" class="form-control" id="quiz-respuesta" name="presentacion[1][quiz_respuesta]">
                                                    </div>

                                                    <!-- Contenedor para el archivo -->
                                                    <div id="archivo-container" style="display: none;">
                                                        <label for="archivo-upload">Subir Archivo:</label>
                                                        <input type="file" class="form-control" id="archivo-upload" name="presentacion[1][archivo]">
                                                        <label for="archivo-descripcion">Descripción del Archivo:</label>
                                                        <textarea class="form-control" id="archivo-descripcion" name="presentacion[1][archivo_descripcion]" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="activity-item d-flex justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <span class="circle-indicator green-indicator"></span>
                                                    <input type="text" class="form-control-plaintext" name="presentacion[2][titulo]" value="Pregúntale al profesor" style="display: inline-block; width: auto;">
                                                    <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                        <i class="fas fa-edit"></i> Contenido
                                                    </button>
                                                </div>
                                                <div>
                                                    <select class="form-select form-select-sm" name="presentacion[2][tipo]" style="width: auto; display: inline-block;">
                                                        <option value="foro" selected>Foro</option>
                                                        <option value="cuestionario">Cuestionario</option>
                                                        <option value="enlace">Enlace</option>
                                                    </select>
                                                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="content-details w-100 mt-2" style="display: none;">
                                                    <textarea class="form-control" name="presentacion[2][contenido]" rows="3" placeholder="Instrucciones para el foro de preguntas..."></textarea>
                                                    <div class="mt-2 small text-muted">Instrucciones para que los estudiantes planteen sus dudas al profesor.</div>

                                                    <!-- Contenedor para el foro -->
                                                    <div id="foro-container" style="display: none;">
                                                        <label for="foro-descripcion">Descripción del Foro:</label>
                                                        <textarea class="form-control" id="foro-descripcion" name="presentacion[2][foro_descripcion]" rows="3"></textarea>
                                                    </div>

                                                    <!-- Contenedor para el quiz -->
                                                    <div id="quiz-container" style="display: none;">
                                                        <label for="quiz-pregunta">Pregunta del Quiz:</label>
                                                        <input type="text" class="form-control" id="quiz-pregunta" name="presentacion[2][quiz_pregunta]">
                                                        <label for="quiz-respuesta">Respuesta Correcta:</label>
                                                        <input type="text" class="form-control" id="quiz-respuesta" name="presentacion[2][quiz_respuesta]">
                                                    </div>

                                                    <!-- Contenedor para el archivo -->
                                                    <div id="archivo-container" style="display: none;">
                                                        <label for="archivo-upload">Subir Archivo:</label>
                                                        <input type="file" class="form-control" id="archivo-upload" name="presentacion[2][archivo]">
                                                        <label for="archivo-descripcion">Descripción del Archivo:</label>
                                                        <textarea class="form-control" id="archivo-descripcion" name="presentacion[2][archivo_descripcion]" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contenedor para las unidades -->
                                    <div id="unidadesContainer">
                                        <!-- Unidad 1 (por defecto) -->
                                        <div class="course-section mb-3" id="unidad-1">
                                            <div class="course-section-header" onclick="toggleSection('unidad-1')">
                                                <span>
                                                    <i class="fas fa-chevron-down me-2"></i>
                                                    <input type="text" class="form-control-plaintext" name="unidades[0][titulo]" value="Unidad 1" style="display: inline-block; width: auto; font-weight: bold;">
                                                </span>
                                                <div>
                                                    <button type="button" class="btn btn-sm text-orange" onclick="addSection('unidad-1', 'recursos', event)">
                                                        <i class="fas fa-plus-circle me-1"></i> Recurso
                                                    </button>
                                                    <button type="button" class="btn btn-sm text-primary" onclick="addSection('unidad-1', 'actividades', event)">
                                                        <i class="fas fa-plus-circle me-1"></i> Actividad
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="course-section-content" id="unidad-1-content">
                                                <!-- Modelo de Interacción -->
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" name="unidades[0][descripcion]" placeholder="Descripción de la unidad" value="Modelo De Interacción Comunicativa">
                                                </div>

                                                <!-- Recursos Didácticos -->
                                                <div class="mb-3">
                                                    <h6 class="section-title">RECURSOS DIDÁCTICOS</h6>
                                                    <div id="unidad-1-recursos">
                                                        <div class="resource-item d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <span class="circle-indicator green-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][recursos][0][titulo]" value="Módulo de la unidad 1" style="display: inline-block; width: auto;">
                                                                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                    <i class="fas fa-edit"></i> Contenido
                                                                </button>
                                                            </div>
                                                            <div>
                                                                <select class="form-select form-select-sm" name="unidades[0][recursos][0][tipo]" style="width: auto; display: inline-block;">
                                                                    <option value="archivo" selected>Archivo</option>
                                                                    <option value="enlace">Enlace</option>
                                                                    <option value="video">Video</option>
                                                                </select>
                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                            <div class="content-details w-100 mt-2" style="display: none;">
                                                                <textarea class="form-control" name="unidades[0][recursos][0][contenido]" rows="3" placeholder="Descripción del recurso, URL del enlace o video, etc."></textarea>
                                                                <div class="mt-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="unidades[0][recursos][0][obligatorio]" id="recurso-unidad-1-0-obligatorio" value="1">
                                                                        <label class="form-check-label" for="recurso-unidad-1-0-obligatorio">Recurso obligatorio</label>
                                                                    </div>
                                                                    <label class="form-label mb-1">Adjuntos/enlaces:</label>
                                                                    <input type="text" class="form-control mb-2" name="unidades[0][recursos][0][url]" placeholder="URL o nombre del archivo">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="resource-item d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][recursos][1][titulo]" value="Recursos bibliográficos y digitales" style="display: inline-block; width: auto;">
                                                                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                    <i class="fas fa-edit"></i> Contenido
                                                                </button>
                                                            </div>
                                                            <div>
                                                                <select class="form-select form-select-sm" name="unidades[0][recursos][1][tipo]" style="width: auto; display: inline-block;">
                                                                    <option value="archivo">Archivo</option>
                                                                    <option value="enlace" selected>Enlace</option>
                                                                    <option value="video">Video</option>
                                                                </select>
                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                            <div class="content-details w-100 mt-2" style="display: none;">
                                                                <textarea class="form-control" name="unidades[0][recursos][1][contenido]" rows="3" placeholder="Descripción del recurso, URL del enlace o video, etc."></textarea>
                                                                <div class="mt-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="unidades[0][recursos][1][obligatorio]" id="recurso-unidad-1-1-obligatorio" value="1">
                                                                        <label class="form-check-label" for="recurso-unidad-1-1-obligatorio">Recurso obligatorio</label>
                                                                    </div>
                                                                    <label class="form-label mb-1">Adjuntos/enlaces:</label>
                                                                    <input type="text" class="form-control mb-2" name="unidades[0][recursos][1][url]" placeholder="URL o nombre del archivo">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="resource-item d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][recursos][2][titulo]" value="Enlace a biblioteca institucional" style="display: inline-block; width: auto;">
                                                                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                    <i class="fas fa-edit"></i> Contenido
                                                                </button>
                                                            </div>
                                                            <div>
                                                                <select class="form-select form-select-sm" name="unidades[0][recursos][2][tipo]" style="width: auto; display: inline-block;">
                                                                    <option value="archivo">Archivo</option>
                                                                    <option value="enlace" selected>Enlace</option>
                                                                    <option value="video">Video</option>
                                                                </select>
                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                            <div class="content-details w-100 mt-2" style="display: none;">
                                                                <textarea class="form-control" name="unidades[0][recursos][2][contenido]" rows="3" placeholder="Descripción del recurso, URL del enlace o video, etc."></textarea>
                                                                <div class="mt-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="unidades[0][recursos][2][obligatorio]" id="recurso-unidad-1-2-obligatorio" value="1">
                                                                        <label class="form-check-label" for="recurso-unidad-1-2-obligatorio">Recurso obligatorio</label>
                                                                    </div>
                                                                    <label class="form-label mb-1">Adjuntos/enlaces:</label>
                                                                    <input type="text" class="form-control mb-2" name="unidades[0][recursos][2][url]" placeholder="URL o nombre del archivo">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Actividades -->
                                                <div class="mb-3">
                                                    <h6 class="section-title">ACTIVIDADES</h6>
                                                    <div id="unidad-1-actividades">
                                                        <div class="activity-item d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][actividades][0][titulo]" value="Protocolo colaborativo de la unidad" style="display: inline-block; width: auto;">
                                                                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                    <i class="fas fa-edit"></i> Contenido
                                                                </button>
                                                            </div>
                                                            <div>
                                                                <select class="form-select form-select-sm" name="unidades[0][actividades][0][tipo]" style="width: auto; display: inline-block;">
                                                                    <option value="tarea" selected>Tarea</option>
                                                                    <option value="quiz">Quiz</option>
                                                                    <option value="foro">Foro</option>
                                                                </select>
                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                            <div class="content-details w-100 mt-2" style="display: none;">
                                                                <textarea class="form-control mb-2" name="unidades[0][actividades][0][contenido]" rows="3" placeholder="Instrucciones para el protocolo colaborativo..."></textarea>
                                                                <div class="row g-2">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Puntuación:</label>
                                                                        <input type="number" class="form-control" name="unidades[0][actividades][0][puntuacion]" min="0" value="10">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Fecha límite:</label>
                                                                        <input type="date" class="form-control" name="unidades[0][actividades][0][fecha_limite]">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Tiempo (min):</label>
                                                                        <input type="number" class="form-control" name="unidades[0][actividades][0][tiempo]" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="unidades[0][actividades][0][obligatorio]" id="actividad-unidad-1-0-obligatorio" value="1" checked>
                                                                        <label class="form-check-label" for="actividad-unidad-1-0-obligatorio">Actividad obligatoria</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="activity-item d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][actividades][1][titulo]" value="Actividad de la unidad 1" style="display: inline-block; width: auto;">
                                                                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                    <i class="fas fa-edit"></i> Contenido
                                                                </button>
                                                            </div>
                                                            <div>
                                                                <select class="form-select form-select-sm" name="unidades[0][actividades][1][tipo]" style="width: auto; display: inline-block;">
                                                                    <option value="tarea" selected>Tarea</option>
                                                                    <option value="quiz">Quiz</option>
                                                                    <option value="foro">Foro</option>
                                                                </select>
                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                            <div class="content-details w-100 mt-2" style="display: none;">
                                                                <textarea class="form-control mb-2" name="unidades[0][actividades][1][contenido]" rows="3" placeholder="Instrucciones para la actividad..."></textarea>
                                                                <div class="row g-2">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Puntuación:</label>
                                                                        <input type="number" class="form-control" name="unidades[0][actividades][1][puntuacion]" min="0" value="10">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Fecha límite:</label>
                                                                        <input type="date" class="form-control" name="unidades[0][actividades][1][fecha_limite]">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Tiempo (min):</label>
                                                                        <input type="number" class="form-control" name="unidades[0][actividades][1][tiempo]" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="unidades[0][actividades][1][obligatorio]" id="actividad-unidad-1-1-obligatorio" value="1" checked>
                                                                        <label class="form-check-label" for="actividad-unidad-1-1-obligatorio">Actividad obligatoria</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="activity-item d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][actividades][2][titulo]" value="Evaluación de la unidad 1" style="display: inline-block; width: auto;">
                                                                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                    <i class="fas fa-edit"></i> Contenido
                                                                </button>
                                                            </div>
                                                            <div>
                                                                <select class="form-select form-select-sm" name="unidades[0][actividades][2][tipo]" style="width: auto; display: inline-block;">
                                                                    <option value="tarea">Tarea</option>
                                                                    <option value="quiz" selected>Quiz</option>
                                                                    <option value="foro">Foro</option>
                                                                </select>
                                                                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                            <div class="content-details w-100 mt-2" style="display: none;">
                                                                <textarea class="form-control mb-2" name="unidades[0][actividades][2][contenido]" rows="3" placeholder="Instrucciones para la evaluación..."></textarea>
                                                                <div class="row g-2">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Puntuación:</label>
                                                                        <input type="number" class="form-control" name="unidades[0][actividades][2][puntuacion]" min="0" value="20">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Fecha límite:</label>
                                                                        <input type="date" class="form-control" name="unidades[0][actividades][2][fecha_limite]">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Tiempo (min):</label>
                                                                        <input type="number" class="form-control" name="unidades[0][actividades][2][tiempo]" min="0" value="30">
                                                                    </div>
                                                                </div>
                                                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="unidades[0][actividades][2][obligatorio]" id="actividad-unidad-1-2-obligatorio" value="1" checked>
                                                                        <label class="form-check-label" for="actividad-unidad-1-2-obligatorio">Actividad obligatoria</label>
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuizQuestion(this, 0, 2)">
                                                                        <i class="fas fa-plus"></i> Añadir pregunta
                                                                    </button>
                                                                </div>
                                                                <div class="quiz-questions mt-3"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón para añadir nueva unidad -->
                                    <button type="button" class="btn btn-light w-100 border mb-4" onclick="addUnidad()">
                                        <i class="fas fa-plus me-1"></i> Añadir Unidad
                                    </button>
                                </div>
                            </div>

                            <!-- Course Settings -->
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label for="coursePrice" class="form-label">Precio</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="coursePrice" name="precio"
                                            step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label for="courseDuration" class="form-label">Duración (horas)</label>
                                    <input type="number" class="form-control" id="courseDuration" name="duracion" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label for="courseLevel" class="form-label">Nivel</label>
                                    <select class="form-select" id="courseLevel" name="nivel">
                                        <option selected>Principiante</option>
                                        <option>Intermedio</option>
                                        <option>Avanzado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Visibility Settings -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Visibilidad del Curso</h5>
                                            <p class="text-muted small mb-0">Controla quién puede ver este curso</p>
                                        </div>
                                        <div class="d-flex">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="radio" name="estado"
                                                    id="draftOption" value="borrador" checked>
                                                <label class="form-check-label" for="draftOption">
                                                    Borrador
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="estado"
                                                    id="publishedOption" value="publicado">
                                                <label class="form-check-label" for="publishedOption">
                                                    Publicado
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-light me-2" onclick="cancelForm()">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-orange">
                                    Guardar Curso
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Tab -->
            <div class="tab-pane fade" id="estadisticas">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Estadísticas de Cursos</h4>
                        <div class="alert alert-info">
                            No hay datos de estadísticas disponibles aún. Serán visibles cuando tengas cursos publicados
                            con estudiantes.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Bootstrap & jQuery Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="js/crear_curso.js"></script>
</body>
</html>