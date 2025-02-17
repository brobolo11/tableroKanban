<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$client   = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database = $client->tableroKanban;
$coleccion = $database->tarjetas;

$tarjetaId = $_POST['id'];
$mensaje   = trim($_POST['mensaje']);
$autor     = $_SESSION['username'];

if ($mensaje !== "") {
    $nota = ['autor' => $autor, 'mensaje' => $mensaje];

    $coleccion->updateOne(
        ["_id" => new ObjectId($tarjetaId)],
        ['$push' => ['notas' => $nota]]
    );
    
    echo htmlspecialchars($autor) . ": " . htmlspecialchars($mensaje);
}
?>
