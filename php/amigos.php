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

function temSolicitacaoPendente($conexao, $id_remetente, $id_destinatario) {
    $sql = "SELECT id_solicitacao FROM solicitacoes_amizade
            WHERE id_remetente = ? AND id_destinatario = ? AND status = 'pendente'
            LIMIT 1";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $id_remetente, $id_destinatario);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $existe = mysqli_num_rows($res) > 0;
    mysqli_stmt_close($stmt);
    return $existe;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'enviar') {
        $id_destinatario = (int)($_POST['id_destinatario'] ?? 0);

        if ($id_destinatario <= 0 || $id_destinatario === $id_usuario) {
            $mensagem = 'Usuário inválido.';
            $tipo_mensagem = 'erro';
        } elseif (saoAmigos($conexao, $id_usuario, $id_destinatario)) {
            $mensagem = 'Vocês já são amigos.';
            $tipo_mensagem = 'info';
        } elseif (temSolicitacaoPendente($conexao, $id_usuario, $id_destinatario)) {
            $mensagem = 'Solicitação já enviada.';
            $tipo_mensagem = 'info';
        } elseif (temSolicitacaoPendente($conexao, $id_destinatario, $id_usuario)) {
            $mensagem = 'Este usuário já te enviou uma solicitação. Aceite na lista abaixo.';
            $tipo_mensagem = 'info';
        } else {
            $sql = "INSERT INTO solicitacoes_amizade (id_remetente, id_destinatario, status) VALUES (?, ?, 'pendente')";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $id_usuario, $id_destinatario);
            if (mysqli_stmt_execute($stmt)) {
                $mensagem = 'Solicitação de amizade enviada!';
                $tipo_mensagem = 'sucesso';
            } else {
                $mensagem = 'Erro ao enviar solicitação.';
                $tipo_mensagem = 'erro';
            }
            mysqli_stmt_close($stmt);
        }
    } elseif ($acao === 'aceitar') {
        $id_solicitacao = (int)($_POST['id_solicitacao'] ?? 0);
        $sql = "SELECT id_remetente, id_destinatario FROM solicitacoes_amizade
                WHERE id_solicitacao = ? AND id_destinatario = ? AND status = 'pendente' LIMIT 1";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $id_solicitacao, $id_usuario);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $sol = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($sol) {
            mysqli_begin_transaction($conexao);
            $id_remetente = (int)$sol['id_remetente'];
            $sqlAmizade = "INSERT INTO amizades (id_usuario1, id_usuario2) VALUES (?, ?)";
            $stmtA = mysqli_prepare($conexao, $sqlAmizade);
            mysqli_stmt_bind_param($stmtA, 'ii', $id_remetente, $id_usuario);
            $ok1 = mysqli_stmt_execute($stmtA);
            mysqli_stmt_close($stmtA);

            $sqlUp = "UPDATE solicitacoes_amizade SET status = 'aceita' WHERE id_solicitacao = ?";
            $stmtU = mysqli_prepare($conexao, $sqlUp);
            mysqli_stmt_bind_param($stmtU, 'i', $id_solicitacao);
            $ok2 = mysqli_stmt_execute($stmtU);
            mysqli_stmt_close($stmtU);

            if ($ok1 && $ok2) {
                mysqli_commit($conexao);
                $mensagem = 'Amizade aceita!';
                $tipo_mensagem = 'sucesso';
            } else {
                mysqli_rollback($conexao);
                $mensagem = 'Erro ao aceitar solicitação.';
                $tipo_mensagem = 'erro';
            }
        } else {
            $mensagem = 'Solicitação não encontrada.';
            $tipo_mensagem = 'erro';
        }
    } elseif ($acao === 'recusar') {
        $id_solicitacao = (int)($_POST['id_solicitacao'] ?? 0);
        $sql = "UPDATE solicitacoes_amizade SET status = 'recusada'
                WHERE id_solicitacao = ? AND id_destinatario = ? AND status = 'pendente'";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $id_solicitacao, $id_usuario);
        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            $mensagem = 'Solicitação recusada.';
            $tipo_mensagem = 'info';
        } else {
            $mensagem = 'Solicitação não encontrada.';
            $tipo_mensagem = 'erro';
        }
        mysqli_stmt_close($stmt);
    } elseif ($acao === 'remover') {
        $id_amigo = (int)($_POST['id_amigo'] ?? 0);
        $sql = "DELETE FROM amizades
                WHERE (id_usuario1 = ? AND id_usuario2 = ?)
                   OR (id_usuario1 = ? AND id_usuario2 = ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'iiii', $id_usuario, $id_amigo, $id_amigo, $id_usuario);
        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            $mensagem = 'Amigo removido.';
            $tipo_mensagem = 'info';
        } else {
            $mensagem = 'Amigo não encontrado.';
            $tipo_mensagem = 'erro';
        }
        mysqli_stmt_close($stmt);
    }
}

