<?php
session_start();
require_once '../conexion/db.php'; // Asegúrate de usar la ruta correcta a tu archivo de conexión
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$curso_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($curso_id <= 0) {
    header('Location: index.php');
    exit;
}
// Verificar si el usuario ya está inscrito en el curso
$sql = "SELECT * FROM usuarios_cursos WHERE curso_id = :curso_id AND usuario_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':curso_id' => $curso_id,
    ':user_id' => $user_id
]);
if ($stmt->rowCount() > 0) {
    header('Location: course.php?id=' . $curso_id . '&message=Ya estás inscrito en este curso.');
    exit;
}
// Si el formulario se envía, procesar la inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO usuarios_cursos (curso_id, usuario_id, fecha_inscripcion) VALUES (:curso_id, :user_id, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':user_id' => $user_id
    ]);
    header('Location: ../courses/iniciar-curso.php?id=' . $curso_id . '&message=Inscripción exitosa.');
    exit;
}
// Obtener información del curso
$sql = "SELECT c.nombre, c.descripcion, c.imagen, c.precio, c.duracion, u.name as instructor 
        FROM cursos c 
        LEFT JOIN usuarios u ON c.instructor_id = u.id 
        WHERE c.id = :curso_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':curso_id' => $curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscribirse en <?php echo htmlspecialchars($curso['nombre']); ?> - SkillBoots</title>
    <!-- Incluir CSS y JS necesarios -->
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <style>
        body {
            background-color: #f8f9fa;
            color: #3a3b45;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .inscription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0;
            background-color: transparent;
        }
        .course-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .course-image {
            height: 240px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .course-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.6));
        }
        .course-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        .course-details {
            padding: 25px;
        }
        .course-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 10px;
            color: #6c757d;
        }
        .info-item i {
            margin-right: 8px;
            color: #FF6600;
        }
        .course-description {
            margin-bottom: 25px;
            color: #495057;
            line-height: 1.6;
        }
        .btn-confirm {
            background-color: #FF6600;
            border-color: #FF6600;
            color: white;
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 102, 0, 0.2);
        }
        .btn-confirm:hover {
            background-color: #e05a00;
            border-color: #e05a00;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 102, 0, 0.3);
        }
        .btn-back {
            background-color: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            margin-right: 15px;
        }
        .btn-back:hover {
            background-color: #6c757d;
            color: white;
        }
        .form-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .confirmation-text {
            font-size: 1.1rem;
            font-weight: 500;
            color: #495057;
            margin-bottom: 25px;
        }
        .price-tag {
            font-size: 2rem;
            font-weight: 700;
            color: #FF6600;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .free-course {
            color: #28a745;
        }
        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
                align-items: flex-start;
            }
            .btn-back, .btn-confirm {
                width: 100%;
                margin-bottom: 10px;
                margin-right: 0;
            }
        }
    </style>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>

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
    include '../includes/navbar-estu.php'; // Navbar para estudiantes
} elseif ($pageData['userRole'] === 'profesor'){
    include '../includes/navbar-pro.php';
}else{
    include '../includes/navbar.php';
}
?>

<div class="inscription-container">
    <div class="course-card">
        <div class="course-image" style="background-image: url('<?php echo !empty($curso['imagen']) ? htmlspecialchars($curso['imagen']) : 'img/curso-default.jpg'; ?>')">
            <h1 class="course-title"><?php echo htmlspecialchars($curso['nombre']); ?></h1>
        </div>
        <div class="course-details">
            <div class="course-info">
                <div class="info-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Instructor: <?php echo htmlspecialchars($curso['instructor'] ?? 'No especificado'); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span>Duración: <?php echo htmlspecialchars($curso['duracion'] ?? 'No especificada'); ?></span>
                </div>
                <?php if(isset($curso['precio']) && $curso['precio'] > 0): ?>
                <div class="price-tag">
                    $<?php echo number_format($curso['precio'], 2); ?>
                </div>
                <?php else: ?>
                <div class="price-tag free-course">
                    Gratis
                </div>
                <?php endif; ?>
            </div>
            
            <div class="course-description">
                <?php echo !empty($curso['descripcion']) ? htmlspecialchars($curso['descripcion']) : 'No hay descripción disponible para este curso.'; ?>
            </div>
            
            <p class="confirmation-text">
                <i class="fas fa-info-circle text-primary me-2"></i>
                Al inscribirte en este curso tendrás acceso inmediato a todo su contenido. 
                ¿Estás seguro de que deseas continuar?
            </p>
            
            <form action="" method="post" class="form-container">
            <a href="javascript:history.back()" class="btn btn-back">
    <i class="fas fa-arrow-left me-2"></i>Cancelar
</a>

                <button type="submit" class="btn btn-confirm">
                    <i class="fas fa-check-circle me-2"></i>Confirmar Inscripción
                </button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript necesario -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>