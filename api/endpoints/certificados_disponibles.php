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
    $sql = "SELECT c.id, c.nombre, uc.fecha_inscripcion, uc.completado
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.usuario_id = :usuario_id
            AND uc.completado = 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $certificados
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener certificados disponibles: ' . $e->getMessage()
    ]);
}
?>