$busca = trim($_GET['busca'] ?? '');
$resultados_busca = [];

if ($busca !== '') {
    $like = '%' . $busca . '%';
    $sql = "SELECT id_usuario, nome_usuario FROM usuarios
            WHERE nome_usuario LIKE ? AND id_usuario != ?
            ORDER BY nome_usuario ASC LIMIT 20";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $like, $id_usuario);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $id_outro = (int)$row['id_usuario'];
        $resultados_busca[] = [
            'id_usuario' => $id_outro,
            'nome_usuario' => $row['nome_usuario'],
            'ja_amigo' => saoAmigos($conexao, $id_usuario, $id_outro),
            'solicitacao_enviada' => temSolicitacaoPendente($conexao, $id_usuario, $id_outro),
            'solicitacao_recebida' => temSolicitacaoPendente($conexao, $id_outro, $id_usuario)
        ];
    }
    mysqli_stmt_close($stmt);
}

$solicitacoes_recebidas = [];
$sqlRec = "SELECT s.id_solicitacao, u.nome_usuario, u.id_usuario
           FROM solicitacoes_amizade s
           JOIN usuarios u ON u.id_usuario = s.id_remetente
           WHERE s.id_destinatario = ? AND s.status = 'pendente'
           ORDER BY s.data_envio DESC";
$stmtRec = mysqli_prepare($conexao, $sqlRec);
mysqli_stmt_bind_param($stmtRec, 'i', $id_usuario);
mysqli_stmt_execute($stmtRec);
$resRec = mysqli_stmt_get_result($stmtRec);
while ($row = mysqli_fetch_assoc($resRec)) {
    $solicitacoes_recebidas[] = $row;
}
mysqli_stmt_close($stmtRec);

$solicitacoes_enviadas = [];
$sqlEnv = "SELECT s.id_solicitacao, u.nome_usuario
           FROM solicitacoes_amizade s
           JOIN usuarios u ON u.id_usuario = s.id_destinatario
           WHERE s.id_remetente = ? AND s.status = 'pendente'
           ORDER BY s.data_envio DESC";
