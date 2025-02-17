<?php
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$client = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database = $client->tableroKanban;
$coleccion = $database->tarjetas;

// Obtener datos de la solicitud AJAX
$id = $_POST['id'];
$nuevoEstado = $_POST['estado'];

// Traducir los IDs de columna a los nombres de estado en la BD
$estadoMap = [
    "ideas" => "IDEAS",
    "toDo" => "TO DO",
    "doing" => "DOING",
    "done" => "DONE"
];

if (isset($estadoMap[$nuevoEstado])) {
    $coleccion->updateOne(
        ["_id" => new ObjectId($id)],
        ['$set' => ["estado" => $estadoMap[$nuevoEstado]]]
    );
}
?>
