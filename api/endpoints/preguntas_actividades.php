<?php
require_once '../config.php';

// Autenticar usuario
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $token);

$usuario_id = autenticarUsuario($token);
if (!$usuario_id) {
    responderJSON(['error' => 'No autorizado', 'code' => 401]);
    exit;
}

try {
    $sql = "SELECT p.id, p.actividad_id, p.texto, p.tipo, p.respuesta_correcta
            FROM preguntas p
            JOIN actividades a ON p.actividad_id = a.id
            JOIN modulos m ON a.unidad_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            JOIN usuarios_cursos uc ON c.id = uc.curso_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY p.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $preguntas
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener preguntas de actividades: ' . $e->getMessage()
    ]);
}
?>
