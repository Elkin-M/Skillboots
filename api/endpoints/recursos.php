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
    $sql = "SELECT r.id, r.unidad_id, r.titulo, r.tipo, r.contenido, r.url, r.obligatorio, r.texto_contenido
            FROM recursos r
            JOIN modulos m ON r.unidad_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            JOIN usuarios_cursos uc ON c.id = uc.curso_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY r.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $recursos
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener recursos: ' . $e->getMessage()
    ]);
}
?>