$stmtEnv = mysqli_prepare($conexao, $sqlEnv);
mysqli_stmt_bind_param($stmtEnv, 'i', $id_usuario);
mysqli_stmt_execute($stmtEnv);
$resEnv = mysqli_stmt_get_result($stmtEnv);
while ($row = mysqli_fetch_assoc($resEnv)) {
    $solicitacoes_enviadas[] = $row;
}
mysqli_stmt_close($stmtEnv);

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

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Amigos</title>
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
            <h1 class="ranking-title">AMIGOS</h1>
            <p class="ranking-subtitle">Adicione amigos para jogar e competir</p>

            <?php if ($mensagem !== ''): ?>
                <div class="amigos-msg amigos-msg-<?php echo htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <section class="amigos-section">
                <h2 class="amigos-section-title">Buscar jogador</h2>
                <form class="amigos-search-form" method="get" action="amigos.php">
                    <input type="text" name="busca" class="amigos-search-input"
                           placeholder="Nome do usuário..."
                           value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>"
                           maxlength="50" required>
                    <button type="submit" class="btn amigos-btn">Buscar</button>
                </form>

                <?php if ($busca !== ''): ?>
                    <?php if (count($resultados_busca) === 0): ?>
                        <p class="amigos-empty">Nenhum jogador encontrado.</p>
                    <?php else: ?>
                        <ul class="amigos-list">
                            <?php foreach ($resultados_busca as $user): ?>
                                <li class="amigos-item">
                                    <span class="amigos-name"><?php echo htmlspecialchars($user['nome_usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if ($user['ja_amigo']): ?>
                                        <span class="amigos-badge">Amigo</span>
                                    <?php elseif ($user['solicitacao_recebida']): ?>
                                        <span class="amigos-badge amigos-badge-pending">Te enviou solicitação</span>
                                    <?php elseif ($user['solicitacao_enviada']): ?>
                                        <span class="amigos-badge amigos-badge-pending">Solicitação enviada</span>
                                    <?php else: ?>
                                        <form method="post" action="amigos.php" class="amigos-inline-form">
                                            <input type="hidden" name="acao" value="enviar">
                                            <input type="hidden" name="id_destinatario" value="<?php echo (int)$user['id_usuario']; ?>">
                                            <button type="submit" class="btn amigos-btn amigos-btn-small">Adicionar</button>
                                        </form>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <?php if (count($solicitacoes_recebidas) > 0): ?>
                <section class="amigos-section">
                    <h2 class="amigos-section-title">Solicitações recebidas</h2>
                    <ul class="amigos-list">
                        <?php foreach ($solicitacoes_recebidas as $sol): ?>
                            <li class="amigos-item">
                                <span class="amigos-name"><?php echo htmlspecialchars($sol['nome_usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <div class="amigos-actions">
                                    <form method="post" action="amigos.php" class="amigos-inline-form">
                                        <input type="hidden" name="acao" value="aceitar">
                                        <input type="hidden" name="id_solicitacao" value="<?php echo (int)$sol['id_solicitacao']; ?>">
                                        <button type="submit" class="btn amigos-btn amigos-btn-small">Aceitar</button>
                                    </form>
                                    <form method="post" action="amigos.php" class="amigos-inline-form">
                                        <input type="hidden" name="acao" value="recusar">
                                        <input type="hidden" name="id_solicitacao" value="<?php echo (int)$sol['id_solicitacao']; ?>">
                                        <button type="submit" class="btn amigos-btn amigos-btn-small amigos-btn-danger">Recusar</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (count($solicitacoes_enviadas) > 0): ?>
                <section class="amigos-section">
                    <h2 class="amigos-section-title">Solicitações enviadas</h2>
                    <ul class="amigos-list">
                        <?php foreach ($solicitacoes_enviadas as $sol): ?>
                            <li class="amigos-item">
                                <span class="amigos-name"><?php echo htmlspecialchars($sol['nome_usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="amigos-badge amigos-badge-pending">Aguardando</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <section class="amigos-section">
                <h2 class="amigos-section-title">Meus amigos (<?php echo count($amigos); ?>)</h2>
                <?php if (count($amigos) === 0): ?>
                    <div class="ranking-empty">
                        <p>Você ainda não tem amigos. Busque jogadores acima!</p>
                    </div>
                <?php else: ?>
                    <ul class="amigos-list">
                        <?php foreach ($amigos as $amigo): ?>
                            <li class="amigos-item">
                                <span class="amigos-name"><?php echo htmlspecialchars($amigo['nome_usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <form method="post" action="amigos.php" class="amigos-inline-form"
                                      onsubmit="return confirm('Remover este amigo?');">
                                    <input type="hidden" name="acao" value="remover">
                                    <input type="hidden" name="id_amigo" value="<?php echo (int)$amigo['id_usuario']; ?>">
                                    <button type="submit" class="btn amigos-btn amigos-btn-small amigos-btn-danger">Remover</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <div class="ranking-actions">
                <a class="btn ranking-play-btn" href="jogo_amigos.php">Jogar com amigos</a>
            </div>
        </div>
    </main>

    <footer class="hud-bottom">
        <a class="hud-ico" href="perfil.php" title="Perfil" aria-label="Perfil">
            <span class="ico ico-user" aria-hidden="true"></span>
        </a>
        <div class="hud-title">QUIMICRAFT</div>
        <div class="hud-right">
            <a class="hud-ico hud-ico-active" href="amigos.php" title="Amigos" aria-label="Amigos">
                <span class="ico ico-friends" aria-hidden="true"></span>
            </a>
            <a class="hud-ico" href="ranking.php" title="Ranking" aria-label="Ranking">
                <span class="ico ico-trophy" aria-hidden="true"></span>
            </a>
        </div>
    </footer>

</body>
</html>
