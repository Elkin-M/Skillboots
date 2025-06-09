<?php
session_start();
require_once '../conexion/db.php';
require_once '../auth/auth.php';

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

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $estado = $_POST['estado'];
    $total_lecciones = $_POST['total_lecciones'];
    $horas_totales = $_POST['horas_totales'];
    $imagen = $_POST['imagen'];

    // Actualizar los detalles del curso en la base de datos
    $sql = "UPDATE cursos SET
                nombre = :nombre,
                descripcion = :descripcion,
                categoria = :categoria,
                estado = :estado,
                total_lecciones = :total_lecciones,
                horas_totales = :horas_totales,
                imagen = :imagen
            WHERE id = :course_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmt->bindParam(':total_lecciones', $total_lecciones, PDO::PARAM_INT);
    $stmt->bindParam(':horas_totales', $horas_totales, PDO::PARAM_INT);
    $stmt->bindParam(':imagen', $imagen, PDO::PARAM_STR);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirigir a la página de detalles del curso
    header("Location: view_course.php?id=$course_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - <?php echo htmlspecialchars($course['nombre']); ?></title>
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar-pro.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Editar Curso</h2>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="nombre">Nombre del Curso</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($course['nombre']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($course['descripcion']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="categoria">Categoría</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" value="<?php echo htmlspecialchars($course['categoria']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select class="form-control" id="estado" name="estado" required>
                                    <option value="publicado" <?php echo $course['estado'] === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                                    <option value="borrador" <?php echo $course['estado'] === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                    <option value="archivado" <?php echo $course['estado'] === 'archivado' ? 'selected' : ''; ?>>Archivado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="total_lecciones">Total de Lecciones</label>
                                <input type="number" class="form-control" id="total_lecciones" name="total_lecciones" value="<?php echo htmlspecialchars($course['total_lecciones']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="horas_totales">Horas Totales</label>
                                <input type="number" class="form-control" id="horas_totales" name="horas_totales" value="<?php echo htmlspecialchars($course['horas_totales']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="imagen">Imagen</label>
                                <input type="text" class="form-control" id="imagen" name="imagen" value="<?php echo htmlspecialchars($course['imagen']); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            <a href="view_courses.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
