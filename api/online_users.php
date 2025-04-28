<?php
session_start();
require_once '../conexion/db.php';

// Verificar CSRF
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'CSRF token inválido']);
    exit;
}

// Actualizar timestamp del usuario actual
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO usuarios_online (usuario_id, ultimo_acceso) 
                               VALUES (:user_id, NOW()) 
                               ON DUPLICATE KEY UPDATE ultimo_acceso = NOW()");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Error actualizando usuario online: " . $e->getMessage());
    }
}

// Obtener usuarios online (últimos 5 minutos)
try {
    $stmt = $conn->prepare("SELECT u.id, u.name, u.lastname 
                           FROM usuarios_online uo 
                           JOIN usuarios u ON uo.usuario_id = u.id 
                           WHERE uo.ultimo_acceso > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                           ORDER BY uo.ultimo_acceso DESC 
                           LIMIT 20");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total de usuarios online
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios_online 
                           WHERE ultimo_acceso > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->execute();
    $total = $stmt->fetchColumn();
    
    // Devolver datos
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total' => $total
    ]);
    
} catch (PDOException $e) {
    error_log("Error obteniendo usuarios online: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Error obteniendo usuarios online']);
}
?>