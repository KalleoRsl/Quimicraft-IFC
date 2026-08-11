<?php
include("conexao.php");

$nome = trim($_POST['nome'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';
$turma = $_POST['turma'] ?? '';

if ($nome === '' || $senha === '' || $confirmar === '' || $turma === '') {
    echo "<script>alert('Preencha todos os campos!'); window.history.back();</script>";
    exit();
}

if ($senha !== $confirmar) {
    echo "<script>alert('As senhas não coincidem!'); window.history.back();</script>";
    exit();
}

$sql = "SELECT id_usuario FROM usuarios WHERE nome_usuario = ? AND id_turma = ? LIMIT 1";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    echo "<script>alert('Erro ao preparar consulta!'); window.history.back();</script>";
    exit();
}

mysqli_stmt_bind_param($stmt, "si", $nome, $turma);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if (!$usuario) {
    echo "<script>alert('Usuário ou turma inválidos!'); window.history.back();</script>";
    exit();
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$id = (int)$usuario['id_usuario'];

$sqlUpdate = "UPDATE usuarios SET senha = ? WHERE id_usuario = ?";
$stmtUpdate = mysqli_prepare($conexao, $sqlUpdate);
mysqli_stmt_bind_param($stmtUpdate, "si", $senhaHash, $id);

if (mysqli_stmt_execute($stmtUpdate)) {
    echo "<script>alert('Senha redefinida com sucesso!'); window.location='../html/login.html';</script>";
} else {
    echo "<script>alert('Erro ao redefinir senha!'); window.history.back();</script>";
}

mysqli_stmt_close($stmtUpdate);
mysqli_close($conexao);
?>
