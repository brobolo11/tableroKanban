<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

// Verificar si el usuario está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit();
}

$client = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database = $client->tableroKanban;
$coleccion = $database->tarjetas;

// Obtener datos del formulario
$autor = $_POST['autor'];
$colaboradores = $_POST['colaboradores'];
// Almacenar la nota inicial como un arreglo de mensajes (con autor y mensaje)
$notas = [
    [
        "autor" => $autor,
        "mensaje" => $_POST['notas']
    ]
];
$estado = "IDEAS"; // Las nuevas tarjetas se crean en la columna IDEAS

$tarjeta = [
    "autor" => $autor,
    "colaboradores" => $colaboradores,
    "notas" => $notas,
    "estado" => $estado
];

$coleccion->insertOne($tarjeta);

// Redirigir al tablero después de agregar la tarjeta
header('Location: kanban.php');
exit();
?>
