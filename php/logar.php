<?php
session_start();
include("conexao.php");
include("includes/alerta.php");

$nome = $_POST['nome'] ?? '';
$senha = $_POST['senha'] ?? '';

$nome = trim($nome);

if (empty($nome) || empty($senha)) {
    redirecionarAlerta("../html/login.html", "erro", "Preencha todos os campos!");
}

$sql = "SELECT u.id_usuario, u.nome_usuario, u.senha, u.id_turma, u.foto_perfil, t.nome_turma
        FROM usuarios u
        LEFT JOIN turmas t ON t.id_turma = u.id_turma
        WHERE u.nome_usuario = ? LIMIT 1";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    redirecionarAlerta("../html/login.html", "erro", "Erro ao preparar login!");
}

mysqli_stmt_bind_param($stmt, "s", $nome);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = $result ? mysqli_fetch_assoc($result) : null;

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    redirecionarAlerta("../html/login.html", "erro", "Usuário ou senha inválidos!");
}

$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nome_usuario'] = $usuario['nome_usuario'];
$_SESSION['id_turma'] = $usuario['id_turma'];
$_SESSION['nome_turma'] = $usuario['nome_turma'] ?? '';
$_SESSION['foto_perfil'] = $usuario['foto_perfil'] ?? '';

redirecionarAlerta("principal.php", "sucesso", "Login realizado com sucesso!");
