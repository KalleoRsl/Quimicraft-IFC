<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "quimicraft";

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro de conexão: " . mysqli_connect_error());
}

?>