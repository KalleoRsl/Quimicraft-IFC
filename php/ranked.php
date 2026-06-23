<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Jogar Rankeada</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="solo-body">

    <header class="solo-header ranked-header">
        <div class="ranked-hud-left">
            <div class="solo-timer" id="timer" aria-live="polite">01:00</div>
            <div class="ranked-score" id="score-display" aria-live="polite">
                <span class="ranked-score-label">PTS</span>
                <span class="ranked-score-value" id="score">0</span>
            </div>
        </div>
        <div class="solo-level" id="phase-label">FASE: 1</div>
        <a class="solo-sair" href="principal.php">SAIR</a>
    </header>

    <main class="solo-main">
        <p class="solo-target-category solo-cat-oxido" id="target-category">ÓXIDO</p>
        <h1 class="solo-target" id="target-name">PEROXIDO DE HIDROGENIO</h1>
        <p class="solo-target-hint" id="target-hint">Forme: Peróxido de hidrogênio</p>

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
            <h2 class="solo-modal-title" id="overlay-title">Tempo esgotado!</h2>
            <p class="solo-modal-text" id="overlay-text">Sua pontuação final será exibida abaixo.</p>
            <div class="ranked-final-score" id="final-score-wrap">
                <span class="ranked-final-label">Pontuação final</span>
                <span class="ranked-final-value" id="final-score">0</span>
                <span class="ranked-final-detail" id="final-detail">0 compostos formados</span>
                <span class="ranked-final-record hidden" id="final-record">Novo recorde!</span>
            </div>
            <div class="solo-modal-actions" id="overlay-actions"></div>
        </div>
    </div>

    <div class="solo-success hidden" id="success-banner" role="status" aria-live="polite">
        <span id="success-text">Sucesso! +80 pts</span>
    </div>

    <script src="../js/jogo_ranked.js"></script>
</body>
</html>
