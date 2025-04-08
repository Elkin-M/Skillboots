<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once './roles/auth.php';
require_once './conexion/db.php';
require_once './utils/security.php'; // New utility class for security functions

// Verify authentication
if (!Auth::isAuthenticated()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get course ID from URL with proper validation
$curso_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$curso_id) {
    header('Location: lista_cursos.php?error=invalid_course');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$userRole = Auth::getUserRole();

// Check course access permissions
$courseAccess = new CourseAccess($conn);
$hasAccess = $courseAccess->checkAccess($user_id, $curso_id, $userRole);

if (!$hasAccess) {
    if ($userRole === 'estudiante') {
        header('Location: detalle_curso.php?id=' . $curso_id . '&error=no_inscrito');
    } else {
        header('Location: dashboard.php?error=no_permiso');
    }
    exit;
}

// Load course data - using a dedicated class for better organization
$courseManager = new CourseAccess($conn);
$curso = $courseManager->getCourseDetails($curso_id);

if (!$curso) {
    header('Location: lista_cursos.php?error=curso_no_encontrado');
    exit;
}

// Get modules, selected module and content
$modulo_id = filter_input(INPUT_GET, 'modulo', FILTER_VALIDATE_INT) ?: 0;
$contenido_id = filter_input(INPUT_GET, 'contenido', FILTER_VALIDATE_INT) ?: 0;

$courseContent = new CourseContent($conn);
$data = $courseContent->loadCourseContent($curso_id, $modulo_id, $contenido_id);

$modulos = $data['modulos'];
$moduloActual = $data['moduloActual'];
$contenidos = $data['contenidos'];
$contenidoActual = $data['contenidoActual'];
$navegacion = $data['navegacion'];

// Inicializar valores predeterminados
$progreso = [
    'completados' => 0,
    'total' => 0,
    'ultimo_acceso' => 'Nunca'
];
$porcentajeProgreso = 0;

// Verificar el rol del usuario
if ($userRole === 'estudiante') {
    $progressTracker = new ProgressTracker($conn);

    // Registrar progreso si hay contenido actual
    if (!empty($contenidoActual)) {
        $progressTracker->recordProgress($user_id, $curso_id, $modulo_id, $contenidoActual['id']);
    }

    // Obtener progreso del estudiante
    $progreso = $progressTracker->getStudentProgress($user_id, $curso_id);

    // Cálculo del porcentaje asegurando que no haya división por 0
    if ($progreso['total'] > 0) {
        $porcentajeProgreso = ($progreso['completados'] / $progreso['total']) * 100;
    } else {
        $porcentajeProgreso = 0;
    }
}

// Define page data
$pageData = [
    'isLoggedIn' => true,
    'userRole' => $userRole,
    'userName' => $_SESSION['user_name'] ?? '',
    'title' => htmlspecialchars($curso['nombre']) . ' - SKILLBOOTS'
];

// Include proper navbar based on user role
if ($pageData['isLoggedIn'] && $pageData['userRole'] === 'estudiante') {
    include 'navbar-estu.php'; // Navbar para estudiantes
} elseif ($pageData['userRole'] === 'profesor') {
    include 'navbar-pro.php'; // Navbar para profesores
} else {
    include 'navbar.php'; // Navbar general
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageData['title']; ?></title>
    <link href="./css/style.css" rel="stylesheet">
    <link href="./css/ver-cursos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Added modern UI framework -->
    <!-- Custom styles -->
    <link href="./css/course-viewer.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Course header with improved design -->
<div class="course-header py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-5 fw-bold text-white mb-1" style="color:#44425A !important;"><?php echo htmlspecialchars($curso['nombre']); ?></h1>
                <div class="d-flex align-items-center">
                    <img src="<?php echo !empty($curso['instructor_foto']) ? htmlspecialchars($curso['instructor_foto']) : 'img/instructor-default.jpg'; ?>"
                         class="rounded-circle me-2" alt="Instructor" width="32" height="32">
                    <p class="mb-0 text-black">Instructor: <strong><?php echo htmlspecialchars($curso['instructor_nombre']); ?></strong></p>
                </div>
            </div>
            <?php if ($userRole === 'estudiante'): ?>
            <div class="col-md-5">
                <div class="progress-card p-3 rounded shadow-sm bg-white">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Progreso del curso</h6>
                        <span class="badge rounded-pill bg-primary"><?php echo $porcentajeProgreso; ?>% completado</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                             role="progressbar"
                             style="width: <?php echo $porcentajeProgreso; ?>%"
                             aria-valuenow="<?php echo $porcentajeProgreso; ?>"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small text-muted">
                        <span><?php echo $progreso['completados']; ?> de <?php echo $progreso['total']; ?> lecciones</span>
                        <span>Última actividad: <?php echo date('d M, H:i', strtotime($progreso['ultimo_acceso'])); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container-fluid px-md-4 pb-5">
    <div class="row gx-4">
        <!-- Sidebar - Module listing with improved design -->
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="course-sidebar rounded shadow-sm">
                <div class="sidebar-header p-3 border-bottom">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-book me-2 text-primary"></i>Contenido del curso
                    </h5>
                </div>

                <div class="accordion modules-accordion" id="modulesAccordion">
                    <?php foreach ($modulos as $indice => $modulo):
                        $isActive = ($modulo['id'] == $modulo_id);
                        $moduleCompletion = 0;

                        if ($userRole === 'estudiante' && isset($progreso['modulos'][$modulo['id']])) {
                            $moduleData = $progreso['modulos'][$modulo['id']];
                            $moduleCompletion = $moduleData['porcentaje'];
                        }
                    ?>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="heading<?php echo $modulo['id']; ?>">
                            <button class="accordion-button <?php echo $isActive ? '' : 'collapsed'; ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse<?php echo $modulo['id']; ?>"
                                    aria-expanded="<?php echo $isActive ? 'true' : 'false'; ?>"
                                    aria-controls="collapse<?php echo $modulo['id']; ?>">
                                <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                    <span>
                                        <span class="module-number me-2"><?php echo $indice + 1; ?>.</span>
                                        <?php echo htmlspecialchars($modulo['titulo']); ?>
                                    </span>
                                    <?php if ($userRole === 'estudiante'): ?>
                                    <div class="module-progress d-flex align-items-center">
                                        <div class="progress me-2" style="width: 40px; height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $moduleCompletion; ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?php echo $moduleCompletion; ?>%</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $modulo['id']; ?>"
                             class="accordion-collapse collapse <?php echo $isActive ? 'show' : ''; ?>"
                             aria-labelledby="heading<?php echo $modulo['id']; ?>"
                             data-bs-parent="#modulesAccordion">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush content-list">
                                <?php foreach ($data['contenidosPorModulo'][$modulo['id']] as $cont):
$esCompletado = is_array($progreso['completados']) ? in_array($cont['id'], $progreso['completados']) : false;                                    $esActivo = ($cont['id'] == $contenido_id);

                                    // Determine icon based on content type
                                    $icon = '';
                                    switch ($cont['tipo']) {
                                        case 'texto': $icon = 'fas fa-file-alt'; break;
                                        case 'video': $icon = 'fas fa-video'; break;
                                        case 'imagen': $icon = 'fas fa-image'; break;
                                        case 'pdf': $icon = 'fas fa-file-pdf'; break;
                                        case 'quiz': $icon = 'fas fa-question-circle'; break;
                                        default: $icon = 'fas fa-file'; break;
                                    }
                                ?>
                                    <li class="list-group-item content-item <?php echo $esActivo ? 'active' : ''; ?> <?php echo $esCompletado ? 'completed' : ''; ?>">
                                        <a href="ver-cursos.php?id=<?php echo $curso_id; ?>&modulo=<?php echo $modulo['id']; ?>&contenido=<?php echo $cont['id']; ?>"
                                           class="content-link d-flex align-items-center text-decoration-none">
                                            <?php if ($userRole === 'estudiante' && $esCompletado): ?>
                                                <span class="check-icon"><i class="fas fa-check-circle text-success"></i></span>
                                            <?php else: ?>
                                                <span class="content-icon"><i class="<?php echo $icon; ?>"></i></span>
                                            <?php endif; ?>
                                            <span class="ms-2"><?php echo htmlspecialchars($cont['titulo']); ?></span>
                                            <?php if (isset($cont['duracion']) && $cont['duracion']): ?>                                                <span class="ms-auto small text-muted"><?php echo $cont['duracion']; ?></span>
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
                
                <?php if ($userRole === 'estudiante'): ?>
                <div class="sidebar-footer p-3 border-top">
                    <a href="recursos_curso.php?id=<?php echo $curso_id; ?>" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-download me-1"></i> Recursos adicionales
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main content area with improved design -->
        <div class="col-lg-9">
            <?php if ($contenidoActual): ?>
                <div class="content-container bg-white rounded shadow-sm overflow-hidden">
                    <div  class="content-header p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 style="font-size: 2.4rem; text-transform: uppercase;" class="h3 fw-bold mb-0"><?php echo htmlspecialchars($contenidoActual['titulo']); ?></h2>
                            <?php if ($userRole === 'estudiante'): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary bg-success" id="toggleNotes">
                                    <i class="fas fa-sticky-note me-1"></i> Mis notas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="content-body p-4">
                        <?php if ($contenidoActual['tipo'] === 'texto'): ?>
                            <p style="line-height: 30px;" class="content-text">
                            <?php
                             $lineas = explode("\n", $contenidoActual['contenido']); // separa por saltos de línea

                            foreach ($lineas as $linea) {
                                if (trim($linea) !== '') {
                                    // Reemplazar **texto** por <strong>texto</strong>
                                    $lineaConNegrita = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $linea);

                                    // Aplicar purificación si lo necesitas
                                    $lineaPurificada = Security::purifyHTML(html: $lineaConNegrita);

                                    // Mostrar en párrafo
                                    echo "<p>{$lineaPurificada}</p>";
                                }
        }
        ?>
                            </p>

                            <?php elseif ($contenidoActual['tipo'] === 'video'): ?>
    <div class="content-video ratio ratio-16x9 mb-4">
        <?php
        $videoUrl = trim($contenidoActual['contenido']);
        
        // Detectar el tipo de URL de video (YouTube, Vimeo, etc.)
        if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
            // Procesar URLs de YouTube
            $videoId = '';
            
            // Formato youtu.be/XXXXXXXXXXX
            if (strpos($videoUrl, 'youtu.be') !== false) {
                $parts = explode('/', $videoUrl);
                $videoId = end($parts);
            } 
            // Formato youtube.com/watch?v=XXXXXXXXXXX
            elseif (strpos($videoUrl, 'watch?v=') !== false) {
                parse_str(parse_url($videoUrl, PHP_URL_QUERY), $params);
                $videoId = isset($params['v']) ? $params['v'] : '';
            }
            // Formato youtube.com/embed/XXXXXXXXXXX
            elseif (strpos($videoUrl, '/embed/') !== false) {
                $parts = explode('/embed/', $videoUrl);
                if (isset($parts[1])) {
                    $videoId = explode('?', $parts[1])[0];
                }
            }
            
            if ($videoId) {
                echo '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($videoId) . '" 
                    title="YouTube video player" frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen></iframe>';
            } else {
                echo '<div class="alert alert-warning">URL de YouTube inválida</div>';
            }
        } 
        // Vimeo
        elseif (strpos($videoUrl, 'vimeo.com') !== false) {
            $vimeoId = '';
            
            // Formato vimeo.com/XXXXXXXXX
            $parts = explode('/', $videoUrl);
            $vimeoId = end($parts);
            
            if ($vimeoId && is_numeric($vimeoId)) {
                echo '<iframe src="https://player.vimeo.com/video/' . htmlspecialchars($vimeoId) . '" 
                    frameborder="0" allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen></iframe>';
            } else {
                echo '<div class="alert alert-warning">URL de Vimeo inválida</div>';
            }
        }
        // Si es una URL directa a un video o ya contiene un iframe completo
        elseif (strpos($videoUrl, '<iframe') !== false) {
            // Si ya es un iframe, mostrarlo directamente
            echo Security::purifyHTML($videoUrl);
        }
        // URLs normales (mp4, webm, etc.)
        elseif (preg_match('/\.(mp4|webm|ogg)$/i', $videoUrl)) {
            echo '<video controls class="w-100 h-100">
                    <source src="' . htmlspecialchars($videoUrl) . '" type="video/' . pathinfo($videoUrl, PATHINFO_EXTENSION) . '">
                    Tu navegador no soporta el elemento de video.
                  </video>';
        }
        // Si es otro tipo de URL o formato no reconocido
        else {
            // Intentar mostrar como iframe genérico
            echo '<iframe src="' . htmlspecialchars($videoUrl) . '" 
                frameborder="0" allowfullscreen class="w-100 h-100"></iframe>';
        }
        ?>
    </div>

                            <?php if (!empty($contenidoActual['transcripcion'])): ?>
                            <div class="video-transcription mt-4">
                                <h5 class="mb-3">Transcripción</h5>
                                <div class="transcription-content p-3 bg-light rounded">
                                    <?php echo nl2br(htmlspecialchars($contenidoActual['transcripcion'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php elseif ($contenidoActual['tipo'] === 'imagen'): ?>
                            <div class="content-image text-center mb-4">
                                <img src="<?php echo htmlspecialchars($contenidoActual['contenido']); ?>"
                                     alt="<?php echo htmlspecialchars($contenidoActual['titulo']); ?>"
                                     class="img-fluid rounded shadow-sm">
                                <?php if (!empty($contenidoActual['descripcion'])): ?>
                                    <figcaption class="mt-2 text-muted">
                                        <?php echo htmlspecialchars($contenidoActual['descripcion']); ?>
                                    </figcaption>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($contenidoActual['tipo'] === 'pdf'): ?>
                            <div class="content-pdf mb-4">
                                <div class="ratio ratio-16x9">
                                    <iframe class="embed-responsive-item"
                                            src="<?php echo htmlspecialchars($contenidoActual['contenido']); ?>"
                                            allowfullscreen></iframe>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="<?php echo htmlspecialchars($contenidoActual['contenido']); ?>"
                                       class="btn btn-sm btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i> Abrir en nueva pestaña
                                    </a>
                                    <a href="<?php echo htmlspecialchars($contenidoActual['contenido']); ?>"
                                       class="btn btn-sm btn-outline-primary ms-2" download>
                                        <i class="fas fa-download me-1"></i> Descargar PDF
                                    </a>
                                </div>
                            </div>

                        <?php elseif ($contenidoActual['tipo'] === 'quiz'): ?>
                            <div class="quiz-container p-4 border rounded">
                                <?php
                                $quiz = json_decode($contenidoActual['contenido'], true);

                                if ($quiz && isset($quiz['preguntas']) && is_array($quiz['preguntas'])):
                                ?>
                                    <form id="quizForm" method="post" action="procesar_quiz.php">
                                        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                                        <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">
                                        <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">

                                        <div class="quiz-header mb-4">
                                            <h4 class="quiz-title"><?php echo htmlspecialchars($quiz['titulo'] ?? 'Evaluación'); ?></h4>
                                            <?php if (!empty($quiz['descripcion'])): ?>
                                                <p class="quiz-description text-muted"><?php echo htmlspecialchars($quiz['descripcion']); ?></p>
                                            <?php endif; ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Este quiz consta de <?php echo count($quiz['preguntas']); ?> preguntas.
                                                <?php if (isset($quiz['tiempo_limite'])): ?>
                                                    Tienes <?php echo $quiz['tiempo_limite']; ?> minutos para completarlo.
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php foreach ($quiz['preguntas'] as $index => $pregunta): ?>
                                            <div class="quiz-question card mb-4">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title mb-0">
                                                        Pregunta <?php echo $index + 1; ?>
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <p class="question-text mb-3"><?php echo htmlspecialchars($pregunta['texto']); ?></p>

                                                    <?php if (isset($pregunta['opciones']) && is_array($pregunta['opciones'])): ?>
                                                        <div class="options-list">
                                                            <?php foreach ($pregunta['opciones'] as $opIndex => $opcion): ?>
                                                                <div class="option-item form-check mb-2 p-2 border rounded">
                                                                    <input class="form-check-input" type="radio"
                                                                        name="respuesta_<?php echo $index; ?>"
                                                                        id="opcion_<?php echo $index; ?>_<?php echo $opIndex; ?>"
                                                                        value="<?php echo $opIndex; ?>">
                                                                    <label class="form-check-label w-100" for="opcion_<?php echo $index; ?>_<?php echo $opIndex; ?>">
                                                                        <?php echo htmlspecialchars($opcion); ?>
                                                                    </label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-check-circle me-2"></i> Enviar respuestas
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        El formato del quiz no es válido. Por favor contacte al instructor.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($userRole === 'estudiante'): ?>
                            <!-- Improved notes section with toggle functionality -->
                            <div id="notesSection" class="notes-container p-4 border-top" style="display: none;">
                                <h5 class="mb-3"><i class="fas fa-sticky-note me-2 text-warning"></i> Mis notas</h5>
                                <?php
                                // Retrieve student notes for this content
                                $sql = "SELECT notas FROM notas_estudiante
                                            WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$user_id, $curso_id, $contenido_id]);
                                $notasActuales = $stmt->fetch(PDO::FETCH_COLUMN) ?: '';
                                ?>
                                <form id="notasForm" action="guardar_notas.php" method="post">
                                    <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                                    <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">
                                    <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">

                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" id="notesTextarea" name="notas"
                                              style="height: 150px" placeholder="Escribe tus notas aquí..."
                                              data-autosave="true"><?php echo htmlspecialchars($notasActuales); ?></textarea>
                                        <label for="notesTextarea">Escribe tus notas aquí...</label>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-1"></i> Guardar notas
                                        </button>
                                        <button type="button" id="exportNotes" class="btn btn-outline-secondary">
                                            <i class="fas fa-file-export me-1"></i> Exportar notas
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Navigation between content -->
                        <div class="content-footer p-4 border-top">
                            <div class="d-flex justify-content-between">
                                <?php if ($navegacion['anterior']): ?>
                                    <a href="ver_curso.php?id=<?php echo $curso_id; ?>&modulo=<?php echo $navegacion['anterior']['modulo_id']; ?>&contenido=<?php echo $navegacion['anterior']['id']; ?>"
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-chevron-left me-2"></i> Anterior
                                    </a>
                                <?php else: ?>
                                    <div></div> <!-- Empty space to maintain alignment -->
                                <?php endif; ?>

                                <?php if ($navegacion['siguiente']): ?>
                                    <a href="ver_curso.php?id=<?php echo $curso_id; ?>&modulo=<?php echo $navegacion['siguiente']['modulo_id']; ?>&contenido=<?php echo $navegacion['siguiente']['id']; ?>"
                                       class="btn btn-primary">
                                        Siguiente <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="completar_curso.php?id=<?php echo $curso_id; ?>" class="btn btn-success">
                                        Completar curso <i class="fas fa-check-circle ms-2"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($userRole === 'estudiante'): ?>
                    <!-- Improved comments section -->
                    <div class="comments-container mt-4 bg-white rounded shadow-sm">
                        <div class="comments-header p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i> Comentarios y preguntas</h5>
                            <button class="btn btn-sm btn-primary" id="toggleCommentForm">
                                <i class="fas fa-plus me-1"></i> Nuevo comentario
                            </button>
                        </div>

                        <div id="commentFormContainer" class="p-4 border-bottom" style="display: none;">
                            <form action="guardar_comentario.php" method="post" class="comment-form">
                                <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                                <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">
                                <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">

                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="commentTextarea" name="comentario"
                                          style="height: 100px" placeholder="Escribe tu comentario aquí..."></textarea>
                                    <label for="commentTextarea">Escribe tu comentario o pregunta...</label>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" id="cancelComment">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Enviar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="comments-list p-4">
                            <?php
                            // Get comments for this content with improved query
                            $commentsManager = new CommentsManager($conn);
                            $comentarios = $commentsManager->getContentComments($curso_id, $contenido_id);

                            if (count($comentarios) > 0):
                            ?>
                                <?php foreach ($comentarios as $comentario): ?>
                                    <div class="comment-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex">
                                            <img src="<?php echo !empty($comentario['foto_perfil']) ? htmlspecialchars($comentario['foto_perfil']) : 'img/profile-default.jpg'; ?>"
                                                 class="rounded-circle me-2" alt="Avatar" width="48" height="48" style="object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">
                                                        <?php echo htmlspecialchars($comentario['usuario_nombre']); ?>
                                                        <?php if ($comentario['usuario_id'] == $curso['instructor_id']): ?>
                                                            <span class="badge bg-primary ms-2">Instructor</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo TimeUtils::timeAgo($comentario['fecha_creacion']); ?>
                                                    </small>
                                                </div>
                                                <div class="comment-text mb-2">
                                                    <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                                                </div>
                                                <div class="comment-actions">
                                                    <button class="btn btn-sm btn-link text-decoration-none p-0"
                                                        onclick="toggleReplyForm('<?php echo $comentario['id']; ?>')">
                                                        <i class="fas fa-reply me-1"></i> Responder
                                                    </button>
                                                    <?php if ($comentario['usuario_id'] == $user_id): ?>
                                                        <button class="btn btn-sm btn-link text-decoration-none p-0 ms-3 text-danger"
                                                               onclick="deleteComment('<?php echo $comentario['id']; ?>')">
                                                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                                <div id="replyForm_<?php echo $comentario['id']; ?>" class="reply-form mt-3" style="display: none;">
                                                    <form action="guardar_respuesta.php" method="post">
                                                        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                                                        <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">
                                                        <input type="hidden" name="contenido_id" value="<?php echo $contenido_id; ?>">
                                                        <input type="hidden" name="comentario_id" value="<?php echo $comentario['id']; ?>">

                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" id="replyTextarea_<?php echo $comentario['id']; ?>" name="respuesta"
                                                                  style="height: 80px" placeholder="Escribe tu respuesta..."></textarea>

<label for="replyTextarea_<?php echo $comentario['id']; ?>">Escribe tu respuesta...</label>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-secondary me-2"
                    onclick="toggleReplyForm('<?php echo $comentario['id']; ?>')">
                Cancelar
            </button>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-paper-plane me-1"></i> Responder
            </button>
        </div>
    </form>
</div>

<?php if (!empty($comentario['respuestas'])): ?>
    <div class="replies-container mt-3 ps-4 border-start">
        <?php foreach ($comentario['respuestas'] as $respuesta): ?>
            <div class="reply-item mb-3">
                <div class="d-flex">
                    <img src="<?php echo !empty($respuesta['foto_perfil']) ? htmlspecialchars($respuesta['foto_perfil']) : 'img/profile-default.jpg'; ?>"
                         class="rounded-circle me-2" alt="Avatar" width="36" height="36" style="object-fit: cover;">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 small">
                                <?php echo htmlspecialchars($respuesta['usuario_nombre']); ?>
                                <?php if ($respuesta['usuario_id'] == $curso['instructor_id']): ?>
                                    <span class="badge bg-primary ms-2">Instructor</span>
                                <?php endif; ?>
                            </h6>
                            <small class="text-muted">
                                <?php echo TimeUtils::timeAgo($respuesta['fecha_creacion']); ?>
                            </small>
                        </div>
                        <div class="reply-text small">
                            <?php echo nl2br(htmlspecialchars($respuesta['respuesta'])); ?>
                        </div>
                        <?php if ($respuesta['usuario_id'] == $user_id): ?>
                            <div class="reply-actions mt-1">
                                <button class="btn btn-sm btn-link text-decoration-none p-0 text-danger small"
                                        onclick="deleteReply('<?php echo $respuesta['id']; ?>')">
                                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-comments text-center py-4">
                                            <i class="fas fa-comments text-muted mb-3" style="font-size: 2rem;"></i>
                                            <p class="text-muted">No hay comentarios todavía. ¡Sé el primero en comentar!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="course-welcome bg-white rounded shadow-sm p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-book-open text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="mb-3">Bienvenido a <?php echo htmlspecialchars($curso['nombre']); ?></h2>
                            <p class="lead mb-4">Selecciona un módulo y contenido del panel izquierdo para comenzar a aprender.</p>
                            <?php if ($moduloActual): ?>
                                <a href="ver_curso.php?id=<?php echo $curso_id; ?>&modulo=<?php echo $moduloActual['id']; ?>&contenido=<?php echo $contenidos[0]['id']; ?>"
                                   class="btn btn-lg btn-primary">
                                    <i class="fas fa-play-circle me-2"></i> Comenzar el curso
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Custom JavaScript for course viewer -->
<script>
// Toggle notes section
document.getElementById('toggleNotes').addEventListener('click', function() {
    const notesSection = document.getElementById('notesSection');
    if (notesSection.style.display === 'none') {
        notesSection.style.display = 'block';
        this.innerHTML = '<i class="fas fa-times me-1"></i> Cerrar notas';
    } else {
        notesSection.style.display = 'none';
        this.innerHTML = '<i class="fas fa-sticky-note me-1"></i> Mis notas';
    }
});

// Toggle comment form
document.getElementById('toggleCommentForm').addEventListener('click', function() {
    const commentForm = document.getElementById('commentFormContainer');
    if (commentForm.style.display === 'none') {
        commentForm.style.display = 'block';
        this.innerHTML = '<i class="fas fa-times me-1"></i> Cancelar';
    } else {
        commentForm.style.display = 'none';
        this.innerHTML = '<i class="fas fa-plus me-1"></i> Nuevo comentario';
    }
});

// Cancel comment button
document.getElementById('cancelComment').addEventListener('click', function() {
    document.getElementById('commentFormContainer').style.display = 'none';
    document.getElementById('toggleCommentForm').innerHTML = '<i class="fas fa-plus me-1"></i> Nuevo comentario';
    document.getElementById('commentTextarea').value = '';
});

// Toggle reply form
function toggleReplyForm(commentId) {
    const replyForm = document.getElementById('replyForm_' + commentId);
    if (replyForm.style.display === 'none') {
        replyForm.style.display = 'block';
    } else {
        replyForm.style.display = 'none';
    }
}

// Delete comment function
function deleteComment(commentId) {
    if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
        window.location.href = 'eliminar_comentario.php?id=' + commentId +
                               '&curso_id=<?php echo $curso_id; ?>' +
                               '&modulo_id=<?php echo $modulo_id; ?>' +
                               '&contenido_id=<?php echo $contenido_id; ?>';
    }
}

