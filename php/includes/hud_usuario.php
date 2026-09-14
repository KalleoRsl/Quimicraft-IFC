<?php

function fotoPerfilUrl(?string $foto): ?string
{
    if ($foto === null || $foto === '') {
        return null;
    }

    $caminhoAbsoluto = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $foto);

    if (!is_file($caminhoAbsoluto)) {
        return null;
    }

    return '../' . $foto;
}

function obterConexaoHud()
{
    global $conexao;

    if (isset($conexao) && $conexao instanceof mysqli) {
        try {
            if ($conexao->ping()) {
                return $conexao;
            }
        } catch (Throwable $e) {
        }
    }

    include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'conexao.php';

    return $conexao;
}

function nomeTurmaHud(): string
{
    $nome = trim((string)($_SESSION['nome_turma'] ?? ''));

    if ($nome === '') {
        $idTurma = $_SESSION['id_turma'] ?? '';

        if ($idTurma === '' || $idTurma === null) {
            return '';
        }

        $conexao = obterConexaoHud();
        $id = (int)$idTurma;
        $sql = 'SELECT nome_turma FROM turmas WHERE id_turma = ? LIMIT 1';
        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $linha = $resultado ? mysqli_fetch_assoc($resultado) : null;
            mysqli_stmt_close($stmt);
            $nome = trim((string)($linha['nome_turma'] ?? ''));

            if ($nome !== '') {
                $_SESSION['nome_turma'] = $nome;
            }
        }
    }

    return $nome !== '' ? strtoupper($nome) : '';
}

function renderHudTurma(): string
{
    $nome = nomeTurmaHud();

    return $nome !== '' ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '';
}

function renderHudAvatar(?string $foto): string
{
    $url = fotoPerfilUrl($foto);
    $style = '';

    if ($url !== null) {
        $style = ' style="background-image:url(' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . ');background-size:cover;background-position:center;"';
    }

    return '<a href="perfil.php" class="hud-avatar-link" title="Meu perfil" aria-label="Meu perfil">'
        . '<div class="hud-avatar"' . $style . ' aria-hidden="true"></div>'
        . '</a>';
}

function renderHudFooter(string $paginaAtiva = ''): string
{
    $amigosAtivo = $paginaAtiva === 'amigos' ? ' hud-ico-active' : '';
    $rankingAtivo = $paginaAtiva === 'ranking' ? ' hud-ico-active' : '';

    return '<footer class="hud-bottom">'
        . '<div class="hud-bottom-left" aria-hidden="true"></div>'
        . '<div class="hud-title">QUIMICRAFT</div>'
        . '<div class="hud-right">'
        . '<a class="hud-ico' . $amigosAtivo . '" href="amigos.php" title="Amigos" aria-label="Amigos">'
        . '<span class="ico ico-friends" aria-hidden="true"></span>'
        . '</a>'
        . '<a class="hud-ico' . $rankingAtivo . '" href="ranking.php" title="Ranking" aria-label="Ranking">'
        . '<span class="ico ico-trophy" aria-hidden="true"></span>'
        . '</a>'
        . '</div>'
        . '</footer>';
}
