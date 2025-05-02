<?php
header('Content-Type: application/json');
require_once('../../conexion/db.php');
session_start();


$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['error' => 'No autorizado', 'code' => 401]);
    exit;
}
// Obtener parámetros de la solicitud
$motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';

try {
    // Obtener información del usuario
    $sql = "SELECT name, lastname, email FROM usuarios WHERE id = :usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Crear notificación para el equipo de soporte
    $sql = "INSERT INTO notifications (user_id, role, message, icon, `read`, created_at)
            VALUES (0, 'admin', :mensaje, 'support', 0, NOW())";

    $stmt = $conn->prepare($sql);
    $mensaje = "Solicitud de soporte humano - Usuario: " . $usuario['name'] . " " . $usuario['lastname'] .
               " (ID: " . $usuario_id . ") - Motivo: " . $motivo;
    $stmt->execute([':mensaje' => $mensaje]);

    $solicitud_id = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $solicitud_id,
            'usuario' => [
                'email' => $usuario['email']
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al solicitar soporte humano: ' . $e->getMessage()
    ]);
}
?>
