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
    $sql = "SELECT cs.id, cs.titulo, cs.descripcion, cs.fecha, cs.hora_inicio, cs.hora_fin, cs.enlace_reunion
            FROM class_sessions cs
            JOIN cursos c ON cs.curso_id = c.id
            JOIN usuarios_cursos uc ON c.id = uc.curso_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY cs.fecha DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $sesiones
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener sesiones de clase: ' . $e->getMessage()
    ]);
}
?>
