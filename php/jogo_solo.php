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
    <title>Quimicraft - Jogar Solo</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="solo-body">

    <header class="solo-header">
        <div class="solo-timer" id="timer" aria-live="polite">00:30</div>
        <div class="solo-level" id="level-label">NIVEL: 1</div>
        <a class="solo-sair" href="principal.php">SAIR</a>
    </header>

    <main class="solo-main">
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
        <div class="solo-modal">
            <h2 class="solo-modal-title" id="overlay-title">Tempo esgotado!</h2>
            <p class="solo-modal-text" id="overlay-text">O tempo acabou antes de formar o composto.</p>
            <div class="solo-modal-actions" id="overlay-actions">
                <button type="button" class="solo-modal-btn" id="btn-retry">Tentar novamente</button>
                <button type="button" class="solo-modal-btn solo-modal-btn-secondary" id="btn-restart">Reiniciar fase</button>
            </div>
        </div>
    </div>

    <div class="solo-success hidden" id="success-banner" role="status" aria-live="polite">
        <span id="success-text">Sucesso! Você formou H₂O</span>
    </div>

    <script src="../js/jogo_solo.js"></script>
</body>
</html>
