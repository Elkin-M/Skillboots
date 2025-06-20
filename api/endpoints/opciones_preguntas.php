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
    $sql = "SELECT o.id, o.pregunta_id, o.texto, o.es_correcta
            FROM opciones o
            JOIN preguntas p ON o.pregunta_id = p.id
            JOIN actividades a ON p.actividad_id = a.id
            JOIN modulos m ON a.modulo_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            JOIN usuarios_cursos uc ON c.id = uc.curso_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY o.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responderJSON([
        'success' => true,
        'data' => $opciones
    ]);

} catch (Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error al obtener opciones de preguntas: ' . $e->getMessage()
    ]);
}
?>
