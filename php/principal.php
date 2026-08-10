<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$nome = $_SESSION['nome_usuario'] ?? 'USUÁRIO';
$turma = $_SESSION['id_turma'] ?? '';
$foto = $_SESSION['foto_perfil'] ?? '';

include('includes/hud_usuario.php');
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
            <?php echo renderHudAvatar($foto); ?>
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

    <?php echo renderHudFooter(); ?>

</body>
</html>
