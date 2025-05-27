<?php
session_start();
require_once '/skillboots/conexion/db.php';

if (isset($_SESSION['user_id'])) {
    // Si no existe, lo inserta, si existe no hace nada (puedes actualizar si prefieres)
    $stmt = $conn->prepare("INSERT INTO usuarios_online (usuario_id, ultimo_acceso)
                            VALUES (:usuario_id, NOW())
                            ON DUPLICATE KEY UPDATE ultimo_acceso = NOW()");
    $stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
}
?>
