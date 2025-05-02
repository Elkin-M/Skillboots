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
    $sql = "SELECT cv.id, cv.contenido_id, cv.fecha_visto
            FROM contenido_visto cv
            WHERE cv.usuario_id = :usuario_id
            ORDER BY cv.fecha_visto DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $contenidos_vistos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $contenidos_vistos
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener contenido visto: ' . $e->getMessage()
    ]);
}
?>
