<?php
session_start();
require_once '../../conexion/db.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Consulta para obtener mensajes no leídos
    $query = "
        SELECT 
            m.id,
            m.asunto,
            m.mensaje,
            m.fecha_envio,
            u.name as remitente_nombre,
            u.email as remitente_email
        FROM mensajes m
        INNER JOIN usuarios u ON m.remitente_id = u.id
        WHERE m.destinatario_id = ? 
        AND m.leido = 0
        ORDER BY m.fecha_envio DESC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas
    foreach ($messages as &$message) {
        $message['fecha_envio'] = date('d/m/Y H:i', strtotime($message['fecha_envio']));
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}