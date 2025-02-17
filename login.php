<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

$uri = 'mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';

try {
    $client = new Client($uri);
    $database = $client->selectDatabase('tableroKanban');
    $collection = $database->selectCollection('usuarios');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';

        if ($nombre && $contrasena) {
            // Buscar usuario en la base de datos
            $usuario = $collection->findOne([
                'nombre' => $nombre,
                'contrasena' => $contrasena
            ]);

            if ($usuario) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $usuario['nombre'];
                header('Location: kanban.php');
                exit();
            } else {
                header('Location: index.html?error=credenciales');
                exit();
            }
        } else {
            header('Location: index.html?error=campos_vacios');
            exit();
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error de conexión con la base de datos';
    header('Location: index.html');
    exit();
}
?>
