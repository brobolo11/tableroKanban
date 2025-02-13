<?php
require 'vendor/autoload.php';

use MongoDB\Client;

$uri = 'mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';

try {
    $client = new Client($uri);
    $database = $client->selectDatabase('tableroKanban');
    $collection = $database->selectCollection('usuarios');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        if (empty($nombre) || empty($contrasena)) {
            header('Location: registrarse.php?error=campos_vacios');
            exit();
        }

        // Comprobar si el usuario ya existe
        $usuarioExistente = $collection->findOne(['nombre' => $nombre]);
        if ($usuarioExistente) {
            header('Location: registrarse.php?error=usuario_existente');
            exit();
        }

        // Insertar usuario en la base de datos
        $collection->insertOne([
            'nombre' => $nombre,
            'contrasena' => $contrasena // Guardando en texto plano
        ]);

        header('Location: index.html?registro_exitoso');
        exit();
    }
} catch (Exception $e) {
    header('Location: registrarse.php?error=conexion_bd');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <header><img src="./images/logo.png" alt=""></header>

    <form method="POST" action="registrarse.php" class="formulario">
        <p class="p1">Regístrate</p>
        <p class="p2">Crea una cuenta para acceder</p>

        <p>Nombre:</p>
        <input type="text" name="nombre" required>

        <p>Contraseña:</p>
        <input type="password" name="contrasena" required>

        <input class="botonIniciar" type="submit" value="Registrarse">
    </form>

    <a href="index.html">Volver al Login</a>
</body>
</html>
