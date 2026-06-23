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
$compostos = isset($input['compostos']) ? (int)$input['compostos'] : 0;
$id_usuario = (int)$_SESSION['id_usuario'];

if ($pontuacao < 0 || $compostos < 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Dados inválidos.']);
    exit();
}

mysqli_begin_transaction($conexao);

try {
    $sqlModo = "SELECT id_modo FROM modos_jogo WHERE nome_modo = 'Rankeada' LIMIT 1";
    $resModo = mysqli_query($conexao, $sqlModo);

    if (!$resModo || mysqli_num_rows($resModo) === 0) {
        throw new Exception('Modo Rankeada não encontrado no banco de dados.');
    }

    $modo = mysqli_fetch_assoc($resModo);
    $id_modo = (int)$modo['id_modo'];

    $sqlPartida = "INSERT INTO partidas (id_modo, data_partida) VALUES (?, NOW())";
    $stmtPartida = mysqli_prepare($conexao, $sqlPartida);
    if (!$stmtPartida) {
        throw new Exception('Erro ao registrar partida.');
    }
    mysqli_stmt_bind_param($stmtPartida, 'i', $id_modo);
    if (!mysqli_stmt_execute($stmtPartida)) {
        throw new Exception('Erro ao salvar partida.');
    }
    $id_partida = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmtPartida);

    $sqlJogador = "INSERT INTO jogadores_partida (id_partida, id_usuario, pontuacao) VALUES (?, ?, ?)";
    $stmtJogador = mysqli_prepare($conexao, $sqlJogador);
    if (!$stmtJogador) {
        throw new Exception('Erro ao registrar jogador na partida.');
    }
    mysqli_stmt_bind_param($stmtJogador, 'iii', $id_partida, $id_usuario, $pontuacao);
    if (!mysqli_stmt_execute($stmtJogador)) {
        throw new Exception('Erro ao salvar pontuação da partida.');
    }
    mysqli_stmt_close($stmtJogador);

    $sqlRecorde = "SELECT recorde FROM usuarios WHERE id_usuario = ? LIMIT 1";
    $stmtRecorde = mysqli_prepare($conexao, $sqlRecorde);
    mysqli_stmt_bind_param($stmtRecorde, 'i', $id_usuario);
    mysqli_stmt_execute($stmtRecorde);
    $resRecorde = mysqli_stmt_get_result($stmtRecorde);
    $usuario = mysqli_fetch_assoc($resRecorde);
    mysqli_stmt_close($stmtRecorde);

    $recordeAtual = (int)($usuario['recorde'] ?? 0);
    $novoRecorde = false;

    if ($pontuacao > $recordeAtual) {
        $sqlUpdateRecorde = "UPDATE usuarios SET recorde = ? WHERE id_usuario = ?";
        $stmtUpdateRecorde = mysqli_prepare($conexao, $sqlUpdateRecorde);
        mysqli_stmt_bind_param($stmtUpdateRecorde, 'ii', $pontuacao, $id_usuario);
        mysqli_stmt_execute($stmtUpdateRecorde);
        mysqli_stmt_close($stmtUpdateRecorde);
        $novoRecorde = true;
        $recordeAtual = $pontuacao;
    }

    $sqlRankingExiste = "SELECT id_ranking, pontuacao FROM ranking WHERE id_usuario = ? LIMIT 1";
    $stmtRankingExiste = mysqli_prepare($conexao, $sqlRankingExiste);
    mysqli_stmt_bind_param($stmtRankingExiste, 'i', $id_usuario);
    mysqli_stmt_execute($stmtRankingExiste);
    $resRanking = mysqli_stmt_get_result($stmtRankingExiste);
    $rankingAtual = mysqli_fetch_assoc($resRanking);
    mysqli_stmt_close($stmtRankingExiste);

    if ($rankingAtual) {
        if ($pontuacao > (int)$rankingAtual['pontuacao']) {
            $sqlUpdateRanking = "UPDATE ranking SET pontuacao = ? WHERE id_usuario = ?";
            $stmtUpdateRanking = mysqli_prepare($conexao, $sqlUpdateRanking);
            mysqli_stmt_bind_param($stmtUpdateRanking, 'ii', $pontuacao, $id_usuario);
            mysqli_stmt_execute($stmtUpdateRanking);
            mysqli_stmt_close($stmtUpdateRanking);
        }
    } else {
        $sqlInsertRanking = "INSERT INTO ranking (id_usuario, pontuacao, posicao) VALUES (?, ?, 0)";
        $stmtInsertRanking = mysqli_prepare($conexao, $sqlInsertRanking);
        mysqli_stmt_bind_param($stmtInsertRanking, 'ii', $id_usuario, $pontuacao);
        mysqli_stmt_execute($stmtInsertRanking);
        mysqli_stmt_close($stmtInsertRanking);
    }

    $sqlLista = "SELECT id_ranking FROM ranking ORDER BY pontuacao DESC, id_ranking ASC";
    $resLista = mysqli_query($conexao, $sqlLista);
    $posicao = 1;

    if ($resLista) {
        while ($row = mysqli_fetch_assoc($resLista)) {
            $idRanking = (int)$row['id_ranking'];
            $sqlPos = "UPDATE ranking SET posicao = ? WHERE id_ranking = ?";
            $stmtPos = mysqli_prepare($conexao, $sqlPos);
            mysqli_stmt_bind_param($stmtPos, 'ii', $posicao, $idRanking);
            mysqli_stmt_execute($stmtPos);
            mysqli_stmt_close($stmtPos);
            $posicao++;
        }
    }

    mysqli_commit($conexao);

    echo json_encode([
        'ok' => true,
        'pontuacao' => $pontuacao,
        'compostos' => $compostos,
        'recorde' => $recordeAtual,
        'novo_recorde' => $novoRecorde
    ]);
} catch (Exception $e) {
    mysqli_rollback($conexao);
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

mysqli_close($conexao);
