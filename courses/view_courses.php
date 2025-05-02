<?php
session_start();
require_once '../conexion/db.php';
require_once '../roles/auth.php';

// Verificar si el usuario está autenticado
if (!Auth::isAuthenticated()) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del curso de la URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta para obtener los detalles del curso
$sql = "SELECT * FROM cursos WHERE id = :course_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el curso existe
if (!$course) {
    echo "Curso no encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Curso - <?php echo htmlspecialchars($course['nombre']); ?></title>
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include 'navbar-pro.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($course['nombre']); ?></h2>
                        <p class="card-text"><?php echo htmlspecialchars($course['descripcion']); ?></p>
                        <p><strong>Categoría:</strong> <?php echo htmlspecialchars($course['categoria']); ?></p>
                        <p><strong>Estado:</strong> <?php echo htmlspecialchars($course['estado']); ?></p>
                        <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($course['fecha_creacion']); ?></p>
                        <p><strong>Total de Lecciones:</strong> <?php echo htmlspecialchars($course['total_lecciones']); ?></p>
                        <p><strong>Horas Totales:</strong> <?php echo htmlspecialchars($course['horas_totales']); ?></p>
                        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_id']); ?></p>
                        <?php if (!empty($course['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($course['imagen']); ?>" alt="Imagen del curso" class="img-fluid">
                        <?php endif; ?>
                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Editar Curso</a>
                        <a href="dashboard-profesor.php" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
