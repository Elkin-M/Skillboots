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
    $sql = "SELECT pc.id, pc.curso_id, pc.modulo_id, pc.contenido_id, pc.completado, pc.fecha_acceso
            FROM progreso_contenido pc
            WHERE pc.usuario_id = :usuario_id
            ORDER BY pc.fecha_acceso DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $progreso = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $progreso
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener progreso de contenido: ' . $e->getMessage()
    ]);
}
?>
