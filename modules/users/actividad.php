<?php
session_start();
require_once '../../conexion/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verificar si se proporcionó un ID de actividad válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
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
        $respuestas_usuario = $_POST['respuestas'];
        $puntuacion_total = 0;
        $puntuacion_obtenida = 0;

        foreach ($preguntas as $pregunta) {
            $puntos_pregunta = isset($pregunta['puntuacion']) ? $pregunta['puntuacion'] : 1;
            $puntuacion_total += $puntos_pregunta;
            $respuesta_correcta = $pregunta['respuesta_correcta'];
            $respuesta_usuario = isset($respuestas_usuario[$pregunta['id']]) ? $respuestas_usuario[$pregunta['id']] : null;
        
            if ($respuesta_usuario == $respuesta_correcta) {
                $puntuacion_obtenida += $puntos_pregunta;
            }
        }
        
        if ($puntuacion_total > 0) {
            $calificacion = ($puntuacion_obtenida / $puntuacion_total) * 100;
        } else {
            $calificacion = 0;
        }

        $sql = "INSERT INTO actividades_completadas (actividad_id, usuario_id, calificacion, fecha_completado) VALUES (:actividad_id, :user_id, :calificacion, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':actividad_id' => $actividad_id,
            ':user_id' => $user_id,
            ':calificacion' => $calificacion
        ]);

        echo "<script>window.parent.postMessage({ action: 'actividadCompletada', actividadId: $actividad_id }, '*');</script>";
        header('Location: actividad.php?id=' . $actividad_id);
        exit;
    }

} catch (Exception $e) {
    error_log("Error en actividad.php: " . $e->getMessage());
    echo "Error al cargar la actividad. Por favor, inténtalo de nuevo.";
    exit;
}

// Definir datos antes de incluir archivos
require_once '../../auth/auth.php';
$isLoggedIn = Auth::isAuthenticated();
$userRole = $isLoggedIn ? Auth::getUserRole() : 'visitante';
$userName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

$pageData = [
    'isLoggedIn' => $isLoggedIn,
    'userRole' => $userRole,
    'userName' => $userName
];

