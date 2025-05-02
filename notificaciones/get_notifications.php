<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion/db.php'; // Asegúrate de que la ruta sea correcta

session_start(); // Iniciar sesión si no está iniciada

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$response = [
    'unread' => 0,
    'notifications' => []
];

try {
    $query = "SELECT id, message, link, icon, `read`, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);

    $notifications = [];
    $unreadCount = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'id' => $row['id'],
            'message' => $row['message'],
            'link' => $row['link'],
            'icon' => $row['icon'] ?? 'fa-bell',
            'read' => $row['read'] == 1, // Convertir en booleano
            'time_ago' => date("d/m/Y H:i", strtotime($row['created_at'])) // Formateo de fecha
        ];

        if ($row['read'] == 0) {
            $unreadCount++;
        }
    }

    $response['notifications'] = $notifications;
    $response['unread'] = $unreadCount;
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
