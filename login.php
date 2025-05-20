<?php
include("includes/conexion.php");
include("includes/funciones.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasena = trim($_POST["contrasena"]);

    if (!empty($usuario) && !empty($contrasena)) {
        $stmt = $conn->prepare("SELECT contrasena FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $row = $resultado->fetch_assoc();
            if (verificar_contrasena($contrasena, $row["contrasena"])) {
                $_SESSION["usuario"] = $usuario;
                header("Location: index.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
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
    <title>Login - Tienda Virtual</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/estilo.css">

</head>
<body class="container">
    <h2 class="mt-4">Iniciar Sesión</h2>
    <?php if (!empty($_GET['mensaje']) && $_GET['mensaje'] == 'registrado') echo "<div class='alert alert-success'>Registro exitoso. Ahora puedes iniciar sesión.</div>"; ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="contrasena" class="form-label">Contraseña</label>
            <input type="password" name="contrasena" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
        <a href="registro.php" class="btn btn-link">Registrarse</a>
    </form>
</body>
</html>