// Si está embebido, usar plantilla reducida
if ($modo_embebido) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($actividad['titulo']); ?></title>
        <!-- ESTILOS ANTES DE INCLUIR NAVBAR -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <!-- Estilos adicionales para navbar -->
        <link rel="stylesheet" href="../../assets/css/navbar.css">
        <link rel="stylesheet" href="../../css/style.css">
    </head>
    <body>
        <?php include "../../includes/navbar-estu.php"; ?>
        <div class="container mt-4">
            <!-- Contenido embebido aquí -->
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} else {
    // Plantilla completa
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($actividad['titulo']); ?></title>
        
        <!-- CARGAR TODOS LOS ESTILOS PRIMERO -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        
        <!-- Estilos locales - AJUSTA ESTAS RUTAS SEGÚN TU ESTRUCTURA -->
        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="../../css/navbar.css">
        <link rel="stylesheet" href="../../css/main.css">
        
        <!-- DEBUG: Agregar estilos inline como fallback -->
        <style>
            .row>*{
                flex-shrink: 3;
                width: 20%;
            }
            /* ESTILOS DE RESPALDO PARA NAVBAR */
            .navbar {
                background-color: #fff !important;
                box-shadow: 0 2px 4px rgba(0,0,0,.1) !important;
                padding: 1rem 0 !important;
            }
            .navbar-brand {
                font-weight: bold !important;
                color: #007bff !important;
            }
            .navbar-nav .nav-link {
                color: #333 !important;
                font-weight: 500 !important;
                margin: 0 0.5rem !important;
                transition: color 0.3s ease !important;
            }
            .navbar-nav .nav-link:hover {
                color: #007bff !important;
            }
            .dropdown-menu {
                border: none !important;
                box-shadow: 0 4px 8px rgba(0,0,0,.15) !important;
            }
            .btn-outline-primary {
                border-color: #007bff !important;
                color: #007bff !important;
            }
            .btn-outline-primary:hover {
                background-color: #007bff !important;
                color: white !important;
            }
            
            /* ESTILOS PARA EL CONTENIDO */
            body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .actividad-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 25px rgba(0,0,0,0.1);
                padding: 40px;
                margin-top: 30px;
                margin-bottom: 30px;
            }
            .pregunta-card {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 10px;
                padding: 25px;
                margin-bottom: 25px;
                border-left: 5px solid #007bff;
                transition: transform 0.2s ease;
            }
            .pregunta-card:hover {
                transform: translateY(-2px);
            }
            .form-check-label {
                cursor: pointer;
                padding: 10px 15px;
                border-radius: 8px;
                transition: all 0.3s ease;
                border: 2px solid transparent;
                margin-left: 10px;
            }
            .form-check-label:hover {
                background-color: #e3f2fd;
                border-color: #2196f3;
            }
            .form-check-input:checked + .form-check-label {
                background-color: #007bff;
                color: white;
                border-color: #007bff;
            }
            .btn-primary {
                padding: 15px 40px;
                font-size: 18px;
                border-radius: 8px;
                background: linear-gradient(45deg, #007bff, #0056b3);
                border: none;
                transition: all 0.3s ease;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,123,255,0.3);
            }
        </style>
    </head>
    <body>
        <?php
        // INCLUIR NAVBAR DESPUÉS DE CARGAR ESTILOS
        if ($isLoggedIn && $userRole === 'estudiante') {
            include '../../includes/navbar-estu.php';
        } elseif ($userRole === 'profesor'){
            include '../../includes/navbar-pro.php';
        } else {
            include '../../includes/navbar.php';
        }
        ?>

        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Información del curso -->
                    <div class="alert alert-info">
                        <h6 class="mb-1"><i class="fas fa-book me-2"></i>Curso: <?php echo htmlspecialchars($actividad['curso_nombre']); ?></h6>
                        <small><i class="fas fa-layer-group me-2"></i>Módulo: <?php echo htmlspecialchars($actividad['modulo_titulo']); ?></small>
                    </div>

                    <div class="actividad-container">
                        <div class="text-center mb-4">
                            <h1 class="display-5 fw-bold text-primary"><?php echo htmlspecialchars($actividad['titulo']); ?></h1>
                            <?php if (!empty($actividad['descripcion'])): ?>
                                <p class="lead text-muted"><?php echo htmlspecialchars($actividad['descripcion']); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if ($actividad_completada): ?>
                            <div class="alert alert-success text-center">
                                <i class="fas fa-trophy fa-3x mb-3"></i>
                                <h3>¡Felicitaciones! Actividad Completada</h3>
                                <p class="mb-2">Completada el <?php echo date('d/m/Y H:i', strtotime($fecha_completado)); ?></p>
                                <h4><i class="fas fa-star me-2"></i>Tu calificación: <?php echo number_format($calificacion, 1); ?>%</h4>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Instrucciones:</strong> Lee cada pregunta cuidadosamente y selecciona la respuesta correcta.
                            </div>

                            <form method="POST" action="" id="actividadForm">
                                <?php foreach ($preguntas as $index => $pregunta): ?>
                                    <div class="pregunta-card">
                                        <h5 class="mb-3">
                                            <span class="badge bg-primary me-3"><?php echo $index + 1; ?></span>
                                            <?php echo htmlspecialchars($pregunta['texto']); ?>
                                        </h5>
                                        
                                        <?php
                                        $sql_opciones = "SELECT * FROM opciones WHERE pregunta_id = :pregunta_id ORDER BY id";
                                        $stmt_opciones = $conn->prepare($sql_opciones);
                                        $stmt_opciones->execute([':pregunta_id' => $pregunta['id']]);
                                        $opciones_pregunta = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        $letras = ['A', 'B', 'C', 'D'];
                                        $i = 0;
                                        
                                        foreach ($opciones_pregunta as $opcion):
                                            if ($i < count($letras)):
                                                $letra = $letras[$i];
                                                ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" 
                                                        id="opcion_<?php echo $pregunta['id'] . '_' . $letra; ?>" 
                                                        value="<?php echo $letra; ?>" required>
                                                    <label class="form-check-label w-100" for="opcion_<?php echo $pregunta['id'] . '_' . $letra; ?>">
                                                        <strong><?php echo $letra; ?>.</strong> <?php echo htmlspecialchars($opcion['texto']); ?>
                                                    </label>
                                                </div>
                                                <?php
                                                $i++;
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-5">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Enviar Respuestas
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5 mt-5">
            <div class="row pt-5">
                <div class="col-lg-7 col-md-12">
                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <h5 class="text-primary text-uppercase mb-4">Contacto</h5>
                            <p><i class="fa fa-map-marker-alt mr-2"></i>123 Street, New York, USA</p>
                            <p><i class="fa fa-phone-alt mr-2"></i>+012 345 67890</p>
                            <p><i class="fa fa-envelope mr-2"></i>info@example.com</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h5 class="text-primary text-uppercase mb-4">Enlaces</h5>
                            <div class="d-flex flex-column">
                                <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Inicio</a>
                                <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Cursos</a>
                                <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Contacto</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts al final -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Validación del formulario
            document.getElementById('actividadForm')?.addEventListener('submit', function(e) {
                const preguntas = document.querySelectorAll('.pregunta-card');
                let todasRespondidas = true;

                preguntas.forEach(function(pregunta, index) {
                    const radios = pregunta.querySelectorAll('input[type="radio"]');
                    let preguntaRespondida = false;
                    
                    radios.forEach(function(radio) {
                        if (radio.checked) {
                            preguntaRespondida = true;
                        }
                    });

                    if (!preguntaRespondida) {
                        todasRespondidas = false;
                        pregunta.style.border = '2px solid #dc3545';
                    } else {
                        pregunta.style.border = 'none';
                    }
                });

                if (!todasRespondidas) {
                    e.preventDefault();
                    alert('Por favor, responde todas las preguntas.');
                    return false;
                }

                return confirm('¿Estás seguro de enviar tus respuestas?');
            });

            // DEBUG: Verificar si los estilos se cargan
            console.log('Página cargada');
            console.log('Bootstrap cargado:', typeof bootstrap !== 'undefined');
            
            // Verificar archivos CSS
            document.addEventListener('DOMContentLoaded', function() {
                const links = document.querySelectorAll('link[rel="stylesheet"]');
                links.forEach(link => {
                    link.onerror = function() {
                        console.error('Error cargando CSS:', this.href);
                    };
                    link.onload = function() {
                        console.log('CSS cargado:', this.href);
                    };
                });
            });
        </script>
    </body>
    </html>
    <?php
}
?>

<!-- ARCHIVO DE DEBUG SEPARADO -->
<?php
/*
CREA ESTE ARCHIVO COMO debug_navbar.php EN LA MISMA CARPETA:

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <h1>Testing Navbar Include</h1>
    
    <?php
    echo "<p>Archivo actual: " . __FILE__ . "</p>";
    echo "<p>Directorio actual: " . __DIR__ . "</p>";
    
    // Verificar si el archivo navbar existe
    $navbar_path = "../../includes/navbar-estu.php";
    if (file_exists($navbar_path)) {
        echo "<p style='color: green;'>✓ Navbar file exists: $navbar_path</p>";
        include $navbar_path;
    } else {
        echo "<p style='color: red;'>✗ Navbar file NOT found: $navbar_path</p>";
        
        // Intentar otras rutas
        $possible_paths = [
            "../includes/navbar-estu.php",
            "includes/navbar-estu.php",
            "./includes/navbar-estu.php"
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                echo "<p style='color: orange;'>Found at: $path</p>";
            }
        }
    }
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
*/
?>