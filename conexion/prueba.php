<?php 
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $rol = $_POST['rol'] ?? ''; // Agregamos el rol

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($contraseña) || empty($nombre) || empty($rol) || empty($apellido)) {
        echo "Todos los campos son obligatorios.";
        exit();
    }

    // Validar que el rol sea válido
    $roles_permitidos = ['admin', 'profesor', 'estudiante'];
    if (!in_array($rol, $roles_permitidos)) {
        echo "Rol no válido";
        exit();
    }

    // Verificar si el email ya está registrado
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Ya existe una cuenta con ese correo.";
        exit();
    }

    // Crear un hash de la contraseña
    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    // Insertar el nuevo usuario en la base de datos incluyendo el rol
    $stmt = $conn->prepare("INSERT INTO usuarios (email, password, name, lastname, rol) VALUES (:email, :password, :nombre, :lastname, :rol)");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $contraseñaHash, PDO::PARAM_STR);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':lastname', $apellido, PDO::PARAM_STR);
    $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Registro exitoso.";
        header('Location: ../sesion.html'); // Redirigir al login tras el registro
        exit();
    } else {
        echo "Hubo un error al registrar al usuario.";
    }
}
?>