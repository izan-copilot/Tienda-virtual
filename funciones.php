<?php
function encriptar_contrasena($contrasena) {
    return password_hash($contrasena, PASSWORD_BCRYPT);
}

function verificar_contrasena($contrasena, $hash) {
    return password_verify($contrasena, $hash);
}
?>
