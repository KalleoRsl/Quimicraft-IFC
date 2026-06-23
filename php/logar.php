<?php
session_start();
include("conexao.php");

$nome = $_POST['nome'] ?? '';
$senha = $_POST['senha'] ?? '';

$nome = trim($nome);

if (empty($nome) || empty($senha)) {
    echo "<script>alert('Preencha todos os campos!'); window.history.back();</script>";
    exit();
}

$sql = "SELECT id_usuario, nome_usuario, senha, id_turma FROM usuarios WHERE nome_usuario = ? LIMIT 1";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    echo "<script>alert('Erro ao preparar login!'); window.history.back();</script>";
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $nome);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = $result ? mysqli_fetch_assoc($result) : null;

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    echo "<script>alert('Usuário ou senha inválidos!'); window.history.back();</script>";
    exit();
}

$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nome_usuario'] = $usuario['nome_usuario'];
$_SESSION['id_turma'] = $usuario['id_turma'];

echo "<script>alert('Login realizado com sucesso!'); window.location='principal.php';</script>";
?>