// Delete reply function
function deleteReply(replyId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta respuesta?')) {
        window.location.href = 'eliminar_respuesta.php?id=' + replyId +
                               '&curso_id=<?php echo $curso_id; ?>' +
                               '&modulo_id=<?php echo $modulo_id; ?>' +
                               '&contenido_id=<?php echo $contenido_id; ?>';
    }
}

// Auto-save notes functionality
const notesTextarea = document.getElementById('notesTextarea');
if (notesTextarea) {
    let typingTimer;
    const doneTypingInterval = 2000; // time in ms, 2 seconds

    notesTextarea.addEventListener('keyup', function() {
        clearTimeout(typingTimer);
        if (notesTextarea.value) {
            typingTimer = setTimeout(autoSaveNotes, doneTypingInterval);
        }
    });

    function autoSaveNotes() {
        const form = document.getElementById('notasForm');
        const formData = new FormData(form);
        formData.append('auto_save', 'true');

        fetch('guardar_notas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Notas guardadas automáticamente');
                // Show a brief feedback
                const savedFeedback = document.createElement('div');
                savedFeedback.className = 'auto-save-feedback';
                savedFeedback.textContent = 'Guardado automático completado';
                savedFeedback.style.position = 'fixed';
                savedFeedback.style.bottom = '20px';
                savedFeedback.style.right = '20px';
                savedFeedback.style.padding = '8px 16px';
                savedFeedback.style.backgroundColor = 'rgba(40, 167, 69, 0.8)';
                savedFeedback.style.color = 'white';
                savedFeedback.style.borderRadius = '4px';
                savedFeedback.style.zIndex = '1000';

                document.body.appendChild(savedFeedback);

                setTimeout(() => {
                    savedFeedback.style.opacity = '0';
                    savedFeedback.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        document.body.removeChild(savedFeedback);
                    }, 500);
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error guardando notas:', error);
        });
    }

    // Export notes functionality
    document.getElementById('exportNotes').addEventListener('click', function() {
        const notesContent = notesTextarea.value;
        const courseTitle = '<?php echo htmlspecialchars($curso['nombre']); ?>';
        const contentTitle = '<?php echo $contenidoActual ? htmlspecialchars($contenidoActual['titulo']) : ''; ?>';

        const blob = new Blob([
            'Título del curso: ' + courseTitle + '\n' +
            'Lección: ' + contentTitle + '\n' +
            'Fecha: ' + new Date().toLocaleDateString() + '\n\n' +
            'Mis notas:\n\n' + notesContent
        ], { type: 'text/plain;charset=utf-8' });

        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'Notas_' + courseTitle.replace(/\s+/g, '_') + '_' +
                   contentTitle.replace(/\s+/g, '_') + '.txt';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });
}

// Mark course as completed for videos when video ends
const videoIframes = document.querySelectorAll('.content-video iframe');
videoIframes.forEach(iframe => {
    iframe.addEventListener('load', function() {
        try {
            // For YouTube videos
            if (iframe.src.includes('youtube.com')) {
                const player = new YT.Player(iframe, {
                    events: {
                        'onStateChange': onPlayerStateChange
                    }
                });

                function onPlayerStateChange(event) {
                    if (event.data === 0) { // video ended
                        markContentAsCompleted();
                    }
                }
            }
        } catch (e) {
            console.log('Error setting up video tracking:', e);
        }
    });
});

// Function to mark content as completed
function markContentAsCompleted() {
    fetch('marcar_completado.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'curso_id=<?php echo $curso_id; ?>&modulo_id=<?php echo $modulo_id; ?>&contenido_id=<?php echo $contenido_id; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Contenido marcado como completado');
            // You could update the UI here if needed
        }
    })
    .catch(error => {
        console.error('Error marking content as completed:', error);
    });
}
</script>

</body>
</html>