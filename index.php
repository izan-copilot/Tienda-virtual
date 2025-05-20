<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

// Procesar acciones del carrito
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id_producto"])) {
        $id = intval($_POST["id_producto"]);
        if (isset($_SESSION["carrito"][$id])) {
            $_SESSION["carrito"][$id]++;
        } else {
            $_SESSION["carrito"][$id] = 1;
        }
    }
    if (isset($_POST["restar_producto"])) {
        $id = intval($_POST["restar_producto"]);
        if (isset($_SESSION["carrito"][$id])) {
            $_SESSION["carrito"][$id]--;
            if ($_SESSION["carrito"][$id] <= 0) {
                unset($_SESSION["carrito"][$id]);
            }
        }
    }
    if (isset($_POST["eliminar_producto"])) {
        $id = intval($_POST["eliminar_producto"]);
        unset($_SESSION["carrito"][$id]);
    }
    if (isset($_POST["finalizar"]) && isset($_POST["direccion"])) {
        $direccion = trim($_POST["direccion"]);
        if (!empty($_SESSION["carrito"]) && !empty($direccion)) {
            $stmt = $conn->prepare("INSERT INTO compras (usuario, id_producto, direccion) VALUES (?, ?, ?)");
            foreach ($_SESSION["carrito"] as $id_producto => $cantidad) {
                for ($i = 0; $i < $cantidad; $i++) {
                    $stmt->bind_param("sis", $_SESSION["usuario"], $id_producto, $direccion);
                    $stmt->execute();
                }
            }
            $_SESSION["carrito"] = [];
            $mensaje = "Compra realizada correctamente.";
        } else {
            $mensaje = "El carrito está vacío o la dirección es inválida.";
        }
    }
}

// Cargar productos (importante hacerlo DESPUÉS de procesar POST)
$resultado = $conn->query("SELECT * FROM productos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda - Comida de Izan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="container">
    <div class="d-flex justify-content-between mt-4 mb-2">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION["usuario"]) ?></h2>
        <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>

    <?php if (!empty($mensaje)) echo "<div class='alert alert-info'>$mensaje</div>"; ?>

    <h4>🛒 Carrito actual</h4>
    <?php if (!empty($_SESSION["carrito"])): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION["carrito"] as $id_producto => $cantidad):
                    $stmt = $conn->prepare("SELECT nombre, precio FROM productos WHERE id_producto = ?");
                    $stmt->bind_param("i", $id_producto);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res->num_rows > 0):
                        $producto = $res->fetch_assoc();
                        $nombre = $producto["nombre"];
                        $precio = $producto["precio"];
                        $subtotal = $precio * $cantidad;
                        $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($nombre) ?></td>
                    <td><?= $cantidad ?></td>
                    <td><?= number_format($precio, 2) ?> €</td>
                    <td><?= number_format($subtotal, 2) ?> €</td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="restar_producto" value="<?= $id_producto ?>">
                            <button class="btn btn-warning btn-sm">−</button>
                        </form>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id_producto" value="<?= $id_producto ?>">
                            <button class="btn btn-success btn-sm">+</button>
                        </form>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="eliminar_producto" value="<?= $id_producto ?>">
                            <button class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th colspan="2"><?= number_format($total, 2) ?> €</th>
                </tr>
            </tfoot>
        </table>
        <form method="POST">
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección de envío</label>
                <input type="text" name="direccion" id="direccion" class="form-control" required>
            </div>
            <button type="submit" name="finalizar" class="btn btn-primary">Finalizar compra</button>
        </form>
    <?php else: ?>
        <p>El carrito está vacío.</p>
    <?php endif; ?>

    <h4 class="mt-5">Productos disponibles</h4>
    <div class="row">
        <?php while ($producto = $resultado->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 d-flex flex-column">
                    <img src="./img/<?= htmlspecialchars($producto["img"]) ?>" class="card-img-top" alt="Producto" style="height: 200px; object-fit: cover;">

                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title"><?= htmlspecialchars($producto["nombre"]) ?></h5>
                            <p class="card-text">Precio: <?= number_format($producto["precio"], 2) ?> €</p>
                        </div>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="id_producto" value="<?= $producto["id_producto"] ?>">
                            <button type="submit" class="btn btn-primary w-100">Agregar al carrito</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
