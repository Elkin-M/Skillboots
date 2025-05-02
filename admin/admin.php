<?php
session_start();

// Verificar si hay un mensaje de éxito o error
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    $curso_id = isset($_GET['curso_id']) ? $_GET['curso_id'] : null;
    echo "El curso ha sido creado exitosamente. <a href='curso.php?id=$curso_id'>Ver curso</a>";
} elseif (isset($_GET['error']) && $_GET['error'] == 'true') {
    $message = isset($_GET['message']) ? urldecode($_GET['message']) : 'Error desconocido.';
    echo "Error: $message";
} else {
    echo "Bienvenido al panel de administración.";
}
?>
