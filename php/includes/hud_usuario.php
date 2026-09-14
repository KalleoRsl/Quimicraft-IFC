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

function renderHudUserBlock(?string $foto, string $nome): string
{
    $turma = renderHudTurma();

    return '<div class="hud-user">'
        . renderHudAvatar($foto)
        . '<div class="hud-usertext">'
        . '<div class="hud-username">' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . '</div>'
        . '<div class="hud-userinfo">' . ($turma !== '' ? $turma : 'SEM TURMA') . '</div>'
        . '</div>'
        . '</div>';
}

function renderHudFooter(string $paginaAtiva = ''): string
{
    $amigosAtivo = $paginaAtiva === 'amigos' ? ' hud-ico-active' : '';
    $rankingAtivo = $paginaAtiva === 'ranking' ? ' hud-ico-active' : '';

    return '<footer class="home-footer">'
        . '<a class="' . trim($amigosAtivo) . '" href="amigos.php">'
        . '<svg class="footer-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M16 11h6"/></svg>'
        . 'AMIGOS</a>'
        . '<a class="' . trim($rankingAtivo) . '" href="ranking.php">'
        . '<svg class="footer-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M7 6H5a3 3 0 0 0 3 5M17 6h2a3 3 0 0 1-3 5"/></svg>'
        . 'RANKING</a>'
        . '</footer>';
}
