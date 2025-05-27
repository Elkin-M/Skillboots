<?php
// ============================================================================
// ARCHIVO: messages/view_message.php
// ============================================================================
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message_id = $_GET['id'] ?? null;

if (!$message_id) {
    header('Location: inbox.php');
    exit;
}

try {
    // Obtener mensaje
    $query = "
        SELECT 
            m.id,
            m.asunto,
            m.mensaje,
            m.fecha_envio,
            m.leido,
            u.nombre as remitente_nombre,
            u.email as remitente_email
        FROM mensajes m
        INNER JOIN usuarios u ON m.remitente_id = u.id
        WHERE m.id = ? AND m.destinatario_id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$message_id, $user_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        header('Location: inbox.php');
        exit;
    }
    
    // Marcar como leído
    if (!$message['leido']) {
        $mark_read = "UPDATE mensajes SET leido = 1 WHERE id = ?";
        $pdo->prepare($mark_read)->execute([$message_id]);
    }
    
} catch (PDOException $e) {
    header('Location: inbox.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($message['asunto'] ?: 'Sin asunto') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($message['asunto'] ?: 'Sin asunto') ?></h4>
                        <a href="inbox.php" class="btn btn-secondary btn-sm float-end">
                            <i class="fas fa-arrow-left mr-1"></i>Volver a la bandeja
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>De:</strong> <?= htmlspecialchars($message['remitente_nombre']) ?> 
                            (<?= htmlspecialchars($message['remitente_email']) ?>)<br>
                            <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($message['fecha_envio'])) ?>
                        </div>
                        
                        <hr>
                        
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($message['mensaje'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>