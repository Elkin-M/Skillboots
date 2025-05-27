<?php
session_start();
require_once '../../conexion/db.php';

// Deshabilitar mostrar errores en pantalla para producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Forzar header JSON desde el inicio
header('Content-Type: application/json');

try {
    // Verificar CSRF - Usar hash_equals para comparación segura
    $csrf_valid = false;
    $received_token = null;
    $session_token = $_SESSION['csrf_token'] ?? null;
    
    // Obtener token de diferentes fuentes
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $received_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (isset($_POST['csrf_token'])) {
        $received_token = $_POST['csrf_token'];
    } elseif (isset($_GET['csrf_token'])) {
        $received_token = $_GET['csrf_token'];
    }
    
    // Validar CSRF con hash_equals para evitar timing attacks
    if ($session_token && $received_token) {
        $csrf_valid = hash_equals($session_token, $received_token);
    }
    
    if (!$csrf_valid) {
        http_response_code(403);
        echo json_encode([
            'error' => 'CSRF token inválido',
            'success' => false,
            'users' => [],
            'total' => 0
        ]);
        exit;
    }
    
    // Actualizar timestamp del usuario actual si está autenticado
    if (isset($_SESSION['user_id'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO usuarios_online (usuario_id, ultimo_acceso) 
                                   VALUES (:user_id, NOW()) 
                                   ON DUPLICATE KEY UPDATE ultimo_acceso = NOW()");
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
        } catch (PDOException $e) {
            error_log("Error actualizando usuario online: " . $e->getMessage());
            // No fallar aquí, continuar con la consulta de usuarios
        }
    }
    
    // Obtener usuarios online (últimos 5 minutos)
    $stmt = $conn->prepare("SELECT u.id, u.name, u.lastname 
                           FROM usuarios_online uo 
                           JOIN usuarios u ON uo.usuario_id = u.id 
                           WHERE uo.ultimo_acceso > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                           ORDER BY uo.ultimo_acceso DESC 
                           LIMIT 20");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Asegurar que tenemos un array válido
    $users = $users ?: [];
    
    // Contar total de usuarios online
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios_online 
                           WHERE ultimo_acceso > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->execute();
    $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $total_result ? intval($total_result['total']) : 0;
    
    // Limpiar nombres de usuarios para evitar problemas de encoding
    foreach ($users as &$user) {
        $user['name'] = $user['name'] ?? '';
        $user['lastname'] = $user['lastname'] ?? '';
        $user['nombre'] = $user['name']; // Alias para compatibilidad
        $user['apellido'] = $user['lastname']; // Alias para compatibilidad
    }
    unset($user); // Romper referencia
    
    // Devolver datos exitosamente
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total' => $total,
        'debug' => [
            'user_count' => count($users),
            'current_user' => $_SESSION['user_id'] ?? 'no_user',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Error obteniendo usuarios online: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error obteniendo usuarios online',
        'success' => false,
        'users' => [],
        'total' => 0,
        'debug' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Error general en usuarios online: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'success' => false,
        'users' => [],
        'total' => 0,
        'debug' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>