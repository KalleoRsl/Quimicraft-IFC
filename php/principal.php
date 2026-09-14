<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$nome = $_SESSION['nome_usuario'] ?? 'USUÁRIO';
$foto = $_SESSION['foto_perfil'] ?? '';

include('includes/hud_usuario.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quimicraft - Menu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg">

    <header class="hud-top">
        <?php echo renderHudUserBlock($foto, $nome); ?>
        <a class="hud-sair" href="sair.php">SAIR</a>
    </header>

    <main class="home-main">
        <h1 class="brand-logo">QUIMI<span class="logo-soft">CRAFT</span></h1>
        <p class="brand-tagline">Aprenda • Evolua • Explore</p>

        <div class="mode-grid">
            <a class="mode-card" href="jogo_solo.php">
                <span class="mode-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </span>
                <span class="mode-title">JOGAR</span>
                <span class="mode-sub">SOLO</span>
            </a>
            <a class="mode-card" href="ranked.php">
                <span class="mode-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M14.2 3.2 20.8 9.8"/><path d="M18.6 5.4 8.5 15.5"/><path d="M7 17l-3 5 5-3"/><path d="M9.8 3.2 3.2 9.8"/><path d="M5.4 5.4 15.5 15.5"/><path d="M17 17l3 5-5-3"/></svg>
                </span>
                <span class="mode-title">JOGAR</span>
                <span class="mode-sub">RANQUEADA</span>
            </a>
            <a class="mode-card" href="jogo_amigos.php">
                <span class="mode-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="16" cy="9" r="2.5"/><path d="M3.5 19c.4-3.2 3-5.5 6.5-5.5 1.4 0 2.7.4 3.8 1"/><path d="M15 19c.3-2 1.7-3.5 4-3.5 1 0 1.8.3 2.5.8"/></svg>
                </span>
                <span class="mode-title">JOGAR</span>
                <span class="mode-sub">AMIGOS</span>
            </a>
        </div>
    </main>

    <?php echo renderHudFooter(); ?>

    <script src="../js/alerta.js"></script>
</body>
</html>
