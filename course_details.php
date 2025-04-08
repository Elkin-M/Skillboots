<?php
// Iniciar sesión si es necesario para el seguimiento del usuario
session_start();

// Incluir archivo de conexión a la base de datos

// config/db.php - Archivo de conexión a la base de datos

// Parámetros de conexión
$host = 'localhost';
$dbname = 'elkinmb3';
$username = 'root';
$password = '';

// Crear conexión
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer conjunto de caracteres
$conn->set_charset("utf8mb4");


// Verificar si se proporcionó un ID de curso válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página principal si no hay ID válido
    header('Location: index.php');
    exit;
}

$curso_id = (int)$_GET['id'];

// Obtener detalles del curso
$sql_curso = "SELECT c.*, u.name as instructor_name, u.foto_perfil as instructor_foto 
              FROM cursos c 
              LEFT JOIN usuarios u ON c.instructor_id = u.id 
              WHERE c.id = ? AND c.estate = 'activo'";
$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param('i', $curso_id);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();

if ($result_curso->num_rows === 0) {
    // Curso no encontrado o no activo
    header('Location: index.php');
    exit;
}

$curso = $result_curso->fetch_assoc();

// Obtener los módulos del curso
$sql_modulos = "SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden";
$stmt_modulos = $conn->prepare($sql_modulos);
$stmt_modulos->bind_param('i', $curso_id);
$stmt_modulos->execute();
$result_modulos = $stmt_modulos->get_result();
$modulos = $result_modulos->fetch_all(MYSQLI_ASSOC);

// Obtener valoraciones del curso
$sql_ratings = "SELECT AVG(rating) as promedio, COUNT(*) as total FROM course_ratings WHERE curso_id = ?";
$stmt_ratings = $conn->prepare($sql_ratings);
$stmt_ratings->bind_param('i', $curso_id);
$stmt_ratings->execute();
$ratings = $stmt_ratings->get_result()->fetch_assoc();

// Verificar si el usuario actual está inscrito en el curso
$usuario_inscrito = false;
$progreso_usuario = 0;

if (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];
    $sql_inscripcion = "SELECT * FROM usuarios_cursos WHERE usuario_id = ? AND curso_id = ?";
    $stmt_inscripcion = $conn->prepare($sql_inscripcion);
    $stmt_inscripcion->bind_param('ii', $usuario_id, $curso_id);
    $stmt_inscripcion->execute();
    $result_inscripcion = $stmt_inscripcion->get_result();
    
    if ($result_inscripcion->num_rows > 0) {
        $inscripcion = $result_inscripcion->fetch_assoc();
        $usuario_inscrito = true;
        $progreso_usuario = $inscripcion['progreso'];
    }
}

// Contar el número total de contenidos en el curso
$total_contenidos = 0;
foreach ($modulos as $modulo) {
    $sql_contenidos = "SELECT COUNT(*) as total FROM contenido_modular WHERE modulo_id = ?";
    $stmt_contenidos = $conn->prepare($sql_contenidos);
    $stmt_contenidos->bind_param('i', $modulo['id']);
    $stmt_contenidos->execute();
    $contenidos = $stmt_contenidos->get_result()->fetch_assoc();
    $total_contenidos += $contenidos['total'];
}

