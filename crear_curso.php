<?php
session_start();
require_once 'roles/auth.php';
require_once 'conexion/db.php'; // Asegúrate de que este archivo se encargue de la conexión PDO

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
    include 'navbar.php';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Cursos - SkillBoots</title>
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- Font Awesome para iconos -->
    <link href="css/crear_curso.css" rel="stylesheet">
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> -->
</head>

<body>
    <!-- Admin Header -->
    <header class="bg-white shadow-sm">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold text-dark mb-0">Panel de Administración</h1>
                <div class="d-flex align-items-center">
                    <span class="me-3" style="color: var(--primary); ">Bienvenido, <?php echo htmlspecialchars($userName); ?></span>
                    <a href="./holaaaa.php" class="btn btn-sm btn-outline-secondary" style="background-color: var(--primary);">Regresar</a>
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

                try {
                    // Asegúrate de que la sesión esté iniciada
                    $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

                    if ($usuario_id === 0) {
                        echo '<div class="col-12"><div class="alert alert-warning">Por favor inicie sesión para ver sus cursos.</div></div>';
                    } else {
                        // Consulta SQL para obtener los cursos del profesor
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
                                    END AS colorProgreso,
                                    (SELECT COUNT(*) FROM usuarios_cursos WHERE curso_id = c.id) AS total_estudiantes
                                FROM
                                    cursos c
                                LEFT JOIN
                                    usuarios_cursos uc ON c.id = uc.curso_id AND uc.usuario_id = :usuario_id
                                WHERE
                                    c.instructor_id = :instructor_id
                                ORDER BY
                                    COALESCE(uc.ultimo_acceso, '1900-01-01') DESC";

                        // Preparar la consulta
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                        $stmt->bindParam(':instructor_id', $usuario_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Mostrar cursos
                        if (empty($cursos)) {
                            echo '<div class="col-12"><div class="alert alert-info">No tienes cursos creados. ¡Comienza creando uno nuevo!</div></div>';
                        } else {
                            foreach ($cursos as $curso) {
                                ?>
                                <div class="col mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <img src="<?= htmlspecialchars($curso['imagen'] ?: 'uploads/default-course.jpg') ?>" class="card-img-top" alt="Course thumbnail"
                                            style="height: 180px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($curso['titulo']) ?></h5>
                                            <p class="card-text text-muted"><?= htmlspecialchars(substr($curso['descripcion'], 0, 100)) . (strlen($curso['descripcion']) > 100 ? '...' : '') ?></p>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">Estado:
                                                    <span class="badge bg-<?= $curso['estado'] === 'borrador' ? 'warning' : ($curso['estado'] === 'publicado' ? 'success' : 'danger') ?>"><?= htmlspecialchars($curso['estado']) ?></span>
                                                </small>
                                                <small class="text-muted">Estudiantes: <?= $curso['total_estudiantes'] ?></small>
                                            </div>
                                            <div class="d-flex justify-content-between mt-2">
                                                <a href="editar-curso.php?id=<?= $curso['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit me-1"></i> Editar
                                                </a>
                                                <a href="ver-cursos.php?id=<?= $curso['id'] ?>" style="background-color: var(--success);" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-eye me-1"></i> Ver
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                } catch (PDOException $e) {
                    echo '<div class="col-12"><div class="alert alert-danger">Error al cargar los cursos: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
                }
                ?>

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
                        <form id="courseForm" action="crear-curso.php" method="POST" enctype="multipart/form-data">
                            <!-- Campo oculto para instructor_id -->
                            <input type="hidden" name="instructor_id" value="<?php echo $usuario_id; ?>">
                            <input type="hidden" name="action" value="create">

                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="courseTitle" class="form-label">Título del Curso <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="courseTitle" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="courseCategory" class="form-label">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-select" id="courseCategory" name="categoria" required>
                                        <option value="" selected disabled>Seleccione una categoría</option>
                                        <option value="Desarrollo Web">Desarrollo Web</option>
                                        <option value="Diseño Gráfico">Diseño Gráfico</option>
                                        <option value="Marketing Digital">Marketing Digital</option>
                                        <option value="Programación">Programación</option>
                                        <option value="Idiomas">Idiomas</option>
                                        <option value="Negocios">Negocios</option>
                                        <option value="Ciencias">Ciencias</option>
                                        <option value="Artes">Artes</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Course Content -->
                            <div class="mb-4">
                                <label for="courseDescription" class="form-label">Descripción del Curso <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="courseDescription" name="descripcion" rows="4" required></textarea>
                                <small class="text-muted">Escribe una descripción clara y atractiva de tu curso. Incluye lo que aprenderán los estudiantes.</small>
                            </div>

                            <!-- Imagen del curso -->
                            <div class="mb-4">
                                <label for="courseImage" class="form-label">Imagen del Curso <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="courseImage" name="imagen" accept="image/*" required>
                                    <button class="btn btn-outline-secondary" type="button" id="previewImageBtn">Vista previa</button>
                                </div>
                                <small class="text-muted">Tamaño recomendado: 1200 x 800 píxeles. Formatos: JPG, PNG.</small>
                                <div id="imagePreview" class="mt-2" style="display:none;">
                                    <img src="" alt="Vista previa" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>

                            <!-- NUEVA ESTRUCTURA DEL CURSO -->
                            <div class="mb-4">
                                <h5 class="mb-3">Estructura del Curso</h5>
                                <div id="courseStructureContainer">
                                    <!-- Sección de actividades de presentación -->
                                    <div class="course-section mb-3">
                                        <div class="course-section-header d-flex justify-content-between align-items-center bg-light p-3 rounded" data-bs-toggle="collapse" href="#presentacion-content" role="button" aria-expanded="true" aria-controls="presentacion-content">
                                            <span><i class="fas fa-chevron-down me-2"></i>Actividades de presentación</span>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="addElement('presentacion', event)">
                                                <i class="fas fa-plus me-1"></i> Añadir elemento
                                            </button>
                                        </div>
                                        <div class="course-section-content collapse show" id="presentacion-content">
                                            <div class="card card-body border rounded mb-2">
                                                <!-- Elementos de presentación -->
                                                <div class="activity-item p-3 border rounded mb-2">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
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
                                                                <option value="archivo">Archivo</option>
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeElement(this)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="content-details w-100 mt-3 p-3 bg-light rounded" style="display: none;">
                                                        <div class="mb-3">
                                                            <label class="form-label">Descripción:</label>
                                                            <textarea class="form-control" name="presentacion[0][contenido]" rows="3" placeholder="Descripción y detalles del contenido..."></textarea>
                                                        </div>

                                                        <!-- Contenedor para diferentes tipos de contenido -->
                                                        <div class="content-type-container">
                                                            <!-- Contenedor para el foro -->
                                                            <div class="foro-container content-specific-fields">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tema de discusión:</label>
                                                                    <textarea class="form-control" name="presentacion[0][foro_tema]" rows="2"></textarea>
                                                                </div>
                                                                <div class="form-check mb-3">
                                                                    <input class="form-check-input" type="checkbox" name="presentacion[0][foro_calificable]" id="foro-calificable-0" value="1">
                                                                    <label class="form-check-label" for="foro-calificable-0">Foro calificable</label>
                                                                </div>
                                                            </div>

                                                            <!-- Contenedor para el cuestionario -->
                                                            <div class="cuestionario-container content-specific-fields" style="display: none;">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Instrucciones:</label>
                                                                    <textarea class="form-control" name="presentacion[0][cuestionario_instrucciones]" rows="2"></textarea>
                                                                </div>
                                                                <div class="quiz-questions mb-3">
                                                                    <div class="quiz-question p-3 border rounded mb-2">
                                                                        <div class="mb-2">
                                                                            <label class="form-label">Pregunta:</label>
                                                                            <input type="text" class="form-control" name="presentacion[0][preguntas][0][texto]">
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <label class="form-label">Tipo:</label>
                                                                            <select class="form-select question-type" name="presentacion[0][preguntas][0][tipo]" onchange="toggleQuestionOptions(this)">
                                                                                <option value="text">Texto</option>
                                                                                <option value="multiple">Opción múltiple</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="question-options" style="display: none;">
                                                                            <label class="form-label">Opciones:</label>
                                                                            <div class="options-container">
                                                                                <div class="option-item d-flex mb-2">
                                                                                    <input type="radio" name="presentacion[0][preguntas][0][correcta]" value="0" class="me-2">
                                                                                    <input type="text" class="form-control form-control-sm" name="presentacion[0][preguntas][0][opciones][]" placeholder="Opción">
                                                                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeOption(this)">
                                                                                        <i class="fas fa-times"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption(this)">
                                                                                <i class="fas fa-plus"></i> Añadir opción
                                                                            </button>
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeQuestion(this)">
                                                                            <i class="fas fa-trash"></i> Eliminar pregunta
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuestion(this, 'presentacion', 0)">
                                                                    <i class="fas fa-plus"></i> Añadir pregunta
                                                                </button>
                                                            </div>

                                                            <!-- Contenedor para enlaces -->
                                                            <div class="enlace-container content-specific-fields" style="display: none;">
                                                                <div class="mb-3">
                                                                    <label class="form-label">URL del enlace:</label>
                                                                    <input type="url" class="form-control" name="presentacion[0][enlace_url]" placeholder="https://">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nombre visible:</label>
                                                                    <input type="text" class="form-control" name="presentacion[0][enlace_nombre]">
                                                                </div>
                                                            </div>

                                                            <!-- Contenedor para archivos -->
                                                            <div class="archivo-container content-specific-fields" style="display: none;">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Archivo:</label>
                                                                    <input type="file" class="form-control" name="presentacion[0][archivo_file]">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Descripción del archivo:</label>
                                                                    <textarea class="form-control" name="presentacion[0][archivo_descripcion]" rows="2"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" name="presentacion[0][obligatorio]" id="elem-obligatorio-0" value="1">
                                                                <label class="form-check-label" for="elem-obligatorio-0">Obligatorio</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contenedor para las unidades -->
                                    <div id="unidadesContainer">
                                        <!-- Unidad 1 (por defecto) -->
                                        <div class="course-section mb-3" id="unidad-1">
                                            <div class="course-section-header d-flex justify-content-between align-items-center bg-light p-3 rounded" data-bs-toggle="collapse" href="#unidad-1-content" role="button" aria-expanded="true" aria-controls="unidad-1-content">
                                                <span>
                                                    <i class="fas fa-chevron-down me-2"></i>
                                                    <input type="text" class="form-control-plaintext fw-bold" name="unidades[0][titulo]" value="Unidad 1" style="display: inline-block; width: auto;">
                                                </span>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="addSection('unidad-1', 'recursos', event)">
                                                        <i class="fas fa-plus-circle me-1"></i> Recurso
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="addSection('unidad-1', 'actividades', event)">
                                                        <i class="fas fa-plus-circle me-1"></i> Actividad
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="course-section-content collapse show" id="unidad-1-content">
                                                <div class="card card-body border-0">
                                                    <!-- Descripción de la unidad -->
                                                    <div class="mb-3">
                                                        <label class="form-label">Descripción de la unidad:</label>
                                                        <textarea class="form-control" name="unidades[0][descripcion]" placeholder="Descripción de la unidad" rows="2">Introducción a los conceptos básicos</textarea>
                                                    </div>

                                                    <!-- Recursos Didácticos -->
                                                    <div class="mb-4">
                                                        <h6 class="section-title fw-bold mb-3">RECURSOS DIDÁCTICOS</h6>
                                                        <div id="unidad-1-recursos">
                                                            <div class="resource-item p-3 border rounded mb-2">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                                    <div>
                                                                        <span class="circle-indicator green-indicator"></span>
                                                                        <input type="text" class="form-control-plaintext" name="unidades[0][recursos][0][titulo]" value="Material de lectura" style="display: inline-block; width: auto;">
                                                                        <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                                                                            <i class="fas fa-edit"></i> Contenido
                                                                        </button>
                                                                    </div>
                                                                    <div>
                                                                        <select class="form-select form-select-sm resource-type" name="unidades[0][recursos][0][tipo]" style="width: auto; display: inline-block;" onchange="toggleResourceType(this)">
                                                                            <option value="archivo" selected>Archivo</option>
                                                                            <option value="enlace">Enlace</option>
                                                                            <option value="video">Video</option>
                                                                            <option value="texto">Texto enriquecido</option>
                                                                        </select>
                                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeElement(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="content-details w-100 mt-3 p-3 bg-light rounded" style="display: none;">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Descripción:</label>
                                                                        <textarea class="form-control" name="unidades[0][recursos][0][descripcion]" rows="2" placeholder="Descripción del recurso"></textarea>
                                                                    </div>

                                                                    <!-- Contenedores para tipos específicos de recursos -->
                                                                    <div class="resource-type-container">
                                                                        <!-- Contenedor para archivos -->
                                                                        <div class="archivo-resource-container resource-specific-fields">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Archivo:</label>
                                                                                <input type="file" class="form-control" name="unidades[0][recursos][0][archivo]">
                                                                                <small class="text-muted">Formatos permitidos: PDF, DOCX, PPTX, XLS, ZIP (máx. 50MB)</small>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Contenedor para enlaces -->
                                                                        <div class="enlace-resource-container resource-specific-fields" style="display: none;">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">URL del enlace:</label>
                                                                                <input type="url" class="form-control" name="unidades[0][recursos][0][enlace_url]" placeholder="https://">
                                                                            </div>
                                                                            <div class="form-check mb-3">
                                                                                <input class="form-check-input" type="checkbox" name="unidades[0][recursos][0][enlace_nueva_ventana]" id="enlace-nueva-ventana-0-0" value="1" checked>
                                                                                <label class="form-check-label" for="enlace-nueva-ventana-0-0">Abrir en nueva ventana</label>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Contenedor para videos -->
                                                                        <div class="video-resource-container resource-specific-fields" style="display: none;">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Tipo de video:</label>
                                                                                <select class="form-select video-source" name="unidades[0][recursos][0][video_tipo]" onchange="toggleVideoSource(this)">
                                                                                    <option value="youtube">YouTube</option>
                                                                                    <option value="vimeo">Vimeo</option>
                                                                                    <option value="archivo">Archivo de video</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="youtube-container video-source-container">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">ID o URL de YouTube:</label>
                                                                                    <input type="text" class="form-control" name="unidades[0][recursos][0][video_url]" placeholder="https://www.youtube.com/watch?v=XXXX">
                                                                                    <small class="text-muted">Ejemplo: https://www.youtube.com/watch?v=dQw4w9WgXcQ</small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="vimeo-container video-source-container" style="display: none;">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">ID o URL de Vimeo:</label>
                                                                                    <input type="text" class="form-control" name="unidades[0][recursos][0][video_url]" placeholder="https://vimeo.com/XXXX">
                                                                                    <small class="text-muted">Ejemplo: https://vimeo.com/24715531</small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="archivo-video-container video-source-container" style="display: none;">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">Archivo de video:</label>
                                                                                    <input type="file" class="form-control" name="unidades[0][recursos][0][video_archivo]" accept="video/*">
                                                                                    <small class="text-muted">Formatos permitidos: MP4, MOV, AVI (máx. 100MB)</small>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Contenedor para texto enriquecido -->
                                                                        <div class="texto-resource-container resource-specific-fields" style="display: none;">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Contenido:</label>
                                                                                <textarea class="form-control" name="unidades[0][recursos][0][texto_contenido]" rows="4"></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mt-3">
                                                                        <div class="form-check mb-2">
                                                                            <input class="form-check-input" type="checkbox" name="unidades[0][recursos][0][obligatorio]" id="recurso-obligatorio-0-0" value="1">
                                                                            <label class="form-check-label" for="recurso-obligatorio-0-0">Recurso obligatorio</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Actividades -->
                                                    <div class="mb-4">
                                                        <h6 class="section-title fw-bold mb-3">ACTIVIDADES</h6>
                                                        <div id="unidad-1-actividades">
                                                            <div class="activity-item p-3 border rounded mb-2">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                                    <div>
                                                                        <span class="circle-indicator gray-indicator"></span>
                                                                        <input type="text" class="form-control-plaintext" name="unidades[0][actividades][0][titulo]" value="Tarea de la unidad" style="display: inline-block; width: auto;">
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
                                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeElement(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="content-details w-100 mt-3 p-3 bg-light rounded" style="display: none;">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Descripción:</label>
                                                                        <textarea class="form-control" name="unidades[0][actividades][0][contenido]" rows="3" placeholder="Instrucciones para la actividad..."></textarea>
                                                                    </div>
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
                                                                    <div class="mt-3">
                                                                        <div class="form-check mb-2">
                                                                            <input class="form-check-input" type="checkbox" name="unidades[0][actividades][0][obligatorio]" id="actividad-obligatorio-0-0" value="1" checked>
                                                                            <label class="form-check-label" for="actividad-obligatorio-0-0">Actividad obligatoria</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
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
                                    <label for="coursePrice" class="form-label">Precio <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="coursePrice" name="precio" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label for="courseDuration" class="form-label">Duración (horas) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="courseDuration" name="duracion" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="courseLevel" class="form-label">Nivel <span class="text-danger">*</span></label>
                                    <select class="form-select" id="courseLevel" name="nivel" required>
                                        <option value="" selected disabled>Seleccione un nivel</option>
                                        <option value="Principiante">Principiante</option>
                                        <option value="Intermedio">Intermedio</option>
                                        <option value="Avanzado">Avanzado</option>
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
                                                <input class="form-check-input" type="radio" name="estado" id="draftOption" value="borrador" checked>
                                                <label class="form-check-label" for="draftOption">
                                                    Borrador
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="estado" id="publishedOption" value="publicado">
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
                                <button type="submit" class="btn btn-primary">
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
                            No hay datos de estadísticas disponibles aún. Serán visibles cuando tengas cursos publicados con estudiantes.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // Variables para manejar el conteo de elementos
let unidadCounter = 1;
let presentacionCounter = 3; // Empezamos con 3 elementos de presentación
let recursosCounter = {};
let actividadesCounter = {};

// Inicializar contadores para la primera unidad
recursosCounter['unidad-1'] = 3;
actividadesCounter['unidad-1'] = 3;

// Función para mostrar una alerta
function showAlert(message, type = 'danger') {
    const alertContainer = document.getElementById('alertContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertContainer.appendChild(alertDiv);
}

// Función para mostrar/ocultar una sección
function toggleSection(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const icon = document.querySelector(`#${sectionId} .fa-chevron-down, #${sectionId} .fa-chevron-right`);

    if (content) {
        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            if (icon) icon.className = 'fas fa-chevron-down me-2';
        } else {
            content.style.display = 'none';
            if (icon) icon.className = 'fas fa-chevron-right me-2';
        }
    }
}

// Función para añadir un elemento a una sección (elementos de presentación)
function addElement(sectionId, event) {
    event.stopPropagation(); // Evitar que se cierre la sección

    const container = document.getElementById(sectionId + '-content');
    const elementCount = sectionId === 'presentacion' ? presentacionCounter++ : 0;

    const newElement = document.createElement('div');
    newElement.className = 'activity-item d-flex justify-content-between align-items-center flex-wrap';

    newElement.innerHTML = `
        <div>
            <span class="circle-indicator gray-indicator"></span>
            <input type="text" class="form-control-plaintext" name="${sectionId}[${elementCount}][titulo]" value="Nuevo elemento" style="display: inline-block; width: auto;">
            <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                <i class="fas fa-edit"></i> Contenido
            </button>
        </div>
        <div>
            <select class="form-select form-select-sm tipo-selector" name="${sectionId}[${elementCount}][tipo]" style="width: auto; display: inline-block;" onchange="updateContentByType(this)">
                <option value="foro">Foro</option>
                <option value="cuestionario">Cuestionario</option>
                <option value="enlace">Enlace</option>
            </select>
            <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="content-details w-100 mt-2" style="display: none;">
            <div class="common-content mb-3">
                <textarea class="form-control" name="${sectionId}[${elementCount}][descripcion]" rows="3" placeholder="Descripción general..."></textarea>
            </div>

            <!-- Contenedor para el foro -->
            <div class="tipo-content foro-content" style="display: none;">
                <div class="mb-3">
                    <label for="foro-descripcion-${elementCount}" class="form-label">Descripción del Foro:</label>
                    <textarea class="form-control" id="foro-descripcion-${elementCount}" name="${sectionId}[${elementCount}][foro_descripcion]" rows="3" placeholder="Instrucciones y tema para la discusión..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="foro-criterios-${elementCount}" class="form-label">Criterios de evaluación:</label>
                    <textarea class="form-control" id="foro-criterios-${elementCount}" name="${sectionId}[${elementCount}][foro_criterios]" rows="2" placeholder="Criterios para evaluar las participaciones..."></textarea>
                </div>
            </div>

            <!-- Contenedor para el cuestionario -->
            <div class="tipo-content cuestionario-content" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">Preguntas del cuestionario:</label>
                    <div class="quiz-questions-container">
                        <!-- Aquí se agregarán dinámicamente las preguntas -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addQuizQuestionToPresentacion(this, '${sectionId}', ${elementCount})">
                        <i class="fas fa-plus"></i> Añadir pregunta
                    </button>
                </div>
            </div>

            <!-- Contenedor para el enlace -->
            <div class="tipo-content enlace-content" style="display: none;">
                <div class="mb-3">
                    <label for="enlace-url-${elementCount}" class="form-label">URL del enlace:</label>
                    <input type="url" class="form-control" id="enlace-url-${elementCount}" name="${sectionId}[${elementCount}][enlace_url]" placeholder="https://ejemplo.com">
                </div>
                <div class="mb-3">
                    <label for="enlace-tipo-${elementCount}" class="form-label">Tipo de recurso:</label>
                    <select class="form-control" id="enlace-tipo-${elementCount}" name="${sectionId}[${elementCount}][enlace_tipo]">
                        <option value="pagina">Página web</option>
                        <option value="documento">Documento</option>
                        <option value="video">Video</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </div>
        </div>
    `;

     // Después de añadir el elemento al contenedor:
     const newSelector = newElement.querySelector('.tipo-selector');
     if (newSelector) {
         // Asegúrate de que el evento onchange funcione correctamente
         newSelector.addEventListener('change', function() {
             updateContentByType(this);
         });
     }

     container.appendChild(newElement);
}

// Función para añadir una nueva unidad
function addUnidad() {
    unidadCounter++;
    const unidadId = `unidad-${unidadCounter}`;

    // Inicializar contadores para esta nueva unidad
    recursosCounter[unidadId] = 0;
    actividadesCounter[unidadId] = 0;

    const unidadHtml = `
        <div class="course-section mb-3" id="${unidadId}">
            <div class="course-section-header" onclick="toggleSection('${unidadId}')">
                <span>
                    <i class="fas fa-chevron-down me-2"></i>
                    <input type="text" class="form-control-plaintext" name="unidades[${unidadCounter-1}][titulo]" value="Unidad ${unidadCounter}" style="display: inline-block; width: auto; font-weight: bold;">
                </span>
                <div>
                    <button type="button" class="btn btn-sm text-orange" onclick="addSection('${unidadId}', 'recursos', event)">
                        <i class="fas fa-plus-circle me-1"></i> Recurso
                    </button>
                    <button type="button" class="btn btn-sm text-primary" onclick="addSection('${unidadId}', 'actividades', event)">
                        <i class="fas fa-plus-circle me-1"></i> Actividad
                    </button>
                </div>
            </div>
            <div class="course-section-content" id="${unidadId}-content">
                <!-- Descripción de la unidad -->
                <div class="mb-3">
                    <input type="text" class="form-control" name="unidades[${unidadCounter-1}][descripcion]" placeholder="Descripción de la unidad">
                </div>

                <!-- Recursos Didácticos -->
                <div class="mb-3">
                    <h6 class="section-title">RECURSOS DIDÁCTICOS</h6>
                    <div id="${unidadId}-recursos">
                        <!-- Los recursos se añadirán dinámicamente -->
                    </div>
                </div>

                <!-- Actividades -->
                <div class="mb-3">
                    <h6 class="section-title">ACTIVIDADES</h6>
                    <div id="${unidadId}-actividades">
                        <!-- Las actividades se añadirán dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('unidadesContainer').insertAdjacentHTML('beforeend', unidadHtml);
}

// Función para añadir un recurso o actividad a una unidad
function addSection(unidadId, tipo, event) {
    event.stopPropagation(); // Evitar que se cierre la sección

    const container = document.getElementById(`${unidadId}-${tipo}`);
    const counterKey = unidadId;
    const unidadIndex = parseInt(unidadId.split('-')[1]) - 1;

    let elementCount = 0;
    if (tipo === 'recursos') {
        elementCount = recursosCounter[counterKey]++;
    } else if (tipo === 'actividades') {
        elementCount = actividadesCounter[counterKey]++;
    }

    const newElement = document.createElement('div');

    if (tipo === 'recursos') {
        newElement.className = 'resource-item d-flex justify-content-between align-items-center flex-wrap';
        newElement.innerHTML = `
            <div>
                <span class="circle-indicator gray-indicator"></span>
                <input type="text" class="form-control-plaintext" name="unidades[${unidadIndex}][recursos][${elementCount}][titulo]" value="Nuevo recurso" style="display: inline-block; width: auto;">
                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                    <i class="fas fa-edit"></i> Contenido
                </button>
            </div>
            <div>
                <select class="form-select form-select-sm tipo-selector" name="unidades[${unidadIndex}][recursos][${elementCount}][tipo]" style="width: auto; display: inline-block;" onchange="updateContentByType(this)">
                    <option value="archivo" selected>Archivo</option>
                    <option value="enlace">Enlace</option>
                    <option value="video">Video</option>
                    <option value="texto">Texto</option>
                </select>
                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="content-details w-100 mt-2" style="display: none;">
                <div class="common-content mb-3">
                    <textarea class="form-control" name="unidades[${unidadIndex}][recursos][${elementCount}][descripcion]" rows="3" placeholder="Descripción del recurso..."></textarea>
                </div>

                <!-- Contenedor específico para archivo -->
                <div class="tipo-content archivo-content">
                    <div class="mb-3">
                        <label class="form-label">Subir archivo:</label>
                        <input type="file" class="form-control" name="unidades[${unidadIndex}][recursos][${elementCount}][archivo]">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][recursos][${elementCount}][obligatorio]" id="recurso-${unidadId}-${elementCount}-obligatorio" value="1">
                        <label class="form-check-label" for="recurso-${unidadId}-${elementCount}-obligatorio">Recurso obligatorio</label>
                    </div>
                </div>

                <!-- Contenedor específico para enlace -->
                <div class="tipo-content enlace-content" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">URL del enlace:</label>
                        <input type="url" class="form-control" name="unidades[${unidadIndex}][recursos][${elementCount}][url]" placeholder="https://ejemplo.com">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][recursos][${elementCount}][obligatorio]" id="recurso-enlace-${unidadId}-${elementCount}-obligatorio" value="1">
                        <label class="form-check-label" for="recurso-enlace-${unidadId}-${elementCount}-obligatorio">Recurso obligatorio</label>
                    </div>
                </div>

                <!-- Contenedor específico para video -->
                <div class="tipo-content video-content" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">URL del video:</label>
                        <input type="url" class="form-control mb-2" name="unidades[${unidadIndex}][recursos][${elementCount}][video_url]" placeholder="https://youtube.com/..." >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">O subir archivo de video:</label>
                        <input type="file" class="form-control" name="unidades[${unidadIndex}][recursos][${elementCount}][video_archivo]" accept="video/*">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][recursos][${elementCount}][obligatorio]" id="recurso-video-${unidadId}-${elementCount}-obligatorio" value="1">
                        <label class="form-check-label" for="recurso-video-${unidadId}-${elementCount}-obligatorio">Recurso obligatorio</label>
                    </div>
                </div>

                <!-- Contenedor específico para texto -->
                <div class="tipo-content texto-content" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Contenido de texto:</label>
                        <textarea class="form-control" name="unidades[${unidadIndex}][recursos][${elementCount}][texto_contenido]" rows="5" placeholder="Escriba el contenido de texto aquí..."></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][recursos][${elementCount}][obligatorio]" id="recurso-texto-${unidadId}-${elementCount}-obligatorio" value="1">
                        <label class="form-check-label" for="recurso-texto-${unidadId}-${elementCount}-obligatorio">Recurso obligatorio</label>
                    </div>
                </div>
            </div>
        `;
    } else if (tipo === 'actividades') {
        newElement.className = 'activity-item d-flex justify-content-between align-items-center flex-wrap';
        newElement.innerHTML = `
            <div>
                <span class="circle-indicator gray-indicator"></span>
                <input type="text" class="form-control-plaintext" name="unidades[${unidadIndex}][actividades][${elementCount}][titulo]" value="Nueva actividad" style="display: inline-block; width: auto;">
                <button type="button" class="btn btn-sm btn-link" onclick="toggleContent(this)">
                    <i class="fas fa-edit"></i> Contenido
                </button>
            </div>
            <div>
                <select class="form-select form-select-sm tipo-selector" name="unidades[${unidadIndex}][actividades][${elementCount}][tipo]" style="width: auto; display: inline-block;" onchange="updateContentByType(this)">
                    <option value="tarea" selected>Tarea</option>
                    <option value="quiz">Quiz</option>
                    <option value="foro">Foro</option>
                </select>
                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="content-details w-100 mt-2" style="display: none;">
                <div class="common-content mb-3">
                    <textarea class="form-control mb-2" name="unidades[${unidadIndex}][actividades][${elementCount}][descripcion]" rows="3" placeholder="Descripción general de la actividad..."></textarea>
                </div>

                <!-- Configuración común para todas las actividades -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Puntuación:</label>
                        <input type="number" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][puntuacion]" min="0" value="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha límite:</label>
                        <input type="date" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][fecha_limite]">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tiempo (min):</label>
                        <input type="number" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][tiempo]" min="0">
                    </div>
                </div>

                <!-- Contenedor para tarea -->
                <div class="tipo-content tarea-content">
                    <div class="mb-3">
                        <label class="form-label">Instrucciones de la tarea:</label>
                        <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][tarea_instrucciones]" rows="3" placeholder="Instrucciones detalladas para realizar la tarea..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Archivos de apoyo:</label>
                        <input type="file" class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][tarea_archivos]" multiple>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][actividades][${elementCount}][obligatorio]" id="actividad-tarea-${unidadId}-${elementCount}-obligatorio" value="1" checked>
                        <label class="form-check-label" for="actividad-tarea-${unidadId}-${elementCount}-obligatorio">Actividad obligatoria</label>
                    </div>
                </div>

                <!-- Contenedor para quiz -->
                <div class="tipo-content quiz-content" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Preguntas del quiz:</label>
                        <div class="quiz-questions mb-2">
                            <!-- Las preguntas se añadirán dinámicamente -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuizQuestion(this, ${unidadIndex}, ${elementCount})">
                            <i class="fas fa-plus"></i> Añadir pregunta
                        </button>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][actividades][${elementCount}][obligatorio]" id="actividad-quiz-${unidadId}-${elementCount}-obligatorio" value="1" checked>
                        <label class="form-check-label" for="actividad-quiz-${unidadId}-${elementCount}-obligatorio">Actividad obligatoria</label>
                    </div>
                </div>

                <!-- Contenedor para foro -->
                <div class="tipo-content foro-content" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Tema del foro:</label>
                        <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][foro_tema]" rows="2" placeholder="Tema central para la discusión..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instrucciones para la participación:</label>
                        <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][foro_instrucciones]" rows="3" placeholder="Instrucciones detalladas para participar en el foro..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Criterios de evaluación:</label>
                        <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${elementCount}][foro_criterios]" rows="2" placeholder="Criterios para evaluar las participaciones..."></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="unidades[${unidadIndex}][actividades][${elementCount}][obligatorio]" id="actividad-foro-${unidadId}-${elementCount}-obligatorio" value="1" checked>
                        <label class="form-check-label" for="actividad-foro-${unidadId}-${elementCount}-obligatorio">Actividad obligatoria</label>
                    </div>
                </div>
            </div>
        `;
    }

// Después de crear el nuevo elemento:
const newSelector = newElement.querySelector('.tipo-selector');
if (newSelector) {
    // Asegúrate de que el evento onchange funcione correctamente
    newSelector.addEventListener('change', function() {
        updateContentByType(this);
    });
}

container.appendChild(newElement);}

// Función para eliminar un elemento
function removeElement(button) {
    const itemElement = button.closest('.resource-item, .activity-item');
    if (itemElement) {
        itemElement.remove();
    }
}

// Función para mostrar la pestaña de crear curso
function showCreateCourseTab() {
    const tabElement = document.querySelector('a[href="#crearCurso"]');
    const bsTab = new bootstrap.Tab(tabElement);
    bsTab.show();
}

// Función para cancelar el formulario
function cancelForm() {
    document.getElementById('courseForm').reset();
    const tabElement = document.querySelector('a[href="#misCursos"]');
    const bsTab = new bootstrap.Tab(tabElement);
    bsTab.show();
}

// Función para mostrar/ocultar el panel de contenido
function toggleContent(button) {
    const itemElement = button.closest('.resource-item, .activity-item');
    const contentPanel = itemElement.querySelector('.content-details');

    if (contentPanel) {
        if (contentPanel.style.display === 'none' || contentPanel.style.display === '') {
            contentPanel.style.display = 'block';
            // Inicializar la vista según el tipo seleccionado actualmente
            const tipoSelect = itemElement.querySelector('.tipo-selector');
            if (tipoSelect) {
                updateContentByType(tipoSelect);
            }
        } else {
            contentPanel.style.display = 'none';
        }
    }
}

// Modificar la función updateContentByType para asegurar consistencia
function updateContentByType(selectElement) {
    const itemElement = selectElement.closest('.resource-item, .activity-item');
    if (!itemElement) return; // Verificación de seguridad

    const contentPanel = itemElement.querySelector('.content-details');
    const tipoSeleccionado = selectElement.value;

    if (!contentPanel) return;

    // Obtener todos los contenedores de tipo específico
    const tipoContainers = contentPanel.querySelectorAll('.tipo-content');

    // Ocultar todos los contenedores específicos de tipo
    tipoContainers.forEach(container => {
        container.style.display = 'none';
    });

    // Mostrar el contenedor específico para el tipo seleccionado
    const tipoActivoContainer = contentPanel.querySelector(`.${tipoSeleccionado}-content`);
    if (tipoActivoContainer) {
        tipoActivoContainer.style.display = 'block';
    }
}

// Función para añadir una pregunta al cuestionario
function addQuizQuestion(button, unidadIndex, actividadIndex) {
    const questionContainer = button.closest('.content-details').querySelector('.quiz-questions');
    const questionCount = questionContainer.children.length;

    const questionElement = document.createElement('div');
    questionElement.className = 'quiz-question card mb-3';
    questionElement.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <span>Pregunta ${questionCount + 1}</span>
            <button type="button" class="btn btn-sm text-danger" onclick="removeQuizQuestion(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label">Pregunta:</label>
                <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][texto]" rows="2" placeholder="Texto de la pregunta...">
            </div>
            <div class="mb-2">
                <label class="form-label">Tipo:</label>
                <select class="form-select mb-2 pregunta-tipo-selector" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][tipo]" onchange="cambiarTipoPregunta(this, ${unidadIndex}, ${actividadIndex}, ${questionCount})">
                    <option value="opcion_multiple">Opción múltiple</option>
                    <option value="verdadero_falso">Verdadero/Falso</option>
                    <option value="texto_libre">Texto libre</option>
                </select>
            </div>
            <div class="opciones-container">
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][respuesta_correcta]" value="0" checked>
                    </div>
                    <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][opciones][0]" placeholder="Opción 1">
                </div>
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][respuesta_correcta]" value="1">
                    </div>
                    <input type="text" class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][opciones][1]" placeholder="Opción 2">
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addQuizOption(this, ${unidadIndex}, ${actividadIndex}, ${questionCount})">
                    <i class="fas fa-plus"></i> Añadir opción
                </button>
            </div>
        </div>
    `;

    questionContainer.appendChild(questionElement);
}

