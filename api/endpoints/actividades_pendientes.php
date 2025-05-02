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
    $sql = "SELECT a.id, a.titulo, a.fecha_limite, a.puntuacion, a.tiempo, c.nombre as curso
            FROM actividades a
            JOIN modulos m ON a.unidad_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            JOIN usuarios_cursos uc ON c.id = uc.curso_id
            LEFT JOIN actividades_completadas ac ON a.id = ac.actividad_id AND ac.usuario_id = :usuario_id
            WHERE uc.usuario_id = :usuario_id2
            AND ac.id IS NULL
            AND a.fecha_limite >= CURDATE()
            ORDER BY a.fecha_limite ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id, ':usuario_id2' => $usuario_id]);

    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'actividades' => $actividades
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener actividades pendientes: ' . $e->getMessage()
    ]);
}
?>
