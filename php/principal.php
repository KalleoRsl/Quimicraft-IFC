<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$nome = $_SESSION['nome_usuario'] ?? 'USUÁRIO';
$turma = $_SESSION['id_turma'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Quimicraft - Menu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg">

    <header class="hud-top">
        <div class="hud-user">
            <div class="hud-avatar" aria-hidden="true"></div>
            <div class="hud-usertext">
                <div class="hud-username"><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="hud-userinfo"><?php echo $turma !== '' ? ('TURMA ' . htmlspecialchars((string)$turma, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
            </div>
        </div>

        <a class="hud-sair" href="sair.php">SAIR</a>
    </header>

    <main class="menu-wrap">
        <div class="menu-grid">
            <a class="menu-btn" href="jogo_amigos.php">JOGAR<br>COM AMIGOS</a>
            <a class="menu-btn" href="jogo_solo.php">JOGAR<br>SOLO</a>
            <a class="menu-btn" href="ranked.php">JOGAR<br>RANKEADA</a>
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
