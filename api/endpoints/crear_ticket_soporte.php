<?php
header('Content-Type: application/json');
require_once('../../conexion/db.php');
session_start();

$usuario_id = 7; //$_SESSION['user_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['error' => 'No autorizado', 'code' => 401]);
    exit;
}

// Obtener parámetros de la solicitud
$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';


try {
    // Crear notificación para el equipo de soporte
    $sql = "INSERT INTO notifications (user_id, role, message, icon, `read`, created_at)
            VALUES (0, 'admin', :mensaje, 'ticket', 0, NOW())";

    $stmt = $conn->prepare($sql);
    $mensaje = "Nuevo ticket de soporte: " . $tipo . " - Usuario ID: " . $usuario_id . " - " . $descripcion;
    $stmt->execute([':mensaje' => $mensaje]);

    $ticket_id = $conn->lastInsertId();

    // Notificar al usuario
    $sql = "INSERT INTO notifications (user_id, role, message, icon, `read`, created_at)
            VALUES (:usuario_id, 'usuario', :mensaje, 'info', 0, NOW())";

    $stmt = $conn->prepare($sql);
    $mensaje_usuario = "Tu ticket de soporte #" . $ticket_id . " ha sido creado. Responderemos en breve.";
    $stmt->execute([':usuario_id' => $usuario_id, ':mensaje' => $mensaje_usuario]);

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $ticket_id
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear ticket de soporte: ' . $e->getMessage()
    ]);
}
?>
