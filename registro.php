<?php
include("includes/conexion.php");
include("includes/funciones.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasena = trim($_POST["contrasena"]);

    if (!empty($usuario) && !empty($contrasena)) {
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT usuario FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $error = "El usuario ya existe.";
        } else {
            // Encriptar la contraseña
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)");
            $stmt->bind_param("ss", $usuario, $hash);
            if ($stmt->execute()) {
                header("Location: login.php?mensaje=registrado");
                exit();
            } else {
                $error = "Error al registrar el usuario.";
            }
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/estilo.css">

</head>
<body class="container">
    <h2 class="mt-4">Registro</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="contrasena" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Registrarse</button>
        <a href="login.php" class="btn btn-link">Ya tengo cuenta</a>
    </form>
</body>
</html>
