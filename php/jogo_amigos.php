<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$nome = $_SESSION['nome_usuario'] ?? 'USUÁRIO';
$turma = $_SESSION['id_turma'] ?? '';
$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem = '';
$tipo_mensagem = '';

include('conexao.php');

function saoAmigos($conexao, $id1, $id2) {
    $sql = "SELECT id_amizade FROM amizades
            WHERE (id_usuario1 = ? AND id_usuario2 = ?)
               OR (id_usuario1 = ? AND id_usuario2 = ?)
            LIMIT 1";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'iiii', $id1, $id2, $id2, $id1);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $existe = mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    return $existe;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'desafiar') {
    $id_amigo = (int)($_POST['id_amigo'] ?? 0);

    if ($id_amigo <= 0 || $id_amigo === $id_usuario) {
        $mensagem = 'Amigo inválido.';
        $tipo_mensagem = 'erro';
    } elseif (!saoAmigos($conexao, $id_usuario, $id_amigo)) {
        $mensagem = 'Vocês precisam ser amigos para desafiar.';
        $tipo_mensagem = 'erro';
    } else {
        $sqlDesafioAtivo = "SELECT id_desafio FROM desafios_amigos
                            WHERE status IN ('pendente', 'aguardando_oponente')
                              AND ((id_desafiante = ? AND id_desafiado = ?)
                                OR (id_desafiante = ? AND id_desafiado = ?))
                            LIMIT 1";
        $stmtAtivo = mysqli_prepare($conexao, $sqlDesafioAtivo);
        mysqli_stmt_bind_param($stmtAtivo, 'iiii', $id_usuario, $id_amigo, $id_amigo, $id_usuario);
        mysqli_stmt_execute($stmtAtivo);
        $resAtivo = mysqli_stmt_get_result($stmtAtivo);
        $desafioAtivo = mysqli_fetch_assoc($resAtivo);
        mysqli_stmt_close($stmtAtivo);

        if ($desafioAtivo) {
            header('Location: batalha_amigos.php?id=' . (int)$desafioAtivo['id_desafio']);
            exit();
        }

        mysqli_begin_transaction($conexao);

        try {
            $sqlModo = "SELECT id_modo FROM modos_jogo WHERE nome_modo = 'Amigos' LIMIT 1";
            $resModo = mysqli_query($conexao, $sqlModo);
            if (!$resModo || mysqli_num_rows($resModo) === 0) {
                throw new Exception('Modo Amigos não encontrado.');
            }
            $modo = mysqli_fetch_assoc($resModo);
            $id_modo = (int)$modo['id_modo'];

            $sqlPartida = "INSERT INTO partidas (id_modo, data_partida) VALUES (?, NOW())";
            $stmtPartida = mysqli_prepare($conexao, $sqlPartida);
            mysqli_stmt_bind_param($stmtPartida, 'i', $id_modo);
            mysqli_stmt_execute($stmtPartida);
            $id_partida = mysqli_insert_id($conexao);
            mysqli_stmt_close($stmtPartida);

            $sqlDesafio = "INSERT INTO desafios_amigos (id_desafiante, id_desafiado, id_partida, status)
                           VALUES (?, ?, ?, 'pendente')";
            $stmtDesafio = mysqli_prepare($conexao, $sqlDesafio);
            mysqli_stmt_bind_param($stmtDesafio, 'iii', $id_usuario, $id_amigo, $id_partida);
            mysqli_stmt_execute($stmtDesafio);
            $id_desafio = mysqli_insert_id($conexao);
            mysqli_stmt_close($stmtDesafio);

            mysqli_commit($conexao);
            header('Location: batalha_amigos.php?id=' . $id_desafio);
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            $mensagem = 'Erro ao criar desafio.';
            $tipo_mensagem = 'erro';
        }
    }
}

$amigos = [];
$sqlAmigos = "SELECT u.id_usuario, u.nome_usuario
              FROM amizades a
              JOIN usuarios u ON u.id_usuario = IF(a.id_usuario1 = ?, a.id_usuario2, a.id_usuario1)
              WHERE a.id_usuario1 = ? OR a.id_usuario2 = ?
              ORDER BY u.nome_usuario ASC";
