<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Usuário não autenticado.']);
    exit();
}

include('conexao.php');

$input = json_decode(file_get_contents('php://input'), true);
$pontuacao = isset($input['pontuacao']) ? (int)$input['pontuacao'] : 0;
$niveis = isset($input['niveis']) ? (int)$input['niveis'] : 0;
$id_desafio = isset($input['id_desafio']) ? (int)$input['id_desafio'] : 0;
$id_usuario = (int)$_SESSION['id_usuario'];

if ($pontuacao < 0 || $niveis < 0 || $id_desafio <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
    exit();
}

mysqli_begin_transaction($conexao);

try {
    $sql = "SELECT d.*, ud.nome_usuario AS nome_desafiante, uo.nome_usuario AS nome_desafiado
            FROM desafios_amigos d
            JOIN usuarios ud ON ud.id_usuario = d.id_desafiante
            JOIN usuarios uo ON uo.id_usuario = d.id_desafiado
            WHERE d.id_desafio = ? LIMIT 1 FOR UPDATE";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_desafio);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $desafio = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$desafio) {
        throw new Exception('Desafio não encontrado.');
    }

    if ($desafio['status'] === 'finalizado') {
        throw new Exception('Este desafio já foi finalizado.');
    }

    $sou_desafiante = ((int)$desafio['id_desafiante'] === $id_usuario);
    $sou_desafiado = ((int)$desafio['id_desafiado'] === $id_usuario);

    if (!$sou_desafiante && !$sou_desafiado) {
        throw new Exception('Você não participa deste desafio.');
    }

    if ($sou_desafiante && $desafio['pontuacao_desafiante'] !== null) {
        throw new Exception('Você já jogou este desafio.');
    }

    if ($sou_desafiado && $desafio['pontuacao_desafiado'] !== null) {
        throw new Exception('Você já jogou este desafio.');
    }

    $id_partida = (int)$desafio['id_partida'];

    $sqlJogador = "INSERT INTO jogadores_partida (id_partida, id_usuario, pontuacao) VALUES (?, ?, ?)";
    $stmtJogador = mysqli_prepare($conexao, $sqlJogador);
    mysqli_stmt_bind_param($stmtJogador, 'iii', $id_partida, $id_usuario, $pontuacao);
    if (!mysqli_stmt_execute($stmtJogador)) {
        throw new Exception('Erro ao registrar pontuação.');
    }
    mysqli_stmt_close($stmtJogador);

    if ($sou_desafiante) {
        $pont_desafiante = $pontuacao;
        $pont_desafiado = $desafio['pontuacao_desafiado'] !== null ? (int)$desafio['pontuacao_desafiado'] : null;
    } else {
        $pont_desafiado = $pontuacao;
        $pont_desafiante = $desafio['pontuacao_desafiante'] !== null ? (int)$desafio['pontuacao_desafiante'] : null;
    }

    $oponente_jogou = $sou_desafiante
        ? ($desafio['pontuacao_desafiado'] !== null)
        : ($desafio['pontuacao_desafiante'] !== null);

    $novo_status = 'aguardando_oponente';
    $id_vencedor = null;
    $resultado = 'aguardando';

    if ($oponente_jogou) {
        $novo_status = 'finalizado';

        if ($pont_desafiante > $pont_desafiado) {
            $id_vencedor = (int)$desafio['id_desafiante'];
            $resultado = ($id_vencedor === $id_usuario) ? 'vitoria' : 'derrota';
        } elseif ($pont_desafiado > $pont_desafiante) {
            $id_vencedor = (int)$desafio['id_desafiado'];
            $resultado = ($id_vencedor === $id_usuario) ? 'vitoria' : 'derrota';
        } else {
            $resultado = 'empate';
        }
    }

    if ($sou_desafiante) {
        $sqlUp = "UPDATE desafios_amigos
                  SET pontuacao_desafiante = ?, status = ?, id_vencedor = ?
                  WHERE id_desafio = ?";
        $stmtUp = mysqli_prepare($conexao, $sqlUp);
        mysqli_stmt_bind_param($stmtUp, 'isii', $pontuacao, $novo_status, $id_vencedor, $id_desafio);
    } else {
        $sqlUp = "UPDATE desafios_amigos
                  SET pontuacao_desafiado = ?, status = ?, id_vencedor = ?
                  WHERE id_desafio = ?";
        $stmtUp = mysqli_prepare($conexao, $sqlUp);
        mysqli_stmt_bind_param($stmtUp, 'isii', $pontuacao, $novo_status, $id_vencedor, $id_desafio);
    }

    if (!mysqli_stmt_execute($stmtUp)) {
        throw new Exception('Erro ao atualizar desafio.');
    }
    mysqli_stmt_close($stmtUp);

    mysqli_commit($conexao);

    $oponente_nome = $sou_desafiante ? $desafio['nome_desafiado'] : $desafio['nome_desafiante'];

    if ($sou_desafiante) {
        $pontuacao_oponente = $desafio['pontuacao_desafiado'] !== null ? (int)$desafio['pontuacao_desafiado'] : null;
    } else {
        $pontuacao_oponente = $desafio['pontuacao_desafiante'] !== null ? (int)$desafio['pontuacao_desafiante'] : null;
    }

    echo json_encode([
        'ok' => true,
        'pontuacao' => $pontuacao,
        'niveis' => $niveis,
        'resultado' => $resultado,
        'oponente_nome' => $oponente_nome,
        'pontuacao_oponente' => $pontuacao_oponente,
        'id_vencedor' => $id_vencedor
    ]);
} catch (Exception $e) {
    mysqli_rollback($conexao);
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

mysqli_close($conexao);
