<?php
include("conexao.php");
include("includes/alerta.php");

$nome = trim($_POST['nome'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';
$turma = $_POST['turma'] ?? '';

if ($nome === '' || $senha === '' || $confirmar === '' || $turma === '') {
    redirecionarAlerta("../html/cadastro.html", "erro", "Preencha todos os campos!");
}

if ($senha != $confirmar) {
    redirecionarAlerta("../html/cadastro.html", "erro", "As senhas não coincidem!");
}

$sqlCheck = "SELECT id_usuario FROM usuarios WHERE nome_usuario = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conexao, $sqlCheck);

if (!$stmtCheck) {
    redirecionarAlerta("../html/cadastro.html", "erro", "Erro ao cadastrar usuário!");
}

mysqli_stmt_bind_param($stmtCheck, "s", $nome);
mysqli_stmt_execute($stmtCheck);
$resultado = mysqli_stmt_get_result($stmtCheck);
$existe = $resultado && mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmtCheck);

if ($existe) {
    redirecionarAlerta("../html/cadastro.html", "erro", "Este nome de usuário já está em uso!");
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nome_usuario, senha, id_turma) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    redirecionarAlerta("../html/cadastro.html", "erro", "Erro ao cadastrar usuário!");
}

mysqli_stmt_bind_param($stmt, "ssi", $nome, $senhaHash, $turma);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    redirecionarAlerta("../html/index.html", "sucesso", "Cadastro realizado com sucesso!");
}

$duplicado = mysqli_errno($conexao) === 1062;
mysqli_stmt_close($stmt);

if ($duplicado) {
    redirecionarAlerta("../html/cadastro.html", "erro", "Este nome de usuário já está em uso!");
}

redirecionarAlerta("../html/cadastro.html", "erro", "Erro ao cadastrar usuário!");