function addQuizQuestionToPresentacion(button, sectionId, elementCount) {
    const questionContainer = button.closest('.cuestionario-content').querySelector('.quiz-questions-container');
    const questionCount = questionContainer.children.length;

    const questionElement = document.createElement('div');
    questionElement.className = 'quiz-question card mb-3';
    questionElement.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <span>Pregunta ${questionCount + 1}</span>
            <button type="button" class="btn btn-sm text-danger" onclick="removeQuizQuestion(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label">Pregunta:</label>
                <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][texto]" rows="2" placeholder="Texto de la pregunta...">
            </div>
            <div class="mb-2">
                <label class="form-label">Tipo:</label>
                <select class="form-select mb-2 pregunta-tipo-selector" name="${sectionId}[${elementCount}][preguntas][${questionCount}][tipo]" onchange="cambiarTipoPregunta(this, '${sectionId}', ${elementCount}, ${questionCount})">
                    <option value="opcion_multiple">Opción múltiple</option>
                    <option value="verdadero_falso">Verdadero/Falso</option>
                    <option value="texto_libre">Texto libre</option>
                </select>
            </div>
            <div class="opciones-container">
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="${sectionId}[${elementCount}][preguntas][${questionCount}][respuesta_correcta]" value="0" checked>
                    </div>
                    <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][opciones][0]" placeholder="Opción 1">
                </div>
                <div class="opcion-item d-flex mb-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="radio" name="${sectionId}[${elementCount}][preguntas][${questionCount}][respuesta_correcta]" value="1">
                    </div>
                    <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][opciones][1]" placeholder="Opción 2">
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addQuizOption(this, '${sectionId}', ${elementCount}, ${questionCount})">
                    <i class="fas fa-plus"></i> Añadir opción
                </button>
            </div>
        </div>
    `;

    questionContainer.appendChild(questionElement);
}

// Función para cambiar el tipo de pregunta
function cambiarTipoPregunta(select, sectionId, elementCount, questionCount) {
    const questionContainer = select.closest('.quiz-question');
    const opcionesContainer = questionContainer.querySelector('.opciones-container');

    const tipo = select.value;

    if (tipo === 'opcion_multiple') {
        opcionesContainer.style.display = 'block';
    } else if (tipo === 'verdadero_falso') {
        // Reemplazar con opciones Verdadero/Falso
        opcionesContainer.innerHTML = `
            <div class="opcion-item d-flex mb-2">
                <div class="form-check me-2">
                    <input class="form-check-input" type="radio" name="${sectionId}[${elementCount}][preguntas][${questionCount}][respuesta_correcta]" value="0" checked>
                </div>
                <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][opciones][0]" value="Verdadero" readonly>
            </div>
            <div class="opcion-item d-flex mb-2">
                <div class="form-check me-2">
                    <input class="form-check-input" type="radio" name="${sectionId}[${elementCount}][preguntas][${questionCount}][respuesta_correcta]" value="1">
                </div>
                <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][opciones][1]" value="Falso" readonly>
            </div>
        `;
        opcionesContainer.style.display = 'block';
    } else if (tipo === 'texto_libre') {
        opcionesContainer.style.display = 'none';
    }
}

// Función para añadir una opción a una pregunta de cuestionario
function addQuizOption(button, sectionId, elementCount, questionCount) {
    const opcionesContainer = button.closest('.opciones-container');
    const opcionCount = opcionesContainer.querySelectorAll('.opcion-item').length;

    const opcionElement = document.createElement('div');
    opcionElement.className = 'opcion-item d-flex mb-2';
    opcionElement.innerHTML = `
        <div class="form-check me-2">
            <input class="form-check-input" type="radio" name="${sectionId}[${elementCount}][preguntas][${questionCount}][respuesta_correcta]" value="${opcionCount}">
        </div>
        <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][opciones][${opcionCount}]" placeholder="Opción ${opcionCount + 1}">
        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeQuizOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Insertar antes del botón
    opcionesContainer.insertBefore(opcionElement, button);
}

