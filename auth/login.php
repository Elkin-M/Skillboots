<?php
session_start(); // Inicia la sesión
include '../conexion/db.php'; // Conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $contraseña = trim($_POST['contraseña'] ?? '');

    // Verificar que los campos no estén vacíos
    if (empty($email) || empty($contraseña)) {
        $_SESSION['error_message'] = "Por favor, ingrese su correo y contraseña.";
        $_SESSION['error_type'] = "warning";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    try {
        // Consulta segura con PDO
        $stmt = $conn->prepare("SELECT id, name, password, rol FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contraseña, $usuario['password'])) {
            // Generar un token
            $token = bin2hex(random_bytes(16));
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Actualizar el token en la base de datos
            $stmt = $conn->prepare("UPDATE usuarios SET token = :token, token_expira = :expiracion WHERE id = :usuario_id");
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expiracion', $expiracion, PDO::PARAM_STR);
            $stmt->bindParam(':usuario_id', $usuario['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Limpiar cualquier mensaje de error anterior
            unset($_SESSION['error_message']);
            unset($_SESSION['error_type']);

            // Guardar información del usuario en la sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['name'];
            $_SESSION['user_rol'] = $usuario['rol'];
            $_SESSION['token'] = $token;
            
            // Mensaje de éxito personalizado según el rol
            $mensajes_bienvenida = [
                'estudiante' => "¡Bienvenido " . $usuario['name'] . "! Listo para aprender.",
                'profesor' => "¡Hola " . $usuario['name'] . "! Panel de profesor cargado.",
                'admin' => "¡Bienvenido " . $usuario['name'] . "! Acceso administrativo concedido."
            ];
            
            $_SESSION['success_message'] = $mensajes_bienvenida[$usuario['rol']] ?? "¡Bienvenido " . $usuario['name'] . "!";
            
            // Redirigir según el rol
            switch ($usuario['rol']) {
                case 'estudiante':
                    header('Location: ../index.php');
                    break;
                case 'profesor':
                    header('Location: ../templates/holaaaa.php');
                    break;
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    break;
                default:
                    $_SESSION['error_message'] = "Rol no reconocido. Contacte al administrador.";
                    $_SESSION['error_type'] = "error";
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Credenciales incorrectas. Verifique su correo y contraseña.";
            $_SESSION['error_type'] = "error";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error del sistema. Por favor, intente más tarde.";
        $_SESSION['error_type'] = "error";
        error_log("Error de login: " . $e->getMessage());
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    $_SESSION['error_message'] = "Acceso no autorizado.";
    $_SESSION['error_type'] = "warning";
    header('Location: ../index.php');
    exit();
}
?>