<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../html/login.html');
    exit();
}

include('conexao.php');
include('includes/hud_usuario.php');

$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem = $_GET['msg'] ?? '';
$tipo_mensagem = $_GET['tipo'] ?? 'sucesso';

$sql = "
    SELECT
        u.nome_usuario,
        u.recorde,
        u.foto_perfil,
        u.id_turma,
        t.nome_turma,
        COALESCE(r.pontuacao, u.recorde, 0) AS pontuacao_ranking,
        COALESCE(r.posicao, 0) AS posicao_ranking
    FROM usuarios u
    LEFT JOIN turmas t ON t.id_turma = u.id_turma
    LEFT JOIN ranking r ON r.id_usuario = u.id_usuario
    WHERE u.id_usuario = ?
    LIMIT 1
";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$usuario) {
    mysqli_close($conexao);
    header('Location: principal.php');
    exit();
}

$sqlPartidas = 'SELECT COUNT(*) AS total FROM jogadores_partida WHERE id_usuario = ?';
$stmtPartidas = mysqli_prepare($conexao, $sqlPartidas);
mysqli_stmt_bind_param($stmtPartidas, 'i', $id_usuario);
mysqli_stmt_execute($stmtPartidas);
$resPartidas = mysqli_stmt_get_result($stmtPartidas);
$partidasJogadas = (int)(mysqli_fetch_assoc($resPartidas)['total'] ?? 0);
mysqli_stmt_close($stmtPartidas);

mysqli_close($conexao);

$nome = $usuario['nome_usuario'];
$turmaNome = $usuario['nome_turma'] ?? '';
$recorde = (int)$usuario['recorde'];
$foto = $usuario['foto_perfil'] ?? '';
$posicaoRanking = (int)$usuario['posicao_ranking'];
$pontuacaoRanking = (int)$usuario['pontuacao_ranking'];
$fotoUrl = fotoPerfilUrl($foto);

$_SESSION['foto_perfil'] = $foto;
$_SESSION['nome_usuario'] = $nome;

$voltar = $_GET['voltar'] ?? 'principal.php';
if (!preg_match('/^[a-zA-Z0-9_\-.]+\.php(\?[a-zA-Z0-9_\-=&.]*)?$/', $voltar)) {
    $voltar = 'principal.php';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Perfil</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg ranking-body perfil-body">

    <a class="btn-voltar perfil-voltar" href="<?php echo htmlspecialchars($voltar, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Voltar">&#8592;</a>

    <main class="perfil-wrap">
        <div class="perfil-card">
            <h1 class="perfil-title">PERFIL</h1>

            <?php if ($mensagem !== ''): ?>
                <div class="perfil-msg perfil-msg-<?php echo htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form class="perfil-foto-form" method="post" action="atualizar_perfil.php" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="foto">
                <label class="perfil-avatar-wrap" title="Clique para trocar a foto">
                    <input type="file" name="foto" class="perfil-foto-input" accept="image/jpeg,image/png,image/gif,image/webp" aria-label="Enviar foto de perfil">
                    <div class="perfil-avatar<?php echo $fotoUrl ? ' perfil-avatar-has-photo' : ''; ?>"<?php echo $fotoUrl ? ' style="background-image:url(' . htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') . ')"' : ''; ?>>
                        <?php if (!$fotoUrl): ?>
                            <span class="perfil-avatar-icon" aria-hidden="true"></span>
                        <?php endif; ?>
                    </div>
                    <span class="perfil-avatar-hint">Trocar foto</span>
                </label>
            </form>

            <form class="perfil-nickname-form" method="post" action="atualizar_perfil.php">
                <input type="hidden" name="acao" value="nickname">
                <label class="perfil-nickname-label" for="perfil-nickname">Nickname</label>
                <div class="perfil-nickname-row">
                    <input
                        type="text"
                        id="perfil-nickname"
                        name="nickname"
                        class="perfil-nickname-input"
                        value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>"
                        maxlength="50"
                        required
                        autocomplete="off"
                    >
                    <button type="submit" class="perfil-save-btn" title="Salvar nickname">OK</button>
                </div>
            </form>

            <div class="perfil-stat">RECORDE: <?php echo number_format($recorde, 0, ',', '.'); ?></div>
            <div class="perfil-stat">TURMA: <?php echo $turmaNome !== '' ? htmlspecialchars(strtoupper($turmaNome), ENT_QUOTES, 'UTF-8') : '—'; ?></div>

            <div class="perfil-stats-row">
                <span class="perfil-stat-inline">RANKING ATUAL: <?php echo $posicaoRanking > 0 ? '#' . $posicaoRanking : '—'; ?></span>
                <span class="perfil-stat-inline">PARTIDAS JOGADAS: <?php echo $partidasJogadas; ?></span>
            </div>

            <a class="perfil-trophy" href="ranking.php" title="Ver ranking" aria-label="Ver ranking">
                <span class="perfil-trophy-icon" aria-hidden="true"></span>
                <?php if ($posicaoRanking > 0 && $posicaoRanking <= 3): ?>
                    <span class="perfil-trophy-badge"><?php echo $posicaoRanking; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </main>

    <?php echo renderHudFooter(); ?>

    <script>
        document.querySelector('.perfil-foto-input')?.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                this.closest('form')?.submit();
            }
        });
    </script>
</body>
</html>
