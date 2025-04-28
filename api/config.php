<?php
// config.php
header('Content-Type: application/json');
require_once '../conexion/db.php';

// Función para responder en formato JSON
function responderJSON($data) {
    echo json_encode($data);
    exit;
}

// Función para autenticar usuarios
function autenticarUsuario($token) {
    global $conn;
    $sql = "SELECT id FROM usuarios WHERE token = :token AND token_expira > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result['id'];
    }
    return false;
}
?>
