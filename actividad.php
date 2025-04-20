<?php
session_start();
require_once 'conexion/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir al login si no está autenticado
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verificar si se proporcionó un ID de actividad válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página principal si no hay ID válido
    header('Location: index.php');
    exit;
}

$actividad_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$modo_embebido = isset($_GET['embedded']) && $_GET['embedded'] == '1';

try {
    // Obtener información de la actividad
    $sql = "SELECT a.*, m.titulo as modulo_titulo, m.curso_id, c.nombre as curso_nombre
            FROM actividades a
            JOIN modulos m ON a.unidad_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            WHERE a.id = :actividad_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':actividad_id' => $actividad_id]);

    if ($stmt->rowCount() === 0) {
        echo "Actividad no encontrada";
        exit;
    }

    $actividad = $stmt->fetch(PDO::FETCH_ASSOC);
    $curso_id = $actividad['curso_id'];

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

    // Obtener las preguntas de la actividad
    $sql = "SELECT * FROM preguntas WHERE actividad_id = :actividad_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':actividad_id' => $actividad_id]);
    $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si la actividad ya ha sido completada
    $sql = "SELECT * FROM actividades_completadas WHERE actividad_id = :actividad_id AND usuario_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':actividad_id' => $actividad_id,
        ':user_id' => $user_id
    ]);

    $actividad_completada = $stmt->rowCount() > 0;

    if ($actividad_completada) {
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $calificacion = $resultado['calificacion'];
        $fecha_completado = $resultado['fecha_completado'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Procesar las respuestas del usuario
        $respuestas_usuario = $_POST['respuestas'];
        $puntuacion_total = 0;
        $puntuacion_obtenida = 0;

        foreach ($preguntas as $pregunta) {
            // Usar un valor por defecto de 1 si no existe puntuacion
            $puntos_pregunta = isset($pregunta['puntuacion']) ? $pregunta['puntuacion'] : 1;
            $puntuacion_total += $puntos_pregunta;
            $respuesta_correcta = $pregunta['respuesta_correcta'];
            $respuesta_usuario = isset($respuestas_usuario[$pregunta['id']]) ? $respuestas_usuario[$pregunta['id']] : null;
        
            if ($respuesta_usuario == $respuesta_correcta) {
                $puntuacion_obtenida += $puntos_pregunta;
            }
        }
        
        // Evitar división por cero
        if ($puntuacion_total > 0) {
            $calificacion = ($puntuacion_obtenida / $puntuacion_total) * 100;
        } else {
            $calificacion = 0; // O cualquier otro valor predeterminado
        }

        $calificacion = ($puntuacion_obtenida / $puntuacion_total) * 100;

        // Guardar el resultado en la base de datos
        $sql = "INSERT INTO actividades_completadas (actividad_id, usuario_id, calificacion, fecha_completado) VALUES (:actividad_id, :user_id, :calificacion, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':actividad_id' => $actividad_id,
            ':user_id' => $user_id,
            ':calificacion' => $calificacion
        ]);

        // Notificar al iframe padre que la actividad ha sido completada
        echo "<script>window.parent.postMessage({ action: 'actividadCompletada', actividadId: $actividad_id }, '*');</script>";

        // Redirigir para evitar reenvío del formulario
        header('Location: actividad.php?id=' . $actividad_id);
        exit;
    }

} catch (Exception $e) {
    error_log("Error en actividad.php: " . $e->getMessage());
    echo "Error al cargar la actividad. Por favor, inténtalo de nuevo.";
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
        <title><?php echo htmlspecialchars($actividad['titulo']); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </head>
    <body>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <h1><?php echo htmlspecialchars($actividad['titulo']); ?></h1>
                    <?php if ($actividad_completada): ?>
                        <div class="alert alert-success">
                            <p>Actividad completada el <?php echo htmlspecialchars($fecha_completado); ?>.</p>
                            <p>Tu calificación: <?php echo $calificacion; ?>%</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
<?php foreach ($preguntas as $pregunta): ?>
    <div class="mb-3">
        <h5><?php echo htmlspecialchars($pregunta['texto']); ?></h5>
        <?php
        // Obtener opciones de la tabla de opciones
        $sql_opciones = "SELECT * FROM opciones WHERE pregunta_id = :pregunta_id";
        $stmt_opciones = $conn->prepare($sql_opciones);
        $stmt_opciones->execute([':pregunta_id' => $pregunta['id']]);
        $opciones_pregunta = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);
        
        // Letras para las opciones
        $letras = ['A', 'B', 'C', 'D'];
        $i = 0;
        
        foreach ($opciones_pregunta as $opcion):
            if ($i < count($letras)):
                $letra = $letras[$i];
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" 
                        id="opcion_<?php echo $pregunta['id'] . '_' . $letra; ?>" 
                        value="<?php echo $letra; ?>"
                        <?php echo ($opcion['es_correcta'] == 1 && $pregunta['respuesta_correcta'] == $letra) ? 'data-correct="true"' : ''; ?>>
                    <label class="form-check-label" for="opcion_<?php echo $pregunta['id'] . '_' . $letra; ?>">
                        <?php echo $letra . '. ' . htmlspecialchars($opcion['texto']); ?>
                    </label>
                </div>
                <?php
                $i++;
            endif;
        endforeach;
        ?>
    </div>
