// ============================================================================
// ARCHIVO: messages/inbox.php
// ============================================================================
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Obtener mensajes del usuario
    $query = "
        SELECT 
            m.id,
            m.asunto,
            m.mensaje,
            m.fecha_envio,
            m.leido,
            u.nombre as remitente_nombre
        FROM mensajes m
        INNER JOIN usuarios u ON m.remitente_id = u.id
        WHERE m.destinatario_id = ?
        ORDER BY m.fecha_envio DESC
        LIMIT 50
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $messages = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Entrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-inbox mr-2"></i>Bandeja de Entrada</h4>
                        <a href="../dashboard/index.php" class="btn btn-secondary btn-sm float-end">
                            <i class="fas fa-arrow-left mr-1"></i>Volver al Dashboard
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No tienes mensajes</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($messages as $message): ?>
                                    <a href="view_message.php?id=<?= $message['id'] ?>" 
                                       class="list-group-item list-group-item-action <?= $message['leido'] ? '' : 'fw-bold' ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <?= htmlspecialchars($message['asunto'] ?: 'Sin asunto') ?>
                                                <?php if (!$message['leido']): ?>
                                                    <span class="badge bg-primary">Nuevo</span>
                                                <?php endif; ?>
                                            </h6>
                                            <small><?= date('d/m/Y H:i', strtotime($message['fecha_envio'])) ?></small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars(substr($message['mensaje'], 0, 100)) ?>...</p>
                                        <small>De: <?= htmlspecialchars($message['remitente_nombre']) ?></small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
