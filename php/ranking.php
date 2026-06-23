<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$nome = $_SESSION['nome_usuario'] ?? 'USUÁRIO';
$turma = $_SESSION['id_turma'] ?? '';
$id_usuario = (int)$_SESSION['id_usuario'];
$modo = isset($_GET['modo']) && $_GET['modo'] === 'geral' ? 'geral' : 'amigos';

include('conexao.php');

if ($modo === 'geral') {
    $sql = "
        SELECT
            u.id_usuario,
            u.nome_usuario,
            COALESCE(r.pontuacao, u.recorde, 0) AS pontuacao
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
            COALESCE(r.pontuacao, u.recorde, 0) AS pontuacao
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

$ranking = [];

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $posicao = 1;

    while ($row = mysqli_fetch_assoc($resultado)) {
        $ranking[] = [
            'posicao' => $posicao,
            'id_usuario' => (int)$row['id_usuario'],
            'nome_usuario' => $row['nome_usuario'],
            'pontuacao' => (int)$row['pontuacao'],
            'eu' => ((int)$row['id_usuario'] === $id_usuario)
        ];
        $posicao++;
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Ranking</title>
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
        <div class="ranking-panel">
            <h1 class="ranking-title">RANKING</h1>
            <p class="ranking-subtitle">Modo Rankeada — melhores pontuações</p>

            <div class="ranking-tabs" role="tablist" aria-label="Filtro do ranking">
                <a class="ranking-tab<?php echo $modo === 'amigos' ? ' ranking-tab-active' : ''; ?>"
                   href="ranking.php?modo=amigos"
                   role="tab"
                   aria-selected="<?php echo $modo === 'amigos' ? 'true' : 'false'; ?>">
                    Eu e amigos
                </a>
                <a class="ranking-tab<?php echo $modo === 'geral' ? ' ranking-tab-active' : ''; ?>"
                   href="ranking.php?modo=geral"
                   role="tab"
                   aria-selected="<?php echo $modo === 'geral' ? 'true' : 'false'; ?>">
                    Geral
                </a>
            </div>

            <?php if ($modo === 'amigos'): ?>
                <p class="ranking-info">Exibindo você e seus amigos cadastrados no sistema.</p>
            <?php else: ?>
                <p class="ranking-info">Classificação geral de todos os jogadores.</p>
            <?php endif; ?>

            <?php if (count($ranking) === 0): ?>
                <div class="ranking-empty">
                    <p>Nenhuma pontuação registrada ainda.</p>
                    <a class="btn ranking-play-btn" href="ranked.php">Jogar Rankeada</a>
                </div>
            <?php else: ?>
                <div class="ranking-table-wrap">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th scope="col">Pos.</th>
                                <th scope="col">Jogador</th>
                                <th scope="col">Pontuação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ranking as $item): ?>
                                <?php
                                    $rowClass = 'ranking-row';
                                    if ($item['eu']) {
                                        $rowClass .= ' ranking-row-me';
                                    }
                                    if ($item['posicao'] === 1) {
                                        $rowClass .= ' ranking-row-gold';
                                    } elseif ($item['posicao'] === 2) {
                                        $rowClass .= ' ranking-row-silver';
                                    } elseif ($item['posicao'] === 3) {
                                        $rowClass .= ' ranking-row-bronze';
                                    }
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="ranking-pos">
                                        <?php if ($item['posicao'] <= 3): ?>
                                            <span class="ranking-medal ranking-medal-<?php echo $item['posicao']; ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <span><?php echo (int)$item['posicao']; ?>º</span>
                                    </td>
                                    <td class="ranking-name">
                                        <?php echo htmlspecialchars($item['nome_usuario'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($item['eu']): ?>
                                            <span class="ranking-you-badge">Você</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="ranking-points"><?php echo number_format($item['pontuacao'], 0, ',', '.'); ?> pts</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="ranking-actions">
                <a class="btn ranking-play-btn" href="ranked.php">Jogar Rankeada</a>
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
            <a class="hud-ico hud-ico-active" href="ranking.php" title="Ranking" aria-label="Ranking">
                <span class="ico ico-trophy" aria-hidden="true"></span>
            </a>
        </div>
    </footer>

</body>
</html>