// Función para eliminar una opción de cuestionario
function removeQuizOption(button) {
    const opcionItem = button.closest('.opcion-item');
    if (opcionItem) {
        opcionItem.remove();
    }
}

// Función para alternar las fuentes de video
function toggleVideoSource(select) {
    const videoContainer = select.closest('.video-content');
    const sourceContainers = videoContainer.querySelectorAll('.video-source-container');

    // Ocultar todos los contenedores
    sourceContainers.forEach(container => {
        container.style.display = 'none';
    });

    // Mostrar el contenedor correspondiente al tipo seleccionado
    const tipo = select.value;
    const targetContainer = videoContainer.querySelector(`.${tipo}-container`);
    if (targetContainer) {
        targetContainer.style.display = 'block';
    }
}

// Función para añadir una pregunta (versión genérica)
function addQuestion(button, sectionId, elementCount) {
    const questionContainer = button.closest('.cuestionario-content').querySelector('.quiz-questions');
    const questionCount = questionContainer.children.length;

    const questionElement = document.createElement('div');
    questionElement.className = 'quiz-question p-3 border rounded mb-2';
    questionElement.innerHTML = `
        <div class="mb-2">
            <label class="form-label">Pregunta:</label>
            <input type="text" class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][texto]">
        </div>
        <div class="mb-2">
            <label class="form-label">Tipo:</label>
            <select class="form-select question-type" name="${sectionId}[${elementCount}][preguntas][${questionCount}][tipo]" onchange="toggleQuestionOptions(this)">
                <option value="text">Texto</option>
                <option value="multiple">Opción múltiple</option>
            </select>
        </div>
        <div class="question-options" style="display: none;">
            <label class="form-label">Opciones:</label>
            <div class="options-container">
                <div class="option-item d-flex mb-2">
                    <input type="radio" name="${sectionId}[${elementCount}][preguntas][${questionCount}][correcta]" value="0" class="me-2">
                    <input type="text" class="form-control form-control-sm" name="${sectionId}[${elementCount}][preguntas][${questionCount}][opciones][]" placeholder="Opción">
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeOption(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption(this)">
                <i class="fas fa-plus"></i> Añadir opción
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeQuestion(this)">
            <i class="fas fa-trash"></i> Eliminar pregunta
        </button>
    `;

    questionContainer.appendChild(questionElement);
}