<?php endforeach; ?>
                            <button type="submit" class="btn btn-primary">Enviar respuestas</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // Plantilla completa (con menú y footer)
    require_once 'roles/auth.php';

    // Optimizar las llamadas a `Auth::isAuthenticated()`
    $isLoggedIn = Auth::isAuthenticated();
    $userRole = $isLoggedIn ? Auth::getUserRole() : 'visitante';
    $userName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

    // Definir los datos de la página
    $pageData = [
        'isLoggedIn' => $isLoggedIn,
        'userRole' => $userRole,
        'userName' => $userName
    ];

    // Incluir la navbar según el rol del usuario
    if ($isLoggedIn && $userRole === 'estudiante') {
        include 'navbar-estu.php'; // Navbar para estudiantes
    } elseif ($pageData['userRole'] === 'profesor'){
        include 'navbar-pro.php';
    } else {
        include 'navbar.php';
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($actividad['titulo']); ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </head>
    <body>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <h1><?php echo htmlspecialchars($actividad['titulo']); ?></h1>
                    <?php if ($actividad_completada): ?>
                        <div class="alert alert-success">
                            <p>Actividad completada el <?php echo htmlspecialchars($fecha_completado); ?>.</p>
                            <p>Tu calificación: <?php echo $calificacion; ?>%</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
<?php foreach ($preguntas as $pregunta): ?>
    <div class="mb-3">
        <h5><?php echo htmlspecialchars($pregunta['texto']); ?></h5>
        <?php
        // Obtener opciones de la tabla de opciones
        $sql_opciones = "SELECT * FROM opciones WHERE pregunta_id = :pregunta_id";
        $stmt_opciones = $conn->prepare($sql_opciones);
        $stmt_opciones->execute([':pregunta_id' => $pregunta['id']]);
        $opciones_pregunta = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);
        
        // Letras para las opciones
        $letras = ['A', 'B', 'C', 'D'];
        $i = 0;
        
        foreach ($opciones_pregunta as $opcion):
            if ($i < count($letras)):
                $letra = $letras[$i];
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" 
                        id="opcion_<?php echo $pregunta['id'] . '_' . $letra; ?>" 
                        value="<?php echo $letra; ?>"
                        <?php echo ($opcion['es_correcta'] == 1 && $pregunta['respuesta_correcta'] == $letra) ? 'data-correct="true"' : ''; ?>>
                    <label class="form-check-label" for="opcion_<?php echo $pregunta['id'] . '_' . $letra; ?>">
                        <?php echo $letra . '. ' . htmlspecialchars($opcion['texto']); ?>
                    </label>
                </div>
                <?php
                $i++;
            endif;
        endforeach;
        ?>
    </div>
<?php endforeach; ?>
                            <button type="submit" class="btn btn-primary">Enviar respuestas</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
      
    <!-- Footer Start -->
 <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-12">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Get In Touch</h5>
                        <p><i class="fa fa-map-marker-alt mr-2"></i>123 Street, New York, USA</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>+012 345 67890</p>
                        <p><i class="fa fa-envelope mr-2"></i>info@example.com</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-outline-light btn-square" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Our Courses</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Web Design</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Apps Design</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Marketing</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Research</a>
                            <a class="text-white" href="#"><i class="fa fa-angle-right mr-2"></i>SEO</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Newsletter</h5>
                <p>Rebum labore lorem dolores kasd est, et ipsum amet et at kasd, ipsum sea tempor magna tempor. Accu kasd sed ea duo ipsum. Dolor duo eirmod sea justo no lorem est diam</p>
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Your Email Address">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4">Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; <a href="#">Domain Name</a>. All Rights Reserved. Designed by <a href="https://htmlcodex.com">HTML Codex</a>
                </p>
            </div>
            <div class="col-lg-6 text-center text-md-right">
                <ul class="nav d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Privacy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Terms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">FAQs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Help</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Footer End -->
    </body>
    </html>
    <?php
}
?>
