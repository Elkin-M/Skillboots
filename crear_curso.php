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
                                            <div class="card card-body border-0">
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

// Función para editar un curso existente
function editCourse(courseId) {
    // Aquí deberías cargar los datos del curso con AJAX
    // Por simplicidad, redirigiremos a una página de edición
    window.location.href = `edit_course.php?id=${courseId}`;
}

// Inicializar las pestañas de Bootstrap cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    const tabElements = document.querySelectorAll('#courseManagementTabs a');
    tabElements.forEach(function(tabEl) {
        tabEl.addEventListener('click', function(e) {
            e.preventDefault();
            const bsTab = new bootstrap.Tab(tabEl);
            bsTab.show();
        });
    });
});

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
            <div class="mb-3">
                <textarea class="form-control" name="unidades[${unidadIndex}][actividades][${actividadIndex}][preguntas][${questionCount}][texto]" rows="2" placeholder="Texto de la pregunta..."></textarea>
            </div>
            <div class="mb-2">
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
            <div class="mb-3">
                <textarea class="form-control" name="${sectionId}[${elementCount}][preguntas][${questionCount}][texto]" rows="2" placeholder="Texto de la pregunta..."></textarea>
            </div>
            <div class="mb-2">
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
