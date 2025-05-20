<?php
$host = "localhost:3307";
$usuario = "root";
$contrasena = ""; // pon tu contraseña si usas una
$base_datos = "tiendadecomida";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
