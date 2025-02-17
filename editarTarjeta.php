<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$client   = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database = $client->tableroKanban;
$coleccion = $database->tarjetas;

$id = $_POST['id'];
$nombre = trim($_POST['nombre']);
$colaboradores = isset($_POST['colaboradores']) ? $_POST['colaboradores'] : [];

// Actualizar la tarjeta con el nuevo nombre y colaboradores
$coleccion->updateOne(
    ["_id" => new ObjectId($id)],
    ['$set' => ['nombre' => $nombre, 'colaboradores' => $colaboradores]]
);

header("Location: kanban.php");
exit();
?>
