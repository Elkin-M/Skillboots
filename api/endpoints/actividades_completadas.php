<?php
header('Content-Type: application/json');
require_once('../../conexion/db.php');
session_start();

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['error' => 'No autorizado', 'code' => 401]);
    exit;
}

try {
    $sql = "SELECT ac.id, a.titulo, ac.calificacion, ac.fecha_completado, c.nombre as curso
            FROM actividades_completadas ac
            JOIN actividades a ON ac.actividad_id = a.id
            JOIN modulos m ON a.unidad_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            WHERE ac.usuario_id = :usuario_id
            ORDER BY ac.fecha_completado DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $actividades
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener actividades completadas: ' . $e->getMessage()
    ]);
}
?>
