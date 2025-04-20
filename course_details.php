<?php
session_start();
require_once 'conexion/db.php'; // Asegúrate de usar la ruta correcta a tu archivo de conexión



// Verificar si se proporcionó un ID de curso válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página principal si no hay ID válido
    header('Location: index.php');
    exit;
}

$curso_id = (int)$_GET['id'];

try {
    // Obtener información del curso
// Obtener información del curso
$sql = "SELECT c.*, u.name as instructor_nombre , lastname as instructor_apellido
        FROM cursos c
        LEFT JOIN usuarios u ON c.instructor_id = u.id
        WHERE c.id = :curso_id AND c.estado = 'publicado' AND c.estate = 'activo'";


    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);

    if ($stmt->rowCount() === 0) {
        echo "Curso no encontrado";
        exit;
    }
    
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener los módulos del curso ordenados por el campo orden
    $sql = "SELECT * FROM modulos WHERE curso_id = :curso_id ORDER BY orden ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada módulo, obtener recursos, actividades y contenido modular
    foreach ($modulos as &$modulo) {
        // Obtener recursos (unidad_id en la tabla recursos corresponde a modulo_id)
        $sql = "SELECT * FROM recursos WHERE unidad_id = :modulo_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $modulo['recursos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener actividades
        $sql = "SELECT * FROM actividades WHERE unidad_id = :modulo_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada actividad, obtener sus preguntas
        foreach ($actividades as &$actividad) {
            $sql = "SELECT * FROM preguntas WHERE actividad_id = :actividad_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':actividad_id' => $actividad['id']]);
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada pregunta, obtener sus opciones
            foreach ($preguntas as &$pregunta) {
                $sql = "SELECT * FROM opciones WHERE pregunta_id = :pregunta_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':pregunta_id' => $pregunta['id']]);
                $pregunta['opciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $actividad['preguntas'] = $preguntas;
        }
        
        $modulo['actividades'] = $actividades;
        
        // Obtener contenido modular
        $sql = "SELECT * FROM contenido_modular WHERE modulo_id = :modulo_id ORDER BY orden ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $modulo['contenido_modular'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Verificar si el usuario está inscrito en el curso
    $usuario_inscrito = false;
    $progreso_curso = 0;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Consultar si el usuario está inscrito
        $sql = "SELECT * FROM usuarios_cursos WHERE curso_id = :curso_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':curso_id' => $curso_id,
            ':user_id' => $user_id
        ]);
        
        if ($stmt->rowCount() > 0) {
            $usuario_inscrito = true;
                
                // Obtener progreso del curso
                $sql = "SELECT AVG(progreso) as progreso FROM usuarios_cursos WHERE curso_id = :curso_id AND usuario_id = :user_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':curso_id' => $curso_id,
                    ':user_id' => $user_id
                ]);
                
                $progreso_curso = round($stmt->fetch(PDO::FETCH_ASSOC)['progreso'], 2);
        }
    }
    
    // Actualizar contador de vistas
    $sql = "UPDATE cursos SET vistas = vistas + 1 WHERE id = :curso_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    
} catch (Exception $e) {
    error_log("Error en curso.php: " . $e->getMessage());
    echo "Error al cargar el curso. Por favor, inténtalo de nuevo.";
    exit;
}

// Obtener número de inscritos
$sql = "SELECT COUNT(*) as inscritos FROM usuarios_cursos WHERE curso_id = :curso_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':curso_id' => $curso_id]);
$inscritos = $stmt->fetch(PDO::FETCH_ASSOC)['inscritos'];

// Obtener número de vistas
$sql = "SELECT vistas FROM cursos WHERE id = :curso_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':curso_id' => $curso_id]);
$vistas = $stmt->fetch(PDO::FETCH_ASSOC)['vistas'];




