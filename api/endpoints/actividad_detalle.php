<?php
header('Content-Type: application/json');
require_once('../../conexion/db.php');
session_start();


$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['error' => 'No autorizado', 'code' => 401]);
    exit;
}

// Obtener el parámetro de búsqueda
$nombre_actividad = isset($_GET['nombre_actividad']) ? $_GET['nombre_actividad'] : '';

try {
    $sql = "SELECT a.id, a.titulo, a.contenido as descripcion, a.fecha_limite, a.puntuacion, a.tiempo,
                c.nombre as curso, m.titulo as modulo
            FROM actividades a
            JOIN modulos m ON a.modulo_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            JOIN usuarios_cursos uc ON c.id = uc.curso_id
            WHERE uc.usuario_id = :usuario_id
            AND a.titulo LIKE :nombre_actividad";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id, ':nombre_actividad' => '%'.$nombre_actividad.'%']);

    $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($actividad) {
        $actividad['instrucciones'] = "seguir las instrucciones detalladas en la descripción y entregarla antes de la fecha límite";
        echo json_encode([
            'success' => true,
            'data' => $actividad
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la actividad especificada'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalle de actividad: ' . $e->getMessage()
    ]);
}
?>
