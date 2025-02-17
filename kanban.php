<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit();
}

$client   = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database = $client->tableroKanban;
$coleccion = $database->tarjetas;

// Usuario logueado
$usuarioLog = $_SESSION['username'];

// Si el usuario es "admin" se muestran TODAS las tarjetas; de lo contrario, se filtran por autor o colaborador.
if ($usuarioLog === 'admin') {
    $tarjetas = iterator_to_array($coleccion->find());
} else {
    $filtro = [
        '$or' => [
            ['autor' => $usuarioLog],
            ['colaboradores' => $usuarioLog]
        ]
    ];
    $tarjetas = iterator_to_array($coleccion->find($filtro));
}

// Obtener la lista de usuarios disponibles (para añadir colaboradores)
// Se usa el campo "nombre". Se excluirán de la lista al usuario logueado y al "admin".
$usuariosCollection = $database->usuarios;
$usuarios = iterator_to_array($usuariosCollection->find());
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
    <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
    <form action="logout.php" method="post">
      <button type="submit" class="botonCerrar">Cerrar Sesión</button>
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
        <!-- Nombre de la idea -->
        <label for="nombre">Nombre de la Idea:</label>
        <input type="text" name="nombre" placeholder="Nombre de la Idea" required>
        
        <!-- Se toma el usuario actual como autor -->
        <input type="hidden" name="autor" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
        
        <fieldset>
          <legend>Selecciona colaboradores:</legend>
          <?php if(empty($usuarios)): ?>
            <p>No hay usuarios disponibles.</p>
          <?php else: ?>
            <?php foreach ($usuarios as $usuario): ?>
              <?php 
                // Excluir al autor (usuario logueado) y a "admin" de la lista de colaboradores
                if (isset($usuario['nombre']) && $usuario['nombre'] !== $_SESSION['username'] && $usuario['nombre'] !== 'admin'):
              ?>
                <label>
                  <input type="checkbox" name="colaboradores[]" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                  <?php echo htmlspecialchars($usuario['nombre']); ?>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </fieldset>
        
        <label for="notas">Nota inicial (opcional):</label>
        <textarea name="notas" placeholder="Escribe una nota inicial (opcional)"></textarea>
        <input type="submit" value="Añadir Idea">
      </form>
    </div>
  </div>

  <!-- Modal para editar tarjeta -->
  <div id="modalEditar" class="modal">
    <div class="modalContenido">
      <span class="cerrarModalEditar">&times;</span>
      <h3>Editar Idea</h3>
      <form action="editarTarjeta.php" method="post">
        <input type="hidden" name="id" id="editId">
        <label for="editNombre">Nombre de la Idea:</label>
        <input type="text" name="nombre" id="editNombre" required>
        <fieldset>
          <legend>Selecciona colaboradores:</legend>
          <div id="editColaboradores">
            <!-- Se rellenarán dinámicamente desde JS -->
          </div>
        </fieldset>
        <input type="submit" value="Guardar Cambios">
      </form>
    </div>
  </div>

  <!-- Tablero con las columnas -->
  <div class="tablero">
    <?php
      // Definición de columnas: IDs y etiquetas
      $columnas = [
        "ideas" => "IDEAS",
        "toDo"  => "TO DO",
        "doing" => "DOING",
        "done"  => "DONE"
      ];
      
      foreach ($columnas as $id => $nombre) {
          echo "<div class='columna' id='$id'>";
          echo "<h2>$nombre</h2>";
          echo "<div class='zonaTareas'>";
          
          // Mostrar las tarjetas que correspondan a la columna
          foreach ($tarjetas as $tarjeta) {
              if ($tarjeta['estado'] === $nombre) {
                  echo "<div class='tarjeta' data-id='{$tarjeta['_id']}'>";
                  if (isset($tarjeta['nombre'])) {
                      echo "<p><strong>Idea:</strong> " . htmlspecialchars($tarjeta['nombre']) . "</p>";
                  }
                  echo "<p><strong>Autor:</strong> " . htmlspecialchars($tarjeta['autor']) . "</p>";
                  echo "<p><strong>Colaboradores:</strong> ";
                  if (is_array($tarjeta['colaboradores']) || $tarjeta['colaboradores'] instanceof \MongoDB\Model\BSONArray) {
                      $colabsArray = is_array($tarjeta['colaboradores']) ? $tarjeta['colaboradores'] : iterator_to_array($tarjeta['colaboradores']);
                      echo htmlspecialchars(implode(", ", $colabsArray));
                  } else {
                      echo htmlspecialchars($tarjeta['colaboradores']);
                  }
                  echo "</p>";
                  
                  // Contenedor interno: mensajes y formulario para agregar nota (inicialmente oculto)
                  echo "<div class='tarjetaContenido' style='display: none;'>";
                  echo "<div class='notas'>";
                  echo "<strong>Mensajes:</strong><br>";
                  if (isset($tarjeta['notas'])) {
                    $notasArray = is_array($tarjeta['notas']) ? $tarjeta['notas'] : iterator_to_array($tarjeta['notas']);
                    foreach ($notasArray as $nota) {
                        echo "<div class='mensaje'>" . htmlspecialchars($nota['autor']) . ": " . htmlspecialchars($nota['mensaje']) . "</div>";
                    }
                  }
                  echo "</div>"; // fin .notas
                  
                  echo "<div class='agregarNota'>";
                  echo "<input type='text' class='inputNota' placeholder='Escribe tu mensaje'>";
                  echo "<button type='button' class='botonAgregarNota'>Enviar</button>";
                  echo "</div>";
                  echo "</div>"; // fin .tarjetaContenido
                  
                  // Mostrar botón de editar solo si el usuario es el autor o es admin
                  if ($usuarioLog === 'admin' || $usuarioLog === $tarjeta['autor']) {
                      // Se envían datos para precargar el modal de edición (colaboradores se envían en formato JSON)
                      echo "<button class='botonEditar' data-id='" . $tarjeta['_id'] . "' data-nombre='" . htmlspecialchars($tarjeta['nombre']) . "' data-autor='" . htmlspecialchars($tarjeta['autor']) . "' data-colaboradores='" . htmlspecialchars(json_encode($tarjeta['colaboradores'])) . "'>Editar</button>";
                  }
                  
                  echo "</div>"; // fin .tarjeta
              }
          }
          
          echo "</div></div>";
      }
    ?>
  </div>

  <!-- Variable JS con la lista de usuarios (para el modal de edición) -->
  <script>
    var usuariosList = <?php echo json_encode($usuarios); ?>;
  </script>
  <script src="kanban.js"></script>
</body>
</html>