// Función para eliminar una pregunta
function removeQuestion(button) {
    const questionElement = button.closest('.quiz-question');
    if (questionElement) {
        questionElement.remove();
    }
}

// Añade esta función al final del archivo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los selectores de tipo existentes al cargar la página
    const tipoSelectors = document.querySelectorAll('.tipo-selector');
    tipoSelectors.forEach(selector => {
        // Configurar el evento onchange para cada selector
        selector.addEventListener('change', function() {
            updateContentByType(this);
        });

        // Inicializar la vista según el tipo actual seleccionado
        updateContentByType(selector);
    });

    // Inicializar las secciones desplegables
    const sections = document.querySelectorAll('.course-section-header');
    sections.forEach(section => {
        section.addEventListener('click', function() {
            const sectionId = this.closest('.course-section').id;
            toggleSection(sectionId);
        });
    });
});
</script>
<!-- Bootstrap & jQuery Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/crear_curso.js"></script>
</body>
</html>

<?php

require_once 'conexion/db.php'; // Asegúrate de que este archivo se encargue de la conexión PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciamos una transacción para asegurar integridad
        $conn->beginTransaction();

        // Recuperar datos del curso
        $nombre = $_POST['nombre'];
        $categoria = $_POST['categoria'];
        $descripcion = $_POST['descripcion'];
        $precio = isset($_POST['precio']) ? $_POST['precio'] : 0;
        $duracion = isset($_POST['duracion']) ? $_POST['duracion'] : 0;
        $nivel = isset($_POST['nivel']) ? $_POST['nivel'] : 'Principiante';
        $estado = isset($_POST['estado']) ? $_POST['estado'] : 'borrador';
        $instructor_id = isset($_POST['instructor_id']) ? $_POST['instructor_id'] : 1;

        // Valores para campos adicionales en la tabla cursos
        $total_lecciones = 0; // Se puede actualizar después
        $horas_totales = isset($_POST['duracion']) ? $_POST['duracion'] : 0;
        $estate = 'activo'; // Por defecto activo

        // Manejar la subida de la imagen del curso
        $imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';

            // Crear el directorio si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imagen = $uploadDir . basename($_FILES['imagen']['name']);
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen)) {
                // La imagen se subió correctamente
            } else {
                throw new Exception("Error al subir la imagen.");
            }
        }

        // Insertar el curso en la base de datos
        $sql = "INSERT INTO cursos (nombre, categoria, descripcion, imagen, precio, duracion, nivel, estado,
                total_lecciones, horas_totales, instructor_id, estate, fecha_creacion)
                VALUES (:nombre, :categoria, :descripcion, :imagen, :precio, :duracion, :nivel, :estado,
                :total_lecciones, :horas_totales, :instructor_id, :estate, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':descripcion' => $descripcion,
            ':imagen' => $imagen,
            ':precio' => $precio,
            ':duracion' => $duracion,
            ':nivel' => $nivel,
            ':estado' => $estado,
            ':total_lecciones' => $total_lecciones,
            ':horas_totales' => $horas_totales,
            ':instructor_id' => $instructor_id,
            ':estate' => $estate
        ]);

        $curso_id = $conn->lastInsertId(); // Obtener el ID del curso recién insertado

        // Contadores para actualizar total_lecciones
        $contador_lecciones = 0;

        // Insertar módulos y actividades
        if (isset($_POST['unidades'])) {
            foreach ($_POST['unidades'] as $index => $unidad) {
                // Validación básica
                if (empty($unidad['titulo'])) {
                    continue; // Saltar este módulo si no tiene título
                }

                $titulo = $unidad['titulo'];
                $descripcion = isset($unidad['descripcion']) ? $unidad['descripcion'] : '';
                $orden = $index + 1; // Usar el índice como orden

                // Insertar en la tabla modulos (antes "unidades")
                $sql = "INSERT INTO modulos (curso_id, titulo, descripcion, orden)
                        VALUES (:curso_id, :titulo, :descripcion, :orden)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':curso_id' => $curso_id,
                    ':titulo' => $titulo,
                    ':descripcion' => $descripcion,
                    ':orden' => $orden
                ]);

                $modulo_id = $conn->lastInsertId(); // ID del módulo recién insertado

                // Insertar recursos
                if (isset($unidad['recursos']) && is_array($unidad['recursos'])) {
                    foreach ($unidad['recursos'] as $recurso) {
                        // Skip si no hay título
                        if (empty($recurso['titulo'])) {
                            continue;
                        }

                        $titulo_recurso = $recurso['titulo'];
                        $tipo_recurso = isset($recurso['tipo']) ? $recurso['tipo'] : 'texto';
                        $contenido_recurso = isset($recurso['contenido']) ? $recurso['contenido'] : '';
                        $obligatorio_recurso = isset($recurso['obligatorio']) ? 1 : 0;
                        $url_recurso = isset($recurso['url']) ? $recurso['url'] : '';
                        $texto_contenido = isset($recurso['texto_contenido']) ? $recurso['texto_contenido'] : '';

                        // En la tabla recursos, el campo se llama unidad_id pero guardamos el modulo_id
                        $sql = "INSERT INTO recursos (unidad_id, titulo, tipo, contenido, obligatorio, url,texto_contenido)
                                VALUES (:modulo_id, :titulo, :tipo, :contenido, :obligatorio, :url,:texto_contenido)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':modulo_id' => $modulo_id,
                            ':titulo' => $titulo_recurso,
                            ':tipo' => $tipo_recurso,
                            ':contenido' => $contenido_recurso,
                            ':obligatorio' => $obligatorio_recurso,
                            ':url' => $url_recurso,
                            ':texto_contenido' => $texto_contenido
                        ]);

                        $contador_lecciones++;
                    }
                }

                // Insertar actividades
                if (isset($unidad['actividades']) && is_array($unidad['actividades'])) {
                    foreach ($unidad['actividades'] as $actividad) {
                        // Skip si no hay título
                        if (empty($actividad['titulo'])) {
                            continue;
                        }

                        $titulo_actividad = $actividad['titulo'];
                        $tipo_actividad = isset($actividad['tipo']) ? $actividad['tipo'] : 'quiz';
                        $contenido_actividad = isset($actividad['contenido']) ? $actividad['contenido'] : '';
                        $puntuacion = isset($actividad['puntuacion']) ? $actividad['puntuacion'] : 0;
                        $fecha_limite = isset($actividad['fecha_limite']) && !empty($actividad['fecha_limite']) ? $actividad['fecha_limite'] : null;
                        $tiempo = isset($actividad['tiempo']) ? $actividad['tiempo'] : 0;
                        $obligatorio_actividad = isset($actividad['obligatorio']) ? 1 : 0;

                        // En la tabla actividades, el campo se llama unidad_id pero guardamos el modulo_id
                        $sql = "INSERT INTO actividades (unidad_id, titulo, tipo, contenido, puntuacion, fecha_limite, tiempo, obligatorio)
                                VALUES (:modulo_id, :titulo, :tipo, :contenido, :puntuacion, :fecha_limite, :tiempo, :obligatorio)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':modulo_id' => $modulo_id,
                            ':titulo' => $titulo_actividad,
                            ':tipo' => $tipo_actividad,
                            ':contenido' => $contenido_actividad,
                            ':puntuacion' => $puntuacion,
                            ':fecha_limite' => $fecha_limite,
                            ':tiempo' => $tiempo,
                            ':obligatorio' => $obligatorio_actividad
                        ]);

                        $actividad_id = $conn->lastInsertId(); // ID de la actividad recién insertada
                        $contador_lecciones++;

                        // Insertar preguntas y opciones
                        if (isset($actividad['preguntas']) && is_array($actividad['preguntas'])) {
                            foreach ($actividad['preguntas'] as $pregunta) {
                                if (empty($pregunta['texto'])) {
                                    continue;
                                }

                                $texto_pregunta = $pregunta['texto'];
                                $tipo_pregunta = isset($pregunta['tipo']) ? $pregunta['tipo'] : 'opcion_multiple';
                                $respuesta_correcta = isset($pregunta['respuesta_correcta']) ? $pregunta['respuesta_correcta'] : '';

                                $sql = "INSERT INTO preguntas (actividad_id, texto, tipo, respuesta_correcta)
                                        VALUES (:actividad_id, :texto, :tipo, :respuesta_correcta)";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([
                                    ':actividad_id' => $actividad_id,
                                    ':texto' => $texto_pregunta,
                                    ':tipo' => $tipo_pregunta,
                                    ':respuesta_correcta' => $respuesta_correcta
                                ]);

                                $pregunta_id = $conn->lastInsertId();

                                // Insertar opciones
                                if (isset($pregunta['opciones']) && is_array($pregunta['opciones'])) {
                                    foreach ($pregunta['opciones'] as $opcion) {
                                        if (empty($opcion['texto'])) {
                                            continue;
                                        }

                                        $texto_opcion = $opcion['texto'];
                                        $es_correcta = isset($opcion['es_correcta']) && $opcion['es_correcta'] ? 1 : 0;

                                        $sql = "INSERT INTO opciones (pregunta_id, texto, es_correcta)
                                                VALUES (:pregunta_id, :texto, :es_correcta)";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute([
                                            ':pregunta_id' => $pregunta_id,
                                            ':texto' => $texto_opcion,
                                            ':es_correcta' => $es_correcta
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                // Insertar contenido modular (si existe en el formulario)
                if (isset($unidad['contenido_modular']) && is_array($unidad['contenido_modular'])) {
                    foreach ($unidad['contenido_modular'] as $contenido_index => $contenido) {
                        if (empty($contenido['titulo'])) {
                            continue;
                        }

                        $titulo_contenido = $contenido['titulo'];
                        $tipo_contenido = isset($contenido['tipo']) ? $contenido['tipo'] : 'texto';
                        $contenido_texto = isset($contenido['contenido']) ? $contenido['contenido'] : '';
                        $orden_contenido = $contenido_index + 1;

                        $sql = "INSERT INTO contenido_modular (modulo_id, tipo, contenido, orden, titulo)
                                VALUES (:modulo_id, :tipo, :contenido, :orden, :titulo)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':modulo_id' => $modulo_id,
                            ':tipo' => $tipo_contenido,
                            ':contenido' => $contenido_texto,
                            ':orden' => $orden_contenido,
                            ':titulo' => $titulo_contenido
                        ]);

                        $contador_lecciones++;
                    }
                }
            }
        }

        // Actualizar el contador de lecciones en el curso
        $sql = "UPDATE cursos SET total_lecciones = :total_lecciones WHERE id = :curso_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':total_lecciones' => $contador_lecciones,
            ':curso_id' => $curso_id
        ]);

        // Commit de la transacción
        $conn->commit();

        // Crear notificación para el instructor
        $sql = "INSERT INTO notifications (user_id, role, message, link, icon, read, created_at)
                VALUES (:user_id, 'instructor', :message, :link, 'check-circle', 0, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $instructor_id,
            ':message' => "Tu curso '$nombre' ha sido creado exitosamente.",
            ':link' => "curso.php?id=$curso_id"
        ]);

        // Redirigir o mostrar un mensaje de éxito
        header("Location: admin.php?success=true&curso_id=$curso_id");
        exit;

    } catch (Exception $e) {
        // Si hay algún error, revertir todo
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        // Registrar el error y mostrar un mensaje genérico
        error_log("Error en crear-curso.php: " . $e->getMessage());
        header("Location: admin.php?error=true&message=" . urlencode("Error al guardar el curso. Por favor, inténtalo de nuevo."));
        exit;
    }
} else {
    // Manejar el caso en que no se envíe un POST
    echo "Método no permitido.";
    exit;
}
?>

