<?php
include("conexao.php");

$nome = $_POST['nome'];
$senha = $_POST['senha'];
$confirmar = $_POST['confirmar'];
$turma = $_POST['turma'];


if (empty($nome) || empty($senha) || empty($confirmar) || empty($turma)) {
    echo "<script>alert('Preencha todos os campos!'); window.history.back();</script>";
    exit();
}

if ($senha != $confirmar) {
    echo "<script>alert('As senhas não coincidem!'); window.history.back();</script>";
    exit();
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);


$sql = "INSERT INTO usuarios (nome_usuario, senha, id_turma)
        VALUES ('$nome', '$senhaHash', '$turma')";

if (mysqli_query($conexao, $sql)) {
    echo "<script>alert('Cadastro realizado com sucesso!'); window.location='../index.html';</script>";
} else {
    echo "<script>alert('Erro ao cadastrar usuário!'); window.history.back();</script>";
}

?>