<?php
session_start(); // Inicia la sesión
include '../conexion/db.php'; // Conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $contraseña = trim($_POST['contraseña'] ?? '');

    // Verificar que los campos no estén vacíos
    if (empty($email) || empty($contraseña)) {
        echo "Por favor, ingrese su correo y contraseña.";
        exit();
    }

    // Consulta segura con PDO
    $stmt = $conn->prepare("SELECT id, name, password, rol FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contraseña, $usuario['password'])) {
        // Generar un token
        $token = bin2hex(random_bytes(16)); // Genera un token aleatorio
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora

        // Actualizar el token en la base de datos
        $stmt = $conn->prepare("UPDATE usuarios SET token = :token, token_expira = :expiracion WHERE id = :usuario_id");
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':expiracion', $expiracion, PDO::PARAM_STR);
        $stmt->bindParam(':usuario_id', $usuario['id'], PDO::PARAM_INT);
        $stmt->execute();

        // Guardar información del usuario en la sesión
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['name']; // Nombre del usuario
        $_SESSION['user_rol'] = $usuario['rol']; // Rol del usuario
        $_SESSION['token'] = $token; // Almacenar el token en la sesión

        // Redirigir según el rol
        if ($usuario['rol'] === 'estudiante') {
            header('Location: ../index.php');
        } elseif ($usuario['rol'] === 'profesor') {
            header('Location: ../templates/holaaaa.php');
        } else {
            echo "Rol no reconocido.";
            exit();
        }
        exit();
    } else {
        echo "Correo o contraseña incorrectos.";
    }
}
?>
