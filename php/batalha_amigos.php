<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_desafio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_desafio <= 0) {
    header("Location: jogo_amigos.php");
    exit();
}

include('conexao.php');

$sql = "SELECT d.*, ud.nome_usuario AS nome_desafiante, uo.nome_usuario AS nome_desafiado
        FROM desafios_amigos d
        JOIN usuarios ud ON ud.id_usuario = d.id_desafiante
        JOIN usuarios uo ON uo.id_usuario = d.id_desafiado
        WHERE d.id_desafio = ? LIMIT 1";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_desafio);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$desafio = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$desafio) {
    mysqli_close($conexao);
    header("Location: jogo_amigos.php");
    exit();
}

$sou_desafiante = ((int)$desafio['id_desafiante'] === $id_usuario);
$sou_desafiado = ((int)$desafio['id_desafiado'] === $id_usuario);

if (!$sou_desafiante && !$sou_desafiado) {
    mysqli_close($conexao);
    header("Location: jogo_amigos.php");
    exit();
}

$minha_pontuacao = $sou_desafiante ? $desafio['pontuacao_desafiante'] : $desafio['pontuacao_desafiado'];
$oponente_nome = $sou_desafiante ? $desafio['nome_desafiado'] : $desafio['nome_desafiante'];

if ($minha_pontuacao !== null) {
    mysqli_close($conexao);
    header("Location: jogo_amigos.php");
    exit();
}

if ($desafio['status'] === 'finalizado') {
    mysqli_close($conexao);
    header("Location: jogo_amigos.php");
    exit();
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Batalha com Amigo</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="solo-body">

    <header class="solo-header ranked-header">
        <div class="ranked-hud-left">
            <div class="solo-timer" id="timer" aria-live="polite">00:30</div>
            <div class="ranked-score" id="score-display" aria-live="polite">
                <span class="ranked-score-label">PTS</span>
                <span class="ranked-score-value" id="score">0</span>
            </div>
        </div>
        <div class="solo-level" id="level-label">NIVEL: 1</div>
        <a class="solo-sair" href="jogo_amigos.php">SAIR</a>
    </header>

    <main class="solo-main">
        <p class="solo-opponent" id="opponent-label">vs <?php echo htmlspecialchars($oponente_nome, ENT_QUOTES, 'UTF-8'); ?></p>
        <p class="solo-target-category solo-cat-oxido" id="target-category">ÓXIDO</p>
        <h1 class="solo-target" id="target-name">AGUA</h1>
        <p class="solo-target-hint" id="target-hint">Forme: Água</p>

        <div class="solo-equation-wrap">
            <div class="solo-equation">
                <div class="solo-drop-zone" id="slot-1" data-slot="1" aria-label="Primeiro reagente">
                    <span class="solo-drop-hint">?</span>
                </div>
                <span class="solo-operator" aria-hidden="true">+</span>
                <div class="solo-drop-zone" id="slot-2" data-slot="2" aria-label="Segundo reagente">
                    <span class="solo-drop-hint">?</span>
                </div>
                <span class="solo-operator" aria-hidden="true">=</span>
                <div class="solo-drop-zone solo-result-zone" id="slot-result" data-slot="result" aria-label="Produto formado">
                    <span class="solo-drop-hint">?</span>
                </div>
            </div>
        </div>
    </main>

    <footer class="solo-inventory" id="inventory" aria-label="Átomos disponíveis"></footer>

    <div class="solo-overlay hidden" id="overlay" role="dialog" aria-modal="true" aria-labelledby="overlay-title">
        <div class="solo-modal ranked-modal">
            <h2 class="solo-modal-title" id="overlay-title">Batalha encerrada!</h2>
            <p class="solo-modal-text" id="overlay-text">Sua pontuação foi registrada.</p>
            <div class="ranked-final-score" id="final-score-wrap">
                <span class="ranked-final-label">Sua pontuação</span>
                <span class="ranked-final-value" id="final-score">0</span>
                <span class="ranked-final-detail" id="final-detail">0 níveis completados</span>
                <span class="ranked-final-record hidden" id="final-result"></span>
            </div>
            <div class="solo-modal-actions" id="overlay-actions"></div>
        </div>
    </div>

    <div class="solo-success hidden" id="success-banner" role="status" aria-live="polite">
        <span id="success-text">Sucesso!</span>
    </div>

    <script>
        window.DESAFIO_ID = <?php echo $id_desafio; ?>;
        window.OPONENTE_NOME = <?php echo json_encode($oponente_nome, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="../js/jogo_amigos.js"></script>
</body>
</html>
