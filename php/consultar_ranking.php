<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Usuário não autenticado.']);
    exit();
}

include('conexao.php');

$id_usuario = (int)$_SESSION['id_usuario'];
$modo = isset($_GET['modo']) ? trim($_GET['modo']) : 'amigos';

if ($modo === 'geral') {
    $sql = "
        SELECT
            u.id_usuario,
            u.nome_usuario,
            COALESCE(r.pontuacao, u.recorde, 0) AS pontuacao,
            r.posicao
        FROM usuarios u
        LEFT JOIN ranking r ON r.id_usuario = u.id_usuario
        WHERE COALESCE(r.pontuacao, u.recorde, 0) > 0
        ORDER BY pontuacao DESC, u.nome_usuario ASC
    ";
    $stmt = mysqli_prepare($conexao, $sql);
} else {
    $sql = "
        SELECT
            u.id_usuario,
            u.nome_usuario,
            COALESCE(r.pontuacao, u.recorde, 0) AS pontuacao,
            r.posicao
        FROM usuarios u
        LEFT JOIN ranking r ON r.id_usuario = u.id_usuario
        WHERE u.id_usuario = ?
           OR u.id_usuario IN (
                SELECT id_usuario2 FROM amizades WHERE id_usuario1 = ?
                UNION
                SELECT id_usuario1 FROM amizades WHERE id_usuario2 = ?
           )
        ORDER BY pontuacao DESC, u.nome_usuario ASC
    ";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'iii', $id_usuario, $id_usuario, $id_usuario);
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao consultar ranking.']);
    exit();
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$lista = [];
$posicao = 1;

while ($row = mysqli_fetch_assoc($resultado)) {
    $lista[] = [
        'posicao' => $posicao,
        'id_usuario' => (int)$row['id_usuario'],
        'nome_usuario' => $row['nome_usuario'],
        'pontuacao' => (int)$row['pontuacao'],
        'eu' => ((int)$row['id_usuario'] === $id_usuario)
    ];
    $posicao++;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode([
    'ok' => true,
    'modo' => $modo,
    'ranking' => $lista
]);
