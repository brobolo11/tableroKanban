<?php
session_start();
require 'vendor/autoload.php';

use MongoDB\Client;

$client   = new Client('mongodb+srv://bfanvei:Lolitofernandez10@cluster0.3swo1.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');
$database = $client->tableroKanban;
$coleccion = $database->tarjetas;

$autor         = $_SESSION['username'];
$nombre        = trim($_POST['nombre']);
$colaboradores = isset($_POST['colaboradores']) ? $_POST['colaboradores'] : [];
$notaInicial   = trim($_POST['notas']);

$notas = [];
if ($notaInicial !== "") {
    $notas[] = ['autor' => $autor, 'mensaje' => $notaInicial];
}

// Crear la nueva idea con estado "IDEAS"
$nuevaIdea = [
    'nombre'        => $nombre,
    'autor'         => $autor,
    'colaboradores' => $colaboradores,
    'notas'         => $notas,
    'estado'        => 'IDEAS'
];

$coleccion->insertOne($nuevaIdea);

header("Location: kanban.php");
exit();
?>