$stmtAmigos = mysqli_prepare($conexao, $sqlAmigos);
mysqli_stmt_bind_param($stmtAmigos, 'iii', $id_usuario, $id_usuario, $id_usuario);
mysqli_stmt_execute($stmtAmigos);
$resAmigos = mysqli_stmt_get_result($stmtAmigos);
while ($row = mysqli_fetch_assoc($resAmigos)) {
    $amigos[] = $row;
}
mysqli_stmt_close($stmtAmigos);

$desafios_pendentes = [];
$sqlPend = "SELECT d.id_desafio, d.status, d.pontuacao_desafiante, d.pontuacao_desafiado,
                   d.id_vencedor, d.data_criacao,
                   ud.nome_usuario AS nome_desafiante,
                   uo.nome_usuario AS nome_desafiado,
                   d.id_desafiante, d.id_desafiado
            FROM desafios_amigos d
            JOIN usuarios ud ON ud.id_usuario = d.id_desafiante
            JOIN usuarios uo ON uo.id_usuario = d.id_desafiado
            WHERE (d.id_desafiante = ? OR d.id_desafiado = ?)
              AND d.status IN ('pendente', 'aguardando_oponente')
            ORDER BY d.data_criacao DESC";
$stmtPend = mysqli_prepare($conexao, $sqlPend);
mysqli_stmt_bind_param($stmtPend, 'ii', $id_usuario, $id_usuario);
mysqli_stmt_execute($stmtPend);
$resPend = mysqli_stmt_get_result($stmtPend);
while ($row = mysqli_fetch_assoc($resPend)) {
    $desafios_pendentes[] = $row;
}
mysqli_stmt_close($stmtPend);

$desafios_finalizados = [];
$sqlFin = "SELECT d.id_desafio, d.pontuacao_desafiante, d.pontuacao_desafiado,
                  d.id_vencedor, d.data_criacao,
                  ud.nome_usuario AS nome_desafiante,
                  uo.nome_usuario AS nome_desafiado,
                  d.id_desafiante, d.id_desafiado
           FROM desafios_amigos d
           JOIN usuarios ud ON ud.id_usuario = d.id_desafiante
           JOIN usuarios uo ON uo.id_usuario = d.id_desafiado
           WHERE (d.id_desafiante = ? OR d.id_desafiado = ?)
             AND d.status = 'finalizado'
           ORDER BY d.data_criacao DESC
           LIMIT 10";
