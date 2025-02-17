<?php
session_start();
require 'vendor/autoload.php'; // Cargar MongoDB

use MongoDB\Client;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit();
}

// Conectar a MongoDB
$client    = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database  = $client->tableroKanban;
$coleccion = $database->tarjetas;

// Obtener todas las tarjetas y convertir el cursor a un arreglo
$tarjetas = iterator_to_array($coleccion->find());
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tablero Kanban</title>
  <link rel="stylesheet" href="kanban.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
  <header>
    <h1>Tablero Kanban</h1>
    <p>Bienvenido, <strong><?php echo $_SESSION['username']; ?></strong></p>
    <form action="logout.php" method="post">
      <input type="submit" class="botonCerrar" value="Cerrar Sesión">
    </form>
  </header>

  <!-- Botón para abrir el modal de agregar tarjeta -->
  <button class="botonAbrirModal">Añadir Idea</button>

  <!-- Modal para agregar tarjeta -->
  <div id="modalAgregar" class="modal">
    <div class="modalContenido">
      <span class="cerrarModal">&times;</span>
      <h3>Añadir Nueva Idea</h3>
      <form action="agregarTarjeta.php" method="post">
        <input type="hidden" name="autor" value="<?php echo $_SESSION['username']; ?>">
        <label for="colaboradores">Colaboradores:</label>
        <input type="text" id="colaboradores" name="colaboradores" required>
        <label for="notas">Notas:</label>
        <textarea name="notas" required></textarea>
        <input type="submit" value="Añadir Idea">
      </form>
    </div>
  </div>

  <!-- Estructura fija de columnas -->
  <div class="tablero">
    <?php
      // Definición de columnas: claves = IDs, valores = texto que se muestra
      $columnas = [
        "ideas" => "IDEAS",
        "toDo"  => "TO DO",
        "doing" => "DOING",
        "done"  => "DONE"
      ];
      
      // Por cada columna se muestra su contenedor, incluso si está vacía
      foreach ($columnas as $id => $nombre) {
          echo "<div class='columna' id='$id'>";
          echo "<h2>$nombre</h2>";
          echo "<div class='zonaTareas'>";
          
          // Se muestran las tarjetas que pertenezcan a esta columna
          foreach ($tarjetas as $tarjeta) {
              if ($tarjeta['estado'] === $nombre) {
                  echo "<div class='tarjeta' data-id='{$tarjeta['_id']}'>";
                  echo "<p><strong>Autor:</strong> {$tarjeta['autor']}</p>";
                  echo "<p><strong>Colaboradores:</strong> {$tarjeta['colaboradores']}</p>";
                  echo "<p><strong>Notas:</strong><br>";
                  // Convertir las notas a arreglo PHP en caso de ser BSONArray
                  $notasArray = iterator_to_array($tarjeta['notas']);
                  foreach ($notasArray as $nota) {
                      echo $nota['autor'] . ": " . $nota['mensaje'] . "<br>";
                  }
                  echo "</p>";
                  echo "</div>";
              }
          }
          
          echo "</div></div>"; // Cierra zonaTareas y columna
      }
    ?>
  </div>

  <script src="kanban.js"></script>
</body>
</html>