<?php
/**
 * Security Utility Class
 *
 * Contains security-related functions like HTML purification,
 * XSS prevention, and input validation
 */

class CourseAccess {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Check if a user has access to a specific course
     *
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param string $userRole User role (estudiante, profesor, admin)
     * @return bool True if user has access, false otherwise
     */
    public function checkAccess($user_id, $course_id, $userRole) {
        // Admins have access to all courses
        if ($userRole === 'admin') {
            return true;
        }

        // Professors have access to courses they created
        if ($userRole === 'profesor') {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM cursos
                WHERE id = ? AND instructor_id = ?
            ");
            $stmt->execute([$course_id, $user_id]);
            return $stmt->fetchColumn() > 0;
        }

        // Students have access to courses they're enrolled in
        if ($userRole === 'estudiante') {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM usuarios_cursos
                WHERE usuario_id = ? AND curso_id = ?
            ");
            $stmt->execute([$user_id, $course_id]);
            return $stmt->fetchColumn() > 0;
        }

        return false;
    }

    /**
     * Get course details by course ID
     *
     * @param int $course_id Course ID
     * @return array|false Course details or false if not found
     */
    public function getCourseDetails($curso_id) {
        $sql = "SELECT c.*, u.name as instructor_nombre, u.foto_perfil as instructor_foto, u.id as instructor_id
               FROM cursos c
               LEFT JOIN usuarios u ON c.instructor_id = u.id
               WHERE c.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class ProgressTracker {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

        /**
     * Calculate the overall progress percentage for a course.
     *
     * @param int $curso_id Course ID
     * @param int $completados Number of completed items
     * @return float Overall progress percentage
     */
    public function calculateOverallProgress($curso_id, $completados) {
        // Consulta SQL para obtener el número total de contenidos en el curso
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total
            FROM contenido_modular cm
            JOIN modulos m ON cm.modulo_id = m.id
            WHERE m.curso_id = ?
        ");
        $stmt->execute([$curso_id]);
        $total = $stmt->fetchColumn();

        return $total > 0 ? ($completados / $total) * 100 : 0;
    }

    /**
     * Registra el progreso del usuario en un curso
     *
     * @param int $user_id ID del usuario
     * @param int $curso_id ID del curso
     * @param int $modulo_id ID del módulo
     * @param int $contenido_id ID del contenido
     * @return bool Retorna true si se registró correctamente, false en caso contrario
     */

     public function recordProgress($user_id, $curso_id, $modulo_id, $contenido_id) {
        // Verificar si ya existe un registro para este usuario y contenido
        $sql = "SELECT id FROM progreso_contenido
               WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id, $contenido_id]);
        $exists = $stmt->fetch(PDO::FETCH_COLUMN);

        // Si no existe, crear un registro
        if (!$exists) {
            $sql = "INSERT INTO progreso_contenido
                   (usuario_id, curso_id, modulo_id, contenido_id, fecha_acceso)
                   VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id, $curso_id, $modulo_id, $contenido_id]);
        } else {
            // Actualizar la fecha de acceso
            $sql = "UPDATE progreso_contenido
                   SET fecha_acceso = NOW()
                   WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id, $curso_id, $contenido_id]);
        }

        // Actualizar último acceso en usuarios_cursos
        $sql = "INSERT INTO usuarios_cursos (usuario_id, curso_id, ultimo_acceso)
               VALUES (?, ?, NOW())
               ON DUPLICATE KEY UPDATE ultimo_acceso = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id]);
    }
    /**
     * Get the student's progress for a specific course.
     *
     * @param int $user_id User ID
     * @param int $curso_id Course ID
     * @return array Student progress data
     */
    public function getStudentProgress($user_id, $curso_id) {
        // Obtener todos los contenidos del curso
        $sql = "SELECT cm.id, cm.modulo_id
               FROM contenido_modular cm
               JOIN modulos m ON cm.modulo_id = m.id
               WHERE m.curso_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id]);
        $contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener contenidos completados
        $sql = "SELECT contenido_id, modulo_id
               FROM progreso_contenido
               WHERE usuario_id = ? AND curso_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id]);
        $completados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir a un formato más fácil de usar
        $idsCompletados = array_column($completados, 'contenido_id');
        $modulosProgreso = [];

        // Calcular progreso por módulo
        foreach ($contenidos as $contenido) {
            $modulo_id = $contenido['modulo_id'];

            if (!isset($modulosProgreso[$modulo_id])) {
                $modulosProgreso[$modulo_id] = [
                    'total' => 0,
                    'completados' => 0,
                    'porcentaje' => 0
                ];
            }

            $modulosProgreso[$modulo_id]['total']++;

            if (in_array($contenido['id'], $idsCompletados)) {
                $modulosProgreso[$modulo_id]['completados']++;
            }
        }

        // Calcular porcentajes
        foreach ($modulosProgreso as $modulo_id => &$progreso) {
            if ($progreso['total'] > 0) {
                $progreso['porcentaje'] = ($progreso['completados'] / $progreso['total']) * 100;
            }
        }

        // Obtener último acceso
        $sql = "SELECT ultimo_acceso FROM usuarios_cursos
               WHERE usuario_id = ? AND curso_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id]);
        $ultimo_acceso = $stmt->fetch(PDO::FETCH_COLUMN) ?: date('Y-m-d H:i:s');

        return [
            'completados' => count($idsCompletados),
            'total' => count($contenidos),
            'modulos' => $modulosProgreso,
            'completados_ids' => $idsCompletados, // ✅ renombrado
            'ultimo_acceso' => $ultimo_acceso
        ];
    }
}

class CommentsManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getContentComments($curso_id, $contenido_id) {
        $sql = "SELECT c.*, u.name as usuario_nombre, u.foto_perfil
               FROM comentarios c
               JOIN usuarios u ON c.usuario_id = u.id
               WHERE c.curso_id = ? AND c.contenido_id = ? AND c.comentario_padre_id IS NULL
               ORDER BY c.fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id, $contenido_id]);
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener respuestas para cada comentario
        foreach ($comentarios as &$comentario) {
            $sql = "SELECT r.*, u.name as usuario_nombre, u.foto_perfil
                   FROM comentarios r
                   JOIN usuarios u ON r.usuario_id = u.id
                   WHERE r.comentario_padre_id = ?
                   ORDER BY r.fecha_creacion ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$comentario['id']]);
            $comentario['respuestas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $comentarios;
    }
}

class TimeUtils {
    public static function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;

        if ($difference < 60) {
            return 'hace un momento';
        } elseif ($difference < 3600) {
            $minutes = floor($difference / 60);
            return $minutes . ' minuto' . ($minutes != 1 ? 's' : '') . ' atrás';
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours . ' hora' . ($hours != 1 ? 's' : '') . ' atrás';
        } elseif ($difference < 604800) {
            $days = floor($difference / 86400);
            return $days . ' día' . ($days != 1 ? 's' : '') . ' atrás';
        } else {
            return date('d M Y', $timestamp);
        }
    }
}

