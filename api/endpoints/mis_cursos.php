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
    $sql = "SELECT c.id, c.nombre, c.descripcion, c.categoria, uc.progreso, uc.ultimo_acceso
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.usuario_id = :usuario_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cursos' => $cursos
    ]);
    

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener cursos: ' . $e->getMessage()
    ]);
}
?>
