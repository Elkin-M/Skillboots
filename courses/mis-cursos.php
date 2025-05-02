<?php
session_start();
require_once '../conexion/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener todos los cursos donde el estudiante está inscrito
try {
    $sql = "SELECT 
                c.id,
                c.nombre,
                c.descripcion,
                c.imagen,
                c.duracion,
                uc.progreso,
                uc.fecha_inscripcion,
                uc.ultimo_acceso,
                u.name as instructor_nombre,
                u.lastname as instructor_apellido
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            LEFT JOIN usuarios u ON c.instructor_id = u.id
            WHERE uc.usuario_id = :user_id
            ORDER BY uc.ultimo_acceso DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error al obtener cursos: " . $e->getMessage());
    $cursos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos - SkillBoots</title>
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .course-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        .course-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<?php
require_once '../auth/auth.php';
$isLoggedIn = Auth::isAuthenticated();
$userRole = $isLoggedIn ? Auth::getUserRole() : 'visitante';
$userName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

if ($isLoggedIn && $userRole === 'estudiante') {
    include '../includes/navbar-estu.php';
} elseif ($userRole === 'profesor') {
    include '../includes/navbar-pro.php';
} else {
    include '../includes/navbar.php';
}
?>

<div class="container py-5">
    <h2 class="mb-4">Mis Cursos</h2>
    
    <?php if (empty($cursos)): ?>
    <div class="alert alert-info">
        <p class="mb-0">No estás inscrito en ningún curso todavía.</p>
        <a href="catalogo.php" class="btn btn-primary mt-3">Explorar cursos disponibles</a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($cursos as $curso): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card course-card shadow-sm">
                <img src="<?php echo htmlspecialchars($curso['imagen'] ?? 'img/curso-default.jpg'); ?>" 
                     class="card-img-top course-image" 
                     alt="<?php echo htmlspecialchars($curso['nombre']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($curso['nombre']); ?></h5>
                    <p class="card-text text-muted">
                        <small>
                            <i class="fas fa-user-tie me-2"></i>
                            <?php echo htmlspecialchars($curso['instructor_nombre'] . ' ' . $curso['instructor_apellido']); ?>
                        </small>
                    </p>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: <?php echo $curso['progreso']; ?>%"
                             aria-valuenow="<?php echo $curso['progreso']; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $curso['progreso']; ?>%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo htmlspecialchars($curso['duracion']); ?>
                        </small>
                        <small class="text-muted">
                            Último acceso: <?php 
                            $ultimoAcceso = new DateTime($curso['ultimo_acceso']);
                            $ahora = new DateTime();
                            $diff = $ultimoAcceso->diff($ahora);
                            echo $diff->days == 0 ? 'Hoy' : 
                                 ($diff->days == 1 ? 'Ayer' : 
                                 'Hace ' . $diff->days . ' días');
                            ?>
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="iniciar-curso.php?id=<?php echo $curso['id']; ?>" 
                       class="btn btn-primary w-100">
                        Continuar curso
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>