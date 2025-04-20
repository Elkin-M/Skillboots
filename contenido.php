<?php
session_start();
require_once 'conexion/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir al login si no está autenticado
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verificar si se proporcionó un ID de contenido válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página principal si no hay ID válido
    header('Location: index.php');
    exit;
}

$contenido_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$modo_embebido = isset($_GET['embedded']) && $_GET['embedded'] == '1';

try {
    // Obtener información del contenido
    $sql = "SELECT cm.*, m.titulo as modulo_titulo, m.curso_id, c.nombre as curso_nombre
            FROM contenido_modular cm
            JOIN modulos m ON cm.modulo_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            WHERE cm.id = :contenido_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':contenido_id' => $contenido_id]);

    if ($stmt->rowCount() === 0) {
        echo "Contenido no encontrado";
        exit;
    }

    $contenido = $stmt->fetch(PDO::FETCH_ASSOC);
    $curso_id = $contenido['curso_id'];

    // Verificar si el usuario está inscrito en el curso
    $sql = "SELECT * FROM usuarios_cursos WHERE curso_id = :curso_id AND usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':user_id' => $user_id
    ]);

    if ($stmt->rowCount() === 0) {
        // Si no está inscrito, redirigir a la página del curso
        header('Location: curso.php?id=' . $curso_id);
        exit;
    }

    // Obtener el contenido anterior y siguiente
    $sql = "SELECT cm.id, cm.titulo, cm.tipo
            FROM contenido_modular cm
            JOIN modulos m ON cm.modulo_id = m.id
            WHERE m.curso_id = :curso_id
            ORDER BY m.orden ASC, cm.orden ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    $todos_contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $anterior = null;
    $siguiente = null;
    $actual_encontrado = false;

    foreach ($todos_contenidos as $index => $cont) {
        if ($cont['id'] == $contenido_id) {
            $actual_encontrado = true;
            if ($index > 0) {
                $anterior = $todos_contenidos[$index - 1];
            }
        } elseif ($actual_encontrado && $siguiente === null) {
            $siguiente = $cont;
            break;
        }
    }

    // Marcar contenido como visto
    $sql = "SELECT * FROM contenido_visto WHERE contenido_id = :contenido_id AND usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':contenido_id' => $contenido_id,
        ':user_id' => $user_id
    ]);

    $ya_visto = $stmt->rowCount() > 0;

    if (!$ya_visto) {
        $sql = "INSERT INTO contenido_visto (contenido_id, usuario_id, fecha_visto) VALUES (:contenido_id, :user_id, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':contenido_id' => $contenido_id,
            ':user_id' => $user_id
        ]);
    }

} catch (Exception $e) {
    error_log("Error en contenido.php: " . $e->getMessage());
    echo "Error al cargar el contenido. Por favor, inténtalo de nuevo.";
    exit;
}

// Construir las rutas base para enlaces
$base_path = dirname($_SERVER['PHP_SELF']);
if (substr($base_path, -1) !== '/') {
    $base_path .= '/';
}

// Si está embebido, usar una plantilla reducida
if ($modo_embebido) {
    // Plantilla embebida (sin menú ni footer completo)
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($contenido['titulo']); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </head>
    <body>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <h1><?php echo htmlspecialchars($contenido['titulo']); ?></h1>
                    <div class="content-body">
                        <?php if ($contenido['tipo'] == 'texto'): ?>
                            <p><?php echo nl2br(htmlspecialchars($contenido['contenido'])); ?></p>
                        <?php elseif ($contenido['tipo'] == 'video'): ?>
                            <video controls class="w-100">
                                <source src="<?php echo htmlspecialchars($contenido['url']); ?>" type="video/mp4">
                                Tu navegador no soporta la etiqueta de video.
                            </video>
                        <?php elseif ($contenido['tipo'] == 'audio'): ?>
                            <audio controls class="w-100">
                                <source src="<?php echo htmlspecialchars($contenido['url']); ?>" type="audio/mpeg">
                                Tu navegador no soporta la etiqueta de audio.
                            </audio>
                        <?php elseif ($contenido['tipo'] == 'imagen'): ?>
                            <img src="<?php echo htmlspecialchars($contenido['url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($contenido['titulo']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <?php if ($anterior): ?>
                            <a href="contenido.php?id=<?php echo $anterior['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        <?php if ($siguiente): ?>
                            <a href="contenido.php?id=<?php echo $siguiente['id']; ?>" class="btn btn-primary ms-auto">
                                Siguiente <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Notificar al iframe padre que el contenido ha sido visto
            window.parent.postMessage({ action: 'contenidoVisto', contenidoId: <?php echo $contenido_id; ?> }, '*');
        </script>
    </body>
    </html>
    <?php
} else {
    // Plantilla completa (con menú y footer)
    require_once 'roles/auth.php';

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($contenido['titulo']); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </head>
    <body>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <h1><?php echo htmlspecialchars($contenido['titulo']); ?></h1>
                    <div class="content-body">
                        <?php if ($contenido['tipo'] == 'texto'): ?>
                            <p><?php echo nl2br(htmlspecialchars($contenido['contenido'])); ?></p>
                        <?php elseif ($contenido['tipo'] == 'video'): ?>
                            <video controls class="w-100">
                                <source src="<?php echo htmlspecialchars($contenido['url']); ?>" type="video/mp4">
                                Tu navegador no soporta la etiqueta de video.
                            </video>
                        <?php elseif ($contenido['tipo'] == 'audio'): ?>
                            <audio controls class="w-100">
                                <source src="<?php echo htmlspecialchars($contenido['url']); ?>" type="audio/mpeg">
                                Tu navegador no soporta la etiqueta de audio.
                            </audio>
                            <?php elseif ($contenido['tipo'] == 'imagen'): ?>
    <img src="<?php echo htmlspecialchars($contenido['contenido']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($contenido['titulo']); ?>">
<?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <?php if ($anterior): ?>
                            <a href="contenido.php?id=<?php echo $anterior['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        <?php if ($siguiente): ?>
                            <a href="contenido.php?id=<?php echo $siguiente['id']; ?>" class="btn btn-primary ms-auto">
                                Siguiente <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Notificar al iframe padre que el contenido ha sido visto
            window.parent.postMessage({ action: 'contenidoVisto', contenidoId: <?php echo $contenido_id; ?> }, '*');
        </script>
        <!-- Footer -->
    </body>
    </html>
    <?php
}
?>
