<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../html/login.html');
    exit();
}

include('conexao.php');

$id_usuario = (int)$_SESSION['id_usuario'];
$mensagem = '';
$tipo = 'sucesso';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit();
}

$acao = $_POST['acao'] ?? 'salvar';

if ($acao === 'foto' && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $arquivo = $_FILES['foto'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $mensagem = 'Erro ao enviar a imagem.';
        $tipo = 'erro';
    } elseif ($arquivo['size'] > 2 * 1024 * 1024) {
        $mensagem = 'A imagem deve ter no máximo 2 MB.';
        $tipo = 'erro';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);

        $extensoes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!isset($extensoes[$mime])) {
            $mensagem = 'Formato inválido. Use JPG, PNG, GIF ou WEBP.';
            $tipo = 'erro';
        } else {
            $dir = dirname(__DIR__) . '/uploads/avatars';

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $nomeArquivo = 'user_' . $id_usuario . '.' . $extensoes[$mime];
            $destino = $dir . '/' . $nomeArquivo;
            $caminhoDb = 'uploads/avatars/' . $nomeArquivo;

            foreach (glob($dir . '/user_' . $id_usuario . '.*') as $antigo) {
                if (is_file($antigo)) {
                    unlink($antigo);
                }
            }

            if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                $sql = 'UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?';
                $stmt = mysqli_prepare($conexao, $sql);
                mysqli_stmt_bind_param($stmt, 'si', $caminhoDb, $id_usuario);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $_SESSION['foto_perfil'] = $caminhoDb;
                $mensagem = 'Foto atualizada com sucesso!';
            } else {
                $mensagem = 'Não foi possível salvar a foto.';
                $tipo = 'erro';
            }
        }
    }
} elseif ($acao === 'nickname') {
    $nickname = trim($_POST['nickname'] ?? '');

    if ($nickname === '') {
        $mensagem = 'O nickname não pode ficar vazio.';
        $tipo = 'erro';
    } elseif (mb_strlen($nickname) > 50) {
        $mensagem = 'O nickname deve ter no máximo 50 caracteres.';
        $tipo = 'erro';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-. ]+$/u', $nickname)) {
        $mensagem = 'Use apenas letras, números, espaços, _ - ou .';
        $tipo = 'erro';
    } else {
        $sqlCheck = 'SELECT id_usuario FROM usuarios WHERE nome_usuario = ? AND id_usuario <> ? LIMIT 1';
        $stmtCheck = mysqli_prepare($conexao, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, 'si', $nickname, $id_usuario);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $existe = $resCheck && mysqli_num_rows($resCheck) > 0;
        mysqli_stmt_close($stmtCheck);

        if ($existe) {
            $mensagem = 'Este nickname já está em uso.';
            $tipo = 'erro';
        } else {
            $sql = 'UPDATE usuarios SET nome_usuario = ? WHERE id_usuario = ?';
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, 'si', $nickname, $id_usuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $_SESSION['nome_usuario'] = $nickname;
            $mensagem = 'Nickname atualizado!';
        }
    }
}

mysqli_close($conexao);

$redirect = 'perfil.php';
if ($mensagem !== '') {
    $redirect .= '?msg=' . urlencode($mensagem) . '&tipo=' . urlencode($tipo);
}

header('Location: ' . $redirect);
exit();
