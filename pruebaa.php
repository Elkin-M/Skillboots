<?php
ob_start(); // Iniciar buffer de salida

// Verificar si la sesión ya está activa antes de iniciarla
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar usuario
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Redirigir si es necesario
if ($userId === 0) {
    echo '<script>window.location.href = "sesion.html";</script>';
    exit;
}

// Función para obtener los datos del estudiante desde la base de datos
function obtenerDatosEstudiante($userId) {
    // Conexión a la base de datos (ajusta estos parámetros según tu configuración)
    $host = 'localhost';
    $dbname = 'elkinmb3';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Datos para el dashboard
        $datos = [
            'progreso_general' => 0,
            'tiempo_total' => 0,
            'cursos_activos' => 0,
            'certificados' => 0,
            'proximos_eventos' => [],
            'actividades_recientes' => [],
            'notificaciones' => [],
            'cursos_recomendados' => [],
            'logros' => [],
            'certificados_lista' => []
        ];

        // 1. Obtener los cursos del estudiante
        $stmt = $conn->prepare("
            SELECT uc.*, c.nombre, c.imagen, c.horas_totales, c.total_lecciones, c.categoria, c.instructor_id
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.usuario_id = :userId AND c.estate = 'activo'
            ORDER BY uc.ultimo_acceso DESC
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalCursos = count($cursos);
        $sumaProgreso = 0;
        $tiempoTotal = 0;
        $cursosActivos = 0;

        // Procesar los cursos del usuario
        if ($totalCursos > 0) {
            // Calcular estadísticas generales
            foreach ($cursos as $curso) {
                $sumaProgreso += $curso['progreso'];
                $tiempoTotal += ($curso['progreso'] / 100) * $curso['horas_totales'];

                if ($curso['progreso'] > 0 && $curso['progreso'] < 100) {
                    $cursosActivos++;
                }
            }

            // Calcular progreso general promedio
            $datos['progreso_general'] = round($sumaProgreso / $totalCursos);
            $datos['tiempo_total'] = round($tiempoTotal);
            $datos['cursos_activos'] = $cursosActivos;

            // 2. Obtener las actividades recientes (últimos accesos a cursos)
            $stmt = $conn->prepare("
                SELECT uc.ultimo_acceso as fecha, 'Accedió al curso' as accion, c.nombre as curso
                FROM usuarios_cursos uc
                JOIN cursos c ON uc.curso_id = c.id
                WHERE uc.usuario_id = :userId AND c.estate = 'activo'
                ORDER BY uc.ultimo_acceso DESC
                LIMIT 3
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $datos['actividades_recientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear fechas de actividades
            foreach ($datos['actividades_recientes'] as &$actividad) {
                $fechaObj = new DateTime($actividad['fecha']);
                $actividad['fecha'] = $fechaObj->format('Y-m-d');
            }

            // 3. Cursos recomendados (basados en la categoría de los cursos actuales)
            $categorias = array_column($cursos, 'categoria');
            if (!empty($categorias)) {
                $categoria = $categorias[0]; // Usar la primera categoría para recomendaciones

                $stmt = $conn->prepare("
                SELECT id, nombre AS titulo, imagen
                FROM cursos
                WHERE LOWER(categoria) LIKE LOWER(:categoria)  -- Permite búsquedas sin importar mayúsculas
                AND estate = 'activo'
                AND id NOT IN (
                    SELECT curso_id FROM usuarios_cursos WHERE usuario_id = :userId
                )
                LIMIT 2
            ");
                $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $datos['cursos_recomendados'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // 4. Certificados (cursos completados al 100%)
            $stmt = $conn->prepare("
                SELECT c.id, c.nombre as curso, uc.ultimo_acceso as fecha
                FROM usuarios_cursos uc
                JOIN cursos c ON uc.curso_id = c.id
                WHERE uc.usuario_id = :userId
                AND uc.progreso = 100
                ORDER BY uc.ultimo_acceso DESC
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $datos['certificados'] = count($certificados);
            $datos['certificados_lista'] = $certificados;

            // Formatear fechas de certificados
            foreach ($datos['certificados_lista'] as &$certificado) {
                $fechaObj = new DateTime($certificado['fecha']);
                $certificado['fecha'] = $fechaObj->format('Y-m-d');
            }

            // 5. Logros (simplificado, en una implementación real podrías tener una tabla de logros)
            if ($datos['certificados'] >= 1) {
                $datos['logros'][] = [
                    'titulo' => 'Primer Certificado',
                    'fecha' => date('Y-m-d'),
                    'imagen' => 'img/badge-certificate.png'
                ];
            }

            if ($datos['progreso_general'] >= 50) {
                $datos['logros'][] = [
                    'titulo' => 'Estudiante Dedicado',
                    'fecha' => date('Y-m-d'),
                    'imagen' => 'img/badge-dedicated.png'
                ];
            }

            // 6. Notificaciones (cursos sin actividad reciente)
            foreach ($cursos as $curso) {
                $ultimoAcceso = new DateTime($curso['ultimo_acceso']);
                $ahora = new DateTime();
                $diff = $ultimoAcceso->diff($ahora)->days;

                if ($diff > 7 && $curso['progreso'] < 100) {
                    $datos['notificaciones'][] = [
                        'tipo' => 'warning',
                        'mensaje' => 'Sin actividad en ' . $curso['nombre'] . ' por ' . $diff . ' días'
                    ];
                }
            }

            // 7. Próximos eventos (simulados - en una implementación real podrías tener una tabla de eventos)
            $datos['proximos_eventos'] = [
                ['fecha' => date('Y-m-d', strtotime('+7 days')), 'titulo' => 'Examen final ' . $cursos[0]['nombre']],
                ['fecha' => date('Y-m-d', strtotime('+14 days')), 'titulo' => 'Inicio de curso nuevo']
            ];
        }

        return $datos;

    } catch(PDOException $e) {
        // En producción, mejor registrar el error en un archivo de log
        error_log("Error en base de datos: " . $e->getMessage());
        return [
            'progreso_general' => 0,
            'tiempo_total' => 0,
            'cursos_activos' => 0,
            'certificados' => 0,
            'proximos_eventos' => [],
            'actividades_recientes' => [],
            'notificaciones' => [],
            'cursos_recomendados' => [],
            'logros' => [],
            'certificados_lista' => []
        ];
    }
}

// Obtener cursos en progreso del estudiante
function obtenerCursosEnProgreso($userId) {
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'elkinmb3';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT
                c.id,
                c.nombre,
                c.imagen,
                c.instructor_id,
                uc.progreso,
                uc.lecciones_completadas,
                c.total_lecciones,
                uc.ultimo_acceso
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.usuario_id = :userId
            AND c.estate = 'activo'
            AND uc.progreso > 0
            AND uc.progreso < 100
            ORDER BY uc.ultimo_acceso DESC
            LIMIT 3
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear las fechas de último acceso
        foreach ($cursos as &$curso) {
            $fechaObj = new DateTime($curso['ultimo_acceso']);
            $ahora = new DateTime();
            $diff = $fechaObj->diff($ahora);

            if ($diff->days == 0) {
                $curso['ultima_actividad'] = 'Hoy';
            } elseif ($diff->days == 1) {
                $curso['ultima_actividad'] = 'Ayer';
            } else {
                $curso['ultima_actividad'] = 'Hace ' . $diff->days . ' días';
            }
        }

        return $cursos;

    } catch(PDOException $e) {
        error_log("Error en base de datos: " . $e->getMessage());
        return [];
    }
}

// Obtener nombre del usuario
function obtenerNombreUsuario($userId) {
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'elkinmb3';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['nombre'] : 'Estudiante';

    } catch(PDOException $e) {
        error_log("Error en base de datos: " . $e->getMessage());
        return 'Estudiante';
    }
}

// Obtener el ID del usuario de la sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Obtener datos del estudiante
$datosEstudiante = obtenerDatosEstudiante($userId);

// Obtener los cursos en progreso
$cursosEnProgreso = obtenerCursosEnProgreso($userId);

// Obtener el nombre del usuario
$userName = obtenerNombreUsuario($userId);
?>

<!-- Dashboard Principal -->
<div class="container-fluid py-4">
    <div class="container">
        <div class="row">
            <!-- Sección de bienvenida y resumen -->
            <div class="col-lg-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">¡Bienvenido de nuevo, <?php echo htmlspecialchars($userName); ?>!</h4>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white p-3 mr-3">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Progreso general</h6>
                                        <h4 class="mb-0"><?php echo $datosEstudiante['progreso_general']; ?>%</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success text-white p-3 mr-3">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Tiempo de estudio</h6>
                                        <h4 class="mb-0"><?php echo $datosEstudiante['tiempo_total']; ?> hrs</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info text-white p-3 mr-3">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Cursos activos</h6>
                                        <h4 class="mb-0"><?php echo $datosEstudiante['cursos_activos']; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning text-white p-3 mr-3">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Certificados</h6>
                                        <h4 class="mb-0"><?php echo $datosEstudiante['certificados']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna izquierda: Progreso de cursos y actividades recientes -->
            <div class="col-lg-8">
                <!-- 1. Progreso de cursos actuales -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Mis Cursos en Progreso</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($cursosEnProgreso)): ?>
                            <div class="text-center py-4">
                                <p>No tienes cursos en progreso actualmente.</p>
                                <a href="catalogo.php" class="btn btn-primary">Explorar cursos</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($cursosEnProgreso as $curso): ?>
                                <div class="d-flex align-items-center mb-4">
                                    <img src="<?php echo htmlspecialchars($curso['imagen']); ?>" alt="<?php echo htmlspecialchars($curso['nombre']); ?>" class="img-fluid rounded" style="width: 100px; height: 60px; object-fit: cover;">
                                    <div class="ml-3 flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($curso['nombre']); ?></h6>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar <?php echo ($curso['progreso'] > 66) ? 'bg-success' : (($curso['progreso'] > 33) ? 'bg-info' : 'bg-warning'); ?>" role="progressbar" style="width: <?php echo $curso['progreso']; ?>%;" aria-valuenow="<?php echo $curso['progreso']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small><?php echo $curso['progreso']; ?>% completado</small>
                                            <small>Última actividad: <?php echo $curso['ultima_actividad']; ?></small>
                                        </div>
                                    </div>
                                    <a href="ver-cursos.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-primary ml-3">Continuar</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="mis-cursos.php" class="text-primary">Ver todos mis cursos <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>

                <!-- 2. Actividades Recientes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Actividades Recientes</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($datosEstudiante['actividades_recientes'])): ?>
                            <div class="text-center py-4">
                                <p>No hay actividades recientes registradas.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($datosEstudiante['actividades_recientes'] as $actividad): ?>
                                <div class="list-group-item py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-2 mr-3">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><?php echo htmlspecialchars($actividad['accion']); ?> - <strong><?php echo htmlspecialchars($actividad['curso']); ?></strong></p>
                                            <small class="text-muted"><?php echo $actividad['fecha']; ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. Calendario -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-primary">Calendario de Eventos</h5>
                        <a href="calendario.php" class="btn btn-sm btn-outline-primary">Añadir Recordatorio</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datosEstudiante['proximos_eventos'])): ?>
                            <div class="text-center py-4">
                                <p>No hay eventos próximos programados.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Evento</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datosEstudiante['proximos_eventos'] as $evento): ?>
                                        <tr>
                                            <td><?php echo $evento['fecha']; ?></td>
                                            <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                            <td><a href="evento.php?fecha=<?php echo urlencode($evento['fecha']); ?>&titulo=<?php echo urlencode($evento['titulo']); ?>" class="btn btn-sm btn-outline-info">Detalles</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Notificaciones, recomendaciones, logros -->
            <div class="col-lg-4">
                <!-- 1. Notificaciones -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Notificaciones</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($datosEstudiante['notificaciones'])): ?>
                            <div class="text-center py-4">
                                <p>No tienes notificaciones nuevas.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($datosEstudiante['notificaciones'] as $notificacion): ?>
                                <div class="list-group-item py-3">
                                    <div class="d-flex">
                                        <div class="mr-3">
                                            <?php if ($notificacion['tipo'] === 'info'): ?>
                                                <i class="fas fa-info-circle text-info"></i>
                                            <?php elseif ($notificacion['tipo'] === 'warning'): ?>
                                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                            <?php else: ?>
                                                <i class="fas fa-bell text-primary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="mb-0"><?php echo htmlspecialchars($notificacion['mensaje']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="notificaciones.php" class="text-primary">Ver todas las notificaciones</a>
                    </div>
                </div>

                <!-- 2. Cursos Recomendados -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Cursos Recomendados</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datosEstudiante['cursos_recomendados'])): ?>
                            <div class="text-center py-3">
                                <p>No hay recomendaciones disponibles.</p>
                                <a href="catalogo.php" class="btn btn-sm btn-primary">Explorar catálogo</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($datosEstudiante['cursos_recomendados'] as $curso): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo htmlspecialchars($curso['imagen']); ?>" alt="<?php echo htmlspecialchars($curso['titulo']); ?>" class="img-fluid rounded" style="width: 80px; height: 50px; object-fit: cover;">
                                <div class="ml-3">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($curso['titulo']); ?></h6>
                                    <a href="ver-cursos.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">Ver detalles</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. Logros y Certificaciones -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Logros y Certificados</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Mis Logros</h6>
                        <?php if (empty($datosEstudiante['logros'])): ?>
                            <p class="text-center">Completa cursos para obtener logros.</p>
                        <?php else: ?>
                            <div class="d-flex mb-4">
                                <?php foreach ($datosEstudiante['logros'] as $logro): ?>
                                <div class="text-center mr-3">
                                    <div class="rounded-circle bg-light p-3 mb-2" style="width: 70px; height: 70px; margin: 0 auto;">
                                        <i class="fas fa-trophy text-warning fa-2x"></i>
                                    </div>
                                    <small><?php echo htmlspecialchars($logro['titulo']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h6 class="text-muted mb-3">Mis Certificados</h6>
                        <?php if (empty($datosEstudiante['certificados_lista'])): ?>
                            <p class="text-center">Completa cursos al 100% para obtener certificados.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($datosEstudiante['certificados_lista'] as $certificado): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-0"><?php echo htmlspecialchars($certificado['curso']); ?></p>
                                        <small class="text-muted"><?php echo $certificado['fecha']; ?></small>
                                    </div>
                                    <a href="certificado.php?id=<?php echo $certificado['id']; ?>" class="btn btn-sm btn-outline-primary">Descargar</a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 4. Soporte y Ayuda -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 font-weight-bold text-primary">Soporte y Ayuda</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary text-white p-3 mr-3">
                                <i class="fas fa-question"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Preguntas Frecuentes</h6>
                                <small>Resuelve tus dudas comunes</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success text-white p-3 mr-3">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Chat de Soporte</h6>
                                <small>Disponible 9:00 AM - 6:00 PM</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info text-white p-3 mr-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Contáctanos</h6>
                                <small>soporte@tudominio.com</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de curso -->
<div class="modal fade" id="courseDetailModal" tabindex="-1" role="dialog" aria-labelledby="courseDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseDetailModalLabel">Detalles del Curso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#courseDetailModal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="courseDetailContent">
                <!-- Contenido dinámico del curso -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#courseDetailModal').modal('hide');" >Cerrar</button>
                <a href="ver-cursos.php?id=<?php echo $curso['id']; ?>" type="button" class="btn btn-primary" id="continueCourseBtn">Continuar Curso</a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para cargar los cursos del estudiante -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // En una implementación real, estos datos vendrían de una petición AJAX
    const cursos = [
        {
            id: 1,
            titulo: 'JavaScript Fundamentals',
            instructor: 'John Doe',
            progreso: 75,
            imagen: 'img/course-1.jpg',
            duracion: '10 horas',
            modulos: 12,
            ultimaActividad: 'Ayer'
        },
        {
            id: 2,
            titulo: 'Responsive Web Design',
            instructor: 'Jane Smith',
            progreso: 45,
            imagen: 'img/course-2.jpg',
            duracion: '8 horas',
            modulos: 10,
            ultimaActividad: 'Hace 3 días'
        },
        {
            id: 3,
            titulo: 'PHP & MySQL Básico',
            instructor: 'Mike Johnson',
            progreso: 20,
            imagen: 'img/course-3.jpg',
            duracion: '12 horas',
            modulos: 15,
            ultimaActividad: 'Hoy'
        }
    ];

    // Función para mostrar el detalle del curso en el modal
    window.mostrarDetalleCurso = function(cursoId) {
        const curso = cursos.find(c => c.id === cursoId);
        if (curso) {
            document.getElementById('courseDetailModalLabel').textContent = curso.titulo;

            const contenido = `
                <div class="row">
                    <div class="col-md-4">
                        <img src="${curso.imagen}" alt="${curso.titulo}" class="img-fluid rounded">
                    </div>
                    <div class="col-md-8">
                        <h5>${curso.titulo}</h5>
                        <p><strong>Instructor:</strong> ${curso.instructor}</p>
                        <p><strong>Duración:</strong> ${curso.duracion}</p>
                        <p><strong>Módulos:</strong> ${curso.modulos}</p>
                        <p><strong>Progreso:</strong> ${curso.progreso}%</p>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: ${curso.progreso}%;"
                                 aria-valuenow="${curso.progreso}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p><strong>Última actividad:</strong> ${curso.ultimaActividad}</p>
                    </div>
                </div>
            `;

            document.getElementById('courseDetailContent').innerHTML = contenido;
            document.getElementById('continueCourseBtn').onclick = function() {
                // Redirigir al curso
                window.location.href = `./ver-cursos.php?id=${cursoId}`;
            };

            $('#courseDetailModal').modal('show');
        }
    }
});
</script>