// Contar estudiantes inscritos
$sql_estudiantes = "SELECT COUNT(*) as total FROM usuarios_cursos WHERE curso_id = ?";
$stmt_estudiantes = $conn->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param('i', $curso_id);
$stmt_estudiantes->execute();
$estudiantes = $stmt_estudiantes->get_result()->fetch_assoc();
$total_estudiantes = $estudiantes['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($curso['nombre']); ?> - SkillBoots</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Incluir Header/Navbar -->
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Imagen y detalles principales del curso -->
            <div class="col-lg-8">
                <div class="card mb-4 shadow">
                    <img src="<?php echo htmlspecialchars($curso['imagen']); ?>" class="card-img-top course-banner" alt="<?php echo htmlspecialchars($curso['nombre']); ?>">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($curso['nombre']); ?></h1>
                        
                        <div class="d-flex align-items-center mb-3">
                            <!-- Valoración -->
                            <div class="ratings me-3">
                                <?php
                                $rating_promedio = empty($ratings['promedio']) ? 0 : round($ratings['promedio'], 1);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= floor($rating_promedio)) {
                                        echo '<i class="fas fa-star text-warning"></i>';
                                    } elseif ($i - 0.5 <= $rating_promedio) {
                                        echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                    } else {
                                        echo '<i class="far fa-star text-warning"></i>';
                                    }
                                }
                                ?>
                                <span class="ms-2"><?php echo $rating_promedio; ?> (<?php echo $ratings['total']; ?> valoraciones)</span>
                            </div>
                            
                            <!-- Categoría -->
                            <span class="badge bg-primary me-2"><?php echo htmlspecialchars($curso['categoria']); ?></span>
                            
                            <!-- Nivel -->
                            <?php if (!empty($curso['nivel'])): ?>
                            <span class="badge bg-info"><?php echo htmlspecialchars($curso['nivel']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Descripción del curso -->
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>
                        
                        <!-- Instructor -->
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($curso['instructor_foto'])): ?>
                                <img src="<?php echo htmlspecialchars($curso['instructor_foto']); ?>" class="rounded-circle me-2" width="40" height="40" alt="Instructor">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="mb-0">Instructor: <strong><?php echo htmlspecialchars($curso['instructor_name']); ?></strong></p>
                            </div>
                        </div>
                        
                        <!-- Detalles adicionales -->
                        <div class="row text-center mb-4">
                            <div class="col-md-3 col-6 mb-2">
                                <div class="p-3 border rounded">
                                    <i class="fas fa-users mb-2"></i>
                                    <p class="mb-0"><?php echo $total_estudiantes; ?> estudiantes</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="p-3 border rounded">
                                    <i class="fas fa-clock mb-2"></i>
                                    <p class="mb-0"><?php echo $curso['horas_totales']; ?> horas</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="p-3 border rounded">
                                    <i class="fas fa-book mb-2"></i>
                                    <p class="mb-0"><?php echo count($modulos); ?> módulos</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="p-3 border rounded">
                                    <i class="fas fa-list mb-2"></i>
                                    <p class="mb-0"><?php echo $total_contenidos; ?> lecciones</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="d-grid gap-2">
                            <?php if ($usuario_inscrito): ?>
                                <a href="course_content.php?id=<?php echo $curso_id; ?>" class="btn btn-success">
                                    <?php if ($progreso_usuario > 0): ?>
                                        Continuar curso (<?php echo $progreso_usuario; ?>% completado)
                                    <?php else: ?>
                                        Comenzar curso
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                <a href="enroll.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">Inscribirse al curso</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenido del curso (módulos) -->
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Contenido del curso</h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionModulos">
                            <?php foreach ($modulos as $index => $modulo): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $modulo['id']; ?>">
                                        <button class="accordion-button <?php echo ($index > 0) ? 'collapsed' : ''; ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $modulo['id']; ?>" 
                                                aria-expanded="<?php echo ($index === 0) ? 'true' : 'false'; ?>" 
                                                aria-controls="collapse<?php echo $modulo['id']; ?>">
                                            <?php echo htmlspecialchars($modulo['titulo']); ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $modulo['id']; ?>" 
                                         class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" 
                                         aria-labelledby="heading<?php echo $modulo['id']; ?>" 
                                         data-bs-parent="#accordionModulos">
                                        <div class="accordion-body">
                                            <?php if (!empty($modulo['descripcion'])): ?>
                                                <p><?php echo htmlspecialchars($modulo['descripcion']); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php
                                            // Obtener contenidos del módulo
                                            $sql_contenido = "SELECT * FROM contenido_modular WHERE modulo_id = ? ORDER BY orden";
                                            $stmt_contenido = $conn->prepare($sql_contenido);
                                            $stmt_contenido->bind_param('i', $modulo['id']);
                                            $stmt_contenido->execute();
                                            $contenidos = $stmt_contenido->get_result()->fetch_all(MYSQLI_ASSOC);
                                            
                                            if (count($contenidos) > 0):
                                            ?>
                                                <ul class="list-group">
                                                    <?php foreach ($contenidos as $contenido): ?>
                                                        <li class="list-group-item d-flex align-items-center">
                                                            <?php 
                                                            // Iconos según tipo de contenido
                                                            switch ($contenido['tipo']) {
                                                                case 'video':
                                                                    echo '<i class="fas fa-play-circle me-2"></i>';
                                                                    break;
                                                                case 'texto':
                                                                    echo '<i class="fas fa-file-alt me-2"></i>';
                                                                    break;
                                                                case 'quiz':
                                                                    echo '<i class="fas fa-question-circle me-2"></i>';
                                                                    break;
                                                                case 'imagen':
                                                                    echo '<i class="fas fa-image me-2"></i>';
                                                                    break;
                                                                case 'pdf':
                                                                    echo '<i class="fas fa-file-pdf me-2"></i>';
                                                                    break;
                                                            }
                                                            ?>
                                                            <?php echo htmlspecialchars($contenido['titulo']); ?>
                                                            <?php if (!$usuario_inscrito): ?>
                                                                <i class="fas fa-lock ms-auto text-muted"></i>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted">No hay contenido disponible en este módulo.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($modulos) === 0): ?>
                            <p class="text-center text-muted">Este curso aún no tiene módulos disponibles.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección de valoraciones -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h3 class="mb-0">Valoraciones del curso</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Obtener valoraciones detalladas
                        $sql_reviews = "SELECT cr.*, u.name as usuario_nombre, u.foto_perfil 
                                       FROM course_ratings cr 
                                       JOIN usuarios u ON cr.usuario_id = u.id 
                                       WHERE cr.curso_id = ? 
                                       ORDER BY cr.id DESC 
                                       LIMIT 5";
                        $stmt_reviews = $conn->prepare($sql_reviews);
                        $stmt_reviews->bind_param('i', $curso_id);
                        $stmt_reviews->execute();
                        $reviews = $stmt_reviews->get_result()->fetch_all(MYSQLI_ASSOC);
                        
                        if (count($reviews) > 0):
                        ?>
                            <div class="reviews">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="d-flex mb-4">
                                        <?php if (!empty($review['foto_perfil'])): ?>
                                            <img src="<?php echo htmlspecialchars($review['foto_perfil']); ?>" class="rounded-circle me-3" width="50" height="50" alt="Usuario">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($review['usuario_nombre']); ?></h5>
                                            <div class="ratings mb-2">
                                                <?php 
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $review['rating']) {
                                                        echo '<i class="fas fa-star text-warning"></i>';
                                                    } elseif ($i - 0.5 <= $review['rating']) {
                                                        echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star text-warning"></i>';
                                                    }
                                                }
                                                ?>
                                                <span class="ms-2"><?php echo $review['rating']; ?></span>
                                            </div>
                                            <p class="mb-0">Excelente curso, muy recomendado.</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">Este curso aún no tiene valoraciones.</p>
                        <?php endif; ?>
                        
                        <?php if ($usuario_inscrito): ?>
                            <hr>
                            <div class="add-review">
                                <h5>Deja tu valoración</h5>
                                <form action="submit_rating.php" method="post">
                                    <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Tu valoración</label>
                                        <div class="rating-stars">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>">
                                                <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Enviar valoración</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cursos relacionados -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Cursos relacionados</h3>
                
                <div class="row">
                    <?php
                    // Obtener cursos de la misma categoría
                    $sql_relacionados = "SELECT c.*, u.name as instructor_name 
                                        FROM cursos c 
                                        JOIN usuarios u ON c.instructor_id = u.id 
                                        WHERE c.categoria = ? AND c.id != ? AND c.estate = 'activo' AND c.estado = 'publicado' 
                                        LIMIT 3";
                    $stmt_relacionados = $conn->prepare($sql_relacionados);
                    $stmt_relacionados->bind_param('si', $curso['categoria'], $curso_id);
                    $stmt_relacionados->execute();
                    $cursos_relacionados = $stmt_relacionados->get_result()->fetch_all(MYSQLI_ASSOC);
                    
                    foreach ($cursos_relacionados as $curso_rel):
                    ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow">
                                <img src="<?php echo htmlspecialchars($curso_rel['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($curso_rel['nombre']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($curso_rel['nombre']); ?></h5>
                                    <p class="text-muted">Por: <?php echo htmlspecialchars($curso_rel['instructor_name']); ?></p>
                                    <p class="card-text flex-grow-1"><?php echo substr(htmlspecialchars($curso_rel['descripcion']), 0, 100) . '...'; ?></p>
                                    <a href="course_details.php?id=<?php echo $curso_rel['id']; ?>" class="btn btn-primary mt-auto">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($cursos_relacionados) === 0): ?>
                        <div class="col-12">
                            <p class="text-center text-muted">No hay cursos relacionados disponibles.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para las estrellas de valoración interactivas
        document.addEventListener('DOMContentLoaded', function() {
            const ratingLabels = document.querySelectorAll('.rating-stars label');
            
            ratingLabels.forEach(label => {
                label.addEventListener('mouseover', function() {
                    const star = this;
                    const stars = Array.from(this.parentNode.children);
                    const starLabels = stars.filter(el => el.tagName === 'LABEL');
                    const starIndex = starLabels.indexOf(star);
                    
                    starLabels.forEach((s, i) => {
                        if (i <= starIndex) {
                            s.querySelector('i').className = 'fas fa-star text-warning';
                        } else {
                            s.querySelector('i').className = 'far fa-star';
                        }
                    });
                });
                
                label.addEventListener('click', function() {
                    const star = this;
                    const stars = Array.from(this.parentNode.children);
                    const starLabels = stars.filter(el => el.tagName === 'LABEL');
                    const starIndex = starLabels.indexOf(star);
                    
                    const radioId = this.getAttribute('for');
                    document.getElementById(radioId).checked = true;
                });
            });
            
            const ratingContainer = document.querySelector('.rating-stars');
            if (ratingContainer) {
                ratingContainer.addEventListener('mouseleave', function() {
                    const checkedRadio = this.querySelector('input:checked');
                    const stars = Array.from(this.children);
                    const starLabels = stars.filter(el => el.tagName === 'LABEL');
                    
                    if (checkedRadio) {
                        const checkedValue = parseInt(checkedRadio.value);
                        starLabels.forEach((s, i) => {
                            const value = 5 - i;
                            if (value <= checkedValue) {
                                s.querySelector('i').className = 'fas fa-star text-warning';
                            } else {
                                s.querySelector('i').className = 'far fa-star';
                            }
                        });
                    } else {
                        starLabels.forEach(s => {
                            s.querySelector('i').className = 'far fa-star';
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>