class CourseContent {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Load course content including modules and contents
     *
     * @param int $course_id Course ID
     * @param int $module_id Module ID
     * @param int $content_id Content ID
     * @return array Course content data
     */

     public function loadCourseContent($curso_id, $modulo_id = 0, $contenido_id = 0) {
        // Obtener todos los módulos del curso
        $sql = "SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$curso_id]);
        $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si no hay módulo seleccionado, usar el primero
        if ($modulo_id == 0 && !empty($modulos)) {
            $modulo_id = $modulos[0]['id'];
        }

        // Obtener el módulo actual
        $moduloActual = null;
        foreach ($modulos as $modulo) {
            if ($modulo['id'] == $modulo_id) {
                $moduloActual = $modulo;
                break;
            }
        }

        // Obtener contenidos del módulo actual
        $contenidos = [];
        $contenidosPorModulo = [];

        if ($moduloActual) {
            $sql = "SELECT * FROM contenido_modular WHERE modulo_id = ? ORDER BY orden ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$modulo_id]);
            $contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no hay contenido seleccionado, usar el primero
            if ($contenido_id == 0 && !empty($contenidos)) {
                $contenido_id = $contenidos[0]['id'];
            }
        }

        // Obtener el contenido actual
        $contenidoActual = null;
        foreach ($contenidos as $contenido) {
            if ($contenido['id'] == $contenido_id) {
                $contenidoActual = $contenido;
                break;
            }
        }

        // Obtener todos los contenidos por módulo para la navegación
        foreach ($modulos as $modulo) {
            $sql = "SELECT * FROM contenido_modular WHERE modulo_id = ? ORDER BY orden ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$modulo['id']]);
            $contenidosPorModulo[$modulo['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Construir navegación anterior/siguiente
        $navegacion = ['anterior' => null, 'siguiente' => null];

        // Encontrar índice del contenido actual
        $indiceActual = -1;
        $indiceModuloActual = -1;

        // Primero, encontrar el índice del módulo actual
        foreach ($modulos as $i => $modulo) {
            if ($modulo['id'] == $modulo_id) {
                $indiceModuloActual = $i;
                break;
            }
        }

        // Luego, si hay un módulo actual, encontrar el índice del contenido actual
        if ($indiceModuloActual >= 0 && !empty($contenidos)) {
            foreach ($contenidos as $i => $contenido) {
                if ($contenido['id'] == $contenido_id) {
                    $indiceActual = $i;
                    break;
                }
            }
        }

        // Configurar navegación previa
        if ($indiceActual > 0) {
            // Contenido anterior en el mismo módulo
            $navegacion['anterior'] = [
                'id' => $contenidos[$indiceActual - 1]['id'],
                'modulo_id' => $modulo_id
            ];
        } elseif ($indiceModuloActual > 0) {
            // Último contenido del módulo anterior
            $moduloAnterior = $modulos[$indiceModuloActual - 1];
            $contenidosModuloAnterior = $contenidosPorModulo[$moduloAnterior['id']];

            if (!empty($contenidosModuloAnterior)) {
                $ultimoContenido = end($contenidosModuloAnterior);
                $navegacion['anterior'] = [
                    'id' => $ultimoContenido['id'],
                    'modulo_id' => $moduloAnterior['id']
                ];
            }
        }

        // Configurar navegación siguiente
        if ($indiceActual >= 0 && $indiceActual < count($contenidos) - 1) {
            // Contenido siguiente en el mismo módulo
            $navegacion['siguiente'] = [
                'id' => $contenidos[$indiceActual + 1]['id'],
                'modulo_id' => $modulo_id
            ];
        } elseif ($indiceModuloActual >= 0 && $indiceModuloActual < count($modulos) - 1) {
            // Primer contenido del módulo siguiente
            $moduloSiguiente = $modulos[$indiceModuloActual + 1];
            $contenidosModuloSiguiente = $contenidosPorModulo[$moduloSiguiente['id']];

            if (!empty($contenidosModuloSiguiente)) {
                $primerContenido = $contenidosModuloSiguiente[0];
                $navegacion['siguiente'] = [
                    'id' => $primerContenido['id'],
                    'modulo_id' => $moduloSiguiente['id']
                ];
            }
        }

        return [
            'modulos' => $modulos,
            'moduloActual' => $moduloActual,
            'contenidos' => $contenidos,
            'contenidoActual' => $contenidoActual,
            'contenidosPorModulo' => $contenidosPorModulo,
            'navegacion' => $navegacion
        ];
    }
}

class Security {
    /**
     * Purifies HTML content to prevent XSS attacks
     *
     * @param string $html The HTML content to purify
     * @return string The purified HTML content
     */
    public static function purifyHTML($html) {
        // Basic implementation - we'll sanitize the HTML
        // For a production environment, consider using HTMLPurifier library

        // Remove potentially dangerous attributes
        $html = preg_replace(
            '/<(.*?)[\s]+(on[a-z]+)[\s]*=[\s]*["\']+(.*?)["\']/i',
            '<$1',
            $html
        );

        // Remove javascript: protocol
        $html = preg_replace(
            '/<(.*?)[\s]+([a-z]+)[\s]*=[\s]*["\']javascript:(.*?)["\']/i',
            '<$1',
            $html
        );

        // Remove potentially dangerous tags
        $dangerousTags = ['script', 'iframe', 'object', 'embed', 'applet', 'form'];
        foreach ($dangerousTags as $tag) {
            $html = preg_replace('/<' . $tag . '(.*?)>(.*?)<\/' . $tag . '>/is', '', $html);
            $html = preg_replace('/<' . $tag . '(.*?)>/is', '', $html);
        }

        return $html;
    }

    /**
     * Sanitize user input to prevent XSS
     *
     * @param string $input The user input to sanitize
     * @return string The sanitized input
     */
    public static function sanitizeInput($input) {
        if (is_string($input)) {
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Generate a CSRF token for forms
     *
     * @return string The generated CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify if a CSRF token is valid
     *
     * @param string $token The token to verify
     * @return bool True if the token is valid, false otherwise
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate an email address
     *
     * @param string $email The email address to validate
     * @return bool True if the email is valid, false otherwise
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if a password is strong enough
     *
     * @param string $password The password to check
     * @return bool True if the password is strong enough, false otherwise
     */
    public static function isStrongPassword($password) {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Generate a secure hash for a password
     *
     * @param string $password The password to hash
     * @return string The hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if the password matches the hash, false otherwise
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
