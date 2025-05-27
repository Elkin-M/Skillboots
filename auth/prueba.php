<?php
session_start();
include '../conexion/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contraseña = trim($_POST['contraseña'] ?? '');
    $rol = trim($_POST['rol'] ?? '');

    // Validar campos vacíos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($contraseña) || empty($rol)) {
        $_SESSION['error_message'] = "Todos los campos son obligatorios.";
        $_SESSION['error_type'] = "warning";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "El formato del correo electrónico no es válido.";
        $_SESSION['error_type'] = "error";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validar longitud de contraseña
    if (strlen($contraseña) < 6) {
        $_SESSION['error_message'] = "La contraseña debe tener al menos 6 caracteres.";
        $_SESSION['error_type'] = "warning";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validar rol
    $roles_validos = ['admin', 'profesor', 'estudiante'];
    if (!in_array($rol, $roles_validos)) {
        $_SESSION['error_message'] = "El rol seleccionado no es válido.";
        $_SESSION['error_type'] = "error";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    try {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Ya existe una cuenta con este correo electrónico.";
            $_SESSION['error_type'] = "warning";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Encriptar contraseña
        $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);

        // Insertar nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (name, apellido, email, password, rol, created_at) VALUES (:nombre, :apellido, :email, :password, :rol, NOW())");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $contraseña_hash, PDO::PARAM_STR);
        $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "¡Cuenta creada exitosamente! Ya puedes iniciar sesión.";
            $_SESSION['show_login'] = true;
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            $_SESSION['error_message'] = "Error al crear la cuenta. Intente nuevamente.";
            $_SESSION['error_type'] = "error";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error del sistema. Por favor, intente más tarde.";
        $_SESSION['error_type'] = "error";
        error_log("Error de registro: " . $e->getMessage());
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si no es POST, redirigir al formulario
    $_SESSION['error_message'] = "Acceso no autorizado.";
    $_SESSION['error_type'] = "error";
    header('Location: ../index.php');
    exit();
}
?>