// Construir las rutas base para enlaces
$base_path = dirname($_SERVER['PHP_SELF']);
if (substr($base_path, -1) !== '/') {
    $base_path .= '/';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($curso['nombre']); ?> - SkillBoots</title>
    <!-- Incluir CSS y JS necesarios -->
    <link href="./css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<body>
<?php 
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
    
    <div class="container mt-4">
        <!-- Cabecera del curso -->
        <div class="row curso-header">
        <div class="col-md-8">
    <div class="curso-header-container p-4 bg-light rounded shadow-sm mb-4" style="padding: 0 !important;">
        <h1 class="display-5 fw-bold text-primary mb-3"><?php echo htmlspecialchars($curso['nombre']); ?></h1>
        
        <div class="curso-badges mb-3">
            <span class="badge bg-primary rounded-pill px-3 py-2 me-2 mb-2"><?php echo htmlspecialchars($curso['categoria']); ?></span>
            <span class="badge bg-secondary rounded-pill px-3 py-2 me-2 mb-2"><?php echo htmlspecialchars($curso['nivel']); ?></span>
            <span class="badge bg-info rounded-pill px-3 py-2 me-2 mb-2"><?php echo $curso['total_lecciones']; ?> lecciones</span>
            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 mb-2"><?php echo $curso['horas_totales']; ?> horas</span>
        </div>
        
        <div class="instructor-info d-flex align-items-center mb-4">
            <div class="instructor-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="fas fa-user-tie"></i>
            </div>
            <div style="margin-left: 5%;">
                <p class="curso-meta fs-5 mb-0">Instructor: <strong><?php echo htmlspecialchars($curso['instructor_nombre'] . ' ' . $curso['instructor_apellido']); ?></strong></p>
                <small class="text-muted"><i class="fas fa-star text-warning"></i> Instructor experto</small>
            </div>
        </div>
        
        <div class="curso-descripcion p-3 bg-white rounded border">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-info-circle text-primary me-2 fs-4"></i>
                <h4 class="mb-0">Descripción del curso</h4>
            </div>
            <p class="fs-5"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>
            
            <div class="curso-features mt-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-laptop-code text-primary me-2"></i>
                            <span>Proyectos prácticos</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-certificate text-primary me-2"></i>
                            <span>Certificado incluido</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-infinity text-primary me-2"></i>
                            <span>Acceso ilimitado</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="#contenido" class="btn btn-primary" style="width: 100%">Ver contenido del curso <i class="fas fa-arrow-down ms-2"></i></a>
            </div>
        </div>
    </div>
</div>
            <div class="col-md-4">
                <?php if (!empty($curso['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars($curso['imagen']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($curso['nombre']); ?>">
                <?php else: ?>
                    <div class="bg-light p-5 rounded text-center">
                        <i class="fas fa-book-open fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <?php if ($curso['precio'] > 0): ?>
                            <h3 class="card-title"><?php echo number_format($curso['precio'], 2); ?> €</h3>
                        <?php else: ?>
                            <h3 class="card-title text-success">Gratis</h3>
                        <?php endif; ?>
                        
                        <?php if ($usuario_inscrito): ?>
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progreso_curso; ?>%;" 
                                     aria-valuenow="<?php echo $progreso_curso; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo $progreso_curso; ?>%
                                </div>
                            </div>
                            <a href="<?php echo $base_path; ?>iniciar-curso.php?id=<?php echo $curso_id; ?>" class="btn btn-success btn-block w-100">Continuar curso</a>
                        <?php else: ?>
                            <?php if ($curso['precio'] > 0): ?>
                                <a href="<?php echo $base_path; ?>inscribirse.php?id=<?php echo $curso_id; ?>" class="btn btn-primary btn-block w-100">Inscribirse</a>
                            <?php else: ?>
                                <a href="<?php echo $base_path; ?>inscribirse.php?id=<?php echo $curso_id; ?>" class="btn btn-success btn-block w-100">Inscribirse gratis</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <i class="fas fa-users me-2"></i> <?php echo isset($curso['inscritos']) ? $curso['inscritos'] : 0; ?> estudiantes
                            <br>
                            <i class="fas fa-eye me-2"></i> <?php echo isset($curso['vistas']) ? $curso['vistas'] : 0; ?> vistas
                            <br>
                            <i class="fas fa-calendar-alt me-2"></i> Última actualización: 
                            <?php echo date('d/m/Y', strtotime($curso['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Temario del curso -->
        <div class="row mt-5">
            <div class="col-12">
                <h2 id="contenido">Contenido del curso</h2>
                <div class="accordion" id="acordeonModulos">
                    <?php foreach ($modulos as $index => $modulo): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $modulo['id']; ?>">
                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $modulo['id']; ?>" 
                                    aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                    aria-controls="collapse<?php echo $modulo['id']; ?>">
                                <strong ><?php echo $modulo['orden']; ?>. <?php echo htmlspecialchars($modulo['titulo']); ?></strong>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $modulo['id']; ?>" 
                             class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                             aria-labelledby="heading<?php echo $modulo['id']; ?>" 
                             data-bs-parent="#acordeonModulos">
                            <div class="accordion-body">
                                <?php if (!empty($modulo['descripcion'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($modulo['descripcion'])); ?></p>
                                <?php endif; ?>
                                
                                <ul class="list-group">
                                    <!-- Contenido modular -->
                                    <?php foreach ($modulo['contenido_modular'] as $contenido): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" >
                                        <div>
                                            <?php 
                                            $icono = 'fa-file-alt';
                                            if ($contenido['tipo'] == 'video') $icono = 'fa-video';
                                            elseif ($contenido['tipo'] == 'audio') $icono = 'fa-headphones';
                                            elseif ($contenido['tipo'] == 'imagen') $icono = 'fa-image';
                                            ?>
                                            <i class="fas <?php echo $icono; ?> me-2"></i>
                                            <?php echo htmlspecialchars($contenido['titulo']); ?>
                                        </div>
                                        <?php if ($usuario_inscrito): ?>
                                            <a href="<?php echo $base_path; ?>contenido.php?id=<?php echo $contenido['id']; ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-lock"></i></span>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                    
                                    <!-- Recursos -->
                                    <?php foreach ($modulo['recursos'] as $recurso): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-download me-2"></i>
                                            <?php echo htmlspecialchars($recurso['titulo']); ?>
                                            <?php if ($recurso['obligatorio']): ?>
                                                <span class="badge bg-danger">Obligatorio</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($usuario_inscrito): ?>
                                            <a href="<?php echo $base_path; ?>recurso.php?id=<?php echo $recurso['id']; ?>" class="btn btn-sm btn-outline-info">Descargar</a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-lock"></i></span>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                    
                                    <!-- Actividades -->
                                    <?php foreach ($modulo['actividades'] as $actividad): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php 
                                            $icono = 'fa-tasks';
                                            if ($actividad['tipo'] == 'quiz') $icono = 'fa-question-circle';
                                            elseif ($actividad['tipo'] == 'assignment') $icono = 'fa-clipboard-list';
                                            ?>
                                            <i class="fas <?php echo $icono; ?> me-2"></i>
                                            <?php echo htmlspecialchars($actividad['titulo']); ?>
                                            <?php if ($actividad['obligatorio']): ?>
                                                <span class="badge bg-danger">Obligatorio</span>
                                            <?php endif; ?>
                                            <?php if ($actividad['puntuacion'] > 0): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $actividad['puntuacion']; ?> puntos</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($usuario_inscrito): ?>
                                            <a href="<?php echo $base_path; ?>actividad.php?id=<?php echo $actividad['id']; ?>" class="btn btn-sm btn-outline-success">Realizar</a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-lock"></i></span>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Comentarios y valoraciones -->
        <div class="row mt-5">
            <div class="col-12">
                <h2>Valoraciones y comentarios</h2>
                
                <?php
                // Consultar valoraciones del curso
        // Consultar valoraciones del curso
        $sql = "SELECT cr.*, u.name as nombre, u.foto_perfil as avatar
                FROM course_ratings cr
                JOIN usuarios u ON cr.usuario_id = u.id
                WHERE cr.curso_id = :curso_id
                ORDER BY cr.id DESC";

                $stmt = $conn->prepare($sql);
                $stmt->execute([':curso_id' => $curso_id]);
                $valoraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calcular valoración promedio
                $sql = "SELECT AVG(rating) as promedio FROM course_ratings WHERE curso_id = :curso_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':curso_id' => $curso_id]);
                $promedio = $stmt->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0;
                ?>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Valoración promedio: <?php echo number_format($promedio, 1); ?>/5</h5>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= round($promedio) ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-2">(<?php echo count($valoraciones); ?> valoraciones)</span>
                            </div>
                        </div>
                        
                        <?php if ($usuario_inscrito): ?>
                        <form action="<?php echo $base_path; ?>valorar-curso.php" method="post" class="mt-3">
                            <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Tu valoración:</label>
                                <div class="star-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="puntuacion" value="<?php echo $i; ?>">
                                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comentario" class="form-label">Tu comentario:</label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar valoración</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Lista de comentarios -->
                <?php if (count($valoraciones) > 0): ?>
                    <?php foreach ($valoraciones as $valoracion): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <?php if (!empty($valoracion['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($valoracion['avatar']); ?>" class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($valoracion['nombre'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($valoracion['nombre'] . ' ' . $valoracion['apellido']); ?></h6>
                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($valoracion['created_at'])); ?></small>
                                </div>
                                <div class="ms-auto">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $valoracion['puntuacion'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($valoracion['comentario'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aún no hay valoraciones para este curso. ¡Sé el primero en valorarlo!
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Cursos relacionados -->
        <div class="row mt-5">
            <div class="col-12">
                <h2>Cursos relacionados</h2>
                
                <?php
                // Consultar cursos de la misma categoría
                $sql = "SELECT id, nombre, imagen, precio, nivel, total_lecciones 
                        FROM cursos 
                        WHERE categoria = :categoria AND id != :curso_id AND estado = 'publicado' AND estate = 'activo'
                        ORDER BY fecha_creacion DESC
                        LIMIT 4";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':categoria' => $curso['categoria'],
                    ':curso_id' => $curso_id
                ]);
                $cursos_relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="row">
                    <?php foreach ($cursos_relacionados as $curso_rel): ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($curso_rel['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($curso_rel['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($curso_rel['nombre']); ?>">
                            <?php else: ?>
                                <div class="bg-light p-4 text-center">
                                    <i class="fas fa-book-open fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($curso_rel['nombre']); ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($curso_rel['nivel']); ?></span>
                                    <span class="badge bg-info"><?php echo $curso_rel['total_lecciones']; ?> lecciones</span>
                                </p>
                                <?php if ($curso_rel['precio'] > 0): ?>
                                    <p class="fw-bold"><?php echo number_format($curso_rel['precio'], 2); ?> €</p>
                                <?php else: ?>
                                    <p class="fw-bold text-success">Gratis</p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="<?php echo $base_path; ?>curso.php?id=<?php echo $curso_rel['id']; ?>" class="btn btn-outline-primary btn-sm d-block">Ver curso</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($cursos_relacionados) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-light">
                            No hay cursos relacionados disponibles.
                        </div>
                    </div>
                    <?php endif; ?>
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

    
    <!-- JavaScript necesario -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para el sistema de valoración por estrellas
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-rating input');
            stars.forEach(star => {
                star.addEventListener('change', function() {
                    const rating = this.value;
                    stars.forEach(s => {
                        const starValue = s.value;
                        const starLabel = s.nextElementSibling;
                        if (starValue <= rating) {
                            starLabel.classList.add('active');
                        } else {
                            starLabel.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
    
    <style>
        /* Estilos generales para un diseño más compacto */
body {
    line-height: 1.5;
}

h1, h2, h3, h4, h5, h6 {
    margin-top: 0;
    margin-bottom: 0.75rem;
}

p {
    margin-bottom: 0.75rem;
}

.mt-4 {
    margin-top: 1.5rem !important;
}

.mt-5 {
    margin-top: 2rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.py-5 {
    padding-top: 2rem !important;
    padding-bottom: 2rem !important;
}
        /* Estilos para el sistema de valoración por estrellas */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            font-size: 1.5rem;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            color: #ddd;
            cursor: pointer;
            margin: 0 5px;
        }
        
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffc107;
        }
        
        .star-rating .active {
            color: #ffc107;
        }
        /* Ajustar el espaciado en la cabecera del curso */
.curso-header {
    margin-bottom: 2rem;
}

.curso-header h1 {
    margin-bottom: 0.5rem;
}

.curso-header .badges {
    margin-bottom: 0.75rem;
}

.curso-meta {
    margin-top: 0.5rem;
    margin-bottom: 1rem;
}

/* Reducir el espacio entre secciones */
.section-divider {
    margin: 2rem 0;
}
/* Estilos para el acordeón de módulos */
.accordion-item {
    margin-bottom: 0.5rem;
    border: 1px solid rgba(0,0,0,.125);
}

.accordion-button {
    width: 100%;
    padding: 0.75rem 1rem;
}

.accordion-body {
    padding: 1rem;
}

.list-group-item {
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0,0,0,.125);
}
/* Ajustes para tarjetas de valoraciones */
.card {
    margin-bottom: 1rem;
}

.card-body {
    padding: 1.25rem;
}

.rating-section {
    margin-bottom: 1.5rem;
}

.star-rating {
    display: inline-flex;
    flex-direction: row-reverse;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.star-rating input {
    display: none;
}

.star-rating label {
    color: #ddd;
    cursor: pointer;
    margin: 0 3px;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffc107;
}

.comment-avatar {
    width: 40px;
    height: 40px;
    object-fit: cover;
}

.comment-author {
    margin-bottom: 0;
}

.comment-date {
    font-size: 0.8rem;
}
/* Ajustes para los cursos relacionados */
.related-courses {
    margin-top: 2rem;
    margin-bottom: 3rem;
}

.course-card {
    height: 100%;
    transition: transform 0.3s;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.course-image {
    height: 160px;
    object-fit: cover;
}

.course-placeholder {
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.course-footer {
    padding: 0.75rem;
    background-color: transparent;
}
    </style>
</body>
</html>