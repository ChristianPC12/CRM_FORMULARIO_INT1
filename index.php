<?php
include("php/conexion.php");

$errores = [];
$cedula = $nombre = $correo = $telefono = $lugarResidencia = $fechaCumpleanos = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim(str_replace("-", "", $_POST['cedula'] ?? ''));
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $lugarResidencia = trim($_POST['lugar_residencia'] ?? '');
    $fechaCumpleanos = $_POST['fecha_cumpleanos'] ?? '';

    if (!preg_match("/^\d{9}$/", $cedula)) {
        $errores['cedula'] = "Formato inválido. Debe ser tipo 1-2345-6789.";
    }

    if (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,50}$/", $nombre)) {
        $errores['nombre'] = "Debe contener solo letras y mínimo 3 caracteres.";
    }

    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = "Correo electrónico inválido.";
    }

    if (!preg_match("/^\d{8}$/", $telefono)) {
        $errores['telefono'] = "Debe tener exactamente 8 dígitos.";
    }

    if (empty($lugarResidencia) || strlen($lugarResidencia) < 4) {
        $errores['lugar_residencia'] = "Debe tener al menos 4 caracteres.";
    }

    if (empty($fechaCumpleanos) || $fechaCumpleanos > date('Y-m-d')) {
        $errores['fecha_cumpleanos'] = "Fecha no válida.";
    }

    if (empty($errores)) {
        $sql_check = "SELECT cedula FROM cliente WHERE cedula = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("s", $cedula);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errores['cedula'] = "La cédula ya está registrada.";
        } else {
            $sql = "INSERT INTO cliente (cedula, nombre, correo, telefono, lugarResidencia, fechaCumpleanos)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssssss", $cedula, $nombre, $correo, $telefono, $lugarResidencia, $fechaCumpleanos);

            if ($stmt->execute()) {
                echo "<script>
                    window.location.href = 'php/mensaje.php?tipo=exito&titulo=Registro Exitoso&mensaje=" . urlencode("Gracias, $nombre. Tu registro ha sido recibido correctamente.") . "';
                </script>";
                exit;
            } else {
                $errores['general'] = "Error al registrar: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }

    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Registro Clientes VIP – Restaurante Bastos</title>
  <link rel="stylesheet" href="css/styles.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
  <div class="form-container">
    <img src="img/bannerVip.png" alt="Banner Restaurante Bastos" class="banner" />

    <div class="header-content">
      <h2>Registro Clientes VIP – Restaurante Bastos</h2>
      <p class="instrucciones">Este formulario tiene como objetivo recopilar la información necesaria para registrar a los clientes que recibirán la <strong>tarjeta VIP</strong> del restaurante <em>Bastos</em>. Al completar este formulario, quedarás oficialmente inscrito en nuestro programa VIP y podrás comenzar a disfrutar de beneficios exclusivos.</p>
      <p class="instrucciones">La información será utilizada únicamente con fines internos para ofrecerte un mejor servicio.</p>
      <p class="instrucciones">Tiempo estimado: <strong>1 a 2 minutos</strong>.</p>
    </div>

    <?php if (!empty($errores['general'])): ?>
      <p style="color: red; font-weight:bold;"><?= $errores['general'] ?></p>
    <?php endif; ?>

    <form method="POST" id="formularioVIP" action="" class="formulario-vip">
      <label for="cedula">Cédula <span class="obligatorio">*</span></label>
      <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>" placeholder="1-2345-6789" required />
      <?php if (!empty($errores['cedula'])): ?><p style="color: red;"><?= $errores['cedula'] ?></p><?php endif; ?>

      <label for="nombre">Nombre completo <span class="obligatorio">*</span></label>
      <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Juan Pérez" required />
      <?php if (!empty($errores['nombre'])): ?><p style="color: red;"><?= $errores['nombre'] ?></p><?php endif; ?>

      <label for="telefono">Teléfono <span class="obligatorio">*</span></label>
      <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($telefono) ?>" placeholder="88888888" required />
      <?php if (!empty($errores['telefono'])): ?><p style="color: red;"><?= $errores['telefono'] ?></p><?php endif; ?>

      <label for="correo">Correo electrónico</label>
      <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($correo) ?>" placeholder="correo@ejemplo.com" />
      <?php if (!empty($errores['correo'])): ?><p style="color: red;"><?= $errores['correo'] ?></p><?php endif; ?>

      <label for="lugar_residencia">Lugar de residencia <span class="obligatorio">*</span></label>
      <input type="text" id="lugar_residencia" name="lugar_residencia" value="<?= htmlspecialchars($lugarResidencia) ?>" placeholder="Liberia, Guanacaste" list="lugares" required />
      <?php if (!empty($errores['lugar_residencia'])): ?><p style="color: red;"><?= $errores['lugar_residencia'] ?></p><?php endif; ?>

      <datalist id="lugares">
        <option value="Cañas, Guanacaste">
        <option value="Liberia, Guanacaste">
        <option value="Bagaces, Guanacaste">
        <option value="Tilarán, Guanacaste">
        <option value="Abangares, Guanacaste">
        <option value="Carrillo, Guanacaste">
        <option value="Santa Cruz, Guanacaste">
        <option value="Nicoya, Guanacaste">
        <option value="La Cruz, Guanacaste">
        <option value="Nandayure, Guanacaste">
        <option value="Hojancha, Guanacaste">
        <option value="San José, San José">
        <option value="Alajuela, Alajuela">
        <option value="Cartago, Cartago">
        <option value="Heredia, Heredia">
        <option value="Puntarenas, Puntarenas">
        <option value="Limón, Limón">
      </datalist>

      <label for="fecha_cumpleanos">Fecha de nacimiento <span class="obligatorio">*</span></label>
      <input type="date" id="fecha_cumpleanos" name="fecha_cumpleanos" value="<?= htmlspecialchars($fechaCumpleanos) ?>" required />
      <?php if (!empty($errores['fecha_cumpleanos'])): ?><p style="color: red;"><?= $errores['fecha_cumpleanos'] ?></p><?php endif; ?>

      <button type="submit" class="btn-enviar">Enviar</button>
    </form>
  </div>

  <!-- Modal confirmación -->
  <div id="modalConfirmCorreo">
    <div class="modal-content">
      <p>¿Está seguro de enviar el formulario sin correo electrónico?</p>
      <div class="modal-buttons">
        <button id="btnConfirmarEnviar" type="button">Sí</button>
        <button id="btnCancelarEnviar" type="button">No</button>
      </div>
    </div>
  </div>

  <script>
    const form = document.getElementById("formularioVIP");
    const correoInput = document.getElementById("correo");
    const modal = document.getElementById("modalConfirmCorreo");
    const btnConfirmar = document.getElementById("btnConfirmarEnviar");
    const btnCancelar = document.getElementById("btnCancelarEnviar");

    form.addEventListener("submit", function(event) {
      if (correoInput.value.trim() === "") {
        event.preventDefault();
        modal.classList.add("active");
      }
    });

    btnConfirmar.addEventListener("click", function() {
      modal.classList.remove("active");

      form.submit();
    });

    btnCancelar.addEventListener("click", function() {
      modal.classList.remove("active");
      correoInput.focus();
    });

    // Formato automático de cédula
    const cedulaInput = document.getElementById("cedula");
    cedulaInput.addEventListener("input", function () {
      let valor = this.value.replace(/\D/g, "");
      valor = valor.substring(0, 9);
      if (valor.length >= 1 && valor.length <= 5)
        this.value = valor.replace(/^(\d{1})(\d{0,4})/, "$1-$2");
      else if (valor.length > 5)
        this.value = valor.replace(/^(\d{1})(\d{4})(\d{0,4})/, "$1-$2-$3");
    });

    document.querySelector("form").addEventListener("submit", function () {
      cedulaInput.value = cedulaInput.value.replace(/-/g, "");
    });

    // Auto foco al primer campo con error
    window.addEventListener("DOMContentLoaded", () => {
      const errores = document.querySelectorAll("p[style*='color: red']");
      if (errores.length > 0) {
        const primerError = errores[0].previousElementSibling;
        if (primerError && primerError.tagName === "INPUT") {
          primerError.focus();
          primerError.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      }
    });
  </script>
</body>
</html>
