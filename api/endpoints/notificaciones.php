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
    $sql = "SELECT n.id, n.message, n.link, n.icon, n.read, n.created_at
            FROM notifications n
            WHERE n.user_id = :usuario_id
            ORDER BY n.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $notificaciones
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener notificaciones: ' . $e->getMessage()
    ]);
}
?>
