<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Cursos - SkillBoots</title>
    <!-- Bootstrap CSS -->
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6600;
        }

        .nav-link.active {
            color: #fd7e14 !important;
        }

        .hola {
            margin-left: 12px;
            background-color: var(--primary);
            border: 2px solid var(--primary);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        .hola:hover {
            transform: scale(1.1);
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Estilos adicionales para la nueva estructura */
        .course-section {
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .course-section-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }

        .course-section-content {
            padding: 15px;
            background-color: #ffffff;
        }

        .resource-item, .activity-item {
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 4px;
            background-color: #f9f9f9;
            border-left: 3px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .resource-item {
            border-left-color: #0d6efd;
        }

        .activity-item {
            border-left-color: #fd7e14;
        }

        .circle-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .green-indicator {
            background-color: #28a745;
        }

        .gray-indicator {
            background-color: #adb5bd;
        }

        .section-title {
            font-weight: 500;
            margin: 15px 0 10px 0;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
    </style>
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
                    <span class="me-3" style="color: var(--primary);">Bienvenido, <?php echo htmlspecialchars($nombre); ?></span>
                    <a href="./holaaaa.php" class="hola">Regresar</a>
                </div>
            </div>
        </div>
    </header>

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
                                            <div class="activity-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="circle-indicator gray-indicator"></span>
                                                    <input type="text" class="form-control-plaintext" name="presentacion[0][titulo]" value="Foro de presentación" style="display: inline-block; width: auto;">
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
                                            </div>
                                            <div class="activity-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="circle-indicator gray-indicator"></span>
                                                    <input type="text" class="form-control-plaintext" name="presentacion[1][titulo]" value="Enlaces de tutoría" style="display: inline-block; width: auto;">
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
                                            </div>
                                            <div class="activity-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="circle-indicator green-indicator"></span>
                                                    <input type="text" class="form-control-plaintext" name="presentacion[2][titulo]" value="Pregúntale al profesor" style="display: inline-block; width: auto;">
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
                                                        <div class="resource-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="circle-indicator green-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][recursos][0][titulo]" value="Módulo de la unidad 1" style="display: inline-block; width: auto;">
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
                                                        </div>
                                                        <div class="resource-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][recursos][1][titulo]" value="Recursos bibliográficos y digitales" style="display: inline-block; width: auto;">
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
                                                        </div>
                                                        <div class="resource-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][recursos][2][titulo]" value="Enlace a biblioteca institucional" style="display: inline-block; width: auto;">
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
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Actividades -->
                                                <div class="mb-3">
                                                    <h6 class="section-title">ACTIVIDADES</h6>
                                                    <div id="unidad-1-actividades">
                                                        <div class="activity-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][actividades][0][titulo]" value="Protocolo colaborativo de la unidad" style="display: inline-block; width: auto;">
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
                                                        </div>
                                                        <div class="activity-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][actividades][1][titulo]" value="Actividad de la unidad 1" style="display: inline-block; width: auto;">
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
                                                        </div>
                                                        <div class="activity-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="circle-indicator gray-indicator"></span>
                                                                <input type="text" class="form-control-plaintext" name="unidades[0][actividades][2][titulo]" value="Evaluación de la unidad 1" style="display: inline-block; width: auto;">
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
<script>
    // Variables para manejar el conteo de elementos
    let unidadCounter = 1;
    let presentacionCounter = 3; // Empezamos con 3 elementos de presentación
    let recursosCounter = {};
    let actividadesCounter = {};

    // Inicializar contadores para la primera unidad
    recursosCounter['unidad-1'] = 3;
    actividadesCounter['unidad-1'] = 3;

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

    // Función para añadir un elemento a una sección
    function addElement(sectionId, event) {
        event.stopPropagation(); // Evitar que se cierre la sección

        const container = document.getElementById(sectionId + '-content');
        const elementCount = sectionId === 'presentacion' ? presentacionCounter++ : 0;

        const newElement = document.createElement('div');
        newElement.className = 'activity-item d-flex justify-content-between align-items-center';

        newElement.innerHTML = `
            <div>
                <span class="circle-indicator gray-indicator"></span>
                <input type="text" class="form-control-plaintext" name="${sectionId}[${elementCount}][titulo]" value="Nuevo elemento" style="display: inline-block; width: auto;">
            </div>
            <div>
                <select class="form-select form-select-sm" name="${sectionId}[${elementCount}][tipo]" style="width: auto; display: inline-block;">
                    <option value="foro">Foro</option>
                    <option value="cuestionario">Cuestionario</option>
                    <option value="enlace">Enlace</option>
                </select>
                <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

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
            newElement.className = 'resource-item d-flex justify-content-between align-items-center';
            newElement.innerHTML = `
                <div>
                    <span class="circle-indicator gray-indicator"></span>
                    <input type="text" class="form-control-plaintext" name="unidades[${unidadIndex}][recursos][${elementCount}][titulo]" value="Nuevo recurso" style="display: inline-block; width: auto;">
                </div>
                <div>
                    <select class="form-select form-select-sm" name="unidades[${unidadIndex}][recursos][${elementCount}][tipo]" style="width: auto; display: inline-block;">
                        <option value="archivo" selected>Archivo</option>
                        <option value="enlace">Enlace</option>
                        <option value="video">Video</option>
                    </select>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        } else if (tipo === 'actividades') {
            newElement.className = 'activity-item d-flex justify-content-between align-items-center';
            newElement.innerHTML = `
                <div>
                    <span class="circle-indicator gray-indicator"></span>
                    <input type="text" class="form-control-plaintext" name="unidades[${unidadIndex}][actividades][${elementCount}][titulo]" value="Nueva actividad" style="display: inline-block; width: auto;">
                </div>
                <div>
                    <select class="form-select form-select-sm" name="unidades[${unidadIndex}][actividades][${elementCount}][tipo]" style="width: auto; display: inline-block;">
                        <option value="tarea" selected>Tarea</option>
                        <option value="quiz">Quiz</option>
                        <option value="foro">Foro</option>
                    </select>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeElement(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }

        container.appendChild(newElement);
    }

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
</script>

<!-- Estilos para el botón orange -->
<style>
    .btn-orange {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .btn-orange:hover {
        background-color: #e05a00;
        border-color: #e05a00;
        color: white;
    }

    .text-orange {
        color: var(--primary);
    }

    .border-dashed {
        border-style: dashed;
        cursor: pointer;
    }

    .border-dashed:hover {
        background-color: #f8f9fa;
    }
</style>

</body>
</html>
