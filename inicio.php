<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Página de Inicio</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <header><img src="./images/logo.png" alt=""></header>
    
    <div class="formulario">
        <h1>¡Bienvenido <?php echo $_SESSION['username']; ?>!</h1>
        
        <!-- Botón para ir al menú -->
        <form action="inicio_menu.php" method="get">
            <input type="submit" class="botonIniciar" value="Ir al Menú">
        </form>
        
        <!-- Botón de cerrar sesión -->
        <form action="logout.php" method="post">
            <input type="submit" class="botonIniciar" value="Cerrar Sesión">
        </form>
    </div>
</body>
</html>