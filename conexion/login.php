<?php
session_start(); // Inicia la sesión
include 'db.php'; // Conexión a la BD

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
        // Guardar información del usuario en la sesión
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['name']; // Nombre del usuario
        $_SESSION['user_rol'] = $usuario['rol']; // Rol del usuario

        // Redirigir según el rol
        if ($usuario['rol'] === 'estudiante') {
            header('Location: ../index.php');
        } elseif ($usuario['rol'] === 'profesor') {
            header('Location: ../holaaaa.php');
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
