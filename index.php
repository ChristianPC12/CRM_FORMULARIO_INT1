<?php
include("php/conexion.php");

// --- Validador estricto de fecha de nacimiento ---
function validarFechaCumpleEstricto(string $s): bool
{
  // Formato exacto AAAA-MM-DD
  $dt = DateTime::createFromFormat('Y-m-d', $s);
  if (!$dt || $dt->format('Y-m-d') !== $s)
    return false;

  // Rango permitido [1900-01-01, hoy]
  $min = new DateTime('1900-01-01');
  $hoy = new DateTime('today');
  if ($dt < $min || $dt > $hoy)
    return false;

  // Edad 0–120
  $edad = (int) $dt->diff($hoy)->y;
  return $edad >= 0 && $edad <= 120;
}


$errores = [];
$cedula = $nombre = $correo = $telefono = $lugarResidencia = $fechaCumpleanos = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $cedula = trim(str_replace("-", "", $_POST['cedula'] ?? ''));
  $nombre = trim($_POST['nombre'] ?? '');
  $correo = trim($_POST['correo'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $lugarResidencia = trim($_POST['lugar_residencia'] ?? '');
  // Acepta el nombre real del input; si quieres compatibilidad, deja ambos.
  $fechaCumpleanos = $_POST['fechaNacimiento'] ?? ($_POST['fecha_cumpleanos'] ?? '');


  $alergias = trim($_POST['alergias'] ?? '');
  $gustosEspeciales = trim($_POST['gustos_especiales'] ?? '');


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

  if (!validarFechaCumpleEstricto($fechaCumpleanos)) {
    $errores['fechaNacimiento'] = "Fecha no válida (use AAAA-MM-DD; entre 1900 y hoy; edad 0–120).";
  }

  if (strlen($alergias) > 100) {
    $errores['alergias'] = "Máximo 100 caracteres.";
  }
  if (strlen($gustosEspeciales) > 100) {
    $errores['gustos_especiales'] = "Máximo 100 caracteres.";
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
      $sql = "INSERT INTO cliente (cedula, nombre, correo, telefono, lugarResidencia, fechaCumpleanos, alergias, gustos_especiales)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $conexion->prepare($sql);
      $stmt->bind_param("ssssssss", $cedula, $nombre, $correo, $telefono, $lugarResidencia, $fechaCumpleanos, $alergias, $gustosEspeciales);

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
      <p class="instrucciones">Este formulario tiene como objetivo recopilar la información necesaria para registrar a
        los clientes que recibirán la <strong>tarjeta VIP</strong> del restaurante <em>Bastos</em>. Al completar este
        formulario, quedarás oficialmente inscrito en nuestro programa VIP y podrás comenzar a disfrutar de beneficios
        exclusivos.</p>
      <p class="instrucciones">La información será utilizada únicamente con fines internos para ofrecerte un mejor
        servicio.</p>
      <p class="instrucciones">Tiempo estimado: <strong>1 a 2 minutos</strong>.</p>
    </div>

    <?php if (!empty($errores['general'])): ?>
      <p style="color: red; font-weight:bold;"><?= $errores['general'] ?></p>
    <?php endif; ?>

    <form method="POST" id="formularioVIP" action="" class="formulario-vip">
      <label for="cedula">Cédula <span class="obligatorio">*</span></label>
      <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>"
        placeholder="1-2345-6789" required />
      <?php if (!empty($errores['cedula'])): ?>
        <p style="color: red;"><?= $errores['cedula'] ?></p><?php endif; ?>

      <label for="nombre">Nombre completo <span class="obligatorio">*</span></label>
      <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Juan Pérez"
        required />
      <?php if (!empty($errores['nombre'])): ?>
        <p style="color: red;"><?= $errores['nombre'] ?></p><?php endif; ?>

      <label for="telefono">Teléfono <span class="obligatorio">*</span></label>
      <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($telefono) ?>" placeholder="88888888"
        required />
      <?php if (!empty($errores['telefono'])): ?>
        <p style="color: red;"><?= $errores['telefono'] ?></p><?php endif; ?>

      <label for="correo">Correo electrónico</label>
      <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($correo) ?>"
        placeholder="correo@ejemplo.com" />
      <?php if (!empty($errores['correo'])): ?>
        <p style="color: red;"><?= $errores['correo'] ?></p><?php endif; ?>

      <label for="lugar_residencia">Lugar de residencia <span class="obligatorio">*</span></label>
      <input type="text" id="lugar_residencia" name="lugar_residencia" value="<?= htmlspecialchars($lugarResidencia) ?>"
        placeholder="Liberia, Guanacaste" list="lugares" required />
      <?php if (!empty($errores['lugar_residencia'])): ?>
        <p style="color: red;"><?= $errores['lugar_residencia'] ?></p><?php endif; ?>

      <datalist id="lugares">

        <!-- Guanacaste -->
        <option value="Liberia, Guanacaste">
        <option value="Nicoya, Guanacaste">
        <option value="Santa Cruz, Guanacaste">
        <option value="Bagaces, Guanacaste">
        <option value="Carrillo, Guanacaste">
        <option value="Cañas, Guanacaste">
        <option value="Abangares, Guanacaste">
        <option value="Tilarán, Guanacaste">
        <option value="Nandayure, Guanacaste">
        <option value="La Cruz, Guanacaste">
        <option value="Hojancha, Guanacaste">
          <!-- San José -->
        <option value="San José, San José">
        <option value="Escazú, San José">
        <option value="Desamparados, San José">
        <option value="Puriscal, San José">
        <option value="Tarrazú, San José">
        <option value="Aserrí, San José">
        <option value="Mora, San José">
        <option value="Goicoechea, San José">
        <option value="Santa Ana, San José">
        <option value="Alajuelita, San José">
        <option value="Vásquez de Coronado, San José">
        <option value="Acosta, San José">
        <option value="Tibás, San José">
        <option value="Moravia, San José">
        <option value="Montes de Oca, San José">
        <option value="Turrubares, San José">
        <option value="Dota, San José">
        <option value="Curridabat, San José">
        <option value="Pérez Zeledón, San José">
        <option value="León Cortés, San José">

          <!-- Alajuela -->
        <option value="Alajuela, Alajuela">
        <option value="San Ramón, Alajuela">
        <option value="Grecia, Alajuela">
        <option value="San Mateo, Alajuela">
        <option value="Atenas, Alajuela">
        <option value="Naranjo, Alajuela">
        <option value="Palmares, Alajuela">
        <option value="Poás, Alajuela">
        <option value="Orotina, Alajuela">
        <option value="San Carlos, Alajuela">
        <option value="Zarcero, Alajuela">
        <option value="Valverde Vega, Alajuela">
        <option value="Upala, Alajuela">
        <option value="Los Chiles, Alajuela">
        <option value="Guatuso, Alajuela">
        <option value="Río Cuarto, Alajuela">

          <!-- Cartago -->
        <option value="Cartago, Cartago">
        <option value="Paraíso, Cartago">
        <option value="La Unión, Cartago">
        <option value="Jiménez, Cartago">
        <option value="Turrialba, Cartago">
        <option value="Alvarado, Cartago">
        <option value="Oreamuno, Cartago">
        <option value="El Guarco, Cartago">

          <!-- Heredia -->
        <option value="Heredia, Heredia">
        <option value="Barva, Heredia">
        <option value="Santo Domingo, Heredia">
        <option value="Santa Bárbara, Heredia">
        <option value="San Rafael, Heredia">
        <option value="San Isidro, Heredia">
        <option value="Belén, Heredia">
        <option value="Flores, Heredia">
        <option value="San Pablo, Heredia">
        <option value="Sarapiquí, Heredia">



          <!-- Puntarenas -->
        <option value="Puntarenas, Puntarenas">
        <option value="Esparza, Puntarenas">
        <option value="Buenos Aires, Puntarenas">
        <option value="Montes de Oro, Puntarenas">
        <option value="Osa, Puntarenas">
        <option value="Quepos, Puntarenas">
        <option value="Golfito, Puntarenas">
        <option value="Coto Brus, Puntarenas">
        <option value="Parrita, Puntarenas">
        <option value="Corredores, Puntarenas">
        <option value="Garabito, Puntarenas">

          <!-- Limón -->
        <option value="Limón, Limón">
        <option value="Pococí, Limón">
        <option value="Siquirres, Limón">
        <option value="Talamanca, Limón">
        <option value="Matina, Limón">
        <option value="Guácimo, Limón">
      </datalist>

      <label for="fechaNacimiento">Fecha de nacimiento <span class="obligatorio">*</span></label>

      <input type="date" name="fechaNacimiento" id="fechaNacimiento" class="form-control" required min="1900-01-01"
        max="<?php echo date('Y-m-d'); ?>">

      <?php if (!empty($errores['fechaNacimiento'])): ?>
        <p style="color: red;"><?= $errores['fechaNacimiento'] ?></p>
      <?php endif; ?>

      <label for="alergias">Alergias (opcional)</label>
      <input type="text" id="alergias" name="alergias" value="<?= htmlspecialchars($_POST['alergias'] ?? '') ?>"
        placeholder="Ej: Mani, mariscos" />

      <label for="gustos_especiales">Gustos especiales (opcional)</label>
      <input type="text" id="gustos_especiales" name="gustos_especiales"
        value="<?= htmlspecialchars($_POST['gustos_especiales'] ?? '') ?>" placeholder="Ej: Sin picante, extra limón" />

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

    form.addEventListener("submit", function (event) {
      if (correoInput.value.trim() === "") {
        event.preventDefault();
        modal.classList.add("active");
      }
    });

    btnConfirmar.addEventListener("click", function () {
      modal.classList.remove("active");

      form.submit();
    });

    btnCancelar.addEventListener("click", function () {
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