$stmtFin = mysqli_prepare($conexao, $sqlFin);
mysqli_stmt_bind_param($stmtFin, 'ii', $id_usuario, $id_usuario);
mysqli_stmt_execute($stmtFin);
$resFin = mysqli_stmt_get_result($stmtFin);
while ($row = mysqli_fetch_assoc($resFin)) {
    $desafios_finalizados[] = $row;
}
mysqli_stmt_close($stmtFin);

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Jogar com Amigos</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg ranking-body">

    <header class="hud-top">
        <div class="hud-user">
            <div class="hud-avatar" aria-hidden="true"></div>
            <div class="hud-usertext">
                <div class="hud-username"><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="hud-userinfo"><?php echo $turma !== '' ? ('TURMA ' . htmlspecialchars((string)$turma, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
            </div>
        </div>
        <a class="hud-sair" href="principal.php">VOLTAR</a>
    </header>

    <main class="ranking-wrap">
        <div class="ranking-panel amigos-panel">
            <h1 class="ranking-title">JOGAR COM AMIGOS</h1>
            <p class="ranking-subtitle">Desafie um amigo — 10 níveis, quem fizer mais pontos vence!</p>

            <?php if ($mensagem !== ''): ?>
                <div class="amigos-msg amigos-msg-<?php echo htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (count($desafios_pendentes) > 0): ?>
                <section class="amigos-section">
                    <h2 class="amigos-section-title">Desafios ativos</h2>
                    <ul class="amigos-list">
                        <?php foreach ($desafios_pendentes as $d): ?>
                            <?php
                                $sou_desafiante = ((int)$d['id_desafiante'] === $id_usuario);
                                $oponente = $sou_desafiante ? $d['nome_desafiado'] : $d['nome_desafiante'];
                                $minha_pont = $sou_desafiante ? $d['pontuacao_desafiante'] : $d['pontuacao_desafiado'];
                                $pont_oponente = $sou_desafiante ? $d['pontuacao_desafiado'] : $d['pontuacao_desafiante'];
                                $ja_joguei = ($minha_pont !== null);
                                $oponente_jogou = ($pont_oponente !== null);
                            ?>
                            <li class="amigos-item amigos-item-desafio">
                                <div class="amigos-desafio-info">
                                    <span class="amigos-name">vs <?php echo htmlspecialchars($oponente, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if ($ja_joguei): ?>
                                        <span class="amigos-badge">Sua pontuação: <?php echo (int)$minha_pont; ?> pts</span>
                                    <?php endif; ?>
                                    <?php if ($oponente_jogou): ?>
                                        <span class="amigos-badge"><?php echo htmlspecialchars($oponente, ENT_QUOTES, 'UTF-8'); ?>: <?php echo (int)$pont_oponente; ?> pts</span>
                                    <?php elseif ($ja_joguei): ?>
                                        <span class="amigos-badge amigos-badge-pending">Aguardando oponente</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!$ja_joguei): ?>
                                    <a class="btn amigos-btn amigos-btn-small" href="batalha_amigos.php?id=<?php echo (int)$d['id_desafio']; ?>">Jogar</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <section class="amigos-section">
                <h2 class="amigos-section-title">Seus amigos</h2>
                <?php if (count($amigos) === 0): ?>
                    <div class="ranking-empty">
                        <p>Você ainda não tem amigos adicionados.</p>
                        <a class="btn ranking-play-btn" href="amigos.php">Adicionar amigos</a>
                    </div>
                <?php else: ?>
                    <ul class="amigos-list">
                        <?php foreach ($amigos as $amigo): ?>
                            <li class="amigos-item">
                                <span class="amigos-name"><?php echo htmlspecialchars($amigo['nome_usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <form method="post" action="jogo_amigos.php" class="amigos-inline-form">
                                    <input type="hidden" name="acao" value="desafiar">
                                    <input type="hidden" name="id_amigo" value="<?php echo (int)$amigo['id_usuario']; ?>">
                                    <button type="submit" class="btn amigos-btn amigos-btn-small amigos-btn-challenge">Desafiar</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <?php if (count($desafios_finalizados) > 0): ?>
                <section class="amigos-section">
                    <h2 class="amigos-section-title">Últimas batalhas</h2>
                    <div class="ranking-table-wrap">
                        <table class="ranking-table">
                            <thead>
                                <tr>
                                    <th scope="col">Oponente</th>
                                    <th scope="col">Placar</th>
                                    <th scope="col">Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($desafios_finalizados as $d): ?>
                                    <?php
                                        $sou_desafiante = ((int)$d['id_desafiante'] === $id_usuario);
                                        $oponente = $sou_desafiante ? $d['nome_desafiado'] : $d['nome_desafiante'];
                                        $minha_pont = $sou_desafiante ? (int)$d['pontuacao_desafiante'] : (int)$d['pontuacao_desafiado'];
                                        $pont_oponente = $sou_desafiante ? (int)$d['pontuacao_desafiado'] : (int)$d['pontuacao_desafiante'];
                                        $venci = ((int)$d['id_vencedor'] === $id_usuario);
                                        $empate = ($minha_pont === $pont_oponente);
                                    ?>
                                    <tr class="ranking-row">
                                        <td class="ranking-name"><?php echo htmlspecialchars($oponente, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="ranking-points"><?php echo $minha_pont; ?> x <?php echo $pont_oponente; ?></td>
                                        <td class="ranking-name">
                                            <?php if ($empate): ?>
                                                <span class="amigos-badge">Empate</span>
                                            <?php elseif ($venci): ?>
                                                <span class="amigos-badge amigos-badge-win">Vitória</span>
                                            <?php else: ?>
                                                <span class="amigos-badge amigos-badge-lose">Derrota</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>

            <div class="ranking-actions">
                <a class="btn ranking-play-btn amigos-btn-secondary-link" href="amigos.php">Gerenciar amigos</a>
            </div>
        </div>
    </main>

    <footer class="hud-bottom">
        <a class="hud-ico" href="perfil.php" title="Perfil" aria-label="Perfil">
            <span class="ico ico-user" aria-hidden="true"></span>
        </a>
        <div class="hud-title">QUIMICRAFT</div>
        <div class="hud-right">
            <a class="hud-ico" href="amigos.php" title="Amigos" aria-label="Amigos">
                <span class="ico ico-friends" aria-hidden="true"></span>
            </a>
            <a class="hud-ico" href="ranking.php" title="Ranking" aria-label="Ranking">
                <span class="ico ico-trophy" aria-hidden="true"></span>
            </a>
        </div>
    </footer>

</body>
</html>
