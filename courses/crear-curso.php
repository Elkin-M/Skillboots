<?php
session_start();
require_once '../conexion/db.php';

// Capturar mensajes de error/éxito de la sesión
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$error_type = isset($_SESSION['error_type']) ? $_SESSION['error_type'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Limpiar mensajes después de capturarlos para evitar que se muestren de nuevo
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
if (isset($_SESSION['error_type'])) unset($_SESSION['error_type']);
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
// Función para debug - enviar al log de errores de PHP
function console_log($data) {
    error_log("[console_log] " . json_encode($data));
}

// Función para debug - enviar datos estructurados al log
function debug_data($label, $data) {
    error_log("[" . $label . "] " . json_encode($data, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // DEPURACIÓN: Mostrar todos los datos recibidos
        debug_data("DATOS POST RECIBIDOS", $_POST);
        debug_data("ARCHIVOS RECIBIDOS", $_FILES);
        
        // Iniciamos una transacción para asegurar integridad
        $conn->beginTransaction();
        console_log("Transacción iniciada");

        // Recuperar y validar datos del curso
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = isset($_POST['precio']) && is_numeric($_POST['precio']) ? floatval($_POST['precio']) : 0.00;
        $duracion = isset($_POST['duracion']) && is_numeric($_POST['duracion']) ? intval($_POST['duracion']) : 0;
        $nivel = trim($_POST['nivel'] ?? 'Principiante');
        $estado = trim($_POST['estado'] ?? 'borrador');
        $instructor_id = isset($_POST['instructor_id']) && is_numeric($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 1;

        // Validaciones básicas
        if (empty($nombre)) {
            throw new Exception("El nombre del curso es obligatorio");
        }
        if (empty($categoria)) {
            throw new Exception("La categoría del curso es obligatoria");
        }
        if (empty($descripcion)) {
            throw new Exception("La descripción del curso es obligatoria");
        }

        // Debug de datos principales del curso
        $curso_data = [
            'nombre' => $nombre,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'duracion' => $duracion,
            'nivel' => $nivel,
            'estado' => $estado,
            'instructor_id' => $instructor_id
        ];
        debug_data("DATOS DEL CURSO PROCESADOS", $curso_data);

        // Valores para campos adicionales en la tabla cursos
        $total_lecciones = 0; // Se actualizará después
        $horas_totales = $duracion;
        $estate = 'activo';
        
        // Manejar la subida de la imagen del curso
        $imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = './uploads/cursos/';
            
            // Crear el directorio si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
                console_log("Directorio de uploads creado: " . $uploadDir);
            }

            // Generar nombre único para evitar conflictos
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = 'curso_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
            $imagen = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen)) {
                console_log("Imagen subida correctamente: " . $imagen);
                // Guardar solo la ruta relativa en la base de datos
                $imagen = '/skillboots/courses/uploads/cursos/' . $filename;
            } else {
                console_log("Error al subir la imagen");
                throw new Exception("Error al subir la imagen.");
            }
        }

        // Insertar el curso en la base de datos
        $sql = "INSERT INTO cursos (nombre, categoria, descripcion, imagen, precio, duracion, nivel, estado,
                total_lecciones, horas_totales, instructor_id, estate, fecha_creacion, vistas)
                VALUES (:nombre, :categoria, :descripcion, :imagen, :precio, :duracion, :nivel, :estado,
                :total_lecciones, :horas_totales, :instructor_id, :estate, NOW(), 0)";

        console_log("Query para insertar curso: " . $sql);
        
        $stmt = $conn->prepare($sql);
        $resultado_curso = $stmt->execute([
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':descripcion' => $descripcion,
            ':imagen' => $imagen,
            ':precio' => $precio,
            ':duracion' => $duracion,
            ':nivel' => $nivel,
            ':estado' => $estado,
            ':total_lecciones' => $total_lecciones,
            ':horas_totales' => $horas_totales,
            ':instructor_id' => $instructor_id,
            ':estate' => $estate
        ]);

        if (!$resultado_curso) {
            throw new Exception("Error al insertar el curso en la base de datos");
        }

        $curso_id = $conn->lastInsertId();
        console_log("Curso insertado con ID: " . $curso_id);

        // Contadores para actualizar total_lecciones
        $contador_lecciones = 0;
        $contador_modulos = 0;

        // Insertar módulos y contenido
        if (isset($_POST['unidades']) && is_array($_POST['unidades'])) {
            debug_data("UNIDADES RECIBIDAS", $_POST['unidades']);
            
            foreach ($_POST['unidades'] as $index => $unidad) {
                console_log("Procesando unidad/módulo " . ($index + 1));
                
                // Validación básica del módulo
                if (empty($unidad['titulo'])) {
                    console_log("Saltando módulo sin título en índice: " . $index);
                    continue;
                }

                $titulo_modulo = trim($unidad['titulo']);
                $descripcion_modulo = trim($unidad['descripcion'] ?? '');
                $orden = $index + 1;

                debug_data("DATOS DEL MÓDULO " . $orden, [
                    'titulo' => $titulo_modulo,
                    'descripcion' => $descripcion_modulo,
                    'orden' => $orden
                ]);

                // Insertar en la tabla modulos
                $sql_modulo = "INSERT INTO modulos (curso_id, titulo, descripcion, orden)
                              VALUES (:curso_id, :titulo, :descripcion, :orden)";
                
                console_log("Query para insertar módulo: " . $sql_modulo);
                
                $stmt_modulo = $conn->prepare($sql_modulo);
                $resultado_modulo = $stmt_modulo->execute([
                    ':curso_id' => $curso_id,
                    ':titulo' => $titulo_modulo,
                    ':descripcion' => $descripcion_modulo,
                    ':orden' => $orden
                ]);

                if (!$resultado_modulo) {
                    throw new Exception("Error al insertar el módulo: " . $titulo_modulo);
                }

                $modulo_id = $conn->lastInsertId();
                $contador_modulos++;
                console_log("Módulo insertado con ID: " . $modulo_id);

                // Procesar RECURSOS del módulo
                if (isset($unidad['recursos']) && is_array($unidad['recursos'])) {
                    console_log("Procesando " . count($unidad['recursos']) . " recursos para el módulo " . $modulo_id);
                    
                    foreach ($unidad['recursos'] as $recurso_index => $recurso) {
                        if (empty($recurso['titulo'])) {
                            console_log("Saltando recurso sin título");
                            continue;
                        }

                        $titulo_recurso = trim($recurso['titulo']);
                        $tipo_recurso = trim($recurso['tipo'] ?? 'texto');
                        $contenido_recurso = trim($recurso['contenido'] ?? '');
                        $obligatorio_recurso = isset($recurso['obligatorio']) ? 1 : 0;
                        $url_recurso = trim($recurso['url'] ?? '');
                        $texto_contenido = trim($recurso['texto_contenido'] ?? '');

                        debug_data("RECURSO " . ($recurso_index + 1), [
                            'titulo' => $titulo_recurso,
                            'tipo' => $tipo_recurso,
                            'obligatorio' => $obligatorio_recurso
                        ]);

                        // IMPORTANTE: Usar modulo_id en lugar de 
                        // Según tu esquema, recursos.modulo_id debería referenciar a modulos.id
                        $sql_recurso = "INSERT INTO recursos (modulo_id, titulo, tipo, contenido, obligatorio, url, texto_contenido)
                                       VALUES (:modulo_id, :titulo, :tipo, :contenido, :obligatorio, :url, :texto_contenido)";
                        
                        $stmt_recurso = $conn->prepare($sql_recurso);
                        $resultado_recurso = $stmt_recurso->execute([
                            ':modulo_id' => $modulo_id,
                            ':titulo' => $titulo_recurso,
                            ':tipo' => $tipo_recurso,
                            ':contenido' => $contenido_recurso,
                            ':obligatorio' => $obligatorio_recurso,
                            ':url' => $url_recurso,
                            ':texto_contenido' => $texto_contenido
                        ]);

                        if ($resultado_recurso) {
                            $contador_lecciones++;
                            console_log("Recurso insertado: " . $titulo_recurso);
                        } else {
                            console_log("Error al insertar recurso: " . $titulo_recurso);
                        }
                    }
                }

                // Procesar ACTIVIDADES del módulo
                if (isset($unidad['actividades']) && is_array($unidad['actividades'])) {
                    console_log("Procesando " . count($unidad['actividades']) . " actividades para el módulo " . $modulo_id);
                    
                    foreach ($unidad['actividades'] as $actividad_index => $actividad) {
                        if (empty($actividad['titulo'])) {
                            console_log("Saltando actividad sin título");
                            continue;
                        }

                        $titulo_actividad = trim($actividad['titulo']);
                        $tipo_actividad = trim($actividad['tipo'] ?? 'quiz');
                        $contenido_actividad = trim($actividad['contenido'] ?? '');
                        $puntuacion = isset($actividad['puntuacion']) && is_numeric($actividad['puntuacion']) ? 
                                     intval($actividad['puntuacion']) : 0;
                        $fecha_limite = isset($actividad['fecha_limite']) && !empty($actividad['fecha_limite']) ?
                                       $actividad['fecha_limite'] : null;
                        $tiempo = isset($actividad['tiempo']) && is_numeric($actividad['tiempo']) ? 
                                 intval($actividad['tiempo']) : 0;
                        $obligatorio_actividad = isset($actividad['obligatorio']) ? 1 : 0;
                        $orden_actividad = $actividad_index + 1;

                        debug_data("ACTIVIDAD " . ($actividad_index + 1), [
                            'titulo' => $titulo_actividad,
                            'tipo' => $tipo_actividad,
                            'puntuacion' => $puntuacion,
                            'obligatorio' => $obligatorio_actividad
                        ]);

                        // IMPORTANTE: Usar modulo_id 
                        $sql_actividad = "INSERT INTO actividades (modulo_id, titulo, tipo, contenido, puntuacion, fecha_limite, tiempo, obligatorio, orden)
                            VALUES (:modulo_id, :titulo, :tipo, :contenido, :puntuacion, :fecha_limite, :tiempo, :obligatorio, :orden)";
                            $stmt_actividad = $conn->prepare($sql_actividad);
                            $resultado_actividad = $stmt_actividad->execute([
                                ':modulo_id' => $modulo_id,
                                ':titulo' => $titulo_actividad,
                                ':tipo' => $tipo_actividad,
                                ':contenido' => $contenido_actividad,
                                ':puntuacion' => $puntuacion,
                                ':fecha_limite' => $fecha_limite,
                                ':tiempo' => $tiempo,
                                ':obligatorio' => $obligatorio_actividad,
                                ':orden' => $orden_actividad
                            ]);


                        if (!$resultado_actividad) {
                            console_log("Error al insertar actividad: " . $titulo_actividad);
                            continue;
                        }

                        $actividad_id = $conn->lastInsertId();
                        $contador_lecciones++;
                        console_log("Actividad insertada con ID: " . $actividad_id);

                        // Procesar PREGUNTAS de la actividad
                        if (isset($actividad['preguntas']) && is_array($actividad['preguntas'])) {
                            console_log("Procesando " . count($actividad['preguntas']) . " preguntas para la actividad " . $actividad_id);
                            
                            foreach ($actividad['preguntas'] as $pregunta_index => $pregunta) {
                                if (empty($pregunta['texto'])) {
                                    console_log("Saltando pregunta sin texto");
                                    continue;
                                }

                                $texto_pregunta = trim($pregunta['texto']);
                                $tipo_pregunta = trim($pregunta['tipo'] ?? 'opcion_multiple');
                                $respuesta_correcta = trim($pregunta['respuesta_correcta'] ?? '');

                                debug_data("PREGUNTA " . ($pregunta_index + 1), [
                                    'texto' => $texto_pregunta,
                                    'tipo' => $tipo_pregunta,
                                    'respuesta_correcta' => $respuesta_correcta
                                ]);

                                $sql_pregunta = "INSERT INTO preguntas (actividad_id, texto, tipo, respuesta_correcta)
                                               VALUES (:actividad_id, :texto, :tipo, :respuesta_correcta)";
                                
                                $stmt_pregunta = $conn->prepare($sql_pregunta);
                                $resultado_pregunta = $stmt_pregunta->execute([
                                    ':actividad_id' => $actividad_id,
                                    ':texto' => $texto_pregunta,
                                    ':tipo' => $tipo_pregunta,
                                    ':respuesta_correcta' => $respuesta_correcta
                                ]);

                                if (!$resultado_pregunta) {
                                    console_log("Error al insertar pregunta");
                                    continue;
                                }

                                $pregunta_id = $conn->lastInsertId();
                                console_log("Pregunta insertada con ID: " . $pregunta_id);

                                // Procesar OPCIONES de la pregunta
                                if (isset($pregunta['opciones']) && is_array($pregunta['opciones'])) {
                                    console_log("Procesando " . count($pregunta['opciones']) . " opciones para la pregunta " . $pregunta_id);
                                    
                                    foreach ($pregunta['opciones'] as $opcion_index => $opcion) {
                                        if (empty($opcion['texto'])) {
                                            console_log("Saltando opción sin texto");
                                            continue;
                                        }

                                        $texto_opcion = trim($opcion['texto']);
                                        $es_correcta = isset($opcion['es_correcta']) && $opcion['es_correcta'] ? 1 : 0;

                                        debug_data("OPCIÓN " . ($opcion_index + 1), [
                                            'texto' => $texto_opcion,
                                            'es_correcta' => $es_correcta
                                        ]);

                                        $sql_opcion = "INSERT INTO opciones (pregunta_id, texto, es_correcta)
                                                      VALUES (:pregunta_id, :texto, :es_correcta)";
                                        
                                        $stmt_opcion = $conn->prepare($sql_opcion);
                                        $resultado_opcion = $stmt_opcion->execute([
                                            ':pregunta_id' => $pregunta_id,
                                            ':texto' => $texto_opcion,
                                            ':es_correcta' => $es_correcta
                                        ]);

                                        if ($resultado_opcion) {
                                            console_log("Opción insertada: " . $texto_opcion);
                                        } else {
                                            console_log("Error al insertar opción: " . $texto_opcion);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Procesar CONTENIDO MODULAR
                if (isset($unidad['contenido_modular']) && is_array($unidad['contenido_modular'])) {
                    console_log("Procesando " . count($unidad['contenido_modular']) . " elementos de contenido modular");
                    
                    foreach ($unidad['contenido_modular'] as $contenido_index => $contenido) {
                        if (empty($contenido['titulo'])) {
                            console_log("Saltando contenido sin título");
                            continue;
                        }

                        $titulo_contenido = trim($contenido['titulo']);
                        $tipo_contenido = trim($contenido['tipo'] ?? 'texto');
                        $contenido_texto = trim($contenido['contenido'] ?? '');
                        $orden_contenido = $contenido_index + 1;

                        debug_data("CONTENIDO MODULAR " . ($contenido_index + 1), [
                            'titulo' => $titulo_contenido,
                            'tipo' => $tipo_contenido,
                            'orden' => $orden_contenido
                        ]);

                        $sql_contenido = "INSERT INTO contenido_modular (modulo_id, tipo, contenido, orden, titulo)
                                         VALUES (:modulo_id, :tipo, :contenido, :orden, :titulo)";
                        
                        $stmt_contenido = $conn->prepare($sql_contenido);
                        $resultado_contenido = $stmt_contenido->execute([
                            ':modulo_id' => $modulo_id,
                            ':tipo' => $tipo_contenido,
                            ':contenido' => $contenido_texto,
                            ':orden' => $orden_contenido,
                            ':titulo' => $titulo_contenido
                        ]);

                        if ($resultado_contenido) {
                            $contador_lecciones++;
                            console_log("Contenido modular insertado: " . $titulo_contenido);
                        } else {
                            console_log("Error al insertar contenido modular: " . $titulo_contenido);
                        }
                    }
                }
            }
        } else {
            console_log("No se recibieron unidades/módulos");
        }

        // Actualizar el contador de lecciones en el curso
        console_log("Actualizando contador de lecciones: " . $contador_lecciones);
        
        $sql_update = "UPDATE cursos SET total_lecciones = :total_lecciones WHERE id = :curso_id";
        $stmt_update = $conn->prepare($sql_update);
        $resultado_update = $stmt_update->execute([
            ':total_lecciones' => $contador_lecciones,
            ':curso_id' => $curso_id
        ]);

        if ($resultado_update) {
            console_log("Contador de lecciones actualizado correctamente");
        } else {
            console_log("Error al actualizar contador de lecciones");
        }

        // Crear notificación para el instructor
        $sql_notificacion = "INSERT INTO notifications (user_id, role, message, link, icon, `read`, created_at)
                            VALUES (:user_id, 'instructor', :message, :link, 'check-circle', 0, NOW())";
        
        $stmt_notificacion = $conn->prepare($sql_notificacion);
        $resultado_notificacion = $stmt_notificacion->execute([
            ':user_id' => $instructor_id,
            ':message' => "Tu curso '$nombre' ha sido creado exitosamente.",
            ':link' => "curso.php?id=$curso_id"
        ]);

        if ($resultado_notificacion) {
            console_log("Notificación creada para el instructor");
        } else {
            console_log("Error al crear notificación");
        }

        // Commit de la transacción
        $conn->commit();
        console_log("Transacción completada exitosamente");
        
        // Debug final
        $resumen = [
            'curso_id' => $curso_id,
            'nombre' => $nombre,
            'modulos_creados' => $contador_modulos,
            'total_lecciones' => $contador_lecciones,
            'estado' => $estado
        ];
        debug_data("RESUMEN FINAL", $resumen);

        // Redirigir con éxito
        header("Location: ../admin/admin.php?success=true&curso_id=$curso_id&lecciones=$contador_lecciones");
        exit;

    } catch (Exception $e) {
        // Si hay algún error, revertir todo
        if ($conn->inTransaction()) {
            $conn->rollBack();
            console_log("Transacción revertida debido a error");
        }

        // Debug del error
        $error_info = [
            'mensaje' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        debug_data("ERROR CAPTURADO", $error_info);

        // Registrar el error
        error_log("Error en crear-curso.php: " . $e->getMessage() . " en línea " . $e->getLine());
        
        // Redirigir con error
        header("Location: ../admin/admin.php?error=true&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Manejar el caso en que no se envíe un POST
    console_log("Método no permitido - se recibió: " . $_SERVER['REQUEST_METHOD']);
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Método no permitido. Se esperaba POST.";
    exit;
}

?>
<script> // Función para mostrar alertas
          function showAlert(message, type) {
            // Crear el elemento de alerta
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                min-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 8px;
                animation: slideInRight 0.5s ease-out;
            `;
            
            // Definir colores según el tipo
            let bgColor, borderColor, textColor, iconClass;
            switch(type) {
                case 'error':
                    bgColor = '#f8d7da';
                    borderColor = '#f5c6cb';
                    textColor = '#721c24';
                    iconClass = 'bi-exclamation-triangle-fill';
                    break;
                case 'warning':
                    bgColor = '#fff3cd';
                    borderColor = '#ffecb5';
                    textColor = '#856404';
                    iconClass = 'bi-exclamation-triangle';
                    break;
                case 'success':
                    bgColor = '#d1e7dd';
                    borderColor = '#badbcc';
                    textColor = '#0f5132';
                    iconClass = 'bi-check-circle-fill';
                    break;
                default:
                    bgColor = '#d1ecf1';
                    borderColor = '#bee5eb';
                    textColor = '#0c5460';
                    iconClass = 'bi-info-circle';
            }
            
            alertDiv.style.backgroundColor = bgColor;
            alertDiv.style.borderColor = borderColor;
            alertDiv.style.color = textColor;
            alertDiv.style.border = `1px solid ${borderColor}`;
            
            alertDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi ${iconClass}" style="font-size: 1.2em;"></i>
                    <span style="flex: 1;">${message}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: ${textColor}; opacity: 0.7;">&times;</button>
                </div>
            `;
            
            // Agregar estilos de animación
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            if (!document.querySelector('#alert-styles')) {
                style.id = 'alert-styles';
                document.head.appendChild(style);
            }
            
            // Agregar al DOM
            document.body.appendChild(alertDiv);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.animation = 'slideOutRight 0.5s ease-in';
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 500);
                }
            }, 5000);
        }
</script>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert-custom';
        
        let bgColor, borderColor, textColor, iconClass;
        switch(type) {
            case 'error':
                bgColor = '#f8d7da';
                borderColor = '#f5c6cb';
                textColor = '#721c24';
                iconClass = 'bi-exclamation-triangle-fill';
                break;
            case 'warning':
                bgColor = '#fff3cd';
                borderColor = '#ffecb5';
                textColor = '#856404';
                iconClass = 'bi-exclamation-triangle';
                break;
            case 'success':
                bgColor = '#d1e7dd';
                borderColor = '#badbcc';
                textColor = '#0f5132';
                iconClass = 'bi-check-circle-fill';
                break;
            default:
                bgColor = '#d1ecf1';
                borderColor = '#bee5eb';
                textColor = '#0c5460';
                iconClass = 'bi-info-circle';
        }
        
        alertDiv.style.backgroundColor = bgColor;
        alertDiv.style.borderColor = borderColor;
        alertDiv.style.color = textColor;
        alertDiv.style.border = `1px solid ${borderColor}`;
        
        alertDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi ${iconClass}" style="font-size: 1.2em;"></i>
                <span style="flex: 1;">${message}</span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: ${textColor}; opacity: 0.7;">&times;</button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.animation = 'slideOutRight 0.5s ease-in';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 500);
            }
        }, 5000);
    }

    <?php if (!empty($error_message)): ?>
        showAlert('<?php echo addslashes($error_message); ?>', '<?php echo $error_type; ?>');
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        showAlert('<?php echo addslashes($success_message); ?>', 'success');
    <?php endif; ?>
});


</script>
