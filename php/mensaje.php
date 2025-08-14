<?php
$titulo = $_GET['titulo'] ?? 'Mensaje';
$mensaje = $_GET['mensaje'] ?? 'Algo ocurrió.';
$tipo = $_GET['tipo'] ?? '';
$esError = $tipo === 'error';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($titulo) ?></title>
  <style>
    :root {
      --amarillo: #F9C41F;
      --gris: #838886;
      --negro: #000000;
      --rojo: #d93025;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(145deg, #ffffff, #f2f2f2);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .mensaje-box {
      background: #ffffff;
      border-radius: 20px;
      padding: 50px 40px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
      text-align: center;
      max-width: 540px;
      width: 90%;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .mensaje-box h1 {
      font-size: 2.2rem;
      margin-bottom: 18px;
      color: <?= $esError ? 'var(--rojo)' : 'var(--amarillo)' ?>;
      border-bottom: 3px solid <?= $esError ? 'var(--rojo)' : 'var(--amarillo)' ?>;
      display: inline-block;
      padding-bottom: 5px;
    }

    .mensaje-box p {
      font-size: 1.2rem;
      color: var(--negro);
      line-height: 1.7;
      margin-bottom: 35px;
    }

    .btn-volver {
      background-color: <?= $esError ? 'var(--rojo)' : 'var(--amarillo)' ?>;
      color: var(--negro);
      border: none;
      padding: 12px 28px;
      font-size: 1rem;
      font-weight: bold;
      border-radius: 10px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .btn-volver:hover {
      background-color: <?= $esError ? '#ba241f' : '#e2b700' ?>;
    }
  </style>
</head>
<script>
  (function () {
    const f = document.getElementById('fechaNacimiento');
    if (!f) return;

    // Reforzar límites por si el HTML quedó cacheado
    f.min = '1900-01-01';
    f.max = new Date().toISOString().slice(0, 10);

    function edadOk(iso) {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) return false;
      const [y,m,d] = iso.split('-').map(Number);
      const fecha = new Date(Date.UTC(y, m - 1, d));
      const real =
        fecha.getUTCFullYear() === y &&
        (fecha.getUTCMonth() + 1) === m &&
        fecha.getUTCDate() === d;
      if (!real) return false;

      const hoy = new Date();
      if (new Date(y, m - 1, d) > hoy) return false; // no futuro
      if (y < 1900) return false;                    // mínimo 1900

      let edad = hoy.getFullYear() - y;
      if ((hoy.getMonth()+1 < m) || ((hoy.getMonth()+1) === m && hoy.getDate() < d)) edad--;
      return edad >= 0 && edad <= 120;
    }

    f.addEventListener('input', () => {
      if (f.value && !edadOk(f.value)) {
        f.setCustomValidity('Fecha inválida. Use AAAA-MM-DD (1900–hoy) y edad 0–120.');
      } else {
        f.setCustomValidity('');
      }
    });
  })();
</script>


<body>
  <div class="mensaje-box">
    <h1><?= htmlspecialchars($titulo) ?></h1>

    <?php if (!$esError): ?>
      <p><?= htmlspecialchars($mensaje) ?></p>
      <p>¡Te damos la más cordial bienvenida al <strong>Programa VIP de Restaurante Bastos</strong>! A partir de ahora, podrás disfrutar de beneficios exclusivos, promociones especiales y un trato preferencial cada vez que nos visites.</p>
    <?php else: ?>
      <p><?= htmlspecialchars($mensaje) ?></p>
      <a href="../index.php" class="btn-volver">Volver al formulario</a>
    <?php endif; ?>
  </div>
</body>
</html>