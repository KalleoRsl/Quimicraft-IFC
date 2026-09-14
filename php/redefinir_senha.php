<?php
include("conexao.php");
include("includes/alerta.php");

$nome = trim($_POST['nome'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';
$turma = $_POST['turma'] ?? '';

if ($nome === '' || $senha === '' || $confirmar === '' || $turma === '') {
    redirecionarAlerta("../html/redefinir_senha.html", "erro", "Preencha todos os campos!");
}

if ($senha !== $confirmar) {
    redirecionarAlerta("../html/redefinir_senha.html", "erro", "As senhas não coincidem!");
}

$sql = "SELECT id_usuario FROM usuarios WHERE nome_usuario = ? AND id_turma = ? LIMIT 1";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    redirecionarAlerta("../html/redefinir_senha.html", "erro", "Erro ao preparar consulta!");
}

mysqli_stmt_bind_param($stmt, "si", $nome, $turma);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if (!$usuario) {
    redirecionarAlerta("../html/redefinir_senha.html", "erro", "Usuário ou turma inválidos!");
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$id = (int)$usuario['id_usuario'];

$sqlUpdate = "UPDATE usuarios SET senha = ? WHERE id_usuario = ?";
$stmtUpdate = mysqli_prepare($conexao, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpdate, "si", $senhaHash, $id);

if (mysqli_stmt_execute($stmtUpdate)) {
    mysqli_stmt_close($stmtUpdate);
    mysqli_close($conexao);
    redirecionarAlerta("../html/login.html", "sucesso", "Senha redefinida com sucesso!");
}

mysqli_stmt_close($stmtUpdate);
mysqli_close($conexao);
redirecionarAlerta("../html/redefinir_senha.html", "erro", "Erro ao redefinir senha!");
