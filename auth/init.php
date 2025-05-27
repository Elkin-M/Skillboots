<?php 
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/skillboots/conexion/db.php';

if (isset($_SESSION['user_id'])) {
    try {
        // Actualizar último acceso
        $stmt = $conn->prepare("UPDATE usuarios_online SET ultimo_acceso = NOW() WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error actualizando usuarios_online: " . $e->getMessage());
    }
}

// Incluir el script de actividad
echo '<script src="/skillboots/assets/js/inactividad.js"></script>';
?>