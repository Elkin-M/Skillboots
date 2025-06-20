<?php
session_start();

// Verificar si hay un mensaje de éxito o error y guardarlo en sesión
if (isset($_GET['success']) && $_GET['success'] === 'true') {
    $_SESSION['success_message'] = "El curso ha sido creado exitosamente.";
} elseif (isset($_GET['error']) && $_GET['error'] === 'true') {
    $message = isset($_GET['message']) ? urldecode($_GET['message']) : 'Error desconocido.';
    $_SESSION['error_message'] = $message;
    $_SESSION['error_type'] = "error"; // opcional
}

// Redirigir siempre a crear-cursos.php
header("Location: ../courses/crear_curso.php");
exit();
?>
